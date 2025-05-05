<?php

namespace IPS\Model;

/**
 * App Access Tokenのクラス
 *
 * 一部のTwitch APIはUser Access Tokenでは利用できずApp Access Tokenが必要となる
 * (例) EventSubの購読登録リクエスト等
 */
class AppAccessToken
{
    /**
     * 有効なApp Access Tokenを返す
     *
     * @return string App Access Token
     */
    public function get()
    {
        $query = "select "
               . "  key, val "
               . "from "
               . "  IPS "
               . "where "
               . "  key = :key ";

        $params = [':key' => 'app_access_token'];

        $db = DB::instance();
        $db->execute($query, $params);
        
        $results = $db->fetch();

        if(!isset($result[0])) {
            // App Access Token が無いので更新処理をしたうえで再取得
            $this->refresh();
            $db->execute($query, $params);
            $results = $db->fetch();
        }

        $accessToken = $results[0]['val'];

        // expireチェック
        $params = [':key' => 'app_access_token_expire'];
        $db->execute($query, $params);
        $results = $db->fetch();

        if(!isset($result[0])) {
            // expireだけ無い
            throw new \Exception('expire is missing.');
        }

        $expire = (int)$results[0]['val'];

        if(time() > $expire) {
            // 有効期限切れなので更新処理をしたうえで再取得
            $this->refresh();
            $params = [':key' => 'app_access_token'];
            $db->execute($query, $params);
            $results = $db->fetch();
            
            $accessToken = $results[0]['val'];
        }
        
        // 有効期限の切れていないApp Access Tokenを返す
        return $accessToken;
    }


    /**
     * App Access Tokenの更新処理
     *
     * @param Oauth\Token $token
     */
    public function refresh()
    {
        Log::debug('refresh app access token.');

        $url = Config::get('twitch', 'api', 'oauth2token');
        $clientId = Config::get('client_id');
        $clientSecret = Config::get('client_secret');

        // request parameter
        $data = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Log::debug("STATUS: {$status}");
        Log::debug("RESPONSE: " . var_export($response, true));

        $json = json_decode($response, true);
        if(!isset($json['access_token'])) {
            throw new \Exception('Failed to refresh an app access token.');
        }

        $accessToken = $json['access_token'];
        $expire = (int)$json['expires_in'] + time() - 60 * 10;

        $this->save($accessToken, $expire);
    }

    /**
     * App Access TokenとExpireをDBに保存する
     *
     * @param string $accessToken
     * @param int $expire
     */
    public function save($accessToken, $expire)
    {
        $query = "insert into "
               . "  IPS "
               . "  ( key, val ) "
               . "values "
               . "  ( :key, :val ) "
               . "on conflict(key) "
               . "do update "
               . "set "
               . "  val = excluded.val ";
        
        $db = DB::instance();
        $params = [
            ':key' => 'app_access_token',
            ':val' => $accessToken,
        ];
        $db->execute($query, $params);
        
        $params = [
            ':key' => 'app_access_token_expire',
            ':val' => $expire,
        ];
        $db->execute($query, $params);
    }
}
