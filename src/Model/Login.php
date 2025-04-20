<?php
 
namespace IPS\Model;


class Login
{
    /**
     * ログイン状態を判定する
     *
     * 認証Cookieに積んであるJSON文字列を元にログイン状態とみなせるかどうかをチェック
     *
     * @param string $json 検証対称のJSON文字列
     * @return bool
     */
    public static function verify($json)
    {
        $decoded = json_decode($json, true);
        if(empty($decoded)) {
            return false;
        }

        $signature = $decoded['s'];
        $access = $decoded['a'];
        $refresh = $decoded['r'];
        $login = $decoded['l'];
        $expire = $decoded['e'];

        $origin = "{$login}--{$refresh}--{$access}--{$expire}";
        $result = password_verify($origin, $signature);
        return $result;
    }

    /**
     * loginを返す
     * ログイン状態の有効性はチェックしないことに注意
     * 必要に応じてverifyメソッドを利用すること
     *
     * @param string $json
     * @return string
     */
    public static function get($json)
    {
        $decoded = json_decode($json, true);
        if(empty($decoded)) {
            return false;
        }

        $login = $decoded['l'];
        return $login;
    }
}
