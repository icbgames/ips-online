<?php

namespace IPS\Controller\Api;

use Rakit\Validation\Validator as Validator;
use IPS\Model as Model;
use IPS\Model\Log as Log;

class Point extends RestBase
{
    private $validator;
    private $point;

    /**
     * Construct
     *
     * @param Validator $validator
     * @param Model\Point $point
     */
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
        $param = $this->postBody();

        $this->validator->addValidator('channel_owner', new class extends \Rakit\Validator\Rule{
            protected $message = 'チャンネルオーナーではありません';
            protected $fillableParams = ['expected'];

            public function check($value): bool
            {
                $this->requireParameters(['expected']);
                $expected = $this->parameter('expected');
                return $value === $ecpected;
            }
        });

        $validation = $this->validator->make($param, [
            'channel' => "required|channel_owner:{$login}",
            'user'    => 'required',
            'point'   => 'required|integer|min:0',
        ], [
            'point:integer'         => 'ポイントは0以上の整数を指定してください',
            'point:min'             => 'ポイントは0以上の整数を指定してください',
        ]);

        $validation->validate();
        if($validation->fails()) {
            $errors = $validation->errors();
            $errorMessages = $errors->firstOfAll();
            $message = implode('<br>', $errorMessages);

            $this->status = 400;
            $this->response = ['message' => $message];

            return;
        }

        $channel = $param['channel'];
        $user = $param['user'];
        $point = (int)$param['point'];

        if($point === 0) {
            $this->point->deleteUserPoint($channel, $user);
        } else {
            $this->point->updateUserPoint($channel, $user, $point);
        }

        $this->response = ['result' => 'OK'];
    }
}
