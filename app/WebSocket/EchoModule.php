<?php

namespace Swoft\Cli\WebSocket;

use Swoft\WebSocket\Server\Annotation\Mapping\OnMessage;
use Swoft\WebSocket\Server\Annotation\Mapping\WsModule;

/**
 * Class EchoModule
 * @WsModule()
 */
class EchoModule
{
    /**
     * @OnMessage()
     */
    public function onMessage(): void
    {

    }
}
