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

RUN cp /usr/local/etc/php/php.ini-${environment} /usr/local/etc/php/php.ini

COPY .docker/php/profile.d/aliases.sh /etc/profile.d/

CMD ["php-fpm"]
