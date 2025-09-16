FROM sail-8.4/app

USER root

RUN apt-get update \
    && apt-get install -y --no-install-recommends supervisor nginx php8.4-fpm \
    && rm -rf /var/lib/apt/lists/*

# create conf dir and copy supervisor programs
RUN mkdir -p /etc/supervisor/conf.d

COPY docker/supervisor/*.conf /etc/supervisor/conf.d/
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php-fpm/www.conf /etc/php/8.4/fpm/pool.d/www.conf

# ensure storage and nginx dirs are writable
RUN mkdir -p /var/www/html/storage /var/www/html/storage/logs /var/www/html/bootstrap/cache /var/lib/nginx /var/lib/nginx/body \
    && mkdir -p /run /var/log \
    && touch /var/log/php8.4-fpm.log /run/nginx.pid \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/lib/nginx /var/log/php8.4-fpm.log /run/nginx.pid

# run container as root so supervisord can start nginx/php-fpm (nginx master needs root)