<?php 
include 'config/koneksi.php';
include 'includes/header.php';

// Ambil parameter search dan filter tipe harga jika ada
$search       = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_skema = isset($_GET['filter_skema']) ? mysqli_real_escape_string($koneksi, $_GET['filter_skema']) : 'semua';

// BANGUN QUERY SECARA DINAMIS
$query_string = "SELECT * FROM tb_jasa_fotocopy WHERE 1=1";

// Jika ada kata kunci pencarian
if (!empty($search)) {
    $query_string .= " AND nama_jasa LIKE '%$search%'";
}

// Jika ada filter skema potongan harga grosir
if ($filter_skema === 'grosir') {
    $query_string .= " AND min_grosir > 0 AND harga_grosir > 0";
} elseif ($filter_skema === 'normal') {
    $query_string .= " AND (min_grosir = 0 OR harga_grosir = 0)";
}

// Urutkan berdasarkan ID terbaru
$query_string .= " ORDER BY id_jasa DESC";
$query = mysqli_query($koneksi, $query_string);
?>

<style>
    .card-custom {
        border: none;
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .form-control, .form-select, .btn {
        border-radius: 10px !important;
    }
    .table th {
        font-weight: 600;
        letter-spacing: 0.5px;
        background-color: #f8fafc !important;
    }
</style>

<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">Tarif Jasa & Finishing</h1>
            <p class="text-muted small mb-0">Atur harga normal, modal HPP, dan skema grosir kustom</p>
        </div>
        <button type="button" class="btn btn-primary rounded-3 fw-semibold shadow-sm mt-2 mt-sm-0 py-2.5 px-4" data-bs-toggle="modal" data-bs-target="#modalTambahJasa">
            <i class="bi bi-plus-circle me-2"></i> Tambah Jasa/Tarif Baru
        </button>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-<?= $_GET['status'] == 'sukses' ? 'success' : 'danger'; ?> alert-dismissible fade show rounded-3" role="alert">
            <?= $_GET['status'] == 'sukses' ? '<strong>Berhasil!</strong> Data jasa berhasil diperbarui.' : '<strong>Gagal!</strong> Terjadi kesalahan dalam memproses data.'; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom shadow-sm bg-white mb-4">
        <div class="card-body p-4">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-secondary">Cari Layanan Jasa</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Ketik nama jasa, kertas, atau finishing..." value="<?= htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Skema Potongan Tarif</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-funnel"></i></span>
                        <select name="filter_skema" class="form-select border-start-0">
                            <option value="semua" <?= $filter_skema == 'semua' ? 'selected' : ''; ?>>Semua Jenis Tarif</option>
                            <option value="grosir" <?= $filter_skema == 'grosir' ? 'selected' : ''; ?>>Memiliki Skema Grosir</option>
                            <option value="normal" <?= $filter_skema == 'normal' ? 'selected' : ''; ?>>Hanya Tarif Normal (Tanpa Grosir)</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark fw-semibold w-100 py-2.5 shadow-sm">
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                    <?php if(!empty($search) || $filter_skema !== 'semua'): ?>
                        <a href="?" class="btn btn-light border fw-semibold py-2.5 shadow-sm" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background-color: #f8fafc; border-bottom: 2px solid #edf2f7;">
                    <tr>
                        <th class="ps-4 py-3 text-muted small text-uppercase">Nama Jasa / Cetak</th>
                        <th class="text-muted small text-uppercase">Estimasi Modal (HPP)</th>
                        <th class="text-muted small text-uppercase">Harga Normal</th>
                        <th class="text-muted small text-uppercase">Aturan Grosir (Min Pembelian)</th>
                        <th class="text-center text-muted small text-uppercase" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_assoc($query)) {
                    ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($row['nama_jasa']); ?></td>
                                <td class="text-muted">Rp <?= number_format($row['harga_modal'], 0, ',', '.'); ?></td>
                                <td class="fw-semibold text-primary">Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($row['min_grosir'] > 0 && $row['harga_grosir'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-medium">
                                            Min <?= $row['min_grosir']; ?> lembar &rarr; Rp <?= number_format($row['harga_grosir'], 0, ',', '.'); ?>/lbr
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted px-3 py-2 rounded-pill">Tidak Ada Grosir</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 me-1" data-bs-toggle="modal" data-bs-target="#modalEditJasa<?= $row['id_jasa']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="proses/proses-fotocopy.php?aksi=hapus&id=<?= $row['id_jasa']; ?>" class="btn btn-sm btn-outline-danger rounded-3" onclick="return confirm('Apakah Anda yakin ingin menghapus tarif jasa ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEditJasa<?= $row['id_jasa']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <form action="proses/proses-fotocopy.php" method="POST">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">Ubah Tarif Jasa</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body py-4">
                                                <input type="hidden" name="id_jasa" value="<?= $row['id_jasa']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold text-muted">Nama Jasa / Kertas / Finishing</label>
                                                    <input type="text" name="nama_jasa" class="form-control rounded-3" value="<?= htmlspecialchars($row['nama_jasa']); ?>" required placeholder="Contoh: Cetak Warna F4 Text">
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold text-muted">Estimasi Modal (Rp)</label>
                                                        <input type="number" name="harga_modal" class="form-control rounded-3" value="<?= $row['harga_modal']; ?>" required min="0">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold text-muted">Harga Jual Normal (Rp)</label>
                                                        <input type="number" name="harga_jual" class="form-control rounded-3" value="<?= $row['harga_jual']; ?>" required min="0">
                                                    </div>
                                                </div>
                                                <div class="bg-light p-3 rounded-3 border">
                                                    <span class="d-block small fw-bold text-dark mb-2"><i class="bi bi-tags text-success me-1"></i>Skema Potongan Harga Grosir (Opsional)</span>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label text-muted" style="font-size: 0.75rem;">Minimal Pembelian</label>
                                                            <input type="number" name="min_grosir" class="form-control rounded-3 bg-white" value="<?= $row['min_grosir']; ?>" min="0" placeholder="0 jika tak ada">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label text-muted" style="font-size: 0.75rem;">Harga Grosir (Per Pcs/Lbr)</label>
                                                            <input type="number" name="harga_grosir" class="form-control rounded-3 bg-white" value="<?= $row['harga_grosir']; ?>" min="0" placeholder="0 jika tak ada">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-light rounded-3 fw-medium" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="aksi" value="edit" class="btn btn-primary rounded-3 fw-semibold px-4">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-muted py-5 small'><i class='bi bi-search d-block fs-2 mb-2 text-secondary'></i>Tarif jasa tidak ditemukan. Coba gunakan kata kunci lain.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahJasa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="proses/proses-fotocopy.php" method="POST">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Jasa / Tarif Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Jasa / Kertas / Finishing</label>
                        <input type="text" name="nama_jasa" class="form-control rounded-3" required placeholder="Contoh: Fotocopy F4 HVS Bolak-Balik">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Estimasi Modal (Rp)</label>
                            <input type="number" name="harga_modal" class="form-control rounded-3" required min="0" placeholder="Kertas+Tinta">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Harga Jual Normal (Rp)</label>
                            <input type="number" name="harga_jual" class="form-control rounded-3" required min="0" placeholder="Harga Jual">
                        </div>
                    </div>
                    <div class="bg-light p-3 rounded-3 border">
                        <span class="d-block small fw-bold text-dark mb-2"><i class="bi bi-tags text-success me-1"></i>Skema Potongan Harga Grosir (Opsional)</span>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 0.75rem;">Minimal Pembelian</label>
                                <input type="number" name="min_grosir" class="form-control rounded-3 bg-white" min="0" value="0" placeholder="Contoh: 20">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 0.75rem;">Harga Grosir (Per Pcs/Lbr)</label>
                                <input type="number" name="harga_grosir" class="form-control rounded-3 bg-white" min="0" value="0" placeholder="Contoh: 300">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 fw-medium" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="aksi" value="tambah" class="btn btn-primary rounded-3 fw-semibold px-4">Simpan Jasa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>