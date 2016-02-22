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
     

 	if(!$auth->IsAllowed("pre_operasi",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("pre_operasi",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "preop.php";
     $backPage = "preop_view.php?";
	$dokterPage = "preop_dokter_find.php?";
	$susterPage = "preop_suster_find.php?";
	
	$page[STATUS_OPERASI_JADWAL] = "operasi_jadwal.php";
	$page[STATUS_BEDAH] = "bedah.php";
	$page[STATUS_PREOP] = "preop.php";

     $tablePreOP = new InoTable("table1","99%","center");
     $tableOP = new InoTable("table1","99%","center");

     if(!$_POST["preop_regulasi"]) $_POST["preop_regulasi"] = "y";

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
          
		$sql = "select item_fisik  from logistik.logistik_item where cast(item_id as integer) = ".QuoteValue(DPE_NUMERIC,$item);
		$dataItem = $dtaccess->Fetch($sql);
		
          $sql = "select dosis_id, dosis_nama from logistik.logistik_dosis where id_fisik = ".QuoteValue(DPE_NUMERIC,$dataItem["item_fisik"]);
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
                    where a.reg_status like '".STATUS_PREOP.$status."' 
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
				$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$page[$dataTable[$i]["reg_status"]{0}].'?id_reg='.$dataTable[$i]["reg_id"].'&status='.$dataTable[$i]["reg_status"]{0}.'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
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
	
     if(!$_POST["btnSave"] && !$_POST["btnUpdate"]) {
          // --- buat cari suster yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id 
                    from klinik.klinik_preop_suster a 
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_preop c on c.preop_id = a.id_preop 
                    where cast(c.preop_waktu as date) = ".QuoteValue(DPE_DATE,date("Y-m-d")); 
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["preop_suster_nama"][$i]) $_POST["preop_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     }


     if ($_GET["id"]) {
          // === buat ngedit ---          
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["preop_id"] = $enc->Decode($_GET["id"]);
          }
          
          
          $sql = "select a.id_reg,preop_id from klinik.klinik_preop a 
				where preop_id = ".QuoteValue(DPE_CHAR,$_POST["preop_id"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $_GET["id_reg"] = $row_edit["id_reg"];
		$_POST["preop_id"] = $row_edit["preop_id"];
     }     

     // --- cari input preop pertama hari ini ---
     $sql = "select a.preop_id 
               from klinik.klinik_preop a 
               where cast(a.preop_waktu as date) = ".QuoteValue(DPE_CHAR,date('Y-m-d'))." 
               order by preop_waktu asc limit 1"; 
     $rs = $dtaccess->Execute($sql);
     $firstData = $dtaccess->Fetch($rs);
      
     $edit = (($firstData["preop_id"]==$_POST["preop_id"])||!$firstData["preop_id"])?true:false;
     
		
	if($_GET["id_reg"]) {
          
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.*,  
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  c.ref_keluhan 
                    from klinik.klinik_registrasi a
				            left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
       //   echo $sql;
       //   die();
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
				from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_id
				where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
				and a.rawat_icd_odos = 'OD' 
				order by a.rawat_icd_urut";
		$dataDiagIcdOD = $dtaccess->FetchAll($sql);
	
		$sql = "select b.icd_nomor, b.icd_nama
				from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_id
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

		$sql = "select a.*, b.bio_av_nama 
				from klinik.klinik_diagnostik a
				left join klinik.klinik_biometri_av b on a.diag_av_constant = b.bio_av_id 
                    where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]); 
          $dataDiagnostik= $dtaccess->Fetch($sql);

          if(!$dataDiagnostik) {
               $sql = "select reg_id from klinik.klinik_registrasi a
                         where a.id_cust_usr = ".$_POST["id_cust_usr"]." and
                         a.reg_tanggal < ".QuoteValue(DPE_DATE,$dataPasien["reg_tanggal"]);
              // $dataRegB4 = $dtaccess->Fetch($sql);
              // echo $sql;
               if($dataRegB4["reg_id"]) {
                    unset($dataDiagnostik);
                    $sql = "select a.*, b.bio_av_nama 
                              from klinik.klinik_diagnostik a
                              left join klinik.klinik_biometri_av b on a.diag_av_constant = b.bio_av_id 
                              where a.id_reg = ".QuoteValue(DPE_CHAR,$dataRegB4["reg_id"]); 
                    $dataDiagnostik= $dtaccess->Fetch($sql);
               }               
          }
          
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
		$_POST["preop_rumus"] = ($dataPreOp["preop_rumus"]) ? $dataPreOp["preop_rumus"]:$dataPemeriksaan["rawat_rumus"];
		$_POST["preop_operasi_paket"] = ($dataPreOp["preop_operasi_paket"]) ? $dataPreOp["preop_operasi_paket"]:$dataPemeriksaan["rawat_operasi_paket"];
		$_POST["preop_operasi_jenis"] = ($dataPreOp["preop_operasi_jenis"]) ? $dataPreOp["preop_operasi_jenis"]:$dataPemeriksaan["rawat_operasi_jenis"];
		
		if($dataPreOp) {
			
			$sql = "select pgw_nama, pgw_id from klinik.klinik_preop_suster a
					join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_preop = ".QuoteValue(DPE_CHAR,$dataPreOp["preop_id"]);
			$rs = $dtaccess->Execute($sql);
			$i=0;
			while($row=$dtaccess->Fetch($rs)) {
				$_POST["id_suster"][$i] = $row["pgw_id"];
				$_POST["preop_suster_nama"][$i] = $row["pgw_nama"];
				$i++;
			}
			
			$sql = "select pgw_nama, pgw_id from klinik.klinik_preop_dokter a
					join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_preop = ".QuoteValue(DPE_CHAR,$dataPreOp["preop_id"]);
			$rs = $dtaccess->Execute($sql);
			$row=$dtaccess->Fetch($rs); 
			$_POST["id_dokter"] = $row["pgw_id"];
			$_POST["preop_dokter_nama"] = $row["pgw_nama"];  
		}
		
		if(!$dataPreOp) {
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
where id_app = ".QuoteValue(DPE_NUMERIC,'3');		
      $rs = $dtaccess->Execute($sql);
			$row=$dtaccess->Fetch($rs); 
			$_POST["id_dokter"] = $row["dokter_1"];
			$_POST["preop_dokter_nama"] = $row["dokter1"];
		if($row["perawat1"]){
    	$_POST["id_suster"][0] = $row["perawat_1"];
			$_POST["preop_suster_nama"][0] = $row["perawat1"];
		}
		  if($row["perawat2"]){
    	$_POST["id_suster"][1] = $row["perawat_2"];
			$_POST["preop_suster_nama"][1] = $row["perawat2"];
		}	
			if($row["perawat3"]){
			$_POST["id_suster"][2] = $row["perawat_3"];
			$_POST["preop_suster_nama"][2] = $row["perawat3"];
		}	
			if($row["perawat4"]){
			$_POST["id_suster"][3] = $row["perawat_4"];
			$_POST["preop_suster_nama"][3] = $row["perawat4"];
		}	
			if($row["perawat5"]){
			$_POST["id_suster"][4] = $row["perawat_5"];
			$_POST["preop_suster_nama"][4] = $row["perawat5"];
		}	
    }
          
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
          $dbField[46] = "preop_rumus";
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
          $dbValue[46] = QuoteValue(DPE_CHARKEY,$_POST["preop_rumus"]);
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
		 
          
          

               $sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
                         and id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])." and fol_jenis = '".STATUS_PREOP."'";
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
                    $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_PREOP);
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
		
		
		 // --- insrt suster ---
          $sql = "delete from klinik.klinik_preop_suster where id_preop = ".QuoteValue(DPE_CHAR,$_POST["preop_id"]);
          $dtaccess->Execute($sql);
          
          foreach($_POST["id_suster"] as $key => $value){
               if($value) {
                    $dbTable = "klinik_preop_suster";
               
                    $dbField[0] = "preop_suster_id";   // PK
                    $dbField[1] = "id_preop";
                    $dbField[2] = "id_pgw";
                           
                    $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["preop_id"]);
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
          $sql = "delete from klinik.klinik_preop_dokter where id_preop = ".QuoteValue(DPE_CHAR,$_POST["preop_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_dokter"]) {
               
               $dbTable = "klinik_preop_dokter";
               
               $dbField[0] = "preop_dokter_id";   // PK
               $dbField[1] = "id_preop";
               $dbField[2] = "id_pgw";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["preop_id"]);
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

          if($_POST["btnSave"]) {
               $sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_PREMEDIKASI.STATUS_ANTRI."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql); 
          }
          
          if($_POST["btnSave"]) echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          exit();   

	}
	
	foreach($rawatKeadaan as $key => $value) {
          unset($show);
          if($_POST["preop_keadaan_umum"]==$key) $show="selected";
		
		$optionsKeadaan[] = $view->RenderOption($key,$value,$show);
	}


     $sql = "select diag_id from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     $dataDiag = $dtaccess->Fetch($sql);
     
     $count=0;	
	$optionsNext[$count] = $view->RenderOption(STATUS_SELESAI,"Tidak Perlu Tindakan",$show); $count++;
	if(!$dataDiag) $optionsNext[$count] = $view->RenderOption(STATUS_DIAGNOSTIK,"Ke Ruang Diagnostik",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_OPERASI_JADWAL,"Penjadwalan Operasi",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_OPERASI,"Operasi Hari Ini",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_BEDAH,"Bedah Minor",$show); $count++;

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

     $sql = "select item_id, item_nama from logistik.logistik_item where id_kat_item = ".QuoteValue(DPE_CHAR,KAT_OBAT_ANESTESIS);
     $dataAnestesisObat = $dtaccess->FetchAll($sql);

     // -- bikin combonya anestesis
     $optAnestesisJenis[0] = $view->RenderOption("","[Pilih Jenis Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisJenis);$i<$n;$i++) {
          $show = ($_POST["preop_anestesis_jenis"]==$dataAnestesisJenis[$i]["anes_jenis_id"]) ? "selected":"";
          $optAnestesisJenis[$i+1] = $view->RenderOption($dataAnestesisJenis[$i]["anes_jenis_id"],$dataAnestesisJenis[$i]["anes_jenis_nama"],$show); 
     }

     $optAnestesisKomplikasi[0] = $view->RenderOption("","[Pilih Komplikasi Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisKomplikasi);$i<$n;$i++) {
          $show = ($_POST["preop_anestesis_komp"]==$dataAnestesisKomplikasi[$i]["anes_komp_id"]) ? "selected":"";
          $optAnestesisKomplikasi[$i+1] = $view->RenderOption($dataAnestesisKomplikasi[$i]["anes_komp_id"],$dataAnestesisKomplikasi[$i]["anes_komp_nama"],$show); 
     }

     $optAnestesisPremedikasi[0] = $view->RenderOption("","[Pilih Premedikasi Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisPremedikasi);$i<$n;$i++) {
          $show = ($_POST["preop_anestesis_pre"]==$dataAnestesisPremedikasi[$i]["anes_pre_id"]) ? "selected":"";
          $optAnestesisPremedikasi[$i+1] = $view->RenderOption($dataAnestesisPremedikasi[$i]["anes_pre_id"],$dataAnestesisPremedikasi[$i]["anes_pre_nama"],$show); 
     }

     $optAnestesisObat[0] = $view->RenderOption("","[Pilih Obat Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisObat);$i<$n;$i++) {
          $show = ($_POST["preop_anestesis_obat"]==$dataAnestesisObat[$i]["item_id"]) ? "selected":"";
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
          $show = ($_POST["preop_iol_jenis"]==$dataIOLJenis[$i]["iol_jenis_id"]) ? "selected":"";
          $optIOLJenis[$i+1] = $view->RenderOption($dataIOLJenis[$i]["iol_jenis_id"],$dataIOLJenis[$i]["iol_jenis_nama"],$show); 
     }

     $optIOLMerk[0] = $view->RenderOption("","[Pilih Merk IOL]",$show); 
     for($i=0,$n=count($dataIOLMerk);$i<$n;$i++) {
          $show = ($_POST["preop_iol_merk"]==$dataIOLMerk[$i]["iol_merk_id"]) ? "selected":"";
          $optIOLMerk[$i+1] = $view->RenderOption($dataIOLMerk[$i]["iol_merk_id"],$dataIOLMerk[$i]["iol_merk_nama"],$show); 
     }

     // --- nyari datanya rumuys ---
     $sql = "select bio_rumus_id, bio_rumus_nama from klinik.klinik_biometri_rumus order by bio_rumus_nama";
     $dataRumus = $dtaccess->FetchAll($sql);
 
     // -- bikin combonya rumus
     $optRumus[0] = $view->RenderOption("","[Pilih Rumus Yg Dipakai]",$show); 
     for($i=0,$n=count($dataRumus);$i<$n;$i++) {
          $show = ($_POST["preop_rumus"]==$dataRumus[$i]["bio_rumus_id"]) ? "selected":"";
          $optRumus[$i+1] = $view->RenderOption($dataRumus[$i]["bio_rumus_id"],$dataRumus[$i]["bio_rumus_nama"],$show); 
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
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>


<script type="text/javascript">

<? $plx->Run(); ?>

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
     } else {
          document.getElementById('tbCatatan').style.display="none";
          document.getElementById('tbTerapi').style.display="block";
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

timer();
function SusterTambah(){ 
     var akhir = eval(document.getElementById('suster_tot').value)+1;
     
     $('#tb_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'preop_suster_nama[]', id:'preop_suster_nama_'+akhir},[],
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
     document.getElementById('preop_suster_nama_'+akhir).readOnly = true;
          
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
		<div class="tableheader">Antrian Pre Operasi</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetTindakan(STATUS_ANTRI); ?></div>
	</div>
	
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Pre Operasi</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetTindakan(STATUS_PROSES); ?></div>
	</div>
</div>

<?php } ?>



<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data PreOperasi</td>
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
                    <?php echo $view->RenderTextBox("preop_dokter_nama","preop_dokter_nama","30","100",$_POST["preop_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>
		</tr>
			
     
          <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php if(!$_POST["preop_suster_nama"]) { ?>
					<tr id="tr_suster_0">
						<td align="left" class="tablecontent-odd" width="70%">
							<?php echo $view->RenderTextBox("preop_suster_nama[]","preop_suster_nama_0","30","100",$_POST["preop_suster_nama"][0],"inputField", "readonly",false);?>
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
                                        <?php echo $view->RenderTextBox("preop_suster_nama[]","preop_suster_nama_".$i,"30","100",$_POST["preop_suster_nama"][$i],"inputField", "readonly",false);?>

									<a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                                        <input type="hidden" id="id_suster_<?php echo $i;?>" name="id_suster[]" value="<?php echo $_POST["id_suster"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">


									<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
									<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterDelete(<?php echo $i;?>);">


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
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - INA - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOD[0]["ina_kode"]." ".$dataDiagInaOD[0]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOD[1]["ina_kode"]." ".$dataDiagInaOD[1]["ina_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - INA - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOS[0]["ina_kode"]." ".$dataDiagInaOS[0]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 4</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOS[1]["ina_kode"]." ".$dataDiagInaOS[1]["ina_nama"];?></td>
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
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_k1_od" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_k1_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_k1_os" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_k1_os"];?>"></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">K2</td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_k2_od" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_k2_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_k2_os" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_k2_os"];?>"></td>
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
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_acial_od" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_acial_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_acial_os" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_acial_os"];?>"></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power IOL</td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_iol_od" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_iol_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_iol_os" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_iol_os"];?>"></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">AV Constant</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("preop_av_constant","preop_av_constant",$optAv,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Standart Deviasi</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderTextBox("preop_deviasi","preop_deviasi","10","30",$dataDiagnostik["diag_deviasi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Rumus yang dipakai</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("preop_rumus","preop_rumus",$optRumus,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>



     <fieldset>
     <legend><strong>Data Pasien Hari Ini </strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="20%" align="left" class="tablecontent">Keluhan Pasien</td>
               <td width="80%" align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_keluhan","preop_keluhan","50","200",$_POST["preop_keluhan"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td width="20%" align="left" class="tablecontent">Keadaan Umum</td>
               <td width="80%" align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("preop_keadaan_umum","preop_keadaan_umum",$optionsKeadaan,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Tensimeter</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_tensi","preop_lab_tensi","15","15",$_POST["preop_lab_tensi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Nadi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nadi","preop_lab_nadi","15","15",$_POST["preop_lab_nadi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pernafasan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nafas","preop_lab_nafas","15","15",$_POST["preop_lab_nafas"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Status Lokal Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_mata_lokal","preop_mata_lokal","15","15",$_POST["preop_mata_lokal"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Gula Darah Acak</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_gula_darah","preop_lab_gula_darah","50","100",$_POST["preop_lab_gula_darah"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Darah Lengkap</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("preop_lab_darah_lengkap","preop_lab_darah_lengkap","5","50",$_POST["preop_lab_darah_lengkap"],"inputField", null,null);?><td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Tonometri OD</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("preop_tonometri_scale_od","preop_tonometri_scale_od","5","5",$_POST["preop_tonometri_scale_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> / 
                    <?php echo $view->RenderTextBox("preop_tonometri_weight_od","preop_tonometri_weight_od","5","5",$_POST["preop_tonometri_weight_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> g = 
                    <?php echo $view->RenderTextBox("preop_tonometri_pressure_od","preop_tonometri_pressure_od","5","5",$_POST["preop_tonometri_pressure_od"],"inputField", "readonly",false);?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Tonometri OS</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("preop_tonometri_scale_os","preop_tonometri_scale_os","5","5",$_POST["preop_tonometri_scale_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> / 
                    <?php echo $view->RenderTextBox("preop_tonometri_weight_os","preop_tonometri_weight_os","5","5",$_POST["preop_tonometri_weight_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> g = 
                    <?php echo $view->RenderTextBox("preop_tonometri_pressure_os","preop_tonometri_pressure_os","5","5",$_POST["preop_tonometri_pressure_os"],"inputField", "readonly",false);?> mmHG
               </td>
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
     <legend><strong>Anestesis</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_anestesis_jenis","preop_anestesis_jenis",$optAnestesisJenis,null,null,null);?></td>
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
     </fieldset>

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
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
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
<input type="hidden" name="preop_id" value="<?php echo $_POST["preop_id"];?>"/>

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
