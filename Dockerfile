FROM surnet/alpine-wkhtmltopdf:3.19.0-0.12.6-full as wkhtmltopdf

FROM php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev \
    libxrender1 libfontconfig1 libxext6 \
    fonts-dejavu fonts-liberation \
    && docker-php-ext-install pdo pdo_mysql zip

# Copy wkhtmltopdf binary from prebuilt image
COPY --from=wkhtmltopdf /bin/wkhtmltopdf /usr/bin/wkhtmltopdf
COPY --from=wkhtmltopdf /bin/wkhtmltoimage /usr/bin/wkhtmltoimage

# Verify
RUN wkhtmltopdf --version

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader

CMD ["php-fpm"]
