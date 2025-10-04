# 🎯 AWS Laravel Deployment Checklist

## Pre-Deployment ✅
- [ ] AWS account created and verified
- [ ] EC2 t2.micro instance launched
- [ ] Security Group configured (HTTP, HTTPS, SSH)
- [ ] Key pair (.pem file) downloaded and secured
- [ ] SSH connection to EC2 successful

## Server Setup ✅
- [ ] System packages updated (`sudo apt update && upgrade`)
- [ ] Apache2 installed and running
- [ ] MySQL installed and secured
- [ ] PHP 8.1 with all Laravel extensions installed
- [ ] Composer installed globally
- [ ] Git installed

## Database Setup ✅
- [ ] MySQL root password set
- [ ] `bakery_db` database created
- [ ] `bakery_user` with strong password created
- [ ] Database privileges granted
- [ ] Connection tested

## Laravel Project Setup ✅
- [ ] Project cloned/uploaded to `/var/www/html/bakery-app`
- [ ] Composer dependencies installed
- [ ] File permissions set correctly (755/775)
- [ ] Ownership set to www-data
- [ ] .env file configured with production settings
- [ ] APP_KEY generated
- [ ] Database migrations run successfully

## Web Server Configuration ✅
- [ ] Apache virtual host created
- [ ] Site enabled, default site disabled
- [ ] mod_rewrite enabled
- [ ] Apache restarted successfully
- [ ] Website accessible via public IP

## Security & Performance ✅
- [ ] APP_DEBUG=false in production
- [ ] Strong database passwords used
- [ ] Laravel caches optimized
- [ ] Storage link created
- [ ] Log files monitored
- [ ] SSL certificate installed (if domain available)

## Testing ✅
- [ ] Homepage loads successfully
- [ ] Admin login works
- [ ] Database operations functional
- [ ] File uploads work
- [ ] All major features tested

## Post-Deployment ✅
- [ ] Database backup script created
- [ ] Monitoring setup
- [ ] Domain pointed to server (if applicable)
- [ ] Email service configured
- [ ] Regular update schedule planned

## Commands for Quick Testing:

```bash
# Test Apache
sudo systemctl status apache2

# Test MySQL connection
sudo -u www-data php artisan tinker
# In tinker: DB::connection()->getPdo();

# Test Laravel
curl http://YOUR_EC2_IP

# Check logs
sudo tail -f /var/www/html/bakery-app/storage/logs/laravel.log

# Check file permissions
ls -la /var/www/html/bakery-app/

# Test artisan commands
cd /var/www/html/bakery-app
sudo -u www-data php artisan route:list
```

## Troubleshooting Commands:

```bash
# If website shows 500 error
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/www/html/bakery-app/storage/logs/laravel.log

# If permission issues
sudo chown -R www-data:www-data /var/www/html/bakery-app
sudo chmod -R 755 /var/www/html/bakery-app
sudo chmod -R 775 /var/www/html/bakery-app/storage

# If composer issues
cd /var/www/html/bakery-app
sudo -u www-data composer install --no-dev --optimize-autoloader

# Clear all caches
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
```

## Success Indicators:
✅ Website loads at http://YOUR_EC2_IP
✅ Admin panel accessible
✅ Database operations work
✅ File uploads functional
✅ No errors in logs

## Next Steps After Success:
1. Point domain to server
2. Install SSL certificate
3. Setup automated backups
4. Configure email service
5. Setup monitoring and alerts

## Emergency Contacts & Resources:
- AWS Support: https://console.aws.amazon.com/support/
- Laravel Docs: https://laravel.com/docs
- Your EC2 Instance IP: [Write it here]
- Your Domain (if any): [Write it here]
- Database Credentials: [Keep secure backup]
