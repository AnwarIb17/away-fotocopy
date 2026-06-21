<?php
// Mengatur zona waktu lokal Indonesia
date_default_timezone_set('Asia/Jakarta');

$host     = "localhost";
$username = "root";
$password = "";
$database = "db_away_fotocopy";

$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek apakah koneksi berhasil
if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}
?>