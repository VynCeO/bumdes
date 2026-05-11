-- =============================================
-- BUMDes Sukses Bersama - Database
-- Jalankan file ini di phpMyAdmin / MySQL CLI
-- =============================================

CREATE DATABASE IF NOT EXISTS bumdes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bumdes_db;

-- Tabel admin
CREATE TABLE IF NOT EXISTS admin_user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100)
);

-- Tabel pimpinan
CREATE TABLE IF NOT EXISTS pimpinan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    posisi VARCHAR(100) NOT NULL,
    foto VARCHAR(255),
    urutan INT DEFAULT 1
);

-- Tabel unit usaha (produk)
CREATE TABLE IF NOT EXISTS unit_usaha (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) DEFAULT 0,
    urutan INT DEFAULT 1,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif'
);

-- Tabel reservasi
CREATE TABLE IF NOT EXISTS reservasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    layanan VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    tanggal_kembali DATE,
    keterangan TEXT,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data default admin (password: admin123)
INSERT INTO admin_user (username, password, nama_lengkap) VALUES
('admin', SHA2('admin123', 256), 'Administrator');

-- Data pimpinan
INSERT INTO pimpinan (nama, posisi, urutan) VALUES
('Syaiful', 'Komisaris / Kepala Desa', 1),
('Marsudi, S.Pd, M.M', 'Direktur', 2),
('Agus Indra Prasetyo', 'Sekretaris', 3),
('Mohammad Murti Sudiyo', 'Bendahara', 4);

-- Data unit usaha
INSERT INTO unit_usaha (nama, deskripsi, harga, urutan) VALUES
('GOR Sugihwaras', 'Gedung Olahraga serbaguna untuk berbagai acara dan kegiatan olahraga masyarakat.', 500000, 1),
('Rental Tenda', 'Sewa tenda untuk berbagai acara: pernikahan, hajatan, dan kegiatan luar ruangan.', 150000, 2),
('Air Minum Kemasan', 'Air minum dalam kemasan higienis produksi BUMDes berkualitas terjamin.', 20000, 3),
('Kopi Melek', 'Warung kopi dengan berbagai varian minuman khas dan jajanan lokal.', 10000, 4),
('Peternakan Sapi & Kambing', 'Penjualan sapi dan kambing berkualitas dari peternakan desa Sugihwaras.', 0, 5),
('Pembayaran PBB', 'Layanan pembayaran Pajak Bumi dan Bangunan (PBB) tanpa antri panjang.', 0, 6);
