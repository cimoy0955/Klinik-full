<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     require_once($APLICATION_ROOT."library/config/global.cfg.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     

 	if(!$auth->IsAllowed("kasir",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kasir",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
     
     $_x_mode = "New";
     $thisPage = "kasir_view.php";
     $findPage = "item_find.php?";
     $findPageOps = "operasi_find.php?";
     $findPageObat = "obat_find.php?";
     $findINAPageJalan = "ina_find.php?";
     $findINAPageInap = "ina_find_inap.php?";
     $findPasienFolio = "pasien_find_folio.php?";
     
     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetFolio");     
     
     function GetFolio() {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT,$rawatStatus,$auth,$bayarPasien; 
               
          $sql = "select a.id_reg, a.id_cust_usr, b.cust_usr_nama,a.fol_jenis,a.fol_waktu,id_biaya,a.fol_lunas,
		    b.cust_usr_jenis, z.reg_status, z.reg_tipe_rawat, z.reg_jenis_pasien
		    from klinik.klinik_folio a 
		    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
		    join klinik.klinik_registrasi z on z.reg_id = a.id_reg
		    where z.reg_status NOT IN ('I2','DM0') and z.reg_jenis_pasien='3' and  a.fol_lunas = 'n'
		    order by id_cust_usr asc";
	  $dataTable = $dtaccess->FetchAll($sql);
		      //return $sql;
	       $row = -1;
	       for($i=0,$n=count($dataTable);$i<$n;$i++) {
			 
		    if($dataTable[$i]["id_reg"]!=$dataTable[$i-1]["id_reg"]){
			 $row++;
			 $data[$row] = $dataTable[$i]["id_reg"];
			 $jenis[$dataTable[$i]["id_reg"]] = $dataTable[$i]["cust_usr_jenis"];
			 $reg[$dataTable[$i]["id_reg"]] = $dataTable[$i]["id_reg"];
			 $fol[$dataTable[$i]["id_reg"]][$row] = $dataTable[$i]["fol_jenis"];
			 $biaya[$dataTable[$i]["id_reg"]][$row] = $dataTable[$i]["id_biaya"]; 
			 $nama[$dataTable[$i]["id_reg"]] = $dataTable[$i]["cust_usr_nama"];
			 $waktu[$dataTable[$i]["id_reg"]] = $dataTable[$i]["fol_waktu"];
			 $regJenis[$dataTable[$i]["id_reg"]] = $dataTable["reg_jenis_pasien"];
		    }
	       }
		if($dataTable) asort($waktu);
          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Layanan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
	  
          $i=0; $nomor=1; $counter=0;
          #for($i=0,$nomor=1,$n=count($data),$counter=0;$i<$n;$i++,$counter=0) {
          if($waktu){
	       foreach($waktu as $key => $value){
		    $coba .= $key."&nbsp;".$value."&nbsp;".$nama[$key]."&nbsp;".$fol[$key][$i]."<br />";
			 $editPage = $thisPage."?jenis=".$fol[$key][$i]."&id_reg=".$reg[$key]."&waktu=".$value;
			 #if($data[$i]!=$data[$i-1]){
			 if($fol[$key][$i]==STATUS_REGISTRASI)
			      $editPage .= "&biaya=".$biaya[$key][$i];
			 if(($jenis[$key]==PASIEN_DINASLUAR)&&($auth->IsAllowed("dinas_luar",PRIV_CREATE)||$auth->IsAllowed("dinas_luar",PRIV_READ)||$auth->IsAllowed("dinas_luar",PRIV_UPDATE)||$auth->IsAllowed("dinas_luar",PRIV_DELETE))){
			     $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
			     $tbContent[$i][$counter][TABLE_ALIGN] = "center";
			     $counter++;
			 }elseif(($jenis[$key]==PASIEN_DINASLUAR)&&(!$auth->IsAllowed("dinas_luar",PRIV_CREATE)||!$auth->IsAllowed("dinas_luar",PRIV_READ)||!$auth->IsAllowed("dinas_luar",PRIV_UPDATE)||!$auth->IsAllowed("dinas_luar",PRIV_DELETE))){
			     $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";               
			     $tbContent[$i][$counter][TABLE_ALIGN] = "center";
			     $counter++;
			 }elseif($jenis[$key]!=PASIEN_DINASLUAR){
			      $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
			     $tbContent[$i][$counter][TABLE_ALIGN] = "center";
			     $counter++;
			 }
			     $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
			     $tbContent[$i][$counter][TABLE_ALIGN] = "right";
			     $counter++;
			     
			     $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$nama[$key];
			     $tbContent[$i][$counter][TABLE_ALIGN] = "left";
			     $counter++;
			     
			     $tbContent[$i][$counter][TABLE_ISI] = $value;
			     $tbContent[$i][$counter][TABLE_ALIGN] = "center";
			     $counter++;
			      
			     $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$rawatStatus[$fol[$key][$i]];
			     $tbContent[$i][$counter][TABLE_ALIGN] = "left";
			     $counter++;
			     
			     $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$bayarPasien[$jenis[$key]];
			     $tbContent[$i][$counter][TABLE_ALIGN] = "left";
			     $counter++;
			#}
			$counter=0; $i++;
	       }
	  }
          //return $coba;
          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
	  //return $sql;
	}
        
	  if($_POST["btnSaveTambah"]){
            
            $id_reg = $_POST["id_reg"];
            $skr = date("Y-m-d");

               $dbTable = "klinik.klinik_folio";
               
               $dbField[0] = "fol_id";   // PK
               $dbField[1] = "id_reg";
               $dbField[2] = "fol_nama";
               $dbField[3] = "fol_nominal";
               $dbField[4] = "id_biaya";
               $dbField[5] = "fol_jenis";
               $dbField[6] = "id_cust_usr";
               $dbField[7] = "fol_waktu";
               $dbField[8] = "fol_lunas";
               $dbField[9] = "fol_dibayar";
               $dbField[10] = "fol_dibayar_when";
               $dbField[11] = "fol_jumlah";
               $dbField[12] = "fol_nominal_satuan";
       
	       if($_POST["biaya_nama"]){
		    if(!$folioId) $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
		    $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
		    $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
		    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_nama"]);
		    $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotal"]));
		    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["biaya_id"]);
		    $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["biaya_jenis"]);
		    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
		    $dbValue[7] = QuoteValue(DPE_DATE,$skr);
		    $dbValue[8] = QuoteValue(DPE_CHAR,'n');
		    $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
		    $dbValue[10] = QuoteValue(DPE_DATE,'');
		    $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);
		    $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
     
		    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	
		    if ($_POST["btnSaveTambah"]) {
			 $dtmodel->Insert() or die("insert  error");	
		    
		    } else if ($_POST["btnUpdate"]) {
			 $dtmodel->Update() or die("update  error");	
		    }
		    
		    unset($dtmodel);
		    unset($dbValue);
		    unset($dbKey);
		    unset($folioId);
	       }
	       
	       if($_POST["obat_nama"]){
		    if(!$folioId) $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
		    $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
		    $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
		    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["obat_nama"]);
		    $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotalObat"]));
		    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["obat_id"]);
		    $dbValue[5] = QuoteValue(DPE_CHAR,"OB");
		    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
		    $dbValue[7] = QuoteValue(DPE_DATE,$skr);
		    $dbValue[8] = QuoteValue(DPE_CHAR,'n');
		    $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
		    $dbValue[10] = QuoteValue(DPE_DATE,'');
		    $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlahObat"]);
		    $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuanObat"]));
     
		    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	
		    if ($_POST["btnSaveTambah"]) {
			 $dtmodel->Insert() or die("insert  error");	
		    
		    } else if ($_POST["btnUpdate"]) {
			 $dtmodel->Update() or die("update  error");	
		    }
		    
		    unset($dtmodel);
		    unset($dbValue);
		    unset($dbKey); 
		    unset($folioId);
	       }
               
	       if($_POST["operasi_nama"]){
		    if(!$folioId) $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
		    $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
		    $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
		    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["operasi_nama"]);
		    $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotalOperasi"]));
		    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["operasi_id"]);
		    $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["operasi_jenis"]);
		    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
		    $dbValue[7] = QuoteValue(DPE_DATE,$skr);
		    $dbValue[8] = QuoteValue(DPE_CHAR,'n');
		    $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
		    $dbValue[10] = QuoteValue(DPE_DATE,'');
		    $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlahOperasi"]);
		    $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuanOperasi"]));

		    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

		    if ($_POST["btnSaveTambah"]) {
			 $dtmodel->Insert() or die("insert  error");	
		    
		    } else if ($_POST["btnUpdate"]) {
			 $dtmodel->Update() or die("update  error");	
		    }
		    
		    unset($dtmodel);
		    unset($dbValue);
		    unset($dbKey); 
		    unset($folioId);
	       }
	       
	       unset($dbField);
               
	       $editPage = $thisPage."?jenis=".$_POST["fol_jenis"]."&id_reg=".$id_reg."&biaya=".$_POST["biaya_id"]."#tambahFol";
               header("location:".$editPage);
               exit();                
     }
     
     if($_POST["btnTambahINA"]){
	  $id_reg = $_POST["id_reg"];
	  $skr = date("Y-m-d");

	  $dbTable = "klinik.klinik_folio";
               
	  $dbField[0] = "fol_id";   // PK
	  $dbField[1] = "id_reg";
	  $dbField[2] = "fol_nama";
	  $dbField[3] = "fol_nominal";
	  $dbField[4] = "id_biaya";
	  $dbField[5] = "fol_jenis";
	  $dbField[6] = "id_cust_usr";
	  $dbField[7] = "fol_waktu";
	  $dbField[8] = "fol_lunas";
	  $dbField[9] = "fol_dibayar";
	  $dbField[10] = "fol_dibayar_when";
	  $dbField[11] = "fol_jumlah";
	  $dbField[12] = "fol_nominal_satuan";
	  if($_POST["rd_jenis_rawat"]=="rawat_inap") $dbField[3] = "fol_ina_kelas";
	  
	  if(!$folioINAId) $folioINAId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
	  $dbValue[0] = QuoteValue(DPE_CHAR,$folioINAId);
	  $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
	  $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["ina_nama"]);
	  $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["ina_nominal"]));
	  $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["ina_id"]);
	  $dbValue[5] = QuoteValue(DPE_CHAR,"IC");
	  $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
	  $dbValue[7] = QuoteValue(DPE_DATE,$skr);
	  $dbValue[8] = QuoteValue(DPE_CHAR,'n');
	  $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
	  $dbValue[10] = QuoteValue(DPE_DATE,'');
	  $dbValue[11] = QuoteValue(DPE_NUMERIC,'1');
	  $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["ina_nominal"]));
	  
	  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

	  if ($_POST["btnTambahINA"]) {
	       $dtmodel->Insert() or die("insert  error");
	  }
     }
     
     if ($_POST["btnDelete"]) {
          $folId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($folId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_folio  
                         where fol_id = ".QuoteValue(DPE_CHAR,$folId[$i]);
               $dtaccess->Execute($sql);
          }
          
          $editPage = $thisPage."?jenis=".$_POST["fol_jenis"]."&id_reg=".$_POST["id_reg"]."&biaya=".$_POST["biaya_id"]."#tagihan";
	    header("location:".$editPage);
	    exit();
     }
	
	if($_GET["id_reg"]) {
		$sql = "select a.reg_jenis_pasien,a.reg_status, cust_usr_alamat, cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin,b.cust_usr_alergi,
				((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr
                    from klinik.klinik_registrasi a 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          //echo $sql;
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["fol_jenis"] = $_GET["jenis"]; 
		$_POST["id_biaya"] = $_GET["biaya"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		$_POST["reg_status"] = $dataPasien["reg_status"];
		
	       
	  //$sql = "select * from klinik.klinik_folio
		//	where fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])."
		//	and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n' "; 
		$sql_fol = "select * from klinik.klinik_folio
			where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n'  order by fol_waktu asc"; 
		//if($_POST["id_biaya"]) {
		//	$sql .= " and id_biaya = ".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
		//}
		//echo $sql_fol;
		$rs = $dtaccess->Execute($sql_fol);
		$dataFolio = $dtaccess->FetchAll($rs);
	  //echo $sql
	//
	//	$sql = "select b.icd_nomor, b.icd_nama
	//			from klinik.klinik_perawatan_icd a
	//			join klinik.klinik_icd b on a.id_icd = b.icd_nomor
	//			left join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
	//			where c.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
	//			and a.rawat_icd_odos = 'OD' 
	//			order by a.rawat_icd_urut";
	//	$dataDiagIcdOD = $dtaccess->FetchAll($sql);
	//	for($i=0;$i<count($dataDiagIcdOD);$i++){
	//	    $_POST["rawat_icd_od_kode"][$i] = $dataDiagIcdOD[$i]["icd_nomor"];
	//	    $_POST["rawat_icd_od_nama"][$i] = $dataDiagIcdOD[$i]["icd_nama"];
	//	}
	////echo $sql."<br />";
	//	$sql = "select b.icd_nomor, b.icd_nama
	//			from klinik.klinik_perawatan_icd a
	//			join klinik.klinik_icd b on a.id_icd = b.icd_nomor
	//			left join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
	//			where c.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
	//			and a.rawat_icd_odos = 'OS' 
	//			order by a.rawat_icd_urut";
	//	$dataDiagIcdOS = $dtaccess->FetchAll($sql);
	//	for($i=0;$i<count($dataDiagIcdOS);$i++){
	//	    $_POST["rawat_icd_os_kode"][$i] = $dataDiagIcdOS[$i]["icd_nomor"];
	//	    $_POST["rawat_icd_os_nama"][$i] = $dataDiagIcdOS[$i]["icd_nama"];
	//	}
	////echo $sql;
	//       $sql = "select b.prosedur_kode, b.prosedur_nama
	//		 from klinik.klinik_perawatan_prosedur a
	//		 join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode
	//		 left join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
	//		 where c.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
	//		 order by a.rawat_prosedur_urut";
	//       $dataProsedur = $dtaccess->FetchAll($sql);
	//       for($i=0;$i<count($dataProsedur);$i++){
	//	    $_POST["rawat_prosedur_kode"][$i] = $dataProsedur[$i]["prosedur_kode"];
	//	    $_POST["rawat_prosedur_nama"][$i] = $dataProsedur[$i]["prosedur_nama"];
	//       }
	  
	}

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
	  
	  $sql = "select a.id_kwitansi , b.kwitansi_nomor from klinik.klinik_folio a
	  join global.global_kwitansi b on b.kwitansi_id = a.id_kwitansi 
	  where a.id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]) ;
	  $dataKWT = $dtaccess->Fetch($sql);
	 
	  $_POST["kwitansi_id"] = $dataKWT['id_kwitansi'];
	  $_POST["kwitansi_nomor"] = $dataKWT['kwitansi_nomor'];
     
	  if(!$_POST["kwitansi_id"]) {
	     $akhirKwit = $dtaccess->GetNewID("global_kwitansi","kwitansi_nomor",DB_SCHEMA_GLOBAL);
	     
	     if($akhirKwit==1){
	     $awalKwit = 11000187;
	     }
	     $_POST["kwitansi_nomor"] = $awalKwit + $akhirKwit;  
		     
	     $dbTable = "global_kwitansi";
			     
	       $dbField[0] = "kwitansi_id";   // PK
	       $dbField[1] = "kwitansi_nomor";
	       $dbField[2] = "id_reg";
				     
	     if(!$_POST["kwitansi_id"]) $_POST["kwitansi_id"] = $dtaccess->GetNewID("global_kwitansi","kwitansi_id",DB_SCHEMA_GLOBAL);	  
	       $dbValue[0] = QuoteValue(DPE_NUMERIC,$_POST["kwitansi_id"]);
	       $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kwitansi_nomor"]);
	       $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
     
	       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
	       
	       $dtmodel->Insert() or die("insert error"); 
	       
	       unset($dtmodel);
	       unset($dbField);
	       unset($dbValue);
	       unset($dbKey);    
	  }
         
	  $sql = "update klinik.klinik_folio set fol_dibayar = fol_nominal, fol_lunas = 'y', fol_dibayar_when = CURRENT_TIMESTAMP, id_kwitansi = ".QuoteValue(DPE_NUMERIC,$_POST["kwitansi_id"])." where  id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas='n'"; //fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])." and
	  $dtaccess->Execute($sql);

	  $sql = "update klinik.klinik_registrasi set reg_waktu = CURRENT_TIME where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	  $dtaccess->Execute($sql);

	  if($_POST["fol_jenis"]=="D") {
	       $sql = "update klinik.klinik_registrasi set reg_status = 'D1' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	       $dtaccess->Execute($sql);
	  }

	  if($_POST["reg_status"]=="C0"){
	       $sql = "update klinik.klinik_registrasi set reg_status = 'E0' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	       $dtaccess->Execute($sql);
	  }
	  
	  $_x_mode = "Save";
	  //echo "<script>CheckSimpan('".$_POST["id_reg"]."','".$_POST["kwitansi_id"]."');</script>";
	}
	

     
	if($_POST["btnHapus"]) { 
		$sql = "delete from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql);
	    
     }

?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>

<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetFolio('target=antri_kiri_isi');     
     mTimer = setTimeout("timer()", 10000);
}

timer();

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=1000,top=35;left=150');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=1000,top=35;left=150');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}

var _wnd_new2;

function BukaWindow2(url,judul)
{
    if(!_wnd_new2) {
			_wnd_new2 = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=1000,top=35;left=150');
	} else {
		if (_wnd_new2.closed) {
			_wnd_new2 = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=1000,top=35;left=150');
		} else {
			_wnd_new2.focus();
		}
	}
     return false;
}

function CheckDataSave(frm) {
     
     if(document.getElementById('rdMemberTipe_M').checked) {
          if(!document.getElementById('id_member').value) {
               alert('Member harap dipilih');
               return false;
          }
     }
     
     if(document.getElementById('txtLama').value==0) {
          alert('Waktu bermain tidak boleh kosong (0)');
          return false;
     }

     
     if(document.getElementById('txtHargaTotal').value==0) {
          alert('Harga Total tidak boleh kosong (0)');
          return false;
     }
     
     if (document.getElementById('spNamaGuest').style.visibility == 'visible') {
     if(document.getElementById('txtNama').value==0) {
          alert('Nama Guest tidak boleh kosong');
          return false;
     }
          
     if(CheckData(frm.txtNama.value,frm.item_id.value,'type=r')){
      	alert('Nama Guest Sudah Ada Sudah Ada');
    	 	frm.txtNama.focus();
    		frm.txtNama.select();
    		return false;
    	}
      }	
     return true;

}

function CheckSimpan(idreg,idkwitansi) {
     if(confirm('Cetak Invoice?')) {
	   BukaWindow('kasir_cetak.php?nokwitansi='+idkwitansi+'&id_reg='+idreg+'','Invoice');
     }
     return true;
}

function GantiHargaItem(jml,hrg) {
     var duit = hrg.toString().replace(/\,/g,"");
     document.getElementById('txtHargaTotal').value = formatCurrency(duit*jml);
}

function GantiHargaObat(jml,hrg) {
     var duit = hrg.toString().replace(/\,/g,"");
     document.getElementById('txtHargaTotalObat').value = formatCurrency(duit*jml);
}

//-------------autocomplete-------------//
	
var drz01;
function lihat01(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest01").style.visibility = "hidden";
    }else{
        drz01 = buatajax01();
        var url="cari0.php";
        drz01.onreadystatechange=stateChanged01;
        var params = "q="+eval;
        drz01.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz01.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz01.setRequestHeader("Content-length", params.length);
        drz01.setRequestHeader("Connection", "close");
        drz01.send(params);
    }

}

function buatajax01(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged01(){

	var data;
    if (drz01.readyState==4 && drz01.status==200){
        data=drz01.responseText;
        if(data.length>0){
            document.getElementById("kotaksugest01").innerHTML = data;
            document.getElementById("kotaksugest01").style.visibility = "";
        }else{
            document.getElementById("kotaksugest01").innerHTML = "";
            document.getElementById("kotaksugest01").style.visibility = "hidden";
        }
    }
}

function isi01(id,kode,nama,total){

    document.getElementById("biaya_id").value = id;
    document.getElementById("biaya_nama").value = nama;
    document.getElementById("biaya_kode").value = kode;
    document.getElementById("txtJumlah").value = "1";
    document.getElementById("txtHargaSatuan").value = formatCurrency(total);
    document.getElementById("txtHargaTotal").value = formatCurrency(total);
    document.getElementById("kotaksugest01").style.visibility = "hidden";
    document.getElementById("kotaksugest01").innerHTML = "";
}

var drz02;
function lihat02(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest02").style.visibility = "hidden";
    }else{
        drz02 = buatajax02();
        var url="cari02.php";
        drz02.onreadystatechange=stateChanged02;
        var params = "q="+eval;
        drz02.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz02.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz02.setRequestHeader("Content-length", params.length);
        drz02.setRequestHeader("Connection", "close");
        drz02.send(params);
    }

}

function buatajax02(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged02(){

	var data;
    if (drz02.readyState==4 && drz02.status==200){
        data=drz02.responseText;
        if(data.length>0){
            document.getElementById("kotaksugest02").innerHTML = data;
            document.getElementById("kotaksugest02").style.visibility = "";
        }else{
            document.getElementById("kotaksugest02").innerHTML = "";
            document.getElementById("kotaksugest02").style.visibility = "hidden";
        }
    }
}

function isi02(id,kode,nama,total){

    document.getElementById("obat_id").value = id;
    document.getElementById("obat_nama").value = nama;
    document.getElementById("obat_kode").value = kode;
    document.getElementById("txtJumlahObat").value = "1";
    document.getElementById("txtHargaSatuanObat").value = formatCurrency(total);
    document.getElementById("txtHargaTotalObat").value = formatCurrency(total);
    document.getElementById("kotaksugest02").style.visibility = "hidden";
    document.getElementById("kotaksugest02").innerHTML = "";
}

var drz03;
function lihat03(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest03").style.visibility = "hidden";
    }else{
        drz03 = buatajax03();
        var url="cari03.php";
        drz03.onreadystatechange=stateChanged03;
        var params = "q="+eval;
        drz03.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz03.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz03.setRequestHeader("Content-length", params.length);
        drz03.setRequestHeader("Connection", "close");
        drz03.send(params);
    }

}

function buatajax03(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged03(){

	var data;
    if (drz03.readyState==4 && drz03.status==200){
        data=drz03.responseText;
        if(data.length>0){
            document.getElementById("kotaksugest03").innerHTML = data;
            document.getElementById("kotaksugest03").style.visibility = "";
        }else{
            document.getElementById("kotaksugest03").innerHTML = "";
            document.getElementById("kotaksugest03").style.visibility = "hidden";
        }
    }
}

function isi03(id,kode,nama,total){

    document.getElementById("obat_id").value = id;
    document.getElementById("obat_nama").value = nama;
    document.getElementById("obat_kode").value = kode;
    document.getElementById("txtJumlahObat").value = "1";
    document.getElementById("txtHargaSatuanObat").value = formatCurrency(total);
    document.getElementById("txtHargaTotalObat").value = formatCurrency(total);
    document.getElementById("kotaksugest03").style.visibility = "hidden";
    document.getElementById("kotaksugest03").innerHTML = "";
}

function cekTambahFolio(){
	if ((document.getElementById("item_id").value == '' || document.getElementById("item_id").value == null) && (document.getElementById("obat_id").value == '' || document.getElementById("obat_id").value == null) && document.getElementById("operasi_id").value == '' || document.getElementById("operasi_id").value == null) {
		alert("Isian tagihan masih kosong. Silahkan pilih tagihan sesuai dengan jenisnya.");
		return false;
	}
}


<?php if($_x_mode=="Save"){ ?>
     BukaWindow('kasir_cetak.php?nokwitansi=<?php echo $_POST["kwitansi_id"];?>&id_reg=<?php echo $_POST["id_reg"];?>&jp=1','Invoice');
     document.location.href='<?php echo $thisPage;?>';
<?php } ?>
</script>

<!--  -->
<div id="antri_main" style="width:100%;height:auto;clear:both;">
	<div class="tableheader">Antrian Kasir</div>
	<div style="margin:10px auto 5px 7px;"><a href="<?php echo $findPasienFolio;?>&jenis=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Tambah Pasien"><img src="<?php echo $ROOT;?>images/bnplus.gif" />Tambah Pasien</a></div>
	<div id="antri_kiri_isi" style="height:100px;"><?php //echo GetFolio(); ?></div>
</div>
<?php if($dataPasien) { ?>
<div style="margin-top: 15px;">
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Pembayaran</td>
	</tr>
</table> 

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" autocomplete="off" >
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
               <td width= "20%" align="left" class="tablecontent">Alamat</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo nl2br($dataPasien["cust_usr_alamat"]); ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Bayar</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $bayarPasien[$dataPasien["reg_jenis_pasien"]]; ?></label></td>
          </tr>
	   </table>
     </fieldset>
     
     <fieldset>
     <legend><strong><a id="tambahFol">Tambah</a></strong></legend>
     <table border="0">
	  <tr>
	       <td>
		    <div style="float: left">
		    <fieldset>
		    <legend>Layanan</legend>
		    <table width="100%" border="0" cellpadding="1" cellspacing="1">
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Kode Biaya&nbsp;</td>
				<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("biaya_kode","biaya_kode","10","100",$_POST["biaya_kode"],"inputField",null,false,"onkeyup=\"lihat01(this.value);\""); ?>
				  <a href="<?php echo $findPage?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Pilih Item">
				  <img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" /></a>
				  <div id="kotaksugest01" style="position:absolute; background-color:#eeeeee;width:410px;visibility:hidden;z-index:100">
				       </div>
				</td>
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Item&nbsp;</td>
				<td align="left" class="tablecontent-odd">
				  <?php echo $view->RenderTextBox("biaya_nama","biaya_nama","30","100",$_POST["biaya_nama"],"inputField", null,false);?>    
				  <input type="hidden" name="biaya_id" id="biaya_id" value="<?php echo $_POST["biaya_id"];?>" />               
								<input type="hidden" name="biaya_jenis" id="biaya_jenis" value="<?php echo $_POST["biaya_jenis"];?>" />
			  </td>
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Jumlah</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtJumlah","txtJumlah","3","3",$_POST["txtJumlah"],"curedit", "autocomplete=\"off\"",true,'onkeyup="GantiHargaItem(this.value,document.getElementById(\'txtHargaSatuan\').value);"');?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaSatuan","txtHargaSatuan","10","10",$_POST["txtHargaSatuan"],"curedit", "autocomplete=\"off\"",true,'onkeyup="GantiHargaItem(document.getElementById(\'txtJumlah\').value,this.value);"');?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Total Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaTotal","txtHargaTotal","10","10",$_POST["txtHargaTotal"],"curedit", "autocomplete=\"off\" readonly",true,'onfocus="GantiHargaItem(document.getElementById(\'txtJumlah\').value,document.getElementById(\'txtHargaSatuan\').value);"');?>
				</td>					
			 </tr>
		    </table>
		    </fieldset>
		    </div>
		    <div style="float: left">
		    <fieldset>
		    <legend>Obat</legend>
		    <table width="100%" border="0" cellpadding="1" cellspacing="1">
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Kode Biaya Obat&nbsp;</td>
				<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("obat_kode","obat_kode","10","100",$_POST["obat_kode"],"inputField","autocomplete=\"off\"",false,"onkeyup=\"lihat02(this.value);\""); ?>
				  <a href="<?php echo $findPageObat?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Item">
				  <img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" /></a>
				  <div id="kotaksugest02" style="position:absolute; background-color:#eeeeee;width:420px;visibility:hidden;z-index:100">
				       </div>
				</td>
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Item&nbsp;</td>
				<td align="left" class="tablecontent-odd">
				  <?php echo $view->RenderTextBox("obat_nama","obat_nama","30","100",$_POST["obat_nama"],"inputField", null,false);?>    
				  <input type="hidden" name="obat_id" id="obat_id" value="<?php echo $_POST["obat_id"];?>" />               
								<input type="hidden" name="obat_jenis" id="obat_jenis" value="<?php echo $_POST["obat_jenis"];?>" />
			  </td>
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Jumlah</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtJumlahObat","txtJumlahObat","3","3",$_POST["txtJumlahObat"],"curedit", "autocomplete=\"off\"",true,'onkeyup="GantiHargaObat(this.value,document.getElementById(\'txtHargaSatuanObat\').value);"');?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaSatuanObat","txtHargaSatuanObat","10","10",$_POST["txtHargaSatuanObat"],"curedit", null,true,'onkeyup="GantiHargaObat(document.getElementById(\'txtJumlahObat\').value,this.value);"');?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Total Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaTotalObat","txtHargaTotalObat","10","10",$_POST["txtHargaTotalObat"],"curedit", "readonly",true,'onfocus="GantiHargaObat(document.getElementById(\'txtJumlahObat\').value,document.getElementById(\'txtHargaSatuanObat\').value);"');?>
				</td>					
			 </tr>
		    </table>
		    </fieldset>
		    </div>
		    <div style="float: left">
		    <fieldset>
		    <legend>Operasi</legend>
		    <table width="100%" border="0" cellpadding="1" cellspacing="1">
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Kode Biaya Operasi&nbsp;</td>
				<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("operasi_kode","operasi_kode","10","100",$_POST["operasi_kode"],"inputField",null,false,"onkeyup=\"lihat03(this.value);\""); ?>
				  <a href="<?php echo $findPageOps?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Item">
				  <img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" /></a>
				  <div id="kotaksugest03" style="position:absolute; background-color:#eeeeee;width:420px;visibility:hidden;z-index:100">
				       </div>
				</td>
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Item&nbsp;</td>
				<td align="left" class="tablecontent-odd">
				  <?php echo $view->RenderTextBox("operasi_nama","operasi_nama","30","100",$_POST["operasi_nama"],"inputField", null,false);?>    
				  <input type="hidden" name="operasi_id" id="operasi_id" value="<?php echo $_POST["operasi_id"];?>" />               
								<input type="hidden" name="operasi_jenis" id="operasi_jenis" value="<?php echo $_POST["operasi_jenis"];?>" /><?php echo $view->RenderHidden("txtJumlahOperasi","txtJumlahOperasi","1");?>
			  </td>
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaSatuanOperasi","txtHargaSatuanOperasi","10","10",$_POST["txtHargaSatuanOperasi"],"curedit", "readonly",true);?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Total Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaTotalOperasi","txtHargaTotalOperasi","10","10",$_POST["txtHargaTotalOperasi"],"curedit", "readonly",true);?>
				</td>					
			 </tr>
		    </table>
		    </fieldset>
		    </div>
		    <input type="submit" name="btnSaveTambah" value="Tambah Biaya" class="button" style="float: right"  onsubmit="return cekTambahFolio();">
		    <input type="hidden" name="fol_jenis" id="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>" />  
		    <input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>    
		    <input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/> 
		    <input type="hidden" name="waktunya" value="<?php echo $_GET["waktu"];?>" />
		    <input type="hidden" name="id_biaya" value="<?php echo $_GET["biaya"];?>" />
		    <input type="hidden" name="id_obat" value="<?php echo $_GET["obat"];?>" />
		    <input type="hidden" name="id_biaya_operasi" value="<?php echo $_GET["biaya_operasi"];?>" />
	       </td>
	  </tr>
     </table>
      
     </fieldset> 
     
     <fieldset>
     <legend><strong><a id="tagihan">Data Tagihan</a></strong></legend>
     <table width="80%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
		   <td width="3%" align="center"><input type="checkbox" onClick="EW_selectKey(this,'cbDelete[]');" /></td>
               <td width="5%" align="center">No</td>
               <td width="30%" align="center">Layanan</td>
               <td width="10%" align="center">Jumlah</td>
               <td width="20%" align="center">Biaya</td>
               <td width="20%" align="center">Total</td>
          </tr>	
          <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { $total+=$dataFolio[$i]["fol_nominal"];?>
			<tr>
				 <td align="right" class="tablecontent"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataFolio[$i]["fol_id"];?>" /></td>
				<td align="right" class="tablecontent"><?php echo ($i+1); ?></td>
				<td align="left" class="tablecontent-odd"><?php echo $dataFolio[$i]["fol_nama"];?></td>
				<td align="right" class="tablecontent-odd"><?php echo $dataFolio[$i]["fol_jumlah"];?></td>
				<td align="right" class="tablecontent-odd"><?php echo currency_format($dataFolio[$i]["fol_nominal_satuan"]);?></td>
				<td align="right" class="tablecontent-odd"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
			</tr>
		<?php } ?>
          <tr>
               <td align="right" class="tablesmallheader" colspan="5">Total</td>
               <td align="right" class="tablesmallheader"><?php echo currency_format($total);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="left">
			 <input type="submit" name="btnDelete" value="Hapus" class="button" />
			 <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Bayar","button",false);?>
				<?php //echo $view->RenderButton(BTN_BUTTON,"btnPrint","btnPrint","Cetak","button",false,'onClick="BukaWindow(\'kasir_cetak.php?jenis='.$_POST["fol_jenis"].'&id_reg='.$_POST["id_reg"].'\',\'Cetak Invoice\')"',null);?>
				<?php if($_POST["fol_jenis"] == STATUS_REGISTRASI) { ?>
				      <?php echo $view->RenderButton(BTN_SUBMIT,"btnHapus" ,"btnHapus","Batal Registrasi","button",false,null);?>
			       <?php } ?>
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
<input type="hidden" name="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>"/>
<input type="hidden" name="reg_status" value="<?php echo $_POST["reg_status"];?>"/>
       
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
</div>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
