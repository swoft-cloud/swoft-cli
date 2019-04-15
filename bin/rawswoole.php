<?php declare(strict_types=1);

$port = 9502;
$http = new Swoole\Http\Server('127.0.0.1', $port);

echo 'Listen on http://127.0.0.1:' . $port . PHP_EOL;

$http->set(['worker_num' => 1]);
$http->on('request', function ($request, $response) {
    $response->end('<h1>Hello Swoole. </h1>');
});

$http->start();
