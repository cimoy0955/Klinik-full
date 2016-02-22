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
     

 	if(!$auth->IsAllowed("refraksi",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("refraksi",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "refraksi.php";


     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetRefraksi,SetRefraksi");     


     function GetRefraksi($status) {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, z.fol_lunas 
				from klinik.klinik_registrasi a
				left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
				left join (
					select distinct fol_lunas, id_reg from klinik.klinik_folio
					where fol_lunas = 'n' and fol_jenis = '".STATUS_REGISTRASI."' 
				) z on a.reg_id = z.id_reg 
                    where a.reg_status like '".STATUS_REFRAKSI.$status."' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
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
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

		if($status==0) {
			$tbHeader[0][$counterHeader][TABLE_ISI] = "Bayar";
			$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
			$counterHeader++;
		}
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
			if($status==0) {
				if(!$dataTable[$i]["fol_lunas"]) $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesRefraksi(\''.$dataTable[$i]["reg_id"].'\')"/>';
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

     function SetRefraksi($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_REFRAKSI.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
	
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin, ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr
                    from klinik.klinik_registrasi a 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"]; 
	}

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {

          $dbTable = "klinik.klinik_refraksi";
          $dbField[0] = "ref_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "ref_keluhan";
          $dbField[3] = "ref_mata_od_nonkoreksi_visus";
          $dbField[4] = "ref_mata_od_koreksi_spheris";
          $dbField[5] = "ref_mata_od_koreksi_cylinder";
          $dbField[6] = "ref_mata_od_koreksi_sudut";
          $dbField[7] = "ref_mata_od_koreksi_visus";
          $dbField[8] = "ref_mata_os_nonkoreksi_visus";
          $dbField[9] = "ref_mata_os_koreksi_spheris";
          $dbField[10] = "ref_mata_os_koreksi_cylinder";
          $dbField[11] = "ref_mata_os_koreksi_sudut";
          $dbField[12] = "ref_mata_os_koreksi_visus";
          $dbField[13] = "ref_pinhole_od";
          $dbField[14] = "ref_pinhole_os";
          $dbField[15] = "ref_streak_koreksi_spheris_od";
          $dbField[16] = "ref_streak_koreksi_cylinder_od";
          $dbField[17] = "ref_streak_koreksi_sudut_od";
          $dbField[18] = "ref_lenso_koreksi_spheris_od";
          $dbField[19] = "ref_lenso_koreksi_cylinder_od";
          $dbField[20] = "ref_lenso_koreksi_sudut_od";
          $dbField[21] = "ref_ark_koreksi_spheris_od";
          $dbField[22] = "ref_ark_koreksi_cylinder_od";
          $dbField[23] = "ref_ark_koreksi_sudut_od";
          $dbField[24] = "ref_prisma_koreksi_dioptri";
          $dbField[25] = "ref_prisma_koreksi_base1";
          $dbField[26] = "ref_prisma_koreksi_base2";
          $dbField[27] = "id_cust_usr";
          $dbField[28] = "ref_streak_koreksi_spheris_os";
          $dbField[29] = "ref_streak_koreksi_cylinder_os";
          $dbField[30] = "ref_streak_koreksi_sudut_os";
          $dbField[31] = "ref_lenso_koreksi_spheris_os";
          $dbField[32] = "ref_lenso_koreksi_cylinder_os";
          $dbField[33] = "ref_lenso_koreksi_sudut_os";
          $dbField[34] = "ref_ark_koreksi_spheris_os";
          $dbField[35] = "ref_ark_koreksi_cylinder_os";
          $dbField[36] = "ref_ark_koreksi_sudut_os";
          
          if(!$_POST["ref_id"]) $_POST["ref_id"] = $dtaccess->GetTransID();
          
          $_POST["ref_mata_od_nonkoreksi_visus"] = $_POST["ref_mata_od_nonkoreksi_visus11"]."/".$_POST["ref_mata_od_nonkoreksi_visus12"]."...".$_POST["ref_mata_od_nonkoreksi_visus21"]."/".$_POST["ref_mata_od_nonkoreksi_visus22"];
          $_POST["ref_mata_os_koreksi_visus"] = $_POST["ref_mata_os_koreksi_visus11"]."/".$_POST["ref_mata_os_nonkoreksi_visus12"]."...".$_POST["ref_mata_os_nonkoreksi_visus21"]."/".$_POST["ref_mata_os_nonkoreksi_visus22"];
          $_POST["ref_pinhole_od"] = $_POST["ref_pinhole_od1"]."/".$_POST["ref_pinhole_od2"];
          $_POST["ref_pinhole_os"] = $_POST["ref_pinhole_os1"]."/".$_POST["ref_pinhole_os2"];
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["ref_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["ref_keluhan"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_nonkoreksi_visus"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_spheris"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_cylinder"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_sudut"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_visus"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_nonkoreksi_visus"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_spheris"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_cylinder"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_sudut"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_visus"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["ref_pinhole_od"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["ref_pinhole_os"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_spheris_od"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_cylinder_od"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_sudut_od"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_spheris_od"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_cylinder_od"]);
          $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_sudut_od"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_spheris_od"]);
          $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_cylinder_od"]);
          $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_sudut_od"]);
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["ref_prisma_koreksi_dioptri"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["ref_prisma_koreksi_base1"]);
          $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["ref_prisma_koreksi_base2"]);
          $dbValue[27] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_spheris_os"]);
          $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_cylinder_os"]);
          $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_sudut_os"]);
          $dbValue[31] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_spheris_os"]);
          $dbValue[32] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_cylinder_os"]);
          $dbValue[33] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_sudut_os"]);
          $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_spheris_os"]);
          $dbValue[35] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_cylinder_os"]);
          $dbValue[36] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_sudut_os"]);
          
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
          
		// -- insert ke folio jika data ark diisi ---
		if($_POST["ref_ark_koreksi_spheris_od"] || $_POST["ref_ark_koreksi_cylinder_od"] || $_POST["ref_ark_koreksi_sudut_od"] || $_POST["ref_ark_koreksi_spheris_os"] || $_POST["ref_ark_koreksi_cylinder_os"] || $_POST["ref_ark_koreksi_sudut_os"]) {
               $sql = "select * from klinik.klinik_biaya where biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_ARK);
               $dataBiaya = $dtaccess->Fetch($sql,DB_SCHEMA);  
               
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
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_nama"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_id"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_jenis"]);
               $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
               $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[8] = QuoteValue(DPE_CHAR,"n");
               $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
               $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
		}
		
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".$_POST["cmbNext"]."0', reg_waktu = CURRENT_TIME  where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql); 
          
          echo "<script>document.location.href='".$thisPage."';</script>";
          exit();   

	}

     $optionsBase1[0] = $view->RenderOption("1","TRUE",$show);
     $optionsBase1[1] = $view->RenderOption("0","FALSE",$show);

     $optionsBase2[0] = $view->RenderOption("1","TRUE",$show);
     $optionsBase2[1] = $view->RenderOption("0","FALSE",$show);

	
	$optionsNext[0] = $view->RenderOption(STATUS_PREOP,"Operasi Hari Ini",$show);
	$optionsNext[1] = $view->RenderOption(STATUS_PEMERIKSAAN,"Pemeriksaan",$show);
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>

<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetRefraksi(0,'target=antri_kiri_isi');     
     GetRefraksi(1,'target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}

function ProsesRefraksi(id) {
	SetRefraksi(id,'type=r');
	timer();
}

timer();


function ChangeDisplay(id) {
     var disp = Array();
     
     disp['none'] = 'block';
     disp['block'] = 'none';
     
     document.getElementById(id).style.display = disp[document.getElementById(id).style.display];
}
</script>



<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Refraksi</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetRefraksi(0); ?></div>
	</div>
	
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Refraksi</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetRefraksi(1); ?></div>
	</div>
</div>




<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Refraksi</td>
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
               <td width= "20%" align="left" class="tablecontent">Keluhan Pasien</td>
               <td width= "40%" align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_keluhan","ref_keluhan","70","200",$_POST["ref_keluhan"],"inputField", null,false);?></textarea></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Pemeriksaan Refraksi</strong></legend>
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
               <td align="center" class="tablecontent-odd" nowrap>
                    <?php echo $view->RenderTextBox("ref_mata_od_nonkoreksi_visus11","ref_mata_od_nonkoreksi_visus11","3","5",$_POST["ref_mata_od_nonkoreksi_visus11"],"inputField", null,false);?>/
                    <?php echo $view->RenderTextBox("ref_mata_od_nonkoreksi_visus12","ref_mata_od_nonkoreksi_visus12","3","5",$_POST["ref_mata_od_nonkoreksi_visus12"],"inputField", null,false);?>...
                    <?php echo $view->RenderTextBox("ref_mata_od_nonkoreksi_visus21","ref_mata_od_nonkoreksi_visus21","3","5",$_POST["ref_mata_od_nonkoreksi_visus21"],"inputField", null,false);?>/
                    <?php echo $view->RenderTextBox("ref_mata_od_nonkoreksi_visus22","ref_mata_od_nonkoreksi_visus22","3","5",$_POST["ref_mata_od_nonkoreksi_visus22"],"inputField", null,false);?>
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_od_koreksi_spheris","ref_mata_od_koreksi_spheris","15","15",$_POST["ref_mata_od_koreksi_spheris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_od_koreksi_cylinder","ref_mata_od_koreksi_cylinder","15","15",$_POST["ref_mata_od_koreksi_cylinder"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_od_koreksi_sudut","ref_mata_od_koreksi_sudut","15","15",$_POST["ref_mata_od_koreksi_sudut"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd" nowrap>
                    <?php echo $view->RenderTextBox("ref_mata_od_koreksi_visus11","ref_mata_od_koreksi_visus11","3","5",$_POST["ref_mata_od_koreksi_visus11"],"inputField", null,false);?>/
                    <?php echo $view->RenderTextBox("ref_mata_od_koreksi_visus12","ref_mata_od_koreksi_visus12","3","5",$_POST["ref_mata_od_koreksi_visus12"],"inputField", null,false);?>...
                    <?php echo $view->RenderTextBox("ref_mata_od_koreksi_visus21","ref_mata_od_koreksi_visus21","3","5",$_POST["ref_mata_od_koreksi_visus21"],"inputField", null,false);?>/
                    <?php echo $view->RenderTextBox("ref_mata_od_koreksi_visus22","ref_mata_od_koreksi_visus22","3","5",$_POST["ref_mata_od_koreksi_visus22"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd" nowrap>
                    <?php echo $view->RenderTextBox("ref_mata_os_nonkoreksi_visus11","ref_mata_os_nonkoreksi_visus11","3","5",$_POST["ref_mata_os_nonkoreksi_visus11"],"inputField", null,false);?>/
                    <?php echo $view->RenderTextBox("ref_mata_os_nonkoreksi_visus12","ref_mata_os_nonkoreksi_visus12","3","5",$_POST["ref_mata_os_nonkoreksi_visus12"],"inputField", null,false);?>...
                    <?php echo $view->RenderTextBox("ref_mata_os_nonkoreksi_visus21","ref_mata_os_nonkoreksi_visus21","3","5",$_POST["ref_mata_os_nonkoreksi_visus21"],"inputField", null,false);?>/
                    <?php echo $view->RenderTextBox("ref_mata_os_nonkoreksi_visus22","ref_mata_os_nonkoreksi_visus22","3","5",$_POST["ref_mata_os_nonkoreksi_visus22"],"inputField", null,false);?>
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_os_koreksi_spheris","ref_mata_os_koreksi_spheris","15","15",$_POST["ref_mata_os_koreksi_spheris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_os_koreksi_cylinder","ref_mata_os_koreksi_cylinder","15","15",$_POST["ref_mata_os_koreksi_cylinder"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_os_koreksi_sudut","ref_mata_os_koreksi_sudut","15","15",$_POST["ref_mata_os_koreksi_sudut"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd" nowrap>
                    <?php echo $view->RenderTextBox("ref_mata_os_koreksi_visus11","ref_mata_os_koreksi_visus11","3","5",$_POST["ref_mata_os_koreksi_visus11"],"inputField", null,false);?>/
                    <?php echo $view->RenderTextBox("ref_mata_os_koreksi_visus12","ref_mata_os_koreksi_visus12","3","5",$_POST["ref_mata_os_koreksi_visus12"],"inputField", null,false);?>...
                    <?php echo $view->RenderTextBox("ref_mata_os_koreksi_visus21","ref_mata_os_koreksi_visus21","3","5",$_POST["ref_mata_os_koreksi_visus21"],"inputField", null,false);?>/
                    <?php echo $view->RenderTextBox("ref_mata_os_koreksi_visus22","ref_mata_os_koreksi_visus22","3","5",$_POST["ref_mata_os_koreksi_visus22"],"inputField", null,false);?>
               </td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbPinhole');">Pinhole</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbPinhole" style="display:none">
          <tr>
               <td align="left" width="10%" class="tablecontent">OD</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("ref_pinhole_od1","ref_pinhole_od1","3","5",$_POST["ref_pinhole_od1"],"inputField", null,false);?> /
                    <?php echo $view->RenderTextBox("ref_pinhole_od2","ref_pinhole_od2","3","5",$_POST["ref_pinhole_od2"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">OS</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("ref_pinhole_os1","ref_pinhole_os1","3","5",$_POST["ref_pinhole_os1"],"inputField", null,false);?> /
                    <?php echo $view->RenderTextBox("ref_pinhole_os2","ref_pinhole_os2","3","5",$_POST["ref_pinhole_os2"],"inputField", null,false);?>
               </td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbRetinoscopy');">Streak Retinoscopy</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbRetinoscopy" style="display:none">
          <tr class="subheader">
               <td width="100%" colspan=4 align="center">Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="10%" align="center">&nbsp;</td>
               <td width="30%" align="center">Spheris</td>
               <td width="30%" align="center">Cylinder</td>
               <td width="30%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_spheris_od","ref_streak_koreksi_spheris_od","15","15",$_POST["ref_streak_koreksi_spheris_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_cylinder_od","ref_streak_koreksi_cylinder_od","15","15",$_POST["ref_streak_koreksi_cylinder_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_sudut_od","ref_streak_koreksi_sudut_od","15","15",$_POST["ref_streak_koreksi_sudut_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_spheris_os","ref_streak_koreksi_spheris_os","15","15",$_POST["ref_streak_koreksi_spheris_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_cylinder_os","ref_streak_koreksi_cylinder_os","15","15",$_POST["ref_streak_koreksi_cylinder_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_sudut_os","ref_streak_koreksi_sudut_os","15","15",$_POST["ref_streak_koreksi_sudut_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbLensometri');">Lensometri</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbLensometri" style="display:none">
          <tr class="subheader">
               <td width="100%" colspan=4 align="center">Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="10%" align="center">&nbsp;</td>
               <td width="30%" align="center">Spheris</td>
               <td width="30%" align="center">Cylinder</td>
               <td width="30%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_spheris_od","ref_lenso_koreksi_spheris_od","15","15",$_POST["ref_lenso_koreksi_spheris_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_cylinder_od","ref_lenso_koreksi_cylinder_od","15","15",$_POST["ref_lenso_koreksi_cylinder_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_sudut_od","ref_lenso_koreksi_sudut_od","15","15",$_POST["ref_lenso_koreksi_sudut_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_spheris_os","ref_lenso_koreksi_spheris_os","15","15",$_POST["ref_lenso_koreksi_spheris_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_cylinder_os","ref_lenso_koreksi_cylinder_os","15","15",$_POST["ref_lenso_koreksi_cylinder_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_sudut_os","ref_lenso_koreksi_sudut_os","15","15",$_POST["ref_lenso_koreksi_sudut_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbArk');">ARK</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbArk" style="display:none">
          <tr class="subheader">
               <td width="100%" colspan=4 align="center">Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="10%" align="center">&nbsp;</td>
               <td width="30%" align="center">Spheris</td>
               <td width="30%" align="center">Cylinder</td>
               <td width="30%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_spheris_od","ref_ark_koreksi_spheris_od","15","15",$_POST["ref_ark_koreksi_spheris_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_cylinder_od","ref_ark_koreksi_cylinder_od","15","15",$_POST["ref_ark_koreksi_cylinder_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_sudut_od","ref_ark_koreksi_sudut_od","15","15",$_POST["ref_ark_koreksi_sudut_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_spheris_os","ref_ark_koreksi_spheris_os","15","15",$_POST["ref_ark_koreksi_spheris_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_cylinder_os","ref_ark_koreksi_cylinder_os","15","15",$_POST["ref_ark_koreksi_cylinder_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_sudut_os","ref_ark_koreksi_sudut_os","15","15",$_POST["ref_ark_koreksi_sudut_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbPrisma');">Koreksi Prisma</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbPrisma" style="display:none">
          <tr class="subheader">
               <td width="30%" align="center">Dioptri</td>
               <td width="30%" align="center">Base Up/Down</td>
               <td width="30%" align="center">Base Up/Down</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_prisma_koreksi_dioptri","ref_prisma_koreksi_dioptri","15","15",$_POST["ref_prisma_koreksi_dioptri"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderComboBox("ref_prisma_koreksi_base1","ref_prisma_koreksi_base1",$optionsBase1,null,null,null);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderComboBox("ref_prisma_koreksi_base2","ref_prisma_koreksi_base2",$optionsBase2,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="left" width="20%" class="tablecontent">Tahap Berikutnya</td>
			<td align="left" width="20%"><?php echo $view->RenderComboBox("cmbNext","cmbNext",$optionsNext,null,null,null);?></td>
			<td align="left"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>	
</table>

<?php echo $view->SetFocus("ref_keluhan");?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="ref_id" value="<?php echo $_POST["ref_id"];?>"/>

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
