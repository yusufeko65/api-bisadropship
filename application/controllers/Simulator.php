<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Simulator extends CI_Controller {

    public $parameter = '/moota/webhook_notif';

    public function __construct(){
        parent::__construct();

        $this->load->library('curl');
    }
    
    public function index()
	{
		$this->load->view('simulator/transfer');
	}

    public function send_curl($json){
        $url = HOSTNAME . $this->parameter;

        $header = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic ' . base64_encode('testing:tst203')
        ];

        foreach($header as $key => $val){
            $this->curl->http_header($key,$val);
        }

        $result = $this->curl->simple_post($url,$json);
        if($result === false){
            $code = $this->curl->error_code; // int
            $error = $this->curl->error_string;

            // Information
            $info = $this->curl->info; // array
            //print_r($info);

            return $code . ' :: ' . $error;
        }

        return $result;
    }

    public function json_format($nominal){
        $array = [
            [
                'id'                => 1,
                'bank_id'           => 'Lagzqb03j42',
                'account_number'    => 1998912400,
                'bank_type'         => 'BCA',
                'date'              => date('Y-m-d'),
                'amount'            => $nominal,
                'description'       => 'testing transfer',
                'type'              => 'CR',
                'balance'           => 1000000
            ],
        ];

        return json_encode( $array );
    }

    public function transfer(){
        $data = $this->input->post('data');
        $json = $this->json_format($data);

        $result = $this->send_curl($json);

        print_r($result);
    }
}