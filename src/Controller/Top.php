<?php

namespace IPS\Controller;

use IPS\Model as Model;
use IPS\Model\Log as Log;

class Top extends Base
{
    protected $accessToken;
    protected $twitch;

    public function __construct(Model\AccessToken $accessToken, Model\Twitch $twitch)
    {
        $this->accessToken = $accessToken;
        $this->twitch = $twitch;
    }

    public function action()
    {
        $code = $this->param('code');

        // codeがある = Twitchの同意を踏んで飛んできた場合
        if(!is_null($code)) {
            $token = $this->accessToken->getTokenByCode($code);
            
            // アクセストークンが取得できなかった場合 (認可コードが不正、もしくは使用済み)
            if(is_null($token->getAccess())) {
                $this->assignErrors('認可コード(code)が不正です。正常に処理できませんでした。');
                Log::info("Failed to get access code from authorization code: {$code}");
                return;
            }

            $this->twitch->specifyLogin($token);

            // loginが取得できなかった場合
            if(is_null($token->getLogin())) {
                $this->assignErrors('アクセストークンの検証に失敗しました。');
                Log::info("Failed to validate access token: " . $token->getAccess());
                return;
            }

            // 取得したアクセストークンとリフレッシュトークンをDBに保存
            $this->accessToken->saveTokens($token);
        }

    }
}
