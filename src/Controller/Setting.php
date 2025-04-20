<?php

namespace IPS\Controller;

use IPS\Model as Model;
use IPS\Model\Log as Log;

class Setting extends Base
{
    protected $login;
    protected $settings;
    protected $accessToken;

    public function __construct(Model\Settings $settings, Model\AccessToken $accessToken)
    {
        $this->settings = $settings;
        $this->accessToken = $accessToken;
    }

    public function action()
    {
        $this->template = 'setting.twig';
        $this->assign('page_name', 'IPS Online Setting');
        $isLoggedIn = $this->isLoggedIn();
        $this->assign('loggedin', $isLoggedIn);

        $settingInfo = $this->settings->get($this->login);
        $this->assign('setting', $settingInfo);
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
