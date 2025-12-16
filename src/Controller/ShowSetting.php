<?php

namespace IPS\Controller;

use IPS\Model as Model;

class ShowSetting extends Base
{
    private $settings;

    public function __construct(Model\Settings $settings)
    {
        $this->settings = $settings;
    }

    public function action()
    {
        $this->template = 'show_setting.twig';
        $this->assign('page_name', 'IPS Online Setting');
        $isLoggedIn = $this->isLoggedIn();
        $this->assign('loggedin', $isLoggedIn);

        $channel = $this->param('channel');
        if(empty($channel)) {
            $this->assign('error_message', '指定したチャンネルは存在しません');
            return;
        }

        $this->assign('channel', $channel);

        $cfg = $this->settings->get($channel);
        if(empty($cfg)) {
            $this->assign('error_message', '指定したチャンネルは存在しません');
            return;
        }

        // check ACL: 0 = private, 1 = public
        $acl = isset($cfg['setting_acl']) ? (int)$cfg['setting_acl'] : 0;
        if($acl !== 1) {
            $this->assign('error_message', "{$channel}の設定は非公開です");
            return;
        }

        $this->assign('settings', $cfg);
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
