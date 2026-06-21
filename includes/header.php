<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Away Fotocopy & ATK</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        /* Style tambahan agar menu aktif terlihat jelas */
        .nav-link.active {
            font-weight: bold;
            border-bottom: 2px solid #fff;
        }
        @media (max-width: 768px) {
            .nav-link.active {
                border-bottom: none;
                border-left: 3px solid #fff;
                padding-left: 10px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar Utama -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-info" href="index.php">
            <i class="bi bi-printer-fill"></i> Away Fotocopy
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-2 text-center">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'kasir.php') ? 'active' : ''; ?>" href="kasir.php">
                        <i class="bi bi-calculator"></i> Layar Kasir
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'produk-atk.php') ? 'active' : ''; ?>" href="produk-atk.php">
                        <i class="bi bi-box-seam"></i> Stok ATK
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'jasa-fotocopy.php') ? 'active' : ''; ?>" href="jasa-fotocopy.php">
                        <i class="bi bi-gear"></i> Tarif Jasa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'active' : ''; ?>" href="laporan.php">
                        <i class="bi bi-file-earmark-bar-graph"></i> Laporan
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Wadah Konten Utama -->
<main class="py-4">