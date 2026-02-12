<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Lottery extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Lottery_model');
    }

    public function index() {
        $data['title'] = "Lottery & Mystery Box Dashboard";
        
        // Statistik ringkas
        $data['total_dead_stock'] = $this->db->where('umur_hari >', 150)
                                             ->where('is_processed', 0)
                                             ->count_all_results('master_dead_stock');
                                             
        $data['total_in_pool']    = $this->db->where('is_active', 1)
                                             ->count_all_results('lottery_pool');
                                             
        // Menggunakan NULL coalescing ?? 0 untuk menghindari error jika data kosong
        $q_pool = $this->db->select_sum('qty_lottery')
                           ->get_where('lottery_pool', ['is_active' => 1])
                           ->row();
        $data['total_qty_pool'] = $q_pool->qty_lottery ?? 0;

        $this->load->view('header', $data);
        $this->load->view('lottery/index', $data);
        $this->load->view('footer');
    }

    public function import_excel()
    {
        if (!isset($_FILES["file_excel"]["name"])) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('lottery/master_list');
        }

        // Kosongkan tabel lama
        $this->db->empty_table('master_dead_stock');

        require_once APPPATH . '../vendor/autoload.php';

        $path = $_FILES["file_excel"]["tmp_name"];
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);

        try {
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca Excel: ' . $e->getMessage());
            redirect('lottery/master_list');
        }

        $today = time();
        $merged_data = [];

        // === CLEAN PRICE TAHAN FORMAT ===
        $clean_price = function ($val) {
            if ($val === null || $val === '' || $val === '-') return 0;

            if (is_numeric($val)) return (float)$val;

            $v = trim($val);
            $v = str_ireplace(['rp', ' '], '', $v);

            if (substr_count($v, ',') == 0 && substr_count($v, '.') > 0) {
                $v = str_replace('.', '', $v);
            } elseif (substr_count($v, ',') == 1 && substr_count($v, '.') >= 1) {
                $v = str_replace('.', '', $v);
                $v = str_replace(',', '.', $v);
            } elseif (substr_count($v, ',') > 0 && substr_count($v, '.') == 0) {
                $v = str_replace(',', '', $v);
            }

            return is_numeric($v) ? (float)$v : 0;
        };

        for ($i = 2; $i <= $highestRow; $i++) {

            $kode = strtoupper(trim($sheet->getCell('A' . $i)->getValue()));
            if (!$kode) continue;

            $nama = strtoupper(trim($sheet->getCell('B' . $i)->getValue()));
            $qty  = (int)$sheet->getCell('D' . $i)->getValue();

            $modal_unit = $clean_price($sheet->getCell('F' . $i)->getValue());
            $modal_ppn  = $clean_price($sheet->getCell('G' . $i)->getValue());

            // Validasi
            if ($qty <= 0) {
                log_message('error', "INVALID ROW {$i} | {$kode} | qty={$qty} | modal={$modal_ppn}");
                continue;
            }

            // === PROSES TANGGAL ===
            $cell_tgl = $sheet->getCell('E' . $i);
            $tgl_timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell_tgl)
                ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($cell_tgl->getValue())
                : strtotime(str_replace(['/', '.'], '-', trim($cell_tgl->getValue())));

            $umur_hari = floor(($today - $tgl_timestamp) / 86400);

            // === MERGE DATA ===
            if (isset($merged_data[$kode])) {
                $old_qty = $merged_data[$kode]['qty_asal'];
                $new_qty = $old_qty + $qty;

                $merged_data[$kode]['modal_ppn'] =
                    (($old_qty * $merged_data[$kode]['modal_ppn']) + ($qty * $modal_ppn)) / $new_qty;

                $merged_data[$kode]['modal_unit'] =
                    (($old_qty * $merged_data[$kode]['modal_unit']) + ($qty * $modal_unit)) / $new_qty;

                $merged_data[$kode]['qty_asal'] = $new_qty;
                $merged_data[$kode]['umur_hari'] = max($merged_data[$kode]['umur_hari'], $umur_hari);

            } else {
                $merged_data[$kode] = [
                    'kode_barang'  => $kode,
                    'nama_barang'  => $nama,
                    'qty_asal'     => $qty,
                    'tgl_stock'    => date('Y-m-d', $tgl_timestamp),
                    'umur_hari'    => $umur_hari,
                    'modal_unit'   => $modal_unit,
                    'modal_ppn'    => $modal_ppn,
                    'is_processed'=> 0
                ];
            }
        }

        if (!empty($merged_data)) {
            $this->db->insert_batch('master_dead_stock', array_values($merged_data));
            $this->session->set_flashdata(
                'success',
                count($merged_data) . ' produk berhasil diimport & digabung.'
            );
        } else {
            $this->session->set_flashdata('error', 'Tidak ada data valid untuk diimport.');
        }

        redirect('lottery/master_list');
    }


    public function master_list() {
        $data['title'] = "Master Dead Stock (> 150 Hari)";
        // Mengambil data yang sudah di-Merge oleh Model
        $data['items'] = $this->Lottery_model->get_dead_stock_only();
        
        $this->load->view('header', $data);
        $this->load->view('lottery/v_master_list', $data);
        $this->load->view('footer');
    }
    public function update_master_item() {
        $id = $this->input->post('id');
        $qty = $this->input->post('qty_asal');
        $modal = $this->input->post('modal_ppn');

        if ($id) {
            $data = [
                'qty_asal' => $qty,
                'modal_ppn' => $modal,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->where('id', $id);
            $update = $this->db->update('master_dead_stock', $data);

            if ($update) {
                $this->session->set_flashdata('success', 'Data stok dan harga berhasil diperbarui.');
            } else {
                $this->session->set_flashdata('error', 'Gagal memperbarui data.');
            }
        }
        redirect('lottery/master_list');
    }


    public function process_to_pool() {
        $action = $this->input->post('action');

        if ($action == 'massal') {
            $ids = $this->input->post('selected_ids'); // Sudah sinkron dengan checkbox di view
            if (empty($ids)) {
                $this->session->set_flashdata('error', 'Pilih barang terlebih dahulu!');
                redirect('lottery/master_list');
            }
            
            // Ambil kode_barang untuk grouping
            $this->db->select('kode_barang');
            $this->db->where_in('id', $ids);
            $res = $this->db->get('master_dead_stock')->result_array();
            $codes = array_column($res, 'kode_barang');

            // Ambil data detail untuk konfirmasi massal
            $this->db->select('
                MAX(id) as id, 
                kode_barang, 
                nama_barang, 
                SUM(qty_asal) as total_qty,
                (SUM(qty_asal * modal_ppn) / SUM(qty_asal)) as modal_per_unit
            ');
            $this->db->where_in('kode_barang', $codes);
            $this->db->where('is_processed', 0);
            $this->db->group_by('kode_barang'); 
            $data['items'] = $this->db->get('master_dead_stock')->result();
            
            $data['title'] = "Setting Massal Win Rate";
            $this->load->view('header', $data);
            $this->load->view('lottery/v_konfirmasi_massal', $data);
            $this->load->view('footer');

        } elseif ($action == 'single') {
            // PERBAIKAN: Ambil ID dari input hidden modal
            $id = $this->input->post('id'); 
            $qty = $this->input->post('qty_lottery');
            $rate = $this->input->post('win_rate');

            // Validasi tambahan agar tidak null
            if ($id && $qty > 0 && $rate !== NULL) {
                $process = $this->Lottery_model->move_to_pool($id, $qty, $rate);
                if ($process) {
                    $this->session->set_flashdata('success', 'Barang berhasil masuk ke Pool.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memproses ke Pool.');
                }
            } else {
                $this->session->set_flashdata('error', 'Data input tidak lengkap.');
            }
            redirect('lottery/master_list');
        }
    }

    // Method tambahan untuk menyimpan hasil setting massal
    public function save_massal_pool() {
        $ids = $this->input->post('ids'); 
        $qtys = $this->input->post('qty'); // Ambil qty dari form
        $rates = $this->input->post('rates'); // Ambil rate individu dari form

        if (empty($ids)) {
            redirect('lottery/master_list');
        }

        $success_count = 0;
        foreach ($ids as $index => $id) {
            $qty_to_pool = $qtys[$index];
            $rate_per_unit = $rates[$index];

            if ($qty_to_pool > 0) {
                // Gunakan data qty dan rate sesuai baris di tabel
                $process = $this->Lottery_model->move_to_pool($id, $qty_to_pool, $rate_per_unit);
                if ($process) $success_count++;
            }
        }

        $this->session->set_flashdata('success', "$success_count barang berhasil diproses ke pool.");
        redirect('lottery/master_list');
    }
}