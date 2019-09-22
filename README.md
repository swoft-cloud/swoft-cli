# Swoft CLI

[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/swoft-cloud/swoft-cli)](https://github.com/swoft-cloud/swoft-cli)
[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoft Doc](https://img.shields.io/badge/docs-passing-green.svg?maxAge=2592000)](https://www.swoft.org/docs)
[![Swoft License](https://img.shields.io/hexpm/l/plug.svg?maxAge=2592000)](https://github.com/swoft-cloud/swoft/blob/master/LICENSE)

🛠️ CLI tool application for quick use swoft.

- Quick create new application skeleton
- Quick create new component skeleton
- Monitor file changes of the user's swoft project and automatically restart the server
- Generate swoft class: http controller, http middleware, ws module, ws controller and more
- More features ...

> Documents on https://www.swoft.org/docs/2.x/zh-CN/tool/swoftcli/index.html

![home](swoftcli-home.png)

## Install

Download phar from github releases page

> Notice: please replace the `{VERSION}` to specified version

```bash
wget https://github.com/swoft-cloud/swoft-cli/releases/download/{VERSION}/swoftcli.phar

# quick check
php swoftcli.phar -V
php swoftcli.phar -h
```

Add to global ENV PATH:

```bash
# move to ENV path:
mv swoftcli.phar /usr/local/bin/swoftcli
chmod a+x /usr/local/bin/swoftcli

# check
swoftcli -V
```

## Build

You can build package from latest code:

```bash
php -d phar.readonly=0 bin/swoftcli phar:pack -o=swoftcli.phar
```

## License

[Apache 2.0](LICENSE)
