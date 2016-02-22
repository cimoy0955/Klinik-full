<?php
/*
 * SERVER ROLL-BACK 100514
 * 
 */
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
     

 	if(!$auth->IsAllowed("tindakan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("tindakan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "operasi_jadwal.php"; 
     $icdPage = "op_icd_find.php?";
     $inaPage = "op_ina_find.php?";
     $dokterPage = "op_dokter_find.php?";
     $susterPage = "op_suster_find.php?";
     $backPage = "preop.php?";
	$page[STATUS_OPERASI_JADWAL] = "operasi_jadwal.php";
	$page[STATUS_BEDAH] = "bedah.php";
	$page[STATUS_PREOP] = "preop.php";


     $tableRefraksi = new InoTable("table1","99%","center");

     $plx = new InoLiveX("GetTindakan,SetTindakan,GetTonometri,GetDosis");     

     function GetTonometri($scale,$weight) {
          global $dtaccess; 
               
          $sql = "select tono_pressure from klinik.klinik_tonometri where tono_scale = ".QuoteValue(DPE_NUMERIC,$scale)." and tono_weight = ".QuoteValue(DPE_NUMERIC,$weight);
          $dataTable = $dtaccess->Fetch($sql);
          return $dataTable["tono_pressure"];

     }     
     
     function GetDosis($item,$id=null) {
          global $dtaccess, $view;
          
		$sql = "select item_fisik from inventori.inv_item where item_id = ".QuoteValue(DPE_NUMERIC,$item);
		$dataItem = $dtaccess->Fetch($sql);
		
          $sql = "select dosis_id, dosis_nama from inventori.inv_dosis where id_fisik = ".QuoteValue(DPE_NUMERIC,$dataItem["item_fisik"]);
          $dataTable = $dtaccess->FetchAll($sql);

          $optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show); 
          for($i=0,$n=count($dataTable);$i<$n;$i++) {
               $show = ($id==$dataTable[$i]["dosis_id"]) ? "selected":"";
               $optDosis[$i+1] = $view->RenderOption($dataTable[$i]["dosis_id"],$dataTable[$i]["dosis_nama"],$show); 
          }
          
          return $view->RenderComboBox("preop_anestesis_dosis","preop_anestesis_dosis",$optDosis,null,null,null);
     }

     function GetTindakan($status) {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT,$rawatStatus,$page; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.reg_status like '".STATUS_PREOP.$status."' or a.reg_status like '".STATUS_OPERASI_JADWAL.$status."' and a.reg_tipe_umur='D' 
                    order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
			if($status==0) {
				$tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesPerawatan(\''.$dataTable[$i]["reg_id"].'\',\''.$dataTable[$i]["reg_status"]{0}.'\')"/>';
			} else {
				$tbContent[$i][$counter][TABLE_ISI] = "<a href=\"".$page[$dataTable[$i]["reg_status"]{0}]."?id_reg=".$dataTable[$i]["reg_id"]."&status=".$dataTable[$i]["reg_status"]{0}."\"><img hspace=\"2\" width=\"16\" height=\"16\" src=\"".$APLICATION_ROOT."images/b_select.png\" alt=\"Proses\" title=\"Proses\" border=\"0\"/></a>";               
			}
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $rawatStatus[$dataTable[$i]["reg_status"]{0}];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}

     function SetTindakan($id,$status) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".$status.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
     
     

     if(!$_POST["cbRegulasi"]) $_POST["cbRegulasi"] = "y";
	
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien,  
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                    from klinik.klinik_registrasi a
				join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"]; 
		$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
		$_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
          
          $diagLink = "perawatan_diag.php?id_cust_usr=".$enc->Encode($dataPasien["id_cust_usr"])."&id_reg=".$enc->Encode($_GET["id_reg"]);


		$sql = "select b.visus_nama as ref_mata_od_nonkoreksi_visus, d.visus_nama as ref_mata_od_koreksi_visus,
				ref_mata_od_koreksi_spheris,ref_mata_od_koreksi_cylinder,ref_mata_od_koreksi_sudut, 
				c.visus_nama as ref_mata_os_nonkoreksi_visus, e.visus_nama as ref_mata_os_koreksi_visus,
				ref_mata_os_koreksi_spheris,ref_mata_os_koreksi_cylinder,ref_mata_os_koreksi_sudut
				from klinik.klinik_refraksi a
				left join klinik.klinik_visus b on a.id_visus_nonkoreksi_od = b.visus_id
				left join klinik.klinik_visus c on a.id_visus_nonkoreksi_os = c.visus_id
				left join klinik.klinik_visus d on a.id_visus_koreksi_od = d.visus_id
				left join klinik.klinik_visus e on a.id_visus_koreksi_os = e.visus_id
				where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataRefraksi = $dtaccess->Fetch($sql); 
     
		$sql = "select b.icd_nomor, b.icd_nama
				from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_nomor
				where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
				and a.rawat_icd_odos = 'OD' 
				order by a.rawat_icd_urut";
		$dataDiagIcdOD = $dtaccess->FetchAll($sql);
	
		$sql = "select b.icd_nomor, b.icd_nama
				from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_nomor
				where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
				and a.rawat_icd_odos = 'OS' 
				order by a.rawat_icd_urut";
		$dataDiagIcdOS = $dtaccess->FetchAll($sql);
		//
		//$sql = "select b.ina_kode, b.ina_nama
		//		from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
		//		where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
		//		and a.rawat_ina_odos = 'OD' 
		//		order by a.rawat_ina_urut";
		//$dataDiagInaOD = $dtaccess->FetchAll($sql);
		//
		//$sql = "select b.ina_kode, b.ina_nama
		//		from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
		//		where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
		//		and a.rawat_ina_odos = 'OS' 
		//		order by a.rawat_ina_urut";
		//$dataDiagInaOS = $dtaccess->FetchAll($sql);

		$sql = "select a.*, b.bio_av_nama 
				from klinik.klinik_diagnostik a
				left join klinik.klinik_biometri_av b on a.diag_av_constant = b.bio_av_id 
                    where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataDiagnostik= $dtaccess->Fetch($sql);
		
		$sql = "select * from klinik.klinik_preop where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dataPreOp = $dtaccess->Fetch($sql);
		
		$view->CreatePost($dataPreOp);
	
		$_POST["preop_keluhan"] = ($dataPreOp["preop_keluhan"]) ? $dataPreOp["preop_keluhan"]:$dataPemeriksaan["rawat_keluhan"];
		$_POST["preop_keadaan_umum"] = ($dataPreOp["preop_keadaan_umum"]) ? $dataPreOp["preop_keadaan_umum"]:$dataPemeriksaan["rawat_keadaan_umum"];
		$_POST["preop_lab_tensi"] = ($dataPreOp["preop_lab_tensi"]) ? $dataPreOp["preop_lab_tensi"]:$dataPemeriksaan["rawat_lab_tensi"];
		$_POST["preop_lab_nadi"] = ($dataPreOp["preop_lab_nadi"]) ? $dataPreOp["preop_lab_nadi"]:$dataPemeriksaan["rawat_lab_nadi"];
		$_POST["preop_lab_nafas"] = ($dataPreOp["preop_lab_nafas"]) ? $dataPreOp["preop_lab_nafas"]:$dataPemeriksaan["rawat_lab_nafas"];
		$_POST["preop_mata_lokal"] = ($dataPreOp["preop_mata_lokal"]) ? $dataPreOp["preop_mata_lokal"]:$dataPemeriksaan["rawat_mata_lokal"];
		$_POST["preop_lab_gula_darah"] = ($dataPreOp["preop_lab_gula_darah"]) ? $dataPreOp["preop_lab_gula_darah"]:$dataPemeriksaan["rawat_lab_gula_darah"];
		$_POST["preop_lab_darah_lengkap"] = ($dataPreOp["preop_lab_darah_lengkap"]) ? $dataPreOp["preop_lab_darah_lengkap"]:$dataPemeriksaan["rawat_lab_darah_lengkap"];
		$_POST["preop_tonometri_scale_od"] = ($dataPreOp["preop_tonometri_scale_od"]) ? $dataPreOp["preop_tonometri_scale_od"]:$dataPemeriksaan["rawat_tonometri_scale_od"];
		$_POST["preop_tonometri_weight_od"] = ($dataPreOp["preop_tonometri_weight_od"]) ? $dataPreOp["preop_tonometri_weight_od"]:$dataPemeriksaan["rawat_tonometri_weight_od"];
		$_POST["preop_tonometri_pressure_od"] = ($dataPreOp["preop_tonometri_pressure_od"]) ? $dataPreOp["preop_tonometri_pressure_od"]:$dataPemeriksaan["rawat_tonometri_pressure_od"];
		$_POST["preop_tonometri_scale_os"] = ($dataPreOp["preop_tonometri_scale_os"]) ? $dataPreOp["preop_tonometri_scale_os"]:$dataPemeriksaan["rawat_tonometri_scale_os"];
		$_POST["preop_tonometri_weight_os"] = ($dataPreOp["preop_tonometri_weight_os"]) ? $dataPreOp["preop_tonometri_weight_os"]:$dataPemeriksaan["rawat_tonometri_weight_os"];
		$_POST["preop_tonometri_pressure_os"] = ($dataPreOp["preop_tonometri_pressure_os"]) ? $dataPreOp["preop_tonometri_pressure_os"]:$dataPemeriksaan["rawat_tonometri_pressure_os"];
		$_POST["preop_k1_od"] = ($dataPreOp["preop_k1_od"]) ? $dataPreOp["preop_k1_od"]:$dataPemeriksaan["rawat_k1_od"];
		$_POST["preop_k1_os"] = ($dataPreOp["preop_k1_os"]) ? $dataPreOp["preop_k1_os"]:$dataPemeriksaan["rawat_k1_os"];
		$_POST["preop_k2_od"] = ($dataPreOp["preop_k2_od"]) ? $dataPreOp["preop_k2_od"]:$dataPemeriksaan["rawat_k2_od"];
		$_POST["preop_k2_os"] = ($dataPreOp["preop_k2_os"]) ? $dataPreOp["preop_k2_os"]:$dataPemeriksaan["rawat_k2_os"];
		$_POST["preop_acial_od"] = ($dataPreOp["preop_acial_od"]) ? $dataPreOp["preop_acial_od"]:$dataPemeriksaan["rawat_acial_od"];
		$_POST["preop_acial_os"] = ($dataPreOp["preop_acial_os"]) ? $dataPreOp["preop_acial_os"]:$dataPemeriksaan["rawat_acial_os"];
		$_POST["preop_iol_od"] = ($dataPreOp["preop_iol_od"]) ? $dataPreOp["preop_iol_od"]:$dataPemeriksaan["rawat_iol_od"];
		$_POST["preop_iol_os"] = ($dataPreOp["preop_iol_os"]) ? $dataPreOp["preop_iol_os"]:$dataPemeriksaan["rawat_iol_os"];
		$_POST["preop_av_constant"] = ($dataPreOp["preop_av_constant"]) ? $dataPreOp["preop_av_constant"]:$dataPemeriksaan["rawat_av_constant"];
		$_POST["preop_deviasi"] = ($dataPreOp["preop_deviasi"]) ? $dataPreOp["preop_deviasi"]:$dataPemeriksaan["rawat_deviasi"];
		$_POST["preop_operasi_paket"] = ($dataPreOp["preop_operasi_paket"]) ? $dataPreOp["preop_operasi_paket"]:$dataPemeriksaan["rawat_operasi_paket"];
		$_POST["preop_operasi_jenis"] = ($dataPreOp["preop_operasi_jenis"]) ? $dataPreOp["preop_operasi_jenis"]:$dataPemeriksaan["rawat_operasi_jenis"];
		 
	}

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
		if(!$_POST["preop_regulasi_berhasil"]) $_POST["preop_regulasi_berhasil"] = "n";
		if(!$_POST["preop_regulasi"]) $_POST["preop_regulasi"] = "n";
		
		if($_POST["btnSave"]) {
               $sql = "delete from klinik.klinik_preop where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
		}
		
          $dbTable = "klinik.klinik_preop";
          $dbField[0] = "preop_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "preop_keluhan";
          $dbField[3] = "preop_keadaan_umum";
          $dbField[4] = "preop_tonometri_scale_od";
          $dbField[5] = "preop_lab_gula_darah";
          $dbField[6] = "preop_lab_darah_lengkap";
          $dbField[7] = "preop_lab_tensi";
          $dbField[8] = "preop_lab_nadi";
          $dbField[9] = "preop_lab_nafas";
          $dbField[10] = "preop_mata_lokal";
          $dbField[11] = "id_cust_usr";
          $dbField[12] = "preop_tonometri_weight_od";
          $dbField[13] = "preop_tonometri_pressure_od";
          $dbField[14] = "preop_waktu";
          $dbField[15] = "preop_tonometri_od";
          $dbField[16] = "preop_tonometri_os";
          $dbField[17] = "preop_tonometri_weight_os";
          $dbField[18] = "preop_tonometri_pressure_os";
          $dbField[19] = "preop_tonometri_scale_os";
          $dbField[20] = "preop_regulasi";
          $dbField[21] = "preop_regulasi_gula_obat";
          $dbField[22] = "preop_regulasi_gula_hasil";
          $dbField[23] = "preop_regulasi_tono_obat";
          $dbField[24] = "preop_regulasi_tono_hasil";
          $dbField[25] = "preop_regulasi_berhasil";
          $dbField[26] = "preop_catatan_ok";
          $dbField[27] = "preop_terapi_obat";
          $dbField[28] = "preop_terapi_saran";
          $dbField[29] = "preop_anestesis_jenis";
          $dbField[30] = "preop_anestesis_obat";
          $dbField[31] = "preop_anestesis_dosis";
          $dbField[32] = "preop_anestesis_komp";
          $dbField[33] = "preop_anestesis_pre";
          $dbField[34] = "preop_iol_jenis";
          $dbField[35] = "preop_iol_merk";
          $dbField[36] = "preop_k1_od";
          $dbField[37] = "preop_k1_os";
          $dbField[38] = "preop_k2_od";
          $dbField[39] = "preop_k2_os";
          $dbField[40] = "preop_acial_od";
          $dbField[41] = "preop_acial_os";
          $dbField[42] = "preop_iol_od";
          $dbField[43] = "preop_iol_os";
          $dbField[44] = "preop_av_constant";
          $dbField[45] = "preop_deviasi";
          $dbField[46] = "preop_tanggal_jadwal";
          $dbField[47] = "preop_operasi_paket";
          $dbField[48] = "preop_operasi_jenis";

          
          if(!$_POST["preop_id"]) $_POST["preop_id"] = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["preop_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["preop_keluhan"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["preop_keadaan_umum"]);
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_scale_od"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["preop_lab_gula_darah"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["preop_lab_darah_lengkap"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["preop_lab_tensi"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["preop_lab_nadi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["preop_lab_nafas"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["preop_mata_lokal"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_weight_od"]);
          $dbValue[13] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_pressure_od"]);
          $dbValue[14] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["preop_tonometri_od"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["preop_tonometri_os"]);
          $dbValue[17] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_weight_os"]);
          $dbValue[18] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_pressure_os"]);
          $dbValue[19] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_scale_os"]);
          $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_gula_obat"]);
          $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_gula_hasil"]);
          $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_tono_obat"]);
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_tono_hasil"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_berhasil"]);
          $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["preop_catatan_ok"]);
          $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["preop_terapi_obat"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["preop_terapi_saran"]);
          $dbValue[29] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_jenis"]);
          $dbValue[30] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_obat"]);
          $dbValue[31] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_dosis"]);
          $dbValue[32] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_komp"]);
          $dbValue[33] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_pre"]);
          $dbValue[34] = QuoteValue(DPE_CHARKEY,$_POST["preop_iol_jenis"]);
          $dbValue[35] = QuoteValue(DPE_CHARKEY,$_POST["preop_iol_merk"]);
          $dbValue[36] = QuoteValue(DPE_CHAR,$_POST["preop_k1_od"]);
          $dbValue[37] = QuoteValue(DPE_CHAR,$_POST["preop_k1_os"]);
          $dbValue[38] = QuoteValue(DPE_CHAR,$_POST["preop_k2_od"]);
          $dbValue[39] = QuoteValue(DPE_CHAR,$_POST["preop_k2_os"]);
          $dbValue[40] = QuoteValue(DPE_CHAR,$_POST["preop_acial_od"]);
          $dbValue[41] = QuoteValue(DPE_CHAR,$_POST["preop_acial_os"]);
          $dbValue[42] = QuoteValue(DPE_CHAR,$_POST["preop_iol_od"]);
          $dbValue[43] = QuoteValue(DPE_CHAR,$_POST["preop_iol_os"]);
          $dbValue[44] = QuoteValue(DPE_CHARKEY,$_POST["preop_av_constant"]);
          $dbValue[45] = QuoteValue(DPE_CHAR,$_POST["preop_deviasi"]);
          
          $tglJadwal = ($_POST["jadwal_tanggal"])?date_db($_POST["jadwal_tanggal"])." ".$_POST["jadwal_jam"].":".$_POST["jadwal_menit"].":00":""; 
          
          $dbValue[46] = QuoteValue(DPE_DATE,$tglJadwal);
          $dbValue[47] = QuoteValue(DPE_CHARKEY,$_POST["preop_operasi_paket"]);
          $dbValue[48] = QuoteValue(DPE_CHARKEY,$_POST["preop_operasi_jenis"]);

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


		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_SELESAI."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql); 



               $sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
                         and id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])." and fol_jenis = '".STATUS_OPERASI_JADWAL."'";
               $dtaccess->Execute($sql);

          
               if($_POST["preop_lab_gula_darah"] || $_POST["preop_lab_darah_lengkap"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_GULA_PREOP);               
               if($_POST["preop_regulasi"]=="y" && ($_POST["preop_regulasi_gula_obat"] || $_POST["preop_regulasi_gula_hasil"])) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_GULAREGULASI_PREOP);               
               	

               if($sql_where) {
					$sql = "select * from klinik.klinik_biaya where ".implode(" or ",$sql_where);
					$dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
					$folWaktu = date("Y-m-d H:i:s");
			}
               $lunas = ($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_SWADAYA)?'n':'y';

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
                    $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_OPERASI_JADWAL);
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
		
		
		// --- nyimpen paket klaim e ---
		if($tglJadwal && $_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA) {
		
               $sql = "select b.paket_klaim_id, paket_klaim_total 
                         from klinik.klinik_biaya_pasien a
                         join klinik.klinik_paket_klaim b on a.id_paket_klaim = b.paket_klaim_id  
                         where a.biaya_pasien_status = ".QuoteValue(DPE_CHAR,STATUS_OPERASI_JADWAL)."
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
                         $dbValue[6] = QuoteValue(DPE_CHAR,STATUS_OPERASI_JADWAL);
                    
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

     // ---- insert ke registrasi ----
          /*$dbTable = "klinik_registrasi";
     
          $dbField[0] = "reg_id";   // PK
          $dbField[1] = "reg_tanggal";
          $dbField[2] = "reg_waktu";
          $dbField[3] = "id_cust_usr";
          $dbField[4] = "reg_status";
          $dbField[5] = "reg_who_update";
          $dbField[6] = "reg_when_update";
          
          $regId = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
          $dbValue[1] = QuoteValue(DPE_DATE,date_db($_POST["jadwal_tanggal"]));
          $dbValue[2] = QuoteValue(DPE_DATE,($_POST["jadwal_jam"].":".$_POST["jadwal_menit"].":00"));
          $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,(STATUS_REFRAKSI.STATUS_ANTRI));
          $dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
          $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          
          //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
          
          if($dataReg["reg_status"]==STATUS_SELESAI || !$dataReg) { 
               $dtmodel->Insert() or die("insert error"); 
          }
          
          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);*/
     
          if($_POST["btnSave"]) echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          exit();   

	}
	
	foreach($rawatKeadaan as $key => $value) {
		$optionsKeadaan[] = $view->RenderOption($key,$value,$show);
	}


     $sql = "select diag_id from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     $dataDiag = $dtaccess->Fetch($sql);
     
     $count=0;
     $optionsNext[$count] = $view->RenderOption(STATUS_SELESAI,"Pulang Dengan Resep",$show); $count++;
     $optionsNext[$count] = $view->RenderOption(STATUS_APOTEK,"Apotek",$show); $count++;
     $count=0;
     $optionsNext1[$count] = $view->RenderOption(STATUS_OPERASI,"Operasi Hari Ini",$show); $count++;
     $optionsNext1[$count] = $view->RenderOption(STATUS_SELESAI,"Pulang Dengan Resep",$show); $count++;
     $optionsNext1[$count] = $view->RenderOption(STATUS_APOTEK,"Apotek",$show); $count++;
	#if(!$dataDiag) $optionsNext[$count] = $view->RenderOption(STATUS_DIAGNOSTIK,"Ke Ruang Diagnostik",$show); $count++;
	#$optionsNext[$count] = $view->RenderOption(STATUS_OPERASI_JADWAL,"Penjadwalan Operasi",$show); $count++;
	
	#$optionsNext[$count] = $view->RenderOption(STATUS_BEDAH,"Bedah Minor",$show); $count++;

     $lokasi = $APLICATION_ROOT."images/foto_perawatan";
	$fotoName = ($_POST["rawat_mata_foto"]) ? $lokasi."/".$_POST["rawat_mata_foto"] : $lokasi."/default.jpg";
	$sketsaName = ($_POST["rawat_mata_sketsa"]) ? $lokasi."/".$_POST["rawat_mata_sketsa"] : $lokasi."/default.jpg";


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


     // --- nyari datanya IOL ---
     $sql = "select iol_jenis_id, iol_jenis_nama from klinik.klinik_iol_jenis";
     $dataIOLJenis = $dtaccess->FetchAll($sql);

     $sql = "select iol_merk_id, iol_merk_nama from klinik.klinik_iol_merk";
     $dataIOLMerk = $dtaccess->FetchAll($sql);

     $optIOLJenis[0] = $view->RenderOption("","[Pilih Jenis IOL]",$show); 
     for($i=0,$n=count($dataIOLJenis);$i<$n;$i++) {
          $show = ($_POST["rawat_iol_jenis"]==$dataIOLJenis[$i]["iol_jenis_id"]) ? "selected":"";
          $optIOLJenis[$i+1] = $view->RenderOption($dataIOLJenis[$i]["iol_jenis_id"],$dataIOLJenis[$i]["iol_jenis_nama"],$show); 
     }

     $optIOLMerk[0] = $view->RenderOption("","[Pilih Merk IOL]",$show); 
     for($i=0,$n=count($dataIOLMerk);$i<$n;$i++) {
          $show = ($_POST["rawat_iol_merk"]==$dataIOLMerk[$i]["iol_merk_id"]) ? "selected":"";
          $optIOLMerk[$i+1] = $view->RenderOption($dataIOLMerk[$i]["iol_merk_id"],$dataIOLMerk[$i]["iol_merk_nama"],$show); 
     }

     // --- nyari datanya rumuys ---
     $sql = "select bio_av_id, bio_av_nama from klinik.klinik_biometri_av order by bio_av_nama";
     $dataAv = $dtaccess->FetchAll($sql);

     // -- bikin combonya av
     $optAv[0] = $view->RenderOption("","[Pilih AV Constant Yg Dipakai]",$show); 
     for($i=0,$n=count($dataAv);$i<$n;$i++) {
          $show = ($_POST["diag_av"]==$dataAv[$i]["bio_av_id"]) ? "selected":"";
          $optAv[$i+1] = $view->RenderOption($dataAv[$i]["bio_av_id"],$dataAv[$i]["bio_av_nama"],$show); 
     }

     // --- nyari datanya rumuys ---
     $sql = "select bio_av_id, bio_av_nama from klinik.klinik_biometri_av order by bio_av_nama";
     $dataAv = $dtaccess->FetchAll($sql);

     // -- bikin combonya av
     $optAv[0] = $view->RenderOption("","[Pilih AV Constant Yg Dipakai]",$show); 
     for($i=0,$n=count($dataAv);$i<$n;$i++) {
          $show = ($_POST["preop_av_constant"]==$dataAv[$i]["bio_av_id"]) ? "selected":"";
          $optAv[$i+1] = $view->RenderOption($dataAv[$i]["bio_av_id"],$dataAv[$i]["bio_av_nama"],$show); 
     }

     $sql = "select b.prosedur_kode, b.prosedur_nama
	       from klinik.klinik_perawatan_prosedur a join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_id
	       where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
	       order by a.rawat_prosedur_urut";
     $dataProsedur = $dtaccess->FetchAll($sql);

     // --- nyari datanya operasi jenis + harganya---
     $sql = "select op_jenis_id, op_jenis_nama from klinik.klinik_operasi_jenis";
     $dataOperasiJenis = $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi Jenis
     $optOperasiJenis[0] = $view->RenderOption("","[Pilih Jenis Operasi]",$show); 
     for($i=0,$n=count($dataOperasiJenis);$i<$n;$i++) {
          $show = ($_POST["preop_operasi_jenis"]==$dataOperasiJenis[$i]["op_jenis_id"]) ? "selected":"";
          $optOperasiJenis[$i+1] = $view->RenderOption($dataOperasiJenis[$i]["op_jenis_id"],$dataOperasiJenis[$i]["op_jenis_nama"],$show); 
     }
     
     
     $sql = "select op_paket_id, op_paket_nama from klinik.klinik_operasi_paket";
     $dataOperasiPaket= $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi paket
     $optOperasiPaket[0] = $view->RenderOption("","[Pilih Paket Operasi]",$show); 
     for($i=0,$n=count($dataOperasiPaket);$i<$n;$i++) {
          $show = ($_POST["preop_operasi_paket"]==$dataOperasiPaket[$i]["op_paket_id"]) ? "selected":"";
          $optOperasiPaket[$i+1] = $view->RenderOption($dataOperasiPaket[$i]["op_paket_id"],$dataOperasiPaket[$i]["op_paket_nama"],$show); 
     }
	 
?>


<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitThickBox(); ?>

<script type="text/javascript">

<? $plx->Run(); ?>


function GantiRegulasi() {
     if(document.getElementById('preop_regulasi').checked) {
          document.getElementById('tbRegulasi').style.display="block";
          document.getElementById('tbCatatan').style.display="none";
          document.getElementById('tbTerapi').style.display="none";
     } else {
          document.getElementById('tbRegulasi').style.display="none";
          document.getElementById('tbCatatan').style.display="block";
          document.getElementById('tbTerapi').style.display="none";
     }
}

function GantiBerhasil() {
     if(document.getElementById('preop_regulasi_berhasil').checked) {
          document.getElementById('tbCatatan').style.display="block";
          document.getElementById('tbTerapi').style.display="none";
	  document.getElementById('next1').style.display="none";
	  document.getElementById('next2').style.display="block";
     } else {
          document.getElementById('tbCatatan').style.display="none";
          document.getElementById('tbTerapi').style.display="block";
	  document.getElementById('next1').style.display="block";
	  document.getElementById('next2').style.display="none";
     }
}

function CheckData(frm) {
     return true;
}


function SetTonometriOD(){
     var scale = document.getElementById('preop_tonometri_scale_od');
     var weight = document.getElementById('preop_tonometri_weight_od');

     if(scale.value && isNaN(scale.value)){
          scale.value = '';
          return false;
     }

     if(weight.value && isNaN(weight.value)){
          weight.value = '';
          return false;
     }

     GetTonometri(scale.value,weight.value,'target=preop_tonometri_pressure_od');
     return true;
}

function SetTonometriOS(){
     var scale = document.getElementById('preop_tonometri_scale_os');
     var weight = document.getElementById('preop_tonometri_weight_os');

     if(scale.value && isNaN(scale.value)){
          scale.value = '';
          return false;
     }

     if(weight.value && isNaN(weight.value)){
          weight.value = '';
          return false;
     }

     GetTonometri(scale.value,weight.value,'target=preop_tonometri_pressure_os');
     return true;
}

function SetDosis(id) {
     GetDosis(id,'target=sp_dosis');
}
</script>


<script type="text/javascript">


var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetTindakan(0,'target=antri_kiri_isi');     
     GetTindakan(1,'target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}

function ProsesPerawatan(id,status) {
	SetTindakan(id,status,'type=r');
	timer();
}

//
//function CheckData(frm) {
//     if(document.getElementById('rawat_lab_alergi').value) { alert('Alergi Terisi'); }   
//     return true;
//}

timer();
</script>


<?php if(!$_GET["id"]) { ?>

<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Pre Operasi</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php /*echo GetTindakan(STATUS_ANTRI);*/ ?></div>
	</div>
	
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Pre Operasi</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php /*echo GetTindakan(STATUS_PROSES);*/ ?></div>
	</div>
</div>

<?php } ?>



<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data <?php echo $rawatStatus[$_GET["status"]]; ?></td>
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
               <td width= "20%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "20%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Umur</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Pasien</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $bayarPasien[$_POST["reg_jenis_pasien"]]; ?></label></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Data Refraksi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="5%" rowspan=2 align="center">Mata</td>
               <td width="30%" rowspan=2 align="center">Visus Tanpa Koreksi</td>
               <td width="35%" colspan=3 align="center">Koreksi</td>
               <td width="30%" rowspan=2 align="center">Visus Dengan Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="15%" align="center">Spheris</td>
               <td width="15%" align="center">Cylinder</td>
               <td width="15%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_od_nonkoreksi_visus"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_sudut"];?></td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_od_koreksi_visus"];?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_os_nonkoreksi_visus"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_sudut"];?></td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_os_koreksi_visus"];?></td>
          </tr>
	</table>
     </fieldset>
     
     <fieldset>
     <legend><strong>Diagnose - ICD - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOD[0]["icd_nomor"]." ".$dataDiagIcdOD[0]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOD[1]["icd_nomor"]." ".$dataDiagIcdOD[1]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOD[2]["icd_nomor"]." ".$dataDiagIcdOD[2]["icd_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - ICD - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOS[0]["icd_nomor"]." ".$dataDiagIcdOS[0]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOS[1]["icd_nomor"]." ".$dataDiagIcdOS[1]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOS[2]["icd_nomor"]." ".$dataDiagIcdOS[2]["icd_nama"];?></td>
          </tr>
	</table>
     </fieldset>
     
     
     <fieldset>
     <legend><strong>Prosedur</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataProsedur[0]["prosedur_kode"]." ".$dataProsedur[0]["prosedur_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataProsedur[1]["prosedur_kode"]." ".$dataProsedur[1]["prosedur_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataProsedur[2]["prosedur_kode"]." ".$dataProsedur[2]["prosedur_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">4</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataProsedur[3]["prosedur_kode"]." ".$dataProsedur[3]["prosedur_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Keratometri</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td align="center" width="20%">&nbsp;</td>
               <td align="center" width="40%">OD</td>
               <td align="center" width="40%">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">K1</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_k1_od","preop_k1_od","30","200",$_POST["preop_k1_od"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_k1_os","preop_k1_os","30","200",$_POST["preop_k1_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">K2</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_k2_od","preop_k2_od","30","200",$_POST["preop_k2_od"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_k2_os","preop_k2_os","30","200",$_POST["preop_k2_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Biometri</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td align="center" width="20%">&nbsp;</td>
               <td align="center" width="40%">OD</td>
               <td align="center" width="40%">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Acial Length</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_acial_od","preop_acial_od","30","200",$_POST["preop_acial_od"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_acial_os","preop_acial_os","30","200",$_POST["preop_acial_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power IOL</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_iol_od","preop_iol_od","30","200",$_POST["preop_iol_od"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_iol_os","preop_iol_os","30","200",$_POST["preop_iol_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">A.Constan</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("preop_av_constant","preop_av_constant",$optAv,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Standart Deviasi</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderTextBox("preop_deviasi","preop_deviasi","30","200",$_POST["preop_deviasi"],"inputField", null,false);?><?php echo $dataDiagnostik["diag_deviasi"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Rencana Tindakan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_operasi_jenis","rawat_operasi_jenis",$optOperasiJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Paket Biaya</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_operasi_paket","rawat_operasi_paket",$optOperasiPaket,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Rencana Pemakaian IOL</strong></legend>
     <table width="40%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis IOL</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_iol_jenis","preop_iol_jenis",$optIOLJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Merk</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_iol_merk","preop_iol_merk",$optIOLMerk,null,null,null);?></td>
          </tr>
<!--
          <tr>
               <td align="left" class="tablecontent" width="35%">Serial Number</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("preop_iol_sn","preop_iol_sn","50","200",$_POST["preop_iol_sn"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Type</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("preop_iol_type","preop_iol_type","50","200",$_POST["preop_iol_type"],"inputField", null,false);?></td>
          </tr>
-->     

	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Data Pasien Hari Ini </strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="20%" align="left" class="tablecontent">Keluhan Pasien</td>
               <td width="80%" align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextBox("preop_keluhan","preop_keluhan","50","200",$_POST["preop_keluhan"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td width="20%" align="left" class="tablecontent">Keadaan Umum</td>
               <td width="80%" align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderComboBox("preop_keadaan_umum","preop_keadaan_umum",$optionsKeadaan,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Darah Lengkap</td>
               <td align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextArea("preop_lab_darah_lengkap","preop_lab_darah_lengkap","3","30",$_POST["preop_lab_darah_lengkap"],"inputField", null,null);?><td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Tonometri OD</td>
               <td align="left" class="tablecontent-odd" colspan="3">
                    <?php echo $view->RenderTextBox("preop_tonometri_scale_od","preop_tonometri_scale_od","5","5",$_POST["preop_tonometri_scale_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> / 
                    <?php echo $view->RenderTextBox("preop_tonometri_weight_od","preop_tonometri_weight_od","5","5",$_POST["preop_tonometri_weight_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> g = 
                    <?php echo $view->RenderTextBox("preop_tonometri_pressure_od","preop_tonometri_pressure_od","5","5",$_POST["preop_tonometri_pressure_od"],"inputField", "readonly",false);?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Tonometri OS</td>
               <td align="left" class="tablecontent-odd" colspan="3">
                    <?php echo $view->RenderTextBox("preop_tonometri_scale_os","preop_tonometri_scale_os","5","5",$_POST["preop_tonometri_scale_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> / 
                    <?php echo $view->RenderTextBox("preop_tonometri_weight_os","preop_tonometri_weight_os","5","5",$_POST["preop_tonometri_weight_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> g = 
                    <?php echo $view->RenderTextBox("preop_tonometri_pressure_os","preop_tonometri_pressure_os","5","5",$_POST["preop_tonometri_pressure_os"],"inputField", "readonly",false);?> mmHG
               </td>
          </tr>
          <tr class="subheader">
               <td align="left" ></td>
               <td align="left" >Awal</td>
	       <td align="left" >Regulasi Dengan</td>
	       <td align="left" >Hasil</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Tensimeter</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_tensi_awal","preop_lab_tensi_awal","15","15",$_POST["preop_lab_tensi_awal"],"inputField", null,false);?></td>
	       <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_tensi_regulasi","preop_lab_tensi_regulasi","15","15",$_POST["preop_lab_tensi_regulasi"],"inputField", null,false);?></td>
	       <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_tensi","preop_lab_tensi","15","15",$_POST["preop_lab_tensi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Nadi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nadi_awal","preop_lab_nadi_awal","15","15",$_POST["preop_lab_nadi_awal"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nadi_regulasi","preop_lab_nadi_regulasi","15","15",$_POST["preop_lab_nadi_regulasi"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nadi","preop_lab_nadi","15","15",$_POST["preop_lab_nadi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pernafasan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nafas_awal","preop_lab_nafas_awal","15","15",$_POST["preop_lab_nafas_awal"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nafas_regulasi","preop_lab_nafas_regulasi","15","15",$_POST["preop_lab_nafas_regulasi"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nafas","preop_lab_nafas","15","15",$_POST["preop_lab_nafas"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Status Lokal Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_mata_lokal_awal","preop_mata_lokal_awal","15","15",$_POST["preop_mata_lokal_awal"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_mata_lokal_regulasi","preop_mata_lokal_regulasi","15","15",$_POST["preop_mata_lokal_regulasi"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_mata_lokal","preop_mata_lokal","15","15",$_POST["preop_mata_lokal"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Gula Darah Acak</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_gula_darah_awal","preop_lab_gula_darah_awal","35","100",$_POST["preop_lab_gula_darah_awal"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_gula_darah_regulasi","preop_lab_gula_darah_regulasi","35","100",$_POST["preop_lab_gula_darah_regulasi"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_gula_darah","preop_lab_gula_darah","35","100",$_POST["preop_lab_gula_darah"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">ECG</td>
               <td align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextBox("preop_ecg","preop_ecg","100","255",$_POST["preop_ecg"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


	<!--
     <fieldset>
     <legend><strong>Anestesis</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_anestesis_jenis","preop_anestesis_jenis",$optAnestesisJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Obat Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_anestesis_obat","preop_anestesis_obat",$optAnestesisObat,null,null,'onChange="SetDosis(this.value);"');?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Dosis</td>
			<td align="left" class="tablecontent-odd" width="65%"><span id="sp_dosis">
			<?php if($_POST["preop_anestesis_dosis"]) { ?>
				<?php echo GetDosis($_POST["preop_anestesis_obat"],$_POST["preop_anestesis_dosis"]);?>
			<?php } else  { ?>
				<?php echo $view->RenderComboBox("preop_anestesis_dosis","preop_anestesis_dosis",$optDosis,null,null,null);?>
			<?php } ?>
			</span></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Komplikasi Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_anestesis_komp","preop_anestesis_komp",$optAnestesisKomplikasi,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Premedikasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_anestesis_pre","preop_anestesis_pre",$optAnestesisPremedikasi,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>-->

     <fieldset>
     <legend><strong>Regulasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="20%" class="tablecontent">Perlu Regulasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderCheckBox("preop_regulasi","preop_regulasi","y","null",($_POST["preop_regulasi"] == "y")?"checked":"",'onClick="GantiRegulasi();"')?>&nbsp;&nbsp;&nbsp;</td>
          </tr>
     </table>
     
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbRegulasi" style="display:block">
          <tr class="subheader">
               <td width="15%" align="center">No</td>
               <td width="45%" align="center">Jenis Regulasi</td>
               <td width="45%" align="center">Dengan Obat</td>
               <td width="45%" align="center">Hasil Regulasi</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent">Gula Darah</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_regulasi_gula_obat","preop_regulasi_gula_obat","30","30",$_POST["preop_regulasi_gula_obat"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_regulasi_gula_hasil","preop_regulasi_gula_hasil","30","30",$_POST["preop_regulasi_gula_hasil"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent">Tonometri</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_regulasi_tono_obat","preop_regulasi_tono_obat","30","30",$_POST["preop_regulasi_tono_obat"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_regulasi_tono_hasil","preop_regulasi_tono_hasil","30","30",$_POST["preop_regulasi_tono_hasil"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent-odd" colspan=4><label for="preop_regulasi_berhasil">Berhasil Diregulasi?</label><?php echo $view->RenderCheckBox("preop_regulasi_berhasil","preop_regulasi_berhasil","y","null",($_POST["preop_regulasi_berhasil"] == "y")?"checked":"",'onClick="GantiBerhasil();"')?>&nbsp;&nbsp;&nbsp;</td>
          </tr>
	</table>

     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbCatatan" style="display:none">
          <tr>
               <td align="left" width="20%" class="tablecontent">Catatan Untuk OK</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_catatan_ok","preop_catatan_ok","100","100",$_POST["preop_catatan_ok"],"inputField", null,false);?></td>
          </tr>
     </table>
     
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbTerapi" style="display:none">
          <tr class="subheader">
               <td width="100%" align="center" colspan=3>Tabel Terapi</td>
          </tr>	
          <tr>
               <td align="right" class="tablecontent">1</td>
               <td align="left" class="tablecontent">Obat</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_terapi_obat","preop_terapi_obat","30","30",$_POST["preop_terapi_obat"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">2</td>
               <td align="left" class="tablecontent">Saran</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_terapi_saran","preop_terapi_saran","30","30",$_POST["preop_terapi_saran"],"inputField", null,false);?></td>
          </tr>
	</table>     	
     </fieldset>
             
     <fieldset>
     <legend><strong>Penjadwalan</strong></legend>
     <table width="60%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent">Tanggal</td>
               <td align="left" class="tablecontent-odd" colspan=3>
				<?php echo $view->RenderTextBox("jadwal_tanggal","jadwal_tanggal","12","10",$_POST["jadwal_tanggal"],"null","null",false);?>
				<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_jadwal_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" />
				
               </td>
               <td align="left" class="tablecontent">Jam</td>
               <td align="left" class="tablecontent-odd" colspan=3>
				<select name="jadwal_jam" class="inputField" >
					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["jadwal_jam"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
					</select>:
					<select name="jadwal_menit" class="inputField" >
					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["jadwal_menit"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
               <td align="left" style="display:block;" id="next1" class="tablecontent">Tahap berikutnya&nbsp;&nbsp;&nbsp;
               <?php echo $view->RenderComboBox("cmbNext","cmbNext",$optionsNext,"inputField",null,null);?>
	       </td>
	       <td align="left" style="display:none;" id="next2" class="tablecontent">Tahap berikutnya&nbsp;&nbsp;&nbsp;
               <?php echo $view->RenderComboBox("cmbNext","cmbNext",$optionsNext1,"inputField",null,null);?>
               </td>
		</tr>
		<tr>
		    <td align="center">
		    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?>
		    </td>
		</tr>
	</table>
     </fieldset>

     </td>
</tr>	

</table>


<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"];?>"/>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
</span>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "jadwal_tanggal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_jadwal_tanggal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<?php } ?>


<?php echo $view->RenderBodyEnd(); ?>
