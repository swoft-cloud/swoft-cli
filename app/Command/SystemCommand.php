<?php

namespace Swoft\Cli\Command;

/**
 * Class SystemCommand
 * - CPU vmstat, sar top, htop, nmon, mpstat
 * - MEMORY free ps -aux
 * - IO iostat, ss, netstat, iptraf, iftop, lsof
 * @package Swoft\Cli\Command
 */
class SystemCommand
{
    /*****************************************************************************
     * helper methods for: CPU
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
     * helper methods for: MEMORY
     ****************************************************************************/

    public function ps(): void
    {

    }

    public function free(): void
    {

    }

    /*****************************************************************************
     * helper methods for: IO
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

    public function iostat(): void
    {

    }

    public function netstat(): void
    {

    }
}
