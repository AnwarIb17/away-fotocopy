<?php include 'config/koneksi.php'; include 'includes/header.php';
$q = mysqli_query($koneksi, "SELECT tanggal FROM tb_tutup_kasir ORDER BY tanggal DESC");
$closed = []; while($r=mysqli_fetch_assoc($q)) $closed[$r['tanggal']] = true;
$tgl = $_GET['tanggal'] ?? date('Y-m-d');
$sudah = isset($closed[$tgl]);

// hitung rekap hari itu (ponytail: ceiling = query langsung, upgrade = materialized view bila >10k trx/hari)
$omzet_tunai = 0; $omzet_piutang = 0;
$qr = mysqli_query($koneksi, "SELECT metode_bayar, status_bayar, SUM(total_bayar) AS s, SUM(nominal_tunai) AS tunai FROM tb_transaksi WHERE DATE(tanggal_waktu)='$tgl' GROUP BY metode_bayar, status_bayar");
while($x=mysqli_fetch_assoc($qr)){
  if(($x['status_bayar']??'lunas')==='lunas' && ($x['metode_bayar']??'tunai')!=='piutang') $omzet_tunai += (int)$x['s'];
  else $omzet_piutang += (int)$x['s'];
}
$op = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(nominal),0) AS s FROM tb_pengeluaran WHERE tanggal='$tgl' AND kategori='operasional'"))['s'] ?? 0;
$stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(nominal),0) AS s FROM tb_pengeluaran WHERE tanggal='$tgl' AND kategori='belanja_stok'"))['s'] ?? 0;
$modal_awal = 0;
$prev = mysqli_query($koneksi, "SELECT uang_fisik FROM tb_tutup_kasir WHERE tanggal<'$tgl' ORDER BY tanggal DESC LIMIT 1");
if($p=mysqli_fetch_assoc($prev)) $modal_awal = (int)$p['uang_fisik'];
$seharusnya = $modal_awal + $omzet_tunai - $op;
$rek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tb_tutup_kasir WHERE tanggal='$tgl'"));

if(isset($_POST['tutup'])){
  $fisik = (int)$_POST['uang_fisik'];
  $ket = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
  $selisih = $fisik - $seharusnya;
  $u = $_SESSION['username'] ?? $_SESSION['nama'] ?? 'kasir';
  mysqli_query($koneksi, "INSERT INTO tb_tutup_kasir (tanggal,modal_awal,omzet_tunai,omzet_piutang,pengeluaran_op,belanja_stok,uang_seharusnya,uang_fisik,selisih,keterangan,user) VALUES ('$tgl',$modal_awal,$omzet_tunai,$omzet_piutang,$op,$stok,$seharusnya,$fisik,$selisih,'$ket','$u') ON DUPLICATE KEY UPDATE uang_fisik=$fisik, selisih=$selisih, keterangan='$ket'");
  header("Location: tutup-kasir.php?tanggal=$tgl&ok=1"); exit;
}
?>
<div class="container-fluid px-4 py-2">
  <h1 class="h3 fw-bold mb-1">Tutup Kasir Harian</h1>
  <p class="text-muted small mb-4">Modal awal = uang fisik kemarin. Uang seharusnya = modal awal + omzet tunai − pengeluaran operasional.</p>
  <form method="GET" class="row g-2 mb-3">
    <div class="col-auto"><input type="date" name="tanggal" value="<?=$tgl?>" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-dark">Lihat</button></div>
    <?php if($sudah): ?><div class="col-auto"><span class="badge bg-success py-2 px-3">Sudah tutup <?=htmlspecialchars($rek['user'])?></span></div><?php endif; ?>
    <?php if(isset($_GET['ok'])): ?><div class="col-auto"><span class="badge bg-primary py-2 px-3">Tersimpan</span></div><?php endif; ?>
  </form>

  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3"><span class="small text-muted text-uppercase">Modal Awal</span><h4 class="mb-0 fw-bold">Rp <?=number_format($modal_awal,0,',','.')?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><span class="small text-muted text-uppercase">Omzet Tunai</span><h4 class="mb-0 fw-bold text-success">Rp <?=number_format($omzet_tunai,0,',','.')?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><span class="small text-muted text-uppercase">Omzet Piutang</span><h4 class="mb-0 fw-bold text-warning">Rp <?=number_format($omzet_piutang,0,',','.')?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><span class="small text-muted text-uppercase">Pengeluaran OP</span><h4 class="mb-0 fw-bold text-danger">Rp <?=number_format($op,0,',','.')?></h4><small class="text-muted">Belanja stok: Rp <?=number_format($stok,0,',','.')?></small></div></div>
    <div class="col-md-6"><div class="card p-3 bg-dark text-white"><span class="small text-white-50 text-uppercase">Uang Seharusnya di Laci</span><h2 class="mb-0 fw-bold text-info">Rp <?=number_format($seharusnya,0,',','.')?></h2></div></div>
    <?php if($rek): ?><div class="col-md-6"><div class="card p-3 <?=$rek['selisih']==0?'border-success':'border-danger'?>"><span class="small text-muted text-uppercase">Uang Fisik & Selisih</span><h4 class="mb-0 fw-bold">Rp <?=number_format($rek['uang_fisik'],0,',','.')?> <small class="<?= $rek['selisih']>=0?'text-success':'text-danger'?>"><?= $rek['selisih']>=0?'+':''?><?=number_format($rek['selisih'],0,',','.')?></small></h4><small class="text-muted"><?=htmlspecialchars($rek['keterangan'])?></small></div></div><?php endif; ?>
  </div>

  <div class="card p-4 mb-4">
    <h5 class="fw-bold">Hitung Fisik & Tutup</h5>
    <form method="POST" class="row g-3 align-items-end">
      <div class="col-md-4"><label class="form-label small fw-semibold">Uang Fisik di Laci (hitung manual)</label><input type="number" name="uang_fisik" class="form-control" required value="<?=$rek['uang_fisik'] ?? $seharusnya?>"></div>
      <div class="col-md-5"><label class="form-label small fw-semibold">Keterangan</label><input type="text" name="keterangan" class="form-control" placeholder="contoh: selisih koin, uang sobek" value="<?=htmlspecialchars($rek['keterangan']??'')?>"></div>
      <div class="col-md-3"><button name="tutup" value="1" class="btn btn-primary w-100 fw-bold">Simpan Tutup Kasir</button></div>
    </form>
  </div>

  <div class="card overflow-hidden">
    <div class="card-header bg-white fw-bold">Riwayat Tutup Kasir (7 terakhir)</div>
    <div class="table-responsive"><table class="table mb-0">
      <thead><tr class="small text-uppercase text-muted"><th>Tanggal</th><th>Omzet Tunai</th><th>Seharusnya</th><th>Fisik</th><th>Selisih</th></tr></thead>
      <tbody><?php $qh=mysqli_query($koneksi,"SELECT * FROM tb_tutup_kasir ORDER BY tanggal DESC LIMIT 7"); while($h=mysqli_fetch_assoc($qh)): ?>
        <tr><td><?=$h['tanggal']?></td><td>Rp <?=number_format($h['omzet_tunai'],0,',','.')?></td><td>Rp <?=number_format($h['uang_seharusnya'],0,',','.')?></td><td>Rp <?=number_format($h['uang_fisik'],0,',','.')?></td><td class="<?=$h['selisih']==0?'text-success':'text-danger'?> fw-bold"><?= $h['selisih']>0?'+':''?><?=number_format($h['selisih'],0,',','.')?></td></tr>
      <?php endwhile; ?></tbody>
    </table></div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
