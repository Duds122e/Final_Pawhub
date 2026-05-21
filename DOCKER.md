# Pawhub Docker Deployment

## Quick start

```bash
cd C:\Pawhub\my_app
docker compose up -d --build
```

## Services

| Service | URL | Description |
|---------|-----|-------------|
| **App** | http://127.0.0.1:8000 | Symfony / Pawhub API + web |
| **MySQL** | `127.0.0.1:3310` | Database `paw_db` |
| **phpMyAdmin** | http://127.0.0.1:8085 | DB admin UI |

## Default credentials

**MySQL**
- User: `paw_user`
- Password: `paw_password`
- Database: `paw_db`
- Root password: `paw_root`

**Admin login (web / API)**
- Username: `admin`
- Password: `admin123`

## Commands

```bash
# Start
docker compose up -d --build

# Stop
docker compose down

# Logs
docker compose logs -f paw_php

# Run migrations manually
docker compose exec paw_php php bin/console doctrine:migrations:migrate --no-interaction

# Create / reset admin
docker compose exec paw_php php bin/console app:create-admin admin admin123 --force

# Shell inside PHP container
docker compose exec paw_php bash
```

## Mobile app API URL

- Emulator Android: `http://10.0.2.2:8000`
- iOS / browser: `http://127.0.0.1:8000`

## Troubleshooting

**Port 8000 in use** — stop other containers or change port in `compose.yaml`:
```yaml
ports:
  - "8001:8000"
```

**Database connection errors** — wait for MySQL healthcheck, then restart PHP:
```bash
docker compose restart paw_php
```

**Rebuild from scratch**
```bash
docker compose down -v
docker compose up -d --build
```
