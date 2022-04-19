ARG PHP_VERSION="${PHP_VERSION}"
FROM php:${PHP_VERSION}

RUN set -xe \
    && apt-get update -y \
    && apt-get install --no-install-recommends --assume-yes --quiet \
        git \
        nano \
        curl \
        unzip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN composer require --dev phpunitgen/console

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

COPY 999-php-cli.ini /usr/local/etc/php/conf.d/999-php-cli.ini

WORKDIR /var/www/html
ENTRYPOINT ["php-fpm"]
