<?php

namespace IPS\Batch;

use IPS\Model as Model;
use IPS\Model\Log as Log;

/**
 * IPSポイントの対象となるユーザーを抽出してポイントを付与する処理
 * 本処理は1分おきに起動することを前提としている点に注意
 * 
 * 
 */
class PointUpdater
{
    private $user;
    private $point;

    public function __construct(Model\User $user, Model\Point $point)
    {
        $this->user = $user;
        $this->point = $point;
    }

    public function execute()
    {
        $targetList = [ 'mayuko7s', 'kirukiru_21' ];
        $add = 10;

        foreach($targetList as $target) {
            // 集計対象のチャンネルでポイント対象となる有効な時間を取得
            // 最終発言日時からこの時間の間はポイント対象となる
            // なので、最終発言日時がM分前以降のユーザーを取得する
            $minutes = 20;
            $userList = $this->user->getRecentChatUserList($target, $minutes);

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
                $this->point->add($userInfo['user_id'], $userInfo['login'], $userInfo['disp_name'], $target, $add);
            }
        }

    }
}
