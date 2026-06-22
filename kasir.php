<?php 
include 'config/koneksi.php';
include 'includes/header.php';

// Generate Nomor Nota Otomatis (Format: NOTA-YYYYMMDD-ID)
$hari_ini = date('Ymd');
$query_nota = mysqli_query($koneksi, "SELECT MAX(id_transaksi) AS last_id FROM tb_transaksi");
$data_nota = mysqli_fetch_assoc($query_nota);
$next_id = ($data_nota['last_id'] ?? 0) + 1;
$nomor_nota = "TRX-" . $hari_ini . "-" . str_pad($next_id, 4, "0", STR_PAD_LEFT);
?>

<script src="https://unpkg.com/html5-qrcode"></script>

<style>
    .search-results {
        position: absolute;
        z-index: 1000;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        border-radius: 8px;
    }
    .cart-card {
        border-radius: 16px;
        border: none;
    }

    /* PREMIUM RESPONSIVE MOBILE KASIR */
    @media (max-width: 767.98px) {
        /* Sembunyikan tabel bawaan di HP agar tidak meluber keluar layar */
        .table-responsive table {
            display: none !important;
        }
        
        /* Tampilkan keranjang kustom berbasis kartu */
        #wadah-keranjang-mobile {
            display: block !important;
        }

        .mobile-cart-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
    }

    /* Di desktop, sembunyikan pembungkus kartu mobile */
    @media (min-width: 768px) {
        #wadah-keranjang-mobile {
            display: none !important;
        }
    }
</style>

<div class="container-fluid px-4">
    <div class="row g-4">
        
        <div class="col-12 col-lg-8">
            <div class="card cart-card shadow-sm p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-search text-primary me-2"></i>Cari ATK / Jasa Cetak</h5>
                
                <div class="position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="input-cari" class="form-control bg-light border-start-0 py-2.5" placeholder="Ketik nama barang ATK, scan barcode, atau nama jasa jilid/cetak...">
                        <button class="btn btn-primary rounded-end-3 px-3 border-0" type="button" id="btn-pemicu-kamera" onclick="mulaiScanKamera()" style="background: linear-gradient(135deg, #4f46e5, #3b82f6);">
                            <i class="bi bi-camera-fill me-1"></i> Scan
                        </button>
                    </div>

                    <div id="pembungkus-kamera" class="d-none mt-3 border rounded-3 overflow-hidden bg-black" style="max-width: 100%; width: 450px; margin: 0 auto;">
                        <div id="area-pemindai" style="width: 100%;"></div>
                        <div class="p-2 text-center bg-dark">
                            <button type="button" class="btn btn-sm btn-danger px-3 rounded-2" onclick="hentikanScanKamera()"><i class="bi bi-xlg me-1"></i> Tutup Kamera</button>
                        </div>
                    </div>

                    <div id="hasil-cari" class="list-group search-results d-none"></div>
                </div>
            </div>

            <div class="card cart-card shadow-sm bg-white overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark m-0"><i class="bi bi-cart3 me-2 text-success"></i>Daftar Item Belanja</span>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3" onclick="kosongkanKeranjang()">Kosongkan</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 text-muted small text-uppercase">Nama Item</th>
                                <th class="text-muted small text-uppercase" style="width: 100px;">Jenis</th>
                                <th class="text-muted small text-uppercase text-center" style="width: 130px;">Jumlah / Qty</th>
                                <th class="text-muted small text-uppercase">Harga Satuan</th>
                                <th class="text-muted small text-uppercase">Subtotal</th>
                                <th class="text-center text-muted small text-uppercase" style="width: 70px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wadah-keranjang">
                        </tbody>
                    </table>

                    <div id="wadah-keranjang-mobile" class="p-3 bg-light" style="display: none;">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card cart-card shadow-sm p-4 bg-dark text-white sticky-top" style="top: 80px; z-index: 10;">
                <span class="text-white-50 small text-uppercase fw-semibold">No. Nota: <?= $nomor_nota; ?></span>
                <hr class="border-secondary my-2">
                
                <div class="mb-4">
                    <span class="text-white-50 small text-uppercase d-block mb-1">Total Yang Harus Dibayar</span>
                    <h1 class="fw-bold text-info mb-0" id="display-total">Rp 0</h1>
                </div>

                <form id="form-transaksi" action="proses/proses-kasir.php" method="POST">
                    <input type="hidden" name="nota_nomor" value="<?= $nomor_nota; ?>">
                    <input type="hidden" id="submit-total" name="total_bayar" value="0">
                    <input type="hidden" id="submit-json-items" name="json_items">

                    <div class="mb-3">
                        <label class="form-label text-white-50 small">Uang Tunai Diterima (Rp)</label>
                        <input type="number" id="input-tunai" name="nominal_tunai" class="form-control form-control-lg bg-secondary text-white border-0 fw-bold fs-4 rounded-3" required min="0" placeholder="0">
                    </div>

                    <div class="mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.06);">
                        <span class="text-white-50 small d-block">Uang Kembalian</span>
                        <h3 class="fw-bold text-warning mb-0" id="display-kembalian">Rp 0</h3>
                        <input type="hidden" id="submit-kembalian" name="kembalian" value="0">
                    </div>

                    <button type="submit" id="btn-simpan" class="btn btn-primary btn-lg w-100 py-3 fw-bold border-0 shadow" style="background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 12px;" disabled>
                        <i class="bi bi-printer-fill me-2"></i>SIMPAN & CETAK NOTA
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    let keranjang = [];
    let html5QrcodeScanner = null;

    // --- ENGINE SCANNER KAMERA MOBILE ---
    function mulaiScanKamera() {
        $('#pembungkus-kamera').removeClass('d-none');
        
        // Inisialisasi scanner pada div 'area-pemindai'
        html5QrcodeScanner = new Html5Qrcode("area-pemindai");
        
        const config = { 
            fps: 15, 
            qrbox: { width: 280, height: 160 } // Rasio kotak penembak disesuaikan dengan panjang barcode barang ATK
        };

        // Buka paksa kamera belakang HP ('environment')
        html5QrcodeScanner.start(
            { facingMode: "environment" }, 
            config,
            onScanSuccess
        ).catch(err => {
            alert("Izin kamera ditolak atau gagal memuat kamera: " + err);
            $('#pembungkus-kamera').addClass('d-none');
        });
    }

    function onScanSuccess(decodedText, decodedResult) {
        // 1. Tulis kode barcode hasil scan ke input pencarian
        $('#input-cari').val(decodedText);
        
        // 2. Matikan kamera otomatis agar hemat baterai HP
        hentikanScanKamera();
        
        // 3. Jalankan pemicu mesin pencari AJAX secara otomatis
        $('#input-cari').trigger('keyup');
    }

    function hentikanScanKamera() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                $('#pembungkus-kamera').addClass('d-none');
                html5QrcodeScanner = null;
            }).catch(err => {
                console.error("Gagal stop kamera:", err);
                $('#pembungkus-kamera').addClass('d-none');
                html5QrcodeScanner = null;
            });
        } else {
            $('#pembungkus-kamera').addClass('d-none');
        }
    }


    // 1. LIVE SEARCH ENGINE (Mencari Barang & Jasa Sekaligus)
    $('#input-cari').on('keyup', function() {
        let keyword = $(this).val();
        if(keyword.length > 1) {
            $.ajax({
                url: 'proses/live-search-kasir.php',
                type: 'GET',
                data: { key: keyword },
                success: function(data) {
                    $('#hasil-cari').html(data).removeClass('d-none');
                }
            });
        } else {
            $('#hasil-cari').addClass('d-none');
        }
    });

    // Sembunyikan pencarian jika klik di luar area
    $(document).click(function(e) {
        if (!$(e.target).closest('#input-cari, #hasil-cari').length) {
            $('#hasil-cari').addClass('d-none');
        }
    });

    // 2. FUNGSI MASUKKAN KE KERANJANG BELANJA
    function tambahKeKeranjang(jenis, id, nama, harga_normal, min_grosir, harga_grosir, stok_tersedia) {
        $('#input-cari').val('');
        $('#hasil-cari').addClass('d-none');

        let index = keranjang.findIndex(item => item.jenis === jenis && item.id === id);

        if (index !== -1) {
            if(jenis === 'atk' && keranjang[index].qty >= stok_tersedia) {
                alert(`Gagal menambah barang!\nBatas sisa stok untuk "${nama}" di gudang hanya tinggal ${stok_tersedia} pcs.`);
                return;
            }
            keranjang[index].qty += 1;
        } else {
            if(jenis === 'atk' && stok_tersedia <= 0) {
                alert(`Gagal!\nStok barang "${nama}" saat ini sedang kosong/habis.`);
                return;
            }
            keranjang.push({
                jenis: jenis,
                id: id,
                nama: nama,
                harga_normal: parseInt(harga_normal),
                min_grosir: parseInt(min_grosir),
                harga_grosir: parseInt(harga_grosir),
                stok_tersedia: parseInt(stok_tersedia),
                qty: 1
            });
        }
        renderKeranjang();
    }

    // 3. HITUNG ATURAN GROSIR & RENDER DUO VIEW (DESKTOP & MOBILE)
    function renderKeranjang() {
        let htmlDesktop = '';
        let htmlMobile = '';
        let totalAll = 0;

        if(keranjang.length === 0) {
            let emptyStateMobile = `<div class='text-center text-muted py-5'><i class='bi bi-cart-x fs-2 d-block mb-2'></i>Keranjang belanja kasir masih kosong.</div>`;
            $('#wadah-keranjang').html(`<tr><td colspan='6' class='text-center text-muted py-5'><i class='bi bi-cart-x fs-2 d-block mb-2'></i>Keranjang belanja kasir masih kosong.</td></tr>`);
            $('#wadah-keranjang-mobile').html(emptyStateMobile);
            hitungPembayaran(0);
            return;
        }

        keranjang.forEach((item, index) => {
            let hargaAktif = item.harga_normal;
            let statusGrosir = '';

            if (item.min_grosir > 0 && item.qty >= item.min_grosir) {
                hargaAktif = item.harga_grosir;
                statusGrosir = `<span class='badge bg-success-subtle text-success ms-1 small' style='font-size:0.65rem;'>Grosir Aktif</span>`;
            }

            let subtotal = hargaAktif * item.qty;
            totalAll += subtotal;

            // Format info sisa stok
            let infoStokFisik = '';
            if (item.jenis === 'atk') {
                infoStokFisik = `<br><small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-box-seam me-1"></i>Sisa Stok: <span class="text-danger fw-bold">${item.stok_tersedia}</span> pcs</small>`;
            }

            // A. HTML UNTUK LAYAR DESKTOP / LAPTOP (TABEL ASLI)
            htmlDesktop += `<tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td class='ps-4 fw-bold text-dark'>
                            ${item.nama} ${statusGrosir}
                            ${infoStokFisik}
                        </td>
                        <td><span class='badge ${item.jenis === 'atk' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning'} rounded-2'>${item.jenis.toUpperCase()}</span></td>
                        <td>
                            <div class='input-group input-group-sm justify-content-center'>
                                <button type='button' class='btn btn-outline-secondary' onclick='ubahQty(${index}, ${item.qty - 1})'>-</button>
                                <input type='number' class='form-control text-center fw-bold text-dark' value='${item.qty}' style='max-width:65px;' min='1' oninput='ubahQtyDirect(${index}, this.value)' onchange='ubahQtyDirect(${index}, this.value)'>
                                <button type='button' class='btn btn-outline-secondary' onclick='ubahQty(${index}, ${item.qty + 1})'>+</button>
                            </div>
                        </td>
                        <td class='text-muted'>Rp ${hargaAktif.toLocaleString('id-ID')}</td>
                        <td class='fw-bold text-slate-800'>Rp ${subtotal.toLocaleString('id-ID')}</td>
                        <td class='text-center pe-3'>
                            <button type='button' class='btn btn-sm text-danger' onclick='hapusItem(${index})'><i class='bi bi-trash3'></i></button>
                        </td>
                     </tr>`;

            // B. HTML UNTUK LAYAR HP / SMARTPHONE (FLEXBOX KARTU)
            htmlMobile += `<div class="mobile-cart-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge ${item.jenis === 'atk' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning'} mb-1" style="font-size:0.7rem;">${item.jenis.toUpperCase()}</span>
                        <div class="fw-bold text-dark" style="font-size:0.95rem; line-height: 1.3;">${item.nama} ${statusGrosir}</div>
                        ${infoStokFisik}
                    </div>
                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent ms-2" onclick='hapusItem(${index})'>
                        <i class="bi bi-trash3 fs-5"></i>
                    </button>
                </div>
                <div class="border-top my-2" style="border-style: dashed !important; border-color: #e2e8f0 !important;"></div>
                <div class="d-flex justify-content-between align-items-center pt-1">
                    <div class="input-group input-group-sm" style="max-width: 120px;">
                        <button type="button" class="btn btn-outline-secondary fw-bold px-2.5" onclick="ubahQty(${index}, ${item.qty - 1})">-</button>
                        <input type="number" class="form-control text-center fw-bold text-dark fs-6" value="${item.qty}" oninput="ubahQtyDirect(${index}, this.value)" onchange="ubahQtyDirect(${index}, this.value)">
                        <button type="button" class="btn btn-outline-secondary fw-bold px-2.5" onclick="ubahQty(${index}, ${item.qty + 1})">+</button>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block" style="font-size: 0.75rem;">@ Rp ${hargaAktif.toLocaleString('id-ID')}</small>
                        <span class="fw-bold text-primary fs-5">Rp ${subtotal.toLocaleString('id-ID')}</span>
                    </div>
                </div>
            </div>`;
        });

        $('#wadah-keranjang').html(htmlDesktop);
        $('#wadah-keranjang-mobile').html(htmlMobile);
        hitungPembayaran(totalAll);
    }

    // Mengubah Qty melalui tombol plus (+) dan minus (-)
    function ubahQty(index, val) {
        let qtyBaru = parseInt(val);
        if(isNaN(qtyBaru) || qtyBaru <= 0) {
            keranjang.splice(index, 1);
        } else {
            if(keranjang[index].jenis === 'atk' && qtyBaru > keranjang[index].stok_tersedia) {
                alert(`Jumlah melebihi batas!\nSisa stok fisik "${keranjang[index].nama}" di gudang toko hanya tersedia ${keranjang[index].stok_tersedia} pcs.`);
                keranjang[index].qty = keranjang[index].stok_tersedia;
            } else {
                keranjang[index].qty = qtyBaru;
            }
        }
        renderKeranjang();
    }

    // Mengubah Qty secara langsung saat diketik manual di form input
    function ubahQtyDirect(index, val) {
        let qtyBaru = parseInt(val);
        
        if(isNaN(qtyBaru) || val === '') return;

        if (qtyBaru <= 0) {
            keranjang[index].qty = 1;
        } else if(keranjang[index].jenis === 'atk' && qtyBaru > keranjang[index].stok_tersedia) {
            alert(`Jumlah tidak valid!\nSisa stok fisik "${keranjang[index].nama}" hanya tersedia ${keranjang[index].stok_tersedia} pcs.`);
            keranjang[index].qty = keranjang[index].stok_tersedia;
        } else {
            keranjang[index].qty = qtyBaru;
        }
        
        let totalAll = 0;
        keranjang.forEach((item) => {
            let hargaAktif = item.harga_normal;
            if (item.min_grosir > 0 && item.qty >= item.min_grosir) {
                hargaAktif = item.harga_grosir;
            }
            totalAll += (hargaAktif * item.qty);
        });
        hitungPembayaran(totalAll);
    }

    function hapusItem(index) {
        keranjang.splice(index, 1);
        renderKeranjang();
    }

    function kosongkanKeranjang() {
        keranjang = [];
        renderKeranjang();
    }

    // 4. ENGINE KALKULATOR KEMBALIAN & VALIDASI TOMBOL SIMPAN
    function hitungPembayaran(total) {
        $('#submit-total').val(total);
        $('#display-total').text('Rp ' + total.toLocaleString('id-ID'));
        $('#submit-json-items').val(JSON.stringify(keranjang));

        let uangTunai = parseInt($('#input-tunai').val()) || 0;
        let kembalian = uangTunai - total;

        if (total > 0 && uangTunai >= total) {
            $('#display-kembalian').text('Rp ' + kembalian.toLocaleString('id-ID'));
            $('#submit-kembalian').val(kembalian);
            $('#btn-simpan').prop('disabled', false);
        } else {
            $('#display-kembalian').text('Rp 0');
            $('#submit-kembalian').val(0);
            $('#btn-simpan').prop('disabled', true);
        }
    }

    $('#input-tunai').on('input', function() {
        let total = parseInt($('#submit-total').val()) || 0;
        hitungPembayaran(total);
    });

    // Panggil keranjang kosong di awal load halaman
    renderKeranjang();
</script>

<?php include 'includes/footer.php'; ?>