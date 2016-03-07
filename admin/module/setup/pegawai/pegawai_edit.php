<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     
     $thisPage = "pegawai_edit.php";
     $viewPage = "pegawai_view.php";

 	if(!$auth->IsAllowed("setup_pegawai",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("setup_pegawai",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $isPersonalia = $auth->IsAllowed("setup_pegawai",PRIV_UPDATE);

     if($_POST["pgw_id"]) $pgwId = $_POST["pgw_id"];

	
	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
          
          if($_POST["btnUpdate"]){
               $pgwId = & $_POST["pgw_id"];
               $_x_mode = "Edit";
          }		
		
		// ---- Checking Data ---- //	
		$err_code = 65535;
      echo $sql;    
		if($_POST["pgw_tanggal_lahir"]) {
               if(check_date(date_db($_POST["pgw_tanggal_lahir"]))==true)$err_code = clearbit($err_code,1);
               else $err_code = setbit($err_code,1);
		} else $err_code = clearbit($err_code,1);	

		if($_POST["pgw_sd_tanggal_lulus"]) {
			if(check_date(date_db($_POST["pgw_sd_tanggal_lulus"]))==true)$err_code = clearbit($err_code,2);
			else $err_code = setbit($err_code,2);
		} else $err_code = clearbit($err_code,2);
		
		if($_POST["pgw_sltp_tanggal_lulus"]) {
			if(check_date(date_db($_POST["pgw_sltp_tanggal_lulus"]))==true)$err_code = clearbit($err_code,3);
			else $err_code = setbit($err_code,3);
		} else $err_code = clearbit($err_code,3);

		if($_POST["pgw_slta_tanggal_lulus"]) {
			if(check_date(date_db($_POST["pgw_slta_tanggal_lulus"]))==true)$err_code = clearbit($err_code,4);
			else $err_code = setbit($err_code,4);
		} else $err_code = clearbit($err_code,4);

		if($_POST["pgw_diploma_tanggal_lulus"]) {
			if(check_date(date_db($_POST["pgw_diploma_tanggal_lulus"]))==true)$err_code = clearbit($err_code,5);
			else $err_code = setbit($err_code,5);
		} else $err_code = clearbit($err_code,5);

		if($_POST["pgw_s1_tanggal_lulus"]) {
			if(check_date(date_db($_POST["pgw_s1_tanggal_lulus"]))==true)$err_code = clearbit($err_code,6);
			else $err_code = setbit($err_code,6);
		} else $err_code = clearbit($err_code,6);

		if($_POST["pgw_s2_tanggal_lulus"]) {
			if(check_date(date_db($_POST["pgw_s2_tanggal_lulus"]))==true)$err_code = clearbit($err_code,7);
			else $err_code = setbit($err_code,7);
		} else $err_code = clearbit($err_code,7);

		if($_POST["pgw_s3_tanggal_lulus"]) {
			if(check_date(date_db($_POST["pgw_s3_tanggal_lulus"]))==true)$err_code = clearbit($err_code,8);
			else $err_code = setbit($err_code,8);
		} else $err_code = clearbit($err_code,8);

		if($_POST["pgw_tanggal_masuk"]) {
			if(check_date(date_db($_POST["pgw_tanggal_masuk"]))==true)$err_code = clearbit($err_code,9);
			else $err_code = setbit($err_code,9);
		} else $err_code = clearbit($err_code,9);
          
          if($_POST["pgw_tanggal_keluar"]) {
			if(check_date(date_db($_POST["pgw_tanggal_keluar"]))==true)$err_code = clearbit($err_code,10);
			else $err_code = setbit($err_code,10);
		} else $err_code = clearbit($err_code,10);
          
          if($_POST["pgw_nip"])$err_code = clearbit($err_code,11);
		else $err_code = setbit($err_code,11);
          
          if ($_POST["btnSave"]) 
               $sql = "SELECT * FROM hris.hris_pegawai WHERE UPPER(pgw_nip) = ".QuoteValue(DPE_CHAR,strtoupper($_POST["pgw_nama"]));
          else
               $sql = "SELECT pgw_id FROM hris_pegawai WHERE UPPER(pgw_nip) = ".QuoteValue(DPE_CHAR,strtoupper($_POST["pgw_nama"]))." AND pgw_id <> ".$_POST["pgw_id"];
  
          $rs_check = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
          
          if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,12);
          else $err_code = clearbit($err_code,12); 
          $dtaccess->Clear($rs_check);
          
          if($_POST["id_dep"]=="--")$err_code = setbit($err_code,13);
		else $err_code = clearbit($err_code,13);
          
          if($_POST["pgw_jabatan_struktural"]=="--")$err_code = setbit($err_code,14);
		else $err_code = clearbit($err_code,14);
          
          if($_POST["usr_loginname"] && !$_POST["id_rol"]) $err_code = setbit($err_code,15);
          else $err_code = clearbit($err_code,15);
          
          if($_POST["usr_loginname"] && ($_POST["usr_password"]!=$_POST["usr_password2"]))
               $err_code = setbit($err_code,16);
          else $err_code = clearbit($err_code,16);

		

		if ($err_code == 0) {
               
               // --- ngisi login name nya ---
               if($_POST["usr_loginname"]) {
                    // --- ngisi user loginnya ---
                    $dbTable = "global_auth_user";
                    
                    $dbField[0] = "usr_id";   // PK
                    $dbField[1] = "usr_name";
                    $dbField[2] = "usr_loginname";
                    $dbField[3] = "id_rol";
                    $dbField[4] = "usr_status";
                    $dbField[5] = "usr_when_create";
                    $dbField[6] = "usr_app_def";
                    if($_POST["is_password"]) $dbField[7] = "usr_password";
     
                    if(!$_POST["usr_status"]) $_POST["usr_status"] = 'n';
                    
                    if($_POST["usr_id"]) $usrId = $_POST["usr_id"];
                    else {
                         $usrId = $dtaccess->GetNewID("global_auth_user","usr_id",DB_SCHEMA_GLOBAL);
                         $_POST["usr_status"] = 'y';
                    }
                    
                    $dbValue[0] = QuoteValue(DPE_NUMERIC,$usrId);
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pgw_nama"]);
                    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["usr_loginname"]);
                    $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_rol"]);
                    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["usr_status"]);
                    $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));               
                    $dbValue[6] = QuoteValue(DPE_CHAR,APP_HRIS);
                    if($_POST["is_password"]) $dbValue[7] = QuoteValue(DPE_CHAR,md5($_POST["usr_password"]));
                         
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
                    
                    if($_POST["usr_id"]){
                         $dtmodel->Update() or die("update error");
                    } else {
                         $dtmodel->Insert() or die("insert error");
                    }
                    
                    unset($dtmodel);
                    unset($dbField);
                    unset($dbValue);
                    unset($dbKey);
               }
               
               
               // --- ngisi data pegawai nya ---
               $counter=0;
               $dbTable = "hris_pegawai";
               
               $dbField[$counter] = "pgw_id";          $counter++;
               $dbField[$counter] = "pgw_nama";               $counter++;
               $dbField[$counter] = "pgw_nama_panggilan";     $counter++;
               $dbField[$counter] = "pgw_tempat_lahir";       $counter++;
               $dbField[$counter] = "pgw_tanggal_lahir";      $counter++;
               $dbField[$counter] = "pgw_jenis_kelamin";      $counter++;
               $dbField[$counter] = "pgw_status_nikah";       $counter++;
			$dbField[$counter] = "pgw_foto";               $counter++;
               $dbField[$counter] = "pgw_agama";             $counter++;
               
               $dbField[$counter] = "pgw_warganegara";       $counter++;
               $dbField[$counter] = "pgw_suku_bangsa";       $counter++;
               $dbField[$counter] = "pgw_ktp_no";            $counter++;
               $dbField[$counter] = "pgw_passport_no";       $counter++;
               $dbField[$counter] = "pgw_golongan_darah";    $counter++;
               $dbField[$counter] = "pgw_nama_bank";         $counter++;
               $dbField[$counter] = "pgw_no_rekening";       $counter++;
               $dbField[$counter] = "pgw_alamat_asal";       $counter++;
               $dbField[$counter] = "pgw_telp_asal";         $counter++;
               $dbField[$counter] = "pgw_hp_asal";           $counter++;
               
               $dbField[$counter] = "pgw_alamat_surat";      $counter++;
               $dbField[$counter] = "pgw_telp_surat";        $counter++;
               $dbField[$counter] = "pgw_alamat_surabaya";   $counter++;
               $dbField[$counter] = "pgw_telp_surabaya";     $counter++;
               $dbField[$counter] = "pgw_telp_hp";           $counter++;
               $dbField[$counter] = "pgw_kontak_darurat";    $counter++;
               $dbField[$counter] = "pgw_kontak_darurat_telp";    $counter++;
               $dbField[$counter] = "pgw_kontak_darurat_hubungan";     $counter++;
               $dbField[$counter] = "pgw_kontak_darurat_hp";           $counter++;
			$dbField[$counter] = "pgw_sd_nama";                $counter++;
               
			$dbField[$counter] = "pgw_sd_kota";                $counter++;     
			$dbField[$counter] = "pgw_sd_tanggal_lulus";       $counter++;
			$dbField[$counter] = "pgw_sd_no_ijasah";           $counter++;
			$dbField[$counter] = "pgw_sltp_nama";              $counter++;
			$dbField[$counter] = "pgw_sltp_kota";              $counter++;
			$dbField[$counter] = "pgw_sltp_tanggal_lulus";     $counter++;
			$dbField[$counter] = "pgw_sltp_no_ijasah";         $counter++;
			$dbField[$counter] = "pgw_slta_nama";              $counter++;
			$dbField[$counter] = "pgw_slta_kota";              $counter++;
			$dbField[$counter] = "pgw_slta_tanggal_lulus";     $counter++;
			
               $dbField[$counter] = "pgw_slta_no_ijasah";         $counter++;
			$dbField[$counter] = "pgw_diploma_nama";           $counter++;
			$dbField[$counter] = "pgw_diploma_pt_asal";        $counter++;
			$dbField[$counter] = "pgw_diploma_kota";           $counter++;
			$dbField[$counter] = "pgw_diploma_bidang_ilmu";    $counter++;
			$dbField[$counter] = "pgw_diploma_tanggal_lulus";  $counter++;
			$dbField[$counter] = "pgw_diploma_no_ijasah";      $counter++;
			$dbField[$counter] = "pgw_diploma_gelar";          $counter++;
			$dbField[$counter] = "pgw_diploma_ipk";            $counter++;
			$dbField[$counter] = "pgw_s1_nama";                $counter++;
			
               $dbField[$counter] = "pgw_s1_pt_asal";             $counter++;
			$dbField[$counter] = "pgw_s1_kota";                $counter++;
			$dbField[$counter] = "pgw_s1_bidang_ilmu";         $counter++;
			$dbField[$counter] = "pgw_s1_tanggal_lulus";       $counter++;
			$dbField[$counter] = "pgw_s1_no_ijasah";           $counter++;
			$dbField[$counter] = "pgw_s1_gelar";               $counter++;
			$dbField[$counter] = "pgw_s1_ipk";                 $counter++;
			$dbField[$counter] = "pgw_s2_nama";                $counter++;
			$dbField[$counter] = "pgw_s2_pt_asal";             $counter++;
			$dbField[$counter] = "pgw_s2_kota";                $counter++;
                         
               $dbField[$counter] = "pgw_s2_bidang_ilmu";         $counter++;
			$dbField[$counter] = "pgw_s2_tanggal_lulus";       $counter++;
			$dbField[$counter] = "pgw_s2_no_ijasah";           $counter++;
			$dbField[$counter] = "pgw_s2_gelar";               $counter++;
			$dbField[$counter] = "pgw_s2_ipk";                 $counter++;
			$dbField[$counter] = "pgw_s3_nama";                $counter++;
			$dbField[$counter] = "pgw_s3_pt_asal";             $counter++;
			$dbField[$counter] = "pgw_s3_kota";                $counter++;
			$dbField[$counter] = "pgw_s3_bidang_ilmu";         $counter++;
			$dbField[$counter] = "pgw_s3_tanggal_lulus";       $counter++;
			
               $dbField[$counter] = "pgw_s3_no_ijasah";           $counter++;
			$dbField[$counter] = "pgw_s3_gelar";               $counter++;
			$dbField[$counter] = "pgw_s3_ipk";                 $counter++;
			$dbField[$counter] = "pgw_gelar_muka";             $counter++;
			$dbField[$counter] = "pgw_gelar_belakang";         $counter++;
			$dbField[$counter] = "pgw_bidang_keahlian";        $counter++;
			$dbField[$counter] = "pgw_gelar_tertinggi";        $counter++;
			$dbField[$counter] = "pgw_pendidikan_tertinggi";   $counter++;
			$dbField[$counter] = "pgw_akta_v";                 $counter++;

			$dbField[$counter] = "pgw_email";                  $counter++;
               $dbField[$counter] = "pgw_situs";                  $counter++;			


               if($isPersonalia) {
                    $dbField[$counter] = "id_dep";                 $counter++;
     			$dbField[$counter] = "pgw_status";                 $counter++;
     			$dbField[$counter] = "pgw_jenis_pegawai";                 $counter++;
                    $dbField[$counter] = "pgw_tanggal_masuk";          $counter++;			
                    $dbField[$counter] = "pgw_tanggal_keluar";         $counter++;
                    $dbField[$counter] = "pgw_alasan_keluar";          $counter++;
                    $dbField[$counter] = "pgw_jabatan_struktural";     $counter++;
                    $dbField[$counter] = "pgw_no_sk_jab_struktural";   $counter++;
                    $dbField[$counter] = "pgw_tanggal_update";	     $counter++;		
                    $dbField[$counter] = "pgw_jam_masuk";              $counter++;
                    $dbField[$counter] = "id_usr";                     $counter++;
                    
                    $dbField[$counter] = "pgw_no_sk_pangkat";          $counter++;
                    $dbField[$counter] = "pgw_tanggal_habis_sk";       $counter++;
                    $dbField[$counter] = "pgw_pangkat_diterima";       $counter++;
                    $dbField[$counter] = "id_gol";                     $counter++;
                    $dbField[$counter] = "pgw_masa_kerja_golongan";    $counter++;
                    $dbField[$counter] = "pgw_masa_kerja_diterima";    $counter++;
                    $dbField[$counter] = "pgw_tmt_pangkat";            $counter++;
                    $dbField[$counter] = "id_ptkp";                    $counter++;
                    $dbField[$counter] = "pgw_gaji_pokok";             $counter++;
     
                    $dbField[$counter] = "pgw_nip";                     $counter++;

               }

			if($_POST["pgw_agama"]=="--") $_POST["pgw_agama"]="null";
			if(!$_POST["pgw_warganegara"])$_POST["pgw_warganegara"]="WNI";
			if(!$_POST["pgw_status_nikah"])$_POST["pgw_status_nikah"]="n";
			if($_POST["pgw_status"]=="--") $_POST["pgw_status"] = 'null';
               if(!$usrId) $usrId = "null";
               
               if($_POST["pgw_jabatan_struktural"]=="--") $_POST["pgw_jabatan_struktural"] = '';
               if($_POST["pgw_warganegara"]=="WNA") $_POST["pgw_warganegara"] = $_POST["wna"];
               $jamMasuk = $_POST["pgw_jam_masuk_jam"].":".$_POST["pgw_jam_masuk_menit"].":00";
			
               $counter = 0;
			if(!$pgwId) $pgwId = $dtaccess->GetNewID("hris_pegawai","pgw_id",DB_SCHEMA_HRIS);
			$dbValue[$counter] = QuoteValue(DPE_NUMERIC,$pgwId);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_nama"]);
               $counter++;               

			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_nama_panggilan"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_tempat_lahir"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_lahir"]));
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_jenis_kelamin"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_status_nikah"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_foto"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_NUMERIC,$_POST["pgw_agama"]);
               $counter++;               
               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_warganegara"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_suku_bangsa"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_ktp_no"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_passport_no"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_golongan_darah"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_nama_bank"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_no_rekening"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_alamat_asal"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_telp_asal"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_hp_asal"]);
               $counter++;               
               
			
               $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_alamat_surat"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_telp_surat"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_alamat_surabaya"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_telp_surabaya"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_telp_hp"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_kontak_darurat"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_kontak_darurat_telp"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_kontak_darurat_hubungan"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_kontak_darurat_hp"]);						
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_sd_nama"]);
               $counter++;               
               
			
               $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_sd_kota"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_sd_tanggal_lulus"]));
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_sd_no_ijasah"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_sltp_nama"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_sltp_kota"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_sltp_tanggal_lulus"]));
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_sltp_no_ijasah"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_slta_nama"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_slta_kota"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_slta_tanggal_lulus"]));
               $counter++;               
               
			
               $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_slta_no_ijasah"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_diploma_nama"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_diploma_pt_asal"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_diploma_kota"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_diploma_bidang_ilmu"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_diploma_tanggal_lulus"]));
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_diploma_no_ijasah"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_diploma_gelar"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_diploma_ipk"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s1_nama"]);
               $counter++;               
               
			
               $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s1_pt_asal"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s1_kota"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s1_bidang_ilmu"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_s1_tanggal_lulus"]));
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s1_no_ijasah"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s1_gelar"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s1_ipk"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s2_nama"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s2_pt_asal"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s2_kota"]);
               $counter++;               
               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s2_bidang_ilmu"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_s2_tanggal_lulus"]));
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s2_no_ijasah"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s2_gelar"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s2_ipk"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s3_nama"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s3_pt_asal"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s3_kota"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s3_bidang_ilmu"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_s3_tanggal_lulus"]));
               $counter++;               
               
			
               $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s3_no_ijasah"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s3_gelar"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_s3_ipk"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_gelar_muka"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_gelar_belakang"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_bidang_keahlian"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_gelar_tertinggi"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_pendidikan_tertinggi"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_akta_v"]);
               $counter++;               
               

               $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_email"]);
               $counter++;               
               
			$dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_situs"]);			
               $counter++;               
               

               if($isPersonalia) {               
               
                    $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["id_dep"]);
                    $counter++;               

                    $dbValue[$counter] = QuoteValue(DPE_NUMERIC,$_POST["pgw_status"]);
                    $counter++;               

                    $dbValue[$counter] = QuoteValue(DPE_NUMERICKEY,$_POST["pgw_jenis_pegawai"]);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_masuk"]));			
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_keluar"]));
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_alasan_keluar"]);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_jabatan_struktural"]);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_no_sk_jab_struktural"]);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_DATE,date("Y-m-d"));
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_TIMESTAMP,$jamMasuk);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_NUMERICKEY,$usrId);
                    $counter++;               
                    
                    
                    $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_no_sk_pangkat"]);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_habis_sk"]));
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_NUMERICKEY,$_POST["pgw_pangkat_diterima"]);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_NUMERICKEY,$_POST["id_gol"]);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_NUMERIC,$_POST["pgw_masa_kerja_golongan"]);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_NUMERIC,$_POST["pgw_masa_kerja_diterima"]);
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tmt_pangkat"]));
                    $counter++;               

                    if($_POST["id_ptkp"]) $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["id_ptkp"]);
                    else  $dbValue[$counter] = "null";
                    $counter++;               
                    
                    $dbValue[$counter] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["pgw_gaji_pokok"]));
                    $counter++;               
     
                    $dbValue[$counter] = QuoteValue(DPE_CHAR,$_POST["pgw_nip"]);			
               }

			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_HRIS);
   
               if($_POST["btnSave"])
                    $dtmodel->Insert() or die("insert error");
               elseif($_POST["btnUpdate"])
                    $dtmodel->Update() or die("update error");
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               if($isPersonalia){
                    echo "<script>document.location.href='pegawai_view.php';</script>";
                    exit();   
               } else echo "Data Updated";

          }
	}

          
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $pgwId = $enc->Decode($_GET["id"]);
          }
          
		$sql = "select a.*,b.id_rol from hris_pegawai a
                    left join hris_jab_struktural b on a.pgw_jabatan_struktural = b. jab_struk_id 
                    where a.pgw_id = ".$pgwId;
		$rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
		$dataPegawai = $dtaccess->Fetch($rs_edit);
		$dtaccess->Clear($rs_edit);
		
		$_POST["pgw_nip"] = $dataPegawai["pgw_nip"]; 		
		$_POST["pgw_nama"] = $dataPegawai["pgw_nama"]; 
		$_POST["pgw_nama_panggilan"] = $dataPegawai["pgw_nama_panggilan"]; 
		$_POST["pgw_tempat_lahir"] = $dataPegawai["pgw_tempat_lahir"]; 
		$_POST["pgw_tanggal_lahir"] = format_date($dataPegawai["pgw_tanggal_lahir"]); 
		$_POST["pgw_jenis_kelamin"] = $dataPegawai["pgw_jenis_kelamin"]; 
		$_POST["pgw_status_nikah"] = $dataPegawai["pgw_status_nikah"]; 
		$_POST["pgw_agama"] = $dataPegawai["pgw_agama"];          
		$_POST["pgw_warganegara"] = $dataPegawai["pgw_warganegara"]; 
		if($_POST["pgw_warganegara"]!="WNI" && $_POST["pgw_warganegara"]!="WNI Keturunan") $_POST["wna"] = $_POST["pgw_warganegara"];
		$_POST["pgw_suku_bangsa"] = $dataPegawai["pgw_suku_bangsa"];
          
		$_POST["pgw_ktp_no"] = $dataPegawai["pgw_ktp_no"]; 
		$_POST["pgw_passport_no"] = $dataPegawai["pgw_passport_no"]; 
		$_POST["pgw_golongan_darah"] = $dataPegawai["pgw_golongan_darah"]; 
		$_POST["pgw_nama_bank"] = $dataPegawai["pgw_nama_bank"]; 
		$_POST["pgw_no_rekening"] = $dataPegawai["pgw_no_rekening"]; 
		$_POST["pgw_alamat_asal"] = $dataPegawai["pgw_alamat_asal"]; 
		$_POST["pgw_kota_asal"] = $dataPegawai["pgw_kota_asal"]; 
		$_POST["pgw_telp_asal"] = $dataPegawai["pgw_telp_asal"]; 
		$_POST["pgw_hp_asal"] = $dataPegawai["pgw_hp_asal"]; 
		$_POST["pgw_alamat_surat"] = $dataPegawai["pgw_alamat_surat"]; 
		
          $_POST["pgw_telp_surat"] = $dataPegawai["pgw_telp_surat"]; 
		$_POST["pgw_alamat_surabaya"] = $dataPegawai["pgw_alamat_surabaya"]; 
		$_POST["pgw_telp_surabaya"] = $dataPegawai["pgw_telp_surabaya"]; 
		$_POST["pgw_telp_hp"] = $dataPegawai["pgw_telp_hp"]; 
		$_POST["pgw_kontak_darurat"] = $dataPegawai["pgw_kontak_darurat"]; 
		$_POST["pgw_kontak_darurat_telp"] = $dataPegawai["pgw_kontak_darurat_telp"]; 
		$_POST["pgw_kontak_darurat_hubungan"] = $dataPegawai["pgw_kontak_darurat_hubungan"]; 
		$_POST["pgw_kontak_darurat_hp"] = $dataPegawai["pgw_kontak_darurat_hp"]; 		
          $_POST["pgw_sd_nama"] = $dataPegawai["pgw_sd_nama"]; 
		$_POST["pgw_sd_kota"] = $dataPegawai["pgw_sd_kota"]; 
		
          $_POST["pgw_sd_tanggal_lulus"] = format_date($dataPegawai["pgw_sd_tanggal_lulus"]); 
		$_POST["pgw_sd_no_ijasah"] = $dataPegawai["pgw_sd_no_ijasah"]; 
		$_POST["pgw_sltp_nama"] = $dataPegawai["pgw_sltp_nama"]; 
		$_POST["pgw_sltp_kota"] = $dataPegawai["pgw_sltp_kota"]; 
		$_POST["pgw_sltp_tanggal_lulus"] = format_date($dataPegawai["pgw_sltp_tanggal_lulus"]); 
		$_POST["pgw_sltp_no_ijasah"] = $dataPegawai["pgw_sltp_no_ijasah"]; 
		$_POST["pgw_slta_nama"] = $dataPegawai["pgw_slta_nama"]; 
		$_POST["pgw_slta_kota"] = $dataPegawai["pgw_slta_kota"]; 
		$_POST["pgw_slta_tanggal_lulus"] = format_date($dataPegawai["pgw_slta_tanggal_lulus"]); 
		$_POST["pgw_slta_no_ijasah"] = $dataPegawai["pgw_slta_no_ijasah"]; 
		
          $_POST["pgw_diploma_nama"] = $dataPegawai["pgw_diploma_nama"]; 
		$_POST["pgw_diploma_pt_asal"] = $dataPegawai["pgw_diploma_pt_asal"]; 
		$_POST["pgw_diploma_kota"] = $dataPegawai["pgw_diploma_kota"]; 
		$_POST["pgw_diploma_bidang_ilmu"] = $dataPegawai["pgw_diploma_bidang_ilmu"]; 
		$_POST["pgw_diploma_tanggal_lulus"] = format_date($dataPegawai["pgw_diploma_tanggal_lulus"]); 
		$_POST["pgw_diploma_no_ijasah"] = $dataPegawai["pgw_diploma_no_ijasah"]; 
		$_POST["pgw_diploma_gelar"] = $dataPegawai["pgw_diploma_gelar"]; 
		$_POST["pgw_diploma_ipk"] = $dataPegawai["pgw_diploma_ipk"]; 
		$_POST["pgw_s1_nama"] = $dataPegawai["pgw_s1_nama"]; 
		$_POST["pgw_s1_pt_asal"] = $dataPegawai["pgw_s1_pt_asal"]; 
		
          $_POST["pgw_s1_kota"] = $dataPegawai["pgw_s1_kota"]; 
		$_POST["pgw_s1_bidang_ilmu"] = $dataPegawai["pgw_s1_bidang_ilmu"]; 
		$_POST["pgw_s1_tanggal_lulus"] = format_date($dataPegawai["pgw_s1_tanggal_lulus"]); 
		$_POST["pgw_s1_no_ijasah"] = $dataPegawai["pgw_s1_no_ijasah"]; 
		$_POST["pgw_s1_gelar"] = $dataPegawai["pgw_s1_gelar"]; 
		$_POST["pgw_s1_ipk"] = $dataPegawai["pgw_s1_ipk"]; 
		$_POST["pgw_s2_nama"] = $dataPegawai["pgw_s2_nama"]; 
		$_POST["pgw_s2_pt_asal"] = $dataPegawai["pgw_s2_pt_asal"]; 
		$_POST["pgw_s2_kota"] = $dataPegawai["pgw_s2_kota"]; 
		$_POST["pgw_s2_bidang_ilmu"] = $dataPegawai["pgw_s2_bidang_ilmu"]; 
		
          $_POST["pgw_s2_tanggal_lulus"] = format_date($dataPegawai["pgw_s2_tanggal_lulus"]); 
		$_POST["pgw_s2_no_ijasah"] = $dataPegawai["pgw_s2_no_ijasah"]; 
		$_POST["pgw_s2_gelar"] = $dataPegawai["pgw_s2_gelar"]; 
		$_POST["pgw_s2_ipk"] = $dataPegawai["pgw_s2_ipk"]; 
		$_POST["pgw_s3_nama"] = $dataPegawai["pgw_s3_nama"]; 
		$_POST["pgw_s3_pt_asal"] = $dataPegawai["pgw_s3_pt_asal"]; 
		$_POST["pgw_s3_kota"] = $dataPegawai["pgw_s3_kota"]; 
		$_POST["pgw_s3_bidang_ilmu"] = $dataPegawai["pgw_s3_bidang_ilmu"]; 
		$_POST["pgw_s3_tanggal_lulus"] = format_date($dataPegawai["pgw_s3_tanggal_lulus"]); 
		$_POST["pgw_s3_no_ijasah"] = $dataPegawai["pgw_s3_no_ijasah"]; 
		
          $_POST["pgw_s3_gelar"] = $dataPegawai["pgw_s3_gelar"]; 
		$_POST["pgw_s3_ipk"] = $dataPegawai["pgw_s3_ipk"]; 
		$_POST["pgw_gelar_muka"] = $dataPegawai["pgw_gelar_muka"]; 
		$_POST["pgw_gelar_belakang"] = $dataPegawai["pgw_gelar_belakang"]; 
		$_POST["pgw_bidang_keahlian"] = $dataPegawai["pgw_bidang_keahlian"]; 
		$_POST["pgw_gelar_tertinggi"] = $dataPegawai["pgw_gelar_tertinggi"]; 
		$_POST["pgw_pendidikan_tertinggi"] = $dataPegawai["pgw_pendidikan_tertinggi"]; 
		$_POST["pgw_akta_v"] = $dataPegawai["pgw_akta_v"]; 
		$_POST["pgw_status"] = $dataPegawai["pgw_status"];          
		$_POST["pgw_jenis_pegawai"] = $dataPegawai["pgw_jenis_pegawai"];          
		
		$_POST["pgw_tanggal_masuk"] = format_date($dataPegawai["pgw_tanggal_masuk"]); 		
		$_POST["pgw_tanggal_keluar"] = format_date($dataPegawai["pgw_tanggal_keluar"]); 
		$_POST["pgw_alasan_keluar"] = $dataPegawai["pgw_alasan_keluar"];
		$_POST["pgw_jabatan_struktural"] = $dataPegawai["pgw_jabatan_struktural"]; 
		$_POST["pgw_no_sk_jab_struktural"] = $dataPegawai["pgw_no_sk_jab_struktural"]; 
		$_POST["pgw_status_kerja"] = $dataPegawai["pgw_status_kerja"]; 
		$_POST["id_dep"] = $dataPegawai["id_dep"]; 
		$_POST["pgw_foto"] = $dataPegawai["pgw_foto"]; 
		$_POST["pgw_email"] = $dataPegawai["pgw_email"]; 
		$_POST["pgw_situs"] = $dataPegawai["pgw_situs"];

		$_POST["pgw_no_sk_pangkat"] = $dataPegawai["pgw_no_sk_pangkat"];
		$_POST["pgw_tanggal_habis_sk"] = format_date($dataPegawai["pgw_tanggal_habis_sk"]);
		$_POST["pgw_pangkat_diterima"] = $dataPegawai["pgw_pangkat_diterima"];
		$_POST["id_gol"] = $dataPegawai["id_gol"];
		$_POST["pgw_masa_kerja_golongan"] = $dataPegawai["pgw_masa_kerja_golongan"];
		$_POST["pgw_masa_kerja_diterima"] = $dataPegawai["pgw_masa_kerja_diterima"];
		$_POST["pgw_tmt_pangkat"] = format_date($dataPegawai["pgw_tmt_pangkat"]);
		$_POST["id_ptkp"] = $dataPegawai["id_ptkp"];
		$_POST["pgw_gaji_pokok"] = currency_format($dataPegawai["pgw_gaji_pokok"]);
		
          $jamMasuk = explode(":",$dataPegawai["pgw_jam_masuk"]);
		$_POST["pgw_jam_masuk_jam"] = $jamMasuk[0];
		$_POST["pgw_jam_masuk_menit"] = $jamMasuk[1];
          $_POST["id_rol"] = $dataPegawai["id_rol"];



          
          if($dataPegawai["id_usr"]){
               $sql = "select * from global_auth_user
                         where usr_id = ".$dataPegawai["id_usr"];
               $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
               $dataUser = $dtaccess->Fetch($rs);
               $dtaccess->Clear($rs);
               
               $_POST["usr_loginname"] = $dataUser["usr_loginname"];
               $_POST["usr_status"] = $dataUser["usr_status"];
               $_POST["usr_id"] = $dataUser["usr_id"];
          }
          
	}
     
     
     if ($_POST["btnDelete"]) {
          $pgwId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($pgwId);$i<$n;$i++) {
               $sql = "select id_usr from hris_pegawai where pgw_id = ".$pgwId[$i];
               $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
               $usrDel = $dtaccess->Fetch($rs);               
               
               $sql = "delete from hris_pegawai where pgw_id = ".$pgwId[$i];
               $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
               
               if($usrDel["id_usr"]){
                    $sql = "update global_auth_user set usr_status = 'n'
                              where usr_id = ".$usrDel["id_usr"];
                    $dtaccess->Execute($sql);
               }
          }
          
          echo "<script>document.location.href='pegawai_view.php';</script>";
          exit();     
     }
	

	// --- cari status pegawai ---
     $sql = "select * from hris_status_pegawai order by status_peg_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataStatus = $dtaccess->FetchAll($rs);
	$dtaccess->Clear($rs);

	// --- cari jenis pegawai ---
     // jenis pegawai: dokter, perawat, refraksionis, administrasi
     $sql = "select * from hris_jenis_pegawai order by jenis_peg_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataJenisPegawai = $dtaccess->FetchAll($rs);
	$dtaccess->Clear($rs);


     
     // --- cari jab struktural ---
     $sql = "select * from hris_jab_struktural order by jab_struk_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataJabatan = $dtaccess->FetchAll($rs);
	$dtaccess->Clear($rs);
	
     // --- cari unit_kerja ---
     $sql = "select * from global_departemen order by dep_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataDep = $dtaccess->FetchAll($rs);
     $dtaccess->Clear($rs);
     
     // --- cari agama ---
     $sql = "select * from hris_agama order by agm_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataAgama = $dtaccess->FetchAll($rs);
     $dtaccess->Clear($rs);
     
     $sql = "select * from hris_golongan order by gol_gol";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $row_golongan = $dtaccess->FetchAll($rs);

// -- cari pendapatan Tidak Kena Pajak
     $sql = "select * from hris_pajak_ptkp order by ptkp_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataPtkp = $dtaccess->FetchAll($rs);


     $lokasi = "../../../../images/foto_pgw";
	if($_POST["pgw_foto"]) $fotoName = $lokasi."/".$_POST["pgw_foto"];
     else $fotoName = $lokasi."/default.jpg";     
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>

<script language="Javascript">

var dataRol = Array();

<?php for($i=0,$n=count($dataJabatan);$i<$n;$i++){ ?>
    dataRol['<?php echo ($dataJabatan[$i]["jab_struk_id"]);?>'] = '<?php echo $dataJabatan[$i]["id_rol"];?>'
<?php } ?>

function GantiRol(frm,id)
{
     if(dataRol[id]) frm.id_rol.value = dataRol[id];
     else frm.id_rol.value = '';
}

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=500,height=150,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=500,height=150,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}

function WargaNegara(frm, elm)
{
     if(elm.checked){
          if (document.getElementById("wn3").checked)
          {
               frm.wna.disabled = false;
               frm.wna.style.backgroundColor = '#FFFFFF';
               frm.wna.focus();
          }
          if ((document.getElementById("wn1").checked) || (document.getElementById("wn2").checked))
          {
               frm.wna.disabled = true;
               frm.wna.style.backgroundColor = '#e2dede';
          }
     } 
}

function GantiPassword(frm, elm)
{
    if(elm.checked){
        frm.usr_password.disabled = false;
        frm.usr_password2.disabled = false;
        frm.usr_password2.style.backgroundColor = '#ffffff';
        frm.usr_password.style.backgroundColor = '#ffffff';
        frm.usr_password.focus();
    } else {
        frm.usr_password.disabled = true;
        frm.usr_password2.disabled = true;
        frm.usr_password2.style.backgroundColor = '#e2dede';
        frm.usr_password.style.backgroundColor = '#e2dede';
    }
}

function jenisPegawai(nilai)
{
	if(nilai=="1"){
		document.getElementById("pgw_jab_akademik").disabled = false;
		document.getElementById("pgw_no_sk_jab_akademik").disabled = false;
		document.getElementById("pgw_status_kerja").disabled = false;		
		document.getElementById("pgw_jab_akademik").style.backgroundColor = '#FFFFFF';
		document.getElementById("pgw_no_sk_jab_akademik").style.backgroundColor = '#FFFFFF';
		document.getElementById("pgw_status_kerja").style.backgroundColor = '#FFFFFF';		
        
	} else {
          
		document.getElementById("pgw_jab_akademik").disabled = true;
		document.getElementById("pgw_no_sk_jab_akademik").disabled = true;
		document.getElementById("pgw_status_kerja").disabled = true;		
		document.getElementById("pgw_jab_akademik").style.backgroundColor = '#e2dede';
		document.getElementById("pgw_no_sk_jab_akademik").style.backgroundColor = '#e2dede';
		document.getElementById("pgw_status_kerja").style.backgroundColor = '#e2dede';		
	}
}
</script>

<style type="text/css">
.bDisable{
	color: #0F2F13;
	border: 1px solid #c2c6d3;
	background-color: #e2dede;
}
</style>

</head>
<body>
     
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>?#msg">
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">INPUT DATA PEGAWAI</td>
	</tr>
</table> 
<table width="100%" border="1" cellpadding="4" cellspacing="1">
	<tr>
          <td colspan="3" align="center" class="subHeader">DATA PRIBADI</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">Nama Lengkap</td>
		<td width= "50%" align="left">
               <input  type="text" name="pgw_nama" size="30" maxlength="50" value="<?php echo htmlspecialchars($_POST["pgw_nama"]);?>" onKeyDown="return tabOnEnter(this, event);"/>
		</td>
          <td rowspan="6">
			<img hspace="2" width="120" height="150" name="img_foto" src="<?php echo $fotoName;?>"  border="1" onDblClick="BukaWindow('pgw_pic.php?orifoto='+ document.frmEdit.pgw_foto.value + '&nama='+document.frmEdit.pgw_nip.value,'UploadFoto')">
			<input type="hidden" name="pgw_foto" value="<?php echo $_POST["pgw_foto"];?>">
		</td>
	</tr>
	<tr>
		<td align="left" class="tablecontent">Nama panggilan</td>
		<td align="left">
               <input type="text" name="pgw_nama_panggilan" size="25" maxlength="50" value="<?php echo $_POST["pgw_nama_panggilan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
	</tr>
	<tr>
		<td width= "30%"class="tablecontent">Tempat Lahir / Tanggal Lahir <?if(readbit($err_code,1)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "70%">
               <input type="text" name="pgw_tempat_lahir" size="15" maxlength="20" value="<?php echo $_POST["pgw_tempat_lahir"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/> / 
               <input type="text" id="pgw_tanggal_lahir" name="pgw_tanggal_lahir" size="15" maxlength="10" value="<?php echo $_POST["pgw_tanggal_lahir"];?>" onKeyDown="return tabOnEnter(this, event);"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lahir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
			(dd-mm-yyy)
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Jenis Kelamin</td>
		<td>
			<select name="pgw_jenis_kelamin" onKeyDown="return tabOnEnter(this, event);">
				<option value="L" <?php if($_POST["pgw_jenis_kelamin"]=="L")echo "selected";?>>Laki-laki</option>
				<option value="P" <?php if($_POST["pgw_jenis_kelamin"]=="P")echo "selected";?>>Perempuan</option>
			</select>
          </td>
	</tr>
	<tr>
		<td class="tablecontent">Status Perkawinan</td>
		<td>
			<input type="radio" name="pgw_status_nikah" id="sty" value="y" <?php if($_POST["pgw_status_nikah"]=="y") echo "checked";?> onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="sty">Menikah</label>&nbsp;
			<input type="radio" name="pgw_status_nikah" id="stn" value="n" <?php if($_POST["pgw_status_nikah"]=="n") echo "checked";?> onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="stn">Belum Menikah</label>
			<input type="radio" name="pgw_status_nikah" id="stj" value="j" <?php if($_POST["pgw_status_nikah"]=="j") echo "checked";?> onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="stj">Janda/Duda</label>
			<input type="radio" name="pgw_status_nikah" id="std" value="t" <?php if($_POST["pgw_status_nikah"]=="t") echo "checked";?> onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="std">Tunangan</label>
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Agama</td>
          <td >
			<select name="pgw_agama" onKeyDown="return tabOnEnter(this, event);">
                    <option value="--" <?php if($_POST["pgw_agama"]=="--") echo "selected"; ?>>[ Pilih Agama ]</option>
				<?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataAgama[$i]["agm_id"];?>" <?php if($dataAgama[$i]["agm_id"]==$_POST["pgw_agama"]) echo "selected"; ?>><?php echo $dataAgama[$i]["agm_nama"];?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Kewarganegaraan</td>
		<td colspan="2">
			<input type="radio" name="pgw_warganegara" id="wn1" value="WNI" 
			<?php if($_POST["pgw_warganegara"]=="WNI" || !$_POST["pgw_warganegara"]) echo "checked";?> 
			onClick="WargaNegara(this.form,this);" 
			onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="wn1" >WNI</label>&nbsp;

			<input type="radio" name="pgw_warganegara" id="wn3" value="WNA" <?php if($_POST["pgw_warganegara"] && $_POST["pgw_warganegara"]!="WNI" && $_POST["pgw_warganegara"]!="WNI Keturunan") echo "checked";?> onClick="WargaNegara(this.form,this);" onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="wn3">WNA</label> &nbsp;
			<input type="text" name="wna" value="<?php echo $_POST["wna"];?>" 
			class="<?php if($_POST["pgw_warganegara"] == "WNI" || !$_POST["pgw_warganegara"]) echo "bDisable";else echo "inputField";?>" 
			size="20" maxlength="100" onKeyDown="return tabOnEnter_select_with_button(this, event);" 
			<?php if($_POST["pgw_warganegara"] == "WNI" || $_POST["pgw_warganegara"] == "WNI Keturunan") echo "disabled";?>0>
		</td>
     </tr>
	<tr>
          <td class="tablecontent" align="left">Suku Bangsa  </td>
          <td colspan="2">
               <input type="text" name="pgw_suku_bangsa" size="15" maxlength="20" value="<?php echo $_POST["pgw_suku_bangsa"];?>"onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
	</tr>
	<tr>
		<td class="tablecontent" align="left">No. KTP </td>
		<td colspan="2">
               <input type="text" name="pgw_ktp_no" size="35" maxlength="50" value="<?php echo $_POST["pgw_ktp_no"];?>"onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
	</tr>
	<tr>
		<td class="tablecontent" align="left">No. Passport </td>
		<td colspan="2">
               <input type="text" name="pgw_passport_no" size="35" maxlength="50" value="<?php echo $_POST["pgw_passport_no"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
	</tr>
	<tr>
		<td class="tablecontent" align="left">Golongan Darah</td>
		<td colspan="2">
			<select name="pgw_golongan_darah" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                    <option value="A" <?php if("A"==$_POST["pgw_golongan_darah"]) echo "selected"; ?>>A</option>
                    <option value="B" <?php if("B"==$_POST["pgw_golongan_darah"]) echo "selected"; ?>>B</option>
                    <option value="AB" <?php if("AB"==$_POST["pgw_golongan_darah"]) echo "selected"; ?>>AB</option>
                    <option value="O" <?php if("O"==$_POST["pgw_golongan_darah"]) echo "selected"; ?>>O</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="tablecontent" align="left">Nama Bank</td>
		<td colspan="2">
               <input type="text" name="pgw_nama_bank" size="35" maxlength="50" value="<?php echo $_POST["pgw_nama_bank"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
	</tr>
	<tr>
		<td class="tablecontent" align="left">Nomor Rekening</td>
		<td colspan="2">
               <input type="text" name="pgw_no_rekening" size="35" maxlength="50" value="<?php echo $_POST["pgw_no_rekening"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
	</tr>
	<tr>
		<td class="tablecontent" align="left">Alamat Email</td>
		<td colspan="2">
               <input type="text" name="pgw_email" size="35" maxlength="50" value="<?php echo $_POST["pgw_email"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
	</tr>
	<tr>
		<td class="tablecontent" align="left">Alamat Situs Pribadi</td>
		<td colspan="2">
               <input type="text" name="pgw_situs" size="35" maxlength="50" value="<?php echo $_POST["pgw_situs"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
	</tr>

<!--dari alamat rumah lamaran-->
	<tr>
		<td width= "30%" class="tablecontent">Alamat Asal</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%"  >
				<tr>
					<td colspan="2">
						<textarea name="pgw_alamat_asal" id="pgw_alamat_asal" rows="3" cols="65"><?php echo $_POST["pgw_alamat_asal"];?></textarea>
					</td>
				</tr>
				<tr>
					<td width="10%" class="tablecontent-odd">Telepon</td>
                         <td>
                              <input type="text" name="pgw_telp_asal" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_asal"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="10%" class="tablecontent-odd">Hp</td>
                         <td>
                              <input type="text" name="pgw_hp_asal" size="15" maxlength="15" value="<?php echo $_POST["pgw_hp_asal"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Alamat Surat</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%"  >
				<tr>
					<td colspan="2">
						<textarea name="pgw_alamat_surat" id="pgw_alamat_surat" rows="3" cols="65"><?php echo $_POST["pgw_alamat_surat"];?></textarea>
					</td>
				</tr>
				<tr>
					<td width="10%" class="tablecontent-odd">Telepon</td>
                         <td>
                              <input type="text" name="pgw_telp_surat" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_surat"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>						     
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Alamat Surabaya</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%">
				<tr>
					<td colspan="2">
						<textarea name="pgw_alamat_surabaya" id="pgw_alamat_surabaya" rows="3" cols="65"><?php echo $_POST["pgw_alamat_surabaya"];?></textarea>
					</td>
				</tr>
				<tr>
					<td width="10%" class="tablecontent-odd">Telepon</td>
                         <td>
                              <input type="text" name="pgw_telp_surabaya" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_surabaya"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="10%" class="tablecontent-odd">Hp</td>
                         <td>
                              <input type="text" name="pgw_telp_hp" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_hp"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Kontak Darurat</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%">
				<tr>
					<td width="15%" class="tablecontent-odd">Nama Kontak</td>
					<td>
                              <input type="text" name="pgw_kontak_darurat" size="35" maxlength="50" value="<?php echo $_POST["pgw_kontak_darurat"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="15%" class="tablecontent-odd">Hubungan</td>
					<td>
                              <input type="text" name="pgw_kontak_darurat_hubungan" size="35" maxlength="50" value="<?php echo $_POST["pgw_kontak_darurat_hubungan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="15%" class="tablecontent-odd">Telepon</td>
                         <td>
                              <input type="text" name="pgw_kontak_darurat_telp" size="15" maxlength="15" value="<?php echo $_POST["pgw_kontak_darurat_telp"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="15%" class="tablecontent-odd">Hp</td>
                         <td>
                              <input type="text" name="pgw_kontak_darurat_hp" size="15" maxlength="15" value="<?php echo $_POST["pgw_kontak_darurat_hp"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>     
	<tr>
          <td colspan="3" align="center" class="subHeader">PENDIDIKAN FORMAL</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Sekolah Dasar</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%"  >
				<tr>
					<td width="20%" class="tablecontent-odd">Nama</td>
                         <td>:
                              <input type="text" name="pgw_sd_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_sd_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Kota</td>
                         <td>:
                              <input type="text" name="pgw_sd_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_sd_kota"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Tanggal Lulus <?if(readbit($err_code,2)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
					<td>: 
						<input type="text" id="pgw_sd_tanggal_lulus" name="pgw_sd_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_sd_tanggal_lulus"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_sd" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Nomor Ijasah</td>
                         <td>:
                              <input type="text" name="pgw_sd_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_sd_no_ijasah"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Sekolah Lanjut Tingkat Pertama</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%"  >
				<tr>
					<td width="20%" class="tablecontent-odd">Nama</td>
                         <td>:
                              <input type="text" name="pgw_sltp_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_sltp_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Kota</td>
                         <td>:
                              <input type="text" name="pgw_sltp_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_sltp_kota"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Tanggal Lulus <?if(readbit($err_code,3)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
					<td>: 
						<input type="text" id="pgw_sltp_tanggal_lulus" name="pgw_sltp_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_sltp_tanggal_lulus"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_sltp" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Nomor Ijasah</td>
                         <td>:
                              <input type="text" name="pgw_sltp_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_sltp_no_ijasah"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Sekolah Lanjut Tingkat Atas</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%"  >
				<tr>
					<td width="20%" class="tablecontent-odd">Nama</td>
                         <td>:
                              <input type="text" name="pgw_slta_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_slta_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Kota</td>
                         <td>:
                              <input type="text" name="pgw_slta_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_slta_kota"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Tanggal Lulus <?if(readbit($err_code,4)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
					<td>: 
						<input type="text" id="pgw_slta_tanggal_lulus" name="pgw_slta_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_slta_tanggal_lulus"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_slta" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Nomor Ijasah</td>
                         <td>:
                              <input type="text" name="pgw_slta_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_slta_no_ijasah"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Program Pendidikan Diploma</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%">
				<tr>
					<td width="20%" class="tablecontent-odd">Jurusan</td>
                         <td>:
                              <input type="text" name="pgw_diploma_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_diploma_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Perguruan Tinggi</td>
                         <td>:
                              <input type="text" name="pgw_diploma_pt_asal" size="35" maxlength="50" value="<?php echo $_POST["pgw_diploma_pt_asal"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Kota</td>
                         <td>:
                              <input type="text" name="pgw_diploma_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_diploma_kota"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Bidang Ilmu</td>
                         <td>:
                              <input type="text" name="pgw_diploma_bidang_ilmu" size="35" maxlength="50" value="<?php echo $_POST["pgw_diploma_bidang_ilmu"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Tanggal Lulus <?if(readbit($err_code,5)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
					<td>: 
						<input type="text" id="pgw_diploma_tanggal_lulus" name="pgw_diploma_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_diploma_tanggal_lulus"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_diploma" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Nomor Ijasah</td>
                         <td>:
                              <input type="text" name="pgw_diploma_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_diploma_no_ijasah"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Gelar</td>
                         <td>:
                              <input type="text" name="pgw_diploma_gelar" size="15" maxlength="15" value="<?php echo $_POST["pgw_diploma_gelar"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">IPK</td>
                         <td>:
                              <input type="text" name="pgw_diploma_ipk" size="15" maxlength="15" value="<?php echo $_POST["pgw_diploma_ipk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Program Pendidikan Strata-1</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%"  >
				<tr>
					<td width="20%" class="tablecontent-odd">Jurusan</td>
                         <td>:
                              <input type="text" name="pgw_s1_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_s1_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Perguruan Tinggi</td>
                         <td>:
                              <input type="text" name="pgw_s1_pt_asal" size="35" maxlength="50" value="<?php echo $_POST["pgw_s1_pt_asal"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Kota</td>
                         <td>:
                              <input type="text" name="pgw_s1_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_s1_kota"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Bidang Ilmu</td>
                         <td>:
                              <input type="text" name="pgw_s1_bidang_ilmu" size="35" maxlength="50" value="<?php echo $_POST["pgw_s1_bidang_ilmu"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Tanggal Lulus <?if(readbit($err_code,6)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
					<td class="tablecontent-odd">: 
						<input type="text" id="pgw_s1_tanggal_lulus" name="pgw_s1_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_s1_tanggal_lulus"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_s1" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Nomor Ijasah</td>
                         <td>:
                              <input type="text" name="pgw_s1_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_s1_no_ijasah"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Gelar</td>
                         <td>:
                              <input type="text" name="pgw_s1_gelar" size="15" maxlength="15" value="<?php echo $_POST["pgw_s1_gelar"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">IPK</td>
                         <td>:
                              <input type="text" name="pgw_s1_ipk" size="15" maxlength="15" value="<?php echo $_POST["pgw_s1_ipk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Program Pendidikan Strata-2</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%"  >
				<tr>
					<td width="20%" class="tablecontent-odd">Jurusan</td>
                         <td>:
                              <input type="text" name="pgw_s2_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_s2_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Perguruan Tinggi</td>
                         <td>:
                              <input type="text" name="pgw_s2_pt_asal" size="35" maxlength="50" value="<?php echo $_POST["pgw_s2_pt_asal"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Kota</td>
                         <td>:
                              <input type="text" name="pgw_s2_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_s2_kota"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Bidang Ilmu</td>
                         <td>:
                              <input type="text" name="pgw_s2_bidang_ilmu" size="35" maxlength="50" value="<?php echo $_POST["pgw_s2_bidang_ilmu"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Tanggal Lulus <?if(readbit($err_code,7)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
					<td>: 
						<input type="text" id="pgw_s2_tanggal_lulus" name="pgw_s2_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_s2_tanggal_lulus"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_s2" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Nomor Ijasah</td>
                         <td>:
                              <input type="text" name="pgw_s2_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_s2_no_ijasah"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Gelar</td>
                         <td>:
                              <input type="text" name="pgw_s2_gelar" size="15" maxlength="15" value="<?php echo $_POST["pgw_s2_gelar"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">IPK</td>
                         <td>:
                              <input type="text" name="pgw_s2_ipk" size="15" maxlength="15" value="<?php echo $_POST["pgw_s2_ipk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Program Pendidikan Strata-3</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%">
				<tr>
					<td width="20%" class="tablecontent-odd">Jurusan</td>
                         <td>:
                              <input type="text" name="pgw_s3_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_s3_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%"class="tablecontent-odd">Perguruan Tinggi</td>
                         <td>:
                              <input type="text" name="pgw_s3_pt_asal" size="35" maxlength="50" value="<?php echo $_POST["pgw_s3_pt_asal"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Kota</td>
                         <td>:
                              <input type="text" name="pgw_s3_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_s3_kota"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Bidang Ilmu</td>
                         <td>:
                              <input type="text" name="pgw_s3_bidang_ilmu" size="35" maxlength="50" value="<?php echo $_POST["pgw_s3_bidang_ilmu"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Tanggal Lulus <?if(readbit($err_code,8)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
					<td>: 
						<input type="text" id="pgw_s3_tanggal_lulus" name="pgw_s3_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_s3_tanggal_lulus"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_s3" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Nomor Ijasah</td>
                         <td>:
                              <input type="text" name="pgw_s3_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_s3_no_ijasah"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Gelar</td>
                         <td>:
                              <input type="text" name="pgw_s3_gelar" size="15" maxlength="15" value="<?php echo $_POST["pgw_s3_gelar"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">IPK</td>
                         <td>:
                              <input type="text" name="pgw_s3_ipk" size="15" maxlength="15" value="<?php echo $_POST["pgw_s3_ipk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent">Sebutan Dan Gelar</td>
		<td colspan="2">
			<table border=1 cellpadding=1 cellspacing=0 width="100%">
				<tr>
					<td width="20%" class="tablecontent-odd">Gelar Muka</td>
                         <td>:
                              <input type="text" name="pgw_gelar_muka" size="35" maxlength="50" value="<?php echo $_POST["pgw_gelar_muka"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Gelar Belakang</td>
                         <td>:
                              <input type="text" name="pgw_gelar_belakang" size="35" maxlength="50" value="<?php echo $_POST["pgw_gelar_belakang"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Bidang Keahlian</td>
                         <td>:
                              <input type="text" name="pgw_bidang_keahlian" size="15" maxlength="50" value="<?php echo $_POST["pgw_bidang_keahlian"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Gelar Tertinggi</td>
                         <td>:
                              <input type="text" name="pgw_gelar_tertinggi" size="35" maxlength="50" value="<?php echo $_POST["pgw_gelar_tertinggi"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Pend. Tertinggi</td>
                         <td>:
                              <input type="text" name="pgw_pendidikan_tertinggi" size="35" maxlength="50" value="<?php echo $_POST["pgw_pendidikan_tertinggi"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Akta V</td>
                         <td>:
                              <input type="text" name="pgw_akta_v" size="15" maxlength="15" value="<?php echo $_POST["pgw_akta_v"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>	
     <?php if($isPersonalia) { ?>
	<tr>
		<td colspan="3" align="center" class="subHeader">PEKERJAAN<BR>(diisi oleh personalia)</td>
	</tr>
     <tr>
		<td width= "30%" align="left" class="tablecontent">Nomor Induk<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "50%" colspan="2" >
               <input  type="text" name="pgw_nip" size="23" maxlength="22" value="<?php echo $_POST["pgw_nip"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
	</tr>	
	<tr>
		<td width= "30%" align="left" class="tablecontent">Unit Kerja<?php if(readbit($err_code,13)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "50%" align="left" colspan="2">
			<select name="id_dep" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
				<option value="--"<?php if ($_POST["id_dep"]=="--") echo"selected"?>>[ Pilih Unit Kerja ]</option>
				<?php for($i=0,$n=count($dataDep);$i<$n;$i++){
					unset($spacer); 
					$length = (strlen($dataDep[$i]["dep_id"])/TREE_LENGTH)-1; 
					for($j=0;$j<$length;$j++) $spacer .= TREE_DELIMITER;?>
					<option value="<?php echo $dataDep[$i]["dep_id"];?>"<?php if ($_POST["id_dep"]==$dataDep[$i]["dep_id"]) echo"selected"?>><?php echo $spacer." ".$dataDep[$i]["dep_nama"];?>&nbsp;</option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent">Status Pegawai</td>
		<td width= "50%" align="left" colspan="2">
			<select name="pgw_status"  onKeyDown="return tabOnEnter_select_with_button(this, event);">
				<option value="--"<?php if ($_POST["pgw_status"]=="--") echo"selected"?>>[ Pilih Status ]</option>
				<?php for($i=0,$n=count($dataStatus);$i<$n;$i++){ ?>
					<option value="<?php echo $dataStatus[$i]["status_peg_id"];?>"<?php if ($_POST["pgw_status"]==$dataStatus[$i]["status_peg_id"]) echo"selected"?>><?php echo $dataStatus[$i]["status_peg_nama"];?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent">Jenis Pegawai</td>
		<td width= "50%" align="left" colspan="2">
			<select name="pgw_jenis_pegawai"  onKeyDown="return tabOnEnter_select_with_button(this, event);">
				<option value="--"<?php if ($_POST["pgw_jenis_pegawai"]=="--") echo"selected"?>>[ Pilih Status ]</option>
				<?php for($i=0,$n=count($dataJenisPegawai);$i<$n;$i++){ ?>
					<option value="<?php echo $dataJenisPegawai[$i]["jenis_peg_id"];?>"<?php if ($_POST["pgw_jenis_pegawai"]==$dataJenisPegawai[$i]["jenis_peg_id"]) echo"selected"?>><?php echo $dataJenisPegawai[$i]["jenis_peg_nama"];?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent">Pangkat Diterima</td>
		<td width= "50%" align="left" colspan="2">
			<select name="pgw_pangkat_diterima"  onKeyDown="return tabOnEnter_select_with_button(this, event);">
				<option value="0"<?php if ($_POST["pgw_pangkat_diterima"]=="0") echo"selected"?>>- Pilih Golongan / Pangkat</option>
				<?php for($i=0,$n=count($row_golongan);$i<$n;$i++){ ?>
					<option value="<?php echo $row_golongan[$i]["gol_id"];?>"<?php if ($_POST["pgw_pangkat_diterima"]==$row_golongan[$i]["gol_id"]) echo"selected"?>><?php echo $row_golongan[$i]["gol_pangkat"];?> / <?php echo $row_golongan[$i]["gol_gol"];?> </option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent">Masa Kerja Diterima</td>
		<td width= "50%" align="left" colspan="2">
               <input type="text" name="pgw_masa_kerja_diterima" size="5" maxlength="2" value="<?php echo $_POST["pgw_masa_kerja_diterima"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent">Pangkat Sekarang</td>
		<td width= "50%" align="left" colspan="2">
			<select name="id_gol" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
				<option value="0"<?php if ($_POST["id_gol"]=="0") echo"selected"?>>- Pilih Golongan / Pangkat</option>
				<?php for($i=0,$n=count($row_golongan);$i<$n;$i++){ ?>
					<option value="<?php echo $row_golongan[$i]["gol_id"];?>"<?php if ($_POST["id_gol"]==$row_golongan[$i]["gol_id"]) echo"selected"?>><?php echo $row_golongan[$i]["gol_pangkat"];?> / <?php echo $row_golongan[$i]["gol_gol"];?> </option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent">Masa Kerja Golongan</td>
		<td width= "50%" align="left" colspan="2">
               <input type="text" name="pgw_masa_kerja_golongan" size="5" maxlength="2" value="<?php echo $_POST["pgw_masa_kerja_golongan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
		</td>
	</tr>
<!--
	<tr >
		<td width= "30%" align="left" class="tablecontent">Jenis Pegawai</td>
		<td width= "50%" align="left" colspan="2">
			<select name="pgw_jenis_pegawai" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="jenisPegawai(this.options[this.selectedIndex].value);" >
				<option value="0" <?php if ($_POST["pgw_jenis_pegawai"]=="0") echo"selected";?>>- Pilih Jenis Pegawai</option>
				<?php for($i=0,$n=count($rowJenisPegawai);$i<$n;$i++){ ?>
					<option value="<?php echo $rowJenisPegawai[$i]["pos_jenis_id"];?>"<?php if ($_POST["pgw_jenis_pegawai"]==$rowJenisPegawai[$i]["pos_jenis_id"]) echo"selected";?> ><?php echo $rowJenisPegawai[$i]["pos_jenis_nama"];?></option>
				<?php } ?>
			</select>
		</td>
	</tr>-->
	<tr>
		<td width= "30%" align="left" class="tablecontent">Tanggal Masuk <?if(readbit($err_code,9)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "50%" align="left" colspan="2">
			<input type="text" id="pgw_tanggal_masuk" name="pgw_tanggal_masuk" size="15" maxlength="15" value="<?php echo $_POST["pgw_tanggal_masuk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_masuk" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />(dd-mm-yyyy)
		</td>
	</tr>
	<!--<tr>
		<td width= "30%" align="left" class="tablecontent">Nomor SK Angkat</td>
		<td width= "50%" align="left" colspan="2">
			<input type="text" name="pgw_no_sk_pangkat" size="20" maxlength="50" value="<?php echo $_POST["pgw_no_sk_pangkat"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">Tanggal SK Angkat <?if(readbit($err_code,16)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "50%" align="left" colspan="2">
			<input type="text" id="pgw_tanggal_habis_sk" name="pgw_tanggal_habis_sk" size="15" maxlength="15" value="<?php echo $_POST["pgw_tanggal_habis_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_habis_sk" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />(dd-mm-yyyy)
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">TMT Angkat</td>
		<td width= "50%" align="left" colspan="2"> <?if(readbit($err_code,17)) {?>&nbsp;<font color="red">(*)</font><?}?>
			<input type="text" id="pgw_tmt_pangkat" name="pgw_tmt_pangkat" size="15" maxlength="15" value="<?php echo $_POST["pgw_tmt_pangkat"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tmt_pangkat" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />(dd-mm-yyyy)
		</td>
	</tr>-->
	<tr>
		<td width= "30%" align="left" class="tablecontent">Tanggal Keluar <?if(readbit($err_code,10)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "50%" align="left" colspan="2">
			<input type="text" id="pgw_tanggal_keluar" name="pgw_tanggal_keluar" size="15" maxlength="15" value="<?php echo $_POST["pgw_tanggal_keluar"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_keluar" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />(dd-mm-yyyy)
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">Alasan Keluar</td>
		<td width= "50%" align="left" colspan="2">
			<input type="text" name="pgw_alasan_keluar" size="50" maxlength="100" value="<?php echo $_POST["pgw_alasan_keluar"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent">Jabatan Struktural<?php if(readbit($err_code,14) || readbit($err_code,15)){?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "50%" align="left" colspan="2">
			<select name="pgw_jabatan_struktural"  onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="GantiRol(this.form,this.options[this.selectedIndex].value)">
				<option value="--" <?php if ($_POST["pgw_jabatan_struktural"]=="--") echo"selected";?>>[ Pilih Jabatan Struktural ]</option>
				<?php for($i=0,$n=count($dataJabatan);$i<$n;$i++){ ?>
					<option value="<?php echo $dataJabatan[$i]["jab_struk_id"];?>"<?php if ($_POST["pgw_jabatan_struktural"]==$dataJabatan[$i]["jab_struk_id"]) echo"selected";?>><?php echo $dataJabatan[$i]["jab_struk_nama"];?></option>
				<?php } ?>
			</select>
		</td>
          <input type="hidden" name="id_rol" value="<?php echo $_POST["id_rol"]?>"/>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">Nomor SK Jabatan Struktural</td>
		<td width= "50%" align="left" colspan="2">
			<input type="text" name="pgw_no_sk_jab_struktural" size="20" maxlength="50" value="<?php echo $_POST["pgw_no_sk_jab_struktural"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">PTKP</td>
		<td width= "50%" align="left" colspan="2">
			<select name="id_ptkp" class="inputField" onKeyDown="return tabOnEnter(this, event);">
				<option class="inputField" value="">[Pilih Status]</option>
				<?php for($i=0,$n=count($dataPtkp);$i<$n;$i++){ ?>								
					<option class="inputField" value="<?php echo $dataPtkp[$i]["ptkp_id"];?>" <?php if($dataPtkp[$i]["ptkp_id"]==$_POST["id_ptkp"]) echo "selected"; ?>><?php echo $dataPtkp[$i]["ptkp_nama"];?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">Gaji Pokok</td>
		<td width= "50%" align="left" colspan="2">
			<input type="text" name="pgw_gaji_pokok" size="20" maxlength="15" value="<?php echo $_POST["pgw_gaji_pokok"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onKeyUp="this.value=formatCurrency(this.value);"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">Masa Kerja</td>
		<td width= "50%" align="left" colspan="2">
			<select name="pgw_masa_kerja" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
                    <option value="0"<?php if ($_POST["pgw_masa_kerja"]==0) echo"selected"?>>< 1 Tahun</option>
				<?php for($i=1,$n=50;$i<=$n;$i++){ ?>
					<option value="<?php echo $i;?>"<?php if ($_POST["pgw_masa_kerja"]==$i) echo"selected"?>><?php echo $i;?> Tahun</option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">Jam Masuk</td>
		<td width= "50%" align="left" colspan="2">
			<select name="pgw_jam_masuk_jam" >
				<?php for($i=0,$n=24;$i<$n;$i++){ ?>
					<option value="<?php echo $i;?>" <?php if($i==$_POST["pgw_jam_masuk_jam"]) echo "selected"; ?>><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?></option>
				<?php } ?>
				</select>:
				<select name="pgw_jam_masuk_menit" >
				<?php for($i=0,$n=60;$i<$n;$i++){ ?>
					<option value="<?php echo $i;?>" <?php if($i==$_POST["pgw_jam_masuk_menit"]) echo "selected"; ?>><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<!--<tr>
		<td width= "30%" align="left" class="tablecontent">Dapat Plafon Rawat Jalan</td>
		<td width= "50%" align="left" colspan="2">
			<input name="pgw_is_plafon_jalan" id="pgw_is_plafon_jalan" type="checkbox" value="y" onKeyDown="return tabOnEnter_select_with_button(this, event);" <?php if($_POST["pgw_is_plafon_jalan"] == "y") echo "checked";?>/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent">Dapat Plafon Rawat Inap</td>
		<td width= "50%" align="left" colspan="2">
			<input name="pgw_is_plafon_inap" id="pgw_is_plafon_inap" type="checkbox" value="y" onKeyDown="return tabOnEnter_select_with_button(this, event);" <?php if($_POST["pgw_is_plafon_inap"] == "y") echo "checked";?>/>
		</td>
	</tr>

     <tr>
          <td colspan="3" align="center" class="subHeader">USER LOGIN</td>
	</tr>
     <tr>
          <td width="30%" class="tablecontent"><strong>&nbsp;Nama Login</strong></td>
          <td width="70%"><input onKeyDown="return tabOnEnter(this, event);" type="text" name="usr_loginname" size="30" maxlength="50" value="<?php echo $_POST["usr_loginname"];?>"/></td>
     </tr>
     <tr>
          <td width="30%" class="tablecontent"><strong>&nbsp;Password</strong></td>
          <td width="70%">
               <input onKeyDown="return tabOnEnter(this, event);" type="password" class="<?php if($_POST["usr_id"]) echo "bDisable";?>" name="usr_password" size="30" maxlength="50" <?php if($_POST["usr_id"]){ ?>disabled<?php } ?>/>
               <?php if($_POST["usr_id"]){ ?>
                    <input onKeyDown="return tabOnEnter(this, event);" type="checkbox" name="is_password" id="is_password" onClick="GantiPassword(this.form,this);"/><label for="is_password">Ganti Password</label>
               <?php } else { ?>
                    <input type="hidden" name="is_password" id="is_password" value="y">
               <?php } ?>
          </td>
     </tr>
     <tr>
          <td width="30%" class="tablecontent"><strong>&nbsp;Ulangi Password<?php if(readbit($err_code,16)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
          <td width="70%">
               <input onKeyDown="return tabOnEnter(this, event);" type="password" class="<?php if($_POST["usr_id"]) echo "bDisable";?>" name="usr_password2" size="30" maxlength="50" <?php if($_POST["usr_id"]){ ?>disabled<?php } ?>/>
          </td>
     </tr>
     <?php if($_POST["usr_id"]){ ?>
          <tr>
               <td width="30%" class="tablecontent"><strong>&nbsp;Status</strong></td>
               <td width="70%">
                    <input onKeyDown="return tabOnEnter(this, event);" type="checkbox" name="usr_status" id="usr_status" value="y" <?php if($_POST["usr_status"]=="y") echo "checked";?>/><label for="usr_status">Aktif</label>
               </td>
          </tr>
     <?php } ?>
-->
     <?php } ?>
     
     <tr>
		<td colspan="3" align="center">&nbsp;</td>
	</tr>	
	<tr>
          <td colspan="4" align="center" class="tableheader">
               <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Simpan" class="button"/>
               <input type="button" name="btnBack" value="Kembali" class="button" onClick="document.location.href='<?php echo $viewPage?>'">               
          </td>
    </tr>
</table>

<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="pgw_id" value="<?php echo $pgwId;?>"/>
<input type="hidden" name="usr_id" value="<?php echo $_POST["usr_id"];?>"/>
<input type="hidden" name="nama" value="<?php echo $_POST["nama"];?>"/>
<?php if(!$isPersonalia) { ?>
     <input type="hidden" name="pgw_nip" value="<?php echo $_POST["pgw_nip"];?>"/>
<?php } ?>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
<? if (readbit($err_code,12)) { ?>
<br>
<font color="green"><strong>Sudah ada data pegawai dengan Nomor Induk <?=$_POST["pgw_nip"]?>.</strong></font>
<? }?>
<? if (readbit($err_code,13)) { ?>
<br>
<font color="green"><strong>Unit Kerja Harus Diisi.</strong></font>
<? }?>
<? if (readbit($err_code,14)) { ?>
<br>
<font color="green"><strong>Jabatan Struktural Harus Diisi.</strong></font>
<? }?>
<? if (readbit($err_code,15)) { ?>
<br>
<font color="green"><strong>Role untuk Jabatan Struktural ini Belum di-Setup<br>Setup dulu Role di Master Jabatan Struktural.</strong></font>
<? }?>
<? if (readbit($err_code,1)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Lahir Pegawai tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Lulus SD tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,3)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Lulus SLTP tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,4)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Lulus SLTA tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,5)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Lulus DIPLOMA tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,6)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Lulus Strata - 1 tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,7)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Lulus Strata - 2 tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,8)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Lulus Strata - 3 tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,9)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Masuk Pegawai tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,10)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Keluar Pegawai tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,1)||readbit($err_code,2)||readbit($err_code,3)||readbit($err_code,4)||readbit($err_code,5)||readbit($err_code,6)||readbit($err_code,7)||readbit($err_code,8)||readbit($err_code,9)||readbit($err_code,10)) { ?>
<br>
<font color="red"><strong>Hint : format tanggal harus "dd-mm-yyyy" (tanggal-bulan-tahun) dan pastikan tanggal tersebut benar.</strong></font>
<? } ?>

<? if (readbit($err_code,16)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal Habis SK Pegawai tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,17)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk TMT Pangkat Pegawai tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,19)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk Tanggal SK Jabatan Akademik tidak benar.</strong></font>
<? } ?>
<? if (readbit($err_code,20)) { ?>
<br>
<font color="green"><strong>Format Tanggal untuk TMT Akademik tidak benar.</strong></font>
<? } ?>
</span>
<script>document.frmEdit.usr_loginname.focus();</script>

</form>


<script type="text/javascript">
// ---tanggal lahir pegawai ---
    Calendar.setup({
        inputField     :    "pgw_tanggal_lahir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_lahir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });

// --- tanggal lulus sd ---
	Calendar.setup({
		inputField     :    "pgw_sd_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_sd",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
     
// --- tanggal lulus sltp ---
	Calendar.setup({
		inputField     :    "pgw_sltp_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_sltp",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});

// --- tanggal lulus slta ---
	Calendar.setup({
		inputField     :    "pgw_slta_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_slta",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});

// --- tanggal lulus diploma ---
	Calendar.setup({
		inputField     :    "pgw_diploma_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_diploma",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});

// --- tanggal lulus s1 ---
	Calendar.setup({
		inputField     :    "pgw_s1_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_s1",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});

// --- tanggal lulus s2 ---
	Calendar.setup({
		inputField     :    "pgw_s2_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_s2",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});

// --- tanggal lulus s3 ---
	Calendar.setup({
		inputField     :    "pgw_s3_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_s3",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});


<?php if($isPersonalia) { ?>

// --- tanggal masuk (PeKERJAAN) ---
	Calendar.setup({
		inputField     :    "pgw_tanggal_masuk",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_masuk",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});

// --- tanggal habis_sk(PeKERJAAN) ---
	/*Calendar.setup({
		inputField     :    "pgw_tanggal_habis_sk",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_habis_sk",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});

// --- tanggal TMT(PeKERJAAN) ---
	Calendar.setup({
		inputField     :    "pgw_tmt_pangkat",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tmt_pangkat",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});*/

// --- tanggal keluar (PeKERJAAN) ---
	Calendar.setup({
		inputField     :    "pgw_tanggal_keluar",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_keluar",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
<?php } ?>
</script>

<?php echo $view->RenderBodyEnd(); ?>
