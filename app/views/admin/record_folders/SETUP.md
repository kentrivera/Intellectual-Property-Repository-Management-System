# QUICK SETUP GUIDE

Follow these simple steps to get your Dynamic Folder Repository running:

## Step 1: Database Setup
1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Open phpMyAdmin: http://localhost/phpmyadmin
4. Click "SQL" tab
5. Copy and paste the entire content of `database.sql`
6. Click "Go" to execute

## Step 2: Verify Installation
1. Open your browser
2. Navigate to: http://localhost/new_IPRM_project/
3. You should see the Dynamic Folder Repository interface

## Step 3: Test the System
1. Click "New Folder" to create a folder
2. Click "Upload File" to upload a test file
3. Navigate through folders by clicking on them
4. Try editing and deleting items

## Common Issues

### "Connection failed" error
- Make sure MySQL is running in XAMPP
- Check if database was created successfully
- Verify credentials in config.php

### Cannot upload files
- Check if `uploads` folder exists
- Ensure folder has write permissions (777)
- Check PHP upload limits in XAMPP

### Page not loading
- Verify Apache is running in XAMPP
- Check if you're using the correct URL
- Clear browser cache

## Default Settings

- **Database Name:** folder_repository
- **Database User:** root
- **Database Password:** (empty)
- **Max File Size:** 50MB
- **Upload Directory:** uploads/

## Need Help?

Check the full README.md file for detailed documentation.
