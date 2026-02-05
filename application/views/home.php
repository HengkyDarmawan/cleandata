<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Penjualan</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#importModal">Import Excel</button>
    </div>

    <?php if($this->session->flashdata('pesan')): ?>
        <div class="alert alert-info"><?= $this->session->flashdata('pesan'); ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Total Pembeli</th>
                            <th>Produk Siap</th>
                            <th>Total Penjualan</th>
                            <th>Konversi (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_import as $row): ?>
                        <tr>
                            <td><?= $row->kode_produk; ?></td>
                            <td><?= $row->nama_produk; ?></td>
                            <td><?= number_format($row->total_pembeli_siap, 0, ',', '.'); ?></td>
                            <td><?= number_format($row->total_produk_siap, 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row->total_penjualan_siap, 0, ',', '.'); ?></td>
                            <td><?= $row->tingkat_konversi_siap; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
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
                    <input type="file" name="excel_file" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Proses & Merge</button>
                </div>
            </form>
        </div>
    </div>
</div>