# 🚀 Laravel Deployment Guide - www.mitt.com

## Server Information
- **Control Panel:** http://192.250.235.71:2082/
- **Username:** mittitco
- **Password:** XR:8N332ftNre
- **Domain:** www.mitt.com

---

## ✅ Step 1: Build Completed
Production assets have been built and are ready in `public/build/`

---

## 📋 Step 2: Create Database in cPanel

### 2.1. Login to cPanel
- Visit: http://192.250.235.71:2082/
- Username: `mittitco`
- Password: `XR:8N332ftNre`

### 2.2. Create MySQL Database
1. Search for **"MySQL Databases"** in cPanel
2. Click on **"MySQL Databases"**
3. Create a new database:
   - Database Name: `mittitco_office_app` (or any name you prefer)
   - Click **"Create Database"**

### 2.3. Create Database User
1. Scroll down to **"MySQL Users"** section
2. Create a new user:
   - Username: `mittitco_admin`
   - Password: Create a strong password (save it!)
   - Click **"Create User"**

### 2.4. Add User to Database
1. Scroll to **"Add User To Database"** section
2. Select:
   - User: `mittitco_admin`
   - Database: `mittitco_office_app`
3. Click **"Add"**
4. Select **"ALL PRIVILEGES"**
5. Click **"Make Changes"**

### 2.5. Note These Details:
```
Database Name: mittitco_office_app
Database User: mittitco_admin
Database Password: [your_password_here]
Database Host: localhost
```

---

## 📦 Step 3: Prepare Files for Upload

### 3.1. Create ZIP File (Exclude unnecessary files)

Run this command in your local terminal:

```bash
cd /Users/mashiurrahman/Desktop/officeM/office_management

# Create a deployment ZIP (excluding node_modules, .git, etc.)
zip -r office_app_deploy.zip . \
  -x "node_modules/*" \
  -x ".git/*" \
  -x ".env" \
  -x "storage/logs/*" \
  -x "*.log" \
  -x ".DS_Store"
```

This will create `office_app_deploy.zip` file.

---

## 📤 Step 4: Upload Files to cPanel

### 4.1. Open File Manager
1. Login to cPanel
2. Click **"File Manager"**
3. Navigate to `public_html/`

### 4.2. Create Application Folder
1. Click **"+ Folder"** button
2. Name: `office_app`
3. Click **"Create New Folder"**

### 4.3. Upload ZIP File
1. Open the `office_app` folder
2. Click **"Upload"** button
3. Select `office_app_deploy.zip` from your computer
4. Wait for upload to complete

### 4.4. Extract Files
1. Go back to File Manager
2. Right-click on `office_app_deploy.zip`
3. Click **"Extract"**
4. Click **"Extract File(s)"**
5. Wait for extraction to complete
6. Delete the ZIP file after extraction

---

## ⚙️ Step 5: Configure Production Environment

### 5.1. Create .env File
1. In File Manager, navigate to `public_html/office_app/`
2. Find `.env.example` file
3. Right-click → **Copy**
4. Name it `.env`

### 5.2. Edit .env File
1. Right-click on `.env` → **Edit**
2. Update these values:

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:qoFF1kzWguwsp0XQ05Rj/4woZcDEjTkJcJ4G15Nxg4g=
APP_DEBUG=false
APP_URL=http://www.mitt.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mittitco_office_app
DB_USERNAME=mittitco_admin
DB_PASSWORD=[your_database_password]

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=error
```

3. Click **"Save Changes"**

---

## 🔧 Step 6: Install Composer Dependencies

### Option A: Using cPanel Terminal (Recommended)

1. In cPanel, search for **"Terminal"**
2. Click to open Terminal
3. Run these commands:

```bash
cd public_html/office_app

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Set permissions
chmod -R 775 storage bootstrap/cache

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Option B: If Terminal Not Available

Upload the `vendor` folder from your local machine:
1. In your local terminal: `composer install --optimize-autoloader --no-dev`
2. ZIP the vendor folder: `zip -r vendor.zip vendor`
3. Upload `vendor.zip` to cPanel
4. Extract it in the `office_app` folder

Then set permissions via File Manager:
- Right-click `storage` folder → **Permissions** → Set to `775`
- Right-click `bootstrap/cache` folder → **Permissions** → Set to `775`

---

## 🌐 Step 7: Configure Domain

### Method 1: Using Addon Domain (Recommended)

1. In cPanel, go to **"Domains"** or **"Addon Domains"**
2. Add domain:
   - Domain: `www.mitt.com`
   - Document Root: `public_html/office_app/public`
3. Click **"Add Domain"**

### Method 2: Using .htaccess Redirect (If main domain)

If `www.mitt.com` is your main domain, create/edit `.htaccess` in `public_html/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ office_app/public/$1 [L]
</IfModule>
```

---

## 🔒 Step 8: Set File Permissions

Ensure correct permissions (if not done via Terminal):

1. **storage/** folder and all subfolders: `775`
2. **bootstrap/cache/** folder: `775`
3. All files inside these folders: `664`

To set permissions:
- Right-click folder → **Permissions**
- Check: Owner (Read, Write, Execute), Group (Read, Write, Execute), World (Read, Execute)
- Apply to all subdirectories

---

## 🗄️ Step 9: Run Database Migrations

### If using Terminal:
```bash
cd public_html/office_app
php artisan migrate --force
```

### If no Terminal access:
You'll need to request SSH access from your hosting provider to run migrations.

---

## 🧪 Step 10: Test Your Application

1. Open browser
2. Visit: `http://www.mitt.com`
3. You should see the login page

### Default Test Login
If you have seeded data:
- Email: (check your database or seeders)
- Password: (check your database or seeders)

---

## 🐛 Troubleshooting

### Issue: 500 Internal Server Error
**Solution:**
1. Check `storage/logs/laravel.log` for errors
2. Ensure `.env` file exists and is configured correctly
3. Check file permissions (775 for folders, 664 for files)
4. Clear cache: `php artisan config:clear`

### Issue: Database Connection Error
**Solution:**
1. Verify database credentials in `.env`
2. Ensure database and user exist in cPanel MySQL
3. Check if user has all privileges on the database

### Issue: CSS/JS Not Loading
**Solution:**
1. Ensure `public/build` folder exists with compiled assets
2. Check `APP_URL` in `.env` matches your domain
3. Clear browser cache
4. Check `.htaccess` file exists in `public/` folder

### Issue: White Screen / No Content
**Solution:**
1. Enable debug mode temporarily: `APP_DEBUG=true` in `.env`
2. Check `storage/logs/laravel.log`
3. Ensure `APP_KEY` is set in `.env`

---

## 📝 Post-Deployment Checklist

- [ ] Database created and configured
- [ ] .env file configured with correct values
- [ ] Composer dependencies installed
- [ ] Database migrations run successfully
- [ ] File permissions set correctly (775/664)
- [ ] Domain pointing to `public` folder
- [ ] Application accessible via browser
- [ ] Login functionality working
- [ ] Test all major features

---

## 🔐 Security Recommendations

1. **Change APP_KEY** if you haven't:
   ```bash
   php artisan key:generate
   ```

2. **Set APP_DEBUG=false** in production

3. **Use strong database password**

4. **Keep .env file secure** (never commit to git)

5. **Regular backups** of database and files

---

## 📞 Need Help?

If you encounter any issues:
1. Check `storage/logs/laravel.log` for detailed errors
2. Ensure all steps were followed correctly
3. Contact your hosting provider for server-specific issues

---

## 🎉 Deployment Complete!

Your Laravel application should now be live at **http://www.mitt.com**

Enjoy your deployed application! 🚀
