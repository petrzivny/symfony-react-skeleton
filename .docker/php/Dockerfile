FROM php:8.2-fpm-alpine as base

LABEL maintainer="Petr Zivny | Totea (zivny@totea.cz)"

# PDO MySQL
# Driver to enable access from PHP to MySQL databases. Implements PDO (doesn't need it). $dbh = new PDO($dsn, $user, $password);
RUN apk update                                                          && \
    docker-php-ext-install -j"$(getconf _NPROCESSORS_ONLN)" pdo_mysql   && \
    rm -rf /var/cache/apk/*

RUN apk add git                                                          && \
    rm -rf /var/cache/apk/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer;

# Install opcache extension
RUN apk add --no-cache \
    && docker-php-ext-install -j$(nproc) opcache \
    && docker-php-ext-enable opcache \
    && rm -rf /tmp/*

COPY .docker/php/profile.d/aliases.sh /etc/profile.d/

ARG symfony_mode
RUN if [ "$symfony_mode" = "dev" ]; \
    then cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini; \
    else cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini; \
    fi

COPY .docker/php/conf.d/docker.conf /usr/local/etc/php-fpm.d/docker.conf


FROM base as local
# Xdebug
COPY .docker/php/conf.d/ext-xdebug.ini /usr/local/etc/php/conf.d/ext-xdebug.ini
RUN apk --update add --virtual .build-dependencies $PHPIZE_DEPS linux-headers && \
    pecl install xdebug                                                       && \
    docker-php-ext-enable xdebug                                              && \
    apk del .build-dependencies                                               && \
    rm -rf /tmp/* /var/cache/apk/*


FROM base as deployment
COPY api/composer.lock api/composer.json ./

RUN if [ "$symfony_mode" = "dev" ]; \
    then composer i --no-progress --no-scripts --ansi && composer clear-cache; \
    else composer i --no-dev --no-progress --no-scripts --ansi && composer clear-cache; \
    fi

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

RUN echo "APP_ENV=test" > .env
RUN bin/console cache:clear



# Only for PROD after deploy
#RUN if [ "$symfony_mode" = "prod" ]; then  \
#      COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --no-dev --classmap-authoritative; \
#      COMPOSER_ALLOW_SUPERUSER=1 composer dump-env prod; \
#    fi
#
#RUN bin/console cache:clear --env $symfony_mode

#RUN echo "opcache.preload=/var/www/html/var/cache/prod/App_KernelProdContainer.preload.php" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
#RUN echo "opcache.preload_user=www-data" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
# Add JIT var_dump(opcache_get_status()['jit']); https://stitcher.io/blog/php-8-jit-setup
