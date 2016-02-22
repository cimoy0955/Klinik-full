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
		$sql = "select cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin,b.cust_usr_alergi, ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr
                    from klinik.klinik_registrasi a 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
    //echo $sql;
    $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["fol_jenis"] = $_GET["jenis"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		
		$sql = "select * from klinik.klinik_folio where fol_lunas = 'n' and fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		//echo $sql;
    $dataFolio = $dtaccess->FetchAll($sql);
	}

	$fotoName = $APLICATION_ROOT."images/logo_kasir.png";

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
     font-size:      10px;
	font-weight: bolder;
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
<!--<table width="375" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="center" style="font-size:14px"><img hspace="2" width="350" height="200" name="img_foto" id="img_foto" src="<?php echo $fotoName;?>"  border="0"></td>
	</tr>
</table> 

<BR><BR>-->

<table width="375" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="center" style="font-size:14px"><STRONG>BUKTI PEMBAYARAN<br>INVOICE</STRONG></td>
	</tr>
</table> 

<table width="375" border="1" cellpadding="3" cellspacing="1" class="tableisi">
	<tr class="judul" style="background-color:#F3F2F2">
		<td width= "40%" align="center"><STRONG>NAMA PASIEN</STRONG></td>
		<td width= "30%" align="center"><STRONG>NO REGISTER</STRONG></td>
		<td width= "40%" align="center"><STRONG>UMUR/JENIS KEL</STRONG></td>
	</tr>	
	<tr>
		<td align="center"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
		<td align="center"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
		<td align="center"><label><?php echo $dataPasien["umur"]."/".$dataPasien["cust_usr_jenis_kelamin"]; ?></label></td>
	</tr>	
</table>

<BR>
	
<table width="375" class="tablenota">
	<tr>
		<td class="judul" width="5%" align="center"><STRONG>NO</STRONG></td>
		<td class="judul" width="10%" align="center"><STRONG>KODE</STRONG></td>
		<td class="judul" width="40%" align="center"><STRONG>JENIS PELAYANAN</STRONG></td>
		<td class="judul" width="10%" align="center"><STRONG>VOL</STRONG></td>
		<td class="judul" width="20%" align="center"><STRONG>SUBTOTAL</STRONG></td>
	</tr>
	
	<?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { $total+=$dataFolio[$i]["fol_nominal"];?>
		<tr>
			<td align="right" class="isi"><?php echo ($i+1); ?></td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="left" class="isi"><?php echo $dataFolio[$i]["fol_nama"];?></td>
			<td align="right" class="isi">1</td>
			<td align="right" class="isi"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
		</tr>
	<?php } ?>
	<?php if($n<10) { for($i=0;$i<(10-$n);$i++) { ?>
		<tr>
			<td align="right" class="isi">&nbsp;</td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="left" class="isi">&nbsp;</td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="right" class="isi">&nbsp;</td>
		</tr>
	<?php }} ?>
	<tr>
		<td class="judul" align="left"colspan=4><strong>TOTAL</strong></td>
		<td class="judul" align="right"><?php echo currency_format($total);?></td>
	</tr>
</table>

<BR>
<BR>

<table width="375" border="1" cellpadding="1" cellspacing="1" class="tableisi">
	<tr class="judul">
		<td width= "25%" align="center">KASIR</td>
		<td width= "25%" align="center">TANGGAL</td>
		<td width= "25%" align="center">TANDA TANGAN</td>
		<td width= "25%" align="center">CONFIRMED</td>
	</tr>	
	<tr>
		<td align="left" class="ttd">&nbsp;</td>
		<td align="center" valign="center"><b><?php $tgl = getdateToday();
                           echo format_date_long($tgl);  ?></b></td>
		<td align="left">&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>	
</table>



</body>
</html>
