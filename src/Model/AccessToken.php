<?php

namespace IPS\Model;

/**
 * Twitch APIを利用するためのアクセストークンのクラス
 */
class AccessToken
{
    /**
     * アクセストークンの更新処理
     *
     * @param Oauth\Token $token
     */
    public function refresh(Oauth\Token $token)
    {
        Log::debug('refresh access token.');

        // 必要な情報を設定
        $client_id = Config::get('client_id');
        $client_secret = Config::get('client_secret');
        $refresh_token = $token->getRefresh();

        // トークンをリフレッシュするためのリクエストURL
        $token_url = Config::get('twitch', 'api', 'oauth2token');

        // POSTデータを設定
        $data = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        curl_close($ch);

        // decode response and check.
        $response_data = json_decode($response, true);
        if (!isset($response_data['access_token'])) {
            Log::info($response);
            throw new \Exception('Failed to refresh an access token.');
        }

        $token->setAccess($response_data['access_token']);
        $expire = (int)$response_data['expires_in'] + time() - 60 * 10;
        $token->setExpire($expire);
    }

    /**
     * 認可コードからアクセストークンとリフレッシュトークンを取得する
     *
     * @param string $code 認可コード
     * @return Oauth\Token
     */
    public function getTokenByCode($code)
    {
        $clientId = Config::get('client_id');
        $clientSecret = Config::get('client_secret');
        $redirectUrl = Config::get('ips', 'url');

        $requestUrl = Config::get('twitch', 'api', 'oauth2token');
        $params = [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $redirectUrl,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        Log::debug($response);

        $data = json_decode($response, true);

        $accessToken = isset($data['access_token']) ? $data['access_token'] : null;
        $refreshToken = isset($data['refresh_token']) ? $data['refresh_token'] : null;

        $expiresIn = isset($data['expires_in']) ? $data['expires_in'] : null;
        if(is_null($expiresIn)) {
            $expire = null;
        } else {
            // 1分前を設定
            $expire = time() + (int)$expiresIn - 60;
        }

        $token = Oauth\Factory::create($accessToken, $refreshToken, $expire);
        return $token;
    }

    /**
     * IPS利用者のアクセストークン、リフレッシュトークンを登録する
     *
     * @param Oauth\Token $token 登録するトークン情報のエンティティ
     */
    public function saveTokens(Oauth\Token $token)
    {
        $query = "insert "
               . "  into SETTINGS "
               . "  ( "
               . "     channel, period, addition, command, name, unit, "
               . "     refresh_token, access_token, access_token_expire "
               . "  ) "
               . "  values "
               . "  ( "
               . "    :login, 5, 10, 'ips', 'ポイント', 'point', "
               . "    :refresh_token, :access_token, :access_token_expire "
               . "  ) "
               . "on conflict(channel) "
               . "do update "
               . "  set "
               . "    refresh_token = excluded.refresh_token, "
               . "    access_token = excluded.access_token, "
               . "    access_token_expire = excluded.access_token_expire ";

        $login = $token->getLogin();
        $refreshToken = $token->getRefresh();
        $accessToken = $token->getAccess();
        $expire = $token->getExpire();

        $params = [
            ':login' => $login,
            ':refresh_token' => $refreshToken,
            ':access_token' => $accessToken,
            ':access_token_expire' => $expire,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
    }

    /**
     * 受け取ったOauthトークンを元にシグネチャを生成して返す
     *
     * @param Oauth\Token $token
     * @return string signature
     */
    public function sign(Oauth\Token $token)
    {
        $login = $token->getLogin();
        $refreshToken = $token->getRefresh();
        $accessToken = $token->getAccess();
        $expire = $token->getExpire();

        if(is_null($login) || is_null($refreshToken) || is_null($accessToken) || is_null($expire)) {
            return false;
        }

        $origin = "{$login}--{$refreshToken}--{$accessToken}--{$expire}";
        $signature = password_hash($origin, PASSWORD_ARGON2I);
        return $signature;
    }

    /**
     * 受け取ったシグネチャが正しいかどうかチェックし、結果を返す
     *
     * @param string $signature
     * @param string $accessToken
     * @param string $refreshToken
     * @param string $login
     * @param int $expire
     * @return bool
     */
    public function verifySign($signature, $accessToken, $refreshToken, $login, $expire)
    {
        $origin = "{$login}--{$refreshToken}--{$accessToken}--{$expire}";
        $result = password_verify($origin, $signature);
        return $result;
    }
}
