<?php

namespace Swoft\Cli\Console\Command;

use Swoft\Console\Advanced\PharCompiler;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Stdlib\Helper\Dir;

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
     * @CommandOption("dir", desc="Setting the project directory for packing, default is current work-dir", default="{workDir}")
     * @CommandOption("fast", desc="Fast build. only add modified files by <cyan>git status -s</cyan>")
     * @CommandOption("refresh", desc="Whether build vendor folder files on phar file exists", default=false)
     * @CommandOption("output", short="o", desc="Setting the output file name", default="app.phar")
     * @CommandOption("config", short="c", desc="Use the defined config for build phar")
     * @example
     *  {fullCommand}                               Pack current dir to a phar file.
     *  {fullCommand} --dir vendor/swoft/devtool    Pack the specified dir to a phar file.
     * @return int
     * @throws \Exception
     */
    public function pack(): int
    {
        $time    = \microtime(1);
        $workDir = input()->getPwd();

        $dir = \input()->getOpt('dir') ?: $workDir;
        $cpr = $this->configCompiler($dir);

        // $counter = 0;
        $refresh  = input()->getOpt('refresh');
        $pharFile = $workDir . '/' . (\input()->sameOpt(['o', 'output']) ?: 'app.phar');

        // use fast build
        if (\input()->getOpt('fast')) {
            $cpr->setModifies($cpr->findChangedByGit());

            \output()->writeln(
                '<info>[INFO]</info>Use fast build, will only pack changed or new files(from git status)'
            );
        }

        \output()->writeln(
            "Now, will begin building phar package.\n from path: <comment>$dir</comment>\n" .
            " phar file: <info>$pharFile</info>"
        );

        \output()->writeln('<info>Pack file to Phar ... ... </info>');
        $cpr->onError(function ($error) {
            \output()->writeln("<warning>$error</warning>");
        });

        if (input()->getOpt('debug')) {
            $cpr->onAdd(function ($path) {
                \output()->writeln(" <comment>+</comment> $path");
            });
        }

        // packing ...
        $cpr->pack($pharFile, $refresh);

        $info = [
            PHP_EOL . '<success>Phar build completed!</success>',
            " - Phar file: $pharFile",
            ' - Phar size: ' . round(filesize($pharFile) / 1024 / 1024, 2) . ' Mb',
            ' - Pack Time: ' . round(microtime(1) - $time, 3) . ' s',
            ' - Pack File: ' . $cpr->getCounter(),
            ' - Commit ID: ' . $cpr->getVersion(),
        ];
        \output()->writeln(\implode("\n", $info));

        return 0;
    }

    /**
     * unpack a phar package to a directory
     * @CommandMapping(
     *     usage="{fullCommand} -f FILE [-d DIR]",
     *     example="{fullCommand} -f myapp.phar -d var/www/app"
     *  )
     * @CommandOption("file", short="f", desc="The packed phar file path", type="string")
     * @CommandOption("dir", short="d", desc="The output dir on extract phar package", type="string")
     * @CommandOption("yes", short="y", desc="Whether display goon tips message", type="string")
     * @CommandOption("overwrite", desc="Whether overwrite exists files on extract phar", type="bool")
     * @return int
     * @throws \RuntimeException
     * @throws \BadMethodCallException
     */
    public function unpack(): int
    {
        if (!$path = \input()->getSameOpt(['f', 'file'])) {
            \output()->writeln("<error>Please input the phar file path by option '-f|--file'</error>");

            return 1;
        }

        $basePath = \input()->getPwd();
        $file     = \realpath($basePath . '/' . $path);

        if (!file_exists($file)) {
            \output()->writeln("<error>The phar file not exists. File: $file</error>");
            return 1;
        }

        $dir       = input()->getSameOpt(['d', 'dir']) ?: $basePath;
        $overwrite = input()->getOpt('overwrite');

        if (!is_dir($dir)) {
            Dir::make($dir);
        }

        \output()->writeln("Now, begin extract phar file:\n $file \nto dir:\n $dir");

        PharCompiler::unpack($file, $dir, null, $overwrite);

        \output()->writeln("<success>OK, phar package have been extract to the dir: $dir</success>");

        return 0;
    }

    /**
     * @param string $dir
     * @return PharCompiler
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function configCompiler(string $dir): PharCompiler
    {
        // config
        $compiler = new PharCompiler($dir);

        // config file.
        $configFile = input()->getSameOpt(['c', 'config']) ?: $dir . '/phar.build.inc';

        if ($configFile && is_file($configFile)) {
            require $configFile;

            $compiler->in($dir);

            return $compiler;
        }

        throw new \InvalidArgumentException("The phar build config file not found. File: $configFile");
    }
}
