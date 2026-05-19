# syntax=docker/dockerfile:1

# =============================================================================
# PBOS Laravel API — multi-target image (dev + production-style runtime)
# =============================================================================

FROM php:8.3-fpm-bookworm AS php-base

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        posix \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g "${GID}" pbos 2>/dev/null || true \
    && useradd -u "${UID}" -g "${GID}" -ms /bin/bash pbos 2>/dev/null || true

WORKDIR /var/www/html

# -----------------------------------------------------------------------------
# Development target — bind-mount source; extensions + composer
# -----------------------------------------------------------------------------
FROM php-base AS dev

COPY docker/php/php.dev.ini /usr/local/etc/php/conf.d/99-pbos-dev.ini
COPY docker/php/opcache.dev.ini /usr/local/etc/php/conf.d/99-pbos-opcache.ini

COPY docker/api/entrypoint.sh /usr/local/bin/pbos-entrypoint.sh
COPY docker/scripts/wait-for-services.sh /usr/local/bin/wait-for-services.sh
RUN chmod +x /usr/local/bin/pbos-entrypoint.sh /usr/local/bin/wait-for-services.sh

ENTRYPOINT ["/usr/local/bin/pbos-entrypoint.sh"]
CMD ["php-fpm"]

# -----------------------------------------------------------------------------
# Production-style target — baked application (CI/CD builds)
# -----------------------------------------------------------------------------
FROM php-base AS production

COPY docker/php/php.prod.ini /usr/local/etc/php/conf.d/99-pbos-prod.ini
COPY docker/php/opcache.prod.ini /usr/local/etc/php/conf.d/99-pbos-opcache.ini

COPY apps/api/composer.json apps/api/composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY apps/api .

RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/api/entrypoint.sh /usr/local/bin/pbos-entrypoint.sh
RUN chmod +x /usr/local/bin/pbos-entrypoint.sh

USER www-data
ENTRYPOINT ["/usr/local/bin/pbos-entrypoint.sh"]
CMD ["php-fpm"]
