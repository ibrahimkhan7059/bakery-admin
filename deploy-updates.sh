#!/bin/bash

# 🚀 Laravel Update Deployment Script
# Run this script on your production server

echo "🔄 Starting Laravel Update Deployment..."

# Step 1: Backup current version
echo "📦 Creating backup..."
cp -r /var/www/html/bakery-app /var/www/html/bakery-app-backup-$(date +%Y%m%d_%H%M%S)

# Step 2: Pull latest changes
echo "📥 Pulling latest changes from Git..."
cd /var/www/html/bakery-app
git pull origin main

# Step 3: Install dependencies
echo "📋 Installing/Updating dependencies..."
composer install --optimize-autoloader --no-dev

# Step 4: Database migrations (if any new)
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Step 5: Clear and rebuild caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "⚡ Building production caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 6: Fix permissions
echo "🔧 Fixing permissions..."
sudo chown -R www-data:www-data /var/www/html/bakery-app
sudo chmod -R 755 /var/www/html/bakery-app
sudo chmod -R 775 /var/www/html/bakery-app/storage
sudo chmod -R 775 /var/www/html/bakery-app/bootstrap/cache

# Step 7: Restart services
echo "🔄 Restarting services..."
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

echo "✅ Deployment completed successfully!"
echo "🌐 Your updated application is now live!"
