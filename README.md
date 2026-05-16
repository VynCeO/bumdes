# BUMDes Sukses Bersama - Web PHP Native

## Cara Instalasi

### 1. Setup Database
- Buka phpMyAdmin
- Import / jalankan file `database.sql`

### 2. Konfigurasi
Edit `config.php`:
```
$host   = 'localhost';
$dbname = 'bumdes_db';
$user   = 'root';
$pass   = '';
```

### 3. Letakkan di Server
- XAMPP: `htdocs/bumdes/`
- Laragon: `www/bumdes/`

### 4. Akses
- Website: `http://localhost/bumdes/`
- Login User: `http://localhost/bumdes/login.php`
- Daftar User: `http://localhost/bumdes/register.php`
- Admin: `http://localhost/bumdes/admin/login.php`
- Login Admin: username `admin` / password `admin123`

## Struktur File
```
bumdes/
├── index.php          → Halaman utama
├── produk.php         → Halaman produk (no hamburger)
├── reservasi.php      → Reservasi (wajib login)
├── login.php          → Login user publik
├── logout.php         → Logout user
├── register.php       → Daftar akun baru
├── config.php         → Koneksi DB + helper
├── database.sql       → Script database
├── assets/css/style.css
└── admin/
    ├── login.php
    ├── logout.php
    ├── auth.php               → Cek sesi + sidebar
    ├── index.php              → Dashboard
    ├── manage_reservasi.php   → CRUD reservasi
    ├── manage_produk.php      → CRUD produk + upload foto
    ├── manage_pimpinan.php    → CRUD pimpinan + upload foto
    └── manage_users.php       → Kelola user terdaftar
```

## Fitur Lengkap
✅ Register & Login user publik (session + cookie "ingat saya")
✅ Reservasi wajib login (anti-spam)
✅ Riwayat reservasi milik user sendiri
✅ Upload foto produk (JPG/PNG/WEBP, maks 3MB)
✅ Upload foto pimpinan (JPG/PNG/WEBP, maks 2MB)
✅ CRUD lengkap reservasi di admin
✅ CRUD produk & pimpinan dengan foto
✅ Kelola users terdaftar
✅ Navbar produk.php tanpa hamburger
✅ Dashboard admin dengan statistik lengkap
