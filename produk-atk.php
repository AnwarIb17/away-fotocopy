<?php 
include 'config/koneksi.php';
include 'includes/header.php';

// Ambil parameter search dan filter stok jika ada
$search       = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_stok  = isset($_GET['filter_stok']) ? mysqli_real_escape_string($koneksi, $_GET['filter_stok']) : 'semua';

// ================= KONFIGURASI PAGINATION =================
$batas = 20; // Jumlah data yang ditampilkan per halaman
$halaman = isset($_GET['p_page']) ? intval($_GET['p_page']) : 1;
if ($halaman < 1) $halaman = 1;
$halaman_awal = ($halaman - 1) * $batas;
// ==========================================================

// BANGUN QUERY SECARA DINAMIS (Untuk data yang akan ditampilkan)
$query_string = "SELECT * FROM tb_atk WHERE 1=1";

// BANGUN QUERY UNTUK MENGHITUNG TOTAL DATA (Harus sama filternya)
$query_total_string = "SELECT COUNT(*) AS total FROM tb_atk WHERE 1=1";

// Jika ada kata kunci pencarian (Cari berdasarkan Nama Barang atau Barcode)
if (!empty($search)) {
    $kondisi_search = " AND (nama_barang LIKE '%$search%' OR barcode LIKE '%$search%')";
    $query_string .= $kondisi_search;
    $query_total_string .= $kondisi_search;
}

// Jika ada filter kondisi stok
if ($filter_stok === 'habis') {
    $kondisi_stok = " AND stok = 0";
    $query_string .= $kondisi_stok;
    $query_total_string .= $kondisi_stok;
} elseif ($filter_stok === 'menipis') {
    $kondisi_stok = " AND stok > 0 AND stok <= 5";
    $query_string .= $kondisi_stok;
    $query_total_string .= $kondisi_stok;
} elseif ($filter_stok === 'aman') {
    $kondisi_stok = " AND stok > 5";
    $query_string .= $kondisi_stok;
    $query_total_string .= $kondisi_stok;
}

// Hitung total data berdasarkan filter aktif untuk menentukan jumlah halaman
$query_hitung = mysqli_query($koneksi, $query_total_string);
$data_hitung = mysqli_fetch_assoc($query_hitung);
$total_data = $data_hitung['total'];
$total_halaman = ceil($total_data / $batas);

// Urutkan berdasarkan ID terbaru DAN BATASI DENGAN LIMIT & OFFSET PAGINATION
$query_string .= " ORDER BY id_atk DESC LIMIT $halaman_awal, $batas";
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
            <h1 class="h3 mb-1 text-dark fw-bold">Stok & Inventaris ATK</h1>
            <p class="text-muted small mb-0">Kelola database barang, restock kulakan, dan lakukan stock opname</p>
        </div>
        <button type="button" class="btn btn-primary rounded-3 fw-semibold shadow-sm mt-2 mt-sm-0 py-2.5 px-4" data-bs-toggle="modal" data-bs-target="#modalTambahAtk">
            <i class="bi bi-plus-circle me-2"></i> Tambah ATK Baru
        </button>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-<?= $_GET['status'] == 'sukses' ? 'success' : 'danger'; ?> alert-dismissible fade show rounded-3" role="alert">
            <?= $_GET['status'] == 'sukses' ? '<strong>Berhasil!</strong> Data inventaris berhasil diperbarui.' : '<strong>Gagal!</strong> Terjadi kesalahan internal database.'; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom shadow-sm bg-white mb-4">
        <div class="card-body p-4">
            <form method="GET" action="produk-atk.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-secondary">Cari Barang</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Ketik nama barang atau barcode..." value="<?= htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Status Ketersediaan Stok</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-funnel"></i></span>
                        <select name="filter_stok" class="form-select border-start-0">
                            <option value="semua" <?= $filter_stok == 'semua' ? 'selected' : ''; ?>>Semua Kondisi Stok</option>
                            <option value="aman" <?= $filter_stok == 'aman' ? 'selected' : ''; ?>>Stok Aman (&gt; 5 pcs)</option>
                            <option value="menipis" <?= $filter_stok == 'menipis' ? 'selected' : ''; ?>>Stok Menipis (&le; 5 pcs)</option>
                            <option value="habis" <?= $filter_stok == 'habis' ? 'selected' : ''; ?>>Stok Habis (0 pcs)</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark fw-semibold w-100 py-2.5 shadow-sm">
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                    <?php if(!empty($search) || $filter_stok !== 'semua'): ?>
                        <a href="produk-atk.php" class="btn btn-light border fw-semibold py-2.5 shadow-sm" title="Reset Filter">
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
                        <th class="ps-4 py-3 text-muted small text-uppercase">Nama Barang ATK</th>
                        <th class="text-muted small text-uppercase">Barcode</th>
                        <th class="text-muted small text-uppercase">Harga Modal</th>
                        <th class="text-muted small text-uppercase">Harga Jual</th>
                        <th class="text-muted small text-uppercase">Sisa Stok</th>
                        <th class="text-muted small text-uppercase">Grosir kustom</th>
                        <th class="text-center text-muted small text-uppercase" style="width: 180px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_assoc($query)) {
                            // Penentuan warna indikator stok
                            $stok_badge = "bg-success-subtle text-success";
                            if ($row['stok'] <= 0) $stok_badge = "bg-dark text-white";
                            elseif ($row['stok'] <= 5) $stok_badge = "bg-danger-subtle text-danger";
                    ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($row['nama_barang']); ?></td>
                                <td class="text-muted small"><?= $row['barcode'] ? htmlspecialchars($row['barcode']) : '<span class="text-light-emphasis">-</span>'; ?></td>
                                <td class="text-muted">Rp <?= number_format($row['harga_modal'], 0, ',', '.'); ?></td>
                                <td class="fw-semibold text-primary">Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                <td><span class="badge <?= $stok_badge; ?> px-3 py-2 rounded-pill fw-bold"><?= $row['stok']; ?> pcs</span></td>
                                <td>
                                    <?php if ($row['min_grosir'] > 0 && $row['harga_grosir'] > 0): ?>
                                        <span class="badge bg-info-subtle text-info px-2.5 py-1.5 rounded-3 fw-medium small">
                                            Min <?= $row['min_grosir']; ?> &rarr; Rp <?= number_format($row['harga_grosir'], 0, ',', '.'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-3">
                                    <button type="button" class="btn btn-sm btn-light rounded-3 border me-1 fw-medium" data-bs-toggle="modal" data-bs-target="#modalStok<?= $row['id_atk']; ?>">
                                        <i class="bi bi-box-seam-fill text-warning me-1"></i>Stok
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 me-1" data-bs-toggle="modal" data-bs-target="#modalEditAtk<?= $row['id_atk']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="proses/proses-atk.php?aksi=hapus&id=<?= $row['id_atk']; ?>" class="btn btn-sm btn-outline-danger rounded-3" onclick="return confirm('Hapus barang ini dari database?')">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalStok<?= $row['id_atk']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <form action="proses/proses-atk.php" method="POST">
                                            <div class="modal-header border-0 pb-0">
                                                <h6 class="modal-title fw-bold">Update Stok Fisik</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body py-3">
                                                <input type="hidden" name="id_atk" value="<?= $row['id_atk']; ?>">
                                                <p class="text-muted small mb-3">Barang: <strong><?= htmlspecialchars($row['nama_barang']); ?></strong><br>Stok sistem saat ini: <?= $row['stok']; ?> pcs</p>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Pilih Tindakan:</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="opsi_stok" id="restock<?= $row['id_atk']; ?>" value="tambah" checked>
                                                        <label class="form-check-label small text-dark fw-medium" for="restock<?= $row['id_atk']; ?>"> Kulakan / Tambah Stok (+)</label>
                                                    </div>
                                                    <div class="form-check mt-1">
                                                        <input class="form-check-input" type="radio" name="opsi_stok" id="opname<?= $row['id_atk']; ?>" value="set">
                                                        <label class="form-check-label small text-danger fw-medium" for="opname<?= $row['id_atk']; ?>"> Stock Opname (Revisi Manual)</label>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="form-label small fw-semibold text-muted">Jumlah Nilai (Pcs)</label>
                                                    <input type="number" name="jumlah_stok" class="form-control rounded-3" required min="0" placeholder="Masukkan angka">
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="submit" name="aksi" value="update_stok" class="btn btn-warning w-100 rounded-3 fw-bold">Proses Pembaruan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="modalEditAtk<?= $row['id_atk']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <form action="proses/proses-atk.php" method="POST">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">Ubah Data ATK</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body py-4">
                                                <input type="hidden" name="id_atk" value="<?= $row['id_atk']; ?>">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-12">
                                                        <label class="form-label small fw-semibold text-muted">Nama Barang ATK</label>
                                                        <input type="text" name="nama_barang" class="form-control rounded-3" value="<?= htmlspecialchars($row['nama_barang']); ?>" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small fw-semibold text-muted">Kode Barcode (Opsional)</label>
                                                        <input type="text" name="barcode" class="form-control rounded-3" value="<?= htmlspecialchars($row['barcode']); ?>">
                                                    </div>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold text-muted">Harga Modal (Rp)</label>
                                                        <input type="number" name="harga_modal" class="form-control rounded-3" value="<?= $row['harga_modal']; ?>" required min="0">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold text-muted">Harga Jual Normal (Rp)</label>
                                                        <input type="number" name="harga_jual" class="form-control rounded-3" value="<?= $row['harga_jual']; ?>" required min="0">
                                                    </div>
                                                </div>
                                                <div class="bg-light p-3 rounded-3 border">
                                                    <span class="d-block small fw-bold text-dark mb-2"><i class="bi bi-tags text-info me-1"></i>Skema Grosir Barang (Opsional)</span>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label text-muted" style="font-size: 0.75rem;">Minimal Pembelian</label>
                                                            <input type="number" name="min_grosir" class="form-control rounded-3 bg-white" value="<?= $row['min_grosir']; ?>" min="0">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label text-muted" style="font-size: 0.75rem;">Harga Grosir (Pcs)</label>
                                                            <input type="number" name="harga_grosir" class="form-control rounded-3 bg-white" value="<?= $row['harga_grosir']; ?>" min="0">
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
                        echo "<tr><td colspan='7' class='text-center text-muted py-5 small'><i class='bi bi-search d-block fs-2 mb-2 text-secondary'></i>Produk ATK tidak ditemukan. Coba ubah kata kunci atau filter Anda.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_halaman > 1): ?>
            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
                <nav>
                    <ul class="pagination mb-0 gap-1">
                        <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 rounded-3 text-dark bg-light" href="produk-atk.php?p_page=1&search=<?= urlencode($search); ?>&filter_stok=<?= urlencode($filter_stok); ?>"><i class="bi bi-chevron-double-left"></i></a>
                        </li>
                        <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 rounded-3 text-dark bg-light" href="produk-atk.php?p_page=<?= $halaman - 1; ?>&search=<?= urlencode($search); ?>&filter_stok=<?= urlencode($filter_stok); ?>"><i class="bi bi-chevron-left"></i> Prev</a>
                        </li>

                        <?php 
                        // Mengatur batas nomor halaman yang tampil (misal hanya memunculkan 3 halaman di sekitar halaman aktif)
                        $jumlah_nomor = 2; 
                        $start_number = ($halaman > $jumlah_nomor) ? $halaman - $jumlah_nomor : 1;
                        $end_number = ($halaman < ($total_halaman - $jumlah_nomor)) ? $halaman + $jumlah_nomor : $total_halaman;

                        for ($x = $start_number; $x <= $end_number; $x++): 
                        ?>
                            <li class="page-item <?= ($halaman == $x) ? 'active' : ''; ?>">
                                <a class="page-link border-0 rounded-3 <?= ($halaman == $x) ? 'bg-primary text-white fw-bold shadow-sm' : 'text-dark bg-light'; ?>" href="produk-atk.php?p_page=<?= $x; ?>&search=<?= urlencode($search); ?>&filter_stok=<?= urlencode($filter_stok); ?>"><?= $x; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 rounded-3 text-dark bg-light" href="produk-atk.php?p_page=<?= $halaman + 1; ?>&search=<?= urlencode($search); ?>&filter_stok=<?= urlencode($filter_stok); ?>">Next <i class="bi bi-chevron-right"></i></a>
                        </li>
                        <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 rounded-3 text-dark bg-light" href="produk-atk.php?p_page=<?= $total_halaman; ?>&search=<?= urlencode($search); ?>&filter_stok=<?= urlencode($filter_stok); ?>"><i class="bi bi-chevron-double-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
        </div>
</div>

<div class="modal fade" id="modalTambahAtk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="proses/proses-atk.php" method="POST">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Barang ATK Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Barang ATK</label>
                        <input type="text" name="nama_barang" class="form-control rounded-3" required placeholder="Contoh: Pulpen Kenko HI-Tech">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Kode Barcode (Scan / Ketik - Opsional)</label>
                        <input type="text" name="barcode" class="form-control rounded-3" placeholder="Kosongkan jika tidak ada">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Modal (Rp)</label>
                            <input type="number" name="harga_modal" class="form-control rounded-3" required min="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Jual (Rp)</label>
                            <input type="number" name="harga_jual" class="form-control rounded-3" required min="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Stok Awal</label>
                            <input type="number" name="stok" class="form-control rounded-3" required min="0" value="0">
                        </div>
                    </div>
                    <div class="bg-light p-3 rounded-3 border">
                        <span class="d-block small fw-bold text-dark mb-2"><i class="bi bi-tags text-info me-1"></i>Skema Grosir Barang (Opsional)</span>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 0.75rem;">Minimal Pembelian</label>
                                <input type="number" name="min_grosir" class="form-control rounded-3 bg-white" min="0" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 0.75rem;">Harga Grosir (Pcs)</label>
                                <input type="number" name="harga_grosir" class="form-control rounded-3 bg-white" min="0" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 fw-medium" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="aksi" value="tambah" class="btn btn-primary rounded-3 fw-semibold px-4">Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>