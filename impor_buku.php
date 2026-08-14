<?php

session_start();
require_once 'config/db.php';


if (isset($SESSION['login']) || $_SESSION['role'] !== 'administrator') {
    die("Akses Ditolak! hanya Administrator yang memiliki hak akses untuk fitur ini");
}

if (isset($_POST['import_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        
        $file_temp     = $_FILES['csv_file']['tmp_name'];
        $raw_file_name = $_FILES['csv_file']['name']; 
        $file_ext      = strtolower(pathinfo($raw_file_name, PATHINFO_EXTENSION));

        if ($file_ext !== 'csv') {
            $pesan = "<div class='alert alert-danger'>⚠️ Gagal! Format file harus <strong>.csv</strong></div>";
        } else {
            $handle = fopen($file_temp, "r");
            if ($handle !== FALSE) {
                $total_berhasil = 0;
                $total_gagal    = 0;
                $row_counter    = 0;

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row_counter++;
                    if ($row_counter === 1) continue; // Lewati baris header (NIM, Nama, dll)

                    
                    $isbn_raw = isset($data[0]) ? preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', trim($data[0])) : '';
                    $isbn     = mysqli_real_escape_string($connect, $isbn_raw);
                    
                    $isbn    = isset($data[0]) ? mysqli_real_escape_string($connect, trim($data[0])) : '';
                    $judul = isset($data[1]) ? mysqli_real_escape_string($connect, trim($data[1])) : '';
                    $pengarang = isset($data[2]) ? mysqli_real_escape_string($connect, trim($data[2])) : '';
                    $penerbit   = isset($data[3]) ? mysqli_real_escape_string($connect, trim($data[3])) : '';
                    $tahun_terbit   = isset($data[4]) ? mysqli_real_escape_string($connect, trim($data[4])) : '';
                    $stok         = isset($data[5]) ? (int)trim($data[5]) : 0;

                    // 1. Cek jika NIM atau Nama kosong
                    if (empty($isbn) || empty($judul)) {
                        $total_gagal++;
                        continue;
                    }

                    
                    $cek_isbn = mysqli_query($connect, "SELECT isbn FROM buku WHERE isbn = '$isbn'");
                    if (mysqli_num_rows($cek_isbn) > 0) {
                        $total_gagal++; // 
                        continue;
                    }

                    // 3. INSERT DATA ANGGOTA BARU
                    $query_insert = "INSERT INTO buku (isbn, judul, pengarang, penerbit, tahun_terbit, stok) VALUES ('$isbn', '$judul', '$pengarang','$penerbit', '$tahun_terbit', '$stok')";
                    if (mysqli_query($connect, $query_insert)) {
                        $total_berhasil++;
                    } else {
                        $total_gagal++;
                    }
                }
                fclose($handle);

               
                $judul_doc   = mysqli_real_escape_string($connect, $raw_file_name);
                $tipe_doc    = 'csv';
                $ukuran_doc  = (int)$_FILES['csv_file']['size'];
                $lokasi_doc  = 'uploads/' . $judul_doc;

                
                $q_max_id    = mysqli_query($connect, "SELECT MAX(id_dokumen) AS max_id FROM dokumen");
                $row_max     = mysqli_fetch_assoc($q_max_id);
                $id_dok_baru = ($row_max['max_id']) ? $row_max['max_id'] + 1 : 1;

                $q_dokumen   = "INSERT INTO dokumen (id_dokumen, judul, tipe_file, ukuran_file, lokasi_file) 
                                VALUES ($id_dok_baru, '$judul_doc', '$tipe_doc', $ukuran_doc, '$lokasi_doc')";
                
                $simpan_dok = mysqli_query($connect, $q_dokumen);

               
                if ($total_berhasil > 0) {
                    $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                                <strong>✅ Impor Berhasil!</strong><br>
                                • Data Baru Dimasukkan: <strong>$total_berhasil</strong> anggota.<br>
                                • Data Duplikat/Diabaikan: <strong>$total_gagal</strong>.<br>
                                <small class='text-muted'>File <strong>$raw_file_name</strong> tersimpan ke riwayat dokumen.</small>
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                              </div>";
                } else {
                    $pesan = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                                <strong>⚠️ Tidak Ada Data Baru yang Ditambahkan!</strong><br>
                                Seluruh NIM pada file <strong>$raw_file_name</strong> ($total_gagal data) sudah terdaftar di database.<br>
                                <small class='text-muted'>File tetap dicatat ke riwayat dokumen.</small>
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                              </div>";
                }
            } else {
                $pesan = "<div class='alert alert-danger'>⚠️ Gagal membuka file CSV.</div>";
            }
        }
    } else {
        $pesan = "<div class='alert alert-danger'>⚠️ Silakan pilih file CSV terlebih dahulu.</div>";
    }
}
$res_buku = mysqli_query($connect, "SELECT * FROM buku ORDER BY isbn DESC");
$res_dokumen = mysqli_query($connect, "SELECT * FROM dokumen ORDER BY id_dokumen DESC");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impor Anggota & Dokumen - Sistem Perpustakaan</title>
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
                        <i class="bi bi-book"></i> Kelola Buku 
                    </a>
                </li>
                <li class="nav-item">
                    <a href="peminjaman.php" class="nav-link">
                        <i class="bi bi-arrow-left-right"></i> Peminjaman 
                    </a>
                </li>
                <li class="nav-item">
                    <a href="impor_anggota.php" class="nav-link">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Impor anggota
                    </a>
                </li>
                <li class="nav-item">
                    <a href="impor_buku.php" class="nav-link active">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Impor buku
                    </a>
                </li>
                <li class="nav-item">
                    <a href="backup.php" class="nav-link">
                        <i class="bi bi-database-down"></i> Backup Data (DBA)
                     </a>
                </li>
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
                <h2 class="h3 mb-0">Impor Data Buku & Arsip Dokumen</h2>
                <span class="text-muted">Poin T3 & T4: Integrasi Data Masal & Pengarsipan Berkas.</span>
            </div>
        </div>

        <?php if(!empty($pesan)) echo $pesan; ?>

        <!-- Form Upload CSV -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-primary text-white font-weight-bold">Upload File CSV dari Laptop</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Pilih File CSV (.csv)</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        <small class="text-muted">Format urutan kolom: <code>ISBN, JUDUL, NAMA PENGARANG, NAMA PENERBIT, TAHUN TERBIT ,STOK</code></small>
                    </div>
                    <div class="col-md-4 text-end mt-4">
                        <button type="submit" name="import_csv" class="btn btn-success w-100">
                            <i class="bi bi-upload"></i> Unggah & Impor Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <!-- Tabel Data Anggota -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white">Daftar BUKU di Database</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ISBN</th>
                                        <th>JUDUL</th>
                                        <th>NAMA PENGARANG</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($res_buku && mysqli_num_rows($res_buku) > 0): ?>
                                        <?php while ($a = mysqli_fetch_assoc($res_buku)): ?>
                                            <tr>
                                                <td><code><?php echo $a['isbn']; ?></code></td>
                                                <td><strong><?php echo $a['judul']; ?></strong></td>
                                                <td><?php echo $a['pengarang'] ? $a['pengarang'] : '-'; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted p-4">Belum ada data anggota.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Riwayat Dokumen Upload -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-secondary text-white"><i class="bi bi-folder2-open"></i> Riwayat Dokumen</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Judul File</th>
                                        <th>Ukuran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($res_dokumen && mysqli_num_rows($res_dokumen) > 0): ?>
                                        <?php while ($d = mysqli_fetch_assoc($res_dokumen)): ?>
                                            <tr>
                                                <td><i class="bi bi-filetype-csv text-success me-1"></i> <?php echo $d['judul']; ?></td>
                                                <td><small class="text-muted"><?php echo round($d['ukuran_file'] / 1024, 1); ?> KB</small></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted p-3">Belum ada dokumen tercatat.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>