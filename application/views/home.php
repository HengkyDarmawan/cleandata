<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Penjualan Jaya PC</h1>
        <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#importModal">
            <i class="fas fa-upload fa-sm text-white-50"></i> Import Excel
        </button>
    </div>

    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <form action="<?= base_url('home/index') ?>" method="get" class="form-inline">
                <label class="mr-2 font-weight-bold">Filter Data:</label>
                
                <select name="toko" class="form-control mr-2 border-info font-weight-bold">
                    <option value="">-- Semua Toko --</option>
                    <option value="Jaya" <?= ($filter_toko == 'Jaya') ? 'selected' : '' ?>>Toko Jaya PC</option>
                    <option value="MSI" <?= ($filter_toko == 'MSI') ? 'selected' : '' ?>>Toko MSI</option>
                    <option value="WD" <?= ($filter_toko == 'WD') ? 'selected' : '' ?>>Toko WD</option>
                </select>

                <select name="bulan" class="form-control mr-2">
                    <?php 
                    $bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    foreach($bulan_list as $b): ?>
                        <option value="<?= $b ?>" <?= ($filter_bulan == $b) ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="tahun" class="form-control mr-2">
                    <?php $thn = date('Y'); for($i=$thn; $i>=$thn-2; $i--): ?>
                        <option value="<?= $i ?>" <?= ($filter_tahun == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>

                <button type="submit" class="btn btn-info px-4">Filter</button>
                <a href="<?= base_url('home') ?>" class="btn btn-secondary ml-2">Reset</a>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary text-uppercase">
                <i class="fas fa-table mr-1"></i> Data Periode: <?= $filter_bulan ?> <?= $filter_tahun ?> 
                <?= ($filter_toko) ? "($filter_toko)" : "(SEMUA TOKO)" ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered small table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Toko</th>
                            <th>Produk</th>
                            <th>Kunjungan</th>
                            <th>Pembeli (NP)</th>
                            <th>Produk (NP)</th>
                            <th>Penjualan (NP)</th>
                            <th>Konversi (NP)</th>
                            <th>Pembeli (PSD)</th>
                            <th>Produk (PSD)</th>
                            <th>Penjualan (PSD)</th>
                            <th>Konversi (PSD)</th>
                            <th>Konversi (S/D)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($data_import)): ?>
                            <?php foreach($data_import as $row): ?>
                            <tr>
                                <td class="text-center font-weight-bold text-info"><?= $row->toko ?></td>
                                <td>
                                    <strong><?= $row->kode_produk; ?></strong><br>
                                    <small class="text-muted"><?= $row->nama_produk; ?></small>
                                </td>
                                <td class="text-center"><?= number_format($row->pengunjung_produk, 0, ',', '.'); ?></td>
                                <td class="text-center"><?= number_format($row->pembeli_dibuat, 0, ',', '.'); ?></td>
                                <td class="text-center"><?= number_format($row->produk_dibuat, 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row->total_penjualan, 0, ',', '.'); ?></td>
                                <td class="text-center"><?= number_format($row->konversi_dibuat, 2); ?>%</td>
                                <td class="text-center"><?= number_format($row->total_pembeli_siap, 0, ',', '.'); ?></td>
                                <td class="text-center"><?= number_format($row->total_produk_siap, 0, ',', '.'); ?></td>
                                <td class="text-success font-weight-bold">Rp <?= number_format($row->total_penjualan_siap, 0, ',', '.'); ?></td>
                                <td class="text-center"><?= number_format($row->tingkat_konversi_siap, 2); ?>%</td>
                                <td class="font-weight-bold bg-light text-primary text-center"><?= number_format($row->tingkat_konversi, 2); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center py-4 text-danger font-weight-bold">
                                    Data untuk periode ini kosong. Silakan import atau pilih filter lain.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('home/proses_upload') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Import Data Penjualan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Pilih Toko Target</label>
                        <select name="toko" class="form-control border-primary" required>
                            <option value="">-- Pilih Toko --</option>
                            <option value="Jaya">Toko Jaya PC</option>
                            <option value="MSI">Toko MSI</option>
                            <option value="WD">Toko WD</option>
                        </select>
                        <small class="text-muted">Pastikan file excel sesuai dengan data toko yang dipilih.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pilih Bulan</label>
                                <select name="bulan" class="form-control" required>
                                    <?php foreach($bulan_list as $b): ?>
                                        <option value="<?= $b ?>" <?= (date('F') == $b) ? 'selected' : '' ?>><?= $b ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pilih Tahun</label>
                                <select name="tahun" class="form-control" required>
                                    <?php 
                                    $thn_sekarang = date('Y');
                                    for($i = $thn_sekarang; $i >= $thn_sekarang-2; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>File Excel (Laporan Shopee)</label>
                        <input type="file" name="excel_file" class="form-control-file border p-2 w-100" required accept=".xlsx, .xls">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold">Mulai Proses & Merge</button>
                </div>
            </form>
        </div>
    </div>
</div>