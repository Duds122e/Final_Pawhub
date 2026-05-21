#!/bin/sh
set -e

DB_HOST="${DB_HOST:-paw_mysql}"
DB_USER="${MYSQL_USER:-paw_user}"
DB_PASS="${MYSQL_PASSWORD:-paw_password}"

echo "Waiting for MySQL at ${DB_HOST}..."
i=0
while [ "$i" -lt 30 ]; do
  if php -r "new PDO('mysql:host=${DB_HOST};port=3306', '${DB_USER}', '${DB_PASS}');" 2>/dev/null; then
    echo "MySQL is ready."
    break
  fi
  i=$((i + 1))
  sleep 2
done

if [ "$i" -ge 30 ]; then
  echo "MySQL did not become ready in time."
  exit 1
fi

mkdir -p var/cache var/log
chmod -R 777 var 2>/dev/null || true

if [ ! -f config/jwt/private.pem ]; then
  echo "Generating JWT keys..."
  php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction
fi

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

if [ "${CREATE_ADMIN:-1}" = "1" ]; then
  echo "Ensuring admin user exists..."
  php bin/console app:create-admin admin admin123 --force --no-interaction 2>/dev/null || true
fi

php bin/console cache:clear --no-warmup 2>/dev/null || true

echo "Starting application..."
exec "$@"
