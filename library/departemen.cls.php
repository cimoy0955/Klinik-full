<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2005
 **/


Class departemen {
   var $conn;
   var $result;
   var $error_msg;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */
   function departemen($connection){
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
   *  cari total record departemen
   *
   */
   function record_count($all=NULL){
      if($all){
         $query = "select count(*) as jum from gl_departemen";
         $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $total_row = $row["jum"];
      } else {
         $total_row = $this->result->RecordCount();
      }
      return $total_row;
   }

   /***************************************************************************************************
   *  cari departemen
   *
   */
   function cari($kode_departemen){
      $query = sprintf("select * from gl_departemen where kode_dept = '%s'",
                        $kode_departemen);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   function cari_by_id($id_departemen){
      $query = sprintf("select * from gl_departemen where id_dept = '%s'",
                        $id_departemen);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   function get_kode($id_departemen){
      $query = sprintf("select kode_dept from gl_departemen where id_dept = '%s'",
                        $id_departemen);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
     
      return $this->result;
   }



   /***************************************************************************************************
   *  tambah departemen
   *
   */
   function _add($id_departemen,$kode_departemen,$nama_departemen,$keterangan_departemen,$id_prk){
      //check apakah kode_perkiraan sudah ada
      $query = sprintf("SELECT count(*) as jum FROM gl_departemen WHERE kode_dept = '%s'",$kode_departemen);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
      $row = $result->FetchRow();
      $jumlah = $row["jum"];
      if ($jumlah==0) {
         $query = sprintf("insert into gl_departemen(id_dept, kode_dept, nama_dept, ket_dept, id_prk)
                          values('%s','%s','%s','%s','%s')",
                          $id_departemen,$kode_departemen,$nama_departemen,$keterangan_departemen,$id_prk);
         $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
         return $this->result;
      } else {
         $this->error_msg = "No departemen sudah ada.";
         return NULL;
      }
   }

   /***************************************************************************************************
   *  tambah departemen baru (selevel)
   *
   */
   function add($id,$kode_departemen,$nama_departemen,$keterangan_departemen,$id_prk){
      $id_parent = substr($id,0,-2);
      $len = strlen($id);

      $query = sprintf("select id_dept from gl_departemen where id_dept like '%s%%' and length(id_dept) = '%s'
                        order by id_dept desc",$id_parent,$len);
      $result = $this->conn->Query($query,1) or die($this->conn->ErrorMsg());
      if($result->RecordCount()){
         $row = $result->FetchRow();
         $jumlah = substr($row["id_dept"],-2)+1;
      }else $jumlah = 1;
      $id_skr = $id_parent.str_pad($jumlah,2,"0",STR_PAD_LEFT);
      $this->result = $this->_add($id_skr, $kode_departemen,$nama_departemen,$keterangan_departemen,$id_prk);

      return $this->result;
   }

   /***************************************************************************************************
   *  tambah departemen baru (anak)
   *
   */
   function add_child($id,$kode_departemen,$nama_departemen,$keterangan_departemen,$id_prk){
      $len = strlen($id)+2;
      $query = sprintf("SELECT id_dept from gl_departemen where id_dept like '%s%%' and length(id_dept)  = %s
                        order by id_dept desc",$id,$len);
      $result = $this->conn->Query($query,1) or die($this->conn->ErrorMsg());

      if($result->RecordCount()){
         $row = $result->FetchRow();
         $jumlah = substr($row["id_dept"],-2)+1;
      }else $jumlah = 1;

      $id_skr = $id.str_pad($jumlah,2,"0",STR_PAD_LEFT);
      $this->result = $this->_add($id_skr, $kode_departemen,$nama_departemen,$keterangan_departemen,$id_prk);

      return $this->result;
   }

   /***************************************************************************************************
   *  edit record $id
   *
   */
   function edit($id,$kode_departemen,$nama_departemen,$keterangan_departemen,$id_prk){
      $query = sprintf(" update gl_departemen set kode_dept = '%s', nama_dept = '%s', ket_dept = '%s', id_prk = '%s' where id_dept = '%s'",$kode_departemen,$nama_departemen,$keterangan_departemen,$id_prk,$id);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /*********************************************************************************
   *    hapus record $id
   *
   */
   function delete($id){
      //check apakah punya anak
      $query = sprintf("select count(*) as jum from gl_departemen where id_dept like '%s%%'",$id);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $jum = $row["jum"];

      if ($jum==1){
         //check apakah perkiraan pernah melakukan transaksi
         $query = sprintf("select count(*) as jum from gl_transaksidetil where dept_id like '%s'",$id);
         $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $jum = $row["jum"];
         if ($jum == 0){
            $query = sprintf("delete from gl_departemen where id_dept = '%s'",$id);
            $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
            return $this->result;
         } else {
            $this->error_msg = '<b>departemen tidak bisa dihapus karena pernah bertransaksi!</b><br><br>';
            return NULL;
         }
      } else {
        $this->error_msg = '<b>Departemen tidak bisa dihapus karena punya anak!</b><br><br>';
        return NULL;
      }
   }

   function can_delete($id){
      //check apakah punya anak
      $query = sprintf("select count(*) as jum from gl_departemen where id_dept like '%s%%'",$id);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $jum = $row["jum"];

      if ( $jum == 1 ) return true;
      else return false;
   }

}


?>
