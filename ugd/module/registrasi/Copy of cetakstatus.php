<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();

     $_x_mode = "New";
 
     if($_GET["id"]) $_POST["cust_usr_id"] = $_GET["id"];
	
	$sql = "select a.*,((current_date - cust_usr_tanggal_lahir)/365) as umur from global.global_customer_user a  where a.cust_usr_id = ".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_id"]);
	$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
	

?>

<html>
<head>

<title>Cetak Kartu Pasien</title>

<style type="text/css">
body {
    font-family:      Arial, Verdana, Helvetica, sans-serif;
    margin: 0px;
	font-size: 10px;
}

.tableisi {
    font-family:      Verdana, Arial, Helvetica, sans-serif;
    font-size:        10px;
	border: none #000000 0px; 
	padding:4px;
	border-collapse:collapse;
}


.tableisi td {
	border: solid #000000 1px; 
	padding:4px;
}

.tablenota {
    font-family:      Verdana, Arial, Helvetica, sans-serif;
    font-size:        10px;
	border: solid #000000 1px; 
	padding:4px;
	border-collapse:collapse;
}

.tablenota .judul  {
	border: solid #000000 1px; 
	padding:4px;
}

.tablenota .isi {
	border-right: solid black 1px;
	padding:4px;
}

.ttd {
	height:50px;
}

.judul {
     font-size:      14px;
	font-weight: bolder;
	border-collapse:collapse;
}


.judul1 {
	font-size: 12px;
	font-weight: bolder;
}
.judul2 {
	font-size: 14px;
	font-weight: bolder;
}
.judul3 {
	font-size: 12px;
	font-weight: normal;
}

.judul4 {
	font-size: 11px;
	font-weight: bold;
	background-color : #CCCCCC;
	text-align : center;
}
</style>

<?php echo $view->InitUpload(); ?>

<script>
$(document).ready( function() {
	window.print();
});
      
</script>
</head>

<body> 

<table width="610" border="1" cellpadding="2" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $APLICATION_ROOT;?>images/logo_bkmm.gif"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2"> <strong>BKMM CEHC SURABAYA</strong><br>      </span>
		<span class="judul3">
		PUSAT PELAYANAN KESEHATAN MATA MASYARAKAT<br>       
	   PROPINSI JAWA TIMUR</span></td>
  </tr>
  <tr>
    <td colspan="2" class="judul4">KARTU REKAM MEDIK RAWAT JALAN</td> 
  </tr>
</table>
<br>
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
    <td colspan="4" class="judul4">IDENTITAS PASIEN</td> 
  </tr>
  <tr>
    <td width="70">NAMA</td>
    <td width="181">&nbsp;<?php echo $dataPasien["cust_usr_nama"]; ?></td>
    <td width="55">NO REG</td>
    <td width="143">&nbsp;<?php echo $dataPasien["cust_usr_kode"]; ?></td>
  </tr>
  <tr>
    <td>UMUR</td>
    <td>&nbsp;<?php echo $dataPasien["umur"]; ?></td>
    <td>TGL</td>
    <td>&nbsp;<?php echo date("d-m-Y"); ?></td>
  </tr>
  <tr>
    <td>ALAMAT</td>
    <td colspan="3">&nbsp;<?php echo $dataPasien["cust_usr_alamat"]; ?></td>
  </tr>
  <tr>
    <td>KOTA / KAB</td>
    <td >&nbsp;<?php echo $dataPasien["cust_usr_kota_asal"]; ?></td>
    <td>TELP/HP</td>
    <td >&nbsp;<?php echo $dataPasien["cust_usr_telp"].($dataPasien["cust_usr_hp"])?("/".$dataPasien["cust_usr_hp"]):""; ?></td>
  </tr>
  <tr>
    <td>RUJUKAN DARI</td>
    <td>&nbsp;</td>
    <td>PEMBIAYAAN</td>
    <td>&nbsp;<?php echo $dataPasien["cust_usr_pekerjaan"]; ?></td>
  </tr>
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
    <td colspan="4" class="judul4">HASIL PEMERIKSAAN</td> 
  </tr>
  <tr>
    <td width="80">ANAMNESA</td>
    <td width="315" colspan="2">&nbsp;</td>
    <td width="80">ALLERGI</td>
  </tr>
</table>
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi"> 
  <tr>
    <td width="237" colspan="3" bgcolor="#CCCCCC" align="center">VISUAL ACUITY OD</td>
    <td width="237" colspan="3" bgcolor="#CCCCCC" align="center">VISUAL ACUITY OD</td> 
  </tr>
  <tr>
    <td width="30" align="center">VA</td>
    <td width="150" align="center">KOREKSI</td> 
    <td width="70" align="center">+KOREKSI</td> 
    <td width="30" align="center">VA</td>
    <td width="150" align="center">KOREKSI</td> 
    <td width="70" align="center">+KOREKSI</td> 
  </tr>
  <tr height="40">
    <td width="30" align="center">&nbsp;</td>
    <td width="150" align="center">&nbsp;</td> 
    <td width="70" align="center">&nbsp;</td> 
    <td width="30" align="center">&nbsp;</td>
    <td width="150" align="center">&nbsp;</td> 
    <td width="70" align="center">&nbsp;</td> 
  </tr>
  <tr>
    <td width="30" colspan="3">KACAMATA OD :</td>
    <td width="30" colspan="3">KACAMATA OS :</td> 
  </tr>
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi"> 
  <tr>
    <td width="180" class="judul4">OD</td>
    <td width="105" class="judul4">SLIT LAMP</td>
    <td width="180" class="judul4">OS</td> 
  </tr> 
  <tr>
    <td rowspan="5"><img src="<?php echo $APLICATION_ROOT;?>images/mata-1.gif"></td>
    <td align="center">PALPEBRA</td>
    <td align="right" rowspan="5"><img src="<?php echo $APLICATION_ROOT;?>images/mata-5.gif"></td> 
  </tr> 
  <tr> 
    <td align="center">KONJUCTIVA</td> 
  </tr> 
  <tr> 
    <td align="center">CORNEA</td> 
  </tr> 
  <tr> 
    <td align="center">ANT CHAMBER</td> 
  </tr> 
  <tr> 
    <td align="center">IRIS</td> 
  </tr> 
  <tr>
    <td rowspan="4"><img src="<?php echo $APLICATION_ROOT;?>images/mata-3.gif"></td>
    <td align="center">PUPIL</td>
    <td rowspan="4" align="right"><img src="<?php echo $APLICATION_ROOT;?>images/mata-6.gif"></td> 
  </tr> 
  <tr> 
    <td align="center">LENSA</td> 
  </tr> 
  <tr> 
    <td align="center">VITREUS</td> 
  </tr> 
  <tr> 
    <td align="center">ALAT LAIN</td> 
  </tr> 
  <tr>
    <td rowspan="4"><img src="<?php echo $APLICATION_ROOT;?>images/mata-4.gif"></td>
    <td align="center">TONOMETRI</td>
    <td rowspan="4" align="right"><img src="<?php echo $APLICATION_ROOT;?>images/mata-7.gif"></td> 
  </tr> 
  <tr> 
    <td align="center">ANELTEST</td> 
  </tr> 
  <tr> 
    <td align="center">FUNDUSCOPY</td> 
  </tr> 
  <tr> 
    <td align="center">SCHIMER TEST</td> 
  </tr> 
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr height="69" valign=top>
	<td colspan="2" width="237" align="left"><u>PERMERISAAN LAB</u></td>
	<td colspan="2" width="237" align="left"><u>KEADAAN UMUM,TENSI,NADI,TEMP,RESPIRASI</u></td>
  </tr> 
  <tr>
	<td width="150" bgcolor="#CCCCCC">DIAGNOSIS OD</td>
	<td width="50" bgcolor="#CCCCCC">ICD 10</td>
	<td width="150" bgcolor="#CCCCCC">DIAGNOSIS OD</td>
	<td width="50" bgcolor="#CCCCCC">ICD 10</td>
  </tr> 
  <tr>
	<td width="150">1.</td>
	<td width="50">&nbsp;</td>
	<td width="150">1.</td>
	<td width="50">&nbsp;</td>
  </tr> 
  <tr>
	<td width="150">2.</td>
	<td width="50">&nbsp;</td>
	<td width="150">2.</td>
	<td width="50">&nbsp;</td>
  </tr> 
  <tr>
	<td width="150">INA DRG</td>
	<td width="50">&nbsp;</td>
	<td width="150">INA DRG</td>
	<td width="50">&nbsp;</td>
  </tr> 
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
	<td width="150" bgcolor="#CCCCCC">TERAPI OBAT/KM</td>
	<td width="50" bgcolor="#CCCCCC">DOSIS</td>
	<td width="150" bgcolor="#CCCCCC">RENCANA TINDAKAN</td>
	<td width="50" bgcolor="#CCCCCC">JENIS</td>
  </tr> 
  <tr height="59">
	<td width="150" rowspan="2">&nbsp;</td>
	<td width="50" rowspan="2">&nbsp;</td>
	<td width="150">&nbsp;</td>
	<td width="50">&nbsp;</td>
  </tr> 
  <tr> 
	<td width="150" colspan="2">ICDM :</td> 
  </tr> 
  <tr height="59" valign="top"> 
	<td width="150" colspan="4">CATATAN :</td> 
  </tr> 
</table>  
<p style="page-break-after:always"></p>

<table width="610" border="1" cellpadding="2" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $APLICATION_ROOT;?>images/logo_bkmm.gif"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2"> <strong>BKMM CEHC SURABAYA</strong><br>      </span>
		<span class="judul3">
		PUSAT PELAYANAN KESEHATAN MATA MASYARAKAT<br>       
	   PROPINSI JAWA TIMUR</span></td>
  </tr>
  <tr>
    <td colspan="2" class="judul4">KARTU REKAM MEDIK RAWAT JALAN LANJUTAN</td> 
  </tr>
</table>
<br>
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
    <td colspan="6" bgcolor="#CCCCCC">HASIL PEMERIKSAAN TANGGAL :</td>  
  </tr> 
  <tr height="30" valign="top">
    <td colspan="6">ANAMNESA :</td>  
  </tr> 
  <tr>
    <td colspan="3" class="judul4">VISUAL ACUITY OD</td>  
    <td colspan="3" class="judul4">VISUAL ACUITY OD</td>  
  </tr> 
  <tr>
    <td width="30" align="center">VA</td>
    <td width="150" align="center">KOREKSI</td> 
    <td width="70" align="center">+KOREKSI</td> 
    <td width="30" align="center">VA</td>
    <td width="150" align="center">KOREKSI</td> 
    <td width="70" align="center">+KOREKSI</td> 
  </tr>
  <tr height="39">
    <td width="30" align="center">&nbsp;</td>
    <td width="150" align="center">&nbsp;</td> 
    <td width="70" align="center">&nbsp;</td> 
    <td width="30" align="center">&nbsp;</td>
    <td width="150" align="center">&nbsp;</td> 
    <td width="70" align="center">&nbsp;</td> 
  </tr>
  <tr>
    <td colspan="3">KACAMATA OD :</td>  
    <td colspan="3">KACAMATA OS :</td>  
  </tr> 
</table> 
<label>HASIL PEMERIKSAAN HARI INI </label>
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr height="49">
    <td colspan="4"><img src="<?php echo $APLICATION_ROOT;?>images/mata-1.gif"></td>  
  </tr>
  <tr> 
    <td width="150" align="center">DIAGNOSIS OD</td> 
    <td width="70" align="center">ICD 10</td>  
    <td width="150" align="center">DIAGNOSIS OD</td> 
    <td width="70" align="center">ICD 10</td> 
  </tr>
  <tr> 
    <td width="150">&nbsp;</td> 
    <td width="70">&nbsp;</td>  
    <td width="150">&nbsp;</td> 
    <td width="70">&nbsp;</td> 
  </tr>
  <tr> 
    <td width="150" colspan="2">INA DRG</td> 
    <td width="150" colspan="2">INA DRG</td>  
  </tr>
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
	<td width="150" bgcolor="#CCCCCC">TERAPI OBAT/KM</td>
	<td width="50" bgcolor="#CCCCCC">DOSIS</td>
	<td width="150" bgcolor="#CCCCCC">RENCANA TINDAKAN</td>
	<td width="50" bgcolor="#CCCCCC">JENIS</td>
  </tr> 
  <tr height="59">
	<td width="150" rowspan="2">&nbsp;</td>
	<td width="50" rowspan="2">&nbsp;</td>
	<td width="150">&nbsp;</td>
	<td width="50">&nbsp;</td>
  </tr> 
  <tr> 
	<td width="150" colspan="2">ICDM :</td> 
  </tr> 
  <tr height="49" valign="top"> 
	<td width="150" colspan="4">CATATAN :</td> 
  </tr> 
  <tr height="49" valign="top">
	<td width="150" colspan="2"><u>PEMERIKSAAN LAB</u></td>
	<td width="50" colspan="2"><u>KEADAAN UMUM,TENSI,NADI,TEMP,RESPIRASI</u></td> 
  </tr> 
</table>  
<label>HASIL PEMERIKSAAN HARI INI </label>
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr height="49">
    <td colspan="4"><img src="<?php echo $APLICATION_ROOT;?>images/mata-1.gif"></td>  
  </tr>
  <tr> 
    <td width="150" align="center">DIAGNOSIS OD</td> 
    <td width="70" align="center">ICD 10</td>  
    <td width="150" align="center">DIAGNOSIS OD</td> 
    <td width="70" align="center">ICD 10</td> 
  </tr>
  <tr> 
    <td width="150">&nbsp;</td> 
    <td width="70">&nbsp;</td>  
    <td width="150">&nbsp;</td> 
    <td width="70">&nbsp;</td> 
  </tr>
  <tr> 
    <td width="150" colspan="2">INA DRG</td> 
    <td width="150" colspan="2">INA DRG</td>  
  </tr>
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
	<td width="150" bgcolor="#CCCCCC">TERAPI OBAT/KM</td>
	<td width="50" bgcolor="#CCCCCC">DOSIS</td>
	<td width="150" bgcolor="#CCCCCC">RENCANA TINDAKAN</td>
	<td width="50" bgcolor="#CCCCCC">JENIS</td>
  </tr> 
  <tr height="59">
	<td width="150" rowspan="2">&nbsp;</td>
	<td width="50" rowspan="2">&nbsp;</td>
	<td width="150">&nbsp;</td>
	<td width="50">&nbsp;</td>
  </tr> 
  <tr> 
	<td width="150" colspan="2">ICDM :</td> 
  </tr> 
  <tr height="49" valign="top"> 
	<td width="150" colspan="4">CATATAN :</td> 
  </tr> 
  <tr height="49" valign="top">
	<td width="150" colspan="2"><u>PEMERIKSAAN LAB</u></td>
	<td width="50" colspan="2"><u>KEADAAN UMUM,TENSI,NADI,TEMP,RESPIRASI</u></td> 
  </tr> 
</table> 
</body>
</html>
