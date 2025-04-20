<?php

use Psr\Container\ContainerInterface;

date_default_timezone_set('Asia/Tokyo');

if(is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once '/home/ips-online/vendor/autoload.php';
}

if(is_file(__DIR__ . '/../src/dependencies.php')) {
    require_once __DIR__ . '/../src/dependencies.php';
} else {
    require_once '/home/ips-online/src/dependencies.php';
}

$requestUri = $_SERVER['REQUEST_URI'];
$parts = explode('?', $requestUri);
$requestUri = trim(trim($parts[0]), '/');

$uriParts = explode('/', $requestUri);
$controller = '';
foreach($uriParts as $part) {
    if(empty($part)) {
        continue;
    }
    $controller .= '/' . strtolower($part);
}
$controller = trim($controller, '/');
if(empty($controller)) {
    $controller = 'top';
}

if(substr($controller, 0, 4) !== 'api/') {
    $controller = "www/{$controller}";
}

$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../src/dependencies.php');
$container = $containerBuilder->build();

try {
    $class = $container->get($controller);
} catch(Exception $e) {
    // 404 or top redirect
    $class = $container->get('www/error');
}

try {
    $class->action();
    $class->render();
} catch(Exception $e) {
    echo $e->getMessage();
}
