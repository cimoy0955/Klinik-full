<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2005
 **/


 Class perkiraan{
    var $conn;
    var $result;
    var $error_msg;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   * 
   */    
   function perkiraan($connection){
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
         $query = "select count(*) as jum from gl_perkiraan";
         $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $total_row = $row["jum"];
      } else {
         $total_row = $this->result->RecordCount();
      }
      return $total_row;
   }

   /***************************************************************************************************
   *  tambah perkiraan baru
   *
   */
   function _add($id,$no_perkiraan,$nama_perkiraan,$id_parent,$isDebet){
      //check apakah no_perkiraan sudah ada
      $query = sprintf("SELECT count(*) as jum FROM gl_perkiraan WHERE no_prk = '%s'",$no_perkiraan);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
      $row = $result->FetchRow();
      $jumlah = $row["jum"];

      if ($jumlah==0) {
         $query = sprintf("INSERT INTO gl_perkiraan(id_prk,order_prk,no_prk,nama_prk,isleft_prk)
                           VALUES('%s','%s','%s',%s,'%s')",$id,$id,$no_perkiraan,QuoteValue(DPE_CHAR,$nama_perkiraan),$isDebet);
         $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
   	     //update parent
         if($id_parent!=''){
            $query = sprintf("UPDATE gl_perkiraan SET isakt_prk = 'N' WHERE id_prk = '%s'", $id_parent);
            $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
         }
         return $this->result;
      } 
      else {
         $this->error_msg = "No Perkiraan sudah ada.";
         return NULL;
      }
   }

   /***************************************************************************************************
   *  tambah perkiraan baru (selevel)
   *
   */
   function add($id,$no_perkiraan,$nama_perkiraan,$isNeraca,$isDebet){
	  $id_parent = substr($id,0,-2);
      $len = strlen($id);
      $nilai = 1;

      if($len <= 2){
         if($isNeraca =='nrc'){
   			$tanda='<';
	   		$nilai=1;
         } else {
   			$tanda = '>=';
	   		$nilai=51;
         }
         $query = sprintf("SELECT * FROM gl_perkiraan WHERE LENGTH(id_prk) = 2 and id_prk %s '51'
						         ORDER BY order_prk DESC",$tanda);
      }else{
         $query = sprintf("SELECT * FROM gl_perkiraan WHERE id_prk LIKE '%s%%' AND LENGTH(id_prk) = '%s'
						         ORDER BY order_prk DESC",$id_parent,$len);
      }
      $result = $this->conn->Query($query,1) or die($this->conn->ErrorMsg());

      if($result->RecordCount()){
         $row = $result->FetchRow();
         $jumlah = substr($row["id_prk"],-2)+1;
      }else $jumlah = $nilai;

      $idskr = $id_parent.str_pad($jumlah,2,"0",STR_PAD_LEFT);
      if($len>2) $isDebet = $row["isleft_prk"];
      elseif($isDebet=='debet')$isDebet='Y';
	  else $isDebet='N'; 
      $this->_add($idskr,$no_perkiraan,$nama_perkiraan,$id_parent,$isDebet);
   }

   /***************************************************************************************************
   *  tambah perkiraan baru (anak)
   *
   */
   function add_child($id,$no_perkiraan,$nama_perkiraan){
      $len = strlen($id)+2;
      $query = sprintf("SELECT id_prk,isleft_prk FROM gl_perkiraan WHERE id_prk LIKE '%s%%' AND LENGTH(id_prk) = %s
					         ORDER BY order_prk DESC",$id,$len);
      $result = $this->conn->Query($query,1) or die($this->conn->ErrorMsg());

      if($result->RecordCount()>0){
         $row = $result->FetchRow();
         $jumlah = substr($row["id_prk"],-2)+1;
      } else $jumlah = 1;
      //nyari saldo normal
	  $query = sprintf("SELECT id_prk,isleft_prk FROM gl_perkiraan WHERE id_prk LIKE '%s%%' ORDER BY order_prk DESC",$id);
      $result = $this->conn->Query($query,1) or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
	  $isDebet=$row["isleft_prk"];
      $idskr = $id.str_pad($jumlah,2,"0",STR_PAD_LEFT);
      $this->_add($idskr,$no_perkiraan,$nama_perkiraan,$id,$isDebet);
   }

   /***************************************************************************************************
   *  edit record $id
   *
   */
   function edit($id,$no_perkiraan,$nama_perkiraan){
      $query= sprintf("UPDATE gl_perkiraan SET no_prk = '%s', nama_prk = %s 
					WHERE id_prk = '%s'",$no_perkiraan,QuoteValue(DPE_CHAR,$nama_perkiraan),$id);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /*********************************************************************************
   *    hapus record $id
   *
   */
   function delete($id){
      //check apakah punya anak
      $query = sprintf("select count(*) as jum from gl_perkiraan where id_prk like '%s%%'",$id);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $jum = $row["jum"];

      if ($jum==1){
         //check apakah perkiraan pernah melakukan transaksi
         $query = sprintf("select count(*) as jum from gl_transaksidetil where prk_id like '%s%%'",$id);
         $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
         $row = $result->FetchRow();
         $jum = $row["jum"];

         if ($jum == 0){
            $id_parent = substr($id,0,-2);
            $query = sprintf("delete from gl_perkiraan where id_prk = '%s'",$id);
            $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
            //-- Cek apakah parent punya anak.
            $query = "SELECT COUNT(*) AS jum FROM gl_perkiraan WHERE id_prk LIKE '".$id_parent."%'";
            $parent = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
            $row = $parent->FetchRow();
            if (($row["jum"] == 1) && ($id_parent != "")) {
                $query = "UPDATE gl_perkiraan SET isakt_prk = 'Y' WHERE id_prk = '".$id_parent."'";
                $this->conn->Execute($query) or die($this->conn->ErrorMsg());
            }

            return true;
         } else {
            $this->error_msg = "&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"red\"><strong>Cannot Delete Account !</strong></font>&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"green\"><strong>Hint : </strong></font>\"Account Has Been Made transaction.\"<br/><br/>";
            return NULL;
         }
      } else {
        $this->error_msg = "&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"red\"><strong>Cannot Delete Account !</strong></font>&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"green\"><strong>Hint : </strong></font>\"Account Have Childs.\"<br/><br/>";
        return NULL;
      }
   }

   /***************************************************************************************************
   *  cari perkiraan
   *
   */
   function cari($nomor_perkiraan){
      $query = sprintf("select * from gl_perkiraan where no_prk='%s'", $nomor_perkiraan);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  cari perkiraan
   *
   */
   function cari_by_id($id_perkiraan){
      $query = sprintf("select * from gl_perkiraan where id_prk='%s'", $id_perkiraan);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  set saldo
   *
   */
   function saldo_set($id_perkiraan, $id_dept, $saldo) {
      /*--------------------------------- POSTING DATA MASTER TRANSAKSI UNTUK SALDO AWAL -*/
      $qryTemp = sprintf("SELECT id_tra FROM gl_transaksi WHERE ref_tra = '-1' and dept_id ='%s'",$id_dept);
      $rstTemp = $this->conn->Execute($qryTemp) or die ($this->conn->ErrorMsg());
      $jumTemp = $rstTemp->RecordCount();
      
      if ($saldo == "NULL") $saldo = 0;   
      if ($jumTemp == 0) {
//		  echo "data di gl_transaksi belum ada";
         $gl_id_tra = $this->conn->GetNewID("gl_transaksi","id_tra",DB_SCHEMA);
         
         $qryTemp = sprintf("INSERT INTO gl_transaksi(id_tra, ref_tra, ket_tra,real_time,dept_id) VALUES('$gl_id_tra', -1, 'SALDO AWAL',".QuoteValue(DPE_DATETIME,time()).",'$id_dept')");
         $rstTemp = $this->conn->Execute($qryTemp) or die ($this->conn->ErrorMsg());
      } else {
//		  echo "data di gl_transaksi sudah ada";
         $dataTemp = $rstTemp->FetchRow();
         $gl_id_tra = $dataTemp["id_tra"];
      }
    
      $qryTemp = sprintf("SELECT id_tra FROM gl_buffer_transaksi WHERE ref_tra = '-1'");
      $rstTemp = $this->conn->Execute($qryTemp) or die ($this->conn->ErrorMsg());
      $jumTemp = $rstTemp->RecordCount();
      if ($jumTemp == 0) {

         $kas_id_tra = $this->conn->GetNewID("gl_buffer_transaksi","id_tra",DB_SCHEMA);
          
         $qryTemp = sprintf("INSERT INTO gl_buffer_transaksi(id_tra, ref_tra, ket_tra,real_time,dept_id) VALUES('".$kas_id_tra."', -1, 'SALDO AWAL',".QuoteValue(DPE_DATETIME,time()).",'$id_dept')");
         $rstTemp = $this->conn->Execute($qryTemp) or die ($this->conn->ErrorMsg());
      } else {
//		  echo "data di gl_buffer_transaksi sudah ada";
         $dataTemp = $rstTemp->FetchRow();
         $kas_id_tra = $dataTemp["id_tra"];
      }

      $query_rsSaldo = sprintf("SELECT jumlah_trad FROM gl_transaksidetil,gl_transaksi
                              WHERE prk_id = '%s' AND tra_id = id_tra
                                    AND gl_transaksi.dept_id = '%s' and ref_tra = '-1'",
                              $id_perkiraan,$id_dept);

      $rsSaldo = $this->conn->Execute($query_rsSaldo) or die ($this->conn->ErrorMsg());
      $jum_rsSaldo = $rsSaldo->RecordCount();

      /*--------------------------------- POSTING SALDO AWAL KE TRANSAKSI GL -*/
      if ($jum_rsSaldo == 0){
//		  echo "data di gl_transaksidetil belum ada";
        $idTrad = $this->conn->GetNewID("gl_transaksidetil","id_trad",DB_SCHEMA);
         $sqlInsert = sprintf("INSERT INTO gl_transaksidetil(id_trad, tra_id,prk_id,ket_trad,jumlah_trad,dept_id) VALUES (%s, '%s','%s','%s',%s,'%s')",
                   $idTrad,$gl_id_tra, $id_perkiraan, 'Saldo Awal', $saldo,
                   $id_dept);
      } else {
//		  echo "data di gl_transaksidetil sudah ada";
         $sqlInsert = sprintf("UPDATE gl_transaksidetil SET jumlah_trad = %s WHERE tra_id = '%s' AND prk_id = '%s' AND dept_id='%s'",
                   $saldo, $gl_id_tra, $id_perkiraan, $id_dept);
      }
      $Result = $this->conn->Execute($sqlInsert) or die ($this->conn->ErrorMsg());
        
      /*--------------------------------- POSTING SALDO AWAL KE KASIR -*/
      $query_rsSaldo = sprintf("SELECT jumlah_trad FROM gl_buffer_transaksidetil,gl_buffer_transaksi WHERE prk_id = '%s' AND tra_id = id_tra AND gl_buffer_transaksi.dept_id = '%s' and ref_tra = '-1'", $id_perkiraan,$id_dept);
//      echo $query_rsSaldo;
	  $rsSaldo = $this->conn->Execute($query_rsSaldo);
      $jum_rsSaldo = $rsSaldo->RecordCount();


      if ($jum_rsSaldo == 0){
//		  echo "data di gl_buffer_transaksidetil belum ada";
        $idTrad = $this->conn->GetNewID("gl_buffer_transaksidetil","id_trad",DB_SCHEMA);
         $sqlInsert = sprintf("INSERT INTO gl_buffer_transaksidetil(id_trad, tra_id,prk_id,ket_trad,jumlah_trad,dept_id) VALUES (%s, '%s','%s','%s',%s,'%s')",
                   $idTrad,$kas_id_tra, $id_perkiraan, 'Saldo Awal', $saldo,$id_dept);
      } else {
//		  echo "data di gl_buffer_transaksidetil sudah ada";
         $sqlInsert = sprintf("UPDATE gl_buffer_transaksidetil SET jumlah_trad = %s WHERE tra_id = '%s' AND prk_id = '%s' AND dept_id = '%s'",
                   $saldo, $kas_id_tra, $id_perkiraan,$id_dept);
      }
      $Result = $this->conn->Execute($sqlInsert) or die ($this->conn->ErrorMsg());
   }


   /***************************************************************************************************
   *  get saldo
   *
   */
   function saldo_get($id_perkiraan,$id_dept) {
      $query = sprintf("SELECT jumlah_trad FROM gl_transaksidetil, gl_transaksi
                        WHERE prk_id = '%s' and tra_id = id_tra
                              and gl_transaksi.dept_id ='%s' and ref_tra = '-1'",
                        $id_perkiraan,$id_dept);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $result;
   }
   /***************************************************************************************************
   *  set budget
   *
   */
   function budget_set($id_perkiraan,$id_dept,$id_periode,$budget,$budget_akhir){
      if ($budget_akhir == "NULL") $budget_akhir = 0;
      if ($budget == "NULL") $budget = 0; 
      $query_rsBudget = sprintf("SELECT * FROM gl_budget WHERE prk_id = '%s' AND prd_id = %s AND dept_id ='%s'",
                    $id_perkiraan, $id_periode,$id_dept);
      $rsBudget = $this->conn->Execute($query_rsBudget) or die ($this->conn->ErrorMsg());
      $row_rsBudget = $rsBudget->RecordCount();
      if ($budget < 0) $pengali = '-'; else $pengali = '';
      if ($row_rsBudget == 0) {
         $sqlInsert = sprintf("INSERT INTO gl_budget (prk_id,prd_id,nilai_bud,dept_id) VALUES ('%s','%s',%s,'%s')",
                 $id_perkiraan, $id_periode, $pengali.$budget_akhir, $id_dept);
      } else if ($row_rsBudget > 0){
         $sqlInsert = sprintf("UPDATE gl_budget SET nilai_bud = %s WHERE prk_id = '%s' AND prd_id = '%s' AND dept_id='%s'",
                 $pengali.$budget_akhir, $id_perkiraan, $id_periode, $id_dept);
      }
      $Result = $this->conn->Execute($sqlInsert) or die ($this->conn->ErrorMsg());
   }

   /***************************************************************************************************
   *  get budget
   *
   */
   function budget_get($id_perkiraan,$id_dept,$tahun){
      $ADODBYear = "extract(YEAR from awal_prd)";
      $query = sprintf("SELECT * FROM gl_periode
                                 LEFT JOIN gl_budget ON ( id_prd = prd_id and dept_id ='%s' and prk_id = '%s')
                                 LEFT JOIN gl_perkiraan p ON ( id_prk = prk_id ) WHERE %s = '%s'
                                 ORDER BY akhir_prd", $id_dept,$id_perkiraan, $ADODBYear,$tahun);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      return $result;
   }

   function move_up($id){
      $order_prk_next = $this->can_move_up($id);
      if(!$order_prk_next) return;

      $query = sprintf("select id_prk from gl_perkiraan where order_prk='%s'",
               $order_prk_next);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $id_next = $row["id_prk"];
      $query = sprintf("select order_prk from gl_perkiraan where id_prk = '%s'",
               $id);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $order_prk_now = $row["order_prk"];

      //update current prk
      $query = sprintf("update gl_perkiraan set order_prk = '%s' where id_prk = '%s'",
               $order_prk_next,$id);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      //end update current prk

      //update current child
      $len = strlen($id);
      $query = sprintf("select id_prk,order_prk from gl_perkiraan
               where id_prk like '%s%%' and length(id_prk) > %s",
               $id,$len);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $jum = $result->RecordCount();

      for($i=0;$i<$jum;$i++){
         $row = $result->FetchRow();
         $id_child = $row["id_prk"];
         $order_prk_child_belakang = substr($row["order_prk"],-2);
         $order_prk_child = $order_prk_next.$order_prk_child_belakang;

         $query = sprintf("update gl_perkiraan set order_prk = '%s' where id_prk ='%s'",
                          $order_prk_child,$id_child);
         $rs = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      }
      //end update current child

      //update next prk
      $query = sprintf("update gl_perkiraan set order_prk = '%s' where id_prk = '%s'",
               $order_prk_now,$id_next);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      //end update next prk

      //update next child
      $len = strlen($id_next);
      $query = sprintf("select id_prk,order_prk from gl_perkiraan
               where id_prk like '%s%%' and length(id_prk) > %s",
               $id_next,$len);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $jum = $result->RecordCount();

      for($i=0;$i<$jum;$i++){
         $row = $result->FetchRow();
         $id_child = $row["id_prk"];
         $order_prk_child_belakang = substr($row["order_prk"],-2);
         $order_prk_child = $order_prk_now.$order_prk_child_belakang;

         $query = sprintf("update gl_perkiraan set order_prk = '%s' where id_prk ='%s'",
                          $order_prk_child,$id_child);
         $rs = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      }
      //end update next child
   }

   function move_down($id){
      $order_prk_next = $this->can_move_down($id);
      if(!$order_prk_next) return;

      $query = sprintf("select id_prk from gl_perkiraan where order_prk='%s'",
               $order_prk_next);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $id_next = $row["id_prk"];
      $query = sprintf("select order_prk from gl_perkiraan where id_prk = '%s'",
               $id);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $order_prk_now = $row["order_prk"];

      //update current prk
      $query = sprintf("update gl_perkiraan set order_prk = '%s' where id_prk = '%s'",
               $order_prk_next,$id);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      //end update current prk

      //update current child
      $len = strlen($id);
      $query = sprintf("select id_prk,order_prk from gl_perkiraan
               where id_prk like '%s%%' and length(id_prk) > %s",
               $id,$len);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $jum = $result->RecordCount();

      for($i=0;$i<$jum;$i++){
         $row = $result->FetchRow();
         $id_child = $row["id_prk"];
         $order_prk_child_belakang = substr($row["order_prk"],-2);
         $order_prk_child = $order_prk_next.$order_prk_child_belakang;

         $query = sprintf("update gl_perkiraan set order_prk = '%s' where id_prk ='%s'",
                          $order_prk_child,$id_child);
         $rs = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      }
      //end update current child

      //update next prk
      $query = sprintf("update gl_perkiraan set order_prk = '%s' where id_prk = '%s'",
               $order_prk_now,$id_next);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      //end update next prk

      //update next child
      $len = strlen($id_next);
      $query = sprintf("select id_prk,order_prk from gl_perkiraan
               where id_prk like '%s%%' and length(id_prk) > %s",
               $id_next,$len);
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $jum = $result->RecordCount();

      for($i=0;$i<$jum;$i++){
         $row = $result->FetchRow();
         $id_child = $row["id_prk"];
         $order_prk_child_belakang = substr($row["order_prk"],-2);
         $order_prk_child = $order_prk_now.$order_prk_child_belakang;

         $query = sprintf("update gl_perkiraan set order_prk = '%s' where id_prk ='%s'",
                          $order_prk_child,$id_child);
         $rs = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      }
      //end update next child
   }

   function can_move_up($id){
      $query = sprintf("select order_prk from gl_perkiraan where id_prk = '%s'",
               $id);
      //echo $query."<br>";
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $order_prk = $row["order_prk"];
      //echo "order:".$order_prk;
      $order_parent_next = substr($order_prk,0,-2);
      $order_child_next = substr($order_prk,-2)-1;
      $order_next = $order_parent_next.str_pad($order_child_next,2,"0",STR_PAD_LEFT);
      //echo "ordernext:".$order_next;
      $query = sprintf("select order_prk from gl_perkiraan where order_prk = '%s'",
               $order_next);
      //echo $query."<br>";
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      if($result->RecordCount()==0) $can_move_up = FALSE;
      else $can_move_up = $order_next;
      return $can_move_up;
   }

   function can_move_down($id){
      $query = sprintf("select order_prk from gl_perkiraan where id_prk = '%s'",
               $id);
      //echo $query."<br>";
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $order_prk = $row["order_prk"];
      //echo "order:".$order_prk;
      $order_parent_next = substr($order_prk,0,-2);
      $order_child_next = substr($order_prk,-2)+1;
      $order_next = $order_parent_next.str_pad($order_child_next,2,"0",STR_PAD_LEFT);
      //echo "ordernext:".$order_next;
      $query = sprintf("select order_prk from gl_perkiraan where order_prk = '%s'",
               $order_next);
      //echo $query."<br>";
      $result = $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
      if($result->RecordCount()==0) $can_move_up = FALSE;
      else $can_move_up = $order_next;
      return $can_move_up;
   }

   function can_delete($id){
      //check apakah punya anak
      $query = sprintf("select count(*) as jum from gl_perkiraan where id_prk like '%s%%'",$id);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $jum = $row["jum"];

      if ( $jum == 1 ) return true;
      else return false;
   }
 }
?>
