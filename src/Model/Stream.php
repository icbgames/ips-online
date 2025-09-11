<?php

namespace IPS\Model;

/**
 * 配信ステータス関連クラス
 */
class Stream
{
    const STATUS_OFFLINE = 0;
    const STATUS_ONLINE = 1;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * 指定したチャンネルの現在の配信ステータスを返す
     *
     * @param string $channel
     * @return int
     */
    public function getStatus($channel)
    {
        $query = "select "
               . "  channel, status "
               . "from "
               . "  STREAM_STATUS "
               . "where "
               . "  channel = :channel ";
        
        $params = [
            ':channel' => $channel,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
        
        $results = $db->fetch();
        if(!isset($results[0])) {
            return static::STATUS_OFFLINE;
        }

        if($results[0]['status'] == 1) {
            return static::STATUS_ONLINE;
        }
        return static::STATUS_OFFLINE;
    }
    
    /**
     * 指定したチャンネルの現在の配信ステータスを更新する
     *
     * @param string $channel
     * @param int $status
     */
    public function updateStatus($channel, $status)
    {
        $query = "insert "
               . "  into STREAM_STATUS "
               . "  ( channel, status ) "
               . "  values "
               . "  ( :channel, :status ) "
               . "on conflict( channel ) "
               . "do update "
               . "  set status = excluded.status ";

        $timestamp = date('Y-m-d H:i:s');

        $params = [
            ':channel' => $channel,
            ':status' => $status,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
    }

    /**
     * 指定したチャンネルの配信ステータスを配信中にする
     *
     * @param string $channel
     */
    public function activate($channel)
    {
        $this->updateStatus($channel, static::STATUS_ONLINE);
    }

    /**
     * 指定したチャンネルの配信ステータスを休止中にする
     * 
     * @param string $channel
     */
    public function deactivate($channel)
    {
        $this->updateStatus($channel, static::STATUS_OFFLINE);
    }
}
