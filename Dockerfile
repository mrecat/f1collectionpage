FROM php:8.2-apache

# Habilitar módulos de Apache
RUN a2enmod rewrite

# Instalar extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar Apache para permitir .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copiar los archivos de la app (sin la carpeta data/ — se monta como volumen en Fly.io)
COPY --chown=www-data:www-data . /var/www/html/

# Guardar la DB actual como seed (se copia al volumen si está vacío al iniciar)
RUN mkdir -p /var/www/html/data-seed \
    && if [ -f /var/www/html/data/collection.db ]; then \
         cp /var/www/html/data/collection.db /var/www/html/data-seed/collection.db; \
       fi

# Crear carpetas necesarias y dar permisos de escritura
RUN mkdir -p /var/www/html/data/images \
    && mkdir -p /var/www/html/sessions \
    && chown -R www-data:www-data /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/sessions \
    && chmod -R 775 /var/www/html/data \
    && chmod -R 775 /var/www/html/sessions

# Configurar PHP para guardar sesiones en carpeta propia
RUN echo "session.save_path = /var/www/html/sessions" >> /usr/local/etc/php/php.ini

# Script de inicio que ajusta el puerto y prepara el volumen
COPY docker-start.sh /docker-start.sh
RUN chmod +x /docker-start.sh

CMD ["/docker-start.sh"]
