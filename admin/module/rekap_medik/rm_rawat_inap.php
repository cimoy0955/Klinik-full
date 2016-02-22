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
     

 	if(!$auth->IsAllowed("perawatan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("perawatan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "perawatan.php";
     $icdPage = "icd_find.php?";
     $inaPage = "ina_find.php?";
     $terapiPage = "obat_find.php?";
	$dokterPage = "rawat_dokter_find.php?";
	$susterPage = "rawat_suster_find.php?";
     $findPage = "pasien_find.php?";

     $tableRefraksi = new InoTable("table1","99%","center");

	if($_GET["id_cust_usr"]) $_POST["id_cust_usr"] = $_GET["id_cust_usr"];
	if($_GET["id_reg"]) $_POST["id_reg"] = $_GET["id_reg"];

     $lokasi = $APLICATION_ROOT."images/foto_perawatan";

//echo $_POST["cust_usr_kode"];

	if($_POST["cust_usr_kode"]) {
		$sql = "select cust_usr_id,cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, ((current_date - cust_usr_tanggal_lahir)/365) as umur 
                    from global.global_customer_user b 
                    where b.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
         //echo $sql;
          $dataPasien = $dtaccess->Fetch($sql);
          
	     $sql = "select * from klinik.klinik_perawatan where id_cust_usr = ".QuoteValue(DPE_CHAR,$dataPasien["cust_usr_id"])." and id_reg <> ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataPemeriksaan = $dtaccess->FetchAll($sql);
	

		for($i=0,$n=count($dataPemeriksaan);$i<$n;$i++) {

			$dataPemeriksaan[$i]["foto"] = $lokasi."/".$dataPemeriksaan[$i]["rawat_mata_foto"];
			$dataPemeriksaan[$i]["sketsa"] = $lokasi."/".$dataPemeriksaan[$i]["rawat_mata_sketsa"];
			
			$sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
					join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"]);
			$rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["suster"][$j] = $row["pgw_nama"];
				$j++;
			}
	
	
			$sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_dokter a
					join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"]);
			$rs = $dtaccess->Execute($sql);
			
			unset($row);
			$row=$dtaccess->Fetch($rs);
			$dataPemeriksaan[$i]["dokter"] = $row["pgw_nama"];


			$sql = "select item_nama, dosis_nama from klinik.klinik_perawatan_terapi a
						left join inventori.inv_item b on a.id_item = b.item_id
						left join inventori.inv_dosis c on a.id_dosis = c.dosis_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"]);
			$rs = $dtaccess->Execute($sql);

			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["terapi_obat"][$j] = $row["item_nama"];
				$dataPemeriksaan[$i]["terapi_dosis"][$j] = $row["dosis_nama"];
				$j++;
                    
			}	
	

			$sql = "select icd_nomor, icd_nama from klinik.klinik_perawatan_icd a
						left join klinik.klinik_icd c on a.id_icd = c.icd_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"])." 
                              and rawat_icd_odos = 'OD'";
			$rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["icd_od_nomor"][$j] = $row["icd_nomor"];
				$dataPemeriksaan[$i]["icd_od_nama"][$j] = $row["icd_nama"];
				$j++;
			}	

			$sql = "select icd_nomor, icd_nama from klinik.klinik_perawatan_icd a
						left join klinik.klinik_icd c on a.id_icd = c.icd_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"])." 
                              and rawat_icd_odos = 'OS'";
			$rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["icd_os_nomor"][$j] = $row["icd_nomor"];
				$dataPemeriksaan[$i]["icd_os_nama"][$j] = $row["icd_nama"];
				$j++;
			}	
	

			$sql = "select ina_kode, ina_nama from klinik.klinik_perawatan_ina a
						left join klinik.klinik_ina c on a.id_ina = c.ina_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"])."
                              and rawat_ina_odos = 'OD'";
			$rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["ina_od_kode"][$j] = $row["ina_kode"];
				$dataPemeriksaan[$i]["ina_od_nama"][$j] = $row["ina_nama"];
				$j++;
			}	
			
	

			$sql = "select ina_kode, ina_nama from klinik.klinik_perawatan_ina a
						left join klinik.klinik_ina c on a.id_ina = c.ina_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"])."
                              and rawat_ina_odos = 'OS'";
			$rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["ina_os_kode"][$j] = $row["ina_kode"];
				$dataPemeriksaan[$i]["ina_os_nama"][$j] = $row["ina_nama"];
				$j++;
			}	
			
		}
	}

?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitThickBox(); ?> 
<script language="javascript">
	function Print() {
		window.print();
	}
	
</script>
<table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
	<tr>
		<td align="left" colspan=2 class="tableheader" class="noborder">Rekap Medik Rawat Inap</td>
	</tr> 
</table> 
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
     <tr>
		<td width= "5%" align="left" class="tablecontent">Kode Pasien</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="25" maxlength="25" value="<?php echo $_POST["cust_usr_kode"];?>"/>
               <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
               <input type="submit" name="btnLanjut" value="Lanjut" class="button"/>
          </td>
</table>
<?php if(!$dataPasien["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>Kode Pasien Tidak Ditemukan</strong></font>
<?php } ?>


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
<tr>
     <td width="60%" class="noborder"> 
     <legend><strong><u>Data Pasien</U></strong></legend>
     <table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "70%" align="left" class="tablecontent-odd"  class="noborder"><label>:&nbsp;<?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Nama Lengkap</td>
               <td width= "70%" align="left" class="tablecontent-odd" class="noborder"><label>:&nbsp;<?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Umur</td>
               <td width= "70%" align="left" class="tablecontent-odd" class="noborder"><label>:&nbsp;<?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Jenis Kelamin</td>
               <td width= "70%" align="left" class="tablecontent-odd" class="noborder"><label>:&nbsp;<?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
	</table>

<?php for($i=0,$n=count($dataPemeriksaan);$i<$n;$i++) { ?> 
	<fieldset style="border-color:red"> 
     <legend><strong><?php echo format_date(substr($dataPemeriksaan[$i]["rawat_waktu"],0,10));?></strong></legend> 
     <legend><u><strong>Petugas</strong></u></legend>
     <table width="100%" border="0" cellpadding="4" cellspacing="1">
          <tr>
               <td width="20%"  class="tablecontent" align="left">Dokter</td>
               <td align="left" class="tablecontent-odd" width="80%"><?php echo $dataPemeriksaan[$i]["dokter"];?></td>
          </tr>
     
          <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php for($j=0,$k=count($dataPemeriksaan[$i]["suster"]);$j<$k;$j++) { ?>
                              <tr id="tr_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $dataPemeriksaan[$i]["suster"][$j];?>
                                   </td>
                              </tr>
                         <?php } ?>
				</table>
               </td>
          </tr>
	</table>


     <legend><strong>Pemeriksaan Mata</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="30%" align="center">Pemeriksaan</td>
               <td width="30%" align="center">OD</td>
               <td width="30%" align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Palpebra</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_palpebra"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_palpebra"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Conjunctiva</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_conjunctiva"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_conjunctiva"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Cornea</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_cornea"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_cornea"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">COA</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_coa"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_coa"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Iris</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_iris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_iris"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pupil</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_pupil"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_pupil"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Lensa</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_lensa"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_lensa"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Ocular Movement</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_ocular"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_ocular"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Funduscopy (Retina)</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_retina"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_retina"];?></td>
          </tr>
	</table>

     <legend><strong>Tindakan Pemeriksaan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="30%" class="tablecontent">Tonometri OD</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $dataPemeriksaan[$i]["rawat_tonometri_scale_od"];?> / 
                    <?php echo $dataPemeriksaan[$i]["rawat_tonometri_weight_od"];?> g = 
                    <?php echo $dataPemeriksaan[$i]["rawat_tonometri_pressure_od"];?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" width="30%" class="tablecontent">Tonometri OS</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $dataPemeriksaan[$i]["rawat_tonometri_scale_os"];?> / 
                    <?php echo $dataPemeriksaan[$i]["rawat_tonometri_weight_os"];?> g = 
                    <?php echo $dataPemeriksaan[$i]["rawat_tonometri_pressure_os"];?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Anel Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_anel"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Schimer Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_schimer"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Irigasi Bola Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_irigasi"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Epilasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_epilasi"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Probing</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_probing"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Flouorecsin Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_flouorecsin"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Uji Kesehatan Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_kesehatan"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Color Blindness</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_color_blindness"];?></td>
          </tr>
	</table>

     <legend><strong>LAB</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent">Alergi</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_lab_alergi"];?></td>
          </tr>
	</table>

     <legend><strong>Foto</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "50%" align="left" class="tablecontent">Gambar Mata</td>
               <td width= "50%" align="left" class="tablecontent">Sketsa Mata</td>
          </tr>
          <tr>
               <td width= "50%" align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $dataPemeriksaan[$i]["foto"];?>"  border="1">
               </td>
               <td width= "50%" align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_sketsa" id="img_sketsa" src="<?php echo $dataPemeriksaan[$i]["sketsa"];?>"  border="1">
               </td>
          </tr>
	</table>

     <legend><strong>Catatan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
          <tr>
               <td align="left" class="tablecontent" width="20%">Catatan</td>
               <td align="left" class="tablecontent-odd"><?php echo nl2br($dataPemeriksaan[$i]["rawat_catatan"]);?></td>
          </tr>
     </table>

     <legend><strong>Terapi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
          <tr class="subheader">
               <td width="30%" align="center">Nama Obat</td>
               <td width="30%" align="center">Dosis</td>
          </tr>	
		<?php for($j=0,$k=count($dataPemeriksaan[$i]["terapi_obat"]);$j<$k;$j++) { ?>
			<tr  class="tablecontent-odd" id="tr_terapi_0">
				<td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["terapi_obat"][$j];?></td>
				<td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["terapi_dosis"][$j];?></td>
			</tr>
		<?php } ?>
     </table>

     <legend><strong>Terapi Kacamata</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
          <tr class="subheader">
               <td width="5%" align="center">Mata</td>
               <td width="15%" align="center">Spheris</td>
               <td width="15%" align="center">Cylinder</td>
               <td width="15%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_od_koreksi_sudut"];?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["rawat_mata_os_koreksi_sudut"];?></td>
          </tr>
     </table>


     <legend><strong>Diagnosis - ICD - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
		<?php for($j=0,$k=count($dataPemeriksaan[$i]["icd_od_nomor"]);$j<$k;$j++) { ?>
          <tr>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["icd_od_nomor"][$j];?></td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["icd_od_nama"][$j];?></td>
          </tr>
		<?php } ?>
	</table>

     <legend><strong>Diagnosis - ICD - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
		<?php for($j=0,$k=count($dataPemeriksaan[$i]["icd_os_nomor"]);$j<$k;$j++) { ?>
          <tr>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["icd_os_nomor"][$j];?></td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["icd_os_nama"][$j];?></td>
          </tr>
		<?php } ?>
	</table>

     <legend><strong>Diagnosis - INA DRG - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="25%">INA DRG</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
		<?php for($j=0,$k=count($dataPemeriksaan[$i]["ina_od_kode"]);$j<$k;$j++) { ?>
          <tr>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["ina_od_kode"][$j];?></td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["ina_od_nama"][$j];?></td>
          </tr>
		<?php } ?>
	</table>

     <legend><strong>Diagnosis - INA DRG - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="25%">INA DRG</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
		<?php for($j=0,$k=count($dataPemeriksaan[$i]["ina_os_kode"]);$j<$k;$j++) { ?>
          <tr>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["ina_os_kode"][$j];?></td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan[$i]["ina_os_nama"][$j];?></td>
          </tr>
		<?php } ?>
	</table>
	</fieldset>
	
	<BR><BR>
	<?php } ?> 
     </td>
</tr>	
</table>
<table id="tblSearching" width="100%"> 
<tr>
	<td align="center" class="tablecontent-odd"><input type="button" name="btnPrint" value="Cetak" class="button" onClick="Print()"></td> 
</tr>
</table>

<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"];?>"/>
<?php echo $view->RenderHidden("hid_id_del","hid_id_del",'');?>

</form>


<?php echo $view->RenderBodyEnd(); ?>
