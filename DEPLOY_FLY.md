# Deploy en Fly.io — F1 Collection Page

## Requisitos previos

1. Instalar `flyctl`: https://fly.io/docs/hands-on/install-flyctl/
2. Hacer login: `fly auth login`

---

## Primera vez (setup inicial)

### 1. Crear la app en Fly.io
```bash
fly apps create f1collection
```
> Si el nombre `f1collection` ya está tomado, cambiá `app = 'f1collection'` en `fly.toml` por otro nombre único.

### 2. Crear el volumen persistente (para DB e imágenes)
```bash
fly volumes create f1data --size 2 --region gru
```
> `gru` = São Paulo (más cercano a Argentina). Podés usar `--size 5` si tenés muchas imágenes.

### 3. Hacer el deploy
```bash
fly deploy
```

### 4. Subir las imágenes existentes al volumen (primer setup)
Las imágenes están en `data/images/` y NO se incluyen en el Docker image.
Tenés que subirlas al volumen una sola vez:

```bash
# Opción A: SSH + tar (recomendado para muchos archivos)
tar czf images.tar.gz data/images/
fly sftp shell
# Dentro del shell de SFTP:
put images.tar.gz /tmp/images.tar.gz
exit

fly ssh console
# Dentro de la VM:
tar xzf /tmp/images.tar.gz -C /var/www/html/data/
exit
```

```bash
# Opción B: usando fly sftp get/put archivo por archivo (para pocos archivos)
fly sftp shell
put data/images/archivo.png /var/www/html/data/images/archivo.png
```

---

## Deploys posteriores (actualizaciones de código)

```bash
fly deploy
```

Los datos (imágenes + DB) quedan intactos en el volumen.

---

## Comandos útiles

```bash
fly status          # Ver estado de la app
fly logs            # Ver logs en tiempo real
fly ssh console     # Acceder a la VM por SSH
fly open            # Abrir la app en el browser
```

---

## Variables de entorno (opcional)

Si querés mover las credenciales de `config.php` a secrets:
```bash
fly secrets set ADMIN_PASSWORD="tu_password_segura"
```
Y en `config.php` reemplazás:
```php
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'fallback');
```

---

## Notas importantes

- **Volumen**: La carpeta `/var/www/html/data/` es el volumen persistente. Todo lo que guardes ahí (imágenes subidas, cambios en la DB) sobrevive los redeploys.
- **DB seed**: Si el volumen está vacío (primer arranque), el script copia automáticamente `data/collection.db` del repo.
- **Puerto**: Fly.io usa `PORT=8080` internamente; el `fly.toml` ya está configurado.
- **Región**: `gru` (São Paulo) es la más cercana a Argentina.
