<?php

namespace IPS\Controller;

use IPS\Model as Model;
use IPS\Model\Log as Log;

class Ignore extends Base
{
    protected $login;
    protected $point;
    protected $accessToken;

    public function __construct(Model\Settings $point, Model\AccessToken $accessToken)
    {
        $this->point = $point;
        $this->accessToken = $accessToken;
    }

    public function action()
    {
        $this->template = 'ignore.twig';
        $this->assign('page_name', 'IPS Online Ignore List');
        $isLoggedIn = $this->isLoggedIn();
        $this->assign('loggedin', $isLoggedIn);

        $ignoreList = $this->point->getIgnoreList($this->login);
        $this->assign('ignoreList', $ignoreList);
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
