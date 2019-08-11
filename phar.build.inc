<?php
/**
 * The is a config file for compile phar package.
 * User: Inhere
 * Date: 2018/1/26 22:11
 * @var \Swoft\Console\Advanced\PharCompiler $compiler
 */

// config
$compiler
    ->stripComments(true)
    ->setShebang(true)
    ->addSuffix(['.tpl', '.json', '.stub'])
    ->addExcludeDir([
        'test',
        'doc',
        'docs',
        'tests',
        'Tests',
        'example',
        'runtime',
        'swoole-ide-helper',
    ])
    ->addFile([
        // 'LICENSE',
        'composer.json',
        'README.md',
        'bin/bootstrap.php',
    ])
    ->setCliIndex('bin/swoftcli')
    // ->setWebIndex('web/index.php')
    // ->setVersionFile('config/config.php')
;

$stripDirs = [
    'psr/',
    'nikic/',
    'monolog/',
    'symfony/',
    'toolkit/',
];

// Swoft 相关都不能去除注释
$compiler->setStripFilter(function ($file) use ($stripDirs) {
    /** @var \SplFileInfo $file */
    $path = $file->getPathname();

    foreach ($stripDirs as $dir) {
        if (\strpos($path, $dir)) {
            return true;
        }
    }

    return false;
});
