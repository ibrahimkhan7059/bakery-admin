#!/bin/bash

# AWS Laravel Deployment Quick Setup Script
# Run this on your Ubuntu EC2 instance

echo "🚀 Starting Laravel Deployment on AWS..."

# Update system
echo "📦 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install LAMP Stack
echo "⚡ Installing Apache, MySQL, PHP..."
sudo apt install apache2 mysql-server -y
sudo apt install php8.1 php8.1-mysql php8.1-xml php8.1-gd php8.1-curl php8.1-mbstring php8.1-zip php8.1-intl php8.1-bcmath libapache2-mod-php8.1 -y

# Install Composer
echo "🎵 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Start services
sudo systemctl start apache2
sudo systemctl start mysql
sudo systemctl enable apache2
sudo systemctl enable mysql

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod headers

echo "✅ Basic setup complete!"
echo "📝 Next steps:"
echo "1. Run: sudo mysql_secure_installation"
echo "2. Create database and user in MySQL"
echo "3. Upload your Laravel project to /var/www/html/"
echo "4. Configure .env file"
echo "5. Run migrations"
echo "6. Setup Apache virtual host"

echo "🔗 Your server IP: $(curl -s http://checkip.amazonaws.com/)"
