FROM php:8.2-fpm-alpine as base

LABEL maintainer="Petr Zivny (petr.zivny@gmail.com)"

# fcgi is needed for php-fpm-healthcheck.sh
RUN apk add --no-cache git acl fcgi

# PDO PgSQL
# Driver to enable access from PHP to Postgres databases. Implements PDO (doesn't need it).
RUN apk add --no-cache libpq-dev && docker-php-ext-install -j$(nproc) pdo_pgsql

# Install opcache extension
RUN docker-php-ext-install -j$(nproc) opcache && docker-php-ext-enable opcache

# Install bcmath extension
RUN docker-php-ext-install -j$(nproc) bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer;

COPY .docker/php/profile.d/aliases.sh /etc/profile.d/
COPY .docker/php/conf.d/docker.conf /usr/local/etc/php-fpm.d/docker.conf

# Needed for php-fpm-healthcheck.sh which is used for k8 readinessProbe and livenessProbe
RUN set -xe && echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf

ARG symfony_mode
RUN if [ "$symfony_mode" = "dev" ]; \
    then cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini; \
    else cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini; \
    fi


FROM base as local
# Xdebug
COPY .docker/php/conf.d/ext-xdebug.ini /usr/local/etc/php/conf.d/ext-xdebug.ini
RUN apk --no-cache add --virtual .build-dependencies $PHPIZE_DEPS linux-headers && \
    pecl install xdebug                                                         && \
    docker-php-ext-enable xdebug                                                && \
    apk del .build-dependencies && rm -rf /tmp/*


FROM base as deployment
COPY api/composer.lock api/composer.json ./

RUN if [ "$symfony_mode" = "dev" ]; \
    then composer i --no-progress --no-scripts --ansi && composer clear-cache; \
    else composer i --no-dev --no-progress --no-scripts --ansi && composer clear-cache; \
    fi

RUN echo "opcache.preload=/var/www/html/config/preload.php" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
RUN echo "opcache.preload_user=www-data" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
# Add JIT var_dump(opcache_get_status()['jit']); https://stitcher.io/blog/php-8-jit-setup


COPY api/bin bin
COPY api/config config
COPY api/migrations migrations
COPY api/public public
COPY api/src src
COPY api/.env .


FROM deployment as ci-pipeline-test
COPY api/.env.test .
COPY api/phpcs.xml.dist .
COPY api/phpstan.neon .
COPY api/phpunit.xml.dist .
COPY api/psalm.xml .
COPY api/tests tests

RUN echo "APP_ENV=test" > .env.local
RUN bin/console cache:clear



