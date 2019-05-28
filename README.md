# swoft cli

🛠️ CLI tool application for quick use swoft.

- Quick create new application skeleton
- Quick create new component skeleton
- Monitor file changes of the user's swoft project and automatically restart the server
- Generate swoft class: http controller, ws controller, model and more class
- More features ...

## Install

```bash
wget https://github.com/swoft-cloud/swoft-cli/releases/download/{VERSION}/swoftcli.phar
# Move to ENV path:
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
