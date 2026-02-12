<div id="selected-info" class="cart-panel">
    <div class="card shadow border-left-primary">
        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between">
            <b><i class="fas fa-shopping-cart"></i> Keranjang</b>
            <!-- <button type="button" class="btn btn-sm btn-light" id="toggle-cart">Hide</button> -->
        </div>
        <div class="card-body p-2" style="max-height: 300px; overflow:auto;">
            <ul id="cart-list" class="list-group list-group-sm"></ul>
        </div>
        <div class="card-footer bg-light">
            <div class="form-group mb-1">
                <label class="small">Harga Tiket</label>
                <input type="number" id="ticket-price" class="form-control form-control-sm" value="50000">
            </div>

            <hr class="my-2">

            <div class="d-flex justify-content-between small text-muted">
                <span>Total Nilai Hadiah</span>
                <span id="cart-total">Rp 0</span>
            </div>

            <div class="d-flex justify-content-between font-weight-bold text-primary mt-1">
                <span>Estimasi Tiket</span>
                <span id="ticket-estimate">0</span>
            </div>

            <button class="btn btn-sm btn-success btn-block mt-2" onclick="$('#formMaster').submit()">
                Proses
            </button>
        </div>
    </div>
</div>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800 text-uppercase font-weight-bold"><?= $title; ?></h1>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-left-success shadow" role="alert">
            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-left-danger shadow" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('lottery/process_to_pool'); ?>" method="POST" id="formMaster">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Barang Layak Lotre</h6>
                <button type="submit" name="action" value="massal" class="btn btn-primary btn-icon-split shadow-sm">
                    <span class="icon text-white-50">
                        <i class="fas fa-arrow-right"></i>
                    </span>
                    <span class="text">Proses Barang Terpilih</span>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%" class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Umur</th>
                                <th>Stok Gudang</th>
                                <th>Modal + PPN</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $i): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox"
                                    class="check-item"
                                    value="<?= $i->id; ?>"
                                    data-id="<?= $i->id; ?>"
                                    data-kode="<?= $i->kode_barang; ?>"
                                    data-nama="<?= htmlspecialchars($i->nama_barang, ENT_QUOTES); ?>"
                                    data-qty="<?= (int)$i->qty_asal; ?>"
                                    data-modal="<?= (float)$i->modal_ppn; ?>"
                                    style="transform: scale(1.2);">
                                </td>
                                <td><span class="font-weight-bold"><?= $i->kode_barang; ?></span></td>
                                <td><?= $i->nama_barang; ?></td>
                                <td>
                                    <span class="badge badge-danger"><?= $i->umur_hari; ?> Hari</span>
                                </td>
                                <td><?= number_format($i->qty_asal, 0, ',', '.'); ?> unit</td>
                                <td>Rp <?= number_format($i->modal_ppn, 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info btn-circle" data-toggle="modal" data-target="#modalAudit<?= $i->id; ?>" title="Audit Stok/Harga">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<?php foreach($items as $i): ?>
    <div class="modal fade" id="modalAudit<?= $i->id; ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-left-info shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-edit"></i> Audit Data Barang</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="<?= base_url('lottery/update_master_item'); ?>" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?= $i->id; ?>">
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Nama Barang</label>
                            <input type="text" class="form-control bg-light text-dark" value="<?= $i->nama_barang; ?>" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-primary">Stok Fisik</label>
                                    <input type="number" name="qty_asal" class="form-control border-primary" value="<?= $i->qty_asal; ?>" required min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-primary">Modal + PPN</label>
                                    <input type="number" step="0.01" name="modal_ppn" class="form-control border-primary" value="<?= $i->modal_ppn; ?>" required>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted"><i>*Data ini adalah data dasar sebelum diproses ke pool lotre.</i></small>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info font-weight-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>



