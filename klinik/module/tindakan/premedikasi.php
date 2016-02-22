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
     

 	if(!$auth->IsAllowed("premedikasi",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("premedikasi",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "premedikasi.php";
	$dokterPage = "premedikasi_dokter_find.php?";
	$susterPage = "premedikasi_suster_find.php?";
	$backPage = "premedikasi_view.php?";
	$adminFindPage = "bedah_admin_find.php?";

     $tablePreOP = new InoTable("table1","99%","center");
     $tableOP = new InoTable("table1","99%","center");

     if(!$_POST["preme_regulasi"]) $_POST["preme_regulasi"] = "y";

     $plx = new InoLiveX("GetPreme,GetOp,GetTonometri,GetDosis,SetPreop");     

     function GetTonometri($scale,$weight) {
          global $dtaccess; 
               
          $sql = "select tono_pressure from klinik.klinik_tonometri where tono_scale = ".QuoteValue(DPE_NUMERIC,$scale)." and tono_weight = ".QuoteValue(DPE_NUMERIC,$weight);
          $dataTable = $dtaccess->Fetch($sql);
          return $dataTable["tono_pressure"];

     }     
     
     function GetDosis($item,$id=null) {
          global $dtaccess, $view;
          
		$sql = "select item_fisik from inventori.inv_item where item_id =".QuoteValue(DPE_NUMERIC,$item);
		$dataItem = $dtaccess->Fetch($sql);
		$sql = "select a.dosis_id, a.dosis_nama, b.fisik_nama
                    from inventori.inv_dosis a
                    join inventori.inv_fisik b on b.fisik_id = a.id_fisik
                    where a.id_fisik = ".QuoteValue(DPE_NUMERIC,$dataItem["item_fisik"]);
          $dataTable = $dtaccess->FetchAll($sql);

          $optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show);
          for($i=0,$n=count($dataTable);$i<$n;$i++) {
               $show = ($id==$dataTable[$i]["dosis_id"]) ? "selected":"";
               $optDosis[$i+1] = $view->RenderOption($dataTable[$i]["dosis_id"],$dataTable[$i]["dosis_nama"]." ".$dataTable[$i]["fisik_nama"],$show);
          }
          
          return $view->RenderComboBox("preme_anestesis_dosis","preme_anestesis_dosis",$optDosis,null,null,null);
     } 

     function GetPreme() {
          global $dtaccess, $view, $tableOP, $thisPage, $APLICATION_ROOT,$rawatStatus; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, a.reg_jadwal, fol_lunas 
				from klinik.klinik_registrasi a 
				join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
				left join klinik.klinik_folio c on c.id_cust_usr = a.id_cust_usr and a.reg_id = c.id_reg
				and c.id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_GULA_PREOP). " 
                    where a.reg_status like '".STATUS_PREMEDIKASI.STATUS_ANTRI."' and a.reg_tipe_umur='D'   order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);
//return $sql;
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
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jadwal";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
			unset($bar);
               if($dataTable[$i]["reg_status"]==STATUS_PREMEDIKASI.STATUS_ANTRI) {
               
                    if(!$dataTable[$i]["fol_lunas"] || $dataTable[$i]["fol_lunas"]=='y') {
                         $bar = '<a href="premedikasi.php?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';
                    } else 
                         $bar = "";
                         
               } else {
                    $bar = '<a onClick="ProsesPerawatan(\''.$dataTable[$i]["reg_id"].'\')" href="premedikasi.php?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
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
               
               if($dataTable[$i]["reg_jadwal"]=='y') $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/off.gif" alt="Terjadwal Operasi Hari Ini" title="Terjadwal Operasi Hari Ini" border="0"/>';
			else $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/on.gif" alt="Tidak Terjadwal Operasi Hari Ini" title="Tidak Terjadwal Operasi Hari Ini" border="0"/>';
               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }

          return $tableOP->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}
	
     function GetOp() {
          global $dtaccess, $view, $tableOP, $thisPage, $APLICATION_ROOT,$rawatStatus; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.reg_status like '".STATUS_OPERASI."%' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
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
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_PREMEDIKASI.STATUS_ANTRI."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
	
     if(!$_POST["btnSave"] && !$_POST["btnUpdate"]) {
          // --- buat cari suster yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id 
                    from klinik.klinik_premedikasi_suster a 
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_premedikasi c on c.preme_id = a.id_preme 
                    where cast(c.preme_waktu as date) = ".QuoteValue(DPE_DATE,date("Y-m-d")); 
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["preme_suster_nama"][$i]) $_POST["preme_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     }

     if ($_GET["id"]) {
          // === buat ngedit ---          
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["preme_id"] = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.id_reg,preme_id from klinik.klinik_premedikasi a 
				where preme_id = ".QuoteValue(DPE_CHAR,$_POST["preme_id"]); 
          $row_edit = $dtaccess->Fetch($sql);
          
          $_GET["id_reg"] = $row_edit["id_reg"]; 
          $_POST["preme_id"] = $row_edit["preme_id"]; 
		

          $sql = "select pgw_nama, pgw_id from klinik.klinik_premedikasi_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_preme = ".QuoteValue(DPE_CHAR,$_POST["preme_id"]); 
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster"][$i] = $row["pgw_id"];
               $_POST["preme_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
          unset($rs);
          unset($row);
          
          $sql = "select pgw_nama, pgw_id from klinik.klinik_premedikasi_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_preme = ".QuoteValue(DPE_CHAR,$_POST["preme_id"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_dokter"] = $row["pgw_id"];
          $_POST["preme_dokter_nama"] = $row["pgw_nama"];

          $sql = "select pgw_nama, pgw_id from klinik.klinik_premedikasi_admin a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          unset($rs);
          unset($row);
                            
          $row=$dtaccess->Fetch($rs);
          $_POST["id_admin"] = $row["pgw_id"];
          $_POST["op_admin_nama"] = $row["pgw_nama"];
          unset($rs);
          unset($row);
		
     }
	
     // --- cari input premedikasi pertama hari ini ---
     $sql = "select a.preme_id 
               from klinik.klinik_premedikasi a 
               where cast(a.preme_waktu as date) = ".QuoteValue(DPE_CHAR,date('Y-m-d'))." 
               order by preme_waktu asc limit 1";
     $rs = $dtaccess->Execute($sql);
     $firstData = $dtaccess->Fetch($rs);
     
     $edit = (($firstData["preme_id"]==$_POST["preme_id"])||!$firstData["preme_id"])?true:false;
      
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien,  
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                    from klinik.klinik_registrasi a
				left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                   left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
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
     
          $sql = "select *
                    from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataPemeriksaan = $dtaccess->Fetch($sql); 
	
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
	
		$sql = "select b.ina_kode, b.ina_nama
				from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
				where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
				and a.rawat_ina_odos = 'OD' 
				order by a.rawat_ina_urut";
		$dataDiagInaOD = $dtaccess->FetchAll($sql);
	
		$sql = "select b.ina_kode, b.ina_nama
				from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
				where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
				and a.rawat_ina_odos = 'OS' 
				order by a.rawat_ina_urut";
		$dataDiagInaOS = $dtaccess->FetchAll($sql);
 
		$sql = "select a.*, b.bio_av_nama,b.bio_av_id
				from klinik.klinik_preop a
				left join klinik.klinik_biometri_av b on a.preop_av_constant = b.bio_av_id 
                    where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]); 
          $dataPreOp= $dtaccess->Fetch($sql);
		
		$sql = "select * from klinik.klinik_premedikasi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]); 
		$dataPreme = $dtaccess->Fetch($sql);
		
		$view->CreatePost($dataPreme); 
		$_POST["preme_keluhan"] = ($dataPreme["preme_keluhan"]) ? $dataPreme["preme_keluhan"]:$dataPreOp["preop_keluhan"];
		$_POST["preme_keadaan_umum"] = ($dataPreme["preme_keadaan_umum"]) ? $dataPreme["preme_keadaan_umum"]:$dataPreOp["preop_keadaan_umum"];
		$_POST["preme_lab_tensi"] = $dataPreme["preme_lab_tensi"];
		$_POST["preme_lab_nadi"] = $dataPreme["preme_lab_nadi"];
		$_POST["preme_lab_nafas"] = $dataPreme["preme_lab_nafas"];
		$_POST["preme_mata_lokal"] = ($dataPreme["preme_mata_lokal"]) ? $dataPreme["preme_mata_lokal"]:$dataPreOp["preop_mata_lokal"];
		$_POST["preme_lab_gula_darah"] = ($dataPreme["preme_lab_gula_darah"]) ? $dataPreme["preme_lab_gula_darah"]:$dataPreOp["preop_lab_gula_darah"];
		$_POST["preme_lab_darah_lengkap"] = ($dataPreme["preme_lab_darah_lengkap"]) ? $dataPreme["preme_lab_darah_lengkap"]:$dataPreOp["preop_lab_darah_lengkap"];
          $_POST["preme_iol_jenis"] = ($dataPreme["preme_iol_jenis"]) ? $dataPreme["preme_iol_jenis"]:$dataPreOp["preop_iol_jenis"];
          $_POST["preme_iol_merk"] = ($dataPreme["preme_iol_merk"]) ? $dataPreme["preme_iol_merk"]:$dataPreOp["preop_iol_merk"];
		$_POST["preme_anestesis_jenis"] = ($dataPreme["preme_anestesis_jenis"]) ? $dataPreme["preme_anestesis_jenis"]:$dataPreOp["preop_anestesis_jenis"];
          $_POST["preme_tonometri_scale_od"] = $dataPreme["preme_tonometri_scale_od"];
		$_POST["preme_tonometri_weight_od"] = $dataPreme["preme_tonometri_weight_od"];
		$_POST["preme_tonometri_pressure_od"] = $dataPreme["preme_tonometri_pressure_od"];
		$_POST["preme_tonometri_scale_os"] = $dataPreme["preme_tonometri_scale_os"];
		$_POST["preme_tonometri_weight_os"] = $dataPreme["preme_tonometri_weight_os"];
		$_POST["preme_tonometri_pressure_os"] = $dataPreme["preme_tonometri_pressure_os"];

		if(!$dataPreme) {
		 $sql = "select a.dokter_1,a.perawat_1,a.perawat_2,a.perawat_3,a.perawat_4,a.perawat_5,
      a.petugas_id, b.pgw_nama as dokter1, c.pgw_nama as perawat1 , 
d.pgw_nama as perawat2, e.pgw_nama as perawat3 , f.pgw_nama as perawat4, g.pgw_nama as perawat5
from global.global_petugas a
left join hris.hris_pegawai b on b.pgw_id = a.dokter_1
left join hris.hris_pegawai c on c.pgw_id = a.perawat_1
left join hris.hris_pegawai d on d.pgw_id = a.perawat_2
left join hris.hris_pegawai e on e.pgw_id = a.perawat_3
left join hris.hris_pegawai f on f.pgw_id = a.perawat_4
left join hris.hris_pegawai g on g.pgw_id = a.perawat_5
where id_app = ".QuoteValue(DPE_NUMERIC,'6');		
      $rs = $dtaccess->Execute($sql);
			$row=$dtaccess->Fetch($rs); 
			$_POST["id_dokter"] = $row["dokter_1"];
			$_POST["preme_dokter_nama"] = $row["dokter1"];
		if($row["perawat1"]){
    	$_POST["id_suster"][0] = $row["perawat_1"];
			$_POST["preme_suster_nama"][0] = $row["perawat1"];
		}
		  if($row["perawat2"]){
    	$_POST["id_suster"][1] = $row["perawat_2"];
			$_POST["preme_suster_nama"][1] = $row["perawat2"];
		}	
			if($row["perawat3"]){
			$_POST["id_suster"][2] = $row["perawat_3"];
			$_POST["preme_suster_nama"][2] = $row["perawat3"];
		}	
			if($row["perawat4"]){
			$_POST["id_suster"][3] = $row["perawat_4"];
			$_POST["preme_suster_nama"][3] = $row["perawat4"];
		}	
			if($row["perawat5"]){
			$_POST["id_suster"][4] = $row["perawat_5"];
			$_POST["preme_suster_nama"][4] = $row["perawat5"];
		}	
    }

	}

	// ----- update data ----- //
     if ($_POST["btnSave"] || $_POST["btnUpdate"] || $_POST["btnSaveOk"] || $_POST["btnUpdateOk"]) {
	
          if($_POST["btnSave"] || $_POST["btnSaveOk"]) {
               $sql = "delete from klinik.klinik_premedikasi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
		}
		
          $dbTable = "klinik.klinik_premedikasi";
		
          $dbField[0] = "preme_id";   // PK
          $dbField[1] = "id_reg"; 
          $dbField[2] = "preme_tonometri_scale_od"; 
          $dbField[3] = "preme_lab_tensi";
          $dbField[4] = "preme_lab_nadi";
          $dbField[5] = "preme_lab_nafas"; 
          $dbField[6] = "id_cust_usr";
          $dbField[7] = "preme_tonometri_weight_od";
          $dbField[8] = "preme_tonometri_pressure_od";
          $dbField[9] = "preme_waktu";
          $dbField[10] = "preme_tonometri_od";
          $dbField[11] = "preme_tonometri_os";
          $dbField[12] = "preme_tonometri_weight_os";
          $dbField[13] = "preme_tonometri_pressure_os";
          $dbField[14] = "preme_tonometri_scale_os"; 
          $dbField[15] = "preme_anestesis_jenis";
          $dbField[16] = "preme_anestesis_obat";
          $dbField[17] = "preme_anestesis_dosis";
          $dbField[18] = "preme_anestesis_komp";
          $dbField[19] = "preme_anestesis_pre";
          $dbField[20] = "preme_iol_jenis";
          $dbField[21] = "preme_iol_merk";
          
          if(!$_POST["preme_id"]) $_POST["preme_id"] = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["preme_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]); 
          $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["preme_tonometri_scale_od"]); 
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["preme_lab_tensi"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["preme_lab_nadi"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["preme_lab_nafas"]); 
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["preme_tonometri_weight_od"]);
          $dbValue[8] = QuoteValue(DPE_NUMERIC,$_POST["preme_tonometri_pressure_od"]);
          $dbValue[9] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["preme_tonometri_od"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["preme_tonometri_os"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["preme_tonometri_weight_os"]);
          $dbValue[13] = QuoteValue(DPE_NUMERIC,$_POST["preme_tonometri_pressure_os"]);
          $dbValue[14] = QuoteValue(DPE_NUMERIC,$_POST["preme_tonometri_scale_os"]); 
          $dbValue[15] = QuoteValue(DPE_CHARKEY,$_POST["preme_anestesis_jenis"]);
          $dbValue[16] = QuoteValue(DPE_CHARKEY,$_POST["preme_anestesis_obat"]);
          $dbValue[17] = QuoteValue(DPE_CHARKEY,$_POST["preme_anestesis_dosis"]);
          $dbValue[18] = QuoteValue(DPE_CHARKEY,$_POST["preme_anestesis_komp"]);
          $dbValue[19] = QuoteValue(DPE_CHARKEY,$_POST["preme_anestesis_pre"]);
          $dbValue[20] = QuoteValue(DPE_CHARKEY,$_POST["preme_iol_jenis"]);
          $dbValue[21] = QuoteValue(DPE_CHARKEY,$_POST["preme_iol_merk"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if ($_POST["btnSave"] || $_POST["btnSaveOk"]) {
              $dtmodel->Insert() or die("insert  error");	
          } elseif ($_POST["btnUpdate"] || $_POST["btnUpdateOk"]) {
              $dtmodel->Update() or die("update  error");	
          }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          // insert ke tabel klinik_history_pasien
          $dbSchema = "klinik";
          $dbTable = "klinik_history_pasien";

          $dbField[0] = "history_id";
          $dbField[1] = "id_reg";
          $dbField[2] = "history_status_pasien";
          $dbField[3] = "history_when_out";

          $history_id = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$history_id);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,STATUS_PREMEDIKASI);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

          $dbKey[0] = 0;

          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,$dbSchema);

          $dtmodel->Insert() or die("insert error");

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          // end insert

          //update biometri
          $dbTable = "klinik.klinik_preop";
          $dbField[0] = "preop_id";   // PK 
          $dbField[1] = "preop_acial_od";
          $dbField[2] = "preop_acial_os";
          $dbField[3] = "preop_iol_od";
          $dbField[4] = "preop_iol_os";
          $dbField[5] = "preop_av_constant";
          $dbField[6] = "preop_deviasi";
          $dbField[7] = "preop_rumus";
      
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["preop_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["preop_acial_od"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["preop_acial_os"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["preop_iol_od"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["preop_iol_os"]);
          $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["preop_av_constant"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["preop_deviasi"]);
          $dbValue[7] = QuoteValue(DPE_CHARKEY,$_POST["preop_rumus"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          

          $dtmodel->Update() or die("update  error");	
   
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
		
		 // --- insrt suster ---
          $sql = "delete from klinik.klinik_premedikasi_suster where id_preme = ".QuoteValue(DPE_CHAR,$_POST["preme_id"]);
          $dtaccess->Execute($sql);
           
          foreach($_POST["id_suster"] as $key => $value){
               if($value) {
                    $dbTable = "klinik_premedikasi_suster";
               
                    $dbField[0] = "preme_suster_id";   // PK
                    $dbField[1] = "id_preme";
                    $dbField[2] = "id_pgw";
                           
                    $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["preme_id"]);
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
		

          // --- insrt dokter ---
          $sql = "delete from klinik.klinik_premedikasi_dokter where id_preme = ".QuoteValue(DPE_CHAR,$_POST["preme_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_dokter"]) {
               
               $dbTable = "klinik_premedikasi_dokter";
               
               $dbField[0] = "preme_dokter_id";   // PK
               $dbField[1] = "id_preme";
               $dbField[2] = "id_pgw";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["preme_id"]);
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


          if($_POST["id_admin"])    {
               $sqlDelete = "delete from klinik.klinik_premedikasi_admin where id_preme = ".QuoteValue(DPE_CHAR,$_POST["preme_id"]);
               $dtaccess->Execute($sqlDelete);
          
                $dbTable = "klinik_premedikasi_admin";
                     
                $dbField[0] = "preme_admin_id";   // PK
                $dbField[1] = "id_preme";
                $dbField[2] = "id_pgw";
                       
                $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["preme_id"]);
                $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_admin"]);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                
                $dtmodel->Insert() or die("insert error"); 
                
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);
           }
          

          if($_POST["btnSaveOk"] || $_POST["btnUpdateOk"]) { 
               $sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_OPERASI.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql); 
          }else{ 
		    if($_POST["btnSave"] || $_POST["btnUpdate"]){
				
				$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_OPERASI_JADWAL.STATUS_ANTRI."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
				$dtaccess->Execute($sql); 
				
				
				// --- buat nyimpen klaim pemeriksaan pas pembatalan ---
				$sql = "select b.* from klinik.klinik_biaya_pasien a
          				join klinik.klinik_paket_klaim b on b.paket_klaim_id = a.id_paket_klaim
          				where biaya_pasien_status = ".QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN)."
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
                              $dbValue[6] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
          			 
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
	       }
          
          if($_POST["btnSave"] || $_POST["btnSaveOk"]) echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          exit();

     }
	
	foreach($rawatKeadaan as $key => $value) { 
		
		$optionsKeadaan[$key] = $value;
	}
 
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

     $sql = "select item_id, item_nama
               from inventori.inv_item
               where id_kat_item = ".QuoteValue(DPE_CHAR,KAT_OBAT_ANESTESIS)."
               order by item_id";
     $dataAnestesisObat = $dtaccess->FetchAll($sql);

     // -- bikin combonya anestesis
     $optAnestesisJenis[0] = $view->RenderOption("","[Pilih Jenis Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisJenis);$i<$n;$i++) {
          $show = ($_POST["preme_anestesis_jenis"]==$dataAnestesisJenis[$i]["anes_jenis_id"]) ? "selected":"";
          $optAnestesisJenis[$i+1] = $view->RenderOption($dataAnestesisJenis[$i]["anes_jenis_id"],$dataAnestesisJenis[$i]["anes_jenis_nama"],$show); 
     }

     $optAnestesisKomplikasi[0] = $view->RenderOption("","[Pilih Komplikasi Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisKomplikasi);$i<$n;$i++) {
          $show = ($_POST["preme_anestesis_komp"]==$dataAnestesisKomplikasi[$i]["anes_komp_id"]) ? "selected":"";
          $optAnestesisKomplikasi[$i+1] = $view->RenderOption($dataAnestesisKomplikasi[$i]["anes_komp_id"],$dataAnestesisKomplikasi[$i]["anes_komp_nama"],$show); 
     }

     $optAnestesisPremedikasi[0] = $view->RenderOption("","[Pilih Premedikasi Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisPremedikasi);$i<$n;$i++) {
          $show = ($_POST["preme_anestesis_pre"]==$dataAnestesisPremedikasi[$i]["anes_pre_id"]) ? "selected":"";
          $optAnestesisPremedikasi[$i+1] = $view->RenderOption($dataAnestesisPremedikasi[$i]["anes_pre_id"],$dataAnestesisPremedikasi[$i]["anes_pre_nama"],$show); 
     }

     $optAnestesisObat[0] = $view->RenderOption("","[Pilih Obat Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisObat);$i<$n;$i++) {
          $show = ($_POST["preme_anestesis_obat"]==$dataAnestesisObat[$i]["item_id"]) ? "selected":"";
          $optAnestesisObat[$i+1] = $view->RenderOption($dataAnestesisObat[$i]["item_id"],$dataAnestesisObat[$i]["item_nama"],$show); 
     }
	$optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show); 

     // --- nyari datanya IOL ---
     $sql = "select iol_jenis_id, iol_jenis_nama from klinik.klinik_iol_jenis";
     $dataIOLJenis = $dtaccess->FetchAll($sql);

     $sql = "select iol_merk_id, iol_merk_nama from klinik.klinik_iol_merk";
     $dataIOLMerk = $dtaccess->FetchAll($sql);

     $optIOLJenis[0] = $view->RenderOption("","[Pilih Jenis IOL]",$show); 
     for($i=0,$n=count($dataIOLJenis);$i<$n;$i++) {
          $show = ($_POST["preme_iol_jenis"]==$dataIOLJenis[$i]["iol_jenis_id"]) ? "selected":"";
          $optIOLJenis[$i+1] = $view->RenderOption($dataIOLJenis[$i]["iol_jenis_id"],$dataIOLJenis[$i]["iol_jenis_nama"],$show); 
     }

     $optIOLMerk[0] = $view->RenderOption("","[Pilih Merk IOL]",$show); 
     for($i=0,$n=count($dataIOLMerk);$i<$n;$i++) {
          $show = ($_POST["preme_iol_merk"]==$dataIOLMerk[$i]["iol_merk_id"]) ? "selected":"";
          $optIOLMerk[$i+1] = $view->RenderOption($dataIOLMerk[$i]["iol_merk_id"],$dataIOLMerk[$i]["iol_merk_nama"],$show); 
     }
    
    // --- nyari datanya rumuys ---
     $sql = "select bio_rumus_id, bio_rumus_nama from klinik.klinik_biometri_rumus order by bio_rumus_nama";
     $dataRumus = $dtaccess->FetchAll($sql);
 
     // -- bikin combonya rumus
     $optRumus[0] = $view->RenderOption("","[Pilih Rumus Yg Dipakai]",$show); 
     for($i=0,$n=count($dataRumus);$i<$n;$i++) {
          $show = ($dataPreOp["preop_rumus"]==$dataRumus[$i]["bio_rumus_id"]) ? "selected":"";
          $optRumus[$i+1] = $view->RenderOption($dataRumus[$i]["bio_rumus_id"],$dataRumus[$i]["bio_rumus_nama"],$show); 
     }
    
     // --- nyari datanya rumuys ---
     $sql = "select bio_av_id, bio_av_nama from klinik.klinik_biometri_av order by bio_av_nama";
     $dataAv = $dtaccess->FetchAll($sql);

     // -- bikin combonya av
     $optAv[0] = $view->RenderOption("","[Pilih AV Constant Yg Dipakai]",$show); 
     for($i=0,$n=count($dataAv);$i<$n;$i++) {
          $show = ($dataPreOp["bio_av_id"]==$dataAv[$i]["bio_av_id"]) ? "selected":"";
          $optAv[$i+1] = $view->RenderOption($dataAv[$i]["bio_av_id"],$dataAv[$i]["bio_av_nama"],$show); 
     }

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


function GantiRegulasi() {
     if(document.getElementById('preme_regulasi').checked) {
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
     if(document.getElementById('preme_regulasi_berhasil').checked) {
          document.getElementById('tbCatatan').style.display="block";
          document.getElementById('tbTerapi').style.display="none";
     } else {
          document.getElementById('tbCatatan').style.display="none";
          document.getElementById('tbTerapi').style.display="block";
     }
}

function CheckData(frm) {
     return true;
}


function SetTonometriOD(){
     var scale = document.getElementById('preme_tonometri_scale_od');
     var weight = document.getElementById('preme_tonometri_weight_od');

     if(scale.value && isNaN(scale.value)){
          scale.value = '';
          return false;
     }

     if(weight.value && isNaN(weight.value)){
          weight.value = '';
          return false;
     }

     GetTonometri(scale.value,weight.value,'target=preme_tonometri_pressure_od');
     return true;
}

function SetTonometriOS(){
     var scale = document.getElementById('preme_tonometri_scale_os');
     var weight = document.getElementById('preme_tonometri_weight_os');

     if(scale.value && isNaN(scale.value)){
          scale.value = '';
          return false;
     }

     if(weight.value && isNaN(weight.value)){
          weight.value = '';
          return false;
     }

     GetTonometri(scale.value,weight.value,'target=preme_tonometri_pressure_os');
     return true;
}

function SetDosis(id) {
     GetDosis(id,'target=sp_dosis');
}

timer();
function SusterTambah(){ 
     var akhir = eval(document.getElementById('suster_tot').value)+1;
     
     $('#tb_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'preme_suster_nama[]', id:'preme_suster_nama_'+akhir},[],
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
     document.getElementById('preme_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterDelete(akhir){
     document.getElementById('hid_suster_del').value += document.getElementById('id_suster_'+akhir).value;
     
     $('#tr_suster_'+akhir).remove();
}
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
		<td align="left" colspan=2 class="tableheader">Input Data Premedikasi</td>
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
	</table>
     </fieldset>
	
     <fieldset>
     <legend><strong>Petugas</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
          <tr>
               <td align="left" width="20%" class="tablecontent">Dokter</td>
               <td align="left" class="tablecontent-odd" width="30%" colspan="2"> 
                    <?php echo $view->RenderTextBox("preme_dokter_nama","preme_dokter_nama","30","100",$_POST["preme_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>
		</tr> 
                  <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php if(!$_POST["preme_suster_nama"]) { ?>
					<tr id="tr_suster_0">
						<td align="left" class="tablecontent-odd" width="70%">
							<?php echo $view->RenderTextBox("preme_suster_nama[]","preme_suster_nama_0","30","100",$_POST["preme_suster_nama"][0],"inputField", "readonly",false);?>
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
                                        <?php echo $view->RenderTextBox("preme_suster_nama[]","preme_suster_nama_".$i,"30","100",$_POST["preme_suster_nama"][$i],"inputField", "readonly",false);?>
								<?php //if($edit) {?>
									<a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
								<?php //} ?>
                                        <input type="hidden" id="id_suster_<?php echo $i;?>" name="id_suster[]" value="<?php echo $_POST["id_suster"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
								<?php //if($edit) {?>
									<?php if($i==0) { ?>
									<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
									<?php } else { ?>
									<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterDelete(<?php echo $i;?>);">
									<?php } ?>
								<?php //} ?>
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
               <td width="20%"  class="tablecontent" align="left">Administrasi</td>
               <td align="left" class="tablecontent-odd" width="80%"  colspan=3>
                    <table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_admin">
                         <tr id="tr_admin_0">
                              <td align="left" class="tablecontent-odd" width="40%">
                                   <?php echo $view->RenderTextBox("op_admin_nama","op_admin_nama","30","100",$_POST["op_admin_nama"],"inputField", "readonly",false);?>       
                                   <?php echo $view->RenderHidden("id_admin","id_admin",$_POST["id_admin"]);?>
                                   <a href="<?php echo $adminFindPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Admin"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Admin" alt="Cari Admin" /></a>
                              </td>
                         </tr>
                    </table>
               </td>
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
     
     <?php 
	  $sql = "select b.prosedur_kode, b.prosedur_nama
	       from klinik.klinik_perawatan_prosedur a join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode
	       where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
	       order by a.rawat_prosedur_urut";
	  $dataProsedur = $dtaccess->FetchAll($sql);
     ?>
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
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataPreOp["preop_k1_od"];?></td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataPreOp["preop_k1_os"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">K2</td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataPreOp["preop_k2_od"];?></td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataPreOp["preop_k2_os"];?></td>
          </tr>
	</table>
     </fieldset>

    
    <!--  <fieldset>
     <legend><strong>Biometri</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td align="center" width="20%">&nbsp;</td>
               <td align="center" width="40%">OD</td>
               <td align="center" width="40%">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Acial Length</td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php //echo $dataPreOp["preop_acial_od"];?></td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php //echo $dataPreOp["preop_acial_os"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power IOL</td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php //echo $dataPreOp["preop_iol_od"];?></td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php //echo $dataPreOp["preop_iol_os"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">A.Constan</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php //echo $dataPreOp["bio_av_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Standart Deviasi</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php //echo $dataPreOp["preop_deviasi"];?></td>
          </tr>
	</table>
     </fieldset> -->
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
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_acial_od" size="30" maxlenght="100" value="<?php echo $dataPreOp["preop_acial_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_acial_os" size="30" maxlenght="100" value="<?php echo $dataPreOp["preop_acial_os"];?>"></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power IOL</td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_iol_od" size="30" maxlenght="100" value="<?php echo $dataPreOp["preop_iol_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_iol_os" size="30" maxlenght="100" value="<?php echo $dataPreOp["preop_iol_os"];?>"></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">AV Constant</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("preop_av_constant","preop_av_constant",$optAv,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Standart Deviasi</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderTextBox("preop_deviasi","preop_deviasi","10","30",$dataPreOp["preop_deviasi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Rumus yang dipakai</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("preop_rumus","preop_rumus",$optRumus,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="50%">
				<fieldset>
				<legend><strong>Data Pasien Hari Ini </strong></legend>
				<table width="100%" border="1" cellpadding="4" cellspacing="1">
					<tr>
						<td width="20%" align="left" class="tablecontent">Keluhan Pasien</td>
						<td width="80%" align="left" class="tablecontent-odd"><?php echo $view->RenderLabel("preop_keluhan","preop_keluhan",$dataPreOp["preop_keluhan"],"inputField", null,false);?></td>
					</tr>
					<tr>
						<td width="20%" align="left" class="tablecontent">Keadaan Umum</td>
						<td width="80%" align="left" class="tablecontent-odd"><?php echo $view->RenderLabel("preop_keadaan_umum","preop_keadaan_umum",$optionsKeadaan[$dataPreOp["preop_keadaan_umum"]],null,null,null);?></td>
					</tr>
					<tr>
						<td align="left" class="tablecontent">Tensimeter</td>
						<td align="left" class="tablecontent-odd"><?php echo $view->RenderLabel("preop_lab_tensi","preop_lab_tensi",$dataPreOp["preop_lab_tensi"],"inputField", null,false);?></td>
					</tr>
					<tr>
						<td align="left" class="tablecontent">Nadi</td>
						<td align="left" class="tablecontent-odd"><?php echo $view->RenderLabel("preop_lab_nadi","preop_lab_nadi",$dataPreOp["preop_lab_nadi"],"inputField", null,false);?></td>
					</tr>
					<tr>
						<td align="left" class="tablecontent">Pernafasan</td>
						<td align="left" class="tablecontent-odd"><?php echo $view->RenderLabel("preop_lab_nafas","preop_lab_nafas",$dataPreOp["preop_lab_nafas"],"inputField", null,false);?></td>
					</tr>
					<tr>
						<td align="left" class="tablecontent">Status Lokal Mata</td>
						<td align="left" class="tablecontent-odd"><?php echo $view->RenderLabel("preop_mata_lokal","preop_mata_lokal",$dataPreOp["preop_mata_lokal"],"inputField", null,false);?></td>
					</tr>
					<tr>
						<td align="left" class="tablecontent" width="20%">Gula Darah Acak</td>
						<td align="left" class="tablecontent-odd"><?php echo $view->RenderLabel("preop_lab_gula_darah","preop_lab_gula_darah",$dataPreOp["preop_lab_gula_darah"],"inputField", null,false);?></td>
					</tr>
					<tr>
						<td align="left" class="tablecontent">Darah Lengkap</td>
						<td align="left" class="tablecontent-odd"><?php echo $view->RenderLabel("preop_lab_darah_lengkap","preop_lab_darah_lengkap",$dataPreOp["preop_lab_darah_lengkap"],"inputField", null,null);?><td>
					</tr>
					<tr>
						<td align="left" width="20%" class="tablecontent">Tonometri OD</td>
						<td align="left" class="tablecontent-odd">
							<?php echo $view->RenderLabel("preop_tonometri_scale_od","preop_tonometri_scale_od",$dataPreOp["preop_tonometri_scale_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> / 
							<?php echo $view->RenderLabel("preop_tonometri_weight_od","preop_tonometri_weight_od",$dataPreOp["preop_tonometri_weight_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> g = 
							<?php echo $view->RenderLabel("preop_tonometri_pressure_od","preop_tonometri_pressure_od",$dataPreOp["preop_tonometri_pressure_od"],"inputField", "readonly",false);?> mmHG
						</td>
					</tr>
					<tr>
						<td align="left" width="20%" class="tablecontent">Tonometri OS</td>
						<td align="left" class="tablecontent-odd">
							<?php echo $view->RenderLabel("preop_tonometri_scale_os","preop_tonometri_scale_os",$dataPreOp["preop_tonometri_scale_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> / 
							<?php echo $view->RenderLabel("preop_tonometri_weight_os","preop_tonometri_weight_os",$dataPreOp["preop_tonometri_weight_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> g = 
							<?php echo $view->RenderLabel("preop_tonometri_pressure_os","preop_tonometri_pressure_os",$dataPreOp["preop_tonometri_pressure_os"],"inputField", "readonly",false);?> mmHG
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
               <td width="50%" valign="top">
				<fieldset>
				<legend><strong>Pemeriksaan Ulang</strong></legend>
				<table width="100%" border="1" cellpadding="4" cellspacing="1"> 
					<tr>
						<td align="left" class="tablecontent">Tensimeter</td>
						<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preme_lab_tensi","preme_lab_tensi","15","15",$_POST["preme_lab_tensi"],"inputField", null,false);?></td>
					</tr>
					<tr>
						<td align="left" class="tablecontent">Nadi</td>
						<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preme_lab_nadi","preme_lab_nadi","15","15",$_POST["preme_lab_nadi"],"inputField", null,false);?></td>
					</tr>
					<tr>
						<td align="left" class="tablecontent">Pernafasan</td>
						<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preme_lab_nafas","preme_lab_nafas","15","15",$_POST["preme_lab_nafas"],"inputField", null,false);?></td>
					</tr>  
					<tr>
						<td align="left" width="20%" class="tablecontent">Tonometri OD</td>
						<td align="left" class="tablecontent-odd">
							<?php echo $view->RenderTextBox("preme_tonometri_scale_od","preme_tonometri_scale_od","5","5",$_POST["preme_tonometri_scale_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> / 
							<?php echo $view->RenderTextBox("preme_tonometri_weight_od","preme_tonometri_weight_od","5","5",$_POST["preme_tonometri_weight_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> g = 
							<?php echo $view->RenderTextBox("preme_tonometri_pressure_od","preme_tonometri_pressure_od","5","5",$_POST["preme_tonometri_pressure_od"],"inputField", "readonly",false);?> mmHG
						</td>
					</tr>
					<tr>
						<td align="left" width="20%" class="tablecontent">Tonometri OS</td>
						<td align="left" class="tablecontent-odd">
							<?php echo $view->RenderTextBox("preme_tonometri_scale_os","preme_tonometri_scale_os","5","5",$_POST["preme_tonometri_scale_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> / 
							<?php echo $view->RenderTextBox("preme_tonometri_weight_os","preme_tonometri_weight_os","5","5",$_POST["preme_tonometri_weight_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> g = 
							<?php echo $view->RenderTextBox("preme_tonometri_pressure_os","preme_tonometri_pressure_os","5","5",$_POST["preme_tonometri_pressure_os"],"inputField", "readonly",false);?> mmHG
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
	</table>




     <fieldset>
     <legend><strong>Rencana Pemakaian IOL</strong></legend>
     <table width="40%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis IOL</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preme_iol_jenis","preme_iol_jenis",$optIOLJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Merk</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preme_iol_merk","preme_iol_merk",$optIOLMerk,null,null,null);?></td>
          </tr>
<!--
          <tr>
               <td align="left" class="tablecontent" width="35%">Serial Number</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("preme_iol_sn","preme_iol_sn","50","200",$_POST["preme_iol_sn"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Type</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("preme_iol_type","preme_iol_type","50","200",$_POST["preme_iol_type"],"inputField", null,false);?></td>
          </tr>
-->     

	</table>
     </fieldset>



     <fieldset>
     <legend><strong>ANESTESIS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preme_anestesis_jenis","preme_anestesis_jenis",$optAnestesisJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Obat Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preme_anestesis_obat","preme_anestesis_obat",$optAnestesisObat,null,null,'onChange="SetDosis(this.value);"');?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Dosis</td>
			<td align="left" class="tablecontent-odd" width="65%"><span id="sp_dosis">
			<?php if($_POST["preme_anestesis_dosis"]) { ?>
				<?php echo GetDosis($_POST["preme_anestesis_obat"],$_POST["preme_anestesis_dosis"]);?>
			<?php } else  { ?>
				<?php echo $view->RenderComboBox("preme_anestesis_dosis","preme_anestesis_dosis",$optDosis,null,null,null);?>
			<?php } ?>
			</span></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Komplikasi Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preme_anestesis_komp","preme_anestesis_komp",$optAnestesisKomplikasi,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">PREMEDIKASI</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preme_anestesis_pre","preme_anestesis_pre",$optAnestesisPremedikasi,null,null,null);?></td>
          </tr>
	</table>
     </fieldset> 
     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center">
				<?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdateOk" : "btnSaveOk","btnSave","Setuju Operasi","button",false,null);?>
				<?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Batal Operasi","button",false,null);?>
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
<input type="hidden" name="preop_id" value="<?php echo $dataPreOp["preop_id"];?>"/>
<input type="hidden" name="preme_id" value="<?php echo $_POST["preme_id"];?>"/>

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
