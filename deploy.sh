export COMPOSER_HOME="/home/blabber/composer"
composer global require "fxp/composer-asset-plugin:^1.2.0"
composer install
php yii migrate
apidoc -i controllers/ -o web/apidoc
