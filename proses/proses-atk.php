<?php
include '../config/koneksi.php';

$aksi = $_REQUEST['aksi'] ?? '';

if ($aksi == 'tambah') {
    $nama_barang   = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $barcode       = !empty($_POST['barcode']) ? mysqli_real_escape_string($koneksi, $_POST['barcode']) : "NULL";
    $harga_modal   = intval($_POST['harga_modal']);
    $harga_jual    = intval($_POST['harga_jual']);
    $stok          = intval($_POST['stok']);
    $min_grosir    = intval($_POST['min_grosir']);
    $harga_grosir  = intval($_POST['harga_grosir']);

    $val_barcode = ($barcode === "NULL") ? "NULL" : "'$barcode'";

    $query = "INSERT INTO tb_atk (barcode, nama_barang, harga_modal, harga_jual, stok, min_grosir, harga_grosir) 
              VALUES ($val_barcode, '$nama_barang', $harga_modal, $harga_jual, $stok, $min_grosir, $harga_grosir)";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: ../produk-atk.php?status=sukses");
    } else {
        header("Location: ../produk-atk.php?status=gagal");
    }
    exit();
}

elseif ($aksi == 'edit') {
    $id_atk        = intval($_POST['id_atk']);
    $nama_barang   = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $barcode       = !empty($_POST['barcode']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['barcode']) . "'" : "NULL";
    $harga_modal   = intval($_POST['harga_modal']);
    $harga_jual    = intval($_POST['harga_jual']);
    $min_grosir    = intval($_POST['min_grosir']);
    $harga_grosir  = intval($_POST['harga_grosir']);

    $query = "UPDATE tb_atk SET 
              nama_barang = '$nama_barang', 
              barcode = $barcode,
              harga_modal = $harga_modal, 
              harga_jual = $harga_jual, 
              min_grosir = $min_grosir, 
              harga_grosir = $harga_grosir 
              WHERE id_atk = $id_atk";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../produk-atk.php?status=sukses");
    } else {
        header("Location: ../produk-atk.php?status=gagal");
    }
    exit();
}

elseif ($aksi == 'update_stok') {
    $id_atk       = intval($_POST['id_atk']);
    $opsi_stok    = $_POST['opsi_stok']; // nilainya bisa 'tambah' atau 'set'
    $jumlah_stok  = intval($_POST['jumlah_stok']);

    if ($opsi_stok == 'tambah') {
        // Skema pasok barang baru pas kulakan (Stok Lama + Stok Baru)
        $query = "UPDATE tb_atk SET stok = stok + $jumlah_stok WHERE id_atk = $id_atk";
    } else {
        // Skema Stock Opname (Ganti manual karena rusak/hilang)
        $query = "UPDATE tb_atk SET stok = $jumlah_stok WHERE id_atk = $id_atk";
    }

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../produk-atk.php?status=sukses");
    } else {
        header("Location: ../produk-atk.php?status=gagal");
    }
    exit();
}

elseif ($aksi == 'hapus') {
    $id_atk = intval($_GET['id']);
    $query = "DELETE FROM tb_atk WHERE id_atk = $id_atk";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../produk-atk.php?status=sukses");
    } else {
        header("Location: ../produk-atk.php?status=gagal");
    }
    exit();
}

header("Location: ../produk-atk.php");
exit();
?>