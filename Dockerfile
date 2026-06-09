FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        unzip \
        zip \
    && docker-php-ext-install intl mbstring pgsql pdo_pgsql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data writable \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/codeigniter.conf \
    && a2enconf codeigniter

ENV CI_ENVIRONMENT=production

CMD sh -c 'printf "Listen ${PORT:-8080}\n" > /etc/apache2/ports.conf && sed -ri "s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:${PORT:-8080}>/" /etc/apache2/sites-available/000-default.conf && apache2-foreground'
