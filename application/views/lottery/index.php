<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title; ?></h1>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Dead Stock (> 150 Hari)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_dead_stock; ?> Item</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Item Aktif di Undian</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_event; ?> Jenis</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gift fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Kupon Gacha</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_qty_event, 0, ',', '.'); ?> Kupon</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Manajemen Sistem Lotre</h6>
        </div>
        <div class="card-body">
            <p>Selamat datang di sistem manajemen <strong>Lottery Imlek</strong>. Silakan ikuti langkah berikut:</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="p-3 border rounded text-center mb-3">
                        <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                        <h5>1. Import Data</h5>
                        <p class="small text-muted">Upload file Excel stok gudang terbaru Anda.</p>
                        <button class="btn btn-success btn-sm btn-block" data-toggle="modal" data-target="#modalImport">Mulai Import</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded text-center mb-3 text-secondary">
                        <i class="fas fa-search-plus fa-3x mb-3"></i>
                        <h5>2. Filter & Pilih</h5>
                        <p class="small text-muted">Pilih barang > 150 hari untuk dijadikan hadiah.</p>
                        <a href="<?= base_url('lottery/master_list'); ?>" class="btn btn-primary btn-sm btn-block">Buka Master List</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded text-center mb-3 text-secondary">
                        <i class="fas fa-dice fa-3x mb-3"></i>
                        <h5>3. Atur Pool & Gacha</h5>
                        <p class="small text-muted">Set rate kemenangan dan mulai undian.</p>
                        <a href="<?= base_url('lottery/pool'); ?>" class="btn btn-dark btn-sm btn-block">Buka Pool Undian</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Stok</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="<?= base_url('lottery/import_excel'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih File Excel (.xlsx)</label>
                        <input type="file" name="file_excel" class="form-control-file" accept=".xlsx" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Upload & Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>