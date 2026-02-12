<?php 
// 1. Logika Sorting: Urutkan array $items berdasarkan modal_per_unit secara Descending (Termahal ke Termurah)
usort($items, function($a, $b) {
    return $b->modal_per_unit <=> $a->modal_per_unit;
});
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Konfigurasi Profit & Peluang</h1>
    </div>

    <form action="<?= base_url('lottery/save_massal_pool'); ?>" method="POST">
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

                        <div class="p-3 bg-light border rounded mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Modal Barang:</span>
                                <span id="displayTotalModal" class="font-weight-bold text-dark">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Target Minimal Tiket (BEP):</span>
                                <span id="displayMinTiket" class="font-weight-bold text-primary">0 Tiket</span>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach($items as $i): ?>
                                    <tr class="item-row">
                                        <td class="text-center align-middle font-weight-bold"><?= $no++; ?></td>
                                        <td class="align-middle">
                                            <input type="hidden" name="ids[]" value="<?= $i->id; ?>">
                                            <input type="hidden" class="modal-unit" name="modal_unit[]" value="<?= (float)$i->modal_per_unit; ?>">
                                            
                                            <div class="font-weight-bold mb-0 text-uppercase"><?= $i->nama_barang; ?></div>
                                            <small class="badge badge-info"><?= $i->kode_barang; ?></small><br>
                                            <small class="text-muted">Harga/Unit: <b>Rp <?= number_format($i->modal_per_unit, 0, ',', '.'); ?></b></small>
                                        </td>
                                        <td class="align-middle text-center">
                                            <input type="number" name="qty[]" class="form-control text-center input-qty" value="<?= $i->total_qty; ?>" oninput="hitungAutoRate()">
                                        </td>
                                        <td class="align-middle">
                                            <input type="number" name="rates[]" step="0.0001" class="form-control text-center input-rate font-weight-bold" placeholder="0.00" oninput="manualUpdate()">
                                        </td>
                                        <td class="align-middle text-center bg-light">
                                            <span class="total-rate-item font-weight-bold text-success">0%</span>
                                        </td>
                                        <td class="text-right align-middle pr-4">
                                            <span class="subtotal-display font-weight-bold">Rp 0</span>
                                            <div class="text-xs text-danger zonk-label"><span class="zonk-display">0</span>% Zonk</div>
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
    const mode = document.getElementById('modeZonk').value;
    const hargaTiket = parseFloat(document.getElementById('hargaTiket').value) || 0;
    const rows = document.querySelectorAll('.item-row');

    let grandTotalModal = 0;
    let totalQty = 0;

    rows.forEach(row => {
        totalQty += parseInt(row.querySelector('.input-qty').value) || 0;
    });

    let targetWinRate = totalQty * RATE_PER_ITEM;

    if (mode === 'with-zonk') {
        if (targetWinRate > MAX_WITH_ZONK) targetWinRate = MAX_WITH_ZONK;
        if (targetWinRate < MIN_WITH_ZONK) targetWinRate = MIN_WITH_ZONK;
    } else {
        if (targetWinRate > 100) targetWinRate = 100;
    }

    // Hitung bobot: mahal kecil, murah besar
    let weights = [];
    let totalWeight = 0;

    rows.forEach(row => {
        const modal = parseFloat(row.querySelector('.modal-unit').value) || 1;
        const qty = parseInt(row.querySelector('.input-qty').value) || 0;
        const weight = (1 / modal) * qty; // murah lebih besar
        weights.push(weight);
        totalWeight += weight;
    });

    let totalGlobalRate = 0;

    rows.forEach((row, idx) => {
        const qty = parseInt(row.querySelector('.input-qty').value) || 0;
        const modal = parseFloat(row.querySelector('.modal-unit').value) || 0;
        const subtotal = modal * qty;
        grandTotalModal += subtotal;

        row.querySelector('.subtotal-display').innerText =
            'Rp ' + Math.round(subtotal).toLocaleString('id-ID');

        const itemTotalRate = (weights[idx] / totalWeight) * targetWinRate;
        const perUnitRate = qty > 0 ? itemTotalRate / qty : 0;

        row.querySelector('.input-rate').value = perUnitRate.toFixed(4);
        row.querySelector('.total-rate-item').innerText = itemTotalRate.toFixed(2) + '%';

        totalGlobalRate += itemTotalRate;
    });

    updateBusinessPanel(totalGlobalRate, grandTotalModal, hargaTiket);
}

/**
 * Dipanggil saat user mengubah angka secara manual
 */
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
    const rateDisplay = document.getElementById('displayTotalRate');
    
    // Warnai merah jika total rate melebihi 100%
    if (totalWinRate > 100) {
        rateDisplay.classList.replace('text-success', 'text-danger');
    } else {
        rateDisplay.classList.replace('text-danger', 'text-success');
    }

    let totalZonk = 100 - totalWinRate;
    if (totalZonk < 0) totalZonk = 0;

    document.getElementById('displayTotalRate').innerText = totalWinRate.toFixed(2) + '%';
    document.getElementById('displayTotalZonk').innerText = totalZonk.toFixed(2) + '%';
    document.getElementById('displayTotalModal').innerText = 'Rp ' + Math.round(totalModal).toLocaleString('id-ID');
    
    const bep = (hargaTiket > 0) ? Math.ceil(totalModal / hargaTiket) : 0;
    document.getElementById('displayMinTiket').innerText = bep.toLocaleString('id-ID') + ' Tiket';

    document.querySelectorAll('.zonk-display').forEach(el => {
        el.innerText = totalZonk.toFixed(2);
    });
}

document.addEventListener('DOMContentLoaded', hitungAutoRate);
</script>