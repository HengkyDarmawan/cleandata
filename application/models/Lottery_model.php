<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lottery_model extends CI_Model {

    /* =======================
       MASTER DEAD STOCK
    ======================= */

    public function get_dead_stock_only() {
        return $this->db
            ->select('id, kode_barang, nama_barang, qty_asal, umur_hari, modal_ppn,
                     (qty_asal * modal_ppn) AS nilai_stock')
            ->from('master_dead_stock')
            ->where('umur_hari >', 150)
            ->where('is_processed', 0)
            ->order_by('nilai_stock', 'DESC')
            ->get()->result();
    }

    public function get_master_by_id($id) {
        return $this->db
            ->get_where('master_dead_stock', ['id' => $id])
            ->row();
    }

    /**
     * Kurangi stok. 
     * is_processed = 1 hanya jika stok = 0
     */
    public function reduce_stock($id, $used_qty) {

        $m = $this->get_master_by_id($id);
        if (!$m) return false;

        $stokBaru = $m->qty_asal - $used_qty;
        if ($stokBaru < 0) $stokBaru = 0;

        $data = ['qty_asal' => $stokBaru];

        if ($stokBaru == 0) {
            $data['is_processed'] = 1;
        }

        return $this->db
            ->where('id', $id)
            ->update('master_dead_stock', $data);
    }

    /* =======================
       EVENT LOTTERY
    ======================= */

    public function create_event($name, $ticket, $manual_price, $total_rate) {
        $this->db->insert('lottery_event', [
            'event_name'      => $name,
            'ticket_price'   => $ticket,
            'ticket_price_manual'=> $manual_price,
            'total_win_rate' => $total_rate,
            'created_at'     => date('Y-m-d H:i:s')
        ]);
        return $this->db->insert_id();
    }

    public function add_event_item($event_id, $m, $qty, $rate) {
        return $this->db->insert('lottery_event_items', [
            'event_id'     => $event_id,
            'master_id'   => $m->id,
            'kode_barang' => $m->kode_barang,
            'nama_barang' => $m->nama_barang,
            'modal_unit'  => $m->modal_ppn,
            'qty_lottery' => $qty,
            'win_rate'    => $rate,
            'total_rate'  => $qty * $rate,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
    }
}
