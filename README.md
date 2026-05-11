# BUMDes Sukses Bersama - Web PHP Native
## Cara Instalasi

### 1. Siapkan Database
- Buka phpMyAdmin atau MySQL CLI
- Jalankan file `database.sql`
- Database `bumdes_db` akan terbuat otomatis

### 2. Sesuaikan Koneksi Database
Edit file `config.php`:
```php
$host   = 'localhost';  // host database
$dbname = 'bumdes_db';  // nama database
$user   = 'root';       // username MySQL
$pass   = '';           // password MySQL
```

### 3. Upload ke Server
Letakkan seluruh folder `bumdes/` di dalam:
- XAMPP: `htdocs/bumdes/`
- Laragon: `www/bumdes/`
- Hosting: `public_html/bumdes/`

### 4. Akses Website
- **Website:** `http://localhost/bumdes/`
- **Admin:** `http://localhost/bumdes/admin/login.php`
- **Login default:** username `admin`, password `admin123`

---

## Struktur File
```
bumdes/
├── index.php          → Halaman utama (Home)
├── produk.php         → Halaman produk & unit usaha
├── reservasi.php      → Form reservasi online
├── config.php         → Koneksi database
├── database.sql       → Script buat database
├── assets/
│   └── css/style.css  → Styling semua halaman
└── admin/
    ├── login.php          → Login admin
    ├── logout.php         → Logout
    ├── auth.php           → Cek sesi & sidebar
    ├── index.php          → Dashboard statistik
    ├── manage_reservasi.php → Kelola pemesanan
    ├── manage_produk.php    → Kelola produk/layanan
    └── manage_pimpinan.php  → Kelola data pimpinan
```

## Fitur
✅ Halaman Home dengan hero, pimpinan, unit usaha, kontak  
✅ Halaman Produk dengan filter kategori  
✅ Form Reservasi Online dengan tampil pemesanan terbaru  
✅ Admin Dashboard dengan statistik  
✅ Kelola Reservasi (konfirmasi / batalkan / hapus)  
✅ Kelola Produk (tambah / edit / hapus)  
✅ Kelola Pimpinan (tambah / edit / hapus)  
✅ Login/Logout admin dengan session  
✅ Responsive mobile-friendly  
