# Makefile for building the project

app_name=twofactor_totp
project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
sign_dir=$(build_dir)/sign
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
package_name=$(app_name)
cert_dir=$(HOME)/.nextcloud/certificates

all: appstore

clean:
	rm -rf $(build_dir)
	rm -rf vendor

composer.phar:
	curl -sS https://getcomposer.org/installer | php

install-deps: install-composer-deps

install-composer-deps: composer.phar
	php composer.phar install

update-composer: composer.phar
	rm -f composer.lock
	php composer.phar install --prefer-dist

appstore: clean install-deps
	mkdir -p $(sign_dir)
	rsync -av \
	--exclude=.git \
	--exclude=build \
	--exclude=.gitignore \
	--exclude=.travis.yml \
	--exclude=.scrutinizer.yml \
        --exclude=CONTRIBUTING.md \
	--exclude=composer.json \
	--exclude=composer.lock \
	--exclude=composer.phar \
	--exclude=l10n/.tx \
	--exclude=l10n/no-php \
	--exclude=Makefile \
	--exclude=nbproject \
	--exclude=screenshots \
	--exclude=phpunit*xml \
	--exclude=tests \
	--exclude=vendor/bin \
	$(project_dir) $(sign_dir)
	@echo "Signing…"
	php ../../occ integrity:sign-app \
		--privateKey=$(cert_dir)/$(app_name).key\
		--certificate=$(cert_dir)/$(app_name).crt\
		--path=$(sign_dir)/$(app_name)
	openssl dgst -sha512 -sign $(cert_dir)/$(app_name).key $(sign_dir)/$(app_name).tar.gz | openssl base64
	tar -cvzf $(build_dir)/$(app_name).tar.gz \
		-C $(sign_dir) $(app_name)

