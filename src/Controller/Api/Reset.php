<?php

namespace IPS\Controller\Api;

use Rakit\Validation\Validator as Validator;
use IPS\Model as Model;
use IPS\Model\Log as Log;

class Reset extends RestBase
{
    private $validator;
    private $point;

    public function __construct(Validator $validator, Model\Point $point)
    {
        $this->validator = $validator;
        $this->point = $point;
    }

    public function action()
    {
        if(!$this->isLogin()) {
            return;
        }

        $login = $this->getLogin();
        $method = $_SERVER['REQUEST_METHOD'];

        if($method !== 'DELETE') {
            $this->status = 405;
            $this->response = ['message' => 'Method Not Allowed'];
            return;
        }

        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if(empty($decoded) || empty($decoded['channel'])) {
            $this->status = 400;
            $this->response = ['message' => 'channel を指定してください'];
            return;
        }

        $channel = $decoded['channel'];
        if($channel !== $login) {
            $this->status = 403;
            $this->response = ['message' => 'チャンネルオーナーではありません'];
            return;
        }

        $this->point->deleteAllPoints($channel);
        $this->response = ['result' => 'OK'];
    }
}

