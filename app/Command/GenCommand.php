<?php

namespace Swoft\Cli\Command;

use Swoft\App;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandArgument;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Console\Helper\ConsoleUtil;
use Swoft\Console\Helper\Interact;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use Swoft\Devtool\FileGenerator;
use Swoft\Devtool\Model\Logic\EntityLogic;

/**
 * Generate some common application template classes
 * @Command()
 *
 * @CommandOption("yes", short="y", desc="No need to confirm when performing file writing", default=false)
 * @CommandOption("override", short="o", desc="Force override exists file", default=false)
 * @CommandOption("namespace", short="n", desc="The class namespace", default="App\Command")
 * @CommandOption("tpl-dir", type="string", desc="The class namespace", default="built-in")
 */
class GenCommand
{
    /**
     * @var string
     */
    public $defaultTplPath;

    public function init(): void
    {
        $this->defaultTplPath = \dirname(__DIR__, 2) . '/res/templates/';
    }

    /**
     * Generate CLI command controller class
     *
     * @CommandMapping(alias="cmd", example="
     * <info>{fullCommand} demo</info>     Gen DemoCommand class to `@app/Command`
     * ")
     *
     * @CommandArgument("name", desc="The class name, don't need suffix and ext. eg: <info>demo</info>")
     * @CommandArgument("dir", desc="The class file save dir. default: <info>@app/Command</info>")
     *
     * @CommandOption("suffix", type="string", desc="The class name suffix", default="Command")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="command.stub")
     * @param Input  $in
     * @param Output $out
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    public function command(Input $in, Output $out): int
    {
        [$config, $data] = $this->collectInfo($in, $out, [
            'suffix'      => 'Command',
            'namespace'   => 'App\\Command',
            'tplFilename' => 'command',
        ]);

        $data['commandVar'] = '{command}';

        return $this->writeFile('@app/Commands', $data, $config, $out);
    }

    /**
     * Generate HTTP controller class
     * @CommandMapping(alias="ctrl", example="
     * <info>{fullCommand} demo --prefix /demo -y</info>          Gen DemoController class to `@app/Controller`
     * <info>{fullCommand} user --prefix /users --rest</info>     Gen UserController class to `@app/Controller`(RESTFul type)
     * ")
     *
     * @CommandArgument("name", desc="The class name, don't need suffix and ext. eg: <info>demo</info>")
     * @CommandArgument("dir", desc="The class file save dir. default: <info>@app/Controller</info>")
     *
     * @CommandOption("rest", type="string", desc="The class will contains CURD actions", default=false)
     * @CommandOption("prefix", type="string", desc="The route prefix for the controller, default is class name", default="string")
     * @CommandOption("suffix", type="string", desc="The class name suffix", default="Command")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="command.stub")
     *
     * @return int
     * @param Input  $in
     * @param Output $out
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    public function controller(Input $in, Output $out): int
    {
        [$config, $data] = $this->collectInfo($in, $out, [
            'suffix'      => 'Controller',
            'namespace'   => 'App\\Controller',
            'tplFilename' => 'controller',
        ]);

        $data['prefix'] = $in->getOpt('prefix') ?: '/' . $data['name'];
        $data['idVar']  = '{id}';

        if ($in->getOpt('rest', false)) {
            $config['tplFilename'] = 'controller-rest';
        }

        return $this->writeFile('@app/Controller', $data, $config, $out);
    }

    /**
     * Generate WebSocket controller class
     * @Usage {fullCommand} CLASS_NAME SAVE_DIR [--option ...]
     * @Arguments
     *   name       The class name, don't need suffix and ext.(eg. <info>demo</info>)
     *   dir        The class file save dir(default: <info>@app/WebSocket</info>)
     * @Options
     *   -y, --yes BOOL             No need to confirm when performing file writing. default is: <info>False</info>
     *   -o, --override BOOL        Force override exists file. default is: <info>False</info>
     *   -n, --namespace STRING     The class namespace. default is: <info>App\WebSocket</info>
     *   --prefix STRING            The route path for the controller. default is class name
     *   --suffix STRING            The class name suffix. default is: <info>Controller</info>
     *   --tpl-file STRING          The template file name. default is: <info>ws-controller.stub</info>
     *   --tpl-dir STRING           The template file dir path.(default: devtool/res/templates)
     * @Example
     *   <info>{fullCommand} echo --prefix /echo -y</info>         Gen EchoController class to `@app/WebSocket`
     *   <info>{fullCommand} chat --prefix /chat</info>     Gen ChatController class to `@app/WebSocket`
     * @return int
     * @param Input  $in
     * @param Output $out
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    public function websocket(Input $in, Output $out): int
    {
        [$config, $data] = $this->collectInfo($in, $out, [
            'suffix'      => 'Controller',
            'namespace'   => 'App\\WebSocket',
            'tplFilename' => 'ws-controller',
        ]);

        $data['prefix'] = $in->getOpt('prefix') ?: '/' . $data['name'];

        return $this->writeFile('@app/WebSocket', $data, $config, $out);
    }

    /**
     * Generate RPC service class
     * @return int
     */
    public function rpcService(): int
    {
        \output()->writeln('un-completed ...');
        return 0;
    }

    /**
     * Generate an event listener class
     * @Usage {fullCommand} CLASS_NAME SAVE_DIR [--option ...]
     * @Arguments
     *   name       The class name, don't need suffix and ext.(eg. <info>demo</info>)
     *   dir        The class file save dir(default: <info>@app/Listener</info>)
     * @Options
     *   -y, --yes BOOL             No need to confirm when performing file writing. default is: <info>False</info>
     *   -o, --override BOOL        Force override exists file. default is: <info>False</info>
     *   -n, --namespace STRING     The class namespace. default is: <info>App\Listener</info>
     *   --suffix STRING            The class name suffix. default is: <info>Listener</info>
     *   --tpl-file STRING          The template file name. default is: <info>listener.stub</info>
     *   --tpl-dir STRING           The template file dir path.(default: devtool/res/templates)
     * @Example
     *   <info>{fullCommand} demo</info>     Gen DemoListener class to `@app/Listener`
     * @param Input  $in
     * @param Output $out
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    public function listener(Input $in, Output $out): int
    {
        list($config, $data) = $this->collectInfo($in, $out, [
            'suffix'      => 'Listener',
            'namespace'   => 'App\\Listener',
            'tplFilename' => 'listener',
        ]);

        return $this->writeFile('@app/Listener', $data, $config, $out);
    }

    /**
     * Generate HTTP middleware class
     * @Usage {fullCommand} CLASS_NAME SAVE_DIR [--option ...]
     * @Arguments
     *   name       The class name, don't need suffix and ext.(eg. <info>demo</info>)
     *   dir        The class file save dir(default: <info>@app/Middlewares</info>)
     * @Options
     *   -y, --yes BOOL             No need to confirm when performing file writing. default is: <info>False</info>
     *   -o, --override BOOL        Force override exists file. default is: <info>False</info>
     *   -n, --namespace STRING     The class namespace. default is: <info>App\Middlewares</info>
     *   --suffix STRING            The class name suffix. default is: <info>Middleware</info>
     *   --tpl-file STRING          The template file name. default is: <info>middleware.stub</info>
     *   --tpl-dir STRING           The template file dir path.(default: devtool/res/templates)
     * @Example
     *   <info>{fullCommand} demo</info>     Gen DemoMiddleware class to `@app/Middlewares`
     * @param Input  $in
     * @param Output $out
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    public function middleware(Input $in, Output $out): int
    {
        list($config, $data) = $this->collectInfo($in, $out, [
            'suffix'      => 'Middleware',
            'namespace'   => 'App\\Middlewares',
            'tplFilename' => 'middleware',
        ]);

        return $this->writeFile('@app/Middlewares', $data, $config, $out);
    }

    /**
     * Generate user task class
     * @Usage {fullCommand} CLASS_NAME SAVE_DIR [--option ...]
     * @Arguments
     *   name       The class name, don't need suffix and ext.(eg. <info>demo</info>)
     *   dir        The class file save dir(default: <info>@app/Tasks</info>)
     * @Options
     *   -y, --yes BOOL             No need to confirm when performing file writing. default is: <info>False</info>
     *   -o, --override BOOL        Force override exists file. default is: <info>False</info>
     *   -n, --namespace STRING     The class namespace. default is: <info>App\Tasks</info>
     *   --suffix STRING            The class name suffix. default is: <info>Task</info>
     *   --tpl-file STRING          The template file name. default is: <info>task.stub</info>
     *   --tpl-dir STRING           The template file dir path.(default: devtool/res/templates)
     * @Example
     *   <info>{fullCommand} demo</info>     Gen DemoTask class to `@app/Tasks`
     * @param Input  $in
     * @param Output $out
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    public function task(Input $in, Output $out): int
    {
        list($config, $data) = $this->collectInfo($in, $out, [
            'suffix'      => 'Task',
            'namespace'   => 'App\\Tasks',
            'tplFilename' => 'task',
        ]);

        return $this->writeFile('@app/Tasks', $data, $config, $out);
    }

    /**
     * Generate user custom process class
     * @Usage {fullCommand} CLASS_NAME SAVE_DIR [--option ...]
     * @Arguments
     *   name       The class name, don't need suffix and ext.(eg. <info>demo</info>)
     *   dir        The class file save dir(default: <info>@app/Process</info>)
     * @Options
     *   -y, --yes BOOL             No need to confirm when performing file writing. default is: <info>False</info>
     *   -o, --override BOOL        Force override exists file. default is: <info>False</info>
     *   -n, --namespace STRING     The class namespace. default is: <info>App\Process</info>
     *   --suffix STRING            The class name suffix. default is: <info>Process</info>
     *   --tpl-file STRING          The template file name. default is: <info>process.stub</info>
     *   --tpl-dir STRING           The template file dir path.(default: devtool/res/templates)
     * @Example
     *   <info>{fullCommand} demo</info>     Gen DemoProcess class to `@app/Process`
     * @param Input  $in
     * @param Output $out
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    public function process(Input $in, Output $out): int
    {
        [$config, $data] = $this->collectInfo($in, $out, [
            'suffix'      => 'Process',
            'namespace'   => 'App\\Process',
            'tplFilename' => 'process',
        ]);

        return $this->writeFile('@app/Process', $data, $config, $out);
    }


    /**
     * Generate entity class
     * @Usage {fullCommand} -d test [--option ...]
     *
     * @Options
     *   -d, --database STRING      Must to set database. `,` symbol is used  to separated by multiple databases
     *   -i, --include STRING       Set the included tables, `,` symbol is used  to separated by multiple tables. default is: <info>all tables</info>
     *   -e, --exclude STRING       Set the excluded tables, `,` symbol is used  to separated by multiple tables. default is: <info>empty</info>
     *   -p, --path STRING          Specified entity generation path, default is: <info>@app/Models/Entity</info>
     *   --driver STRING            Specify database driver(mysql/pgsql/mongodb), default is: <info>mysql</info>
     *   --table-prefix STRING      Specify the table prefix that needs to be removed, default is: <info>empty</info>
     *   --field-prefix STRING      Specify the field prefix that needs to be removed, default is: <info>empty</info>
     *   --tpl-file STRING          The template file name. default is: <info>entity.stub</info>
     *   --tpl-dir STRING           The template file dir path.(default: devtool/res/templates)
     * @Example
     *   <info>{fullCommand} -d test</info>     Gen DemoProcess class to `@app/Models/Entity`
     *
     * @param Input  $in
     * @param Output $out
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    public function entity(Input $in, Output $out): void
    {
        $params = [
            'test',
            '',
            '',
            '@app/Models/Entity',
            'mysql',
            '',
            '',
            'entity',
            $this->defaultTplPath
        ];

        /* @var EntityLogic $logic */
        $logic = \bean(EntityLogic::class);
        $logic->generate($params);
    }

    /**
     * @param Input  $in
     * @param Output $out
     * @param array  $defaults
     * @return array
     */
    private function collectInfo(Input $in, Output $out, array $defaults = []): array
    {
        $config = [
            'tplFilename' => $in->getOpt('tpl-file') ?: $defaults['tplFilename'],
            'tplDir'      => $in->getOpt('tpl-dir') ?: $this->defaultTplPath,
        ];

        if (!$name = $in->getArg(0)) {
            $name = $in->read('Please input class name(no suffix and ext. eg. test): ');
        }

        if (!$name) {
            $out->writeln('<error>No class name input! Quit</error>', true, 1);
        }

        $sfx  = $in->getOpt('suffix') ?: $defaults['suffix'];
        $data = [
            'name'      => $name,
            'suffix'    => $sfx,
            'namespace' => $in->sameOpt(['n', 'namespace']) ?: $defaults['namespace'],
            'className' => \ucfirst($name) . $sfx,
        ];

        return [$config, $data];
    }

    /**
     * @param string $defaultDir
     * @param array  $data
     * @param array  $config
     * @param Output $out
     * @return int
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    private function writeFile(string $defaultDir, array $data, array $config, Output $out): int
    {
        // $out->writeln("Some Info: \n" . \json_encode($config, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
        $out->writeln("Class data: \n" . \json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));

        if (!$saveDir = \input()->getArg(1)) {
            $saveDir = $defaultDir;
        }

        $file = \Swoft::getAlias($saveDir) . '/' . $data['className'] . '.php';

        $out->writeln("Target File: <info>$file</info>\n");

        if (\file_exists($file)) {
            $override = \input()->sameOpt(['o', 'override']);

            if (null === $override) {
                if (!Interact::confirm('Target file has been exists, override?', false)) {
                    $out->writeln(' Quit, Bye!');

                    return 0;
                }
            } elseif (!$override) {
                $out->writeln(' Quit, Bye!');

                return 0;
            }
        }

        $yes = \input()->sameOpt(['y', 'yes'], false);

        if (!$yes && !Interact::confirm('Now, will write content to file, ensure continue?')) {
            $out->writeln(' Quit, Bye!');

            return 0;
        }

        $ger = new FileGenerator($config);

        if ($ok = $ger->renderAs($file, $data)) {
            $out->writeln('<success>OK, write successful!</success>');
        } else {
            $out->writeln('<error>NO, write failed!</error>');
        }

        return 0;
    }
}
