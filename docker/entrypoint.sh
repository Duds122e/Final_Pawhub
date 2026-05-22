#!/bin/sh

if [ -z "$DATABASE_URL" ] && [ -n "$MYSQL_URL" ]; then
  export DATABASE_URL="$MYSQL_URL"
fi

# Doctrine needs serverVersion=8.0 on Railway MYSQL_URL (avoids "MySQL < 8" deprecation / wrong platform)
normalize_database_url() {
  if [ -z "$DATABASE_URL" ]; then
    return
  fi
  case "$DATABASE_URL" in
    *serverVersion=*|*server_version=*)
      return
      ;;
  esac
  case "$DATABASE_URL" in
    *\?*)
      export DATABASE_URL="${DATABASE_URL}&serverVersion=8.0&charset=utf8mb4"
      ;;
    *)
      export DATABASE_URL="${DATABASE_URL}?serverVersion=8.0&charset=utf8mb4"
      ;;
  esac
}
normalize_database_url

# Fix DEFAULT_URI when Railway variable points at localhost (common misconfiguration)
if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
  export DEFAULT_URI="https://${RAILWAY_PUBLIC_DOMAIN}"
elif [ -z "$DEFAULT_URI" ] || [ "$DEFAULT_URI" = "http://localhost:8000" ] || [ "$DEFAULT_URI" = "http://localhost" ]; then
  export DEFAULT_URI="https://finalpawhub-production.up.railway.app"
fi

PORT="${PORT:-8000}"
export PORT
APP_ENV="${APP_ENV:-prod}"

mkdir -p var/cache/sessions var/log config/jwt public/bundles
chmod -R 777 var 2>/dev/null || true

run_console() {
  php bin/console "$@" --env="$APP_ENV" --no-interaction 2>&1
}

wait_for_db() {
  if [ -z "$DATABASE_URL" ]; then
    echo "No DATABASE_URL — skipping DB wait."
    return 1
  fi
  echo "Waiting for database..."
  i=0
  while [ "$i" -lt 60 ]; do
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
      return 0
    fi
    i=$((i + 1))
    sleep 1
  done
  echo "WARNING: Database not ready after 60s."
  return 1
}

bootstrap_app() {
  if ! wait_for_db; then
    return
  fi

  if [ ! -f config/jwt/private.pem ]; then
    echo "Generating JWT keys..."
    run_console lexik:jwt:generate-keypair --skip-if-exists || true
  fi

  echo "Running migrations..."
  run_console doctrine:migrations:migrate --allow-no-migration || true

  if [ "${CREATE_ADMIN:-1}" = "1" ]; then
    echo "Creating admin user (admin / admin123)..."
    if run_console app:create-admin admin admin123 --force; then
      echo "Admin user ready."
    else
      echo "WARNING: app:create-admin failed — check logs above."
    fi
  fi

  echo "Seeding adoptable pets (if none exist)..."
  run_console app:seed-adoption-pets || true

  run_console assets:install public || true
  run_console cache:clear || true

  echo "Bootstrap complete."
}

echo "DEFAULT_URI=${DEFAULT_URI}"
if [ -n "$DATABASE_URL" ]; then
  echo "DATABASE_URL configured (serverVersion present: $(echo "$DATABASE_URL" | grep -q 'serverVersion=' && echo yes || echo no))"
fi
echo "Starting server on 0.0.0.0:${PORT}..."
php -S "0.0.0.0:${PORT}" -t public public/index_router.php &
SERVER_PID=$!
sleep 1

# Run bootstrap in background (health.php works without DB)
bootstrap_app &

echo "Server ready (pid ${SERVER_PID})."
wait $SERVER_PID
