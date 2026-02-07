<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Analisa Performa TikTok Shop</h1>
        <button class="btn btn-danger shadow-sm" data-toggle="modal" data-target="#importTiktokModal">
            <i class="fas fa-file-import fa-sm text-white-50"></i> Import Data TikTok
        </button>
    </div>

    <div class="card shadow mb-4 border-left-danger">
        <div class="card-body">
            <form action="<?= base_url('tiktok/index') ?>" method="get" class="form-inline">
                <input type="text" name="q" class="form-control mr-2 border-primary" placeholder="Cari ID/Nama Produk..." value="<?= $search_query ?>" style="width: 220px;">
                
                <select name="toko" class="form-control mr-2 font-weight-bold border-danger">
                    <option value="">-- Semua Toko --</option>
                    <option value="Jaya" <?= ($filter_toko == 'Jaya') ? 'selected' : '' ?>>Jaya PC TikTok</option>
                    <option value="LEXAR" <?= ($filter_toko == 'LEXAR') ? 'selected' : '' ?>>LEXAR TikTok</option>
                    <option value="JayaPro" <?= ($filter_toko == 'JayaPro') ? 'selected' : '' ?>>JayaPro TikTok</option>
                </select>

                <select name="bulan" class="form-control mr-2">
                    <?php foreach($bulan_list as $b): ?>
                        <option value="<?= $b ?>" <?= ($filter_bulan == $b) ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="tahun" class="form-control mr-2">
                    <?php for($i=date('Y'); $i>=2024; $i--): ?>
                        <option value="<?= $i ?>" <?= ($filter_tahun == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>

                <button type="submit" class="btn btn-danger px-4">Analisa</button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-danger uppercase">Data TikTok: <?= $filter_bulan ?> <?= $filter_tahun ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered small table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-dark text-white text-center">
                        <tr>
                            <th>Toko</th>
                            <th>ID & Nama Produk</th>
                            <th>Penonton</th>
                            <th>Klik Unik</th>
                            <th>Keranjang</th>
                            <th>Pesanan</th>
                            <th>Pembeli</th>
                            <th>GMV Total</th>
                            <th>GMV Konten</th>
                            <th class="bg-danger">CR (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($data_tiktok)): ?>
                            <?php foreach($data_tiktok as $row): ?>
                            <tr>
                                <td class="text-center"><?= $row->toko ?></td>
                                <td>
                                    <strong><?= $row->id_produk ?></strong><br>
                                    <small class="text-muted"><?= substr($row->nama_produk, 0, 45) ?>...</small>
                                </td>
                                <td class="text-center"><?= number_format($row->penonton, 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($row->klik_unik, 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($row->keranjang, 0, ',', '.') ?></td>
                                <td class="text-center font-weight-bold"><?= number_format($row->pesanan_sku, 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($row->pembeli, 0, ',', '.') ?></td>
                                <td class="text-right">Rp <?= number_format($row->gmv, 0, ',', '.') ?></td>
                                <td class="text-right text-success">Rp <?= number_format($row->gmv_konten, 0, ',', '.') ?></td>
                                <td class="text-center font-weight-bold bg-light text-danger"><?= $row->tingkat_konversi ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center">Data Kosong.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importTiktokModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('tiktok/proses_import') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Import Data TikTok Shop</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Toko Target</label>
                        <select name="toko" class="form-control" required>
                            <option value="Jaya">Jaya PC TikTok</option>
                            <option value="LEXAR">LEXAR TikTok</option>
                            <option value="JayaPro">Jaya PRO TikTok</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                <?php foreach($bulan_list as $b): ?>
                                    <option value="<?= $b ?>"><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control mr-2 border-primary font-weight-bold">
                                <?php 
                                $tahun_sekarang = date('Y'); // Mengambil tahun saat ini (misal 2025)
                                for($i = $tahun_sekarang; $i >= 2024; $i--): 
                                ?>
                                    <option value="<?= $i ?>" <?= ($filter_tahun == $i) ? 'selected' : '' ?>>
                                        <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label>File Excel TikTok</label>
                        <input type="file" name="excel_file" class="form-control-file border p-2 w-100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger btn-block font-weight-bold">PROSES DATA TIKTOK</button>
                </div>
            </form>
        </div>
    </div>
</div>