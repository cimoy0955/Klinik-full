<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/tree.cls.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     

 	if(!$auth->IsAllowed("operasi",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("operasi",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "operasi.php";
     $backPage = "operasi_view.php?";

     $tablePreOP = new InoTable("table1","99%","center");
     $tableOP = new InoTable("table1","99%","center");

     $icdPage = "op_icd_find.php?";
     $inaPage = "op_ina_find.php?";
     $dokterPage = "op_dokter_find.php?";
     $susterPage = "op_suster_find.php?";

     if(!$_POST["cbRegulasi"]) $_POST["cbRegulasi"] = "y";

     $plx = new InoLiveX("GetOp,GetPreme,SetPreop");     

     function GetPreme() {
          global $dtaccess, $view, $tableOP, $thisPage, $APLICATION_ROOT,$rawatStatus; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, fol_lunas 
				from klinik.klinik_registrasi a
				join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
				left join klinik.klinik_folio c on c.id_cust_usr = a.id_cust_usr and a.reg_id = c.id_reg
				and c.id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_GULA_PREOP)." 
                    where (a.reg_status like '".STATUS_PREMEDIKASI.STATUS_PROSES."' or a.reg_status like '".STATUS_PREOP.STATUS_ANTRI."') order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
               
               if($dataTable[$i]["reg_status"]==STATUS_PREMEDIKASI.STATUS_PROSES) {
               
                    if(!$dataTable[$i]["fol_lunas"] || $dataTable[$i]["fol_lunas"]=='y') {
                         $bar = '<a href="premedikasi.php?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';
                    } else 
                         $bar = "";
                         
               } else {
                    $bar = '<a onClick="ProsesPerawatan(\''.$dataTable[$i]["reg_id"].'\')" href="preop.php?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
               }
			
			$tbContent[$i][$counter][TABLE_ISI] = $bar;               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }

          return $tableOP->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}


     function GetOp() {
          global $dtaccess, $view, $tableOP, $thisPage, $APLICATION_ROOT,$rawatStatus; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.reg_status like '".STATUS_OPERASI.STATUS_PROSES."' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
			$tbContent[$i][$counter][TABLE_ISI] = '<a href="operasi.php?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }

          return $tableOP->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}

     function SetPreop($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_PREOP.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
	
     if(!$_POST["btnSave"] && !$_POST["btnUpdate"]) {
          // --- buat cari suster yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id 
                    from klinik.klinik_operasi_suster a 
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_operasi c on c.op_id = a.id_op 
                    where cast(c.op_waktu as date) = ".QuoteValue(DPE_DATE,date("Y-m-d")); 
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["op_suster_nama"][$i]) $_POST["op_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     }

     if ($_GET["id"]) {
          // === buat ngedit ---          
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["op_id"] = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.id_reg,op_id from klinik.klinik_operasi a 
				where op_id = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $_GET["id_reg"] = $row_edit["id_reg"];
          $_POST["op_id"] = $row_edit["op_id"];
     }
	
     // --- cari input premedikasi pertama hari ini ---
     $sql = "select a.op_id 
               from klinik.klinik_operasi a 
               where cast(a.op_waktu as date) = ".QuoteValue(DPE_CHAR,date('Y-m-d'))." 
               order by op_waktu asc limit 1";
     $rs = $dtaccess->Execute($sql);
     $firstData = $dtaccess->Fetch($rs);
     
     $edit = (($firstData["op_id"]==$_POST["op_id"])||!$firstData["op_id"])?true:false;

	
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, ((current_date - cust_usr_tanggal_lahir)/365) as umur,
				a.id_cust_usr, c.ref_keluhan, reg_jenis_pasien
                    from klinik.klinik_registrasi a
				join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"]; 
		$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
		$_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
          
          $sql = "select * from klinik.klinik_operasi where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataOperasi= $dtaccess->Fetch($sql);
          
          $view->CreatePost($dataOperasi);

		$tmpJamMulai = explode(":", $_POST["op_jam_mulai"]);
		$_POST["op_mulai_jam"] = $tmpJamMulai[0];
		$_POST["op_mulai_menit"] = $tmpJamMulai[1];
		
		$tmpJamSelesai = explode(":", $_POST["op_jam_selesai"]);
		$_POST["op_selesai_jam"] = $tmpJamSelesai[0];
		$_POST["op_selesai_menit"] = $tmpJamSelesai[1];
		
		
          $sql = "select pgw_nama, pgw_id from klinik.klinik_operasi_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster"][$i] = $row["pgw_id"];
               $_POST["op_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }


          $sql = "select * from klinik.klinik_operasi_duranteop a 
                    where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_durop_komp"][$row["id_durop_komp"]] = "y";
          }


          $sql = "select pgw_nama, pgw_id from klinik.klinik_operasi_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_dokter"] = $row["pgw_id"];
          $_POST["op_dokter_nama"] = $row["pgw_nama"];


		$sql = "select b.icd_nomor as op_icd_kode, b.icd_nama as op_icd_nama, a.id_icd  
				from klinik.klinik_operasi_icd a  
                    join klinik.klinik_icd b on a.id_icd = b.icd_id 
				where a.id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
		$dataIcd = $dtaccess->FetchAll($sql);


		$sql = "select b.ina_kode as op_ina_kode, b.ina_nama as op_ina_nama 
				from klinik.klinik_ina b 
				where b.ina_id = ".QuoteValue(DPE_CHAR,$dataOperasi["id_ina"]);
		$dataIna = $dtaccess->Fetch($sql);
		$view->CreatePost($dataIna);

	}

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {

		$_POST["op_jam_mulai"] = $_POST["op_mulai_jam"].":".$_POST["op_mulai_menit"];
		$_POST["op_jam_selesai"] = $_POST["op_selesai_jam"].":".$_POST["op_selesai_menit"];

          if($_POST["btnSave"]) {
               $sql = "delete from klinik.klinik_operasi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
          }
          
          $dbTable = "klinik.klinik_operasi";
          $dbField[0] = "op_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "op_jam_mulai";
          $dbField[3] = "op_jam_selesai";
          $dbField[4] = "op_jenis";
          $dbField[5] = "id_ina";
          $dbField[6] = "op_tindakan";
          $dbField[7] = "op_paket_biaya";
          $dbField[8] = "op_conj";
          $dbField[9] = "op_cauter";
          $dbField[10] = "id_cust_usr";
          $dbField[11] = "op_corneal_enter_jam";
          $dbField[12] = "op_corneal_enter_diperluas";
          $dbField[13] = "op_corneal_enter_jam1";
          $dbField[14] = "op_corneal_enter_jam2";
          $dbField[15] = "op_indirectomy";
          $dbField[16] = "op_indirectomy_tipe";
          $dbField[17] = "op_indirectomy_jam";
          $dbField[18] = "op_nucleus_removal";
          $dbField[19] = "op_cortex_removal";
          $dbField[20] = "op_corneal_suture";
          $dbField[21] = "op_corneal_suture_ukuran";
          $dbField[22] = "op_suture_tipe";
          $dbField[23] = "op_coa";
          $dbField[24] = "op_obat";
          $dbField[25] = "op_komplikasi_manajemen";
          $dbField[26] = "op_iol_jenis";
          $dbField[27] = "op_iol_merk";
          $dbField[28] = "op_iol_power";
          $dbField[29] = "op_iol_posisi";
          $dbField[30] = "op_pesan_operator";
          $dbField[31] = "op_waktu";
          $dbField[32] = "id_op_metode";
          
          if(!$_POST["op_id"]) $_POST["op_id"] = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["op_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_DATE,$_POST["op_jam_mulai"]);
          $dbValue[3] = QuoteValue(DPE_DATE,$_POST["op_jam_selesai"]);
          $dbValue[4] = QuoteValue(DPE_CHARKEY,$_POST["op_jenis"]);
          $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["id_ina"]);
          $dbValue[6] = QuoteValue(DPE_CHARKEY,$_POST["op_tindakan"]);
          $dbValue[7] = QuoteValue(DPE_CHARKEY,$_POST["op_paket_biaya"]);
          $dbValue[8] = QuoteValue(DPE_CHARKEY,$_POST["op_conj"]);
          $dbValue[9] = QuoteValue(DPE_CHARKEY,$_POST["op_cauter"]);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["op_corneal_enter_jam"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["op_corneal_enter_diperluas"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["op_corneal_enter_jam1"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["op_corneal_enter_jam2"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["op_indirectomy"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["op_indirectomy_tipe"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["op_indirectomy_jam"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["op_nucleus_removal"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["op_cortex_removal"]);
          $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["op_corneal_suture"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["op_corneal_suture_ukuran"]);
          $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["op_suture_tipe"]);
          $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["op_coa"]);
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["op_obat"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["op_komplikasi_manajemen"]);
          $dbValue[26] = QuoteValue(DPE_CHARKEY,$_POST["op_iol_jenis"]);
          $dbValue[27] = QuoteValue(DPE_CHARKEY,$_POST["op_iol_merk"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["op_iol_power"]);
          $dbValue[29] = QuoteValue(DPE_CHARKEY,$_POST["op_iol_posisi"]);
          $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["op_pesan_operator"]);
          $dbValue[31] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s")); 
          $dbValue[32] = QuoteValue(DPE_NUMERICKEY,$_POST["id_op_metode"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if ($_POST["btnSave"]) {
              $dtmodel->Insert() or die("insert  error");	
          } elseif ($_POST["btnUpdate"]) {
              $dtmodel->Update() or die("update  error");	
          }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);


          // --- insrt suster ---
          $sql = "delete from klinik.klinik_operasi_suster where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_suster"]) {
               foreach($_POST["id_suster"] as $key => $value){
                    if($value) {
                         $dbTable = "klinik_operasi_suster";
                    
                         $dbField[0] = "op_suster_id";   // PK
                         $dbField[1] = "id_op";
                         $dbField[2] = "id_pgw";
                                
                         $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["op_id"]);
                         $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$value);
                         
                         //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField);
                         unset($dbValue);
                         unset($dbKey);
                    }
               }
          }
          // --- insrt dokter ---
          $sql = "delete from klinik.klinik_operasi_dokter where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_dokter"]) {
               
               $dbTable = "klinik_operasi_dokter";
               
               $dbField[0] = "op_dokter_id";   // PK
               $dbField[1] = "id_op";
               $dbField[2] = "id_pgw";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["op_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_dokter"]);
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }
          
          
          // --- insert icd ---
          $sql = "delete from klinik.klinik_operasi_icd where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);
          
          $dbTable = "klinik.klinik_operasi_icd";
          
          $dbField[0] = "op_icd_id";
          $dbField[1] = "id_op";
          $dbField[2] = "id_icd";
          
          foreach($_POST["op_icd_kode"] as $key=>$value) {
               if($value) {
               
                    $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
                    $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["op_id"]);
                    $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["id_icd"][$key]);
     
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dbKey[1] = 1; 
                    
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
                    $dtmodel->Insert() or die("insert  error");
                    
                    unset($dtmodel);
                    unset($dbValue);
                    unset($dbKey);
               }
          }


		// --- insert duop
          if($_POST["id_durop_komp"]) {
               foreach($_POST["id_durop_komp"] as $key=>$value) {
               
                    $dbTable = "klinik_operasi_duranteop";
                    
                    $dbField[0] = "op_durop_id";   // PK
                    $dbField[1] = "id_op";
                    $dbField[2] = "id_durop_komp";
                           
                    $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["op_id"]);
                    $dbValue[2] = QuoteValue(DPE_CHAR,$key);
                    
                    //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                    
                    $dtmodel->Insert() or die("insert error"); 
                    
                    unset($dtmodel);
                    unset($dbField);
                    unset($dbValue);
                    unset($dbKey);
               }
          }
		
		
		$sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
				and id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])." and fol_jenis = '".STATUS_OPERASI."'";
          $dtaccess->Execute($sql);
		
		$sql = "delete from klinik.klinik_registrasi_klaim where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dtaccess->Execute($sql); 
		
		if($_POST["op_status"]=='y') {
			
			$sql = "select * from klinik.klinik_operasi_paket where op_paket_id = ".QuoteValue(DPE_CHAR,$_POST["op_paket_biaya"]); 
			$dataBiaya = $dtaccess->Fetch($sql,DB_SCHEMA);
			$folWaktu = date("Y-m-d H:i:s");
			
			if($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_SWADAYA) $byr = "n";
			else $byr = "y";
			
			$dbTable = "klinik_folio";
				
			$dbField[0] = "fol_id";   // PK
			$dbField[1] = "id_reg";
			$dbField[2] = "fol_nama";
			$dbField[3] = "fol_nominal";
			$dbField[4] = "fol_lunas";
			$dbField[5] = "fol_jenis";
			$dbField[6] = "id_cust_usr";
			$dbField[7] = "fol_waktu"; 
			
			$folId = $dtaccess->GetTransID();
			$dbValue[0] = QuoteValue(DPE_CHAR,$folId);
			$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
			$dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["op_paket_nama"]);
			$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["op_paket_total"]);
			$dbValue[4] = QuoteValue(DPE_CHAR,$byr);
			$dbValue[5] = QuoteValue(DPE_CHAR,STATUS_OPERASI);
			$dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
			$dbValue[7] = QuoteValue(DPE_DATE,$folWaktu); 
			 
			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
			
			$dtmodel->Insert() or die("insert error"); 
			
			unset($dtmodel);
			unset($dbValue);
			unset($dbKey);
			unset($dbField);  
			 
			$sql = "select * from klinik.klinik_operasi_paket_split
					where id_op_paket = ".QuoteValue(DPE_CHAR,$dataBiaya["op_paket_id"])." and op_paket_split_nominal > 0"; 
			$dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
			
			for($a=0,$b=count($dataSplit);$a<$b;$a++) { 
				$dbTable = "klinik_folio_split";
			
				$dbField[0] = "folsplit_id";   // PK
				$dbField[1] = "id_fol";
				$dbField[2] = "id_split";
				$dbField[3] = "folsplit_nominal";
				 
				$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
				$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
				$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$a]["id_split"]);
				$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["op_paket_split_nominal"]);
				 
				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
				
				$dtmodel->Insert() or die("insert error"); 
				
				unset($dtmodel);
				unset($dbField);
				unset($dbValue);
				unset($dbKey); 
			}
			
			
			// --- ngisi klaim INA klo pasien JAMKESMAS PUSAT ---
			if($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_JAMKESNAS_PUSAT && $_POST["id_ina"]){
			
                    // --- delete data e dulu ---
                    $sql = "delete from klinik.klinik_registrasi_ina
                              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
                              and reg_ina_jenis = ".QuoteValue(DPE_CHAR,STATUS_OPERASI);
                    $rs = $dtaccess->Execute($sql);                    
                    
                    $sql = "select ina_nama, ina_nominal
                              from klinik.klinik_ina
                              where ina_id = ".QuoteValue(DPE_CHAR,$_POST["id_ina"]);
                    $rsIna = $dtaccess->Execute($sql);
                    
                    while($rowIna = $dtaccess->Fetch($rsIna)) {
                    
                         $dbTable = "klinik_registrasi_ina";
				
          			$dbField[0] = "reg_ina_id";   // PK
          			$dbField[1] = "reg_ina_nama";
          			$dbField[2] = "id_ina";
          			$dbField[3] = "reg_ina_nominal";
          			$dbField[4] = "id_cust_usr";
                         $dbField[5] = "id_reg";
                         $dbField[6] = "reg_ina_jenis";
                         $dbField[7] = "reg_ina_when";
                         $dbField[8] = "reg_ina_who";
          			
          				$regInaId = $dtaccess->GetTransID();
          				$dbValue[0] = QuoteValue(DPE_CHAR,$regInaId);
          				$dbValue[1] = QuoteValue(DPE_CHAR,$rowIna["ina_nama"]);
          				$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_ina"]);
          				$dbValue[3] = QuoteValue(DPE_NUMERIC,$rowIna["ina_nominal"]); 
          				$dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
                              $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                              $dbValue[6] = QuoteValue(DPE_CHAR,STATUS_OPERASI);
                              $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                              $dbValue[8] = QuoteValue(DPE_CHAR,$userData["name"]);
          			 
          			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
          			
          			$dtmodel->Insert() or die("insert error"); 
          			
          			unset($dtmodel);
          			unset($dbValue);
          			unset($dbKey);
          			unset($dbField);  
          			
          			
          			// --- nyimpen split e ---
          			$sql = "select ina_split_id, ina_split_nominal 
                                   from klinik.klinik_ina_split
                                   where id_ina = ".QuoteValue(DPE_CHAR,$_POST["id_ina"])."
                                   and ina_split_nominal > 0";
                         $rsSplit = $dtaccess->Execute($sql);
                         
                         while($rowSplit = $dtaccess->Fetch($rsSplit)) {
                         
                              $dbTable = "klinik_registrasi_ina_split";
				
               			$dbField[0] = "reg_ina_split_id";   // PK
               			$dbField[1] = "id_reg";
               			$dbField[2] = "id_reg_ina";
               			$dbField[3] = "id_ina_split";
               			$dbField[4] = "reg_ina_split_nominal";
               			
               				$regInaSplitId = $dtaccess->GetTransID();
               				$dbValue[0] = QuoteValue(DPE_CHAR,$regInaSplitId);
               				$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               				$dbValue[2] = QuoteValue(DPE_CHAR,$regInaId);
               				$dbValue[3] = QuoteValue(DPE_CHAR,$rowSplit["ina_split_id"]); 
               				$dbValue[4] = QuoteValue(DPE_NUMERIC,$rowSplit["ina_split_nominal"]);
               			 
               			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               			
               			$dtmodel->Insert() or die("insert error"); 
               			
               			unset($dtmodel);
               			unset($dbValue);
               			unset($dbKey);
               			unset($dbField);  
                         }
                         
                    }
			}			
		}
			
		
		if($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA) {
		
     		if($_POST["op_status"]=='y') $jenisLayanan = STATUS_OPERASI;
     		elseif($_POST["op_status"]=='n') $jenisLayanan = STATUS_PEMERIKSAAN;
     		
     		$sql = "select b.* from klinik.klinik_biaya_pasien a
     				join klinik.klinik_paket_klaim b on b.paket_klaim_id = a.id_paket_klaim
     				where biaya_pasien_status = ".QuoteValue(DPE_CHAR,$jenisLayanan)."
     				and biaya_pasien_jenis = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]); 
     		$dataPaket = $dtaccess->FetchAll($sql,DB_SCHEMA); 
     			
     		for($i=0,$n=count($dataPaket);$i<$n;$i++) { 
     			
     			$dbTable = "klinik_registrasi_klaim";
     				
     			$dbField[0] = "reg_klaim_id";   // PK
     			$dbField[1] = "id_reg";
     			$dbField[2] = "id_paket_klaim";
     			$dbField[3] = "reg_klaim_nominal";
     			$dbField[4] = "reg_klaim_when";
                    $dbField[5] = "reg_klaim_who";
                    $dbField[6] = "reg_klaim_jenis";
     			
     				$regKlaimId = $dtaccess->GetTransID();
     				$dbValue[0] = QuoteValue(DPE_CHAR,$regKlaimId);
     				$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
     				$dbValue[2] = QuoteValue(DPE_CHAR,$dataPaket[$i]["paket_klaim_id"]);
     				$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataPaket[$i]["paket_klaim_total"]); 
     				$dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                         $dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
                         $dbValue[6] = QuoteValue(DPE_CHAR,$jenisLayanan);
     			 
     			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
     			
     			$dtmodel->Insert() or die("insert error"); 
     			
     			unset($dtmodel);
     			unset($dbValue);
     			unset($dbKey);
     			unset($dbField);  
     			 
     			$sql = "select * from klinik.klinik_paket_klaim_split
     					where id_paket_klaim = ".QuoteValue(DPE_CHAR,$dataPaket[$i]["paket_klaim_id"])." 
                              and klaim_split_nominal > 0";
     			$dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
     			
     			for($a=0,$b=count($dataSplit);$a<$b;$a++) { 
     				$dbTable = "klinik_registrasi_klaim_split";
     			
     				$dbField[0] = "reg_klaim_split_id";   // PK
     				$dbField[1] = "id_reg";
     				$dbField[2] = "id_klaim_split";
     				$dbField[3] = "reg_klaim_split_nominal";
     				$dbField[4] = "id_reg_klaim";
     					  
     				$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
     				$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
     				$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$a]["klaim_split_id"]);
     				$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["klaim_split_nominal"]);
     				$dbValue[4] = QuoteValue(DPE_CHAR,$regKlaimId);
     				 
     				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
     				
     				$dtmodel->Insert() or die("insert error"); 
     				
     				unset($dtmodel);
     				unset($dbField);
     				unset($dbValue);
     				unset($dbKey); 
     			} 
     		}
     	}
     	
		
          if($_POST["btnSave"]) {
               if($_POST["op_status"]=='y') $status = STATUS_SELESAI;                                
               elseif ($_POST["op_status"]=='n') $status = STATUS_OPERASI_JADWAL."0";
               
               
               $sql = "update klinik.klinik_registrasi set reg_status = '".$status."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
          }
	
          if($_POST["btnSave"]) echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          exit();   

	}

     // --- nyari datanya IOL ---
     $sql = "select iol_jenis_id, iol_jenis_nama from klinik.klinik_iol_jenis";
     $dataIOLJenis = $dtaccess->FetchAll($sql);

     $sql = "select iol_merk_id, iol_merk_nama from klinik.klinik_iol_merk";
     $dataIOLMerk = $dtaccess->FetchAll($sql);

     $sql = "select iol_pos_id, iol_pos_nama from klinik.klinik_iol_posisi";
     $dataIOLPosisi = $dtaccess->FetchAll($sql);

     $optIOLJenis[0] = $view->RenderOption("","[Pilih Jenis IOL]",$show); 
     for($i=0,$n=count($dataIOLJenis);$i<$n;$i++) {
          $show = ($_POST["op_iol_jenis"]==$dataIOLJenis[$i]["iol_jenis_id"]) ? "selected":"";
          $optIOLJenis[$i+1] = $view->RenderOption($dataIOLJenis[$i]["iol_jenis_id"],$dataIOLJenis[$i]["iol_jenis_nama"],$show); 
     }

     $optIOLMerk[0] = $view->RenderOption("","[Pilih Merk IOL]",$show); 
     for($i=0,$n=count($dataIOLMerk);$i<$n;$i++) {
          $show = ($_POST["op_iol_merk"]==$dataIOLMerk[$i]["iol_merk_id"]) ? "selected":"";
          $optIOLMerk[$i+1] = $view->RenderOption($dataIOLMerk[$i]["iol_merk_id"],$dataIOLMerk[$i]["iol_merk_nama"],$show); 
     }

     $optIOLPos[0] = $view->RenderOption("","[Pilih Posisi IOL]",$show); 
     for($i=0,$n=count($dataIOLPosisi);$i<$n;$i++) {
          $show = ($_POST["op_iol_posisi"]==$dataIOLPosisi[$i]["iol_pos_id"]) ? "selected":"";
          $optIOLPos[$i+1] = $view->RenderOption($dataIOLPosisi[$i]["iol_pos_id"],$dataIOLPosisi[$i]["iol_pos_nama"],$show); 
     }

     $sql = "select op_jenis_nama from klinik.klinik_operasi_jenis where op_jenis_id = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_operasi_jenis"]);
     $dataJenisTindakan= $dtaccess->Fetch($sql);

     // --- nyari datanya operasi jenis + harganya---
     $sql = "select op_jenis_id, op_jenis_nama from klinik.klinik_operasi_jenis";
     $dataOperasiJenis = $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi Jenis
     $optOperasiJenis[0] = $view->RenderOption("","[Pilih Jenis Operasi]",$show); 
     for($i=0,$n=count($dataOperasiJenis);$i<$n;$i++) {
          $show = ($_POST["op_jenis"]==$dataOperasiJenis[$i]["op_jenis_id"]) ? "selected":"";
          $optOperasiJenis[$i+1] = $view->RenderOption($dataOperasiJenis[$i]["op_jenis_id"],$dataOperasiJenis[$i]["op_jenis_nama"],$show); 
     }

     // --- nyari datanya operasi teknik + harganya---
     $sql = "select op_tek_id, op_tek_nama from klinik.klinik_operasi_teknik";
     $dataOperasiTeknik = $dtaccess->FetchAll($sql);


     // --- nyari datanya komplikasi durante ---
     $sql = "select durop_komp_id, durop_komp_nama from klinik.klinik_duranteop_komplikasi";
     $dataDurop = $dtaccess->FetchAll($sql);

     
     $sql = "select op_paket_id, op_paket_nama from klinik.klinik_operasi_paket";
     $dataOperasiPaket= $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi paket
     $optOperasiPaket[0] = $view->RenderOption("","[Pilih Paket Operasi]",$show); 
     for($i=0,$n=count($dataOperasiPaket);$i<$n;$i++) {
          $show = ($_POST["op_paket_biaya"]==$dataOperasiPaket[$i]["op_paket_id"]) ? "selected":"";
          $optOperasiPaket[$i+1] = $view->RenderOption($dataOperasiPaket[$i]["op_paket_id"],$dataOperasiPaket[$i]["op_paket_nama"],$show); 
     }

     $sql = "select * from klinik.klinik_operasi_metode ";
     $dataMetodeOp = $dtaccess->FetchAll($sql);

     $metodeOp[0] = $view->RenderOption("","[Pilih Metode Operasi]",$show); 
     for($i=0,$n=count($dataMetodeOp);$i<$n;$i++) {
		unset($show);
          $show = ($_POST["id_op_metode"]==$dataMetodeOp[$i]["op_metode_id"]) ? "selected":"";
          $metodeOp[$i+1] = $view->RenderOption($dataMetodeOp[$i]["op_metode_id"],$dataMetodeOp[$i]["op_metode_nama"],$show); 
     }
     
     unset($show);
     
     $optConj[] = $view->RenderOption("","[--]",$show);
	$show = ($_POST["op_conj"]==1) ? "selected":"";
     $optConj[] = $view->RenderOption("1","Fornix Base",$show); 
	$show = ($_POST["op_conj"]==2) ? "selected":"";
     $optConj[] = $view->RenderOption("2","Limbal Base",$show); 
     
     $optCauter[] = $view->RenderOption("","[--]",$show); 
	$show = ($_POST["op_cauter"]==1) ? "selected":"";
     $optCauter[] = $view->RenderOption("1","Minimal",$show); 
	$show = ($_POST["op_cauter"]==2) ? "selected":"";
     $optCauter[] = $view->RenderOption("2","Moderate",$show); 
	$show = ($_POST["op_cauter"]==3) ? "selected":"";
     $optCauter[] = $view->RenderOption("3","Severe",$show); 

     $optIndirectomy[] = $view->RenderOption("","[--]",$show); 
	$show = ($_POST["op_indirectomy"]==1) ? "selected":"";
     $optIndirectomy[] = $view->RenderOption("1","No",$show); 
	$show = ($_POST["op_indirectomy"]==2) ? "selected":"";
     $optIndirectomy[] = $view->RenderOption("2","Yes",$show); 

     $optNucleus[] = $view->RenderOption("","[--]",$show); 
	$show = ($_POST["op_nucleus_removal"]==1) ? "selected":"";
     $optNucleus[] = $view->RenderOption("1","Irigasi",$show); 
	$show = ($_POST["op_nucleus_removal"]==2) ? "selected":"";
     $optNucleus[] = $view->RenderOption("2","Expresi",$show); 
	$show = ($_POST["op_nucleus_removal"]==3) ? "selected":"";
     $optNucleus[] = $view->RenderOption("3","Lain-lain",$show); 

     $optCortex[] = $view->RenderOption("","[--]",$show); 
	$show = ($_POST["op_cortex_removal"]==1) ? "selected":"";
     $optCortex[] = $view->RenderOption("1","manual I/A",$show); 
	$show = ($_POST["op_cortex_removal"]==2) ? "selected":"";
     $optCortex[] = $view->RenderOption("2","Vitreus",$show); 
	$show = ($_POST["op_cortex_removal"]==3) ? "selected":"";
     $optCortex[] = $view->RenderOption("3","lain-lain",$show); 

     $optCorneal[] = $view->RenderOption("","[--]",$show); 
	$show = ($_POST["op_corneal_suture"]==1) ? "selected":"";
     $optCorneal[] = $view->RenderOption("1","Vicryl",$show); 
	$show = ($_POST["op_corneal_suture"]==2) ? "selected":"";
     $optCorneal[] = $view->RenderOption("2","Zeide",$show); 
	$show = ($_POST["op_corneal_suture"]==3) ? "selected":"";
     $optCorneal[] = $view->RenderOption("3","Dexon",$show); 
	$show = ($_POST["op_corneal_suture"]==4) ? "selected":"";
     $optCorneal[] = $view->RenderOption("4","Lain-lain",$show); 

     $optTypeSuture[] = $view->RenderOption("","[--]",$show); 
	$show = ($_POST["op_suture_tipe"]==1) ? "selected":"";
     $optTypeSuture[] = $view->RenderOption("1","Interupt",$show); 
	$show = ($_POST["op_suture_tipe"]==2) ? "selected":"";
     $optTypeSuture[] = $view->RenderOption("2","Continous Type",$show); 

     $optCOA[] = $view->RenderOption("","[--]",$show); 
	$show = ($_POST["op_coa"]==1) ? "selected":"";
     $optCOA[] = $view->RenderOption("1","NSS",$show); 
	$show = ($_POST["op_coa"]==2) ? "selected":"";
     $optCOA[] = $view->RenderOption("2","AIR",$show); 
	$show = ($_POST["op_coa"]==3) ? "selected":"";
     $optCOA[] = $view->RenderOption("3","Lain-lain",$show); 

     $optObat[] = $view->RenderOption("","[--]",$show); 
	$show = ($_POST["op_obat"]==1) ? "selected":"";
     $optObat[] = $view->RenderOption("1","Healon",$show); 
	$show = ($_POST["op_obat"]==2) ? "selected":"";
     $optObat[] = $view->RenderOption("2","Myostat",$show); 
	$show = ($_POST["op_obat"]==3) ? "selected":"";
     $optObat[] = $view->RenderOption("3","Atropin",$show); 
	$show = ($_POST["op_obat"]==4) ? "selected":"";
     $optObat[] = $view->RenderOption("4","Pantocain",$show); 
	$show = ($_POST["op_obat"]==5) ? "selected":"";
     $optObat[] = $view->RenderOption("5","Efrisel",$show); 
	$show = ($_POST["op_obat"]==6) ? "selected":"";
     $optObat[] = $view->RenderOption("6","Genta Inj",$show); 
	$show = ($_POST["op_obat"]==7) ? "selected":"";
     $optObat[] = $view->RenderOption("7","Cortison Inj",$show); 
	$show = ($_POST["op_obat"]==8) ? "selected":"";
     $optObat[] = $view->RenderOption("8","Metilen Blue",$show); 
	$show = ($_POST["op_obat"]==9) ? "selected":"";
     $optObat[] = $view->RenderOption("9","Lain-lain",$show); 
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>


<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetPreme('target=antri_kiri_isi');     
     GetOp('target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}

function ProsesPerawatan(id) {
	SetPreop(id,'type=r');
	timer();
}


function CheckData(frm) {
     return true;
}

function CheckSimpan(frm){
	
	if(frm.op_status.value=='y') {
     	if(!frm.op_jenis.value) {
     		alert('Jenis Operasi Harus di Pilih');
     		frm.op_jenis.focus();
     		return false;
     	}
     	
     	if(!frm.op_paket_biaya.value) {
     		alert('Paket Biaya Harus di Pilih');
     		frm.op_paket_biaya.focus();
     		return false;
     	}
     }
     
     if(frm.reg_jenis_pasien.value==<?php echo PASIEN_BAYAR_JAMKESNAS_PUSAT?> && !frm.op_ina_kode.value){
          alert('INA DRG Harus di Pilih');
          frm.op_ina_kode.focus();
          
          return false;
     }
	
	return true;
}


function IcdTambah(){
     var akhir = eval(document.getElementById('icd_tot').value)+1;
     
     $('#tb_icd').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_icd_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:10, maxLength:100, name:'op_icd_kode[]', id:'op_icd_kode_'+akhir},[],
                         'a',{ href:'<?php echo $icdPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari ICD'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_icd[]', id:'id_icd_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:50, maxLength:100, name:'op_icd_nama[]', id:'op_icd_nama_'+akhir},[],
                         
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { IcdDelete(akhir) } );
     document.getElementById('op_icd_kode_'+akhir).readOnly = true;
     document.getElementById('op_icd_nama_'+akhir).readOnly = true;
          
     document.getElementById('icd_tot').value = akhir;
     tb_init('a.thickbox');
}

function IcdDelete(akhir){     
     $('#tr_icd_'+akhir).remove();
}

function SusterTambah(){
     var akhir = eval(document.getElementById('suster_tot').value)+1;
     
     $('#tb_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'op_suster_nama[]', id:'op_suster_nama_'+akhir},[],
                         'a',{ href:'<?php echo $susterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_suster[]', id:'id_suster_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { SusterDelete(akhir) } );
     document.getElementById('op_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterDelete(akhir){
     document.getElementById('hid_suster_del').value += document.getElementById('id_suster_'+akhir).value;
     
     $('#tr_suster_'+akhir).remove();
}


timer();
</script>


<?php if(!$_GET["id"]) { ?>

<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Proses Premedikasi</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetPreme(); ?></div>
	</div> 
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Operasi</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetOp(); ?></div>
	</div>
</div>

<?php } ?>



<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Operasi</td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "30%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Umur</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
	</table>
     </fieldset>




     <fieldset>
     <legend><strong>Data Laporan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="30%"  class="tablecontent" align="left">Status</td>
               <td align="left" class="tablecontent-odd" width="80%" colspan=3>
                    <select name="op_status" id="op_status" class="inputField">
                         <option value="y">Operasi</option>
                         <option value="n">Batal</option>
                    </select>
               </td>
          </tr>
          
          <tr>
               <td width="30%"  class="tablecontent" align="left">Operator</td>
               <td align="left" class="tablecontent-odd" width="80%" colspan=3> 
                    <?php echo $view->RenderTextBox("op_dokter_nama","op_dokter_nama","30","100",$_POST["op_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>
          </tr>
     
          <tr>
               <td width="30%"  class="tablecontent" align="left">Asisten Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%" colspan=3>
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php if(!$_POST["op_suster_nama"]) { ?>
					<tr id="tr_suster_0">
						<td align="left" class="tablecontent-odd" width="40%">
							<?php echo $view->RenderTextBox("op_suster_nama[]","op_suster_nama_0","30","100",$_POST["op_suster_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
							<input type="hidden" id="id_suster_0" name="id_suster[]" value="<?php echo $_POST["id_suster"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="60%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
							<input name="suster_tot" id="suster_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $view->RenderTextBox("op_suster_nama[]","op_suster_nama_".$i,"30","100",$_POST["op_suster_nama"][$i],"inputField", "readonly",false);?>
								<?php if($edit) { ?>
									<a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
								<?php } ?>
                                        <input type="hidden" id="id_suster_<?php echo $i;?>" name="id_suster[]" value="<?php echo $_POST["id_suster"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
								<?php if($edit) { ?>
									<?php if($i==0) { ?>
									<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
									<?php } else { ?>
									<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterDelete(<?php echo $i;?>);">
									<?php } ?>
								<?php } ?>
                                        <input name="suster_tot" id="suster_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_suster_del","hid_suster_del",'');?>
				</table>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Jam</td>
               <td align="left" class="tablecontent-odd" colspan=3>
				<select name="op_mulai_jam" class="inputField" >
					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_mulai_jam"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>:
				<select name="op_mulai_menit" class="inputField" >
					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_mulai_menit"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				s/d
				<select name="op_selesai_jam" class="inputField" >
					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_jam"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>:
				<select name="op_selesai_menit" class="inputField" >
					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_menit"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderComboBox("op_jenis","op_jenis",$optOperasiJenis,null,null,null);?>               
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Kode ICD-9 CM</td>
               <td align="left" class="tablecontent-odd" width="20%" colspan="3"> 
                    <table width="100%" border="1" cellpadding="2" cellspacing="1" id="tb_icd">
                         <?php if(!$dataIcd) {?>
                              <tr id="tr_icd_0">
                                   <td width="">
                                        <?php echo $view->RenderTextBox("op_icd_kode[0]","op_icd_kode_0","10","100",$_POST["op_icd_kode"][0],"inputField", "readonly",false);?>
                                        <a href="<?php echo $icdPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                                        <input type="hidden" id="id_icd_0" name="id_icd[0]" value="<?php echo $_POST["id_icd"][0];?>"/>
                                   </td>
                                   <td>
                                        <?php echo $view->RenderTextBox("op_icd_nama[0]","op_icd_nama_0","50","100",$_POST["op_icd_nama"][0],"inputField", "readonly",false);?>                    
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
     							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="IcdTambah();">
     							<input name="icd_tot" id="icd_tot" type="hidden" value="0">
     			               </td>
                              </tr>
                         <?php } else {
                              for($i=0,$n=count($dataIcd);$i<$n;$i++) {?>
                                   <tr id="tr_icd_<?php echo $i?>">
                                        <td>
                                             <?php echo $view->RenderTextBox("op_icd_kode[]","op_icd_kode_".$i,"10","100",$dataIcd[$i]["op_icd_kode"],"inputField", "readonly",false);?>
                                             <a href="<?php echo $icdPage;?>&el=<?php echo $i?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                                             <input type="hidden" id="id_icd_<?php echo $i?>" name="id_icd[]" value="<?php echo $dataIcd[$i]["id_icd"];?>"/>
                                        </td>
                                        <td>
                                             <?php echo $view->RenderTextBox("op_icd_nama[]","op_icd_nama_".$i,"50","100",$dataIcd[$i]["op_icd_nama"],"inputField", "readonly",false);?>
                                        </td>
                                        <td align="left" class="tablecontent-odd" width="30%">
                                             <?php if($i==0) {?>
          							   <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="IcdTambah();">
          							<?php } else {?>
          							   <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="IcdDelete(<?php echo $i;?>);">
          							<?php }?>
          							
          							<input name="icd_tot" id="icd_tot" type="hidden" value="<?php echo $n?>">
          			               </td>
                                   </tr>
                              <?php } 
                         }
                         echo $view->RenderHidden("hid_icd_del","hid_icd_del",'');?>
                    </table>
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">INA DRG</td>
               <td align="left" class="tablecontent-odd" width="20%"> 
                    <?php echo $view->RenderTextBox("op_ina_kode","op_ina_kode","10","100",$_POST["op_ina_kode"],"inputField", "readonly",false);?>
                    <a href="<?php echo $inaPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" id="id_ina" name="id_ina" value="<?php echo $_POST["id_ina"];?>"/>
               </td>

               <td align="left" width="20%" class="tablecontent">Jenis Procedure</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_ina_nama","op_ina_nama","50","100",$_POST["op_ina_nama"],"inputField", "readonly",false);?>               
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Tindakan Operasi</td>
               <td align="left" class="tablecontent-odd" width="20%"> 
                    <?php echo $view->RenderTextBox("op_tindakan","op_tindakan","30","100",$_POST["op_tindakan"],"inputField", null,false);?>
               </td>

               <td align="left" width="20%" class="tablecontent">Paket Biaya</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderComboBox("op_paket_biaya","op_paket_biaya",$optOperasiPaket,null,null,null);?>                              
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Prosedur Operasi</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <table width="100%" border="1" cellpadding="1" cellspacing="1">
                         <tr>
                              <td align="left" class="tablecontent" width="20%">Conj. Flap</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("op_conj","op_conj",$optConj,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Cauter</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("op_cauter","op_cauter",$optCauter,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Corneal Enter</td>
                              <td align="left" class="tablecontent-odd">
                                   Jam <?php echo $view->RenderTextBox("op_corneal_enter_jam","op_corneal_enter_jam","10","100",$_POST["op_corneal_enter_jam"],"inputField", null,false);?>
                                   Diperluas <?php echo $view->RenderTextBox("op_corneal_enter_diperluas","op_corneal_enter_diperluas","10","100",$_POST["op_corneal_enter_diperluas"],"inputField", null,false);?>
                                   Jam <?php echo $view->RenderTextBox("op_corneal_enter_jam1","op_corneal_enter_jam1","10","100",$_POST["op_corneal_enter_jam1"],"inputField", null,false);?> - 
                                   <?php echo $view->RenderTextBox("op_corneal_enter_jam2","op_corneal_enter_jam2","10","100",$_POST["op_corneal_enter_jam2"],"inputField", null,false);?>
                              </td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Indirectomy</td>
                              <td align="left" class="tablecontent-odd">
                                   <?php echo $view->RenderComboBox("op_indirectomy","op_indirectomy",$optIndirectomy,null,null,null);?>
                                   Tipe <?php echo $view->RenderTextBox("op_indirectomy_tipe","op_indirectomy_tipe","10","100",$_POST["op_indirectomy_tipe"],"inputField", null,false);?>
                                   Jam <?php echo $view->RenderTextBox("op_indirectomy_jam","op_indirectomy_jam","10","100",$_POST["op_indirectomy_jam"],"inputField", null,false);?> 
                              </td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Nucleus Removal</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("op_nucleus_removal","op_nucleus_removal",$optNucleus,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Cortex Removal</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("op_cortex_removal","op_cortex_removal",$optCortex,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Corneal Suture</td>
                              <td align="left" class="tablecontent-odd">
                                   <?php echo $view->RenderComboBox("op_corneal_suture","op_corneal_suture",$optCorneal,null,null,null);?>
                                   Ukuran <?php echo $view->RenderTextBox("op_corneal_suture_ukuran","op_corneal_suture_ukuran","10","100",$_POST["op_corneal_suture_ukuran"],"inputField", null,false);?> 
                              </td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Type Suture</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("op_suture_tipe","op_suture_tipe",$optTypeSuture,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">COA Form With</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("op_coa","op_coa",$optCOA,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Obat/Bahan</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("op_obat","op_obat",$optObat,null,null,null);?></td>
                         </tr>
                    </table>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Metode Operasi</td>
               <td align="left" class="tablecontent-odd" colspan=3><?php echo $view->RenderComboBox("id_op_metode","id_op_metode",$metodeOp,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Komplikasi Durante OP</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <?php for($i=0,$n=count($dataDurop);$i<$n;$i++) { ?>                    
                         <?php echo $view->RenderCheckBox("id_durop_komp[".$dataDurop[$i]["durop_komp_id"]."]","id_durop_komp_".$dataDurop[$i]["durop_komp_id"],"y","null",($_POST["id_durop_komp"][$dataDurop[$i]["durop_komp_id"]] == "y")?"checked":"");?>
                         <label for="id_durop_komp_<?php echo $dataDurop[$i]["durop_komp_id"];?>"><?php echo $dataDurop[$i]["durop_komp_nama"];?></label><BR>
                    <?php } ?>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Manajemen Komplikasi</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderTextBox("op_komplikasi_manajemen","op_komplikasi_manajemen","50","100",$_POST["op_komplikasi_manajemen"],"inputField", null,false);?>               
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Jenis IOL</td>
               <td align="left" class="tablecontent-odd" colspan=3><?php echo $view->RenderComboBox("op_iol_jenis","op_iol_jenis",$optIOLJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Merk</td>
               <td align="left" class="tablecontent-odd" colspan=3><?php echo $view->RenderComboBox("op_iol_merk","op_iol_merk",$optIOLMerk,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power</td>
               <td align="left" class="tablecontent-odd" colspan=3><?php echo $view->RenderTextBox("op_iol_power","op_iol_power","10","200",$_POST["op_iol_power"],"inputField", null,false);?> Dioptri</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Posisi IOL Terpasang</td>
               <td align="left" class="tablecontent-odd" colspan=3><?php echo $view->RenderComboBox("op_iol_posisi","op_iol_posisi",$optIOLPos,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pesan Khusus Dari Operator</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderTextArea("op_pesan_operator","op_pesan_operator","3","40",$_POST["op_pesan_operator"]);?>               
               </td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,"onClick=\"return CheckSimpan(this.form);\"");?></td>
		</tr>
	</table>
     </fieldset>

     </td>
</tr>	

</table>


<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="op_id" value="<?php echo $_POST["op_id"];?>"/>
<input type="hidden" name="reg_jenis_pasien" id="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
</span>

</form>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
