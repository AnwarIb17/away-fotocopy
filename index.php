<?php 
include 'config/koneksi.php';
include 'includes/header.php';

// Ambil tanggal hari ini
$hari_ini = date('Y-m-d');

// 1. STATISTIK UTAMA (SEPANJANG MASA)
$q_atk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_atk");
$d_atk = mysqli_fetch_assoc($q_atk);

$q_jasa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_jasa_fotocopy");
$d_jasa = mysqli_fetch_assoc($q_jasa);

$q_omzet = mysqli_query($koneksi, "SELECT SUM(total_bayar) as total FROM tb_transaksi");
$d_omzet = mysqli_fetch_assoc($q_omzet);
$total_omzet = $d_omzet['total'] ?? 0;

// 2. PERFORMA HARI INI
$q_hari_ini = mysqli_query($koneksi, "SELECT COUNT(*) as nota, SUM(total_bayar) as omzet FROM tb_transaksi WHERE DATE(tanggal_waktu) = '$hari_ini'");
$d_hari_ini = mysqli_fetch_assoc($q_hari_ini);
$nota_hari_ini = $d_hari_ini['nota'] ?? 0;
$omzet_hari_ini = $d_hari_ini['omzet'] ?? 0;

// 3. DATA UNTUK GRAFIK PERBANDINGAN ITEM (ATK VS JASA)
$q_chart_atk = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM tb_detail_transaksi WHERE jenis_item = 'atk'");
$d_chart_atk = mysqli_fetch_assoc($q_chart_atk);
$total_terjual_atk = $d_chart_atk['total'] ?? 0;

$q_chart_jasa = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM tb_detail_transaksi WHERE jenis_item = 'fotocopy'");
$d_chart_jasa = mysqli_fetch_assoc($q_chart_jasa);
$total_terjual_jasa = $d_chart_jasa['total'] ?? 0;
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --dark-blue: #0f172a;
        --indigo-gradient: linear-gradient(135deg, #6366f1, #4f46e5);
        --emerald-gradient: linear-gradient(135deg, #10b981, #059669);
        --amber-gradient: linear-gradient(135deg, #f59e0b, #d97706);
        --rose-gradient: linear-gradient(135deg, #f43f5e, #e11d48);
    }
    .card-dashboard {
        border: none;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card-dashboard:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
    }
    .text-gradient-purple {
        background: linear-gradient(135deg, #4f46e5, #0ea5e9);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .quick-link-btn {
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
    }
    .quick-link-btn:hover {
        background-color: #f8fafc !important;
        border-color: #cbd5e1;
        transform: translateX(4px);
    }
</style>

<div class="container-fluid px-4 py-2">
    <div class="card card-dashboard text-white mb-4 shadow-sm" style="background: linear-gradient(135deg, #1e293b, #0f172a); border-left: 6px solid #38bdf8;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-info text-dark mb-2 fw-bold px-3 py-1.5 rounded-pill text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Live System</span>
                    <h1 class="fw-bold mb-1" style="letter-spacing: -0.5px;">Dashboard Utama POS</h1>
                    <p class="text-muted small mb-0">Kelola transaksi, pantau ketersediaan stok ATK, dan rincian omzet Away Fotocopy secara terintegrasi.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="kasir.php" class="btn btn-info fw-bold text-dark px-4 py-2.5 rounded-3 shadow-sm">
                        <i class="bi bi-calculator-fill me-2"></i>Mulai Transaksi Kasir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card card-dashboard p-3 bg-white shadow-sm border-start border-emerald border-3" style="border-left: 4px solid #10b981 !important;">
                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Omzet Hari Ini</span>
                <h4 class="fw-bold text-success mt-1 mb-0">Rp <?= number_format($omzet_hari_ini, 0, ',', '.'); ?></h4>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-dashboard p-3 bg-white shadow-sm" style="border-left: 4px solid #3b82f6 !important;">
                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Nota Hari Ini</span>
                <h4 class="fw-bold text-primary mt-1 mb-0"><?= $nota_hari_ini; ?> Transaksi</h4>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-dashboard p-3 bg-white shadow-sm" style="border-left: 4px solid #f59e0b !important;">
                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Total Produk ATK</span>
                <h4 class="fw-bold text-dark mt-1 mb-0"><?= $d_atk['total']; ?> Item</h4>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-dashboard p-3 bg-white shadow-sm" style="border-left: 4px solid #a855f7 !important;">
                <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Total Ragam Jasa</span>
                <h4 class="fw-bold text-dark mt-1 mb-0"><?= $d_jasa['total']; ?> Layanan</h4>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-dashboard text-white shadow-sm p-4" style="background: var(--emerald-gradient);">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="small text-white-50 text-uppercase fw-bold tracking-wider" style="font-size: 0.75rem;">Total Pendapatan Akumulatif (Gross Omzet)</span>
                        <h2 class="fw-bold mt-2 mb-0" style="font-size: 2.5rem; letter-spacing: -1px;">Rp <?= number_format($total_omzet, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-4 p-3 d-none d-sm-block">
                        <i class="bi bi-wallet2 fs-1 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card card-dashboard shadow-sm bg-white p-4 h-100">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart-fill text-indigo me-2"></i>Porsi Penjualan</h5>
                <div style="position: relative; height: 220px;">
                    <?php if($total_terjual_atk > 0 || $total_terjual_jasa > 0): ?>
                        <canvas id="chartPorsi"></canvas>
                    <?php else: ?>
                        <div class="text-center text-muted py-5 small"><i class="bi bi-pie-chart d-block fs-2 mb-2"></i>Belum ada data penjualan tercatat.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-dashboard shadow-sm bg-white p-4 h-100">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Akses Pintas Menu Navigasi</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="produk-atk.php" class="btn btn-light quick-link-btn text-start p-3 rounded-3 d-flex align-items-center justify-content-between bg-white w-100 h-100">
                            <div>
                                <strong class="text-dark d-block">Gudang & Stok ATK</strong>
                                <span class="text-muted small">Kelola ketersediaan barang jualan</span>
                            </div>
                            <i class="bi bi-arrow-right-short text-muted fs-4"></i>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="jasa-fotocopy.php" class="btn btn-light quick-link-btn text-start p-3 rounded-3 d-flex align-items-center justify-content-between bg-white w-100 h-100">
                            <div>
                                <strong class="text-dark d-block">Tarif Layanan Jasa</strong>
                                <span class="text-muted small">Atur harga print, copy, & finishing</span>
                            </div>
                            <i class="bi bi-arrow-right-short text-muted fs-4"></i>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="laporan.php" class="btn btn-light quick-link-btn text-start p-3 rounded-3 d-flex align-items-center justify-content-between bg-white w-100 h-100">
                            <div>
                                <strong class="text-dark d-block">Laporan Keuangan</strong>
                                <span class="text-muted small">Pantau grafik tren omzet harian</span>
                            </div>
                            <i class="bi bi-arrow-right-short text-muted fs-4"></i>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="kasir.php" class="btn btn-light quick-link-btn text-start p-3 rounded-3 d-flex align-items-center justify-content-between bg-white w-100 h-100">
                            <div>
                                <strong class="text-dark d-block">Layar Utama Kasir</strong>
                                <span class="text-muted small">Input item belanjaan pembeli</span>
                            </div>
                            <i class="bi bi-arrow-right-short text-muted fs-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-dashboard shadow-sm bg-white overflow-hidden mb-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Kontrol Restock: Peringatan Stok Menipis</h5>
            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1.5 small fw-bold">Ambang Batas &le; 5 Pcs</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4 py-2.5">Nama Barang ATK</th>
                        <th class="py-2.5">Kode Barcode</th>
                        <th class="py-2.5 text-center" style="width: 140px;">Sisa Stok Fisik</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $q_kritis = mysqli_query($koneksi, "SELECT * FROM tb_atk WHERE stok <= 5 ORDER BY stok ASC");
                    if (mysqli_num_rows($q_kritis) > 0) {
                        while ($k = mysqli_fetch_assoc($q_kritis)) {
                    ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($k['nama_barang']); ?></td>
                                <td class="text-muted small fw-mono"><?= !empty($k['barcode']) ? htmlspecialchars($k['barcode']) : '-'; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-danger text-white rounded-3 py-1.5 px-3 fw-bold d-block">
                                        <?= $k['stok']; ?> Pcs
                                    </span>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center text-muted py-5 small'><i class='bi bi-check-circle-fill text-success d-block fs-2 mb-2'></i>Kondisi Stok Aman! Semua inventaris ATK terisi dengan baik di atas 5 pcs.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('chartPorsi');
    if (ctx) {
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Produk ATK', 'Layanan Jasa'],
                datasets: [{
                    data: [<?= $total_terjual_atk; ?>, <?= $total_terjual_jasa; ?>],
                    backgroundColor: ['#6366f1', '#a855f7'],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 12 } }
                    }
                },
                cutout: '70%' // Membuat grafik donat menjadi lebih tipis dan elegan
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>