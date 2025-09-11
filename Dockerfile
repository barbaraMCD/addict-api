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
    gnupg \
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

# Télécharger et installer Stripe CLI (binaire)
RUN curl -s https://packages.stripe.dev/api/security/keypair/stripe-cli-gpg/public | gpg --dearmor > /usr/share/keyrings/stripe.gpg
RUN echo "deb [signed-by=/usr/share/keyrings/stripe.gpg] https://packages.stripe.dev/stripe-cli-debian-local stable main" > /etc/apt/sources.list.d/stripe.list
RUN apt update && apt install stripe

FROM system as builder

COPY composer.json composer.lock symfony.lock ./

RUN set -eux; \
	composer install --prefer-dist --no-scripts --no-progress --no-suggest; \
	composer clear-cache

FROM builder as test

COPY ./ ./

RUN rm -rf vendor/ && \
    composer install --prefer-dist --optimize-autoloader




FROM builder as runner

COPY ./ ./

RUN bin/console cache:warmup

EXPOSE 8000

CMD ["php-fpm"]

