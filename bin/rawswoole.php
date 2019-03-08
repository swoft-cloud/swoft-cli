<?php

$http = new Swoole\Http\Server('127.0.0.1', 9501);
$http->on('request', function ($request, $response) {
    $response->end('<h1>Hello Swoole. </h1>');
});

$http->start();