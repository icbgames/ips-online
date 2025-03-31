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

    protected function action()
    {
    }

    protected function render()
    {
    }
}
