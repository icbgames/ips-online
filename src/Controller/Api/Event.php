<?php

namespace IPS\Controller\Api;

use Rakit\Validation\Validator as Validator;
use IPS\Model as Model;
use IPS\Model\Config as Config;
use IPS\Model\Log as Log;

class Event extends PlainBase
{
    private $settings;
    private $point;

    /**
     * Construct
     *
     * @param Model\Settings $settings
     * @param Model\Point $point
     */
    public function __construct(Model\Settings $settings, Model\Point $point)
    {
        $this->settings = $settings;
        $this->point = $point;
    }

    public function action()
    {
        Log::debug('api/event');

        // signature検証
        $messageId = $this->headers('Twitch-Eventsub-Message-Id');
        $timestamp = $this->headers('Twitch-Eventsub-Message-Timestamp');
        $signature = $this->headers('Twitch-Eventsub-Message-Signature');
        $messageType = $this->headers('Twitch-Eventsub-Message-Type');

        $body = $this->postBody(true);
        Log::debug("request body for /api/evnet: " . var_export($body, true));

        $hmacMessage = $messageId . $timestamp . $body;
        Log::debug("HMAC: {$hmacMessage}");

        $secret = Config::get('eventsub_secret');
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $hmacMessage, $secret);
        Log::debug("Expected: {$expectedSignature}");

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
            Log::info('challenge verification');
            Log::debug("challenge: {$request['challenge']}");

            echo $request['challenge'];
            return;
        }

        // 通知処理
        if($messageType === 'notification') {
            $event = $request['event'];

            // レイドの場合
            if($request['subscription']['type'] === 'channel.raid') {
                $raiderId = $event['from_broadcaster_user_id'];
                $raiderLogin = $event['from_broadcaster_user_login'];
                $raiderName = $event['from_broadcaster_user_name'];
                $channel = $event['to_broadcaster_user_login'];

                $viewers = (int)$event['viewers'];

                $setting = $this->settings->get($channel);
                $add = (int)$setting['raid'] + (int)$setting['raid_bonus'] * $viewers;

                Log::info('>>> RAID INFO');
                Log::info(var_export($event, true));
                Log::debug(var_export($setting, true));
                Log::info("raid add point: {$add}");

                $this->point->add($raiderId, $raiderLogin, $raiderName, $channel, $add);
            } elseif($request['subscription']['type'] === 'channel.cheer') {
                Log::info('>>> BITS INFO');
                Log::info(var_export($event, true));
            } elseif($request['subscription']['type'] === 'channel.subscription.gift') {
                Log::info('>>> SUB GIFT INFO');
                Log::info(var_export($event, true));
            }
        }

    }
}
