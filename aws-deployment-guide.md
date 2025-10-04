# AWS Laravel Deployment Guide - Manual EC2 (Full Control)

## 🎯 Why Manual EC2 Setup?
- **Full Control**: Complete server configuration control
- **Cost Effective**: Most economical option (~$12-15/month after free tier)
- **Learning**: Best way to understand server management
- **Scalability**: Easy to scale and customize later
- **Performance**: Optimized specifically for your application

## Pre-Requirements Checklist:
- [ ] AWS Account created and verified
- [ ] Credit card added (for free tier)
- [ ] Your Laravel project ready (GitHub repo recommended)
- [ ] Domain name (optional but recommended)
- [ ] SSH client installed (PuTTY for Windows or Git Bash)

## Step 1: EC2 Instance Setup

### Create EC2 Instance (Detailed Steps):

#### 1. Launch Instance:
1. **AWS Console** → **EC2** → **Launch Instance**
2. **Name**: `BakeHub-Production-Server`
3. **Application and OS Images**: 
   - **Ubuntu Server 22.04 LTS** (Free tier eligible)
   - **Architecture**: 64-bit (x86)

#### 2. Instance Type:
- **t2.micro** (1 vCPU, 1 GB Memory) - Free tier eligible
- For production with more traffic, consider **t3.small** later

#### 3. Key Pair (Login):
- **Create new key pair**
- **Name**: `bakehub-key`
- **Type**: RSA
- **Format**: .pem (for OpenSSH)
- **Download and save securely** - You'll need this to connect!

#### 4. Network Settings (Security Group):
- **Create security group**: `BakeHub-SG`
- **Allow SSH traffic from**: Your IP only (more secure)
- **Allow HTTP traffic from**: Internet (0.0.0.0/0)
- **Allow HTTPS traffic from**: Internet (0.0.0.0/0)

#### 5. Storage:
- **8 GB** gp3 (Free tier eligible)
- **Encrypted**: Yes (for security)

#### 6. Advanced Details:
- Keep defaults for now
- **User Data** (optional): Leave empty

#### 7. Launch Instance:
- Review all settings
- **Launch Instance**
- Wait 2-3 minutes for initialization

### Connect to EC2 (Windows Users):

#### Method 1: Git Bash (Recommended)
```bash
# Download Git for Windows if not installed
# Open Git Bash in the folder where your .pem file is saved

# Set permissions for key file
chmod 400 bakehub-key.pem

# Connect to server (replace with your actual IP)
ssh -i "bakehub-key.pem" ubuntu@your-ec2-public-ip

# Example:
ssh -i "bakehub-key.pem" ubuntu@54.123.45.67
```

#### Method 2: PuTTY
```
1. Download PuTTY and PuTTYgen
2. Convert .pem to .ppk using PuTTYgen
3. Use .ppk file in PuTTY for authentication
4. Connect to: ubuntu@your-ec2-public-ip:22
```

#### First Login Commands:
```bash
# Update system packages (always do this first!)
sudo apt update && sudo apt upgrade -y

# Check system info
uname -a
free -h
df -h
```

## Step 2: Install Required Software (LAMP Stack)

### Install Apache Web Server:
```bash
# Install Apache
sudo apt install apache2 -y

# Start and enable Apache
sudo systemctl start apache2
sudo systemctl enable apache2

# Check status
sudo systemctl status apache2

# Test: Visit http://your-ec2-public-ip
# You should see "Apache2 Ubuntu Default Page"
```

### Install MySQL Database Server:
```bash
# Install MySQL
sudo apt install mysql-server -y

# Start MySQL service
sudo systemctl start mysql
sudo systemctl enable mysql

# Secure MySQL installation (IMPORTANT!)
sudo mysql_secure_installation

# Follow the prompts:
#
# - Remove anonymous users: YES
# - Disallow root login remotely: YES
# - Remove test database: YES
# - Reload privilege tables: YES
```

### Install PHP 8.2 with Required Extensions:
```bash
# Install PHP and essential extensions for Laravel (Ubuntu 22.04/24.04)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 \
  php8.2-mysql \
  php8.2-xml \
  php8.2-gd \
  php8.2-curl \
  php8.2-mbstring \
  php8.2-zip \
  php8.2-intl \
  php8.2-bcmath \
  libapache2-mod-php8.2 -y

# Verify PHP installation
php -v
php -m | grep -i mysql

# Restart Apache to load PHP module
sudo systemctl restart apache2
```

### Install Composer (PHP Package Manager):
```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php

# Make it globally accessible
sudo mv composer.phar /usr/local/bin/composer

# Verify installation
composer --version

# Should show: Composer version x.x.x
```

### Install Git (for cloning repository):
```bash
# Install Git
sudo apt install git -y

# Configure Git (optional but recommended)
git config --global user.name "Your Name"
git config --global user.email "your-email@example.com"

# Verify
git --version
```

## Step 3: Upload and Setup Laravel Project

### Method 1: Git Clone (Recommended for GitHub repos):
```bash
# Navigate to web directory
cd /var/www/html

# Remove default Apache page
sudo rm index.html

# Clone your repository (replace with your actual repo URL)
sudo git clone https://github.com/ibrahimkhan7059/bakery-app.git bakery-app

# If private repo, you'll need to authenticate:
# sudo git clone https://username:token@github.com/username/repo.git bakery-app

# Set proper ownership
sudo chown -R www-data:www-data bakery-app
sudo chown -R ubuntu:ubuntu bakery-app  # For your user access
```

### Method 2: Upload via SCP (if no GitHub):
```bash
# From your local machine (Git Bash/PowerShell):
# Replace <path-to-key> with the full path to your bakehub-key.pem file
# Replace <your-ec2-ip> with your EC2 public IP (e.g., 16.171.148.59)
# Do NOT include 'http://' or angle brackets in the IP address!untu/
scp -i "C:/Users/IK/Downloads/bakehub-key.pem" -r "C:/xampp/htdocs/FYP/bakery-app" ubuntu@16.171.148.59:/home/ubuntu/

# If you see 'No such file or directory', double-check the IP address and remove any 'http://' or brackets.
```

### Method 3: Create ZIP and Upload:
```bash
# Create ZIP of your project locally
# Upload via SCP:
scp -i "bakehub-key.pem" bakery-app.zip ubuntu@your-ec2-ip:/home/ubuntu/

# Extract on server:
cd /home/ubuntu
unzip bakery-app.zip
sudo mv bakery-app /var/www/html/
sudo chown -R www-data:www-data /var/www/html/bakery-app
```

### Configure Laravel Application:
```bash
# Navigate to project directory
cd /var/www/html/bakery-app

# Install Composer dependencies (this may take a few minutes)
sudo -u www-data composer install --optimize-autoloader --no-dev

# Set proper permissions for Laravel
sudo chmod -R 755 /var/www/html/bakery-app
sudo chmod -R 775 /var/www/html/bakery-app/storage
sudo chmod -R 775 /var/www/html/bakery-app/bootstrap/cache

# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/bakery-app

# Copy environment configuration
sudo cp .env.example .env
sudo chown www-data:www-data .env

# Generate application key
sudo -u www-data php artisan key:generate

# Verify the key was generated
sudo cat .env | grep APP_KEY
```

## Step 4: Database Setup

### Create MySQL Database:
```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE bakery_db;
CREATE USER 'bakery_user'@'localhost' IDENTIFIED BY 'Mik@125863';
GRANT ALL PRIVILEGES ON bakery_db.* TO 'bakery_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Configure .env file:
```bash
sudo nano .env
```

Update these values:
```env
APP_NAME="BakeHub"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-ec2-public-ip

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bakery_db
DB_USERNAME=bakery_user
DB_PASSWORD=your_strong_password

# Add other production settings
LOG_CHANNEL=daily
LOG_LEVEL=error
```

### Run Migrations:
```bash
cd /var/www/html/bakery-app
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --force (if you have seeders)
```

## Step 5: Apache Configuration

### Create Virtual Host:
```bash
sudo nano /etc/apache2/sites-available/bakery-app.conf
```

Add this configuration:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/bakery-app/public

    <Directory /var/www/html/bakery-app/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/bakery-app_error.log
    CustomLog ${APACHE_LOG_DIR}/bakery-app_access.log combined
</VirtualHost>
```

### Enable Site and Modules:
```bash
# Enable site
sudo a2ensite bakery-app.conf
sudo a2dissite 000-default.conf

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod headers

# Restart Apache
sudo systemctl restart apache2
```

## Step 6: Security & Performance

### SSL Certificate (Let's Encrypt):
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get SSL certificate (after domain is pointed to server)
sudo certbot --apache -d your-domain.com
```

### Optimize Laravel:
```bash
cd /var/www/html/bakery-app

# Cache configurations
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Create symbolic link for storage
sudo -u www-data php artisan storage:link
```

## Step 7: Domain Setup (Optional)

### Point Domain to EC2:
1. Go to your domain registrar
2. Add A record: @ → your-ec2-public-ip
3. Add A record: www → your-ec2-public-ip
4. Wait for DNS propagation (24-48 hours)

## Step 8: Monitoring & Backup

### Setup Log Monitoring:
```bash
# View Laravel logs
sudo tail -f /var/www/html/bakery-app/storage/logs/laravel.log

# View Apache logs
sudo tail -f /var/log/apache2/bakery-app_error.log
```

### Database Backup Script:
```bash
# Create backup script
sudo nano /home/ubuntu/backup-db.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u bakery_user -p'your_strong_password' bakery_db > /home/ubuntu/backups/bakery_db_$DATE.sql
find /home/ubuntu/backups/ -name "*.sql" -mtime +7 -delete
```

## Step 9: Testing

### Test Your Application:
1. Visit: `http://your-ec2-public-ip`
2. Test all functionalities
3. Check admin dashboard
4. Test database operations
5. Check file uploads work

## Troubleshooting

### Common Issues:

1. **Permission Issues:**
```bash
sudo chown -R www-data:www-data /var/www/html/bakery-app
sudo chmod -R 755 /var/www/html/bakery-app
sudo chmod -R 775 /var/www/html/bakery-app/storage
sudo chmod -R 775 /var/www/html/bakery-app/bootstrap/cache
```

2. **Apache Not Starting:**
```bash
sudo systemctl status apache2
sudo journalctl -u apache2
```

3. **Database Connection Issues:**
```bash
# Test connection
sudo -u www-data php artisan tinker
DB::connection()->getPdo();
```

4. **Laravel Errors:**
```bash
# Clear all caches
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
```

### Troubleshooting SCP/SSH Permission Denied (publickey)
#
# - Make sure you are using the exact .pem key file downloaded when creating the EC2 instance.
# - The key file permissions must be set to 400:
chmod 400 /path/to/bakehub-key.pem
# - The username for Ubuntu AMIs is always 'ubuntu'.
# - If you get 'Permission denied (publickey)', double-check the key path, permissions, and username.
# - If you lost the key, you must create a new instance with a new key pair (AWS does not allow adding keys to running instances for security).

# Troubleshooting: If you see '-bash: cd: /var/www/html/bakery-app: No such file or directory',
# - The upload may have failed or the folder is in a different location.
# - Run 'ls /var/www/html' to check if 'bakery-app' exists.
# - If not, repeat the upload step and ensure there are no SCP/SSH errors.

## Cost Optimization

### Free Tier Limits:
- EC2 t2.micro: 750 hours/month (always free for 12 months)
- EBS Storage: 30GB/month
- Data Transfer: 15GB/month

### Tips:
1. Use t2.micro instance (free tier)
2. Monitor usage via AWS CloudWatch
3. Set up billing alerts
4. Stop instance when not needed (development)

## Next Steps After Deployment

1. Setup automated backups
2. Configure monitoring (CloudWatch)
3. Setup staging environment
4. Implement CI/CD pipeline
5. Add load balancing (if needed)
6. Setup email service (SES)
7. Add CDN (CloudFront)

## Security Checklist

- [x] SSH key-based authentication
- [x] Firewall configured (Security Groups)
- [x] SSL certificate installed
- [x] Database passwords strong
- [x] APP_DEBUG=false in production
- [x] Regular backups scheduled
- [x] System updates automated
