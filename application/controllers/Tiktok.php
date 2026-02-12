<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Pastikan autoload composer sudah berjalan untuk PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

class Tiktok extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Load library yang dibutuhkan
        $this->load->database();
        $this->load->helper('url');
    }

    public function index() {
        $bulan_filter = $this->input->get('bulan') ?: 'Januari';
        $tahun_filter = $this->input->get('tahun') ?: date('Y');
        $toko_filter  = $this->input->get('toko');
        $search       = $this->input->get('q');

        $this->db->from('import_tiktok');
        $this->db->where('bulan', $bulan_filter);
        $this->db->where('tahun', $tahun_filter);

        if ($toko_filter) {
            $this->db->where('toko', $toko_filter);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('id_produk', $search);
            $this->db->or_like('nama_produk', $search);
            $this->db->group_end();
        }

        $data['title'] = "Analisa TikTok Shop";
        $data['data_tiktok'] = $this->db->get()->result();
        
        // Data pendukung filter
        $data['filter_bulan'] = $bulan_filter;
        $data['filter_tahun'] = $tahun_filter;
        $data['filter_toko']  = $toko_filter;
        $data['search_query'] = $search;
        $data['bulan_list']   = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $this->load->view('header', $data);
        $this->load->view('tiktok_view', $data);
        $this->load->view('footer', $data);
    }
    private function get_bulan_indo($mo) {
        $bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        return $bulan[$mo];
    }

    public function proses_import() {
        $toko  = $this->input->post('toko');
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');

        if (!isset($_FILES['excel_file']['name'])) {
            redirect('tiktok');
        }

        $path = $_FILES['excel_file']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($path);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // 1. Hapus data lama agar tidak duplikat
            $this->db->where(['toko' => $toko, 'bulan' => $bulan, 'tahun' => $tahun]);
            $this->db->delete('import_tiktok');

            $data_import = [];
            
            // 2. Looping mulai dari baris ke-2 (index 1) karena baris 1 adalah header
            for ($i = 1; $i < count($sheetData); $i++) {
                // Lewati jika baris kosong
                if (empty($sheetData[$i][0])) continue;

                $data_import[] = [
                    'toko'             => $toko,
                    'bulan'            => $bulan,
                    'tahun'            => $tahun,
                    'id_produk'        => $sheetData[$i][0],  // Kolom A: ID Produk
                    'nama_produk'      => $sheetData[$i][1],  // Kolom B: Nama Produk
                    'penonton'         => (int)$sheetData[$i][2], // Kolom C: Penonton
                    'tayangan'         => (int)$sheetData[$i][3], // Kolom D: Tayangan
                    'klik_unik'        => (int)$sheetData[$i][4], // Kolom E: Klik Unik
                    'keranjang'        => (int)$sheetData[$i][8], // Kolom I: Pengguna yang Menambahkan ke Keranjang
                    'pesanan_sku'      => (int)$sheetData[$i][5], // Kolom F: Pesanan SKU
                    'pembeli'          => (int)$sheetData[$i][7], // Kolom H: Pembeli
                    'gmv'              => (float)str_replace(['Rp', '.', ','], ['', '', '.'], $sheetData[$i][10]), // Kolom K: GMV
                    'gmv_konten'       => (float)str_replace(['Rp', '.', ','], ['', '', '.'], $sheetData[$i][16]), // Kolom Q: GMV Konten
                    'tingkat_klik'     => (float)str_replace(['%', ','], ['', '.'], $sheetData[$i][12]), // Kolom M: Tkt Tayangan ke Klik
                    'tingkat_konversi' => (float)str_replace(['%', ','], ['', '.'], $sheetData[$i][11]), // Kolom L: Tkt Tayangan ke Bayar
                ];
            }

            // 3. Simpan sekaligus
            if (!empty($data_import)) {
                $this->db->insert_batch('import_tiktok', $data_import);
            }

            $this->session->set_flashdata('success', 'Data TikTok ' . $toko . ' periode ' . $bulan . ' berhasil diimport.');
            redirect('tiktok/index?bulan='.$bulan.'&tahun='.$tahun.'&toko='.$toko);

        } catch (Exception $e) {
            die('Error loading file: ' . $e->getMessage());
        }
    }
    public function ranking() {
        // 1. Ambil input filter
        $bulan_filter = $this->input->get('bulan') ?: $this->get_bulan_indo(date('m'));
        $tahun_filter = $this->input->get('tahun') ?: date('Y');
        $toko_filter  = $this->input->get('toko'); 
        $metrik       = $this->input->get('metrik') ?: 'gmv';
        $search       = $this->input->get('q');

        // 2. Daftar metrik
        $allowed_metrics = [
            'penonton'         => 'penonton',
            'klik_unik'        => 'klik_unik',
            'keranjang'        => 'keranjang',
            'pesanan_sku'      => 'pesanan_sku',
            'pembeli'          => 'pembeli',
            'gmv'              => 'gmv',
            'gmv_konten'       => 'gmv_konten',
            'tingkat_klik'     => 'tingkat_klik',
            'tingkat_konversi' => 'tingkat_konversi'
        ];

        $sort_column = (isset($allowed_metrics[$metrik])) ? $allowed_metrics[$metrik] : 'gmv';

        // 3. Bangun Query dengan Filter Validasi
        $build_query = function($order) use ($bulan_filter, $tahun_filter, $toko_filter, $sort_column, $metrik, $search) {
            
            $this->db->from('import_tiktok');

            // --- LOGIKA PENCARIAN ---
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('id_produk', $search);
                $this->db->or_like('nama_produk', $search);
                $this->db->group_end();
            }

            // --- PERBAIKAN UTAMA: Saring Nilai Nol ---
            // Agar ranking relevan, kita hanya ambil produk yang minimal ada interaksi (penonton > 0)
            $this->db->where('penonton >', 0);

            if ($order == 'ASC') {
                // Untuk Bottom 10, kita hanya ingin produk yang ADA angkanya tapi KECIL.
                // Produk yang nilainya 0 (tidak laku sama sekali) biasanya tidak berguna untuk dianalisis rankingnya.
                $this->db->where($sort_column . ' >', 0);
            }

            if ($bulan_filter == 'semua') {
                $toko_select = ($toko_filter) ? 'toko' : '"GABUNGAN" as toko';
                
                $this->db->select('id_produk, nama_produk, tahun, "Semua Bulan" as bulan, '.$toko_select.',
                    SUM(penonton) as penonton, 
                    SUM(klik_unik) as klik_unik, 
                    SUM(pesanan_sku) as pesanan_sku, 
                    SUM(gmv) as gmv, 
                    AVG(tingkat_konversi) as tingkat_konversi');
                
                $this->db->where('tahun', $tahun_filter);
                if ($toko_filter) $this->db->where('toko', $toko_filter);
                
                $this->db->group_by('id_produk');
                
                $final_sort = (in_array($metrik, ['tingkat_konversi', 'tingkat_klik'])) 
                            ? "AVG($sort_column)" : "SUM($sort_column)";
                
                $this->db->order_by($final_sort, $order);

            } else {
                $this->db->select('*');
                if ($bulan_filter) $this->db->where('bulan', $bulan_filter);
                if ($tahun_filter) $this->db->where('tahun', $tahun_filter);
                if ($toko_filter)  $this->db->where('toko', $toko_filter);
                
                $this->db->order_by($sort_column, $order);
            }

            // Limit 10 kecuali sedang mencari produk spesifik
            if (empty($search)) {
                $this->db->limit(10);
            }

            return $this->db->get()->result();
        };

        // 4. Eksekusi
        $data['top_10']    = $build_query('DESC');
        $data['bottom_10'] = (empty($search)) ? $build_query('ASC') : [];

        // 5. Data View
        $data['title']        = "Ranking Performa TikTok";
        $data['filter_bulan'] = $bulan_filter;
        $data['filter_tahun'] = $tahun_filter;
        $data['filter_toko']  = $toko_filter; 
        $data['filter_metrik'] = $metrik;
        $data['search_query'] = $search;
        $data['bulan_list']   = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $this->load->view('header', $data);
        $this->load->view('tiktok_ranking', $data);
        $this->load->view('footer', $data);
    }
}