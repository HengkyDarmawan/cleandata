<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Lottery extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Lottery_model');
    }

    public function index() {
        $data['title'] = "Lottery & Mystery Box Dashboard";

        // Statistik ringkas
        $data['total_dead_stock'] = $this->db
            ->where('umur_hari >', 150)
            ->where('is_processed', 0)
            ->count_all_results('master_dead_stock');

        // ambil total event
        $data['total_event'] = $this->db
            ->count_all_results('lottery_event');

        // total item di semua event
        $q_items = $this->db->select_sum('qty_lottery')
                            ->get('lottery_event_items')
                            ->row();
        $data['total_qty_event'] = $q_items->qty_lottery ?? 0;

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

        $items = $this->input->post('items');
        if (!$items) redirect('lottery/master_list');

        $ids = array_keys($items);

        $this->db->select('id, kode_barang, nama_barang, qty_asal, modal_ppn as modal');
        $this->db->where_in('id', $ids);
        $this->db->where('is_processed', 0);

        $rows = $this->db->get('master_dead_stock')->result();

        // gabungkan dengan data dari cart
        foreach ($rows as $r) {
            $r->qty   = (int) $items[$r->id]['qty'];
            $r->modal = (float) $items[$r->id]['modal'];
            $r->total = $r->qty * $r->modal;
        }

        $data['items'] = $rows;
        $data['title'] = "Setting Event Lottery";

        $this->load->view('header', $data);
        $this->load->view('lottery/v_konfirmasi_massal', $data);
        $this->load->view('footer');
    } 


    public function save_massal_pool() {

        $this->load->model('Lottery_model');

        $event_name    = $this->input->post('event_name');
        $ticket_price = $this->input->post('price_per_ticket');
        $ticket_price_manual = $this->input->post('ticket_price_manual');
        $ids   = $this->input->post('ids');
        $qtys  = $this->input->post('qty');
        $rates = $this->input->post('rates');

        if (!$ids || !$event_name) {
            redirect('lottery/master_list');
            return;
        }

        // Hitung total win rate
        $total_rate = 0;
        foreach ($rates as $i => $r) {
            $total_rate += ($r * $qtys[$i]);
        }

        if ($total_rate > 100) {
            $this->session->set_flashdata('error', 'Total win rate > 100%');
            redirect('lottery/master_list');
            return;
        }

        $this->db->trans_start();

        $event_id = $this->Lottery_model
                        ->create_event($event_name, $ticket_price, $ticket_price_manual, $total_rate);

        foreach ($ids as $i => $mid) {

            if ($qtys[$i] <= 0) continue;

            $m = $this->Lottery_model->get_master_by_id($mid);
            if (!$m) continue;

            if ($qtys[$i] > $m->qty_asal) {
                $qtys[$i] = $m->qty_asal;
            }

            $this->Lottery_model
                ->add_event_item($event_id, $m, $qtys[$i], $rates[$i]);

            // Kurangi stok (BUKAN hide)
            $this->Lottery_model
                ->reduce_stock($m->id, $qtys[$i]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal membuat event!');
        } else {
            $this->session->set_flashdata('success', 'Event berhasil dibuat!');
        }

        redirect('lottery/master_list');
    }

}