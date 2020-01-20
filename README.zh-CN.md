# Swoft CLI

[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/swoft-cloud/swoft-cli)](https://github.com/swoft-cloud/swoft-cli)
[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoft Doc](https://img.shields.io/badge/docs-passing-green.svg?maxAge=2592000)](https://www.swoft.org/docs)
[![Swoft License](https://img.shields.io/hexpm/l/plug.svg?maxAge=2592000)](https://github.com/swoft-cloud/swoft/blob/master/LICENSE)

> **[EN README](README.md)**

🛠️ Swoft CLI 是一个独立的命令行应用包，提供了一些内置的工具方便开发者使用。

- 生成Swoft应用类文件，例如：http控制器，websocket模块类等等
- 监视用户swoft项目的文件更改并自动重新启动服务器
- 快速创建新应用项目
- 快速创建新的组件包
- 将一个swoft应用打包成 phar 包

> swoft-cli 是基于 swoft 2.0 构建的应用，运行使用同样需要swoole

![home](swoftcli-home.png)

## 安装

安装 swoftcli 非常简单。我们提供已经打包好的phar放在GitHub上。

> 更多使用文档 on http://www.swoft.io/docs/2.x/zh-CN/tool/swoftcli/index

## 下载

你需要从 swoft-cli 的 [GitHub Releases](https://github.com/swoft-cloud/swoft-cli/releases) 下载打包好的 `swoftcli.phar`

> 注意：需要将下面命令里的 `{VERSION}` 替换为指定的版本。当然也你可以直接通过浏览器下载

```bash
wget https://github.com/swoft-cloud/swoft-cli/releases/download/{VERSION}/swoftcli.phar

# 检查包是否可用
php swoftcli.phar -V
php swoftcli.phar -h
```

## 全局使用

如果你需要在任何地方都可以直接使用 `swoftcli`:

```bash
# move to ENV path:
mv swoftcli.phar /usr/local/bin/swoftcli
chmod a+x /usr/local/bin/swoftcli

# check
swoftcli -V
```

## 构建

如果你需要从最新的代码构建phar包：

```bash
git clone https://github.com/swoft-cloud/swoft-cli
cd swoft-cli 
composer install

// build
php -d phar.readonly=0 bin/swoftcli phar:pack -o=swoftcli.phar
```

## License

[Apache 2.0](LICENSE)
