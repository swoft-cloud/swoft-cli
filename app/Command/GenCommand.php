<?php declare(strict_types=1);

namespace Swoft\Cli\Command;

use InvalidArgumentException;
use Leuffen\TextTemplate\TemplateParsingException;
use ReflectionException;
use RuntimeException;
use Swoft;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Cli\Bean\FileGenerator;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandArgument;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Console\Helper\Interact;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use function bean;
use function dirname;
use function file_exists;
use function json_encode;
use function ucfirst;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

/**
 * Generate some common application template classes
 *
 * @Command(alias="generate")
 *
 * @CommandOption("yes", short="y", desc="No need to confirm when performing file writing", default=false, type="bool")
 * @CommandOption("override", short="o", desc="Force override exists file", default=false, type="bool")
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
        $this->defaultTplPath = dirname(__DIR__, 2) . '/res/templates/';
    }

    /**
     * Generate CLI command controller class
     *
     * @CommandMapping(alias="cmd")
     *
     * @CommandArgument("name", desc="The class name, don't need suffix and ext. eg: <info>demo</info>")
     * @CommandArgument("dir", desc="The class file save dir", default="@app/Command")
     *
     * @CommandOption("suffix", type="string", desc="The class name suffix", default="Command")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="command.stub")
     * @param Input  $in
     * @param Output $out
     *
     * @return int
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws TemplateParsingException
     * @example <info>{fullCommand} demo</info>     Gen DemoCommand class to command dir
     *
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
     *
     * @CommandMapping(alias="ctrl")
     *
     * @CommandArgument("name", desc="The class name, don't need suffix and ext. eg: <info>demo</info>")
     * @CommandArgument("dir", desc="The class file save dir", default="@app/Http/Controller")
     *
     * @CommandOption("rest", type="string", desc="The class will contains CURD actions", default=false)
     * @CommandOption("prefix", type="string", desc="The route prefix for the controller, default is class name", default="string")
     * @CommandOption("suffix", type="string", desc="The class name suffix", default="Command")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="command.stub")
     *
     * @param Input  $in
     * @param Output $out
     *
     * @return int
     *
     * @return int
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws TemplateParsingException
     * @example
     *   <info>{fullCommand} demo --prefix /demo -y</info>      Gen DemoController class to http controller dir
     *   <info>{fullCommand} user --prefix /users --rest</info> Gen UserController class to http controller dir(RESTFul)
     *
     */
    public function controller(Input $in, Output $out): int
    {
        [$config, $data] = $this->collectInfo($in, $out, [
            'suffix'      => 'Controller',
            'namespace'   => 'App\\Http\\Controller',
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
     * Generate WebSocket module/controller class
     * @CommandMapping(alias="ws")
     *
     * @CommandArgument("name", desc="The class name, don't need suffix and ext. eg: <info>demo</info>")
     * @CommandArgument("dir", desc="The class file save dir", default="@app/WebSocket")
     *
     * @CommandOption("prefix", type="string", desc="The route prefix for the websocket, default is class name", default="string")
     * @CommandOption("suffix", type="string", desc="The class name suffix", default="Command")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="ws-module.stub")
     * @param Input  $in
     * @param Output $out
     *
     * @return int
     * @return int
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws TemplateParsingException
     * @example
     * <info>{fullCommand} echo --prefix /echo -y</info>   Gen EchoController class to WebSocket dir
     * <info>{fullCommand} chat --prefix /chat</info>      Gen ChatController class to WebSocket dir
     *
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
     * @CommandMapping(alias="rpc-ctrl")
     *
     * @return int
     */
    public function rpcController(): int
    {
        \output()->writeln('un-completed ...');
        return 0;
    }

    /**
     * Generate an event listener class
     * @CommandMapping()
     *
     * @CommandArgument("name", desc="The class name, don't need suffix and ext. eg: <info>demo</info>")
     * @CommandArgument("dir", desc="The class file save dir", default="@app/Listener")
     *
     * @CommandOption("suffix", type="string", desc="The class name suffix", default="Command")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="listener.stub")
     * @param Input  $in
     * @param Output $out
     *
     * @return int
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws TemplateParsingException
     * @example <info>{fullCommand} demo</info>     Gen DemoListener class to Listener dir
     *
     */
    public function listener(Input $in, Output $out): int
    {
        [$config, $data] = $this->collectInfo($in, $out, [
            'suffix'      => 'Listener',
            'namespace'   => 'App\\Listener',
            'tplFilename' => 'listener',
        ]);

        return $this->writeFile('@app/Listener', $data, $config, $out);
    }

    /**
     * Generate HTTP middleware class
     * @CommandMapping(alias="middle")
     *
     * @CommandArgument("name", desc="The class name, don't need suffix and ext. eg: <info>demo</info>")
     * @CommandArgument("dir", desc="The class file save dir", default="@app/Middleware")
     *
     * @CommandOption("suffix", type="string", desc="The class name suffix", default="Middleware")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="middleware.stub")
     * @param Input  $in
     * @param Output $out
     *
     * @return int
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws TemplateParsingException
     * @example
     * <info>{fullCommand} demo</info>     Gen DemoMiddleware class to Middleware dir
     *
     */
    public function middleware(Input $in, Output $out): int
    {
        [$config, $data] = $this->collectInfo($in, $out, [
            'suffix'      => 'Middleware',
            'namespace'   => 'App\\Http\\Middleware',
            'tplFilename' => 'middleware',
        ]);

        return $this->writeFile('@app/Middleware', $data, $config, $out);
    }

    /**
     * Generate user task class
     * @CommandMapping()
     *
     * @CommandArgument("name", desc="The class name, don't need suffix and ext. eg: <info>demo</info>")
     * @CommandArgument("dir", desc="The class file save dir", default="@app/Task")
     *
     * @CommandOption("suffix", type="string", desc="The class name suffix", default="Task")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="task.stub")
     * @param Input  $in
     * @param Output $out
     *
     * @return int
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws TemplateParsingException
     * @example
     * <info>{fullCommand} demo</info>     Gen DemoTask class to Task dir
     *
     */
    public function task(Input $in, Output $out): int
    {
        [$config, $data] = $this->collectInfo($in, $out, [
            'suffix'      => 'Task',
            'namespace'   => 'App\\Task',
            'tplFilename' => 'task',
        ]);

        return $this->writeFile('@app/Task', $data, $config, $out);
    }

    /**
     * Generate user custom process class
     *
     * @CommandMapping()
     *
     * @CommandArgument("name", desc="The class name, don't need suffix and ext. eg: <info>demo</info>")
     * @CommandArgument("dir", desc="The class file save dir", default="@app/Process")
     *
     * @CommandOption("suffix", type="string", desc="The class name suffix", default="Process")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="process.stub")
     *
     * @param Input  $in
     * @param Output $out
     *
     * @return int
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws TemplateParsingException
     * @example
     * <info>{fullCommand} demo</info>     Gen DemoProcess class to Process dir
     *
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
     * Generate entity class by database table schema
     * @CommandMapping()
     *
     * @CommandOption("database", short="d", desc="Must to set database. `,` symbol is used  to separated by multi databases", mode=Command::OPT_REQUIRED)
     * @CommandOption("include", short="i", desc="Set the included tables, `,` symbol is used  to separated by multiple tables", default="all tables")
     * @CommandOption("exclude", short="e", desc="Set the excluded tables, `,` symbol is used  to separated by multiple tables")
     * @CommandOption("path", short="p", desc="Specified entity generation path", default="@app/Model/Entity")
     * @CommandOption("driver", desc="Specify database driver(mysql/pgsql/mongodb)", default="mysql", type="string")
     * @CommandOption("table-prefix", desc="Specify the table prefix that needs to be removed", type="string")
     * @CommandOption("field-prefix", desc="Specify the field prefix that needs to be removed", type="string")
     * @CommandOption("tpl-file", type="string", desc="The template file dir path", default="entity.stub")
     *
     * @param Input  $in
     * @param Output $out
     *
     * @throws ReflectionException
     * @throws ContainerException
     * @example
     * <info>{fullCommand} -d test</info>     Gen DemoProcess class to Model/Entity
     */
    public function entity(Input $in, Output $out): void
    {
        $params = [
            'test',
            '',
            '',
            '@app/Model/Entity',
            'mysql',
            '',
            '',
            'entity',
            $this->defaultTplPath
        ];

        /* @var EntityLogic $logic */
        $logic = bean(EntityLogic::class);
        $logic->generate($params);
    }

    /**
     * @param Input  $in
     * @param Output $out
     * @param array  $defaults
     *
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
            $out->writeln('<error>No class name input! Quit</error>', true);
        }

        $sfx  = $in->getOpt('suffix') ?: $defaults['suffix'];
        $data = [
            'name'      => $name,
            'suffix'    => $sfx,
            'namespace' => $in->sameOpt(['n', 'namespace']) ?: $defaults['namespace'],
            'className' => ucfirst($name) . $sfx,
        ];

        return [$config, $data];
    }

    /**
     * @param string $defaultDir
     * @param array  $data
     * @param array  $config
     * @param Output $out
     *
     * @return int
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws TemplateParsingException
     */
    private function writeFile(string $defaultDir, array $data, array $config, Output $out): int
    {
        // $out->writeln("Some Info: \n" . \json_encode($config, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));
        $out->writeln("Class data: \n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if (!$saveDir = \input()->getArg(1)) {
            $saveDir = $defaultDir;
        }

        $file = Swoft::getAlias($saveDir) . '/' . $data['className'] . '.php';

        $out->writeln("Target File: <info>$file</info>\n");

        if (file_exists($file)) {
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
