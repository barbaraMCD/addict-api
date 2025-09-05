FROM php:8.2.19-fpm as system

# Variables d'environnement
ENV OS=linux
ENV COMPOSER_HOME /var/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /app

# Install basic dependencies
RUN apt-get update && apt-get install -y \
    git \
    wget \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql intl zip opcache bcmath \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN mkdir /var/composer
RUN mkdir /var/composer/cache
RUN chmod -R 777 /var/composer/cache
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --2.4

# Télécharger et installer Symfony CLI (binaire)
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

FROM system as builder

COPY composer.json composer.lock symfony.lock ./

RUN set -eux; \
	composer install --prefer-dist --no-scripts --no-progress --no-suggest; \
	composer clear-cache

FROM builder as test

COPY ./ ./

# Install dev dependencies for testing
RUN composer install --dev --prefer-dist --optimize-autoloader


FROM builder as runner

COPY ./ ./

RUN bin/console cache:warmup

EXPOSE 8000

CMD ["php-fpm"]

