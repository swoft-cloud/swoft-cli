<?php declare(strict_types=1);

use Swoft\Http\Server\Swoole\RequestListener;
use Swoft\Server\Swoole\SwooleEvent;

return [
    'cliApp'     => [

    ],
    'httpServer' => [
        /** @see \Swoft\Http\Server\HttpServer::$setting */
        'setting' => [
            'log_file' => alias('@runtime/swoole.log'),
        ],
    ],
    'wsServer'   => [
        'on'      => [
            // Enable http handle
            SwooleEvent::REQUEST => bean(RequestListener::class),
        ],
        /** @see \Swoft\WebSocket\Server\WebSocketServer::$setting */
        'setting' => [
            'log_file' => alias('@runtime/swoole.log'),
        ],
    ],
];
