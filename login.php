<?php
session_start();
include 'config/koneksi.php';

// Jika sudah login, langsung dialihkan ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if (isset($_POST['submit_login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']); // Enkripsi MD5 sesuai dengan database

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);
        
        // Buat session login
        $_SESSION['login'] = true;
        $_SESSION['username'] = $data['username'];
        $_SESSION['nama'] = $data['nama_lengkap'];

        header("Location: index.php");
        exit;
    } else {
        $error = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Away Fotocopy POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; width: 100%; max-width: 400px; padding: 30px; color: #f1f5f9; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <img src="assets/img/logo.png" alt="Logo" width="60" height="60" class="mb-2 rounded-3" onerror="this.src='https://cdn-icons-png.flaticon.com/512/9375/9375173.png'">
        <h4 class="fw-bold text-info m-0">Away Fotocopy</h4>
        <small class="text-muted">Internal Point of Sales System</small>
    </div>

    <?php if ($error) : ?>
        <div class="alert alert-danger py-2 border-0 text-center" style="font-size: 14px; background-color: #ef4444; color: #fff;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label small fw-semibold">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-secondary border-0 text-white"><i class="bi bi-person-fill"></i></span>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autocomplete="off">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-secondary border-0 text-white"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
        </div>
        <button type="submit" name="submit_login" class="btn btn-info w-100 fw-bold text-dark py-2 rounded-2">
            Masuk ke Sistem <i class="bi bi-box-arrow-in-right ms-1"></i>
        </button>
    </form>
</div>

</body>
</html>