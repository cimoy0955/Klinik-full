<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2005 
 **/

Class periode {
   var $conn;
   var $result;

   /***************************************************************************************************
   *  Constructor;  
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */    
   function periode($connection){
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
   *  cari total record periode
   * 
   */
   function record_count($all=NULL){
      if($all){
         $query = "select count(*) as jum from gl_periode";
         $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $total_row = $row["jum"];
      } else {
         $total_row = $this->result->RecordCount();
      }
      return $total_row;
   }
   
   /***************************************************************************************************
   *  Menambahkan periode baru
   *
   */
   function add($awal_periode,$akhir_periode,$nama_periode){
      $insertSQL = sprintf("INSERT INTO gl_periode(id_prd, awal_prd, akhir_prd, nama_prd) VALUES (%s, %s, %s, %s)",
     				   "nextval('id_gl_periode')",$awal_periode,$akhir_periode,$nama_periode);
      $this->result = $this->conn->Execute($insertSQL);
      return $this->result;
   }

   /***************************************************************************************************
   *  update periode
   *
   */
   function edit($awal_periode,$akhir_periode,$nama_periode,$id_periode){
      $insertSQL = sprintf("UPDATE gl_periode SET awal_prd = %s, akhir_prd = %s, nama_prd = %s WHERE id_prd = %s",
                  $awal_periode,$akhir_periode,$nama_periode,$id_periode);
      $this->result = $this->conn->Execute($insertSQL);
      return $this->result;
   }

   /***************************************************************************************************
   * hapus periode
   *
   */
   function delete($id_periode){
      $deleteSQL = sprintf("DELETE FROM gl_periode WHERE id_prd = %s", $id_periode);
      $this->result = $this->conn->Execute($deleteSQL) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  cari periode
   *
   */
   function lihat($maxRows,$startRow){
      $query = sprintf("SELECT * FROM gl_periode ORDER BY awal_prd, akhir_prd, nama_prd");
      $this->result = $this->conn->Query($query, $maxRows, $startRow ) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  cari periode dari $tanggal_transaksi
   *
   */
   function cari($tanggal_transaksi){
          $query = "SELECT * FROM gl_periode AS p
                     WHERE ('$tanggal_transaksi' >= p.awal_prd AND '$tanggal_transaksi' <= p.akhir_prd)";
      $this->result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      return $this->result;
   }
}
?>
