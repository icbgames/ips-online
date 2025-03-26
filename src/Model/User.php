<?php

namespace IPS\Model;

/**
 * ユーザー関連クラス
 */
class User
{
    private $twitch;

    /**
     * Constructor
     *
     * @param Twitch $twitch
     */
    public function __construct(Twitch $twitch)
    {
        $this->twitch = $twitch;
    }

    /**
     * ユーザーの最終発言日時を更新する
     *
     * @param string $login
     * @param string $target
     */
    public function updateTimestamp($login, $target)
    {
        $query = "insert "
               . "  into LATEST_CHAT_TIME "
               . "  (login, target, chatted_at) "
               . "  values "
               . "  ( :login, :target, :timestamp) "
               . "on conflict(login, target) "
               . "do update "
               . "  set chatted_at = excluded.chatted_at ";

        $timestamp = date('Y-m-d H:i:s');

        $params = [
            ':login' => $login,
            ':target' => $target,
            ':timestamp' => $timestamp
        ];

        $db = DB::instance();
        $db->execute($query, $params);
    }

    /**
     * 直近でコメント投稿したユーザーの一覧を取得する
     *
     * @param $target string 対象のチャンネル
     * @param $minutes int 直近何分遡るか
     */
    public function getRecentChatUserList($target, $minutes)
    {
        $query = "select "
               . "  login, target, chatted_at "
               . "from "
               . "  LATEST_CHAT_TIME "
               . "where "
               . "  target = :target AND "
               . "  chatted_at > :threshold "
               . "order by "
               . "  chatted_at asc";
        
        $threshold = date('Y-m-d H:i:s', strtotime("-{$minutes} minute"));
        $params = [
            ':target' => $target,
            ':threshold' => $threshold
        ];

        $db = DB::instance();
        $db->execute($query, $params);
        
        $results = $db->fetch();
        return $results;
    }

    /**
     * loginを指定してユーザー情報を取得する
     * DB内にキャッシュがある場合はキャッシュを参照して返す
     * キャッシュが無い、もしくは有効期限が切れている場合はTwitch APIから取得したうえでキャッシュする
     *
     * @param string $login
     */
    public function getUserInfo($login)
    {
        $query = "select "
               . "  user_id, login, disp_name "
               . "from "
               . "  USER_MASTER "
               . "where "
               . "  login = :login AND "
               . "  updated_at > :threshold ";

        $threshold = date('Y-m-d H:i:s', strtotime("-7 day"));
        $params = [
            ':login' => $login,
            ':threshold' => $threshold
        ];

        $db = DB::instance();
        $db->execute($query, $params);

        $results = $db->fetch();
        if(!empty($results)) {
            return $results[0];
        }

        // DBに有効なキャッシュが無い場合Twitch APIから取得
        $userInfo = $this->twitch->getUserInfo($login);
        $this->cacheUserInfo($userInfo->id, $userInfo->login, $userInfo->display_name);
        return [
            'user_id' => $userInfo->id,
            'login' => $userInfo->login,
            'disp_name' => $userInfo->display_name
        ];
    }

    /**
     * 対象のユーザー情報をキャッシュする
     *
     * @param int $userId
     * @param string $login
     * @param string $dispName
     */
    public function cacheUserInfo($userId, $login, $dispName)
    {
        $query = "insert "
               . "  into USER_MASTER "
               . "  (user_id, login, disp_name, updated_at) "
               . "  values "
               . "  ( :user_id, :login, :disp_name, :timestamp) "
               . "on conflict(user_id) "
               . "do update "
               . "  set "
               . "    login = excluded.login, "
               . "    disp_name = excluded.disp_name, "
               . "    updated_at = excluded.updated_at ";

        $timestamp = date('Y-m-d H:i:s');

        $params = [
            ':user_id' => $userId,
            ':login' => $login,
            ':disp_name' => $dispName,
            ':timestamp' => $timestamp
        ];

        $db = DB::instance();
        $db->execute($query, $params);
    }
}

