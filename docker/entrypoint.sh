#!/bin/sh

if [ -z "$DATABASE_URL" ] && [ -n "$MYSQL_URL" ]; then
  export DATABASE_URL="$MYSQL_URL"
fi

PORT="${PORT:-8000}"
export PORT

mkdir -p var/cache var/log config/jwt public/bundles
chmod -R 777 var 2>/dev/null || true

echo "Starting server on 0.0.0.0:${PORT}..."
php -S "0.0.0.0:${PORT}" -t public/ &
SERVER_PID=$!
sleep 1

# Background bootstrap — must not block /health.php
(
  if [ -n "$DATABASE_URL" ]; then
    echo "Waiting for database..."
    i=0
    while [ "$i" -lt 45 ]; do
      if php -r '
        $url = getenv("DATABASE_URL");
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
        break
      fi
      i=$((i + 1))
      sleep 2
    done
  fi

  if [ ! -f config/jwt/private.pem ]; then
    php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction 2>/dev/null || true
  fi

  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>/dev/null || true

  if [ "${CREATE_ADMIN:-1}" = "1" ]; then
    php bin/console app:create-admin admin admin123 --force --no-interaction 2>/dev/null || true
  fi

  php bin/console assets:install public --no-interaction 2>/dev/null || true
  php bin/console cache:clear --env="${APP_ENV:-prod}" --no-warmup 2>/dev/null || true

  echo "Bootstrap complete."
) &

echo "Server ready (pid ${SERVER_PID})."
wait $SERVER_PID
