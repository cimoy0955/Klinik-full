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
	$menu[$countHeader]["head"] = '<img src="com/logo/konfigurasi.png" width="64" height="64"><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Konfigurasi';
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=cp";
	
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Role";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_role";			
  		$countMenu++;
  	
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Hak Akses";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_hakakses";		
  		$countMenu++;
  		
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Ganti Password";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "ganti_password";		
  		$countMenu++;
  
  
  $countHeader++;
	$countMenu = 0;	
	$menu[$countHeader]["head"] = '<img src="com/logo/master.png" width="64" height="64"><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Master';
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=master";
	
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Biaya";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_role";			
  		$countMenu++;
  	
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Kamar";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_hakakses";		
  		$countMenu++;
  		
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Ganti Password";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "ganti_password";		
  		$countMenu++;		
  
  $countHeader++;
	$countMenu = 0;	
	$menu[$countHeader]["head"] = '<img src="com/logo/report.png" width="64" height="64"><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Report';
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=laporan";
	
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Biaya";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_role";			
  		$countMenu++;
  	
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Kamar";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_hakakses";		
  		$countMenu++;
  		
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Ganti Password";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "ganti_password";		
  		$countMenu++;			
  
  $countHeader++;
	$countMenu = 0;	
	$menu[$countHeader]["head"] = '<img src="com/logo/keuangan.png" width="64" height="64"><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Keuangan';
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=keuangan";		
	
    	$menu[$countHeader]["sub"][$countMenu]["head"] = "Biaya";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_role";			
  		$countMenu++;
  	
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Kamar";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_hakakses";		
  		$countMenu++;
  		
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Ganti Password";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "ganti_password";		
  		$countMenu++;		
  		
  
  $countHeader++;
	$countMenu = 0;	
	$menu[$countHeader]["head"] = '<img src="com/logo/keuangan.png" width="64" height="64"><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Accounting';
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=accounting";		
	
    	$menu[$countHeader]["sub"][$countMenu]["head"] = "Biaya";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_role";			
  		$countMenu++;
  	
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Kamar";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_hakakses";		
  		$countMenu++;
  		
  		$menu[$countHeader]["sub"][$countMenu]["head"] = "Ganti Password";
  		$menu[$countHeader]["sub"][$countMenu]["priv"] = "ganti_password";		
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
	
	
	$sql = "select * from global.global_app";
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
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
<img src="com/images/icon.png"/><a >Heal ExSys v.1.1 </a>&nbsp;
<img src="com/images/bn.gif" /><?php echo $userData["loginname"];?>&nbsp;<?php if(strtolower($userData["loginname"]) == "petex") {?>
<select class="input" name="cmbSystem" onKeyDown=" return tabOnEnter(this, event); " onChange="javascript: switchApp(this.value);">
<?php for($i=0;$i<count($dataTable);$i++){?>
 <option value="<?php echo $dataTable[$i]["app_id"];?>" onKeyDown="return tabOnEnter(this, event);" <?php echo ($dataTable[$i]["app_id"]=="13")?"selected":"";?>><?php echo $dataTable[$i]["app_nama"];?></option>
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
