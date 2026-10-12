# 🔐 Secure File Storage System with Cryptography

[![PHP Version](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Encryption](https://img.shields.io/badge/Encryption-AES--256--CBC-orange?style=for-the-badge&logo=security&logoColor=white)](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
[![Status](https://img.shields.io/badge/Status-Completed-success?style=for-the-badge)]()

A full-stack, enterprise-grade secure file storage and sharing web application designed for end-to-end data confidentiality, integrity, and access control. Built with PHP, MySQL, and OpenSSL cryptography as a Capstone project.

---

## 📌 Project Overview

In traditional cloud storage solutions, unencrypted files stored on servers remain vulnerable to unauthorized inspection, data breaches, and insider threats. 

This **Secure File Storage System** addresses these vulnerabilities by ensuring that **all uploaded files are encrypted prior to being written to disk using military-grade AES-256-CBC encryption**. Even in the event of an unauthorized server compromise or physical disk inspection, the files cannot be read without cryptographic keys.

Decryption occurs on-the-fly in-memory only when an authorized user with explicit permissions requests a download.

---

## 🌟 Key Features

### 🛡️ 1. Cryptographic Security & Privacy
- **AES-256-CBC Symmetric Encryption**: Industry-standard symmetric cipher protecting file contents.
- **Cryptographically Secure Pseudo-Random IVs**: Generated dynamically using `openssl_random_pseudo_bytes()` for every upload, thwarting pattern analysis and dictionary attacks.
- **SHA-256 Key Derivation**: Master keys hashed to produce strong 256-bit keys.
- **Zero Raw Data at Rest**: Files are renamed and stored as encrypted `.enc` binary blobs in the storage layer.

### 👥 2. Role-Based Access Control (RBAC) & Authentication
- **Multi-Role Architecture**: Dedicated workflows and dashboards for regular **Users** and **Administrators**.
- **Secure Password Hashing**: Utilizes PHP's native `password_hash()` (BCrypt) for credential protection.
- **Session Security**: Session timeouts, HTTP-Only cookies, and strict session validation preventing session hijacking.

### 📁 3. Advanced File Management
- **Modern Drag & Drop Interface**: Visual upload box with instant file preview and size validation.
- **File Type & Size Restrictions**: Configurable limits (default 50MB) and extension whitelisting (PDF, DOCX, ZIP, JPG, PNG, etc.).
- **Download-on-Demand Decryption**: Instant decryption and stream response without saving decrypted files to disk.

### 🤝 4. Granular File Sharing & Permissions
- **Multi-Level Permissions**:
  - 👁️ **View Only**
  - 📥 **Download Access**
  - 🔐 **Full Access**
- **User Discovery**: Search registered users by username or email for quick sharing.
- **Revocation**: File owners can view active shares and revoke permissions anytime.

### 📊 5. Audit Logging & System Monitoring
- **Complete Audit Trail**: Logs timestamp, user ID, IP address, action performed (upload, download, share, revoke, login), and status.
- **Admin Analytics**: Interactive stat cards detailing total users, files stored, disk usage, and daily activity.

### 🎨 6. Modern Responsive UI/UX
- **Interactive Design**: Glassmorphism and modern gradient themes.
- **Responsive Layout**: Compatible with desktop, tablet, and mobile screens.
- **Feedback & Micro-interactions**: Smooth transitions, animated stats counters, and contextual notifications.

---

## 🏗️ System Architecture & Workflow

```text
+-------------+         1. Upload Raw File          +---------------------+
|    User     | ----------------------------------> |     Application     |
|   Client    |                                     |    (PHP Engine)     |
+-------------+                                     +---------------------+
       ^                                                       |
       |                                      2. Generate Random IV (16 bytes)
       |                                      3. Encrypt via AES-256-CBC
       |                                                       v
       |                                            +---------------------+
       |                                            |    Server Disk      |
       |                                            |    (uploads/*.enc)  |
       |                                            +---------------------+
       |
       |                4. Authorized Download Request
       +-------------------------------------------------------+
                                                               |
                                            5. Read .enc & Extract IV
                                            6. Decrypt in memory
                                            7. Stream original file
```

---

## 📂 Project Directory Structure

```plaintext
secure_file_storage/
├── css/
│   └── styles.css              # Custom styling, animations, and responsive design
├── js/
│   └── main.js                 # Drag & drop upload, dynamic UI handling
├── includes/
│   ├── config.php              # Application & database configurations
│   ├── db.php                  # PDO database connection handler
│   ├── encryption.php          # Core AES-256-CBC encryption/decryption routines
│   ├── auth.php                # Authentication & session verification
│   ├── audit.php               # Security audit logging functions
│   ├── header.php              # Shared navigation & layout header
│   └── footer.php              # Layout footer
├── uploads/                    # Encrypted file storage directory (.enc files)
├── logs/                       # System & error logs
├── vendor/                     # PHPMailer & Composer dependencies
├── admin_dashboard.php         # Administrator control panel
├── admin_login.php             # Admin authentication endpoint
├── audit_logs.php              # System security log viewer
├── crypto.sql                  # MySQL database schema & initial seed data
├── download.php                # Decrypt & download controller
├── index.php                   # Landing page
├── login.php                   # User login
├── logout.php                  # Session termination
├── manage_files.php            # File management interface
├── manage_users.php            # User management interface
├── my_files.php                # Personal files repository
├── register.php                # Account registration
├── share.php                   # File sharing modal/controller
├── shared_files.php            # Repository of files shared with current user
├── upload.php                  # File upload controller
├── user_dashboard.php          # User workspace & metrics
└── verify_database.php         # Database health & verification utility
```

---

## 🗄️ Database Schema

The database `crypto` comprises four core relational tables:

1. **`users`**: User credentials (`id`, `username`, `email`, `password_hash`, `role`, `created_at`).
2. **`files`**: Metadata of uploaded files (`id`, `user_id`, `original_name`, `stored_name`, `file_size`, `mime_type`, `created_at`).
3. **`file_shares`**: Sharing mappings (`id`, `file_id`, `shared_with_user_id`, `permission_level`, `created_at`).
4. **`audit_logs`**: System security logs (`id`, `user_id`, `action`, `details`, `ip_address`, `timestamp`).

---

## 🚀 Setup & Installation Guide

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any WAMP/LAMP stack with PHP 8.0+ and MySQL/MariaDB)
- Apache `mod_rewrite` enabled
- OpenSSL extension enabled in `php.ini` (`extension=openssl`)

### Step 1: Clone or Copy Repository
Clone or place this repository into your XAMPP web root directory:
```bash
cd c:/xampp/htdocs/
git clone https://github.com/karthik143526/Cryptography-Capstone.git secure_file_storage
```

### Step 2: Start Apache and MySQL
Open the **XAMPP Control Panel** and click **Start** next to:
- **Apache**
- **MySQL**

### Step 3: Import the Database
1. Open your browser and navigate to `http://localhost/phpmyadmin/`.
2. Create a new database named **`crypto`** with collation `utf8mb4_general_ci`.
3. Select the `crypto` database and click **Import**.
4. Choose the [`crypto.sql`](crypto.sql) file located in the project root and click **Import**.

### Step 4: Verify Database Connection
Verify settings in [`includes/config.php`](includes/config.php):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'crypto');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);
```
Optionally run the diagnostic tool in your browser:
```
http://localhost/secure_file_storage/verify_database.php
```

### Step 5: Launch the Application
Open your browser and navigate to:
```
http://localhost/secure_file_storage/
```

---

## 🔑 Default Credentials

| Role | Username / Email | Password |
| :--- | :--- | :--- |
| **User** | `karthikvenkat062@gmail.com` | *(Registered password)* |
| **Admin** | Access via `admin_login.php` | *(Configured admin credentials)* |

---

## 🔒 Security Best Practices Implemented

- [x] **Separation of Encrypted Data**: Original file names and metadata are stored in SQL, while content blobs reside encrypted on disk with nonces/IVs.
- [x] **SQL Injection Prevention**: 100% prepared PDO statements with bound parameters.
- [x] **XSS Mitigation**: Contextual sanitization with `htmlspecialchars()` across all input display points.
- [x] **CSRF & Session Security**: Secure session tokens, strict timeouts, and HTTP-only cookie flags.
- [x] **Direct Access Protection**: Uploaded files cannot be executed or read directly as scripts (stored as non-executable `.enc`).

---

## 📜 License
This project is developed as an academic Capstone project in Cryptography and Information Security. Distributed for academic and educational evaluation.
