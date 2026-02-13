<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Event Lottery</h1>

    <div class="card shadow">
        <div class="card-body">

            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nama Event</th>
                    <th>Ticket Price (BEP)</th>
                    <th>Harga Jual</th>
                    <th>Total Win Rate</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if($events): $no=1; foreach($events as $e): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $e->event_name ?></td>
                    <td>Rp <?= number_format($e->ticket_price,0,',','.') ?></td>
                    <td>Rp <?= number_format($e->ticket_price_manual,0,',','.') ?></td>
                    <td><?= $e->total_win_rate ?>%</td>
                    <td>
                        <?= $e->is_locked ? '<span class="badge badge-danger">Locked</span>' :
                                            '<span class="badge badge-success">Active</span>' ?>
                    </td>
                    <td>
                        <a href="<?= base_url('lottery/event_detail/'.$e->id) ?>" class="btn btn-sm btn-info">
                            Detail
                        </a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center">Belum ada event</td></tr>
                <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
