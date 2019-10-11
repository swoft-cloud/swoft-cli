<?php declare(strict_types=1);

// use Swoft\Http\Server\Swoole\RequestListener;
// use Swoft\Server\Swoole\SwooleEvent;

return [
    'cliApp'     => [
        'name'        => 'Swoft-cli',
        'version'     => '0.1.2',
        'description' => 'CLI tool application for quick use swoft framework',
    ],
    'cliRouter'     => [
        'idAliases' => [
            // 'run' => 'serve:run'
        ],
        'disabledGroups' => ['http', 'asset'],
    ],
    'httpServer' => [
        /** @see \Swoft\Http\Server\HttpServer::$setting */
        'setting' => [
            'log_file' => alias('@runtime/swoftcli.log'),
        ],
    ],
    // 'wsServer'   => [
    //     'on'      => [
    //         // Enable http handle
    //         SwooleEvent::REQUEST => bean(RequestListener::class),
    //     ],
    //     'debug' => env('SWOFT_DEBUG', 1),
    //     'setting' => [
    //         'log_file' => alias('@runtime/swoole.log'),
    //     ],
    // ],
];
