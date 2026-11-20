# syntax=docker/dockerfile:1
ARG PHP_IMAGE=dunglas/frankenphp:1-php8.4-alpine@sha256:bacc982b5c926a5adee882cd245fc96fb79c316fe9ea8d845e2e3f3863113adb
ARG NODE_IMAGE=node:24-alpine@sha256:116b800c3754b440ee0aa3ed2f20f5bf3d99a554bfd9adeea09ea8017f6e85f5
ARG COMPOSER_IMAGE=composer:2@sha256:919efe8e62179c71dfd31fad035fd671fe7454211c6b2aa30a10af0ab4acedba

FROM ${COMPOSER_IMAGE} AS composer-bin

FROM ${PHP_IMAGE} AS php-base
RUN install-php-extensions pdo_mysql pdo_pgsql pdo_sqlite mbstring intl zip bcmath opcache \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php-production.ini /usr/local/etc/php/conf.d/zz-rentacar.ini
WORKDIR /app

FROM php-base AS php-deps
COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY routes ./routes
COPY artisan ./artisan
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader \
    && composer check-platform-reqs --no-dev \
    && php artisan package:discover --ansi

FROM ${NODE_IMAGE} AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY --from=php-deps /app/vendor ./vendor
RUN npm run build && test -s public/build/manifest.json

FROM php-base AS production
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=8080 \
    XDG_CONFIG_HOME=/config \
    XDG_DATA_HOME=/data
RUN addgroup -g 10001 app \
    && adduser -D -H -u 10001 -G app app \
    && setcap -r /usr/local/bin/frankenphp \
    && mkdir -p /config/caddy /data/caddy \
    && chown -R app:app /config /data
COPY --from=php-deps /app /app
COPY --from=frontend /app/public/build /app/public/build
COPY public/index.php public/favicon.ico public/robots.txt /app/public/
COPY resources/views /app/resources/views
# This YAML is served by an existing Laravel route.
COPY docs/openapi.yaml /app/docs/openapi.yaml
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/rentacar-entrypoint
COPY docker/validate-runtime.php /usr/local/bin/rentacar-validate-runtime.php
RUN mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && ln -s /app/storage/app/public public/storage \
    && chown -R app:app storage bootstrap/cache \
    && chmod -R u=rwX,g=rX,o= storage bootstrap/cache
USER 10001:10001
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl --fail --silent --output /dev/null "http://127.0.0.1:${PORT}/up" || exit 1
ENTRYPOINT ["rentacar-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
