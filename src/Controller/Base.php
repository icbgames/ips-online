<?php

namespace IPS\Controller;

use IPS\Model\Config as Config;

/**
 * Web系コントローラー基底クラス
 *
 * 各コントローラーはこのクラスを継承すること
 */
abstract class Base
{
    protected $status = 200;
    protected $assignVars = [];
    protected $template = null;


    /**
     * 指定されたリクエストパラメータを返す
     *
     * @param string $key
     * @return string
     */
    protected function param($key)
    {
        if(isset($_POST[$key])) {
            return $_POST[$key];
        }
        if(isset($_GET[$key])) {
            return $_GET[$key];
        }
        return null;
    }

    /**
     * 画面テンプレートへ埋め込む変数の値をセットする
     *
     * 引数が2つの場合は1次元目に第2引数の値を
     * 3つの場合は2次元目に第3引数の値を格納する
     *
     * @param string $key1 1次元目のキー
     * @param string $key2 2次元目のキー、または格納する値
     * @param string $var 格納する値
     */
    protected function assign($key1, $key2, $var = null)
    {
        if(is_null($var)) {
            $this->assignVars[$key1] = $key2;
        } else {
            $this->assignVars[$key1][$key2] = $var;
        }
    }

    protected function assignErrors($error)
    {
        if(!isset($this->assignVars['errors'])) {
            $this->assignVars['errors'] = [];
        }
        $this->assignVars['errors'][] = $error;
    }

    /**
     * Set-Cookieヘッダを発行する
     *
     * @param string $name
     * @param string $value
     * @param int $expire CookieのExpireに設定するUNIXタイムスタンプ
     */
    protected function cookie($name, $value, $expire)
    {
        setcookie($name, $value, $expire, '/', '', false, false);
    }

    public function action()
    {
    }

    public function render()
    {
        $oauthUrl = Config::get('twitch', 'oauth', 'url');
        $this->assignVars['oauthUrl'] = $oauthUrl;

        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../templates');
        $twig = new \Twig\Environment($loader);
        echo $twig->render($this->template, $this->assignVars);
    }
}
