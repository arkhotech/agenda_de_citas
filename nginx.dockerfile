FROM nginx

COPY ./assets/nginx.conf /etc/nginx/nginx.conf

COPY application /var/www/html

COPY environment /var/www/html/local/.env

ENV  APP_ROOT=/var/www/html
 
RUN mkdir -p $APP_ROOT/local/storage/framework/cache; \
    mkdir -p $APP_ROOT/local/storage/framework/sessions ;\
    mkdir -p $APP_ROOT/local/storage/framework/views ; \
    mkdir -p $APP_ROOT/local/storage/app ; \
    mkdir -p $APP_ROOT/local/storage/logs ; \
    chown -R www-data:www-data $APP_ROOT/local/storage ; \
    chmod 765 -R $APP_ROOT/local/storage/framework
