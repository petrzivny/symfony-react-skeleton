#!/bin/sh

# If you don't want fetch secrets from external GCP vault (e.g. in CI pipeline) use flag "./postdeploy.sh --skip-vault-fetch"

set -e
GCP_SECRET_MANAGER_PROJECT_ID="basic-388908"
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

if ! $skipVaultFetch
  then bin/console secrets:external:decrypt-to-file $GCP_SECRET_MANAGER_PROJECT_ID
fi

COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --no-dev --classmap-authoritative
COMPOSER_ALLOW_SUPERUSER=1 composer dump-env prod

bin/console cache:clear

setfacl -R -m u:"$HTTPD_USER":rx -m u:"$CONSOLE_USER":rx .en*

kill -USR2 1
sleep 2

if ! $skipVaultFetch
  then
    echo "" > .env.local.php
    rm .env .env.prod.local
fi
