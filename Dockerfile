FROM php:8.4.3-apache
COPY . /var/www/html
WORKDIR /var/www/html
RUN docker-php-ext-install mysqli
RUN apt-get update && apt-get install -y libfreetype-dev libjpeg62-turbo-dev libpng-dev libzip-dev git zip unzip \
    && docker-php-ext-install zip \
	&& docker-php-ext-install gd
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
EXPOSE 80