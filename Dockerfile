FROM php:8.2.19-fpm as system

ENV COMPOSER_HOME /var/composer
ENV COMPOSER_ALLOW_SUPERUSER 1
WORKDIR /app

# Install Dependencies
RUN apt update && apt install -y wget curl git libcurl4-gnutls-dev zlib1g-dev libicu-dev g++ libxml2-dev libpq-dev zip libzip-dev unzip \
    libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng-dev libxpm-dev libjpeg-dev libwebp-dev gnupg2 \
    nginx supervisor \
    --no-install-recommends

RUN apt autoremove && apt autoclean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP Extensions
RUN docker-php-ext-install gettext sockets pdo pdo_pgsql intl opcache gd zip bcmath

# Install Composer with specific version
RUN mkdir /var/composer
RUN mkdir /var/composer/cache
RUN chmod -R 777 /var/composer/cache
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --2.4

FROM system as builder

COPY composer.json composer.lock symfony.lock ./

RUN set -eux; \
    composer install --prefer-dist --no-scripts --no-progress --optimize-autoloader; \
    composer clear-cache

FROM builder as runner

COPY ./ ./

# Symfony CLI TEMPORARY USE 5.10.2 TO BE ABLE TO BIND 0.0.0.0 SHOULD BE REPLACE BY PHP-FPM
#RUN wget https://get.symfony.com/cli/installer -O - | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
RUN wget https://github.com/symfony-cli/symfony-cli/releases/download/v5.10.2/symfony-cli_5.10.2_amd64.deb && dpkg -i symfony-cli_5.10.2_amd64.deb && rm symfony-cli_5.10.2_amd64.deb

FROM system as builder

COPY composer.json composer.lock symfony.lock ./

RUN set -eux; \
	composer install --prefer-dist --no-scripts --no-progress --no-suggest; \
	composer clear-cache

FROM builder as runner

COPY ./ ./

RUN bin/console cache:warmup

EXPOSE 8000

CMD ["php-fpm"]