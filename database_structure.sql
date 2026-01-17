-- Database Structure for Akasa Teknik Rental System

-- Users Table (penyewa)
CREATE TABLE IF NOT EXISTS penyewa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    no_hp VARCHAR(15),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table (produk)
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_mesin VARCHAR(100) NOT NULL,
    merk VARCHAR(50),
    spesifikasi TEXT,
    harga_sewa_per_hari DECIMAL(10,2) NOT NULL,
    stok INT DEFAULT 0,
    gambar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Rental Transactions Table (sewa)
CREATE TABLE IF NOT EXISTS sewa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewa INT NOT NULL,
    id_produk INT NOT NULL,
    tanggal_sewa DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    total_harga DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'disewa', 'selesai', 'dibatalkan') DEFAULT 'pending',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_penyewa) REFERENCES penyewa(id),
    FOREIGN KEY (id_produk) REFERENCES produk(id)
);

-- Indexes for better performance
CREATE INDEX idx_sewa_penyewa ON sewa(id_penyewa);
CREATE INDEX idx_sewa_produk ON sewa(id_produk);
CREATE INDEX idx_sewa_status ON sewa(status);
CREATE INDEX idx_sewa_tanggal ON sewa(tanggal_sewa);
