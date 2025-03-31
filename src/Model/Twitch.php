<?php

namespace IPS\Model;

class Twitch
{
    protected $accessToken;

    /**
     * Constructor
     *
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
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
        }

        $response = json_decode($response, false);
        if(!isset($response->data[0])) {
            // error
        }
        
        return $response->data[0];
    }
    
    /**
     * 対象のチャンネルにメッセージを送信する
     *
     * @param string $channel 送信するチャンネル
     * @param string $message 送信するチャットメッセージ
     */
    public function sendChat($channel, $message)
    {
        $token = Oauth\Factory::create();
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

        $data = json_decode($response, true);
        $login = isset($data['data'][0]['login']) ? $data['data'][0]['login'] : null;

        $token->setLogin($login);
        return $login;
    }
}
