FROM nginx:1.29-alpine as base


FROM base as deployment
COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY fe/dist /var/www/html
