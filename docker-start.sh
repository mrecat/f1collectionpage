#!/bin/bash
# Fly.io y Render asignan el puerto via $PORT
# Por defecto 8080 para Fly.io, 80 para local

PORT="${PORT:-8080}"

DATA_DIR="/var/www/html/data"
SEED_DIR="/var/www/html/data-seed"

# Primer arranque: copiar DB e imágenes desde la imagen Docker al volumen
if [ ! -f "$DATA_DIR/.initialized" ]; then
  echo "Primer arranque: inicializando volumen desde seed..."
  mkdir -p "$DATA_DIR/images"

  if [ -f "$SEED_DIR/collection.db" ]; then
    cp "$SEED_DIR/collection.db" "$DATA_DIR/collection.db"
    echo "Base de datos copiada."
  fi

  if [ -d "$SEED_DIR/images" ] && [ "$(ls -A $SEED_DIR/images 2>/dev/null)" ]; then
    echo "Copiando imagenes (puede tardar un momento)..."
    cp -r "$SEED_DIR/images/." "$DATA_DIR/images/"
    echo "Imagenes copiadas."
  fi

  touch "$DATA_DIR/.initialized"
  echo "Inicializacion completa."
fi

# Asegurar permisos correctos
chown -R www-data:www-data "$DATA_DIR"
chmod -R 775 "$DATA_DIR"

# Reemplazar el puerto en la config de Apache
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

echo "Iniciando Apache en puerto ${PORT}..."
exec apache2-foreground
