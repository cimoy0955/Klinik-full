<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2005
 **/

Class inventori {
   var $conn;
   var $result;
   var $error;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */
   function inventori($connection){
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
      $id_link = $this->conn->GetNewID("gl_link","id_link",DB_SCHEMA);
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
}
?>
