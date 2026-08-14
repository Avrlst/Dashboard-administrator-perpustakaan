<?php
// index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/db.php'; // Menggunakan $connect

// Proteksi Halaman: Wajib Login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// -------------------------------------------------------------------------
// QUERY STATISTIK DATA UNTUK DASHBOARD
// -------------------------------------------------------------------------
// 1. Total Anggota
$q_anggota = mysqli_query($connect, "SELECT COUNT(*) AS total FROM anggota");
$total_anggota = 0;
if ($q_anggota) {
    $row_a = mysqli_fetch_assoc($q_anggota);
    $total_anggota = $row_a['total'];
}

// 2. Total Judul Buku
$q_buku = mysqli_query($connect, "SELECT COUNT(*) AS total FROM buku");
$total_buku = 0;
if ($q_buku) {
    $row_b = mysqli_fetch_assoc($q_buku);
    $total_buku = $row_b['total'];
}

// 3. Total Stok Buku Tersedia
$q_stok = mysqli_query($connect, "SELECT SUM(stok) AS total_stok FROM buku");
$total_stok = 0;
if ($q_stok) {
    $row_s = mysqli_fetch_assoc($q_stok);
    $total_stok = $row_s['total_stok'] ? $row_s['total_stok'] : 0;
}

// 4. Total Transaksi Peminjaman Aktif
$q_pinjam = mysqli_query($connect, "SELECT COUNT(*) AS total FROM peminjaman");
$total_pinjam = 0;
if ($q_pinjam) {
    $row_p = mysqli_fetch_assoc($q_pinjam);
    $total_pinjam = $row_p['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Perpustakaan</title>
    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            min-height: 100vh;
            overflow-x: hidden;
        }
        /* Style Sidebar */
        .sidebar {
            min-height: 100vh;
            width: 260px;
            background-color: #212529;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            font-weight: 500;
            border-radius: 5px;
            margin: 2px 15px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        /* Style Area Konten Utama */
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-light">

    <!-- SIDEBAR NAVIGASI -->
    <div class="sidebar d-flex flex-column justify-content-between pb-3">
        <div>
            <!-- Header Sidebar -->
            <div class="px-4 py-4 border-bottom border-secondary mb-3">
                <h5 class="fw-bold mb-0 text-white">Perpustakaan BSI</h5>
                <small class="text-muted">Sistem Perpustakaan</small>
            </div>

            <!-- Menu Navigasi -->
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="buku.php" class="nav-link">
                        <i class="bi bi-book"></i> Kelola Buku 
                    </a>
                </li>
                <li class="nav-item">
                    <a href="peminjaman.php" class="nav-link">
                        <i class="bi bi-arrow-left-right"></i> Peminjaman 
                    </a>
                </li>
                
                <?php if ($_SESSION['role'] === 'administrator'): ?>
                <li class="nav-item">
                    <a href="impor_anggota.php" class="nav-link">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Impor Anggota
                    </a>
                </li>
                <li class="nav-item">
                    <a href="impor_buku.php" class="nav-link">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Impor Buku
                    </a>
                </li>
                <li class="nav-item">
                <a href="backup.php" class="nav-link">
                    <i class="bi bi-database-down"></i> Backup Data (DBA)
                </a>
</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Profil & Logout di Bagian Bawah Sidebar -->
        <div class="px-3 border-top border-secondary pt-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 text-white text-truncate" style="max-width: 130px;">
                        <?php echo $_SESSION['nama_lengkap']; ?>
                    </h6>
                    <small class="text-muted d-block"><?php echo $_SESSION['role']; ?></small>
                </div>
                <a href="logout.php" class="btn btn-outline-danger btn-sm" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- KONTEN UTAMA (MAIN CONTENT) -->
    <div class="main-content">
        
        <!-- Header Atas Dashboard -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 mb-0">Dashboard Utama</h2>
                <span class="text-muted">Ringkasan statistik data perpustakaan terkini.</span>
            </div>
            <span class="badge bg-primary px-3 py-2 fs-6">
                Role: <?php echo $_SESSION['role']; ?>
            </span>
        </div>

        <!-- Alert Status Login -->
        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                    if ($_GET['status'] === 'welcome_admin') echo "Selamat datang kembali, Administrator! Anda memiliki akses penuh.";
                    else echo "Selamat datang, Petugas! Selamat bekerja.";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Cards Statistik -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 text-white-50">Total Anggota</h6>
                                <h2 class="display-6 fw-bold mb-0"><?php echo $total_anggota; ?></h2>
                            </div>
                            <i class="bi bi-people fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 text-white-50">Judul Buku</h6>
                                <h2 class="display-6 fw-bold mb-0"><?php echo $total_buku; ?></h2>
                            </div>
                            <i class="bi bi-journal-bookmark fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 text-white-50">Total Stok Buku</h6>
                                <h2 class="display-6 fw-bold mb-0"><?php echo $total_stok; ?></h2>
                            </div>
                            <i class="bi bi-bookshelf fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 text-dark-50">Peminjaman Aktif</h6>
                                <h2 class="display-6 fw-bold mb-0"><?php echo $total_pinjam; ?></h2>
                            </div>
                            <i class="bi bi-arrow-repeat fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Pintas Modul Ujian -->
        <h4 class="mb-3">Akses Cepat Fitur</h4>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-book"></i> Pengelolaan Buku (T1)</h5>
                        <p class="card-text text-muted">Tambah stok, perbarui judul, dan kelola katalog buku.</p>
                        <a href="buku.php" class="btn btn-outline-primary w-100">Buka Kelola Buku</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="bi bi-arrow-left-right"></i> Peminjaman & Kembali (T2)</h5>
                        <p class="card-text text-muted">Catat transaksi sirkulasi peminjaman dan hitung denda.</p>
                        <a href="peminjaman.php" class="btn btn-outline-success w-100">Buka Peminjaman</a>
                    </div>
                </div>
            </div>

            <?php if ($_SESSION['role'] === 'administrator'): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body">
                        <h5 class="card-title text-dark"><i class="bi bi-file-earmark-spreadsheet"></i> Impor Anggota CSV (T3 & T4)</h5>
                        <p class="card-text text-muted">Integrasi data masal dari CSV dan pembersihan data otomatis.</p>
                        <a href="impor_anggota.php" class="btn btn-primary w-100">Buka Impor CSV</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>