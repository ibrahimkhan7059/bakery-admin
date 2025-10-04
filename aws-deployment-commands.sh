#!/bin/bash

# 🚀 AWS Laravel Deployment - All Commands in One Script
# Copy-paste these commands step by step on your EC2 instance

echo "🎯 Starting BakeHub Laravel Deployment on AWS EC2..."
echo "==============================================="

# STEP 1: Update System
echo "📦 Step 1: Updating system packages..."
sudo apt update && sudo apt upgrade -y

# STEP 2: Install LAMP Stack
echo "⚡ Step 2: Installing Apache..."
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2

echo "🛢️ Installing MySQL..."
sudo apt install mysql-server -y
sudo systemctl start mysql
sudo systemctl enable mysql

echo "🐘 Installing PHP 8.1..."
sudo apt install php8.1 \
  php8.1-mysql \
  php8.1-xml \
  php8.1-gd \
  php8.1-curl \
  php8.1-mbstring \
  php8.1-zip \
  php8.1-intl \
  php8.1-bcmath \
  php8.1-tokenizer \
  php8.1-json \
  php8.1-fileinfo \
  libapache2-mod-php8.1 -y

sudo systemctl restart apache2

echo "🎵 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

echo "📚 Installing Git..."
sudo apt install git unzip -y

# STEP 3: Setup Database
echo "🛢️ Step 3: Setting up MySQL database..."
echo "Run these commands manually in MySQL:"
echo "sudo mysql -u root -p"
echo "CREATE DATABASE bakery_db;"
echo "CREATE USER 'bakery_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';"
echo "GRANT ALL PRIVILEGES ON bakery_db.* TO 'bakery_user'@'localhost';"
echo "FLUSH PRIVILEGES;"
echo "EXIT;"

# STEP 4: Clone Laravel Project
echo "📥 Step 4: Cloning Laravel project..."
cd /var/www/html
sudo rm -f index.html

# Replace with your actual GitHub repository
echo "Replace 'YOUR_GITHUB_URL' with your actual repository URL"
echo "sudo git clone YOUR_GITHUB_URL bakery-app"

# Example:
# sudo git clone https://github.com/ibrahimkhan7059/bakery-app.git bakery-app

# STEP 5: Configure Laravel
echo "⚙️ Step 5: Configuring Laravel..."
cd /var/www/html/bakery-app

# Install dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev

# Set permissions
sudo chmod -R 755 /var/www/html/bakery-app
sudo chmod -R 775 /var/www/html/bakery-app/storage
sudo chmod -R 775 /var/www/html/bakery-app/bootstrap/cache
sudo chown -R www-data:www-data /var/www/html/bakery-app

# Environment setup
sudo cp .env.example .env
sudo chown www-data:www-data .env
sudo -u www-data php artisan key:generate

# STEP 6: Apache Virtual Host
echo "🌐 Step 6: Setting up Apache Virtual Host..."
sudo tee /etc/apache2/sites-available/bakery-app.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName $(curl -s http://checkip.amazonaws.com/)
    DocumentRoot /var/www/html/bakery-app/public

    <Directory /var/www/html/bakery-app/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/bakery-app_error.log
    CustomLog \${APACHE_LOG_DIR}/bakery-app_access.log combined
</VirtualHost>
EOF

# Enable site and modules
sudo a2ensite bakery-app.conf
sudo a2dissite 000-default.conf
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2

echo "✅ Basic setup complete!"
echo ""
echo "🔧 Next Manual Steps:"
echo "1. Run: sudo mysql_secure_installation"
echo "2. Create database in MySQL (commands shown above)"
echo "3. Edit /var/www/html/bakery-app/.env file with database credentials"
echo "4. Run: sudo -u www-data php artisan migrate --force"
echo "5. Test your site: http://$(curl -s http://checkip.amazonaws.com/)"
echo ""
echo "🌐 Your server IP: $(curl -s http://checkip.amazonaws.com/)"
