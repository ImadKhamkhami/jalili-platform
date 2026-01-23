FROM php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev \
    libxrender1 libfontconfig1 libxext6 \
    fonts-dejavu fonts-liberation \
    wget \
    && docker-php-ext-install pdo pdo_mysql zip

# Install wkhtmltopdf (official deb)
RUN wget https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.6/wkhtmltox_0.12.6-1.buster_amd64.deb \
    && apt-get install -y ./wkhtmltox_0.12.6-1.buster_amd64.deb \
    && rm wkhtmltox_0.12.6-1.buster_amd64.deb

# Verify wkhtmltopdf exists (debug safety)
RUN which wkhtmltopdf && wkhtmltopdf --version

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

CMD ["php-fpm"]
