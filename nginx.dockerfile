FROM nginx

COPY ./assets/nginx.conf /etc/nginx/nginx.conf

COPY .env /var/www/html/.env

COPY application /var/www/html
