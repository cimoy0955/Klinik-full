<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2005
 **/
require_once('perkiraan.cls.php');
require_once('departemen.cls.php');


Class perk_dept {
   var $conn;
   var $result;
   var $error_msg;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */
   function perk_dept($connection){
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
         $query = "select count(*) as jum from gl_perk_dept";
         $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $total_row = $row["jum"];
      } else {
         $total_row = $this->result->RecordCount();
      }
      return $total_row;
   }

   /***************************************************************************************************
   *  cari list perkiraan pada departemen
   *
   */
   function get_list_perkiraan($id_departemen){
      $query = sprintf("select gl_perk_dept.id_dp,gl_perkiraan.* from gl_perk_dept,gl_perkiraan
                        where gl_perk_dept.prk_id = gl_perkiraan.id_prk
                        and gl_perk_dept.dept_id = '%s' order by gl_perkiraan.id_prk",
                        $id_departemen);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   function get_list_perkiraan_master($id_departemen, $id_master = "--"){
        if ( $id_master != '--' ) $sql_master = sprintf("AND b.id_prk LIKE '%s%%'", $id_master);
        else $sql_master = "";

      $query = sprintf("SELECT a.id_dp, b.* FROM gl_perk_dept a, gl_perkiraan b
                        WHERE a.prk_id = b.id_prk
                        AND a.dept_id = '%s' %s ORDER BY b.id_prk",
                        $id_departemen, $sql_master);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  cari list departemen pada perkiraan
   *
   */
   function get_list_departemen($id_perkiraan){
      $query = sprintf("select gl_perk_dept.id_dp,gl_departemen.* from gl_perk_dept,gl_departemen
                        where gl_perk_dept.dept_id = gl_departemen.id_dept
                        and gl_perk_dept.prk_id = '%s' order by gl_departemen.id_dept",
                        $id_perkiraan);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  tambah perk_dept
   *
   */
   function add($kode_departemen,$kode_perkiraan){
      $perkiraan = new perkiraan($this->conn);
      $departemen = new departemen($this->conn);

      $result = $perkiraan->cari($kode_perkiraan);
      if(!$result){
         $this->error_msg = "Account Code Not Found.";
         return NULL;
      }else{
         $row = $result->FetchRow();
         $id_perkiraan = $row["id_prk"];
      }

      $result = $departemen->cari($kode_departemen);
      if(!$result){
         $this->error_msg = "Department Code Not Found.";
         return NULL;
      }else{
         $row = $result->FetchRow();
         $id_departemen = $row["id_dept"];
      }

      $query = sprintf("select count(*) as jum from gl_perk_dept where dept_id = '%s' and prk_id = '%s'",
                        $id_departemen,$id_perkiraan);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $jum = $row["jum"];
      if($jum!=0){
         $this->error_msg = "Account Code Already Exist.";
         return NULL;
      }
      $length = strlen($id_perkiraan);
      for($i=2 ; $i <=$length ; $i+=2){
         $_id_perkiraan = substr($id_perkiraan, 0,$i);
         $query = sprintf("select count(*) as jum from gl_perk_dept where prk_id = '%s' and dept_id ='%s'",
                     $_id_perkiraan,$id_departemen);
         $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $jum = $row["jum"];
         if($jum==0){
            $query = sprintf("insert into gl_perk_dept (id_dp,dept_id, prk_id)
                        values(%s,'%s','%s')",
                        "nextval('id_gl_perk_dept')",$id_departemen,$_id_perkiraan);
            $this->result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         }
      }
      return $this->result;
   }

   /***************************************************************************************************
   *  edit record $id
   *
   */
   function edit($id,$kode_departemen,$kode_perkiraan){
      $perkiraan = new perkiraan($this->conn);
      $departemen = new departemen($this->conn);

      $result = $perkiraan->cari($kode_perkiraan);
      if(!$result){
         $this->error_msg = "nomor perkiraan tidak ditemukan";
         return NULL;
      }else{
         $row = $result->FetchRow();
         $id_perkiraan = $row["id_prk"];
      }

      $result = $departemen->cari($kode_departemen);
      if(!$result){
         $this->error_msg = "kode departemen tidak ditemukan";
         return NULL;
      }else{
         $row = $result->FetchRow();
         $id_departemen = $row["id_dept"];
      }

      $query = sprintf("update gl_perk_dept set dept_id ='%s', prk_id = '%s'
                        where id_dp = '%s'",$id_departemen,$id_perkiraan,$id);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /*********************************************************************************
   *    hapus record $id
   *
   */
   function delete($id){
      $query  = sprintf("select dept_id,prk_id from gl_perk_dept where id_dp = '%s'",$id);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $id_perkiraan = $row["prk_id"];
      $id_departemen = $row["dept_id"];
      $length = strlen($id_perkiraan);
      $query = sprintf("delete from gl_perk_dept where prk_id like '%s%%' and dept_id = '%s'",
                     $id_perkiraan,$id_departemen);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      for($i=$length-2 ; $i >0 ; $i-=2){
         $_id_perkiraan = substr($id_perkiraan, 0,$i);
         $query = sprintf("select count(*) as jum from gl_perk_dept where prk_id like '%s%%' and dept_id ='%s' and length(prk_id) > %d ",
               $_id_perkiraan, $id_departemen,$i);
         $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $jum = $row["jum"];
         if($jum==0){
            $query = sprintf("delete from gl_perk_dept where prk_id ='%s' and dept_id = '%s'",
                     $_id_perkiraan,$id_departemen);
            $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
         } else break;
      }
      return $this->result;
   }

}


?>
