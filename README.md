# 📚 Sistem Manajemen Perpustakaan (Library Management System)

Aplikasi web manajemen perpustakaan yang dibangun menggunakan **Native PHP**, **MySQL**, dan **Bootstrap**. Proyek ini dirancang untuk mendigitalisasi operasional perpustakaan, mencakup pengelolaan data buku dan anggota, sirkulasi peminjaman serta pengembalian dengan perhitungan denda keterlambatan otomatis, hingga fitur utilitas basis data untuk administrator.

---

## 🌟 Fitur Utama

- **Manajemen Sirkulasi & Denda Otomatis**: Pencatatan transaksi peminjaman dan pengembalian buku secara *real-time* yang dilengkapi dengan kalkulasi denda keterlambatan otomatis berdasarkan tenggat waktu pengembalian.
- **Katalog Buku & Data Anggota**: Pengelolaan data inventaris buku (judul, penulis, kategori, stok) serta pendataan anggota perpustakaan yang terstruktur menggunakan basis data relasional MySQL.
- **Bulk CSV Import**: Fitur impor data buku atau anggota secara massal menggunakan format file `.csv` untuk mempercepat proses input data awal.
- **Database Backup & Restore Tools**: Utilitas khusus administrator untuk melakukan pencadangan (*backup*) dan pemulihan (*restore*) basis data secara langsung dari panel admin guna menjaga keamanan dan integritas data.
- **Dashboard & Laporan Sirkulasi**: Ringkasan statistik koleksi buku, peminjaman aktif, serta riwayat transaksi dengan antarmuka responsif berbasis Bootstrap.

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: Native PHP (Prosedural / Modular)
- **Database**: MySQL (Relational Database)
- **Frontend**: HTML5, CSS3, Bootstrap, JavaScript
- **Tools & Environment**: Laragon / XAMPP, phpMyAdmin, Git

---

## 📁 Struktur Proyek

```text
├── config/              # Konfigurasi koneksi database MySQL & pengaturan sistem
├── admin/               # Halaman dashboard, manajemen buku, anggota, & transaksi
├── utils/               # Modul impor CSV massal & skrip backup/restore database
├── assets/              # File statis (Bootstrap CSS/JS, ikon, & gambar pendukung)
├── database/            # File skema SQL (.sql) untuk inisialisasi tabel awal
└── index.php            # Halaman utama & gerbang otentikasi administrator

🚀 Cara Menjalankan Proyek (Lokal)
 * Clone repositori ini ke folder web server lokal (htdocs atau www):
   git clone [https://github.com/Avrlst/library-management-system.git](https://github.com/Avrlst/library-management-system.git)

 * Siapkan Database MySQL:
   * Buka phpMyAdmin (http://localhost/phpmyadmin).
   * Buat database baru (misalnya db_perpustakaan).
   * Impor file .sql yang tersedia di dalam folder database/.
 * Sesuaikan Konfigurasi Koneksi:
   Buka file konfigurasi database di folder config/ dan pastikan kredensial sesuai dengan server lokal kamu (host, username, password, dan nama_database).
 * Jalankan Aplikasi:
   Nyalakan layanan Apache dan MySQL pada Laragon atau XAMPP, lalu akses aplikasi melalui browser di:
   http://localhost/library-management-system

👤 Pengembang
Avrilistianto Ananda Nabil
 * GitHub: @Avrlst
 * Portofolio: avrilistianto-portofolio.vercel.app

---

### Cara Pasang ke Repositori GitHub:

1. Buka halaman repositori **Sistem Manajemen Perpustakaan** di akun GitHub `@Avrlst`.
2. Klik tombol **Add file** > **Create new file** (atau klik ikon pensil pada file `README.md` jika sudah tersedia).
3. Beri nama file `README.md`, lalu tempel (*paste*) seluruh kode Markdown di atas ke dalam editor.
4. Klik **Commit changes...** untuk menyimpannya

