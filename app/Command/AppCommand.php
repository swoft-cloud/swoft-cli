<?php

namespace Swoft\Cli\Command;

use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Console\Helper\PharCompiler;
use Swoft\Console\Output\Output;
use Swoft\Stdlib\Helper\ComposerHelper;
use Swoft\Stdlib\Helper\Dir;
use Swoft\Stdlib\Helper\Sys;

/**
 * There are some help command for application[<cyan>built-in</cyan>]
 *
 * @Command(coroutine=false)
 */
class AppCommand
{
    /**
     * init the project, will create runtime dirs
     *
     * @CommandMapping("init")
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function initApp(): void
    {
        \output()->writeln('Create runtime directories: ', false);

        $tmpDir = \alias('@runtime');
        $dirs   = [
            'logs',
            'uploadfiles'
        ];

        foreach ($dirs as $dir) {
            Dir::make($tmpDir . '/' . $dir);
        }

        \output()->writeln('<success>OK</success>');
    }

    /**
     * Print current system environment information
     * @CommandMapping()
     * @param Output $output
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function env(Output $output): void
    {
        $buffer = [];
        // sys info
        $info = [
            "<bold>System environment info</bold>\n",
            'OS'             => \PHP_OS,
            'Php version'    => \PHP_VERSION,
            'Swoole version' => \SWOOLE_VERSION,
            'Swoft version'  => \Swoft::VERSION,
            'AppName'        => \config('name'),
            'BasePath'       => \BASE_PATH,
        ];

        foreach ($info as $name => $value) {
            if (\is_int($name)) {
                $buffer[] = $value;
                continue;
            }

            $name     = \str_pad($name, 35);
            $buffer[] = \sprintf('  <comment>%s</comment> %s', $name, $value);
        }

        $output->writeln($buffer);
    }

    /**
     * Check current operating environment information
     * @CommandMapping()
     * @param Output $output
     * @throws \RuntimeException
     */
    public function check(Output $output): void
    {
        // env check
        list($code, $return,) = Sys::run('php --ri swoole');
        $asyncRdsEnabled = $code === 0 ? \strpos($return, 'async redis client => enabled') : false;

        $list = [
            "<bold>Runtime environment check</bold>\n",
            'PHP version is greater than 7.1?'    => self::wrap(PHP_VERSION_ID > 70100, 'current is ' . \PHP_VERSION),
            'Swoole extension is installed?'      => self::wrap(\extension_loaded('swoole')),
            'Swoole version is greater than 2.1?' => self::wrap(\version_compare(SWOOLE_VERSION, '2.1.0', '>='),
                'current is ' . \SWOOLE_VERSION),
            'Swoole async redis is enabled?'      => self::wrap($asyncRdsEnabled),
            'Swoole coroutine is enabled?'        => self::wrap(\class_exists('Swoole\Coroutine', false)),
            "\n<bold>Extensions that conflict with 'swoole'</bold>\n",
            ' - zend'                             => self::wrap(!\extension_loaded('zend'),
                'Please disabled it, otherwise swoole will be affected!', true),
            ' - xdebug'                           => self::wrap(!\extension_loaded('xdebug'),
                'Please disabled it, otherwise swoole will be affected!', true),
            ' - xhprof'                           => self::wrap(!\extension_loaded('xhprof'),
                'Please disabled it, otherwise swoole will be affected!', true),
            ' - blackfire'                        => self::wrap(!\extension_loaded('blackfire'),
                'Please disabled it, otherwise swoole will be affected!', true),
        ];

        $buffer = [];
        $pass   = $total = 0;

        foreach ($list as $question => $value) {
            if (\is_int($question)) {
                $buffer[] = $value;
                continue;
            }

            $total++;

            if ($value[0]) {
                $pass++;
            }

            $question = \str_pad($question, 45);
            $buffer[] = \sprintf('  <comment>%s</comment> %s', $question, $value[1]);
        }

        $buffer[] = "\nCheck total: <bold>$total</bold>, Pass the check: <success>$pass</success>";

        $output->writeln($buffer);
    }

    /**
     * List all swoft components
     * @CommandMapping()
     * @param Output $output
     * @return int
     */
    public function components(Output $output): int
    {
        $lockFile = \alias('@root/composer.lock');

        if (!\is_file($lockFile)) {
            $output->colored("composer.lock file not exists. File: $lockFile", 'warning');
            return -1;
        }

        $buffer = [];
        $map    = ComposerHelper::parseLockFile($lockFile);

        foreach ($map as $item) {
            $buffer[] = \sprintf(
                '<info>%s</info> - <bold>%s</bold> (published at: %s)',
                \str_pad($item['name'], '20'),
                $item['version'],
                \substr($item['time'], 0, 19)
            );
        }

        $output->writeln($buffer);

        return 0;
    }

    /**
     * pack project to a phar package
     * @CommandMapping(
     *     usage="{fullCommand} [--dir DIR] [--output FILE]",
     *     example="
     *      {fullCommand} Pack current dir to a phar file.
     *      {fullCommand} --dir vendor/swoft/devtool       Pack the specified dir to a phar file.
     *     "
     *  )
     *
     * @CommandOption("dir", desc="Setting the project directory for packing, default is current work-dir", default="{workDir}")
     * @CommandOption("fast", desc="Fast build. only add modified files by <cyan>git status -s</cyan>")
     * @CommandOption("refresh", desc="Whether build vendor folder files on phar file exists", default=false)
     * @CommandOption("output", short="o", desc="Setting the output file name", default="app.phar")
     * @CommandOption("config", short="c", desc="Use the defined config for build phar")
     *
     * @return int
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \RuntimeException
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

    /**
     * @param bool   $condition
     * @param string $msg
     * @param bool   $showOnFalse
     * @return array
     */
    private static function wrap($condition, string $msg = '', $showOnFalse = false): array
    {
        $result = $condition ? '<success>Yes</success>' : '<red>No</red>';
        $des    = '';

        if ($msg) {
            if ($showOnFalse) {
                $des = !$condition ? " ($msg)" : '';
            } else {
                $des = " ($msg)";
            }
        }

        return [(bool)$condition, $result . $des];
    }
}
