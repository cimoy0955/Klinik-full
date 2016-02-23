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

function switchApp(eval) {
     if(eval=='10'){
          window.parent.document.location.href='../klinik/index.php';
	}else if(eval=='11'){
	   window.parent.document.location.href='../optik/index.php';
	}else if(eval=='12'){
	   window.parent.document.location.href='../logistik/index.php';
	}else if(eval=='13'){
	   window.parent.document.location.href='../admin/index.php';
	}else if(eval=='14'){
	   window.parent.document.location.href='../management/index.php';
	}else if(eval=='15'){
	   window.parent.document.location.href='../accounting/index.php';
	}else if(eval=='16'){
	   window.parent.document.location.href='../rawat_inap/index.php';
	}else if(eval=='17'){
	   window.parent.document.location.href='../laboratorium/index.php';
	}else if(eval=='18'){
	   window.parent.document.location.href='../apotik/index.php';
	}else if(eval=='19'){
	   window.parent.document.location.href='../ugd/index.php';
	}else if(eval=='20'){
	   window.parent.document.location.href='../apotik_swadaya/index.php';
	}else if(eval=='21'){
	   window.parent.document.location.href='../refraksi/index.php';   
	}else if(eval=='22'){
	   window.parent.document.location.href='../diagnostik/index.php'; 
	}else if(eval=='23'){
	   window.parent.document.location.href='../front_office/index.php';
	}else if(eval=='24'){
	   window.parent.document.location.href='../kasir/index.php';  
	}else if(eval=='25'){
	   window.parent.document.location.href='../dinas_luar/index.php';  
	}else if(eval=='27'){
	 window.parent.document.location.href='../poli_anak/index.php';
	}
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
<img src="com/images/bn.gif" /><?php echo $userData["loginname"];?>&nbsp;<?php if(strtolower($userData["loginname"]) == "petex") {?>
<select class="input" name="cmbSystem" onKeyDown=" return tabOnEnter(this, event); " onChange="javascript: switchApp(this.value);">
<?php for($i=0;$i<count($dataTable);$i++){?>
 <option value="<?php echo $dataTable[$i]["app_id"];?>" onKeyDown="return tabOnEnter(this, event);" <?php echo ($dataTable[$i]["app_id"]=="27")?"selected":"";?>><?php echo $dataTable[$i]["app_nama"];?></option>
<?php }?>
</select>
<?php }?>
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
