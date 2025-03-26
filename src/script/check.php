<?php

use Psr\Container\ContainerInterface;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../dependencies.php';

date_default_timezone_set('Asia/Tokyo');

$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../dependencies.php');
$container = $containerBuilder->build();

if(!isset($argv[2])) {
    exit;
}

$user = $argv[1];
$channel = $argv[2];

$checker = $container->get('ipsBatchPointChecker');
$checker->execute($user, $channel);

