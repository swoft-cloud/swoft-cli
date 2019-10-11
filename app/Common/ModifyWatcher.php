<?php declare(strict_types=1);

namespace Swoft\Cli\Common;

use RuntimeException;
use Swoft\Stdlib\Helper\Sys;
use function array_merge;
use function basename;
use function file_get_contents;
use function file_put_contents;
use function fnmatch;
use function glob;
use function implode;
use function in_array;
use function is_dir;
use function is_file;
use function json_encode;
use function md5;
use function md5_file;
use function preg_match;
use function strpos;
use function trim;

/**
 * Class FilesWatcher - Check Dir's files modified by md5_file()
 *
 * @since 2.0
 */
final class ModifyWatcher
{
    /**
     * @var string
     */
    private $idFile;

    /**
     * @var string Gen by md5($this->md5Hashes);
     */
    private $dirHash = '';

    /**
     * @var string Previous dirHash value
     */
    private $oldHash = '';

    /**
     * @var string[]
     */
    private $watchDirs = [];

    /**
     * @var array MD5 hashes string for each file
     * [
     *  file => file md5
     * ]
     */
    private $md5Hashes = [];

    /**
     * @var array Included file name
     */
    private $names = ['*.php'];

    /**
     * @var array Excluded file name
     */
    private $notNames = [
        '.gitignore',
        'LICENSE[.txt]', // 'LICENSE' 'LICENSE.txt'
    ];

    /**
     * @var array Excluded directory name
     */
    private $excludes = [];

    /**
     * Quickly detect if there is a change, stop detecting and notify if there is a file change
     *
     * @var bool
     */
    private $fastMode = true;

    /**
     * @var array
     */
    private $changedInfo = [];

    /**
     * @var bool
     */
    private $ignoreDotDirs = true;

    /**
     * @var bool
     */
    private $ignoreDotFiles = true;

    /**
     * @var int
     */
    private $fileCounter = 0;

    /**
     * ModifyWatcher constructor.
     *
     * @param string|null $idFile
     */
    public function __construct(string $idFile = '')
    {
        $this->idFile = $idFile;
    }

    /**
     * @param string $idFile
     *
     * @return $this
     */
    public function setIdFile(string $idFile): self
    {
        $this->idFile = $idFile;
        return $this;
    }

    /**
     * @param string|array $notNames
     *
     * @return ModifyWatcher
     */
    public function name($notNames): self
    {
        $this->notNames = array_merge($this->notNames, (array)$notNames);
        return $this;
    }

    /**
     * @param string|array $notNames
     *
     * @return ModifyWatcher
     */
    public function notName($notNames): self
    {
        $this->notNames = array_merge($this->notNames, (array)$notNames);
        return $this;
    }

    /**
     * @param string|array $excludeDirs
     *
     * @return ModifyWatcher
     */
    public function exclude($excludeDirs): self
    {
        $this->excludes = array_merge($this->excludes, (array)$excludeDirs);
        return $this;
    }

    /**
     * @param bool $ignoreDotDirs
     *
     * @return ModifyWatcher
     */
    public function ignoreDotDirs($ignoreDotDirs): ModifyWatcher
    {
        $this->ignoreDotDirs = (bool)$ignoreDotDirs;
        return $this;
    }

    /**
     * @param bool $ignoreDotFiles
     *
     * @return ModifyWatcher
     */
    public function ignoreDotFiles($ignoreDotFiles): ModifyWatcher
    {
        $this->ignoreDotFiles = (bool)$ignoreDotFiles;
        return $this;
    }

    /**
     * @param string|array $dirs
     *
     * @return $this
     */
    public function watch($dirs): self
    {
        $this->watchDirs = array_merge($this->watchDirs, (array)$dirs);
        return $this;
    }

    /**
     * alias of the watch()
     *
     * @param string|array $dirs
     *
     * @return $this
     */
    public function watchDir($dirs): self
    {
        $this->watchDirs = array_merge($this->watchDirs, (array)$dirs);
        return $this;
    }

    /**
     * init calc dir hash value
     */
    public function initHash(): void
    {
        $fastMode = $this->fastMode;
        // set to false
        $this->fastMode = false;

        $this->isChanged();

        // revert
        $this->fastMode    = $fastMode;
        $this->changedInfo = [];
    }

    /**
     * @return bool
     */
    public function isModified(): bool
    {
        return $this->isChanged();
    }

    /**
     * @return bool
     * @throws RuntimeException
     */
    public function isChanged(): bool
    {
        if (!$this->idFile) {
            $this->idFile = Sys::getTempDir() . '/' . md5(json_encode($this->watchDirs)) . '.id';
        }

        // Get old hash id
        if (!$this->oldHash = $this->dirHash) {
            $this->oldHash = $this->getMd5ByIdFile();
        }

        $this->calcMd5Hash();

        return $this->dirHash !== $this->oldHash;
    }

    /**
     * @return bool|string
     */
    public function getMd5ByIdFile()
    {
        if (!$file = $this->idFile) {
            return false;
        }

        if (!is_file($file)) {
            return false;
        }

        return trim(file_get_contents($file));
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function calcMd5Hash(): string
    {
        if (!$this->watchDirs) {
            throw new RuntimeException('Please setting want to watched directories before run.');
        }

        // Reset data
        $this->changedInfo = [];

        foreach ($this->watchDirs as $dir) {
            $this->collectDirMd5($dir);
        }

        // Save old hash
        if ($this->dirHash) {
            $this->oldHash = $this->dirHash;
        }

        $this->dirHash = md5(implode('', $this->md5Hashes));

        if ($this->idFile) {
            file_put_contents($this->idFile, $this->dirHash);
        }

        return $this->dirHash;
    }

    /**
     * @param string $watchDir
     */
    private function collectDirMd5(string $watchDir): void
    {
        if ($this->fastMode && $this->changedInfo) {
            return; // end calc
        }

        // TODO replace `scandir` to `glob` or `SPLFile*`
        foreach (glob($watchDir . '/*') as $path) {
            $name = basename($path);

            // Recursive directory
            if (is_dir($path)) {
                if ($this->isWatchDir($name)) {
                    $this->collectDirMd5($path);
                }

                continue;
            }

            // Check file
            if (!$this->isWatchFile($name)) {
                continue;
            }

            $oldMd5  = '';
            $fileMd5 = md5_file($path);

            if (isset($this->md5Hashes[$path])) {
                $oldMd5 = $this->md5Hashes[$path];
            }

            $this->fileCounter++;
            $this->md5Hashes[$path] = $fileMd5;

            if ($this->fastMode && $oldMd5 !== $fileMd5) {
                $this->changedInfo = [
                    'file'    => $path,
                    'oldMd5'  => $oldMd5,
                    'fileMd5' => $fileMd5,
                ];
                return; // end calc
            }
        }
    }

    /**
     * @param string $dName
     *
     * @return bool
     */
    public function isWatchDir(string $dName): bool
    {
        if ($this->ignoreDotDirs && strpos($dName, '.') === 0) {
            return false;
        }

        if (in_array($dName, $this->excludes, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $fName
     *
     * @return bool
     */
    public function isWatchFile(string $fName): bool
    {
        if ($this->ignoreDotFiles && strpos($fName, '.') === 0) {
            return false;
        }

        // Check exclude
        if ($this->notNames) {
            foreach ($this->notNames as $name) {
                if (preg_match('#' . $name . '#', $fName)) {
                    return false;
                }
            }
        }

        // Check watch match
        if (!$this->names) {
            return true;
        }

        foreach ($this->names as $name) {
            if (fnmatch($name, $fName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function getIdFile(): ?string
    {
        return $this->idFile;
    }

    /**
     * @return string[]
     */
    public function getWatchDir(): array
    {
        return $this->watchDirs;
    }

    /**
     * @return string
     */
    public function getDirHash(): string
    {
        return $this->dirHash;
    }

    /**
     * @return int
     */
    public function getFileCounter(): int
    {
        return $this->fileCounter;
    }

    /**
     * @return bool
     */
    public function isFastMode(): bool
    {
        return $this->fastMode;
    }

    /**
     * @param bool $fastMode
     *
     * @return ModifyWatcher
     */
    public function setFastMode(bool $fastMode): self
    {
        $this->fastMode = $fastMode;
        return $this;
    }

    /**
     * @return array
     */
    public function getChangedInfo(): array
    {
        return $this->changedInfo;
    }

    /**
     * @return string
     */
    public function getOldHash(): string
    {
        return $this->oldHash;
    }
}
