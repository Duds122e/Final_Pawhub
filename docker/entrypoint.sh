#!/bin/sh
set -e

# Railway MySQL plugin exposes MYSQL_URL; Symfony expects DATABASE_URL
if [ -z "$DATABASE_URL" ] && [ -n "$MYSQL_URL" ]; then
  export DATABASE_URL="$MYSQL_URL"
fi

PORT="${PORT:-8000}"
export PORT

wait_for_database() {
  if [ -n "$DATABASE_URL" ]; then
    echo "Waiting for database (DATABASE_URL)..."
    i=0
    while [ "$i" -lt 60 ]; do
      if php -r '
        $url = getenv("DATABASE_URL");
        if (!$url) { exit(1); }
        $p = parse_url($url);
        $host = $p["host"] ?? "127.0.0.1";
        $port = $p["port"] ?? 3306;
        $user = urldecode($p["user"] ?? "root");
        $pass = urldecode($p["pass"] ?? "");
        try {
          new PDO("mysql:host={$host};port={$port}", $user, $pass);
          exit(0);
        } catch (Throwable $e) {
          exit(1);
        }
      ' 2>/dev/null; then
        echo "Database is ready."
        return 0
      fi
      i=$((i + 1))
      sleep 2
    done
    echo "Database did not become ready in time."
    return 1
  fi

  DB_HOST="${DB_HOST:-paw_mysql}"
  DB_USER="${MYSQL_USER:-paw_user}"
  DB_PASS="${MYSQL_PASSWORD:-paw_password}"

  echo "Waiting for MySQL at ${DB_HOST}..."
  i=0
  while [ "$i" -lt 30 ]; do
    if php -r "new PDO('mysql:host=${DB_HOST};port=3306', '${DB_USER}', '${DB_PASS}');" 2>/dev/null; then
      echo "MySQL is ready."
      return 0
    fi
    i=$((i + 1))
    sleep 2
  done
  echo "MySQL did not become ready in time."
  return 1
}

mkdir -p var/cache var/log
chmod -R 777 var 2>/dev/null || true

if [ "${SKIP_DB_WAIT:-0}" != "1" ]; then
  wait_for_database || exit 1
else
  echo "Skipping database wait (SKIP_DB_WAIT=1)."
fi

if [ ! -f config/jwt/private.pem ]; then
  echo "Generating JWT keys..."
  php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction
fi

# Start HTTP server FIRST so Railway healthcheck can reach /health.php
echo "Starting application on 0.0.0.0:${PORT}..."
php -S "0.0.0.0:${PORT}" -t public/ &
SERVER_PID=$!

# Give the server a moment to bind
sleep 2

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

if [ "${CREATE_ADMIN:-1}" = "1" ]; then
  echo "Ensuring admin user exists..."
  php bin/console app:create-admin admin admin123 --force --no-interaction 2>/dev/null || true
fi

APP_ENV="${APP_ENV:-dev}"
if [ "$APP_ENV" = "prod" ]; then
  php bin/console cache:clear --env=prod --no-warmup 2>/dev/null || true
else
  php bin/console cache:clear --no-warmup 2>/dev/null || true
fi

echo "Application ready (pid ${SERVER_PID})."
wait $SERVER_PID
