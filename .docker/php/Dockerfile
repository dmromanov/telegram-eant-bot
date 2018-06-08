FROM php:7.2-fpm

RUN apt-get update && \
    apt-get install -y unzip libicu-dev libcurl4-gnutls-dev libxml2-dev nginx

#RUN docker-php-ext-configure intl && \
#    docker-php-ext-install -j$(nproc) pdo_mysql

#RUN pecl install apcu && \
#    echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

RUN echo "pdo_mysql.default_socket=/var/run/mysqld.sock" > /usr/local/etc/php/conf.d/zzzz_cusom.ini
RUN echo "mysql.default_socket=/var/run/mysqld.sock" >> /usr/local/etc/php/conf.d/zzzz_cusom.ini

RUN chown -R www-data:www-data /var/www/html
