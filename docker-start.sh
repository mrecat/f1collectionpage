#!/bin/bash
# Render asigna un puerto dinámico via $PORT
# Apache necesita saber ese puerto antes de arrancar

PORT="${PORT:-80}"

# Reemplazar el puerto en la config de Apache
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

echo "Iniciando Apache en puerto ${PORT}..."
exec apache2-foreground
