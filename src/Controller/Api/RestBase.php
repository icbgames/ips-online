<?php

namespace IPS\Controller\Api;

use IPS\Model\Login as Login;

/**
 * Rest API系コントローラー基底クラス
 *
 * 各コントローラーはこのクラスを継承すること
 */
abstract class RestBase
{
    protected $status = 200;
    protected $response;
    protected $headers = null;

    /**
     * 指定されたリクエストパラメータを返す (GET)
     *
     * @param string $key
     * @return string
     */
    protected function get($key)
    {
        if(isset($_GET[$key])) {
            return $_GET[$key];
        }
        return null;
    }

    /**
     * 指定されたリクエストパラメータを返す (POST)
     *
     * @param string $key
     * @return string
     */
    protected function post($key)
    {
        if(isset($_POST[$key])) {
            return $_POST[$key];
        }
        return null;
    }

    /**
     * POSTメソッドで送信されてきたリクエストのリクエストボディを取得して返す
     *
     * @param bool $raw trueを指定するとリクエストボディをそのまま返す。falseの場合JSONと判断し連想配列に変換して返す
     * @return array
     */
    protected function postBody($raw = false)
    {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $body = file_get_contents('php://input');
        if($raw) {
            return $body;
        }

        $decoded = json_decode($body, true);
        if(empty($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * 指定されたリクエストヘッダの値を返す。未指定の場合はヘッダすべてを返す。
     *
     * @param string $key
     * @return array|string
     */
    protected function headers($key = null)
    {
        if(is_null($this->headers)) {
            $this->headers = getallheaders();
        }

        if(is_null($key)) {
            return $this->headers;
        }

        if(isset($this->headers[$key])) {
            return $this->headers[$key];
        }

        return null;
    }

    /**
     * 有効なログイン状態かどうかをチェックして結果を返す
     *
     * @return bool
     */
    protected function isLogin()
    {
        if(!isset($_COOKIE['IPS'])) {
            $this->status = 400;
            $this->response = ['message' => 'invalid cookie'];
            return;
        }
        $cookie = $_COOKIE['IPS'];
        $result = Login::verify($cookie);
        if(!$result) {
            $this->status = 400;
            $this->response = ['message' => 'invalid cookie'];
        }

        return $result;
    }

    /**
     * Cookieからloginを抽出して返す
     * isLoginを先に呼び出し、ログイン状態が有効であることを確認してから使うこと
     *
     * @return string
     */
    protected function getLogin()
    {
        $cookie = $_COOKIE['IPS'];
        $login = Login::get($cookie);
        return $login;
    }

    public function action()
    {
    }

    public function render()
    {
        http_response_code($this->status);
        header('Content-Type: application/json');

        $json = json_encode($this->response);
        echo $json;
    }
}
