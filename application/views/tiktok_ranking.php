<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-trophy text-warning"></i> Ranking Performa TikTok Shop</h1>
    </div>

    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <form action="<?= base_url('tiktok/ranking') ?>" method="get" class="form-inline">
                <input type="text" name="q" class="form-control mr-2 border-primary" placeholder="Cari Produk..." value="<?= $search_query ?>" style="width: 200px;">
                
                <select name="metrik" class="form-control mr-2 font-weight-bold border-primary text-primary">
                    <option value="gmv" <?= ($filter_metrik == 'gmv') ? 'selected' : '' ?>>Berdasarkan GMV (Omzet)</option>
                    <option value="pesanan_sku" <?= ($filter_metrik == 'pesanan_sku') ? 'selected' : '' ?>>Berdasarkan Pesanan</option>
                    <option value="penonton" <?= ($filter_metrik == 'penonton') ? 'selected' : '' ?>>Berdasarkan Penonton</option>
                    <option value="tingkat_konversi" <?= ($filter_metrik == 'tingkat_konversi') ? 'selected' : '' ?>>Berdasarkan CR (%)</option>
                </select>

                <select name="toko" class="form-control mr-2">
                    <option value="">-- Semua Toko --</option>
                    <option value="Jaya" <?= ($filter_toko == 'Jaya') ? 'selected' : '' ?>>Jaya PC</option>
                    <option value="LEXAR" <?= ($filter_toko == 'LEXAR') ? 'selected' : '' ?>>LEXAR</option>
                    <option value="JayaPro" <?= ($filter_toko == 'JayaPro') ? 'selected' : '' ?>>Jaya PRO</option>
                </select>

                <select name="bulan" class="form-control mr-2">
                    <option value="semua" <?= ($filter_bulan == 'semua') ? 'selected' : '' ?>>Semua Bulan</option>
                    <?php foreach($bulan_list as $b): ?>
                        <option value="<?= $b ?>" <?= ($filter_bulan == $b) ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="tahun" class="form-control mr-2">
                    <?php for($i=date('Y'); $i>=2024; $i--): ?>
                        <option value="<?= $i ?>" <?= ($filter_tahun == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>

                <button type="submit" class="btn btn-primary px-4 font-weight-bold">Tampilkan</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-arrow-up"></i> Top 10 Produk Teratas</h6>
                </div>
                <div class="card-body">
                    <?php if(!empty($top_10)): $no=1; foreach($top_10 as $row): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge badge-success">#<?= $no++ ?></span>
                                <small class="font-weight-bold text-dark text-uppercase"><?= $row->toko ?></small>
                            </div>
                            <div class="text-dark small font-weight-bold mb-1" style="line-height: 1.2; height: 2.4em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?= $row->nama_produk ?></div>
                            
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="small text-primary font-weight-bold">
                                    <?php 
                                    if(in_array($filter_metrik, ['gmv','gmv_konten'])) echo "GMV: Rp " . number_format($row->$filter_metrik, 0, ',', '.');
                                    elseif($filter_metrik == 'penonton') echo "Penonton: " . number_format($row->penonton, 0, ',', '.');
                                    elseif($filter_metrik == 'pesanan_sku') echo "Pesanan: " . number_format($row->pesanan_sku, 0, ',', '.') . " Unit";
                                    else echo "Konversi: " . number_format($row->tingkat_konversi, 2) . "%";
                                    ?>
                                </div>
                                <small class="text-success font-weight-bold"><?= number_format($row->tingkat_konversi, 2) ?>% CR</small>
                            </div>

                            <div class="progress progress-sm mt-1" style="height: 8px;">
                                <?php 
                                    $max_top = ($top_10[0]->$filter_metrik > 0) ? $top_10[0]->$filter_metrik : 1;
                                    $percent_top = ($row->$filter_metrik / $max_top) * 100;
                                ?>
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent_top ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="text-center py-4 text-muted">Data tidak ditemukan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-danger">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-arrow-down"></i> Bottom 10 Produk (Evaluasi)</h6>
                </div>
                <div class="card-body">
                    <?php if(!empty($bottom_10)): $no=1; foreach($bottom_10 as $row): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge badge-danger">#<?= $no++ ?></span>
                                <small class="font-weight-bold text-dark text-uppercase"><?= $row->toko ?></small>
                            </div>
                            <div class="text-dark small font-weight-bold mb-1" style="line-height: 1.2; height: 2.4em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?= $row->nama_produk ?></div>
                            
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="small text-danger font-weight-bold">
                                    <?php 
                                    if(in_array($filter_metrik, ['gmv','gmv_konten'])) echo "GMV: Rp " . number_format($row->$filter_metrik, 0, ',', '.');
                                    elseif($filter_metrik == 'penonton') echo "Penonton: " . number_format($row->penonton, 0, ',', '.');
                                    elseif($filter_metrik == 'pesanan_sku') echo "Pesanan: " . number_format($row->pesanan_sku, 0, ',', '.') . " Unit";
                                    else echo "Konversi: " . number_format($row->tingkat_konversi, 2) . "%";
                                    ?>
                                </div>
                                <small class="text-danger font-weight-bold"><?= number_format($row->tingkat_konversi, 2) ?>% CR</small>
                            </div>

                            <div class="progress progress-sm mt-1" style="height: 8px;">
                                <?php 
                                    // Untuk bottom, bar dihitung berdasarkan perbandingan dengan nilai tertinggi di list bottom
                                    $max_bot = ($bottom_10[count($bottom_10)-1]->$filter_metrik > 0) ? $bottom_10[count($bottom_10)-1]->$filter_metrik : 1;
                                    $percent_bot = ($row->$filter_metrik / $max_bot) * 100;
                                ?>
                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $percent_bot ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="text-center py-4 text-muted">Data terbawah tidak tersedia/kosong.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>