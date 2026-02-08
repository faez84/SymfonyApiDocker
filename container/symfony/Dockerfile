# syntax=docker/dockerfile:1.7
FROM php:8.2.1-fpm AS build
RUN apt-get update && \
    apt-get install -y git libicu-dev libldap2-dev libzip-dev zip && \
    docker-php-ext-install pdo_mysql intl zip && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY app/composer.json app/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress --no-scripts

COPY app/ ./
ARG DATABASE_URL="mysql://root:root@127.0.0.1:3306/api"
ENV APP_ENV=prod APP_DEBUG=0 DEFAULT_URI=http://localhost DATABASE_URL=$DATABASE_URL
RUN printf "APP_ENV=%s\nAPP_DEBUG=%s\nDEFAULT_URI=%s\nDATABASE_URL=%s\JWT_PASSPHRASE=%s\n" "$APP_ENV" "$APP_DEBUG" "$DEFAULT_URI" "$DATABASE_URL" "%env(resolve:SECRET_JWT_PASSPHRASE)%" > .env
RUN composer run-script post-install-cmd --no-interaction
RUN --mount=type=secret,id=symfony_decrypt_key,required=false \
    export SYMFONY_DECRYPTION_SECRET="$(cat /run/secrets/symfony_decrypt_key 2>/dev/null || true)" && \
    php bin/console cache:clear

FROM php:8.2.1-fpm AS runtime
RUN apt-get update && \
    apt-get install -y libicu-dev libldap2-dev libzip-dev zip && \
    docker-php-ext-install pdo_mysql intl zip

WORKDIR /app
COPY --from=build /app /app

RUN usermod -u 1000 www-data
RUN usermod -G 100 www-data

EXPOSE 9000
