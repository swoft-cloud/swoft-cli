<?php declare(strict_types=1);

namespace Swoft\Cli\WebSocket;

use Swoft\WebSocket\Server\Annotation\Mapping\OnMessage;
use Swoft\WebSocket\Server\Annotation\Mapping\WsModule;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * Class EchoModule
 * @WsModule()
 */
class EchoModule
{
    /**
     * @OnMessage()
     * @param Server $server
     * @param Frame  $frame
     */
    public function onMessage(Server $server, Frame $frame): void
    {
        $server->push($frame->fd, 'welcome');
    }
}
