<?php

session_start();

if (isset($_SESSION['login'])) {
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Perpustakaan Kampus</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container login-container">
    <div class="row w-100 justify-content-center">
        <div class="col-md-5 col-lg-4">
            
            <div class="card login-card p-4">
                <div class="card-body">
                    <h3 class="card-title text-center mb-2 fw-bold text-primary">SIPUS KAMPUS</h3>
                    <p class="text-muted text-center mb-4">Sertifikasi Kompetensi DBA</p>
                    
                    <!-- Notifikasi Error jika Algoritma auth.php mendeteksi kegagalan -->
                    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
                        <div class="alert alert-danger text-center py-2 fs-6" role="alert">
                            Username atau Password salah!
                        </div>
                    <?php endif; ?>

                    
                    <form action="auth.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label text-secondary">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label text-secondary">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-primary w-100 py-2 mt-2 fw-semibold">
                            Masuk ke Sistem
                        </button>
                    </form>

                </div>
            </div>
            
            <p class="text-center text-muted mt-3 fs-7">&copy; 2026 Perpustakaan Universitas</p>
        </div>
    </div>
</div>

</body>
</html>