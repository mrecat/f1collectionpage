# F1 Collection

Catálogo web de una colección personal de miniaturas de Fórmula 1 en escala 1:43.

Sitio: https://f1collection.onrender.com

## Stack

- PHP 8.2 + Apache
- SQLite (`data/collection.db`)
- JavaScript y CSS sin frameworks

## Estructura

| Ruta | Contenido |
|---|---|
| `index.php` | Entrada y enrutado por `?page=` |
| `pages/` | Una plantilla por página |
| `db.php` | Esquema, consultas y subida de fotos |
| `auth.php` | Sesión de administrador |
| `action.php` | Acciones del panel (alta, borrado, portada) |
| `export.php` | Exportación a CSV y versión para imprimir |
| `repair.php` | Reconstruye los vínculos de fotos desde `data/images/` |
| `data/collection.db` | Base de datos |
| `data/images/` | Fotos de los autos, en WebP |

## Correr en local

Con XAMPP: copiar la carpeta dentro de `htdocs` y abrir `http://localhost/<carpeta>/`.

Requiere las extensiones `pdo_sqlite` y `gd`. En XAMPP, `gd` suele venir comentada
en `php.ini` (`;extension=gd`); hay que habilitarla para que las fotos subidas se
conviertan a WebP.

Sin XAMPP:

```
php -S localhost:8000
```

## Configuración

Copiar `config.example.php` como `config.php` y completar el hash de la contraseña:

```
php -r "echo password_hash('TU_CONTRASEÑA', PASSWORD_DEFAULT);"
```

## Deploy

Render construye la imagen con el `Dockerfile` en cada push a `main`.

El plan gratuito de Render no tiene disco persistente: en cada arranque el contenedor
copia `data/` desde el repo. Por eso los autos y fotos nuevos se cargan en local y se
suben con un commit; lo que se cargue desde el panel del sitio online se pierde al
reiniciar.
