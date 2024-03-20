<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Moota extends CI_Controller {

    public $url = 'https://app.moota.co/api/v1/';

    public $accept = 'application/json';

    public $secret = '';

    public $parameter = '';

    public $token = 'c3VzYW50MCNiNHNtYWxsYWhAMjgwMzE5OTU';

    public function __construct(){
        parent::__construct();

        $this->secret = SECRET_KEY_MOOTA;
        $this->load->library('curl');
    }

	public function index()
	{
		// silent is Gold
	}

    public function get_curl($token){
        if($token != $this->token){
            return false;
        }

        $url = $this->url . $this->parameter;
        $header = [
            'Accept'            => $this->accept,
            'Authorization'     => 'Bearer ' . $this->secret
        ];

        foreach($header as $key => $val){
            $this->curl->http_header($key,$val);
        }

        $result = $this->curl->simple_get($url);
        return json_decode($result,true);
    }

    public function profile($token){
        $this->parameter = 'profile';
        return $this->get_curl($token);
    }

    public function balance($token){
        $this->parameter = 'balance';
        return $this->get_curl($token);
    }

    public function bank($token){
        $this->parameter = 'bank';
        return $this->get_curl($token);
    }

    public function bank_detail($token,$id=0){
        $this->parameter = 'bank/' . $id;
        return $this->get_curl($token);
    }

    public function mutation($token,$id=0){
        $this->parameter = 'bank/' . $id . '/mutation';
        return $this->get_curl($token);
    }

    public function mutation_last($token,$id=0,$data=10){
        $this->parameter = 'bank/' . $id . '/mutation/recent/' . $data;
        return $this->get_curl($token);
    }

    public function mutation_search_amount($token,$id=0,$amount=10){
        $this->parameter = 'bank/' . $id . '/mutation/search/' . $amount;
        return $this->get_curl($token);
    }

    public function mutation_search_description($token,$id=0,$description=10){
        $this->parameter = 'bank/' . $id . '/mutation/search/description/' . $description;
        return $this->get_curl($token);
    }

    public function check_payment($token){
        $banks = $this->bank($token);

        foreach($banks['data'] as $key => $val){
            $bank_id = $val['bank_id'];
            
            $mutations = $this->mutation_last($token,$bank_id);
            foreach($mutations as $ky => $vl){
                $check = $this->pembayaran->check($vl);
                if($check) break; 
            }
        }
    }

    public function webhook_notif(){
        header("Content-Type:application/json");

         //cek user password 
        // if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) { 

        //     header('HTTP/1.1 401 Unauthorized'); 
        //     header('WWW-Authenticate: Basic realm="My Realm"'); 
        //     echo '{"error":"No access"}';
        //     exit(); 
        // }
        
        $notifications = json_decode( file_get_contents("php://input"), true );

        if(empty($notifications)){
            return '';
        }

        if(!is_array($notifications)) {
            $notifications = json_decode( $notifications );
        }
        
        if( count($notifications) > 0 ) {
            $this->load->model('pembayaran');

            foreach( $notifications as $notification) {
                $check = $this->pembayaran->check($notification);
                if($check) break; 
            }
        }
    }
}
