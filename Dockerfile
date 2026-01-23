FROM php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev \
    libxrender1 libfontconfig1 libxext6 \
    wkhtmltopdf \
    && docker-php-ext-install pdo pdo_mysql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

# تأكيد وجود wkhtmltopdf (اختياري لكنه مفيد)
RUN wkhtmltopdf --version

CMD ["php-fpm"]
