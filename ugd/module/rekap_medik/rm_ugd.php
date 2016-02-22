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
     

 	if(!$auth->IsAllowed("rekam_medik_ugd",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("rekam_medik_ugd",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $resusitasi["Y"] = "YA";
     $resusitasi["T"] = "TIDAK";
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
	  $sql = "select a.*,c.cust_usr_id,c.cust_usr_nama,c.cust_usr_kode, c.cust_usr_jenis_kelamin, ((current_date - c.cust_usr_tanggal_lahir)/365) as umur
		    from klinik_perawatan a
		    join klinik_registrasi b on b.reg_id=a.id_reg
		    join global.global_customer_user c on b.id_cust_usr=c.cust_usr_id
		    where c.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"])." and reg_tipe_rawat=".QuoteValue(DPE_CHAR,RAWAT_UGD);
	  $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
	  $dataPemeriksaan = $dtaccess->Fetch($rs);
	  $view->CreatePost($dataPemeriksaan);
	  
	  $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster"][$i] = $row["pgw_id"];
               $_POST["rawat_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }

          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_dokter"] = $row["pgw_id"];
          $_POST["rawat_dokter_nama"] = $row["pgw_nama"];

          // --- icd od
          $sql = "select icd_nama,icd_nomor, icd_id from klinik.klinik_perawatan_icd a
                    join klinik.klinik_icd b on a.id_icd = b.icd_nomor where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_icd_odos = 'OD'
                    order by rawat_icd_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_icd_id"][$i] = $row["icd_id"];
               $_POST["rawat_icd_kode"][$i] = $row["icd_nomor"];
               $_POST["rawat_icd_nama"][$i] = $row["icd_nama"];
               $i++;
          }

          // --- prosedur
          $sql = "select prosedur_kode, prosedur_nama from klinik.klinik_perawatan_prosedur a
                    join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." order by rawat_prosedur_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_prosedur_kode"][$i] = $row["prosedur_kode"];
               $_POST["rawat_prosedur_nama"][$i] = $row["prosedur_nama"];
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
<table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
	<tr>
		<td align="left" colspan=2 class="tableheader" class="noborder">Rekap Medik UGD</td>
	</tr> 
</table> 
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
     <tr>
		<td width= "5%" align="left" class="tablecontent">No. RM</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="25" maxlength="25" value="<?php echo $_POST["cust_usr_kode"];?>"/>
               <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
               <input type="submit" name="btnLanjut" value="Lanjut" class="button"/>
          </td>
</table>
<?php if(!$dataPemeriksaan["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>No. RM Tidak Ditemukan</strong></font>
<?php } ?>


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
<tr>
     <td width="60%" class="noborder"> 
     <legend><strong><u>Data Pasien</U></strong></legend>
     <table width="100%" border="0" cellpadding="4" cellspacing="1" id="noborder">
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">No. RM<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "70%" align="left" class="tablecontent-odd"  class="noborder"><label>:&nbsp;<?php echo $_POST["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Nama Lengkap</td>
               <td width= "70%" align="left" class="tablecontent-odd" class="noborder"><label>:&nbsp;<?php echo $_POST["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Umur</td>
               <td width= "70%" align="left" class="tablecontent-odd" class="noborder"><label>:&nbsp;<?php echo $_POST["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent" class="noborder">Jenis Kelamin</td>
               <td width= "70%" align="left" class="tablecontent-odd" class="noborder"><label>:&nbsp;<?php echo $jenisKelamin[$_POST["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
	</table>
<br />
     <fieldset style="border-color:red"> 
     <legend><strong><?php echo format_date(substr($dataPemeriksaan[$i]["rawat_waktu"],0,10));?></strong></legend> 
     <legend><u><strong>Petugas</strong></u></legend>
     <table width="100%" border="0" cellpadding="4" cellspacing="1">
          <tr>
               <td width="20%"  class="tablecontent" align="left">Dokter</td>
               <td align="left" class="tablecontent-odd" width="80%"><?php echo $_POST["rawat_dokter_nama"];?></td>
          </tr>
     
          <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
		    <table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php for($j=0,$k=count($_POST["rawat_suster_nama"]);$j<$k;$j++) { ?>
			 <tr id="tr_suster_<?php echo $i;?>">
			      <td align="left" class="tablecontent-odd" width="70%">
				   <?php echo $_POST["rawat_suster_nama"][$j];?>
			      </td>
			 </tr>
                         <?php } ?>
		    </table>
               </td>
          </tr>
     </table>
     <br />
     <legend><strong>Anamnesa</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
	  <tr>
	       <td style="text-align:left;vertical-align: top;width: 30%;" class="tablecontent">&nbsp;Anamnesa</td>
	       <td style="width:70%;" class="tablecontent-odd"><?php echo $_POST["rawat_anamnesa"];?></td>
	  </tr>
     </table>
     <br />
     <legend><strong>Pemeriksaan Fisik</strong></legend>
     <label>Status Generalis</label>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
	  <tr>
	       <td style="text-align:left;vertical-align: top;width: 30%;" class="tablecontent">&nbsp;KU</td>
	       <td style="width:70%;" class="tablecontent-odd">
		    <?php echo $_POST["rawat_ugd_ku"]; ?>
	       </td>
	  </tr>
	  <tr>
	       <td style="text-align:left;vertical-align: top;width: 30%;" class="tablecontent">&nbsp;Kesadaran (GCS)</td>
	       <td style="width:70%;" class="tablecontent-odd">
		    <?php echo $_POST["rawat_ugd_kesadaran"]; ?>
	       </td>
	  </tr>
	  <tr>
	       <td style="text-align:left;vertical-align: top;width: 30%;" class="tablecontent">&nbsp;Tindakan Resusitasi</td>
	       <td style="width:70%;" class="tablecontent-odd"><?php echo $resusitasi[$_POST["rawat_ugd_resusitasi"]];?>
	       </td>
	  </tr>
	  <tr>
	       <td align="left" class="tablecontent">&nbsp;Tensi</td>
	       <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_lab_tensi"];?></td>
	  </tr>
	  <tr>
	       <td align="left" class="tablecontent">&nbsp;Nadi</td>
	       <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_lab_nadi"];?></td>
	  </tr>
	  <tr>
	       <td align="left" class="tablecontent">&nbsp;Suhu</td>
	       <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_lab_suhu"];?></td>
	  </tr>
	  <tr>
	       <td align="left" class="tablecontent">&nbsp;Pernafasan</td>
	       <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_lab_nafas"];?></td>
	  </tr>
	  <tr>
	       <td align="left" class="tablecontent">&nbsp;ECG</td>
	       <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_lab_nafas"];?></td>
	  </tr>
	  <tr>
	       <td align="left" class="tablecontent">Status Lokalis</td>
	       <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_ugd_status_lokalis"];?></td>
	   </tr>
	  <tr>
	       <td align="left" class="tablecontent">Pemeriksaan Lab</td>
	       <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_ugd_pemeriksaan_lab"];?></td>
	   </tr>
	  <tr>
	       <td align="left" class="tablecontent">Pemeriksaan Radiology</td>
	       <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_ugd_pemeriksaan_radiology"];?></td>
	   </tr>
     </table>    
     <br />
     <legend><strong>Catatan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
          <tr>
               <td align="left" class="tablecontent" width="20%">Catatan</td>
               <td align="left" class="tablecontent-odd"><?php echo nl2br($dataPemeriksaan[$i]["rawat_catatan"]);?></td>
          </tr>
     </table>
     <br />
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
     <br />
     <legend><strong>Diagnosis - ICD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
		<?php for($j=0,$k=count($_POST["rawat_icd_id"]);$j<$k;$j++) { ?>
          <tr>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_icd_kode"][$j];?></td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_icd_nama"][$j];?></td>
          </tr>
		<?php } ?>
     </table>
     <br />
     <legend><strong>Prosedur</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="25%">Nomor</td>
               <td align="center" class="subheader">Keterangan Prosedur</td>
          </tr>
		<?php for($j=0,$k=count($_POST["rawat_prosedur_kode"]);$j<$k;$j++) { ?>
          <tr>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_prosedur_kode"][$j];?></td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_prosedur_nama"][$j];?></td>
          </tr>
		<?php } ?>
     </table>
     
	<BR><BR>
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
