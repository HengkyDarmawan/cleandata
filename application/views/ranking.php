<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= base_url('home/ranking') ?>" method="get" class="form-inline">
                <input type="text" name="q" id="search_produk" class="form-control mr-2 border-primary" placeholder="Ketik Kode atau Nama..." value="<?= $search_query ?>" style="width: 250px;" autocomplete="off">
                <select name="toko" class="form-control mr-2 font-weight-bold text-dark border-info">
                    <option value="">-- Semua Toko --</option>
                    <option value="Jaya" <?= (isset($filter_toko) && $filter_toko == 'Jaya') ? 'selected' : '' ?>>Toko Jaya PC</option>
                    <option value="MSI" <?= (isset($filter_toko) && $filter_toko == 'MSI') ? 'selected' : '' ?>>Toko MSI</option>
                    <option value="WD" <?= (isset($filter_toko) && $filter_toko == 'WD') ? 'selected' : '' ?>>Toko WD</option>
                </select>

                <select name="bulan" class="form-control mr-2" required>
                    <option value="semua" <?= ($filter_bulan == 'semua') ? 'selected' : '' ?>>-- Semua Bulan (Tahunan) --</option>
                    <?php $bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    foreach($bulan_list as $b): ?>
                        <option value="<?= $b ?>" <?= ($filter_bulan == $b) ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="tahun" class="form-control mr-2" required>
                    <?php $thn = date('Y'); for($i=$thn; $i>=$thn-2; $i--): ?>
                        <option value="<?= $i ?>" <?= ($filter_tahun == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>

                <select name="metrik" class="form-control mr-2 font-weight-bold border-primary text-primary">
                    <option value="pengunjung_produk" <?= ($filter_metrik == 'pengunjung_produk') ? 'selected' : '' ?>>Hero: Total Kunjungan</option>
                    <option value="pembeli_dibuat" <?= ($filter_metrik == 'pembeli_dibuat') ? 'selected' : '' ?>>Hero: Total Pembeli (NP)</option>
                    <option value="total_penjualan" <?= ($filter_metrik == 'total_penjualan') ? 'selected' : '' ?>>Hero: Penjualan (NP)</option>
                    <option value="konversi_dibuat" <?= ($filter_metrik == 'konversi_dibuat') ? 'selected' : '' ?>>Hero: Konversi (NP)</option>
                    <option value="total_penjualan_siap" <?= ($filter_metrik == 'total_penjualan_siap') ? 'selected' : '' ?>>Hero: Penjualan (PSD)</option>
                    <option value="tingkat_konversi" <?= ($filter_metrik == 'tingkat_konversi') ? 'selected' : '' ?>>Hero: Konversi (S/D)</option>
                </select>

                <button type="submit" class="btn btn-primary shadow-sm">
                    <i class="fas fa-sync-alt fa-sm text-white-50"></i> Generate Ranking
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-trophy"></i> ANALISIS PERFORMA: 10 TERBAIK & 10 TERBURUK 
                <br>
                <small class="text-dark">
                    Toko: <strong><?= (!empty($filter_toko)) ? strtoupper($filter_toko) : 'GABUNGAN SEMUA TOKO' ?></strong> | 
                    Berdasarkan: <strong><?= str_replace('_', ' ', strtoupper($filter_metrik)) ?></strong> | 
                    Periode: <strong><?= ($filter_bulan == 'semua') ? 'TAHUN ' . $filter_tahun : strtoupper($filter_bulan) . ' ' . $filter_tahun ?></strong>
                </small>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered small table-hover" id="dataTableRanking">
                    <thead class="bg-dark text-white text-center">
                        <tr>
                            <th width="5%">Rank</th>
                            <th width="12%">Periode</th>
                            <th>Produk</th>
                            <th>Kunjungan</th>
                            <th>Pembeli (NP)</th>
                            <th>Penjualan (NP)</th>
                            <th>Pembeli (PSD)</th>
                            <th>Penjualan (PSD)</th>
                            <th>Konversi (S/D)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-success text-white">
                            <td colspan="9" class="text-center font-weight-bold">🥇 10 PRODUK PERFORMA TERBAIK (TOP)</td>
                        </tr>
                        <?php if(!empty($top_10)): ?>
                            <?php $no=1; foreach($top_10 as $row): ?>
                            <tr style="background-color: #f8fff9;">
                                <td class="text-center font-weight-bold"><?= $no ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?= ($filter_bulan == 'semua') ? 'FY ' . $row->tahun : $row->bulan ?></span>
                                </td>
                                <td>
                                    <?php if(empty($filter_toko)): ?>
                                        <span class="badge badge-dark"><?= $row->toko ?></span>
                                    <?php endif; ?>
                                    <strong><?= $row->kode_produk ?></strong><br>
                                    <small class="text-muted"><?= $row->nama_produk ?></small>
                                </td>
                                <td class="text-center"><?= number_format($row->pengunjung_produk, 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($row->pembeli_dibuat, 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row->total_penjualan, 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($row->total_pembeli_siap, 0, ',', '.') ?></td>
                                <td class="font-weight-bold text-success">Rp <?= number_format($row->total_penjualan_siap, 0, ',', '.') ?></td>
                                <td class="font-weight-bold bg-light text-center text-primary"><?= number_format($row->tingkat_konversi, 2) ?>%</td>
                            </tr>
                            <?php $no++; endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center">Data Top 10 tidak tersedia.</td></tr>
                        <?php endif; ?>

                        <tr class="bg-danger text-white">
                            <td colspan="9" class="text-center font-weight-bold">⚠️ 10 PRODUK PERFORMA TERENDAH (BOTTOM)</td>
                        </tr>
                        <?php if(!empty($bottom_10)): ?>
                            <?php 
                            $bottom_sorted = array_reverse($bottom_10);
                            $no_bottom = 1; 
                            foreach($bottom_sorted as $row): ?>
                            <tr style="background-color: #fff8f8;">
                                <td class="text-center font-weight-bold text-danger"><?= $no_bottom ?></td>
                                <td class="text-center">
                                    <span class="badge badge-secondary"><?= ($filter_bulan == 'semua') ? 'FY ' . $row->tahun : $row->bulan ?></span>
                                </td>
                                <td>
                                    <?php if(empty($filter_toko)): ?>
                                        <span class="badge badge-dark"><?= $row->toko ?></span>
                                    <?php endif; ?>
                                    <strong><?= $row->kode_produk ?></strong><br>
                                    <small class="text-muted"><?= $row->nama_produk ?></small>
                                </td>
                                <td class="text-center"><?= number_format($row->pengunjung_produk, 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($row->pembeli_dibuat, 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row->total_penjualan, 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($row->total_pembeli_siap, 0, ',', '.') ?></td>
                                <td class="text-danger font-weight-bold">Rp <?= number_format($row->total_penjualan_siap, 0, ',', '.') ?></td>
                                <td class="font-weight-bold bg-light text-center text-danger"><?= number_format($row->tingkat_konversi, 2) ?>%</td>
                            </tr>
                            <?php $no_bottom++; endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center">Data Bottom 10 tidak tersedia.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
