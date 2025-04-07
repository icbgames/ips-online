<?php

namespace IPS\Batch;

use IPS\Model as Model;
use IPS\Model\Log as Log;

/**
 * サブスクステータスを更新する処理
 * 
 */
class SubscriptionUpdater
{
    private $user;
    private $twitch;

    public function __construct(Model\User $user, Model\Twitch $twitch)
    {
        $this->user = $user;
        $this->twitch = $twitch;
    }

    public function execute()
    {
        $channelList = ['kirukiru_21'];
        foreach($channelList as $channel) {
            $this->updateTargetChannel($channel);
        }
    }

    private function updateTargetChannel($channel)
    {
        $userList = $this->user->getRecentChatUserList($channel, 120);
        foreach($userList as $user) {
            $this->updateSubscriptionStatus($channel, $user['login']);
            sleep(3);
        }
    }

    private function updateSubscriptionStatus($channel, $login)
    {
        $result = $this->twitch->getSubscriptionInfo($login, $channel);
        if(is_null($result)) {
            $tier = 0;
        } elseif($result['tier'] == '1000') {
            $tier = 1;
        } elseif($result['tier'] == '2000') {
            $tier = 2;
        } elseif($result['tier'] == '3000') {
            $tier = 3;
        } else {
            $tier = 0;
        }

        Log::debug("channel: {$channel} / login: {$login} / tier: {$tier}");
        $this->user->updateSubscriptionTier($channel, $login, $tier);
    }
}
