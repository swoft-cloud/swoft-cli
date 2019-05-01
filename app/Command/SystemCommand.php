<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Helper\Show;

/**
 * Provide some system information commands
 * - CPU
 * - MEMORY
 * - DISK
 * - IO
 * @Command(alias="sys")
 */
class SystemCommand
{
    /**
     * @CommandMapping()
     */
    public function info(): void
    {
        $info = [
            // "<bold>System environment info</bold>\n",
            'OS'             => \PHP_OS,
            'Php version'    => \PHP_VERSION,
            'Swoole version' => \SWOOLE_VERSION,
            'Swoft version'  => \Swoft::VERSION,
            // 'App Name'       => \APP_NAME,
            'Base Path'      => \BASE_PATH,
            // 'CPU number'      => \swoole_cpu_num(),
        ];

        Show::aList($info, 'System Environment');
    }

    public function top(): void
    {

    }

    public function uptime(): void
    {

    }

    /*****************************************************************************
     * CPU stat methods: vmstat, htop
     ****************************************************************************/

    public function vmstat(): void
    {

    }

    /**
     * @example do run htop
     */
    public function htop(): void
    {

    }

    public function nmon(): void
    {

    }

    public function mpstat(): void
    {

    }

    /*****************************************************************************
     * MEMORY stat methods: free
     ****************************************************************************/

    public function ps(): void
    {

    }

    public function free(): void
    {

    }

    /*****************************************************************************
     * IO stat methods: ss, lsof, iftop, iptraf, netstat, iostat, iotop, ioprofile
     ****************************************************************************/

    public function ss(): void
    {

    }

    public function lsof(): void
    {

    }

    public function iftop(): void
    {

    }

    public function iptraf(): void
    {

    }

    public function netstat(): void
    {

    }

    /**
     * @CommandMapping(alias="iotop")
     */
    public function ioTop(): void
    {

    }

    /**
     * @CommandMapping(alias="iostat")
     */
    public function ioStat(): void
    {

    }

    /**
     * @CommandMapping(alias="ioprofile")
     */
    public function ioProfile(): void
    {

    }

    /*****************************************************************************
     * DISK stat methods: df, fdisk
     ****************************************************************************/

    public function df(): void
    {

    }
}
