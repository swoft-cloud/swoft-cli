# swoft cli

🛠️ CLI tool application for quick use swoft.

- Quick create new application skeleton
- Quick create new component skeleton
- Monitor file changes of the user's swoft project and automatically restart the server
- Generate swoft class: http controller, ws controller, model and more class
- More features ...

> Documents on https://www.swoft.org/docs/2.x/zh-CN/tool/swoftcli/index.html

## Install

Download phar from github releases page

> Notice: please replace the `VERSION` to specified version

```bash
wget https://github.com/swoft-cloud/swoft-cli/releases/download/VERSION/swoftcli.phar

# quick check
php swoftcli.phar -V
php swoftcli.phar -h
```

Add to global ENV PATH:

```bash
# move to ENV path:
mv swoftcli.phar /user/local/bin/swoftcli
chmod a+x /user/local/bin/swoftcli

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
