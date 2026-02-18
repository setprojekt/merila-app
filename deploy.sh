#!/bin/bash
# Deploy skript za Merila (Ubuntu VM / produkcija)
# Uporaba: ./deploy.sh [--no-git]
#   --no-git ... ne povišuje git (uporabno če prenašate prek FileZilla/SCP)

set -e

APP_DIR="${APP_DIR:-/var/www/merila-app}"
cd "$APP_DIR"

echo "🚀 Deploy Merila v $APP_DIR"

if [[ "$1" != "--no-git" ]]; then
  if git rev-parse --git-dir > /dev/null 2>&1; then
    echo "📥 git pull..."
    git pull origin main || true
  else
    echo "⚠️  Ni git repozitorija (--no-git ali prenos brez git)."
  fi
else
  echo "⏭️  Preskočen git pull (--no-git)."
fi

echo "📦 Composer..."
composer install --no-dev --optimize-autoloader

echo "🔨 NPM build..."
npm install && npm run build

echo "🗃️  Migracije..."
php artisan migrate --force

echo "📋 Cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

if systemctl is-active --quiet php8.3-fpm 2>/dev/null; then
  echo "🔄 Reload PHP-FPM..."
  sudo systemctl reload php8.3-fpm
fi

if systemctl is-active --quiet merila-queue 2>/dev/null; then
  echo "🔄 Restart queue worker..."
  sudo systemctl restart merila-queue
fi

echo "✅ Deploy končan."
