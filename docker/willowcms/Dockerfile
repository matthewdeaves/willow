FROM alpine:latest

# Setup document root
WORKDIR /var/www/html

# Install packages and remove default server definition
RUN apk add --no-cache \
  curl \
  imagemagick \
  nginx \
  php83 \
  php83-ctype \
  php83-curl \
  php83-dom \
  php83-fileinfo \
  php83-fpm \
  php83-gd \
  php83-intl \
  php83-mbstring \
  php83-mysqli \
  php83-opcache \
  php83-openssl \
  php83-phar \
  php83-session \
  php83-tokenizer \
  php83-xml \
  php83-xmlreader \
  php83-xmlwriter \
  php83-pecl-imagick \
  php83-pcntl \
  php83-redis \
  php83-zip \
  php83-pdo_mysql \
  php83-bcmath \
  php83-sockets \
  php83-intl \
  php83-cli \
  php83-pdo_sqlite \
  supervisor \
  php83-simplexml \
  wget \
  unzip \
  bash && \
  rm -rf /var/lib/apt/lists/*

# Configure nginx - http
COPY docker/willowcms/nginx.conf /etc/nginx/nginx.conf
# Configure nginx - default server
COPY docker/willowcms/nginx-cms.conf /etc/nginx/conf.d/default.conf

# Configure PHP-FPM
ENV PHP_INI_DIR /etc/php83
COPY docker/willowcms/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY docker/willowcms/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# Install Composer
RUN wget https://getcomposer.org/installer -O composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php

# Configure supervisord
COPY docker/willowcms/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody:nobody /var/www/html /run /var/lib/nginx /var/log/nginx

# Set permissions for the mounted directory
RUN chmod -R 777 /var/www/html /run /var/lib/nginx /var/log/nginx

# Switch to use a non-root user from here on
USER nobody

# Expose the port nginx is reachable on
EXPOSE 80

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping || exit 1
