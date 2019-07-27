<?php declare(strict_types=1);

namespace Swoft\Cli\Helper;

use Swoft\Console\Helper\Show;

/**
 * Class CliHelper
 * @since 2.0
 */
class CliHelper
{
    public static function info(string $msg): void
    {
        Show::writeln("[<cyan>SWOFTCLI</cyan>] <info>$msg</info>");
    }

    public static function warn(string $msg): void
    {
        Show::writeln("[<cyan>SWOFTCLI</cyan>] <warning>$msg</warning>");
    }

    public static function error(string $msg): void
    {
        Show::writeln("[<cyan>SWOFTCLI</cyan>] <error>$msg</error>");
    }
}
