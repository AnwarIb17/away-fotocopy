<?php
// Memastikan session dimulai sebelum ada output HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Halaman: Jika belum login, tendang kembali ke halaman login.php
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Away Fotocopy & ATK Internal POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fa;
            color: #334155;
        }
        .navbar {
            background-color: #0f172a !important;
        }
        .nav-link {
            font-weight: 500;
            color: #94a3b8 !important;
            transition: all 0.2s ease;
            border-radius: 8px;
            padding: 8px 16px !important;
        }
        .nav-link:hover {
            color: #f1f5f9 !important;
            background-color: #1e293b;
        }
        .nav-link.active {
            color: #38bdf8 !important;
            background-color: #1e293b;
            font-weight: 600;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm py-2">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold text-info fs-4 d-flex align-items-center" href="index.php">
            <img src="assets/img/logo.png" alt="Logo" width="35" height="35" class="d-inline-block align-top me-2 rounded-2" style="object-fit: cover;">
            Away Fotocopy
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-1 text-center mt-2 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-grid-1x2-fill me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'kasir.php') ? 'active' : ''; ?>" href="kasir.php">
                        <i class="bi bi-calculator-fill me-1"></i> Layar Kasir
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'produk-atk.php') ? 'active' : ''; ?>" href="produk-atk.php">
                        <i class="bi bi-box-seam-fill me-1"></i> Stok ATK
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'jasa-fotocopy.php') ? 'active' : ''; ?>" href="jasa-fotocopy.php">
                        <i class="bi bi-sliders me-1"></i> Tarif Jasa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'pengeluaran.php') ? 'active' : ''; ?>" href="pengeluaran.php">
                        <i class="bi bi-wallet2 me-1"></i> Pengeluaran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'active' : ''; ?>" href="laporan.php">
                        <i class="bi bi-file-earmark-bar-graph-fill me-1"></i> Laporan
                    </a>
                </li>
                <!-- Tombol Keluar / Logout Sistem -->
                <li class="nav-item">
                    <a class="nav-link text-danger fw-bold" href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem kasir?')">
                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="py-4">