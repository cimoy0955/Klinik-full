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
	  $_POST["id_reg"] = $_GET["id_reg"]; 
	  $_POST["nokwitansi"] = $_GET["nokwitansi"]; 
     
	if($_GET["jp"]==1){
	  $sql = "select a.*,b.biaya_kode as kode_layanan from klinik.klinik_folio a
		    left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
		    where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_kwitansi = ".QuoteValue(DPE_CHAR,$_POST["nokwitansi"]);
	  $dataFolio = $dtaccess->FetchAll($sql);
	}elseif($_GET["jp"]==0){
	  $sql = "select * from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_kwitansi = ".QuoteValue(DPE_CHAR,$_POST["nokwitansi"])." and fol_jenis not in ('IC','SP')";
	  $dataFolio = $dtaccess->FetchAll($sql);
	}
	  $sql = "select b.cust_usr_nama,b.cust_usr_kode,b.cust_usr_jenis_kelamin ,b.cust_usr_alergi, ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.kwitansi_nomor
	      from klinik.klinik_registrasi a 
	      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
	      left join global.global_kwitansi c on c.id_reg = a.reg_id
	      where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     $dataPasien= $dtaccess->Fetch($sql);

     
	  //$_POST["kwitansi_nomor"] = $kodeKwitansi;
	  $_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
    
     }
    
        
    
    
?>

<html>
<head>

<title>Cetak Invoice</title>

<style type="text/css">
html{
  	width	:100%;
  	height	:100%;
  	margin	:0;
  	padding	:0;
}

body {
    font-family		: arial;
    letter-spacing	: 2px;
    margin			: 0px;
  	font-size		: 10px;
  	width			: 100%;
  	height			: 100%;
  	margin			: 0;
  	padding			: 0;
}

.tableisi {
	border-collapse	: collapse;
	font-family		: monospace;
	font-size		: 9px;
	border-top		: black solid 1px; 
	border-bottom	: black solid 1px;
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

<div style="width:750px;margin:1px auto auto 10px;position:absolute;height:auto;float:left;">
<table align="center" style="width:95%;"  border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="center" style="font-size:17px">
		PEMERINTAH PROPINSI JAWA TIMUR<br />
		<big><b>RSMM SURABAYA</b></big><br />
		RUMAH SAKIT MATA MASYARAKAT<br /><br />
		<small>Jl. Gayung Kebonsari Timur No. 49 Surabaya<br />
    tlp. (031) 8283508-10 E-mail bkmm@diknesjatim.go.id
    </small>
    </td>
	</tr>
</table> 


<BR><BR>

<table border="0" align="center" cellpadding="4" style="width:100%;" cellspacing="1">
	<tr>
		<td align="center" style="font-size:14px"><STRONG>BUKTI PEMBAYARAN</STRONG></td>
	</tr>
</table> 
<br>
<table border="0" align="center" style="width:95%;border:1px solid black;border-collapse:collapse;font-size:12px;" >
	<tr>
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width= "40%" align="center"><b>NAMA PASIEN</b></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width= "30%" align="center"><b>NO REGISTER</b></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width= "40%" align="center"><b>NOMOR KWITANSI</b></td>
	</tr>	
	<tr height="25">
		<td style="border-bottom:1px solid black;border-right:1px solid black;" align="center"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;"  align="center"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;"  align="center"><label><?php echo $dataPasien["kwitansi_nomor"]; ?></label></td>
	</tr>	
</table>

<BR>

	
<table style="width:95%;"   align="center"  class="tablenota">
	<tr height="30">
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width="5%" align="center"><STRONG>NO</STRONG></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width="10%" align="center"><STRONG>KODE</STRONG></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width="40%" align="center"><STRONG>JENIS PELAYANAN</STRONG></td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;" width="5%" align="center"><STRONG>JUMLAH</STRONG></td>
		<td style="border-bottom:1px solid black;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;" width="20%" align="center"><STRONG>SUBTOTAL</STRONG></td>
	</tr>
	
	<?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { $total+=$dataFolio[$i]["fol_nominal"];?>
		<tr height="25">
			<td align="right" class="isi"><?php echo ($i+1); ?>&nbsp;&nbsp;&nbsp;</td>
			<td align="left" class="isi">&nbsp;<?php echo ($dataFolio[$i]["kode_layanan"])? $dataFolio[$i]["kode_layanan"]:$dataFolio[$i]["kode_obat"]; ?></td>
			<td align="left" class="isi">&nbsp;<?php echo $dataFolio[$i]["fol_nama"];?></td>
			<td align="right" class="isi"><?php echo $dataFolio[$i]["fol_jumlah"];?>&nbsp;&nbsp;&nbsp;</td>
			<td align="right" class="isi"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?>&nbsp;&nbsp;&nbsp;</td>
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
		<td style="border-bottom:1px solid black;border-top:1px solid black;" align="left"colspan=4>&nbsp;<strong>TOTAL</strong></td>
		<td style="border-bottom:1px solid black;border-top:1px solid black;" align="right"><?php echo currency_format($total);?>&nbsp;</td>
	</tr>
</table>

<br>
<table border="0" align="center" style="width:95%;border:1px solid black;border-collapse:collapse;font-size:12px;">
<tr height="25">
		<td colspan=4>&nbsp;&nbsp;<strong>TERBILANG</strong></td>
	</tr>
<tr height="25">
		<td style="border-bottom:1px solid black;border-top:1px solid black;" align="left">&nbsp;&nbsp;<?php echo terbilang($total);?> Rupiah</td>
	</tr>
	</table>
<br>
<BR>

<table  border="0" align="center" style="width:95%;border:1px solid black;border-collapse:collapse;font-size:12px;" >
	<tr height="25">
		<td  style="border-bottom:1px solid black;border-right:1px solid black;"  width= "25%" align="center">KASIR</td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;"  width= "25%" align="center">TANGGAL</td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;"  width= "25%" align="center">TANDA TANGAN</td>
		<td style="border-bottom:1px solid black;border-right:1px solid black;"  width= "25%" align="center">CONFIRMED</td>
	</tr>	
	<tr height="65">
		<td align="left"  style="border-right:1px solid black;text-align:center;" ><?php echo $userData['name']; ?></td>
		<td  style="border-right:1px solid black;" align="center" valign="center"><b><?php $tgl = getdateToday();
                           echo format_date_long($tgl);  ?></b></td>
		<td style="border-right:1px solid black;" align="left">&nbsp;</td>
		<td style="border-right:1px solid black;" align="left">&nbsp;</td>
	</tr>	
</table>
</div>

</body>
</html>
