<?php

class Pembayaran extends CI_Model
{
    public function __construct(){
        parent::__construct();

        date_default_timezone_set('Asia/Jakarta');
        $this->load->database();
    }

    public function check_masaBooking(){
        $sql = "SELECT setting_value FROM _setting WHERE setting_grup='config' AND setting_key='config_masabayar'";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_total(){
        $sql = "SELECT pesanan_no, pesanan_tgl ,status_id, (pesanan_subtotal+pesanan_kurir-dari_poin-kode_unik+biaya_packing) as total FROM _order WHERE kode_unik > 0 AND status_id IN (9)";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_bank_by_rek($norek){
        $sql = "SELECT bank_id FROM _bank_rekening WHERE rekening_no='$norek'";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function check($value){
        $status = false;

        $totals = $this->get_total();
        foreach($totals as $k=>$v){
            if($v["total"] == $value['amount']){
                $this->update_order($v,$value);
                $status = true;
                break;
            }else{
                // check masa booking
                $masa = $this->check_masaBooking() ;
                $masa = isset($masa[0]) ? (int) $masa[0]['setting_value'] : 24;

                $now = date('Y-m-d H:i:s');
                $pesan = $v['pesanan_tgl'];

                $snow = strtotime($now);
                $spesan = strtotime($pesan);
                $selisih = ceil( ($snow - $spesan) / (60 * 60));
                if($selisih > $masa){
                   // $this->update_order_cancel($v['pesanan_no']);
                }
            }
        }

        return $status;
    }

    public function update_order($val,$notif){
        if(isset($val['pesanan_no'])){
            // Update status
            $this->db->where('pesanan_no',$val['pesanan_no']);
            $this->db->update('_order',[
                'status_id' => 10
            ]);

            // Get Bank tujuan
            $no_rek = $notif['account_number'];
            $id_bank = $this->get_bank_by_rek($no_rek);
            $id_bank = isset($id_bank[0]) ? $id_bank[0]['bank_id'] : 0;

            // Insert Konfirmasi bayar
            $this->db->insert('_order_konfirmasi_bayar',[
                'order_pesan'       => $val['pesanan_no'],
                'jml_bayar'         => $val['total'],
                'bank_rek_tujuan'   => $id_bank,
                'bank_dari'         => 0,
                'bank_rek_dari'     => 0,
                'bank_atasnama_dari'=> '-',
                'tgl_transfer'      => $notif['created_at'],
                'status_bayar'      => 12,
                'tgl_input'         => date('Y-m-d'),
                'ip_data'           => '::1',
                'buktitransfer'     => ''
            ]);

            // Insert History Order Status
            $this->db->insert('_order_status',[
                'nopesanan'     => $val['pesanan_no'],
                'tanggal'       => $notif['created_at'],
                'keterangan'    => $notif['description'],
                'status_id'     => 10
            ]);
        }

        return true;
    }

    public function update_order_cancel($no_order=0){
        // Update status
        $this->db->where('pesanan_no',$no_order);
        $this->db->update('_order',[
            'status_id' => 14
        ]);

        // Insert History Order Status
        $this->db->insert('_order_status',[
            'nopesanan'     => $no_order,
            'tanggal'       => date('Y-m-d H:i:s'),
            'keterangan'    => 'Batal otomatis',
            'status_id'     => 14
        ]);
    }
	public function get_status_cancel(){
		$sql = "SELECT `idostatus`,`nopesanan` FROM `_order_status` WHERE `status_id`=14 AND `keterangan`='Batal Otomatis'";
	
		$query = $this->db->query($sql);
	    return $query->result_array();
	}
		
	public function get_order_cancel(){
		$cancels = [];
		$status = $this->get_status_cancel();
		foreach($status as $key => $val){
			$ids = $val['idostatus'];
			$cancels[] = $val['nopesanan'];

			// Update
			$this->db->where('idostatus',$ids);
            $this->db->update('_order_status',[
                'keterangan' => '#Batal Otomatis'
            ]);
		}
		
		$cancel = implode(',',$cancels);
        $sql = "SELECT * FROM `_order_detail` WHERE `pesanan_no` IN ($cancel)";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

	public function update_stok_cancel(){
		$cancel = $this->get_order_cancel();
		foreach($cancel as $key => $val){
			$qty = $val['jml'];
			$idproduk = $val['produk_id'];
			$idukuran = $val['ukuranid'];
			$idwarna = $val['warnaid'];

			// Get Produk
			$sql = "SELECT idopt,stok FROM `_produk_options` WHERE `idproduk`='$idproduk' AND ukuran='$idukuran' AND warna='$idwarna'";

	        $query = $this->db->query($sql);
	        $produk = $query->result_array();

			if(isset($produk[0])){
				$idopt = $produk[0]['idopt'];
				$stok = $produk[0]['stok'] + $qty;

				// Update Stock
				$this->db->where('idopt',$idopt);
	            $this->db->update('_produk_options',[
	                'stok' => $stok
	            ]);

				// Update Stock Produk
				$sqls = "SELECT jml_stok FROM `_produk` WHERE `idproduk`='$idproduk'";

		        $querys = $this->db->query($sqls);
		        $prd = $querys->result_array();

				$jstok = $prd[0]['jml_stok'] + $qty;
				
				$this->db->where('idproduk',$idproduk);
	            $this->db->update('_produk',[
	                'jml_stok' => $jstok
	            ]);
			}
			
		}
    }
}
