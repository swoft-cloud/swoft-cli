# ref https://gist.github.com/inhere/c98df2b096ee3ccc3d36ec61923c9fc9
.DEFAULT_GOAL := help
.PHONY: all usage help clean

##There are some make command for the project
##

TAG=$(tag)

help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | sed -e 's/: / /'

##Available Commands:

  phar:		## Pack the project codes to swoftcli.phar package
phar:
	php -d phar.readonly=0 bin/swoftcli phar:pack -o=swoftcli.phar

  release:	## Release swoftcli to new tag and push to remote. eg: tag=v2.0.3
release:
	git tag -a $(TAG) -m "Release $(TAG)"
	git push origin $(TAG)

  sami:		## Gen classes docs by sami.phar
classdoc:
# rm -rf docs/classes-docs
	rm -rf docs/classes-docs
# gen docs
	php sami.phar update ./script/sami.doc.inc

  all:		## Run addrmt and spush and release
all: addrmt spush release

