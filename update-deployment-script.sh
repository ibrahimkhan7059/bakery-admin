#!/bin/bash

echo "🔄 Starting BakeHub Update Deployment..."
echo "========================================"

# Navigate to project directory
cd /var/www/bakery-app

# Create backup of current version (optional)
echo "📦 Creating backup..."
sudo cp -r /var/www/bakery-app /var/www/bakery-app-backup-$(date +%Y%m%d_%H%M%S)

# Pull latest changes from Git
echo "📥 Pulling latest changes from Git..."
sudo -u www-data git pull origin main

# Check if composer.json changed
if sudo -u www-data git diff HEAD~1 --name-only | grep -q composer.json; then
    echo "📋 Composer dependencies updated, installing..."
    sudo -u www-data composer install --no-dev --optimize-autoloader
else
    echo "📋 No composer changes detected, skipping..."
fi

# Check for new migrations
echo "🗄️ Checking for database migrations..."
if sudo -u www-data php artisan migrate:status | grep -q "Pending"; then
    echo "🗄️ Running new migrations..."
    sudo -u www-data php artisan migrate --force
else
    echo "🗄️ No new migrations found"
fi

# Clear all caches
echo "🧹 Clearing application caches..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan route:clear

# Rebuild optimized caches
echo "⚡ Building optimized caches..."
sudo -u www-data php artisan config:cache

# Try to cache routes (skip if errors)
sudo -u www-data php artisan route:cache 2>/dev/null || echo "⚠️ Route cache skipped due to errors"

# Try to cache views (skip if errors)
sudo -u www-data php artisan view:cache 2>/dev/null || echo "⚠️ View cache skipped due to errors"

# Update file permissions
echo "🔐 Updating file permissions..."
sudo chown -R www-data:www-data /var/www/bakery-app
sudo chmod -R 755 /var/www/bakery-app
sudo chmod -R 775 /var/www/bakery-app/storage
sudo chmod -R 775 /var/www/bakery-app/bootstrap/cache

# Restart services
echo "🔄 Restarting services..."
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

# Check service status
echo "✅ Checking service status..."
if sudo systemctl is-active --quiet nginx && sudo systemctl is-active --quiet php8.2-fpm; then
    echo "✅ All services are running successfully!"
    echo "🌐 Your updated app is live at: http://16.16.99.167:8080"
else
    echo "❌ Some services failed to start. Check logs with:"
    echo "   sudo systemctl status nginx"
    echo "   sudo systemctl status php8.2-fpm"
fi

echo "========================================"
echo "🎉 BakeHub Update Deployment Complete!"
