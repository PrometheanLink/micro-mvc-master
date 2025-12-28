# Nature Watch - SiteGround Deployment Guide

## Prerequisites
- SiteGround hosting account with cPanel access
- FTP client (FileZilla) or use cPanel File Manager
- Access to phpMyAdmin

---

## Step 1: Create MySQL Database (5 minutes)

1. Log into **SiteGround Site Tools** or **cPanel**
2. Go to **Site > MySQL** (or **Databases > MySQL Databases**)
3. Create a new database:
   - Database name: `naturewatch` (or similar)
   - Note the full name (usually prefixed like `username_naturewatch`)
4. Create a database user:
   - Username: `nw_user` (or similar)
   - Password: Generate a strong password - **SAVE THIS!**
5. Add user to database with **ALL PRIVILEGES**

---

## Step 2: Import Database Schema (2 minutes)

1. Go to **phpMyAdmin** (Site Tools > Site > MySQL > phpMyAdmin)
2. Select your new database from the left sidebar
3. Click the **Import** tab
4. Choose file: `deploy/siteground_schema.sql`
5. Click **Go** to import

---

## Step 3: Upload Files (10 minutes)

### Option A: Using File Manager (Easiest)
1. Go to **Site Tools > Site > File Manager**
2. Navigate to `public_html`
3. Create a new folder: `naturewatch` (or use a subdomain)
4. Upload all files from the project (except `deploy/` folder)

### Option B: Using FTP
1. Connect via FTP (credentials in Site Tools > FTP Accounts)
2. Navigate to `public_html/naturewatch/`
3. Upload all files

### Files to Upload:
```
/framework/       (entire folder)
/phoenix/         (entire folder)
/site/            (entire folder)
/index.php
/.htaccess        (use deploy/htaccess-https.txt - rename to .htaccess)
```

**DO NOT upload:**
- `/deploy/` folder
- `/docker-compose.yml`
- `/Dockerfile`
- `/.claude/` folder

---

## Step 4: Configure Database Connection (1 minute)

Edit `framework/config/db.cfg` with your SiteGround credentials:

```
YOUR_DB_USER:YOUR_DB_PASSWORD:localhost:YOUR_DB_NAME:3306
```

Example:
```
u123_nwuser:MySecurePass123!:localhost:u123_naturewatch:3306
```

---

## Step 5: Set Up HTTPS (2 minutes)

1. Replace `.htaccess` with the contents of `deploy/htaccess-https.txt`
2. SiteGround auto-provisions SSL, so HTTPS should work immediately

---

## Step 6: Create Admin Account (2 minutes)

1. Visit: `https://yourdomain.com/naturewatch/en/register/`
2. Register your admin account
3. In phpMyAdmin, run:
   ```sql
   UPDATE users
   SET email_verified = 1, status = 'active', role = 10
   WHERE email = 'your@email.com';
   ```

---

## Step 7: Test Everything

- [ ] Homepage loads: `/en/nature-watch/`
- [ ] Login works: `/en/login/`
- [ ] Admin panel loads: `/en/admin-nature-watch/`
- [ ] Map displays correctly
- [ ] Photo upload works
- [ ] Sighting submission works

---

## Subdomain Setup (Optional)

If you want `naturewatch.yourdomain.com` instead of `yourdomain.com/naturewatch`:

1. Go to **Site Tools > Domain > Subdomains**
2. Create subdomain: `naturewatch`
3. Set document root to: `public_html/naturewatch`
4. Upload files to that folder
5. Update `.htaccess` RewriteBase if needed

---

## Folder Permissions

If you get permission errors:
```
/framework/config/    → 755
/framework/config/db.cfg → 644
/site/uploads/        → 755 (create if doesn't exist)
```

---

## Troubleshooting

### "500 Internal Server Error"
- Check `.htaccess` syntax
- Verify PHP version is 8.0+ (Site Tools > Devs > PHP Manager)

### "Database connection failed"
- Verify `db.cfg` credentials
- Check database user has permissions

### Login not working
- Clear browser cache
- Check if cookies are being set (HTTPS required for secure cookies)

### Map not loading
- Check browser console for errors
- Leaflet CDN might be blocked - verify CSP in .htaccess

---

## Production Checklist

- [ ] Database credentials are correct
- [ ] `.htaccess` is HTTPS version
- [ ] Admin account created and verified
- [ ] Test all functionality
- [ ] Set up daily backups (Site Tools > Security > Backups)

---

## URLs After Deployment

| Page | URL |
|------|-----|
| Nature Watch | `/en/nature-watch/` |
| Login | `/en/login/` |
| Register | `/en/register/` |
| Admin Panel | `/en/admin-nature-watch/` |

---

**Total deployment time: ~20 minutes**
