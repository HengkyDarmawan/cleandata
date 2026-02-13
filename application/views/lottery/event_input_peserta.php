<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Input Peserta Event</h1>
    </div>

    <!-- FORM INPUT -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Peserta</h6>
        </div>
        <div class="card-body">

            <form method="post" action="<?= base_url('lottery/save_peserta') ?>">
                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label>Event</label>
                        <<select name="event_id" class="form-control" required>
                            <option value="">-- Pilih Event --</option>
                            <?php foreach($events as $e): ?>
                                <option value="<?= $e->id ?>"
                                    <?= $e->stock['sisa']<=0?'disabled':'' ?>>
                                    <?= $e->event_name ?>
                                    (Sisa: <?= $e->stock['sisa'] ?>)
                                    <?= $e->stock['sisa']<=0?' - HABIS':'' ?>
                                </option>
                            <?php endforeach ?>
                        </select>

                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama Peserta" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>No HP / WhatsApp</label>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxx">
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@email.com">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Kota</label>
                        <input type="text" name="kota" class="form-control" placeholder="Jakarta">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat rumah"></textarea>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Sales</label>
                        <input type="text" name="sales" class="form-control" placeholder="Nama sales">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Qty Tiket</label>
                        <input type="number" name="qty_ticket" class="form-control" min="1" required>
                    </div>

                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Peserta
                </button>
            </form>

        </div>
    </div>

    <!-- LIST PESERTA -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Peserta</h6>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>No HP</th>
                            <th>Email</th>
                            <th>Kota</th>
                            <th>QTY</th>
                            <th>Event</th>
                            <th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($list): $no=1; foreach($list as $p): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $p->nama ?></td>
                            <td><?= $p->no_hp ?></td>
                            <td><?= $p->email ?></td>
                            <td><?= $p->kota ?></td>
                            <td><?= $p->qty_ticket ?></td>
                            <td><?= $p->event_name ?></td>
                            <td>
                                <a href="<?= base_url('lottery/print_invoice/'.$p->id) ?>" 
                                class="btn btn-sm btn-danger" target="_blank">
                                <i class="fas fa-file-pdf"></i> Invoice
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada peserta</td>
                        </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
