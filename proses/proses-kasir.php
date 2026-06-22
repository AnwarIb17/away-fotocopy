<?php
session_start();
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Menyesuaikan input dengan database Anda
    $nomor_nota     = mysqli_real_escape_string($koneksi, $_POST['nota_nomor']);
    $total_bayar    = intval($_POST['total_bayar']);
    $nominal_tunai  = intval($_POST['nominal_tunai']);
    $kembalian      = intval($_POST['kembalian']);
    $json_items     = $_POST['json_items']; 

    // Validasi data dasar
    if (empty($json_items) || $total_bayar <= 0 || $nominal_tunai < $total_bayar) {
        header("Location: ../kasir.php?status=gagal_input");
        exit();
    }

    $items = json_decode($json_items, true);
    if (!is_array($items)) {
        header("Location: ../kasir.php?status=gagal_json");
        exit();
    }

    $detail_inserts = [];

    foreach ($items as $item) {
        $id_item = intval($item['id']);
        $jenis   = mysqli_real_escape_string($koneksi, $item['jenis']); // 'atk' atau 'fotocopy'
        $qty     = intval($item['qty']);
        
        // Aturan penentuan harga jual aktif (Normal vs Grosir)
        $harga_jual_aktif = $item['harga_normal'];
        if ($item['min_grosir'] > 0 && $qty >= $item['min_grosir']) {
            $harga_jual_aktif = $item['harga_grosir'];
        }
        $subtotal_jual = $harga_jual_aktif * $qty;

        // Validasi stok khusus untuk barang ATK
        if ($jenis === 'atk') {
            $query_m = mysqli_query($koneksi, "SELECT stok FROM tb_atk WHERE id_atk = $id_item");
            $data_m  = mysqli_fetch_assoc($query_m);
            if ($data_m['stok'] < $qty) {
                header("Location: ../kasir.php?status=stok_habis");
                exit();
            }
        }

        // Simpan data rincian sesuai dengan struktur tb_detail_transaksi Anda
        $detail_inserts[] = [
            'id_item'      => $id_item,
            'jenis_item'   => $jenis,
            'jumlah'       => $qty,
            'harga_satuan' => $harga_jual_aktif,
            'subtotal'     => $subtotal_jual
        ];
    }

    // 1. INSERT UTAMA KE TABEL `tb_transaksi` (Sesuai kolom database Anda)
    // Query ini menggunakan klausa fleksibel untuk mendukung penamaan kolom Anda
    $query_transaksi = "INSERT INTO tb_transaksi (nomor_nota, total_bayar, nominal_tunai, kembalian) 
                        VALUES ('$nomor_nota', $total_bayar, $nominal_tunai, $kembalian)";
    
    // Jika kolom di database Anda adalah nota_nomor, kita fallback ke query alternatif apabila query pertama gagal
    if (!mysqli_query($koneksi, $query_transaksi)) {
        $query_transaksi = "INSERT INTO tb_transaksi (nota_nomor, total_bayar, nominal_tunai, kembalian) 
                            VALUES ('$nomor_nota', $total_bayar, $nominal_tunai, $kembalian)";
        $run_query = mysqli_query($koneksi, $query_transaksi);
    } else {
        $run_query = true;
    }
    
    if ($run_query) {
        $id_transaksi_baru = mysqli_insert_id($koneksi);

        // 2. INSERT KE TABEL `tb_detail_transaksi` & POTONG STOK
        foreach ($detail_inserts as $det) {
            $id_item      = $det['id_item'];
            $jenis_item   = $det['jenis_item'];
            $jumlah       = $det['jumlah'];
            $harga_satuan = $det['harga_satuan'];
            $subtotal     = $det['subtotal'];

            // Query insert mencocokkan kolom: id_transaksi, jenis_item, id_item, jumlah, harga_satuan, subtotal
            $query_detail = "INSERT INTO tb_detail_transaksi (id_transaksi, jenis_item, id_item, jumlah, harga_satuan, subtotal) 
                             VALUES ($id_transaksi_baru, '$jenis_item', $id_item, $jumlah, $harga_satuan, $subtotal)";
            mysqli_query($koneksi, $query_detail);

            // Jika item berupa produk ATK, potong stok fisiknya
            if ($jenis_item === 'atk') {
                mysqli_query($koneksi, "UPDATE tb_atk SET stok = stok - $jumlah WHERE id_atk = $id_item");
            }
        }

        // Transaksi sukses! Langsung alihkan ke cetak nota
        header("Location: ../cetak-nota.php?id=$id_transaksi_baru");
        exit();
    } else {
        header("Location: ../kasir.php?status=gagal_simpan");
        exit();
    }
} else {
    header("Location: ../kasir.php");
    exit();
}
?>