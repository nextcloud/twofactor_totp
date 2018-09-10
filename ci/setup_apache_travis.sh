#!/bin/bash

set -ev

#######################################################################
# Based on https://docs.travis-ci.com/user/languages/php/#apache--php #
#######################################################################

sudo apt-get update
sudo apt-get install apache2 libapache2-mod-fastcgi

# enable php-fpm
phpenv version-name
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf
sudo a2enmod rewrite actions fastcgi alias
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
sudo sed -i -e "s,www-data,travis,g" /etc/apache2/envvars
sudo sed -i -e "s,80,8080,g" /etc/apache2/ports.conf
sudo chown -R travis:travis /var/lib/apache2/fastcgi
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

# configure apache virtual hosts
sudo cp -f ci/travis-ci-apache /etc/apache2/sites-available/000-default.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(readlink -f $TRAVIS_BUILD_DIR/../core)?g" --in-place /etc/apache2/sites-available/000-default.conf
cat /etc/apache2/sites-available/000-default.conf
sudo service apache2 restart
