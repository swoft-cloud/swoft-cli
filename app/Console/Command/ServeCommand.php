<?php declare(strict_types=1);

namespace Swoft\Cli\Console\Command;

use Swoft\Cli\Bean\ModifyWatcher;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandArgument;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Console\Helper\Show;
use Swoft\Console\Input\Input;
use Swoole\Process;

/**
 * Provide some commands for manage and watch swoft server project
 *
 * @Command(idAliases={"run": "serve:run"}, coroutine=false)
 * @CommandOption("debug", default=false, desc="open debug mode for display more detail", type="bool")
 * @CommandOption("php-bin", default="/usr/local/bin/php", desc="Custom the php bin file path", type="string")
 */
class ServeCommand
{
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
     * @return bool
     */
    private function collectInfo(Input $input): bool
    {
        $workDir = $input->getPwd();

        $this->debug  = $input->getBoolOpt('debug');
        $this->phpBin = $input->getOpt('php-bin');

        $interval = (int)$input->getOpt('interval', 3);
        if ($interval < 0 || $interval > 15) {
            $interval = 3;
        }

        $this->interval = $interval;
        $this->binFile  = $input->getSameOpt(['bin-file', 'b']);
        $this->startCmd = $input->getSameOpt(['start-cmd', 'c']);

        if ($nameString = $input->getSameOpt(['watch-dir', 'w'])) {
            $this->watchDir = \explode(',', \str_replace(' ', '', $nameString));
        }

        $this->targetPath = $input->getArg('targetPath', $workDir);

        // Parse relative path
        if (\strpos($this->targetPath, '..') !== false) {
            $this->targetPath = \realpath($this->targetPath);
        }

        // $cmd = "php {$pwd}/bin/swoft http:start";
        // $cmd = "php {$pwd}/bin/swoftcli sys:info";
        $this->entryFile = $this->targetPath . '/' . $this->binFile;

        \output()->aList([
            'current pid' => \getmypid(),
            'current dir' => $workDir,
            'target path' => $this->targetPath,
            'watch dirs'  => $this->watchDir,
            'entry file'  => $this->entryFile,
            'execute cmd' => \sprintf('%s %s/%s %s', $this->phpBin, $this->targetPath, $this->binFile, $this->startCmd),
        ], 'Some information');

        if (!\file_exists($this->entryFile)) {
            Show::liteError('The swoft entry file is not exist');
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
     * @example
     *   {binFile} run -c ws:start -b bin/swoft /path/to/php/swoft
     * @param Input $input
     */
    public function run(Input $input): void
    {
        if (!$this->collectInfo($input)) {
            return;
        }

        $fileName = 'server-' . \md5($this->entryFile) . '.id';
        $watchDirs = \array_map(function ($name) {
            return $this->targetPath . '/' . $name;
        }, $this->watchDir);

        // $mw = new ModifyWatcher(Sys::getTempDir() . '/' . $fileName));
        $mw = new ModifyWatcher(\Swoft::getAlias('@runtime/' . $fileName));
        $mw->watchDir($watchDirs);
        $mw->initHash();

        Show::aList($mw->getWatchDir(), 'watched directories');

        $pid = $this->startServer();

        while ($pid > 0) {
            if ($ret = Process::wait(false)) {
                $exitPid  = $ret['pid'];
                $exitCode = $ret['code'];
                Show::warning("Server [$exitPid] exited (signal {$ret['signal']}, code $exitCode)");

                // Exit with error
                if ($exitCode !== 0) {
                    Show::error('Server error exit');
                    return;
                }

                if ($exitPid === $pid) {
                    $pid = $this->startServer();
                }
            }

            if ($mw->isChanged()) {
                Show::info(\date('Y/m/d H:i:s') . ': file changed!');
                Show::aList($mw->getChangedInfo(), 'modify info');
                Show::info('will restart server');

                if (false === $this->stopServer($pid)) {
                    Show::writeln('Exit');
                    break;
                }

                $pid = $this->startServer();
            } elseif ($this->debug) {
                Show::info(\date('Y/m/d H:i:s') . ': no change!');
            }

            \sleep($this->interval);
        }
    }

    private function startServer(): int
    {
        Show::info('Start swoft server');

        // Create process
        $p = new Process(function (Process $p) {
            $p->exec($this->phpBin, [$this->entryFile, $this->startCmd]);
        });

        return $p->start();
    }

    public function stopServer(int $pid): bool
    {
        Show::info('Stop old server. PID ' . $pid);

        $ok = false;
        // SIGTERM = 15
        $signal    = 15;
        $timeout   = 5;
        $startTime = \time();

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
            if ((\time() - $startTime) >= $timeout) {
                $ok = false;
                Show::error('Stop sever is failed');
                break;
            }

            // Try kill process
            $ok = Process::kill($pid, $signal);
            \sleep(1);
        }

        return $ok;
    }
}
