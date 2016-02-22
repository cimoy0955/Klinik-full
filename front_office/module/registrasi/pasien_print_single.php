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

	$sql = "select a.cust_usr_id, a.cust_usr_nama, a.cust_usr_kode , a.cust_usr_alamat,
          b.reg_tanggal, b.reg_jenis_pasien from global.global_customer_user a
	      join klinik.klinik_registrasi b on b.id_cust_usr = a.cust_usr_id 
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
	if (!isset($width))   $width    = "350";
	if (!isset($height))  $height   = "64";
	if (!isset($xres))    $xres     = "2";
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

		$obj = new C39Object(200, 120, $style, $barcode);
		
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
	//window.print();
});
</script>
<!-- akhir perintah -->
<style type="text/css">
	#lbl_tanggal {
		position: fixed;
		margin-top: 100px;
		margin-left: 47px;
	}
	#isi_tanggal{
		position: fixed;
		margin-left: 40px;
	}
	#obj_barcode {
		position: fixed;
		margin-top: 40px;
		margin-left: 0px;
	}
	#lbl_kode {
		position: fixed;
		margin-top: 10px;
		margin-left: 47px;
	}
	#isi_kode {
		position: fixed;
		margin-left: 18px;
	}
	#lbl_nama {
		position: fixed;
		margin-top: 25px;
		margin-left:47px;
	}
	#isi_nama {
		position: fixed;
		margin-left: 49px
	}
</style>
</head>

<body>
	<span id="lbl_tanggal"><label>Tanggal Masuk<span id="isi_tanggal">:&nbsp;<?php echo format_date($dataItem["reg_tanggal"]);?></span></label></span>
	<span id="obj_barcode">
	<?php 
	if ($obj) {
		if ($check_error) {
			echo "<font color='#FF0000'>".($obj->GetError())."</font>";
		} else { ?>
			<img src="<?php echo $ROOT;?>library/barcode/image.php?code=<?=$barcode?>&style=<?=$style?>&type=<?=$type?>&width=<?=$width?>&height=<?=$height?>&xres=<?=$xres?>&font=<?=$font?>">
	<?php 	}
	}
	?>
	</span>
	<span id="lbl_kode"><label>Kode/Jenis Pasien<span id="isi_kode">:&nbsp;<?php echo $dataItem["cust_usr_kode"];?> / 
	<?php echo $bayarPasien2[$dataItem["reg_jenis_pasien"]]; ?></span></label></span>
	<span id="lbl_nama"><label>Nama Pasien<span id="isi_nama">:&nbsp;<?php echo $dataItem["cust_usr_nama"];?></span></label></span>
				
</body>
</html>
