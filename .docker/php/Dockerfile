FROM php:8.2-fpm-alpine as base

LABEL maintainer="Petr Zivny | Totea (zivny@totea.cz)"

# PDO MySQL
# Driver to enable access from PHP to MySQL databases. Implements PDO (doesn't need it). $dbh = new PDO($dsn, $user, $password);
RUN apk update                                                          && \
    docker-php-ext-install -j"$(getconf _NPROCESSORS_ONLN)" pdo_mysql   && \
    rm -rf /var/cache/apk/*

ARG environment=development

RUN apk add git                                                          && \
    rm -rf /var/cache/apk/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer;

# Xdebug
COPY .docker/php/conf.d/ext-xdebug.ini /usr/local/etc/php/conf.d/ext-xdebug.ini
RUN apk --update add --virtual .build-dependencies $PHPIZE_DEPS linux-headers && \
    pecl install xdebug                                                       && \
    docker-php-ext-enable xdebug                                              && \
    apk del .build-dependencies                                               && \
    rm -rf /tmp/* /var/cache/apk/*

## Install mbstring extension
#RUN apk add --no-cache \
#    oniguruma-dev \
#    && docker-php-ext-install mbstring \
#    && docker-php-ext-enable mbstring \
#    && rm -rf /tmp/*

# Install opcache extension
RUN apk add --no-cache \
    && docker-php-ext-install -j$(nproc) opcache \
    && docker-php-ext-enable opcache \
    && rm -rf /tmp/*

RUN cp /usr/local/etc/php/php.ini-${environment} /usr/local/etc/php/php.ini

# Only for PROD
#RUN echo "opcache.preload=/var/www/html/var/cache/prod/App_KernelProdContainer.preload.php" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
#RUN echo "opcache.preload_user=www-data" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
# Add JIT var_dump(opcache_get_status()['jit']); https://stitcher.io/blog/php-8-jit-setup

COPY .docker/php/profile.d/aliases.sh /etc/profile.d/

CMD ["php-fpm"]
