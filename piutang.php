<?php
include 'config/koneksi.php';
include 'includes/header.php';

// tambah pelanggan
if(isset($_POST['tambah_pelanggan'])){
  $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
  $hp   = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
  $alm  = mysqli_real_escape_string($koneksi, $_POST['alamat']);
  mysqli_query($koneksi,"INSERT INTO tb_pelanggan (nama,no_hp,alamat) VALUES ('$nama','$hp','$alm')");
  header("Location: piutang.php?ok=p"); exit;
}
if(isset($_GET['hapus_pelanggan'])){
  $id=(int)$_GET['hapus_pelanggan'];
  mysqli_query($koneksi,"DELETE FROM tb_pelanggan WHERE id_pelanggan=$id");
  header("Location: piutang.php"); exit;
}
if(isset($_POST['bayar'])){
  $id_trx=(int)$_POST['id_transaksi'];
  $nom=(int)$_POST['nominal'];
  $ket=mysqli_real_escape_string($koneksi,$_POST['keterangan']);
  $tgl=$_POST['tanggal']?:date('Y-m-d');
  $trx=mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT sisa_piutang FROM tb_transaksi WHERE id_transaksi=$id_trx"));
  $sisa=(int)($trx['sisa_piutang']??0);
  if($nom>0 && $nom<=$sisa){
    mysqli_query($koneksi,"INSERT INTO tb_piutang_bayar (id_transaksi,tanggal,nominal,keterangan) VALUES ($id_trx,'$tgl',$nom,'$ket')");
    $baru=$sisa-$nom;
    $stat=$baru<=0?'lunas':'belum_lunas';
    mysqli_query($koneksi,"UPDATE tb_transaksi SET sisa_piutang=$baru, status_bayar='$stat' WHERE id_transaksi=$id_trx");
  }
  header("Location: piutang.php?ok=bayar"); exit;
}

$qpel = mysqli_query($koneksi,"SELECT * FROM tb_pelanggan ORDER BY nama ASC");
$sumOutstanding = mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COALESCE(SUM(sisa_piutang),0) as s, COUNT(*) as c FROM tb_transaksi WHERE status_bayar!='lunas' AND sisa_piutang>0"));
?>
<div class="container-fluid px-4 py-2">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1">Piutang / Bon Pelanggan</h1>
      <p class="small text-muted mb-0">Bon sekolah/kantor: catat di Kasir → metode <b>Piutang</b>. Outstanding: <b>Rp <?=number_format($sumOutstanding['s'],0,',','.')?></b> (<?=$sumOutstanding['c']?> nota)</p>
    </div>
    <button class="btn btn-dark fw-bold" data-bs-toggle="modal" data-bs-target="#mPelanggan"><i class="bi bi-person-plus me-1"></i> Tambah Pelanggan</button>
  </div>
  <?php if(isset($_GET['ok'])): ?><div class="alert alert-success py-2">Tersimpan.</div><?php endif; ?>

  <div class="card overflow-hidden mb-4">
    <div class="card-header bg-white fw-bold d-flex justify-content-between"><span>Daftar Piutang Belum Lunas</span><a href="kasir.php" class="btn btn-sm btn-primary">Ke Kasir</a></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead><tr class="small text-uppercase text-muted"><th class="ps-3">Nota / Tgl</th><th>Pelanggan</th><th>Total</th><th>Sisa</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
        <?php
        $qp = mysqli_query($koneksi,"SELECT t.*, p.nama AS nm FROM tb_transaksi t LEFT JOIN tb_pelanggan p ON p.id_pelanggan=t.pelanggan_id WHERE t.sisa_piutang>0 ORDER BY t.id_transaksi DESC");
        if(mysqli_num_rows($qp)==0) echo "<tr><td colspan=6 class='text-center text-muted py-4'>Tidak ada piutang.</td></tr>";
        while($r=mysqli_fetch_assoc($qp)):
          $nota=$r['nomor_nota']?:($r['nota_nomor']??'-');
          $dibayar=(int)$r['total_bayar']-(int)$r['sisa_piutang'];
        ?>
          <tr>
            <td class="ps-3"><span class="fw-bold">#<?=htmlspecialchars($nota)?></span><br><small class="text-muted"><?=date('d M Y H:i',strtotime($r['tanggal_waktu']))?></small></td>
            <td><?=htmlspecialchars($r['nm']??'-')?></td>
            <td>Rp <?=number_format($r['total_bayar'],0,',','.')?><br><small class="text-muted">DP: Rp <?=number_format($dibayar,0,',','.')?></small></td>
            <td class="fw-bold text-danger">Rp <?=number_format($r['sisa_piutang'],0,',','.')?></td>
            <td><span class="badge bg-warning text-dark"><?=$r['status_bayar']?></span></td>
            <td class="text-center">
              <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bayar<?=$r['id_transaksi']?>">Bayar</button>
              <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#hist<?=$r['id_transaksi']?>">Riwayat</button>
            </td>
          </tr>
          <!-- modal bayar -->
          <div class="modal fade" id="bayar<?=$r['id_transaksi']?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <form method="POST"><div class="modal-header"><h6 class="fw-bold mb-0">Bayar Piutang #<?=htmlspecialchars($nota)?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <input type="hidden" name="id_transaksi" value="<?=$r['id_transaksi']?>">
              <p class="small text-muted">Sisa: <b class="text-danger">Rp <?=number_format($r['sisa_piutang'],0,',','.')?></b></p>
              <div class="mb-2"><label class="form-label small">Tanggal Bayar</label><input type="date" name="tanggal" class="form-control" value="<?=date('Y-m-d')?>"></div>
              <div class="mb-2"><label class="form-label small">Nominal</label><input type="number" name="nominal" class="form-control" required min="1" max="<?=$r['sisa_piutang']?>" value="<?=$r['sisa_piutang']?>"></div>
              <div class="mb-2"><label class="form-label small">Keterangan</label><input type="text" name="keterangan" class="form-control" placeholder="angsuran / lunas"></div>
            </div><div class="modal-footer"><button name="bayar" value="1" class="btn btn-success w-100 fw-bold">Simpan Pembayaran</button></div></form>
          </div></div></div>
          <!-- modal hist -->
          <div class="modal fade" id="hist<?=$r['id_transaksi']?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h6 class="fw-bold mb-0">Riwayat #<?=htmlspecialchars($nota)?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <?php $qh=mysqli_query($koneksi,"SELECT * FROM tb_piutang_bayar WHERE id_transaksi={$r['id_transaksi']} ORDER BY tanggal DESC"); if(mysqli_num_rows($qh)==0) echo "<p class='small text-muted'>Belum ada angsuran. DP awal = Rp ".number_format($dibayar,0,',','.')."</p>"; else { echo "<table class='table table-sm small'><tr><th>Tgl</th><th>Nominal</th><th>Ket</th></tr>"; while($h=mysqli_fetch_assoc($qh)) echo "<tr><td>{$h['tanggal']}</td><td>Rp ".number_format($h['nominal'],0,',','.')."</td><td>".htmlspecialchars($h['keterangan'])."</td></tr>"; echo "</table>"; } ?>
            </div>
          </div></div></div>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card overflow-hidden">
    <div class="card-header bg-white fw-bold">Daftar Pelanggan (<?=mysqli_num_rows($qpel)?>)</div>
    <div class="table-responsive"><table class="table mb-0 table-hover">
      <thead><tr class="small text-uppercase text-muted"><th class="ps-3">Nama</th><th>HP</th><th>Alamat</th><th class="text-center">Aksi</th></tr></thead>
      <tbody>
      <?php while($p=mysqli_fetch_assoc($qpel)): ?>
        <tr><td class="ps-3 fw-semibold"><?=htmlspecialchars($p['nama'])?></td><td class="small"><?=htmlspecialchars($p['no_hp'])?></td><td class="small text-muted"><?=htmlspecialchars($p['alamat'])?></td><td class="text-center"><a href="piutang.php?hapus_pelanggan=<?=$p['id_pelanggan']?>" onclick="return confirm('Hapus?')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a></td></tr>
      <?php endwhile; ?>
      </tbody>
    </table></div>
  </div>
</div>

<div class="modal fade" id="mPelanggan" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <form method="POST"><div class="modal-header"><h5 class="fw-bold">Tambah Pelanggan</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-2"><label class="form-label small fw-semibold">Nama / Instansi *</label><input name="nama" class="form-control" required placeholder="SDN 01, PT Maju"></div>
    <div class="mb-2"><label class="form-label small">No HP / WA</label><input name="no_hp" class="form-control" placeholder="08xx"></div>
    <div class="mb-2"><label class="form-label small">Alamat</label><input name="alamat" class="form-control"></div>
  </div><div class="modal-footer"><button name="tambah_pelanggan" value="1" class="btn btn-dark fw-bold w-100">Simpan</button></div></form>
</div></div></div>
<?php include 'includes/footer.php'; ?>
