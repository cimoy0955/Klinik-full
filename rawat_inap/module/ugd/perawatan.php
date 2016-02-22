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
     
 	if(!$auth->IsAllowed("perawatan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("perawatan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "perawatan.php";
     $icdPage = "icd_find.php?";
     $inaPage = "ina_find.php?";
     $terapiPage = "obat_find.php?";
	$dokterPage = "rawat_dokter_find.php?";
	$susterPage = "rawat_suster_find.php?";
     $backPage = "perawatan_view.php?";

     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetPerawatan,SetPerawatan,GetTonometri,GetDosis");     

     function GetTonometri($scale,$weight) {
          global $dtaccess; 
               
          $sql = "select tono_pressure from klinik.klinik_tonometri where tono_scale = ".QuoteValue(DPE_NUMERIC,$scale)." and tono_weight = ".QuoteValue(DPE_NUMERIC,$weight);
          $dataTable = $dtaccess->Fetch($sql);
          return $dataTable["tono_pressure"];

     }     

     function GetPerawatan($status) {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, a.reg_jadwal
                    from klinik.klinik_registrasi a
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.reg_ugd = 'y' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
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
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;


		if($status==0) {
			$tbHeader[0][$counterHeader][TABLE_ISI] = "Bayar";
			$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
			$counterHeader++;
		}
		
		$tbHeader[0][$counterHeader][TABLE_ISI] = "Jadwal";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;
          
          
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
			if($status==0) {
				if(!$dataTable[$i]["fol_lunas"]) $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesPerawatan(\''.$dataTable[$i]["reg_id"].'\')"/>';
			} else {
				$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
			}
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          
			if($status==0) {
				if(!$dataTable[$i]["fol_lunas"]) $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/on.gif" style="cursor:pointer" alt="Lunas" title="Lunas" border="0"/>';
				else $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/off.gif" style="cursor:pointer" alt="Belum Lunas" title="Belum Lunas" border="0"/>';
				$tbContent[$i][$counter][TABLE_ALIGN] = "center";
				$counter++;
			}
			
			if($dataTable[$i]["reg_jadwal"]=='y') $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="15" height="15" src="'.$APLICATION_ROOT.'images/off.gif" alt="Terjadwal Operasi Hari Ini" title="Terjadwal Operasi Hari Ini" border="0"/>';
			else $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/on.gif" alt="Tidak Terjadwal Operasi Hari Ini" title="Tidak Terjadwal Operasi Hari Ini" border="0"/>';
               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }

          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}

     function SetPerawatan($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_PEMERIKSAAN.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
     
     function GetDosis($fisik,$akhir,$id=null) {
          global $dtaccess, $view;
          
          $sql = "select dosis_id, dosis_nama from inventori.inv_dosis where id_fisik = ".QuoteValue(DPE_NUMERIC,$fisik);
          $dataTable = $dtaccess->FetchAll($sql);

          $optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show); 
          for($i=0,$n=count($dataTable);$i<$n;$i++) {
               $show = ($id==$dataTable[$i]["dosis_id"]) ? "selected":"";
               $optDosis[$i+1] = $view->RenderOption($dataTable[$i]["dosis_id"],$dataTable[$i]["dosis_nama"],$show); 
          }
          
          return $view->RenderComboBox("id_dosis[]","id_dosis_".$akhir,$optDosis,null,null,null);
     }
     
     
     if(!$_POST["btnSave"] && !$_POST["btnUpdate"]) {
          // --- buat cari suster yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id 
                    from klinik.klinik_perawatan_suster a 
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat 
                    where c.rawat_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"));
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["rawat_suster_nama"][$i]) $_POST["rawat_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     }
	
	if(!$_POST["btnSave"] && !$_POST["btnUpdate"]) {
	
     	// --- buat cari pemeriksaan yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
                    where c.rawat_tanggal = ".QuoteValue(DPE_DATE,date('Y-m-d')); 
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["ref_suster_nama"][$i]) $_POST["ref_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     
     }


     if ($_GET["id"]) {
          // === buat ngedit ---          
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["rawat_id"] = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.id_reg from klinik.klinik_perawatan a 
				where rawat_id = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $_GET["id_reg"] = $row_edit["id_reg"];
     }     
     
     
     // --- cari input pemeriksaan pertama hari ini ---
     $sql = "select a.rawat_id 
               from klinik.klinik_perawatan a 
               where a.rawat_tanggal = ".QuoteValue(DPE_CHAR,date('Y-m-d'))." 
               order by rawat_waktu asc limit 1"; 
     $rs = $dtaccess->Execute($sql);
     $firstData = $dtaccess->Fetch($rs);
	
     $edit = (($firstData["rawat_id"]==$_POST["rawat_id"])||!$firstData["rawat_id"])?true:false;
      
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, 
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                    from klinik.klinik_registrasi a
				            left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          //echo $sql;
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
          $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
		$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
          
          $diagLink = "perawatan_diag.php?id_cust_usr=".$enc->Encode($dataPasien["id_cust_usr"])."&id_reg=".$enc->Encode($_GET["id_reg"]);
        
      $sql = "select * from klinik.klinik_ugd where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
      //echo $sql;
      $dataUgd= $dtaccess->Fetch($sql);  
          
          $sql = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          
          $dataPemeriksaan = $dtaccess->Fetch($sql);
          if($dataPemeriksaan) $_x_mode = "Diag";

          $view->CreatePost($dataPemeriksaan);


          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster"][$i] = $row["pgw_id"];
               $_POST["rawat_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }


          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_dokter"] = $row["pgw_id"];
          $_POST["rawat_dokter_nama"] = $row["pgw_nama"];

          // --- icd od
          $sql = "select icd_nama,icd_nomor, icd_id from klinik.klinik_perawatan_icd a
                    join klinik.klinik_icd b on a.id_icd = b.icd_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_icd_odos = 'OD'
                    order by rawat_icd_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_icd_od_id"][$i] = $row["icd_id"];
               $_POST["rawat_icd_od_kode"][$i] = $row["icd_nomor"];
               $_POST["rawat_icd_od_nama"][$i] = $row["icd_nama"];
               $i++;
          }

          // --- icd os
          $sql = "select icd_nama,icd_nomor, icd_id from klinik.klinik_perawatan_icd a
                    join klinik.klinik_icd b on a.id_icd = b.icd_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_icd_odos = 'OS'
                    order by rawat_icd_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_icd_os_id"][$i] = $row["icd_id"];
               $_POST["rawat_icd_os_kode"][$i] = $row["icd_nomor"];
               $_POST["rawat_icd_os_nama"][$i] = $row["icd_nama"];
               $i++;
          }


          // --- ina od
          $sql = "select ina_nama,ina_kode, ina_id from klinik.klinik_perawatan_ina a
                    join klinik.klinik_ina b on a.id_ina = b.ina_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_ina_odos = 'OD'
                    order by rawat_ina_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_ina_od_id"][$i] = $row["ina_id"];
               $_POST["rawat_ina_od_kode"][$i] = $row["ina_kode"];
               $_POST["rawat_ina_od_nama"][$i] = $row["ina_nama"];
               $i++;
          }
          
          
          // --- ina os
          $sql = "select ina_nama,ina_kode, ina_id from klinik.klinik_perawatan_ina a
                    join klinik.klinik_ina b on a.id_ina = b.ina_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_ina_odos = 'OS'
                    order by rawat_ina_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_ina_os_id"][$i] = $row["ina_id"];
               $_POST["rawat_ina_os_kode"][$i] = $row["ina_kode"];
               $_POST["rawat_ina_os_nama"][$i] = $row["ina_nama"];
               $i++;
          }

		//-- ina klaim--
          $sql = "select ina_nama, ina_kode, ina_id 
				from klinik.klinik_registrasi_ina a
                    join klinik.klinik_ina b on a.id_ina = b.ina_id
				where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])."
				and reg_ina_jenis = ".QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
          $rs = $dtaccess->Execute($sql);
		$dataKlaim = $dtaccess->Fetch($rs);
		
		$_POST["rawat_ina_klaim_id"][0] = $dataKlaim["ina_id"];
		$_POST["rawat_ina_klaim_nama"][0] = $dataKlaim["ina_nama"];
		$_POST["rawat_ina_klaim_kode"][0] = $dataKlaim["ina_kode"];
		
            // --- terapi obat
          $sql = "select item_id, item_nama,item_fisik, id_dosis from klinik.klinik_perawatan_terapi a
                    left join inventori.inv_item b on a.id_item = b.item_id
                    where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." 
                    order by rawat_item_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_item"][$i] = $row["id_item"];
               $_POST["item_fisik"][$i] = $row["item_fisik"];
               $_POST["item_nama"][$i] = $row["item_nama"];
               $_POST["id_dosis"][$i] = $row["id_dosis"];
               $i++;
          }

	} 
	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
	
          
          // --- delete data e dulu ---
          if($_POST["btnSave"]) {               
               $sql = "delete from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
          }
          
    $sql = "update klinik.klinik_registrasi set reg_ugd = 'n' 
      where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql);

          $dbTable = "klinik.klinik_perawatan";
          $dbField[0] = "rawat_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "rawat_keluhan";
          $dbField[3] = "rawat_keadaan_umum";
          $dbField[4] = "rawat_tonometri_scale_od";
          $dbField[5] = "rawat_anel";
          $dbField[6] = "rawat_schimer";
          $dbField[7] = "rawat_lab_gula_darah";
          $dbField[8] = "rawat_lab_darah_lengkap";
          $dbField[9] = "rawat_lab_tensi";
          $dbField[10] = "rawat_lab_nadi";
          $dbField[11] = "rawat_lab_nafas";
          $dbField[12] = "rawat_lab_alergi";
          $dbField[13] = "rawat_mata_od_palpebra";
          $dbField[14] = "rawat_mata_os_palpebra";
          $dbField[15] = "rawat_mata_od_conjunctiva";
          $dbField[16] = "rawat_mata_os_conjunctiva";
          $dbField[17] = "rawat_mata_od_cornea";
          $dbField[18] = "rawat_mata_os_cornea";
          $dbField[19] = "rawat_mata_od_coa";
          $dbField[20] = "rawat_mata_os_coa";
          $dbField[21] = "rawat_mata_od_iris";
          $dbField[22] = "rawat_mata_os_iris";
          $dbField[23] = "rawat_mata_od_pupil";
          $dbField[24] = "rawat_mata_os_pupil";
          $dbField[25] = "rawat_mata_od_lensa";
          $dbField[26] = "rawat_mata_os_lensa";
          $dbField[27] = "rawat_mata_od_ocular";
          $dbField[28] = "rawat_mata_os_ocular";
          $dbField[29] = "rawat_mata_od_retina";
          $dbField[30] = "rawat_mata_os_retina";
          $dbField[31] = "id_cust_usr";
          $dbField[32] = "rawat_tonometri_weight_od";
          $dbField[33] = "rawat_tonometri_pressure_od";
          $dbField[34] = "rawat_mata_foto";          
          $dbField[35] = "rawat_mata_sketsa";
          $dbField[36] = "rawat_tonometri_od";
          $dbField[37] = "rawat_tonometri_os";
          $dbField[38] = "rawat_anestesis_jenis";
          $dbField[39] = "rawat_anestesis_obat";
          $dbField[40] = "rawat_anestesis_dosis";
          $dbField[41] = "rawat_anestesis_komp";
          $dbField[42] = "rawat_anestesis_pre";
          $dbField[43] = "rawat_operasi_jenis";
          $dbField[44] = "rawat_operasi_paket";
          $dbField[45] = "rawat_tonometri_weight_os";
          $dbField[46] = "rawat_tonometri_pressure_os";
          $dbField[47] = "rawat_tonometri_scale_os";
          $dbField[48] = "rawat_color_blindness";
          $dbField[49] = "rawat_catatan";
          $dbField[50] = "rawat_irigasi";
          $dbField[51] = "rawat_epilasi";
          $dbField[52] = "rawat_suntikan";
          $dbField[53] = "rawat_probing";
          $dbField[54] = "rawat_flouorecsin";
          $dbField[55] = "rawat_kesehatan";
          $dbField[56] = "rawat_kacamata_refraksi";
          $dbField[57] = "rawat_mata_od_koreksi_spheris";
          $dbField[58] = "rawat_mata_od_koreksi_cylinder";
          $dbField[59] = "rawat_mata_od_koreksi_sudut";
          $dbField[60] = "rawat_mata_os_koreksi_spheris";
          $dbField[61] = "rawat_mata_os_koreksi_cylinder";
          $dbField[62] = "rawat_mata_os_koreksi_sudut";
          $dbField[63] = "rawat_tanggal";
          $dbField[64] = "rawat_od_vitreus"; 
          $dbField[65] = "rawat_os_vitreus"; 
                    
          if($_POST["btnSave"]) $dbField[66] = "rawat_waktu";
          
          
          if(!$_POST["rawat_id"]) $_POST["rawat_id"] = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["rawat_keluhan"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["rawat_keadaan_umum"]);
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_scale_od"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["rawat_anel"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["rawat_schimer"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_gula_darah"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_darah_lengkap"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_tensi"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_nadi"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_nafas"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_alergi"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_palpebra"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_palpebra"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_conjunctiva"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_conjunctiva"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_cornea"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_cornea"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_coa"]);
          $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_coa"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_iris"]);
          $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_iris"]);
          $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_pupil"]);
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_pupil"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_lensa"]);
          $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_lensa"]);
          $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_ocular"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_ocular"]);
          $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_retina"]);
          $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_retina"]);
          $dbValue[31] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[32] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_weight_od"]);
          $dbValue[33] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_pressure_od"]);
          $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_foto"]);          
          $dbValue[35] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_sketsa"]);
          $dbValue[36] = QuoteValue(DPE_CHAR,$_POST["rawat_tonometri_od"]);
          $dbValue[37] = QuoteValue(DPE_CHAR,$_POST["rawat_tonometri_os"]);
          $dbValue[38] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_jenis"]);
          $dbValue[39] = QuoteValue(DPE_NUMERICKEY,$_POST["rawat_anestesis_obat"]);
          $dbValue[40] = QuoteValue(DPE_CHAR,$_POST["rawat_anestesis_dosis"]);
          $dbValue[41] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_komp"]);
          $dbValue[42] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_pre"]);
          $dbValue[43] = QuoteValue(DPE_CHARKEY,$_POST["rawat_operasi_jenis"]);
          $dbValue[44] = QuoteValue(DPE_CHARKEY,$_POST["rawat_operasi_paket"]);
          $dbValue[45] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_weight_os"]);
          $dbValue[46] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_pressure_os"]);
          $dbValue[47] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_scale_os"]);
          $dbValue[48] = QuoteValue(DPE_CHAR,$_POST["rawat_color_blindness"]);
          $dbValue[49] = QuoteValue(DPE_CHAR,$_POST["rawat_catatan"]);
          $dbValue[50] = QuoteValue(DPE_CHAR,$_POST["rawat_irigasi"]);
          $dbValue[51] = QuoteValue(DPE_CHAR,$_POST["rawat_epilasi"]);
          $dbValue[52] = QuoteValue(DPE_CHAR,$_POST["rawat_suntikan"]);
          $dbValue[53] = QuoteValue(DPE_CHAR,$_POST["rawat_probing"]);
          $dbValue[54] = QuoteValue(DPE_CHAR,$_POST["rawat_flouorecsin"]);
          $dbValue[55] = QuoteValue(DPE_CHAR,$_POST["rawat_kesehatan"]);
          $dbValue[56] = QuoteValue(DPE_CHAR,$_POST["rawat_kacamata_refraksi"]);
          $dbValue[57] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_spheris"]);
          $dbValue[58] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_cylinder"]);
          $dbValue[59] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_sudut"]);
          $dbValue[60] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_spheris"]);
          $dbValue[61] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_cylinder"]);
          $dbValue[62] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_sudut"]);
          $dbValue[63] = QuoteValue(DPE_DATE,date("Y-m-d"));
          $dbValue[64] = QuoteValue(DPE_CHAR,$_POST["rawat_od_vitreus"]); 
          $dbValue[65] = QuoteValue(DPE_CHAR,$_POST["rawat_os_vitreus"]); 
          
          if($_POST["btnSave"]) $dbValue[66] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

          
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
          
          
          // -- ini insert ke tabel rawat icd
		$sql = "delete from klinik.klinik_perawatan_icd where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_icd";
          $dbField[0] = "rawat_icd_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_icd";
          $dbField[3] = "rawat_icd_urut";
          $dbField[4] = "rawat_icd_odos";
          
          for($i=0,$n=count($_POST["rawat_icd_od_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_od_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OD");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_od_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }


          for($i=0,$n=count($_POST["rawat_icd_os_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_os_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OS");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_os_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }

			
		// -- ini insert ke tabel registrasi ina 
		$sql = "delete from klinik.klinik_registrasi_ina
				where id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql);
		
          if($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_JAMKESNAS_PUSAT) {
	
			$dbTable = "klinik.klinik_registrasi_ina";
			$dbField[0] = "reg_ina_id";   // PK
			$dbField[1] = "reg_ina_nama";
			$dbField[2] = "id_ina"; 
			$dbField[3] = "id_cust_usr";
			$dbField[4] = "id_reg";
			$dbField[5] = "reg_ina_jenis";
			$dbField[6] = "reg_ina_when";
			$dbField[7] = "reg_ina_who";
				
				$regInaId = $dtaccess->GetTransID();
				$dbValue[0] = QuoteValue(DPE_CHARKEY,$regInaId);
				$dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_klaim_nama"][0]);
				$dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_klaim_id"][0]);
				$dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
				$dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
				$dbValue[5] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
				$dbValue[6] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
				$dbValue[7] = QuoteValue(DPE_CHAR,$userData["name"]);
	
				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	
				$dtmodel->Insert() or die("insert  error");
				
				unset($dtmodel);
				unset($dbValue);
				unset($dbKey);
				unset($dbTable);
				
				$sql = "select * from klinik.klinik_ina_split
						where id_ina = ".QuoteValue(DPE_CHAR,$_POST["rawat_ina_klaim_id"][0])." and ina_split_nominal > 0 "; 
				$dataInaSplit = $dtaccess->FetchAll($sql);
				
				$dbTable = "klinik.klinik_registrasi_ina_split";
				
				$dbField[0] = "reg_ina_split_id";   // PK
				$dbField[1] = "id_reg";
				$dbField[2] = "id_reg_ina";
				$dbField[3] = "id_ina_split";
				$dbField[4] = "reg_ina_split_nominal";
				
				for($i=0,$n=count($dataInaSplit);$i<$n;$i++) {
					$dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
					$dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
					$dbValue[2] = QuoteValue(DPE_CHARKEY,$regInaId);
					$dbValue[3] = QuoteValue(DPE_CHARKEY,$dataInaSplit[$i]["ina_split_id"]);
					$dbValue[4] = QuoteValue(DPE_NUMERIC,$dataInaSplit[$i]["ina_split_nominal"]);
			
					$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
					$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
			
					$dtmodel->Insert() or die("insert  error");
					
					unset($dtmodel);
					unset($dbValue);
					unset($dbKey);
					
					$totalInoSplit += $dataInaSplit[$i]["ina_split_nominal"];
				}
				
				// -- ini update total nya 
				$sql = "update klinik.klinik_registrasi_ina set reg_ina_nominal = ".QuoteValue(DPE_NUMERIC,$totalInoSplit)."
						where reg_ina_id = ".QuoteValue(DPE_CHAR,$regInaId);
				$dtaccess->Execute($sql); 
				
		}
          
          // -- ini insert ke tabel rawat icd
		$sql = "delete from klinik.klinik_perawatan_ina where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_ina";
          $dbField[0] = "rawat_ina_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_ina";
          $dbField[3] = "rawat_ina_urut";
          $dbField[4] = "rawat_ina_odos";
          
          for($i=0,$n=count($_POST["rawat_ina_od_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_od_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHARKEY,"OD");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_ina_od_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }

          for($i=0,$n=count($_POST["rawat_ina_os_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_os_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHARKEY,"OS");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_ina_os_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }

          
          // -- ini insert ke tabel rawat terapi
		$sql = "delete from klinik.klinik_perawatan_terapi where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_terapi";
          $dbField[0] = "rawat_item_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_item";
          $dbField[3] = "rawat_item_urut";
          $dbField[4] = "id_dosis";
          
          for($i=0,$n=count($_POST["id_item"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["id_item"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHARKEY,$_POST["id_dosis"][$i]);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["id_item"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
          
          
          // --- insrt suster ---
          /*
               // --- cari data pemeriksaan hari ini ---
               $sql = "select rawat_id
                         from klinik.klinik_perawatan
                         where rawat_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"));
               
               if(!$edit) $sql .= " and rawat_id = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]); 
               $rs = $dtaccess->Execute($sql);
               $dataRawat = $dtaccess->FetchAll($rs);               
               
          $sqlDelete = "delete from klinik.klinik_perawatan_suster 
                         where id_rawat in ( ".$sql." )";
                         
          */
          
          $sqlDelete = "delete from klinik.klinik_perawatan_suster where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]); 
          
          $dtaccess->Execute($sqlDelete);               
          
          //for($i=0,$n=count($dataRawat);$i<$n;$i++) {
               foreach($_POST["id_suster"] as $key => $value){
                    if($value) {
                         $dbTable = "klinik_perawatan_suster";
                    
                         $dbField[0] = "rawat_suster_id";   // PK
                         $dbField[1] = "id_rawat";
                         $dbField[2] = "id_pgw";
                                
                         $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                         //$dbValue[1] = QuoteValue(DPE_CHAR,$dataRawat[$i]["rawat_id"]);
                         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
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
          //}

          // --- insrt dokter ---
          $sql = "delete from klinik.klinik_perawatan_dokter where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_dokter"]) {
               
               $dbTable = "klinik_perawatan_dokter";
               
               $dbField[0] = "rawat_dokter_id";   // PK
               $dbField[1] = "id_rawat";
               $dbField[2] = "id_pgw";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
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
          
		$sql = "update global.global_customer_user set cust_usr_alergi = ".QuoteValue(DPE_CHAR,$_POST["rawat_lab_alergi"])." where cust_usr_id = ".$_POST["id_cust_usr"];
          $dtaccess->Execute($sql);
          
          
          if($_POST["_x_mode"]!="Edit") {
               
               $sql = "update klinik.klinik_registrasi set reg_status = '".$_POST["cmbNext"].STATUS_ANTRI."', reg_waktu = CURRENT_TIME where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql); 
               
               // --- nyimpen paket klaim e ---
               if($_POST["cmbNext"]==STATUS_SELESAI && $_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA) { 
               
                    $sql = "select b.paket_klaim_id, paket_klaim_total 
                              from klinik.klinik_biaya_pasien a
                              join klinik.klinik_paket_klaim b on a.id_paket_klaim = b.paket_klaim_id  
                              where a.biaya_pasien_status = ".QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN)."
                              and a.biaya_pasien_jenis = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]);
                    $rs = $dtaccess->Execute($sql);
                    
                    // --- delete dulu data yg lama ---
                    $sql = "delete from klinik.klinik_registrasi_klaim
                              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                    $dtaccess->Execute($sql);                    
                    
                    while($row = $dtaccess->Fetch($rs)) {
                    
                         $dbTable = "klinik.klinik_registrasi_klaim";
               
                         $dbField[0] = "reg_klaim_id";   // PK
                         $dbField[1] = "id_reg";
                         $dbField[2] = "id_paket_klaim";
                         $dbField[3] = "reg_klaim_nominal";
                         $dbField[4] = "reg_klaim_when";
                         $dbField[5] = "reg_klaim_who";
                         $dbField[6] = "reg_klaim_jenis";
                              
                              $klaimId =  $dtaccess->GetTransID();
                              $dbValue[0] = QuoteValue(DPE_CHAR,$klaimId);
                              $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                              $dbValue[2] = QuoteValue(DPE_CHAR,$row["paket_klaim_id"]);
                              $dbValue[3] = QuoteValue(DPE_NUMERIC,$row["paket_klaim_total"]);
                              $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                              $dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
                              $dbValue[6] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
                         
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField);
                         unset($dbValue);
                         unset($dbKey);
                         
                         
                         // --- ngisi tbl split e ---
                         $sql = "select klaim_split_id, klaim_split_nominal 
                                   from klinik.klinik_paket_klaim_split 
                                   where klaim_split_nominal > 0 
                                   and id_paket_klaim = ".QuoteValue(DPE_CHAR,$row["paket_klaim_id"]);
                         $rsSplit = $dtaccess->Execute($sql);
                         
                         while($rowSplit = $dtaccess->Fetch($rsSplit)) {
                         
                              $dbTable = "klinik.klinik_registrasi_klaim_split";
               
                              $dbField[0] = "reg_klaim_split_id";   // PK
                              $dbField[1] = "id_reg";
                              $dbField[2] = "id_klaim_split";
                              $dbField[3] = "id_reg_klaim";
                              $dbField[4] = "reg_klaim_split_nominal";
                                     
                                   $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                                   $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                                   $dbValue[2] = QuoteValue(DPE_CHAR,$rowSplit["klaim_split_id"]);
                                   $dbValue[3] = QuoteValue(DPE_CHAR,$klaimId);
                                   $dbValue[4] = QuoteValue(DPE_NUMERIC,$rowSplit["klaim_split_nominal"]);
                              
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
     
               
               // -- insert ke folio jika data gula diisi ---
               $sql = "select diag_id from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataDiag = $dtaccess->Fetch($sql);
          
     
               if(!$dataDiag) {
		
				$sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
						and id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])." and fol_jenis = '".STATUS_PEMERIKSAAN."'";
				$dtaccess->Execute($sql);

                    if($_POST["rawat_kesehatan"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_UJIMATA);

				if($sql_where) {
					$sql = "select * from klinik.klinik_biaya where ".implode(" or ",$sql_where);
					$dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
					$folWaktu = date("Y-m-d H:i:s");
				}               
               
	               $lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 


                    
				for($i=0,$n=count($dataBiaya);$i<$n;$i++) {
					$dbTable = "klinik_folio";
				
					$dbField[0] = "fol_id";   // PK
					$dbField[1] = "id_reg";
					$dbField[2] = "fol_nama";
					$dbField[3] = "fol_nominal";
					$dbField[4] = "id_biaya";
					$dbField[5] = "fol_jenis";
					$dbField[6] = "id_cust_usr";
					$dbField[7] = "fol_waktu";
					$dbField[8] = "fol_lunas";
					$dbField[9] = "fol_jumlah";
                    $dbField[10] = "fol_nominal_satuan";
						  
					$folId = $dtaccess->GetTransID();
					$dbValue[0] = QuoteValue(DPE_CHAR,$folId);
					$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
					$dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_nama"]);
					$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
					$dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
					$dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_jenis"]);
					$dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
					$dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
					$dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
					$dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
					
					//if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
					$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
					$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
					
					$dtmodel->Insert() or die("insert error"); 
					
					unset($dtmodel);
					unset($dbField);
					unset($dbValue);
					unset($dbKey);

				 
					$sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"])." and bea_split_nominal > 0";
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
						$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["bea_split_nominal"]);
						 
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
               
          }
          
          
          if($_POST["_x_mode"]!="Edit") echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          exit();

	}
	
	foreach($rawatKeadaan as $key => $value) {
          unset($show);
          if($_POST["rawat_keadaan_umum"]==$key) $show="selected";
		$optionsKeadaan[] = $view->RenderOption($key,$value,$show);
	}


     $sql = "select diag_id from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     $dataDiag = $dtaccess->Fetch($sql);
     
     $count=0;	
	$optionsNext[$count] = $view->RenderOption(STATUS_SELESAI,"Tidak Perlu Tindakan",$show); $count++;
	if(!$dataDiag){ $optionsNext[$count] = $view->RenderOption(STATUS_RAWATINAP,"Rawat Inap",$show); $count++; }
	$optionsNext[$count] = $view->RenderOption(STATUS_MENINGGAL,"Pasien Meninggal",$show); $count++;

     $lokasi = $APLICATION_ROOT."images/foto_perawatan";
	$fotoName = ($_POST["rawat_mata_foto"]) ? $lokasi."/".$_POST["rawat_mata_foto"] : $lokasi."/default.jpg";
	$sketsaName = ($_POST["rawat_mata_sketsa"]) ? $lokasi."/".$_POST["rawat_mata_sketsa"] : $lokasi."/default.jpg";


     // --- nyari datanya operasi jenis + harganya---
     $sql = "select op_jenis_id, op_jenis_nama from klinik.klinik_operasi_jenis";
     $dataOperasiJenis = $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi Jenis
     $optOperasiJenis[0] = $view->RenderOption("","[Pilih Jenis Operasi]",$show); 
     for($i=0,$n=count($dataOperasiJenis);$i<$n;$i++) {
          $show = ($_POST["rawat_operasi_jenis"]==$dataOperasiJenis[$i]["op_jenis_id"]) ? "selected":"";
          $optOperasiJenis[$i+1] = $view->RenderOption($dataOperasiJenis[$i]["op_jenis_id"],$dataOperasiJenis[$i]["op_jenis_nama"],$show); 
     }
     
     
     $sql = "select op_paket_id, op_paket_nama from klinik.klinik_operasi_paket";
     $dataOperasiPaket= $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi paket
     $optOperasiPaket[0] = $view->RenderOption("","[Pilih Paket Operasi]",$show); 
     for($i=0,$n=count($dataOperasiPaket);$i<$n;$i++) {
          $show = ($_POST["rawat_operasi_paket"]==$dataOperasiPaket[$i]["op_paket_id"]) ? "selected":"";
          $optOperasiPaket[$i+1] = $view->RenderOption($dataOperasiPaket[$i]["op_paket_id"],$dataOperasiPaket[$i]["op_paket_nama"],$show); 
     }

     // --- nyari datanya anestesis---
     $sql = "select anes_jenis_id, anes_jenis_nama from klinik.klinik_anestesis_jenis";
     $dataAnestesisJenis = $dtaccess->FetchAll($sql);

     $sql = "select anes_komp_id, anes_komp_nama from klinik.klinik_anestesis_komplikasi";
     $dataAnestesisKomplikasi = $dtaccess->FetchAll($sql);

     $sql = "select anes_pre_id, anes_pre_nama from klinik.klinik_anestesis_premedikasi";
     $dataAnestesisPremedikasi = $dtaccess->FetchAll($sql);

     $sql = "select item_id, item_nama from inventori.inv_item where id_kat_item = ".QuoteValue(DPE_CHAR,KAT_OBAT_ANESTESIS);
     $dataAnestesisObat = $dtaccess->FetchAll($sql);

     // -- bikin combonya anestesis
     $optAnestesisJenis[0] = $view->RenderOption("","[Pilih Jenis Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisJenis);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_jenis"]==$dataAnestesisJenis[$i]["anes_jenis_id"]) ? "selected":"";
          $optAnestesisJenis[$i+1] = $view->RenderOption($dataAnestesisJenis[$i]["anes_jenis_id"],$dataAnestesisJenis[$i]["anes_jenis_nama"],$show); 
     }

     $optAnestesisKomplikasi[0] = $view->RenderOption("","[Pilih Komplikasi Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisKomplikasi);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_komp"]==$dataAnestesisKomplikasi[$i]["anes_komp_id"]) ? "selected":"";
          $optAnestesisKomplikasi[$i+1] = $view->RenderOption($dataAnestesisKomplikasi[$i]["anes_komp_id"],$dataAnestesisKomplikasi[$i]["anes_komp_nama"],$show); 
     }

     $optAnestesisPremedikasi[0] = $view->RenderOption("","[Pilih Premedikasi Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisPremedikasi);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_pre"]==$dataAnestesisPremedikasi[$i]["anes_pre_id"]) ? "selected":"";
          $optAnestesisPremedikasi[$i+1] = $view->RenderOption($dataAnestesisPremedikasi[$i]["anes_pre_id"],$dataAnestesisPremedikasi[$i]["anes_pre_nama"],$show); 
     }

     $optAnestesisObat[0] = $view->RenderOption("","[Pilih Obat Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisObat);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_obat"]==$dataAnestesisObat[$i]["item_id"]) ? "selected":"";
          $optAnestesisObat[$i+1] = $view->RenderOption($dataAnestesisObat[$i]["item_id"],$dataAnestesisObat[$i]["item_nama"],$show); 
     }

     $optTerapiObat[0] = $view->RenderOption("","[Pilih Obat Terapi]",$show); 
     for($i=0,$n=count($dataAnestesisObat);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_obat"]==$dataAnestesisObat[$i]["item_id"]) ? "selected":"";
          $optTerapiObat[$i+1] = $view->RenderOption($dataAnestesisObat[$i]["item_id"],$dataAnestesisObat[$i]["item_nama"],$show); 
     }
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>


<script type="text/javascript">

	function ajaxFileUpload()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajaxFileUpload
		(
			{
				url:'perawatan_upload.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							alert(data.msg);
                                   document.getElementById('rawat_mata_foto').value= data.file;
                                   document.img_foto.src='<?php echo $lokasi."/";?>'+data.file;
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

	}

	function ajaxFileUploadSketsa()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajaxFileUpload
		(
			{
				url:'perawatan_upload.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							alert(data.msg);
                                   document.getElementById('rawat_mata_sketsa').value= data.file;
                                   document.img_sketsa.src='<?php echo $lokasi."/";?>'+data.file;
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

	}
</script>	

<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetPerawatan(0,'target=antri_kiri_isi');     
     GetPerawatan(1,'target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}

function ProsesPerawatan(id) {
	SetPerawatan(id,'type=r');
	timer();
}

function SetTonometriOD(){
     var scale = document.getElementById('rawat_tonometri_scale_od');
     var weight = document.getElementById('rawat_tonometri_weight_od');

     if(scale.value && isNaN(scale.value)){
          scale.value = '';
          return false;
     }

     if(weight.value && isNaN(weight.value)){
          weight.value = '';
          return false;
     }

     GetTonometri(scale.value,weight.value,'target=rawat_tonometri_pressure_od');
     return true;
}

function SetTonometriOS(){
     var scale = document.getElementById('rawat_tonometri_scale_os');
     var weight = document.getElementById('rawat_tonometri_weight_os');

     if(scale.value && isNaN(scale.value)){
          scale.value = '';
          return false;
     }

     if(weight.value && isNaN(weight.value)){
          weight.value = '';
          return false;
     }

     GetTonometri(scale.value,weight.value,'target=rawat_tonometri_pressure_os');
     return true;
}

function SetDosis(fisik,akhir) {
     GetDosis(fisik,akhir,'target=sp_item_'+akhir);
     return true;
}

function CheckData(frm) {
	
	<?php if($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_JAMKESNAS_PUSAT) { ?> 
		if(!frm.rawat_ina_klaim_id_0.value){
			alert('Klaim INA DRG Harus diisi');
			frm.rawat_ina_klaim_kode_0.focus();
			return false;
		} 
	<?php } ?>


     return true;
}

timer();


function Tambah(){
     var akhir = eval(document.getElementById('hid_tot').value)+1;
     
     $('#tb_terapi').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_terapi_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:20, maxLength:100, name:'item_nama[]', id:'item_nama_'+akhir},[],
                         'a',{ href:'<?php echo $terapiPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_item[]', id:'id_item_'+akhir+''}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                    [
                         'span', {id:'sp_item_'+akhir+''}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { Delete(akhir) } );
     document.getElementById('item_nama_'+akhir).readOnly = true;
          
     document.getElementById('hid_tot').value = akhir;
     tb_init('a.thickbox');
}

function Delete(akhir){
     document.getElementById('hid_id_del').value += document.getElementById('id_item_'+akhir).value;
     
     $('#tr_terapi_'+akhir).remove();
}


function SusterTambah(){
     var akhir = eval(document.getElementById('suster_tot').value)+1;
     
     $('#tb_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'rawat_suster_nama[]', id:'rawat_suster_nama_'+akhir},[],
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
     document.getElementById('rawat_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterDelete(akhir){
     document.getElementById('hid_suster_del').value += document.getElementById('id_suster_'+akhir).value;
     
     $('#tr_suster_'+akhir).remove();
}




var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}



var _wnd_cetak;

function BukaWindowCetak(url,judul)
{
    if(!_wnd_cetak) {
			_wnd_cetak = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=600,left=100,top=100');
	} else {
		if (_wnd_cetak.closed) {
			_wnd_cetak = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=600,left=100,top=100');
		} else {
			_wnd_cetak.focus();
		}
	}
     return false;
}
</script>



<?php if(!$_GET["id"]) { ?>
<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<!--<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Pemeriksaan UGD</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetPerawatan(STATUS_ANTRI); ?></div>
	</div>-->
	
	<div id="antri_kanan" style="float:left;width:49%;">
		<div class="tableheader">Pemeriksaan UGD</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetPerawatan(STATUS_PROSES); ?></div>
	</div>
</div>

<?php } ?>



<?php if($_GET["id_reg"]) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Pemeriksaan UGD</td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="60%">

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
          <tr>
               <td width= "30%" align="left" class="tablecontent">Keluhan Pasien</td>
               <td width= "40%" align="left" class="tablecontent-odd"><?php echo $dataUgd["ugd_keterangan"]; ?></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Keadaan Umum</td>
               <td width= "40%" align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_keadaan_umum","rawat_keadaan_umum",$optionsKeadaan,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Tensi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_tensi","rawat_lab_tensi","15","15",$_POST["rawat_lab_tensi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Nadi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_nadi","rawat_lab_nadi","15","15",$_POST["rawat_lab_nadi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pernafasan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_nafas","rawat_lab_nafas","15","15",$_POST["rawat_lab_nafas"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Alergi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_alergi","rawat_lab_alergi","25","100",$_POST["rawat_lab_alergi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent"><span style="color:red">Rekap Medik</span></td>
               <td align="left" class="tablecontent-odd">
                    <a onClick="BukaWindow('rekap_medik.php?id_reg=<?php echo $_POST["id_reg"];?>&id_cust_usr=<?php echo $_POST["id_cust_usr"];?>','Rekap Medik')" href="#"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Rekap Medik" alt="Rekap Medik"/></a>
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Petugas</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="20%"  class="tablecontent" align="left">Dokter</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("rawat_dokter_nama","rawat_dokter_nama","30","100",$_POST["rawat_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>
          </tr>
     
          <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php if(!$_POST["rawat_suster_nama"]) { ?>
					<tr id="tr_suster_0">
						<td align="left" class="tablecontent-odd" width="70%">
							<?php echo $view->RenderTextBox("rawat_suster_nama[]","rawat_suster_nama_0","30","100",$_POST["rawat_suster_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
							<input type="hidden" id="id_suster_0" name="id_suster[]" value="<?php echo $_POST["id_suster"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="30%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
							<input name="suster_tot" id="suster_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $view->RenderTextBox("rawat_suster_nama[]","rawat_suster_nama_".$i,"30","100",$_POST["rawat_suster_nama"][$i],"inputField", "readonly",false);?>
                                        <?php //if($edit) {?>
                                             <a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                                        <?php //}?>
                                        <input type="hidden" id="id_suster_<?php echo $i;?>" name="id_suster[]" value="<?php echo $_POST["id_suster"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
                                        <?php// if($edit) {?>
                                             <?php if($i==0) { ?>
                                                  <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
                                             <?php } else { ?>
                                                  <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterDelete(<?php echo $i;?>);">
                                             <?php } ?>
                                        <?php// }?>
                                        <input name="suster_tot" id="suster_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_suster_del","hid_suster_del",'');?>
				</table>
               </td>
          </tr>
	</table>
     </fieldset>

     <!--
     <fieldset>
     <legend><strong>Pemeriksaan Mata</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="30%" align="center">Pemeriksaan</td>
               <td width="30%" align="center">OD</td>
               <td width="30%" align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Palpebra</td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_od_palpebra","rawat_mata_od_palpebra","30","30",$_POST["rawat_mata_od_palpebra"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_os_palpebra","rawat_mata_os_palpebra","30","30",$_POST["rawat_mata_os_palpebra"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Conjunctiva</td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_od_conjunctiva","rawat_mata_od_conjunctiva","30","30",$_POST["rawat_mata_od_conjunctiva"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_os_conjunctiva","rawat_mata_os_conjunctiva","30","30",$_POST["rawat_mata_os_conjunctiva"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Cornea</td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_od_cornea","rawat_mata_od_cornea","30","30",$_POST["rawat_mata_od_cornea"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_os_cornea","rawat_mata_os_cornea","30","30",$_POST["rawat_mata_os_cornea"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">COA</td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_od_coa","rawat_mata_od_coa","30","30",$_POST["rawat_mata_od_coa"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_os_coa","rawat_mata_os_coa","30","30",$_POST["rawat_mata_os_coa"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Iris</td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_od_iris","rawat_mata_od_iris","30","30",$_POST["rawat_mata_od_iris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_os_iris","rawat_mata_os_iris","30","30",$_POST["rawat_mata_os_iris"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pupil</td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_od_pupil","rawat_mata_od_pupil","30","30",$_POST["rawat_mata_od_pupil"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_os_pupil","rawat_mata_os_pupil","30","30",$_POST["rawat_mata_os_pupil"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Lensa</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_lensa","rawat_mata_od_lensa","30","30",$_POST["rawat_mata_od_lensa"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_lensa","rawat_mata_os_lensa","30","30",$_POST["rawat_mata_os_lensa"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Vitreus</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_od_vitreus","rawat_od_vitreus","3","27",$_POST["rawat_od_vitreus"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_os_vitreus","rawat_os_vitreus","3","27",$_POST["rawat_os_vitreus"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Ocular Movement</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_ocular","rawat_mata_od_ocular","30","30",$_POST["rawat_mata_od_ocular"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_ocular","rawat_mata_os_ocular","30","30",$_POST["rawat_mata_os_ocular"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Funduscopy (Retina)</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_retina","rawat_mata_od_retina","30","30",$_POST["rawat_mata_od_retina"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_retina","rawat_mata_os_retina","30","30",$_POST["rawat_mata_os_retina"],"inputField", null,false);?></td>
          </tr> 
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Tindakan Pemeriksaan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="30%" class="tablecontent">Tonometri OD</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_tonometri_scale_od","rawat_tonometri_scale_od","5","5",$_POST["rawat_tonometri_scale_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> / 
                    <?php echo $view->RenderTextBox("rawat_tonometri_weight_od","rawat_tonometri_weight_od","5","5",$_POST["rawat_tonometri_weight_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> g = 
                    <?php echo $view->RenderTextBox("rawat_tonometri_pressure_od","rawat_tonometri_pressure_od","5","5",$_POST["rawat_tonometri_pressure_od"],"inputField", "readonly",false);?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" width="30%" class="tablecontent">Tonometri OS</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_tonometri_scale_os","rawat_tonometri_scale_os","5","5",$_POST["rawat_tonometri_scale_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> / 
                    <?php echo $view->RenderTextBox("rawat_tonometri_weight_os","rawat_tonometri_weight_os","5","5",$_POST["rawat_tonometri_weight_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> g = 
                    <?php echo $view->RenderTextBox("rawat_tonometri_pressure_os","rawat_tonometri_pressure_os","5","5",$_POST["rawat_tonometri_pressure_os"],"inputField", "readonly",false);?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Anel Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_anel","rawat_anel","15","15",$_POST["rawat_anel"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Schimer Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_schimer","rawat_schimer","15","15",$_POST["rawat_schimer"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Irigasi Bola Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_irigasi","rawat_irigasi","15","15",$_POST["rawat_irigasi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Epilasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_epilasi","rawat_epilasi","15","15",$_POST["rawat_epilasi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Suntikan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_suntikan","rawat_suntikan","15","15",$_POST["rawat_suntikan"],"inputField", null,false);?></td>
          </tr>
     
          <tr>
               <td align="left" class="tablecontent">Probing</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_probing","rawat_probing","15","15",$_POST["rawat_probing"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Flouorecsin Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_flouorecsin","rawat_flouorecsin","15","15",$_POST["rawat_flouorecsin"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Uji Kesehatan Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_kesehatan","rawat_kesehatan","15","15",$_POST["rawat_kesehatan"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Color Blindness</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_color_blindness","rawat_color_blindness","5","50",$_POST["rawat_color_blindness"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>
-->
     <fieldset>
     <legend><strong>Upload Foto</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "50%" align="left" class="tablecontent">Gambar USG I</td>
               <td width= "50%" align="left" class="tablecontent">Gambar USG II</td>
          </tr>
          <tr>
               <td width= "50%" align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $fotoName;?>"  border="1">
                    <input type="hidden" name="rawat_mata_foto" id="rawat_mata_foto" value="<?php echo $_POST["rawat_mata_foto"];?>">
               </td>
               <td width= "50%" align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_sketsa" id="img_sketsa" src="<?php echo $sketsaName;?>"  border="1">
                    <input type="hidden" name="rawat_mata_sketsa" id="rawat_mata_sketsa" value="<?php echo $_POST["rawat_mata_sketsa"];?>">
               </td>
          </tr>
          <tr>
               <td colspan=2 align="center">
                    <div id="loading" style="display:none;"><img id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></div> 
                    <input id="fileToUpload" type="file" size="35" name="fileToUpload" class="inputField">
               </td>
          </tr>
          <tr>
               <td width= "50%" align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Gambar USG I</button>
               </td>
               <td width= "50%" align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUploadSketsa();">Upload Gambar USG II</button>
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Catatan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
          <tr>
               <td align="left" class="tablecontent" width="20%">Catatan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_catatan","rawat_catatan","5","50",$_POST["rawat_catatan"],"inputField", null,false);?></td>
          </tr>
     </table>
     </fieldset>






     <fieldset>
     <legend><strong>Diagnosis - ICD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[0]","rawat_icd_od_kode_0","10","100",$_POST["rawat_icd_od_kode"][0],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[0]" id="rawat_icd_od_id_0" value="<?php echo $_POST["rawat_icd_od_id"][0]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_icd_od_nama[0]","rawat_icd_od_nama_0","50","100",$_POST["rawat_icd_od_nama"][0],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[1]","rawat_icd_od_kode_1","10","100",$_POST["rawat_icd_od_kode"][1],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[1]" id="rawat_icd_od_id_1" value="<?php echo $_POST["rawat_icd_od_id"][1]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_icd_od_nama[1]","rawat_icd_od_nama_1","50","100",$_POST["rawat_icd_od_nama"][1],"inputField", "readonly",false);?></td>
          </tr>
	</table>
     </fieldset>

     <!--<fieldset>
     <legend><strong>Diagnosis - ICD - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php //echo $view->RenderTextBox("rawat_icd_os_kode[0]","rawat_icd_os_kode_0","10","100",$_POST["rawat_icd_os_kode"][0],"inputField", "readonly",false);?>
                    <a href="<?php //echo $icdPage;?>&tipe=os&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php //echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[0]" id="rawat_icd_os_id_0" value="<?php //echo $_POST["rawat_icd_os_id"][0]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_icd_os_nama[0]","rawat_icd_os_nama_0","50","100",$_POST["rawat_icd_os_nama"][0],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php //echo $view->RenderTextBox("rawat_icd_os_kode[1]","rawat_icd_os_kode_1","10","100",$_POST["rawat_icd_os_kode"][1],"inputField", "readonly",false);?>
                    <a href="<?php //echo $icdPage;?>&tipe=os&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php //echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[1]" id="rawat_icd_os_id_1" value="<?php //echo $_POST["rawat_icd_os_id"][1]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_icd_os_nama[1]","rawat_icd_os_nama_1","50","100",$_POST["rawat_icd_os_nama"][1],"inputField", "readonly",false);?></td>
          </tr>
	</table>
     </fieldset>-->

     <fieldset>
     <legend><strong>Diagnosis - INA DRG</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">INA DRG</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_ina_od_kode[0]","rawat_ina_od_kode_0","10","100",$_POST["rawat_ina_od_kode"][0],"inputField", "readonly",false);?>
                    <a href="<?php echo $inaPage;?>&tipe=od&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_od_id[0]" id="rawat_ina_od_id_0" value="<?php echo $_POST["rawat_ina_od_id"][0]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_ina_od_nama[0]","rawat_ina_od_nama_0","50","100",$_POST["rawat_ina_od_nama"][0],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_ina_od_kode[1]","rawat_ina_od_kode_1","10","100",$_POST["rawat_ina_od_kode"][1],"inputField", "readonly",false);?>
                    <a href="<?php echo $inaPage;?>&tipe=od&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_od_id[1]" id="rawat_ina_od_id_1" value="<?php echo $_POST["rawat_ina_od_id"][1]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_ina_od_nama[1]","rawat_ina_od_nama_1","50","100",$_POST["rawat_ina_od_nama"][1],"inputField", "readonly",false);?></td>
          </tr>
	</table>
     </fieldset>

     <!--<fieldset>
     <legend><strong>Diagnosis - INA DRG - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">INA DRG</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php //echo $view->RenderTextBox("rawat_ina_os_kode[0]","rawat_ina_os_kode_0","10","100",$_POST["rawat_ina_os_kode"][0],"inputField", "readonly",false);?>
                    <a href="<?php //echo $inaPage;?>&tipe=os&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php //echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_os_id[0]" id="rawat_ina_os_id_0" value="<?php //echo $_POST["rawat_ina_os_id"][0]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_ina_os_nama[0]","rawat_ina_os_nama_0","50","100",$_POST["rawat_ina_os_nama"][0],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php //echo $view->RenderTextBox("rawat_ina_os_kode[1]","rawat_ina_os_kode_1","10","100",$_POST["rawat_ina_os_kode"][1],"inputField", "readonly",false);?>
                    <a href="<?php //echo $inaPage;?>&tipe=os&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php //echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_os_id[1]" id="rawat_ina_os_id_1" value="<?php //echo $_POST["rawat_ina_os_id"][1]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_ina_os_nama[1]","rawat_ina_os_nama_1","50","100",$_POST["rawat_ina_os_nama"][1],"inputField", "readonly",false);?></td>
          </tr>
	</table>
     </fieldset>-->
	

     <fieldset>
     <legend><strong>Terapi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
          <tr class="subheader">
               <td width="30%" align="center">Nama Obat</td>
               <td width="30%" align="center">Dosis</td>
               <td width="10%" align="center">&nbsp;</td>
          </tr>	
          <?php if(!$_POST["item_nama"]) { ?>
               <tr  class="tablecontent-odd" id="tr_terapi_0">
                    <td align="left" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("item_nama[0]","item_nama_0","20","100",$_POST["item_nama"][0],"inputField", "readonly",false);?>
                         <a href="<?php echo $terapiPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" /></a>
                         <input type="hidden" name="id_item[0]" id="id_item_0" value="<?php echo $_POST["id_item"][0]?>" />                    
                    </td>
                    <td align="center" class="tablecontent-odd"><span id="sp_item_0"></span></td>
                    <td align="center" class="tablecontent-odd">
                         <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="Tambah();">
                         <input name="hid_tot" id="hid_tot" type="hidden" value="0">
                    </td>                    
               </tr>
          <?php } else { ?>
               <?php for($i=0,$n=count($_POST["id_item"]);$i<$n;$i++) { ?>
                    <tr id="tr_terapi_<?php echo $i;?>">
                         <td align="left" class="tablecontent-odd" width="70%">
                              <?php echo $view->RenderTextBox("item_nama[]","item_nama_".$i,"30","100",$_POST["item_nama"][$i],"inputField", "readonly",false);?>
                              <a href="<?php echo $terapiPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Obat" alt="Cari Obat" /></a>
                              <input type="hidden" id="id_item_<?php echo $i;?>" name="id_item[]" value="<?php echo $_POST["id_item"][$i];?>"/>
                         </td>
                         <td align="center" class="tablecontent-odd"><span id="sp_item_<?php echo $i;?>"><?php echo GetDosis($_POST["item_fisik"][$i],$i,$_POST["id_dosis"][$i]);?></span></td>
                         <td align="left" class="tablecontent-odd" width="30%">
                              <?php if($i==0) { ?>
                              <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="Tambah();">
                              <?php } else { ?>
                              <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="Delete(<?php echo $i;?>);">
                              <?php } ?>
                              <input name="hid_tot" id="hid_tot" type="hidden" value="<?php echo $n;?>">
                         </td>
                    </tr>
               <?php } ?>
          <?php } ?>
     </table>
     </fieldset>
	
	

     <!--<fieldset>
     <legend><strong>Terapi Kacamata</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
          <tr class="subheader">
               <td width="5%" align="center">Mata</td>
               <td width="15%" align="center">Spheris</td>
               <td width="15%" align="center">Cylinder</td>
               <td width="15%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_od_koreksi_spheris","rawat_mata_od_koreksi_spheris","15","15",$_POST["rawat_mata_od_koreksi_spheris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_od_koreksi_cylinder","rawat_mata_od_koreksi_cylinder","15","15",$_POST["rawat_mata_od_koreksi_cylinder"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_od_koreksi_sudut","rawat_mata_od_koreksi_sudut","15","15",$_POST["rawat_mata_od_koreksi_sudut"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_os_koreksi_spheris","rawat_mata_os_koreksi_spheris","15","15",$_POST["rawat_mata_os_koreksi_spheris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_os_koreksi_cylinder","rawat_mata_os_koreksi_cylinder","15","15",$_POST["rawat_mata_os_koreksi_cylinder"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php //echo $view->RenderTextBox("rawat_mata_os_koreksi_sudut","rawat_mata_os_koreksi_sudut","15","15",$_POST["rawat_mata_os_koreksi_sudut"],"inputField", null,false);?></td>
          </tr>
     </table>
     </fieldset>-->


	<?php if($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_JAMKESNAS_PUSAT) { ?>
		<fieldset>
		<legend><strong>PAKET KLAIM INA DRG</strong></legend>
		<table width="100%" border="1" cellpadding="4" cellspacing="1">
			<tr> 
				<td align="center" class="subheader" width="25%">INA DRG</td>
				<td align="center" class="subheader">Keterangan</td>
			</tr>
			<tr> 
				<td align="left" class="tablecontent-odd">
					<?php echo $view->RenderTextBox("rawat_ina_klaim_kode[0]","rawat_ina_klaim_kode_0","10","100",$_POST["rawat_ina_klaim_kode"][0],"inputField", "readonly",false);?>
					<a href="<?php echo $inaPage;?>&tipe=klaim&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
					<input type="hidden" name="rawat_ina_klaim_id[0]" id="rawat_ina_klaim_id_0" value="<?php echo $_POST["rawat_ina_klaim_id"][0]?>" />                    
				</td>
				<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_ina_klaim_nama[0]","rawat_ina_klaim_nama_0","50","100",$_POST["rawat_ina_klaim_nama"][0],"inputField", "readonly",false);?></td>
			</tr> 
		</table>
		</fieldset>
	<?php } ?>

     <fieldset>
     <legend><strong>Rencana Tindakan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_operasi_jenis","rawat_operasi_jenis",$optOperasiJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Paket Biaya</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_operasi_paket","rawat_operasi_paket",$optOperasiPaket,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Rencana Anestesis</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_jenis","rawat_anestesis_jenis",$optAnestesisJenis,null,null,null);?></td>
          </tr>
<!--
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Obat Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_obat","rawat_anestesis_obat",$optAnestesisObat,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Dosis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("rawat_anestesis_dosis","rawat_anestesis_dosis","50","200",$_POST["rawat_anestesis_dosis"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Komplikasi Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_komp","rawat_anestesis_komp",$optAnestesisKomplikasi,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Premedikasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_pre","rawat_anestesis_pre",$optAnestesisPremedikasi,null,null,null);?></td>
          </tr>
     
-->
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Cetak Surat</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center">
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnUpdate","btnUpdate","Surat Keterangan Sakit","button",false,'onClick="BukaWindowCetak(\'surat_sakit.php?id_cust_usr='.$_POST["id_cust_usr"].'&id_reg='.$_POST["id_reg"].'\',\'Cetak Invoice\')"',null);?>
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
               <?php if(!$_GET["id"]) { ?>
                    <td align="left" width="30%" class="tablecontent">Tahap Berikutnya</td>
                    <td align="left" width="20%"><?php echo $view->RenderComboBox("cmbNext","cmbNext",$optionsNext,null,null,null);?></td>
               <?php } ?>
			<td align="left"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit" || $_x_mode == "Diag") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>

     </td>
     
     <td width="40%" height="100%" valign="top"><iframe style="width:100%;height:100%;" marginwidth="0" marginheight="0" id="ifrmDiag" name="ifrmDiag" src="<?php echo $diagLink;?>" scrolling="auto" align="center" frameborder="0"></iframe></td>
</tr>	

</table>

<?php echo $view->SetFocus("rawat_keluhan");?>

<input type="hidden" name="_x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"];?>"/>
<?php echo $view->RenderHidden("hid_id_del","hid_id_del",'');?>

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
