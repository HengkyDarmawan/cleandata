<div id="selected-info" style="position: fixed; top: 100px; right: 30px; z-index: 9999; display: none;">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Terpilih</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="count-selected">0</span> Item</div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                </div>
            </div>
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
                                    <input type="checkbox" class="check-item" name="selected_ids[]" value="<?= $i->id; ?>" style="transform: scale(1.2);">
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


<script>
$(document).ready(function () {

    var table = $('#dataTable').DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        lengthChange: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_",
            paginate: { next: "›", previous: "‹" }
        }
    });

    let selected = new Set();

    function sync() {
        table.rows().every(function () {
            var cb = $(this.node()).find('.check-item');
            if (cb.length) cb.prop('checked', selected.has(cb.val()));
        });

        $('#count-selected').text(selected.size);
        $('#selected-info').toggle(selected.size > 0);
    }

    $('#dataTable tbody').on('change', '.check-item', function () {
        var id = $(this).val();
        this.checked ? selected.add(id) : selected.delete(id);
        sync();
    });

    $('#check-all').on('change', function () {
        var checked = this.checked;
        table.rows({ search: 'applied' }).every(function () {
            var cb = $(this.node()).find('.check-item');
            if (!cb.length) return;
            checked ? selected.add(cb.val()) : selected.delete(cb.val());
            cb.prop('checked', checked);
        });
        sync();
    });

    table.on('draw', sync);

    $('#formMaster').on('submit', function () {
        if (selected.size === 0) {
            alert('Pilih minimal satu barang!');
            return false;
        }

        $(this).find('input[name="selected_ids[]"]').remove();
        selected.forEach(id => {
            $(this).append(`<input type="hidden" name="selected_ids[]" value="${id}">`);
        });

        return confirm(`Proses ${selected.size} barang?`);
    });

});
</script>

