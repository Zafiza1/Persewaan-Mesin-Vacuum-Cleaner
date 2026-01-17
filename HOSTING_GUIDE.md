# Akasa Teknik - Website Hosting Guide (Windows)

## Database Configuration ✅
Database sudah dikonfigurasi dengan:
- **Host:** 127.0.0.1
- **Port:** 3306
- **Username:** root
- **Password:** (kosong)
- **Database:** akasa

## Environment Configuration ✅
Environment sudah diset ke **production** untuk live hosting.

## Langkah-langkah Hosting di Windows:

### 1. XAMPP Setup
1. **Install XAMPP** jika belum ada:
   - Download dari https://www.apachefriends.org/
   - Install di `C:\xampp`

2. **Start XAMPP Control Panel**:
   - Buka XAMPP Control Panel
   - Start Apache (port 80/443)
   - Start MySQL (port 3306)

3. **Database Setup**:
   - Buka http://localhost/phpmyadmin
   - Buat database baru dengan nama `akasa`
   - Import file `database_structure.sql`

### 2. File Location
Pastikan website berada di:
```
C:\xampp\htdocs\akasa_teknik\
```

### 3. Windows File Permissions
- Right-click folder `akasa_teknik`
- Properties → Security → Edit
- Pastikan `IIS_IUSRS` atau `Everyone` memiliki akses:
  - Read & Execute
  - List folder contents
  - Read

### 4. Test Local Development
Buka browser dan akses:
- **Website:** http://localhost/akasa_teknik/
- **Admin Panel:** http://localhost/akasa_teknik/admin/
- **Form Sewa:** http://localhost/akasa_teknik/form_sewa_simple.php

### 5. Production Deployment (Windows Server)

#### Option A: XAMPP di Windows Server
1. Install XAMPP di production server
2. Copy folder `akasa_teknik` ke `C:\xampp\htdocs\`
3. Configure virtual host di Apache

#### Option B: IIS (Internet Information Services)
1. Install IIS via Windows Features
2. Install PHP Manager for IIS
3. Setup PHP dan MySQL extension
4. Create website di IIS Manager

### 6. XAMPP Configuration Files

#### Apache Config (`C:\xampp\apache\conf\httpd.conf`):
```apache
# Enable modules
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule deflate_module modules/mod_deflate.so
LoadModule expires_module modules/mod_expires.so
LoadModule headers_module modules/mod_headers.so
```

#### Virtual Host (`C:\xampp\apache\conf\extra\httpd-vhosts.conf`):
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/akasa_teknik"
    ServerName localhost
    
    <Directory "C:/xampp/htdocs/akasa_teknik">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 7. Windows Firewall
Pastikan port berikut di-allow:
- **Port 80** (HTTP)
- **Port 443** (HTTPS) 
- **Port 3306** (MySQL - jika remote access)

### 8. Port Configuration (jika conflict)
Jika port 80/3306 conflict:
```apache
# Change Apache port di httpd.conf
Listen 8080
ServerName localhost:8080
```

```ini
# Change MySQL port di my.ini
port = 3307
```

## Troubleshooting Windows

### XAMPP tidak start:
1. **Port conflict**: Cek dengan `netstat -ano | findstr :80`
2. **Skype/TeamViewer**: Disable atau change port
3. **Run as Administrator**: Buka XAMPP Control Panel as Admin

### Database Error:
1. **MySQL tidak start**: Cek error log di `C:\xampp\mysql\data\mysql_error.log`
2. **Password reset**: Reset MySQL password via XAMPP Shell

### Permission Issues:
1. **Read-only attribute**: Uncheck folder properties
2. **Antivirus**: Add exception untuk XAMPP folder

### PHP Errors:
1. **PHP version**: Minimum 7.4 (cek di XAMPP Shell: `php -v`)
2. **Extensions**: Pastikan mysqli, gd, curl enabled

## Production Checklist Windows

### Security:
- ✅ Environment set ke production
- ✅ Change default XAMPP passwords
- ✅ Disable XAMPP directory listing
- ✅ Update XAMPP regularly

### Performance:
- ✅ Enable Apache caching
- ✅ Optimize MySQL config
- ✅ Use PHP OPcache

### Monitoring:
- ✅ Setup Windows Event Log monitoring
- ✅ Monitor disk space
- ✅ Check Apache/MySQL logs regularly

## Access dari Network/LAN

Untuk akses dari komputer lain:
1. **Windows Firewall**: Allow Apache
2. **Apache config**: Change `Listen 80` to `Listen 0.0.0.0:80`
3. **Access via**: `http://[IP_SERVER]/akasa_teknik`

## Support Windows
Jika ada masalah:
1. **XAMPP Error Log**: `C:\xampp\apache\logs\error.log`
2. **MySQL Error Log**: `C:\xampp\mysql\data\mysql_error.log`
3. **Windows Event Log**: Windows Logs → Application
4. **Test connection**: Buat file test.php dengan `phpinfo()`

Website siap dihosting di Windows! 🚀
