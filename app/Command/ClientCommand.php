<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Console\Helper\Show;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use Swoft\Tcp\Protocol;
use Swoole\Coroutine\Client;
use const SWOOLE_SOCK_TCP;

/**
 * Class ClientCommand[<mga>WIP</mga>]
 *
 * @Command()
 */
class ClientCommand
{
    /**
     * connect to an tcp server and allow send message interactive
     *
     * @CommandMapping()
     * @CommandOption("host", short="H", desc="the tcp server host address", default="127.0.0.1", type="string")
     * @CommandOption("port", short="p", desc="the tcp server port number", default="18309", type="integer")
     * @CommandOption("split", short="s", desc="the tcp package split type: eof, len", default="eof", type="string")
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tcp(Input $input, Output $output): void
    {
        $proto = new Protocol();
        $sType = $input->getSameOpt(['split', 's'], 'eof');
        if ($sType === 'len') {
            $proto->setOpenLengthCheck(true);
        }

        $output->aList([
            'splitPackageType' => $proto->getSplitType(),
            'dataPackerType'   => $proto->getType(),
            'dataPackerClass'  => $proto->getPackerClass(),
        ], 'Client Protocol');

        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set($proto->getConfig());

        $host = $input->getSameOpt(['host', 'H'], '127.0.0.1');
        $port = $input->getSameOpt(['port', 'p'], 18309);
        $addr = $host . ':' . $port;

        CONNCET:
        $output->colored('Begin connecting to tcp server: ' . $addr);
        if (!$ok = $client->connect((string)$host, (int)$port, 5.0)) {
            $code = $client->errCode;
            /** @noinspection PhpComposerExtensionStubsInspection */
            $msg = socket_strerror($code);
            Show::error("Connect server failed. Error($code): $msg");
            return;
        }

        $output->colored('Success connect to tcp server. now, you can send message');
        $output->title('INTERACTIVE', ['indent' => 0]);

        while (true) {
            if (!$msg = $output->read('<info>client</info>> ')) {
                $output->liteWarning('Please input message for send');
                continue;
            }

            // Exit interactive terminal
            if ($msg === 'quit' || $msg === 'exit') {
                $output->colored('Quit, Bye!');
                break;
            }

            // Send message $msg . $proto->getPackageEOf()
            if (false === $client->send($proto->packBody($msg))) {
                /** @noinspection PhpComposerExtensionStubsInspection */
                $output->error('Send error - ' . socket_strerror($client->errCode));

                if ($this->quickConfirm($input, 'Reconnect', true)) {
                    $client->close();
                    goto CONNCET;
                }

                $output->colored('GoodBye!');
                break;
            }

            // Recv response
            $res = $client->recv(2.0);
            if ($res === false) {
                /** @noinspection PhpComposerExtensionStubsInspection */
                $output->error('Recv error - ' . socket_strerror($client->errCode));
                continue;
            }

            if ($res === '') {
                $output->info('Server closed connection');
                if ($this->quickConfirm($input, 'Reconnect', true)) {
                    $client->close();
                    goto CONNCET;
                }

                $output->colored('GoodBye!');
                break;
            }

            [$head, $body] = $proto->unpackData($res);
            $output->prettyJSON($head);
            $output->writef('<yellow>server</yellow>> %s', $body);
        }

        $client->close();
    }

    /**
     * @CommandMapping("ws")
     */
    public function websocket(): void
    {

    }

    /**
     * @param Input  $input
     * @param string $msg
     * @param bool   $default
     *
     * @return bool
     */
    private function quickConfirm(Input $input, string $msg, bool $default = false): bool
    {
        $def = $default ? 'y' : 'n';
        $yes = $input->read("{$msg}? y/n[$def]: ");

        if ('' === $yes) {
            $yes = $def;
        }

        return stripos($yes, 'y') === 0;
    }

    /**
     * @CommandMapping()
     */
    public function udp(): void
    {

    }
}
