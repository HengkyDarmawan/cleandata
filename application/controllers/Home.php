<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Memanggil library PhpSpreadsheet via Composer Autoload
use PhpOffice\PhpSpreadsheet\IOFactory;

class Home extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Library database dan session sudah aktif via autoload.php
    }

    public function index() {
        // Mengambil data untuk ditampilkan di tabel view
        $data['data_import'] = $this->db->get('import')->result();
        $this->load->view('header'); // Sesuaikan jika kamu pakai template terpisah
        $this->load->view('home', $data);
        $this->load->view('footer');
    }

    public function proses_upload() {
        // Optimasi memori untuk spek RAM 16GB kamu
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        if (!isset($_FILES['excel_file']['name'])) {
            $this->session->set_flashdata('pesan', 'File tidak ditemukan!');
            redirect('home');
        }

        $file = $_FILES['excel_file']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $data_final = [];

            foreach ($sheetData as $i => $row) {
                if ($i == 1) continue; // Skip baris header Excel

                // LOGIKA KODE: Gunakan Kolom D (Kode Variasi). Jika '-', gunakan Kolom B (Nama Produk)
                $kode = trim($row['D']);
                if (empty($kode) || $kode == '-') {
                    $kode = trim($row['B']);
                }
                
                if (empty($kode)) continue;

                // SANITASI DATA: Membersihkan titik ribuan dan persen agar bisa dihitung
                $pembeli   = (int) str_replace(['.', ','], '', $row['V']);
                $produk    = (int) str_replace(['.', ','], '', $row['W']);
                $penjualan = (float) str_replace(['.', ','], '', $row['X']);
                $konversi  = (float) str_replace(['%', ','], ['', '.'], $row['Y']);

                if (isset($data_final[$kode])) {
                    // JIKA KODE SAMA: Lakukan Merge dengan menjumlahkan nilainya (SUM)
                    $data_final[$kode]['total_pembeli_siap']   += $pembeli;
                    $data_final[$kode]['total_produk_siap']    += $produk;
                    $data_final[$kode]['total_penjualan_siap'] += $penjualan;
                    
                    // Untuk konversi, kita ambil nilai terbaru atau rata-rata
                    $data_final[$kode]['tingkat_konversi_siap'] = $konversi;
                } else {
                    // JIKA KODE BARU: Tambahkan ke array
                    $data_final[$kode] = [
                        'kode_produk'           => $kode,
                        'nama_produk'           => $row['B'],
                        'total_pembeli_siap'    => $pembeli,
                        'total_produk_siap'     => $produk,
                        'total_penjualan_siap'  => $penjualan,
                        'tingkat_konversi_siap' => $konversi
                    ];
                }
            }

            if (!empty($data_final)) {
                // OPSIONAL: Kosongkan tabel sebelum simpan data baru (Replace All)
                $this->db->empty_table('import');

                // Simpan sekaligus banyak (Batch Insert)
                $this->db->insert_batch('import', array_values($data_final));
                
                $this->session->set_flashdata('pesan', 'Berhasil mengolah ' . count($data_final) . ' data unik.');
            }

        } catch (Exception $e) {
            $this->session->set_flashdata('pesan', 'Gagal: ' . $e->getMessage());
        }

        redirect('home');
    }
}