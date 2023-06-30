#!/bin/sh
set -e
GCP_SECRET_MANAGER_PROJECT_ID="basic-388908"
#HTTPD_USER=$(ps axo -o user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
HTTPD_USER=www-data
CONSOLE_USER=$(whoami)

chmod 700 -R .
mkdir -p var
setfacl -dR -m u:"$HTTPD_USER":rwX -m u:"$CONSOLE_USER":rwX var
setfacl -R -m u:"$HTTPD_USER":rwX -m u:"$CONSOLE_USER":rwX var
setfacl -R -m u:"$HTTPD_USER":rx -m u:"$CONSOLE_USER":rx .

# Comment next line if you don't want to use GCP Secret Manager
#bin/console secrets:external:decrypt-to-file $GCP_SECRET_MANAGER_PROJECT_ID
COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --no-dev --classmap-authoritative
COMPOSER_ALLOW_SUPERUSER=1 composer dump-env prod
bin/console cache:warmup

setfacl -R -m u:"$HTTPD_USER":rx -m u:"$CONSOLE_USER":rx .en*
