<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public $menu;

    public function __construct() {
        parent::__construct();

        // load model menu
        $this->load->model('Menu_model');

        // ambil menu aktif
        $this->menu = $this->Menu_model->get_active_menu();
    }
}
