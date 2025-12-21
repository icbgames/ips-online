<?php

namespace IPS\Model;

/**
 * チャンネルポイント設定関連のクラス
 */
class Channelpoints
{
    // 登録可能な個数上限
    const REGISTER_CAPACITY = 5;

    /**
     * 対象となるチャンネルポイントIDの情報を返す
     *
     * @param string $id
     * @return array
     */
    public function get($id)
    {
        $query = "select "
               . "  id, channel, message, permillage, point "
               . "from "
               . "  CHANNEL_POINT_SETTINGS "
               . "where "
               . "  id = :id "
               . "order by "
               . "  message ";
        
        $params = [
            ':id' => $id,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
        
        $results = $db->fetch();
        return $results;
    }

    /**
     * 対象となるチャンネルのチャンネルポイント情報の一覧を返す
     *
     * @param string $channel
     * @return array
     */
    public function getList($channel)
    {
        $query = "select "
               . "  id, channel, message, permillage, point "
               . "from "
               . "  CHANNEL_POINT_SETTINGS "
               . "where "
               . "  channel = :channel "
               . "order by "
               . "  id, message ";
        
        $params = [
            ':channel' => $channel,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
        
        $results = $db->fetch();
        return $results;
    }

    /**
     * 指定したIDのチャンネルポイント情報を削除する
     *
     * @param string $id
     */
    public function remove($id)
    {
        $query = "delete from "
               . "  CHANNEL_POINT_SETTINGS "
               . "where "
               . "  id = :id ";
        $params = [
            ':id' => $id,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
    }

    /**
     * チャンネルポイント情報を登録する
     *
     * @param string $id
     * @param string $channel
     * @param array $data
     */
    public function register($id, $channel, $data)
    {
        $query = "insert into "
               . "  CHANNEL_POINT_SETTINGS "
               . "  ( id, channel, message, permillage, point ) "
               . "values "
               . "  ( :id, :channel, :message, :permillage, :point ) ";

        $db = DB::instance();

        foreach($data as $d) {
            if(!isset($d['message']) || !isset($d['permillage']) || !isset($d['point'])) {
                continue;
            }
            $params = [
                ':id' => $id,
                ':channel' => $channel,
                ':message' => $d['message'],
                ':permillage' => $d['permillage'],
                ':point' => $d['point'],
            ];
            $db->execute($query, $params);
        }
    }
}

