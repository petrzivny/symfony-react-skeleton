FROM nginx:1.25-alpine as base


FROM base as deployment
COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY api/public /var/www/html/public
#COPY fe/dist /var/www/html/public
