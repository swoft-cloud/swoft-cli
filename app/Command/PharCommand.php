<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use BadMethodCallException;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Swoft\Console\Advanced\PharCompiler;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Console\Helper\Show;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use Swoft\Stdlib\Helper\Dir;
use Swoft\Stdlib\Helper\Str;
use function filesize;
use function input;
use function is_file;
use function microtime;
use function output;
use function realpath;
use function round;

/**
 * There are some command for help package application
 *
 * @Command(coroutine=false)
 */
class PharCommand
{
    /**
     * pack project codes to a phar package
     * @CommandMapping(usage="{fullCommand} [--dir DIR] [--output FILE]",)
     *
     * @CommandOption("dir", type="DIRECTORY",
     *     desc="Setting the project directory for packing, default is current work-dir"
     * )
     * @CommandOption("fast", desc="Fast build. only add modified files by <cyan>git status -s</cyan>")
     * @CommandOption("refresh", default=false, type="bool", desc="Whether build vendor folder files on phar file exists")
     * @CommandOption("output", short="o", desc="Setting the output file name", type="string", default="app.phar")
     * @CommandOption("config", short="c", type="string", desc="Use the defined config file for build phar")
     * @CommandOption("files", type="string", desc="only pack the list files to the exist phar, multi use ',' split")
     * @param Input  $input
     * @param Output $output
     *
     * @return int
     * @throws Exception
     * @example
     *   {fullCommand}                               Pack current dir to a phar file.
     *   {fullCommand} --dir vendor/swoft/devtool    Pack the specified dir to a phar file.
     *
     *  custom output phar file name
     *   php -d phar.readonly=0 {binFile} phar:pack -o=scli.phar
     *
     *  only update the input files:
     *   php -d phar.readonly=0 bin/swoftcli phar:pack -o=swoftcli.phar --debug --files app/Command/ServeCommand.php
     */
    public function pack(Input $input, Output $output): int
    {
        $startAt  = microtime(true);
        $workDir  = $input->getPwd();
        $outFile  = $input->sameOpt(['o', 'output']) ?: 'app.phar';
        $pharFile = $workDir . '/' . $outFile;

        $dir = $input->getOpt('dir') ?: $workDir;
        $cpr = $this->configCompiler($dir);

        Show::aList([
            'work dir'  => $workDir,
            'project'   => $dir,
            'phar file' => $pharFile,
        ], 'Building Information');

        // use fast build
        if ($input->getBoolOpt('fast')) {
            $cpr->setModifies($cpr->findChangedByGit());
            $output->liteInfo('Use fast build, will only pack changed or new files(by git status)');
        }

        // musual append some files
        if ($files = $input->getStringOpt('files')) {
            $cpr->setModifies(Str::explode($files));
            $output->liteInfo("will only pack input files to the exists phar: $outFile");
        }

        $cpr->onError(function ($error) {
            output()->writeln("<warning>$error</warning>");
        });

        $counter = null;
        if ($input->getOpt('debug')) {
            $output->info('Pack file to Phar ... ...');
            $cpr->onAdd(function ($path) {
                output()->writeln(" <info>+</info> $path");
            });

            $cpr->on('skip', function (string $path, bool $isFile) {
                output()->writeln(" <red>-</red> $path" . ($isFile ? '[F]' : '[D]'));
            });
        } else {
            $output->info('Pack file to Phar:');
            $counter = Show::counterTxt('<info>File Packing ...</info>', 'Done.');
            $cpr->onAdd(function () use ($counter) {
                $counter->send(1);
            });
        }

        // packing ...
        $refresh = $input->getBoolOpt('refresh');
        $cpr->pack($pharFile, $refresh);

        // end
        if ($counter) {
            $counter->send(-1);
        }

        $info = [
            PHP_EOL . '<success>Phar Build Completed!</success>',
            " - Phar file: $pharFile",
            ' - Phar size: ' . round(filesize($pharFile) / 1024 / 1024, 2) . ' Mb',
            ' - Pack Time: ' . round(microtime(true) - $startAt, 3) . ' s',
            ' - Pack File: ' . $cpr->getCounter(),
            ' - Commit ID: ' . $cpr->getLastCommit(),
        ];
        $output->writeln($info);

        return 0;
    }

    /**
     * unpack a phar package to a directory
     * @CommandMapping(usage="{fullCommand} -f FILE [-d DIR]")
     * @CommandOption("file", short="f", desc="The packed phar file path", type="string")
     * @CommandOption("dir", short="d", desc="The output dir on extract phar package", type="string")
     * @CommandOption("yes", short="y", desc="Whether display goon tips message", type="string")
     * @CommandOption("overwrite", desc="Whether overwrite exists files on extract phar", type="bool")
     *
     * @return int
     * @throws RuntimeException
     * @throws BadMethodCallException
     * @example {fullCommand} -f myapp.phar -d var/www/app
     */
    public function unpack(): int
    {
        if (!$path = input()->getSameOpt(['f', 'file'])) {
            output()->writeln("<error>Please input the phar file path by option '-f|--file'</error>");

            return 1;
        }

        $basePath = input()->getPwd();
        $file     = realpath($basePath . '/' . $path);

        if (!file_exists($file)) {
            output()->writeln("<error>The phar file not exists. File: $file</error>");
            return 1;
        }

        $dir       = input()->getSameOpt(['d', 'dir']) ?: $basePath;
        $overwrite = input()->getOpt('overwrite');

        if (!is_dir($dir)) {
            Dir::make($dir);
        }

        output()->writeln("Now, begin extract phar file:\n $file \nto dir:\n $dir");

        PharCompiler::unpack($file, $dir, null, $overwrite);

        output()->writeln("<success>OK, phar package have been extract to the dir: $dir</success>");

        return 0;
    }

    /**
     * @param string $dir
     *
     * @return PharCompiler
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function configCompiler(string $dir): PharCompiler
    {
        $compiler = new PharCompiler($dir);

        // config file.
        $configFile = input()->getSameOpt(['c', 'config']) ?: $dir . '/phar.build.inc';

        if ($configFile && is_file($configFile)) {
            require $configFile;

            $compiler->in($dir);

            return $compiler;
        }

        throw new InvalidArgumentException("The phar build config file not found. File: $configFile");
    }
}
