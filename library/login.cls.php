<?php
/**
 *
 *
 * @version $Id$
 * @copyright 2005
 **/

Class gl_login {
   var $conn;
   var $result;
   var $error_msg;
   var $user;
   var $type;
   var $dept_id;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */
   function gl_login($connection){
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

   function login($nama,$password){
      $sqlUser ='SELECT nama,password,type,dept_id FROM gl_users WHERE nama = \''.$nama.'\'';
      $sqlPass ='SELECT md5(\''.$password.'\') as pass';

      $qr_row = $this->conn->Execute($sqlUser) or die($this->conn->ErrorMsg());
      $qr_pass = $this->conn->Execute($sqlPass) or die($this->conn->ErrorMsg());

      if( $qr_row->RecordCount() == 0 ) {
         $this->error_msg = "Login Atau Password Anda Salah";
         return NULL;
      }

      $row = $qr_row->FetchRow();
      $pass = $qr_pass->FetchRow();

      if ( strcmp( $pass["pass"], $row["password"]) != 0  ) {
         $this->error_msg = "Login Atau Password Anda Salah";
         return NULL;
      } else {
         $this->user = $row["nama"];
         $this->type = $row["type"];
         $this->dept_id = $row["dept_id"];
         return true;
      }
   }
}

?>