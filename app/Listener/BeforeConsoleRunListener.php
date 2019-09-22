<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://swoft.org/docs
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace Swoft\Cli\Listener;

use Swoft\Console\Application;
use Swoft\Console\ConsoleEvent;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BeforeConsoleRunListener - event handler
 *
 * @Listener(ConsoleEvent::RUN_BEFORE)
 */
class BeforeConsoleRunListener implements EventHandlerInterface
{
    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event): void
    {
        $configFile = getcwd() . '/swoftcli.yml';

        if (file_exists($configFile)) {
            /** @var Application $app */
            $app  = $event->getTarget();
            $data = Yaml::parseFile($configFile);

            $app->setMulti($data);
        }
    }
}
