DROP DATABASE IF EXISTS classroom_db;
CREATE DATABASE classroom_db;
USE classroom_db;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('dosen', 'mahasiswa') NOT NULL
);

-- Tabel mata kuliah
CREATE TABLE matkul (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL
);

-- Tabel tugas
CREATE TABLE tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deadline DATETIME NOT NULL,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    matkul_id INT NOT NULL,
    FOREIGN KEY (matkul_id) REFERENCES matkul(id) ON DELETE CASCADE
);

-- Tabel jawaban tugas / submissions
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tugas_id INT NOT NULL,
    user_id INT NOT NULL,
    jawaban TEXT NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    nilai INT DEFAULT NULL,
    komentar TEXT DEFAULT NULL,
    FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contoh user (password: password123)
INSERT INTO users (name, email, password, role) VALUES
('Dosen Satu', 'dosen@example.com', '$2y$10$uM1YVGjmlPBmEqKU8ty1S.zm/JFA1WkZfhu1ZV5zrJP7H1vdXEBVi', 'dosen'),
('Mahasiswa Satu', 'mhs@example.com', '$2y$10$uM1YVGjmlPBmEqKU8ty1S.zm/JFA1WkZfhu1ZV5zrJP7H1vdXEBVi', 'mahasiswa');

-- Contoh matkul
INSERT INTO matkul (nama) VALUES ('Pemrograman Web');

-- Contoh tugas
INSERT INTO tugas (judul, deskripsi, matkul_id) VALUES 
('Tugas 1', 'Buat form login dan register menggunakan PHP dan MySQL.', 1);
