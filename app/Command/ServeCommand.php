<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use Swoft;
use Swoft\Cli\Common\ModifyWatcher;
use Swoft\Cli\Helper\CliHelper;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandArgument;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Console\Helper\Show;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use Swoft\Stdlib\Helper\Sys;
use Swoole\Process;
use function array_filter;
use function array_map;
use function date;
use function explode;
use function file_exists;
use function getmypid;
use function md5;
use function realpath;
use function sleep;
use function sprintf;
use function str_replace;
use function strpos;
use function time;
use function trim;

/**
 * Provide some commands for manage and watch swoft server project
 *
 * @since 2.0
 *
 * @Command(idAliases={"run": "serve:run"}, coroutine=false)
 * @CommandOption("php-bin", default="php", desc="Custom the php bin file path", type="string")
 */
class ServeCommand
{
    /**
     * @var int
     */
    private $pid = 0;

    /**
     * @var int
     */
    private $retry = 0;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var string
     */
    private $phpBin;

    /**
     * @var string
     */
    private $binFile;

    /**
     * @var string
     */
    private $startCmd;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var string
     */
    private $targetPath;

    /**
     * @var string[]
     */
    private $watchDir = ['app', 'config'];

    /**
     * @var string
     */
    private $entryFile;

    /**
     * @param Input $input
     *
     * @return bool
     */
    private function collectInfo(Input $input): bool
    {
        $workDir = $input->getPwd();

        $this->debug  = $input->getBoolOpt('debug');
        $this->phpBin = $input->getOpt('php-bin');

        if ($this->phpBin === 'php') {
            [$ok, $ret,] = Sys::run('which php');

            if ($ok === 0) {
                $this->phpBin = trim($ret);
            }
        }

        $interval = (int)$input->getOpt('interval', 3);
        if ($interval < 0 || $interval > 15) {
            $interval = 3;
        }

        $this->interval = $interval;
        $this->binFile  = $input->getSameOpt(['bin-file', 'b']);
        $this->startCmd = $input->getSameOpt(['start-cmd', 'c']);

        if ($nameString = $input->getSameOpt(['watch-dir', 'w'])) {
            $this->watchDir = explode(',', str_replace(' ', '', $nameString));
        }

        $this->targetPath = $input->getArg('targetPath', $workDir);

        // Parse relative path
        if (strpos($this->targetPath, '..') !== false) {
            $this->targetPath = realpath($this->targetPath);
        }

        // $cmd = "php {$pwd}/bin/swoft http:start";
        // $cmd = "php {$pwd}/bin/swoftcli sys:info";
        $this->entryFile = $this->targetPath . '/' . $this->binFile;

        Show::aList([
            'current pid' => getmypid(),
            'current dir' => $workDir,
            'php binFile' => $this->phpBin,
            'target path' => $this->targetPath,
            'watch dirs'  => $this->watchDir,
            'entry file'  => $this->entryFile,
            'execute cmd' => sprintf('%s %s/%s %s', $this->phpBin, $this->targetPath, $this->binFile, $this->startCmd),
        ], 'Work information');

        if (!file_exists($this->entryFile)) {
            Show::error('The application entry file is not exist');
            return false;
        }

        $watchDirs = array_map(function ($name) {
            $path = $this->targetPath . '/' . trim($name, '/ ');

            if (!is_dir($path)) {
                Show::warning("The want watched dir '{$path}' is not exist");
                return '';
            }

            return $path;
        }, $this->watchDir);

        if (!$this->watchDir = array_filter($watchDirs)) {
            Show::error('Did not enter any valid monitoring directory');
            return false;
        }

        return true;
    }

    /**
     * Start the swoft server and monitor the file changes to restart the server
     *
     * @CommandMapping()
     * @CommandArgument("targetPath", type="path",
     *     desc="Your swoft project path, default is current work directory"
     * )
     * @CommandOption("interval", type="integer", default=3,
     *     desc="Interval time for watch files, unit is seconds"
     * )
     * @CommandOption(
     *     "bin-file", short="b", type="string", default="bin/swoft",
     *     desc="Entry file for the swoft project"
     * )
     * @CommandOption(
     *     "start-cmd", short="c", type="string", default="http:start",
     *     desc="the server startup command to be executed"
     * )
     * @CommandOption(
     *     "watch", short="w", default="app,config", type="directories",
     *     desc="List of directories you want to watch, relative the <cyan>targetPath</cyan>"
     * )
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *   {binFile} run    Default, will start http server
     *   {binFile} run -c ws:start -b bin/swoft /path/to/swoft
     */
    public function run(Input $input, Output $output): void
    {
        if (!$this->collectInfo($input)) {
            return;
        }

        $fileName = 'server-' . md5($this->entryFile) . '.id';
        // $mw = new ModifyWatcher(Sys::getTempDir() . '/' . $fileName));
        $mw = new ModifyWatcher(Swoft::getAlias('@runtime/' . $fileName));
        $mw->watchDir($this->watchDir);
        $mw->initHash();

        Show::aList($mw->getWatchDir(), 'Watched Directories');

        $pid = $this->startServer();

        while ($pid > 0) {
            if ($ret = Process::wait(false)) {
                $exitPid  = $ret['pid'];
                $exitCode = $ret['code'];
                CliHelper::warn("Target server(pid $exitPid) exited (signal {$ret['signal']}, code $exitCode)");
                if ($exitCode !== 0) {
                    CliHelper::error('Server non-zero status exit');
                }

                // if ($retry > 2) {
                //     $msg = $output->read("Have auto try start server {$retry} times, restart?(y/n) >");
                //     if ($msg && 0 === stripos($msg, 'n')) {
                //         CliHelper::info('Exit');
                //         return;
                //     }
                //
                //     $retry = 0;
                // }

                CliHelper::info('Will try restart server ... after 3 seconds');
                sleep(3);
                $pid = $this->startServer();
                continue;
            }

            if ($mw->isChanged()) {
                CliHelper::info(date('Y/m/d H:i:s') . ': file changed!');
                Show::aList($mw->getChangedInfo(), 'modify info');
                CliHelper::info('Will restart server');

                if (false === $this->stopServer($pid)) {
                    CliHelper::info('Exit');
                    break;
                }

                $pid = $this->startServer();
            } elseif ($this->debug) {
                CliHelper::info(date('Y/m/d H:i:s') . ': files no change!');
            }

            sleep($this->interval);
        }
    }

    private function startServer(): int
    {
        CliHelper::info('Start swoft server');

        // Create process
        $p = new Process(function (Process $p) {
            $p->exec($this->phpBin, [$this->entryFile, $this->startCmd]);
        });

        return $p->start();
    }

    public function stopServer(int $pid): bool
    {
        CliHelper::info('Stop old server. PID ' . $pid);

        $ok = false;
        // SIGTERM = 15
        $signal    = 15;
        $timeout   = 45;
        $startTime = time();

        // retry stop if not stopped.
        while (true) {
            //  Recycling process
            if (($ret = Process::wait(false)) && $ret['pid'] === $pid) {
                return true;
            }

            // Process is not running
            if (!Process::kill($pid, 0)) {
                return true;
            }

            // Has been timeout
            if ((time() - $startTime) >= $timeout) {
                $ok = false;
                CliHelper::error('Stop sever is failed');
                break;
            }

            // Try kill process
            $ok = Process::kill($pid, $signal);
            sleep(1);
        }

        return $ok;
    }
}
