<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2005
 **/
require_once('./classes/departemen.cls.php');

Class users {
   var $conn;
   var $result;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */
   function users($connection){
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
   *  cari total record user
   *
   */
   function record_count($all=NULL){
      if($all){
         $query = "select count(*) as jum from gl_users";
         $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $total_row = $row["jum"];
      } else {
         $total_row = $this->result->RecordCount();
      }
      return $total_row;
   }

   /***************************************************************************************************
   *  Menambahkan user baru
   *
   */
   function add($nama,$password,$type,$kode_dept){
      $departemen = new departemen($this->conn);
      $result = $departemen->cari($kode_dept);
      $row = $result->FetchRow();
      $dept_id = $row["id_dept"];
      $query = sprintf("insert into gl_users(nama, password, type, dept_id) values('%s',md5('%s'),'%s','%s')",
                        $nama,$password,$type,$dept_id);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  update user
   *
   */
   function edit($nama,$password,$type,$kode_dept){
      $departemen = new departemen($this->conn);
      $result = $departemen->cari($kode_dept);
      $row = $result->FetchRow();
      $dept_id = $row["id_dept"];
      $query = sprintf(" update gl_users set password = md5('%s'), type = '%s', dept_id='%s' where nama = '%s'",
                        $password, $type, $dept_id, $nama);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   * hapus user
   *
   */
   function delete($nama){
      $query = sprintf("DELETE FROM gl_users WHERE nama = '%s'", $nama);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  cari user
   *
   */
   function cari($nama){
      $query = sprintf("SELECT * FROM gl_users where nama = '%s'",$nama);
      $this->result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      return $this->result;
   }
}
?>