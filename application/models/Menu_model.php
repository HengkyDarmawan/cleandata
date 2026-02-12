<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Menu_model extends CI_Model {

    public function get_active_menu() {
        return $this->db
            ->where('is_active', 1)
            ->order_by('sort_order', 'ASC')
            ->get('sidebar_menu')
            ->result();
    }
}