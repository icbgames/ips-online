<?php

namespace IPS\Batch;

use IPS\Model as Model;
use IPS\Model\Log as Log;

/**
 * コマンドを実行する
 * 
 */
class CommandExecutor
{
    private $settings;

    public function __construct(Model\Settings $settings)
    {
        $this->settings = $settings;
    }

    public function execute($login, $channel, $command, $param = null)
    {
        Log::debug("CommandExecutor::execute >>> {$login}, {$channel}, {$command}, {$param}");
        $settings = $this->settings->get($channel);
        Log::debug(var_export($settings,true));
        if(!isset($settings['command'])) {
            $settings['command'] = 'ips';
        }
        if("!{$settings['command']}" === $command) {
            Log::debug("Exec check.php");
            $script = __DIR__ . '/../script/check.php';
            exec("php {$script} {$login} {$channel}");
            return;
        }
    }
}
