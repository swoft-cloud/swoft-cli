<?php

namespace Swoft\Cli\WebSocket\Controller;

use Swoft\WebSocket\Server\Annotation\Mapping\WsController;
use Swoft\WebSocket\Server\Annotation\Mapping\MessageMapping;

/**
 * Class HomeController
 * @WsController("")
 */
class HomeController
{
    /**
     * @return string
     * @MessageMapping()
     */
    public function index(): string
    {
        return 'hi';
    }
}
