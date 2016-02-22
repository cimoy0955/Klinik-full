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
	
	$sqlPasien = "select a.*,((current_date - cust_usr_tanggal_lahir)/365) as umur, b.prop_nama, c.kota_nama
		   from global.global_customer_user a
		   left join global.global_propinsi b on b.prop_id = a.cust_usr_propinsi
		   left join global.global_kota c on c.kota_id = a.cust_usr_kota
		   where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
     $rsPasien = $dtaccess->Execute($sqlPasien,DB_SCHEMA_GLOBAL);
	$dataPasien = $dtaccess->Fetch($rsPasien);
	
	// ---- Data registrasi terakhir ---- 
	$sqlReg = "select reg_id, reg_jenis_pasien, reg_rujukan, b.rujukan_nama, a.reg_tipe_umur  
			from klinik.klinik_registrasi a
			left join global.global_rujukan b on b.rujukan_id = a.reg_rujukan
			where a.id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])."   
			order by reg_when_update desc limit 1"; 
	$rsReg = $dtaccess->Execute($sqlReg,DB_SCHEMA_GLOBAL);
	$dataReg = $dtaccess->Fetch($rsReg);
	$tipeUmur = $dataReg["reg_tipe_umur"];
	
	// untuk keperluan testing reg_tipe_umur
	//$tipeUmur = "A";
	
	if($tipeUmur=="D"){
	    $txtJudulKartu = "KARTU REKAM MEDIK RAWAT JALAN";
	}elseif($tipeUmur=="A"){
	    $txtJudulKartu = "KARTU REKAM MEDIS RAWAT JALAN PEDIATRY OPTHALMOLOGY";
	}
?>

<html>
<head>

<title>Cetak Kartu Pasien</title> 
<script> 
	window.print();  
</script>
<style type="text/css">
 @media screen {
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
     .judul5 {
	     font-size: 11px;
	     font-weight: bold;
	     background-color : #040404;
	     text-align : center;
	     color : #FFFFFF;
     } 
     
 }
 
 @media print {
     body {
	   font-family:      Arial, Verdana, Helvetica, sans-serif;
	   margin: 0px;
	    font-size: 9px;
     }
     
     .tableisi {
	    font-family:      Verdana, Arial, Helvetica, sans-serif;
	    font-size:        9px;
	     border: none #000000 0px; 
	     padding:2px;
	     border-collapse:collapse;
     }
     
     
     .tableisi td {
	     border: solid #000000 1px; 
	     padding:2px;
     }
     
     .tablenota {
	    font-family:      Verdana, Arial, Helvetica, sans-serif;
	    font-size:        9px;
	     border: solid #000000 1px; 
	     padding:2px;
	     border-collapse:collapse;
     }
     
     .tablenota .judul  {
	     border: solid #000000 1px; 
	     padding:2px;
     }
     
     .tablenota .isi {
	     border-right: solid black 1px;
	     padding:2px;
     }
     
     .ttd {
	     height:35px;
     }
     
     .judul {
	     font-size:      12px;
	     font-weight: bolder;
	     border-collapse:collapse;
     }
     
     
     .judul1 {
	     font-size: 11px;
	     font-weight: bolder;
     }
     .judul2 {
	     font-size: 12px;
	     font-weight: bolder;
     }
     .judul3 {
	     font-size: 11px;
	     font-weight: normal;
     }
     
     .judul4 {
	     font-size: 10px;
	     font-weight: bold;
	     background-color : #CCCCCC;
	     text-align : center;
     }
     .judul5 {
	     font-size: 10px;
	     font-weight: bold;
	     background-color : #040404;
	     text-align : center;
	     color : #FFFFFF;
     }
 }
     

</style>
</head>

<body>
<table width="610" border="1" cellpadding="2" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $APLICATION_ROOT;?>images/logo_bkmm.gif"  width="50%" height="50%"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2" style="text-transform: uppercase;font: bold 16px;">rsmm jawa timur<br></span>
		<span class="judul3" style="text-transform: uppercase;">
		rumah sakit mata kesehatan mata masyarakat<br>       
	   provinsi jawa timur</span></td>
  </tr>
  <tr>
    <td colspan="2" style="font-weight: 14px; background-color: #8f8f8f; color: #ffffff; text-align: center;"><?php echo $txtJudulKartu;?></td> 
  </tr>
</table>
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
 
  <tr>
    <td width="80">NAMA</td>
    <td width="141" colspan="3">&nbsp;<strong><font style="font-size:13pt;font-weight:bold"><?php echo $dataPasien["cust_usr_nama"]; ?></font></strong></td>
    <td width="80">NO REG</td>
    <td width="143">&nbsp;<strong><font style="font-size:15pt;font-weight:bold"><?php echo $dataPasien["cust_usr_kode"]; ?></font></strong></td>
  </tr>
  <tr>
    <td width="80">UMUR</td>
    <td width="50">&nbsp;<?php echo $dataPasien["umur"]; ?></td>
    <td width="20">SEX</td>
    <td width="61">&nbsp;<?php echo $dataPasien["cust_usr_jenis_kelamin"];?></td>
    <td width="80">TGL KUNJUNGAN</td>
    <td width="143">&nbsp;<?php echo date("d-m-Y"); ?></td>
  </tr>
  <tr>
    <td>ALAMAT</td>
    <td colspan="3">&nbsp;<?php echo $dataPasien["cust_usr_alamat"]; ?></td>
    <td>PEKERJAAN</td>
    <td>&nbsp;<?php echo $dataPasien["cust_usr_pekerjaan"]; ?></td>
  </tr>
  <tr>
    <td>KOTA / KAB</td>
    <td colspan="3">&nbsp;<?php echo $dataPasien["prop_nama"]; ?>&nbsp;/&nbsp;<?php echo $dataPasien["kota_nama"];?></td>
    <td>TELP/HP</td>
    <td >&nbsp;<?php 
    if($dataPasien["cust_usr_telp"]){
    echo $dataPasien["cust_usr_telp"]."&nbsp;/&nbsp;".$dataPasien["cust_usr_hp"]; 
    }
    elseif(!$dataPasien["cust_usr_telp"]){
    echo $dataPasien["cust_usr_hp"]; 
    }
    ?></td>
  </tr>
  <tr>
    <td>RUJUKAN DARI</td>
    <td colspan="3">&nbsp;<?php echo $dataReg["rujukan_nama"];?></td>
    <td>PEMBIAYAAN</td>
    <td>&nbsp;<?php echo $bayarPasien[$dataReg["reg_jenis_pasien"]];?></td>
  </tr>
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
    <td colspan="9" class="judul5" style="background-color: #cfcfcf;color:#000000;">CATATAN MEDIS (SUBYEKTIF, OBYEKTIF, ASSESMENT DAN PLANING)</td>
    <td width="25" style="background-color: #cfcfcf;color:#000000;">PETUGAS</td>
  </tr>
  <tr>
     <td width="15" rowspan="<?php echo ($tipeUmur=="D")?"6":"9";?>" style="background-color: #CFCFCF;font: bold 30px arial, sans-serif ">S</td>
    <td width="237" rowspan="<?php echo ($tipeUmur=="D")?"2":"";?>" colspan="4" style="vertical-align: top" >ANAMNESA / KELUHAN UTAMA:</td>
    <td width="237" rowspan="<?php echo ($tipeUmur=="D")?"":"2";?>" colspan="4" <?php if($tipeUmur=="A") echo "style=\"vertical-align: top\"";?>>RIWAYAT<br />PENYAKIT:</td>
    <td rowspan="<?php echo ($tipeUmur=="D")?"2":"3";?>">&nbsp;</td>
  </tr>
  <?php if($tipeUmur=="A"){ ?>
  <tr>
     <td rowspan="2" colspan="4" style="vertical-align: top">RIWAYAT<br />KELAHIRAN:</td>
  </tr>
  <?php }?>
  <tr>
     <td colspan="4">ALLERGY:</td>
  </tr>
  <?php if($tipeUmur=="D"){?>
  <tr>
    <td width="237" colspan="4" bgcolor="#cfcfcf" align="center">VISUAL ACUITY OD</td>
    <td width="237" colspan="4" bgcolor="#cfcfcf" align="center">VISUAL ACUITY OS</td>
    <td width="25" style="background-color: #BFBFBF;color:#000000;">PETUGAS</td>
  </tr>
  <tr>
    <td width="30" align="center">VA</td>
    <td width="150" align="center" colspan="2">KOREKSI</td> 
    <td width="70" align="center">+KOREKSI</td> 
    <td width="30" align="center">VA</td>
    <td width="150" align="center" colspan="2">KOREKSI</td> 
    <td width="70" align="center">+KOREKSI</td>
    <td rowspan="4">&nbsp;</td>
  </tr>
  <tr height="20">
    <td width="30" align="center">&nbsp;</td>
    <td width="150" align="left" colspan="2">&nbsp;</td> 
    <td width="70" align="center">&nbsp;</td> 
    <td width="30" align="center">&nbsp;</td>
    <td width="150" align="left" colspan="2">&nbsp;</td> 
    <td width="70" align="center">&nbsp;</td> 
  </tr>
  <tr>
    <td width="30">ADD</td>
    <td width="100">&nbsp;</td>
    <td width="120" colspan="2">JAGGER</td>
    <td width="30">ADD</td> 
    <td width="100">&nbsp;</td>
    <td width="120" colspan="2">JAGGER</td>
  </tr>
  <tr height="20"> 
    <td width="15" rowspan="21" style="background-color: #BFBFBF;font: bold 30px arial, sans-serif;color: #ffffff;">O</td>
    <td width="30" align="center" style="vertical-align: top;">KM<br />LAMA</td>
    <td width="75" align="left" style="vertical-align: top;" colspan="3">OD<br/>OS</td>
    <td width="30" align="left" style="vertical-align: top;font: bold 16px;">ARK</td>
    <td width="150" colspan="3" style="vertical-align: top;">OD<br/>OS</td>
  </tr>
  <?php }elseif($tipeUmur=="A"){?>
  <tr>
    <td width="237" colspan="4" bgcolor="#cfcfcf" align="center">VISUAL ACUITY OD</td>
    <td width="237" colspan="4" bgcolor="#cfcfcf" align="center">VISUAL ACUITY OS</td>
    <td width="25" style="background-color: #cfcfcf;color:#000000;">PETUGAS</td>
  </tr>
  <tr>
    <td width="30" align="center" style="background-color: #cfcfcf;color:#000000;">-KOREKSI</td>
    <td width="150" align="center" colspan="2" style="background-color: #cfcfcf;color:#000000;">KOREKSI</td> 
    <td width="70" align="center" style="background-color: #cfcfcf;color:#000000;">+KOREKSI</td> 
    <td width="30" align="center" style="background-color: #cfcfcf;color:#000000;">-KOREKSI</td>
    <td width="150" align="center" colspan="2" style="background-color: #cfcfcf;color:#000000;">KOREKSI</td> 
    <td width="70" align="center" style="background-color: #cfcfcf;color:#000000;">+KOREKSI</td>
    <td rowspan="6">&nbsp;</td>
  </tr>
  <tr height="20">
    <td width="30" align="left">PK</td>
    <td width="150" align="left" colspan="2">&nbsp;</td> 
    <td width="70" align="left">&nbsp;</td> 
    <td width="30" align="left">PK</td>
    <td width="150" align="left" colspan="2">&nbsp;</td> 
    <td width="70" align="left">&nbsp;</td> 
  </tr>
  <tr height="20">
    <td width="30" align="left">ARK PK</td>
    <td width="150" align="left" colspan="3">&nbsp;</td> 
    <td width="30" align="left">ARK PK</td>
    <td width="150" align="left" colspan="3">&nbsp;</td>
  </tr>
  <tr height="20">
    <td width="30" align="left">PL</td>
    <td width="150" align="left" colspan="2">&nbsp;</td> 
    <td width="70" align="left">&nbsp;</td> 
    <td width="30" align="left">PL</td>
    <td width="150" align="left" colspan="2">&nbsp;</td> 
    <td width="70" align="left">&nbsp;</td> 
  </tr>
  <tr height="20">
    <td width="30" align="left">ARK PS</td>
    <td width="150" align="left" colspan="3">&nbsp;</td> 
    <td width="30" align="left">ARK PS</td>
    <td width="150" align="left" colspan="3">&nbsp;</td>
  </tr>
  <tr height="20">
    <td width="15" rowspan="22" style="background-color: #bfbfbf;font: bold 30px arial, sans-serif;color: #ffffff;">O</td>
    <td width="30" align="left" style="vertical-align: top;">KM LAMA</td>
    <td width="75" align="left" style="vertical-align: top;">OD</td> 
    <td width="75" align="left" style="vertical-align: top;" colspan="2">OS</td>
    <td width="30" align="left">WFDT<br />TEST</td>
    <td width="150" colspan="3">&nbsp;</td>
  </tr>
  <?php }?>
  <tr>
    <td width="180" class="judul4" colspan="3">OD</td>
    <td width="105" style="background-color: #bfbfbf; color:  #000000; font-weight: bold; text-align:center;" colspan="2">SLIT LAMP</td>
    <td width="180" class="judul4" colspan="3">OS</td> 
    <td width="150" rowspan="<?php echo ($tipeUmur=="D")?"14":"15";?>">&nbsp;</td>
  </tr> 
  <tr>
    <td rowspan="5"><img src="<?php echo $APLICATION_ROOT;?>images/mata-1.gif" width="85%" height="85%"></td>
    <td width="25%" align="left" colspan="2">&nbsp;</td>
    <td align="center" width="25%" colspan="2">PALPEBRA</td>
    <td width="25%" align="right" colspan="2">&nbsp;</td>
    <td align="right" rowspan="5"><img src="<?php echo $APLICATION_ROOT;?>images/mata-5.gif" width="85%" height="85%"></td> 
  </tr> 
  <tr> 
    <td align="left" colspan="2">&nbsp;</td>
    <td align="center" colspan="2">KONJUCTIVA</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr> 
  <tr> 
    <td align="left" colspan="2">&nbsp;</td>
    <td align="center" colspan="2">CORNEA</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr> 
  <tr> 
    <td align="left" colspan="2">&nbsp;</td>
    <td align="center" colspan="2">ANT CHAMBER</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr> 
  <tr> 
    <td align="left" colspan="2">&nbsp;</td>
    <td align="center" colspan="2">IRIS</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr> 
  <tr>
    <td rowspan="4"><img src="<?php echo $APLICATION_ROOT;?>images/mata-3.gif" width="85%" height="85%"></td>
    <td align="left" colspan="2">&nbsp;</td>
    <td align="center" colspan="2">PUPIL</td>
    <td align="right" colspan="2">&nbsp;</td>
    <td rowspan="4" align="right"><img src="<?php echo $APLICATION_ROOT;?>images/mata-6.gif" width="85%" height="85%"></td> 
  </tr> 
  <tr> 
    <td align="left" colspan="2">&nbsp;</td>
    <td align="center" colspan="2">LENSA</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr> 
  <tr> 
    <td align="left" colspan="2">&nbsp;</td>
    <td align="center" colspan="2">VITREUS</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr> 
  <tr> 
    <td colspan="2">&nbsp;</td>
    <td align="center" colspan="2" style="background-color: #bfbfbf; color: #000000; font-weight: bold; text-align:center;">ALAT LAIN</td> 
    <td colspan="2">&nbsp;</td>
  </tr> 
  <tr>
    <td rowspan="<?php echo ($tipeUmur=="D")?"4":"5";?>"><img src="<?php echo $APLICATION_ROOT;?>images/mata-4.gif" width="85%" height="85%"></td>
    <td align="left" colspan="2">&nbsp;</td>
    <td align="center" colspan="2">TONOMETRI</td>
    <td align="right" colspan="2">&nbsp;</td>
    <td rowspan="<?php echo ($tipeUmur=="D")?"4":"5";?>" align="right"><img src="<?php echo $APLICATION_ROOT;?>images/mata-7.gif" width="85%" height="85%"></td> 
  </tr> 
  <tr> 
    <td align="left" colspan="2"><?php echo $dataPeriksa["rawat_anel"];?></td>
    <td align="center" colspan="2">ANELTEST</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr> 
  <tr> 
    <td align="left" colspan="2"><?php echo $dataPeriksa["rawat_mata_od_coa"];?></td>
    <td align="center" colspan="2">FUNDUSCOPY</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr> 
  <tr> 
    <td align="left" colspan="2"><?php echo $dataPeriksa["rawat_schimer"];?></td>
    <td align="center" colspan="2">SCHIMER TEST</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr>
  <?php if($tipeUmur=="A"){?>
  <tr> 
    <td align="left" colspan="2">&nbsp;</td>
    <td align="center" colspan="2">ESO/EXO TROPIA</td> 
    <td align="right" colspan="2">&nbsp;</td>
  </tr>
  <?php }?>
  <tr>
	<td colspan="4" width="237" align="justify" style="background-color: #bfbfbf; color: #000000; text-decoration: underline;">PEMERIKSAAN LAB</td>
	<td colspan="4" width="237" align="justify" style="background-color: #bfbfbf; color: #000000; text-decoration: underline;">KEADAAN UMUM,TENSI,NADI,TEMP,RESPIRASI</td>
	<td width="25" style="background-color: #bfbfbf; color: #000000;">PETUGAS</td>
  </tr>
  <tr>
	<td colspan="4" width="237" align="justify">&nbsp;</td>
	<td colspan="4" width="237" align="justify">&nbsp;</td>
	<td rowspan="2">&nbsp;</td>
  </tr>
  <tr>
	<td colspan="4" width="237" align="justify">&nbsp;</td>
	<td colspan="4" width="237" align="justify">&nbsp;</td>
  </tr>
  <tr>
     <td colspan="8" style="text-align: center;background-color: #bfbfbf; color: #000000; ">HASIL ELEKTROMEDIK (USB, HFA, INDIRECT OPTH, LASER, OCT, FF, FFA, RETCAM)</td>
	<td width="25" style="background-color: #bfbfbf;color:#000000;">PETUGAS</td>
  </tr>
  <tr>
     <td colspan="8">&nbsp;</td>
	<td rowspan="2">&nbsp;</td>
  </tr>
  <tr>
     <td colspan="8">&nbsp;</td>
  </tr>
  <tr>
    <td width="13" rowspan="5" style="background-color: #AFAFAF;font: bold 30px arial, sans-serif;color: #ffffff;">A</td>
	<td bgcolor="#AFAFAF" colspan="3" style="text-align: left">DIAGNOSIS OD</td>
	<td bgcolor="#AFAFAF">ICD 10</td>
	<td bgcolor="#AFAFAF" colspan="3" style="text-align: left">DIAGNOSIS OS</td>
	<td bgcolor="#AFAFAF">ICD 10</td>
	<td style="background-color: #AFAFAF;color:#000000;text-align: center;">PETUGAS</td>
  </tr> 
  <tr>
	<td colspan="3" style="text-align: left">1. &nbsp;</td>
	<td>&nbsp;</td>
	<td colspan="3" style="text-align: left">1. &nbsp;</td>
	<td>&nbsp;</td>
	<td rowspan="4">&nbsp;</td>
  </tr> 
  <tr>
	<td colspan="3" style="text-align: left">2. &nbsp;</td>
	<td>&nbsp;</td>
	<td colspan="3" style="text-align: left">2. &nbsp;</td>
	<td>&nbsp;</td>
  </tr>  
  <tr>
	<td colspan="3" style="text-align: left">3. &nbsp;</td>
	<td>&nbsp;</td>
	<td colspan="3" style="text-align: left">3. &nbsp;</td>
	<td>&nbsp;</td>
  </tr>
  <tr>
	<td colspan="8">KONDISI KHUSUS/SEVERITY LEVEL DAN KODE INA CBG</td>
  </tr> 
  <tr>
     <td width="13" rowspan="10" style="background-color: #9F9F9F;font: bold 30px arial, sans-serif;color: #ffffff;">P</td>
	<td bgcolor="#9F9F9F" colspan="3" style="text-align: center">TERAPI OBAT/KM</td>
	<td bgcolor="#9F9F9F" style="text-align: center">DOSIS</td>
	<td bgcolor="#9F9F9F" colspan="4" style="text-align: center">RENCANA TINDAKAN</td>
	<td style="background-color: #9F9F9F;color:#000000;text-align: center;">PETUGAS</td>
  </tr> 
  <tr>
	<td colspan="3">&nbsp;</td>
	<td>&nbsp;</td>
	<td colspan="4">&nbsp;</td>
	<td rowspan="5">&nbsp;</td>
  </tr> 
  <tr>
	<td colspan="3">&nbsp;</td>
	<td>&nbsp;</td>
	<td colspan="4">&nbsp;</td>
  </tr>
  <tr>
	<td colspan="3">&nbsp;</td>
	<td>&nbsp;</td>
	<td colspan="4">&nbsp;</td>
  </tr>
  <tr>
	<td colspan="3">&nbsp;</td>
	<td>&nbsp;</td>
	<td colspan="4">&nbsp;</td>
  </tr>
  <tr>
	<td colspan="3">&nbsp;</td>
	<td>&nbsp;</td>
	<td colspan="4" style="text-align: left;">KODE:</td>
  </tr>
  <tr> 
	<td colspan="8" style="text-transform: uppercase; background-color: #9f9f9f; color: #ffffff; text-decoration: underline;">
	    catatan rencana penanganan, tindakan, dan penyuluhan pasien
	</td>
	<td style="text-transform: uppercase; background-color: #9f9f9f; color: #ffffff;">petugas</td>
  </tr> 
  <tr> 
     <td colspan="8">&nbsp;</td>
     <td rowspan="3">&nbsp;</td>
  </tr> 
  <tr> 
     <td colspan="8">&nbsp;</td>
  </tr> 
  <tr> 
     <td colspan="8">&nbsp;</td>
  </tr> 
</table>   
     <script>Print();</script>
</body>
</html>
