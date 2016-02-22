<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2005
 **/

require_once ('periode.cls.php');
require_once ('departemen.cls.php');
require_once ('perkiraan.cls.php');
require_once ('jurnal_entry.cls.php');

Class gl_kas {
   var $conn;
   var $type;        //"MASUK"    == masuk
                     //"KELUAR"    == keluar
   var $result;
   var $error_msg;
   var $master_id;
   var $last_id;
   var $total_transaksi;
   var $jurnal_entry;
   var $no_bukti;
   var $kode_kas;
   var $nama_kas;
   var $disetujui;
   var $bendahara;
   var $diterima;
   var $untuk;
   var $sudah_terima;
   var $tanggal_transaksi;
   var $kode_dept;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   ****************************************************************************************************/
   function gl_kas($connection,$type){
      $this->conn = $connection;
      $this->type = $type;
      $this->jurnal_entry = new jurnal_entry($this->conn);
   }

   /***************************************************************************************************
   *  set koneksi yang akan dipakai class ini
   *
   ****************************************************************************************************/
   function setConnection($connection)
   {
      $this->conn = $connection;
   }

   /***************************************************************************************************
   *  Get Error Message;
   *
   ****************************************************************************************************/
   function ErrorMsg(){
      return $this->error_msg;
   }

   /***************************************************************************************************
   *  start kasir kas
   *
   ****************************************************************************************************/
   function start($no_perkiraan, $kode_dept, $kode_job=NULL, $tanggal_transaksi,$id=NULL){
      if($this->type=="MASUK"){
         $tabel = "gl_buffer_kasmasuk";
      }else if($this->type=="KELUAR"){
         $tabel = "gl_buffer_kaskeluar";
      }

      if($id==NULL){
        $periode = new periode($this->conn);
        $perkiraan = new perkiraan($this->conn);
        $result = $perkiraan->cari($no_perkiraan);
        if(!($row_prk = $result->FetchRow()))   {
           $this->error_msg = "Kode Akun Tidak Ditemukan! Silahkan Masukkan Kode Akun Yang Lain.";
           return NULL;
        }
    
        $result = $periode->cari($tanggal_transaksi);
        if(!($row_prd = $result->FetchRow()))   {
           $this->error_msg = "Tanggal Yang Anda Masukkan Tidak Ditemukan Di Tabel Periode! Silahkan Masukkan Tanggal Lain.";
           return NULL;
        }

        $this->result = $this->jurnal_entry->start($tanggal_transaksi, $kode_dept, $kode_job);
        $this->master_id = $this->jurnal_entry->master_id;
        $this->no_bukti = $this->jurnal_entry->no_ref_baru;
        $this->kode_kas = $row_prk["no_prk"];
        $this->nama_kas = $row_prk["nama_prk"];
      }else{
         $query = sprintf("select a.* , b.no_prk, b.nama_prk from ".$tabel." a, gl_perkiraan b, gl_buffer_transaksi c where b.id_prk = a.kodekas_kke and c.id_tra = a.tra_id AND c.id_tra = %s",$id);
         $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $this->master_id = $row["tra_id"];
         $this->kode_kas = $row["no_prk"];
         $this->nama_kas = $row["nama_prk"];
         $this->disetujui = $row["disetujui_kke"];
         $this->bendahara = $row["bendahara_kke"];
         $this->diterima = $row["terimaoleh_kke"];
         $this->untuk = $row["untuk_kke"];
         $this->sudah_terima = $row["terimadari_kke"];
         $this->jurnal_entry->is_kasir = $this->type;
         $this->result = $this->jurnal_entry->start($tanggal_transaksi, $kode_dept, $kode_job, $this->master_id);
         $this->no_bukti = $this->jurnal_entry->no_ref_baru;
         $this->tanggal_transaksi = $this->jurnal_entry->tanggal_transaksi;
         $this->kode_dept = $this->jurnal_entry->kode_dept;
         $this->total_transaksi = $this->total_transaksi();
      }
      return $this->result;
   }

   /***************************************************************************************************
   *  membatalkan transaksi
   *
   ****************************************************************************************************/
   function batal(){
      $this->jurnal_entry->master_id = $this->master_id;
      $this->result = $this->jurnal_entry->batal();
      return $this->result;
   }

   /***************************************************************************************************
   *  hapus detil transaksi
   *
   ****************************************************************************************************/
   function del_detil($id_transaksidetil){
      $this->result = $this->jurnal_entry->del_detil($id_transaksidetil);
      return $this->result;
   }

   /***************************************************************************************************
   *  tambah detil transaksi
   *
   ****************************************************************************************************/
   function add_detil($no_perkiraan,$kode_dept,$kode_job,$keterangan_transaksidetil,$jumlah_transaksidetil){
      if($this->type=="MASUK"){
         $jumlah_transaksidetil = -1 * $jumlah_transaksidetil;
      }else if($this->type=="KELUAR"){
         $jumlah_transaksidetil = $jumlah_transaksidetil;
      }
      $this->jurnal_entry->master_id = $this->master_id;
      $ret = $this->jurnal_entry->add_detil($no_perkiraan, $kode_dept, $kode_job, $keterangan_transaksidetil, $jumlah_transaksidetil);
      if(!ret){
         $this->error_msg = $this->jurnal_entry->ErrorMsg();
         return NULL;
      }

      $this->total_transaksi = $this->total_transaksi();
      return $this->result;
   }

   function total_transaksi(){
      $query = sprintf("select sum(jumlah_trad) as jumlahtrans from gl.gl_transaksidetil_tmp where tra_id = '%s'",
                     $this->master_id);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $total_transaksi = $row["jumlahtrans"];
      return $total_transaksi;
   }

   /***************************************************************************************************
   *  simpan detil transaksi
   *
   ****************************************************************************************************/
   function post($namauser, $kode_dept, $kode_job=NULL, $no_bukti, $kode_perkiraan, $ket_asli, $tanggal_transaksi,$total_transaksi,
                  $sudah_terima, $untuk, $disetujui, $bendahara, $diterima,$id=NULL){
      $perkiraan = new perkiraan($this->conn);
      $departemen = new departemen($this->conn);
      $_Job = new job($this->conn);
      $result = $departemen->cari($kode_dept);
      if(!$row= $result->FetchRow()){
         $this->error_msg = " <h3>Kode departemen tidak ada!</h3>
                              <h4>Coba masukkan kode yang lainnya</h4>";
         return NULL;
      }else  $id_departemen = $row["id_dept"];
     if($kode_job!=NULL){
       $result = $_Job->cari($kode_job);
       if(!$row= $result->FetchRow()){
          $this->error_msg = " <h3>Kode Job tidak ada!</h3>
                               <h4>Coba masukkan kode yang lainnya</h4>";
          return NULL;
       }else  $id_job = $row["id_job"];
      }


      if($this->type=="MASUK"){
         $total_transaksi = $total_transaksi;
         $tabel = "gl_buffer_kasmasuk";
      }else if($this->type=="KELUAR"){
         $total_transaksi = -1 * $total_transaksi;
         $tabel = "gl_buffer_kaskeluar";
         //kasirkaskeluaredt
      }
      $this->jurnal_entry->master_id = $this->master_id;
      if($id){
         $query = sprintf("delete from %s where tra_id='%s'",$tabel, $id);
         echo $sql;
         $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $this->jurnal_entry->post($namauser, $kode_dept, $no_bukti, $ket_asli, $tanggal_transaksi, $id);
      }else{
          echo "sana";exit();
         $this->jurnal_entry->post($namauser, $kode_dept, $no_bukti, $ket_asli, $tanggal_transaksi);
      }
      //masukkan kodekas ke gl_buffer_transaksidetil
      $this->last_id = $this->jurnal_entry->last_id;
      $result = $perkiraan->cari($kode_perkiraan);
      $row = $result->FetchRow();

      $dbTable = "gl_buffer_transaksidetil";
      
      $dbField[0] = "id_trad";   // PK
      $dbField[1] = "tra_id";
      $dbField[2] = "ket_trad";
      $dbField[3] = "jumlah_trad";
      $dbField[4] = "prk_id";
      $dbField[5] = "dept_id";
      $dbField[6] = "job_id";
     
      /* DPE_NUMERIC,DPE_CHAR,DPE_DATE */

      
      if(!$tradId) $tradId = $this->conn->GetNewID("gl_buffer_transaksidetil","id_trad",DB_SCHEMA);
      $dbValue[0] = QuoteValue(DPE_NUMERIC,$tradId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$this->last_id);
      $dbValue[2] = QuoteValue(DPE_CHAR,$ket_asli);
      $dbValue[3] = QuoteValue(DPE_CHAR,$total_transaksi);
      $dbValue[4] = QuoteValue(DPE_CHAR,$row["id_prk"]);
      $dbValue[5] = QuoteValue(DPE_CHAR,$id_departemen);
      if($id_job)$dbValue[6] = QuoteValue(DPE_CHAR,$id_job);
      else $dbValue[6] = QuoteValue(DPE_NUMERIC,"null");

      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA);
      $dtmodel->Insert() or die("insert  error");	
      
      unset($dtmodel);
      unset($dbField);
      unset($dbValue);
      unset($dbKey);
      unset($tradId);

      $query = sprintf("select id_prk from gl_perkiraan where no_prk='%s'",$kode_perkiraan);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());

      if($row = $result->FetchRow()){
         $id_insert = $row["id_prk"];
      } else {
         $id_insert = 0;
      }

      $query = sprintf("insert into %s
             (id_kke, kodekas_kke, nobukti_kke, tanggal_kke, terimadari_kke, untuk_kke, tra_id, disetujui_kke, bendahara_kke, terimaoleh_kke,dept_id)
             values (%s, '%s', %s, '%s', '%s', '%s', %s, '%s', '%s', '%s', '%s')",
             $tabel,
             "nextval('id_".$tabel."')",
             $id_insert,
             $no_bukti,
             $tanggal_transaksi,
             $sudah_terima,
             $untuk,
             $this->last_id,
             $disetujui,
             $bendahara,
             $diterima,$id_departemen);
      $this->conn->Execute($query) or die($this->conn->ErrorMsg());
   }

   function hapus($id){
      if($this->type=="MASUK"){
         $tabel = "gl_buffer_kasmasuk";
      }else if($this->type=="KELUAR"){
         $tabel = "gl_buffer_kaskeluar";
      }
      $query = sprintf("select * from %s where id_kke = %s",$tabel,$id);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $this->master_id = $row["tra_id"];
      $this->jurnal_entry->hapus($this->master_id);
      $query = sprintf("delete from %s where id_kke = %s",$tabel,$id);
      $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
   }
}

?>
