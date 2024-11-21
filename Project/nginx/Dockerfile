FROM nginx:1.21-alpine

COPY ./app/public/ /var/www/html/public/
COPY ./nginx/templates/nginx-server.conf.template /etc/nginx/templates/default.conf.template
