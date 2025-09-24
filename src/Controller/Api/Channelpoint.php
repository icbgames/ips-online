<?php

namespace IPS\Controller\Api;

use Rakit\Validation\Validator as Validator;
use IPS\Model as Model;
use IPS\Model\Log as Log;

class Channelpoint extends RestBase
{
    private $validator;
    private $channelpoints;

    public function __construct(Validator $validator, Model\Channelpoints $channelpoints)
    {
        $this->validator = $validator;
        $this->channelpoints = $channelpoints;
    }

    public function action()
    {
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
        $this->channelpoints->remove($id);
        $this->channelpoints->register($id, $channel, $data);

        $this->response = ['result' => 'OK'];
    }
}
