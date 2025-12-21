<?php

namespace IPS\Controller\Api;

use Rakit\Validation\Validator as Validator;
use IPS\Model as Model;
use IPS\Model\Log as Log;

class Channelpoint extends RestBase
{
    private $validator;
    private $channelpoints;
    private $twitch;

    public function __construct(Validator $validator, Model\Channelpoints $channelpoints, Model\Twitch $twitch)
    {
        $this->validator = $validator;
        $this->channelpoints = $channelpoints;
        $this->twitch = $twitch;
    }

    public function action()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        // DELETE: accept JSON body with { id: "..." } and perform remove
        if($method === 'DELETE') {
            if(!$this->isLogin()) {
                return;
            }
            $login = $this->getLogin();

            $body = file_get_contents('php://input');
            $param = json_decode($body, true);
            if(empty($param) || !is_array($param) || !isset($param['id'])) {
                $this->status = 400;
                $this->response = ['message' => 'invalid body'];
                return;
            }

            $id = $param['id'];

            // check ownership: only allow deleting settings that belong to the logged-in channel
            $exists = $this->channelpoints->get($id);
            if(empty($exists) || !isset($exists[0]['channel'])) {
                $this->status = 404;
                $this->response = ['message' => 'not found'];
                return;
            }
            $ownerChannel = $exists[0]['channel'];
            if($ownerChannel !== $login) {
                $this->status = 403;
                $this->response = ['message' => 'forbidden'];
                return;
            }

            $this->twitch->deleteEventSub($login, Model\Twitch::SUBTYPE_CHANNEL_POINT);
            $this->channelpoints->remove($id);
            $this->response = ['result' => 'OK'];
            return;
        }

        // POST: register new channelpoint settings (existing behavior)
        if(!$this->isLogin()) {
            return;
        }

        $channel = $this->getLogin();
        $param = $this->postBody();

        if(empty($param) || !is_array($param)) {
            $this->status = 400;
            $this->response = ['message' => 'invalid body'];
            return;
        }

        // top-level validation
        $validation = $this->validator->make($param, [
            'id' => 'required',
            'data' => 'required|array',
        ]);

        $validation->validate();
        if($validation->fails()) {
            $errors = $validation->errors();
            $message = implode('<br>', $errors->firstOfAll());
            $this->status = 400;
            $this->response = ['message' => $message];
            return;
        }

        // 登録済みトリガーが既に上限個数以上ある場合はエラー
        $registeredTmp = $this->channelpoints->getList($channel);
        $registeredIds = array_unique(array_column($registeredTmp, 'id'));
        if(count($registeredIds) >= ChannelPoints::REGISTER_CAPACITY) {
            $this->status = 400;
            $this->response = ['message' => '登録できるトリガーは1チャンネルにつき' . ChannelPoints::REGISTER_CAPACITY . 'つまでです'];
            return;
        }

        // 登録処理
        $id = $param['id'];
        $data = $param['data'];

        // validate each data item
        $itemErrors = [];
        foreach($data as $idx => $item) {
            if(!is_array($item)) {
                $itemErrors[] = "data[{$idx}] is invalid";
                continue;
            }

            $v = $this->validator->make($item, [
                'message' => 'required|max:30',
                'permillage' => 'required|integer|min:0|max:1000',
                'point' => 'required|integer|min:0',
            ]);
            $v->validate();
            if($v->fails()) {
                $errs = $v->errors();
                $itemErrors[] = "data[{$idx}]: " . implode(', ', $errs->firstOfAll());
            }
        }

        if(!empty($itemErrors)) {
            $this->status = 400;
            $this->response = ['message' => implode('<br>', $itemErrors)];
            return;
        }

        // remove existing and register new
        $this->twitch->subscribeEventSub($channel, Model\Twitch::SUBTYPE_CHANNEL_POINT);
        $this->channelpoints->remove($id);
        $this->channelpoints->register($id, $channel, $data);

        $this->response = ['result' => 'OK'];
    }
}
