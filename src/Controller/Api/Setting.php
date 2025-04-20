<?php

namespace IPS\Controller\Api;

use Rakit\Validation\Validator as Validator;
use IPS\Model as Model;
use IPS\Model\Log as Log;

class Setting extends RestBase
{
    private $validator;
    private $settings;

    /**
     * Construct
     *
     * @param Validator $validator
     * @param Model\Settings $settings
     */
    public function __construct(Validator $validator, Model\Settings $settings)
    {
        $this->validator = $validator;
        $this->settings = $settings;
    }

    public function action()
    {
        if(!$this->isLogin()) {
            return;
        }

        $login = $this->getLogin();
        $param = $this->postBody();

        $validation = $this->validator->make($param, [
            'name'        => 'required|max:10',
            'unit'        => 'required|max:10',
            'command'     => 'required|alpha|max:10',
            'period'      => 'required|integer|min:1|max:30',
            'addition'    => 'required|integer|min:1|max:100',
            'addition_t1' => 'required|integer|min:0|max:1000',
            'addition_t2' => 'required|integer|min:0|max:1000',
            'addition_t3' => 'required|integer|min:0|max:1000',
        ], [
            'name:max'         => 'ポイント名称は10文字以内で設定してください',
            'unit:max'         => 'ポイントの単位は10文字以内で設定してください',
            'command:alpha'    => 'コマンドは半角アルファベット10文字以内で設定してください',
            'command:max'      => 'コマンドは半角アルファベット10文字以内で設定してください',
            'period:integer'   => 'ポイント加算期間は1～30の半角数字で設定してください',
            'period:min'       => 'ポイント加算期間は1～30の半角数字で設定してください',
            'period:max'       => 'ポイント加算期間は1～30の半角数字で設定してください',
            'addition:integer' => 'ポイント加算量は1～100の半角数字で設定してください',
            'addition:min'     => 'ポイント加算量は1～100の半角数字で設定してください',
            'addition:max'     => 'ポイント加算量は1～100の半角数字で設定してください',
            'addition_t1:integer' => 'Tier1ボーナスは0～1000の半角数字で設定してください',
            'addition_t1:min'     => 'Tier1ボーナスは0～1000の半角数字で設定してください',
            'addition_t1:max'     => 'Tier1ボーナスは0～1000の半角数字で設定してください',
            'addition_t2:integer' => 'Tier2ボーナスは0～1000の半角数字で設定してください',
            'addition_t2:min'     => 'Tier2ボーナスは0～1000の半角数字で設定してください',
            'addition_t2:max'     => 'Tier2ボーナスは0～1000の半角数字で設定してください',
            'addition_t3:integer' => 'Tier3ボーナスは0～1000の半角数字で設定してください',
            'addition_t3:min'     => 'Tier3ボーナスは0～1000の半角数字で設定してください',
            'addition_t3:max'     => 'Tier3ボーナスは0～1000の半角数字で設定してください',
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

        $name = $param['name'];
        $unit = $param['unit'];
        $command = $param['command'];
        $period = $param['period'];
        $addition = $param['addition'];
        $additionT1 = $param['addition_t1'];
        $additionT2 = $param['addition_t2'];
        $additionT3 = $param['addition_t3'];

        $params = [
            'name' => $name,
            'unit' => $unit,
            'command' => $command,
            'period' => $period,
            'addition' => $addition,
            'addition_t1' => $additionT1,
            'addition_t2' => $additionT2,
            'addition_t3' => $additionT3,
        ];
        $this->settings->update($login, $params);
        $this->response = [$name, $unit, $command, $period, $addition, $additionT1, $additionT2, $additionT3];
        Log::debug(var_export($this->response,true));
    }
}
