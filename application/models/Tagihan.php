<?php

class Tagihan extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

    public function get_kode_tagihan($nominal){
        $sql = "SELECT kode_unik FROM _order WHERE pesanan_subtotal='$nominal' AND status_id IN (9,17)";

        $query = $this->db->query($sql);
        return $query->result_array();
    }
}