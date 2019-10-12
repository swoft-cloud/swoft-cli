<?php declare(strict_types=1);

use Swoft\Cli\SwoftCLI;

return [
    'name'        => 'Swoft-cli',
    'debug'       => env('SWOFT_DEBUG', 0),
    'version'     => SwoftCLI::VERSION,
    'description' => '🛠️ Command line tool application for quick use swoft',
];
