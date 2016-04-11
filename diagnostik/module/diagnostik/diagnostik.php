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
     

 	if(!$auth->IsAllowed("diagnostik",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("diagnostik",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "diagnostik.php";
	$dokterPage = "diag_dokter_find.php?";
	$susterPage = "diag_suster_find.php?";
     $backPage = "diag_view.php?";
     $adminFindPage = "diag_admin_find.php?";


     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetDiagnostik,SetDiagnostik");     


     function GetDiagnostik($status) {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, a.reg_jadwal, z.fol_lunas 
                    from klinik.klinik_registrasi a
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
				join (
					select distinct fol_lunas, id_reg from klinik.klinik_folio
					where fol_jenis = '".STATUS_DIAGNOSTIK."' 
				) z on a.reg_id = z.id_reg 
                    where a.reg_status = '".STATUS_DIAGNOSTIK.$status."' and a.reg_tipe_umur='D'  order by reg_status desc, reg_tanggal asc, reg_waktu asc";
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
			/*if($status==0) {
				if(!$dataTable[$i]["fol_lunas"]) $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesDiagnostik(\''.$dataTable[$i]["reg_id"].'\')"/>';
			} else {*/
				$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
			//}
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

			if($status==0) {
				if(!$dataTable[$i]["fol_lunas"]) $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/on.gif" style="cursor:pointer" alt="Lunas" title="Lunas" border="0"/>';
				else 
				$tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/off.gif" style="cursor:pointer" alt="Belum Lunas" title="Belum Lunas" border="0"/>';
				$tbContent[$i][$counter][TABLE_ALIGN] = "center";
				$counter++;
			
			}
			
			if($dataTable[$i]["reg_jadwal"]=='y') $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/off.gif" alt="Terjadwal Operasi Hari Ini" title="Terjadwal Operasi Hari Ini" border="0"/>';
			else $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/on.gif" alt="TIdak Terjadwal Operasi Hari Ini" title="Tidak Terjadwal Operasi Hari Ini" border="0"/>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }

          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}

     function SetDiagnostik($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_DIAGNOSTIK.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
	
     if(!$_POST["btnSave"] && !$_POST["btnUpdate"]) {
          // --- buat cari suster yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id 
                    from klinik.klinik_diagnostik_suster a 
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_diagnostik c on c.diag_id = a.id_diag 
                    where cast(c.diag_waktu as date) = ".QuoteValue(DPE_DATE,date("Y-m-d")); 
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["diag_suster_nama"][$i]) $_POST["diag_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     }

     if ($_GET["id"]) {
          // === buat ngedit ---          
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["diag_id"] = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_diagnostik a 
				where diag_id = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $view->CreatePost($row_edit);
          $_GET["id_reg"] = $row_edit["id_reg"];
          



          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster"][$i] = $row["pgw_id"];
               $_POST["diag_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
          unset($rs);
          unset($row);

          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_admin a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
          $rs = $dtaccess->Execute($sql);
          
          $i=0;
          $row=$dtaccess->Fetch($rs);
          $_POST["id_admin"] = $row["pgw_id"];
          $_POST["diag_admin_nama"] = $row["pgw_nama"];
          $i++;
          unset($rs);
          unset($row);
          

          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"])." and diag_dokter_periksa = ".QuoteValue(DPE_CHAR,'main');
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_main_dokter"] = $row["pgw_id"];
          $_POST["diag_main_dokter_nama"] = $row["pgw_nama"];
          unset($rs);
          unset($row);
           
          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"])." and diag_dokter_periksa = ".QuoteValue(DPE_CHAR,'yag');
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_yag_dokter"] = $row["pgw_id"];
          $_POST["diag_yag_dokter_nama"] = $row["pgw_nama"];
          unset($rs);
          unset($row);
           
          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"])." and diag_dokter_periksa = ".QuoteValue(DPE_CHAR,'lpi');
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_lpi_dokter"] = $row["pgw_id"];
          $_POST["diag_lpi_dokter_nama"] = $row["pgw_nama"];
          unset($rs);
          unset($row);
           
          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"])." and diag_dokter_periksa = ".QuoteValue(DPE_CHAR,'argon');
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["diag_argon_dokter_nama"] = $row["pgw_nama"];
          $_POST["id_argon_dokter"] = $row["pgw_id"];
          unset($rs);
          unset($row);
           
          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"])." and diag_dokter_periksa = ".QuoteValue(DPE_CHAR,'slt');
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["diag_slt_dokter_nama"] = $row["pgw_nama"];
          $_POST["id_slt_dokter"] = $row["pgw_id"];
          unset($rs);
          unset($row);
           
          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"])." and diag_dokter_periksa = ".QuoteValue(DPE_CHAR,'glaukoma');
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["diag_glaukoma_dokter_nama"] = $row["pgw_nama"];
          $_POST["id_glaukoma_dokter"] = $row["pgw_id"];
          unset($rs);
          unset($row);
           
          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"])." and diag_dokter_periksa = ".QuoteValue(DPE_CHAR,'rap');
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["diag_rap_dokter_nama"] = $row["pgw_nama"];
          $_POST["id_rap_dokter"] = $row["pgw_id"];
          unset($rs);
          unset($row);
           
          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"])." and diag_dokter_periksa = ".QuoteValue(DPE_CHAR,'lpi');
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["diag_lpi_dokter_nama"] = $row["pgw_nama"];
          $_POST["id_lpi_dokter"] = $row["pgw_id"];
          unset($rs);
          unset($row);
           
     }

     // --- cari input diagnostik pertama hari ini ---
     $sql = "select a.diag_id 
               from klinik.klinik_diagnostik a 
               where cast(a.diag_waktu as date) = ".QuoteValue(DPE_CHAR,date('Y-m-d'))." 
               order by diag_waktu asc limit 1";
     $rs = $dtaccess->Execute($sql);
     $firstData = $dtaccess->Fetch($rs);
     
     $edit = (($firstData["diag_id"]==$_POST["diag_id"])||!$firstData["diag_id"])?true:false;
     
	
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, b.cust_usr_alergi, a.reg_jenis_pasien,  
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr
                    from klinik.klinik_registrasi a 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
          //$sql_update = "update klinik.klinik_registrasi set reg_status = '".STATUS_DIAGNOSTIK.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
		//$dtaccess->Execute($sql_update);
		
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
      $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
	
		$sql = "select a.id_biaya, b.biaya_kode
			 from klinik.klinik_folio a
			 left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
			 where a.id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
			 $dataNostik = $dtaccess->FetchAll($sql);
				//prir($dataNostik); a.id_biaya = to_char(b.biaya_id, '999')
				//echo $sql;
      for($i=0,$n=count($dataNostik);$i<$n;$i++) {
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_KERATOMETRI){
      $dataNostik["keratometri"] = BIAYA_KERATOMETRI;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_BIOMETRI){
      $dataNostik["biometri"] = BIAYA_BIOMETRI;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_GULA){
      $dataNostik["gula"] = BIAYA_GULA;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_USG){
      $dataNostik["usg"] = BIAYA_USG;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_EKG){
      $dataNostik["ekg"] = BIAYA_EKG;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_FUNDUS){
      $dataNostik["fundus"] = BIAYA_FUNDUS;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_OPTHALMOSCOPY){
      $dataNostik["opthalmoscopy"] = BIAYA_OPTHALMOSCOPY;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_OCT){
      $dataNostik["oct"] = BIAYA_OCT;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_YAG){
      $dataNostik["yag"] = BIAYA_YAG;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_ARGON){
      $dataNostik["argon"] = BIAYA_ARGON;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_HUMPREY){
      $dataNostik["humprey"] = BIAYA_HUMPREY;      
      }
      
      if($dataNostik[$i]["biaya_kode"]==BIAYA_SLT){
      $dataNostik["slt"] = BIAYA_SLT;      
      }
      
      }
				
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
where id_app = ".QuoteValue(DPE_NUMERIC,'5');		
      $rs = $dtaccess->Execute($sql);
			$row=$dtaccess->Fetch($rs); 
			$_POST["id_dokter"] = $row["dokter_1"];
			$_POST["diag_dokter_nama"] = $row["dokter1"];
		if($row["perawat1"]){
    	$_POST["id_suster"][0] = $row["perawat_1"];
			$_POST["diag_suster_nama"][0] = $row["perawat1"];
		}
		  if($row["perawat2"]){
    	$_POST["id_suster"][1] = $row["perawat_2"];
			$_POST["diag_suster_nama"][1] = $row["perawat2"];
		}	
			if($row["perawat3"]){
			$_POST["id_suster"][2] = $row["perawat_3"];
			$_POST["diag_suster_nama"][2] = $row["perawat3"];
		}	
			if($row["perawat4"]){
			$_POST["id_suster"][3] = $row["perawat_4"];
			$_POST["diag_suster_nama"][3] = $row["perawat4"];
		}	
			if($row["perawat5"]){
			$_POST["id_suster"][4] = $row["perawat_5"];
			$_POST["diag_suster_nama"][4] = $row["perawat5"];
		}	
	
				
				
	}

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
	
          if($_POST["btnSave"]) {
               $sql = "delete from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
          }

          $dbTable = "klinik.klinik_diagnostik";
          $dbField[0] = "diag_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "diag_k1_nilai";
          $dbField[3] = "diag_k1_od";
          $dbField[4] = "diag_k1_os";
          $dbField[5] = "diag_k2_nilai";
          $dbField[6] = "diag_k2_od";
          $dbField[7] = "diag_k2_os";
          $dbField[8] = "diag_acial_od";
          $dbField[9] = "diag_acial_os";
          $dbField[10] = "diag_iol_od";
          $dbField[11] = "diag_iol_os";
          $dbField[12] = "diag_coa";
          $dbField[13] = "diag_lensa";
          $dbField[14] = "diag_retina";
          $dbField[15] = "diag_kesimpulan";
          $dbField[16] = "id_cust_usr";
          $dbField[17] = "diag_gambar_usg";
          $dbField[18] = "diag_gambar_fundus";
          $dbField[19] = "diag_gambar_humpre";
          $dbField[20] = "diag_av_constant";
          $dbField[21] = "diag_deviasi";
          $dbField[22] = "diag_rumus";
          $dbField[23] = "diag_waktu";
          $dbField[24] = "diag_ekg";
          $dbField[25] = "diag_fundus";
          $dbField[26] = "diag_opthalmoscop";
          $dbField[27] = "diag_oct";
          $dbField[28] = "diag_yag";
          $dbField[29] = "diag_argon";
          $dbField[30] = "diag_glaukoma";
          $dbField[31] = "diag_humpre";
          $dbField[32] = "diag_gambar_oct";
          $dbField[33] = "diag_lab_gula_darah";
          $dbField[34] = "diag_lab_darah_lengkap";
          $dbField[35] = "diag_slt";
          $dbField[36] = "diag_lpi";
          $dbField[37] = "diag_nc_tonometri";
          $dbField[38] = "diag_nc_biometri";
	  
          
          if(!$_POST["diag_id"]) $_POST["diag_id"] = $dtaccess->GetTransID();
          
		$dbValue[0] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["diag_k1_nilai"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["diag_k1_od"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["diag_k1_os"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["diag_k2_nilai"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["diag_k2_od"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["diag_k2_os"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["diag_acial_od"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["diag_acial_os"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["diag_iol_od"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["diag_iol_os"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["diag_coa"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["diag_lensa"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["diag_retina"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,addslashes($_POST["diag_kesimpulan"]));
          $dbValue[16] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_usg"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_fundus"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_humpre"]);
          $dbValue[20] = QuoteValue(DPE_CHARKEY,$_POST["diag_av_constant"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["diag_deviasi"]);
          $dbValue[22] = QuoteValue(DPE_CHARKEY,$_POST["diag_rumus"]);
          $dbValue[23] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["diag_ekg"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["diag_fundus"]);
          $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["diag_opthalmoscop"]);
          $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["diag_oct"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["diag_yag"]);
          $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["diag_argon"]);
          $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["diag_glaukoma"]);
          $dbValue[31] = QuoteValue(DPE_CHAR,$_POST["diag_humpre"]);
          $dbValue[32] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_oct"]);
          $dbValue[33] = QuoteValue(DPE_CHAR,$_POST["diag_lab_gula_darah"]);
          $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["diag_lab_darah_lengkap"]);
          $dbValue[35] = QuoteValue(DPE_CHAR,$_POST["diag_slt"]);
          $dbValue[36] = QuoteValue(DPE_CHAR,$_POST["diag_lpi"]);
          $dbValue[37] = QuoteValue(DPE_CHAR,$_POST["diag_nc_tonometri"]);
          $dbValue[38] = QuoteValue(DPE_CHAR,$_POST["diag_nc_biometri"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if ($_POST["btnSave"]) {
              $dtmodel->Insert() or die("insert  error");	
          } else if ($_POST["btnUpdate"]) {
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
          $dbValue[2] = QuoteValue(DPE_CHAR,STATUS_DIAGNOSTIK);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

          $dbKey[0] = 0;

          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,$dbSchema);

          $dtmodel->Insert() or die("insert error");

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          // end insert 
          

          // --- insrt suster ---
          $sql = "delete from klinik.klinik_diagnostik_suster where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
          $dtaccess->Execute($sql);
          
          foreach($_POST["id_suster"] as $key => $value){
               if($value) {
                    $dbTable = "klinik_diagnostik_suster";
               
                    $dbField[0] = "diag_suster_id";   // PK
                    $dbField[1] = "id_diag";
                    $dbField[2] = "id_pgw";
                           
                    $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
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
          $sql = "delete from klinik.klinik_diagnostik_dokter where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_main_dokter"]) {
               
               $dbTable = "klinik_diagnostik_dokter";
               
               $dbField[0] = "diag_dokter_id";   // PK
               $dbField[1] = "id_diag";
               $dbField[2] = "id_pgw";
               $dbField[3] = "diag_dokter_periksa";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_main_dokter"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,"main");
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }

          if($_POST["id_yag_dokter"]) {
               
               $dbTable = "klinik_diagnostik_dokter";
               
               $dbField[0] = "diag_dokter_id";   // PK
               $dbField[1] = "id_diag";
               $dbField[2] = "id_pgw";
               $dbField[3] = "diag_dokter_periksa";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_yag_dokter"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,"yag");
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }

          if($_POST["id_argon_dokter"]) {
               
               $dbTable = "klinik_diagnostik_dokter";
               
               $dbField[0] = "diag_dokter_id";   // PK
               $dbField[1] = "id_diag";
               $dbField[2] = "id_pgw";
               $dbField[3] = "diag_dokter_periksa";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_argon_dokter"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,"argon");
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }

          if($_POST["id_slt_dokter"]) {
               
               $dbTable = "klinik_diagnostik_dokter";
               
               $dbField[0] = "diag_dokter_id";   // PK
               $dbField[1] = "id_diag";
               $dbField[2] = "id_pgw";
               $dbField[3] = "diag_dokter_periksa";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_slt_dokter"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,"slt");
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }

          if($_POST["id_glaukoma_dokter"]) {
               
               $dbTable = "klinik_diagnostik_dokter";
               
               $dbField[0] = "diag_dokter_id";   // PK
               $dbField[1] = "id_diag";
               $dbField[2] = "id_pgw";
               $dbField[3] = "diag_dokter_periksa";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_glaukoma_dokter"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,"glaukoma");
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }

          if($_POST["id_rap_dokter"]) {
               
               $dbTable = "klinik_diagnostik_dokter";
               
               $dbField[0] = "diag_dokter_id";   // PK
               $dbField[1] = "id_diag";
               $dbField[2] = "id_pgw";
               $dbField[3] = "diag_dokter_periksa";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_rap_dokter"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,"rap");
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }

          if($_POST["id_lpi_dokter"]) {
               
               $dbTable = "klinik_diagnostik_dokter";
               
               $dbField[0] = "diag_dokter_id";   // PK
               $dbField[1] = "id_diag";
               $dbField[2] = "id_pgw";
               $dbField[3] = "diag_dokter_periksa";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_lpi_dokter"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,"lpi");
               
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
               $sqlDelete = "delete from klinik.klinik_diagnostik_admin where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
               $dtaccess->Execute($sqlDelete);
          
                $dbTable = "klinik_diagnostik_admin";
                     
                $dbField[0] = "diag_admin_id";   // PK
                $dbField[1] = "id_diag";
                $dbField[2] = "id_pgw";
                       
                $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
                $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_admin"]);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                
                $dtmodel->Insert() or die("insert error"); 
                
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);
           }
          

          if($_POST["btnSave"]) {
               
              // $sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_PEMERIKSAAN.STATUS_ANTRI."', reg_waktu = CURRENT_TIME  where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
             //  $dtaccess->Execute($sql);
               
                         
               // ---- inset ket folio ---//
               if($_POST["diag_k1_od"] || $_POST["diag_k1_os"]|| $_POST["diag_k2_od"] || $_POST["diag_k2_os"]) 
                    $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_KERATOMETRI);
                    
               if($_POST["diag_acial_od"] || $_POST["diag_acial_os"]|| $_POST["diag_iol_od"] || $_POST["diag_iol_os"] || $_POST["diag_av_constant"] || $_POST["diag_deviasi"] || $_POST["diag_rumus"]) 
                    $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_BIOMETRI);
                    
               if($_POST["diag_coa"] || $_POST["diag_lensa"]|| $_POST["diag_diag_retina"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_USG);
               if($_POST["diag_lab_gula_darah"] || $_POST["diag_lab_darah_lengkap"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_GULA);

               if($_POST["diag_ekg"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_EKG);
               if($_POST["diag_fundus"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_FUNDUS);
               if($_POST["diag_opthalmoscop"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_OPTHALMOSCOPY);
               if($_POST["diag_oct"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_OCT);
               if($_POST["diag_yag"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_YAG);
               if($_POST["diag_argon"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_ARGON);
               if($_POST["diag_glaukoma"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_GLAUKOMA);
               if($_POST["diag_humpre"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_HUMPREY);

               
               if($sql_where) {
                    $sql = "select * from klinik.klinik_biaya where ".implode(" or ",$sql_where);
                    //$dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
                    $folWaktu = date("Y-m-d H:i:s");
               }               
               
               /*$lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 
               
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
                    unset($dbValue);
                    unset($dbKey);
                    unset($dbField); 
				 
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
               }*/
          $sql = "update klinik.klinik_registrasi set reg_status = '".$_POST["cmbNext"]."', reg_waktu = CURRENT_TIME  where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql); 
          }          
          
          if($_POST["btnSave"]) echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";

          exit();   

	}

/*   untuk ngecek jenis perawatan pasiennya
 *   (Pasien rawat jalan mata, pasien rawat inap, atau pasien UGD)
 *   supaya combobox next nya sesuai dengan jenis perawatan
 */
     $sql_test = "select reg_tipe_rawat from klinik.klinik_registrasi where reg_id=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
     $rs_test = $dtaccess->Execute($sql_test);
     $data_test = $dtaccess->Fetch($rs_test);
 
     if($data_test["reg_tipe_rawat"]=="M"){
	  $count=0;	    
	  $optionsNext[$count] = $view->RenderOption(STATUS_PEMERIKSAAN.STATUS_ANTRI,"Ruang 2 - Pemeriksaan",$show); $count++;
	  $optionsNext[$count] = $view->RenderOption(STATUS_BEDAH.STATUS_ANTRI,"Ruang 3 - Bedah Minor",$show); $count++;
	  $optionsNext[$count] = $view->RenderOption(STATUS_PREMEDIKASI.STATUS_ANTRI,"Ruang 5 - Penjadwalan Operasi",$show); $count++;
	  $optionsNext[$count] = $view->RenderOption(STATUS_PREOP.STATUS_ANTRI,"Ruang 5 - Pre Operasi",$show); $count++;
    $optionsNext[$count] = $view->RenderOption(STATUS_LABORATORIUM.STATUS_ANTRI,"Laboratorium",$show); $count++;
	  $optionsNext[$count] = $view->RenderOption(STATUS_RAWATINAP.STATUS_ANTRI,"Rawat Inap",$show); $count++;
     }elseif($data_test["reg_tipe_rawat"]=="D"){
	  $count=0;
	  $optionsNext[$count] = $view->RenderOption(STATUS_PEMERIKSAAN.STATUS_ANTRI,"Ruang UGD",$show); $count++;
     }elseif($data_test["reg_tipe_rawat"]=="I"){
	  $count=0;
	  $optionsNext[$count] = $view->RenderOption(STATUS_RAWATINAP.STATUS_MENGINAP,"Kembali ke Rawat Inap",$show); $count++;
     }


    /* $optionsBase1[0] = $view->RenderOption("1","TRUE",$show);
     $optionsBase1[1] = $view->RenderOption("0","FALSE",$show);

     $optionsBase2[0] = $view->RenderOption("1","TRUE",$show);
     $optionsBase2[1] = $view->RenderOption("0","FALSE",$show);

	
	$optionsNext[0] = $view->RenderOption("O","Operasi Hari Ini",$show);
	$optionsNext[1] = $view->RenderOption("P","Perawatan",$show);*/

     $lokasiUsg = $APLICATION_ROOT."images/foto_usg";
	$fotoUsg = $lokasiUsg."/".$_POST["diag_gambar_usg"];

     $lokasiFundus = $APLICATION_ROOT."images/foto_fundus";
	$fotoFundus = $lokasiFundus."/".$_POST["diag_gambar_fundus"];

     $lokasiHumpre = $APLICATION_ROOT."images/foto_humpre";
	$fotoHumpre = $lokasiHumpre."/".$_POST["diag_gambar_humpre"];

     $lokasiOct = $APLICATION_ROOT."images/foto_oct";
	$fotoOct = $lokasiOct."/".$_POST["diag_gambar_oct"];


     // --- nyari datanya rumuys ---
     $sql = "select bio_rumus_id, bio_rumus_nama from klinik.klinik_biometri_rumus order by bio_rumus_nama";
     $dataRumus = $dtaccess->FetchAll($sql);

     // -- bikin combonya rumus
     $optRumus[0] = $view->RenderOption("","[Pilih Rumus Yg Dipakai]",$show); 
     for($i=0,$n=count($dataRumus);$i<$n;$i++) {
          $show = ($_POST["diag_rumus"]==$dataRumus[$i]["bio_rumus_id"]) ? "selected":"";
          $optRumus[$i+1] = $view->RenderOption($dataRumus[$i]["bio_rumus_id"],$dataRumus[$i]["bio_rumus_nama"],$show); 
     }

     // --- nyari datanya rumuys ---
     $sql = "select bio_av_id, bio_av_nama from klinik.klinik_biometri_av order by bio_av_nama";
     $dataAv = $dtaccess->FetchAll($sql);

     // -- bikin combonya av
     $optAv[0] = $view->RenderOption("","[Pilih AV Constant Yg Dipakai]",$show); 
     for($i=0,$n=count($dataAv);$i<$n;$i++) {
          $show = ($_POST["diag_av_constant"]==$dataAv[$i]["bio_av_id"]) ? "selected":"";
          $optAv[$i+1] = $view->RenderOption($dataAv[$i]["bio_av_id"],$dataAv[$i]["bio_av_nama"],$show); 
     }
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>

<script type="text/javascript">

//	function ajaxFileUpload()
//	{
//		$("#loading")
//		.ajaxStart(function(){
//			$(this).show();
//		})
//		.ajaxComplete(function(){
//			$(this).hide();
//		});
//
//		$.ajaxFileUpload
//		(
//			{
//				url:'registrasi_upload.php',
//				secureuri:false,
//				fileElementId:'fileToUpload',
//				dataType: 'json',
//				success: function (data, status)
//				{
//					if(typeof(data.error) != 'undefined')
//					{
//						if(data.error != '')
//						{
//							alert(data.error);
//						}else
//						{
//							alert(data.msg);
//							//UpdateMedia(data.file,'type=r');
//                                   //GetThumbs('target=dv_thumbs');
//                                   document.getElementById('cust_usr_foto').value= data.file;
//                                   document.img_foto.src='<?php echo $lokasi."/";?>'+data.file;
//						}
//					}
//				},
//				error: function (data, status, e)
//				{
//					alert(e);
//				}
//			}
//		)
//		
//		return false;
//
//	}
	function ajaxFileUpload(fileupload,hidval,img)
	{
		var lokasi = Array();
		
		lokasi['img_usg'] = '<?php echo $lokasiUsg;?>';
		lokasi['img_fundus'] = '<?php echo $lokasiFundus;?>';
		lokasi['img_humpre'] = '<?php echo $lokasiHumpre;?>';
		lokasi['img_oct'] = '<?php echo $lokasiOct;?>';
		
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
				url:fileupload,
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
                                   document.getElementById(hidval).value= data.file;
                                   document.getElementById(img).src=lokasi[img]+'/'+data.file;
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
     //GetDiagnostik(0,'target=antri_kiri_isi');     
     GetDiagnostik(0,'target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}

function ProsesDiagnostik(id) {
	SetDiagnostik(id,'type=r');
	timer();
}

timer();

function ChangeDisplay(id) {
     var disp = Array();
     
     disp['none'] = 'block';
     disp['block'] = 'none';
     
     document.getElementById(id).style.display = disp[document.getElementById(id).style.display];
}

function SusterTambah(){
     var akhir = eval(document.getElementById('suster_tot').value)+1;
     
     $('#tb_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'diag_suster_nama[]', id:'diag_suster_nama_'+akhir},[],
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
     document.getElementById('diag_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterDelete(akhir){
     document.getElementById('hid_suster_del').value += document.getElementById('id_suster_'+akhir).value;
     
     $('#tr_suster_'+akhir).remove();
}

</script>


<?php if(!$_GET["id"]) { ?>

<!--<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Diagnostik</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php //echo GetDiagnostik(0); ?></div>
	</div>-->
		
	<div id="antri_kanan" style="float:left;width:80%;">
		<div class="tableheader">Antrian Diagnostik</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php //echo GetDiagnostik(1); ?></div>
	</div>
</div>
<br />
<?php } ?>



<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Diagnostik</td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
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
               <td width= "20%" align="left" class="tablecontent">Alergi</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label style="color:red"><?php echo $dataPasien["cust_usr_alergi"]; ?></label></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Petugas</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="20%"  class="tablecontent" align="left">Dokter</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("diag_main_dokter_nama","diag_main_dokter_nama","30","100",$_POST["diag_main_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&diag=main&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_main_dokter" name="id_main_dokter" value="<?php echo $_POST["id_main_dokter"];?>"/>
               </td>
          </tr>
     
          <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php if(!$_POST["diag_suster_nama"]) { ?>
					<tr id="tr_suster_0">
						<td align="left" class="tablecontent-odd" width="70%">
							<?php echo $view->RenderTextBox("diag_suster_nama[]","diag_suster_nama_0","30","100",$_POST["diag_suster_nama"][0],"inputField", "readonly",false);?>
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
                                        <?php echo $view->RenderTextBox("diag_suster_nama[]","diag_suster_nama_".$i,"30","100",$_POST["diag_suster_nama"][$i],"inputField", "readonly",false);?>
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
								<?php// } ?>
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
               <td align="left" class="tablecontent-odd" width="80%">
                    <table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_admin">
                         <tr id="tr_admin_0">
                              <td align="left" class="tablecontent-odd" width="40%">
                                   <?php echo $view->RenderTextBox("diag_admin_nama","diag_admin_nama","30","100",$_POST["diag_admin_nama"],"inputField", "readonly",false);?>       
                                   <?php echo $view->RenderHidden("id_admin","id_admin",$_POST["id_admin"]);?>
                                   <a href="<?php echo $adminFindPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Admin"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Admin" alt="Cari Admin" /></a>
                              </td>
                         </tr>
                    </table>
               </td>
          </tr>
	</table>
     </fieldset>


<? if($dataNostik["keratometri"] == BIAYA_KERATOMETRI) { ?>
     <fieldset>
     <legend><a href="#keratometri" id="keratometri" style="cursor:pointer" onClick="ChangeDisplay('tbKeratometri');"><strong>Keratometri</strong></a></legend>
     <table width="80%" border="1" cellpadding="4" cellspacing="1" id="tbKeratometri" style="display:none">
          <tr class="subheader">
               <td width="5%" align="center">&nbsp;</td>
               <td width="30%" align="center">OD</td>
               <td width="30%" align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">K1</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k1_od","diag_k1_od","30","30",$_POST["diag_k1_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k1_os","diag_k1_os","30","30",$_POST["diag_k1_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">K2</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k2_od","diag_k2_od","30","30",$_POST["diag_k2_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k2_os","diag_k2_os","30","30",$_POST["diag_k2_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>
<? } ?>

<? if($dataNostik["biometri"] == BIAYA_BIOMETRI) { ?>
     <fieldset>
     <legend><a href="#biometri" id="biometri" style="cursor:pointer" onClick="ChangeDisplay('tbBiometri');"><strong>Biometri</strong></a></legend>
     <table width="70%" border="1" cellpadding="4" cellspacing="1" id="tbBiometri" style="display:none">
          <tr class="subheader">
               <td width="25%" align="center">&nbsp;</td>
               <td width="35%" align="center">OD</td>
               <td width="35%" align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Acial Length</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_od","diag_acial_od","30","30",$_POST["diag_acial_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_os","diag_acial_os","30","30",$_POST["diag_acial_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power IOL</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","30","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_os","diag_iol_os","30","30",$_POST["diag_iol_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">AV Constant</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("diag_av_constant","diag_av_constant",$optAv,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Standart Deviasi</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderTextBox("diag_deviasi","diag_deviasi","10","30",$_POST["diag_deviasi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Rumus yang dipakai</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("diag_rumus","diag_rumus",$optRumus,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>
<? } ?>

<? if($dataNostik["gula"] == BIAYA_GULA) { ?>
     <fieldset>
     <legend><a href="#gula" id="gula" style="cursor:pointer" onClick="ChangeDisplay('tbGula');"><strong>LAB</strong></a></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1"  id="tbGula" style="display:none">
          <tr>
               <td align="left" class="tablecontent" width="20%">Gula Darah Acak</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_lab_gula_darah","diag_lab_gula_darah","50","100",$_POST["diag_lab_gula_darah"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Darah Lengkap</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_lab_darah_lengkap","diag_lab_darah_lengkap","5","50",$_POST["diag_lab_darah_lengkap"],"inputField", null,null);?></td>
          </tr>
	</table>
     </fieldset>
<? } ?>

<? if($dataNostik["usg"] == BIAYA_USG) { ?>
     <fieldset>
     <legend><a href="#usg" style="cursor:pointer" onClick="ChangeDisplay('tbUSG');" id="usg"><strong>USG</strong></a></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbUSG" style="display:none">
          <tr>
               <td align="left" class="tablecontent">COA</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_coa","diag_coa","15","15",$_POST["diag_coa"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Lensa</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_lensa","diag_lensa","15","15",$_POST["diag_lensa"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Retina</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_retina","diag_retina","15","15",$_POST["diag_retina"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Kesimpulan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_kesimpulan","diag_kesimpulan","5","50",$_POST["diag_kesimpulan"],"inputField", null,null);?></td>
          </tr>
	</table>
     </fieldset>
<? } ?>


     <fieldset>
     <legend><a href="#medik" onClick="ChangeDisplay('tbMedik');" id="medik"><strong>Tindakan Medik di R. Diagnostik</strong></a></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbMedik" style="display:none">
<? if($dataNostik["ekg"] == BIAYA_EKG) { ?>
          <tr>
               <td align="left" class="tablecontent" width="20%">EKG</td>
               <td align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextArea("diag_ekg","diag_ekg","2","50",$_POST["diag_ekg"],"inputField", null,null);?></td>
          </tr>
          <? } ?>
<? if($dataNostik["fundus"] == BIAYA_FUNDUS) { ?>
          <tr>
               <td align="left" class="tablecontent">Fundus Angiografi</td>
               <td align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextArea("diag_fundus","diag_fundus","2","50",$_POST["diag_fundus"],"inputField", null,null);?></td>
          </tr>
          <? } ?>
<? if($dataNostik["opthalmoscopy"] == BIAYA_OPTHALMOSCOPY) { ?>
          <tr>
               <td align="left" class="tablecontent">Indirect Opthalmoscopy</td>
               <td align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextArea("diag_opthalmoscop","diag_opthalmoscop","2","50",$_POST["diag_opthalmoscop"],"inputField", null,null);?></td>
          </tr>
          <? } ?>
<? if($dataNostik["oct"] == BIAYA_OCT) { ?>
          <tr>
               <td align="left" class="tablecontent">Optical Coherence Tomographi</td>
               <td align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextArea("diag_oct","diag_oct","2","50",$_POST["diag_oct"],"inputField", null,null);?></td>
          </tr>
          <? } ?>
<? if($dataNostik["yag"] == BIAYA_YAG) { ?>
          <tr>
               <td align="left" class="tablecontent">Yag Laser</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_yag","diag_yag","2","50",$_POST["diag_yag"],"inputField", null,null);?></td>
               <td width="20%"  class="tablecontent" align="left">Dokter Pelaksana</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("diag_yag_dokter_nama","diag_yag_dokter_nama","30","100",$_POST["diag_yag_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&diag=yag&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_yag_dokter" name="id_yag_dokter" value="<?php echo $_POST["id_yag_dokter"];?>"/>
               </td>
          </tr>
          <? } ?>
<? if($dataNostik["argon"] == BIAYA_ARGON) { ?>
          <tr>
               <td align="left" class="tablecontent">Argon Laser</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_argon","diag_argon","2","50",$_POST["diag_argon"],"inputField", null,null);?></td>
               <td width="20%"  class="tablecontent" align="left">Dokter Pelaksana</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("diag_argon_dokter_nama","diag_argon_dokter_nama","30","100",$_POST["diag_argon_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&diag=argon&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_argon_dokter" name="id_argon_dokter" value="<?php echo $_POST["id_argon_dokter"];?>"/>
               </td>
          </tr>
          <? } ?>
<? if($dataNostik["slt"] == BIAYA_SLT) { ?>
          <tr>
               <td align="left" class="tablecontent">Laser SLT</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_slt","diag_slt","2","50",$_POST["diag_slt"],"inputField", null,null);?></td>
               <td width="20%"  class="tablecontent" align="left">Dokter Pelaksana</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("diag_slt_dokter_nama","diag_slt_dokter_nama","30","100",$_POST["diag_slt_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>diag=slt&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_slt_dokter" name="id_slt_dokter" value="<?php echo $_POST["id_slt_dokter"];?>"/>
               </td>
          </tr>
          <? } ?>
<? if($dataNostik["glaukoma"] == BIAYA_GLAUKOMA) { ?>
          <tr>
               <td align="left" class="tablecontent">Laser Glaukoma</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_glaukoma","diag_glaukoma","2","50",$_POST["diag_glaukoma"],"inputField", null,null);?></td>
               <td width="20%"  class="tablecontent" align="left">Dokter Pelaksana</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("diag_glaukoma_dokter_nama","diag_glaukoma_dokter_nama","30","100",$_POST["diag_glaukoma_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&diag=glaukoma&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_glaukoma_dokter" name="id_glaukoma_dokter" value="<?php echo $_POST["id_glaukoma_dokter"];?>"/>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Laser RAP</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_rap","diag_rap","2","50",$_POST["diag_rap"],"inputField", null,null);?></td>
               <td width="20%"  class="tablecontent" align="left">Dokter Pelaksana</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("diag_rap_dokter_nama","diag_rap_dokter_nama","30","100",$_POST["diag_rap_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&diag=rap&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_rap_dokter" name="id_rap_dokter" value="<?php echo $_POST["id_rap_dokter"];?>"/>
               </td>
          </tr>
          <? } ?>
          <tr>
               <td align="left" class="tablecontent">Laser Peripheral Iridectomy</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_lpi","diag_lpi","2","50",$_POST["diag_lpi"],"inputField", null,null);?></td>
               <td width="20%"  class="tablecontent" align="left">Dokter Pelaksana</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("diag_lpi_dokter_nama","diag_lpi_dokter_nama","30","100",$_POST["diag_lpi_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&diag=lpi&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_lpi_dokter" name="id_lpi_dokter" value="<?php echo $_POST["id_lpi_dokter"];?>"/>
               </td>
          </tr>
<? if($dataNostik["humprey"] == BIAYA_HUMPREY) { ?>
          <tr>
               <td align="left" class="tablecontent">Humprey</td>
               <td align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextArea("diag_humpre","diag_humpre","2","50",$_POST["diag_humpre"],"inputField", null,null);?></td>
          </tr>
          <? } ?>
          <tr>
               <td align="left" class="tablecontent">Non Contact Biometri</td>
               <td align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextArea("diag_nc_biometri","diag_nc_biometri","2","50",$_POST["diag_nc_biometri"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Non Contact Tonometri</td>
               <td align="left" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextArea("diag_nc_tonometri","diag_nc_tonometri","2","50",$_POST["diag_nc_tonometri"],"inputField", null,null);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><a href="#foto" id="foto" onClick="ChangeDisplay('tbFoto');"><strong>Upload Foto</strong></a></legend>
     <table width="75%" border="1" cellpadding="4" cellspacing="1" id="tbFoto" style="display:none">
          <tr>
               <td width="25%" align="left" class="tablecontent">Gambar USG</td>
               <td width="25%" align="left" class="tablecontent">Gambar Fundus</td>          
               <td width="25%" align="left" class="tablecontent">Gambar Humpreys</td>
               <td width="25%" align="left" class="tablecontent">Gambar OCT</td>
          </tr>
          <tr>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_usg" id="img_usg" src="<?php echo $fotoUsg;?>"  border="1">
                    <input type="hidden" name="diag_gambar_usg" id="diag_gambar_usg" value="<?php echo $_POST["diag_gambar_usg"];?>">
               </td>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_fundus" id="img_fundus" src="<?php echo $fotoFundus;?>"  border="1">
                    <input type="hidden" name="diag_gambar_fundus" id="diag_gambar_fundus" value="<?php echo $_POST["diag_gambar_fundus"];?>">
               </td> 
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_humpre" id="img_humpre" src="<?php echo $fotoHumpre;?>"  border="1">
                    <input type="hidden" name="diag_gambar_humpre" id="diag_gambar_humpre" value="<?php echo $_POST["diag_gambar_humpre"];?>">
               </td>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_oct" id="img_oct" src="<?php echo $fotoOct;?>"  border="1">
                    <input type="hidden" name="diag_gambar_oct" id="diag_gambar_oct" value="<?php echo $_POST["diag_gambar_oct"];?>">
               </td>
          </tr>
          <tr>
               <td colspan=4 align="center">
                    <div id="loading" style="display:none;"><img id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></div> 
                    <input id="fileToUpload" type="file" size="35" name="fileToUpload" class="inputField">
               </td>
          </tr>
          <tr>
               <td align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload('diag_usg.php','diag_gambar_usg','img_usg');">Upload USG</button>
               </td>
               <td align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload('diag_fundus.php','diag_gambar_fundus','img_fundus');">Upload Fundus</button>
               </td>
               <td align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload('diag_humpre.php','diag_gambar_humpre','img_humpre');">Upload Humpre</button>
               </td>
               <td align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload('diag_oct.php','diag_gambar_oct','img_oct');">Upload OCT</button>
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

    <!-- <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>-->
     </td>
</tr>	

</table>

<?php echo $view->SetFocus("diag_k1_nilai");?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="diag_id" value="<?php echo $_POST["diag_id"];?>"/>

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
