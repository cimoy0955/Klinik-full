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
     
/*
	$countHeader = 0;
	$countMenu = 0;	
	$menu[$countHeader]["head"] = "KONFIGURASI";
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=cp";
	*/
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
	$menu[$countHeader]["head"] = "MASTER";
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=setup";
	
      $menu[$countHeader]["sub"][$countMenu]["head"] = "Pegwai";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_pegawai";     	
     	$countMenu++;
	
      $menu[$countHeader]["sub"][$countMenu]["head"] = "Jenis Pasien";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_jenis_pasien";     	
     	$countMenu++;

      $menu[$countHeader]["sub"][$countMenu]["head"] = "Paket Operasi";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_paket_operasi";     	
     	$countMenu++;

      $menu[$countHeader]["sub"][$countMenu]["head"] = "Jenis Operasi";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_jenis_operasi";     	
     	$countMenu++;
	
      $menu[$countHeader]["sub"][$countMenu]["head"] = "ICD 10";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_icd";     	
     	$countMenu++;
	
      $menu[$countHeader]["sub"][$countMenu]["head"] = "INA DRG";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_ina";     	
     	$countMenu++;
	
      $menu[$countHeader]["sub"][$countMenu]["head"] = "Biaya";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_biaya";     	
     	$countMenu++;
	
      $menu[$countHeader]["sub"][$countMenu]["head"] = "Obat";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "item";     	
     	$countMenu++;

      $menu[$countHeader]["sub"][$countMenu]["head"] = "Dosis";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_dosis";     	
     	$countMenu++;

      $menu[$countHeader]["sub"][$countMenu]["head"] = "Visus";
     	$menu[$countHeader]["sub"][$countMenu]["priv"] = "setup_visus";     	
     	$countMenu++;

	/*	
	$countHeader++; 
	$countMenu = 0;	
	$menu[$countHeader]["head"] = "LOKET";
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=loket";
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Registrasi";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "registrasi";
		$countMenu++;
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Edit Jenis Pasien";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "edit_jenis_pasien";
		$countMenu++;
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Kasir";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "kasir";
		$countMenu++;
		*/

	$countHeader++; 
	$countMenu = 0;	
	$menu[$countHeader]["head"] = "Pemeriksaan";
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=pemeriksaan";
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Refraksi";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "refraksi";
		$countMenu++;
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Perawatan";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "perawatan";
		$countMenu++;
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Diagnostik";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "diagnostik";
		$countMenu++;
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Tindakan";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "tindakan";
		$countMenu++;
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Premedikasi";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "premedikasi";
		$countMenu++;

		

	$countHeader++; 
	$countMenu = 0;	
	$menu[$countHeader]["head"] = "Report";
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=report";
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Registrasi";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "report_registrasi";
		$countMenu++;
          
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Refraksi";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "report_refraksi";
		$countMenu++;
          
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Pemeriksaan";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "report_pemeriksaan";
		$countMenu++;
          
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Tindakan";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "report_tindakan";
		$countMenu++;
          
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Operasi";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "report_operasi";
		$countMenu++;

		$menu[$countHeader]["sub"][$countMenu]["head"] = "Refraksi";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "report_refraksi";
		$countMenu++;

		$menu[$countHeader]["sub"][$countMenu]["head"] = "Perawatan";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "report_perawatan";
		$countMenu++;

		$menu[$countHeader]["sub"][$countMenu]["head"] = "Diagnostik";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "report_diagnostik";
		$countMenu++;

		$menu[$countHeader]["sub"][$countMenu]["head"] = "Kasir";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "report_kasir";
		$countMenu++; 
/*
	$countHeader++; 
	$countMenu = 0;	
	$menu[$countHeader]["head"] = "CETAK";
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=cetak";
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Kartu Identitas Pasien";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "cetak_kartu_pasien";
		$countMenu++;
	
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Surat Ket Sakit";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "surat_ket_sakit";
		$countMenu++;
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "Surat Rujukan";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "surat_rujukan";
		$countMenu++;
	
		$menu[$countHeader]["sub"][$countMenu]["head"] = "S.Ket Kesehatan Mata";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "surat_ket_kesehatan_mata";
		$countMenu++;
          /*
		$menu[$countHeader]["sub"][$countMenu]["head"] = "S.Permintaan Operasi";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "surat_ket_sakit";
		$countMenu++;
	
	/*$countHeader++; 
	$countMenu = 0;	
	$menu[$countHeader]["head"] = "HELP";
	$menu[$countHeader]["status"] = false;
	$menu[$countHeader]["href"] = "frm_left.php?panel=help";
     
          $menu[$countHeader]["sub"][$countMenu]["head"] = "iPanel";
		$menu[$countHeader]["sub"][$countMenu]["priv"] = "help";			
		$countMenu++;*/
					      
		
     
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

<link href="<?php echo $APLICATION_ROOT;?>images/inosoft-icon.ico" rel="Shortcut Icon" >
<link href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css" rel="stylesheet" type="text/css">
<script language="javascript">
function Logout()
{
    if(confirm('Are You Sure to LogOut?')) window.parent.document.location.href='logout.php';
    else return false;
}
</script>


<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}

-->
</style>


</HEAD>

<BODY>
<div id="tblHead" style="position:relative;display:block">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
		<td align="left"><img src="images/logocaremax.jpg" alt="" vspace=0 hspace=0></td>
<td width="56%" valign="top" nowrap align="right"><strong><?php echo $dataUser["usr_loginname"];?>  </strong><a href="../userguide/index.html" target="_blank">Bantuan  | <!--<a href="redirect.php" target="_blank">Hubungi Kami |-->  <a href="" onClick="javascript: return Logout();">LogOut</a></td>
<td align="right"><img src="images/caremax_left.jpg" alt="" vspace=0 hspace=0></td>
	  </tr>
	</table>
</div>

<div id="tblMenu" style="position:relative;display:block">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="menutop">
	  <tr>
		<td align="left">
			<table border="0" cellpadding="0" cellspacing="0" align="left">
			  <tr class="menutop">				
				<?php for($i=0,$n=$countHeader;$i<=$n;$i++){?>
					<?php if ($menu[$i]["status"] == true) { ?>
						<td onClick="window.parent.leftFrame.document.location.href='<?php echo $menu[$i]["href"]?>';window.parent.resizeLeft();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $menu[$i]["head"]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
					<? } ?>
				<?php }?>				
				<td onClick="javascript: return Logout();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LOGOUT&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			  </tr>
			</table>
		</td>
		<td align="right" width="3%"><img OnClick="window.parent.collapseTop();window.parent.changeTopImage();" src="images/bd_uppage.png" name="_top_img_" id="_top_img_" width="13" height="20" hspace="0" vspace="0" border="0" align="top" title="up" style="cursor:pointer"></td>
	  </tr>
	</table>
<div>
</BODY>
</HTML>


