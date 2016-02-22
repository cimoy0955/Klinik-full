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
/*
	
		$sql = "select cust_usr_id,cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, ((current_date - cust_usr_tanggal_lahir)/365) as umur 
                    from global.global_customer_user b 
                    where b.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
         //echo $sql;
          $dataPasien = $dtaccess->Fetch($sql);
  */
     if($_GET["rawat_id"]) {
	  $sql = "select a.*,b.*, c.cust_usr_id,c.cust_usr_nama,c.cust_usr_kode, c.cust_usr_jenis_kelamin, ((current_date - c.cust_usr_tanggal_lahir)/365) as umur
	       from klinik.klinik_perawatan a
	       left join klinik.klinik_registrasi b on b.reg_id=a.id_reg
	       left join global.global_customer_user c on c.cust_usr_id=b.id_cust_usr
	       where rawat_id = ".QuoteValue(DPE_CHAR,$enc->Decode($_GET["rawat_id"]));
	       //echo $sql;
          $dataPemeriksaan = $dtaccess->Fetch($sql);
	

		

	  $dataPemeriksaan["foto"] = $lokasi."/".$dataPemeriksaan["rawat_mata_foto"];
	  $dataPemeriksaan["sketsa"] = $lokasi."/".$dataPemeriksaan["rawat_mata_sketsa"];
	  
	  $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
			  join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"]);
	  $rs = $dtaccess->Execute($sql);
	  //echo $sql;
	  $j=0; unset($row);
	  while($row=$dtaccess->Fetch($rs)) {
		  $dataPemeriksaan["suster"][$j] = $row["pgw_nama"];
		  $j++;
	  }


	  $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_dokter a
			  join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"]);
	  $rs = $dtaccess->Execute($sql);
	  //echo $sql;
	  unset($row);
	  $row=$dtaccess->Fetch($rs);
	  $dataPemeriksaan["dokter"] = $row["pgw_nama"];


		  $sql = "select item_nama from klinik.klinik_perawatan_terapi a
				  left join inventori.inv_item b on a.id_item = b.item_id
				  where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"]);
	  $rs = $dtaccess->Execute($sql);
	       //echo $sql;
	  
	  $j=0; unset($row);
	  while($row=$dtaccess->Fetch($rs)) {
		  $dataPemeriksaan["terapi_obat"][$j] = $row["obat_nama"];
		  $j++;
      
	  }	


	  $sql = "select icd_nomor, icd_nama from klinik.klinik_perawatan_icd a
				  left join klinik.klinik_icd c on a.id_icd = c.icd_nomor where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])." 
		and rawat_icd_odos = 'OD'";
	  $rs = $dtaccess->Execute($sql);
	  
	  $j=0; unset($row);
	  while($row=$dtaccess->Fetch($rs)) {
		  $dataPemeriksaan["icd_od_nomor"][$j] = $row["icd_nomor"];
		  $dataPemeriksaan["icd_od_nama"][$j] = $row["icd_nama"];
		  $j++;
	  }	

	  $sql = "select icd_nomor, icd_nama from klinik.klinik_perawatan_icd a
				  left join klinik.klinik_icd c on a.id_icd = c.icd_nomor where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])." 
		and rawat_icd_odos = 'OS'";
	  $rs = $dtaccess->Execute($sql);
	  
	  $j=0; unset($row);
	  while($row=$dtaccess->Fetch($rs)) {
		  $dataPemeriksaan["icd_os_nomor"][$j] = $row["icd_nomor"];
		  $dataPemeriksaan["icd_os_nama"][$j] = $row["icd_nama"];
		  $j++;
	  }
	  
	  // --- prosedur
          $sql = "select prosedur_kode, prosedur_nama from klinik.klinik_perawatan_prosedur a
                    join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])." order by rawat_prosedur_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;
//echo $sql;
          while($row=$dtaccess->Fetch($rs)) {
               $dataPemeriksaan["rawat_prosedur_kode"][$i] = $row["prosedur_kode"];
               $dataPemeriksaan["rawat_prosedur_nama"][$i] = $row["prosedur_nama"];
               $i++;

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
<style>
     @media print{
	  button {
	       display: none;
	  }
	  
	  .noborder {
	       border: none;
	  }
	  
	  #noborder {
	       border: none;
	  }
     }
     
</style>
<table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
	<tr>
		<td align="left" colspan=2 class="tableheader" class="noborder">Rekap Medik Rawat Inap</td>
	</tr> 
</table> 
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
<tr>
     <td width="60%" class="noborder"> 
     <legend><strong><u>Data Pasien</U></strong></legend>
     <table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "70%" align="left" class="tablecontent-odd"  class="noborder"><label>:&nbsp;<?php echo $dataPemeriksaan["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Nama Lengkap</td>
               <td width= "70%" align="left" class="tablecontent-odd" class="noborder"><label>:&nbsp;<?php echo $dataPemeriksaan["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Umur</td>
               <td width= "70%" align="left" class="tablecontent-odd" class="noborder"><label>:&nbsp;<?php echo $dataPemeriksaan["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Jenis Kelamin</td>
               <td width= "70%" align="left" class="tablecontent-odd" class="noborder"><label>:&nbsp;<?php echo $jenisKelamin[$dataPemeriksaan["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
	</table>
     <br />
     <legend><strong>Tanggal Pemeriksaan:&nbsp;<?php echo format_date_long(substr($dataPemeriksaan["rawat_waktu"],0,10))."&nbsp;".substr($dataPemeriksaan["rawat_waktu"],11,8);?></strong></legend> 
     <legend><u><strong>Petugas</strong></u></legend>
     <table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
          <tr>
               <td width="20%"  class="tablecontent noborder" align="left">Dokter</td>
               <td align="left" class="tablecontent-odd noborder" width="80%"><?php echo $dataPemeriksaan["dokter"];?></td>
          </tr>
     
          <tr>
               <td width="20%"  class="tablecontent noborder" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd noborder" width="80%"> 
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster" id="noborder">
                         <?php for($j=0,$k=count($dataPemeriksaan["suster"]);$j<$k;$j++) { ?>
                              <tr id="tr_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd noborder" width="70%">
                                        <?php echo $dataPemeriksaan["suster"][$j];?>
                                   </td>
                              </tr>
                         <?php } ?>
				</table>
               </td>
          </tr>
	</table>
     <br />


     <!--<legend><strong>Pemeriksaan Mata</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="30%" align="center">Pemeriksaan</td>
               <td width="30%" align="center">OD</td>
               <td width="30%" align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Palpebra</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_palpebra"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_palpebra"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Conjunctiva</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_conjunctiva"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_conjunctiva"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Cornea</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_cornea"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_cornea"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">COA</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_coa"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_coa"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Iris</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_iris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_iris"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pupil</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_pupil"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_pupil"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Lensa</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_lensa"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_lensa"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Ocular Movement</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_ocular"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_ocular"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Funduscopy (Retina)</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_retina"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_retina"];?></td>
          </tr>
	</table>

     <legend><strong>Tindakan Pemeriksaan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="30%" class="tablecontent">Tonometri OD</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $dataPemeriksaan["rawat_tonometri_scale_od"];?> / 
                    <?php echo $dataPemeriksaan["rawat_tonometri_weight_od"];?> g = 
                    <?php echo $dataPemeriksaan["rawat_tonometri_pressure_od"];?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" width="30%" class="tablecontent">Tonometri OS</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $dataPemeriksaan["rawat_tonometri_scale_os"];?> / 
                    <?php echo $dataPemeriksaan["rawat_tonometri_weight_os"];?> g = 
                    <?php echo $dataPemeriksaan["rawat_tonometri_pressure_os"];?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Anel Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_anel"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Schimer Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_schimer"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Irigasi Bola Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_irigasi"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Epilasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_epilasi"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Probing</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_probing"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Flouorecsin Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_flouorecsin"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Uji Kesehatan Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_kesehatan"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Color Blindness</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_color_blindness"];?></td>
          </tr>
	</table>-->

     <legend><strong>LAB</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="noborder">
          <tr>
               <td align="left" class="tablecontent noborder">Keluhan</td>
               <td align="left" class="tablecontent-odd noborder" style="width: 80%"><?php echo $dataPemeriksaan["rawat_keluhan"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent noborder">Keadaan Umum</td>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["rawat_keadaan_umum"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent noborder">Tensi</td>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["rawat_lab_tensi"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent noborder">Nadi</td>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["rawat_lab_nadi"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent noborder">Suhu</td>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["rawat_lab_suhu"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent noborder">Nafas</td>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["rawat_lab_nafas"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent noborder">Alergi</td>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["rawat_lab_alergi"];?></td>
          </tr>
     </table>
     <br />

     <legend><strong>Gambar USG</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="noborder">
          <tr>
               <td width= "50%" align="left" class="tablecontent noborder">Gambar USG I</td>
               <td width= "50%" align="left" class="tablecontent noborder">gambar USG II</td>
          </tr>
          <tr>
               <td width= "50%" align="center"  class="tablecontent-odd noborder">
                    <img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $dataPemeriksaan["foto"];?>"  border="1">
               </td>
               <td width= "50%" align="center"  class="tablecontent-odd noborder">
                    <img hspace="2" width="120" height="150" name="img_sketsa" id="img_sketsa" src="<?php echo $dataPemeriksaan["sketsa"];?>"  border="1">
               </td>
          </tr>
     </table>
     <br />
     <legend><strong>Catatan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="noborder"> 
          <tr>
               <td align="left" class="tablecontent noborder" width="20%">Catatan</td>
               <td align="left" class="tablecontent-odd noborder"><?php echo nl2br($dataPemeriksaan["rawat_catatan"]);?></td>
          </tr>
     </table>
     <br />
     <legend><strong>Terapi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="noborder"> 
          <tr class="subheader noborder">
               <td width="30%" align="center">Nama Obat</td>
               <td width="30%" align="center">Dosis</td>
          </tr>	
		<?php for($j=0,$k=count($dataPemeriksaan["terapi_obat"]);$j<$k;$j++) { ?>
			<tr  class="tablecontent-odd noborder" id="noborder">
				<td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["terapi_obat"][$j];?></td>
				<td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["terapi_dosis"][$j];?></td>
			</tr>
		<?php } ?>
     </table>
     <br />
     <!--<legend><strong>Terapi Kacamata</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
          <tr class="subheader">
               <td width="5%" align="center">Mata</td>
               <td width="15%" align="center">Spheris</td>
               <td width="15%" align="center">Cylinder</td>
               <td width="15%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_koreksi_sudut"];?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_koreksi_sudut"];?></td>
          </tr>
     </table>-->


     <legend><strong>Diagnosis - ICD - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="noborder">
          <tr>
               <td align="center" class="subheader noborder" width="25%">ICD</td>
               <td align="center" class="subheader noborder">Keterangan</td>
          </tr>
		<?php for($j=0,$k=count($dataPemeriksaan["icd_od_nomor"]);$j<$k;$j++) { ?>
          <tr>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["icd_od_nomor"][$j];?></td>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["icd_od_nama"][$j];?></td>
          </tr>
		<?php } ?>
     </table>
     <br />
     <legend><strong>Diagnosis - ICD - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="noborder">
          <tr>
               <td align="center" class="subheader noborder" width="25%">ICD</td>
               <td align="center" class="subheader noborder">Keterangan</td>
          </tr>
		<?php for($j=0,$k=count($dataPemeriksaan["icd_os_nomor"]);$j<$k;$j++) { ?>
          <tr>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["icd_os_nomor"][$j];?></td>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["icd_os_nama"][$j];?></td>
          </tr>
		<?php } ?>
     </table>
     <br />
     <legend><strong>Prosedur</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id='noborder'>
          <tr>
               <td align="center" class="subheader noborder" width="25%">Nomor</td>
               <td align="center" class="subheader noborder">Keterangan</td>
          </tr>
		<?php for($j=0,$k=count($dataPemeriksaan["rawat_prosedur_kode"]);$j<$k;$j++) { ?>
          <tr>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["rawat_prosedur_kode"][$j];?></td>
               <td align="left" class="tablecontent-odd noborder"><?php echo $dataPemeriksaan["rawat_prosedur_nama"][$j];?></td>
          </tr>
		<?php } ?>
     </table>

     </td>
</tr>	
</table>
<table id="tblSearching" width="100%"> 
<tr>
	<td align="center" class="tablecontent-odd"><input type="button" name="btnPrint" value="Cetak" class="button" onClick="Print()" style="display:block;"></td> 
</tr>
</table>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"];?>"/>
<?php echo $view->RenderHidden("hid_id_del","hid_id_del",'');?>

</form>


<?php echo $view->RenderBodyEnd(); ?>
