<?php declare(strict_types=1);

namespace Swoft\Cli\WebSocket;

use Swoft\Http\Message\Request;
use Swoft\WebSocket\Server\Annotation\Mapping\OnOpen;
use Swoft\WebSocket\Server\Annotation\Mapping\WsModule;
use Swoft\Cli\WebSocket\Chat\HomeController;
use Swoft\WebSocket\Server\MessageParser\TokenTextParser;

/**
 * Class ChatModule
 * @WsModule(
 *     "/chat",
 *     messageParser=TokenTextParser::class,
 *     controllers={HomeController::class}
 * )
 */
class ChatModule
{
    /**
     * @OnOpen()
     * @param Request $request
     * @param int     $fd
     */
    public function onOpen(Request $request, int $fd): void
    {
        \server()->push($fd, 'welcome');
    }
}
