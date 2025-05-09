<?php

namespace IPS\Controller;

use IPS\Model as Model;
use IPS\Model\Twitch as Twitch;
use IPS\Model\Log as Log;

class Top extends Base
{
    protected $accessToken;
    protected $twitch;

    public function __construct(Model\AccessToken $accessToken, Twitch $twitch)
    {
        $this->accessToken = $accessToken;
        $this->twitch = $twitch;
    }

    public function action()
    {
        $this->template = 'top.twig';
        $this->assign('page_name', 'IPS Online Top Page');
        $isLoggedIn = $this->isLoggedIn();
        $this->assign('loggedin', $isLoggedIn);

        $code = $this->param('code');

        // codeがある = Twitchの同意を踏んで飛んできた場合
        if(!is_null($code)) {
            Log::info("register redirect: {$code}");
            $token = $this->accessToken->getTokenByCode($code);
            
            // アクセストークンが取得できなかった場合 (認可コードが不正、もしくは使用済み)
            if(is_null($token->getAccess())) {
                $this->assignErrors('認可コード(code)が不正です。正常に処理できませんでした。');
                Log::info("Failed to get access code from authorization code: {$code}");
                return;
            }

            Log::debug("try to specify login");
            $this->twitch->specifyLogin($token);

            // loginが取得できなかった場合
            if(is_null($token->getLogin())) {
                $this->assignErrors('アクセストークンの検証に失敗しました。');
                Log::info("Failed to validate access token: " . $token->getAccess());
                return;
            }

            // 取得したアクセストークンとリフレッシュトークンをDBに保存
            $this->accessToken->saveTokens($token);

            // レイド通知、ビッツ通知、サブギフ通知のEventSub購読処理
            $this->twitch->subscribeEventSub($token->getLogin(), Twitch::SUBTYPE_RAID);
            $this->twitch->subscribeEventSub($token->getLogin(), Twitch::SUBTYPE_BITS);
            $this->twitch->subscribeEventSub($token->getLogin(), Twitch::SUBTYPE_GIFT);

            $this->assign('twitch', 'login', $token->getLogin());
            $this->assign('twitch', 'access_token', $token->getAccess());
            $this->assign('twitch', 'refresh_token', $token->getRefresh());
            $this->assign('twitch', 'expire', $token->getExpire());

            $signature = $this->accessToken->sign($token);
            $this->assign('twitch', 'signature', $signature);

            $cookieData = [
                'a' => $token->getAccess(),
                'r' => $token->getRefresh(),
                'l' => $token->getLogin(),
                'e' => $token->getExpire(),
                's' => $signature,
            ];
            $cookie = json_encode($cookieData);
            $this->cookie('IPS', $cookie, $token->getExpire());

            // ログイン状態にする
            $this->assign('loggedin', true);
            $this->assign('login', $token->getLogin());
        }
    }

    /**
     * ログイン状態かどうかを返す
     *
     * @return bool
     */
    private function isLoggedIn()
    {
        $this->assign('login', 'ゲスト');
        if(!isset($_COOKIE['IPS'])) {
            return false;
        }
        $cookie = json_decode($_COOKIE['IPS'], true);
        if(empty($cookie)) {
            return false;
        }

        $signature = $cookie['s'];
        $access = $cookie['a'];
        $refresh = $cookie['r'];
        $login = $cookie['l'];
        $expire = $cookie['e'];

        $result = $this->accessToken->verifySign($signature, $access, $refresh, $login, $expire);

        if($result) {
            $this->assign('login', $login);
        }

        return $result;
    }
}
