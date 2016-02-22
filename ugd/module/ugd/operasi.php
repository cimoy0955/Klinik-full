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
     

 	if(!$auth->IsAllowed("operasi",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("operasi",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "preop.php";

     $tablePreOP = new InoTable("table1","99%","center");
     $tableOP = new InoTable("table1","99%","center");

     $icdPage = "op_icd_find.php?";
     $inaPage = "op_ina_find.php?";
     $dokterPage = "op_dokter_find.php?";
     $susterPage = "op_suster_find.php?";

     if(!$_POST["cbRegulasi"]) $_POST["cbRegulasi"] = "y";

     $plx = new InoLiveX("GetPreop,SetPreop");     


     function GetPreop() {
          global $dtaccess, $view, $tablePreOP, $thisPage, $APLICATION_ROOT,$rawatStatus; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.reg_status like '".STATUS_PREOP."%' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
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
		
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
			$tbContent[$i][$counter][TABLE_ISI] = '<a onClick="ProsesPerawatan(\''.$dataTable[$i]["reg_id"].'\')" href="preop.php?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $rawatStatus[$dataTable[$i]["reg_status"]{1}];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }

          return $tablePreOP->RenderView($tbHeader,$tbContent,$tbBottom);
		
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
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_PREOP.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
	
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                    from klinik.klinik_registrasi a
				join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"]; 
		$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
          
          $diagLink = "perawatan_diag.php?id_cust_usr=".$enc->Encode($dataPasien["id_cust_usr"])."&id_reg=".$enc->Encode($_GET["id_reg"]);


          $sql = "select ref_mata_od_nonkoreksi_visus, ref_mata_od_koreksi_visus,ref_mata_od_koreksi_spheris,ref_mata_od_koreksi_cylinder,ref_mata_od_koreksi_sudut, 
                    ref_mata_os_nonkoreksi_visus, ref_mata_os_koreksi_visus,ref_mata_os_koreksi_spheris,ref_mata_os_koreksi_cylinder,ref_mata_os_koreksi_sudut
                    from klinik.klinik_refraksi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataRefraksi = $dtaccess->Fetch($sql); 
     
          $sql = "select *
                    from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataPemeriksaan = $dtaccess->Fetch($sql); 

          $sql = "select b.icd_nomor, b.icd_nama
                    from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_id
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    order by a.rawat_icd_urut";
          $dataDiagIcd = $dtaccess->FetchAll($sql);
     
          $sql = "select b.ina_kode, b.ina_nama
                    from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    order by a.rawat_ina_urut";
          $dataDiagIna = $dtaccess->FetchAll($sql);
          
          $sql = "select op_jenis_nama from klinik.klinik_operasi_jenis where op_jenis_id = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_operasi_jenis"]);
          $dataJenisTindakan= $dtaccess->Fetch($sql);

	}

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {


		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_SELESAI."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql); 

          echo "<script>document.location.href='".$thisPage."';</script>";
          exit();   

	}
	
	foreach($rawatKeadaan as $key => $value) {
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

     $sql = "select iol_pos_id, iol_pos_nama from klinik.klinik_iol_posisi";
     $dataIOLPosisi = $dtaccess->FetchAll($sql);

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

     $optIOLPos[0] = $view->RenderOption("","[Pilih Posisi IOL]",$show); 
     for($i=0,$n=count($dataIOLPosisi);$i<$n;$i++) {
          $show = ($_POST["rawat_iol_pos"]==$dataIOLPosisi[$i]["iol_pos_id"]) ? "selected":"";
          $optIOLPos[$i+1] = $view->RenderOption($dataIOLPosisi[$i]["iol_pos_id"],$dataIOLPosisi[$i]["iol_pos_nama"],$show); 
     }

     $sql = "select op_jenis_nama from klinik.klinik_operasi_jenis where op_jenis_id = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_operasi_jenis"]);
     $dataJenisTindakan= $dtaccess->Fetch($sql);

     // --- nyari datanya operasi jenis + harganya---
     $sql = "select op_jenis_id, op_jenis_nama from klinik.klinik_operasi_jenis";
     $dataOperasiJenis = $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi Jenis
     $optOperasiJenis[0] = $view->RenderOption("","[Pilih Jenis Operasi]",$show); 
     for($i=0,$n=count($dataOperasiJenis);$i<$n;$i++) {
          $show = ($_POST["rawat_operasi_jenis"]==$dataOperasiJenis[$i]["op_jenis_id"]) ? "selected":"";
          $optOperasiJenis[$i+1] = $view->RenderOption($dataOperasiJenis[$i]["op_jenis_id"],$dataOperasiJenis[$i]["op_jenis_nama"],$show); 
     }

     // --- nyari datanya operasi teknik + harganya---
     $sql = "select op_tek_id, op_tek_nama from klinik.klinik_operasi_teknik";
     $dataOperasiTeknik = $dtaccess->FetchAll($sql);


     // --- nyari datanya komplikasi durante ---
     $sql = "select durop_komp_id, durop_komp_nama from klinik.klinik_duranteop_komplikasi";
     $dataDurop = $dtaccess->FetchAll($sql);

     
     $sql = "select op_paket_id, op_paket_nama from klinik.klinik_operasi_paket";
     $dataOperasiPaket= $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi paket
     $optOperasiPaket[0] = $view->RenderOption("","[Pilih Paket Operasi]",$show); 
     for($i=0,$n=count($dataOperasiPaket);$i<$n;$i++) {
          $show = ($_POST["rawat_operasi_paket"]==$dataOperasiPaket[$i]["op_paket_id"]) ? "selected":"";
          $optOperasiPaket[$i+1] = $view->RenderOption($dataOperasiPaket[$i]["op_paket_id"],$dataOperasiPaket[$i]["op_paket_nama"],$show); 
     }

     $optOperasiPaket[] = $view->RenderOption($dataOperasiPaket[$i]["op_paket_id"],$dataOperasiPaket[$i]["op_paket_nama"],$show);
     
     unset($show);
     
     $optConj[] = $view->RenderOption("","[--]",$show); 
     $optConj[] = $view->RenderOption("","Fornix Base",$show); 
     $optConj[] = $view->RenderOption("","Limbal Base",$show); 
     
     $optCauter[] = $view->RenderOption("","[--]",$show); 
     $optCauter[] = $view->RenderOption("","Minimal",$show); 
     $optCauter[] = $view->RenderOption("","Moderate",$show); 
     $optCauter[] = $view->RenderOption("","Severe",$show); 

     $optIndirectomy[] = $view->RenderOption("","[--]",$show); 
     $optIndirectomy[] = $view->RenderOption("","No",$show); 
     $optIndirectomy[] = $view->RenderOption("","Yes",$show); 

     $optNucleus[] = $view->RenderOption("","[--]",$show); 
     $optNucleus[] = $view->RenderOption("","Irigasi",$show); 
     $optNucleus[] = $view->RenderOption("","Expresi",$show); 
     $optNucleus[] = $view->RenderOption("","Lain-lain",$show); 

     $optCortex[] = $view->RenderOption("","[--]",$show); 
     $optCortex[] = $view->RenderOption("","manual I/A",$show); 
     $optCortex[] = $view->RenderOption("","Vitreus",$show); 
     $optCortex[] = $view->RenderOption("","lain-lain",$show); 

     $optCorneal[] = $view->RenderOption("","[--]",$show); 
     $optCorneal[] = $view->RenderOption("","Vicryl",$show); 
     $optCorneal[] = $view->RenderOption("","Zeide",$show); 
     $optCorneal[] = $view->RenderOption("","Dexon",$show); 
     $optCorneal[] = $view->RenderOption("","Lain-lain",$show); 

     $optTypeSuture[] = $view->RenderOption("","[--]",$show); 
     $optTypeSuture[] = $view->RenderOption("","Interupt",$show); 
     $optTypeSuture[] = $view->RenderOption("","Continous Type",$show); 

     $optCOA[] = $view->RenderOption("","[--]",$show); 
     $optCOA[] = $view->RenderOption("","NSS",$show); 
     $optCOA[] = $view->RenderOption("","AIR",$show); 
     $optCOA[] = $view->RenderOption("","Lain-lain",$show); 

     $optObat[] = $view->RenderOption("","[--]",$show); 
     $optObat[] = $view->RenderOption("","Healon",$show); 
     $optObat[] = $view->RenderOption("","Myostat",$show); 
     $optObat[] = $view->RenderOption("","Atropin",$show); 
     $optObat[] = $view->RenderOption("","Pantocain",$show); 
     $optObat[] = $view->RenderOption("","Efrisel",$show); 
     $optObat[] = $view->RenderOption("","Genta Inj",$show); 
     $optObat[] = $view->RenderOption("","Cortison Inj",$show); 
     $optObat[] = $view->RenderOption("","Metilen Blue",$show); 
     $optObat[] = $view->RenderOption("","Lain-lain",$show); 
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>


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
				url:'perawatan_upload.php',
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
                                   document.getElementById('rawat_mata_foto').value= data.file;
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

	function ajaxFileUploadSketsa()
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
				url:'perawatan_upload.php',
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
                                   document.getElementById('rawat_mata_sketsa').value= data.file;
                                   document.img_sketsa.src='<?php echo $lokasi."/";?>'+data.file;
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
     GetPreop('target=antri_kiri_isi');     
     GetOp('target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}

function ProsesPerawatan(id) {
	SetPreop(id,'type=r');
	timer();
}


function GantiRegulasi() {
     if(document.getElementById('cbRegulasi').checked) {
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
     if(document.getElementById('cbBerhasil').checked) {
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

timer();
</script>



<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian PreOperasi</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetPreop(); ?></div>
	</div>
	
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Operasi</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetOp(); ?></div>
	</div>
</div>




<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Operasi</td>
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
               <td width= "30%" align="left" class="tablecontent">No. RM<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Umur</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Data Laporan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="20%" class="tablecontent">Operator</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_dokter_nama","op_dokter_nama_0","20","100",$_POST["op_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter_0" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>

               <td align="left" width="20%" class="tablecontent">Asisten Perawat</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_suster_nama","op_suster_nama_0","20","100",$_POST["op_suster_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                    <input type="hidden" id="id_suster_0" name="id_suster" value="<?php echo $_POST["id_suster"];?>"/> <BR>

                    <?php echo $view->RenderTextBox("op_suster_nama","op_suster_nama_1","20","100",$_POST["op_suster_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $susterPage;?>&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                    <input type="hidden" id="id_suster_1" name="id_suster" value="<?php echo $_POST["id_suster"];?>"/> <BR>

                    <?php echo $view->RenderTextBox("op_suster_nama","op_suster_nama_2","20","100",$_POST["op_suster_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $susterPage;?>&el=2&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                    <input type="hidden" id="id_suster_2" name="id_suster" value="<?php echo $_POST["id_suster"];?>"/> <BR>
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
               <td align="left" class="tablecontent-odd" width="20%"> 
                    <?php echo $view->RenderTextBox("op_icd_kode","op_icd_kode","10","100",$_POST["op_icd_kode"],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" id="id_icd" name="id_icd" value="<?php echo $_POST["id_icd"];?>"/>
               </td>

               <td align="left" width="20%" class="tablecontent">Jenis Procedure</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_icd_nama","op_icd_nama","50","100",$_POST["op_icd_nama"],"inputField", "readonly",false);?>               
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
               <td align="left" width="20%" class="tablecontent">Tindakan Operasi</td>
               <td align="left" class="tablecontent-odd" width="20%"> 
                    <?php echo $view->RenderTextBox("op_ina_kode","op_ina_kode","30","100",$_POST["op_ina_kode"],"inputField", null,false);?>
               </td>

               <td align="left" width="20%" class="tablecontent">Paket Biaya</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderComboBox("rawat_operasi_paket","rawat_operasi_paket",$optOperasiPaket,null,null,null);?>                              
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Prosedur Operasi</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <table width="100%" border="1" cellpadding="1" cellspacing="1">
                         <tr>
                              <td align="left" class="tablecontent" width="20%">Conj. Flap</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_conj","rawat_conj",$optConj,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Cauter</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_cauter","rawat_cauter",$optCauter,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Corneal Enter</td>
                              <td align="left" class="tablecontent-odd">
                                   Jam <?php echo $view->RenderTextBox("rawat_diag1","rawat_diag1","10","100",$_POST["rawat_diag1"],"inputField", null,false);?>
                                   Diperluas <?php echo $view->RenderTextBox("rawat_diag1","rawat_diag1","10","100",$_POST["rawat_diag1"],"inputField", null,false);?>
                                   Jam <?php echo $view->RenderTextBox("rawat_diag1","rawat_diag1","10","100",$_POST["rawat_diag1"],"inputField", null,false);?> - 
                                   <?php echo $view->RenderTextBox("rawat_diag1","rawat_diag1","10","100",$_POST["rawat_diag1"],"inputField", null,false);?>
                              </td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Indirectomy</td>
                              <td align="left" class="tablecontent-odd">
                                   <?php echo $view->RenderComboBox("rawat_conj","rawat_conj",$optIndirectomy,null,null,null);?>
                                   Tipe <?php echo $view->RenderTextBox("rawat_diag1","rawat_diag1","10","100",$_POST["rawat_diag1"],"inputField", null,false);?>
                                   Jam <?php echo $view->RenderTextBox("rawat_diag1","rawat_diag1","10","100",$_POST["rawat_diag1"],"inputField", null,false);?> 
                              </td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Nucleus Removal</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_conj","rawat_conj",$optNucleus,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Cortex Removal</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_conj","rawat_conj",$optCortex,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Corneal Suture</td>
                              <td align="left" class="tablecontent-odd">
                                   <?php echo $view->RenderComboBox("rawat_conj","rawat_conj",$optCorneal,null,null,null);?>
                                   Ukuran <?php echo $view->RenderTextBox("rawat_diag1","rawat_diag1","10","100",$_POST["rawat_diag1"],"inputField", null,false);?> 
                              </td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Type Suture</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_conj","rawat_conj",$optTypeSuture,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">COA Form With</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_conj","rawat_conj",$optCOA,null,null,null);?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent">Obat/Bahan</td>
                              <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_conj","rawat_conj",$optObat,null,null,null);?></td>
                         </tr>
                    </table>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Komplikasi Durante OP</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <?php for($i=0,$n=count($dataDurop);$i<$n;$i++) { ?>                    
                         <?php echo $view->RenderCheckBox("id_durop_komp[".$dataDurop[$i]["durop_komp_id"]."]","id_durop_komp_".$dataDurop[$i]["durop_komp_id"],"y","null");?>
                         <label for="id_durop_komp_<?php echo $dataDurop[$i]["durop_komp_id"];?>"><?php echo $dataDurop[$i]["durop_komp_nama"];?></label><BR>
                    <?php } ?>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Manajemen Komplikasi</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderTextBox("rawat_diag1","rawat_diag1","50","100",$_POST["rawat_diag1"],"inputField", null,false);?>               
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Jenis IOL</td>
               <td align="left" class="tablecontent-odd" colspan=3><?php echo $view->RenderComboBox("rawat_iol_jenis","rawat_iol_jenis",$optIOLJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Merk</td>
               <td align="left" class="tablecontent-odd" colspan=3><?php echo $view->RenderComboBox("rawat_iol_merk","rawat_iol_merk",$optIOLMerk,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power</td>
               <td align="left" class="tablecontent-odd" colspan=3><?php echo $view->RenderTextBox("rawat_iol_sn","rawat_iol_sn","10","200",$_POST["rawat_iol_sn"],"inputField", null,false);?> Dioptri</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Posisi IOL Terpasang</td>
               <td align="left" class="tablecontent-odd" colspan=3><?php echo $view->RenderComboBox("rawat_iol_pos","rawat_iol_pos",$optIOLPos,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pesan Khusus Dari Operator</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderTextArea("rawat_diag1","rawat_diag1","3","40",$_POST["rawat_diag1"]);?>               
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

</form>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
