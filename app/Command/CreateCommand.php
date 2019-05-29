<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use Swoft;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Helper\Show;
use Swoft\Console\Input\Input;

/**
 * Class CreateCommand
 *
 * @package Swoft\Cli\Command
 * @Command(desc="Privide some commads for quick create applicat or component[<mga>WIP</mga>]")
 */
class CreateCommand
{
    /**
     * @CommandMapping()
     * @param Input $input
     */
    public function app(Input $input): void
    {
        Show::info('WIP');
    }

    /**
     * @CommandMapping(alias="cpt")
     * @param Input $input
     */
    public function component(Input $input): void
    {
        Show::info('WIP');
    }
}
