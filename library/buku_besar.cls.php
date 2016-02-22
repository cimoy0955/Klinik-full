<?php
require_once('perkiraan.cls.php');
require_once('departemen.cls.php');
require_once('periode.cls.php');
//require_once('./ers_db.inc.php');


/**
 *
 *
 * @version $Id$
 * @copyright 2005
 **/

Class buku_besar {
   var $conn;
   var $result;
   var $error;
   var $id_perkiraan;
   var $nomor_perkiraan;
   var $nama_perkiraan;
   var $is_perkiraan_aktif;
   var $id_departemen;
   var $nama_departemen;
   var $nama_periode;
   var $awal_periode;
   var $akhir_periode;
   var $sdpi;
   var $sdbi;
   var $saldo_sekarang;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */
   function buku_besar($connection){
      $this->conn = $connection;
   }

   /***************************************************************************************************
   *  set koneksi yang akan dipakai class ini
   *
   */
   function setConnection($connection)
   {
      $this->conn = $connection;
   }

   /***************************************************************************************************
   *  Get Error Message;
   *
   */
   function ErrorMsg(){
      return $this->error_msg;
   }

   /***************************************************************************************************
   *  get buku besar
   *
   */
   function get_data($nomor_perkiraan,$kode_dept,$tanggal_transaksi,$sementara=NULL){
      $perkiraan = new perkiraan($this->conn);
      $departemen = new departemen($this->conn);
      if($sementara==NULL)
         $tabel = "gl_transaksi";
      else
         $tabel = "gl_buffer_transaksi";

      //perkiraan
      $result = $perkiraan->cari($nomor_perkiraan);
      $row = $result->FetchRow();
      $jum = $result->RecordCount();

      if($jum == 0 ){
         $this->error = "<h3> Nomor Perkiraan Tidak ditemukan </h3>";
         return NULL;
      }
      $this->nomor_perkiraan = $row["no_prk"];
      $this->nama_perkiraan = $row["nama_prk"];
      $this->id_perkiraan = $row["id_prk"];
      $this->is_perkiraan_aktif = $row["isakt_prk"];

      //departemen
      $result = $departemen->cari($kode_dept);
      $row = $result->FetchRow();
      $jum = $result->RecordCount();
      if($jum == 0 ){
         $this->error = "<h3> Kode Departemen Tidak ditemukan </h3>";
         return NULL;
      }
      $this->nama_departemen = $row["nama_dept"];
      $this->id_departemen = $row["id_dept"];

      //periode
      $array = explode("-", $tanggal_transaksi);
      $bulan = $array[1];
      $tahun = $array[0];
      $tanggal = $array[2];
      $periode = new periode($this->conn);
      $result = $periode->cari($tanggal_transaksi);
      $row = $result->FetchRow();
      $this->nama_periode = $row["nama_prd"];
      $this->awal_periode = $row["awal_prd"];
      $this->akhir_periode = $row["akhir_prd"];

      if (substr($this->id_perkiraan,0,2) >= "51" ){
         $query_add = sprintf(" and ref_tra <> '-1' and ref_tra <> '-2' and tanggal_tra >=  '%s'",
                           $this->awal_periode);
         $query_add1 = " and ref_tra <> '-1' and ref_tra <> '-2'";
      }

    $findDep = $this->id_departemen."%";
      $query = sprintf("SELECT sum(jumlah_trad) as saldo FROM ".$tabel.",".$tabel."detil
                        WHERE id_tra = tra_id AND prk_id = '%s' AND ".$tabel.".dept_id like '%s'
                        AND tanggal_tra <= '%s'",
                        $this->id_perkiraan,$findDep,$this->akhir_periode);
      $query .= $query_add;

      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $this->sdpi = $row["saldo"];

      //$ADODBYear = $this->conn->SQLDate('Y','tanggal_tra');
      //$ADODBMonth = $this->conn->SQLDate('m','tanggal_tra');
      //$filter_tgl = filter_mysqldate($tanggal, $bulan, $tahun);

      //if ($filter_tgl == "") $filter_tgl = "1 = 1";
      
      $filter_tgl = sprintf("tanggal_tra <= '%s'", $tanggal_transaksi);;

      //echo $filter_tgl;
 
      $query = sprintf("SELECT SUM(jumlah_trad) AS saldo
                        FROM ".$tabel.",".$tabel."detil WHERE id_tra = tra_id
                        AND prk_id = '%s' AND ".$tabel.".dept_id like '%s'
                        AND %s",
                        $this->id_perkiraan, $findDep,
                        $filter_tgl);

      /*$query = sprintf("SELECT SUM(jumlah_trad) AS saldo
                        FROM ".$tabel.",".$tabel."detil WHERE id_tra = tra_id
                        AND prk_id = '%s' AND ".$tabel.".dept_id = '%s'
                        AND (( %s <= %s AND  %s = %s ) OR %s < %s)",
                        $this->id_perkiraan, $this->id_departemen,
                        $ADODBMonth, $bulan, $ADODBYear, $tahun, $ADODBYear,$tahun);
      */
      $query .= $query_add;
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $this->sdbi = $row["saldo"];

      if (substr($prk['id_prk'],0,2) < "51" ) {
        $query = sprintf("SELECT SUM(jumlah_trad) AS saldo FROM ".$tabel.",".$tabel."detil
                          WHERE id_tra = tra_id AND prk_id = '%s' AND ".$tabel.".dept_id like '%s'
                          AND tanggal_tra < '%s'",
                          $this->id_perkiraan,$findDep,$this->awal_periode);
        $query .= $query_add;
        $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
        $row = $result->FetchRow();
        $this->saldo_sekarang = $row["saldo"];
      }

      $query = sprintf("SELECT tanggal_tra, ref_tra, ket_tra, no_prk, jumlah_trad
                        FROM gl_perkiraan, ".$tabel.", ".$tabel."detil
                        WHERE id_prk = prk_id AND id_tra = tra_id AND tanggal_tra >= '%s'
                              AND tanggal_tra <= '%s' AND prk_id = '%s' AND ".$tabel.".dept_id like'%s'
                        ORDER BY tanggal_tra, ref_tra",
                        $this->awal_periode, $this->akhir_periode, $this->id_perkiraan, $findDep );
      $this->result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());

      return $this->result;
   }
}
?>
