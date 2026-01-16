# Dynamic Folder Repository System

A complete file management system with dynamic folder navigation, file upload, and full CRUD operations built with PHP, MySQL, Tailwind CSS, and JavaScript.

## Features

✨ **Core Features:**
- Dynamic folder-by-folder navigation
- File upload with support for multiple file types
- Full CRUD operations for files and folders
- Breadcrumb navigation
- Responsive design with Tailwind CSS
- Real-time updates
- File download functionality
- Beautiful UI with Font Awesome icons

## Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, Tailwind CSS, JavaScript (ES6+)
- **Icons:** Font Awesome 6.4
- **Server:** Apache (XAMPP)

## Installation

### 1. Prerequisites
- XAMPP (or any Apache + MySQL + PHP stack)
- Web browser (Chrome, Firefox, Edge, Safari)

### 2. Setup Database

1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the database schema:
   - Click "New" to create a new database or use the SQL tab
   - Open `database.sql` file and execute it
   - This will create the `folder_repository` database with necessary tables

Alternatively, run from command line:
```bash
mysql -u root -p < database.sql
```

### 3. Configure Database Connection

Edit `config.php` if needed to match your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'folder_repository');
```

### 4. Set Permissions

Ensure the `uploads` directory has write permissions:
```bash
chmod 777 uploads/
```

The directory will be created automatically on first upload if it doesn't exist.

### 5. Access the Application

Open your browser and navigate to:
```
http://localhost/new_IPRM_project/
```

## Project Structure

```
new_IPRM_project/
├── index.html          # Main interface
├── app.js             # JavaScript functionality
├── config.php         # Configuration settings
├── db.php             # Database connection class
├── api_folders.php    # Folder CRUD API endpoints
├── api_files.php      # File CRUD API endpoints
├── upload.php         # File upload handler
├── database.sql       # Database schema
├── uploads/           # Uploaded files directory (auto-created)
└── README.md          # This file
```

## Usage Guide

### Creating Folders
1. Click the "New Folder" button
2. Enter folder name
3. Click "Create"
4. The folder will appear in the Folders panel

### Uploading Files
1. Click the "Upload File" button
2. Select a file (max 50MB)
3. Optionally add a description
4. Click "Upload"
5. The file will appear in the Files panel

### Navigating Folders
- Click on any folder in the Folders panel to open it
- Use the breadcrumb navigation at the top to go back
- Click on folder names in breadcrumb to jump to that level

### Managing Files
- **Download:** Click the download icon next to any file
- **Edit:** Click the edit icon to change file name or description
- **Delete:** Click the delete icon to remove the file

### Managing Folders
- **Edit:** Hover over a folder and click the edit icon
- **Delete:** Hover over a folder and click the delete icon
  - Note: Cannot delete folders with files or subfolders

## Supported File Types

- **Images:** jpg, jpeg, png, gif
- **Documents:** pdf, doc, docx, txt
- **Spreadsheets:** xls, xlsx
- **Archives:** zip, rar

You can modify allowed extensions in `config.php`:
```php
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar']);
```

## Configuration Options

### Maximum File Size
Edit in `config.php`:
```php
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
```

### Upload Directory
Edit in `config.php`:
```php
define('UPLOAD_DIR', __DIR__ . '/uploads/');
```

## API Endpoints

### Folder Endpoints
- `GET api_folders.php?action=get_folders&parent_id={id}` - Get folders
- `GET api_folders.php?action=get_folder&id={id}` - Get single folder
- `GET api_folders.php?action=get_breadcrumb&folder_id={id}` - Get breadcrumb
- `POST api_folders.php?action=create_folder` - Create folder
- `POST api_folders.php?action=update_folder` - Update folder
- `GET api_folders.php?action=delete_folder&id={id}` - Delete folder
- `GET api_folders.php?action=get_files&folder_id={id}` - Get files in folder

### File Endpoints
- `GET api_files.php?action=get_file&id={id}` - Get file info
- `POST api_files.php?action=update_file` - Update file
- `GET api_files.php?action=delete_file&id={id}` - Delete file
- `GET api_files.php?action=download&id={id}` - Download file
- `POST upload.php` - Upload file

## Security Features

- SQL injection prevention using PDO prepared statements
- File type validation
- File size limits
- XSS prevention with HTML escaping
- CSRF protection ready (can be enhanced)

## Troubleshooting

### Cannot upload files
- Check Apache PHP upload settings in `php.ini`:
  ```
  upload_max_filesize = 50M
  post_max_size = 50M
  ```
- Ensure `uploads/` directory has write permissions

### Database connection error
- Verify MySQL is running in XAMPP
- Check database credentials in `config.php`
- Ensure database is created using `database.sql`

### Files not displaying
- Check browser console for JavaScript errors
- Verify API endpoints are accessible
- Check file permissions

## Future Enhancements

- User authentication and authorization
- File sharing and permissions
- File preview functionality
- Drag-and-drop file upload
- Bulk operations
- Search functionality
- File versioning
- Recycle bin

## License

This project is open source and available under the MIT License.

## Support

For issues and questions, please check the troubleshooting section or review the code comments.
