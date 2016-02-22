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
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     

 	if(!$auth->IsAllowed("rawat_inap",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("rawat_inap",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "check_out.php";
     $findPage = "item_find.php?";
     $icdPage = "icd_find2.php?";
     $procPage = "proc_find.php?";

     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetFolio");     
     
     function GetFolio() {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT,$rawatStatus,$bayarPasien; 
               
          $sql = "select a.reg_id, a.reg_jenis_pasien, b.rawatinap_waktu_masuk, b.rawatinap_tanggal_masuk,b.rawatinap_tanggal_keluar, c.cust_usr_nama
                  from klinik.klinik_registrasi a
                  left join klinik.klinik_rawatinap b on b.id_reg = a.reg_id
                  left join global.global_customer_user c on c.cust_usr_id = a.id_cust_usr
                  where a.reg_status like '".STATUS_RAWATINAP."%' order by b.rawatinap_tanggal_masuk asc";
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataTable = $dtaccess->FetchAll($rs);
	
    //die();
	//	$row = -1;
	//	for($i=0,$n=count($dataTable);$i<$n;$i++) {
			 
		//	if($dataTable[$i]["id_reg"]!=$dataTable[$i-1]["id_reg"] || $dataTable[$i]["fol_jenis"]!=$dataTable[$i-1]["fol_jenis"]) {
		/*	if($dataTable[$i]["reg_id"]!=$dataTable[$i-1]["reg_id"] ){
      	$row++;
				$data[$row] = $dataTable[$i]["id_reg"];
				
				$reg[$dataTable[$i]["id_reg"]] = $dataTable[$i]["id_reg"];
		//		$fol[$dataTable[$i]["id_reg"]][$row] = $dataTable[$i]["fol_jenis"];
				$biaya[$dataTable[$i]["id_reg"]][$row] = $dataTable[$i]["id_biaya"]; 
				$nama[$dataTable[$i]["id_reg"]] = $dataTable[$i]["cust_usr_nama"];
				$waktu[$dataTable[$i]["id_reg"]] = $dataTable[$i]["rawatinap_tanggal_masuk"];
			}
		}
      */   
       $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Checkout";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++;
          
         for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
						
			  // $editPage = $thisPage."?jenis=".$fol[$data[$i]][$i]."&id_reg=".$reg[$data[$i]]."&waktu=".$waktu[$data[$i]];
				
        //if($fol[$data[$i]][$i]==STATUS_CEKOUT)
				//$editPage .= "&biaya=".$biaya[$data[$i]][$i];
				
    			$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_okay.png" alt="Proses" title="Proses" border="0"/></a>';               
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$counter++;
    			 
    			$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["rawatinap_tanggal_masuk"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$bayarPasien[$dataTable[$i]["reg_jenis_pasien"]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$counter++;
    			
            }
          #return $sql;
          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}
        if($_POST["btnSaveTambah"]){
            
            $id_reg = $_POST["id_reg"];
            $skr = $_POST["waktunya"];
           
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
               $dbField[11] = "id_biaya_tambahan";
               $dbField[12] = "fol_jumlah";
               $dbField[13] = "fol_nominal_satuan";
               
               if(!$folioId) $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
               $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_nama"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["fol_jenis"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
               $dbValue[7] = QuoteValue(DPE_DATE,$skr);
               $dbValue[8] = QuoteValue(DPE_CHAR,'n');
               $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
               $dbValue[10] = QuoteValue(DPE_DATE,'');
               $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["biaya_id_tambahan"]);
               $dbValue[12] = QuoteValue(DPE_NUMERIC,'1');
               $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
               			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               if ($_POST["btnSaveTambah"]) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
     
               $editPage = $thisPage."?jenis=".$_POST["fol_jenis"]."&id_reg=".$id_reg."&biaya=".$_POST["biaya_id"];
               header("location:".$editPage);
               exit();        
        
     }
	
	if($_GET["id_reg"]) {
//	echo $_GET["id_reg"];
		$sql = "select a.reg_jenis_pasien, cust_usr_alamat, cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin,b.cust_usr_alergi,
		    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.*, d.*
                    from klinik.klinik_registrasi a 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
		    left join klinik.klinik_rawatinap c on c.id_reg = a.reg_id
		    left join klinik.klinik_biaya d on d.biaya_jenis = c.id_kategori_kamar
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
	       $_POST["id_reg"] = $_GET["id_reg"]; 
	       $_POST["fol_jenis"] = $_GET["jenis"]; 
	       $_POST["id_biaya"] = $_GET["biaya"]; 
	       $_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
	       $_POST["rawatinap_id"] = $dataPasien["rawatinap_id"];
	       $_POST["rawatinap_tanggal_masuk"] = format_date($dataPasien["rawatinap_tanggal_masuk"]);
	       if(!$_POST["rawatinap_tanggal_keluar"]) $_POST["rawatinap_tanggal_keluar"] = date("d-m-Y");
	       $_POST["kelas_inap"] = $dataPasien["biaya_nama"];
	       $_POST["biaya_jenis_inap"] = $dataPasien["biaya_jenis"];
	       
	       if($_POST["rawatinap_tanggal_masuk"]==$_POST["rawatinap_tanggal_keluar"]) $_POST["rawatinap_jumlah_hari"] = 1;
	       else $_POST["rawatinap_jumlah_hari"] = (DateDiff($dataPasien["rawatinap_tanggal_masuk"],date('Y-m-d')) + 1);
	       $_POST["biaya_total"] = $dataPasien["biaya_total"] * $_POST["rawatinap_jumlah_hari"];
	  //$sql = "select * from klinik.klinik_folio
		//	where fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])."
		//	and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n' "; 
	
  	$sql = "select * from klinik.klinik_folio
			where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and fol_lunas = 'n' ";

		$rs = $dtaccess->Execute($sql);
		$dataFolio = $dtaccess->FetchAll($rs);
		//echo $sql;
		
		$sql = "select rawat_id from klinik.klinik_perawatan where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$rs_rawat = $dtaccess->Execute($sql);
		$dataRawat = $dtaccess->Fetch($rs_rawat);
		//echo $sql;
// --- icd od
          $sql = "select icd_nama,icd_nomor, icd_id from klinik.klinik_perawatan_icd a
                    join klinik.klinik_icd b on a.id_icd = b.icd_nomor where id_rawat = ".QuoteValue(DPE_CHAR,$dataRawat["rawat_id"])." and rawat_icd_odos = 'OD'
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
                    join klinik.klinik_icd b on a.id_icd = b.icd_nomor where id_rawat = ".QuoteValue(DPE_CHAR,$dataRawat["rawat_id"])." and rawat_icd_odos = 'OS'
                    order by rawat_icd_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_icd_os_id"][$i] = $row["icd_id"];
               $_POST["rawat_icd_os_kode"][$i] = $row["icd_nomor"];
               $_POST["rawat_icd_os_nama"][$i] = $row["icd_nama"];
               $i++;

          }

          // --- prosedur
          $sql = "select prosedur_kode, prosedur_nama from klinik.klinik_perawatan_prosedur a
                    join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode where id_rawat = ".QuoteValue(DPE_CHAR,$dataRawat["rawat_id"])." order by rawat_prosedur_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_prosedur_kode"][$i] = $row["prosedur_kode"];
               $_POST["rawat_prosedur_nama"][$i] = $row["prosedur_nama"];
               $i++;

          }
	}

	// ----- update data ----- //
	
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
	  $sql = "select * from klinik.klinik_biaya where biaya_jenis = ".QuoteValue(DPE_CHAR,$_POST["biaya_jenis_inap"])." or biaya_kode = ".QuoteValue(DPE_CHAR,$_POST["id_visite"]);
	  $rs = $dtaccess->Execute($sql);
	  $dataBiaya = $dtaccess->FetchAll($rs);
	  //
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
	  
	  //--- nyimpen data pembayaran Kelas Rawat Inap
	  $folId = $dtaccess->GetTransID();
	  $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
	  $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	  $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[0]["biaya_nama"]);
	  $dbValue[3] = QuoteValue(DPE_NUMERIC,($_POST["rawatinap_jumlah_hari"] * $dataBiaya[0]["biaya_total"]));
	  $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya[0]["biaya_id"]);
	  $dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya[0]["biaya_jenis"]);
	  $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
	  $dbValue[7] = QuoteValue(DPE_DATE,getdateToday());
	  $dbValue[8] = QuoteValue(DPE_CHAR,"n");
	  $dbValue[9] = QuoteValue(DPE_NUMERIC,$_POST["rawatinap_jumlah_hari"]);
	  $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[0]["biaya_total"]);
	  
	  //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
	  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
	  
	  $dtmodel->Insert() or die("insert error"); 
	  
	  unset($dtmodel);
	  unset($dbValue);
	  unset($dbKey);
	  
	  //--- nyimpen data pembayaran Biaya Visite
	  if($_POST["id_visite"]!="--"){
	       $folId = $dtaccess->GetTransID();
	       $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
	       $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	       $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[1]["biaya_nama"]);
	       $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["biaya_visite"]);
	       $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya[1]["biaya_id"]);
	       $dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya[1]["biaya_jenis"]);
	       $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
	       $dbValue[7] = QuoteValue(DPE_DATE,getdateToday());
	       $dbValue[8] = QuoteValue(DPE_CHAR,"n");
	       $dbValue[9] = QuoteValue(DPE_NUMERIC,$_POST["rawatinap_jumlah_hari"]);
	       $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[1]["biaya_total"]);
	       
	       //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
	       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
	       
	       $dtmodel->Insert() or die("insert error"); 
	       
	       unset($dtmodel);
	       unset($dbValue);
	       unset($dbKey);
	  }
	  unset($dbField);
	  unset($dbTable);
		       
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
	  
	  $sql = "select id_bed from klinik.klinik_rawatinap where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	  $dataBed = $dtaccess->Fetch($sql);
	  
	  $dtaccess->Execute("update klinik.klinik_kamar_bed set bed_reserved='n' where bed_id =".QuoteValue(DPE_CHAR,$dataBed["id_bed"]));
			   
	   $sql = "update klinik.klinik_folio set fol_dibayar = fol_nominal, fol_lunas = 'n', fol_dibayar_when = CURRENT_TIMESTAMP where fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	   $dtaccess->Execute($sql); 
	   $sql = "update klinik.klinik_registrasi set reg_waktu = CURRENT_TIME, reg_status = ".QuoteValue(DPE_CHAR,STATUS_CEKOUT."0")."  where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	   $dtaccess->Execute($sql);
	   $tglnya = explode("-",$_POST["rawatinap_tanggal_keluar"]);
	   $sql = "update klinik.klinik_rawatinap set rawatinap_waktu_keluar = ".QuoteValue(DPE_DATE,date("Y-m-d H:i:s", mktime(0,0,0,$tglnya[1],$tglnya[0],$tglnya[2])));
	   $dtaccess->Execute($sql);
	   
	   // -- ini insert ke tabel rawat icd
	   $sql = "select rawat_id from klinik.klinik_perawatan where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	   $rs_rawat = $dtaccess->Execute($sql);
	   $dataRawat = $dtaccess->Fetch($rs_rawat);
	   
	   $sql = "delete from klinik.klinik_perawatan_icd where id_rawat = ".QuoteValue(DPE_CHAR,$dataRawat["rawat_id"]);
	   $dtaccess->Execute($sql); 

	  $dbTable = "klinik.klinik_perawatan_icd";
	  $dbField[0] = "rawat_icd_id";   // PK
	  $dbField[1] = "id_rawat";
	  $dbField[2] = "id_icd";
	  $dbField[3] = "rawat_icd_urut";
	  $dbField[4] = "rawat_icd_odos";
	  
	  for($i=0,$n=count($_POST["rawat_icd_od_kode"]);$i<$n;$i++) {
	       $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
	       $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawat["rawat_id"]);
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
	       $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawat["rawat_id"]);
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
	  
	  //-- insert ke tabel rawat prosedur --//
	  $sql = "delete from klinik.klinik_perawatan_prosedur where id_rawat = ".QuoteValue(DPE_CHAR,$dataRawat["rawat_id"]);
		$dtaccess->Execute($sql); 

	  $dbTable = "klinik.klinik_perawatan_prosedur";
	  $dbField[0] = "rawat_prosedur_id";   // PK
	  $dbField[1] = "id_rawat";
	  $dbField[2] = "id_prosedur";
	  $dbField[3] = "rawat_prosedur_urut";
	  
	  for($i=0,$n=count($_POST["rawat_prosedur_kode"]);$i<$n;$i++) {
	       $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
	       $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataRawat["rawat_id"]);
	       $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_prosedur_kode"][$i]);
	       $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);

	       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

	       if($_POST["rawat_prosedur_kode"][$i]) $dtmodel->Insert() or die("insert  error");
	       
	       unset($dtmodel);
	       unset($dbValue);
	       unset($dbKey);
	  }
     
	  //-- end insert tabel rawat prosedur --//
          
	}
	
	  if($_POST["btnHapus"]) { 
		$sql = "delete from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql);
	    
	  }

	  // ---- option combo box tarif visite ---- //
	  $sql_visite = "select * from klinik.klinik_biaya where upper(biaya_nama) like 'VISITE%' ORDER BY biaya_kode";
	  $rs_visite = $dtaccess->Execute($sql_visite);
	  $optVisite[] = $view->RenderOption("--","Pilih Biaya Visite",$show);
	  while($dataVisite = $dtaccess->Fetch($rs_visite)){
	       unset($show); $i++;
	       if($dataVisite["biaya_id"]==$_POST["id_visite"]) $show="selected";
	       $optVisite[] = $view->RenderOption($dataVisite["biaya_kode"],$dataVisite["biaya_nama"],$show);
	       $biayaTotal[$dataVisite["biaya_kode"]] = currency_format($dataVisite["biaya_total"]);
	  }
	  
	  
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitThickBox(); ?>

<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetFolio('target=antri_kiri_isi');     
     mTimer = setTimeout("timer()", 10000);
}

timer();

function gantiVisite(nilai){
     var jmlHari = document.getElementById('rawatinap_jumlah_hari').value * 1;
     var biayaVisite;
     var totalBiaya;
     if (nilai=="--") {
	  document.getElementById("span_visite").innerHTML = "&nbsp;";
	  document.getElementById("biaya_visite").value = "0";
     }else if (nilai=="RI3-03") {
	  biayaVisite = <?php echo stripcurrency($biayaTotal["RI3-03"]);?> * 1;
	  totalBiaya = jmlHari * biayaVisite;
	  document.getElementById("span_visite").innerHTML = "Rp&nbsp;"+formatCurrency(totalBiaya);
	  document.getElementById("biaya_visite").value = totalBiaya;
     }else if (nilai=="RI3-04") {
	  biayaVisite = <?php echo stripcurrency($biayaTotal["RI3-04"]);?> * 1;
	  totalBiaya = jmlHari * biayaVisite;
	  document.getElementById("span_visite").innerHTML = "Rp&nbsp;"+formatCurrency(totalBiaya);
	  document.getElementById("biaya_visite").value = totalBiaya;
     }
}

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=800,height=600,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=800,height=600,left=100,top=100');
		} else {
			_wnd_new.focus();
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


function CheckSimpan() {
     if(confirm('Cetak Resume?')) BukaWindow('kasir_cetak.php?jenis=<?php echo $_POST["fol_jenis"];?>&id_reg=<?php echo $_POST["id_reg"]?>','Resume Cek Out');
     alert('Pasien check out akan menuju kasir');
     return true;
}

function GantiHarga(harga) {
     var duit = document.getElementById('txtHargaSatuan').value.toString().replace(/\,/g,"");
     document.getElementById('txtHargaTotal').value = formatCurrency(duit*harga);
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

</script>



<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
		<div class="tableheader">Data Pasien</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetFolio(); ?></div>
</div>




<?php if($dataPasien) {  ?>
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
	   </table>
     </fieldset>
     <fieldset>
     <legend><strong>Detil Rawat Inap</strong></legend>
      <table width="100%" border="0" cellpadding="1" cellspacing="1">
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Masuk Tgl.</td>
                    <td align="left" width="80%" class="tablecontent-odd">
			 <?php echo $view->RenderTextBox("rawatinap_tanggal_masuk","rawatinap_tanggal_masuk","15","20",$_POST["rawatinap_tanggal_masuk"],"inputField",null,false);?>
		    </td>
	       </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Keluar Tgl.</td>
                    <td align="left" width="80%" class="tablecontent-odd">
			 <?php echo $view->RenderTextBox("rawatinap_tanggal_keluar","rawatinap_tanggal_keluar","15","20",$_POST["rawatinap_tanggal_keluar"],"inputField",null,false);?>
		    </td>
	       </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Jumlah Hari Inap</td>
                    <td align="left" width="80%" class="tablecontent-odd">
			 <?php echo $view->RenderTextBox("rawatinap_jumlah_hari","rawatinap_jumlah_hari","15","20",$_POST["rawatinap_jumlah_hari"],"inputField",null,true);?>
		    </td>
	       </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Kelas</td>
                    <td align="left" width="80%" class="tablecontent-odd">&nbsp;
			 <label><?php echo $_POST["kelas_inap"];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp&nbsp;<?php echo currency_format($_POST["biaya_total"]);?></label><input type="hidden" name="biaya_totalnya" value="<?php $_POST["biaya_total"];?>" />
			 <input type="hidden" name="biaya_jenis_inap" id="biaya_jenis_inap" value="<?php echo $_POST["biaya_jenis_inap"];?>" />
		    </td>
	       </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Tarif</td>
                    <td align="left" width="80%" class="tablecontent-odd">
			 <?php echo $view->RenderComboBox("id_visite","id_visite",$optVisite,"inputfield",null,"onChange=\"gantiVisite(this.options[this.selectedIndex].value);\""); ?>
			 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="span_visite">&nbsp;</span><input type="hidden" name="biaya_visite" id="biaya_visite" value="<?php echo $_POST["biaya_visite"];?>" />
		    </td>
	       </tr>
      </table>
     </fieldset>
     
     <!--<form name="frmTambah" method="POST" action="<?php //echo $_SERVER["PHP_SELF"]?>" > -->
     <fieldset>
     <legend><strong>Tambah</strong></legend>
      <table width="100%" border="0" cellpadding="1" cellspacing="1">
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Item&nbsp;</td>
                    <td align="left" width="80%" class="tablecontent-odd">
                      <?php echo $view->RenderTextBox("biaya_nama","biaya_nama","30","100",$_POST["biaya_nama"],"inputField", "readonly",false);?>
                     
                      <a href="<?php echo $findPage?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Item"><img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" /></a>    
                       <input type="hidden" id="biaya_id" name="biaya_id" value="<?php echo $_POST["biaya_id"];?>"/>             
                </td>
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Jumlah</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         
                         <?php echo $view->RenderTextBox("txtJumlah","txtJumlah","3","3",$_POST["txtJumlah"],"curedit", "",false,'onChange=GantiHarga(this.value)');?>
                    </td>					
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Biaya</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("txtHargaSatuan","txtHargaSatuan","10","10",$_POST["txtHargaSatuan"],"curedit", "readonly",false);?>
                    </td>					
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Total Biaya</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("txtHargaTotal","txtHargaTotal","10","10",$_POST["txtHargaTotal"],"curedit", "readonly",false);?>
                    </td>					
               </tr>
               <tr>
                    <td colspan="4" align="left" class="tblCol">
                         <input type="submit" name="btnSaveTambah" value="Simpan" class="button">
                         <input type="hidden" name="biaya_id_tambahan" id="biaya_id_tambahan" value="<?php echo $_POST["biaya_id_tambahan"];?>" />
                         <input type="hidden" name="fol_jenis" id="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>" />  
                         <input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>    
                         <input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/> 
                         <input type="hidden" name="waktunya" value="<?php echo $_GET["waktu"];?>" />
                         <input type="hidden" name="id_biaya" value="<?php echo $_GET["biaya"];?>" />
                    </td>
               </tr>
            </table>
     </fieldset>
     <!--</form>-->

     <fieldset>
     <legend><strong>Diagnosis Akhir - ICD - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[0]","rawat_icd_od_kode_0","10","100",$_POST["rawat_icd_od_kode"][0],"inputField", null,false,"onkeyup=\"lihat(this.value);\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[1]","rawat_icd_od_kode_1","10","100",$_POST["rawat_icd_od_kode"][1],"inputField", null,false," onkeyup=\"lihat1(this.value)\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[2]","rawat_icd_od_kode_2","10","100",$_POST["rawat_icd_od_kode"][2],"inputField", null,false," onkeyup=\"lihat4(this.value)\"");?>
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
     <legend><strong>Diagnosis Akhir - ICD - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[0]","rawat_icd_os_kode_0","10","100",$_POST["rawat_icd_os_kode"][0],"inputField", null,false,"onkeyup=\"lihat2(this.value)\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[1]","rawat_icd_os_kode_1","10","100",$_POST["rawat_icd_os_kode"][1],"inputField", null,false,"onkeyup=\"lihat3(this.value)\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[2]","rawat_icd_os_kode_2","10","100",$_POST["rawat_icd_os_kode"][2],"inputField", null,false," onkeyup=\"lihat6(this.value)\"");?>
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
	    <legend><strong>Prosedur Terakhir</strong></legend>
	    <table width="100%" border="1" cellpadding="4" cellspacing="1">
		   <tr>
			  <td align="center" class="subheader" width="5%"></td>
			  <td align="center" class="subheader" width="25%">Kode</td>
			  <td align="center" class="subheader">Keterangan</td>
		   </tr>
		   <tr>
			  <td align="center" class="tablecontent" width="5%">1</td>
			  <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[0]","rawat_prosedur_kode_0","10","100",$_POST["rawat_prosedur_kode"][0],"inputField", null,false,"onkeyup=\"lookProc(this.value);\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[1]","rawat_prosedur_kode_1","10","100",$_POST["rawat_prosedur_kode"][0],"inputField", null,false,"onkeyup=\"lookProc1(this.value);\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[2]","rawat_prosedur_kode_2","10","100",$_POST["rawat_prosedur_kode"][2],"inputField", null,false,"onkeyup=\"lookProc2(this.value);\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[3]","rawat_prosedur_kode_3","10","100",$_POST["rawat_prosedur_kode"][3],"inputField", null,false,"onkeyup=\"lookProc3(this.value);\"");?>
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
     <legend><strong>Data Tagihan</strong></legend>
     <table width="80%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="5%" align="center">No</td>
               <td width="30%" align="center">Layanan</td>
                <td width="15%" align="center">Jenis</td>
               <td width="10%" align="center">Jumlah</td>
               <td width="20%" align="center">Biaya</td>
               <td width="20%" align="center">Total</td>
          </tr>	
          <?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { $total+=$dataFolio[$i]["fol_nominal"];?>
			<tr>
				<td align="right" class="tablecontent"><?php echo ($i+1); ?></td>
				<td align="left" class="tablecontent-odd"><?php echo $dataFolio[$i]["fol_nama"];?></td>
				<td align="left" class="tablecontent-odd"><?php echo $dataFolio[$i]["fol_jenis"];?></td>
				<td align="right" class="tablecontent-odd"><?php echo "1";?></td>
				<td align="right" class="tablecontent-odd"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
				<td align="right" class="tablecontent-odd"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
			</tr>
		<?php } ?>
          <tr>
               <td align="right" class="tablesmallheader" colspan=4>Total</td>
               <td align="right" class="tablesmallheader"><?php echo currency_format($total);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="left">
				<?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Check Out","button",false,"onClick=\"CheckSimpan();\"");?>
				<?php #echo $view->RenderButton(BTN_BUTTON,"btnPrint","btnPrint","Cetak","button",false,'onClick="BukaWindow(\'kasir_cetak.php?jenis='.$_POST["fol_jenis"].'&id_reg='.$_POST["id_reg"].'\',\'Cetak Invoice\')"',null);?>
				<?php if($_POST["fol_jenis"] == STATUS_REGISTRASI) { ?>
				      <?php #echo $view->RenderButton(BTN_SUBMIT,"btnHapus" ,"btnHapus","Batal Registrasi","button",false,null);?>
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
