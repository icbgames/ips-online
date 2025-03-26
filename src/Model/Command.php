<?php

namespace IPS\Model;

class Command
{
    public static function execute($channel, $login, $message)
    {
        $command = null;
        $parameter = null;

        if(preg_match('/^!([a-zA-Z0-9\_\-]+) ?(.*)$/', $message, $matches)) {
            if(isset($matches[1])) {
                $command = $matches[1];
            }
            if(isset($matches[2])) {
                $parameter = $matches[2];
            }
        } else {
            return;
        }

        $channelCommandList = static::getChannelCommandList($channel);
        $globalCommandList = static::getGlobalCommandList();

        if(!array_key_exists($command, $channelCommandList)) {
            if(!array_key_exists($command, $globalCommandList)) {
                // command does not exist.
                Log::debug("Command does not exist: {$command}");
                return;
            }

            // global command
            $parameterRequired = $globalCommand[$command];
            if($parameterRequired && is_null($parameter)) {
                // missing required parameter.
                Log::debug("Command[{$command}] requires a parameter, but missing.");
                return;
            }
        }
    }

    public static function getChannelCommandList($channel)
    {
        return [
            [],
        ];
    }

    public static function getGlobalCommandList()
    {
        return [
            'ips' => ['Global', 'pointNotify', null],
        ];
    }
}

