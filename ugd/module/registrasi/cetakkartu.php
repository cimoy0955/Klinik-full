<?php 
	require_once("root.inc.php");
	require_once($ROOT."library/bitFunc.lib.php");
	require_once($ROOT."library/auth.cls.php");
	require_once($ROOT."library/textEncrypt.cls.php");
	require_once($ROOT."library/datamodel.cls.php");
	require_once($ROOT."library/inoBarcode.cls.php");
	require_once($APLICATION_ROOT."library/view.cls.php");
	
	$dtaccess = new DataAccess();
	$enc = new textEncrypt();
	$auth = new CAuth();

	// -- authentifikasi ---- //
	$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	
 	if(!$auth->IsAllowed("registrasi",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("registrasi",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

	$barcode = new InoBarcode();

	if($_GET["id"]) $_POST["cust_usr_id"] = $_GET["id"];
	
	$sql = "select a.* from global.global_customer_user a  where a.cust_usr_id = ".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_id"]);
	$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
	
	$fotoName = $APLICATION_ROOT."images/foto_pasien/".$dataPasien["cust_usr_foto"];

?>

<html>
<head>

<title>Cetak Kartu Pasien</title>

<style type="text/css">
body {
    font-family:      Arial, Verdana, Helvetica, sans-serif;
    margin: 0px;
}

#dv_nama {
	position:absolute;
	top:0px;
	left:50px;
	z-index:1;
	font-size: 14px;
	font-weight:bolder;
}


#dv_kode {
	position:absolute;
	top:35px;
	left:50px;
	z-index:1;
	font-size: 14px;
	font-weight:bolder;
}


#dv_alamat {
	position:absolute;
	top:50px;
	left:50px;
	z-index:1;
	font-size: 11px;
}

#dv_barcode {
	position:absolute;
	top:77px;
	left:20px;
	z-index:1;
}

#dv_foto {
	position:absolute;
	top:23px;
	left:230px;
	z-index:1;
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
<div id="dv_nama"><?php echo $dataPasien["cust_usr_nama"]." (".$dataPasien["cust_usr_jenis_kelamin"].")";?></div>
<div id="dv_kode"><?php echo $dataPasien["cust_usr_kode"];?></div>
<div id="dv_alamat"><?php echo nl2br(wordwrap($dataPasien["cust_usr_alamat"],25))."<BR>".$dataPasien["cust_usr_telp"];?></div>
<!--<div id="dv_foto"><img hspace="2" width="60" height="75" name="img_foto" id="img_foto" src="<?php echo $fotoName;?>"  border="1"></div>-->
<div id="dv_barcode"><?php echo $barcode->Render($dataPasien["cust_usr_kode"]); ?></div>


</body>
</html>
