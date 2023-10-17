FROM php:8.1-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt-dev libpq-dev;

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql gd opcache intl zip calendar dom mbstring zip gd xsl bcmath
RUN pecl install apcu && docker-php-ext-enable apcu

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Installation de mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html/