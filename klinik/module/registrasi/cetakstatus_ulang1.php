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
	
	$sql = "select a.*,((current_date - cust_usr_tanggal_lahir)/365) as umur from global.global_customer_user a  where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
	$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
	
	// ---- Data registrasi terakhir ---- 
	$sql = "select reg_id, reg_jenis_pasien 
			from klinik.klinik_registrasi a 
			where a.id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])." and reg_tanggal <> ".QuoteValue(DPE_DATE,date('Y-m-d'))." 
			order by reg_when_update desc limit 1"; 
	$dataReg = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
	
	if($dataReg) { 
		//--- data refraksi --- 
		$sql = "select ref_id, ref_keluhan, b.visus_nama as visus_non_od, c.visus_nama as visus_non_os,ref_mata_od_koreksi_spheris,ref_mata_od_koreksi_cylinder,
				ref_mata_od_koreksi_sudut,ref_mata_os_koreksi_spheris,ref_mata_od_koreksi_cylinder,ref_mata_os_koreksi_sudut, d.visus_nama as visus_od, e.visus_nama as visus_os 
				from klinik.klinik_refraksi a
				left join klinik.klinik_visus b on b.visus_id = a.id_visus_nonkoreksi_od
				left join klinik.klinik_visus c on c.visus_id = a.id_visus_nonkoreksi_os 
				left join klinik.klinik_visus d on d.visus_id = a.id_visus_koreksi_od 
				left join klinik.klinik_visus e on e.visus_id = a.id_visus_koreksi_os 
				where a.id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])."
				and a.id_reg = ".QuoteValue(DPE_CHAR,$dataReg["reg_id"]);
		$dataRefraksi = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
		
		
		//--- data pemeriksaan --- 
		$sql = "select a.*, op_jenis_nama, op_paket_nama 
				from klinik.klinik_perawatan a
				left join klinik.klinik_operasi_jenis b on b.op_jenis_id = a.rawat_operasi_jenis
				left join klinik.klinik_operasi_paket c on c.op_paket_id = a.rawat_operasi_paket 
				where a.id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])."
				and a.id_reg = ".QuoteValue(DPE_CHAR,$dataReg["reg_id"]);
		$dataPeriksa = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
		
          // --- icd od
          $sql = "select icd_nama,icd_nomor, icd_id from klinik.klinik_perawatan_icd a
                    join klinik.klinik_icd b on a.id_icd = b.icd_id
				where id_rawat = ".QuoteValue(DPE_CHAR,$dataPeriksa["rawat_id"])." and rawat_icd_odos = 'OD'
                    order by rawat_icd_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) { 
               $_POST["rawat_icd_od_kode"][$i] = $row["icd_nomor"];
               $_POST["rawat_icd_od_nama"][$i] = $row["icd_nama"];
               $i++;
          }
		
          // --- icd os
          $sql = "select icd_nama,icd_nomor, icd_id from klinik.klinik_perawatan_icd a
                    join klinik.klinik_icd b on a.id_icd = b.icd_id
				where id_rawat = ".QuoteValue(DPE_CHAR,$dataPeriksa["rawat_id"])." and rawat_icd_odos = 'OS'
                    order by rawat_icd_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) { 
               $_POST["rawat_icd_os_kode"][$i] = $row["icd_nomor"];
               $_POST["rawat_icd_os_nama"][$i] = $row["icd_nama"];
               $i++;
          }
		
            // --- terapi obat
          $sql = "select item_id, item_nama,item_fisik, id_dosis,dosis_nama from klinik.klinik_perawatan_terapi a
                    left join inventori.inv_item b on a.id_item = b.item_id
				left join inventori.inv_dosis c on c.dosis_id = a.id_dosis 
                    where id_rawat = ".QuoteValue(DPE_CHAR,$dataPeriksa["rawat_id"])." 
                    order by rawat_item_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0; 
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["dosis_nama"] .= $row["dosis_nama"]."<br>"; 
               $_POST["item_nama"] .= $row["item_nama"]."<br>"; 
               $i++;
          }
		
          // --- ina od
          $sql = "select ina_nama,ina_kode, ina_id from klinik.klinik_perawatan_ina a
                    join klinik.klinik_ina b on a.id_ina = b.ina_id
				where id_rawat = ".QuoteValue(DPE_CHAR,$dataPeriksa["rawat_id"])." and rawat_ina_odos = 'OD'
                    order by rawat_ina_urut";
          $rs = $dtaccess->Execute($sql);
		$inaOd = $dtaccess->Fetch($rs);
		
          // --- ina os
          $sql = "select ina_nama,ina_kode, ina_id from klinik.klinik_perawatan_ina a
                    join klinik.klinik_ina b on a.id_ina = b.ina_id
				where id_rawat = ".QuoteValue(DPE_CHAR,$dataPeriksa["rawat_id"])." and rawat_ina_odos = 'OS'
                    order by rawat_ina_urut";
          $rs = $dtaccess->Execute($sql); 
		$inaOs = $dtaccess->Fetch($rs);
		
		//--- data diagnosis  --- 
		$sql = "select diag_lab_gula_darah,diag_lab_darah_lengkap 
				from klinik.klinik_diagnostik a 
				where a.id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])."
				and a.id_reg = ".QuoteValue(DPE_CHAR,$dataReg["reg_id"]);
		$dataDiag = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
		 
		//--- data bedah minor  --- 
		$sql = "select icd_nama 
				from klinik.klinik_perawatan_operasi a
				join klinik.klinik_perawatan_operasi_icd b on b.id_op = a.op_id
				join klinik.klinik_icd c on c.icd_id = b.id_icd 
				where a.id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])."
				and a.id_reg = ".QuoteValue(DPE_CHAR,$dataReg["reg_id"]);
		$dataBedah = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
		 
		//--- data bedah minor  --- 
		$sql = "select icd_nama 
				from klinik.klinik_operasi a 
				join klinik.klinik_icd c on c.icd_id = a.id_icd 
				where a.id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])."
				and a.id_reg = ".QuoteValue(DPE_CHAR,$dataReg["reg_id"]);
		$dataOperasi = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
	}

?>

<html>
<head>

<title>Cetak Kartu Pasien</title> 
<script> 
	window.print();  
</script>
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
    .judul5 {
	    font-size: 11px;
	    font-weight: bold;
	    background-color : #040404;
	    text-align : center;
	    color : #FFFFFF;
    } 

</style>
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
    <td>&nbsp;<?php echo $dataPasien["cust_usr_alamat"]; ?></td>
    <td>PEKERJAAN</td>
    <td>&nbsp;<?php echo $dataPasien["cust_usr_pekerjaan"]; ?></td>
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
    <td>&nbsp;<?php echo $bayarPasien[$dataReg["reg_jenis_pasien"]];?></td>
  </tr>
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
    <td colspan="4" class="judul5">HASIL PEMERIKSAAN</td> 
  </tr>
  <tr>
    <td width="80">ANAMNESA</td>
    <td width="235" align="left">&nbsp;<?php echo $dataRefraksi["ref_keluhan"];?></td>
    <td width="235" align="right">&nbsp;<?php echo $dataPeriksa["rawat_lab_alergi"];?></td>
    <td nowrap>ALLERGI</td>
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
    <td width="30" align="center"><?php echo $dataRefraksi["visus_non_od"];?></td>
    <td width="150" align="left"><?php echo $dataRefraksi["ref_mata_od_koreksi_spheris"]; echo ($dataRefraksi["ref_mata_od_koreksi_cylinder"])?", ".$dataRefraksi["ref_mata_od_koreksi_cylinder"]:""; echo ($dataRefraksi["ref_mata_od_koreksi_sudut"])?", ".$dataRefraksi["ref_mata_od_koreksi_sudut"]:"";?></td> 
    <td width="70" align="center"><?php echo $dataRefraksi["visus_od"];?></td> 
    <td width="30" align="center"><?php echo $dataRefraksi["visus_non_os"];?></td>
    <td width="150" align="left"><?php echo $dataRefraksi["ref_mata_os_koreksi_spheris"]; echo ($dataRefraksi["ref_mata_os_koreksi_cylinder"])?", ".$dataRefraksi["ref_mata_os_koreksi_cylinder"]:""; echo ($dataRefraksi["ref_mata_os_koreksi_sudut"])?", ".$dataRefraksi["ref_mata_os_koreksi_sudut"]:"";?></td> 
    <td width="70" align="center"><?php echo $dataRefraksi["visus_os"];?></td> 
  </tr>
  <tr>
    <td width="30" colspan="3">KACAMATA OD :&nbsp;</td>
    <td width="30" colspan="3">KACAMATA OS :&nbsp;</td> 
  </tr>
</table> 
<table width="610" border="1" cellpadding="0" cellspacing="0" class="tableisi"> 
  <tr>
    <td width="180" class="judul4" colspan="2">OD</td>
    <td width="105" class="judul5">SLIT LAMP</td>
    <td width="180" class="judul4" colspan="2">OS</td> 
  </tr> 
  <tr>
    <td rowspan="5"><img src="<?php echo $APLICATION_ROOT;?>images/mata-1.gif"></td>
    <td width="25%" align="left"><?php echo $dataPeriksa["rawat_mata_od_palpebra"];?></td>
    <td align="center" width="25%">PALPEBRA</td>
    <td width="25%" align="right"><?php echo $dataPeriksa["rawat_mata_os_palpebra"];?></td>
    <td align="right" rowspan="5"><img src="<?php echo $APLICATION_ROOT;?>images/mata-5.gif"></td> 
  </tr> 
  <tr> 
    <td align="left"><?php echo $dataPeriksa["rawat_mata_od_conjunctiva"];?></td>
    <td align="center">KONJUCTIVA</td> 
    <td align="right"><?php echo $dataPeriksa["rawat_mata_os_conjunctiva"];?></td>
  </tr> 
  <tr> 
    <td align="left"><?php echo $dataPeriksa["rawat_mata_od_cornea"];?></td>
    <td align="center">CORNEA</td> 
    <td align="right"><?php echo $dataPeriksa["rawat_mata_os_cornea"];?></td>
  </tr> 
  <tr> 
    <td align="left"><?php echo $dataPeriksa["rawat_mata_od_coa"];?></td>
    <td align="center">ANT CHAMBER</td> 
    <td align="right"><?php echo $dataPeriksa["rawat_mata_os_coa"];?></td>
  </tr> 
  <tr> 
    <td align="left"><?php echo $dataPeriksa["rawat_mata_od_iris"];?></td>
    <td align="center">IRIS</td> 
    <td align="right"><?php echo $dataPeriksa["rawat_mata_os_iris"];?></td>
  </tr> 
  <tr>
    <td rowspan="4"><img src="<?php echo $APLICATION_ROOT;?>images/mata-3.gif"></td>
    <td align="left"><?php echo $dataPeriksa["rawat_mata_od_pupil"];?></td>
    <td align="center">PUPIL</td>
    <td align="right"><?php echo $dataPeriksa["rawat_mata_os_pupil"];?></td>
    <td rowspan="4" align="right"><img src="<?php echo $APLICATION_ROOT;?>images/mata-6.gif"></td> 
  </tr> 
  <tr> 
    <td align="left"><?php echo $dataPeriksa["rawat_mata_od_lensa"];?></td>
    <td align="center">LENSA</td> 
    <td align="right"><?php echo $dataPeriksa["rawat_mata_os_lensa"];?></td>
  </tr> 
  <tr> 
    <td align="left"><?php echo $dataPeriksa["rawat_od_vitreus"];?></td>
    <td align="center">VITREUS</td> 
    <td align="right">&nbsp;</td>
  </tr> 
  <tr> 
    <td>&nbsp;</td>
    <td align="center" class="judul5">ALAT LAIN</td> 
    <td>&nbsp;</td>
  </tr> 
  <tr>
    <td rowspan="4"><img src="<?php echo $APLICATION_ROOT;?>images/mata-4.gif"></td>
    <td align="left"><?php echo $dataPeriksa["rawat_tonometri_pressure_od"];?></td>
    <td align="center">TONOMETRI</td>
    <td align="right"><?php echo $dataPeriksa["rawat_tonometri_pressure_os"];?></td>
    <td rowspan="4" align="right"><img src="<?php echo $APLICATION_ROOT;?>images/mata-7.gif"></td> 
  </tr> 
  <tr> 
    <td align="left"><?php echo $dataPeriksa["rawat_anel"];?></td>
    <td align="center">ANELTEST</td> 
    <td align="right">&nbsp;</td>
  </tr> 
  <tr> 
    <td align="left"><?php echo $dataPeriksa["rawat_mata_od_coa"];?></td>
    <td align="center">FUNDUSCOPY</td> 
    <td align="right">&nbsp;</td>
  </tr> 
  <tr> 
    <td align="left"><?php echo $dataPeriksa["rawat_schimer"];?></td>
    <td align="center">SCHIMER TEST</td> 
    <td align="right">&nbsp;</td>
  </tr> 
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr height="45" valign=top>
	<td colspan="2" width="237" align="justify"><u>PERMERISAAN LAB</u>
	<br>- &nbsp;<?php echo $dataDiag["diag_lab_gula_darah"];?><br>- &nbsp;<?php echo $dataDiag["diag_lab_darah_lengkap"];?></td>
	<td colspan="2" width="237" align="justify"><u>KEADAAN UMUM,TENSI,NADI,TEMP,RESPIRASI</u><br>
	<?php echo $rawatKeadaan[$dataPeriksa["rawat_keadaan_umum"]]; echo ($dataPeriksa["rawat_lab_tensi"])?", ".$dataPeriksa["rawat_lab_tensi"]:""; echo ($dataPeriksa["rawat_lab_nadi"])?", ".$dataPeriksa["rawat_lab_nadi"]:""; echo ($dataPeriksa["rawat_lab_nafas"])?", ".$dataPeriksa["rawat_lab_nafas"]:"";?>
  </td>
  </tr> 
  <tr>
	<td width="150" bgcolor="#CCCCCC">DIAGNOSIS OD</td>
	<td width="50" bgcolor="#CCCCCC">ICD 10</td>
	<td width="150" bgcolor="#CCCCCC">DIAGNOSIS OD</td>
	<td width="50" bgcolor="#CCCCCC">ICD 10</td>
  </tr> 
  <tr>
	<td width="150">1. <?php echo $_POST["rawat_icd_od_nama"][0];?></td>
	<td width="50"><?php echo $_POST["rawat_icd_od_kode"][0];?></td>
	<td width="150">1. <?php echo $_POST["rawat_icd_os_nama"][0];?></td>
	<td width="50"><?php echo $_POST["rawat_icd_os_kode"][0];?></td>
  </tr> 
  <tr>
	<td width="150">2. <?php echo $_POST["rawat_icd_od_nama"][1];?></td>
	<td width="50"><?php echo $_POST["rawat_icd_od_kode"][1];?></td>
	<td width="150">2. <?php echo $_POST["rawat_icd_os_nama"][1];?></td>
	<td width="50"><?php echo $_POST["rawat_icd_os_kode"][1];?></td>
  </tr> 
  <tr>
	<td width="150">INA DRG</td>
	<td width="50"><?php echo $inaOd["ina_nama"];?></td>
	<td width="150">INA DRG</td>
	<td width="50"><?php echo $inaOs["ina_nama"];?></td>
  </tr> 
</table> 
<table width="610" border="1" cellpadding="2" cellspacing="0" class="tableisi">
  <tr>
	<td width="150" bgcolor="#CCCCCC">TERAPI OBAT/KM</td>
	<td width="50" bgcolor="#CCCCCC">DOSIS</td>
	<td width="150" bgcolor="#CCCCCC">RENCANA TINDAKAN</td>
	<td width="50" bgcolor="#CCCCCC">JENIS</td>
  </tr> 
  <tr height="20">
	<td width="150" rowspan="2"><?php echo $_POST["item_nama"];?></td>
	<td width="50" rowspan="2"><?php echo $_POST["dosis_nama"];?></td>
	<td width="150"><?php echo $dataPeriksa["op_paket_nama"];?></td>
	<td width="50"><?php echo $dataPeriksa["op_jenis_nama"];?></td>
  </tr> 
  <tr> 
	<td width="150" colspan="2">ICDM :&nbsp;<?php echo ($dataBedah["icd_nama"])?$dataBedah["icd_nama"]:$dataOperasi["icd_nama"];?></td> 
  </tr> 
  <tr height="25" valign="top"> 
	<td width="150" colspan="4">CATATAN :</td> 
  </tr> 
</table>   
     <script>Print();</script>
</body>
</html>
