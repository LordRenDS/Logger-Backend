#!/bin/bash
set -e

# Ensure .env exists
if [ ! -f ".env" ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Clear and cache Laravel settings
if [ -f "./artisan" ]; then
    # Generate app key if not set
    if ! grep -q "APP_KEY=base64:" .env || [ -z "$(grep APP_KEY .env | cut -d '=' -f 2)" ]; then
        echo "Generating application key..."
        php artisan key:generate --force
    fi

    echo "Clearing and caching Laravel settings..."
    php artisan cache:clear || true
    php artisan config:cache || true
    php artisan route:cache || true

    # Wait for database to be ready
    echo "Waiting for database..."
    for i in {1..30}; do
        if php artisan db:monitor --databases=pgsql > /dev/null 2>&1; then
            break
        fi
        echo "Database is not ready yet... ($i/30)"
        sleep 2
    done

    echo "Running migrations and seeders..."
    php artisan migrate --seed --force
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- apache2-foreground "$@"
fi

exec "$@"
