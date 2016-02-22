<?php 
	require_once("root.inc.php");
	require_once($ROOT."library/bitFunc.lib.php");
	require_once($ROOT."library/auth.cls.php");
	require_once($ROOT."library/textEncrypt.cls.php");
	require_once($ROOT."library/datamodel.cls.php");
	require_once($ROOT."library/currFunc.lib.php");
	require_once($ROOT."library/inoLiveX.php");
	require_once($APLICATION_ROOT."library/view.cls.php");
	
	$dtaccess = new DataAccess();
	$enc = new textEncrypt();
	$auth = new CAuth();

	// -- authentifikasi ---- //
	$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$plx = new InoLiveX("");
	
	if(!$auth->IsAllowed("registrasi")){
		die("access_denied");
		exit(1);
	}

	if($_POST["cust_usr_id"])  $pasId = & $_POST["cust_usr_id"];
 
    if ($_GET["idpas"]) {
		$pasId = $_GET["idpas"];

	$sql = "select cust_usr_id, cust_usr_nama, cust_usr_kode , cust_usr_alamat from global.global_customer_user a
				where cust_usr_id = ".QuoteValue(DPE_CHAR,$pasId);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataItem = $dtaccess->Fetch($rs);
	}

// --- bagian barcode --- //
	define (__TRACE_ENABLED__, false);
	define (__DEBUG_ENABLED__, false);
									   
	require($ROOT."library/barcode/barcode.php");		   
	require($ROOT."library/barcode/i25object.php");
	require($ROOT."library/barcode/c39object.php");
	require($ROOT."library/barcode/c128aobject.php");
	require($ROOT."library/barcode/c128bobject.php");
	require($ROOT."library/barcode/c128cobject.php"); 
							  
	/* Default value */
	if (!isset($output))  $output   = "png";
	if (isset($_GET["idpas"])) $barcode  = $dataItem["cust_usr_kode"];
	if (!isset($type))    $type     = "C39";
	if (!isset($width))   $width    = "200";
	if (!isset($height))  $height   = "64";
	if (!isset($xres))    $xres     = "1";
	if (!isset($font))    $font     = "1";

	$border = "off";
	$drawtext = "on";
	$stretchtext = "on";
	/*********************************/ 
					
	if (isset($barcode) && strlen($barcode)>0) {    
		$style  = BCS_ALIGN_CENTER;					       
		$style |= ($output  == "png" ) ? BCS_IMAGE_PNG  : 0; 
		$style |= ($output  == "jpeg") ? BCS_IMAGE_JPEG : 0; 
		$style |= ($border  == "on"  ) ? BCS_BORDER 	  : 0; 
		$style |= ($drawtext== "on"  ) ? BCS_DRAW_TEXT  : 0; 
		$style |= ($stretchtext== "on" ) ? BCS_STRETCH_TEXT  : 0; 
		$style |= ($negative== "on"  ) ? BCS_REVERSE_COLOR  : 0; 

		$obj = new C39Object(250, 120, $style, $barcode);
		
		if ($obj) {
			if ($obj->DrawObject($xres)) {
				$check_error = 0;
			} else {
				$check_error = 1;
			}
		}
	} 
// --- End bagian barcode --- //

?>

<html>
<head>
<title>Cetak Item Barcode</title>
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inventori_prn.css">

<!-- perintah untuk langsung menampilkan kotak dialog print saat load halaman -->
<?php echo $view->InitUpload(); ?>
<script>

$(document).ready( function() {
	window.print();
});
</script>
<!-- akhir perintah -->

</head>

<body>
<table width="150" height="100"  celpadding="0" cellspacing="0" noborder border ="0">
	<tr>
		<td>
			<table width="100%" border="0">
				<tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td> 
          <td>&nbsp;</td>
          <td>&nbsp;</td> 
					<td valign="top" align="center">&nbsp;
						<?php 
							if ($obj) {
								if ($check_error) {
									echo "<font color='#FF0000'>".($obj->GetError())."</font>";
								} else { ?>
									<img src="<?php echo $ROOT;?>library/barcode/image.php?code=<?=$barcode?>&style=<?=$style?>&type=<?=$type?>&width=<?=$width?>&height=<?=$height?>&xres=<?=$xres?>&font=<?=$font?>">
								<?php }
							}
						?>
					<td> 
				</tr>
				<tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td> 
          <td>&nbsp;</td>
          <td>&nbsp;</td>
					<td align="center" width="75%"><font size="4"><label>&nbsp;<?php echo $dataItem["cust_usr_kode"];?></label></font></td>
				</tr>					
				<tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td> 
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
					<td align="center"><font size="3"><label>&nbsp;<?php echo $dataItem["cust_usr_nama"];?></label></font></td>
				</tr>
			</table>
		</td>
</tr>
</table>
</body>
</html>
