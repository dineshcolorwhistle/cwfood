#!/usr/bin/env bash
set -e

ENV=$1

if [ -z "$ENV" ]; then
  echo "ERROR: Environment not specified (staging|prod)"
  exit 1
fi

echo "Starting deployment for environment: $ENV"
echo "Working directory: $(pwd)"

# Safety check (prevents wrong path deploy)
if [[ "$ENV" == "staging" && "$(pwd)" != *"cwfood.eduwhistle.com"* ]]; then
  echo "ERROR: Not in staging directory"
  exit 1
fi

if [[ "$ENV" == "prod" && "$(pwd)" != *"cwfoodproduction.eduwhistle.com"* ]]; then
  echo "ERROR: Not in production directory"
  exit 1
fi

# Composer dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Laravel cache clear
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Optimize
php artisan optimize

echo "Deployment completed successfully for $ENV"
