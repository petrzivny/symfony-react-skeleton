#!/bin/sh

set -e
HTTPD_USER=www-data
CONSOLE_USER=$(whoami)

chmod 700 -R .
setfacl -R -m u:"$HTTPD_USER":rx -m u:"$CONSOLE_USER":rx .

mkdir -p var

COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --no-dev --classmap-authoritative
# This command outputs to STDERR
COMPOSER_ALLOW_SUPERUSER=1 composer dump-env prod 2>&1

bin/console cache:clear
setfacl -R -m u:www-data:rwX -m u:"$CONSOLE_USER":rwX var
setfacl -dR -m u:www-data:rwX -m u:"$CONSOLE_USER":rwX var

bin/console doctrine:migrations:migrate --allow-no-migration --no-interaction
