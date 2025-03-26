<?php

use Psr\Container\ContainerInterface;
use IPS\Model\Log as Log;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../dependencies.php';

date_default_timezone_set('Asia/Tokyo');

$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../dependencies.php');
$container = $containerBuilder->build();

Log::debug("command start");
if(!isset($argv[3])) {
    exit;
}
Log::debug("parameter ok");

$login = $argv[1];
$channel = $argv[2];
$arg = $argv[3];

$parts = explode(' ', $arg);
$command = $parts[0];
if(isset($parts[1])) {
    $param = $parts[1];
} else {
    $param = null;
}

Log::debug("{$login}, {$channel}, {$command}, {$param}");

$executor = $container->get('ipsBatchCommandExecutor');
$executor->execute($login, $channel, $command, $param);

