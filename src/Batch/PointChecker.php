<?php

namespace IPS\Batch;

use IPS\Model as Model;
use IPS\Model\Log as Log;

/**
 * コマンドを実行したユーザーの保有ポイントと順位をチェックする処理
 * 
 * チェックした結果は当該チャンネルのチャット欄へ送信される
 */
class PointChecker
{
    private $point;
    private $settings;
    private $user;
    private $twitch;

    /**
     * Constructor
     *
     * @param Point    $point
     * @param Settings $settings
     * @param User     $user
     * @param Twitch   $twitch
     */
    public function __construct(Model\Point $point, Model\Settings $settings, Model\User $user, Model\Twitch $twitch)
    {
        $this->point = $point;
        $this->settings = $settings;
        $this->user = $user;
        $this->twitch = $twitch;
    }

    /**
     * 対象のチャンネルで保有しているポイントと順位を取得し、当該チャンネルの名称に即した内容でチャットに送信する
     *
     * @param string $user 実行するユーザー名(login)
     * @param string $channel チェック対象のチャンネル
     */
    public function execute($user, $channel)
    {
        $result = $this->point->getChannelPointAndRank($user, $channel);
        Log::debug(var_export($result,true));
        $settings = $this->settings->get($channel);
        $userInfo = $this->user->getUserInfo($user);

        $userName = $userInfo['disp_name'] ? $userInfo['disp_name'] : $userInfo['login'];
        $pointName = $settings['name'] ?? 'ポイント';
        $point = $result['points'];
        $unit = $settings['unit'] ?? 'point';
        $rank = $result['rank'];

        $message = "{$userName}さんの{$pointName}: {$point} {$unit} (#{$rank})";
        Log::debug(">>> " . $message);
        $this->twitch->sendChat($channel, $message);

    }
}
