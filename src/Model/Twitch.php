<?php

namespace IPS\Model;

/**
 * Twitch関連情報をTwitch APIを通じて操作するクラス
 *
 */
class Twitch
{
    /**
     * EventSub用のtype
     */
    const SUBTYPE_RAID = 'channel.raid';
    const SUBTYPE_BITS = 'channel.cheer';
    const SUBTYPE_GIFT = 'channel.subscription.gift';
    const SUBTYPE_STREAM_ONLINE = 'stream.online';
    const SUBTYPE_STREAM_OFFLINE = 'stream.offline';

    protected $accessToken;
    protected $appAccessToken;

    /**
     * Constructor
     *
     * @param AccessToken $accessToken
     * @param AppAccessToken $appAccessToken
     */
    public function __construct(AccessToken $accessToken, AppAccessToken $appAccessToken)
    {
        $this->accessToken = $accessToken;
        $this->appAccessToken = $appAccessToken;
    }

    /**
     * 指定されたloginのユーザー情報を取得する
     * TwitchのAPIをコールする
     *
     * @param string $login
     * @return array
     */
    public function getUserInfo($login)
    {
        $clientId = Config::get('client_id');
        $token = Oauth\Factory::create();

        if($token->isExpired()) {
            $this->accessToken->refresh($token);
        }
        $accessToken = $token->getAccess();

        $url = Config::get('twitch', 'api', 'users') . '?login=' . urlencode($login);

        $options = [
            'http' => [
                'header' => "Client-ID: {$clientId}\r\n" .
                            "Authorization: Bearer {$accessToken}\r\n",
                'method' => 'GET'
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if($response === false) {
            // error
            throw new \Exception("Failed to get user info: {$login}");
        }

        $response = json_decode($response, false);
        if(!isset($response->data[0])) {
            // error
            throw new \Exception("Invalid user info: {$login}");
        }
        
        return $response->data[0];
    }

    /**
     * 指定したユーザーのサブスク状況を返す
     *
     * @param string $login
     * @param string $channel
     * @return array
     */
    public function getSubscriptionInfo($login, $channel)
    {
        // channnelからチャンネルIDを取得する
        $user = new User($this); // @todo DIしたいのに設計ミスって循環依存になってしまったのでいつか設計見直す
        $userInfo = $user->getUserInfo($channel);
        $channelId = $userInfo['user_id'];

        // loginからユーザーIDを取得する
        $loginUserInfo = $user->getUserInfo($login);
        $userId = $loginUserInfo['user_id'];

        // TwitchのクライアントIDとOAuthトークンを設定
        $clientId = Config::get('client_id');
        $token = $this->accessToken->getUserToken($channel);
        $accessToken = $token->getAccess();

        // ヘッダー設定
        $headers = [
            "Client-ID: {$clientId}",
            "Authorization: Bearer {$accessToken}",
        ];

        // サブスクライバー情報を取得するためのAPIエンドポイント
        $url = Config::get('twitch', 'api', 'subscriptions');
        $url = "{$url}?broadcaster_id={$channelId}&user_id={$userId}";

        // cURLを使用してAPIリクエスト
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        // レスポンスをデコード
        $data = json_decode($response, true);

        // サブスクライバーかどうかを判定
        if (isset($data['data']) && count($data['data']) > 0) {
            return $data['data'][0];
        }
        return null;
    }

    /**
     * 対象のチャンネルにメッセージを送信する
     *
     * @param string $channel 送信するチャンネル
     * @param string $message 送信するチャットメッセージ
     */
    public function sendChat($channel, $message)
    {
        $token = $this->accessToken->getUserToken('ips_online');
        if($token->isExpired()) {
            $this->accessToken->refresh($token);
        }
        $accessToken = $token->getAccess();

        // Twitch IRCサーバー情報
        $server = Config::get('twitch', 'irc', 'host');
        $port = Config::get('twitch', 'irc', 'port');
        $nickname = Config::get('twitch', 'irc', 'nick');
        $timeout = Config::get('twitch', 'irc', 'timeout');
        $channel = "#{$channel}";
        
        // IRCサーバーに接続
        $socket = fsockopen($server, $port, $errno, $errstr, $timeout);
        if (!$socket) {
            Log::error("IRC connect error: {$errstr} ({$errno})");
            die("接続エラー: $errstr ($errno)\n");
        }
        
        // IRCサーバーにログイン
        fwrite($socket, "PASS oauth:{$accessToken}\r\n");
        fwrite($socket, "NICK {$nickname}\r\n");
        fwrite($socket, "JOIN {$channel}\r\n");

        // チャット送信
        fwrite($socket, "PRIVMSG {$channel} :{$message}\r\n");
        
        fclose($socket);
    }

    /**
     * アクセスコードから所有者のloginを特定し、エンティティにセットのうえ返す
     *
     * @param Oauth\Token $token
     * @param string
     */
    public function specifyLogin(Oauth\Token $token)
    {
        $accessToken = $token->getAccess();
        $clientId = Config::get('client_id');
        
        $url = Config::get('twitch', 'api', 'users');

        $headers = [
            "Authorization: Bearer {$accessToken}",
            "Client-ID: {$clientId}",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        Log::debug($response);

        $data = json_decode($response, true);
        $login = isset($data['data'][0]['login']) ? $data['data'][0]['login'] : null;

        $token->setLogin($login);
        return $login;
    }

    /**
     * 指定したチャンネルでレイド通知のEventSubを購読する
     *
     * @param string $channel
     * @param string $type
     */
    public function subscribeEventSub($channel, $type)
    {
        Log::debug("Subscribe EventSub: {$channel} - {$type}");

        // channnelからチャンネルIDを取得する
        $user = new User($this); // @todo DIしたいのに設計ミスって循環依存になってしまったのでいつか設計見直す
        $userInfo = $user->getUserInfo($channel);
        $userId = $userInfo['user_id'];

        // アクセストークンを取得
        $accessToken = $this->appAccessToken->get();

        $clientId = Config::get('client_id');
        $callbackUrl = Config::get('ips', 'callback');
        $secret = Config::get('eventsub_secret');

        // 既に購読済みかどうかチェックして購読済みなら何もしない
        $url = Config::get('twitch', 'api', 'eventsubs');
        $headers = [
            "Client-ID: {$clientId}",
            "Authorization: Bearer {$accessToken}",
            'Content-Type: application/json',
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($response, true);
        foreach($json['data'] as $subs) {
            switch($subs['type']) {
                case static::SUBTYPE_RAID:
                    if($subs['type'] === $type && $subs['condition']['to_broadcaster_user_id'] == $userId && $subs['status'] === 'enabled') {
                        Log::debug("Already subscribed: {$userId} ({$channel} - {$type})");
                        return;
                    }
                    break;

                case static::SUBTYPE_BITS:
                case static::SUBTYPE_GIFT:
                    if($subs['type'] === $type && $subs['condition']['broadcaster_user_id'] == $userId && $subs['status'] === 'enabled') {
                        Log::debug("Already subscribed: {$userId} ({$channel} - {$type})");
                        return;
                    }
                    break;
            }
        }


        // リクエストデータ
        switch($type) {
            case static::SUBTYPE_RAID:
                $condition = ['to_broadcaster_user_id' => (string)$userId];
                break;
            case static::SUBTYPE_BITS:
            case static::SUBTYPE_GIFT:
                $condition = ['broadcaster_user_id' => (string)$userId];
                break;
        }
        $request = [
            'type' => $type,
            'version' => '1',
            'condition' => $condition,
            'transport' => [
                'method' => 'webhook',
                'callback' => $callbackUrl,
                'secret' => $secret,
            ],
        ];
        Log::debug(var_export($request, true));
        
        // curl api call
        $headers = [
            "Client-ID: {$clientId}",
            "Authorization: Bearer {$accessToken}",
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Log::debug("STATUS: {$status}");
        Log::debug(var_export($response, true));
    }

    /**
     * 指定したチャンネルで配信開始・終了通知のEventSubを購読する
     *
     * @param string $channel
     */
    public function subscribeStreamEvent($channel)
    {
        Log::debug("Subscribe EventSub: {$channel} - stream");

        // channnelからチャンネルIDを取得する
        $user = new User($this); // @todo DIしたいのに設計ミスって循環依存になってしまったのでいつか設計見直す
        $userInfo = $user->getUserInfo($channel);
        $userId = $userInfo['user_id'];

        // アクセストークンを取得
        $accessToken = $this->appAccessToken->get();

        $clientId = Config::get('client_id');
        $callbackUrl = Config::get('ips', 'callback');
        $secret = Config::get('eventsub_secret');

        // 既に購読済みかどうかチェックして購読済みなら何もしない
        $url = Config::get('twitch', 'api', 'eventsubs');
        $headers = [
            "Client-ID: {$clientId}",
            "Authorization: Bearer {$accessToken}",
            'Content-Type: application/json',
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($response, true);
        foreach($json['data'] as $subs) {
            if($subs['type'] == static::SUBTYPE_STREAM_ONLINE &&
               $subs['condition']['broadcaster_user_id'] == $userId &&
               $subs['status'] === 'enabled') {
                Log::debug("Already subscribed: {$userId} ({$channel} - stream)");
                return;
            }
        }

        // リクエストデータ
        $condition = ['broadcaster_user_id' => (string)$userId];

        // 配信開始
        $request = [
            'type' => static::SUBTYPE_STREAM_ONLINE,
            'version' => '1',
            'condition' => $condition,
            'transport' => [
                'method' => 'webhook',
                'callback' => $callbackUrl,
                'secret' => $secret,
            ],
        ];
        Log::debug(var_export($request, true));
        
        // curl api call
        $headers = [
            "Client-ID: {$clientId}",
            "Authorization: Bearer {$accessToken}",
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Log::debug("STATUS: {$status}");
        Log::debug(var_export($response, true));

        // 配信終了
        $request['type'] = static::SUBTYPE_STREAM_OFFLINE;
        Log::debug(var_export($request, true));
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Log::debug("STATUS: {$status}");
        Log::debug(var_export($response, true));
    }
}
