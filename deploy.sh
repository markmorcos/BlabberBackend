#!/bin/sh

sed -i "s#localhost:8055#myblabber.com/be-$1#g" apidoc.json
apidoc -i controllers/ -o web/apidoc

rsync -avq --exclude='*.zip .git' ./ ../public_html/be-$1-temp

cd ../public_html/

export COMPOSER_HOME="/home/blabber/composer"
composer global require "fxp/composer-asset-plugin:^1.2.0"
composer install -d be-$1-temp

sed -i "s#'/'#'/be-$1'#g" be-$1-temp/config/web.php
rm be-$1-temp/web/index.php
mv be-$1-temp/web/index-$1.php be-$1-temp/web/index.php
rm be-$1-temp/config/db.php
mv be-$1-temp/config/db-$1.php be-$1-temp/config/db.php

rsync -avq be-$1/web/uploads be-$1-temp/web

find be-$1-temp/* -type d -print0 | xargs -0 chmod 0755
find be-$1-temp/* -type f -print0 | xargs -0 chmod 0644
chmod -R 777 be-$1-temp/assets be-$1-temp/runtime be-$1-temp/web/uploads

rm -R be-$1
mv be-$1-temp be-$1