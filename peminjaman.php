<?php
// peminjaman.php
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

$pesan = "";

// -------------------------------------------------------------------------
// PROSES 1: CATAT PEMINJAMAN BARU
// -------------------------------------------------------------------------
if (isset($_POST['pinjam_buku'])) {
    $nim         = mysqli_real_escape_string($connect, $_POST['nim']);
    $isbn        = mysqli_real_escape_string($connect, $_POST['isbn']);
    $tgl_pinjam  = mysqli_real_escape_string($connect, $_POST['tgl_pinjam']);
    $tgl_kembali = mysqli_real_escape_string($connect, $_POST['tgl_kembali']);

    // Cek Stok Buku Terlebih Dahulu
    $q_stok = mysqli_query($connect, "SELECT stok, judul FROM buku WHERE isbn = '$isbn'");
    $buku   = mysqli_fetch_assoc($q_stok);

    if (!$buku || $buku['stok'] <= 0) {
        $pesan = "<div class='alert alert-danger'>Gagal! Stok buku '<strong>" . ($buku['judul'] ?? $isbn) . "</strong>' sedang habis/kosong.</div>";
    } else {
        // Trik PHP: Cari ID terbesar saat ini lalu tambahkan 1
        $q_max_id = mysqli_query($connect, "SELECT MAX(id_pinjam) AS max_id FROM peminjaman");
        $row_max  = mysqli_fetch_assoc($q_max_id);
        $id_pinjam_baru = ($row_max['max_id']) ? $row_max['max_id'] + 1 : 1;

        // 1. Insert ke Tabel peminjaman dengan memasukkan $id_pinjam_baru
        $query_header = "INSERT INTO peminjaman (id_pinjam, nim, tgl_pinjam, tgl_kembali_seharusnya) 
                        VALUES ($id_pinjam_baru, '$nim', '$tgl_pinjam', '$tgl_kembali')";
        
        if (mysqli_query($connect, $query_header)) {

            // 2. Insert ke Tabel detail_peminjaman
            $query_detail = "INSERT INTO detail_peminjaman (id_pinjam, isbn) 
                            VALUES ($id_pinjam_baru, '$isbn')";
            mysqli_query($connect, $query_detail);

            // 3. Kurangi Stok Buku (-1)
            mysqli_query($connect, "UPDATE buku SET stok = stok - 1 WHERE isbn = '$isbn'");

            $pesan = "<div class='alert alert-success'>Transaksi peminjaman berhasil dicatat! Stok buku otomatis berkurang.</div>";
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal mencatat transaksi: " . mysqli_error($connect) . "</div>";
        }
    }
}  

// -------------------------------------------------------------------------
// PROSES 2: PROSES PENGEMBALIAN BUKU (DENGAN HITUNG DENDA OTOMATIS)
// -------------------------------------------------------------------------
if (isset($_GET['kembali'])) {
    $id_pinjam = (int)$_GET['kembali'];

    // Ambil data ISBN dan Tanggal Tenggat dari database
    $q_detail = mysqli_query($connect, "SELECT dp.isbn, p.tgl_kembali_seharusnya 
                                        FROM detail_peminjaman dp 
                                        JOIN peminjaman p ON dp.id_pinjam = p.id_pinjam 
                                        WHERE dp.id_pinjam = $id_pinjam");
    $data_p   = mysqli_fetch_assoc($q_detail);

    if ($data_p) {
        $isbn_kembali = $data_p['isbn'];
        $tgl_tenggat  = $data_p['tgl_kembali_seharusnya'];
        $tgl_sekarang = date('Y-m-d'); // Tanggal pengembalian hari ini

        // --- HITUNG DENDA OTOMATIS ---
        $tarif_denda_per_hari = 1000; // Tarif denda Rp 1.000 / hari
        $denda = 0;
        $keterlambatan = 0;

        if (strtotime($tgl_sekarang) > strtotime($tgl_tenggat)) {
            $selisih_detik = strtotime($tgl_sekarang) - strtotime($tgl_tenggat);
            $keterlambatan = floor($selisih_detik / (60 * 60 * 24)); // Konversi detik ke hari
            $denda         = $keterlambatan * $tarif_denda_per_hari;
        }

        // 1. Hapus dari detail_peminjaman & peminjaman
        mysqli_query($connect, "DELETE FROM detail_peminjaman WHERE id_pinjam = $id_pinjam");
        $query_kembali = "DELETE FROM peminjaman WHERE id_pinjam = $id_pinjam";
        
        if (mysqli_query($connect, $query_kembali)) {
            // 2. Kembalikan Stok Buku (+1)
            mysqli_query($connect, "UPDATE buku SET stok = stok + 1 WHERE isbn = '$isbn_kembali'");

            // 3. Tampilkan Pesan Informasi Denda
            if ($denda > 0) {
                $pesan = "<div class='alert alert-warning'>
                            ⚠️ <strong>Buku Berhasil Dikembalikan!</strong><br>
                            Peminjam terlambat <strong>$keterlambatan hari</strong>.<br>
                            Total Denda yang harus dibayar: <strong>Rp " . number_format($denda, 0, ',', '.') . "</strong> (Rp 1.000 / hari).
                          </div>";
            } else {
                $pesan = "<div class='alert alert-info'>✅ Buku telah dikembalikan tepat waktu! Tidak ada denda. Stok buku bertambah (+1).</div>";
            }
        }
    }
}


$res_anggota = mysqli_query($connect, "SELECT nim, nama FROM anggota ORDER BY nama ASC");
$res_buku    = mysqli_query($connect, "SELECT isbn, judul, stok FROM buku WHERE stok > 0 ORDER BY judul ASC");


$query_tabel = "SELECT p.id_pinjam, p.nim, p.tgl_pinjam, p.tgl_kembali_seharusnya, a.nama AS nama_anggota, b.judul AS judul_buku, dp.isbn 
                FROM peminjaman p
                JOIN anggota a ON p.nim = a.nim
                LEFT JOIN detail_peminjaman dp ON p.id_pinjam = dp.id_pinjam
                LEFT JOIN buku b ON dp.isbn = b.isbn
                ORDER BY p.id_pinjam DESC";
$res_transaksi = mysqli_query($connect, $query_tabel);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peminjaman & Pengembalian - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white shadow p-4 rounded">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Poin T2: Sirkulasi Peminjaman & Pengembalian</h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali ke Dashboard</a>
    </div>

    <?php if(!empty($pesan)) echo $pesan; ?>

    
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">Catat Peminjaman Buku Baru</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Pilih Anggota (Peminjam)</label>
                    <select name="nim" class="form-select" required>
                        <option value="">-- Pilih Anggota --</option>
                        <?php while ($a = mysqli_fetch_assoc($res_anggota)): ?>
                            <option value="<?php echo $a['nim']; ?>">
                                <?php echo $a['nim'] . " - " . $a['nama']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Pilih Buku</label>
                    <select name="isbn" class="form-select" required>
                        <option value="">-- Pilih Buku Tersedia --</option>
                        <?php while ($b = mysqli_fetch_assoc($res_buku)): ?>
                            <option value="<?php echo $b['isbn']; ?>">
                                <?php echo $b['judul'] . " (Stok: " . $b['stok'] . ")"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Pinjam</label>
                    <input type="date" name="tgl_pinjam" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Tenggat Kembali</label>
                    <input type="date" name="tgl_kembali" class="form-control" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" name="pinjam_buku" class="btn btn-success px-4">Proses Peminjaman</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Histori Transaksi Aktif -->
    <div class="card">
        <div class="card-header bg-dark text-white">Daftar Transaksi Peminjaman Aktif</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#ID Pinjam</th>
                            <th>Peminjam</th>
                            <th>Judul Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tenggat Kembali</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_transaksi && mysqli_num_rows($res_transaksi) > 0): ?>
                            <?php while ($t = mysqli_fetch_assoc($res_transaksi)): ?>
                                <tr>
                                    <td>#<?php echo $t['id_pinjam']; ?></td>
                                    <td><strong><?php echo $t['nama_anggota']; ?></strong><br><small class="text-muted"><?php echo $t['nim']; ?></small></td>
                                    <td><?php echo $t['judul_buku'] ? $t['judul_buku'] : '-'; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($t['tgl_pinjam'])); ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($t['tgl_kembali_seharusnya'])); ?></td>
                                    <td>
                                        <a href="peminjaman.php?kembali=<?php echo $t['id_pinjam']; ?>" 
                                           class="btn btn-sm btn-outline-success" 
                                           onclick="return confirm('Proses pengembalian buku ini?')">Kembalikan Buku</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada peminjaman aktif.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</body>
</html>