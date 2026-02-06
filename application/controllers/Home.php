<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Home extends CI_Controller {

    public function index() {
        // Tangkap filter dari URL (GET), jika kosong gunakan bulan dan tahun saat ini
        $bulan_filter = $this->input->get('bulan') ?: $this->get_bulan_indo(date('m'));
        $tahun_filter = $this->input->get('tahun') ?: date('Y');

        $this->db->where('bulan', $bulan_filter);
        $this->db->where('tahun', $tahun_filter);
        $data['title'] = "Rangking Hero Product";
        $data['data_import'] = $this->db->get('import')->result();

        // Kirim variabel filter ke view agar select option tetap 'terpilih'
        $data['filter_bulan'] = $bulan_filter;
        $data['filter_tahun'] = $tahun_filter;

        $this->load->view('header', $data);
        $this->load->view('home', $data);
        $this->load->view('footer', $data);
    }

    // Helper untuk mengubah angka bulan ke nama bulan Indonesia (sesuai database Anda)
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

        $input_bulan = $this->input->post('bulan');
        $input_tahun = $this->input->post('tahun');

        $file = $_FILES['excel_file']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $data_final = [];

            foreach ($sheetData as $i => $row) {
                if ($i == 1) continue; 

                $kode = trim($row['A']);
                if (empty($kode)) continue;

                // KUNCI BARU: Gabungkan Kode + Bulan + Tahun agar unik per periode
                $merge_key = $kode . '-' . $input_bulan . '-' . $input_tahun;

                $clean = [
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
                    // Jika produk, bulan, dan tahun SAMA, maka SUM
                    $data_final[$merge_key]['pengunjung_produk']    += $clean['pengunjung_produk'];
                    $data_final[$merge_key]['pembeli_dibuat']       += $clean['pembeli_dibuat'];
                    $data_final[$merge_key]['produk_dibuat']        += $clean['produk_dibuat'];
                    $data_final[$merge_key]['total_penjualan']      += $clean['total_penjualan'];
                    $data_final[$merge_key]['total_pembeli_siap']   += $clean['total_pembeli_siap'];
                    $data_final[$merge_key]['total_produk_siap']    += $clean['total_produk_siap'];
                    $data_final[$merge_key]['total_penjualan_siap'] += $clean['total_penjualan_siap'];
                } else {
                    // Jika berbeda (beda produk atau beda bulan/tahun), maka pisahkan jadi baris baru
                    $data_final[$merge_key] = array_merge([
                        'kode_produk' => $kode,
                        'nama_produk' => $row['B'],
                    ], $clean);
                }
            }

            if (!empty($data_final)) {
                // JANGAN gunakan empty_table() jika ingin menyimpan data bulan-bulan sebelumnya
                $this->db->insert_batch('import', array_values($data_final));
                $this->session->set_flashdata('pesan', "Data berhasil diunggah untuk periode $input_bulan $input_tahun.");
            }

        } catch (Exception $e) {
            $this->session->set_flashdata('pesan', 'Gagal: ' . $e->getMessage());
        }
        redirect('home');
    }
    // public function ranking() {
    //     $bulan_filter = $this->input->get('bulan');
    //     $tahun_filter = $this->input->get('tahun');
    //     $metrik       = $this->input->get('metrik');

    //     // Menentukan kolom pengurutan berdasarkan pilihan user
    //     $allowed_metrics = [
    //         'pengunjung_produk'    => 'pengunjung_produk',
    //         'pembeli_dibuat'       => 'pembeli_dibuat',
    //         'total_penjualan'      => 'total_penjualan',
    //         'konversi_dibuat'      => 'konversi_dibuat',
    //         'total_pembeli_siap'   => 'total_pembeli_siap',
    //         'total_produk_siap'    => 'total_produk_siap',
    //         'total_penjualan_siap' => 'total_penjualan_siap',
    //         'tingkat_konversi_siap'=> 'tingkat_konversi_siap',
    //         'tingkat_konversi'     => 'tingkat_konversi'
    //     ];

    //     $sort_column = (isset($allowed_metrics[$metrik])) ? $allowed_metrics[$metrik] : 'total_penjualan_siap';

    //     if ($bulan_filter == 'semua') {
    //         // MODE PER TAHUN: Akumulasi semua bulan
    //         $this->db->select('kode_produk, nama_produk, tahun, 
    //             "Semua Bulan" as bulan, 
    //             SUM(pengunjung_produk) as pengunjung_produk, 
    //             SUM(pembeli_dibuat) as pembeli_dibuat, 
    //             SUM(produk_dibuat) as produk_dibuat, 
    //             SUM(total_penjualan) as total_penjualan, 
    //             AVG(konversi_dibuat) as konversi_dibuat, 
    //             SUM(total_pembeli_siap) as total_pembeli_siap, 
    //             SUM(total_produk_siap) as total_produk_siap, 
    //             SUM(total_penjualan_siap) as total_penjualan_siap, 
    //             AVG(tingkat_konversi_siap) as tingkat_konversi_siap, 
    //             AVG(tingkat_konversi) as tingkat_konversi');
    //         $this->db->from('import');
    //         $this->db->where('tahun', $tahun_filter);
    //         $this->db->group_by('kode_produk');
            
    //         // Pengurutan untuk hasil agregat (SUM/AVG)
    //         if ($metrik == 'konversi_dibuat' || $metrik == 'tingkat_konversi_siap' || $metrik == 'tingkat_konversi') {
    //             $this->db->order_by('AVG('.$sort_column.')', 'DESC');
    //         } else {
    //             $this->db->order_by('SUM('.$sort_column.')', 'DESC');
    //         }
    //     } else {
    //         // MODE PER BULAN: Tampilkan data spesifik
    //         $this->db->select('*');
    //         $this->db->from('import');
    //         if ($bulan_filter) $this->db->where('bulan', $bulan_filter);
    //         if ($tahun_filter) $this->db->where('tahun', $tahun_filter);
    //         $this->db->order_by($sort_column, 'DESC');
    //     }

    //     $data['data_import']  = $this->db->get()->result();
    //     $data['filter_bulan'] = $bulan_filter;
    //     $data['filter_tahun'] = $tahun_filter;
    //     $data['filter_metrik']= $metrik;

    //     $this->load->view('header', $data);
    //     $this->load->view('ranking', $data);
    //     $this->load->view('footer', $data);
    // }
    public function ranking() {
        $bulan_filter = $this->input->get('bulan');
        $tahun_filter = $this->input->get('tahun');
        $metrik       = $this->input->get('metrik');

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

        // --- FUNGSI HELPER QUERY ---
        $build_query = function($order) use ($bulan_filter, $tahun_filter, $sort_column, $metrik) {
            if ($bulan_filter == 'semua') {
                $this->db->select('kode_produk, nama_produk, tahun, "Semua Bulan" as bulan, 
                    SUM(pengunjung_produk) as pengunjung_produk, SUM(pembeli_dibuat) as pembeli_dibuat, 
                    SUM(produk_dibuat) as produk_dibuat, SUM(total_penjualan) as total_penjualan, 
                    AVG(konversi_dibuat) as konversi_dibuat, SUM(total_pembeli_siap) as total_pembeli_siap, 
                    SUM(total_produk_siap) as total_produk_siap, SUM(total_penjualan_siap) as total_penjualan_siap, 
                    AVG(tingkat_konversi_siap) as tingkat_konversi_siap, AVG(tingkat_konversi) as tingkat_konversi');
                $this->db->from('import');
                $this->db->where('tahun', $tahun_filter);
                $this->db->group_by('kode_produk');
                
                $final_sort = (in_array($metrik, ['konversi_dibuat', 'tingkat_konversi_siap', 'tingkat_konversi'])) 
                            ? "AVG($sort_column)" : "SUM($sort_column)";
                $this->db->order_by($final_sort, $order);
            } else {
                $this->db->select('*');
                $this->db->from('import');
                if ($bulan_filter) $this->db->where('bulan', $bulan_filter);
                if ($tahun_filter) $this->db->where('tahun', $tahun_filter);
                $this->db->order_by($sort_column, $order);
            }
            return $this->db->limit(10)->get()->result();
        };

        // Ambil 10 Terbaik (Top 10)
        $data['top_10'] = $build_query('DESC');
        
        // Ambil 10 Terburuk (Bottom 10)
        $data['bottom_10'] = $build_query('ASC');

        $data['filter_bulan'] = $bulan_filter;
        $data['filter_tahun'] = $tahun_filter;
        $data['filter_metrik'] = $metrik;

        $this->load->view('header', $data);
        $this->load->view('ranking', $data);
        $this->load->view('footer', $data);
    }
}