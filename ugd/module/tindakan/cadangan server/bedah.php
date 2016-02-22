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
     $statusInjeksi = false;
     

 	if(!$auth->IsAllowed("tindakan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("tindakan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "bedah.php"; 
     $icdPage = "op_icd_find.php?";
     $inaPage = "op_ina_find.php?";
     $dokterPage = "op_dokter_find.php?";
     $susterPage = "op_suster_find.php?";
     $backPage = "bedah_view.php?";
     
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
     
     function GetDosis($item,$akhir,$id=null) {
          global $dtaccess, $view;
          
		$sql = "select item_fisik from inventori.inv_item where item_id = ".QuoteValue(DPE_NUMERIC,$item);
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
          
          return $view->RenderComboBox("id_dosis[]","id_dosis".$akhir,$optDosis,null,null,null);
     }

     function GetTindakan($status) { 
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT,$rawatStatus,$page; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.reg_status like '".STATUS_OPERASI_JADWAL.$status."' 
                    or a.reg_status like '".STATUS_BEDAH.$status."'  
				or a.reg_status like '".STATUS_PREOP.$status."' 
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

     if ($_GET["id"]) {
          // === buat ngedit ---          
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["op_id"] = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.id_reg from klinik.klinik_perawatan_operasi a 
				where op_id = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $_GET["id_reg"] = $row_edit["id_reg"];
     }     
	
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien,  
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                    from klinik.klinik_registrasi a
				join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"]; 
		$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"]; 
          $_POST["id_reg"] = $_GET["id_reg"];
          $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];

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
     
     
          $sql = "select rawat_id, rawat_tonometri_scale_od, rawat_tonometri_weight_od, rawat_tonometri_pressure_od, 
                    rawat_anel, rawat_schimer, rawat_operasi_jenis  
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
          
          $sql = "select * from klinik.klinik_perawatan_operasi where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataOperasi= $dtaccess->Fetch($sql);
          
          $view->CreatePost($dataOperasi);

		$tmpJamMulai = explode(":", $_POST["op_jam_mulai"]);
		$_POST["op_mulai_jam"] = $tmpJamMulai[0];
		$_POST["op_mulai_menit"] = $tmpJamMulai[1];
		
		$tmpJamSelesai = explode(":", $_POST["op_jam_selesai"]);
		$_POST["op_selesai_jam"] = $tmpJamSelesai[0];
		$_POST["op_selesai_menit"] = $tmpJamSelesai[1];


          $sql = "select pgw_nama, pgw_id from hris.hris_pegawai b where pgw_id = ".QuoteValue(DPE_NUMERIC,$_POST["id_dokter"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_dokter"] = $row["pgw_id"];
          $_POST["op_dokter_nama"] = $row["pgw_nama"];

          $sql = "select pgw_nama, pgw_id from hris.hris_pegawai b where pgw_id = ".QuoteValue(DPE_NUMERIC,$_POST["id_suster"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_suster"] = $row["pgw_id"];
          $_POST["op_suster_nama"] = $row["pgw_nama"];


		
          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_terapi_suster a 
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_op = ".QuoteValue(DPE_CHAR,$dataOperasi["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster_terapi"][$i] = $row["pgw_id"];
               $_POST["op_suster_terapi_nama"][$i] = $row["pgw_nama"];
               $i++;
          }



          $sql = "select * from klinik.klinik_perawatan_duranteop a 
                    where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_durop_komp"][$row["id_durop_komp"]] = "y";
          }
          
          
          $sql = "select * from klinik.klinik_perawatan_injeksi a 
                    where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $dataDetailInjeksi = $dtaccess->FetchAll($rs);
	

		$sql = "select b.icd_nomor as op_icd_kode, b.icd_nama as op_icd_nama, a.id_icd  
				from klinik.klinik_perawatan_operasi_icd a  
                    join klinik.klinik_icd b on a.id_icd = b.icd_id 
				where a.id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
		$dataIcd = $dtaccess->FetchAll($sql);


		$sql = "select b.ina_kode as op_ina_kode, b.ina_nama as op_ina_nama 
				from klinik.klinik_ina b 
				where b.ina_id = ".QuoteValue(DPE_CHAR,$dataOperasi["id_ina"]);
		$dataIna = $dtaccess->Fetch($sql);
		$view->CreatePost($dataIna);
		
     }


     if($_POST["btnSave"] || $_POST["btnUpdate"]) {
          
          if($_POST["btnSave"]) {
               $sql = "delete from klinik.klinik_perawatan_operasi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
		}

          $dbTable = "klinik.klinik_perawatan_operasi";
          $dbField[0] = "op_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "op_jam_mulai";
          $dbField[3] = "op_jam_selesai";
          $dbField[4] = "op_tanggal";
          $dbField[5] = "id_op_jenis";
          $dbField[6] = "id_ina";
          $dbField[7] = "op_pesan";
          $dbField[8] = "op_tipe";
          $dbField[9] = "id_dokter";
          $dbField[10] = "id_suster";
          $dbField[11] = "id_cust_usr";
          $dbField[12] = "op_paket_biaya";
                    
          if(!$_POST["op_id"]) $_POST["op_id"] = $dtaccess->GetTransID();
          $_POST["op_jam_mulai"] = $_POST["op_mulai_jam"].":".$_POST["op_mulai_menit"].":00";
          $_POST["op_jam_selesai"] = $_POST["op_selesai_jam"].":".$_POST["op_selesai_menit"].":00";
          $_POST["op_tanggal"] = date("Y-m-d");
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["op_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_DATETIME,$_POST["op_jam_mulai"]);
          $dbValue[3] = QuoteValue(DPE_DATETIME,$_POST["op_jam_selesai"]);
          $dbValue[4] = QuoteValue(DPE_DATE,$_POST["op_tanggal"]);
          $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["id_op_jenis"]);
          $dbValue[6] = QuoteValue(DPE_CHARKEY,$_POST["id_ina"]);
          $dbValue[7] = QuoteValue(DPE_CHARKEY,$_POST["op_pesan"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,"B");
          $dbValue[9] = QuoteValue(DPE_NUMERICKEY,$_POST["id_dokter"]);
          $dbValue[10] = QuoteValue(DPE_NUMERICKEY,$_POST["id_suster"]);
          $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
          $dbValue[12] = QuoteValue(DPE_CHARKEY,$_POST["op_paket_biaya"]);
          

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
          
          
          // --- insert ke tabel perawatan_operasi_icd
          $sql = "delete from klinik.klinik_perawatan_operasi_icd where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);
          
          $dbTable = "klinik.klinik_perawatan_operasi_icd";
          
          $dbField[0] = "rwt_op_icd_id";
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
          
          // -- ini insert ke tabel durante OP
		$sql = "delete from klinik.klinik_perawatan_duranteop where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_duranteop";
          $dbField[0] = "id_op";
          $dbField[1] = "id_durop_komp";
          
          if($_POST["id_durop_komp"]) {
               foreach($_POST["id_durop_komp"] as $key=>$value) {
                    
                    $dbValue[0] = QuoteValue(DPE_CHARKEY,$_POST["op_id"]);
                    $dbValue[1] = QuoteValue(DPE_CHARKEY,$key);
     
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dbKey[1] = 1; 
                    
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
                    $dtmodel->Insert() or die("insert  error");
                    
                    unset($dtmodel);
                    unset($dbValue);
                    unset($dbKey);
               }
          }
          
          
          // --- insert ke tabel perawatan injeksi ---
          $sql = "delete from klinik.klinik_perawatan_injeksi where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);
          
          $sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
                    and id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_INJEKSI);
          $dtaccess->Execute($sql);
          
                    
          if($_POST["id_item"]) {
          
               $dbTable = "klinik.klinik_perawatan_injeksi";
               $dbField[0] = "rawat_injeksi_id";
               $dbField[1] = "id_op";
               $dbField[2] = "id_dosis";
               $dbField[3] = "id_injeksi";
               $dbField[4] = "id_item";
          
               foreach($_POST["id_item"] as $key=>$value) {
                    
                    if($value) {
                         $rawatInjeksiId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHARKEY,$rawatInjeksiId);
                         $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["op_id"]);
                         $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["id_dosis"][$key]);
                         $dbValue[3] = QuoteValue(DPE_CHARKEY,$_POST["id_injeksi"][$key]);
                         $dbValue[4] = QuoteValue(DPE_NUMERICKEY,$value);
          
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dbKey[1] = 1; 
                         
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
                         $dtmodel->Insert() or die("insert  error");
                         
                         unset($dtmodel);
                         unset($dbValue);
                         unset($dbKey);
                         
                         $statusInjeksi = true;               
                    }
               }
               
               if($statusInjeksi) {
                    // --- insert d folio n folio_split ---
                    $sql = "select biaya_nama, biaya_total, biaya_jenis  
                              from klinik.klinik_biaya 
                              where biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_INJEKSI);
                    $rs = $dtaccess->Execute($sql);
                    $dataBiaya = $dtaccess->Fetch($rs);
                    
                    $lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 
                    
                    $dbTable = "klinik.klinik_folio";
                    $dbField[0] = "fol_id";
                    $dbField[1] = "id_reg";
                    $dbField[2] = "fol_nama";
                    $dbField[3] = "fol_nominal";
                    $dbField[4] = "id_biaya";
                    $dbField[5] = "fol_jenis";
                    $dbField[6] = "id_cust_usr";
                    $dbField[7] = "fol_waktu";
                    $dbField[8] = "fol_lunas";
                    
                         $folioId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHARKEY,$folioId);
                         $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
                         $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_nama"]);
                         $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
                         $dbValue[4] = QuoteValue(DPE_CHARKEY,BIAYA_INJEKSI);
                         $dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_jenis"]);
                         $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);                    
                         $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                         $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
          
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value 
                    
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
                    $dtmodel->Insert() or die("insert  error");
                    
                    unset($dtmodel);
                    unset($dbValue);
                    unset($dbKey);
                    
                    
                    $sql = "select bea_split_nominal, id_split
                              from klinik.klinik_biaya_split
                              where id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_INJEKSI)."
                              and bea_split_nominal > 0";
                    $rs = $dtaccess->Execute($sql);
                    
                    $dbTable = "klinik.klinik_folio_split";
                    $dbField[0] = "folsplit_id";
                    $dbField[1] = "id_fol";
                    $dbField[2] = "id_split";
                    $dbField[3] = "folsplit_nominal";
                    
                    while($row = $dtaccess->Fetch($rs)) {
                    
                         $splitId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHARKEY,$splitId);
                         $dbValue[1] = QuoteValue(DPE_CHARKEY,$folioId);
                         $dbValue[2] = QuoteValue(DPE_CHAR,$row["id_split"]);
                         $dbValue[3] = QuoteValue(DPE_NUMERIC,$row["bea_split_nominal"]);
                         
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value 
                    
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
                         $dtmodel->Insert() or die("insert  error");
                         
                         unset($dtmodel);
                         unset($dbValue);
                         unset($dbKey);
                         
                    }
               }
          }
          
          // --- insrt suster ---
          $sql = "delete from klinik.klinik_perawatan_terapi_suster where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_suster"]) {
               foreach($_POST["id_suster_terapi"] as $key => $value){
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

          
          if($_POST["btnSave"]) {
               $sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_SELESAI."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql); 
               
               
			$sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
					and fol_jenis = ".QuoteValue(DPE_CHAR,STATUS_BEDAH);
			$dtaccess->Execute($sql);
			
			
			// --- nyimpen folio paket operasi ----
			if($_POST["op_paket_biaya"]) {
			
				$sql = "select * from klinik.klinik_operasi_paket where op_paket_id = ".QuoteValue(DPE_CHAR,$_POST["op_paket_biaya"]); 
				$dataBiaya = $dtaccess->Fetch($sql,DB_SCHEMA);
				$folWaktu = date("Y-m-d H:i:s");
				$lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 
				
	
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
				$dbValue[4] = QuoteValue(DPE_CHAR,$lunas);
				$dbValue[5] = QuoteValue(DPE_CHAR,STATUS_BEDAH);
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
			}
			
			
			
               // --- nyimpen paket klaim e ---
               if($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA) { 
               
                    $sql = "select b.paket_klaim_id, paket_klaim_total 
                              from klinik.klinik_biaya_pasien a
                              join klinik.klinik_paket_klaim b on a.id_paket_klaim = b.paket_klaim_id  
                              where a.biaya_pasien_status = ".QuoteValue(DPE_CHAR,STATUS_BEDAH)."
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
                              $dbValue[6] = QuoteValue(DPE_CHAR,STATUS_BEDAH);
                         
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

          if($_POST["btnSave"]) echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          exit();   
     }
     
     $sql = "select op_jenis_nama from klinik.klinik_operasi_jenis where op_jenis_id = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_operasi_jenis"]);
     $dataJenisTindakan= $dtaccess->Fetch($sql);

     
     // --- nyari datanya operasi jenis + harganya---
     $sql = "select op_jenis_id, op_jenis_nama from klinik.klinik_operasi_jenis";
     $dataOperasiJenis = $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi Jenis
     $optOperasiJenis[0] = $view->RenderOption("","[Pilih Jenis Operasi]",$show); 
     for($i=0,$n=count($dataOperasiJenis);$i<$n;$i++) {
          $show = ($_POST["id_op_jenis"]==$dataOperasiJenis[$i]["op_jenis_id"]) ? "selected":"";
          $optOperasiJenis[$i+1] = $view->RenderOption($dataOperasiJenis[$i]["op_jenis_id"],$dataOperasiJenis[$i]["op_jenis_nama"],$show); 
     }
     
     
     // --- buat option nama obat ---
     $sql = "select item_id, item_nama   
               from inventori.inv_item 
               where id_kat_item = ".QuoteValue(DPE_CHAR,KAT_OBAT_INJEKSI)."  
               order by item_id";
     $dataObat = $dtaccess->FetchAll($sql);     
     
     
     // --- buat option teknik injeksi ---
     $sql = "select * from klinik.klinik_injeksi order by injeksi_id";
     $dataInjeksi = $dtaccess->FetchAll($sql);
     

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

     
?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
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


function CheckData(frm) {
     
     if(frm.reg_jenis_pasien.value==<?php echo PASIEN_BAYAR_JAMKESNAS_PUSAT?> && !frm.op_ina_kode.value){
          alert('INA DRG Harus di Pilih');
          frm.op_ina_kode.focus();
          
          return false;
     }
     
     if(document.getElementById('rawat_lab_alergi').value) { alert('Alergi Terisi'); }   
     return true;
}

function ItemDosis(item,akhir){
     GetDosis(item,akhir,'target=div_dosis_'+akhir);
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

function InjeksiTambah(){
     var akhir = eval(document.getElementById('hid_tot_injeksi').value)+1;
     
     $('#tb_injeksi').createAppend(
          'tr', { id:'tr_injeksi_'+akhir+'' },
                ['td', { className: 'tablecontent', align: 'center', style: 'color: black;' },
                    [
                         'label',{} ,[]
                    ],
               'td', { className: 'tablecontent-odd', style: 'color: black;' },
                    [
                         'select', {name:'id_item[]', id:'id_item_'+akhir+'', onchange:'ItemDosis(this.options[this.selectedIndex].value,'+akhir+')' } ,[]
                    ],
               'td', { className: 'tablecontent', align: 'center', style: 'color: black;' },
                    [
                         'label',{} ,[]
                    ],
               'td', { className: 'tablecontent-odd', style: 'color: black;' },
                    [
                         'span', {id:'div_dosis_'+akhir+''}
                    ],
               'td', { className: 'tablecontent', align: 'center', style: 'color: black;' },
                    [
                         'label',{} ,[]
                    ],
               'td', { className: 'tablecontent-odd', style: 'color: black;' },
                    [
                         'select', {name:'id_injeksi[]', id:'id_injeksi_'+akhir+''} ,[]
                    ],
               'td', { align: 'center', style: 'color: black;' },
                    [
                         'input', {type:'button', class:'button', value:'Del', name:'btnDel['+akhir+']', id:'btnDel_'+akhir+''}
                    ]      
                ]                    
     );
     
     $('#btnDel_'+akhir+'').click( function() { Delete(akhir) } );
     
     
     document.getElementById('id_item_'+akhir).options[0]= new Option('[Pilih Obat]', '');
     <?php for($j=0, $o=count($dataObat); $j<$o; $j++) { ?>
          document.getElementById('id_item_'+akhir).options[<?php echo ($j+1);?>]= new Option('<?php echo $dataObat[$j]["item_nama"];?>', '<?php echo $dataObat[$j]["item_id"];?>');
     <?php } ?>
     
     
     document.getElementById('id_injeksi_'+akhir).options[0]= new Option('[Pilih Teknik Injeksi]', '');
     <?php for($j=0, $o=count($dataInjeksi); $j<$o; $j++) { ?>
          document.getElementById('id_injeksi_'+akhir).options[<?php echo ($j+1);?>]= new Option('<?php echo $dataInjeksi[$j]["injeksi_nama"];?>', '<?php echo $dataInjeksi[$j]["injeksi_id"];?>');
     <?php } ?>     
          
     document.getElementById('hid_tot_injeksi').value = akhir;     
}

function Delete(akhir){
     //document.getElementById('hid_suster_del').value += document.getElementById('id_suster_'+akhir).value;
     
     $('#tr_injeksi_'+akhir).remove();
}



function SusterTambah(){
     var akhir = eval(document.getElementById('suster_tot').value)+1;
     
     $('#tb_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'op_suster_terapi_nama[]', id:'op_suster_terapi_nama_'+akhir},[],
                         'a',{ href:'<?php echo $susterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Suster'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_suster_terapi[]', id:'id_suster_terapi_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { SusterDelete(akhir) } );
     document.getElementById('op_suster_terapi_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterDelete(akhir){
     document.getElementById('hid_suster_terapi_del').value += document.getElementById('id_suster_terapi_'+akhir).value;
     
     $('#tr_suster_'+akhir).remove();
}


timer();
</script>


<?php if(!$_GET["id"]) { ?>

<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Tindakan</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetTindakan(STATUS_ANTRI); ?></div>
	</div>
	
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Tindakan</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetTindakan(STATUS_PROSES); ?></div>
	</div>
</div>

<?php } ?>



<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data <?php echo $rawatStatus[$_POST["status"]]; ?></td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="80%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "20%" align="left" class="tablecontent">No. RM<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
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

     </td>
</tr>	
</table>

<BR>

<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

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
     <legend><strong>Rencana Tindakan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataJenisTindakan["op_jenis_nama"];?></td>
          </tr>
	</table>
     </fieldset>
     
     <fieldset>
     <legend><strong>Inputan Injeksi</strong></legend>
     <table width="100%" border="1" cellpadding="2" cellspacing="1" id="tb_injeksi">
          <?php if(!$dataDetailInjeksi) {?>
               <tr id="tr_injeksi_<?php echo $i;?>">
                    <td width="10%" class="tablecontent">Nama Obat</td>
                    <td width="20%" class="tablecontent-odd">
                         <?php 
                              $optObat[0] = $view->RenderOption("","[Pilih Obat]",$show); 
                              for($i=0,$n=count($dataObat);$i<$n;$i++) {
                                   $optObat[$i+1] = $view->RenderOption($dataObat[$i]["item_id"],$dataObat[$i]["item_nama"]); 
                              }
                                                             
                              echo $view->RenderComboBox("id_item[0]","id_item_0",$optObat,null,null,"onChange='ItemDosis(this.options[this.selectedIndex].value,0)'");
                         ?>
                    </td>
                    <td width="10%" class="tablecontent">Dosis</td>
                    <td width="20%" class="tablecontent-odd">
                         <span id="div_dosis_0">
                         </span>
                    </td>
                    <td width="10%" class="tablecontent">Teknik Injeksi</td>
                    <td width="15%" class="tablecontent-odd">
                         <?php
                              $optInjeksi[0] = $view->RenderOption("","[Pilih Teknik Injeksi]",$show); 
                              for($i=0,$n=count($dataInjeksi);$i<$n;$i++) {
                                   $optInjeksi[$i+1] = $view->RenderOption($dataInjeksi[$i]["injeksi_id"],$dataInjeksi[$i]["injeksi_nama"]); 
                              } 
                              
                              echo $view->RenderComboBox("id_injeksi[0]","id_injeksi_0",$optInjeksi);
                         ?>
                    </td>
                    <td width="15%" align="center">
                         <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="InjeksiTambah();">
                         <input name="hid_tot_injeksi" id="hid_tot_injeksi" type="hidden" value="0">
                    </td>               
               </tr>
          <?php } else {?>
               <?php for($i=0,$n=count($dataDetailInjeksi);$i<$n;$i++) { ?>
                    <tr>
                         <td width="10%" class="tablecontent"><?php echo ($i==0)?"Nama Obat":"&nbsp;"?></td>
                         <td width="20%" class="tablecontent-odd">
                              <?php 
                                   $optObat[0] = $view->RenderOption("","[Pilih Obat]",$show); 
                                   for($j=0,$k=count($dataObat);$j<$k;$j++) {
                                        $show = ($dataDetailInjeksi[$i]["id_item"]==$dataObat[$j]["item_id"]) ? "selected":"";
                                        $optObat[$j+1] = $view->RenderOption($dataObat[$j]["item_id"],$dataObat[$j]["item_nama"],$show); 
                                   }
                                    
                                   echo $view->RenderComboBox("id_item[]","id_item_".$i,$optObat,null,null,"onChange='ItemDosis(this.options[this.selectedIndex].value,".$i.")'");
                              ?>
                         </td>
                         <td width="10%" class="tablecontent"><?php echo ($i==0)?"Dosis":"&nbsp;"?></td>
                         <td width="20%" class="tablecontent-odd">
                              <span id="div_dosis_<?php echo $i?>">
                                   <?php echo GetDosis($dataDetailInjeksi[$i]["id_item"],$i,$dataDetailInjeksi[$i]["id_dosis"]);?>
                              </span>
                         </td>                         
                         <td width="10%" class="tablecontent"><?php echo ($i==0)?"Teknik Injeksi":"&nbsp;"?></td>
                         <td width="15%" class="tablecontent-odd">
                              <?php
                                   $optInjeksi[0] = $view->RenderOption("","[Pilih Teknik Injeksi]",$show); 
                                   for($j=0,$k=count($dataInjeksi);$j<$k;$j++) {
                                        $show = ($dataDetailInjeksi[$i]["id_injeksi"]==$dataInjeksi[$j]["injeksi_id"]) ? "selected":"";
                                        $optInjeksi[$j+1] = $view->RenderOption($dataInjeksi[$j]["injeksi_id"],$dataInjeksi[$j]["injeksi_nama"],$show); 
                                   }
                                    
                                   echo $view->RenderComboBox("id_injeksi[0]","id_injeksi_0",$optInjeksi);
                              ?>
                         </td>
                         <td align="center" width="15%">
                              <?php if($i==0) { ?>
                                   <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="InjeksiTambah();">
                              <?php } else { ?>
                                   <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterDelete(<?php echo $i;?>);">
                              <?php } ?>
                              
                              <input name="hid_tot_injeksi" id="hid_tot_injeksi" type="hidden" value="<?php echo $n;?>">
                         </td>
                    </tr>
               <?php } ?>
          <?php }?>
          <tr>
               <td width="30%"  class="tablecontent" align="left">Petugas Injeksi</td>
               <td align="left" class="tablecontent-odd" width="80%" colspan=6>
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php if(!$_POST["op_suster_terapi_nama"]) { ?>
					<tr id="tr_suster_0">
						<td align="left" class="tablecontent-odd" width="40%">
							<?php echo $view->RenderTextBox("op_suster_terapi_nama[]","op_suster_terapi_nama_0","30","100",$_POST["op_suster_terapi_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
							<input type="hidden" id="id_suster_terapi_0" name="id_suster_terapi[]" value="<?php echo $_POST["id_suster_terapi"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="60%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
							<input name="suster_tot" id="suster_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster_terapi"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $view->RenderTextBox("op_suster_terapi_nama[]","op_suster_terapi_nama_".$i,"30","100",$_POST["op_suster_terapi_nama"][$i],"inputField", "readonly",false);?>
								<?php if($edit) { ?>
									<a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
								<?php } ?>
                                        <input type="hidden" id="id_suster_terapi_<?php echo $i;?>" name="id_suster_terapi[]" value="<?php echo $_POST["id_suster_terapi"][$i];?>"/>
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
                    <?php echo $view->RenderHidden("hid_suster_terapi_del","hid_suster_terapi_del",'');?>
				</table>
               </td>
          </tr>
          
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Data Laporan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="20%" class="tablecontent">Dokter</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_dokter_nama","op_dokter_nama","20","100",$_POST["op_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>

               <td align="left" width="20%" class="tablecontent">Asisten Perawat</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_suster_nama","op_suster_nama_0","20","100",$_POST["op_suster_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                    <input type="hidden" id="id_suster_0" name="id_suster" value="<?php echo $_POST["id_suster"];?>"/> <BR>
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
                    <?php echo $view->RenderComboBox("id_op_jenis","id_op_jenis",$optOperasiJenis,null,null,null);?>               
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Kode ICDM</td>
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
               <td align="left" class="tablecontent">Paket Biaya</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <?php echo $view->RenderComboBox("op_paket_biaya","op_paket_biaya",$optOperasiPaket,null,null,null);?>
               </td>
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
               <td align="left" class="tablecontent">Pesan Khusus Dari Operator</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderTextArea("op_pesan","op_pesan","3","40",$_POST["op_pesan"]);?>               
               </td>
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
<input type="hidden" name="op_id" value="<?php echo $_POST["op_id"];?>"/>
<input type="hidden" name="status" value="<?php echo $_POST["status"];?>"/>


</form>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
