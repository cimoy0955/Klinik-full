<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2005
 *
 **/

Class link_gl {
   var $conn;
   var $result;
   var $error;
   var $type=NULL;
   /*
   *  type :
   *     0 = kas masuk
   *     1 = kas keluar
   *     2 = pembelian
   *     3 = penjualan
   *
   *
   */


   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */
   function link_gl($connection,$type=NULL){
      $this->conn = $connection;
      $this->type = $type;
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
   *  cari total record link
   *
   */
   function record_count($all=NULL){
      if($all){
         $query = "select count(*) as jum from gl_link";
         $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $total_row = $row["jum"];
      } else {
         $total_row = $this->result->RecordCount();
      }
      return $total_row;
   }

   /***************************************************************************************************
   *  Menambahkan link baru
   *
   */
   function add($id_perkiraan,$keterangan,$jenis_ref, $lookup){
      $id_link = $this->GetNewID("gl_link","id_link",DB_SCHEMA);
      $query = sprintf("insert into gl_link(id_link, prk_id, ket_link, jenis_ref, lookup) values(%s,'%s','%s','%s','%s')",
                        $id_link, $id_perkiraan, $keterangan, $jenis_ref, $lookup);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  update link
   *
   */
   function edit($id,$id_perkiraan,$keterangan,$jenis_ref, $lookup){
      $query = sprintf("update gl_link set prk_id = '%s', ket_link = '%s', jenis_ref = '%s', lookup= '%s'
                        where id_link = '%s'",
                        $id_perkiraan, $keterangan, $jenis_ref, $lookup, $id);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   * hapus link
   *
   */
   function delete($id){
      $query = sprintf("DELETE FROM gl_link WHERE id_link = '%s'", $id);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  cari link
   *
   */
   function cari($id=NULL){
      if($id!=NULL){
         $query = sprintf("SELECT * FROM gl_link,gl_perkiraan
                           where gl_link.prk_id = gl_perkiraan.id_prk
                           and id_link = '%s' order by id_link",$id);
      }else{
         $query = sprintf("SELECT * FROM gl_link,gl_perkiraan
                           where gl_link.prk_id = gl_perkiraan.id_prk order by id_link");
      }
      $this->result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      return $this->result;
   }

   function get_link($type){
      switch($type){
         case "KAS_MASUK":
            $jenis_ref = 0;
            break;
         case "KAS_KELUAR":
            $jenis_ref = 1;
            break;
         case "PEMBELIAN";
            $jenis_ref = 2;
            break;
         case "PENJUALAN";
            $jenis_ref = 3;
            break;
      }
      $query = sprintf("SELECT * FROM gl_link,gl_perkiraan
                        where gl_link.prk_id = gl_perkiraan.id_prk
                        and jenis_ref = %s
                        order by id_prk",$jenis_ref);
      $this->result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      return $this->result;
   }
}
?>
