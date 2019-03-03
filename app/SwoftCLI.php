<?php declare(strict_types=1);

namespace Swoft\Cli;

use Swoft\SwoftApplication;

/**
 * Class SwoftCLI
 */
class SwoftCLI extends SwoftApplication
{
    protected function init(): void
    {
        \Swoft::setAlias('@swoftcli', \dirname(__DIR__));
    }
}
