<?php

namespace IPS\Controller;

use IPS\Model as Model;
use IPS\Model\Log as Log;
use IPS\Model\Login as Login;

class Channelpoint extends Base
{
    protected $login;
    protected $twitch;
    protected $accessToken;

    public function __construct(Model\Twitch $twitch, Model\AccessToken $accessToken)
    {
        $this->twitch = $twitch;
        $this->accessToken = $accessToken;
    }

    public function action()
    {
        $this->template = 'channelpoint.twig';
        $this->assign('page_name', 'IPS Online Channel Point');
        $isLoggedIn = $this->isLoggedIn();
        $this->assign('loggedin', $isLoggedIn);

        if($isLoggedIn) {
            $rewards = $this->twitch->getRewards($channel);
            $this->assign('rewards', $rewards);
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
            $this->login = $login;
            $this->assign('login', $login);
        }

        return $result;
    }
}
