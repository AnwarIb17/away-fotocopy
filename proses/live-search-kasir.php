<?php
include '../config/koneksi.php';

$key = isset($_GET['key']) ? mysqli_real_escape_string($koneksi, $_GET['key']) : '';

if (trim($key) === '') {
    exit();
}

// 1. Ambil data dari Tabel ATK (Cari berdasarkan Nama atau Barcode)
$query_atk = mysqli_query($koneksi, "SELECT * FROM tb_atk WHERE nama_barang LIKE '%$key%' OR barcode LIKE '%$key%' LIMIT 5");

// 2. Ambil data dari Tabel Jasa
$query_jasa = mysqli_query($koneksi, "SELECT * FROM tb_jasa_fotocopy WHERE nama_jasa LIKE '%$key%' LIMIT 5");

$html = '';

// TAMPILKAN HASIL KATEGORI ATK
if ($query_atk && mysqli_num_rows($query_atk) > 0) {
    $html .= '<div class="bg-secondary text-white px-3 py-1.5 small fw-bold">PRODUK ATK INVENTARIS</div>';
    while ($atk = mysqli_fetch_assoc($query_atk)) {
        $barcode_display = !empty($atk['barcode']) ? $atk['barcode'] : '-';
        $html .= '<button type="button" class="list-group-item list-group-item-action py-2.5 px-3 border-0" onclick="tambahKeKeranjang(\'atk\', ' . $atk['id_atk'] . ', \'' . addslashes($atk['nama_barang']) . '\', ' . $atk['harga_jual'] . ', ' . $atk['min_grosir'] . ', ' . $atk['harga_grosir'] . ', ' . $atk['stok'] . ')">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-dark d-block">' . htmlspecialchars($atk['nama_barang']) . '</strong>
                            <span class="text-muted small" style="font-size: 0.75rem;">Barcode: ' . $barcode_display . ' | Stok: ' . $atk['stok'] . ' pcs</span>
                        </div>
                        <span class="text-primary fw-bold text-end">Rp ' . number_format($atk['harga_jual'], 0, ',', '.') . '</span>
                    </div>
                  </button>';
    }
}

// TAMPILKAN HASIL KATEGORI JASA
if ($query_jasa && mysqli_num_rows($query_jasa) > 0) {
    $html .= '<div class="bg-warning text-dark px-3 py-1.5 small fw-bold">JASA FOTOCOPY & FINISHING</div>';
    while ($jasa = mysqli_fetch_assoc($query_jasa)) {
        $html .= '<button type="button" class="list-group-item list-group-item-action py-2.5 px-3 border-0" onclick="tambahKeKeranjang(\'fotocopy\', ' . $jasa['id_jasa'] . ', \'' . addslashes($jasa['nama_jasa']) . '\', ' . $jasa['harga_jual'] . ', ' . $jasa['min_grosir'] . ', ' . $jasa['harga_grosir'] . ', 999999)">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-dark d-block">' . htmlspecialchars($jasa['nama_jasa']) . '</strong>
                            <span class="text-muted small" style="font-size: 0.75rem;">Kategori Layanan Jasa</span>
                        </div>
                        <span class="text-success fw-bold text-end">Rp ' . number_format($jasa['harga_jual'], 0, ',', '.') . '</span>
                    </div>
                  </button>';
    }
}

if (empty($html)) {
    $html = '<div class="list-group-item text-center text-muted py-3 small">Barang atau Jasa tidak ditemukan.</div>';
}

echo $html;
?>