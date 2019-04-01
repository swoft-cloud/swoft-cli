<?php

namespace Swoft\Cli\WebSocket\Chat;

use Swoft\Session\Session;
use Swoft\WebSocket\Server\Annotation\Mapping\WsController;
use Swoft\WebSocket\Server\Annotation\Mapping\MessageMapping;

/**
 * Class HomeController
 * @WsController("")
 */
class HomeController
{
    /**
     * message command is: 'home.index'
     *
     * @return void
     * @MessageMapping()
     */
    public function index(): void
    {
        Session::mustGet()->push('hi, this is home.index');
    }

    /**
     * message command is: 'home.echo'
     *
     * @param $data
     * @MessageMapping()
     */
    public function echo($data): void
    {
        Session::mustGet()->push('recv: ' .$data);
    }
}
