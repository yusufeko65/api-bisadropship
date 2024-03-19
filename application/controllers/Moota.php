<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Moota extends CI_Controller {

    public $url = 'https://app.moota.co/api/v1/';

    public $accept = 'application/json';

    public $secret = '';

    public $parameter = '';

    public function __construct(){
        parent::__construct();

        $this->secret = SECRET_KEY_MOOTA;
        $this->load->library('curl');
    }

	public function index()
	{
		// silent is Gold
	}

    public function get_curl(){
        $url = $this->url . $this->parameter;
        $header = [
            'Accept'            => $this->accept,
            'Authorization'     => 'Bearer ' . $this->secret
        ];

        foreach($header as $key => $val){
            $this->curl->http_header($key,$val);
        }

        $result = $this->curl->simple_get($url);
        return $result;
    }

    public function profile(){
        $this->parameter = 'profile';
        $this->get_curl();
    }

    public function balance(){
        $this->parameter = 'balance';
        $this->get_curl();
    }

    public function bank(){
        $this->parameter = 'bank';
        $this->get_curl();
    }

    public function bank_detail($id=0){
        $this->parameter = 'bank/' . $id;
        $this->get_curl();
    }

    public function mutation($id=0){
        $this->parameter = 'bank/' . $id . '/mutation';
        $this->get_curl();
    }

    public function mutation_last($id=0,$data=10){
        $this->parameter = 'bank/' . $id . '/mutation/recent/' . $data;
        $this->get_curl();
    }

    public function mutation_search_amount($id=0,$amount=10){
        $this->parameter = 'bank/' . $id . '/mutation/search/' . $amount;
        $this->get_curl();
    }

    public function mutation_search_description($id=0,$description=10){
        $this->parameter = 'bank/' . $id . '/mutation/search/description/' . $description;
        $this->get_curl();
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
