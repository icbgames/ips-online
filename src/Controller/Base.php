<?php

namespace IPS\Controller;

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

    public function action()
    {
    }

    public function render()
    {
        echo '<!DOCTYPE><html><head><title>IPS Online</title></head><body>';
        echo '<h1>Welcom to IPS Online.</h1><hr>';
        echo '<pre>';
        var_dump($this->assignVars);
        echo '</pre><hr></body></html>';
    }
}
