# Intellectual Property Repository Management System

A secure, full-featured web application for managing intellectual property documents with role-based access control and download permission workflow.

## 🎯 Features

### Core Functionality
- **Secure Authentication** - Session-based with password hashing
- **Role-Based Access Control** - Admin and Staff/Viewer roles
- **IP Records Management** - Create, update, archive IP records (Patents, Trademarks, Copyrights, Industrial Designs)
- **Document Management** - Upload, version control, soft delete with trash bin
- **Download Permission Workflow** - Request-based system with approval/rejection
- **Secure File Downloads** - Token-based with expiry and download limits
- **Search Functionality** - Full-text search across files, titles, and tags
- **Activity Logging** - Comprehensive audit trail for all actions
- **Trash Bin** - Two-step deletion with restore capability

### Security Features
- Password hashing with bcrypt
- CSRF protection
- Session security
- File upload validation
- Secure file storage outside public directory
- Token-based secure downloads
- SQL injection prevention with PDO prepared statements
- XSS protection with input sanitization

## 🛠️ Tech Stack

### Backend
- PHP 8+
- MySQL Database
- PDO for database access
- MVC Architecture
- Custom routing system

### Frontend
- HTML5
- Tailwind CSS (CDN)
- Vanilla JavaScript
- SweetAlert2 for alerts
- Font Awesome icons

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache Web Server with mod_rewrite enabled
- XAMPP/WAMP/LAMP recommended

## 🚀 Installation

### Step 1: Database Setup

1. Open phpMyAdmin or MySQL command line
2. Create a new database:
   ```sql
   CREATE DATABASE ip_repository_db;
   ```
3. Import the database schema:
   - Navigate to `/database/schema.sql`
   - Execute the SQL file in your database

### Step 2: Configuration

1. Open `/config/config.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ip_repository_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
3. Update BASE_URL if your folder name is different:
   ```php
   define('BASE_URL', 'http://localhost/Intellectual%20Property%20Repository%20Management%20System/public');
   ```

### Step 3: Permissions

Ensure the following directories are writable:
```bash
chmod 755 uploads/
chmod 755 uploads/documents/
chmod 755 uploads/trash/
```

### Step 4: Apache Configuration

Ensure mod_rewrite is enabled in your Apache configuration:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

And allow .htaccess overrides in your virtual host or httpd.conf:
```apache
<Directory "C:/xampp/htdocs">
    AllowOverride All
</Directory>
```

### Step 5: Access the Application

Open your browser and navigate to:
```
http://localhost/Intellectual%20Property%20Repository%20Management%20System/public
```

## 👤 Default Accounts

### Admin Account
- **Username:** admin
- **Password:** Admin@123
- **Role:** Administrator

### Staff Account
- **Username:** staff
- **Password:** Staff@123
- **Role:** Staff/Viewer

**⚠️ IMPORTANT:** Change these passwords immediately after first login in a production environment!

## 📁 Project Structure

```
Intellectual Property Repository Management System/
│
├── app/
│   ├── controllers/          # Application controllers
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── DocumentController.php
│   │   ├── IPRecordController.php
│   │   └── StaffController.php
│   │
│   ├── models/              # Database models
│   │   ├── User.php
│   │   ├── IPRecord.php
│   │   ├── Document.php
│   │   ├── DownloadRequest.php
│   │   └── ActivityLog.php
│   │
│   ├── views/               # View templates
│   │   ├── layouts/         # Layout templates
│   │   ├── admin/           # Admin views
│   │   ├── staff/           # Staff views
│   │   └── auth/            # Authentication views
│   │
│   └── middleware/          # Middleware (future use)
│
├── config/
│   └── config.php           # Application configuration
│
├── core/
│   ├── Database.php         # Database connection class
│   ├── Controller.php       # Base controller
│   └── Router.php           # URL routing system
│
├── database/
│   └── schema.sql           # Database schema
│
├── public/
│   ├── index.php            # Application entry point
│   ├── .htaccess            # Apache rewrite rules
│   ├── css/                 # Custom CSS files
│   └── js/                  # Custom JavaScript files
│
├── uploads/
│   ├── documents/           # Uploaded documents (secured)
│   ├── trash/               # Soft-deleted documents
│   └── .htaccess            # Deny direct access
│
└── README.md                # This file
```

## 🔑 User Roles & Permissions

### Administrator
- Full system access
- Manage users (create, activate, deactivate)
- Manage IP records (create, update, archive, delete)
- Upload and manage documents
- Upload new document versions
- Review download requests (approve/reject)
- Access trash bin (restore/permanently delete)
- View activity logs
- Generate reports

### Staff/Viewer
- Read-only access to IP records
- Browse and search documents
- Request download permission
- View own request status
- Download approved documents (token-based)
- No upload or modification rights

## 📝 How to Use

### For Administrators

#### Managing Users
1. Navigate to **Users** from sidebar
2. Click **Add User** button
3. Fill in user details (username, email, password, role)
4. Submit to create user
5. Activate/deactivate users as needed

#### Managing IP Records
1. Navigate to **IP Records**
2. Click **Create New Record**
3. Select IP type (Patent, Trademark, Copyright, Industrial Design)
4. Fill in details (title, owner, filing date, status, tags)
5. Click **Save**

#### Uploading Documents
1. Open an IP record
2. Click **Upload Document**
3. Select file (PDF, images, or documents)
4. Upload completes - document is secured

#### Managing Download Requests
1. Navigate to **Download Requests**
2. Review pending requests
3. Click **Approve** or **Reject**
4. For approval: Set download limit and expiry time
5. User receives secure download link

### For Staff/Viewers

#### Browsing Records
1. Navigate to **Browse Records**
2. Use filters or search
3. Click on record to view details

#### Requesting Downloads
1. Find desired document
2. Click **Request Download**
3. Provide reason (optional)
4. Wait for admin approval

#### Downloading Files
1. Navigate to **My Requests**
2. Find approved requests
3. Click **Download** link
4. Download is logged and counted

## 🔐 Security Best Practices

1. **Change default passwords immediately**
2. **Use strong passwords** (minimum 8 characters with mixed case, numbers, symbols)
3. **Regularly backup database** and uploaded files
4. **Keep PHP and MySQL updated**
5. **Monitor activity logs** for suspicious behavior
6. **Set appropriate file permissions**
7. **Use HTTPS in production** (SSL/TLS certificate)
8. **Configure proper php.ini settings** for production

## 🐛 Troubleshooting

### "404 Not Found" errors
- Ensure mod_rewrite is enabled
- Check .htaccess files exist
- Verify BASE_URL in config.php

### "Database Connection Failed"
- Verify database credentials in config.php
- Ensure MySQL service is running
- Check if database exists

### File upload errors
- Check upload directory permissions
- Verify MAX_FILE_SIZE in config.php
- Check PHP upload_max_filesize setting

### Session issues
- Ensure session directory is writable
- Check session.save_path in php.ini
- Clear browser cookies

## 📊 Database Tables

- **users** - User accounts
- **ip_types** - IP categories (Patent, Trademark, etc.)
- **ip_records** - Intellectual property records
- **ip_documents** - Uploaded documents
- **document_versions** - Document version history
- **download_requests** - Download permission requests
- **download_logs** - Download audit trail
- **activity_logs** - System activity audit trail

## 🔄 Future Enhancements

- Email notifications for request approvals
- Document preview functionality
- Advanced reporting and analytics
- API endpoints for integrations
- Multi-language support
- Document encryption
- Two-factor authentication
- Bulk operations

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review activity logs for errors
3. Check Apache error logs
4. Verify database connection and structure

## 📄 License

This project is developed for educational and organizational use.

## 👨‍💻 Development

Built with ❤️ using PHP, MySQL, and Tailwind CSS

### Development Setup
1. Enable error reporting in config.php during development
2. Use browser developer tools for frontend debugging
3. Check Apache error logs for PHP errors
4. Monitor database queries using MySQL slow query log

---

**Version:** 1.0.0  
**Last Updated:** January 2026
