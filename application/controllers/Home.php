<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\IOFactory;

class Home extends MY_Controller {

    public function index() {
        $bulan_filter = $this->input->get('bulan') ?: $this->get_bulan_indo(date('m'));
        $tahun_filter = $this->input->get('tahun') ?: date('Y');
        $toko_filter  = $this->input->get('toko'); 
        $search       = $this->input->get('q'); // Tambahkan input search produk

        $this->db->from('import');
        
        // Filter Dasar
        $this->db->where('bulan', $bulan_filter);
        $this->db->where('tahun', $tahun_filter);
        
        if ($toko_filter) {
            $this->db->where('toko', $toko_filter);
        }

        // --- LOGIKA FILTER PER PRODUK ---
        if ($search) {
            $this->db->group_start();
            $this->db->like('kode_produk', $search);
            $this->db->or_like('nama_produk', $search);
            $this->db->group_end();
        }

        $data['title'] = "Data Master Import";
        $data['data_import'] = $this->db->get()->result();

        $data['filter_bulan'] = $bulan_filter;
        $data['filter_tahun'] = $tahun_filter;
        $data['filter_toko']  = $toko_filter;
        $data['search_query'] = $search; // Kirim balik ke view

        $this->load->view('header', $data);
        $this->load->view('home', $data);
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

    public function proses_upload() {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $input_toko  = $this->input->post('toko'); 
        $input_bulan = $this->input->post('bulan');
        $input_tahun = $this->input->post('tahun');

        if (empty($input_toko)) {
            $this->session->set_flashdata('pesan', 'Gagal: Silahkan pilih toko terlebih dahulu.');
            redirect('home');
        }

        $file = $_FILES['excel_file']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $data_final = [];

            foreach ($sheetData as $i => $row) {
                if ($i == 1) continue; 

                $kode = trim($row['A']);
                if (empty($kode)) continue;

                // Merge Key menyertakan Toko agar data tidak tercampur antar cabang
                $merge_key = $kode . '-' . $input_toko . '-' . $input_bulan . '-' . $input_tahun;

                $clean = [
                    'toko'                 => $input_toko,
                    'bulan'                => $input_bulan,
                    'tahun'                => $input_tahun,
                    'pengunjung_produk'    => (int) str_replace(['.', ','], '', $row['I']),
                    'pembeli_dibuat'       => (int) str_replace(['.', ','], '', $row['R']),
                    'produk_dibuat'        => (int) str_replace(['.', ','], '', $row['S']),
                    'total_penjualan'      => (float) str_replace(['.', ','], '', $row['T']),
                    'konversi_dibuat'      => (float) str_replace(['%', ','], ['', '.'], $row['U']),
                    'total_pembeli_siap'   => (int) str_replace(['.', ','], '', $row['V']),
                    'total_produk_siap'    => (int) str_replace(['.', ','], '', $row['W']),
                    'total_penjualan_siap' => (float) str_replace(['.', ','], '', $row['X']),
                    'tingkat_konversi_siap'=> (float) str_replace(['%', ','], ['', '.'], $row['Y']),
                    'tingkat_konversi'     => (float) str_replace(['%', ','], ['', '.'], $row['Z']),
                ];

                if (isset($data_final[$merge_key])) {
                    $data_final[$merge_key]['pengunjung_produk']    += $clean['pengunjung_produk'];
                    $data_final[$merge_key]['pembeli_dibuat']       += $clean['pembeli_dibuat'];
                    $data_final[$merge_key]['produk_dibuat']        += $clean['produk_dibuat'];
                    $data_final[$merge_key]['total_penjualan']      += $clean['total_penjualan'];
                    $data_final[$merge_key]['total_pembeli_siap']   += $clean['total_pembeli_siap'];
                    $data_final[$merge_key]['total_produk_siap']    += $clean['total_produk_siap'];
                    $data_final[$merge_key]['total_penjualan_siap'] += $clean['total_penjualan_siap'];
                } else {
                    $data_final[$merge_key] = array_merge([
                        'kode_produk' => $kode,
                        'nama_produk' => $row['B'],
                    ], $clean);
                }
            }

            if (!empty($data_final)) {
                $this->db->insert_batch('import', array_values($data_final));
                $this->session->set_flashdata('pesan', "Data Toko $input_toko berhasil diunggah.");
            }

        } catch (Exception $e) {
            $this->session->set_flashdata('pesan', 'Gagal: ' . $e->getMessage());
        }
        redirect('home');
    }

    //perbandingan antara produk
    public function ranking() {
        $bulan_filter = $this->input->get('bulan');
        $tahun_filter = $this->input->get('tahun');
        $toko_filter  = $this->input->get('toko'); 
        $metrik       = $this->input->get('metrik');
        $search       = $this->input->get('q'); // Tangkap input search dari view

        $allowed_metrics = [
            'pengunjung_produk'    => 'pengunjung_produk',
            'pembeli_dibuat'       => 'pembeli_dibuat',
            'total_penjualan'      => 'total_penjualan',
            'konversi_dibuat'      => 'konversi_dibuat',
            'total_pembeli_siap'   => 'total_pembeli_siap',
            'total_produk_siap'    => 'total_produk_siap',
            'total_penjualan_siap' => 'total_penjualan_siap',
            'tingkat_konversi_siap'=> 'tingkat_konversi_siap',
            'tingkat_konversi'     => 'tingkat_konversi'
        ];

        $sort_column = (isset($allowed_metrics[$metrik])) ? $allowed_metrics[$metrik] : 'total_penjualan_siap';

        // Tambahkan $search ke dalam blok 'use' agar bisa diakses di dalam function
        $build_query = function($order) use ($bulan_filter, $tahun_filter, $toko_filter, $sort_column, $metrik, $search) {
            
            $this->db->from('import');

            // --- LOGIKA PENCARIAN (Search) ---
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('kode_produk', $search);
                $this->db->or_like('nama_produk', $search);
                $this->db->group_end();
            }

            if ($bulan_filter == 'semua') {
                $toko_select = ($toko_filter) ? 'toko' : '"GABUNGAN" as toko';
                
                $this->db->select('kode_produk, nama_produk, tahun, "Semua Bulan" as bulan, '.$toko_select.',
                    SUM(pengunjung_produk) as pengunjung_produk, SUM(pembeli_dibuat) as pembeli_dibuat, 
                    SUM(produk_dibuat) as produk_dibuat, SUM(total_penjualan) as total_penjualan, 
                    AVG(konversi_dibuat) as konversi_dibuat, SUM(total_pembeli_siap) as total_pembeli_siap, 
                    SUM(total_produk_siap) as total_produk_siap, SUM(total_penjualan_siap) as total_penjualan_siap, 
                    AVG(tingkat_konversi_siap) as tingkat_konversi_siap, AVG(tingkat_konversi) as tingkat_konversi');
                
                $this->db->where('tahun', $tahun_filter);
                if ($toko_filter) $this->db->where('toko', $toko_filter);
                
                $this->db->group_by('kode_produk');
                
                $final_sort = (in_array($metrik, ['konversi_dibuat', 'tingkat_konversi_siap', 'tingkat_konversi'])) 
                            ? "AVG($sort_column)" : "SUM($sort_column)";
                $this->db->order_by($final_sort, $order);
            } else {
                $this->db->select('*');
                if ($bulan_filter) $this->db->where('bulan', $bulan_filter);
                if ($tahun_filter) $this->db->where('tahun', $tahun_filter);
                if ($toko_filter)  $this->db->where('toko', $toko_filter);
                
                $this->db->order_by($sort_column, $order);
            }

            // Jika user melakukan pencarian spesifik (search), kita hilangkan limit 10 
            // agar user bisa melihat perbandingan semua toko untuk produk tersebut
            if (empty($search)) {
                $this->db->limit(10);
            }

            return $this->db->get()->result();
        };

        $data['top_10']    = $build_query('DESC');
        
        // Bottom 10 biasanya tidak relevan jika user sedang mencari produk spesifik
        $data['bottom_10'] = (empty($search)) ? $build_query('ASC') : [];

        $data['filter_bulan']  = $bulan_filter;
        $data['filter_tahun']  = $tahun_filter;
        $data['filter_toko']   = $toko_filter; 
        $data['filter_metrik'] = $metrik;
        $data['search_query']  = $search; // Kirim balik ke view untuk isi input search

        $this->load->view('header', $data);
        $this->load->view('ranking', $data);
        $this->load->view('footer', $data);
    }
    public function get_autocomplete() {
        if (isset($_GET['term'])) {
            $result = $this->db->select('kode_produk, nama_produk')
                            ->like('kode_produk', $_GET['term'])
                            ->or_like('nama_produk', $_GET['term'])
                            ->group_by('kode_produk')
                            ->limit(10)
                            ->get('import');
            if ($result->num_rows() > 0) {
                foreach ($result->result() as $row) {
                    // Label adalah apa yang muncul di pilihan, value adalah apa yang masuk ke input
                    $arr_result[] = [
                        'label' => $row->kode_produk . " - " . $row->nama_produk,
                        'value' => $row->kode_produk 
                    ];
                }
                echo json_encode($arr_result);
            }
        }
    }
    
}