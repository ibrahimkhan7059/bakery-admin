#!/bin/bash

# 🚀 Auto-Deploy Script for BakeHub
# Run this on your EC2 server after making code changes

echo "🔄 Starting BakeHub Auto-Deployment..."

# Navigate to project directory
cd /var/www/html/bakery-app

# Pull latest changes from Git
echo "📥 Pulling latest changes..."
sudo git pull origin main

# Install/update dependencies if composer.json changed
echo "📦 Updating dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader

# Clear all Laravel caches
echo "🧹 Clearing caches..."
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Optimize for production
echo "⚡ Optimizing for production..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Run migrations if any
echo "🛢️ Running database migrations..."
sudo -u www-data php artisan migrate --force

# Fix permissions
echo "🔐 Setting correct permissions..."
sudo chown -R www-data:www-data /var/www/html/bakery-app
sudo chmod -R 755 /var/www/html/bakery-app
sudo chmod -R 775 /var/www/html/bakery-app/storage
sudo chmod -R 775 /var/www/html/bakery-app/bootstrap/cache

# Restart Apache (if needed)
sudo systemctl reload apache2

echo "✅ Deployment completed successfully!"
echo "🌐 Visit: http://$(curl -s http://checkip.amazonaws.com/)"

# Show recent logs
echo "📋 Recent Laravel logs:"
sudo tail -n 10 /var/www/html/bakery-app/storage/logs/laravel.log
