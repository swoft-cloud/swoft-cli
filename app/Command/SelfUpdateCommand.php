<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use Swoft\Cli\SwoftCLI;
use Swoft\Console\Helper\Show;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandArgument;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use Swoole\Coroutine\Http\Client;
use Toolkit\Cli\Download;
use function file_get_contents;
use function json_decode;
use function parse_url;

/**
 * update the swoft-cli to latest version from github
 *
 * @Command("self-update", alias="selfupdate, update-self, updateself", coroutine=true)
 */
class SelfUpdateCommand
{
    public const LATEST_RELEASE_URL = 'https://api.github.com/repos/swoft-cloud/swoft-cli/releases/latest';

    /**
     * update the swoft-cli to latest version from github
     *
     * @CommandMapping(alias="selfupdate, update-self, updateself")
     * @CommandOption(
     *     "check", type="bool",
     *     desc="only fetch latest release information, but dont download and update package",
     * )
     * @param Input  $input
     * @param Output $output
     */
    public function down(Input $input, Output $output): void
    {
        $output->colored('Current Version: ' . SwoftCLI::VERSION);

        // @see https://developer.github.com/v3/repos/releases/
        // curl https://api.github.com/repos/swoft-cloud/swoft-cli/releases/latest

        // $jsonText = file_get_contents(self::LATEST_RELEASE_URL);

        $output->colored('> Fetch latest release information for Github ...', 'cyan');

        $result = $this->fetchInfo();
        $latest = json_decode($result, true);
        if (!$latest) {
            Show::error('Failed for update: fetch latest version info failed');
            return;
        }

        $tagName  = $latest['tag_name'];
        $metaInfo = [
            'local version'  => 'v' . SwoftCLI::VERSION,
            'latest version' => $tagName,
            'created at'     => $latest['created_at'],
            'published at'   => $latest['published_at'],
        ];

        Show::aList($metaInfo, 'latest release information');
        if ($input->getOpt('check')) {
            return;
        }

        // get phar download address.
        if (!isset($latest['assets'][0]['browser_download_url'])) {
            Show::error('Failed for update: not found latest phar package download url');
            return;
        }

        $pharUrl = $latest['assets'][0]['browser_download_url'];

        Download::file($pharUrl);
    }

    private function fetchInfo(): string
    {
        $info = $this->parseUrl(self::LATEST_RELEASE_URL);
        $port = (int)$info['port'];
        $path = $info['path'];

        $client = new Client($info['host'], $port, $port === 443);
        $client->setHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36',
        ]);
        $client->execute($path);

        // $status = $client->statusCode;
        $result = $client->body;

        // close connection
        $client->close();

        return $result;
    }

    /**
     * @param string $url
     *
     * @return array
     */
    private function parseUrl(string $url): array
    {
        $info = parse_url($url);

        if ($info['scheme'] === 'https') {
            $info['port'] = 443;
        }

        return $info;
    }
}
