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

<div class="container-fluid px-4 py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold h3 text-dark mb-0"><i class="bi bi-wallet2 text-danger me-2"></i> Pengeluaran Toko</h1>
        <button class="btn btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle me-2"></i>Catat Pengeluaran
        </button>
    </div>

    <div class="card card-dashboard shadow-sm bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                    ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= date('d-m-Y', strtotime($p['tanggal'])); ?></td>
                                <td><span class="badge bg-secondary rounded-2"><?= htmlspecialchars($p['kategori']); ?></span></td>
                                <td class="text-muted"><?= htmlspecialchars($p['keterangan']); ?></td>
                                <td class="text-end fw-bold text-danger">Rp <?= number_format($p['nominal'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <a href="pengeluaran.php?hapus=<?= $p['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Hapus catatan pengeluaran ini?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-muted py-5 small'>Belum ada data pengeluaran bulan ini.</td></tr>";
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
                <button type="button" class="btn-close btn-close-white" data-bs-toggle="modal"></button>
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
                            <option value="Restock ATK">Restock ATK</option>
                            <option value="Bahan Baku Fotocopy (Kertas/Tinta)">Bahan Baku Fotocopy</option>
                            <option value="Operasional Toko (Listrik/Wifi)">Operasional Toko</option>
                            <option value="Gaji Karyawan">Gaji Karyawan</option>
                            <option value="Lainnya">Lainnya</option>
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