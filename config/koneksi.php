<?php
date_default_timezone_set('Asia/Jakarta');

$host     = "localhost";
$user     = "root";
$pass     = "";
$db       = "db_away_fotocopy";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (mysqli_connect_errno()) {
    echo "Gagal tersambung ke basis data MySQL: " . mysqli_connect_error();
    exit();
}
?>