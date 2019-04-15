<?php declare(strict_types=1);

class SimpleServer {
    public function __construct()
    {
        $port = 9501;
        $http = new Swoole\Http\Server('127.0.0.1', $port);

        echo 'Listen on http://127.0.0.1:' . $port . PHP_EOL;

        $http->on('request', [$this, 'onRequest']);
        $http->start();
    }

    public function onRequest($request, $response)
    {
        \var_dump(__METHOD__ . __LINE__);

        \var_dump(\preg_match('/\w+/', '/test/index'));
        $response->end('<h1>Hello Swoole. </h1>');

    }
}

$srv = new SimpleServer();
