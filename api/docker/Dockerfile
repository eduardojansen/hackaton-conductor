FROM php:5.6-apache
MAINTAINER Alexandre Medeiros <alxmedeiros@gmail.com>

# Install GD
RUN apt-get update \
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng12-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && apt-get install -y libmcrypt-dev \
    && apt-get install -y libxml2-dev \
    && docker-php-ext-install mcrypt \
    && docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip

# Define PHP_TIMEZONE env variable
ENV PHP_TIMEZONE America/Recife

# Configure Apache Document Root
ENV APACHE_DOC_ROOT /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite

ADD apache.conf /etc/apache2/sites-enabled/000-default.conf

# Additional PHP ini configuration
COPY ./php.ini /usr/local/etc/php/conf.d/