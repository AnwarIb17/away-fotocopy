<?php
include '../config/koneksi.php';
session_start();
if (!isset($_SESSION['login'])) { header("Location: ../login.php"); exit; }

// Export CSV — native, tanpa lib. ponytail: upgrade = PhpSpreadsheet jika butuh .xlsx + style
$tgl_awal  = $_GET['tanggal_awal'] ?? date('Y-m-d', strtotime('-6 days'));
$tgl_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');
$jenis = $_GET['jenis'] ?? 'transaksi'; // transaksi | pengeluaran

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$jenis.'-'.$tgl_awal.'-sd-'.$tgl_akhir.'.csv"');
$out = fopen('php://output','w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM Excel

if ($jenis === 'pengeluaran') {
    fputcsv($out, ['Tanggal','Kategori','Keterangan','Nominal']);
    $q = mysqli_query($koneksi, "SELECT * FROM tb_pengeluaran WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' ORDER BY tanggal DESC");
    while($r=mysqli_fetch_assoc($q)) fputcsv($out, [$r['tanggal'],$r['kategori'],$r['keterangan'],$r['nominal']]);
} else {
    fputcsv($out, ['No Nota','Tanggal','Total Bayar','Tunai','Kembalian','Metode','Status','Pelanggan']);
    $q = mysqli_query($koneksi, "SELECT t.*, p.nama AS nm_pelanggan FROM tb_transaksi t LEFT JOIN tb_pelanggan p ON p.id_pelanggan=t.pelanggan_id WHERE DATE(t.tanggal_waktu) BETWEEN '$tgl_awal' AND '$tgl_akhir' ORDER BY t.id_transaksi DESC");
    while($r=mysqli_fetch_assoc($q)){
        $nota = $r['nomor_nota'] ?: ($r['nota_nomor'] ?? '-');
        fputcsv($out, [$nota, $r['tanggal_waktu'], $r['total_bayar'], $r['nominal_tunai'], $r['kembalian'], $r['metode_bayar'] ?? 'tunai', $r['status_bayar'] ?? 'lunas', $r['nm_pelanggan'] ?? '-']);
    }
}
fclose($out);
