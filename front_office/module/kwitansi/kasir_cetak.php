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
     

 	if(!$auth->IsAllowed("pembayaran_pasien",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("pembayaran_pasien",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "kasir_view.php";

 // echo $_GET["id_reg"].".".$_GET["jenis"];
	
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin,b.cust_usr_alergi, ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr
                    from klinik.klinik_registrasi a 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
    $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["fol_jenis"] = $_GET["jenis"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		
		$sql = "select * from klinik.klinik_folio where fol_lunas = 'n' and fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dataFolio = $dtaccess->FetchAll($sql);
		
		$sql = "select * from global.global_kwitansi where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
		$datakwitansi= $dtaccess->Fetch($sql);
		
    $_POST["kwitansi_id"] = $datakwitansi["kwitansi_id"];
    $_POST["kwitansi_nomor"] = $datakwitansi["kwitansi_nomor"];
		

          
          if(!$datakwitansi){
          $sql = "select max(CAST(substring(kwitansi_nomor from 1 for 6) as BIGINT)) as kode 
          from global.global_kwitansi";
          $lastKode = $dtaccess->Fetch($sql);
          $_POST["kwitansi_nomor"] = str_pad($lastKode["kode"]+1,6,"0",STR_PAD_LEFT);
          
        
        $dbTable = "global.global_kwitansi";
			
				$dbField[0] = "kwitansi_id";   // PK
				$dbField[1] = "kwitansi_nomor";
				$dbField[2] = "id_reg";
				
        if(!$_POST["kwitansi_id"]) $_POST["kwitansi_id"] = $dtaccess->GetNewID("global_kwitansi","kwitansi_id",DB_SCHEMA_GLOBAL);	  
				$dbValue[0] = QuoteValue(DPE_NUMERIC,$_POST["kwitansi_id"]);
				$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kwitansi_nomor"]);
				$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);

				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
				
				$dtmodel->Insert() or die("insert error"); 
				
				unset($dtmodel);
				unset($dbField);
				unset($dbValue);
				unset($dbKey);
          }
     }

	/*$fotoName = $APLICATION_ROOT."images/logo_kasir.png";*/
	$lokasi = $APLICATION_ROOT."image";
  
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
	border: solid #000000 0px; 
	padding:4px;
	border-collapse:collapse;
}

.tablenota .judul  {
	border: solid #000000 0px; 
	padding:4px;
}

.tablenota .isi {
	border-right: solid black 0px;
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
      
</script>
</head>

<body>
<table width="560" border="0" cellpadding="2" cellspacing="1">
	<tr><td align="right" valign="top" width="15%"><img src="<?php echo $APLICATION_ROOT;?>image/LogoPemkot.jpg" width="55px"></td>
		<td align="center" valign="top" style="font-size:12px; letter-spacing: 2px" width="85%"><STRONG>PEMERINTAH KOTA MADIUN</STRONG><BR><font size="3px"><B>RUMAH SAKIT UMUM DAERAH</B></font><BR>Jl. Campursari No. 12 Telp. / Fax. (0351)481314<br><b>MADIUN</b></td>
	</tr>
<tr>
		<td align="center" colspan="2"><hr color="black" width="100%"></td></tr>
</table>
<table width="560" border="0" cellpadding="2" cellspacing="1">
	<tr>
	<td align="right" valign="top" width="65%" style="font-size:12,5px"><b><u>KWITANSI</u></b></td>
	<td  align="center" width="35%" style="font-size:12px"><b>No. <?php echo $_POST["kwitansi_nomor"];?></b></td>
	</tr>
</table>
<table width="560" border="0" cellpadding="2" cellspacing="1">
	<tr><td><br></td></tr>
  <tr>
	<td align="left" valign="top" width="20%" style="font-size:12px">Sudah terima dari</td>
	<td  align="left" width="2%" style="font-size:12px">: </td>
	<td  align="left" width="78%" style="font-size:12px"><?php echo $dataPasien["cust_usr_nama"]; ?> </td>
	</tr>
	<tr>
	<td align="left" valign="top" width="20%" style="font-size:12px">Buat Pembayaran</td>
	<td  align="left" valign="top" width="2%" style="font-size:12px">: </td>
	<td  align="left" valign="top" width="78%" style="font-size:12px"><table width="100%" class="tablenota">
	<tr>
		<td class="judul" width="5%" align="center"><STRONG>NO</STRONG></td>
		<td class="judul" width="10%" align="center"><STRONG></STRONG></td>
		<td class="judul" width="40%" align="center"><STRONG>PELAYANAN</STRONG></td>
		<td class="judul" width="10%" align="center"><STRONG>VOL</STRONG></td>
		<td class="judul" width="20%" align="center"><STRONG>BIAYA</STRONG></td>
	</tr>
	
	<?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { 
  if($dataFolio[$i]["fol_jumlah"]){
            $total = $dataFolio[$i]["fol_jumlah"]*$dataFolio[$i]["fol_nominal"];
          }elseif(!$dataFolio[$i]["fol_jumlah"]){
            $total = $dataFolio[$i]["fol_nominal"];
          } ?>
		<tr>
			<td align="center" class="isi"><?php echo ($i+1); ?></td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="left" class="isi"><?php echo $dataFolio[$i]["fol_nama"];?></td>
			<td align="center" class="isi">
      <?php if($dataFolio[$i]["fol_jumlah"]){
          echo currency_format($dataFolio[$i]["fol_jumlah"]);
          }else{
          echo "1";
          } ?></td>
			<td align="right" class="isi">
      <?php  echo currency_format($total);?></td>
      <?php $totalHarga+=$total; ?>
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
		<td class="judul" align="left"colspan=4><strong>TOTAL BIAYA YANG HARUS DIBAYAR</strong></td>
		<td class="judul" align="right"><?php echo currency_format($totalHarga);?></td>
	</tr>
</table> </td>
	</tr>
</table>
<BR>
<table width="560" border="0" cellpadding="2" cellspacing="1">
	<tr>
	<td align="left" valign="top" width="20%" style="font-size:12px"><b>TERBILANG</b></td>
	<td  align="left" valign="top" width="2%" style="font-size:12px">: </td>
	<td  align="left" valign="top" width="45%" style="font-size:12px"><?php echo terbilang($total);?> Rupiah</td>
	<td  align="center" width="45%" style="font-size:12px">Madiun,  <?php $tgl = getdateToday();
                           echo format_date_long($tgl);  ?><br>Yang Menerima<br><br><br><br><b><u><?php echo $userData['name']; ?></u></b></td>
	</tr>
	</TABLE>
<!--<table width="500" class="tablenota">
	<tr>
		<td class="judul" width="5%" align="center"><STRONG>NO</STRONG></td>
		<td class="judul" width="10%" align="center"><STRONG>KODE</STRONG></td>
		<td class="judul" width="40%" align="center"><STRONG>JENIS PELAYANAN</STRONG></td>
		<td class="judul" width="10%" align="center"><STRONG>VOL</STRONG></td>
		<td class="judul" width="20%" align="center"><STRONG>SUBTOTAL</STRONG></td>
	</tr>
	
	<?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { 
  if($dataFolio[$i]["fol_jumlah"]){
            $total = $dataFolio[$i]["fol_jumlah"]*$dataFolio[$i]["fol_nominal"];
          }elseif(!$dataFolio[$i]["fol_jumlah"]){
            $total = $dataFolio[$i]["fol_nominal"];
          } ?>
<!--		<tr>
			<td align="right" class="isi"><?php echo ($i+1); ?></td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="left" class="isi"><?php echo $dataFolio[$i]["fol_nama"];?></td>
			<td align="right" class="isi">
      <?php if($dataFolio[$i]["fol_jumlah"]){
          echo currency_format($dataFolio[$i]["fol_jumlah"]);
          }else{
          echo "1";
          } ?></td>
			<td align="right" class="isi">
      <?php  echo currency_format($total);?></td>
      <?php $totalHarga+=$total; ?>
		</tr>
	<?php } ?>
	<?php if($n<10) { for($i=0;$i<(10-$n);$i++) { ?>
<!--		<tr>
			<td align="right" class="isi">&nbsp;</td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="left" class="isi">&nbsp;</td>
			<td align="right" class="isi">&nbsp;</td>
			<td align="right" class="isi">&nbsp;</td>
		</tr>
	<?php }} ?>
	<tr>
		<td class="judul" align="left"colspan=4><strong>TOTAL</strong></td>
		<td class="judul" align="right"><?php echo currency_format($totalHarga);?></td>
	</tr>
</table>

<BR>                                                                            
<BR>
<BR>
<BR>
<BR>
<BR>

<table width="100" border="0" cellpadding="1" cellspacing="1">
	<tr class="judul">
		<td width= "25%" align="center" style="font-size:12px"><strong>( NAMA KASIR )</strong></td>		
	</tr>	
		
</table>  -->



</body>
</html>
