<?php 
// 1. Logika Sorting: Urutkan array $items berdasarkan modal_per_unit secara Descending (Termahal ke Termurah)
usort($items, function($a, $b) {
    return $b->modal <=> $a->modal;
});
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Konfigurasi Profit & Peluang</h1>
    </div>

    <form action="<?= base_url('lottery/save_massal_pool'); ?>" method="POST">
        <div class="card mb-3 p-3">
            <div class="row">
                <div class="col-md-6">
                    <label>Nama Event</label>
                    <input type="text" name="event_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Harga Tiket Jual (Rp)</label>
                    <input type="number" name="ticket_price_manual" class="form-control" placeholder="Contoh: 50000" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 col-lg-5">
                <div class="card shadow mb-4 border-left-success sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header py-3 bg-success text-white">
                        <h6 class="m-0 font-weight-bold">Simulasi Keuntungan</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark">Mode Kemenangan:</label>
                            <select id="modeZonk" class="form-control border-left-primary" onchange="hitungAutoRate()">
                                <option value="no-zonk">Pasti Menang (Sisa % ke Barang Murah)</option>
                                <option value="with-zonk" selected>Ada Zonk (Sisa % jadi Kosong)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Harga Tiket Per Tiket (Rp)</label>
                            <input type="number" id="hargaTiket" name="price_per_ticket" class="form-control form-control-lg text-success font-weight-bold" value="50000" oninput="hitungAutoRate()">
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Qty Tersedia:</span>
                            <span id="displayTotalQty" class="font-weight-bold text-dark">0 pcs</span>
                        </div>
                        <div class="p-3 bg-light border rounded mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Modal Barang:</span>
                                <span id="displayTotalModal" class="font-weight-bold text-dark">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Target Minimal Tiket (BEP):</span>
                                <span id="displayMinTiket" class="font-weight-bold text-primary">0 Tiket</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="font-weight-bold">Estimasi Omzet BEP:</span>
                                <span id="displayOmzet" class="font-weight-bold text-info">Rp 0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="font-weight-bold">Total Win Rate:</span>
                                <span id="displayTotalRate" class="font-weight-bold text-success">0%</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="font-weight-bold">Peluang Zonk:</span>
                                <span id="displayTotalZonk" class="font-weight-bold text-danger">100%</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-block btn-lg mt-4 shadow font-weight-bold" onclick="return confirm('Publikasikan data ke Pool?')">
                            <i class="fas fa-rocket mr-2"></i> PUBLIKASIKAN POOL
                        </button>
                        <a href="<?= base_url('lottery/master_list'); ?>" class="btn btn-secondary btn-block mt-2">Batal</a>
                    </div>
                </div>
            </div>

            <div class="col-xl-9 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="50" class="text-center">No</th>
                                        <th>Detail Produk (Urut Termahal)</th>
                                        <th width="80" class="text-center">kuantitas (Qty)</th>
                                        <th width="120" class="text-center">Rate/Unit (%)</th>
                                        <th width="120" class="text-center text-success">Total Rate (%)</th>
                                        <th width="140" class="text-right pr-4">Subtotal Modal</th>
                                        <th>Lock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach($items as $i): ?>
                                    <tr class="item-row">
                                        <td class="text-center"><?= $no++; ?></td>

                                        <td>
                                            <input type="hidden" name="ids[]" value="<?= $i->id ?>">
                                            <input type="hidden" class="modal-unit" value="<?= $i->modal ?>">

                                            <div class="font-weight-bold"><?= $i->nama_barang ?></div>
                                            <small class="badge badge-info"><?= $i->kode_barang ?></small><br>
                                            <small>Harga/Unit: <b>Rp <?= number_format($i->modal) ?></b></small><br>
                                            <small class="text-muted">Stok: <b class="text-primary"><?= $i->qty_asal ?></b> pcs</small>
                                            <input type="hidden" class="stok-max" value="<?= $i->qty_asal ?>">
                                        </td>

                                        <td>
                                            <input type="number" name="qty[]" 
                                            class="form-control input-qty" 
                                            value="<?= min($i->qty, $i->qty_asal) ?>"
                                            min="1"
                                            max="<?= $i->qty_asal ?>"
                                            oninput="limitQty(this); hitungAutoRate()">
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" name="rates[]" 
                                                class="form-control input-rate"
                                                value="0.1"
                                                oninput="manualUpdate()">
                                        </td>

                                        <td class="text-center">
                                            <span class="total-rate-item font-weight-bold">0%</span>
                                        </td>

                                        <td class="text-right">
                                            <span class="subtotal-display font-weight-bold">Rp 0</span>
                                            <div class="text-xs text-danger">
                                                <span class="zonk-display">0</span>% Zonk
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="lock-rate">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
/**
 * Fungsi hitung otomatis rekomendasi rate dan simulasi profit
 */
const RATE_PER_ITEM = 0.1;   // 0.1% per unit
const MAX_WITH_ZONK = 40;   // max win rate
const MIN_WITH_ZONK = 25;

    function hitungAutoRate() {
        const hargaInput = document.getElementById('hargaTiket');
        const mode = document.getElementById('modeZonk').value;
        const rows = document.querySelectorAll('.item-row');

        let grandTotalModal = 0;
        let totalLockedRate = 0;
        let totalWeight = 0;
        let unlocked = [];
        let totalQty = 0;

        // HITUNG TOTAL QTY
        rows.forEach(r => {
            totalQty += parseInt(r.querySelector('.input-qty').value) || 0;
        });

        let targetWinRate = totalQty * RATE_PER_ITEM;

        if (mode === 'with-zonk') {
            if (targetWinRate > MAX_WITH_ZONK) targetWinRate = MAX_WITH_ZONK;
            if (targetWinRate < MIN_WITH_ZONK) targetWinRate = MIN_WITH_ZONK;
            hargaInput.removeAttribute('readonly');
        } else {
            targetWinRate = 100;
            hargaInput.setAttribute('readonly', true);
        }

        // HITUNG MODAL & LOCKED
        rows.forEach(row => {
            const modal = parseFloat(row.querySelector('.modal-unit').value) || 1;
            const qty = parseInt(row.querySelector('.input-qty').value) || 0;
            const rate = parseFloat(row.querySelector('.input-rate').value) || 0;
            const locked = row.querySelector('.lock-rate').checked;

            const subtotal = modal * qty;
            grandTotalModal += subtotal;

            row.querySelector('.subtotal-display').innerText =
                'Rp ' + Math.round(subtotal).toLocaleString('id-ID');

            if (locked) {
                totalLockedRate += rate * qty;
            } else {
                const weight = qty / modal; // makin murah makin besar
                unlocked.push({ row, qty, modal, weight });
                totalWeight += weight;
            }
        });

        // AUTO HARGA TIKET (setelah modal diketahui)
        let hargaTiket = parseFloat(hargaInput.value) || 0;

        if (mode !== 'with-zonk' && totalQty > 0) {
            hargaTiket = Math.ceil(grandTotalModal / totalQty);
            hargaInput.value = hargaTiket;
        }

        // BAGI RATE SISA
        let sisaRate = targetWinRate - totalLockedRate;
        if (sisaRate < 0) sisaRate = 0;

        let totalGlobalRate = totalLockedRate;

        unlocked.forEach(obj => {
            const totalItemRate = (obj.weight / totalWeight) * sisaRate;
            const perUnit = obj.qty > 0 ? totalItemRate / obj.qty : 0;

            obj.row.querySelector('.input-rate').value = perUnit.toFixed(4);
            obj.row.querySelector('.total-rate-item').innerText =
                totalItemRate.toFixed(2) + '%';

            totalGlobalRate += totalItemRate;
        });

        // UPDATE BARANG YANG LOCK
        rows.forEach(row => {
            if (row.querySelector('.lock-rate').checked) {
                const qty = parseInt(row.querySelector('.input-qty').value) || 0;
                const rate = parseFloat(row.querySelector('.input-rate').value) || 0;
                const totalItemRate = rate * qty;
                row.querySelector('.total-rate-item').innerText =
                    totalItemRate.toFixed(2) + '%';
            }
        });

        updateBusinessPanel(totalGlobalRate, grandTotalModal, hargaTiket);
    }


    function manualUpdate() {
        let totalGlobalWinRate = 0;
        let grandTotalModal = 0;
        const hargaTiket = parseFloat(document.getElementById('hargaTiket').value) || 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const modalUnit = parseFloat(row.querySelector('.modal-unit').value) || 0;
            const qty = parseInt(row.querySelector('.input-qty').value) || 0;
            const ratePerUnit = parseFloat(row.querySelector('.input-rate').value) || 0;

            const totalItemRate = ratePerUnit * qty;
            row.querySelector('.total-rate-item').innerText = totalItemRate.toFixed(2) + '%';

            grandTotalModal += (modalUnit * qty);
            totalGlobalWinRate += totalItemRate;
        });

        updateBusinessPanel(totalGlobalWinRate, grandTotalModal, hargaTiket);
    }


    function updateBusinessPanel(totalWinRate, totalModal, hargaTiket) {
        const rows = document.querySelectorAll('.item-row');
        let totalQty = 0;

        rows.forEach(r => {
            totalQty += parseInt(r.querySelector('.input-qty').value) || 0;
        });

        document.getElementById('displayTotalQty').innerText = totalQty + ' pcs';

        const rateDisplay = document.getElementById('displayTotalRate');

        if (totalWinRate > 100) {
            rateDisplay.classList.replace('text-success', 'text-danger');
        } else {
            rateDisplay.classList.replace('text-danger', 'text-success');
        }

        let totalZonk = 100 - totalWinRate;
        if (totalZonk < 0) totalZonk = 0;

        document.getElementById('displayTotalRate').innerText = totalWinRate.toFixed(2) + '%';
        document.getElementById('displayTotalZonk').innerText = totalZonk.toFixed(2) + '%';
        document.getElementById('displayTotalModal').innerText =
            'Rp ' + Math.round(totalModal).toLocaleString('id-ID');

        const mode = document.getElementById('modeZonk').value;
        let bep = 0;

        if (mode === 'no-zonk') {
            bep = totalQty; // karena semua pasti dapat barang
        } else {
            bep = (hargaTiket > 0) ? Math.ceil(totalModal / hargaTiket) : 0;
        }
        document.getElementById('displayMinTiket').innerText =
            bep.toLocaleString('id-ID') + ' Tiket';

        document.querySelectorAll('.zonk-display').forEach(el => {
            el.innerText = totalZonk.toFixed(2);
        });
        const omzet = bep * hargaTiket;
        document.getElementById('displayOmzet').innerText =
            'Rp ' + Math.round(omzet).toLocaleString('id-ID');
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('lock-rate')) {
            const row = e.target.closest('.item-row');
            const rateInput = row.querySelector('.input-rate');

            if (e.target.checked) {
                rateInput.setAttribute('readonly', true);
                row.classList.add('table-warning');
            } else {
                rateInput.removeAttribute('readonly');
                row.classList.remove('table-warning');
            }
            hitungAutoRate();
        }
    });
    function limitQty(input) {
        const max = parseInt(input.closest('.item-row').querySelector('.stok-max').value) || 0;
        let val = parseInt(input.value) || 0;

        if (val > max) input.value = max;
        if (val < 1) input.value = 1;
    }
    
document.addEventListener('DOMContentLoaded', hitungAutoRate);
</script>