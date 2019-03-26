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

        \ini_set('pcre.jit', 'off');

        \Swoft::setAlias('@swoftcli', \dirname(__DIR__));
    }

    public function getCLoggerConfig(): array
    {
        $config = parent::getCLoggerConfig();
        // disable
        $config['enable'] = false;

        return $config;
    }
}
