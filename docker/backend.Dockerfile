FROM php:8.2-alpine

RUN apk add --no-cache sqlite-dev unzip git \
    && docker-php-ext-install pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app/backend

CMD sh -c "composer install --no-interaction --optimize-autoloader && php -S 0.0.0.0:8080 -t public"
