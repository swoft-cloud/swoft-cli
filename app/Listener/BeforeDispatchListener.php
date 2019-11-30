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

use Swoft\Cli\Helper\CliHelper;
use Swoft\Console\Application;
use Swoft\Console\ConsoleEvent;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BeforeDispatchListener - event handler
 *
 * @Listener(ConsoleEvent::DISPATCH_BEFORE)
 */
class BeforeDispatchListener implements EventHandlerInterface
{
    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event): void
    {
        $configFile = getcwd() . '/swoftcli.yml';

        if (file_exists($configFile)) {
            CliHelper::info('find config file(swoftcli.yml) in work directory, will load it');

            /** @var Application $app */
            $app  = $event->getTarget();
            $data = Yaml::parseFile($configFile);

            $app->setMulti($data);
        }
    }
}
