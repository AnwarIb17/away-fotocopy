<?php 
include 'config/koneksi.php';
include 'includes/header.php';

// Proses Simpan Pengeluaran Baru
if (isset($_POST['simpan_pengeluaran'])) {
    $tanggal    = $_POST['tanggal'];
    $kategori   = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $nominal    = intval($_POST['nominal']);

    $insert = mysqli_query($koneksi, "INSERT INTO tb_pengeluaran (tanggal, kategori, keterangan, nominal) VALUES ('$tanggal', '$kategori', '$keterangan', '$nominal')");
    
    if ($insert) {
        echo "<script>alert('Pengeluaran berhasil dicatat!'); window.location='pengeluaran.php';</script>";
    } else {
        echo "<script>alert('Gagal mencatat pengeluaran!');</script>";
    }
}

// Proses Hapus Pengeluaran
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM tb_pengeluaran WHERE id = $id_hapus");
    echo "<script>window.location='pengeluaran.php';</script>";
}
?>

<style>
@media (max-width: 768px) {
    /* Sembunyikan header tabel asli di HP */
    .responsive-card-table thead {
        display: none;
    }
    
    /* Hilangkan border & shadow bawaan card besar di HP agar tidak double border */
    .card-dashboard {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    /* Ubah setiap baris data (tr) menjadi box/card terpisah */
    .responsive-card-table tbody tr {
        display: block;
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    
    /* Atur kolom (td) menyusun ke bawah dan rata kanan-kiri */
    .responsive-card-table tbody td {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        text-align: right;
        padding: 8px 0 !important;
        border-bottom: 1px dashed #edf2f7 !important;
    }

    /* Hilangkan padding kiri bawaan kolom pertama di HP */
    .responsive-card-table tbody td.ps-4 {
        padding-left: 0 !important;
    }
    
    /* Atur kolom aksi (tombol hapus) di bagian paling bawah card */
    .responsive-card-table tbody td:last-child {
        border-bottom: none !important;
        justify-content: flex-end;
        padding-top: 10px !important;
    }

    /* Memunculkan teks nama kolom secara otomatis di sisi kiri data */
    .responsive-card-table tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        text-align: left;
        color: #718096;
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    /* Khusus styling tampilan ketika data kosong */
    .responsive-card-table tbody tr.empty-row {
        display: block !important;
        text-align: center !important;
        padding: 40px 10px !important;
    }
    .responsive-card-table tbody tr.empty-row td {
        display: block !important;
        text-align: center !important;
        border: none !important;
    }
    .responsive-card-table tbody tr.empty-row td::before {
        display: none;
    }
}
</style>

<div class="container-fluid px-4 py-2">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3 mb-4">
        <h1 class="fw-bold h3 text-dark mb-0">
            <i class="bi bi-wallet2 text-danger me-2"></i> Pengeluaran Toko
        </h1>
        <div>
            <button class="btn btn-danger fw-bold w-100 w-sm-auto py-2 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-2"></i>Catat Pengeluaran
            </button>
        </div>
    </div>

    <div class="card card-dashboard shadow-sm bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 responsive-card-table">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Tanggal</th>
                        <th class="py-3">Kategori</th>
                        <th class="py-3">Keterangan</th>
                        <th class="py-3 text-end">Nominal</th>
                        <th class="py-3 text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $q_pengeluaran = mysqli_query($koneksi, "SELECT * FROM tb_pengeluaran ORDER BY tanggal DESC, id DESC");
                    if (mysqli_num_rows($q_pengeluaran) > 0) {
                        while ($p = mysqli_fetch_assoc($q_pengeluaran)) {
                            
                            // Pemetaan nama kategori untuk visualisasi tabel
                            $badge_color = 'bg-secondary';
                            $display_kategori = $p['kategori'];
                            
                            if ($p['kategori'] == 'belanja_stok') {
                                $badge_color = 'bg-warning text-dark';
                                $display_kategori = 'Belanja Stok / Modal';
                            } elseif ($p['kategori'] == 'operasional') {
                                $badge_color = 'bg-info text-dark';
                                $display_kategori = 'Operasional';
                            }
                    ?>
                            <tr>
                                <td data-label="Tanggal" class="ps-4 fw-semibold"><?= date('d-m-Y', strtotime($p['tanggal'])); ?></td>
                                <td data-label="Kategori"><span class="badge <?= $badge_color; ?> rounded-2 fw-semibold"><?= htmlspecialchars($display_kategori); ?></span></td>
                                <td data-label="Keterangan" class="text-muted"><?= htmlspecialchars($p['keterangan']); ?></td>
                                <td data-label="Nominal" class="text-end fw-bold text-danger">Rp <?= number_format($p['nominal'], 0, ',', '.'); ?></td>
                                <td data-label="Aksi" class="text-center">
                                    <a href="pengeluaran.php?hapus=<?= $p['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Hapus catatan pengeluaran ini?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr class='empty-row'><td colspan='5' class='text-center text-muted py-5 small'>Belum ada data pengeluaran bulan ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Catat Pengeluaran Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <!-- Kategori yang bermakna 'Modal/HPP Produk' dialihkan ke nilai: belanja_stok -->
                            <option value="belanja_stok">Restock ATK & Barang Dagangan</option>
                            <option value="belanja_stok">Bahan Baku Fotocopy (Kertas / Tinta / Toner)</option>
                            
                            <!-- Kategori yang bermakna 'Biaya Bersih Toko' dialihkan ke nilai: operasional -->
                            <option value="operasional">Operasional Toko (Listrik / Wifi / Air)</option>
                            <option value="operasional">Gaji Karyawan</option>
                            <option value="operasional">Lainnya / Keperluan Tak Terduga</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nominal (Rp)</label>
                        <input type="number" name="nominal" class="form-control" placeholder="Contoh: 50000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Keterangan / Detail</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Beli Kertas Sinar Dunia A4 2 Rim" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_pengeluaran" class="btn btn-danger fw-bold">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>