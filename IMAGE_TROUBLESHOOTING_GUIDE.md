# Product Image Troubleshooting Guide

## Problem
Product images upload successfully on localhost but don't show on the server/production environment.

## Common Causes & Solutions

### 1. Storage Symbolic Link Missing or Broken

The most common issue is that the symbolic link from `public/storage` to `storage/app/public` is missing or not working properly on the server.

**Solution:**

**On Server (via SSH or Terminal):**
```bash
cd /path/to/your/bakery-app
php artisan storage:link
```

**If the link already exists and shows an error:**
```bash
# Remove the existing link
rm public/storage

# Recreate the link
php artisan storage:link
```

**For Windows Servers:**
If you're deploying to a Windows server, symbolic links might not work properly. Instead:

1. **Option A: Create a manual copy (not recommended for production)**
   ```cmd
   mklink /D "C:\path\to\public\storage" "C:\path\to\storage\app\public"
   ```

2. **Option B: Store images directly in public folder**
   Update the ProductController to save images in `public/products` instead:
   
   In `ProductController.php`, change:
   ```php
   Storage::disk('public')->put($path, $img->toJpeg($quality));
   ```
   to:
   ```php
   Storage::disk('public_direct')->put($path, $img->toJpeg($quality));
   ```
   
   Then add this to `config/filesystems.php`:
   ```php
   'public_direct' => [
       'driver' => 'local',
       'root' => public_path('products'),
       'url' => env('APP_URL').'/products',
       'visibility' => 'public',
   ],
   ```

### 2. File Permissions Issue

The server might not have proper permissions to read the uploaded images.

**Solution (Linux/Unix servers):**
```bash
# Set proper permissions for storage folder
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Set proper ownership (replace 'www-data' with your server's web user)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

**For specific folders:**
```bash
chmod -R 775 storage/app/public
chown -R www-data:www-data storage/app/public
```

### 3. .htaccess Configuration

If you're using Apache, ensure `.htaccess` is properly configured.

**Check `public/.htaccess`:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Allow access to storage folder
<IfModule mod_alias.c>
    Alias /storage/ /path/to/storage/app/public/
</IfModule>
```

### 4. Missing Files on Server

Images might not have been uploaded to the server during deployment.

**Solution:**
Ensure your deployment process includes the storage folder:

```bash
# Check if images exist on server
ls -la storage/app/public/products/

# If empty, transfer from local
scp -r storage/app/public/products/* user@server:/path/to/storage/app/public/products/
```

### 5. URL Configuration Issue

The application URL might not be correctly set.

**Check `.env` file:**
```env
APP_URL=https://yourdomain.com
```

Make sure:
- No trailing slash
- Correct protocol (http vs https)
- Correct domain

**Clear config cache:**
```bash
php artisan config:clear
php artisan config:cache
```

### 6. CORS or Security Headers

If using a CDN or separate domain for images, CORS might be blocking access.

**Add to `public/.htaccess`:**
```apache
<IfModule mod_headers.c>
    <FilesMatch "\.(jpg|jpeg|png|gif|svg|webp)$">
        Header set Access-Control-Allow-Origin "*"
    </FilesMatch>
</IfModule>
```

### 7. Image Path Debugging

Add debugging to check if images exist:

**In your blade template:**
```blade
@if($product->image)
    <!-- Debug: Show full path -->
    <p class="small text-muted">Storage Path: {{ $product->image }}</p>
    <p class="small text-muted">Full URL: {{ asset('storage/' . $product->image) }}</p>
    <p class="small text-muted">File Exists: {{ Storage::disk('public')->exists($product->image) ? 'Yes' : 'No' }}</p>
    
    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
@endif
```

### 8. Check Storage Disk Configuration

**In `config/filesystems.php`:**
```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
        'throw' => false,
    ],
],
```

## Quick Diagnostic Checklist

Run these checks on your server:

```bash
# 1. Check if symbolic link exists
ls -la public/storage

# 2. Check if images directory exists
ls -la storage/app/public/products/

# 3. Check if images are there
ls -la storage/app/public/products/ | head -10

# 4. Check permissions
ls -la storage/app/public/

# 5. Test direct access
curl -I https://yourdomain.com/storage/products/imagename.jpg
```

## Testing Image Upload

To verify the complete flow:

1. **Upload a test image locally**
2. **Check database** - ensure image path is saved correctly
3. **Check file system** - verify file exists in `storage/app/public/products/`
4. **Check browser** - inspect element on the image and check the src attribute
5. **Check server logs** - Look in `storage/logs/laravel.log` for errors

## Alternative Solution: Use Database Storage Path

If symbolic links continue to be problematic, you can modify the image storage approach:

**In `ProductController.php`:**
```php
// Store with full path
$validated['image'] = 'storage/products/' . $filename;

// In the view
<img src="{{ asset($product->image) }}" alt="{{ $product->name }}">
```

## Server-Specific Solutions

### cPanel/Shared Hosting:
1. Use File Manager to create symbolic link manually
2. Or store images in `public_html/products` directly

### VPS/Dedicated Server:
1. Ensure proper permissions with `chmod` and `chown`
2. Check if SELinux is blocking access (if using CentOS/RHEL)

### Docker:
1. Ensure volumes are properly mounted
2. Check container permissions

## Contact Your Hosting Provider

If none of these solutions work, your hosting provider might have:
- Disabled symbolic links
- Special permission requirements
- Custom server configuration
- Specific deployment guidelines

Contact them with this specific question:
"I need to create symbolic links from `public/storage` to `storage/app/public` for Laravel file storage. How can I do this on your hosting environment?"

## Final Notes

- Always backup before making changes
- Test changes on staging environment first
- Monitor error logs during testing
- Document what works for your specific setup
