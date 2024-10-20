# Use base PHP 8.2-fpm image
FROM php:8.2-fpm

# Update sources and upgrade software
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y --no-install-recommends \
    imagemagick \
    supervisor \
    autoconf \
    zlib1g-dev \
    libpng-dev \
    libicu-dev \
    git \
    gcc \
    wget \
    make \
    libzip-dev \
    unzip && \
    rm -rf /var/lib/apt/lists/*

# Configure supervisord
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Use built-in Docker helper to install most PHP modules
RUN docker-php-ext-install intl sockets bcmath mysqli pdo_mysql zip pcntl

# Install and enable PCOV for code coverage
RUN pecl install pcov && \
    docker-php-ext-enable pcov

# Install a script that makes installing PHP extensions easier
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install Redis extension
RUN pecl install redis && \
    docker-php-ext-enable redis

# Use it to install ImageMagick module from source
RUN install-php-extensions https://github.com/Imagick/imagick/archive/refs/tags/3.7.0.tar.gz

# Install Composer
RUN wget https://getcomposer.org/installer -O composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php

# Increase PHP limits
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini
