<?php

namespace Swoft\Cli\Console\Handler;

use Swoft\Console\Annotation\Mapping\CommandHandler;

/**
 * Class ServeHandler
 * @CommandHandler()
 */
class ServeHandler
{
    public function execute(): void
    {
        \printf(__METHOD__);
    }
}
