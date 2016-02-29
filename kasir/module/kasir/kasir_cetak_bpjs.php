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
     
	    $sql = "select a.id_kwitansi, a.fol_dibayar_when , b.kwitansi_nomor from klinik.klinik_folio a
		   join global.global_kwitansi b on b.kwitansi_id = a.id_kwitansi 
		   where a.id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]) ;
	    $dataKWT = $dtaccess->Fetch($sql);
	    
	    $_POST["kwitansi_id"] = $dataKWT['id_kwitansi'];
	    $_POST["id_reg"] = $_GET["id_reg"]; 
	    $_POST["fol_jenis"] = $_GET["jenis"]; 
	    $_POST["kwitansi_nomor"] = $dataKWT['kwitansi_nomor'];
	    
	    
	  $sql = "select b.icd_nomor, b.icd_nama
			  from klinik.klinik_perawatan_icd a
			  join klinik.klinik_icd b on a.id_icd = b.icd_nomor
			  left join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
			  where c.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
			  and a.rawat_icd_odos = 'OD' 
			  order by a.rawat_icd_urut";
	  $dataDiagIcdOD = $dtaccess->FetchAll($sql);
	  
  //echo $sql."<br />";
	  $sql = "select b.icd_nomor, b.icd_nama
			  from klinik.klinik_perawatan_icd a
			  join klinik.klinik_icd b on a.id_icd = b.icd_nomor
			  left join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
			  where c.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
			  and a.rawat_icd_odos = 'OS' 
			  order by a.rawat_icd_urut";
	  $dataDiagIcdOS = $dtaccess->FetchAll($sql);
	  
  //echo $sql;
	 $sql = "select b.prosedur_kode, b.prosedur_nama
		   from klinik.klinik_perawatan_prosedur a
		   join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode
		   left join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
		   where c.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
		   order by a.rawat_prosedur_urut";
	 $dataProsedur = $dtaccess->FetchAll($sql);
	 
	  /*
	   * cari data INA CBG
	   */
	  $sql_rj = "select * from klinik.klinik_folio a
		    join klinik.klinik_tarif_rj b on b.tarif_rj_code = a.id_biaya
		    where a.fol_jenis='IC' and a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	  $rs_rj = $dtaccess->Execute($sql_rj);
	  $dataINACBGRJ = $dtaccess->Fetch($rs_rj);
	  //echo $sql;
	  
	  $sql_ri = "select * from klinik.klinik_folio a
		    join klinik.klinik_tarif_ri b on b.tarif_ri_code = a.id_biaya
		    where a.fol_jenis='IC' and a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	  $rs_ri = $dtaccess->Execute($sql_ri);
	  $dataINACBGRI = $dtaccess->Fetch($rs_ri);
	  // -- end cari INA CBG -- //
	  
	  $sql_folio_special_tariff = "select sum(fol_dibayar) as total_special_tariff from klinik.klinik_folio where fol_jenis='SP' and id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	  $rs_folio_special_tariff = $dtaccess->Execute($sql_folio_special_tariff);
	  $dataFolioSpecialTariff = $dtaccess->Fetch($rs_folio_special_tariff);
	  
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
				
				//$dtmodel->Insert() or die("insert error"); 
				
				unset($dtmodel);
				unset($dbField);
				unset($dbValue);
				unset($dbKey);    
        }
         
    $sql = "update klinik.klinik_folio set id_kwitansi = ".QuoteValue(DPE_NUMERIC,$_POST["kwitansi_id"])."  where fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		//$dtaccess->Execute($sql); 
        	
		$sql = "select * from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dataFolio = $dtaccess->FetchAll($sql);
		
		$sql = "select b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_jenis_kelamin ,b.cust_usr_alergi,
		((current_date - cust_usr_tanggal_lahir)/365) as umur, a.*, d.rawatinap_tanggal_masuk, d.rawatinap_tanggal_keluar, e.biaya_nama as kelas_inap
		from klinik.klinik_registrasi a
		left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
		left join global.global_kwitansi c on c.id_reg = a.reg_id
		left join klinik.klinik_rawatinap d on a.reg_id = d.id_reg
		left join klinik.klinik_biaya e on e.biaya_kode = d.id_kategori_kamar
		where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
		    //echo $sql;
	    $dataPasien= $dtaccess->Fetch($sql);
  
      
		//$_POST["kwitansi_nomor"] = $kodeKwitansi;
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
	}
    
        
    
    
?>

<html>
<head>

<title>Cetak Tagihan BPJS</title>

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

.tableisi {
	border-collapse:collapse;
    font-family:      monospace;
    font-size:10px;
	border-top: black solid 1px; 
	border-bottom: black solid 1px;
}



.tablenota {
    font-family:      monospace;
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
function cetak() {
	window.print();
}
      
</script>

</head>

<body onload="cetak();">

<div style="width:14cm;margin:10px 0 0 5px;position:absolute;height:21.5cm;" align="center" style="margin-left:70px;">
<table align="center" style="width:21cm;"  border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="center" style="font-size:17px">
		PEMERINTAH PROPINSI JAWA TIMUR<br />
		<big><b>RSMM CeHC SURABAYA</b></big><br />
		RUMAH SAKIT MATA MASYARAKAT<br /><br />
		<small>Jl. Gayung Kebonsari Timur No. 49 Surabaya<br />
    tlp. (031) 8283508-10 E-mail bkmm@diknesjatim.go.id
    </small>
    </td>
	</tr>
</table> 
<BR><BR>

<table border="0" align="center" cellpadding="4" style="width:21cm;" cellspacing="1">
     <tr>
	    <td align="center" style="font-size:14px"><STRONG>RESUME PELAYANAN BPJS</STRONG></td>
     </tr>
</table> 
<br>
<table border="1" align="center" style="width:21cm;border:1px solid black;border-collapse:collapse;font-size:12px;" >
     <tr style="">
	    <td style="border-top:1px solid black; border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0" width= "20%" align="left"><b>NO REG</b></td>
	    <td style="border-top:1px solid black; border-bottom:1px solid black;border-right:1px solid black;" width= "15%" align="left"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>		
	    <td style="border-top:1px solid black; border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0"  width= "15%" align="left"><b>NO BPJS</b></td>
	    <td style="border-top:1px solid black; border-bottom:1px solid black;border-right:1px solid black;" width= "50%" align="center" colspan="2"><label><?php echo $dataPasien["reg_no_kartubpjs"];?></label></td>
     </tr>	
     <tr height="25">
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0" width= "20%" align="left"><b>NAMA PASIEN</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;" width= "30%" align="left" colspan="2"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "25%" align="left"><b>UMUR:</b>&nbsp;<label><?php echo $dataPasien["umur"]; ?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "25%" align="left"><b>L/P:</b>&nbsp;<label><?php echo $dataPasien["cust_usr_jenis_kelamin"]; ?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0"   width= "20%" align="left"><b>NO SEP.</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "80%" align="left" colspan="4"><label><?php echo $dataPasien["reg_no_sep"]; ?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0"   width= "20%" align="left"><b>JENIS PEL/KELAS</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "80%" align="left" colspan="4"><label><?php echo ($dataPasien["reg_status"]{1}=="I"||$dataPasien["reg_status"]{1}=="C"||$dataPasien["kelas_inap"])?"Rawat&nbsp;Inap&nbsp;/&nbsp;".$dataPasien["kelas_inap"]:"Rawat&nbsp;Jalan";?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0"   width= "20%" align="left"><b>TGL PELAYANAN (MASUK)</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "30%" align="left" colspan="2"><label><?php echo format_date($dataPasien["reg_tanggal"]);?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0"   width= "20%" align="left"><b>TGL PELAYANAN (KELUAR)</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "30%" align="left"><label><?php echo format_date($dataPasien["rawatinap_tanggal_masuk"]);?></label></td>
     </tr>
     <tr><?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { $total+=$dataFolio[$i]["fol_nominal"]; }?>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0"   width= "20%" align="left"><b>TARIF PERGUB</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "80%" align="left" colspan="4"><label><?php echo currency_format($total);?></label></td>
     </tr>
</table><br />
<table border="1" align="center" style="width:21cm;border:1px solid black;border-collapse:collapse;font-size:12px;" >
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#000000; color:#ffffff;"   width= "20%" align="left"><b>&nbsp;</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#000000; color:#ffffff;"   width= "60%" colspan="2" align="center"><b>DIAGNOSIS ICD 10/9</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#000000; color:#ffffff;"   width= "20%" align="center"><b>KODE ICD</b></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0"   width= "20%" align="left"><b>DIAGNOSIS UTAMA</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "60%" colspan="2" align="left"><label><?php echo $dataDiagIcdOD[0]["icd_nama"];?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><label><?php echo $dataDiagIcdOD[0]["icd_nomor"];?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>DIAGNOSIS I</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "60%" colspan="2" align="left"><label><?php echo $dataDiagIcdOS[0]["icd_nama"];?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><label><?php echo $dataDiagIcdOS[0]["icd_nomor"];?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>DIAGNOSIS II</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "60%" colspan="2" align="left"><label><?php echo $dataDiagIcdOS[1]["icd_nama"];?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><label><?php echo $dataDiagIcdOS[1]["icd_nomor"];?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#c0c0c0"   width= "20%" align="left"><b>PROSEDUR UTAMA</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "60%" colspan="2" align="left"><label><?php echo $dataProsedur[0]["prosedur_nama"];?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><label><?php echo $dataProsedur[0]["prosedur_kode"];?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>PROSEDUR I</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "60%" colspan="2" align="left"><label><?php echo $dataProsedur[1]["prosedur_nama"];?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><label><?php echo $dataProsedur[1]["prosedur_kode"];?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>PROSEDUR II</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "60%" colspan="2" align="left"><label><?php echo $dataProsedur[2]["prosedur_nama"];?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><label><?php echo $dataProsedur[2]["prosedur_kode"];?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>PROSEDUR III</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "60%" colspan="2" align="left"><label><?php echo $dataProsedur[3]["prosedur_nama"];?></label></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><label><?php echo $dataProsedur[3]["prosedur_kode"];?></label></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black; background-color:#000000; color:#ffffff;" colspan="4"><b>HASIL GROUPER</b></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>TIPE PASIEN</td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "30%" align="left"><?php echo ($dataPasien["reg_status"]{1}=="I"||$dataPasien["reg_status"]{1}=="C"||$dataPasien["kelas_inap"])?"Rawat&nbsp;Inap":"Rawat&nbsp;Jalan";?></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   colspan="2" width= "50%" align="left"><?php echo ($dataPasien["reg_status"]{1}=="I"||$dataPasien["reg_status"]{1}=="C"||$dataPasien["kelas_inap"])?$dataPasien["kelas_inap"]:"&nbsp;";?></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>KODE INA CBG</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "30%" align="left">&nbsp;<?php echo ($dataINACBGRJ)?$dataINACBGRJ["tarif_rj_code"]:$dataINACBGRI["tarif_ri_code"];?></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   colspan="2" width= "50%" align="left"><b>TIPE CBG:&nbsp;<?php echo ($dataINACBGRJ)?$dataINACBGRJ["tarif_rj_deskripsi"]:$dataINACBGRI["tarif_ri_deskripsi"];?></b></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>TARIF INA CBG</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   colspan="3" width= "50%" align="left">&nbsp;Rp&nbsp;<?php echo ($dataINACBGRJ)?currency_format($dataINACBGRJ["fol_nominal"]):currency_format($dataINACBGRI["fol_nominal"]);?></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>TARIF CBG SPESIAL</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   colspan="3" width= "50%" align="left">&nbsp;Rp&nbsp;<?php echo currency_format($dataFolioSpecialTariff["total_special_tariff"]);?></td>
     </tr>
     <tr>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   width= "20%" align="left"><b>TOTAL TARIF CBG</b></td>
	    <td style="border-bottom:1px solid black;border-right:1px solid black;"   colspan="3" width= "50%" align="left">&nbsp;Rp&nbsp;<?php echo ($dataINACBGRJ)?currency_format($dataINACBGRJ["fol_nominal"]+$dataFolioSpecialTariff["total_special_tariff"]):currency_format($dataINACBGRI["fol_nominal"]+$dataFolioSpecialTariff["total_special_tariff"]);?></td>
     </tr>
</table><br />
<table  border="0" align="center" style="width:21cm;border:0px solid black;border-collapse:collapse;font-size:12px;" >
	<tr height="25">
		<td  style="border-bottom:0px solid black;border-right:0px solid black;"  width= "50%" align="center"><?php echo FormatFromTimeStamp($dataKWT['fol_dibayar_when']);?></td>
		<td style="border-bottom:0px solid black;border-right:0px solid black;"  width= "50%" align="center">&nbsp;</td>
	</tr>	
	<tr height="25">
		<td  style="border-bottom:0px solid black;border-right:0px solid black;"  width= "50%" align="center">Petugas Codding</td>
		<td style="border-bottom:0px solid black;border-right:0px solid black;"  width= "50%" align="center">Dokter Penanggung Jawab</td>
	</tr>	
	<tr height="65">
		<td align="center"  style="border-right:0px solid black; vertical-align:bottom;" >(<?php echo $userData['name']; ?>)</td>
		<td style="border-right:0px solid black;" align="left">&nbsp;</td>
	</tr>	
</table>
</div>

</body>
</html>
