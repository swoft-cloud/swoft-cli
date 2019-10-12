<?php declare(strict_types=1);

namespace Swoft\Cli;

use Swoft;
use Swoft\Stdlib\Helper\Sys;
use Swoft\SwoftApplication;
use function dirname;
use function ini_set;
use const PHP_VERSION_ID;

/**
 * Class SwoftCLI
 */
class SwoftCLI extends SwoftApplication
{
    public const VERSION = '0.1.3';

    protected function afterInit(): void
    {
        parent::afterInit();

        $this->setRuntimePath(Sys::getTempDir());

        // fix: on php73 preg_* error
        if (PHP_VERSION_ID > 70300) {
            ini_set('pcre.jit', 'off');
        }

        Swoft::setAlias('@swoftcli', dirname(__DIR__));
    }

    public function getCLoggerConfig(): array
    {
        $config = parent::getCLoggerConfig();
        // disable print console log
        $config['enable'] = false;

        return $config;
    }
}
