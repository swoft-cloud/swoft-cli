<?php declare(strict_types=1);

namespace Swoft\Cli\Helper;

use Swoft\Console\Helper\Show;
use function date;

/**
 * Class CliHelper
 * @since 2.0
 */
class CliHelper
{
    public const PREFIX = ' <cyan>[SWOFTCLI]</cyan>';

    public static function info(string $msg): void
    {
        Show::writeln(date('Y/m/d-H:i:s') . self::PREFIX . " <info>$msg</info>");
    }

    public static function warn(string $msg): void
    {
        Show::writeln(date('Y/m/d-H:i:s') . self::PREFIX . " <warning>$msg</warning>");
    }

    public static function error(string $msg): void
    {
        Show::writeln(date('Y/m/d-H:i:s') . self::PREFIX . " <error>$msg</error>");
    }
}
