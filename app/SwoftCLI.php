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
    
    public const CLI_LOGO = '
   _____               ______     ________    ____
  / ___/      ______  / __/ /_   / ____/ /   /  _/
  \__ \ | /| / / __ \/ /_/ __/  / /   / /    / /
 ___/ / |/ |/ / /_/ / __/ /_   / /___/ /____/ /
/____/|__/|__/\____/_/  \__/   \____/_____/___/
';

    public const CLI_LOGO_SMALL = '
   ____            _____    _______   ____
  / __/    _____  / _/ /_  / ___/ /  /  _/
 _\ \| |/|/ / _ \/ _/ __/ / /__/ /___/ /
/___/|__,__/\___/_/ \__/  \___/____/___/
';

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
