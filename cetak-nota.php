<?php
include 'config/koneksi.php';

// Ambil ID Transaksi dari URL
$id_transaksi = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Ambil data utama transaksi
$query_trx = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE id_transaksi = $id_transaksi");
if (mysqli_num_rows($query_trx) == 0) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='kasir.php';</script>";
    exit();
}
$trx = mysqli_fetch_assoc($query_trx);

// Menangani fleksibilitas nama kolom nomor nota Anda
$nomor_nota = isset($trx['nomor_nota']) ? $trx['nomor_nota'] : ($trx['nota_nomor'] ?? '-');
$tanggal_trx = isset($trx['tanggal']) ? date('d/m/Y H:i', strtotime($trx['tanggal'])) : date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nota - <?= htmlspecialchars($nomor_nota); ?></title>
    <style>
        /* Pengaturan Layout untuk Printer Kasir Thermal */
        body {
            font-family: 'Courier New', Courier, monospace; /* Font khas struk belanja */
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 10px;
            width: 58mm; /* Standar printer thermal mini, akan otomatis melar jika kertas 80mm */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .header-toko h2 {
            margin: 0 0 3px 0;
            font-size: 15px;
            text-transform: uppercase;
        }
        .header-toko p {
            margin: 0;
            font-size: 11px;
        }
        .info-nota {
            font-size: 11px;
            margin: 8px 0;
        }
        .info-nota table { width: 100%; }
        .tabel-item {
            width: 100%;
            border-collapse: collapse;
        }
        .tabel-item td {
            padding: 3px 0;
            vertical-align: top;
        }
        .tabel-total {
            width: 100%;
            margin-top: 5px;
        }
        .tabel-total td { padding: 2px 0; }
        .footer-struk {
            margin-top: 15px;
            font-size: 11px;
        }
        
        /* Hilangkan elemen tidak penting saat mode cetak fisik */
        @media print {
            body { padding: 0; margin: 0; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="header-toko text-center">
        <h2>Away Fotocopy</h2>
        <p>Jl. Raya Utama No. 45 Toko Away</p>
        <p>Telp: 0812-3456-7890</p>
    </div>

    <div class="divider"></div>

    <div class="info-nota">
        <table>
            <tr>
                <td>Nota: <?= htmlspecialchars($nomor_nota); ?></td>
            </tr>
            <tr>
                <td>Tgl : <?= $tanggal_trx; ?></td>
            </tr>
            <tr>
                <td>Ksr : Administrator</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <table class="tabel-item">
        <tbody>
            <?php
            // 2. Ambil rincian detail item belanja
            $query_detail = mysqli_query($koneksi, "SELECT * FROM tb_detail_transaksi WHERE id_transaksi = $id_transaksi");
            while ($det = mysqli_fetch_assoc($query_detail)) {
                // Cari nama item berdasarkan jenisnya
                $id_item = $det['id_item'];
                $nama_item = 'Item Tidak Diketahui';

                if ($det['jenis_item'] === 'atk') {
                    $q_item = mysqli_query($koneksi, "SELECT nama_barang FROM tb_atk WHERE id_atk = $id_item");
                    if($d_item = mysqli_fetch_assoc($q_item)) $nama_item = $d_item['nama_barang'];
                } else {
                    $q_item = mysqli_query($koneksi, "SELECT nama_jasa FROM tb_jasa_fotocopy WHERE id_jasa = $id_item");
                    if($d_item = mysqli_fetch_assoc($q_item)) $nama_item = $d_item['nama_jasa'];
                }
            ?>
                <tr>
                    <td colspan="2" style="font-weight: bold;"><?= htmlspecialchars($nama_item); ?></td>
                </tr>
                <tr>
                    <td style="padding-left: 5px; font-size: 11px;">
                        <?= $det['jumlah']; ?> x <?= number_format($det['harga_satuan'], 0, ',', '.'); ?>
                    </td>
                    <td class="text-right">
                        <?= number_format($det['subtotal'], 0, ',', '.'); ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="tabel-total">
        <tr style="font-weight: bold;">
            <td>TOTAL ALL</td>
            <td class="text-right">Rp <?= number_format($trx['total_bayar'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>TUNAI</td>
            <td class="text-right">Rp <?= number_format($trx['nominal_tunai'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>KEMBALIAN</td>
            <td class="text-right">Rp <?= number_format($trx['kembalian'], 0, ',', '.'); ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="footer-struk text-center">
        <p>Terima Kasih Atas Kunjungan Anda</p>
        <p>Barang yang sudah dibeli</p>
        <p>tidak dapat ditukar/dikembalikan</p>
    </div>

    <script>
        // Jalankan perintah print otomatis saat halaman selesai dimuat
        window.print();

        // Setelah jendela cetak ditutup (baik sukses cetak atau batal),
        // otomatis alihkan halaman kembali ke layar kasir untuk transaksi berikutnya
        window.onafterprint = function() {
            window.location.href = 'kasir.php';
        };

        // Antisipasi untuk beberapa browser lawas jika onafterprint tidak terpicu
        setTimeout(function() {
            // Jika dalam 3 detik kursor tidak pindah, ini akan bersiap kembali
            // Namun idealnya onafterprint di atas sudah cukup responsif
        }, 3000);
    </script>
</body>
</html>