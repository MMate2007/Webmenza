FROM php:8.4.3-apache
COPY . /var/www/html
WORKDIR /var/www/html
RUN docker-php-ext-install mysqli
RUN apt-get update && apt-get install -y libfreetype-dev libjpeg62-turbo-dev libpng-dev libzip-dev git zip unzip \
    && docker-php-ext-install zip \
	&& docker-php-ext-install gd
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
RUN a2enmod ssl
WORKDIR /etc/apache2/ssl
RUN openssl req -new -newkey rsa:4096 -days 3650 -nodes -x509 -subj \
    "/C=HU/ST=state/L=loc/O=org/CN=fqdn" \
    -keyout ./ssl.key -out ./ssl.crt
WORKDIR /etc/apache/sites-available
COPY ./conf /etc/apache/sites-available
RUN a2ensite dev
WORKDIR /var/www/html
EXPOSE 80
EXPOSE 443