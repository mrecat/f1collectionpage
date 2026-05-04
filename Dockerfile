FROM php:8.2-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copiar código de la app (sin data/)
COPY --chown=www-data:www-data . /var/www/html/

# Copiar data/ explícitamente como seed
COPY --chown=www-data:www-data data/ /var/www/html/data-seed/

# Crear carpetas vacías para el volumen y sesiones
RUN mkdir -p /var/www/html/data/images \
    && mkdir -p /var/www/html/sessions \
    && chown -R www-data:www-data /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/sessions \
    && chmod -R 775 /var/www/html/data \
    && chmod -R 775 /var/www/html/sessions

RUN echo "session.save_path = /var/www/html/sessions" >> /usr/local/etc/php/php.ini

COPY docker-start.sh /docker-start.sh
RUN chmod +x /docker-start.sh

CMD ["/docker-start.sh"]
