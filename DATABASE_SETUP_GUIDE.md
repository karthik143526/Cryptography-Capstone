# 🔧 Database Fix & Setup Guide

## Overview
This document provides step-by-step instructions to fix the "same database is being used again" error and properly set up your Secure File Storage project.

---

## 📋 Problem Summary

The error "same database is being used again" typically occurs when:
1. ✗ MySQL service is not running
2. ✗ Database `secure_file_storage` doesn't exist
3. ✗ Database credentials in `includes/db.php` are incorrect
4. ✗ SQL script was imported multiple times causing duplicate key conflicts
5. ✗ PHP can't connect to the MySQL server

---

## ✅ Quick Start - Complete Setup (10 minutes)

### Step 1: Start MySQL
1. Open **XAMPP Control Panel**
2. Look for "MySQL" module
3. Click **Start** button
4. Wait for status to show "Running" (should be green)

### Step 2: Verify Database Configuration
1. Open your browser
2. Navigate to: `http://localhost/secure_file_storage/verify_database.php`
3. This will check all your database settings
4. Take note of any errors shown

### Step 3: Create/Reset Database (if needed)
1. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Click on **Databases** tab
3. Look for `secure_file_storage` database
   - **If it exists**: Continue to Step 4
   - **If it DOESN'T exist**: Create it
     - Type `secure_file_storage` in the text field
     - Click **Create**

### Step 4: Import Database Schema
1. In phpMyAdmin, select the `secure_file_storage` database
2. Click **Import** tab
3. Click **Choose File**
4. Select `secure_improved.sql` from your project folder
5. Click **Import**
6. You should see: "Import has been executed successfully"

### Step 5: Verify Success
1. Go back to: `http://localhost/secure_file_storage/verify_database.php`
2. All checks should show **✓ OK**
3. You should see admin user: `karthikvenkat062@gmail.com`

### Step 6: Test the Application
1. Open: `http://localhost/secure_file_storage/index.php`
2. Try to login with:
   - **Email/Username**: `karthikvenkat062@gmail.com`
   - **Password**: `192372092`
3. If successful, all database issues are fixed! ✓

---

## 🆕 New/Updated Files Explanation

### 1. `includes/config.php` ⭐ NEW
**Purpose**: Centralized configuration file for database and application settings

**What it contains**:
- Database connection details
- Security settings
- File upload limits
- Error handling configuration
- Session timeouts

**Benefits**:
- ✓ Single place to change credentials
- ✓ Better organization
- ✓ Easier to maintain in production
- ✓ Security constants defined

**How to use**:
```php
require_once 'includes/config.php'; // Already required by db.php
echo DB_HOST;  // Access database constants
echo UPLOAD_MAX_SIZE; // Access app constants
```

### 2. `includes/db.php` 🔄 UPDATED
**Purpose**: Database connection handler with improved error handling

**What changed**:
- Now uses `config.php` for credentials
- Better error messages for debugging
- Automatic connection pooling per request
- Helper functions added: `isDatabaseConnected()`, `executeQuery()`
- Logs database errors to file
- Production/development mode support

**Benefits**:
- ✓ More reliable connection handling
- ✓ Better error diagnostics
- ✓ Easier debugging
- ✓ Secure error messages in production

### 3. `secure_improved.sql` ⭐ NEW
**Purpose**: Improved database schema with fix for duplicate key issues

**What changed**:
- Uses `INSERT IGNORE` instead of `ON DUPLICATE KEY UPDATE`
- Added table indexes for better performance
- Added timestamps for tracking
- Added collation for UTF-8 support
- Better documentation
- Default permissions included

**Benefits**:
- ✓ Can be run multiple times safely
- ✓ Better performance on queries
- ✓ Proper character encoding
- ✓ Easier to understand schema

### 4. `verify_database.php` ⭐ NEW
**Purpose**: Automated database verification and troubleshooting tool

**What it checks**:
- ✓ PHP extensions installed
- ✓ Configuration file loaded
- ✓ MySQL server connection
- ✓ Database exists
- ✓ All required tables exist
- ✓ Admin user created
- ✓ File permissions correct

**How to use**:
1. Access: `http://localhost/secure_file_storage/verify_database.php`
2. Review all checks
3. Follow remediation steps if any fail
4. Keep this file for debugging later

---

## 🐛 Troubleshooting

### Error: "Database Connection Failed"

**Cause**: MySQL is not running

**Solution**:
```
1. Open XAMPP Control Panel
2. Click "Start" for MySQL
3. Wait 5 seconds
4. Refresh your browser
```

---

### Error: "Database 'secure_file_storage' not found"

**Cause**: Database doesn't exist or wasn't created properly

**Solution**:
```
1. Go to phpMyAdmin: http://localhost/phpmyadmin
2. Click "Databases" tab
3. Create new database named: secure_file_storage
4. Import secure_improved.sql to that database
5. Refresh browser
```

---

### Error: "Unknown column in file list"

**Cause**: Schema mismatch or old database structure

**Solution**:
```
1. Go to phpMyAdmin
2. Select secure_file_storage database
3. Delete all tables (click checkbox "Empty" if present)
4. Delete the database completely
5. Create new database secure_file_storage
6. Import secure_improved.sql
```

---

### Error: "Duplicate entry ... for key 'email'"

**Cause**: Admin user inserted twice

**Solution**:
```
1. In phpMyAdmin, select secure_file_storage
2. Click SQL tab
3. Run: DELETE FROM users WHERE id = 1 OR id = 2;
4. Run the same DELETE query again to be sure
5. Go to Import and re-import secure_improved.sql
```

---

### Application works but shows database errors in logs

**Cause**: Connection issues or permission problems

**Solution**:
1. Check `logs/database_errors.log` for details
2. Ensure MySQL user `root` has all permissions
3. Run verify_database.php to diagnose

---

## 🔐 Security Recommendations

### Before Going to Production:

1. **Change Default Credentials**
   - Edit `includes/config.php`
   - Change MySQL password
   - Generate new admin password hash

2. **Move Config File Outside Web Root**
   ```
   Move: includes/config.php
   To: /path/outside/web/config.php
   ```

3. **Remove Verification Script**
   - Delete `verify_database.php` from server

4. **Enable HTTPS**
   - Update `includes/config.php`
   - Set `SESSION_SECURE` to `true`

5. **Set Environment to Production**
   - In `includes/config.php`
   - Change `APP_ENV` from 'development' to 'production'

---

## 📊 Database Schema Overview

### Tables Created:

1. **users** - Stores user accounts
   - Columns: id, name, email, username, password, role, status, created_at
   - Default: Admin user + Demo user

2. **files** - Stores uploaded files metadata
   - Columns: id, user_id, file_name, file_path, file_size, uploaded_at
   - References: users.id

3. **file_shares** - Tracks file sharing
   - Columns: id, file_id, shared_with, permission, shared_at
   - References: files.id, users.id

4. **permissions** - Defines role-based permissions
   - Columns: id, role, module, permission, access_level
   - Default: Admin and User permissions configured

5. **audit_logs** - Tracks all user actions
   - Columns: id, user_id, action, module, details, ip_address, created_at
   - References: users.id

---

## 🧪 Testing Checklist

After setup, verify everything works:

- [ ] Verify database page shows all ✓ checks
- [ ] Can access login page
- [ ] Can login with admin account
- [ ] Can upload a file
- [ ] Can download uploaded file
- [ ] Can share file with demo user
- [ ] Can view admin dashboard
- [ ] Audit logs show your activities
- [ ] Can logout successfully

---

## 📞 Getting Help

If issues persist:

1. **Check verify_database.php** - Run this first
2. **Review logs folder** - Check database_errors.log
3. **Check MySQL status** - Is it running?
4. **Check credentials** - Match config.php with phpMyAdmin
5. **Clear browser cache** - Sometimes helps
6. **Restart XAMPP** - Full restart of services

---

## 🚀 Next Steps

After successful setup:

1. Review [PROFESSIONAL_ENHANCEMENTS.md](PROFESSIONAL_ENHANCEMENTS.md)
2. Change default passwords
3. Configure email notifications
4. Set up regular backups
5. Review security settings

---

**Version**: 1.0  
**Last Updated**: 2024  
**For**: Secure File Storage Project

