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
 
COPY assets/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

COPY assets/docker.conf /usr/local/etc/php-fpm.d/docker.conf

ENV DB_HOST=caldatabase

COPY application /var/www/html

RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html/local

RUN apt-get install curl php5.6-cli git ; \
    curl -sS https://getcomposer.org/installer |  php -- --install-dir=/usr/local/bin --filename=composer 

USER www-data

RUN composer install

USER root


RUN apt-get install -y mysql-client

COPY environment /var/www/html/local/.env

COPY sql /sql

ENV  APP_ROOT=/var/www/html

RUN mkdir -p $APP_ROOT/local/storage/framework/cache; \
    mkdir -p $APP_ROOT/local/storage/framework/sessions ;\
    mkdir -p $APP_ROOT/local/storage/framework/views ; \
    mkdir -p $APP_ROOT/local/storage/app ; \
    mkdir -p $APP_ROOT/local/storage/logs ;\
    chown -R www-data:www-data $APP_ROOT/local/storage ; \
    chmod 765 -R $APP_ROOT/local/storage/framework
