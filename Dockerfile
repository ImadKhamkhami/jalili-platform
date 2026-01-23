FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev \
    libxrender1 libfontconfig1 libxext6 \
    wkhtmltopdf \
    && docker-php-ext-install pdo pdo_mysql zip

# Set wkhtmltopdf path
RUN which wkhtmltopdf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
