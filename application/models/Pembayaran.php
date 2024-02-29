<?php

class Pembayaran extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

    public function get_total(){
        $sql = "SELECT pesanan_no, status_id, (pesanan_subtotal+pesanan_kurir-dari_poin-kode_unik) as total FROM _order WHERE status_id IN (9,17)";

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
            }
        }

        return $totals;
    }

    public function update_order($val,$notif){
        if(isset($val['pesanan_no'])){
            // Update status
            $this->db->where('pesanan_no',$val['pesanan_no']);
            $this->db->update('_order',[
                'status_id' => 10
            ]);

            // Insert Konfirmasi bayar
            $this->db->insert('_order_konfirmasi_bayar',[
                'order_pesan'       => $val['pesanan_no'],
                'jml_bayar'         => $val['total'],
                'bank_rek_tujuan'   => 0,
                'bank_dari'         => $notif['bank_type'],
                'bank_rek_dari'     => $notif['account_number'],
                'bank_atasnama_dari'=> '-',
                'tgl_transfer'      => $notif['date'],
                'status_bayar'      => 12,
                'tgl_input'         => date('Y-m-d'),
                'ip_data'           => '::1',
                'buktitransfer'     => ''
            ]);

            // Insert History Order Status
            $this->db->insert('_order_status',[
                'nopesanan'     => $val['pesanan_no'],
                'tanggal'       => date('Y-m-d H:i:s'),
                'status_id'     => 10
            ]);
        }

        return true;
    }
}