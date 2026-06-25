<?php 
include 'config/koneksi.php';
include 'includes/header.php';

// Set range tanggal (Default: 7 hari terakhir)
$tanggal_awal  = isset($_GET['tanggal_awal']) ? mysqli_real_escape_string($koneksi, $_GET['tanggal_awal']) : date('Y-m-d', strtotime('-6 days'));
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? mysqli_real_escape_string($koneksi, $_GET['tanggal_akhir']) : date('Y-m-d');

// 1. HITUNG RINGKASAN STATISTIK UTAMA (OMZET & VOLUME NOTA)
$query_stat = mysqli_query($koneksi, "SELECT 
                COUNT(id_transaksi) as total_nota, 
                SUM(total_bayar) as total_omzet 
                FROM tb_transaksi 
                WHERE DATE(tanggal_waktu) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'");
$stat = mysqli_fetch_assoc($query_stat);

$total_nota  = $stat['total_nota'] ?? 0;
$total_omzet = $stat['total_omzet'] ?? 0;

// 2. HITUNG TOTAL PENGELUARAN TOKO PADA PERIODE INI (FITUR BARU)
$query_pengeluaran = mysqli_query($koneksi, "SELECT SUM(nominal) as total FROM tb_pengeluaran WHERE tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'");
$data_pengeluaran = mysqli_fetch_assoc($query_pengeluaran);
$total_operasional = $data_pengeluaran['total'] ?? 0;

// 3. LOGIKA MATEMATIS / QUERY UNTUK MENGHITUNG TOTAL LABA KOTOR PRODUK (Selesih Jual - Modal HPP)
$total_laba_produk = 0;
$query_hitung_laba = mysqli_query($koneksi, "SELECT 
                        dt.jenis_item, dt.id_item, dt.jumlah, dt.subtotal,
                        atk.harga_modal AS modal_atk,
                        jasa.harga_modal AS modal_jasa
                        FROM tb_detail_transaksi dt
                        INNER JOIN tb_transaksi t ON dt.id_transaksi = t.id_transaksi
                        LEFT JOIN tb_atk atk ON dt.jenis_item = 'atk' AND dt.id_item = atk.id_atk
                        LEFT JOIN tb_jasa_fotocopy jasa ON dt.jenis_item = 'fotocopy' AND dt.id_item = jasa.id_jasa
                        WHERE DATE(t.tanggal_waktu) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'");

while ($laba_row = mysqli_fetch_assoc($query_hitung_laba)) {
    $harga_modal_satuan = ($laba_row['jenis_item'] === 'atk') ? $laba_row['modal_atk'] : $laba_row['modal_jasa'];
    $total_modal_item = intval($harga_modal_satuan) * intval($laba_row['jumlah']);
    
    $laba_murni_item = intval($laba_row['subtotal']) - $total_modal_item;
    $total_laba_produk += $laba_murni_item;
}

// LABA BERSIH AKHIR = Laba Kotor Produk - Pengeluaran Operasional Toko
$total_laba_bersih = $total_laba_produk - $total_operasional;


// 4. AMBIL DATA UNTUK GRAFIK KOMPARASI HARIANS
$label_grafik      = [];
$data_grafik_omzet = [];
$data_grafik_laba  = [];

// Buat array tanggal untuk memetakan laba harian produk secara akurat
$laba_harian_array = [];
$query_laba_harian = mysqli_query($koneksi, "SELECT 
                        DATE(t.tanggal_waktu) as tgl_hari, dt.jenis_item, dt.jumlah, dt.subtotal,
                        atk.harga_modal AS modal_atk, jasa.harga_modal AS modal_jasa
                        FROM tb_detail_transaksi dt
                        INNER JOIN tb_transaksi t ON dt.id_transaksi = t.id_transaksi
                        LEFT JOIN tb_atk atk ON dt.jenis_item = 'atk' AND dt.id_item = atk.id_atk
                        LEFT JOIN tb_jasa_fotocopy jasa ON dt.jenis_item = 'fotocopy' AND dt.id_item = jasa.id_jasa
                        WHERE DATE(t.tanggal_waktu) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'");

while ($lh = mysqli_fetch_assoc($query_laba_harian)) {
    $tgl_key = $lh['tgl_hari'];
    $h_modal = ($lh['jenis_item'] === 'atk') ? $lh['modal_atk'] : $lh['modal_jasa'];
    $sub_laba = intval($lh['subtotal']) - (intval($h_modal) * intval($lh['jumlah']));
    
    if(!isset($laba_harian_array[$tgl_key])) {
        $laba_harian_array[$tgl_key] = 0;
    }
    $laba_harian_array[$tgl_key] += $sub_laba;
}

// Ambil juga data pengeluaran per hari untuk mengurangi laba harian grafik secara presisi
$pengeluaran_harian_array = [];
$query_pengeluaran_harian = mysqli_query($koneksi, "SELECT tanggal, SUM(nominal) as pengeluaran_hari FROM tb_pengeluaran WHERE tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' GROUP BY tanggal");
while ($ph = mysqli_fetch_assoc($query_pengeluaran_harian)) {
    $pengeluaran_harian_array[$ph['tanggal']] = $ph['pengeluaran_hari'];
}

// Ambil omzet harian dan satukan semuanya
$query_grafik = mysqli_query($koneksi, "SELECT 
                    DATE(tanggal_waktu) as tgl, 
                    SUM(total_bayar) as omzet_harian 
                    FROM tb_transaksi 
                    WHERE DATE(tanggal_waktu) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
                    GROUP BY DATE(tanggal_waktu)
                    ORDER BY DATE(tanggal_waktu) ASC");

while ($g = mysqli_fetch_assoc($query_grafik)) {
    $current_tgl = $g['tgl'];
    $label_grafik[] = date('d M', strtotime($current_tgl));
    $data_grafik_omzet[] = $g['omzet_harian'];
    
    // Laba bersih hari ini = Laba produk hari ini - Pengeluaran hari ini
    $laba_prod_hari_ini = $laba_harian_array[$current_tgl] ?? 0;
    $pengeluaran_hari_ini = $pengeluaran_harian_array[$current_tgl] ?? 0;
    $data_grafik_laba[]  = $laba_prod_hari_ini - $pengeluaran_hari_ini;
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5, #3730a3);
        --success-gradient: linear-gradient(135deg, #10b981, #065f46);
        --info-gradient: linear-gradient(135deg, #0ea5e9, #0369a1);
        --danger-gradient: linear-gradient(135deg, #f43f5e, #be123c);
    }
    .card-custom {
        border: none;
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
    }
    .gradient-primary { background: var(--primary-gradient); }
    .gradient-success { background: var(--success-gradient); }
    .gradient-info { background: var(--info-gradient); }
    .gradient-danger { background: var(--danger-gradient); }
    
    .table th {
        font-weight: 600;
        letter-spacing: 0.5px;
        background-color: #f8fafc !important;
    }
    .form-control, .btn {
        border-radius: 10px !important;
    }
</style>

<div class="container-fluid px-4 py-2">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">Ringkasan Eksekutif Keuangan</h1>
            <p class="text-muted small mb-0"><i class="bi bi-calendar3 me-1"></i> Periode Aktif: <span class="fw-semibold text-dark"><?= date('d M Y', strtotime($tanggal_awal)); ?></span> s/d <span class="fw-semibold text-dark"><?= date('d M Y', strtotime($tanggal_akhir)); ?></span></p>
        </div>
    </div>

    <div class="card card-custom shadow-sm bg-white mb-4">
        <div class="card-body p-4">
            <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Mulai Dari</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="tanggal_awal" class="form-control border-start-0" value="<?= $tanggal_awal; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Sampai Dengan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-calendar-check"></i></span>
                        <input type="date" name="tanggal_akhir" class="form-control border-start-0" value="<?= $tanggal_akhir; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-dark fw-semibold w-100 py-2.5 shadow-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i> Perbarui Data Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card card-custom shadow-sm text-white gradient-info p-3 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="small text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Total Omzet (Bruto)</span>
                        <h2 class="fw-bold mt-2 mb-0" style="font-size: 1.6rem;">Rp <?= number_format($total_omzet, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-4 p-2.5">
                        <i class="bi bi-wallet2 fs-3 text-white"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-white border-opacity-10 small text-white-50">
                    <i class="bi bi-graph-up me-1"></i> Pendapatan kotor kasir
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card card-custom shadow-sm text-white gradient-danger p-3 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="small text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Pengeluaran Operasional</span>
                        <h2 class="fw-bold mt-2 mb-0" style="font-size: 1.6rem;">Rp <?= number_format($total_operasional, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-4 p-2.5">
                        <i class="bi bi-cash-stack fs-3 text-white"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-white border-opacity-10 small text-white-50">
                    <i class="bi bi-arrow-down-circle me-1"></i> Biaya operasional toko
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card card-custom shadow-sm text-white gradient-success p-3 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="small text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Laba Bersih Akhir</span>
                        <h2 class="fw-bold mt-2 mb-0" style="font-size: 1.6rem;">Rp <?= number_format($total_laba_bersih, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-4 p-2.5">
                        <i class="bi bi-cash-coin fs-3 text-white"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-white border-opacity-10 small text-white-50">
                    <i class="bi bi-check-circle-fill me-1"></i> Sudah dipotong HPP & Biaya Toko
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card card-custom shadow-sm text-white gradient-primary p-3 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="small text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Volume Transaksi</span>
                        <h2 class="fw-bold mt-2 mb-0" style="font-size: 1.6rem;"><?= $total_nota; ?> <span style="font-size: 1rem; font-weight: normal;">Nota</span></h2>
                    </div>
                    <div class="bg-white bg-opacity-15 rounded-4 p-2.5">
                        <i class="bi bi-journal-check fs-3 text-white"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-white border-opacity-10 small text-white-50">
                    <i class="bi bi-people-fill me-1"></i> Pelanggan sukses dilayani
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom shadow-sm bg-white mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Tren Perbandingan Omzet vs Laba Bersih</h5>
        </div>
        <div class="card-body p-4 pt-0">
            <?php if(!empty($data_grafik_omzet)): ?>
                <div style="height: 300px; position: relative;">
                    <canvas id="chartOmzet"></canvas>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-5 small"><i class="bi bi-activity d-block fs-1 mb-2"></i>Belum ada visualisasi data untuk grafik pada tanggal ini.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card card-custom shadow-sm overflow-hidden bg-white">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-ul text-secondary me-2"></i>Rincian Log Buku Riwayat Transaksi</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4 py-3 text-secondary small text-uppercase">No. Nota</th>
                        <th class="text-secondary small text-uppercase">Waktu Transaksi</th>
                        <th class="text-secondary small text-uppercase">Total Pembayaran</th>
                        <th class="text-secondary small text-uppercase">Estimasi Laba</th>
                        <th class="text-secondary small text-uppercase">Uang Tunai</th>
                        <th class="text-secondary small text-uppercase">Kembalian</th>
                        <th class="text-center text-secondary small text-uppercase" style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_list = mysqli_query($koneksi, "SELECT * FROM tb_transaksi 
                                    WHERE DATE(tanggal_waktu) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
                                    ORDER BY id_transaksi DESC");
                    
                    if (mysqli_num_rows($query_list) > 0) {
                        while ($row = mysqli_fetch_assoc($query_list)) {
                            $id_trx_row = $row['id_transaksi'];
                            $nota  = isset($row['nomor_nota']) ? $row['nomor_nota'] : ($row['nota_nomor'] ?? '-');
                            $waktu = $row['tanggal_waktu'];

                            $laba_nota_ini = 0;
                            $query_laba_nota = mysqli_query($koneksi, "SELECT dt.jenis_item, dt.jumlah, dt.subtotal,
                                                atk.harga_modal AS modal_atk, jasa.harga_modal AS modal_jasa
                                                FROM tb_detail_transaksi dt
                                                LEFT JOIN tb_atk atk ON dt.jenis_item = 'atk' AND dt.id_item = atk.id_atk
                                                LEFT JOIN tb_jasa_fotocopy jasa ON dt.jenis_item = 'fotocopy' AND dt.id_item = jasa.id_jasa
                                                WHERE dt.id_transaksi = $id_trx_row");
                            
                            while($ln = mysqli_fetch_assoc($query_laba_nota)) {
                                $mod_satuan = ($ln['jenis_item'] === 'atk') ? $ln['modal_atk'] : $ln['modal_jasa'];
                                $laba_nota_ini += (intval($ln['subtotal']) - (intval($mod_satuan) * intval($ln['jumlah'])));
                            }
                    ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="ps-4 fw-bold text-primary">#<?= htmlspecialchars($nota); ?></td>
                                <td class="text-secondary small"><?= date('d M Y - H:i', strtotime($waktu)); ?></td>
                                <td class="fw-bold text-dark">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                <td class="fw-bold text-success">Rp <?= number_format($laba_nota_ini, 0, ',', '.'); ?></td>
                                <td class="text-muted small">Rp <?= number_format($row['nominal_tunai'], 0, ',', '.'); ?></td>
                                <td class="text-muted small">Rp <?= number_format($row['kembalian'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <a href="cetak-nota.php?id=<?= $row['id_transaksi']; ?>" target="_blank" class="btn btn-sm btn-light border fw-semibold rounded-3 text-dark">
                                        <i class="bi bi-printer text-primary me-1"></i> Struk
                                    </a>
                                </td>
                            </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted py-5'>Tidak ada data transaksi terekam pada periode ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('chartOmzet');
    if (ctx) {
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($label_grafik); ?>,
                datasets: [
                    {
                        label: 'Omzet Kotor (Rp)',
                        data: <?php echo json_encode($data_grafik_omzet); ?>,
                        backgroundColor: 'rgba(14, 165, 233, 0.05)',
                        borderColor: '#0ea5e9',
                        borderWidth: 3,
                        pointBackgroundColor: '#0ea5e9',
                        pointRadius: 4,
                        tension: 0.2,
                        fill: true
                    },
                    {
                        label: 'Laba Bersih Akhir (Rp)',
                        data: <?php echo json_encode($data_grafik_laba); ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderColor: '#10b981',
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 4,
                        tension: 0.2,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: true,
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 12, weight: 'bold' } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: function(value) { return 'Rp ' + value.toLocaleString('id-ID'); },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>