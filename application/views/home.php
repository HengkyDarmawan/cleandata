<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Penjualan</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#importModal">Import Excel</button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= base_url('home/index') ?>" method="get" class="form-inline">
                <label class="mr-2 font-weight-bold">Periode Data:</label>
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

                <button type="submit" class="btn btn-info">Filter</button>
                <a href="<?= base_url('home') ?>" class="btn btn-secondary ml-2">Reset</a>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary text-uppercase">
                Data Periode: <?= $filter_bulan ?> <?= $filter_tahun ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered small" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>Periode</th>
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
                                <td><span class="badge badge-info"><?= $row->bulan ?> <?= $row->tahun ?></span></td>
                                <td>
                                    <strong><?= $row->kode_produk; ?></strong><br>
                                    <small class="text-muted"><?= $row->nama_produk; ?></small>
                                </td>
                                <td><?= number_format($row->pengunjung_produk, 0, ',', '.'); ?></td>
                                <td><?= number_format($row->pembeli_dibuat, 0, ',', '.'); ?></td>
                                <td><?= number_format($row->produk_dibuat, 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row->total_penjualan, 0, ',', '.'); ?></td>
                                <td><?= number_format($row->konversi_dibuat, 2); ?>%</td>
                                <td><?= number_format($row->total_pembeli_siap, 0, ',', '.'); ?></td>
                                <td><?= number_format($row->total_produk_siap, 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row->total_penjualan_siap, 0, ',', '.'); ?></td>
                                <td><?= number_format($row->tingkat_konversi_siap, 2); ?>%</td>
                                <td class="font-weight-bold bg-light text-primary"><?= number_format($row->tingkat_konversi, 2); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center py-4">Data untuk periode ini kosong. Silakan import atau pilih bulan lain.</td>
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
                <div class="modal-header"><h5>Import Data Penjualan</h5></div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih Bulan</label>
                        <select name="bulan" class="form-control" required>
                            <option value="">-- Pilih Bulan --</option>
                            <?php 
                            $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            foreach($bulan as $b): ?>
                                <option value="<?= $b ?>"><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pilih Tahun</label>
                        <select name="tahun" class="form-control" required>
                            <?php 
                            $thn_sekarang = date('Y');
                            for($i = $thn_sekarang; $i >= $thn_sekarang-5; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>File Excel</label>
                        <input type="file" name="excel_file" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Proses & Merge</button>
                </div>
            </form>
        </div>
    </div>
</div>