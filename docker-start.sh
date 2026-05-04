#!/bin/bash
# Fly.io y Render asignan el puerto via $PORT
# Por defecto 8080 para Fly.io, 80 para local

PORT="${PORT:-8080}"

# Inicializar el volumen de datos si está vacío (primer arranque en Fly.io)
DATA_DIR="/var/www/html/data"
if [ ! -f "$DATA_DIR/collection.db" ]; then
  echo "Inicializando directorio de datos..."
  mkdir -p "$DATA_DIR/images"
  # Si hay una DB de bootstrap en /var/www/html/data-seed, copiarla
  if [ -f "/var/www/html/data-seed/collection.db" ]; then
    cp /var/www/html/data-seed/collection.db "$DATA_DIR/collection.db"
    echo "Base de datos copiada desde seed."
  fi
fi

# Asegurar permisos correctos
chown -R www-data:www-data "$DATA_DIR"
chmod -R 775 "$DATA_DIR"

# Reemplazar el puerto en la config de Apache
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

echo "Iniciando Apache en puerto ${PORT}..."
exec apache2-foreground
