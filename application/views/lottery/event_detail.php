<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">
        <i class="fas fa-gift"></i> <?= $event->event_name ?>
    </h1>

    <div class="alert alert-info shadow-sm">
        <i class="fas fa-ticket-alt"></i>
        Total Tiket: <b><?= $total_ticket ?></b> |
        Terjual: <b><?= $sold_ticket ?></b> |
        Sisa: <b><?= $sisa_ticket ?></b>
    </div>

    <div class="row">

        <!-- PESERTA -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-users"></i> Peserta (Random)
                </div>
                <div class="card-body" style="max-height:420px;overflow:auto">
                    <?php if($participants): ?>
                        <ul class="list-group">
                            <?php foreach($participants as $p): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= $p->nama ?>
                                    <span class="badge badge-success badge-pill">
                                        <?= $p->qty_ticket ?> tiket
                                    </span>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-center text-muted">Belum ada peserta</p>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <!-- WHEEL -->
        <div class="col-lg-4 text-center">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-sync-alt"></i> Spin Wheel
                </div>
                <div class="card-body">

                    <div style="position:relative;display:inline-block;">
                        <div style="
                            position:absolute;
                            top:-25px;
                            left:50%;
                            transform:translateX(-50%);
                            font-size:36px;
                            color:red;">
                            ▼
                        </div>
                        <canvas id="wheelCanvas" width="380" height="380"></canvas>
                    </div>

                    <button id="spinBtn"
                        class="btn btn-success btn-lg mt-3"
                        <?= ($sisa_ticket<=0 || $event->is_locked)?'disabled':'' ?>>
                        <i class="fas fa-play"></i> SPIN
                    </button>

                </div>
            </div>
        </div>

        <!-- PEMENANG -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-trophy"></i> Pemenang
                </div>
                <div class="card-body" style="max-height:420px;overflow:auto">
                    <?php if($history): ?>
                        <ul class="list-group">
                            <?php foreach($history as $h): ?>
                                <li class="list-group-item">
                                    <b><?= $h->nama_pembeli ?></b><br>
                                    <small class="text-muted"><?= $h->nama_barang ?></small>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-center text-muted">Belum ada pemenang</p>
                    <?php endif ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const canvas = document.getElementById("wheelCanvas");
const ctx = canvas.getContext("2d");
const center = canvas.width / 2;

const prizes = <?= json_encode($items) ?>;
let segments = [];

prizes.forEach(p => {
    for(let i=0;i<p.win_rate;i++) segments.push(p);
});

let angle = 0;
let speed = 0;
let spinning = false;

function drawWheel(){
    const slice = 2 * Math.PI / segments.length;
    ctx.clearRect(0,0,canvas.width,canvas.height);

    segments.forEach((p,i)=>{
        const start = angle + i * slice;
        ctx.beginPath();
        ctx.moveTo(center,center);
        ctx.arc(center,center,center-10,start,start+slice);
        ctx.fillStyle = i % 2 ? "#4e73df" : "#1cc88a";
        ctx.fill();

        ctx.save();
        ctx.translate(center,center);
        ctx.rotate(start + slice/2);
        ctx.textAlign = "right";
        ctx.fillStyle = "#fff";
        ctx.font = "bold 14px Arial";
        ctx.fillText(p.nama_barang, center-20, 5);
        ctx.restore();
    });
}

drawWheel();

document.getElementById("spinBtn").onclick = () => {
    if(spinning) return;
    speed = Math.random() * 0.4 + 0.5;
    spinning = true;
    animate();
};

function animate(){
    angle += speed;
    speed *= 0.985;

    if(speed < 0.002){
        spinning = false;
        showWinner();
        return;
    }
    drawWheel();
    requestAnimationFrame(animate);
}

function showWinner(){
    const slice = 2 * Math.PI / segments.length;
    let index = Math.floor((2*Math.PI - angle % (2*Math.PI)) / slice);
    let win = segments[index];

    Swal.fire({
        icon: 'success',
        title: '🎉 Pemenang!',
        html: `<h4>${win.nama_barang}</h4>`,
        confirmButtonText: 'OK'
    }).then(()=>{
        window.location.href =
            "<?= base_url('lottery/save_winner/'.$event->id) ?>/" + win.kode_barang;
    });
}
</script>
