<?php

namespace IPS\Batch;

use IPS\Model as Model;
use IPS\Model\Config as Config;
use IPS\Model\Log as Log;

/**
 * IPSに登録しているチャンネルの配信ステータスをチェックし
 * DBにステータスを書き込む処理
 */
class StreamStatusUpdater
{
    private $stream;
    private $twitch;

    public function __construct(Model\Stream $stream, Model\Twitch $twitch)
    {
        $this->stream = $stream;
        $this->twitch = $twitch;
    }

    public function execute()
    {
        $channels = $this->stream->getAllChannels();

        foreach($channels as $channel) {
            if($channel === 'ips_online') {
                continue;
            }

            $status = $this->twitch->getStreamStatus($channel);
            Log::debug("Stream status of {$channel}: {$status}");

            if($status === Model\Stream::STATUS_ONLINE) {
                $this->stream->activate($channel);
            } elseif($status === Model\Stream::STATUS_OFFLINE) {
                $this->stream->deactivate($channel);
            }

            sleep(1);
        }
    }
}
