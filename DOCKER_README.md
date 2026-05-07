# Docker Production Deployment

## Quick Start

```bash
# 1. Copy and fill environment variables
cp .env.example .env
nano .env   # fill in APP_KEY, DB passwords, Redis password

# 2. Generate app key (if not set)
docker run --rm -v $(pwd):/app -w /app composer:2.7 \
  php artisan key:generate --show

# 3. Build and start all services
docker compose up -d --build

# 4. Watch startup logs
docker compose logs -f app
```

## Services

| Service     | Container         | Purpose                        |
|-------------|-------------------|--------------------------------|
| `app`       | fwidget_app       | Nginx + PHP-FPM + Laravel app  |
| `db`        | fwidget_db        | MySQL 8 database               |
| `redis`     | fwidget_redis     | Cache, sessions, queues        |
| `queue`     | fwidget_queue     | Laravel queue worker           |
| `scheduler` | fwidget_scheduler | Laravel task scheduler         |

## Useful Commands

```bash
# View all running containers
docker compose ps

# Tail app logs
docker compose logs -f app

# Run artisan commands
docker compose exec app php artisan <command>

# Enter app shell
docker compose exec app sh

# Run migrations manually
docker compose exec app php artisan migrate --force

# Clear all caches
docker compose exec app php artisan optimize:clear

# Restart just the app
docker compose restart app

# Stop everything
docker compose down

# Stop and remove volumes (DELETES ALL DATA)
docker compose down -v
```

## File Structure

```
project/
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
├── .env.example
└── docker/
    ├── entrypoint.sh
    ├── php/
    │   ├── php.ini
    │   └── opcache.ini
    ├── nginx/
    │   ├── nginx.conf
    │   └── default.conf
    ├── mysql/
    │   └── my.cnf
    └── supervisor/
        └── supervisord.conf
```

## Updating the App

```bash
git pull
docker compose up -d --build app
```
