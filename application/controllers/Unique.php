<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unique extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        // silent is gold
    }

    public function request($nominal = 0) {
        $this->load->model('tagihan');

        header("Content-Type:application/json");
        if(empty($nominal)){
            echo json_encode(
                array(
                    'status'    => false,
                    'message'   => 'Nominal Empty',
                    'data'      => 0
                )
            );
            exit;
        }

        // Check rand(50,400)
        $k = [0];
        $kode = $this->tagihan->get_kode_tagihan( $nominal );
        foreach($kode as $key => $val){
            $k[] = $val['kode_unik'];
        }

        $k = implode(',', $k);
        while( in_array( ($n = mt_rand(50,400)), array($k) ) );

        echo json_encode(
            array(
                'status'    => true,
                'message'   => 'Success',
                'data'      => $n
            )
        );
    }
}