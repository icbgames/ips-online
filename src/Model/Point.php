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

    /**
     * 指定したチャンネルのポイントランキングを返す
     *
     * ポイント降順、login昇順でソートする
     *
     * @param string $channel ランキング取得対称のチャンネル
     * @return array
     */
    public function getPointRanking($channel)
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
               . "  ) ";
        
        $params = [
            ':channel' => $channel,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
        
        $results = $db->fetch();
        return $results;
    }

    /**
     * 指定したチャンネルにおける対象のユーザーのポイントを引数で与えた数値で更新する
     *
     * @param string $channel 対象のチャンネル
     * @param string $login 対象のユーザー
     * @param int $point 更新するポイント
     */
    public function updateUserPoint($channel, $login, $point)
    {
        $query = "update IPS_POINTS "
               . "set "
               . "  points = :points "
               . "where "
               . "  channel = :channel and "
               . "  login = :login ";
        
        $params = [
            ':points' => $point,
            ':channel' => $channel,
            ':login' => $login,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
    }

    /**
     * 指定したチャンネルにおける対象のユーザーのポイント情報を削除する
     *
     * @param string $channel 対象のチャンネル
     * @param string $login 対象のユーザー
     */
    public function deleteUserPoint($channel, $login)
    {
        $query = "delete from "
               . "  IPS_POINTS "
               . "where "
               . "  channel = :channel and "
               . "  login = :login ";
        
        $params = [
            ':channel' => $channel,
            ':login' => $login,
        ];

        $db = DB::instance();
        $db->execute($query, $params);
    }
}

