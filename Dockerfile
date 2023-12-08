FROM php:8.2-cli

RUN pecl install xdebug-3.2.1
RUN docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
