-- =============================================
-- BUMDes Sukses Bersama - Database Lengkap
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

-- Tabel USER publik (untuk reservasi anti-spam)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    no_hp VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    foto VARCHAR(255),
    urutan INT DEFAULT 1,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif'
);

-- Tabel reservasi
CREATE TABLE IF NOT EXISTS reservasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    layanan VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    tanggal_kembali DATE,
    keterangan TEXT,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Data admin default (password: admin123)
INSERT IGNORE INTO admin_user (username, password, nama_lengkap) VALUES
('admin', SHA2('admin123', 256), 'Administrator');

-- Data pimpinan
INSERT IGNORE INTO pimpinan (id, nama, posisi, urutan) VALUES
(1,'Syaiful','Komisaris / Kepala Desa',1),
(2,'Marsudi, S.Pd, M.M','Direktur',2),
(3,'Agus Indra Prasetyo','Sekretaris',3),
(4,'Mohammad Murti Sudiyo','Bendahara',4);

-- Data unit usaha
INSERT IGNORE INTO unit_usaha (id, nama, deskripsi, harga, urutan) VALUES
(1,'GOR Sugihwaras','Gedung Olahraga serbaguna untuk berbagai acara dan kegiatan olahraga masyarakat.',500000,1),
(2,'Rental Tenda','Sewa tenda untuk berbagai acara: pernikahan, hajatan, dan kegiatan luar ruangan.',150000,2),
(3,'Air Minum Kemasan','Air minum dalam kemasan higienis produksi BUMDes berkualitas terjamin.',20000,3),
(4,'Kopi Melek','Warung kopi dengan berbagai varian minuman khas dan jajanan lokal.',10000,4),
(5,'Peternakan Sapi & Kambing','Penjualan sapi dan kambing berkualitas dari peternakan desa Sugihwaras.',0,5),
(6,'Pembayaran PBB','Layanan pembayaran Pajak Bumi dan Bangunan (PBB) tanpa antri panjang.',0,6);
