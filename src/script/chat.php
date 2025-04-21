<?php

use Psr\Container\ContainerInterface;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../dependencies.php';

date_default_timezone_set('Asia/Tokyo');

$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../dependencies.php');
$container = $containerBuilder->build();

$chat = $container->get('ipsBatchChatMonitor');

if(isset($argv[1])) {
    $target = $argv[1];
} else {
    $target = 'kirukiru_21';
}

$chat->setChannel($target);
$chat->execute();

