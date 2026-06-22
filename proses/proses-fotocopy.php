<?php
include '../config/koneksi.php';

// AMBIL METODE AKSI (BISA POST ATAU GET UNTUK HAPUS)
$aksi = $_REQUEST['aksi'] ?? '';

if ($aksi == 'tambah') {
    $nama_jasa     = mysqli_real_escape_string($koneksi, $_POST['nama_jasa']);
    $harga_modal   = intval($_POST['harga_modal']);
    $harga_jual    = intval($_POST['harga_jual']);
    $min_grosir    = intval($_POST['min_grosir']);
    $harga_grosir  = intval($_POST['harga_grosir']);

    $query = "INSERT INTO tb_jasa_fotocopy (nama_jasa, harga_modal, harga_jual, min_grosir, harga_grosir) 
              VALUES ('$nama_jasa', $harga_modal, $harga_jual, $min_grosir, $harga_grosir)";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: ../jasa-fotocopy.php?status=sukses");
    } else {
        header("Location: ../jasa-fotocopy.php?status=gagal");
    }
    exit();
}

elseif ($aksi == 'edit') {
    $id_jasa       = intval($_POST['id_jasa']);
    $nama_jasa     = mysqli_real_escape_string($koneksi, $_POST['nama_jasa']);
    $harga_modal   = intval($_POST['harga_modal']);
    $harga_jual    = intval($_POST['harga_jual']);
    $min_grosir    = intval($_POST['min_grosir']);
    $harga_grosir  = intval($_POST['harga_grosir']);

    $query = "UPDATE tb_jasa_fotocopy SET 
              nama_jasa = '$nama_jasa', 
              harga_modal = $harga_modal, 
              harga_jual = $harga_jual, 
              min_grosir = $min_grosir, 
              harga_grosir = $harga_grosir 
              WHERE id_jasa = $id_jasa";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../jasa-fotocopy.php?status=sukses");
    } else {
        header("Location: ../jasa-fotocopy.php?status=gagal");
    }
    exit();
}

elseif ($aksi == 'hapus') {
    $id_jasa = intval($_GET['id']);

    $query = "DELETE FROM tb_jasa_fotocopy WHERE id_jasa = $id_jasa";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../jasa-fotocopy.php?status=sukses");
    } else {
        header("Location: ../jasa-fotocopy.php?status=gagal");
    }
    exit();
}

// Proteksi jika file diakses langsung secara tidak sah
header("Location: ../jasa-fotocopy.php");
exit();
?>