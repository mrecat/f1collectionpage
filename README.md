# 🏎️ F1 Collection v3 — Con Sistema de Login

## Antes de subir al servidor

### 1. Editá config.php
Abrí el archivo `config.php` y cambiá:
```php
define('ADMIN_USER',     'admin');        // ← tu usuario
define('ADMIN_PASSWORD', 'f1collection2025'); // ← tu contraseña
```

### 2. Subí los archivos
Copiá TODA la carpeta al servidor (incluyendo `data/` con tu base de datos e imágenes).

### 3. Permisos (solo Linux/VPS)
```bash
chmod 755 /ruta/f1collection/
chmod 775 /ruta/f1collection/data/
chmod 775 /ruta/f1collection/data/images/
chown www-data:www-data /ruta/f1collection/data/ -R
```

### En XAMPP (Windows) no necesitás cambiar permisos.

## Cómo funciona

| Visitante | Admin (logueado) |
|---|---|
| Ve la colección completa | Todo lo del visitante |
| Filtra y busca | ⭐ Marcar favoritos |
| Ve fotos y modal | ➕ Agregar autos |
| Ve stats | ✏️ Editar autos |
| Ve favoritos | 🗑️ Eliminar autos |
| — | 📷 Subir fotos |

El botón 🔒 ADMIN en el menú lleva al login.
Una vez logueado aparece 🔓 SALIR para cerrar sesión.

## Hosting gratuito recomendado para PHP + SQLite

- **InfinityFree** (infinityfree.com) — gratuito, PHP + SQLite
- **000webhost** (000webhost.com) — gratuito, PHP
- **Byet Host** (byet.host) — gratuito, PHP

> ⚠️ En hosting gratuito verificá que permitan escritura en carpetas (para SQLite y fotos).
> Si hay problemas, contactá al soporte o buscá "enable write permissions [nombre del host]".

## Estructura de archivos

```
f1collection/
├── config.php         ← ⚙️ EDITÁ ESTO PRIMERO (usuario y contraseña)
├── auth.php           ← Manejo de sesiones
├── index.php          ← Entrada principal
├── db.php             ← Base de datos SQLite
├── style.css          ← Estilos F1
├── app.js             ← JavaScript
├── data/
│   ├── .htaccess      ← Protege collection.db del acceso directo
│   ├── collection.db  ← Tu base de datos (¡hacé backup de esto!)
│   └── images/        ← Fotos de los autos
└── pages/
    ├── collection.php
    ├── stats.php
    ├── favorites.php
    ├── add.php
    ├── edit.php
    └── login.php
```
