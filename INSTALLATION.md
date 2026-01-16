# Installation Guide
## Intellectual Property Repository Management System

This guide will walk you through the complete installation process.

---

## Prerequisites

Before you begin, ensure you have:
- ✅ XAMPP (or WAMP/LAMP) installed
- ✅ PHP 8.0 or higher
- ✅ MySQL 5.7 or higher
- ✅ Apache with mod_rewrite enabled
- ✅ Web browser (Chrome, Firefox, Edge recommended)

---

## Step-by-Step Installation

### Step 1: Extract Files

1. Extract the project folder to your web server directory:
   - **XAMPP:** `C:\xampp\htdocs\`
   - **WAMP:** `C:\wamp64\www\`
   - **LAMP:** `/var/www/html/`

2. The folder should be named exactly:
   ```
   Intellectual Property Repository Management System
   ```

### Step 2: Start Services

1. Open **XAMPP Control Panel**
2. Start **Apache** service
3. Start **MySQL** service
4. Verify both services show "Running" status

### Step 3: Create Database

**Option A: Using phpMyAdmin (Recommended)**

1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click on **"New"** in left sidebar
3. Database name: `ip_repository_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click **"Create"**

**Option B: Using MySQL Command Line**

```sql
CREATE DATABASE ip_repository_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 4: Import Database Schema

1. In phpMyAdmin, select `ip_repository_db` database
2. Click **"Import"** tab at the top
3. Click **"Choose File"** button
4. Navigate to:
   ```
   Intellectual Property Repository Management System/database/schema.sql
   ```
5. Click **"Go"** button at bottom
6. Wait for success message: "Import has been successfully finished"

### Step 5: Configure Application

1. Open the file:
   ```
   Intellectual Property Repository Management System/config/config.php
   ```

2. Verify/Update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ip_repository_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Leave empty for default XAMPP
   ```

3. Verify BASE_URL (usually correct by default):
   ```php
   define('BASE_URL', 'http://localhost/Intellectual%20Property%20Repository%20Management%20System/public');
   ```

### Step 6: Set Directory Permissions

**For Windows (XAMPP):**
- Right-click on `uploads` folder
- Properties → Security tab
- Edit → Add → Everyone
- Grant "Full control"
- Click OK

**For Linux/Mac:**
```bash
cd "Intellectual Property Repository Management System"
chmod -R 755 uploads/
chmod -R 755 uploads/documents/
chmod -R 755 uploads/trash/
```

### Step 7: Verify Apache Configuration

1. Open XAMPP Control Panel
2. Click **"Config"** button next to Apache
3. Select **"Apache (httpd.conf)"**
4. Find and ensure this line is NOT commented out:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
5. Find the `<Directory>` section and ensure:
   ```apache
   <Directory "C:/xampp/htdocs">
       AllowOverride All
   </Directory>
   ```
6. Save file and restart Apache

### Step 8: Access the Application

1. Open your web browser
2. Navigate to:
   ```
   http://localhost/Intellectual%20Property%20Repository%20Management%20System/public
   ```
   
   OR (if spaces cause issues):
   ```
   http://localhost/Intellectual Property Repository Management System/public
   ```

3. You should see the **Login Page**

### Step 9: First Login

Use the default admin credentials:

**Administrator Account:**
- Username: `admin`
- Password: `Admin@123`

**Staff Account:**
- Username: `staff`
- Password: `Staff@123`

---

## Post-Installation Steps

### 1. Change Default Passwords

⚠️ **CRITICAL FOR SECURITY**

1. Login as admin
2. Navigate to Users section
3. Update passwords for both accounts

### 2. Create Additional Users

1. Go to **Users** menu
2. Click **"Add User"**
3. Fill in:
   - Username
   - Email
   - Password
   - Full Name
   - Role (Admin or Staff)
4. Click **"Create"**

### 3. Configure Upload Settings (Optional)

Edit `config/config.php`:

```php
// Maximum file size (in bytes)
define('MAX_FILE_SIZE', 10485760); // 10MB default

// Allowed file types
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

// Token expiry (in hours)
define('TOKEN_EXPIRY_HOURS', 24);

// Default download limit
define('DEFAULT_DOWNLOAD_LIMIT', 3);
```

---

## Troubleshooting

### Issue: "404 Page Not Found"

**Solution:**
1. Check if `mod_rewrite` is enabled in Apache
2. Verify `.htaccess` file exists in `public/` folder
3. Check `AllowOverride All` in httpd.conf
4. Restart Apache

### Issue: "Database Connection Failed"

**Solution:**
1. Verify MySQL service is running
2. Check database credentials in `config/config.php`
3. Ensure database `ip_repository_db` exists
4. Test connection in phpMyAdmin

### Issue: "Permission Denied" on File Upload

**Solution:**
1. Set correct permissions on `uploads/` folder
2. Check PHP user has write permissions
3. Verify folder ownership

### Issue: Login Page Doesn't Load

**Solution:**
1. Clear browser cache
2. Check Apache error logs:
   - XAMPP: `C:\xampp\apache\logs\error.log`
3. Verify PHP version (must be 8.0+):
   ```
   php -v
   ```

### Issue: Blank White Page

**Solution:**
1. Enable error reporting in `config/config.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
2. Check PHP error logs
3. Verify all files were extracted properly

---

## Verification Checklist

✅ XAMPP Apache and MySQL running  
✅ Database created successfully  
✅ Schema imported without errors  
✅ Config file updated with correct settings  
✅ Upload folders have proper permissions  
✅ mod_rewrite enabled  
✅ Login page loads correctly  
✅ Can login with default credentials  

---

## Quick Start Guide

After successful installation:

### As Administrator:

1. **Add Users**
   - Users menu → Add User
   - Create staff accounts

2. **Create IP Records**
   - IP Records → Create New Record
   - Select type (Patent, Trademark, etc.)
   - Fill in details

3. **Upload Documents**
   - Open IP Record
   - Click Upload Document
   - Select files

4. **Manage Requests**
   - Download Requests menu
   - Review and approve/reject

### As Staff:

1. **Browse Records**
   - Browse Records menu
   - View IP records

2. **Search**
   - Use search box
   - Find documents by keyword

3. **Request Download**
   - View document details
   - Click Request Download
   - Wait for approval

4. **Download Files**
   - My Requests menu
   - Click Download link for approved requests

---

## Security Recommendations

1. ✅ Change default passwords immediately
2. ✅ Use strong passwords (8+ chars, mixed case, numbers, symbols)
3. ✅ Regular database backups
4. ✅ Keep PHP and MySQL updated
5. ✅ Monitor activity logs
6. ✅ Use HTTPS in production
7. ✅ Disable error display in production
8. ✅ Restrict file upload types and sizes

---

## Support

If you encounter issues:

1. Check this installation guide
2. Review troubleshooting section
3. Check Apache error logs
4. Verify database connection
5. Ensure all prerequisites are met

---

## Next Steps

- ✅ Configure email notifications (future enhancement)
- ✅ Set up automated backups
- ✅ Configure SSL certificate for HTTPS
- ✅ Customize branding and colors
- ✅ Set up monitoring and analytics

---

**Installation Complete!** 🎉

Your Intellectual Property Repository Management System is now ready to use.

---

**Last Updated:** January 2026  
**Version:** 1.0.0
