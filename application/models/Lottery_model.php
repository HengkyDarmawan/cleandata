<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lottery_model extends CI_Model {

    public function get_dead_stock_only() {
        $this->db->select('id, kode_barang, nama_barang, qty_asal, umur_hari, modal_ppn,
            (qty_asal * modal_ppn) AS nilai_stock');
        $this->db->from('master_dead_stock');
        $this->db->where('umur_hari >', 150);
        $this->db->where('is_processed', 0);
        $this->db->order_by('nilai_stock', 'DESC');
        return $this->db->get()->result();
    }
    
    public function move_to_pool($id, $qty_lottery, $rate) {
        $master = $this->db->get_where('master_dead_stock', ['id' => $id])->row();
        if (!$master) return false;

        $data = array(
            'master_id'    => $master->id,
            'kode_barang'  => $master->kode_barang,
            'nama_barang'  => $master->nama_barang,
            'qty_lottery'  => $qty_lottery,
            'modal_unit'   => $master->modal_ppn, 
            'win_rate'     => $rate,
            'is_active'    => 1,
            'type'         => 'item'
        );

        $this->db->trans_start();
        $this->db->insert('lottery_pool', $data);
        
        // Update master: tandai sudah diproses
        $this->db->where('id', $id);
        $this->db->update('master_dead_stock', ['is_processed' => 1]);
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
    public function update_stock($id, $new_qty) {
        $this->db->where('id', $id);
        return $this->db->update('master_dead_stock', ['qty_asal' => $new_qty]);
    }
    
}