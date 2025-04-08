<?php

namespace IPS\Model;

class Log
{
    const LOG_FILE_PATH = __DIR__ . '/../../logs/ips.log';

    public static function debug($message)
    {
        if(Config::get('ips', 'log', 'level') < 3) {
            return;
        }
        static::writeLog('DEBUG', $message);
    }

    public static function info($message)
    {
        if(Config::get('ips', 'log', 'level') < 2) {
            return;
        }
        static::writeLog('INFO', $message);
    }

    public static function warn($message)
    {
        if(Config::get('ips', 'log', 'level') < 1) {
            return;
        }
        static::writeLog('WARN', $message);
    }

    public static function error($message)
    {
        static::writeLog('ERROR', $message);
    }

    protected static function writeLog($label, $message)
    {
        $datetime = date('Y/m/d H:i:s');
        $message = preg_replace(['/\r/', '/\n/'], ['{_CR_}', '{_LF_}'], $message);

        $pid = getmypid();

        $trace = debug_backtrace(2);
        if(substr($trace[0]['file'], -7) === 'Log.php') {
            $index = 1;
        } else {
            $index = 0;
        }
        preg_match('/^.+\/([^\/]+\/[^\/]+)$/', $trace[$index]['file'], $matches);
        $file = $matches[1];
        $line = $trace[$index]['line'];

        $format = "{$datetime} [{$label}] [{$pid}] [{$file}({$line})] {$message}\n";
        error_log($format, 3, static::LOG_FILE_PATH);
    }
}
