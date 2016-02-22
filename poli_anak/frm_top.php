<?php
require_once("root.inc.php");
require_once($ROOT."library/auth.cls.php");
require_once($ROOT."library/textEncrypt.cls.php");
require_once($ROOT."library/inoLiveX.php");
require_once($ROOT."library/datamodel.cls.php");
     
$auth = new CAuth();
$enc = new textEncrypt();
$dtaccess = new DataAccess();
$userData = $auth->GetUserData();
     

$countHeader = 0;
$countMenu = 0;	
$menu[$countHeader]["head"] = '<img src="com/logo/front_office.png" width="64" height="64"><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pendaftaran';
$menu[$countHeader]["status"] = true;
$menu[$countHeader]["href"] = "frm_left.php?panel=registrasi";
	
	$menu[$countHeader]["sub"][$countMenu]["head"] = "Re-Registrasi";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

$countHeader++;
$countMenu = 0;	
$menu[$countHeader]["head"] = '<img src="com/logo/pemeriksaan.png" width="64" height="64"><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pemeriksaan';
$menu[$countHeader]["status"] = true;
$menu[$countHeader]["href"] = "frm_left.php?panel=dokter";
	/*
	$menu[$countHeader]["sub"][$countMenu]["head"] = "Pemeriksaan";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;
*/
	$menu[$countHeader]["sub"][$countMenu]["head"] = "Diagnostik";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Tindakan";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Rujukan";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

$countHeader++;
$countMenu = 0;	
$menu[$countHeader]["head"] = '<img src="com/logo/report.png" width="64" height="64"><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Laporan';
$menu[$countHeader]["status"] = true;
$menu[$countHeader]["href"] = "frm_left.php?panel=laporan";

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Report Pasien";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Report Pemeriksaan";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Report Diagnostik";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Report Tindakan";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Rekap Pasien";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Rekap Pemeriksaan";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Rekap Diagnostik";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Rekap Tindakan";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Surat Keterangan Sakit";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

	$menu[$countHeader]["sub"][$countMenu]["head"] = "Surat Rujukan";
	$menu[$countHeader]["sub"][$countMenu]["priv"] = "poli_anak";			
	$countMenu++;

$dataPriv = $auth->IsMenuAllowed($menu);

for($a=0,$b=$countHeader;$a<=$b;$a++) {
	    for($i=0,$n=count($menu[$a]["sub"]);$i<$n;$i++){
		   if($dataPriv[$menu[$a]["sub"][$i]["priv"]]===true){
			   $menu[$a]["status"] = true;
	       break;     
		   } 
	   }
   }
   
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>

<link href="<?php echo $APLICATION_ROOT;?>com/images/icon.png" rel="Shortcut Icon" >
<link href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css" rel="stylesheet" type="text/css">
<script language="javascript">
function Logout()
{
    if(confirm('Are You Sure to LogOut?')) window.parent.document.location.href='logout.php';
    else return false;
}
</script>


<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">

body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}

.asd {
height:25px;
background-color:rgb(0,153,0);
color:#ffffff;
text-align:center;
padding:0;
margin:0;
padding:0;

}

.asd a{
color:#ffffff;
margin:3px 0 0 0;

}

.asd img{
border-style:none;
width:16px;
height:16px;
margin:3px 2px 0 50px;
padding:0;

}
</style>


</HEAD>
<BODY>
<div class="asd">
<img src="com/images/bantuan.png" /><a >Bantuan</a>
<img src="com/images/logout.png" /><a href="" onClick="javascript: return Logout();">LogOut</a>
<img src="com/images/icon.png"/><a >Heal ExSys v.1.1 </a>
</div>

<div id="tblMenu" style="position:relative;display:block">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
		<td align="left">
			<table border="0" cellpadding="0" cellspacing="0" align="center">
			  <tr>
				<?php for($i=0,$n=$countHeader;$i<=$n;$i++){?>
					<?php if ($menu[$i]["status"] == true) { ?>
						<td align="center" style="cursor:pointer;" onClick="window.parent.leftFrame.document.location.href='<?php echo $menu[$i]["href"]?>';window.parent.resizeLeft();">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $menu[$i]["head"]?></td>
					<?php } ?>
				<?php }?>				
			  </tr>
			</table>
		</td>

	  </tr>
	</table>
</div>
<hr />
</BODY>
</HTML>
