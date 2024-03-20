<?php

class Pembayaran extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

    public function get_total(){
        $sql = "SELECT pesanan_no, status_id, (pesanan_subtotal+pesanan_kurir-dari_poin-kode_unik+biaya_packing) as total FROM _order WHERE status_id IN (9,17)";

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
                'tanggal'       => date('Y-m-d H:i:s'),
                'status_id'     => 10
            ]);
        }

        return true;
    }
}