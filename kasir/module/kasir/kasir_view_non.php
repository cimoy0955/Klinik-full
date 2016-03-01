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
     $thisPage = "kasir_view_non.php";
     $findPage = "item_find.php?";
     $findPageOps = "operasi_find.php?";
     $findPageObat = "obat_find_non.php?";
     $findINAPageJalan = "ina_find.php?";
     $findINAPageInap = "ina_find_inap.php?";
     $icdPage = "icd_find.php?";
     $procPage = "proc_find.php?";
     $findPasienFolio = "pasien_find_folio.php?";
     
     $tableRefraksi = new InoTable("table1","99%","center");

     if ($_GET["currentPage"]) {
     	$offset = ($_GET["currentPage"] - 1) * 20;
     	$currnt_page = $_GET["currentPage"];
     }else{
     	$offset = 0;
     	$currnt_page = 1;
     }
     


     $plx = new InoLiveX("GetFolio,GetSpecialTariff");     
     
     function GetFolio() {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT,$rawatStatus,$auth,$bayarPasien,$offset,$currnt_page; 
               
          $sql = "select a.id_reg, a.id_cust_usr, b.cust_usr_nama,a.fol_jenis,a.fol_waktu,id_biaya,a.fol_lunas,
		    b.cust_usr_jenis, z.reg_status, z.reg_tipe_rawat, z.reg_jenis_pasien
		    from klinik.klinik_folio a 
		    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
		    join klinik.klinik_registrasi z on z.reg_id = a.id_reg
		    where z.reg_status NOT IN  ('I2','DM0') and z.reg_jenis_pasien<>'3' and  a.fol_lunas = 'n'
		    order by id_cust_usr asc
		    limit 20 offset ".$offset;
		    $rs = $dtaccess->Execute($sql);
	  		$dataTable = $dtaccess->FetchAll($rs);

	  	$sql_num = "select a.id_reg, a.id_cust_usr, b.cust_usr_nama,a.fol_jenis,a.fol_waktu,id_biaya,a.fol_lunas,
		    b.cust_usr_jenis, z.reg_status, z.reg_tipe_rawat, z.reg_jenis_pasien
		    from klinik.klinik_folio a 
		    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
		    join klinik.klinik_registrasi z on z.reg_id = a.id_reg
		    where z.reg_status NOT IN  ('I2','DM0') and z.reg_jenis_pasien<>'3' and  a.fol_lunas = 'n'";
		$rs_num = $dtaccess->Execute($sql_num);
		$row_num = $dtaccess->RowCount($rs_num);
		      #return $sql;
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
	  	$tbBottom[0][0][TABLE_ISI] = $view->RenderPaging($row_num,20,$currnt_page);
	  	$tbBottom[0][0][TABLE_ALIGN] = "left";
	  	$tbBottom[0][0][TABLE_COLSPAN] = $counterHeader;
          #return $coba;
          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}
	
	function GetSpecialTariff($id){
	  global $dtaccess;
	  
	  $sql = "select * from klinik.klinik_specialtariff where sptarif_id='$id'";
	  $rs = $dtaccess->Execute($sql);
	  $dataTariff = $dtaccess->Fetch($rs);
	  return $dataTariff["name"]."+".$dataTariff["kode"]."+".$dataTariff["reg1"];
	  //return $sql;
	}
        	
	  /*
	   * bagian simpan tambah folio
	   */
	  
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
	  /* end of tambah folio */
     
     /*
      * bagian tambah INA CBG ke tabel folio
      */
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
	  
	  if(!$folioINAId) $folioINAId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
	  $dbValue[0] = QuoteValue(DPE_CHAR,$folioINAId);
	  $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
	  $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["ina_nama"]);
	  $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["ina_nominal"]));
	  $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["ina_kode"]);
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
	  
	  unset($dbTable);
	  unset($dbField);
	  unset($dbValue);
	  unset($dbKey);
	  unset($dtmodel);
	  
	  /*
	   * update data ICD & PROSEDUR
	   */
	  $sql_rawat = "select rawat_id from klinik.klinik_perawatan where id_reg=".QuoteValue(DPE_CHAR,$id_reg);
	  $rs_rawat = $dtaccess->Execute($sql_rawat);
	  $dataRawatICD = $dtaccess->Fetch($rs_rawat);
	  
	  // -- ini insert ke tabel rawat icd
		$sql = "delete from klinik.klinik_perawatan_icd where id_rawat = ".QuoteValue(DPE_CHAR,$dataRawatICD["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_icd";
          $dbField[0] = "rawat_icd_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_icd";
          $dbField[3] = "rawat_icd_urut";
          $dbField[4] = "rawat_icd_odos";
          
          for($i=0,$n=count($_POST["rawat_icd_od_kode"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawatICD["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_od_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OD");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_od_kode"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
	  
          for($i=0,$n=count($_POST["rawat_icd_os_kode"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawatICD["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_os_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OS");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_os_kode"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
	  // -- end of insert ICD
	  unset($dbTable);

	  //-- insert ke tabel rawat prosedur --//
	  $sql = "delete from klinik.klinik_perawatan_prosedur where id_rawat = ".QuoteValue(DPE_CHAR,$dataRawatICD["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_prosedur";
          $dbField[0] = "rawat_prosedur_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_prosedur";
          $dbField[3] = "rawat_prosedur_urut";
          
          for($i=0,$n=count($_POST["rawat_prosedur_kode"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawatICD["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_prosedur_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_prosedur_kode"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
  	  unset($dbTable);

	  // -- end of insert prosedur	  
	  /* end of update ICD & PROSEDUR*/
	  
	  $editPage = $thisPage."?jenis=".$_POST["fol_jenis"]."&id_reg=".$id_reg."&biaya=".$_POST["biaya_id"]."#tambahINA";
	  header("location:".$editPage);
	  exit(); 
     }
     /* end of tambah INA CBG */
     
     /*
      * bagian tambah tagihan special procedure
      */
     if($_POST["btnSaveSpcProc"]){
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
	       
	       if($_POST["spc_proc_id"]!="--"){
		    $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
		    $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
		    $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
		    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["spc_proc_nama"]);
		    $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["spc_proc_nominal"]));
		    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["spc_proc_id"]);
		    $dbValue[5] = QuoteValue(DPE_CHAR,'SP');
		    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
		    $dbValue[7] = QuoteValue(DPE_DATE,$skr);
		    $dbValue[8] = QuoteValue(DPE_CHAR,'n');
		    $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
		    $dbValue[10] = QuoteValue(DPE_DATE,'');
		    $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["spc_proc_jml"]);
		    $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["spc_proc_nominal"]));
     
		    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	
		    if ($_POST["btnSaveSpcProc"]) {
			 $dtmodel->Insert() or die("insert  error");	
		    }               
		    unset($dtmodel);
		    unset($dbValue);
		    unset($dbKey); 
	       }
	       
	       if($_POST["spc_pros_id"]!="--"){
		    $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
		    $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
		    $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
		    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["spc_pros_nama"]);
		    $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["spc_pros_nominal"]));
		    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["spc_pros_id"]);
		    $dbValue[5] = QuoteValue(DPE_CHAR,'SP');
		    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
		    $dbValue[7] = QuoteValue(DPE_DATE,$skr);
		    $dbValue[8] = QuoteValue(DPE_CHAR,'n');
		    $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
		    $dbValue[10] = QuoteValue(DPE_DATE,'');
		    $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["spc_pros_jml"]);
		    $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["spc_pros_nominal"]));
     
		    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	
		    if ($_POST["btnSaveSpcProc"]) {
			 $dtmodel->Insert() or die("insert  error");	
		    }               
		    unset($dtmodel);
		    unset($dbValue);
		    unset($dbKey); 
	       }
	       
	       if($_POST["spc_drug_id"]!="--"){
		    $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
		    $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
		    $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
		    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["spc_drug_nama"]);
		    $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["spc_drug_nominal"]));
		    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["spc_drug_id"]);
		    $dbValue[5] = QuoteValue(DPE_CHAR,'SP');
		    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
		    $dbValue[7] = QuoteValue(DPE_DATE,$skr);
		    $dbValue[8] = QuoteValue(DPE_CHAR,'n');
		    $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
		    $dbValue[10] = QuoteValue(DPE_DATE,'');
		    $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["spc_drug_jml"]);
		    $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["spc_drug_nominal"]));
     
		    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	
		    if ($_POST["btnSaveSpcProc"]) {
			 $dtmodel->Insert() or die("insert  error");	
		    }               
		    unset($dtmodel);
		    unset($dbValue);
		    unset($dbKey); 
	       }
	       
	       if($_POST["spc_inv_id"]!="--"){
		    $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
		    $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
		    $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
		    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["spc_inv_nama"]);
		    $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["spc_inv_nominal"]));
		    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["spc_inv_id"]);
		    $dbValue[5] = QuoteValue(DPE_CHAR,'SP');
		    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
		    $dbValue[7] = QuoteValue(DPE_DATE,$skr);
		    $dbValue[8] = QuoteValue(DPE_CHAR,'n');
		    $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
		    $dbValue[10] = QuoteValue(DPE_DATE,'');
		    $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["spc_inv_jml"]);
		    $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["spc_inv_nominal"]));
     
		    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	
		    if ($_POST["btnSaveSpcProc"]) {
			 $dtmodel->Insert() or die("insert  error");	
		    }               
		    unset($dtmodel);
		    unset($dbValue);
		    unset($dbKey); 
	       }
	       unset($dbField);
               $editPage = $thisPage."?jenis=".$_POST["fol_jenis"]."&id_reg=".$id_reg."&biaya=".$_POST["biaya_id"]."#tariff_spc";
               header("location:".$editPage);
               exit();                
     }
     
     /* end of tambah special procedure */
     
     /*
      * bagian delete from tabel folio
      */
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
     /* end of delete data tabel folio */
	
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
	
		$sql = "select b.icd_nomor, b.icd_nama
				from klinik.klinik_perawatan_icd a
				join klinik.klinik_icd b on a.id_icd = b.icd_nomor
				left join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
				where c.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
				and a.rawat_icd_odos = 'OD' 
				order by a.rawat_icd_urut";
		$dataDiagIcdOD = $dtaccess->FetchAll($sql);
		for($i=0;$i<count($dataDiagIcdOD);$i++){
		    $_POST["rawat_icd_od_kode"][$i] = $dataDiagIcdOD[$i]["icd_nomor"];
		    $_POST["rawat_icd_od_nama"][$i] = $dataDiagIcdOD[$i]["icd_nama"];
		}
	//echo $sql."<br />";
		$sql = "select b.icd_nomor, b.icd_nama
				from klinik.klinik_perawatan_icd a
				join klinik.klinik_icd b on a.id_icd = b.icd_nomor
				left join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
				where c.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
				and a.rawat_icd_odos = 'OS' 
				order by a.rawat_icd_urut";
		$dataDiagIcdOS = $dtaccess->FetchAll($sql);
		for($i=0;$i<count($dataDiagIcdOS);$i++){
		    $_POST["rawat_icd_os_kode"][$i] = $dataDiagIcdOS[$i]["icd_nomor"];
		    $_POST["rawat_icd_os_nama"][$i] = $dataDiagIcdOS[$i]["icd_nama"];
		}
	//echo $sql;
	       $sql = "select b.prosedur_kode, b.prosedur_nama
			 from klinik.klinik_perawatan_prosedur a
			 join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode
			 left join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
			 where c.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
			 order by a.rawat_prosedur_urut";
	       $dataProsedur = $dtaccess->FetchAll($sql);
	       for($i=0;$i<count($dataProsedur);$i++){
		    $_POST["rawat_prosedur_kode"][$i] = $dataProsedur[$i]["prosedur_kode"];
		    $_POST["rawat_prosedur_nama"][$i] = $dataProsedur[$i]["prosedur_nama"];
	       }
	  
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
	  $fol_tanggal = date_db($_POST["fol_tanggal"]);
	  $fol_tanggal = split("-", $fol_tanggal);
	  $fol_tanggal = date('Y-m-d H:i:s', mktime(8, 0, 0, $fol_tanggal[1], $fol_tanggal[2], $fol_tanggal[0]));
	  $sql = "update klinik.klinik_folio set fol_dibayar = fol_nominal, fol_lunas = 'y', fol_dibayar_when = '".$fol_tanggal."', id_kwitansi = ".QuoteValue(DPE_NUMERIC,$_POST["kwitansi_id"])."  where  id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]); //fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])." and
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
	  
	  /*
	   * update data ICD & PROSEDUR
	   */
	  $sql_rawat = "select rawat_id from klinik.klinik_perawatan where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	  $rs_rawat = $dtaccess->Execute($sql_rawat);
	  $dataRawatICD = $dtaccess->Fetch($rs_rawat);
	  
	  //echo $dataRawatICD["rawat_id"];
	  // -- ini insert ke tabel rawat icd
		$sql = "delete from klinik.klinik_perawatan_icd where id_rawat = ".QuoteValue(DPE_CHAR,$dataRawatICD["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_icd";
          $dbField[0] = "rawat_icd_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_icd";
          $dbField[3] = "rawat_icd_urut";
          $dbField[4] = "rawat_icd_odos";
          
          for($i=0,$n=count($_POST["rawat_icd_od_kode"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawatICD["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_od_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OD");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_od_kode"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
	  
          for($i=0,$n=count($_POST["rawat_icd_os_kode"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawatICD["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_os_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OS");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_os_kode"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
	  // -- end of insert ICD
	  unset($dbField);
	  unset($dbTable);

	  //-- insert ke tabel rawat prosedur --//
	  $sql = "delete from klinik.klinik_perawatan_prosedur where id_rawat = ".QuoteValue(DPE_CHAR,$dataRawatICD["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_prosedur";
          $dbField[0] = "rawat_prosedur_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_prosedur";
          $dbField[3] = "rawat_prosedur_urut";
          
          for($i=0,$n=count($_POST["rawat_prosedur_kode"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawatICD["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_prosedur_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_prosedur_kode"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
	  unset($dbField);
  	  unset($dbTable);

	  $_x_mode = "Save";
	  // -- end of insert prosedur	  
	  /* end of update ICD & PROSEDUR*/
	}
	

     
	if($_POST["btnHapus"]) { 
		$sql = "delete from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql);
	    
     }
     
     // --- untuk bikin option special tariff --- //
     $optSpProc[] = $view->RenderOption("--","[pilih special procedure]",null,null);
     $optSpDrug[] = $view->RenderOption("--","[pilih special drug]",null,null);
     $optSpPros[] = $view->RenderOption("--","[pilih special prosthesis]",null,null);
     $optSpInv[] = $view->RenderOption("--","[pilih special investigation]",null,null);
     $sql = "select name, sptarif_id, groupcode from klinik.klinik_specialtariff where kelas_rs='B' order by groupcode";
     $rs = $dtaccess->Execute($sql);
     while($dataTariff = $dtaccess->Fetch($rs)){
	  if(strtolower($dataTariff["groupcode"])=="sprocedure"){
	       $optSpProc[] = $view->RenderOption($dataTariff["sptarif_id"],$dataTariff["name"],null,null);
	  } elseif (strtolower($dataTariff["groupcode"])=="sdrug"){
	       $optSpDrug[] = $view->RenderOption($dataTariff["sptarif_id"],$dataTariff["name"],null,null);
	  } elseif (strtolower($dataTariff["groupcode"])=="sprosthesis"){
	       $optSpPros[] = $view->RenderOption($dataTariff["sptarif_id"],$dataTariff["name"],null,null);
	  } elseif (strtolower($dataTariff["groupcode"])=="sinvestigation"){
	       $optSpInv[] = $view->RenderOption($dataTariff["sptarif_id"],$dataTariff["name"],null,null);
	  }
     }
     // --- end option special tariff --- //

?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitThickBox(); ?>

<script type="text/javascript">

<? $plx->Run(); ?>

function SetNominalProc(id) {
     var datanya = GetSpecialTariff(id,'type=r');
     var datanya2 = datanya.split("+");
     document.getElementById('spc_proc_nama').value = datanya2[0];
     document.getElementById('spc_proc_kode').value = datanya2[1];
     document.getElementById('spc_proc_nominal').value = datanya2[2];
     document.getElementById('lb_spproc_nama').innerHTML = datanya2[0];
     document.getElementById('lb_spproc_kode').innerHTML = datanya2[1];
     document.getElementById('lb_spproc_nominal').innerHTML = formatCurrency(datanya2[2]);
     document.getElementById('spc_proc_jml').value = 1;
}

function SetNominalPros(id) {
     var datanya = GetSpecialTariff(id,'type=r');
     var datanya2 = datanya.split("+");
     document.getElementById('spc_pros_nama').value = datanya2[0];
     document.getElementById('spc_pros_kode').value = datanya2[1];
     document.getElementById('spc_pros_nominal').value = datanya2[2];
     document.getElementById('lb_sppros_nama').innerHTML = datanya2[0];
     document.getElementById('lb_sppros_kode').innerHTML = datanya2[1];
     document.getElementById('lb_sppros_nominal').innerHTML = formatCurrency(datanya2[2]);
     document.getElementById('spc_pros_jml').value = 1;
}

function SetNominalDrug(id) {
     var datanya = GetSpecialTariff(id,'type=r');
     var datanya2 = datanya.split("+");
     document.getElementById('spc_drug_nama').value = datanya2[0];
     document.getElementById('spc_drug_kode').value = datanya2[1];
     document.getElementById('spc_drug_nominal').value = datanya2[2];
     document.getElementById('lb_spdrug_nama').innerHTML = datanya2[0];
     document.getElementById('lb_spdrug_kode').innerHTML = datanya2[1];
     document.getElementById('lb_spdrug_nominal').innerHTML = formatCurrency(datanya2[2]);
     document.getElementById('spc_drug_jml').value = 1;
}

function SetNominalInv(id) {
     var datanya = GetSpecialTariff(id,'type=r');
     var datanya2 = datanya.split("+");
     document.getElementById('spc_inv_nama').value = datanya2[0];
     document.getElementById('spc_inv_kode').value = datanya2[1];
     document.getElementById('spc_inv_nominal').value = datanya2[2];
     document.getElementById('lb_spinv_nama').innerHTML = datanya2[0];
     document.getElementById('lb_spinv_kode').innerHTML = datanya2[1];
     document.getElementById('lb_spinv_nominal').innerHTML = formatCurrency(datanya2[2]);
     document.getElementById('spc_inv_jml').value = 1;
}

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetFolio('target=antri_kiri_isi');     
     mTimer = setTimeout("timer()", 1000);
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

function CheckSimpan(jenis) {
     if(confirm('Cetak Invoice?')) {
	   BukaWindow('kasir_cetak.php?jenis=<?php echo $_POST["fol_jenis"];?>&id_reg=<?php echo $_POST["id_reg"];?>','Invoice');
	   BukaWindow2('kasir_cetak_bpjs.php?jenis=<?php echo $_POST["fol_jenis"];?>&id_reg=<?php echo $_POST["id_reg"];?>','Tagihan BPJS');
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
	
var drz;
function lihat0(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest").style.visibility = "hidden";
    }else{
        drz = buatajax();
        var url="cari0.php";
        drz.onreadystatechange=stateChanged;
        var params = "q="+eval;
        drz.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz.setRequestHeader("Content-length", params.length);
        drz.setRequestHeader("Connection", "close");
        drz.send(params);
    }

}

function buatajax(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged(){

var data;
    if (drz.readyState==4 && drz.status==200){
        data=drz.responseText;
        if(data.length>0){
            document.getElementById("kotaksugest0").innerHTML = data;
            document.getElementById("kotaksugest0").style.visibility = "";
        }else{
            document.getElementById("kotaksugest0").innerHTML = "";
            document.getElementById("kotaksugest0").style.visibility = "hidden";
        }
    }
}

function isi(id,kode,nama,total){

    document.getElementById("biaya_id").value = id;
    document.getElementById("biaya_nama").value = nama;
    document.getElementById("biaya_kode").value = kode;
    document.getElementById("txtJumlah").value = "1";
    document.getElementById("txtHargaSatuan").value = formatCurrency(total);
    document.getElementById("txtHargaTotal").value = formatCurrency(total);
    document.getElementById("kotaksugest").style.visibility = "hidden";
    document.getElementById("kotaksugest").innerHTML = "";
}

//-------------autocomplete Dewi-------------//
	
var drz;
function lihat(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest").style.visibility = "hidden";
    }else{
        drz = buatajax();
        var url="cari.php";
        drz.onreadystatechange=stateChanged;
        var params = "q="+eval;
        drz.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz.setRequestHeader("Content-length", params.length);
        drz.setRequestHeader("Connection", "close");
        drz.send(params);
    }

}

function buatajax(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged(){

var data;
    if (drz.readyState==4 && drz.status==200){
        data=drz.responseText;
        if(data.length>0){
            document.getElementById("kotaksugest").innerHTML = data;
            document.getElementById("kotaksugest").style.visibility = "";
        }else{
            document.getElementById("kotaksugest").innerHTML = "";
            document.getElementById("kotaksugest").style.visibility = "hidden";
        }
    }
}

function isi(rawat_icd_od_nama_0,rawat_icd_od_id_0,rawat_icd_od_kode_0){

    document.getElementById("rawat_icd_od_nama_0").value = rawat_icd_od_nama_0;
    document.getElementById("rawat_icd_od_id_0").value = rawat_icd_od_id_0;
    document.getElementById("rawat_icd_od_kode_0").value = rawat_icd_od_kode_0;
    document.getElementById("kotaksugest").style.visibility = "hidden";
    document.getElementById("kotaksugest").innerHTML = "";
}


//------

	
var drz1;
function lihat1(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest1").style.visibility = "hidden";
    }else{
        drz1 = buatajax1();
        var url="cari1.php";
        drz1.onreadystatechange=stateChanged1;
        var params1 = "q="+eval;
        drz1.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz1.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz1.setRequestHeader("Content-length", params1.length);
        drz1.setRequestHeader("Connection", "close");
        drz1.send(params1);
    }

}

function buatajax1(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged1(){

var data1;
    if (drz1.readyState==4 && drz1.status==200){
        data1=drz1.responseText;
        if(data1.length>0){
            document.getElementById("kotaksugest1").innerHTML = data1;
            document.getElementById("kotaksugest1").style.visibility = "";
        }else{
            document.getElementById("kotaksugest1").innerHTML = "";
            document.getElementById("kotaksugest1").style.visibility = "hidden";
        }
    }
}

function isi1(rawat_icd_od_nama_1,rawat_icd_od_id_1,rawat_icd_od_kode_1){

    document.getElementById("rawat_icd_od_nama_1").value = rawat_icd_od_nama_1;
    document.getElementById("rawat_icd_od_id_1").value = rawat_icd_od_id_1;
    document.getElementById("rawat_icd_od_kode_1").value = rawat_icd_od_kode_1;
    document.getElementById("kotaksugest1").style.visibility = "hidden";
    document.getElementById("kotaksugest1").innerHTML = "";
}

//------

	
var drz2;
function lihat2(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest2").style.visibility = "hidden";
    }else{
        drz2 = buatajax2();
        var url="cari2.php";
        drz2.onreadystatechange=stateChanged2;
        var params2 = "q="+eval;
        drz2.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz2.setRequestHeader("Content-length", params2.length);
        drz2.setRequestHeader("Connection", "close");
        drz2.send(params2);
    }

}

function buatajax2(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged2(){

var data2;
    if (drz2.readyState==4 && drz2.status==200){
        data2=drz2.responseText;
        if(data2.length>0){
            document.getElementById("kotaksugest2").innerHTML = data2;
            document.getElementById("kotaksugest2").style.visibility = "";
        }else{
            document.getElementById("kotaksugest2").innerHTML = "";
            document.getElementById("kotaksugest2").style.visibility = "hidden";
        }
    }
}

function isi2(rawat_icd_os_nama_0,rawat_icd_os_id_0,rawat_icd_os_kode_0){

    document.getElementById("rawat_icd_os_nama_0").value = rawat_icd_os_nama_0;
    document.getElementById("rawat_icd_os_id_0").value = rawat_icd_os_id_0;
    document.getElementById("rawat_icd_os_kode_0").value = rawat_icd_os_kode_0;
    document.getElementById("kotaksugest2").style.visibility = "hidden";
    document.getElementById("kotaksugest2").innerHTML = "";
}



//------

	
var drz3;
function lihat3(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest3").style.visibility = "hidden";
    }else{
        drz3 = buatajax3();
        var url="cari3.php";
        drz3.onreadystatechange=stateChanged3;
        var params3 = "q="+eval;
        drz3.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz3.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz3.setRequestHeader("Content-length", params3.length);
        drz3.setRequestHeader("Connection", "close");
        drz3.send(params3);
    }

}

function buatajax3(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged3(){

var data3;
    if (drz3.readyState==4 && drz3.status==200){
        data3=drz3.responseText;
        if(data3.length>0){
            document.getElementById("kotaksugest3").innerHTML = data3;
            document.getElementById("kotaksugest3").style.visibility = "";
        }else{
            document.getElementById("kotaksugest3").innerHTML = "";
            document.getElementById("kotaksugest3").style.visibility = "hidden";
        }
    }
}

function isi3(rawat_icd_os_nama_1,rawat_icd_os_id_1,rawat_icd_os_kode_1){

    document.getElementById("rawat_icd_os_nama_1").value = rawat_icd_os_nama_1;
    document.getElementById("rawat_icd_os_id_1").value = rawat_icd_os_id_1;
    document.getElementById("rawat_icd_os_kode_1").value = rawat_icd_os_kode_1;
    document.getElementById("kotaksugest3").style.visibility = "hidden";
    document.getElementById("kotaksugest3").innerHTML = "";
}


//------

	
var drz4;
function lihat4(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest4").style.visibility = "hidden";
    }else{
        drz4 = buatajax4();
        var url="cari4.php";
        drz4.onreadystatechange=stateChanged4;
        var params4 = "q="+eval;
        drz4.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz4.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz4.setRequestHeader("Content-length", params4.length);
        drz4.setRequestHeader("Connection", "close");
        drz4.send(params4);
    }

}

function buatajax4(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged4(){

var data4;
    if (drz4.readyState==4 && drz4.status==200){
        data4=drz4.responseText;
        if(data4.length>0){
            document.getElementById("kotaksugest4").innerHTML = data4;
            document.getElementById("kotaksugest4").style.visibility = "";
        }else{
            document.getElementById("kotaksugest4").innerHTML = "";
            document.getElementById("kotaksugest4").style.visibility = "hidden";
        }
    }
}

function isi4(rawat_ina_od_nama_0,rawat_ina_od_id_0,rawat_ina_od_kode_0){

    document.getElementById("rawat_icd_od_nama_2").value = rawat_ina_od_nama_0;
    document.getElementById("rawat_icd_od_id_2").value = rawat_ina_od_id_0;
    document.getElementById("rawat_icd_od_kode_2").value = rawat_ina_od_kode_0;
    document.getElementById("kotaksugest4").style.visibility = "hidden";
    document.getElementById("kotaksugest4").innerHTML = "";
}


//------

	
var drz5;
function lihat5(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest5").style.visibility = "hidden";
    }else{
        drz5 = buatajax5();
        var url="cari5.php";
        drz5.onreadystatechange=stateChanged5;
        var params5 = "q="+eval;
        drz5.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz5.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz5.setRequestHeader("Content-length", params5.length);
        drz5.setRequestHeader("Connection", "close");
        drz5.send(params5);
    }

}

function buatajax5(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged5(){

var data5;
    if (drz5.readyState==4 && drz5.status==200){
        data5=drz5.responseText;
        if(data5.length>0){
            document.getElementById("kotaksugest5").innerHTML = data5;
            document.getElementById("kotaksugest5").style.visibility = "";
        }else{
            document.getElementById("kotaksugest5").innerHTML = "";
            document.getElementById("kotaksugest5").style.visibility = "hidden";
        }
    }
}

function isi5(rawat_ina_od_nama_1,rawat_ina_od_id_1,rawat_ina_od_kode_1){

    document.getElementById("rawat_icd_od_nama_3").value = rawat_ina_od_nama_1;
    document.getElementById("rawat_icd_od_id_3").value = rawat_ina_od_id_1;
    document.getElementById("rawat_icd_od_kode_3").value = rawat_ina_od_kode_1;
    document.getElementById("kotaksugest5").style.visibility = "hidden";
    document.getElementById("kotaksugest5").innerHTML = "";
}


//------

	
var drz6;
function lihat6(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest6").style.visibility = "hidden";
    }else{
        drz6 = buatajax6();
        var url="cari6.php";
        drz6.onreadystatechange=stateChanged6;
        var params6 = "q="+eval;
        drz6.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz6.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz6.setRequestHeader("Content-length", params6.length);
        drz6.setRequestHeader("Connection", "close");
        drz6.send(params6);
    }

}

function buatajax6(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged6(){

var data6;
    if (drz6.readyState==4 && drz6.status==200){
        data6=drz6.responseText;
        if(data6.length>0){
            document.getElementById("kotaksugest6").innerHTML = data6;
            document.getElementById("kotaksugest6").style.visibility = "";
        }else{
            document.getElementById("kotaksugest6").innerHTML = "";
            document.getElementById("kotaksugest6").style.visibility = "hidden";
        }
    }
}

function isi6(rawat_ina_os_nama_0,rawat_ina_os_id_0,rawat_ina_os_kode_0){

    document.getElementById("rawat_icd_os_nama_2").value = rawat_ina_os_nama_0;
    document.getElementById("rawat_icd_os_id_2").value = rawat_ina_os_id_0;
    document.getElementById("rawat_icd_os_kode_2").value = rawat_ina_os_kode_0;
    document.getElementById("kotaksugest6").style.visibility = "hidden";
    document.getElementById("kotaksugest6").innerHTML = "";
}


//------

	
var drz7;
function lihat7(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest7").style.visibility = "hidden";
    }else{
        drz7 = buatajax7();
        var url="cari7.php";
        drz7.onreadystatechange=stateChanged7;
        var params7 = "q="+eval;
        drz7.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz7.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz7.setRequestHeader("Content-length", params7.length);
        drz7.setRequestHeader("Connection", "close");
        drz7.send(params7);
    }

}

function buatajax7(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged7(){

var data7;
    if (drz7.readyState==4 && drz7.status==200){
        data7=drz7.responseText;
        if(data7.length>0){
            document.getElementById("kotaksugest7").innerHTML = data7;
	    document.getElementById("kotaksugest7").style.visibility = "";
        }else{
            document.getElementById("kotaksugest7").innerHTML = "";
            document.getElementById("kotaksugest7").style.visibility = "hidden";
        }
    }
}

function isi7(rawat_ina_os_nama_1,rawat_ina_os_id_1,rawat_ina_os_kode_1){

    document.getElementById("rawat_icd_os_nama_3").value = rawat_ina_os_nama_1;
    document.getElementById("rawat_icd_os_id_3").value = rawat_ina_os_id_1;
    document.getElementById("rawat_icd_os_kode_3").value = rawat_ina_os_kode_1;
    document.getElementById("kotaksugest7").style.visibility = "hidden";
    document.getElementById("kotaksugest7").innerHTML = "";
}

var drz8;
function lookProc(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest8").style.visibility = "hidden";
    }else{
        drz8 = buatajax8();
        var url="cari_prosedur0.php";
        drz8.onreadystatechange=stateChanged8;
        var params = "q="+eval;
        drz8.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz8.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz8.setRequestHeader("Content-length", params.length);
        drz8.setRequestHeader("Connection", "close");
        drz8.send(params);
    }

}

function buatajax8(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged8(){

var data8;
    if (drz8.readyState==4 && drz8.status==200){
        data8=drz8.responseText;
        if(data8.length>0){
            document.getElementById("kotaksugest8").innerHTML = data8;
            document.getElementById("kotaksugest8").style.visibility = "";
        }else{
            document.getElementById("kotaksugest8").innerHTML = "";
            document.getElementById("kotaksugest8").style.visibility = "hidden";
        }
    }
}

function isi8(nama,id,kode){
    document.getElementById("rawat_prosedur_nama_0").value = nama;
    document.getElementById("rawat_prosedur_id_0").value = id;
    document.getElementById("rawat_prosedur_kode_0").value = kode;
    document.getElementById("kotaksugest8").style.visibility = "hidden";
    document.getElementById("kotaksugest8").innerHTML = "";
}

var drz9;
function lookProc1(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest9").style.visibility = "hidden";
    }else{
        drz9 = buatajax9();
        var url="cari_prosedur1.php";
        drz9.onreadystatechange=stateChanged9;
        var params = "q="+eval;
        drz9.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz9.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz9.setRequestHeader("Content-length", params.length);
        drz9.setRequestHeader("Connection", "close");
        drz9.send(params);
    }

}

function buatajax9(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged9(){

var data9;
    if (drz9.readyState==4 && drz9.status==200){
        data9=drz9.responseText;
        if(data9.length>0){
            document.getElementById("kotaksugest9").innerHTML = data9;
            document.getElementById("kotaksugest9").style.visibility = "";
        }else{
            document.getElementById("kotaksugest9").innerHTML = "";
            document.getElementById("kotaksugest9").style.visibility = "hidden";
        }
    }
}

function isi9(nama,id,kode){

    document.getElementById("rawat_prosedur_nama_1").value = nama;
    document.getElementById("rawat_prosedur_id_1").value = id;
    document.getElementById("rawat_prosedur_kode_1").value = kode;
    document.getElementById("kotaksugest9").style.visibility = "hidden";
    document.getElementById("kotaksugest9").innerHTML = "";
}

var drz10;
function lookProc2(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest10").style.visibility = "hidden";
    }else{
        drz10 = buatajax10();
        var url="cari_prosedur2.php";
        drz10.onreadystatechange=stateChanged10;
        var params = "q="+eval;
        drz10.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz10.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz10.setRequestHeader("Content-length", params.length);
        drz10.setRequestHeader("Connection", "close");
        drz10.send(params);
    }

}

function buatajax10(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged10(){

var data10;
    if (drz10.readyState==4 && drz10.status==200){
        data10=drz10.responseText;
        if(data10.length>0){
            document.getElementById("kotaksugest10").innerHTML = data10;
            document.getElementById("kotaksugest10").style.visibility = "";
        }else{
            document.getElementById("kotaksugest10").innerHTML = "";
            document.getElementById("kotaksugest10").style.visibility = "hidden";
        }
    }
}

function isi10(nama,id,kode){
    document.getElementById("rawat_prosedur_nama_2").value = nama;
    document.getElementById("rawat_prosedur_id_2").value = id;
    document.getElementById("rawat_prosedur_kode_2").value = kode;
    document.getElementById("kotaksugest10").style.visibility = "hidden";
    document.getElementById("kotaksugest10").innerHTML = "";
}

var drz11;
function lookProc3(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest11").style.visibility = "hidden";
    }else{
        drz11 = buatajax11();
        var url="cari_prosedur3.php";
        drz11.onreadystatechange=stateChanged11;
        var params = "q="+eval;
        drz11.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz11.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz11.setRequestHeader("Content-length", params.length);
        drz11.setRequestHeader("Connection", "close");
        drz11.send(params);
    }

}

function buatajax11(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged11(){

var data11;
    if (drz11.readyState==4 && drz11.status==200){
        data11=drz11.responseText;
        if(data11.length>0){
            document.getElementById("kotaksugest11").innerHTML = data11;
            document.getElementById("kotaksugest11").style.visibility = "";
        }else{
            document.getElementById("kotaksugest11").innerHTML = "";
            document.getElementById("kotaksugest11").style.visibility = "hidden";
        }
    }
}

function isi11(nama,id,kode){
    document.getElementById("rawat_prosedur_nama_3").value = nama;
    document.getElementById("rawat_prosedur_id_3").value = id;
    document.getElementById("rawat_prosedur_kode_3").value = kode;
    document.getElementById("kotaksugest11").style.visibility = "hidden";
    document.getElementById("kotaksugest11").innerHTML = "";
}

//-------------End autocomplete Dewi-------------//
//------

var INAFindInap1 = '<?php echo $findINAPageInap;?>&kelas=i&TB_iframe=true&height=400&width=450&modal=true';
var INAFindInap2 = '<?php echo $findINAPageInap;?>&kelas=ii&TB_iframe=true&height=400&width=450&modal=true';
var INAFindInap3 = '<?php echo $findINAPageInap;?>&kelas=iii&TB_iframe=true&height=400&width=450&modal=true';
var INAFindJalan = '<?php echo $findINAPageJalan;?>TB_iframe=true&height=400&width=450&modal=true';
function setINAFind(rdo){
     if (rdo=="rawat_jalan") {
	  document.getElementById('_ina_find').href = INAFindJalan;
	  document.getElementById('_kelas_inap').style.display = 'none';
	  document.getElementById('_ina_find').innerHTML = '<img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" />';
     }else if (rdo=="rawat_inap") {
	  document.getElementById('_ina_find').href = INAFindInap1;
	  document.getElementById('_kelas_inap').style.display = 'block';
     }
}

function setINAKelas(args) {
     var hrefnya;
     if (args=="kelas_1") {
	  hrefnya = INAFindInap1;
	  document.getElementById('_ina_find').innerHTML = '<img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" />';
     }else if (args=="kelas_2") {
	  hrefnya = INAFindInap2;
	  document.getElementById('_ina_find').innerHTML = '<img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" />';
     }else if (args=="kelas_3") {
	  hrefnya = INAFindInap3;
	  document.getElementById('_ina_find').innerHTML = '<img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" />';
     }
     document.getElementById('_ina_find').href = hrefnya;
}

<?php if($_x_mode=="Save"){ ?>
     BukaWindow('kasir_cetak.php?nokwitansi=<?php echo $_POST["kwitansi_id"];?>&id_reg=<?php echo $_POST["id_reg"];?>&jp=0','Invoice');
     BukaWindow2('kasir_cetak_bpjs.php?jenis=<?php echo $_POST["fol_jenis"];?>&id_reg=<?php echo $_POST["id_reg"];?>','Tagihan BPJS');
     document.location.href='<?php echo $thisPage;?>';
<?php } ?>
</script>



<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
		<div class="tableheader">Antrian Kasir</div>
		 <div style="margin:10px auto 5px 7px;"><a href="<?php echo $findPasienFolio;?>&jenis=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Tambah Pasien"><img src="<?php echo $ROOT;?>images/bnplus.gif" />Tambah Pasien</a></div>
		<div id="antri_kiri_isi" style="height:265;overflow:auto"><?php //echo GetFolio(); ?></div>
</div><?php //echo $sql_fol;?>

<?php if($dataPasien) { ?>
     
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Pembayaran</td>
	</tr>
</table> 

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" >
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
          <tr>
               <td width= "20%" align="left" class="tablecontent">Tanggal Pembayaran</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><input type="text" id="fol_tanggal" name="fol_tanggal" size="15" maxlength="10" value="<?php echo FormatFromTimeStamp($_GET["waktu"]);?>" onKeyDown="return tabOnEnter(this, event);"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_fol_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" /></label></td>
          </tr>
           <script>
			  Calendar.setup({
			        inputField     :    "fol_tanggal",      // id of the input field
			        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
			        showsTime      :    false,            // will display a time selector
			        button         :    "img_fol_tanggal",   // trigger for the calendar (button ID)
			        singleClick    :    true,           // double-click mode
			        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
			    });
			  </script>
<?php echo $view->RenderHidden("reg_jenis_bayar","reg_jenis_bayar",$dataPasien["reg_jenis_pasien"]);?>
          
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
				<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("biaya_kode","biaya_kode","10","100",$_POST["biaya_kode"],"inputField",null,false,"onkeyup=\"lihat0(this.value);\""); ?>
				  <a href="<?php echo $findPage?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Item">
				  <img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" /></a>
				  <div id="kotaksugest0" style="position:absolute; background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
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
				       <?php echo $view->RenderTextBox("txtJumlah","txtJumlah","3","3",$_POST["txtJumlah"],"curedit", null,false,'onchange="GantiHargaItem(this.value,document.getElementById(\'txtHargaSatuan\').value);"');?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaSatuan","txtHargaSatuan","10","10",$_POST["txtHargaSatuan"],"curedit", null,true,'onchange="GantiHargaItem(document.getElementById(\'txtJumlah\').value,this.value);"');?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Total Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaTotal","txtHargaTotal","10","10",$_POST["txtHargaTotal"],"curedit", "autocomplete=\"off\" readonly",true);?>
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
				<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("obat_kode","obat_kode","10","100",$_POST["obat_kode"],"inputField",null,false,"onkeyup=\"lihat0(this.value);\""); ?>
				  <a href="<?php echo $findPageObat?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Item">
				  <img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" /></a>
				  <div id="kotaksugest0" style="position:absolute; background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
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
				       <?php echo $view->RenderTextBox("txtJumlahObat","txtJumlahObat","3","3",$_POST["txtJumlahObat"],"curedit", null,false,'onchange="GantiHargaObat(this.value,document.getElementById(\'txtHargaSatuanObat\').value);"');?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaSatuanObat","txtHargaSatuanObat","10","10",$_POST["txtHargaSatuanObat"],"curedit", null,true,'onchange="GantiHargaObat(document.getElementById(\'txtJumlahObat\').value,this.value);"');?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Total Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaTotalObat","txtHargaTotalObat","10","10",$_POST["txtHargaTotalObat"],"curedit", "autocomplete=\"off\" readonly",true);?>
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
				<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("operasi_kode","operasi_kode","10","100",$_POST["operasi_kode"],"inputField",null,false,"onkeyup=\"lihat0(this.value);\""); ?>
				  <a href="<?php echo $findPageOps?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Item">
				  <img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" /></a>
				  <div id="kotaksugest0" style="position:absolute; background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
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
				       <?php echo $view->RenderTextBox("txtHargaSatuanOperasi","txtHargaSatuanOperasi","10","10",$_POST["txtHargaSatuanOperasi"],"curedit", "autocomplete=\"off\" readonly",true);?>
				</td>					
			 </tr>
			 <tr>
				<td align="left" class="tablecontent">&nbsp;Total Biaya</td>
				<td align="left" class="tablecontent-odd">
				       <?php echo $view->RenderTextBox("txtHargaTotalOperasi","txtHargaTotalOperasi","10","10",$_POST["txtHargaTotalOperasi"],"curedit", "autocomplete=\"off\" readonly",true);?>
				</td>					
			 </tr>
		    </table>
		    </fieldset>
		    </div>
		    <input type="submit" name="btnSaveTambah" value="Simpan" class="button" style="float: left">
		    <input type="hidden" name="fol_jenis" id="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>" />  
		    <input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>    
		    <input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/> 
		    <!-- <input type="hidden" name="waktunya" value="<?php echo $_GET["waktu"];?>" /> -->
		    <input type="hidden" name="id_biaya" value="<?php echo $_GET["biaya"];?>" />
		    <input type="hidden" name="id_obat" value="<?php echo $_GET["obat"];?>" />
		    <input type="hidden" name="id_biaya_operasi" value="<?php echo $_GET["biaya_operasi"];?>" />
	       </td>
	  </tr>
     </table>
      
     </fieldset> 
     
     <?php //if($dataPasien["reg_jenis_pasien"]=="12"||$dataPasien["reg_jenis_pasien"]=="13"||$dataPasien["reg_jenis_pasien"]=="14"){?>
     <fieldset>
     <legend><strong>Diagnosis - ICD - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[0]","rawat_icd_od_kode_0","10","100",$_POST["rawat_icd_od_kode"][0],"inputField", "autocomplete=\"off\"",false,"onkeyup=\"lihat(this.value);\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[0]" id="rawat_icd_od_id_0" value="<?php echo $_POST["rawat_icd_od_id"][0]?>" />
			  <div id="kotaksugest" style="position:absolute; background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
               </td>
               <td align="left" class="tablecontent-odd"><div> 
              <input type="text" size= "50" name="rawat_icd_od_nama[0]" id="rawat_icd_od_nama_0" value="<?php echo $_POST["rawat_icd_od_nama"][0]?>" />
                </div>
                
         </td> 
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[1]","rawat_icd_od_kode_1","10","100",$_POST["rawat_icd_od_kode"][1],"inputField",  "autocomplete=\"off\"",false," onkeyup=\"lihat1(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[1]" id="rawat_icd_od_id_1" value="<?php echo $_POST["rawat_icd_od_id"][1]?>" />                    
                <div id=kotaksugest1 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_icd_od_nama[1]" id="rawat_icd_od_nama_1" value="<?php echo $_POST["rawat_icd_od_nama"][1]?>" />
                </div>
         </td> 
          </tr>     
          <tr>
               <td align="left" class="tablecontent">3</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[2]","rawat_icd_od_kode_2","10","100",$_POST["rawat_icd_od_kode"][2],"inputField",  "autocomplete=\"off\"",false," onkeyup=\"lihat4(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=2&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[2]" id="rawat_icd_od_id_2" value="<?php echo $_POST["rawat_icd_od_id"][2]?>" />                    
                <div id=kotaksugest4 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_icd_od_nama[2]" id="rawat_icd_od_nama_2" value="<?php echo $_POST["rawat_icd_od_nama"][2]?>" />
                </div>
         </td> 
          </tr>  
          </tr>
	</table>
     </fieldset>

     <fieldset>
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
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[0]","rawat_icd_os_kode_0","10","100",$_POST["rawat_icd_os_kode"][0],"inputField",  "autocomplete=\"off\"",false,"onkeyup=\"lihat2(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[0]" id="rawat_icd_os_id_0" value="<?php echo $_POST["rawat_icd_os_id"][0]?>" />                    
                <div id=kotaksugest2 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
		   <td align="left" class="tablecontent-odd"><div>      
		   <input type="text" size= "50" name="rawat_icd_os_nama[0]" id="rawat_icd_os_nama_0" value="<?php echo $_POST["rawat_icd_os_nama"][0]?>" />
               </div>
         </td> 
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[1]","rawat_icd_os_kode_1","10","100",$_POST["rawat_icd_os_kode"][1],"inputField",  "autocomplete=\"off\"",false,"onkeyup=\"lihat3(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[1]" id="rawat_icd_os_id_1" value="<?php echo $_POST["rawat_icd_os_id"][1]?>" />                    
                <div id=kotaksugest3 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_icd_os_nama[1]" id="rawat_icd_os_nama_1" value="<?php echo $_POST["rawat_icd_os_nama"][1]?>" />
                </div>
         </td> 
          </tr>           
          <tr>
               <td align="left" class="tablecontent">3</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[2]","rawat_icd_os_kode_2","10","100",$_POST["rawat_icd_os_kode"][2],"inputField",  "autocomplete=\"off\"",false," onkeyup=\"lihat6(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=2&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[2]" id="rawat_icd_os_id_2" value="<?php echo $_POST["rawat_icd_os_id"][2]?>" />                    
                <div id="kotaksugest6" style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_icd_os_nama[2]" id="rawat_icd_os_nama_2" value="<?php echo $_POST["rawat_icd_os_nama"][2]?>" />
                </div>
         </td> 
          </tr>    
	</table>
     </fieldset>
     <!-- user request 14 Mar '14 -->
     <!-- adding procedure field -->
     <fieldset>
	    <legend><strong>Prosedur</strong></legend>
	    <table width="100%" border="1" cellpadding="4" cellspacing="1">
		   <tr>
			  <td align="center" class="subheader" width="5%"></td>
			  <td align="center" class="subheader" width="25%">Kode</td>
			  <td align="center" class="subheader">Keterangan</td>
		   </tr>
		   <tr>
			  <td align="center" class="tablecontent" width="5%">1</td>
			  <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[0]","rawat_prosedur_kode_0","10","100",$_POST["rawat_prosedur_kode"][0],"inputField",  "autocomplete=\"off\"",false,"onkeyup=\"lookProc(this.value);\"");?>
                    <a href="<?php echo $procPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_prosedur_id[0]" id="rawat_prosedur_id_0" value="<?php echo $_POST["rawat_prosedur_id"][0]?>" />                    
			  <div id=kotaksugest8 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
			  </td>
			  <td align="left" class="tablecontent-odd"><div>      
			  <input type="text" size= "50" name="rawat_prosedur_nama[0]" id="rawat_prosedur_nama_0" value="<?php echo $_POST["rawat_prosedur_nama"][0]?>" />
			  </div>
		   </tr>
		   <tr>
			  <td align="center" class="tablecontent" width="5%">2</td>
			  <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[1]","rawat_prosedur_kode_1","10","100",$_POST["rawat_prosedur_kode"][1],"inputField",  "autocomplete=\"off\"",false,"onkeyup=\"lookProc1(this.value);\"");?>
                    <a href="<?php echo $procPage;?>&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_prosedur_id[1]" id="rawat_prosedur_id_1" value="<?php echo $_POST["rawat_prosedur_id"][1]?>" />                    
			  <div id=kotaksugest9 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
			  </td>
			  <td align="left" class="tablecontent-odd"><div>      
			  <input type="text" size= "50" name="rawat_prosedur_nama[1]" id="rawat_prosedur_nama_1" value="<?php echo $_POST["rawat_prosedur_nama"][1]?>" />
			  </div>
		   </tr>
		   <tr>
			  <td align="center" class="tablecontent" width="5%">3</td>
			  <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[2]","rawat_prosedur_kode_2","10","100",$_POST["rawat_prosedur_kode"][2],"inputField",  "autocomplete=\"off\"",false,"onkeyup=\"lookProc2(this.value);\"");?>
                    <a href="<?php echo $procPage;?>&el=2&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_prosedur_id[2]" id="rawat_prosedur_id_2" value="<?php echo $_POST["rawat_prosedur_id"][2]?>" />                    
			  <div id=kotaksugest10 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
			  </td>
			  <td align="left" class="tablecontent-odd"><div>      
			  <input type="text" size= "50" name="rawat_prosedur_nama[2]" id="rawat_prosedur_nama_2" value="<?php echo $_POST["rawat_prosedur_nama"][2]?>" />
			  </div>
		   </tr>
		   <tr>
			  <td align="center" class="tablecontent" width="5%">4</td>
			  <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[3]","rawat_prosedur_kode_3","10","100",$_POST["rawat_prosedur_kode"][3],"inputField",  "autocomplete=\"off\"",false,"onkeyup=\"lookProc3(this.value);\"");?>
                    <a href="<?php echo $procPage;?>&el=3&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_prosedur_id[3]" id="rawat_prosedur_id_3" value="<?php echo $_POST["rawat_prosedur_id"][3]?>" />                    
			  <div id=kotaksugest11 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
			  </td>
			  <td align="left" class="tablecontent-odd"><div>      
			  <input type="text" size= "50" name="rawat_prosedur_nama[3]" id="rawat_prosedur_nama_3" value="<?php echo $_POST["rawat_prosedur_nama"][3]?>" />
			  </div>
		   </tr>
	    </table>
     </fieldset>
     
     <fieldset>
	    <legend><strong><a id="tambahINA">INA CBG</a></strong></legend>
	    <table width="80%" border="0" cellpadding="4" cellspacing="1">
	       <tr>
		    <td style="text-align: left; width: 20%;" class="tablecontent">&nbsp;Jenis Rawat</td>
		    <td style="text-align: left; width: 80%;" class="tablecontent-odd">
		    <?php
			 echo $view->RenderRadio("rd_jenis_rawat","rd_jenis_rawat_jalan","rawat_jalan","inputField",null,"onClick='setINAFind(this.value);'");
			 echo $view->RenderLabel("lbl_rawat_jalan","rd_jenis_rawat_jalan","Rawat Jalan","inputField",null,null);
			 echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			 echo $view->RenderRadio("rd_jenis_rawat","rd_jenis_rawat_inap","rawat_inap","inputField",null,"onClick='setINAFind(this.value);'");
			 echo $view->RenderLabel("lbl_rawat_inap","rd_jenis_rawat_inap","Rawat Inap","inputField",null,null);
		    ?>
		    <div id="_kelas_inap" style="display: none;">
		    <?php
			 echo $view->RenderRadio("rd_kelas_rawat","rd_rawat_kelas_3","kelas_3","inputField",null,"onClick='setINAKelas(this.value);'");
			 echo $view->RenderLabel("lbl_rawat_kelas_3","rd_rawat_kelas_3","Kelas III","inputField",null,null);
			 echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			 echo $view->RenderRadio("rd_kelas_rawat","rd_rawat_kelas_2","kelas_2","inputField",null,"onClick='setINAKelas(this.value);'");
			 echo $view->RenderLabel("lbl_rawat_kelas_2","rd_rawat_kelas_2","Kelas II","inputField",null,null);
			 echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			 echo $view->RenderRadio("rd_kelas_rawat","rd_rawat_kelas_1","kelas_1","inputField","selected","onClick='setINAKelas(this.value);'");
			 echo $view->RenderLabel("lbl_rawat_kelas_1","rd_rawat_kelas_1","Kelas I","inputField",null,null);
		    ?>
		    </div>
		    </td>
	       </tr>
		    <tr>
			 <td align="left" width="20%" class="tablecontent">&nbsp;Kode INA CBG&nbsp;</td>
			 <td align="left" width="80%" class="tablecontent-odd"><?php echo $view->RenderTextBox("ina_kode","ina_kode","20","100",$_POST["ina_kode"],"inputField","readonly",false,"onkeyup=\"lihat1(this.value);\""); ?>
			      <a href="#" class="thickbox" title="Pilih Item" id="_ina_find"></a><div id="kotaksugest1" style="position:absolute; background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			      </div>
			 </td>
		    </tr>   
		    <tr>
			  <td align="left" width="20%" class="tablecontent">&nbsp;Deskripsi&nbsp;</td>
			  <td align="left" width="80%" class="tablecontent-odd">
			      <?php echo $view->RenderTextBox("ina_nama","ina_nama","30","100",$_POST["ina_nama"],"inputField", "readonly",false);?>    
			      <input type="hidden" name="ina_id" id="ina_id" value="<?php echo $_POST["ina_id"];?>" /> 
			 </td>
		    </tr>
		    <tr>
			 <td align="left" width="20%" class="tablecontent">&nbsp;Harga&nbsp;</td>
			 <td align="left" width="80%" class="tablecontent-odd">
			      <?php echo $view->RenderTextBox("ina_nominal","ina_nominal","30","100",$_POST["ina_nominal"],"inputField", "readonly",false);?>    
			 </td>
		    </tr>
		    <tr>
			 <td style="text-align: left; width: 20%;" class="tablecontent" colspan="2">
				      <?php echo $view->RenderButton(BTN_SUBMIT,"btnTambahINA" ,"btnTambahINA","Tambah INA","button",false,null);?>
			 </td>
		    </tr>
	    </table>
     </fieldset>
     <a id="tariff_spc"></a> 
     <fieldset>
	    <legend><strong><a id="inacbg">Tarif Spesial</a></strong></legend>
	    <table width="80%" border="0" cellpadding="4" cellspacing="1">
	       <tr>
		    <td style="text-align: left; width: 20%;" class="tablecontent">&nbsp;Special Procedures</td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <?php echo $view->RenderComboBox("spc_proc_id","spc_proc_id",$optSpProc,"inputField",null,"onchange=\"SetNominalProc(this.value);\"");?>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <span id="lb_spproc_kode"></span>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <span id="lb_spproc_nama"></span>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 &nbsp;Rp.&nbsp;<span id="lb_spproc_nominal"></span>
		    </td>
			 <?php echo $view->RenderHidden("spc_proc_nominal","spc_proc_nominal",$_POST["spc_proc_nominal"]);?>
			 <?php echo $view->RenderHidden("spc_proc_nama","spc_proc_nama",$_POST["spc_proc_nama"]);?>
			 <?php echo $view->RenderHidden("spc_proc_kode","spc_proc_kode",$_POST["spc_proc_kode"]);?>
			 <?php echo $view->RenderHidden("spc_proc_jml","spc_proc_jml",$_POST["spc_proc_jml"]);?>
	       </tr>
	       <tr>
		    <td style="text-align: left; width: 20%;" class="tablecontent">&nbsp;Special Investigation</td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <?php echo $view->RenderComboBox("spc_inv_id","spc_inv_id",$optSpInv,"inputField",null,"onchange=\"SetNominalInv(this.value);\"");?>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <span id="lb_spinv_kode"></span>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <span id="lb_spinv_nama"></span>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 &nbsp;Rp.&nbsp;<span id="lb_spinv_nominal"></span>
		    </td>
			 <?php echo $view->RenderHidden("spc_inv_nominal","spc_inv_nominal",$_POST["spc_inv_nominal"]);?>
			 <?php echo $view->RenderHidden("spc_inv_nama","spc_inv_nama",$_POST["spc_inv_nama"]);?>
			 <?php echo $view->RenderHidden("spc_inv_kode","spc_inv_kode",$_POST["spc_inv_kode"]);?>
			 <?php echo $view->RenderHidden("spc_inv_jml","spc_inv_jml",$_POST["spc_inv_jml"]);?>
	       </tr>
	       <tr>
		    <td style="text-align: left; width: 20%;" class="tablecontent">&nbsp;Special Prosthesis</td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <?php echo $view->RenderComboBox("spc_pros_id","spc_pros_id",$optSpPros,"inputField",null,"onchange=\"SetNominalPros(this.value);\"");?>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <span id="lb_sppros_kode"></span>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <span id="lb_sppros_nama"></span>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 &nbsp;Rp.&nbsp;<span id="lb_sppros_nominal"></span>
		    </td>
			 <?php echo $view->RenderHidden("spc_pros_nominal","spc_pros_nominal",$_POST["spc_pros_nominal"]);?>
			 <?php echo $view->RenderHidden("spc_pros_nama","spc_pros_nama",$_POST["spc_pros_nama"]);?>
			 <?php echo $view->RenderHidden("spc_pros_kode","spc_pros_kode",$_POST["spc_pros_kode"]);?>
			 <?php echo $view->RenderHidden("spc_pros_jml","spc_pros_jml",$_POST["spc_pros_jml"]);?>
	       </tr>
	       <tr>
		    <td style="text-align: left; width: 20%;" class="tablecontent">&nbsp;Special Drug</td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <?php echo $view->RenderComboBox("spc_drug_id","spc_drug_id",$optSpDrug,"inputField",null,"onchange=\"SetNominalDrug(this.value);\"");?>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <span id="lb_spdrug_kode"></span>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 <span id="lb_spdrug_nama"></span>
		    </td>
		    <td style="text-align: left; width: 20%;" class="tablecontent-odd">
			 &nbsp;Rp.&nbsp;<span id="lb_spdrug_nominal"></span>
		    </td>
			 <?php echo $view->RenderHidden("spc_drug_nominal","spc_drug_nominal",$_POST["spc_drug_nominal"]);?>
			 <?php echo $view->RenderHidden("spc_drug_nama","spc_drug_nama",$_POST["spc_drug_nama"]);?>
			 <?php echo $view->RenderHidden("spc_drug_kode","spc_drug_kode",$_POST["spc_drug_kode"]);?>
			 <?php echo $view->RenderHidden("spc_drug_jml","spc_drug_jml",$_POST["spc_drug_jml"]);?>
	       </tr>
	       <tr>
		    <td colspan="5">
			 <?php echo $view->RenderButton(BTN_SUBMIT,"btnSaveSpcProc","btnSaveSpcProc","Simpan Special Procedure","button",null,null);?>
		    </td>
	       </tr>
	       <!--<tr>
		    <td style="text-align: left; width: 20%;" class="tablecontent">&nbsp;Special Investigation</td>
		    <td style="text-align: left; width: 80%;" class="tablecontent-odd">
			 <?php echo $view->RenderTextBox("spc_inv_kode","spc_inv_kode","15","100",$_POST["spc_inv_kode"],"inputField","readonly",false);?>
			 &nbsp;
			 <a href="#" class="thickbox" title="Pilih Item" >
			      <img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item">
			 </a>
			 &nbsp;&nbsp;<span id="spc_inv_nama"></span>
			 &nbsp;&nbsp;Rp.&nbsp;<span id="spc_inv_nominal"></span>
			 <?php echo $view->RenderHidden("spc_inv_id","spc_inv_id",$_POST["spc_inv_id"]);?>
		    </td>
	       </tr>	
	       <tr>
		    <td style="text-align: left; width: 20%;" class="tablecontent">&nbsp;Tarif Spesial</td>
		    <td style="text-align: left; width: 80%;" class="tablecontent-odd">
			 <select name="tarif_spesial_jenis" id="tarif_spesial_jenis" class="inputField">
			      <option value="kode_subacut">Kode Subacut/Chronic</option>
			      <option value="special_procedure">Special Procedure</option>
			      <option value="special_prostesis">Special Prostesis</option>
			      <option value="special_investigation">Special Investigation</option>
			      <option value="special_drug">Special Drug</option>
			 </select>
		    </td>
	       </tr>
	       <tr>
		    <td style="text-align: left; width: 20%;" class="tablecontent">&nbsp;Nominal</td>
		    <td style="text-align: left; width: 80%;" class="tablecontent-odd">
			 <?php echo $view->RenderTextBox("tarif_spesial_nominal","tarif_spesial_nominal","30","100",$_POST["tarif_spesial_nominal"],"inputField",null,true); ?>
		    </td>
	       </tr>
	       <tr>
		    <td style="text-align: left; width: 80%;" class="tablecontent-odd" colspan="2">
			 <?php echo $view->RenderButton(BTN_SUBMIT,"btnTambahSpc" ,"btnTambahSpc","Tambah Tarif Spesial","button",false,null);?>
		    </td>
	       </tr>-->
	    </table>
     </fieldset>
     
     <?php //} ?>
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

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
