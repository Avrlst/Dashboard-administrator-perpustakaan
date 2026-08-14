CREATE DATABASE IF NOT EXISTS db_perpustakaan;
USE db_perpustakaan;

CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(255)NOT NULL,
    role ENUM('administrator','petugas') NOT NULL
);

CREATE TABLE anggota (
    nim VARCHAR(15) NOT NULL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    jurusan VARCHAR(50) NOT NULL,
    no_tlp VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL
);

CREATE TABLE buku (
    isbn VARCHAR(20) NOT NULL PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    pengarang VARCHAR(20) NOT NULL,
    penerbit VARCHAR(20) NOT NULL,
    tahun_terbit YEAR,
    stok INT DEFAULT 0
);

CREATE TABLE peminjaman (
    id_pinjam VARCHAR(20) PRIMARY KEY,
    nim VARCHAR(15),
    tgl_pinjam DATE NOT NULL,
    tgl_kembali_seharusnya DATE NOT NULL,
    FOREIGN KEY (nim) REFERENCES anggota(nim) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE detail_peminjaman(
    id_pinjam VARCHAR(20),
    isbn VARCHAR(20),
    status ENUM('Dipinjam', 'Kembali') DEFAULT'Dipinjam',
    tgl_dikembalikan_aktual DATE NULL,
    denda DECIMAL(10,2) DEFAULT 0.00,
    PRIMARY KEY (id_pinjam,isbn),
    FOREIGN KEY (id_pinjam) REFERENCES peminjaman(id_pinjam) ON DELETE CASCADE,
    FOREIGN KEY (isbn) REFERENCES buku(isbn) ON UPDATE CASCADE
);

CREATE TABLE dokumen(
    id_dokumen VARCHAR(20) PRIMARY KEY ,
    judul VARCHAR(255) NOT NULL,
    tipe_file VARCHAR(10) NOT NULL,
    ukuran_file INT NOT NULL,
    lokasi_file VARCHAR(255) NOT NULL
);

INSERT INTO users (username,password,nama_lengkap,role) VALUES
('admin',MD5('admin123'),'Administrator Perpustakaan','Administrator'),
('petugas',MD5('petugas123'),'Petugas Perpustakaan','Petugas');
