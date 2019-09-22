<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use Swoft;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Input\Input;
use Swoft\Console\Helper\Show;
use function swoole_cpu_num;
use const BASE_PATH;
use const PHP_OS;
use const PHP_VERSION;
use const SWOOLE_VERSION;

/**
 * Provide some system information commands[<mga>WIP</mga>]
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
     * @param Input  $in
     */
    public function info(Input $in): void
    {
        $info = [
            // "<bold>System environment info</bold>\n",
            'OS'             => PHP_OS,
            'CPU number'     => swoole_cpu_num(),
            'Php version'    => PHP_VERSION,
            'Swoole version' => SWOOLE_VERSION,
            'Swoft version'  => Swoft::VERSION,
            // 'App Name'       => \APP_NAME,
            'Base Dir'       => BASE_PATH,
            'Work Dir'       => $in->getWorkDir(),
        ];

        Show::aList($info, 'System Environment');
    }

    public function top(): void
    {
        Show::info('WIP');
    }

    public function uptime(): void
    {
        Show::info('WIP');
    }

    /*****************************************************************************
     * CPU stat methods: vmstat, htop
     ****************************************************************************/

    public function vmstat(): void
    {
        Show::info('WIP');
    }

    /**
     * @example do run htop
     */
    public function htop(): void
    {
        Show::info('WIP');
    }

    public function nmon(): void
    {
        Show::info('WIP');
    }

    public function mpstat(): void
    {
        Show::info('WIP');
    }

    /*****************************************************************************
     * MEMORY stat methods: free
     ****************************************************************************/

    public function ps(): void
    {
        Show::info('WIP');
    }

    public function free(): void
    {
        Show::info('WIP');
    }

    /*****************************************************************************
     * IO stat methods: ss, lsof, iftop, iptraf, netstat, iostat, iotop, ioprofile
     ****************************************************************************/

    public function ss(): void
    {

    }

    public function lsof(): void
    {
        Show::info('WIP');
    }

    public function iftop(): void
    {
        Show::info('WIP');
    }

    public function iptraf(): void
    {
        Show::info('WIP');
    }

    public function netstat(): void
    {
        Show::info('WIP');
    }

    /**
     * @CommandMapping(alias="iotop")
     */
    public function ioTop(): void
    {
        Show::info('WIP');
    }

    /**
     * @CommandMapping(alias="iostat")
     */
    public function ioStat(): void
    {
        Show::info('WIP');
    }

    /**
     * @CommandMapping(alias="ioprofile,iopro")
     */
    public function ioProfile(): void
    {
        Show::info('WIP');
    }

    /*****************************************************************************
     * DISK stat methods: df, fdisk
     ****************************************************************************/

    public function df(): void
    {
        Show::info('WIP');
    }
}
