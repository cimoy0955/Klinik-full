<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2005
 **/


Class job {
   var $conn;
   var $result;
   var $error_msg;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */
   function job($connection){
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
   *  cari total record job
   *
   */
   function record_count($all=NULL){
      if($all){
         $query = "select count(*) as jum from gl_job";
         $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $total_row = $row["jum"];
      } else {
         $total_row = $this->result->RecordCount();
      }
      return $total_row;
   }

   /***************************************************************************************************
   *  cari job
   *
   */
   function cari($kode_job){
      $query = sprintf("select * from gl_job where kode_job = '%s'",
                        $kode_job);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   function cari_by_id($id_job){
      $query = sprintf("select * from gl_job where id_job = '%s'",
                        $id_job);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   function get_kode($id_job){
      $query = sprintf("select kode_job from gl_job where id_job = '%s'",
                        $id_job);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
     
      return $this->result;
   }



   /***************************************************************************************************
   *  tambah job
   *
   */
   function _add($id_job,$kode_job,$nama_job,$keterangan_job){
      //check apakah kode_perkiraan sudah ada
      $query = sprintf("SELECT count(*) as jum FROM gl_job WHERE kode_job = '%s'",$kode_job);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
      $row = $result->FetchRow();
      $jumlah = $row["jum"];
      if ($jumlah==0) {
         $query = sprintf("insert into gl_job(id_job, kode_job, nama_job, ket_job)
                          values('%s','%s','%s','%s')",
                          $id_job,$kode_job,$nama_job,$keterangan_job);
         $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
         return $this->result;
      } else {
         $this->error_msg = "No job sudah ada.";
         return NULL;
      }
   }

   /***************************************************************************************************
   *  tambah job baru (selevel)
   *
   */
   function add($id,$kode_job,$nama_job,$keterangan_job){
      $id_parent = substr($id,0,-2);
      $len = strlen($id);

      $query = sprintf("select id_job from gl_job where id_job like '%s%%' and length(id_job) = '%s'
                        order by id_job desc",$id_parent,$len);
      $result = $this->conn->Query($query,1) or die($this->conn->ErrorMsg());
      if($result->RecordCount()){
         $row = $result->FetchRow();
         $jumlah = substr($row["id_job"],-2)+1;
      }else $jumlah = 1;
      $id_skr = $id_parent.str_pad($jumlah,2,"0",STR_PAD_LEFT);
      $this->result = $this->_add($id_skr, $kode_job,$nama_job,$keterangan_job);

      return $this->result;
   }

   /***************************************************************************************************
   *  tambah job baru (anak)
   *
   */
   function add_child($id,$kode_job,$nama_job,$keterangan_job){
      $len = strlen($id)+2;
      $query = sprintf("SELECT id_job from gl_job where id_job like '%s%%' and length(id_job)  = %s
                        order by id_job desc",$id,$len);
      $result = $this->conn->Query($query,1) or die($this->conn->ErrorMsg());

      if($result->RecordCount()){
         $row = $result->FetchRow();
         $jumlah = substr($row["id_job"],-2)+1;
      }else $jumlah = 1;

      $id_skr = $id.str_pad($jumlah,2,"0",STR_PAD_LEFT);
      $this->result = $this->_add($id_skr, $kode_job,$nama_job,$keterangan_job);

      return $this->result;
   }

   /***************************************************************************************************
   *  edit record $id
   *
   */
   function edit($id,$kode_job,$nama_job,$keterangan_job){
      $query = sprintf(" update gl_job set kode_job = '%s', nama_job = '%s', ket_job = '%s' 
                        where id_job = '%s'",$kode_job,$nama_job,$keterangan_job,$id);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /*********************************************************************************
   *    hapus record $id
   *
   */
   function delete($id){
      //check apakah punya anak
      $query = sprintf("select count(*) as jum from gl_job where id_job like '%s%%'",$id);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $jum = $row["jum"];

      if ($jum==1){
         //check apakah perkiraan pernah melakukan transaksi
         $query = sprintf("select count(*) as jum from gl_transaksidetil where job_id like '%s'",$id);
         $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $jum = $row["jum"];
         if ($jum == 0){
            $query = sprintf("delete from gl_job where id_job = '%s'",$id);
            $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
            return $this->result;
         } else {
            $this->error_msg = '<b>Job tidak bisa dihapus karena pernah bertransaksi!</b><br><br>';
            return NULL;
         }
      } else {
        $this->error_msg = '<b>Job tidak bisa dihapus karena punya anak!</b><br><br>';
        return NULL;
      }
   }

   function can_delete($id){
      //check apakah punya anak
      $query = sprintf("select count(*) as jum from gl_job where id_job like '%s%%'",$id);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $jum = $row["jum"];

      if ( $jum == 1 ) return true;
      else return false;
   }

}


?>
