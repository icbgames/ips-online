<?php

namespace IPS\Controller\Api;

use Rakit\Validation\Validator as Validator;
use IPS\Model as Model;
use IPS\Model\Log as Log;

class Ignore extends RestBase
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

        // POST: add, DELETE: delete
        $method = $_SERVER['REQUEST_METHOD'];

        if($method === 'POST') {
            $body = $this->postBody();
            if(empty($body) || empty($body['channel']) || empty($body['user'])) {
                $this->status = 400;
                $this->response = ['message' => 'channel と user を指定してください'];
                return;
            }

            // simple channel owner check: channel must equal login
            if($body['channel'] !== $login) {
                $this->status = 403;
                $this->response = ['message' => 'チャンネルオーナーではありません'];
                return;
            }

            $channel = $body['channel'];
            $user = $body['user'];

            $this->point->addIgnoreUser($channel, $user);
            $this->response = ['result' => 'OK'];
            return;
        }

        if($method === 'DELETE') {
            // parse body for DELETE; postBody only handles POST, so parse raw JSON
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if(empty($decoded) || empty($decoded['channel']) || empty($decoded['user'])) {
                $this->status = 400;
                $this->response = ['message' => 'channel と user を指定してください'];
                return;
            }

            if($decoded['channel'] !== $login) {
                $this->status = 403;
                $this->response = ['message' => 'チャンネルオーナーではありません'];
                return;
            }

            $this->point->deleteIgnoreUser($decoded['channel'], $decoded['user']);
            $this->response = ['result' => 'OK'];
            return;
        }

        $this->status = 405;
        $this->response = ['message' => 'Method Not Allowed'];
    }
}
