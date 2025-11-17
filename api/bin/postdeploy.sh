#!/bin/sh

set -e
#HTTPD_USER=$(ps axo -o user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
HTTPD_USER=www-data
CONSOLE_USER=$(whoami)

options=$(getopt -o s -l skip-vault-fetch -n 'postdeploy' -- "$@")
eval set -- "$options"

skipVaultFetch=false
if [ "$1" = "--skip-vault-fetch" ] || [ "$1" = "-s" ]
 then
   skipVaultFetch=true
fi

chmod 700 -R .
mkdir -p var
setfacl -dR -m u:"$HTTPD_USER":rwX -m u:"$CONSOLE_USER":rwX var
setfacl -R -m u:"$HTTPD_USER":rwX -m u:"$CONSOLE_USER":rwX var
setfacl -R -m u:"$HTTPD_USER":rx -m u:"$CONSOLE_USER":rx .

COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --no-dev --classmap-authoritative
# This command outputs to STDERR
COMPOSER_ALLOW_SUPERUSER=1 composer dump-env prod 2>&1

bin/console cache:clear
setfacl -dR -m u:"$HTTPD_USER":rwX -m u:"$CONSOLE_USER":rwX var/cache

setfacl -R -m u:"$HTTPD_USER":rx -m u:"$CONSOLE_USER":rx .en*

kill -USR2 1
sleep 5

if ! $skipVaultFetch
  then echo "" > .env.local.php && rm .env.prod.local
fi

if ! $skipVaultFetch
  then bin/console doctrine:migrations:migrate --allow-no-migration --no-interaction
fi
