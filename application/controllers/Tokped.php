<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Tokped extends CI_Controller {

    public function index() {
        // Tangkap filter dari URL (GET), jika kosong gunakan bulan dan tahun saat ini
        $bulan_filter = $this->input->get('bulan') ?: $this->get_bulan_indo(date('m'));
        $tahun_filter = $this->input->get('tahun') ?: date('Y');

        $this->db->where('bulan', $bulan_filter);
        $this->db->where('tahun', $tahun_filter);
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
}