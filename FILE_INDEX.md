# 📂 FILE INDEX
## Intellectual Property Repository Management System

Complete listing of all files in the project with descriptions.

---

## 📁 Root Directory Files

| File | Description | Status |
|------|-------------|--------|
| README.md | Complete system documentation | ✅ |
| INSTALLATION.md | Detailed installation guide | ✅ |
| PROJECT_SUMMARY.md | Project overview and summary | ✅ |
| CHANGELOG.md | Version history and changes | ✅ |
| LICENSE.txt | MIT license information | ✅ |
| QUICKSTART.html | Visual quick start guide | ✅ |
| check-system.php | System verification tool | ✅ |
| FILE_INDEX.md | This file | ✅ |

---

## 📁 /app Directory

### /app/controllers (5 files)

| File | Purpose | Functions |
|------|---------|-----------|
| AuthController.php | Authentication | login, logout, session management |
| AdminController.php | Admin operations | dashboard, user management, requests |
| StaffController.php | Staff operations | browse, search, request downloads |
| DocumentController.php | Document handling | upload, version, delete, download |
| IPRecordController.php | IP record CRUD | create, update, archive, delete |

### /app/models (5 files)

| File | Purpose | Database Table |
|------|---------|----------------|
| User.php | User management | users |
| IPRecord.php | IP record operations | ip_records, ip_types |
| Document.php | Document management | ip_documents, document_versions |
| DownloadRequest.php | Download permissions | download_requests, download_logs |
| ActivityLog.php | Audit trail | activity_logs |

### /app/views/layouts (1 file)

| File | Purpose | Features |
|------|---------|----------|
| main.php | Main layout template | Sidebar, header, footer, scripts |

### /app/views/auth (1 file)

| File | Purpose | Features |
|------|---------|----------|
| login.php | Login page | Form, validation, demo credentials |

### /app/views/admin (6+ files)

| File | Purpose | Features |
|------|---------|----------|
| dashboard.php | Admin dashboard | Statistics, activity, pending requests |
| users.php | User management | List, create, edit, activate/deactivate |
| ip-records.php | IP records list | Browse, filter, search |
| view-ip-record.php | Single record view | Details, documents, requests |
| download-requests.php | Request management | Approve, reject, review |
| trash.php | Trash bin | Soft-deleted items, restore |
| activity-logs.php | Activity logs | Filter, search, export |

### /app/views/staff (4+ files)

| File | Purpose | Features |
|------|---------|----------|
| dashboard.php | Staff dashboard | Stats, recent requests, search |
| ip-records.php | Browse records | View, filter, search |
| view-ip-record.php | Record details | Documents, request download |
| search.php | Search interface | Results, filters |
| my-requests.php | Request tracking | Status, download links |

### /app/middleware (Ready for expansion)

| Status | Purpose |
|--------|---------|
| 📁 Empty | For future middleware implementations |

---

## 📁 /config Directory (1 file)

| File | Purpose | Contains |
|------|---------|----------|
| config.php | System configuration | DB credentials, paths, limits, settings |

---

## 📁 /core Directory (3 files)

| File | Purpose | Type |
|------|---------|------|
| Database.php | Database connection | Singleton PDO class |
| Controller.php | Base controller | Parent class for all controllers |
| Router.php | URL routing | Request handling and dispatching |

---

## 📁 /database Directory (2 files)

| File | Purpose | Contains |
|------|---------|----------|
| schema.sql | Database schema | All tables, views, indexes, sample data |
| queries.sql | Helper queries | Useful SQL commands and examples |

---

## 📁 /public Directory

| File | Purpose | Type |
|------|---------|------|
| index.php | Application entry point | Router initialization |
| .htaccess | Apache configuration | Rewrite rules, security headers |

### /public/css (1 file)

| File | Purpose | Contains |
|------|---------|----------|
| style.css | Custom styles | Additional CSS for UI enhancements |

### /public/js (1 file)

| File | Purpose | Contains |
|------|---------|----------|
| common.js | JavaScript utilities | Helper functions, AJAX, validation |

---

## 📁 /uploads Directory

| Directory | Purpose | Protection |
|-----------|---------|------------|
| /documents | Active uploaded files | .htaccess deny all |
| /trash | Soft-deleted files | .htaccess deny all |
| .htaccess | Directory protection | Prevents direct access |

---

## 📊 File Statistics

### By Type

| Type | Count | Purpose |
|------|-------|---------|
| PHP Files | 15+ | Controllers, models, views |
| SQL Files | 2 | Database schema and queries |
| JavaScript | 1 | Client-side functionality |
| CSS | 1 | Custom styling |
| HTML | 1 | Quick start guide |
| Markdown | 5 | Documentation |
| Config | 3 | .htaccess and config files |
| **Total** | **28+** | Complete system |

### By Category

| Category | Files | Lines of Code (approx) |
|----------|-------|------------------------|
| Documentation | 5 | 2,000+ |
| Controllers | 5 | 1,500+ |
| Models | 5 | 1,200+ |
| Views | 10+ | 2,000+ |
| Core | 3 | 600+ |
| Config | 2 | 200+ |
| Database | 2 | 500+ |
| Assets | 2 | 800+ |
| **Total** | **34+** | **8,800+** |

---

## 🔍 File Purpose Quick Reference

### Essential for Operation

✅ **Must Have (Critical)**
- config/config.php
- database/schema.sql
- core/Database.php
- core/Controller.php
- core/Router.php
- public/index.php
- public/.htaccess
- uploads/.htaccess

### Controllers (Application Logic)

✅ **Controllers**
- AuthController.php - Login/logout
- AdminController.php - Admin features
- StaffController.php - Staff features
- DocumentController.php - File operations
- IPRecordController.php - Record management

### Models (Data Layer)

✅ **Models**
- User.php - User operations
- IPRecord.php - Record operations
- Document.php - File operations
- DownloadRequest.php - Permission handling
- ActivityLog.php - Audit logging

### Views (Presentation)

✅ **Layouts**
- layouts/main.php - Main template

✅ **Authentication**
- auth/login.php - Login page

✅ **Admin Views**
- admin/dashboard.php
- admin/users.php (implementation may vary)
- admin/ip-records.php
- admin/download-requests.php
- admin/trash.php
- admin/activity-logs.php

✅ **Staff Views**
- staff/dashboard.php
- staff/ip-records.php
- staff/search.php
- staff/my-requests.php

### Documentation

📚 **User Guides**
- README.md - Main documentation
- INSTALLATION.md - Setup instructions
- QUICKSTART.html - Quick reference

📚 **Developer Docs**
- PROJECT_SUMMARY.md - Overview
- CHANGELOG.md - Version history
- FILE_INDEX.md - This file

### Utilities

🛠️ **Tools**
- check-system.php - System checker
- database/queries.sql - SQL helpers

🛠️ **Assets**
- public/css/style.css - Styling
- public/js/common.js - JavaScript

---

## 📝 File Modification Guide

### When to Edit

| File | Edit When | Example |
|------|-----------|---------|
| config.php | Change settings | Database credentials, file limits |
| schema.sql | Database changes | New tables, modify structure |
| Controllers | Add features | New endpoints, business logic |
| Models | Data operations | New queries, data methods |
| Views | UI changes | Layout, design, content |
| style.css | Custom styling | Colors, spacing, effects |
| common.js | Client logic | Validation, AJAX, utilities |

### When NOT to Edit

⚠️ **Core Files (unless necessary)**
- Database.php
- Controller.php
- Router.php
- .htaccess files

---

## 🔐 Security-Critical Files

| File | Why Critical | Protection |
|------|--------------|------------|
| config.php | Contains DB credentials | Never commit to public repos |
| uploads/.htaccess | Prevents direct file access | Keep deny all rule |
| public/.htaccess | Security headers | Maintain rewrite rules |
| AuthController.php | Authentication logic | Audit changes carefully |
| User.php | Password handling | Never log passwords |

---

## 🚀 Deployment Checklist

### Files to Review Before Production

- [ ] config.php - Update credentials
- [ ] config.php - Disable error display
- [ ] config.php - Set BASE_URL
- [ ] schema.sql - Change default passwords
- [ ] public/.htaccess - Review security headers
- [ ] uploads/ - Verify permissions (755)

### Files to Never Deploy

- ❌ check-system.php (remove or restrict access)
- ❌ database/queries.sql (optional, for reference only)
- ❌ Any backup files (.bak, .old, etc.)
- ❌ Development logs

---

## 📦 Backup Recommendations

### Critical Files to Backup

**Priority 1 (Daily)**
- Database (mysqldump)
- uploads/documents/ folder
- config/config.php

**Priority 2 (Weekly)**
- All app/ directory
- All core/ directory
- public/ directory

**Priority 3 (Monthly)**
- Complete project backup
- Documentation updates

---

## 🔄 Version Control

### Files to Include in Git

✅ **Include**
- All .php files
- All .sql files
- Documentation (.md)
- .htaccess files
- CSS and JavaScript
- README files

❌ **Exclude (.gitignore)**
- config.php (use config.sample.php)
- uploads/ folder contents
- Vendor folders
- IDE-specific files
- Log files
- Backup files

---

## 📋 File Dependencies

### Dependency Map

```
index.php
├── config.php
├── core/Database.php
├── core/Controller.php
├── core/Router.php
└── controllers/
    ├── AuthController.php
    │   ├── models/User.php
    │   └── models/ActivityLog.php
    ├── AdminController.php
    │   ├── models/User.php
    │   ├── models/IPRecord.php
    │   ├── models/Document.php
    │   ├── models/DownloadRequest.php
    │   └── models/ActivityLog.php
    └── [other controllers...]
```

---

## 📈 Growth Projections

### Expected File Growth

| Category | Current | v1.1 | v2.0 |
|----------|---------|------|------|
| Controllers | 5 | 8 | 12 |
| Models | 5 | 7 | 10 |
| Views | 10+ | 20+ | 30+ |
| **Total Files** | 34+ | 50+ | 70+ |

---

**Last Updated:** January 10, 2026  
**Version:** 1.0.0  
**Status:** Complete ✅
