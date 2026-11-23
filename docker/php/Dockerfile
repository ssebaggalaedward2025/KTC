# BPTMS application image (php-fpm). Used by app, queue and scheduler services.
FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
        icu-libs libzip libpng libjpeg-turbo freetype libwebp \
        mysql-client fcgi tini \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS icu-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev libwebp-dev linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql intl zip gd bcmath exif pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY docker/php/conf.d/bptms.ini /usr/local/etc/php/conf.d/zz-bptms.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Development target: code is bind-mounted.
FROM base AS dev
RUN apk add --no-cache nodejs npm git
USER www-data

# Production target: code baked in, no dev dependencies.
FROM base AS prod
COPY --chown=www-data:www-data . /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && php artisan storage:link || true
USER www-data
ENTRYPOINT ["/sbin/tini", "--"]
CMD ["php-fpm"]
