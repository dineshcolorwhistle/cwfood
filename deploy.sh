#!/bin/bash
set -e

echo "Starting deployment..."

git config --global --add safe.directory "$(pwd)"

git fetch origin
git checkout main
git reset --hard origin/main

composer install --no-dev --optimize-autoloader

php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

php artisan optimize

echo "Deployment completed successfully."
