<?php

namespace IPS\Controller;

use IPS\Model as Model;
use IPS\Model\Log as Log;
use IPS\Model\Login as Login;

class Channelpoint extends Base
{
    protected $login;
    protected $twitch;
    protected $channelpoint;
    protected $accessToken;

    public function __construct(Model\Twitch $twitch, Model\Channelpoints $channelpoint, Model\AccessToken $accessToken)
    {
        $this->twitch = $twitch;
        $this->channelpoint = $channelpoint;
        $this->accessToken = $accessToken;
    }

    public function action()
    {
        $this->template = 'channelpoint.twig';
        $this->assign('page_name', 'IPS Online Channel Point');
        $isLoggedIn = $this->isLoggedIn();
        $this->assign('loggedin', $isLoggedIn);

        if($isLoggedIn) {
            $registeredList = $this->channelpoint->getList($this->login);
            $registeredIds = array_column($registeredList, 'id');

            $rewardList = [];
            $rewards = $this->twitch->getRewards($this->login);
            foreach($rewards as $r) {
                if($r['is_enabled'] !== true) {
                    continue;
                }
                $tmp = [
                    'id' => $r['id'],
                    'title' => $r['title'],
                    'cost' => $r['cost'],
                    'bg' => $r['background_color'],
                    'image' => is_null($r['image']) ? $r['default_image']['url_1x'] : $r['image']['url_1x'],
                    'is_registered' => in_array($r['id'], $registeredIds),
                ];

                $rewardList[] = $tmp;
            }
            Log::debug(var_export($rewardList, true));
            $this->assign('rewards', $rewardList);
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
