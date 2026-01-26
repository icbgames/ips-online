<?php

namespace IPS\Batch;

use IPS\Model as Model;
use IPS\Model\Config as Config;
use IPS\Model\Log as Log;

/**
 * ログのアラート監視
 * 
 */
class ProcMonitor
{
    protected $discord;

    public function __construct(Model\Discord $discord)
    {
        $this->discord = $discord;
    }

    /**
     * プロセス監視の実行
     *
     * 一定時間おきに起動し、プロセス
     */
    public function execute()
    {
        // 監視対象のチャンネル
        $channels = Config::get('ips', 'channels');

        // プロセス再起動コマンド
        $startCommand = Config::get('command', 'chat_monitor');

        $message = "----------\n";

        // プロセスの確認
        foreach($channels as $channel) {

            $command = "ps ax | grep -c \"script/chat.php {$channel}\"";
            $result = shell_exec($command);
            $result = trim($result);

            $channel = str_replace('_', '\\_', $channel);

            if($result < 2) {
                // プロセスが落ちている
                $message .= "プロセスが停止しています: {$channel}\n";
                shell_exec("nohup {$startCommand} {$channel} &");
            } elseif($result == 3) {
                // プロセスが正常に起動している
                $message .= "{$channel}: status OK\n";
            } else {
                // プロセスの多重起動等の不正
                $message .= "プロセスに異常があります: {$channel}\n";
            }
        }

        $message .= "----------\n";
        $this->discord->post($message);

    }
}
