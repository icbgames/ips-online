<?php

namespace IPS\Model;

/**
 * IPSのポイント関連のクラス
 */
class Point
{
    /**
     * 対象となるチャンネルの無視ユーザーリストを取得し返す
     *
     * @param string $target
     * @return array
     */
    public function getIgnoreList($target)
    {
        $query = "select "
               . "  login "
               . "from "
               . "  IGNORE_USERS "
               . "where "
               . "  channel = :target "
               . "order by "
               . "  login asc";
        
        $params = [
            ':target' => $target,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
        
        $results = $db->fetch();
        return $results;
    }

    /**
     * 対象となるユーザーにポイントを付与する
     *
     * @param int $userId
     * @param string $login
     * @param string $dispName
     * @param string $target
     * @param int $add
     */
    public function add($userId, $login, $dispName, $target, $add)
    {
        $query = "insert into "
               . "  IPS_POINTS "
               . "  ( user_id, login, disp_name, channel, points ) "
               . "values "
               . "  ( :user_id, :login, :disp_name, :channel, :points ) "
               . "on conflict(user_id, channel) "
               . "do update "
               . "set "
               . "  login = excluded.login, "
               . "  disp_name = excluded.disp_name, "
               . "  points = points + excluded.points ";
        
        $params = [
            ':user_id' => $userId,
            ':login' => $login,
            ':disp_name' => $dispName,
            ':channel' => $target,
            ':points' => $add,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
    }

    /**
     *
     *
     * @param string $login
     * @param string $channel
     * @return array
     */
    public function getChannelPoint($login, $channel)
    {
        $query = "select "
               . "  user_id, login, disp_name, channel, points "
               . "from "
               . "  IPS_POINTS "
               . "where "
               . "  login = :login AND "
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

    /**
     *
     *
     * @param string $login
     * @param string $channel
     * @return array
     */
    public function getChannelPointAndRank($login, $channel)
    {
        $query = "select "
               . "  user_id, login, disp_name, channel, points, rank "
               . "from "
               . "  ( "
               . "    select "
               . "      user_id, login, disp_name, channel, points, "
               . "      RANK() OVER (order by points desc, login asc) as rank "
               . "    from "
               . "      IPS_POINTS "
               . "    where "
               . "      channel = :channel "
               . "  ) "
               . "where "
               . "  login = :login";
        
        $params = [
            ':channel' => $channel,
            ':login' => $login
        ];

        $db = DB::instance();
        $db->execute($query, $params);
        
        $results = $db->fetch();
        if(empty($results)) {
            return [
                'user_id'   => null,
                'login'     => $login,
                'disp_name' => $login,
                'channel'   => $channel,
                'points'    => 0,
                'rank'      => '-',
            ];
        }
        return $results[0];
    }
}

