<?php 
// 1. Hubungkan ke database dan komponen layout
include 'config/koneksi.php';
include 'includes/header.php';

// 2. QUERY HITUNG PENDAPATAN HARI INI
$query_pendapatan = mysqli_query($koneksi, "SELECT SUM(total_bayar) AS total FROM tb_transaksi WHERE DATE(tanggal_waktu) = CURDATE()");
$data_pendapatan = mysqli_fetch_assoc($query_pendapatan);
$pendapatan_hari_ini = $data_pendapatan['total'] ?? 0;

// 3. QUERY HITUNG JUMLAH TRANSAKSI HARI INI
$query_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) AS total_trx FROM tb_transaksi WHERE DATE(tanggal_waktu) = CURDATE()");
$data_transaksi = mysqli_fetch_assoc($query_transaksi);
$trx_hari_ini = $data_transaksi['total_trx'] ?? 0;

// 4. QUERY HITUNG ATK YANG STOKNYA MAU HABIS
$query_stok_tipis = mysqli_query($koneksi, "SELECT COUNT(*) AS total_tipis FROM tb_atk WHERE stok <= 5");
$data_stok_tipis = mysqli_fetch_assoc($query_stok_tipis);
$stok_menipis_count = $data_stok_tipis['total_tipis'] ?? 0;

// 5. QUERY DATA GRAFIK (7 Hari Terakhir)
$hari = [];
$omzet = [];
for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $label_hari = date('D, d M', strtotime($tgl));
    
    $q_grafik = mysqli_query($koneksi, "SELECT SUM(total_bayar) AS total FROM tb_transaksi WHERE DATE(tanggal_waktu) = '$tgl'");
    $d_grafik = mysqli_fetch_assoc($q_grafik);
    
    $hari[] = $label_hari;
    $omzet[] = $d_grafik['total'] ?? 0;
}
?>

<!-- Tambahan Google Fonts, Chart.js & Custom CSS -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f4f7fa;
    }
    .custom-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .custom-card:hover {
        transform: translateY(-4px);
    }
    .icon-shape {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-quick {
        border-radius: 12px;
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #334155;
    }
    .btn-quick:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateX(4px);
        color: #0f172a;
    }
    .table-container {
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }
</style>

<div class="container px-4 py-2">
    <!-- Judul & Waktu -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h3 mb-1 text-slate-800 fw-bold" style="color: #1e293b;">Ringkasan Toko</h1>
            <p class="text-muted small mb-0">Pantau performa harian Away Fotocopy</p>
        </div>
        <div class="text-muted small fw-medium mt-2 mt-sm-0 bg-white px-3 py-2 rounded-3 border shadow-sm">
            <i class="bi bi-calendar3 text-primary me-2"></i><?= date('d F Y'); ?>
        </div>
    </div>

    <!-- ROW 1: RANGKUMAN UTAMA (MODERN CARDS) -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card custom-card text-white h-100 shadow-sm" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small text-uppercase fw-semibold tracking-wider">Pendapatan Hari Ini</span>
                        <h2 class="fw-bold mb-0 mt-1" style="font-size: 1.85rem;">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="icon-shape"><i class="bi bi-wallet2 fs-4"></i></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card custom-card text-white h-100 shadow-sm" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small text-uppercase fw-semibold tracking-wider">Transaksi Sukses</span>
                        <h2 class="fw-bold mb-0 mt-1" style="font-size: 1.85rem;"><?= $trx_hari_ini; ?> <span class="fs-5 fw-normal">Nota</span></h2>
                    </div>
                    <div class="icon-shape"><i class="bi bi-receipt fs-4"></i></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card custom-card text-white h-100 shadow-sm" style="background: linear-gradient(135deg, <?= $stok_menipis_count > 0 ? '#ef4444, #b91c1c' : '#64748b, #475569'; ?>);">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small text-uppercase fw-semibold tracking-wider">Stok ATK Menipis</span>
                        <h2 class="fw-bold mb-0 mt-1" style="font-size: 1.85rem;"><?= $stok_menipis_count; ?> <span class="fs-5 fw-normal">Produk</span></h2>
                    </div>
                    <div class="icon-shape"><i class="bi bi-exclamation-triangle fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 2: LINE CHART (GRAFIK TREN PENJUALAN) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card custom-card shadow-sm p-4 bg-white">
                <h5 class="fw-bold mb-3" style="color: #1e293b;"><i class="bi bi-graph-up text-primary me-2"></i>Tren Omzet 7 Hari Terakhir</h5>
                <div style="height: 280px; position: relative;">
                    <canvas id="omzetChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 3: DETAIL TABEL MONITOR & NAVIGASI CEPAT -->
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card custom-card table-container shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <span class="fw-bold text-danger d-flex align-items-center m-0">
                        <i class="bi bi-bell-fill me-2"></i> Peringatan Stok (&le; 5)
                    </span>
                    <span class="badge bg-danger-subtle text-danger px-2.5 py-1 rounded-pill small fw-semibold">Butuh Belanja</span>
                </div>
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead style="background-color: #f8fafc; border-bottom: 2px solid #edf2f7;">
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase py-3">Nama Barang ATK</th>
                                    <th class="text-muted small text-uppercase py-3">Sisa Stok</th>
                                    <th class="text-center text-muted small text-uppercase py-3" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ambil_stok_tipis = mysqli_query($koneksi, "SELECT * FROM tb_atk WHERE stok <= 5 ORDER BY stok ASC");
                                if (mysqli_num_rows($ambil_stok_tipis) > 0) {
                                    while ($atk = mysqli_fetch_assoc($ambil_stok_tipis)) {
                                        echo "<tr style='border-bottom: 1px solid #f1f5f9;'>
                                                <td class='ps-4 fw-medium text-dark'>" . htmlspecialchars($atk['nama_barang']) . "</td>
                                                <td><span class='badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold'>" . $atk['stok'] . " pcs</span></td>
                                                <td class='text-center pe-3'>
                                                    <a href='produk-atk.php' class='btn btn-sm btn-primary w-100 rounded-3 fw-medium py-1.5 shadow-sm'>
                                                        <i class='bi bi-plus-circle me-1'></i> Pasok
                                                    </a>
                                                </td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center text-muted py-5'><i class='bi bi-check-circle-fill text-success fs-3 d-block mb-2'></i> Semua stok ATK di toko aman!</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card custom-card shadow-sm p-4 bg-white">
                <h5 class="fw-bold mb-3" style="color: #1e293b;"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Navigasi Pintas</h5>
                <div class="d-grid gap-2.5">
                    <a href="kasir.php" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm border-0 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 14px;">
                        <i class="bi bi-calculator-fill me-2"></i> BUKA LAYAR KASIR
                    </a>
                    <a href="produk-atk.php" class="btn btn-quick py-3 text-start px-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-box-seam text-primary me-2"></i> Kelola Stok & Barang ATK</span>
                        <i class="bi bi-chevron-right small text-muted"></i>
                    </a>
                    <a href="jasa-fotocopy.php" class="btn btn-quick py-3 text-start px-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-sliders text-success me-2"></i> Atur Tarif Jasa Fotokopi</span>
                        <i class="bi bi-chevron-right small text-muted"></i>
                    </a>
                    <a href="laporan.php" class="btn btn-quick py-3 text-start px-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-bar-chart-line text-info me-2"></i> Cek Laporan Keuangan</span>
                        <i class="bi bi-chevron-right small text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT CONFIGURATION FOR CHART.JS -->
<script>
    const ctx = document.getElementById('omzetChart').getContext('2d');
    
    // Membuat gradien warna biru transparan di bawah garis grafik
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($hari); ?>,
            datasets: [{
                label: 'Omzet Penjualan (Rp)',
                data: <?= json_encode($omzet); ?>,
                borderColor: '#3b82f6',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                backgroundColor: gradient,
                tension: 0.35 // Membuat garis melengkung smooth halus
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false } // Sembunyikan label kotak atas agar bersih
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        },
                        color: '#64748b',
                        font: { family: 'Plus Jakarta Sans' }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: '#64748b',
                        font: { family: 'Plus Jakarta Sans' }
                    }
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>