#Use base php 8.3-fpm image
FROM php:8.2-fpm

#Update sources and upgrade software
RUN apt-get update && \
apt-get upgrade -y

#Install image magick
RUN apt-get install imagemagick -y

#Install tools needed to compile PHP modules
RUN apt-get install \
autoconf \
zlib1g-dev \
libpng-dev \
libicu-dev \
git \
gcc \
wget \
make -y

#Use built in docker helper to install most PHP modules
RUN docker-php-ext-install intl sockets bcmath mysqli pdo_mysql

#Install a script that makes installing PHP extensions easier
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

#Use it to install image magic module from source
RUN install-php-extensions https://github.com/Imagick/imagick/archive/refs/tags/3.7.0.tar.gz
