FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    libzip-dev \
    linux-headers \
    $PHPIZE_DEPS \
    openssl \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        intl \
        opcache \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM base AS dev

COPY docker/php.ini /usr/local/etc/php/conf.d/onreach.ini
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-scripts --no-interaction --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

FROM base AS prod

COPY docker/php.ini /usr/local/etc/php/conf.d/onreach.ini
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --ignore-platform-reqs --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
