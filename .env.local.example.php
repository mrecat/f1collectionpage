<?php
// Copiá este archivo como .env.local.php y completá los valores.
// NUNCA commitees .env.local.php — está en .gitignore.
//
// Para generar el hash de tu contraseña, corré en la terminal:
//   php -r "echo password_hash('tu-contraseña', PASSWORD_BCRYPT);"

putenv('ADMIN_USER=admin');
putenv('ADMIN_PASSWORD_HASH=PEGAR_HASH_AQUI');
