<?php

namespace IPS\Model;

/**
 * チャンネル設定関連のクラス
 */
class Settings
{
    /**
     * 対象となるチャンネルの設定を返す
     *
     * @param string $channel
     * @return array
     */
    public function get($channel)
    {
        $query = "select "
               . "  channel, period, addition, command, name, unit, "
               . "  refresh_token, access_token, access_token_expire "
               . "from "
               . "  SETTINGS "
               . "where "
               . "  channel = :channel ";
        
        $params = [
            ':channel' => $channel,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
        
        $results = $db->fetch();
        if(empty($results)) {
            return null;
        }
        return $results[0];
    }
}

