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


     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetDiagnostik,SetDiagnostik");     


     function GetDiagnostik($status) {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, z.fol_lunas 
                    from klinik.klinik_registrasi a
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
				left join (
					select distinct fol_lunas, id_reg from klinik.klinik_folio
					where fol_lunas = 'n' and fol_jenis = '".STATUS_PEMERIKSAAN."' 
				) z on a.reg_id = z.id_reg 
                    where a.reg_status like '".STATUS_DIAGNOSTIK.$status."' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
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
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;

		if($status==0) {
			$tbHeader[0][$counterHeader][TABLE_ISI] = "Bayar";
			$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
			$counterHeader++;
		}
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
			if($status==0) {
				if(!$dataTable[$i]["fol_lunas"]) $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesDiagnostik(\''.$dataTable[$i]["reg_id"].'\')"/>';
			} else {
				$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
			}
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
				else $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/off.gif" style="cursor:pointer" alt="Belum Lunas" title="Belum Lunas" border="0"/>';
				$tbContent[$i][$counter][TABLE_ALIGN] = "center";
				$counter++;
			}
          }

          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}

     function SetDiagnostik($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_DIAGNOSTIK.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
	
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin,b.cust_usr_alergi, ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr
                    from klinik.klinik_registrasi a 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"]; 
	}

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {

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
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["diag_kesimpulan"]);
          $dbValue[16] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_usg"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_fundus"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_humpre"]);
          $dbValue[20] = QuoteValue(DPE_CHARKEY,$_POST["diag_av_constant"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["diag_deviasi"]);
          $dbValue[22] = QuoteValue(DPE_CHARKEY,$_POST["diag_rumus"]);
          
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
          
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_PEMERIKSAAN.STATUS_ANTRI."', reg_waktu = CURRENT_TIME  where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql);
          
                    
          // ---- inset ket folio ---//
          $sql = "select * from klinik.klinik_biaya where biaya_jenis = ".QuoteValue(DPE_CHAR,STATUS_DIAGNOSTIK);
          $dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
          $folWaktu = date("Y-m-d H:i:s");
          
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
                      
          for($i=0,$n=count($dataBiaya);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_nama"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_jenis"]);
               $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
               $dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
               $dbValue[8] = QuoteValue(DPE_CHAR,"n");
               $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
                    
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
          
          echo "<script>document.location.href='".$thisPage."';</script>";
          exit();   

	}

     $optionsBase1[0] = $view->RenderOption("1","TRUE",$show);
     $optionsBase1[1] = $view->RenderOption("0","FALSE",$show);

     $optionsBase2[0] = $view->RenderOption("1","TRUE",$show);
     $optionsBase2[1] = $view->RenderOption("0","FALSE",$show);

	
	$optionsNext[0] = $view->RenderOption("O","Operasi Hari Ini",$show);
	$optionsNext[1] = $view->RenderOption("P","Perawatan",$show);

     $lokasiUsg = $APLICATION_ROOT."images/foto_usg";
     $lokasiFundus = $APLICATION_ROOT."images/foto_fundus";
     $lokasiHumpre = $APLICATION_ROOT."images/foto_humpre";

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
          $show = ($_POST["diag_av"]==$dataAv[$i]["bio_av_id"]) ? "selected":"";
          $optAv[$i+1] = $view->RenderOption($dataAv[$i]["bio_av_id"],$dataAv[$i]["bio_av_nama"],$show); 
     }
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>

<script type="text/javascript">

	function ajaxFileUpload(fileupload,hidval,img)
	{
		var lokasi = Array();
		
		lokasi['img_usg'] = '<?php echo $lokasiUsg;?>';
		lokasi['img_fundus'] = '<?php echo $lokasiFundus;?>';
		lokasi['img_humpre'] = '<?php echo $lokasiHumpre;?>';
		
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
     GetDiagnostik(0,'target=antri_kiri_isi');     
     GetDiagnostik(1,'target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}

function ProsesDiagnostik(id) {
	SetDiagnostik(id,'type=r');
	timer();
}

timer();


</script>



<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Diagnostik</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetDiagnostik(0); ?></div>
	</div>
	
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Diagnostik</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetDiagnostik(1); ?></div>
	</div>
</div>




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


     <fieldset>
     <legend><strong>USG</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
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
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_lensa","diag_lensa","15","15",$_POST["diag_lensa"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Kesimpulan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_kesimpulan","diag_kesimpulan","5","50",$_POST["diag_kesimpulan"],"inputField", null,null);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Upload Foto</strong></legend>
     <table width="60%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="33%" align="left" class="tablecontent">Gambar USG</td>
               <td width="33%" align="left" class="tablecontent">Gambar Fundus</td>
               <td width="33%" align="left" class="tablecontent">Gambar Humpre</td>
          </tr>
          <tr>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_usg" id="img_usg" src="<?php echo $fotoName;?>"  border="1">
                    <input type="hidden" name="diag_gambar_usg" id="diag_gambar_usg" value="<?php echo $_POST["diag_gambar_usg"];?>">
               </td>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_fundus" id="img_fundus" src="<?php echo $sketsaName;?>"  border="1">
                    <input type="hidden" name="diag_gambar_fundus" id="diag_gambar_fundus" value="<?php echo $_POST["diag_gambar_fundus"];?>">
               </td>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_humpre" id="img_humpre" src="<?php echo $fotoName;?>"  border="1">
                    <input type="hidden" name="diag_gambar_humpre" id="diag_gambar_humpre" value="<?php echo $_POST["diag_gambar_humpre"];?>">
               </td>
          </tr>
          <tr>
               <td colspan=3 align="center">
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

<?php echo $view->SetFocus("diag_k1_nilai");?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
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
