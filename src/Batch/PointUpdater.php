<?php

namespace IPS\Batch;

use IPS\Model as Model;
use IPS\Model\Config as Config;
use IPS\Model\Log as Log;

/**
 * IPSポイントの対象となるユーザーを抽出してポイントを付与する処理
 * 本処理は1分おきに起動することを前提としている点に注意
 * 
 * 
 */
class PointUpdater
{
    private $settings;
    private $user;
    private $point;

    public function __construct(Model\Settings $settings, Model\User $user, Model\Point $point)
    {
        $this->settings = $settings;
        $this->user = $user;
        $this->point = $point;
    }

    public function execute()
    {
        $targetList = Config::get('ips', 'channels');

        foreach($targetList as $target) {
            Log::debug("update target: {$target}");

            // 集計対象のチャンネルでポイント対象となる有効な時間を取得
            // 最終発言日時からこの時間の間はポイント対象となる
            // なので、最終発言日時がM分前以降のユーザーを取得する
            $setting = $this->settings->get($target);
            $minutes = (int)$setting['period'];
            $userList = $this->user->getRecentChatUserList($target, $minutes);

            // 1回あたりの加算ポイント
            $add = (int)$setting['addition'];
            $add1 = (int)$setting['addition_t1'];
            $add2 = (int)$setting['addition_t2'];
            $add3 = (int)$setting['addition_t3'];

            // 集計対象のチャンネルのポイント対象外ユーザーを取得
            // bot等にポイントを付与しないための措置
            $ignoreListWk = $this->point->getIgnoreList($target);
            $ignoreList = [];
            foreach($ignoreListWk as $w) {
                $ignoreList[] = $w['login'];
            }

            // ポイント対象ユーザーごとの処理
            foreach($userList as $user) {
                // 無視リストに含まれていたらスキップ
                if(in_array($user['login'], $ignoreList)) {
                    Log::debug("SKIP -> {$user['login']}");
                    continue;
                }

                // ポイント付与処理
                $userInfo = $this->user->getUserInfo($user['login']);
                
                // サブスクTierに応じて加算ポイントを計算
                switch($user['tier']) {
                    case 1: $addition = $add + $add1; break;
                    case 2: $addition = $add + $add2; break;
                    case 3: $addition = $add + $add3; break;
                    default: $addition = $add;
                }

                // 実際の加算処理
                $this->point->add($userInfo['user_id'], $userInfo['login'], $userInfo['disp_name'], $target, $addition);
            }
        }

    }
}
