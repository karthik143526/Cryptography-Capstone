# Logs Directory

This directory stores application and database error logs.

## Log Files Generated:
- `database_errors.log` - Database connection and query errors
- `audit.log` - User activity logs (if configured)
- `system.log` - System-level errors

## Permissions:
Ensure this directory is writable by the web server:
```bash
chmod 755 logs/
chmod 644 logs/*.log
```

## For Production:
- Rotate logs regularly to save disk space
- Monitor log files for errors and security issues
- Keep logs outside the web root if possible
- Set up automated log cleanup
