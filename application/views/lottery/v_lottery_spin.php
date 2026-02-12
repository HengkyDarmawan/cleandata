<link rel="stylesheet" href="<?= base_url('assets/superwheel/superwheel.min.css'); ?>">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Kontrol Live TikTok</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Pembeli (TikTok ID):</label>
                        <input type="text" id="nama_pembeli" class="form-control form-control-lg border-primary" placeholder="Ketik nama di sini...">
                    </div>
                    <button type="button" id="btn-spin" class="btn btn-danger btn-block btn-lg shadow">
                        <i class="fas fa-sync-alt"></i> PUTAR SEKARANG
                    </button>
                    
                    <hr>
                    <h6 class="font-weight-bold">Pemenang Terakhir:</h6>
                    <div id="winner-log" class="alert alert-info" style="display:none;">
                        <strong id="log-name"></strong> menang <br> 
                        <span id="log-item" class="h5"></span>
                    </div>
                </div>
            </div>

            <div class="card shadow mt-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="m-0 font-weight-bold">Log Pemenang Terakhir (Live)</h6>
                </div>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-striped" id="table-history">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pembeli</th>
                                <th>Hadiah</th>
                            </tr>
                        </thead>
                        <tbody id="history-body">
                            <?php foreach($recent_winners as $rw): ?>
                            <tr>
                                <td><?= date('H:i', strtotime($rw->created_at)); ?></td>
                                <td><strong><?= $rw->nama_pembeli; ?></strong></td>
                                <td><?= $rw->nama_barang; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8 text-center">
            <div id="wheel-container" style="padding: 20px;">
                <div class="wheel"></div> 
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= base_url('assets/superwheel/superwheel.min.js'); ?>"></script>

<script>
$(document).ready(function() {
    var slices = []; // Pindahkan ke global agar bisa diakses tombol spin

    // 1. Inisialisasi Roda
    $.getJSON("<?= base_url('lottery/get_wheel_items'); ?>", function(data) {
        if(data.length === 0) {
            alert("Tidak ada barang aktif di Pool!");
            return;
        }

        $.each(data, function(key, val) {
            slices.push({
                text: val.nama_barang,
                value: val.id,
                message: val.nama_barang,
                background: getRandomColor(),
                win_rate: parseFloat(val.win_rate)
            });
        });

        $('.wheel').superWheel({
            slices: slices,
            text : { color: '#fff', size: 12, offset: 10, orientation: 'v' },
            line : { width: 4, color: '#fff' },
            outer: { width: 8, color: '#fff' },
            inner: { width: 10, color: '#fff' },
            center: { width: 30, background: '#fff' },
            marker: { background: '#e74c3c' },
            duration: 8000
        });
    });

    // 2. Logika Spin
    $('#btn-spin').on('click', function() {
        var nama = $('#nama_pembeli').val();
        if(nama == "") {
            alert("Masukkan nama pembeli dulu!");
            return;
        }
        
        // Tentukan pemenang berdasarkan win_rate
        var winner_id = calculateWinner(slices);
        $('.wheel').superWheel('start', 'value', winner_id);
    });

    // 3. Callback saat roda berhenti
    $('.wheel').on('atComplete', function(e, res) {
        var nama = $('#nama_pembeli').val();
        
        $.ajax({
            url: "<?= base_url('lottery/process_win'); ?>",
            type: "POST",
            data: { id: res.value, pembeli: nama },
            dataType: "JSON",
            success: function(response) {
                if(response.status == 'success') {
                    showWinnerEffect(nama, res.message);
                    updateHistoryTable(nama, res.message);
                    $('#nama_pembeli').val('');
                } else {
                    alert(response.message);
                }
                $('#btn-spin').prop('disabled', false);
            }
        });
    });

    $('.wheel').on('atStart', function(){
        $('#btn-spin').prop('disabled', true);
    });

    // Fungsi menentukan pemenang berdasarkan win_rate
    function calculateWinner(items) {
        var total_rate = 0;
        $.each(items, function(i, item) { total_rate += item.win_rate; });
        
        var random = Math.random() * total_rate;
        var current_rate = 0;
        
        for (var i = 0; i < items.length; i++) {
            current_rate += items[i].win_rate;
            if (random <= current_rate) {
                return items[i].value;
            }
        }
        return items[0].value;
    }

    function getRandomColor() {
        var colors = ['#1abc9c', '#3498db', '#9b59b6', '#f1c40f', '#e67e22', '#e74c3c', '#2c3e50'];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    function showWinnerEffect(nama, hadiah) {
        $('#log-name').text(nama);
        $('#log-item').text(hadiah);
        $('#winner-log').slideDown();
    }

    function updateHistoryTable(nama, hadiah) {
        var now = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        var row = `<tr>
            <td>${now}</td>
            <td><strong>${nama}</strong></td>
            <td>${hadiah}</td>
        </tr>`;
        $('#history-body').prepend(row);
    }
});
</script>