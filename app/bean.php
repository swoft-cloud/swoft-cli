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
            'update-self' => 'self-update:down',
            'updateself'  => 'self-update:down',
            'selfupdate'  => 'self-update:down',
            'self-update' => 'self-update:down',
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
