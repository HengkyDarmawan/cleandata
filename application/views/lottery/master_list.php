<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= $title; ?></h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-calendar fa-sm text-white-50"></i> Periode: <?= date('Y'); ?>
        </a>
    </div>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('success'); ?>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Data Dead Stock Terdeteksi (> 150 Hari)</h6>
            <div class="dropdown no-arrow">
                <span class="badge badge-info p-2">Total: <?= count($items); ?> Barang</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Tanggal Masuk</th>
                            <th>Umur</th>
                            <th>Stok Tersedia</th>
                            <th>Modal + PPN</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $row): ?>
                        <tr>
                            <td class="font-weight-bold"><?= $row->kode_barang; ?></td>
                            <td><?= $row->nama_barang; ?></td>
                            <td><?= date('d/m/Y', strtotime($row->tgl_stock)); ?></td>
                            <td>
                                <span class="badge badge-pill badge-danger shadow-sm">
                                    <i class="fas fa-history"></i> <?= $row->umur_hari; ?> Hari
                                </span>
                            </td>
                            <td><?= number_format($row->qty_asal, 0, ',', '.'); ?> unit</td>
                            <td>Rp <?= number_format($row->modal_ppn, 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-primary btn-sm btn-icon-split shadow-sm" 
                                        data-toggle="modal" data-target="#modalPilih<?= $row->id; ?>">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-gift"></i>
                                    </span>
                                    <span class="text">Jadikan Lotre</span>
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalPilih<?= $row->id; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content border-left-primary shadow">
                                    <div class="modal-header">
                                        <h5 class="modal-title font-weight-bold text-primary">Konfigurasi Kupon Gacha</h5>
                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <form action="<?= base_url('lottery/add_to_pool'); ?>" method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $row->id; ?>">
                                            
                                            <div class="p-3 bg-gray-100 rounded mb-3">
                                                <small class="text-uppercase font-weight-bold text-muted">Barang:</small>
                                                <div class="h6 font-weight-bold text-gray-800"><?= $row->nama_barang; ?></div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">Jumlah Kupon</label>
                                                        <input type="number" name="qty_lottery" class="form-control" 
                                                               placeholder="Max: <?= $row->qty_asal; ?>" 
                                                               max="<?= $row->qty_asal; ?>" min="1" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">Win Rate (%)</label>
                                                        <div class="input-group">
                                                            <input type="number" step="0.01" name="win_rate" class="form-control" 
                                                                   placeholder="Contoh: 0.5" required>
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alert alert-warning py-2 small">
                                                <i class="fas fa-info-circle"></i> 
                                                Barang yang sudah masuk pool akan otomatis hilang dari daftar Master Dead Stock.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Masukkan ke Undian</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[ 3, "desc" ]] // Sort berdasarkan umur hari paling lama
        });
    });
</script>