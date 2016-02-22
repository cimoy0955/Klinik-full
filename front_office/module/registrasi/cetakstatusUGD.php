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
	
	//if($tipeUmur=="D"){
	    $txtJudulKartu = "KARTU REKAM MEDIS UNIT GAWAT DARURAT";
	//}elseif($tipeUmur=="A"){
	//    $txtJudulKartu = "KARTU REKAM MEDIS RAWAT JALAN PEDIATRY OPTHALMOLOGY";
	//}
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
     
     
     .tableisi th {
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
     
     .triase {
	  font-size: 8px;
	  text-align: left;
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
     
     .triase {
	  font-size: 10px;
	  text-align: left;
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
    <td colspan="10" class="judul5" style="background-color: #cfcfcf;color:#000000;">IDENTITAS TRIASE</td>
    <!--<td width="25" style="background-color: #cfcfcf;color:#000000;">PETUGAS</td>-->
  </tr>
  <tr>
     <td class="triase" style="border-bottom: none;border-right: none;">JENIS SAKIT</td>
     <td colspan="9" class="triase" style="border-bottom: none;border-left: none;">:&nbsp;................................................................</td>
  </tr>
  <tr>
     <td class="triase" style="border-top: none;border-right:none;border-bottom:none;">JENIS KASUS</td>
     <td class="triase" colspan="9" style="border-top:none;border-left:none;border-bottom:none;">:
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;BEDAH&nbsp;&nbsp;</span>
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;NON BEDAH</span>&nbsp;&nbsp;
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;TRAUMA</span>&nbsp;&nbsp;
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;NON TRAUMA</span></span></span></span></span>
     </td>
  </tr>
  <tr>
     <td class="triase" style="border-right:none;border-top:none;border-bottom:none;">KATEGORI</td>
     <td class="triase" colspan="9" style="border-left:none;border-top:none;border-bottom:none;">:
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;MATA&nbsp;&nbsp;</span>
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;NON MATA</span>&nbsp;&nbsp;</span></span>
     </td>
  </tr>
  <tr>
     <td class="triase" style="border-right:none;border-top:none;border-bottom:none;">TINGKAT KEDARURATAN</td>
     <td class="triase" colspan="9" style="border-left:none;border-top:none;border-bottom:none;">:
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;BERAT&nbsp;&nbsp;</span>
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;SEDANG</span>&nbsp;&nbsp;
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;RINGAN&nbsp;&nbsp;</span>
	  <span style="position: inherit; margin: 3px 10px;"><input type="radio" /><span style="position: inherit; margin: 1px 1px;">&nbsp;BUKAN KASUS DARURAT</span>&nbsp;&nbsp;</span></span>
     </td>
  </tr>
  <tr>
     <td class="triase" colspan="10" style="border-top:none;border-bottom:none;">TANGGAL KEJADIAN&nbsp;&nbsp;
     :&nbsp;........................&nbsp;&nbsp;&nbsp;
     PUKUL&nbsp;&nbsp;
     :&nbsp;........................&nbsp;&nbsp;&nbsp;
     TEMPAT KEJADIAN&nbsp;&nbsp;
     :&nbsp;........................
     </td>
  </tr>
  <tr>
     <td class="triase" colspan="10" style="border-top:none;border-bottom:none;">TIBA DI RSMM PUKUL&nbsp;&nbsp;
     :&nbsp;........................,&nbsp;&nbsp;&nbsp;
     DENGAN TRANSPORT&nbsp;&nbsp;
     :&nbsp;AMBULANCE/KENDARAAN UMUM/PRIBADI&nbsp;&nbsp;&nbsp;
     </td>
  </tr>
  <tr>
     <td class="triase" colspan="10" style="border-top:none;border-bottom:none;">DITANGANI MULAI PUKUL&nbsp;&nbsp;
     :&nbsp;...............&nbsp;&nbsp;&nbsp;
     DIPERIKSA DOKTER PUKUL&nbsp;&nbsp;
     :&nbsp;...............&nbsp;&nbsp;&nbsp;
     OLEH DOKTER&nbsp;&nbsp;
     :&nbsp;........................
     </td>
  </tr>
</table>
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
    <td colspan="10" class="judul5" style="background-color: #cfcfcf;color:#000000;">CATATAN MEDIS (SUBYEKTIF, OBYEKTIF, ASSESMENT DAN PLANING)</td>
    <!--<td width="25" style="background-color: #cfcfcf;color:#000000;">PETUGAS</td>-->
  </tr>
  <tr>
     <td width="15" rowspan="3" style="background-color: #CFCFCF;font: bold 30px arial, sans-serif ">S</td>
    <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;" >ANAMNESA:</td>
  </tr>
  <tr>
     <td colspan="9" style="border-bottom:none;border-top:none;">&nbsp</td>
  </tr>  
  <tr>
     <td colspan="9" style="border-top:none;">&nbsp</td>
  </tr>
  <tr>
     <td width="15" rowspan="17" style="background-color: #BABABA;font: bold 30px arial, sans-serif ">O</td>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;text-decoration: underline;"  >PEMERIKSAAN FISIK:</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;text-decoration: underline;"  >STATUS GENERALIS:</td>
  </tr>
  <tr>
     <td width="15" style="border-right:none;border-bottom:none;border-top:none;" >&nbsp;</td>
     <td width="222" colspan="8" style="vertical-align: top;border-bottom:none;text-decoration: none;border-top:none;border-left:none;"  >KU:</td>
  </tr>
  <tr>
     <td width="15" style="border-right:none;border-bottom:none;border-top:none;" >&nbsp;</td>
     <td width="222" colspan="8" style="vertical-align: top;border-bottom:none;text-decoration: none;border-top:none;border-left:none;"  >KESADARAN (GCS):</td>
  </tr>
  <tr>
     <td width="15" style="border-right:none;border-bottom:none;border-top:none;" >&nbsp;</td>
     <td width="222" colspan="8" style="vertical-align: top;border-bottom:none;text-decoration: none;border-top:none;border-left:none;"  >KESADARAN (GCS):</td>
  </tr>
  <tr>
     <td width="15" style="border-right:none;border-bottom:none;border-top:none;" >&nbsp;</td>
     <td width="222" colspan="8" style="vertical-align: top;border-bottom:none;text-decoration: none;border-top:none;border-left:none;"  >TINDAKAN RESUSITASI:&nbsp;&nbsp;&nbsp;TIDAK&nbsp;/&nbsp;YA&nbsp;....................................</td>
  </tr>
  <tr>
     <td width="15" style="border-right:none;border-bottom:none;border-top:none;" >&nbsp;</td>
     <td width="222" colspan="8" style="vertical-align: top;border-bottom:none;text-decoration: none;border-top:none;border-left:none;"  >
	  TENSI:&nbsp;...........&nbsp;&nbsp;&nbsp;
	  NADI:&nbsp;...........&nbsp;&nbsp;&nbsp;
	  SUHU:&nbsp;...........&nbsp;&nbsp;&nbsp;
	  RESPIRASI:&nbsp;...........SPO2&nbsp;&nbsp;&nbsp;
     </td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;text-decoration: underline;"  >STATUS LOKALIS:</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;text-decoration: none;"  >&nbsp;</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;text-decoration: none;"  >&nbsp;</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;text-decoration: underline;"  >PEMERIKSAAN LAB:</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;text-decoration: none;"  >&nbsp;</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;text-decoration: underline;"  >PEMERIKSAAN RADIOLOGY:</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;text-decoration: none;"  >&nbsp;</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;text-decoration: underline;"  >PEMERIKSAAN MATA (UNTUK KASUS MATA):</td>
  </tr>
  <tr>
     <td width="15" style="border-right:none;border-bottom:none;border-top:none;" >&nbsp;</td>
     <td width="222" colspan="8" style="vertical-align: top;border-bottom:none;text-decoration: none;border-top:none;border-left:none;"  >
	  <table style="width: 80%;border-style: solid;" border="1" cellpadding="0px" cellspacing="0px">
	       <tr>
		    <td style="width: 10%;text-align: center;font-size: 8px;border-style: solid;">VA OD</td>
		    <td style="width: 25%;text-align: center;font-size: 8px;border-style: solid;">KOREKSI</td>
		    <td style="width: 15%;text-align: center;font-size: 8px;border-style: solid;">+KOREKSI</td>
		    <td style="width: 10%;text-align: center;font-size: 8px;border-style: solid;">VA OS</td>
		    <td style="width: 25%;text-align: center;font-size: 8px;border-style: solid;">KOREKSI</td>
		    <td style="width: 15%;text-align: center;font-size: 8px;border-style: solid;">+KOREKSI</td>
	       </tr>
	       <tr>
		    <td style="width: 10%;text-align: center;border-style: solid;">&nbsp;</td>
		    <td style="width: 25%;text-align: center;border-style: solid;">&nbsp;</td>
		    <td style="width: 15%;text-align: center;border-style: solid;">&nbsp;</td>
		    <td style="width: 10%;text-align: center;border-style: solid;">&nbsp;</td>
		    <td style="width: 25%;text-align: center;border-style: solid;">&nbsp;</td>
		    <td style="width: 15%;text-align: center;border-style: solid;">&nbsp;</td>
	       </tr>
	  </table>
     </td>
  </tr>
  <tr>
     <td width="15" style="border-right:none;border-bottom:none;border-top:none;">&nbsp;</td>
     <td width="222" colspan="8" style="vertical-align: top;border-bottom:none;text-decoration: none;border-top:none;border-left:none;" >
	  <img src='<?php echo $APLICATION_ROOT;?>/images/mata-1.gif' style="float:left;margin: 5px;" />
	  <table border="0" style="font-size: 8px;float:left;margin:50px 0px 0px 150px;">
	       <tr>
		    <td>TIO OD</td>
		    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		    <td>TIO OS</td>
		    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	       </tr>
	       <tr>
		    <td colspan="4">FLUORESCIN TEST</td>
	       </tr>
	  </table>
     </td>
  </tr>
  <tr>
     <td width="15" rowspan="4" style="background-color: #a6a6a6;font: bold 30px arial, sans-serif ">A</td>
    <td width="237" colspan="9" style="vertical-align: top;font-weight: bold;text-align: center;" >DIAGNOSIS KERJA</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;" >1.</td>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;" >2.</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-top:none;" >KONDISI KHUSUS/SEVERETY LEVEL</td>
  </tr>
  <tr>
     <td width="15" rowspan="7" style="background-color: #919191;font: bold 30px arial, sans-serif ">P</td>
    <td width="237" colspan="9" style="vertical-align: top;font-weight: bold;text-align: center;" >TERAPI OBAT/INJEKSI/INFUSE/O<sub>2</sub>/TINDAKAN RESUSITASI</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;" >&nbsp;</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-bottom:none;border-top:none;" >&nbsp;</td>
  </tr>
  <tr>
     <td width="237" colspan="9" style="vertical-align: top;border-top:none;" ><span style="font-size:10px;font-weight: bold;">KELANJUTAN&nbsp;&nbsp;&nbsp;</span>
	  <span style="font-size:8px;"><input type="radio" />&nbsp;DIRUJUK KE.....................&nbsp;&nbsp;
	  <input type="radio" />&nbsp;PULANG&nbsp;&nbsp;
	  <input type="radio" />&nbsp;RAWAT INAP&nbsp;&nbsp;
	  <input type="radio" />&nbsp;OK TINDAKAN</span>
     </td>
  </tr>
</table>   
     <script>Print();</script>
</body>
</html>
