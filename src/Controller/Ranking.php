<?php

namespace IPS\Controller;

use IPS\Model as Model;
use IPS\Model\Log as Log;
use IPS\Model\Login as Login;

class Ranking extends Base
{
    protected $point;

    public function __construct(Model\Point $point)
    {
        $this->point = $point;
    }

    public function action()
    {
        $this->template = 'ranking.twig';
        $this->assign('page_name', 'IPS Online Ranking');
        $isLoggedIn = $this->isLoggedIn();
        $this->assign('loggedin', $isLoggedIn);

        $channel = $this->get('channel');
        if(empty($channel)) {
            $this->status = 302;
            header('Location: /');
            return;
        }

        Log::debug("ranking for {$channel}");
        $this->assign('channel' $channel);
        $ranking = $this->point->getPointRanking($channel);
        $this->assign('ranking', $ranking);
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
        $cookie = $_COOKIE['IPS'];
        $result = Login::verify($cookie);

        if($result) {
            $login = $this->getLogin();
            $this->assign('login', $login);
        }

        return $result;
    }

    private function getLogin()
    {
        $cookie = $_COOKIE['IPS'];
        $login = Login::get($cookie);
        return $login;
    }
}
