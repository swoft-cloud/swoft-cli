<?php declare(strict_types=1);

// use Swoft\Http\Server\Swoole\RequestListener;
// use Swoft\Server\Swoole\SwooleEvent;
use Swoft\Cli\SwoftCLI;

return [
    'cliApp'     => [
        'name'        => config('name'),
        'version'     => SwoftCLI::VERSION,
        'description' => config('description'),
    ],
    'cliRouter'     => [
        'idAliases' => [
            // 'run' => 'serve:run'
            'ab'          => 'tool:ab',
            'update-self' => 'self-update:up',
            'updateself'  => 'self-update:up',
            'selfupdate'  => 'self-update:up',
            'self-update' => 'self-update:up',
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
