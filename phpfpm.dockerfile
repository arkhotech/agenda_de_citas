FROM php:5.6-fpm

RUN awk '$1 ~ "^deb" { $3 = $3 "-backports"; print; exit }' /etc/apt/sources.list > /etc/apt/sources.list.d/backports.list 

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install pdo pdo_mysql

RUN mv /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf.bak
 
COPY ./assets/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

COPY ./assets/docker.conf /usr/local/etc/php-fpm.d/docker.conf

COPY ./application/local /var/www/html
