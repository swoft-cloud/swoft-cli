<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use Swoft\Console\Helper\Show;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandArgument;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;

/**
 * some internal tool commands, like ab, update-self
 *
 * @Command(coroutine=false)
 */
class ToolCommand
{
    /**
     * like ab test tool, but can use for test http, websocket, tcp server
     *
     * @CommandMapping()
     */
    public function ab(): void
    {
        Show::info('WIP');
    }
}
