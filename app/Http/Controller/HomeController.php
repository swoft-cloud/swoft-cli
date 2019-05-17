<?php declare(strict_types=1);

namespace Swoft\Cli\Http\Controller;

use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;

/**
 * Class HomeController
 *
 * @package Swoft\Cli\Http\Controller
 * @Controller("")
 */
class HomeController
{
    /**
     * @return string
     * @RequestMapping("/")
     */
    public function index(): string
    {
        return 'hi';
    }
}
