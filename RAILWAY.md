# Deploy Pawhub on Railway

## Why you saw 502 Bad Gateway

Railway routes traffic to the **`PORT`** environment variable. The app was hardcoded to port **8000** and the entrypoint waited for host **`paw_mysql`**, which does not exist on Railway — the container exited before the server started.

## Fix (already in repo)

- App listens on `$PORT`
- Database wait uses `DATABASE_URL` from Railway MySQL
- Production build (`APP_ENV=prod`)

Redeploy after pushing these changes.

## Railway setup checklist

### 1. Add MySQL

In your Railway project:

1. **New** → **Database** → **MySQL**
2. Open the MySQL service → **Variables** → copy `MYSQL_URL` or reference `${{MySQL.MYSQL_URL}}`

### 2. Web service variables

On your **Final_Pawhub** (web) service, set:

| Variable | Value |
|----------|--------|
| `APP_ENV` | `prod` |
| `APP_DEBUG` | `0` |
| `APP_SECRET` | long random string |
| `DATABASE_URL` | `${{MySQL.MYSQL_URL}}?serverVersion=8.0&charset=utf8mb4` |
| `JWT_PASSPHRASE` | any random string (16+ chars) |
| `DEFAULT_URI` | `https://finalpawhub-production.up.railway.app` (delete if set to `http://localhost:8000` — entrypoint auto-fixes when `RAILWAY_PUBLIC_DOMAIN` is set) |
| `JWT_PASSPHRASE` | random string (same as used when generating JWT keys) |
| `CORS_ALLOW_ORIGIN` | `^https?://.*$` (if using mobile app) |
| `CREATE_ADMIN` | `1` (first deploy only, then `0`) |

Optional (email / Google):

- `MAILER_DSN`, `OAUTH_GOOGLE_*` — set from `.env.example` if needed

### 3. Link MySQL to web service

**Settings** → **Networking** / **Variables** → ensure the web service can read the MySQL plugin URL.

Append to `DATABASE_URL` if missing:

```
?serverVersion=8.0&charset=utf8mb4
```

Example:

```
mysql://root:pass@host.railway.internal:3306/railway?serverVersion=8.0&charset=utf8mb4
```

### 4. Deploy

- Connect repo: `Duds122e/Final_Pawhub`
- Root directory: `/` (project root with `Dockerfile`)
- Railway builds from `Dockerfile` automatically

### 5. Verify logs

**Deployments** → **View logs**. You should see:

```
Database is ready.
Running migrations...
Starting application on 0.0.0.0:XXXX
PHP Development Server started
```

### 6. Admin login

- URL: `https://your-app.up.railway.app/login`
- User: `admin`
- Password: `admin123` (change after first login)

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 Internal Server Error | Open `/health.php?db=1` — if `db` is not `ok`, fix `DATABASE_URL`. Check deploy logs for `ERROR: DATABASE_URL is not set`. Set `APP_DEBUG=1` briefly to see the real error. |
| 502 Bad Gateway | Check deploy logs; confirm `DATABASE_URL` is set |
| DB connection failed | Add `?serverVersion=8.0` to `DATABASE_URL` |
| Migrations fail | Ensure MySQL service is in same project and linked |
| Slow first request | Normal — Symfony warms cache on first hit |

## Local Docker vs Railway

| | Local `docker compose` | Railway |
|--|------------------------|---------|
| Port | 8000 | `$PORT` (dynamic) |
| DB host | `paw_mysql` | from `DATABASE_URL` |
| Env | `.env` file | Railway Variables |
