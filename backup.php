<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'administrator') {
    echo "<script>alert('Akses Ditolak! Fitur Backup Database hanya untuk Administrator.'); window.location='index.php';</script>";
    exit;
}

if (isset($_POST['download_backup'])) {
    
    // Ambil daftar seluruh tabel di database
    $tables = array();
    $result = mysqli_query($connect, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }

    $return = "-- ========================================================\n";
    $return .= "-- BACKUP DATABASE SISTEM PERPUSTAKAAN\n";
    $return .= "-- Tanggal Backup: " . date('Y-m-d H:i:s') . "\n";
    $return .= "-- Oleh Admin    : " . $_SESSION['nama_lengkap'] . "\n";
    $return .= "-- ========================================================\n\n";
    $return .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    // Loop setiap tabel
    foreach ($tables as $table) {
        $result = mysqli_query($connect, "SELECT * FROM `$table`");
        $num_fields = mysqli_num_fields($result);

        $return .= "DROP TABLE IF EXISTS `$table`;\n";
        $row2 = mysqli_fetch_row(mysqli_query($connect, "SHOW CREATE TABLE `$table`"));
        $return .= "\n\n" . $row2[1] . ";\n\n";

        for ($i = 0; $i < $num_fields; $i++) {
            while ($row = mysqli_fetch_row($result)) {
                $return .= "INSERT INTO `$table` VALUES(";
                for ($j = 0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $return .= '"' . $row[$j] . '"';
                    } else {
                        $return .= '""';
                    }
                    if ($j < ($num_fields - 1)) {
                        $return .= ',';
                    }
                }
                $return .= ");\n";
            }
        }
        $return .= "\n\n\n";
    }
    $return .= "SET FOREIGN_KEY_CHECKS=1;\n";

    // Header untuk Pengunduhan Berkas .sql
    $filename = 'backup_perpustakaan_' . date('Y-m-d_H-i-s') . '.sql';
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($return));
    
    echo $return;
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Database - Administrator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; overflow-x: hidden; }
        .sidebar { min-height: 100vh; width: 260px; background-color: #212529; color: #fff; position: fixed; top: 0; left: 0; z-index: 100; }
        .sidebar .nav-link { color: #adb5bd; padding: 12px 20px; font-weight: 500; border-radius: 5px; margin: 2px 15px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #0d6efd; }
        .sidebar .nav-link i { margin-right: 10px; }
        .main-content { margin-left: 260px; padding: 30px; }
        @media (max-width: 768px) { .sidebar { margin-left: -260px; } .main-content { margin-left: 0; } }
    </style>
</head>
<body class="bg-light">

    <!-- SIDEBAR NAVIGASI -->
    <div class="sidebar d-flex flex-column justify-content-between pb-3">
        <div>
            <div class="px-4 py-4 border-bottom border-secondary mb-3">
                <h5 class="fw-bold mb-0 text-white">📚 DBA Panel</h5>
                <small class="text-muted">Sistem Perpustakaan</small>
            </div>
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="buku.php" class="nav-link">
                        <i class="bi bi-book"></i> Kelola Buku (T1)
                    </a>
                </li>
                <li class="nav-item">
                    <a href="peminjaman.php" class="nav-link">
                        <i class="bi bi-arrow-left-right"></i> Peminjaman (T2)
                    </a>
                </li>
                <?php if ($_SESSION['role'] === 'Administrator'): ?>
                <li class="nav-item">
                    <a href="impor_anggota.php" class="nav-link">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Impor CSV (T3 & T4)
                    </a>
                </li>
                <li class="nav-item">
                    <a href="backup.php" class="nav-link active">
                        <i class="bi bi-database-down"></i> Backup Data (DBA)
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="px-3 border-top border-secondary pt-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 text-white text-truncate" style="max-width: 130px;"><?php echo $_SESSION['nama_lengkap']; ?></h6>
                    <small class="text-muted d-block"><?php echo $_SESSION['role']; ?></small>
                </div>
                <a href="logout.php" class="btn btn-outline-danger btn-sm" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 mb-0">Backup Data Basis Data</h2>
                <span class="text-muted">Fitur Administrator untuk Pemeliharaan & Disaster Recovery.</span>
            </div>
            <span class="badge bg-danger px-3 py-2">Fitur Khusus Administrator</span>
        </div>

        <div class="card border-0 shadow-sm col-md-8">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-shield-lock-fill text-warning fs-1 me-3"></i>
                    <div>
                        <h5 class="card-title mb-1">Backup Keseluruhan Basis Data</h5>
                        <p class="text-muted mb-0">Unduh seluruh tabel, data anggota, buku, sirkulasi peminjaman, dan arsip dokumen dalam bentuk file SQL.</p>
                    </div>
                </div>
                <hr>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i> File hasil backup dapat di-import kembali melalui <strong>phpMyAdmin</strong> pada menu <em>Import</em> jika terjadi kerusakan data.
                </div>
                <form method="POST">
                    <button type="submit" name="download_backup" class="btn btn-primary btn-lg w-100 mt-2">
                        <i class="bi bi-download me-2"></i> Unduh Backup Database (.sql)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>