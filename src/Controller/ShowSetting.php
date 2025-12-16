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
}
