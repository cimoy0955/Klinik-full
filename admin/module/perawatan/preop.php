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
	}

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {


		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_OPERASI.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
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

     // --- nyari datanya rumuys ---
     $sql = "select bio_av_id, bio_av_nama from klinik.klinik_biometri_av order by bio_av_nama";
     $dataAv = $dtaccess->FetchAll($sql);

     // -- bikin combonya av
     $optAv[0] = $view->RenderOption("","[Pilih AV Constant Yg Dipakai]",$show); 
     for($i=0,$n=count($dataAv);$i<$n;$i++) {
          $show = ($_POST["diag_av"]==$dataAv[$i]["bio_av_id"]) ? "selected":"";
          $optAv[$i+1] = $view->RenderOption($dataAv[$i]["bio_av_id"],$dataAv[$i]["bio_av_nama"],$show); 
     }

?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>


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
     <legend><strong>Rencana Pemakaian IOL</strong></legend>
     <table width="40%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis IOL</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_iol_jenis","rawat_iol_jenis",$optIOLJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Merk</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_iol_merk","rawat_iol_merk",$optIOLMerk,null,null,null);?></td>
          </tr>
<!--
          <tr>
               <td align="left" class="tablecontent" width="35%">Serial Number</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("rawat_iol_sn","rawat_iol_sn","50","200",$_POST["rawat_iol_sn"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Type</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("rawat_iol_type","rawat_iol_type","50","200",$_POST["rawat_iol_type"],"inputField", null,false);?></td>
          </tr>
-->     

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
     <legend><strong>Diagnose - ICD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcd[0]["icd_nomor"]." ".$dataDiagIcd[0]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcd[1]["icd_nomor"]." ".$dataDiagIcd[1]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcd[2]["icd_nomor"]." ".$dataDiagIcd[2]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 4</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcd[3]["icd_nomor"]." ".$dataDiagIcd[3]["icd_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - INA</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIna[0]["ina_kode"]." ".$dataDiagIna[0]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIna[1]["ina_kode"]." ".$dataDiagIna[1]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIna[2]["ina_kode"]." ".$dataDiagIna[2]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 4</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIna[3]["ina_kode"]." ".$dataDiagIna[3]["ina_nama"];?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Keratometri</strong></legend>
     <table width="80%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="5%" align="center">&nbsp;</td>
               <td width="25%" align="center">Nilai</td>
               <td width="30%" align="center">OD</td>
               <td width="30%" align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">K1</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k1_nilai","diag_k1_nilai","15","15",$_POST["diag_k1_nilai"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k1_od","diag_k1_od","30","30",$_POST["diag_k1_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k1_os","diag_k1_os","30","30",$_POST["diag_k1_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">K2</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k2_nilai","diag_k2_nilai","15","15",$_POST["diag_k2_nilai"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k2_od","diag_k2_od","30","30",$_POST["diag_k2_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k2_os","diag_k2_os","30","30",$_POST["diag_k2_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Biometri</strong></legend>
     <table width="70%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="15%" align="center">&nbsp;</td>
               <td width="45%" align="center">OD</td>
               <td width="45%" align="center">OS</td>
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
               <td align="left" class="tablecontent">A.Constan</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("diag_av_constant","diag_av_constant",$optAv,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Standart Deviasi</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","30","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Data Pasien Hari Ini</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="20%" class="tablecontent">Keadaan Umum</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","10","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Tensimeter</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","10","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Nadi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","10","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Pernafasan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","10","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Gula Darah Acak</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","10","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Darah Lengkap</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","10","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Status Lokal Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","10","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Tonometri</td>
               <td align="left" class="tablecontent-odd"> 
                    <?php echo $dataPemeriksaan["rawat_tonometri_scale_od"]; ?> / 
                    <?php echo $dataPemeriksaan["rawat_tonometri_weight_od"]; ?> g = 
                    <?php echo $dataPemeriksaan["rawat_tonometri_pressure_od"]; ?> mmHG
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Anestesis</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_jenis","rawat_anestesis_jenis",$optAnestesisJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Obat Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_obat","rawat_anestesis_obat",$optAnestesisObat,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Dosis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("rawat_anestesis_dosis","rawat_anestesis_dosis","50","200",$_POST["rawat_anestesis_dosis"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Komplikasi Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_komp","rawat_anestesis_komp",$optAnestesisKomplikasi,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Premedikasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_pre","rawat_anestesis_pre",$optAnestesisPremedikasi,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Regulasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="20%" class="tablecontent">Perlu Regulasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderCheckBox("cbRegulasi","cbRegulasi","y","null",($_POST["cbRegulasi"] == "y")?"checked":"",'onClick="GantiRegulasi();"')?>&nbsp;&nbsp;&nbsp;</td>
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
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_od","diag_acial_od","30","30",$_POST["diag_acial_od"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_os","diag_acial_os","30","30",$_POST["diag_acial_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent">Tonometri</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_od","diag_acial_od","30","30",$_POST["diag_acial_od"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_os","diag_acial_os","30","30",$_POST["diag_acial_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent-odd" colspan=4><label for="cbBerhasil">Berhasil Diregulasi?</label><?php echo $view->RenderCheckBox("cbBerhasil","cbBerhasil","y","null",($_POST["cbBerhasil"] == "y")?"checked":"",'onClick="GantiBerhasil();"')?>&nbsp;&nbsp;&nbsp;</td>
          </tr>
	</table>

     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbCatatan" style="display:none">
          <tr>
               <td align="left" width="20%" class="tablecontent">Catatan Untuk OK</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","30","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
          </tr>
     </table>
     
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbTerapi" style="display:none">
          <tr class="subheader">
               <td width="100%" align="center" colspan=3>Tabel Terapi</td>
          </tr>	
          <tr>
               <td align="right" class="tablecontent">1</td>
               <td align="left" class="tablecontent">Obat</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_od","diag_acial_od","30","30",$_POST["diag_acial_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">2</td>
               <td align="left" class="tablecontent">Saran</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_od","diag_acial_od","30","30",$_POST["diag_acial_od"],"inputField", null,false);?></td>
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
