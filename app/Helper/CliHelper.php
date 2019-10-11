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

    /**
     * @param  string $msg
     */
    public static function info(string $msg): void
    {
        Show::writeln(date('Y/m/d-H:i:s') . self::PREFIX . " <info>$msg</info>");
    }

    /**
     * @param  string $msg
     */
    public static function warn(string $msg): void
    {
        Show::writeln(date('Y/m/d-H:i:s') . self::PREFIX . " <warning>$msg</warning>");
    }

    /**
     * @param  string $msg
     */
    public static function error(string $msg): void
    {
        Show::writeln(date('Y/m/d-H:i:s') . self::PREFIX . " <error>$msg</error>");
    }
}
