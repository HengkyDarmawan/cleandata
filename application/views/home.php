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
                
                <input type="text" name="q" id="search_produk" class="form-control mr-2 border-primary" 
                       placeholder="Cari Kode/Nama Produk..." value="<?= $search_query ?>" 
                       style="width: 200px;" autocomplete="off">

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
                <?= ($search_query) ? "- Produk: '$search_query'" : "" ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered small table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-dark text-white text-center">
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
                            <th class="bg-primary">Konversi (S/D)</th>
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
                                    Data tidak ditemukan. Silakan import atau sesuaikan filter.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1">...</div>

<script>
$(document).ready(function() {
    $("#search_produk").autocomplete({
        source: "<?php echo base_url('home/get_autocomplete/?'); ?>",
        select: function (event, ui) {
            $(this).val(ui.item.value);
            return false;
        },
        minLength: 2
    });
});
</script>