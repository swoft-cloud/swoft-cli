<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use InvalidArgumentException;
use RuntimeException;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandArgument;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Console\Helper\Interact;
use Swoft\Console\Input\Input;
use Swoft\Stdlib\Helper\Dir;
use Swoft\Stdlib\Helper\Sys;
use function alias;
use function is_dir;
use function output;

/**
 * provide some commands for application developing[<mga>WIP</mga>]
 * @Command()
 */
class AssetCommand
{
    /**
     * @return array
     */
    public static function internalConfig(): array
    {
        return [
            'swoft/devtool' => [
                '@devtool/web/dist/devtool/static',
                '@root/public/devtool'
            ],
        ];
    }

    /**
     * Used to publish the internal resources of the module to the 'public' directory
     *
     * @CommandMapping(example="{fullCommand} swoft/devtool")
     *
     * @CommandArgument("srcDir", desc="The source assets directory path. eg. `@vendor/some/lib/assets`")
     * @CommandArgument("dstDir", desc="The dist directory component name", default="@root/public/some/lib")
     * @CommandOption("yes", short="y", desc="Do not confirm when execute publish", default=false)
     * @CommandOption("force", short="f", desc="Force override all exists file", default=false)
     *
     * @param Input $input
     *
     * @return int
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function publish(Input $input): int
    {
        $assetDir  = $input->getArg(0);
        $targetDir = $input->getArg(1);

        if (!$assetDir && !$targetDir) {
            output()->colored('arguments is required!', 'warning');

            return -1;
        }

        // first arg is internal component name
        if ($assetDir && !$targetDir) {
            $config = static::internalConfig();

            if (!isset($config[$assetDir])) {
                output()->colored('missing arguments!', 'warning');
            }

            [$assetDir, $targetDir] = $config[$assetDir];
        }

        $assetDir  = alias($assetDir);
        $targetDir = alias($targetDir);

        $force = $input->sameOpt(['f', 'force'], false);

        if ($force && is_dir($targetDir)) {
            output()->writeln("Will delete the old assets: $targetDir");

            [$code, , $error] = Sys::run("rm -rf $targetDir");

            if ($code !== 0) {
                output()->colored("Delete dir $targetDir is failed!", 'error');
                output()->writeln($error);

                return -2;
            }
        }

        $yes     = $input->sameOpt(['y', 'yes'], false);
        $command = "cp -Rf $assetDir $targetDir";

        output()->writeln("Will run shell command:\n $command");

        if (!$yes && !Interact::confirm('Ensure continue?')) {
            output()->writeln(' Quit, Bye!');

            return 0;
        }

        Dir::make($targetDir);

        [$code, , $error] = Sys::run($command, alias('@root'));

        if ($code !== 0) {
            output()->colored("Publish assets to $targetDir is failed!", 'error');
            output()->writeln($error);

            return -2;
        }

        output()->colored("\nPublish assets to $targetDir is OK!", 'success');

        return 0;
    }

    public function test(): int
    {
        return 0;
    }
}
