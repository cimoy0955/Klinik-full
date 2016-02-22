<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");   
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currency.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
 	$dtaccess = new DataAccess();                    
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();


 	if(!$auth->IsAllowed("kasir",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kasir",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "kasir_view.php";

  //ngambil data
        
	if($_GET["id_reg"]) {
	
		$sql = "select a.id_kwitansi , b.kwitansi_nomor from klinik.klinik_folio a
    join global.global_kwitansi b on b.kwitansi_id = a.id_kwitansi 
    where a.id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]) ;
		$dataKWT = $dtaccess->Fetch($sql);
    
    $_POST["kwitansi_id"] = $dataKWT['id_kwitansi'];
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["fol_jenis"] = $_GET["jenis"]; 
    $_POST["kwitansi_nomor"] = $dataKWT['kwitansi_nomor'];


	 


        if(!$_POST["kwitansi_id"]) 
        {
        
        
        $akhirKwit = $dtaccess->GetNewID("global_kwitansi","kwitansi_nomor",DB_SCHEMA_GLOBAL);
        
        if($akhirKwit==1){
        $awalKwit = 11000187;
        }
        $_POST["kwitansi_nomor"] = $awalKwit + $akhirKwit;  
		
        $dbTable = "global_kwitansi";
			
				$dbField[0] = "kwitansi_id";   // PK
				$dbField[1] = "kwitansi_nomor";
				$dbField[2] = "id_reg";
				
        if(!$_POST["kwitansi_id"]) $_POST["kwitansi_id"] = $dtaccess->GetNewID("global_kwitansi","kwitansi_id",DB_SCHEMA_GLOBAL);	  
				$dbValue[0] = QuoteValue(DPE_NUMERIC,$_POST["kwitansi_id"]);
				$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kwitansi_nomor"]);
				$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);

				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
				
				$dtmodel->Insert() or die("insert error"); 
				
				unset($dtmodel);
				unset($dbField);
				unset($dbValue);
				unset($dbKey);    
        }
         
    $sql = "update klinik.klinik_folio set id_kwitansi = ".QuoteValue(DPE_NUMERIC,$_POST["kwitansi_id"])."  where fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql); 
        	
		$sql_folio = "select * from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n'" ;
		$rs_folio = $dtaccess->Execute($sql_folio);
		$dataFolio = $dtaccess->FetchAll($rs_folio);


		$sql = "select b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_jenis_kelamin ,b.cust_usr_alergi, ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, b.cust_usr_alamat, b.cust_usr_jenis, d.kota_nama, e.rawatinap_tanggal_masuk, e.rawatinap_tanggal_keluar
                    from klinik.klinik_registrasi a 
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    left join global.global_kwitansi c on c.id_reg = a.reg_id
		    left join global.global_kota d on d.kota_id = b.cust_usr_kota
		    left join klinik.klinik_rawatinap e on e.id_reg = a.reg_id
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
   $dataPasien= $dtaccess->Fetch($sql);
  
      
		//$_POST["kwitansi_nomor"] = $kodeKwitansi;
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		
		$sql = "select rawat_id, rawat_tonometri_scale_od, rawat_tonometri_weight_od, rawat_tonometri_pressure_od, 
                    rawat_anel, rawat_schimer, rawat_operasi_jenis  
                    from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataPemeriksaan = $dtaccess->Fetch($sql);
          
          $sql = "select b.icd_nomor, b.icd_nama
                    from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_nomor
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    and a.rawat_icd_odos = 'OD' 
                    order by a.rawat_icd_urut";
	  $rsIcdOD = $dtaccess->Execute($sql);
          $dataDiagIcdOD = $dtaccess->FetchAll($rsIcdOD);
     
          $sql = "select b.icd_nomor, b.icd_nama
                    from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_nomor
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    and a.rawat_icd_odos = 'OS' 
                    order by a.rawat_icd_urut";
	  $rsIcdOS = $dtaccess->Execute($sql);
          $dataDiagIcdOS = $dtaccess->FetchAll($rsIcdOS);
     
          $sql = "select b.prosedur_kode, b.prosedur_nama
                    from klinik.klinik_perawatan_prosedur a join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    order by a.rawat_prosedur_urut";
	  $rsProsedur = $dtaccess->Execute($sql);
          $dataProsedur = $dtaccess->FetchAll($rsProsedur);
	}
    
?>

<html>
<head>

<title>Cetak Resume Pasien Rawat Inap</title>

<style type="text/css">
html{
  	width:100%;
  	height:100%;
  	margin:0;
  	padding:0;
}

body {
    font-family:      monospace;
    margin: 0px;
  	font-size: 10px;
  	width:100%;
  	height:100%;
  	margin:0;
  	padding:0;
}

table {
     width: 21cm;
     border: 1px solid black;
     border-collapse: collapse;
     font-size: 12px;
     font-weight: bold;
}

.spc_row{
     font-size: 13px;
     font-weight: bold;
}
.tableisi {
	border-collapse:collapse;
    font-family:      Verdana, Arial, Helvetica, sans-serif;
    font-size:10px;
	border-top: black solid 1px; 
	border-bottom: black solid 1px;
}



.tablenota {
    font-family:      Verdana, Arial, Helvetica, sans-serif;
    font-size:        10px;
	border: solid black 1px; 
	border-collapse:collapse;
}

.tablenota .judul  {
	border: solid black 1px; 

}

.tablenota .isi {
	border-right: solid black 1px;
}

.ttd {
	height:50px;
}

.judul {

     font-size:      10px;
	font-weight: bolder;
}


</style>



<script>
//$(document).ready( function() {
//	window.print();
//});
      
</script>

</head>

<body>

<div style="width:14cm;margin:80px 0 0 25px;position:absolute;height:15cm;" align="center" style="margin-left:70px;">
<table border="1" align="center" cellpadding="4" style="width:21cm;" cellspacing="1">
     <tr>
	  <td align="center" style="font-size: 18px;" colspan="10"><strong>RSMM JAWA TIMUR</strong></td>
     </tr>
     <tr height="1px">
	  <td align="center" style="font-size: 1px;" colspan="10"><strong>&nbsp;</strong></td>
     </tr>
     <tr>
	  <td align="center" style="font-size:14px;" colspan="10"><STRONG>RESUME REKAM MEDIS RAWAT INAP</STRONG></td>
     </tr>
     <tr height="1px">
	  <td align="center" style="font-size: 1px;" colspan="10"><strong>&nbsp;</strong></td>
     </tr>
     <tr>
	  <td width= "10%" align="left" class="spc_row">&nbsp;NAMA </td>
	  <td align="left" width="30%" class="spc_row" colspan="4"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
	  <td width= "15%" align="left" class="spc_row" colspan="2">&nbsp; NO REKAM MEDIS </td>
	  <td align="center" width="45%" colspan="3" class="spc_row"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
     </tr>	
     <tr>
	  <td width= "10%" align="left">&nbsp; ALAMAT </td>
	  <td align="left" width="30%" colspan="4"><label><?php echo nl2br($dataPasien["cust_usr_alamat"]); ?></label></td>
	  <td width= "25%" align="left" colspan="2">&nbsp; JENIS KEL </td>
	  <td align="center" width="10%"><label><?php echo $dataPasien["cust_usr_jenis_kelamin"]; ?></label></td>
	  <td width= "10%" align="left">&nbsp; UMUR </td>
	  <td align="center" width="10%"><label><?php echo HitungUmur($dataPasien["cust_usr_tanggal_lahir"]); ?></label></td>
     </tr>	
     <tr>
	  <td width= "10%" align="left">&nbsp; KAB/KOTA </td>
	  <td align="left" width="30%" colspan="4"><label><?php echo $dataPasien["kota_nama"]; ?></label></td>
	  <td width= "15%" align="left" colspan="2">&nbsp; JENIS PEMBIAYAAN </td>
	  <td align="center" width="45%" colspan="3"><label><?php echo $bayarPasien[$dataPasien["cust_usr_jenis"]]; ?></label></td>
     </tr>	
     <tr height="1px">
	  <td align="center" style="font-size: 1px;" colspan="10"><strong>&nbsp;</strong></td>
     </tr>
     <tr>
	  <td align="center" style="font-size: 14px;" colspan="10"><strong>HARI RAWAT INAP</strong></td>
     </tr>
     <tr>
	 <td width= "10%" align="left">&nbsp; MASUK </td>
	  <td align="center" width="30%" colspan="4"><label><?php echo $dataPasien["rawatinap_tanggal_masuk"]; ?></label></td>
	  <td width= "7%" align="left">&nbsp; LOS </td>
	  <td align="center" width="45%" colspan="4"><label><?php echo $dataPasien["cust_usr_jenis"]; ?></label></td>
     </tr>
     <tr>
	 <td width= "10%" align="left">&nbsp; KELUAR </td>
	  <td align="center" width="30%" colspan="4"><label><?php echo $dataPasien["rawatinap_tanggal_masuk"]; ?></label></td>
	  <td width= "7%" align="left">&nbsp; KELAS </td>
	  <td align="center" width="10%"><label><?php echo $dataPasien["cust_usr_jenis"]; ?></label></td>
	  <td width= "10%" align="left">&nbsp; RUANG </td>
	  <td align="center" width="%" colspan="2"><label><?php echo $dataPasien["cust_usr_jenis"]; ?></label></td>
     </tr>
     <tr>
	 <td width= "10%" align="left">&nbsp; PINDAH<br />&nbsp;KELAS </td>
	  <td align="center" width="30%" colspan="4"><label><?php echo $dataPasien["rawatinap_tanggal_masuk"]; ?></label></td>
	  <td width= "7%" align="left">&nbsp; MULAI<br />&nbsp;PINDAH </td>
	  <td align="center" width="45%" colspan="4"><label><?php echo $dataPasien["cust_usr_jenis"]; ?></label></td>
     </tr>
     <tr>
	  <td align="center" style="font-size: 14px;" colspan="10"><strong>PEMERIKSAAN DAN PERAWATAN SELAMA RAWAT INAP</strong></td>
     </tr>
     <tr>
	 <td width= "10%" align="left" rowspan="2">&nbsp; VISUS<br />&nbsp;AWAL </td>
	 <td colspan="9"> OD<br />&nbsp; </td>
     </tr>
     <tr>
	  <td colspan="9"> OS<br />&nbsp; </td>
     </tr>
     <tr>
	  <td> KU </td>
	  <td colspan="9">
	        KESADARAN: ........................
	       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	       T: .........
	       &nbsp;&nbsp;&nbsp;&nbsp;
	       N: .........
	       &nbsp;&nbsp;&nbsp;&nbsp;
	       RR: .........
	       &nbsp;&nbsp;&nbsp;&nbsp;
	       GDA: .........
	       &nbsp;&nbsp;&nbsp;&nbsp;
	       LAB: ......... 
	  </td>
     </tr>
     <tr>
	  <td colspan="2" rowspan="2" width="20%"> DIAGNOSIS AWAL </td>
	  <td colspan="8" width="80%"> OD<br />&nbsp; </td>
     </tr>
     <tr>
	  <td colspan="8"> OS<br />&nbsp; </td>
     </tr>
     <tr>
	  <td colspan="2" rowspan="2" width="20%"> DIAGNOSIS AKHIR </td>
	  <td colspan="8" width="80%"> OD<br />&nbsp; </td>
     </tr>
     <tr>
	  <td colspan="8"> OS<br />&nbsp; </td>
     </tr>
     <tr>
	  <td colspan="2" rowspan="3" width="20%"> TINDAKAN OPERASI<br />YA&nbsp;/&nbsp;TIDAK </td>
	  <td colspan="8" width="80%"> OD<br />&nbsp; </td>
     </tr>
     <tr>
	  <td colspan="8"> OS<br />&nbsp; </td>
     </tr>
     <tr>
	  <td colspan="8"> ADA KOMPLIKASI OPERASI YA / TIDAK ? .......... </td>
     </tr>
     <tr>
	  <td align="center" rowspan="2"> TINDAKAN<BR />MEDIS<BR />SAAT<br>DIRAWAT </td>
	  <td colspan="2"> PROSEDURE<br>PEMERIKSAAN </td>
	  <td colspan="7">&nbsp;</td>
     </tr>
     <tr>
	  <td colspan="2"> PROSEDURE TINDAKAN<br />(NON OPERASI) </td>
	  <td colspan="7">&nbsp;</td>
     </tr>
     <tr>
	  <td colspan="3" rowspan="3" align="center">
	       KEADAAN SAAT PULANG
	  </td>
	  <td>SEMBUH</td>
	  <td colspan="3">BLM SEMBUH PERLU KONTROL</td>
	  <td colspan="3">PERLU DIRUJUK</td>
     </tr>
     <tr>
	  <td rowspan="2">VISUS</td>
	  <td colspan="6">OD<br />&nbsp;</td>
     </tr>
     <tr>
	  <td colspan="6">OS<br />&nbsp;
	  </td>
     </tr>
     <tr>
	  <td colspan="10">PULANG PAKSA? YA / TIDAK&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RESEP SAAT PULANG? YA / TIDAK&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PENYULUHAN SAAT PULANG? YA / TIDAK
	  </td>
     </tr>
     <tr>
	  <td colspan="10">DIRUJUK KELUAR? YA / TIDAK; DIRUJUK KE ......................
	  </td>
     </tr>
</table>
<table border="0" align="center" style="width:21cm;border:1px solid black;border-collapse:collapse;font-size:12px;">
     <tr>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;">Diagnosis Utama</td>
	  <td align="left" style="width: 40%; text-align: left;border:1px solid black;"><?php echo $dataDiagIcdOD[0]["icd_nama"];?></td>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">ICD:&nbsp;&nbsp;<?php echo $dataDiagIcdOD[0]["icd_nomor"];?></td>
     </tr>
     <tr>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">Diagnosis II</td>
	  <td align="left" style="width: 40%; text-align: left;border:1px solid black;""><?php echo $dataDiagIcdOD[1]["icd_nama"];?></td>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">ICD:&nbsp;&nbsp;<?php echo $dataDiagIcdOD[1]["icd_nomor"];?></td>
     </tr>
     <tr>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">Diagnosis III</td>
	  <td align="left" style="width: 40%; text-align: left;border:1px solid black;""><?php echo $dataDiagIcdOD[2]["icd_nama"];?></td>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">ICD:&nbsp;&nbsp;<?php echo $dataDiagIcdOD[2]["icd_nomor"];?></td>
     </tr>
</table>
<table border="0" align="center" style="width:21cm;border:1px solid black;border-collapse:collapse;font-size:12px;">
     <tr>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">Prosedur Utama</td>
	  <td align="left" style="width: 40%; text-align: left;border:1px solid black;""><?php echo $dataProsedur[0]["prosedur_nama"];?></td>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">ICD:<?php echo $dataProsedur[0]["prosedur_kode"];?></td>
     </tr>
     <tr>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">Prosedur II</td>
	  <td align="left" style="width: 40%; text-align: left;border:1px solid black;""><?php echo $dataProsedur[1]["prosedur_nama"];?></td>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">ICD:<?php echo $dataProsedur[1]["prosedur_kode"];?></td>
     </tr>
     <tr>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">Prosedur III</td>
	  <td align="left" style="width: 40%; text-align: left;border:1px solid black;""><?php echo $dataProsedur[2]["prosedur_nama"];?></td>
	  <td align="left" style="width: 20%; text-align: left;border:1px solid black;"">ICD:<?php echo $dataProsedur[2]["prosedur_kode"];?></td>
     </tr>
</table>
<!--
<table style="width:21cm;"   align="center"  class="tablenota">
	<tr height="30">
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width="5%" align="center"><STRONG>NO</STRONG></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width="10%" align="center"><STRONG>KODE</STRONG></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width="40%" align="center"><STRONG>JENIS PELAYANAN</STRONG></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width="5%" align="center"><STRONG>JUMLAH</STRONG></td>
		<td style="border-bottom:1px solid black;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;" width="20%" align="center"><STRONG>SUBTOTAL</STRONG></td>
	</tr>
	
	<?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { $total+=$dataFolio[$i]["fol_nominal"];?>
		<tr height="25">
			<td align="right" class="isi"><?php echo ($i+1); ?></td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="left" class="isi"><?php echo $dataFolio[$i]["fol_nama"];?></td>
			<td align="right" class="isi"><?php echo $dataFolio[$i]["fol_jumlah"];?></td>
			<td align="right" class="isi"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
		</tr>
	<?php } ?>
	<?php if($n<10) { for($i=0;$i<(10-$n);$i++) { ?>
		<tr height="10">
			<td align="right" class="isi">&nbsp;</td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="left" class="isi">&nbsp;</td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="right" class="isi">&nbsp;</td>
		</tr>
	<?php }} ?>
	<tr height="25">
		<td style="border-bottom:1px solid black;border-top:1px solid black;" align="left"colspan=4><strong>TOTAL</strong></td>
		<td style="border-bottom:1px solid black;border-top:1px solid black;" align="right"><?php echo currency_format($total);?></td>
	</tr>
</table>

<BR>
<br>
<table border="0" align="center" style="width:21cm;border:1px solid black;border-collapse:collapse;font-size:12px;">
<tr height="25">
		<td colspan=4><strong>TERBILANG</strong></td>
	</tr>
<tr height="25">
		<td style="border-bottom:1px solid black;border-top:1px solid black;" align="left"><?php echo terbilang($total);?> Rupiah</td>
	</tr>
	</table>
<br>
<BR>
-->
<table  border="0" align="center" style="width:21cm;border:1px solid black;border-collapse:collapse;font-size:12px;" >
	<tr height="25">
		<td  style="border-bottom:1px solid black;border-right:1px solid black;"  width= "25%" align="center">PEETUGAS RAWAT INAP</td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;"  width= "25%" align="center">TANGGAL</td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;"  width= "25%" align="center">TANDA TANGAN</td>
	</tr>	
	<tr height="25">
		<td align="left"  style="border-right:1px solid black;" ><?php echo $userData['name']; ?></td>
		<td  style="border-right:1px solid black;" align="center" valign="center"><b><?php echo format_date_long(getdateToday());  ?> </td>
		<td style="border-right:1px solid black;" align="left">&nbsp;</td>
	</tr>	
</table>
</div>

</body>
</html>
