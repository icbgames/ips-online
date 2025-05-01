<?php

namespace IPS\Controller;

use IPS\Model as Model;
use IPS\Model\Log as Log;
use IPS\Model\Login as Login;

/**
 * おしらせページコントローラー
 */
class Information extends Base
{
    public function __construct()
    {
    }

    public function action()
    {
        // ログイン判定
        $login = 'ゲスト';
        $loggedin = false;

        if(isset($_COOKIE['IPS'])) {
            $cookie = $_COOKIE['IPS'];
            $loggedin = Login::verify($cookie);
            
            if($loggedin) {
                $login = Login::get($cookie);
            }
        }
        $this->assign('login', $login);
        $this->assign('loggedin', $loggedin);

        $this->template = 'information.twig';
        $this->assign('page_name', 'IPS Online Information');
    }
}
