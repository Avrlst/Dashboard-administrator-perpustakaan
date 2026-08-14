<?php

session_start();
require_once 'config/db.php';


if (!isset($_SESSION['login'])) {
    header('Location : login.php');
    exit;
}
$pesan = "";

if (isset($_POST['tambah_buku'])) {
    $isbn = mysqli_real_escape_string($connect,trim($_POST['isbn']));
    $judul = mysqli_real_escape_string($connect,trim($_POST['judul']));
    $pengarang = mysqli_real_escape_string($connect,trim($_POST['pengarang']));
    $penerbit = mysqli_real_escape_string($connect,trim($_POST['penerbit']));
    $tahun_terbit = mysqli_real_escape_string($connect,trim($_POST['tahun_terbit']));
    $stok =(int)$_POST['stok'];

    $cek_isbn = mysqli_query($connect,"SELECT isbn FROM buku WHERE isbn = '$isbn'");
    if (mysqli_num_rows($cek_isbn) > 0)
 {
    $pesan = "<div class='alert alert-danger'>Gagal! Buku dengan ISBN <strong>$isbn</strong> sudah terdaftar.</div>";
 }else{
    $query_insert = "INSERT INTO buku (isbn,judul,pengarang,penerbit,tahun_terbit,stok)
                    VALUES ('$isbn','$judul','$pengarang','$penerbit','$tahun_terbit','$stok')";
    if (mysqli_query($connect,$query_insert)) {
        $pesan = "<div class='alert alert-success'>Berhasil menambahkan buku baru!</div>";
    }else{
        $pesan = "<div class='alert alert-danger'>Gagal menyimpan data buku.</div>";
    }
 }
 }

 if(isset($_GET['hapus'])) {
    $isbn_hapus = mysqli_real_escape_string($connect,$_GET['hapus']);

    if($_SESSION['role'] === 'administrator') {
        $query_delete = "DELETE FROM buku WHERE isbn = '$isbn_hapus'";
        if(mysqli_query($connect,$query_delete)) {
            $pesan = "<div class='alert alert-warning'>Data buku berhasil dihapus.</div>";
        }else {
        $pesan = "<div class='alert alert-danger'>Akses ditolak! Hanya Administrator yang boleh menghapus data.</div>";
        }
    }
 }

 $result_buku = mysqli_query($connect, "SELECT * FROM buku ORDER BY judul ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Buku - Sistem Perpustakaan BSI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white shadow p-4 rounded">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pengelolaan Data Buku</h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali ke Dashboard</a>
    </div>

    <?= $pesan; ?>

   
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Tambah Data Buku Baru</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">ISBN</label>
                    <input type="text" name="isbn" class="form-control" placeholder="978-xxx-xxx" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Judul Buku</label>
                    <input type="text" name="judul" class="form-control" placeholder="Judul Buku" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pengarang</label>
                    <input type="text" name="pengarang" class="form-control" placeholder="Nama Pengarang">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Penerbit</label>
                    <input type="text" name="penerbit" class="form-control" placeholder="Nama Penerbit">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" class="form-control" placeholder="2024" min="1900" max="2099">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-control" value="1" min="0" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" name="tambah_buku" class="btn btn-success">Simpan Data Buku</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header bg-dark text-white">Daftar Stok Buku Perpustakaan</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ISBN</th>
                            <th>Judul Buku</th>
                            <th>Pengarang</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_buku) > 0): ?>
                            <?php while ($buku = mysqli_fetch_assoc($result_buku)): ?>
                                <tr>
                                    <td><code><?= $buku['isbn']; ?></code></td>
                                    <td><strong><?= $buku['judul']; ?></strong></td>
                                    <td><?= $buku['pengarang'] ? $buku['pengarang'] : '-'; ?></td>
                                    <td><?= $buku['penerbit'] ? $buku['penerbit'] : '-'; ?></td>
                                    <td><?= $buku['tahun_terbit'] ? $buku['tahun_terbit'] : '-'; ?></td>
                                    <td>
                                        <span class="badge <?= $buku['stok'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?= $buku['stok']; ?> unit
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['role'] === 'Administrator'): ?>
                                            <a href="buku.php?hapus=<?= $buku['isbn']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin ingin menghapus buku ini?')">Hapus</a>
                                        <?php else: ?>
                                            <span class="text-muted small">No Access</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data buku tersimpan.</td>
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
?>