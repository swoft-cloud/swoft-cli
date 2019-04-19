<?php declare(strict_types=1);

namespace Swoft\Cli;

use Swoft\SwoftApplication;

/**
 * Class SwoftCLI
 */
class SwoftCLI extends SwoftApplication
{
    protected function afterInit(): void
    {
        parent::afterInit();

        // fix: on php73 preg_* error
        \ini_set('pcre.jit', 'off');

        \Swoft::setAlias('@swoftcli', \dirname(__DIR__));
    }

    public function getCLoggerConfig(): array
    {
        $config = parent::getCLoggerConfig();
        // disable
        $config['enable'] = (int)\env('SWOFT_DEBUG', 0) > 0;

        return $config;
    }
}
