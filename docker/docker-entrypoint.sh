#!/bin/bash
set -e

# Clear and cache Laravel settings
# Check if artisan exists before running
if [ -f "./artisan" ]; then
    php artisan cache:clear || true
    php artisan config:cache || true
    php artisan route:cache || true
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- apache2-foreground "$@"
fi

exec "$@"
