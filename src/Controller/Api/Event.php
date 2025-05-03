<?php

namespace IPS\Controller\Api;

use Rakit\Validation\Validator as Validator;
use IPS\Model as Model;
use IPS\Model\Log as Log;

class Event extends RestBase
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
        // signature検証
        $messageId = $this->headers('Twitch-Eventsub-Message-Id');
        $timestamp = $this->headers('Twitch-Eventsub-Message-Timestamp');
        $signature = $this->headers('Twitch-Eventsub-Message-Signature');
        $messageType = $this->headers('Twitch-Eventsub-Message-Type');

        $body = $this->getPostBody();

        $hmacMessage = $messageId . $timestamp . $body;

        $secret = Config::get('eventsub_secret');
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $hmacMessage, $secret);

        $result = hash_equals($expectedSignature, $signature);
        if(!$result) {
            // invalid signature
            $this->status = 403;
            $this->response = ['result' => 'invalid signature'];
            return;
        }

        $request = json_decode($body, true);

        // challenge検証
        if($messageType === 'webhook_callback_verification') {
            echo $request['challenge'];
            return;
        }

        // 通知処理
        if($messageType === 'notification') {
            $event = $request['event'];

            // レイドの場合
            if($request['subscription']['type'] === 'channel.raid') {
                $raider = $event['from_broadcaster_user_login'];
                $viewers = (int)$event['viewers'];

                $channel = $event['to_broadcaster_user_login'];
                $setting = $this->settings->get($channel);
                $add = (int)$setting['raid'] + (int)$setting['raid_bonus'] * $viewers;

                Log::info('>>> RAID INFO');
                Log::info(var_export($event, true));
                Log::debug(var_export($setting, true));
                Log::info("raid add point: {$add}");
            }
        }

    }
}
