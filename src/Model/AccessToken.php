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
}
