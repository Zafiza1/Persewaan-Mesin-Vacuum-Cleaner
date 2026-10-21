# Persewaan Mesin Vacuum Cleaner - Akasa Teknik

Sistem manajemen persewaan mesin vacuum cleaner berbasis PHP dengan SQLite. Aplikasi ini memudahkan pengelolaan penyewaan mesin vacuum cleaner untuk kebutuhan rumah, kantor, hingga industri.

## Fitur Utama

- **Manajemen Produk**: Kelola stok dan harga sewa mesin vacuum cleaner
- **Sistem Sewa**: Proses pemesanan sewa dengan sistem status (pending, disewa, selesai, dibatalkan)
- **User Authentication**: Login/Register untuk penyewa
- **Admin Dashboard**: Panel admin untuk mengelola produk dan pesanan
- **Responsive Design**: Tampilan modern dan responsif dengan Bootstrap 5
- **Keamanan**: CSRF protection, password hashing, dan input sanitization
- **SQLite Database**: Database file-based, tidak perlu setup MySQL

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: SQLite3 (file-based, no setup required)
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5
- **Icons**: Font Awesome

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- Ekstensi PHP: sqlite3, mbstring
- Web server (Apache/Nginx) atau PHP built-in server

## Cara Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd Persewaan-Mesin-Vacuum-Cleaner
```

### 2. Setup Database

Jalankan script setup untuk membuat database dan tabel secara otomatis:

```bash
php setup.php
```

Script ini akan:
- Membuat database SQLite di folder `database/akasa.db`
- Membuat semua tabel yang diperlukan
- Membuat user admin default
- Menambahkan produk contoh

**Catatan**: Setelah setup selesai, hapus file `setup.php` untuk keamanan:

```bash
rm setup.php
```

### 3. Jalankan Aplikasi

#### Opsi A: Menggunakan PHP Built-in Server (Paling Mudah)

```bash
php -S localhost:8000
```

Lalu buka browser dan akses: `http://localhost:8000`

#### Opsi B: Menggunakan XAMPP/WAMP (Windows)

1. Copy folder project ke `htdocs` (XAMPP) atau `www` (WAMP)
2. Buka browser dan akses: `http://localhost/Persewaan-Mesin-Vacuum-Cleaner/`

#### Opsi C: Menggunakan Apache/Nginx

Konfigurasi virtual host dan arahkan ke folder project ini.

## Cara Login

### Login sebagai Admin

Setelah menjalankan setup, Anda dapat login sebagai admin dengan kredensial default:

- **URL**: `{BASE_URL}admin/login.php`
- **Username**: `admin`
- **Password**: `admin123`

⚠️ **PENTING**: Ganti password admin segera setelah login pertama untuk keamanan!

### Login sebagai Penyewa (User)

Pengguna dapat mendaftar akun baru atau login dengan akun yang sudah terdaftar:

- **URL Register**: `{BASE_URL}user/register.php`
- **URL Login**: `{BASE_URL}user/login.php`

### Fitur Admin

- Dashboard: Ringkasan statistik sewa
- Manage Products: Tambah, edit, hapus produk
- Manage Orders: Kelola pesanan sewa (terima, tolak, selesaikan)
- Laporan: Laporan sewa dan kwitansi

### Fitur User

- Lihat katalog produk
- Buat pesanan sewa
- Lihat riwayat sewa
- Edit profil

## Struktur Project

```
Persewaan-Mesin-Vacuum-Cleaner/
├── admin/                  # Halaman admin
│   ├── dashboard.php
│   ├── login.php
│   ├── manage_products.php
│   ├── manage_orders.php
│   └── ...
├── api/                    # API endpoints
├── assets/                 # Static assets (CSS, JS, images)
├── config/                 # Konfigurasi
│   ├── database.php        # Konfigurasi database (SQLite)
│   ├── environment.php     # Konfigurasi environment
│   └── paths.php           # Konfigurasi path
├── controllers/            # Controller logic
├── database/               # Folder database SQLite
│   └── akasa.db            # Database file (dibuat otomatis)
├── includes/               # Include files (header, footer, functions)
│   └── sqlite_helper.php   # Helper untuk SQLite
├── models/                 # Model database
├── pages/                  # Halaman publik
│   ├── produk.php
│   ├── kontak.php
│   └── ...
├── user/                   # Halaman user
│   ├── login.php
│   ├── register.php
│   ├── orders.php
│   └── ...
├── views/                  # View templates
├── index.php               # Halaman utama
├── setup.php               # Script setup database
├── .installed              # Marker file (dibuat setelah setup)
└── .htaccess               # Konfigurasi Apache
```

## Keuntungan Menggunakan SQLite

- **Tanpa Setup MySQL**: Tidak perlu install dan konfigurasi MySQL server
- **Tanpa Password**: Tidak perlu mengatur user dan password database
- **Portable**: Database hanya 1 file, mudah dipindahkan atau backup
- **Cepat**: Sangat cepat untuk aplikasi kecil hingga menengah
- **Cross-Platform**: Jalan di Windows, Linux, Mac tanpa perubahan

## Troubleshooting

### Error: Koneksi Database Gagal

Pastikan:
1. Ekstensi SQLite3 sudah aktif di PHP (cek dengan `php -m | findstr sqlite`)
2. Folder `database/` bisa ditulis (writable)
3. Jalankan `php setup.php` untuk membuat database

### Error: Headers Already Sent

Pastikan tidak ada output sebelum `session_start()` atau `header()`. Check untuk whitespace sebelum tag PHP `<?php`.

### Images Tidak Tampil

Pastikan:
1. Folder `assets/images/` ada dan berisi gambar
2. Permission folder sudah benar (readable)
3. URL gambar benar di konfigurasi paths
4. Gambar produk ada di `assets/images/products/` (vc2.jpg, vc3.jpg, vc4.jpg)
5. Jika gambar tidak ada, script otomatis akan menampilkan placeholder

### Error BASE_URL Salah

BASE_URL sekarang otomatis dideteksi dari server. Jika masih ada masalah, cek file `config/paths.php`.

### Database File Tidak Dibuat

Jika file `database/akasa.db` tidak dibuat setelah menjalankan setup:
1. Pastikan folder `database/` ada dan bisa ditulis
2. Cek permission folder
3. Jalankan `php setup.php` lagi

## Keamanan

- Jangan pernah meng-commit file `.env` atau file setup ke repository
- Selalu hapus file `setup.php` setelah instalasi
- Ganti password admin default segera
- Gunakan HTTPS di production
- Validasi semua input user
- Gunakan prepared statements untuk query database

## Customization

### Mengubah Default Admin

Edit file `setup.php` dan ubah variabel di bagian:

```php
$default_admin_username = 'admin';
$default_admin_password = 'admin123'; // Ganti ini
$default_admin_nama = 'Administrator';
$default_admin_email = 'admin@akasateknik.com';
```

### Mengubah Konfigurasi Environment

Edit file `config/environment.php` untuk mengubah mode development/production.

## Support

Untuk pertanyaan atau masalah, silakan hubungi tim support Akasa Teknik.

## License

© 2024 Akasa Teknik. All rights reserved.
