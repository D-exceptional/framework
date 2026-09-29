FROM nginx:alpine

# Remove default configuration
RUN rm /etc/nginx/conf.d/default.conf

# Application public files
COPY public/ /var/www/public/

# Production Nginx configuration
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf