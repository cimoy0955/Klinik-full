<?php
     //LIBRARY
    require_once("root.inc.php");
    require_once($ROOT."library/bitFunc.lib.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");
    require_once($ROOT."library/dateFunc.lib.php");
    require_once($ROOT."library/tree.cls.php");
    require_once($ROOT."library/inoLiveX.php");
    require_once($APLICATION_ROOT."library/view.cls.php");
    require_once($APLICATION_ROOT."library/config/global.cfg.php");
     
     //INISIALISAI AWAL LIBRARY
    $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
    $dtaccess = new DataAccess();
    $enc = new textEncrypt();
    $auth = new CAuth();
    $err_code = 0;
    $tree = new CTree("global.global_customer","cust_id",TREE_LENGTH);
    $userData = $auth->GetUserData();
     
    if(!$_POST["reg_dinasluar_tanggal"]) $_POST["reg_dinasluar_tanggal"] = getDateToday();
    if($_GET["tipe"]) $_POST["tipe"] = $_GET["tipe"];
     
    $plx = new InoLiveX("CheckKode,GetReg,CekUmur,get_rujukan_rs,get_rujukan_dokter");     

 	/*if(!$auth->IsAllowed("registrasi",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("registrasi",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }*/


    //variable awal
    //if($_POST["x_mode"]=="Edit"){
      $_x_mode = $_POST["x_mode"];
    /*}else{
      $_x_mode = "New"; 
    }*/
    $thisPage = "registrasi.php";
    $viewPage = "pegawai_view.php";
    $findPage = "pasien_find.php?";
    $cariPage = "kk_find.php?";
    $findPage_rujukan = "registrasi_tambah_rujukan.php?";
    $findPage_rujukan_dokter = "registrasi_tambah_rujukan_dokter.php?";
	
	
	 //AJAX / JQUERY
     function CheckKode($kode,$custUsrId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.cust_usr_id FROM global.global_customer_user a 
                    WHERE upper(a.cust_usr_kode) = ".QuoteValue(DPE_CHAR,strtoupper($kode));
                    
          if($custUsrId) $sql .= " and a.cust_usr_id <> ".QuoteValue(DPE_NUMERIC,$custUsrId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaKode = $dtaccess->Fetch($rs);
          
	  return $dataAdaKode["cust_usr_id"];
	}
     
     function GetReg($kode)
	{
          global $dtaccess;
          
          $sql = "SELECT reg_id FROM klinik.klinik_registrasi a
                    join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr  
                    WHERE reg_status not like ".QuoteValue(DPE_CHAR,STATUS_SELESAI."%")." 
                    and upper(b.cust_usr_kode) = ".QuoteValue(DPE_CHAR,strtoupper($kode));
                    
          if($custUsrId) $sql .= " and a.cust_usr_id <> ".QuoteValue(DPE_CHAR,$custUsrId);
          
          $rs = $dtaccess->Execute($sql);
          $data = $dtaccess->Fetch($rs);
          
	  return $data["reg_id"];
	}
      
    function CekUmur($tgl_lahir)
    {
        $umur = HitungUmur($tgl_lahir);
        return $umur; 
    }

    function get_rujukan_rs()
    {
    	global $dtaccess;

		$sql = "select rujukan_rs_id, rujukan_rs_nama from global.global_rujukan_rs order by rujukan_rs_id";
    	$rs = $dtaccess->Execute($sql);
    	$data = $dtaccess->FetchAll($rs);
    	return "{\"data\":".json_encode($data)."}";
    }

    function get_rujukan_dokter()
    {
    	global $dtaccess;

		$sql = "select rujukan_dokter_id, rujukan_dokter_nama from global.global_rujukan_dokter order by rujukan_dokter_id";
    	$rs = $dtaccess->Execute($sql);
    	$data = $dtaccess->FetchAll($rs);
    	return "{\"data\":".json_encode($data)."}";
    }

      
  //AMBIL DATA AWAL UNTUK EDIT
	if($_POST["btnLanjut"]) {
	    $sql = "select a.*,b.cust_nama,c.reg_jenis_pasien,c.reg_status,c.reg_rujukan from global.global_customer_user a
		    left join global.global_customer b on a.id_cust = b.cust_id
		    left join klinik.klinik_registrasi c on c.id_cust_usr = a.cust_usr_id 
		    where a.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"])."
		    order by c.reg_tanggal desc,c.reg_waktu desc"; 
	    $dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
		//echo $sql;
	    if($dataPasien['reg_status'] && $dataPasien['reg_status']{0}!='E'){
	      $nowStatus = $rawatStatus[$dataPasien['reg_status']{1}];
	      $nowPasien = $rawatStatus[$dataPasien['reg_status']{0}];
	      echo "<font color='green'><strong>Hint : Pasien ".$_POST["cust_usr_kode"]." Sedang Berada di  ".$nowStatus." ".$nowPasien." , harap selesaikan pemeriksaan terlebih dahulu</strong></font>";
	      die();                                      
	    }

		$_POST["cust_nama"] = htmlspecialchars($dataPasien["cust_nama"]); 
		$_POST["cust_usr_id"] = $dataPasien["cust_usr_id"]; 
		$_POST["cust_usr_nama"] = htmlspecialchars($dataPasien["cust_usr_nama"]); 
		$_POST["cust_usr_tempat_lahir"] = $dataPasien["cust_usr_tempat_lahir"]; 
		$_POST["cust_usr_tanggal_lahir"] = format_date($dataPasien["cust_usr_tanggal_lahir"]); 
		$_POST["cust_usr_jenis_kelamin"] = $dataPasien["cust_usr_jenis_kelamin"]; 
		$_POST["cust_usr_status_nikah"] = $dataPasien["cust_usr_status_nikah"]; 
		$_POST["cust_usr_agama"] = $dataPasien["cust_usr_agama"];          
		$_POST["cust_usr_warganegara"] = $dataPasien["cust_usr_warganegara"]; 
		if($_POST["cust_usr_warganegara"]!="WNI" && $_POST["cust_usr_warganegara"]!="WNI Keturunan") $_POST["wna"] = $_POST["cust_usr_warganegara"];
		$_POST["cust_usr_golongan_darah"] = $dataPasien["cust_usr_golongan_darah"]; 
		$_POST["cust_usr_alamat"] = htmlspecialchars($dataPasien["cust_usr_alamat"]); 
		$_POST["cust_usr_telp"] = $dataPasien["cust_usr_telp"]; 
		$_POST["cust_usr_hp"] = $dataPasien["cust_usr_hp"]; 
		$_POST["cust_usr_foto"] = $dataPasien["cust_usr_foto"]; 
		$_POST["cust_usr_kota"] = $dataPasien["cust_usr_kota"]; 
		$_POST["cust_usr_propinsi"] = $dataPasien["cust_usr_propinsi"]; 
		$_POST["cust_usr_kodepos"] = $dataPasien["cust_usr_kodepos"]; 
		$_POST["cust_usr_tinggi"] = $dataPasien["cust_usr_tinggi"]; 
		$_POST["cust_usr_berat"] = $dataPasien["cust_usr_berat"]; 
		$_POST["cust_usr_pekerjaan"] = $dataPasien["cust_usr_pekerjaan"]; 
		$_POST["cust_id"] = $dataPasien["id_cust"]; 
		$_POST["cust_usr_alergi"] = $dataPasien["cust_usr_alergi"]; 
		$_POST["cust_usr_jenis"] = $dataPasien["reg_jenis_pasien"]; 
		$_POST["cust_usr_kota_asal"] = htmlspecialchars($dataPasien["cust_usr_kota_asal"]);
		$_POST["reg_rujukan"] = $dataPasien["reg_rujukan"];
		$_POST["cust_usr_noktp"] = $dataPasien["cust_usr_noktp"];

          $_x_mode = "Edit";
	}//end lanjut
	     
  //POST FOTO
	$lokasi = $APLICATION_ROOT."images/foto_pasien";
	if($_POST["cust_usr_foto"]) $fotoName = $lokasi."/".$_POST["cust_usr_foto"];
     else $fotoName = $lokasi."/default.gif";     
	     
  // ADD / EDIT DATA
	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $userCustId = $_POST["cust_usr_id"];
               $_x_mode = "Edit";
          }
	  
	  if($_POST["cust_usr_kode"]) $err_code = clearbit($err_code,11);
	  else $err_code = setbit($err_code,11);
	  
	  if($_POST["cust_usr_nama"]) $err_code = clearbit($err_code,1);
	  else $err_code = setbit($err_code,1);
	  
	  if($_POST["cust_usr_tanggal_lahir"]) $err_code = clearbit($err_code,2);
	  else $err_code = setbit($err_code,2);	  
	  
	  if($_POST["cust_usr_alamat"]) $err_code = clearbit($err_code,3);
	  else $err_code = setbit($err_code,3);
	  /*
	  if($_POST["cust_usr_noktp"]) $err_code = clearbit($err_code,13);
	  else $err_code = setbit($err_code,13);
	  */
	  	
	  if($_POST["reg_rujukan"] != '--' && $_POST["reg_rujukan"] != '6' ) {
	  	if ($_POST["id_rujukan_dokter"] == '--') 
	  		$err_code = setbit($err_code,6);
	  	else $err_code = clearbit($err_code,6);

	  	if ($_POST["id_rujukan_rs"] == '--') 
	  		$err_code = setbit($err_code,5);
	  	else $err_code = clearbit($err_code,5);
	  } 
	  

		if ($_POST["btnSave"]) {

			if ($_POST["cust_usr_noktp"]) {
		  	$sql = "select cust_usr_kode from global.global_customer_user where cust_usr_noktp = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_noktp"]);
		  	$rs_ktp = $dtaccess->Execute($sql);
			$data_ktp = $dtaccess->Fetch($rs_ktp);
			if(!$data_ktp) $err_code = clearbit($err_code,14);
			else $err_code = setbit($err_code,14);
			}else{
				$err_code = clearbit($err_code,14);
			}
			
			if ($_POST["cust_usr_nama"] && $_POST["cust_usr_alamat"] && $_POST["cust_usr_tanggal_lahir"]) {
				
				$sql = "select cust_usr_kode 
					from global.global_customer_user
					where upper(cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_nama"]."%"). 
					" and cust_usr_tanggal_lahir = ".QuoteValue(DPE_CHAR,date_db($_POST["cust_usr_tanggal_lahir"]))." 
					and upper(cust_usr_alamat) like ".QuoteValue(DPE_CHAR,"%".strtoupper(trim($_POST["cust_usr_alamat"]))."%");
				$rs_cekNama = $dtaccess->Execute($sql);
				$data_cekNama = $dtaccess->Fetch($rs_cekNama);
				if(!$data_cekNama) $err_code = clearbit($err_code,15);
				else $err_code = setbit($err_code,15);
				}
			}

	  if($err_code == 0){

	    if(!$_POST["cust_nama"]) $_POST["cust_nama"] = $_POST["cust_usr_nama"];
	    $sql = "select cust_id, cust_nama from global.global_customer 
		where upper(cust_nama) = ".QuoteValue(DPE_CHAR,strtoupper($_POST["cust_nama"])); 
	    $dataCust = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
	    
	    if($dataCust) $custId = $dataCust["cust_id"];
	    
	    // --- ngisi data customer nya ---
	    $dbTable = "global_customer";
       
	    $dbField[0] = "cust_id";   // PK
	    $dbField[1] = "cust_nama";
		   
	    if(!$dataCust) $custId = $tree->AddChild();
	    $dbValue[0] = QuoteValue(DPE_CHAR,$custId);
	    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["cust_nama"]);
	    
	    //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
	    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
  
	    if(!$dataCust)
		 $dtmodel->Insert() or die("insert error");
	    elseif($dataCust)
		 $dtmodel->Update() or die("update error");
	    //echo $cek_nya."<br />";
	    unset($dtmodel);
	    unset($dbField);
	    unset($dbValue);
	    unset($dbKey);
	    
	    
	    // --- insert ke tbl client user ---
	    $dbTable = "global.global_customer_user";
	    
	    $dbField[0] = "cust_usr_id";   // PK
	    $dbField[1] = "cust_usr_nama";
	    $dbField[2] = "id_cust";
	    $dbField[3] = "cust_usr_tempat_lahir";
	    $dbField[4] = "cust_usr_tanggal_lahir";
	    $dbField[5] = "cust_usr_alamat";            
	    $dbField[6] = "cust_usr_kodepos";            
	    $dbField[7] = "cust_usr_telp";
	    $dbField[8] = "cust_usr_hp";
	    $dbField[9] = "cust_usr_jenis_kelamin";
	    $dbField[10] = "cust_usr_status_nikah";
	    $dbField[11] = "cust_usr_agama";            
	    $dbField[12] = "cust_usr_golongan_darah";            
	    $dbField[13] = "cust_usr_tinggi";            
	    $dbField[14] = "cust_usr_berat";            
	    $dbField[15] = "cust_usr_foto";
	    $dbField[16] = "cust_usr_pekerjaan";            
	    $dbField[17] = "cust_usr_who_update";
	    $dbField[18] = "cust_usr_when_update";
	    $dbField[19] = "cust_usr_kode";
	    $dbField[20] = "cust_usr_alergi";
	    $dbField[21] = "cust_usr_kota_asal";
	    $dbField[22] = "cust_usr_jenis";
	    $dbField[23] = "cust_usr_propinsi";
	    $dbField[24] = "cust_usr_kota";
	    $dbField[25] = "cust_usr_noktp";
	    
	    if(!$_POST["cust_usr_agama"] || $_POST["cust_usr_agama"]=="--") $_POST["cust_usr_agama"] = 'null';
	    
	    if(!$_POST["cust_usr_id"]) $userCustId = $dtaccess->GetNewID("global_customer_user","cust_usr_id",DB_SCHEMA_GLOBAL);
	    else $userCustId = $_POST["cust_usr_id"];
	    $dbValue[0] = QuoteValue(DPE_NUMERICKEY,$userCustId);
	    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama"]);
	    $dbValue[2] = QuoteValue(DPE_CHAR,$custId);
	    $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["cust_usr_tempat_lahir"]);
	    $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggal_lahir"]));
	    $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat"]);
	    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kodepos"]);
	    $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["cust_usr_telp"]);
	    $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["cust_usr_hp"]);
	    $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis_kelamin"]);
	    $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["cust_usr_status_nikah"]);
	    $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["cust_usr_agama"]);
	    $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["cust_usr_golongan_darah"]);
	    $dbValue[13] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_tinggi"]);
	    $dbValue[14] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_berat"]);
	    $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["cust_usr_foto"]);
	    $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["cust_usr_pekerjaan"]);
	    $dbValue[17] = QuoteValue(DPE_CHAR,$userData["name"]);
	    $dbValue[18] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
	    $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
	    $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alergi"]);
	    $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kota_asal"]);
	    $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
	    $dbValue[23] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_propinsi"]);
	    $dbValue[24] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_kota"]);
	    $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["cust_usr_noktp"]);
	    
	    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	    
	    if($_x_mode=="New"){  
		 $dtmodel->Insert() or die("insert error");
		 //$cek_nya = "save";
	    }elseif($_x_mode=="Edit"||$_x_mode=="Tambah"){
		 $dtmodel->Update() or die("update error");
		 //$cek_nya = "update";
	    }
	    //$cek_nya = $_POST["x_mode"];
	    //echo $cek_nya."<br />";
	    unset($dtmodel);
	    unset($dbField);
	    unset($dbValue);
	    unset($dbKey);
	      
	    $sql = "select reg_status from klinik.klinik_registrasi 
		where id_cust_usr = ".QuoteValue(DPE_CHAR,$userCustId)." order by reg_tanggal desc, reg_waktu desc limit 1";
	    $dataReg = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);  
			    
	    $sql = "select preop_tanggal_jadwal 
		from klinik.klinik_preop 
		where id_cust_usr = ".QuoteValue(DPE_CHAR,$userCustId)." 
		and cast(preop_tanggal_jadwal as date) = ".QuoteValue(DPE_DATE,date("Y-m-d"));      
      
	    $rs = $dtaccess->Execute($sql);    
	    $dataJadwal = $dtaccess->Fetch($rs);
	    
	    if($dataJadwal) $jadwal = 'y';
	    else $jadwal = 'n';
	    
	    $dbSchema = "klinik";
	    $dbTable = "klinik_registrasi";
       
	    $dbField[0] = "reg_id";   // PK
	    $dbField[1] = "reg_tanggal";
	    $dbField[2] = "reg_waktu";
	    $dbField[3] = "id_cust_usr";
	    $dbField[4] = "reg_status";
	    $dbField[5] = "reg_who_update";
	    $dbField[6] = "reg_when_update";
	    $dbField[7] = "reg_jenis_pasien";
	    $dbField[8] = "reg_status_pasien";
	    $dbField[9] = "reg_jadwal";
	    $dbField[10] = "reg_keterangan";
	    $dbField[11] = "reg_rujukan";
	    $dbField[12] = "reg_tipe_umur";
	    $dbField[13] = "reg_no_sep";
	    $dbField[14] = "reg_no_kartubpjs";
	    $dbField[15] = "reg_ugd";
	    $dbField[16] = "reg_status_ugd";
	    $dbField[17] = "reg_tipe_rawat";
	    if ($_POST["id_rujukan_rs"] != "--") $dbField[18] = "id_rujukan_rs";
	    if ($_POST["id_rujukan_dokter"] != "--") $dbField[19] = "id_rujukan_dokter";
	    if($_POST["cust_usr_jenis"]==PASIEN_DINASLUAR){
	      $dbField[20] = "reg_dinasluar_tanggal";
	      $dbField[21] = "reg_dinasluar_kota";
	    }
	    	    
	    // untuk pasien dibawah 15th statusnya A,masuk ke antrian polianak
	    if(HitungUmur(date_db($_POST["cust_usr_tanggal_lahir"]))>15 && $_POST["tipe"]!=RAWAT_UGD){
			$regStatusUmur = "D";
			$regStatus = $_POST["cmbNext"];
	    }elseif(HitungUmur(date_db($_POST["cust_usr_tanggal_lahir"]))<=15){
			$regStatusUmur = "A";
			$regStatus = STATUS_PEMERIKSAAN;
	    }
	    
	    if($_POST["tipe"]==RAWAT_UGD) $regStatus = STATUS_PEMERIKSAAN;
	    $pasienStatus = ($_POST["btnSave"]||($_POST["tipe"]==RAWAT_UGD))? PASIEN_BARU : PASIEN_LAMA;
	    
	    $regId = $dtaccess->GetTransID();
	    $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
	    $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
	    $dbValue[2] = QuoteValue(DPE_DATE,date("H:i:s"));
	    $dbValue[3] = QuoteValue(DPE_CHAR,$userCustId);
	    $dbValue[4] = QuoteValue(DPE_CHAR,($regStatus.STATUS_ANTRI));
	    $dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
	    $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
	    $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$_POST["cust_usr_jenis"]);
	    $dbValue[8] = QuoteValue(DPE_CHAR,$pasienStatus);
	    $dbValue[9] = QuoteValue(DPE_CHAR,$jadwal);
	    $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["reg_keterangan"]);
	    $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["reg_rujukan"]);
	    $dbValue[12] = QuoteValue(DPE_CHAR,$regStatusUmur);
	    $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["reg_no_sep"]);
	    $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["reg_no_kartubpjs"]);
	    $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["reg_ugd"]);
	    $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["reg_ugd"]);
	    $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["tipe"]);
	    if ($_POST["id_rujukan_rs"] != "--") $dbValue[18] = QuoteValue(DPE_NUMERIC,$_POST["id_rujukan_rs"]);
	    if ($_POST["id_rujukan_dokter"] != "--") $dbValue[19] = QuoteValue(DPE_NUMERIC,$_POST["id_rujukan_dokter"]);
	    if($_POST["cust_usr_jenis"]==PASIEN_DINASLUAR){
			$dbValue[20] = QuoteValue(DPE_DATE,date_db($_POST["reg_dinasluar_tanggal"]));
			$dbValue[21] = QuoteValue(DPE_CHAR,$_POST["reg_dinasluar_kota"]);
	    }
	    
	    //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
	    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	    
	    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,$dbSchema);
	    
	    if($dataReg["reg_status"]{0}==STATUS_SELESAI || !$dataReg) { 
		 $dtmodel->Insert() or die("insert error");
		 //$cek_nya = "daftarulang";
	    }
	    
	    //echo $cek_nya."<br />";
	    unset($dtmodel);
	    unset($dbField);
	    unset($dbValue);
	    unset($dbKey);

	    // insert ke tabel klinik_history_pasien
	    $dbTable = "klinik_history_pasien";

	    $dbField[0] = "history_id";
	    $dbField[1] = "id_reg";
	    $dbField[2] = "history_status_pasien";
	    $dbField[3] = "history_when_out";

	    $history_id = $dtaccess->GetTransID();
	    $dbValue[0] = QuoteValue(DPE_CHAR,$history_id);
	    $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
	    $dbValue[2] = QuoteValue(DPE_CHAR,STATUS_REGISTRASI);
	    $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

	    $dbKey[0] = 0;

	    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,$dbSchema);

	    $dtmodel->Insert() or die("insert error");

	    unset($dtmodel);
	    unset($dbField);
	    unset($dbValue);
	    unset($dbKey);
	    // end insert 
	    
	    // insert ke folio ---
	    if($dataReg["reg_status"]{0}==STATUS_SELESAI || !$dataReg) {
	    
		if($_POST["tipe"]==RAWAT_JALAN && $regStatusUmur == "D") $sql_where = "where biaya_kode = ".QuoteValue(DPE_CHAR,"RJ1-02");
		elseif($_POST["tipe"]==RAWAT_JALAN && $regStatusUmur == "A") $sql_where = "where biaya_kode = ".QuoteValue(DPE_CHAR,"RJ1-03");
		elseif($_POST["tipe"]==RAWAT_UGD)  $sql_where = "where biaya_kode = ".QuoteValue(DPE_CHAR,"RJ3-01");
		/*
		if($_POST["tipe"]==RAWAT_JALAN) $sql_where = "where biaya_kode = ".QuoteValue(DPE_CHAR,"RJ3-27");
		elseif($_POST["tipe"]==RAWAT_UGD)  $sql_where = "where biaya_kode = ".QuoteValue(DPE_CHAR,"RJ3-29");
*/
		$sql = "select * from klinik.klinik_biaya ".$sql_where;
		 $dataBiaya = $dtaccess->Fetch($sql,DB_SCHEMA);  
		 
		 //$lunas = ($_POST["cust_usr_jenis"]==PASIEN_BAYAR_SWADAYA)?'n':'y';
		 $lunas = "n";
		 
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
		 $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
		 $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_nama"]);
		 $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
		 $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_id"]);
		 $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_REGISTRASI);
		 $dbValue[6] = QuoteValue(DPE_CHAR,$userCustId);
		 $dbValue[7] = QuoteValue(DPE_TIMESTAMP,date("Y-m-d H:i:s"));
		 $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
		 $dbValue[9] = QuoteValue(DPE_CHAR,'1');
		 $dbValue[10] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_total"]);
		 
		 //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
		 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
		 
		 $dtmodel->Insert() or die("insert error");
		 //$cek_nya = "insertfolio";
		 //echo $cek_nya."<br />";
		 unset($dtmodel);
		 unset($dbField);
		 unset($dbValue);
		 unset($dbKey);
			  
		  $sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_LOKET)." and bea_split_nominal > 0";
		 $dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
			  
			  for($i=0,$n=count($dataSplit);$i<$n;$i++) {
				  $dbTable = "klinik_folio_split";
			  
				  $dbField[0] = "folsplit_id";   // PK
				  $dbField[1] = "id_fol";
				  $dbField[2] = "id_split";
				  $dbField[3] = "folsplit_nominal";
					    
				  $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
				  $dbValue[1] = QuoteValue(DPE_CHAR,$folId);
				  $dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$i]["id_split"]);
				  $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$i]["bea_split_nominal"]);
				  
				  //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
				  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
				  
				  $dtmodel->Insert() or die("insert error");
				  //$cek_nya = "folioslip";
				  //echo $cek_nya."<br />";
				  unset($dtmodel);
				  unset($dbField);
				  unset($dbValue);
				  unset($dbKey);
			  }
	    }
	    // --- end insert folio ---
	    $_x_mode = "Save";
	    
	  }
	}
	
     // --- cari agama ---
     $sql = "select * from global.global_agama order by agm_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataAgama = $dtaccess->FetchAll($rs);

     // --- cari agama ---
     $sql = "select * from global.global_customer_tipe order by cust_tipe_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataTipePasien = $dtaccess->FetchAll($rs);
     
     // --- Bikin kode customer baru ---
     if(!$_POST["cust_usr_kode"] && $_POST["custTambah"]) {

        $_POST["cust_usr_id"] = $dtaccess->GetNewID("global_customer_user","cust_usr_id",DB_SCHEMA_GLOBAL);
 
        $sql = "select max(CAST(substring(cust_usr_kode from 1 for 6) as BIGINT)) as kode from global.global_customer_user
                    where substring(cust_usr_kode from 8 for 2) = ".QuoteValue(DPE_CHAR,date("y"));
        $lastKode = $dtaccess->Fetch($sql);
        $_POST["cust_usr_kode"] = str_pad($lastKode["kode"]+1,6,"0",STR_PAD_LEFT)."-".date("y");
	/*echo $sql*/;
	  $dbTable = "global.global_customer_user";
  
	  $dbField[0] = "cust_usr_id";   // PK
	  $dbField[1] = "cust_usr_kode";
		    
	  $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
	  $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
	  
	  //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
	  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
	  
	  $dtmodel->Insert() or die("insert error"); 
	  //echo $cek_nya."<br />";
	  unset($dtmodel);
	  unset($dbField);
	  unset($dbValue);
	  unset($dbKey);
     
	  $_x_mode="Tambah";
     }
     
     //-- bikin combo kota khusus Pulau Jawa --//
     $sql = "select * from global_kota where id_prop>=10 and id_prop<=15 order by id_prop DESC, kota_id";
     $rs_kota = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     while($data_kota = $dtaccess->Fetch($rs_kota)){
        $opt_kota[] = $view->RenderOption($data_kota["kota_id"],$data_kota["kota_nama"],($data_kota["kota_id"]==$_POST["reg_dinasluar_kota"])?"selected":"",null);
     }
     
     $sql = "select * from global.global_rujukan";
     $rs = $dtaccess->Execute($sql);
     $rujukan = $dtaccess->FetchAll($rs);
   
   
    if ($_POST["btnDel"]) {       

     $custUsrId = & $_POST["cust_usr_id"];       
	$sql = "delete from global.global_customer_user where cust_usr_id = ".QuoteValue(DPE_CHAR,$custUsrId);	
     $dtaccess->Execute($sql); 		
     $reset=1;
                   
		header("location:registrasi.php");
		exit();      
     }
     
     if($_POST["btnCancel"]){
	if($_x_mode=="Tambah"){
	  $sql_delete = "delete from global.global_customer_user where cust_usr_id=".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
	  $dtaccess->Execute($sql_delete);
	}
	header("location:registrasi.php");
	exit();
     }
     /* user request 17 Feb 14:
      * default kota=surabaya
      * default propinsi=jatim
      * updated on 18 Mar 14:
      * tambah cek untuk data prop & kota sebelumnya
      */
     if($_POST["cust_usr_propinsi"]) $_POST["cust_usr_propinsi"] = $_POST["cust_usr_propinsi"];
     else $_POST["cust_usr_propinsi"] = 15;
     if($_POST["cust_usr_kota"]) $_POST["cust_usr_kota"] = $_POST["cust_usr_kota"];
     else $_POST["cust_usr_kota"] = 222;
     $sql_prop = "SELECT * FROM global.global_propinsi ORDER BY prop_id";
     $rs_prop = $dtaccess->Execute($sql_prop);
     while($data_prop = $dtaccess->Fetch($rs_prop)){
        $optProp[] = $view->RenderOption($data_prop["prop_id"],$data_prop["prop_nama"],($data_prop["prop_id"]==$_POST["cust_usr_propinsi"])?"selected":"",null);
     }
     
     if ($_POST["cust_usr_propinsi"]) {
        $sql_kota_user = "SELECT * FROM global.global_kota WHERE id_prop = ".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_propinsi"])." ORDER BY kota_id";
        $rs_kota_user = $dtaccess->Execute($sql_kota_user);
        while($data_kota_user = $dtaccess->Fetch($rs_kota_user)){
          $optKotaUser[] = $view->RenderOption($data_kota_user["kota_id"],$data_kota_user["kota_nama"],($data_kota_user["kota_id"]==$_POST["cust_usr_kota"])?"selected":"",null);
        }   
     }else{
       $optKotaUser[] = $view->RenderOption("-","--Pilih Propinsi Terlebih Dulu--",null,null);
     }
     
     /* update combo box jenis pasien
      */
     foreach($bayarPasien as $key => $value){
	$optUsrJenis[] = $view->RenderOption($key,$value,($key==$_POST["cust_usr_jenis"])?"selected":"",null);
     }
     // -- end update 17 Feb 14 -- //

    $optStr = "";
 	$optStr = str_replace('{"data":', '', get_rujukan_rs()); 
	$optStr = str_replace("]}", "]", $optStr);
	$optStr = json_decode($optStr);
	// print_r($optStr);
	$optRujukanID[] = $view->RenderOption("--","--","selected");
	for($i=0;$i<count($optStr);$i++){
		$optRujukanID[] = $view->RenderOption($optStr[$i]->rujukan_rs_id,$optStr[$i]->rujukan_rs_nama,($_POST["id_rujukan_rs"]==$optStr[$i]->rujukan_rs_id)?"selected":"",null);
	}
     
    $optStr2 = "";
    $optStr2 = str_replace('{"data":', '', get_rujukan_dokter()); 
	$optStr2 = str_replace("]}", "]", $optStr2);
	$optStr2 = json_decode($optStr2);
	// print_r($optStr);
	$optRujukanDokterID[] = $view->RenderOption("--","--","selected");
	for($i=0;$i<count($optStr2);$i++){
		$optRujukanDokterID[] = $view->RenderOption($optStr2[$i]->rujukan_dokter_id,$optStr2[$i]->rujukan_dokter_nama,($_POST["id_rujukan_dokter"]==$optStr2[$i]->rujukan_dokter_id)?"selected":"",null);
	}

	$count=0;	
	 $optionsNext[$count] = $view->RenderOption(STATUS_REFRAKSI,$rawatStatus[STATUS_REFRAKSI],$show); $count++;
	 $optionsNext[$count] = $view->RenderOption(STATUS_DIAGNOSTIK_TIPE,$rawatStatus[STATUS_DIAGNOSTIK_TIPE],$show); $count++;
	 $optionsNext[$count] = $view->RenderOption(STATUS_PREOP,$rawatStatus[STATUS_PREOP],$show); $count++;
	 $optionsNext[$count] = $view->RenderOption(STATUS_OPERASI,$rawatStatus[STATUS_OPERASI],$show); $count++;
	 $optionsNext[$count] = $view->RenderOption(STATUS_LABORATORIUM,$rawatStatus[STATUS_LABORATORIUM],$show); $count++;
	 
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitDom(); ?>
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>
<div onKeyDown="CaptureEvent(event);">

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
				url:'registrasi_upload.php',
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
							//UpdateMedia(data.file,'type=r');
                                   //GetThumbs('target=dv_thumbs');
                                   document.getElementById('cust_usr_foto').value= data.file;
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
</script>	


<script language="Javascript">

<? $plx->Run(); ?>

var dataRol = Array();


var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=300,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=300,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}

function CaptureEvent(evt){
     var keyCode = document.layers ? evt.which : document.all ? evt.keyCode : evt.keyCode;     	
     
     if(keyCode==113) {  // -- F2 untuk menambah pasien baru ---
       if (confirm('Akan diciptakan No Rekam Medik baru. Apakah ingin Melanjutkan?')==1)
       {
            document.getElementById('custTambah').value = 'tambah';
            document.frmFind.submit();
        }
          
     }
     return false;
}


var _wnd_stat;

function BukaStatWindow(url,judul)
{
    if(!_wnd_stat) {
			_wnd_stat = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=610,height=600,left=100,top=100');
	} else {
		if (_wnd_stat.closed) {
			_wnd_stat = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=610,height=600,left=100,top=100');
		} else {
			_wnd_stat.focus();
		}
	}
     return false;
}

     <?php if($_x_mode=="Save" && $_POST["tipe"]==RAWAT_JALAN){ ?>
        BukaStatWindow('cetakstatus.php?id=<?php echo $userCustId;?>','Kartu Status');
        if(confirm('Cetak Barcode Pasien?')) BukaWindow('pasien_print_single.php?idpas=<?php echo $userCustId;?>','Barcode Pasien');
	       document.location.href='<?php echo $thisPage;?>?tipe=<?php echo RAWAT_JALAN?>';
     <?php }elseif($_x_mode=="Save" && $_POST["tipe"]==RAWAT_UGD){ ?>
	BukaStatWindow('cetakstatusUGD.php?id=<?php echo $userCustId;?>','Kartu Status');
        if(confirm('Cetak Barcode Pasien?')) BukaWindow('pasien_print_single.php?idpas=<?php echo $userCustId;?>','Barcode Pasien');
	       document.location.href='<?php echo $thisPage;?>?tipe=<?php echo RAWAT_UGD?>';
    <?php }?>
    
    
function CheckSimpan(frm) {
     <?php if($_x_mode == "Edit"){?>
     if(!frm.cust_usr_kode.value) {
          alert("Kode Pasien Harus Diisi");
          return false;
     }
     <?php }?>
     
     if(!frm.cust_usr_nama.value) {
          alert('Nama Harus Diisi');
          return false;
     } 
     
     if(!frm.cust_usr_jenis.value) {
          alert('Jenis Pasien Harus Diisi');
          return false;
     }
     
     if(CheckKode(frm.cust_usr_kode.value,frm.cust_usr_id.value,'type=r')){
		alert('Kode Pasien Sudah Ada');
		frm.cust_usr_kode.focus();
		frm.cust_usr_kode.select();
		return false;
	} 
	
     if(GetReg(frm.cust_usr_kode.value,'type=r')){ 
		alert('Pasien Telah Melakukan Registrasi');
		return true;
	}
	
     if (!frm.cust_usr_tanggal_lahir.value) {
	  alert('Tanggal lahir pasien harus diisi');
	  return false;
     }
     
     if (!frm.cust_usr_tempat_lahir.value) {
	  alert('Tempat Lahir Pasien Harus Diisi');
	  return false;
     }
     
     if (!frm.cust_usr_hp.value||!frm.cust_usr_telp) {
	  alert('Nomor telpon pasien harus diisi');
	  return false;
     }
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
		document.getElementById("cust_usr_jab_akademik").disabled = false;
		document.getElementById("cust_usr_no_sk_jab_akademik").disabled = false;
		document.getElementById("cust_usr_status_kerja").disabled = false;		
		document.getElementById("cust_usr_jab_akademik").style.backgroundColor = '#FFFFFF';
		document.getElementById("cust_usr_no_sk_jab_akademik").style.backgroundColor = '#FFFFFF';
		document.getElementById("cust_usr_status_kerja").style.backgroundColor = '#FFFFFF';		
        
	} else {
          
		document.getElementById("cust_usr_jab_akademik").disabled = true;
		document.getElementById("cust_usr_no_sk_jab_akademik").disabled = true;
		document.getElementById("cust_usr_status_kerja").disabled = true;		
		document.getElementById("cust_usr_jab_akademik").style.backgroundColor = '#e2dede';
		document.getElementById("cust_usr_no_sk_jab_akademik").style.backgroundColor = '#e2dede';
		document.getElementById("cust_usr_status_kerja").style.backgroundColor = '#e2dede';		
	}
}

function update_rujukan_rs(){

	var obj = document.getElementById("id_rujukan_rs");
	var length = obj.options.length;

	//clear the existing options first
	for (i = 0; i < length; i++) {
	  obj.options[i] = null;
	}

	//js DOM to create options
	var optText = get_rujukan_rs('type=r');	
	var jsonText = JSON.parse(optText);

	var opt = document.createElement("option");
	obj.text = "--";
	obj.value = "--";
	obj.add(opt);

	for (var i = 0; i < jsonText.data.length; i++) {
		var opt = document.createElement("option");
		opt.text = jsonText.data[i].rujukan_rs_nama;
		opt.value = jsonText.data[i].rujukan_rs_id;
		obj.add(opt);
	};
	
}

function update_rujukan_dokter(){

	var obj = document.getElementById("id_rujukan_dokter");
	var length = obj.options.length;

	//clear the existing options first
	for (i = 0; i < length; i++) {
	  obj.options[i] = null;
	}

	//js DOM to create options
	var optText = get_rujukan_dokter('type=r');	
	var jsonText = JSON.parse(optText);
	
	var opt = document.createElement("option");
	obj.text = "--";
	obj.value = "--";
	obj.add(opt);
	
	for (var i = 0; i < jsonText.data.length; i++) {
		var opt = document.createElement("option");
		opt.text = jsonText.data[i].rujukan_dokter_nama;
		opt.value = jsonText.data[i].rujukan_dokter_id;
		obj.add(opt);
	};
	
}

$("select").select2();

function CheckDataSave(frm) 
{
		return true;         
	
}  

function CheckUmur(tgl)
{
    var umur;
    umur = CekUmur(tgl,'type=r');
    if(umur<=15){
      alert("Pasien berumur 15 tahun kebawah. Pasien akan masuk antrian Poli Anak.");
    }
}

function view_sep(eval) {
  if ((eval=='12')||(eval=='13')||(eval=='14')) {
    document.getElementById('no_sep').innerHTML = 'Nomor Kartu BPJS&nbsp;&nbsp;<input type="text" size="20" maxlength="50" name="reg_no_kartubpjs" id="reg_no_kartubpjs" class="inputField" value="<?php echo $_POST["reg_no_kartubpjs"];?>">&nbsp;&nbsp;&nbsp;Nomor S.E.P.&nbsp;&nbsp;<input type="text" size="20" maxlength="50" name="reg_no_sep" id="reg_no_sep" class="inputField" value="<?php echo $_POST["reg_no_sep"];?>">';
  }else{
    document.getElementById('no_sep').innerHTML = '&nbsp;';
  }
}

function view_opkom(eval) {
  var sel_opkom = '&nbsp;&nbsp;<select name="reg_opkom_jenis" id="reg_opkom_jenis" onKeyDown="return tabOnEnter(this, event);"> <option value="Kiriman">Kiriman</option> <option value="Luar">Luar Gedung</option> </select>';
  if (eval=='8') {
    document.getElementById('opkom_view').innerHTML = sel_opkom;
  }else{
    document.getElementById('opkom_view').innerHTML = '&nbsp;';
  }
}

function view_rujukan(eval) {
	if (eval != 6 && eval != '--'){
		document.getElementById('detail_rujukan').style.display = "inline-block";
	} else {
		document.getElementById('detail_rujukan').style.display = "none";
		document.getElementById('id_rujukan_rs').selectedIndex = "0";
		document.getElementById('id_rujukan_dokter').selectedIndex = "0";
	}
}

</script>

<style type="text/css">
.Disable{
	color: #0F2F13;
	border: 1px solid #c2c6d3;
	background-color: #e2dede;
}
</style>

<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Registrasi PASIEN&nbsp;<?php echo $rawatPasien[$_GET["tipe"]];?></td>
	</tr>
</table> 
<?php //echo $_x_mode;?>

	
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" autocomplete="off">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
     <tr>
		<td width= "5%" align="left" class="tablecontent">Kode Pasien</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="25" maxlength="25" value="<?php echo $_POST["cust_usr_kode"];?>"/>
               <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
               <input type="submit" name="btnLanjut" value="Lanjut" class="button"/>
               <!--<input type="submit" name="btnAdd" value="Tambah" class="button"/>-->
                <input type="hidden" name="custTambah" id="custTambah"/>
		<input type="hidden" name="tipe" value="<?php echo $_POST["tipe"];?>"/>
          </td>
          </tr>
          <tr>                       
          <td colspan="4" class="tablecontent">Tekan tombol F2 untuk menambah pasien baru</td>                  
        </tr>
</table>
<?php if(!$_POST["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>Kode Pasien Tidak Ditemukan</strong></font>
<?php } ?>

<script>document.frmFind.cust_usr_kode.focus();</script>

</form>

<?php if($_POST["cust_usr_id"] || $_POST["custTambah"] || $_POST["cust_usr_jenis"]) { ?>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data"  onSubmit="return CheckDataSave(this)"  autocomplete="off">
<table width="100%" border="1" cellpadding="4" cellspacing="1" id="tabel_reg">
	<tr>
          <td colspan="3" align="center" class="subHeader">DATA PRIBADI</td>
	</tr>
     <tr>
		<td width= "20%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "40%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="15" maxlength="15" value="<?php echo $_POST["cust_usr_kode"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
          <td rowspan="5"  width= "40%"  valign="top" class="tablecontent-odd">
			<img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $fotoName;?>"  border="1">
			<input type="hidden" name="cust_usr_foto" id="cust_usr_foto" value="<?php echo $_POST["cust_usr_foto"];?>">
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Nomor KTP<?php if(readbit($err_code,13)||readbit($err_code,14)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td class="tablecontent-odd">
		  <?php echo $view->RenderTextBox("cust_usr_noktp","cust_usr_noktp","20","16",$_POST["cust_usr_noktp"],"inputField",null,false);?>
		</td>
	</tr>	
	<tr>
		<td width= "20%" align="left" class="tablecontent">Nama Lengkap<?php if(readbit($err_code,1) || readbit($err_code,15)) {?>&nbsp;<font color="red">(*)</font><?php }?></td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_nama" id="cust_usr_nama" size="30" maxlength="50" value="<?php echo $_POST["cust_usr_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
		</td>
	</tr>
	<?php //if($_x_mode=='Edit'){?>	
	<!--<tr>
		<td width= "20%" align="left" class="tablecontent">Nama KK</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_nama" id="cust_nama" size="30" maxlength="50" value="<?php echo $_POST["cust_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
               <a href="<?php echo $cariPage;?>&height=400&width=450&modal=true" class="thickbox" title="Cari KK"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Pasien" alt="Cari KK" /></a>
		</td>
	</tr>-->
	 <?php //}?>
  <tr>
		<td width= "20%"class="tablecontent"><!--Tempat Lahir / -->Tanggal Lahir <?php if(readbit($err_code,2) || readbit($err_code,15)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "40%" class="tablecontent-odd">
               <!--<input type="text" name="cust_usr_tempat_lahir" size="15" maxlength="20" value="<?php echo $_POST["cust_usr_tempat_lahir"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/> / -->
               <input type="text" id="cust_usr_tanggal_lahir" name="cust_usr_tanggal_lahir" size="15" maxlength="10" value="<?php echo $_POST["cust_usr_tanggal_lahir"];?>" onKeyDown="return tabOnEnter(this, event);"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lahir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
			<label>(dd-mm-yyy)</label>
		</td>
	</tr>
	<tr>
		<td width= "20%" class="tablecontent">Alamat<?php if(readbit($err_code,3) || readbit($err_code,15)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td class="tablecontent-odd">
			<table border=1 cellpadding=1 cellspacing=0 width="100%">
				<tr>
					<td colspan="2">
						<textarea name="cust_usr_alamat" id="cust_usr_alamat" rows="3" cols="65"><?php echo $_POST["cust_usr_alamat"];?></textarea>
					</td>
				</tr>
				<?php //if($_x_mode=='Edit'){?>
				<tr>
					<td width="20%" class="tablecontent-odd">Kode Pos</td>
                         <td>
                              <input type="text" name="cust_usr_kodepos" size="15" maxlength="15" value="<?php echo $_POST["cust_usr_kodepos"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<?php //}?>
				<tr>
					<td width="20%" class="tablecontent-odd">Telepon<?php if(readbit($err_code,4)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
                         <td>
                              <input type="text" name="cust_usr_telp" size="15" maxlength="15" value="<?php echo $_POST["cust_usr_telp"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Hp</td>
                         <td>
                              <input type="text" name="cust_usr_hp" size="15" maxlength="15" value="<?php echo $_POST["cust_usr_hp"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">Propinsi</td>
		<td width= "50%" align="left" class="tablecontent-odd" colspan=2>
      <?php echo $view->RenderComboBox("cust_usr_propinsi","cust_usr_propinsi",$optProp,"inputfield",null,"onchange=submit();");?>
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">Kota</td>
		<td width= "50%" align="left" class="tablecontent-odd" colspan=2>
      <?php echo $view->RenderComboBox("cust_usr_kota","cust_usr_kota",$optKotaUser,"inputfield");?>
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Jenis Kelamin</td>
		<td colspan="2" class="tablecontent-odd">
			<select name="cust_usr_jenis_kelamin" onKeyDown="return tabOnEnter(this, event);">
				<option value="L" <?php if($_POST["cust_usr_jenis_kelamin"]=="L")echo "selected";?>>Laki-laki</option>
				<option value="P" <?php if($_POST["cust_usr_jenis_kelamin"]=="P")echo "selected";?>>Perempuan</option>
			</select>
          </td>
	</tr>
	<?php //if($_x_mode=='Edit'){?>
	<!--<tr>
		<td class="tablecontent">Status Perkawinan</td>
		<td colspan="2" class="tablecontent-odd">
			<input type="radio" name="cust_usr_status_nikah" id="sty" value="y" <?php if($_POST["cust_usr_status_nikah"]=="y") echo "checked";?> onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="sty">Menikah</label>&nbsp;
			<input type="radio" name="cust_usr_status_nikah" id="stn" value="n" <?php if($_POST["cust_usr_status_nikah"]=="n") echo "checked";?> onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="stn">Belum Menikah</label>
		</td>
	</tr>
	<?//}?>
	<tr>
		<td class="tablecontent">Agama</td>
          <td colspan="2" class="tablecontent-odd">
			<select name="cust_usr_agama" id="cust_usr_agama" onKeyDown="return tabOnEnter(this, event);">
                    <option value="">[ Pilih Agama ]</option>
				<?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataAgama[$i]["agm_id"];?>" <?php if($dataAgama[$i]["agm_id"]==$_POST["cust_usr_agama"]) echo "selected"; ?>><?php echo $dataAgama[$i]["agm_nama"];?></option>
				<?php } ?>
			</select>
		</td>
	</tr>-->
	<?php //if($_x_mode=='Edit'){?>
	<!--<tr>
		<td class="tablecontent" align="left">Golongan Darah</td>
		<td colspan="2" class="tablecontent-odd">
			<select name="cust_usr_golongan_darah" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                    <option value="-">[ Pilih Golongan Darah ]</option>
                    <option value="A" <?php if("A"==$_POST["cust_usr_golongan_darah"]) echo "selected"; ?>>A</option>
                    <option value="B" <?php if("B"==$_POST["cust_usr_golongan_darah"]) echo "selected"; ?>>B</option>
                    <option value="AB" <?php if("AB"==$_POST["cust_usr_golongan_darah"]) echo "selected"; ?>>AB</option>
                    <option value="O" <?php if("O"==$_POST["cust_usr_golongan_darah"]) echo "selected"; ?>>O</option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td width= "20%" align="left" class="tablecontent">Tinggi Badan</td>
		<td width= "50%" align="left"  colspan="2" class="tablecontent-odd">
               <input  type="text" name="cust_usr_tinggi" size="7" maxlength="5" value="<?php echo $_POST["cust_usr_tinggi"];?>" onKeyDown="return tabOnEnter(this, event);"/> <label>cm</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<span class="tablecontent">Berat Badan</span>
               <input  type="text" name="cust_usr_berat" size="7" maxlength="5" value="<?php echo $_POST["cust_usr_berat"];?>" onKeyDown="return tabOnEnter(this, event);"/> <label>kg</label>
		</td>
	</tr>-->
	<tr>
		<td width= "20%" align="left" class="tablecontent">Upload Foto</td>
		<td width= "50%" align="left"  colspan="2" class="tablecontent-odd">
			<div id="loading" style="display:none;"><img id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></div> 
			<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="inputField" onclick="this.value=null;" onchange="return ajaxFileUpload();">
			<!--<button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload</button>-->
		</td>
	</tr>
	<?php //} ?>
	<!--<tr>
		<td width= "20%" align="left" class="tablecontent">Pekerjaan</td>
		<td width= "50%" align="left" class="tablecontent-odd" colspan="2" >
		  <select name="cust_usr_pekerjaan" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                    <option value="-">[ Pilih Pekerjaan ]</option>
                    <option value="Swasta" <?php if("Swasta"==$_POST["cust_usr_pekerjaan"]) echo "selected"; ?>>Swasta</option>
                    <option value="PNS" <?php if("PNS"==$_POST["cust_usr_pekerjaan"]) echo "selected"; ?>>PNS</option>
                    <option value="Wiraswasta" <?php if("Wiraswasta"==$_POST["cust_usr_pekerjaan"]) echo "selected"; ?>>Wiraswasta</option>
                    <option value="Pelajar" <?php if("Pelajar"==$_POST["cust_usr_pekerjaan"]) echo "selected"; ?>>Pelajar/Mahasiswa</option>
		    <option value="Pertanian" <?php if("Pertanian"==$_POST["cust_usr_pekerjaan"]) echo "selected"; ?>>Pertanian/Perkebunan</option>
		    <option value="Nelayan" <?php if("Nelayan"==$_POST["cust_usr_pekerjaan"]) echo "selected"; ?>>Nelayan</option>
		  </select>-->
               <!--<input  type="text" name="cust_usr_pekerjaan" size="30" maxlength="50" value="<?php //echo $_POST["cust_usr_pekerjaan"];?>" onKeyDown="return tabOnEnter(this, event);"/>-->
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Jenis Pasien</td>
		<td colspan="2" class="tablecontent-odd" >
		  <?php echo $view->RenderComboBox("cust_usr_jenis","cust_usr_jenis",$optUsrJenis,"inputfield",null,"onchange='view_sep(this.value);'");?>&nbsp;&nbsp;
		  <!-- user request on 14 Mar 14-->
		  <!-- added a check point to display SEP number-->
		  <span id="no_sep">
		    <?php
			if($_POST["reg_no_sep"] || $_POST["cust_usr_jenis"]=="12" || $_POST["cust_usr_jenis"]=="13" || $_POST["cust_usr_jenis"]=="14") echo "Nomor BPJS&nbsp;&nbsp;".$view->RenderTextBox("reg_no_kartubpjs","reg_no_kartubpjs",20,50,$_POST["reg_no_kartubpjs"],"inputField",null,false)."&nbsp;&nbsp;&nbsp;Nomor S.E.P.&nbsp;&nbsp;".$view->RenderTextBox("reg_no_sep","reg_no_sep",20,50,$_POST["reg_no_sep"],"inputField",null,false);
			else echo "&nbsp;";
		    ?>
		  </span>
		  <!-- end update-->
		</td>
	</tr>
	
	<?php if($_POST["cust_usr_jenis"]==PASIEN_DINASLUAR){?>
  <tr>
    <td class="tablecontent">Tanggal</td>
    <td class="tablecontent-odd" colspan="2"><?php echo $view->RenderTextBox("reg_dinasluar_tanggal","reg_dinasluar_tanggal","12","100",format_date($_POST["reg_dinasluar_tanggal"]),"inputField",null,false); ?> 
    <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_dinasluar" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" /></td>
	</tr>
  <tr>
    <td class="tablecontent">Kota</td>
    <td class="tablecontent-odd" colspan="2"><?php echo $view->RenderComboBox("reg_dinasluar_kota","reg_dinasluar_kota",$opt_kota,"inputField"); ?> </td>
  </tr>
  <script>
  Calendar.setup({
        inputField     :    "reg_dinasluar_tanggal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_dinasluar",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
  </script>
  <?php }?>
	<tr>
	  	<td class="tablecontent">Rujukan</td>
		<td colspan="2" class="tablecontent-odd" >
		<select name="reg_rujukan" id="reg_rujukan" onKeyDown="return tabOnEnter(this, event);" onchange="view_rujukan(this.value);"> 
			<option value="--" >[ Pilih Rujukan ]</option>
			<?php
			  for($i=0;$i<count($rujukan);$i++){
			?>
			  <option value="<?php echo $rujukan[$i]["rujukan_id"];?>" <?php if($_POST["reg_rujukan"]==$rujukan[$i]["rujukan_id"]) echo "selected";?>><?php echo $rujukan[$i]["rujukan_nama"];?></option>
			<?php } ?>
			</select>
			<!-- user request 14 Mar 2014 -->
			<!-- adding option if rujukan==opkom -->
			<span id="opkom_view">
			  <?php
				if($_POST["reg_opkom_jenis"] || $_POST["reg_rujukan"]=="8") {
				  $optOpkom[] = $view->RenderOption("Kiriman","Kiriman",($_POST["reg_opkom_jenis"]=="Kiriman")?"selected":"",null);
				  $optOpkom[] = $view->RenderOption("Luar","Luar Gedung",($_POST["reg_opkom_jenis"]=="Luar")?"selected":"",null);
				  echo $view->RenderComboBox("reg_opkom_jenis","reg_opkom_jenis",$optOpkom,"inputfield",null,null);
				}else echo "&nbsp;";
			    ?>
			</span>
			<!-- end update-->
			<span id="detail_rujukan" style="display:none;">
				&nbsp;&nbsp;Asal Rujukan:&nbsp;<?php if (readbit($err_code,5)) {?>&nbsp;<font color="red">(*)</font><?php } ?>
				<?php echo $view->RenderComboBox("id_rujukan_rs","id_rujukan_rs",$optRujukanID,"myselect",null,null) ?>
				&nbsp;<a href="<?php echo $findPage_rujukan;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Tambah Asal Rujukan">
					<img src="<?php echo($APLICATION_ROOT);?>images/b_insrow.png" border="0" width="12" height="14" style="cursor:pointer;margin-top: 4px;" title="Tambah Asal Rujukan" alt="Tambah Asal Rujukan" />
				</a>
				&nbsp;&nbsp;&nbsp;Dokter Penanggung Jawab Rujukan:&nbsp;<?php if (readbit($err_code,6)) {?>&nbsp;<font color="red">(*)</font><?php } ?><?php echo $view->RenderComboBox("id_rujukan_dokter","id_rujukan_dokter",$optRujukanDokterID,"myselect",null,null) ?>
				&nbsp;<a href="<?php echo $findPage_rujukan_dokter;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Tambah Dokter Perujuk">
					<img src="<?php echo($APLICATION_ROOT);?>images/b_insrow.png" border="0" width="12" height="14" style="cursor:pointer;margin-top: 4px;" title="Tambah Dokter Perujuk" alt="Tambah Dokter Perujuk" />
				</a>
			</span>
		</td>
	</tr>
	<!-- ditambahkan antrian pasien (refraksi, diagnostik, poli 5, lab) -->
	<tr>
		<td class="tablecontent">Antrian</td>
          <td colspan="2" class="tablecontent-odd" >
               <?php echo $view->RenderComboBox("cmbNext","cmbNext",$optionsNext,null,null,null);?>
          </td>
	</tr>
	<?php //if($_x_mode=='Edit'){?>	
  <tr>
		<td class="tablecontent">Keterangan Tambahan</td>
          <td colspan="2" class="tablecontent-odd" >
               <textarea name="reg_keterangan" id="reg_keterangan" rows="3" cols="65"><?php echo $_POST["reg_keterangan"];?></textarea>
          </td>
	</tr>
	<?php //}?>
     <tr>
		<td colspan="3" align="center" class="tablecontent-odd">&nbsp;</td>
	</tr>	
	<tr>
          <td colspan="3" align="center" class="tableheader">
              <input type="submit" name="<? echo ($_x_mode=="Edit")?"btnUpdate":"btnSave"; ?>" id="btnSave" value="Daftar" class="button" onclick="CheckUmur(document.getElementById('cust_usr_tanggal_lahir').value)" /><!-- onclick="CheckSimpan(frmEdit)" /> -->
              <input type="submit" name="btnCancel" id="btnCancel" value="Batal" class="button" />
          </td>
    </tr>
</table>

<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="cust_usr_id" id="cust_usr_id" value="<?php echo $_POST["cust_usr_id"];?>"/>
<input type="hidden" name="cust_id" value="<?php echo $_POST["cust_id"];?>"/>
<input type="hidden" name="nama" value="<?php echo $_POST["nama"];?>"/>
<input type="hidden" name="tipe" value="<?php echo $_POST["tipe"];?>"/>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,1)) { ?>
<br>
<font color="green"><strong>Nama pasien harap diisi.</strong></font>
<? } ?>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="green"><strong>Tanggal lahir pasien harap diisi.</strong></font>
<? } ?>
<? if (readbit($err_code,3)) { ?>
<br>
<font color="green"><strong>Alamat pasien harap diisi.</strong></font>
<? } ?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Rekam Medik harus diisi.</strong></font>
<? } ?>
<? if (readbit($err_code,14)) { ?>
<br>
<font color="green"><strong>Nomor KTP ditemukan sama dengan pasien dengan Nomor Rekam Medik <?php echo $data_ktp["cust_usr_kode"]; ?>.</strong></font>
<? } ?>
<? if (readbit($err_code,15)) { ?>
<br>
<font color="green"><strong>Nama, tanggal lahir dan alamat pasien ditemukan sama dengan pasien dengan Nomor Rekam Medik <?php echo $data_cekNama["cust_usr_kode"]; ?>.</strong></font>
<? } ?>
</span>
<script>document.frmEdit.cust_usr_kode.focus();</script>
</form>


<script type="text/javascript">
// ---tanggal lahir pegawai ---
    Calendar.setup({
        inputField     :    "cust_usr_tanggal_lahir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_lahir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>