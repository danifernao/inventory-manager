#!/bin/bash
# Ejecuta el script con Bash.

set -e
# Detiene la ejecución del script si cualquier comando falla.

# Genera la clave de la aplicación.
php artisan key:generate

# Espera a que MySQL esté listo.
dockerize -wait tcp://mysql:3306 -timeout 60s

# Destruye las tablas existentes y ejecuta las migraciones.
php artisan migrate --seed

# Crea un enlace simbólico desde public/storage hacia storage/app/public para acceso público a archivos.
php artisan storage:link

echo "Iniciando Apache. El proyecto web estará disponible en http://localhost/"

# Inicia Apache.
apache2-foreground