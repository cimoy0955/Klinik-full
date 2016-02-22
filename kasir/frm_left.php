<?php
   require_once("root.inc.php");
   require_once($ROOT."library/auth.cls.php");
   require_once($ROOT."library/textEncrypt.cls.php");
   
   $auth = new CAuth();
   $enc = new textEncrypt();
   $userData = $auth->GetUserData();
   $dtaccess = new DataAccess();
   
   if($_GET["panel"]) $panel = $_GET["panel"];
   
     $namaPetunjuk[1] = "Alur";
     $namaPetunjuk[2] = "User Guide";
     $namaPetunjuk[3] = "Training Kit";

     $sql = "select *  
			       from global.global_petunjuk  a 
             order by tunjuk_ket, tunjuk_file ";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     for($i=0,$n=count($dataTable);$i<$n;$i++){
          $alur[$dataTable[$i]["tunjuk_ket"]]++;
          $id[$dataTable[$i]["tunjuk_ket"]][$alur[$dataTable[$i]["tunjuk_ket"]]] = $dataTable[$i]["tunjuk_id"];
          $nm[$dataTable[$i]["tunjuk_ket"]][$alur[$dataTable[$i]["tunjuk_ket"]]] = $dataTable[$i]["tunjuk_nama"];
          $file[$dataTable[$i]["tunjuk_ket"]][$alur[$dataTable[$i]["tunjuk_ket"]]] = $dataTable[$i]["tunjuk_file"];  
     }
     
	$countMenu = 0;
	
	switch($panel){   
    
		// --- menu konfigurasi ---
	/*	case "cp":
			$menu[$countMenu]["head"] = "Role";
			$menu[$countMenu]["priv"] = "setup_role";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/konfigurasi/role/role_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			
			$menu[$countMenu]["head"] = "Hak Akses";
			$menu[$countMenu]["priv"] = "setup_hakakses";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/konfigurasi/hakakses/hakakses_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			
			$menu[$countMenu]["head"] = "Ganti Password";
			$menu[$countMenu]["priv"] = "ganti_password";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/konfigurasi/ganti_password/ganti_password.php";
			$menu[$countMenu]["status"] = true;	
			
			break;
	*/		
		
		// --- menu loket ---
		case "loket":
			/*$menu[$countMenu]["head"] = "Registrasi";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/registrasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Registrasi Rawat Inap";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/registrasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Edit Jenis Pasien";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/jenis_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;

			$menu[$countMenu]["head"] = "Edit Pasien";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/pasien_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;

			$menu[$countMenu]["head"] = "Antrian";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/antrian.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/
	
			$menu[$countMenu]["head"] = "Kasir Swadana";
			$menu[$countMenu]["priv"] = "kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/kasir_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Kasir Non Swadana";
			$menu[$countMenu]["priv"] = "kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/kasir_view_non.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			/*
			$menu[$countMenu]["head"] = "Edit Jenis Pasien";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/jenis_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/
	
			break;
			
    

		case "cetak":
			$menu[$countMenu]["head"] = "Cetak Ulang Kwitansi";
			$menu[$countMenu]["priv"] = "kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/kasir_cetak_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Cetak Akhir Kwitansi";
			$menu[$countMenu]["priv"] = "kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/kasir_cetak_akhir_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Edit Kwitansi Pasien";
			$menu[$countMenu]["priv"] = "kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/kasir_cetak_akhir_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;

			break;
			
		case "report":
			$menu[$countMenu]["head"] = "Report Kasir";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_loket.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
               
			$menu[$countMenu]["head"] = "Report Kasir All Item";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_loket2.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Report Kasir per Kas";
			$menu[$countMenu]["priv"] = "report_kasir_per_kas";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_loket_per_kas.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
			$menu[$countMenu]["head"] = "Report Pendapatan per Layanan";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_pendapatan_perlayanan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Kasir per Jenis Kas";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_kasir_perkas.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Rekap Kasir";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_rekap_loket.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
     
			break;
		// --- menu help ---
		case "help":
			for($i=0,$no=1,$n=3;$i<$n;$i++,$no++) {
				$menu[$i]["head"] = $namaPetunjuk[$no];
				$menu[$i]["status"] = true;
				
				for($a=1,$co=0,$m=$alur[$no];$a<=$m;$a++,$co++){   
					$menu[$i]["sub"][$co]["item"] = $nm[$no][$a]; 
					$menu[$i]["sub"][$co]["priv"] = "help";
					$menu[$i]["sub"][$co]["href"] = "module/help/attachment.php?id=".$id[$no][$a]."";
					$menu[$i]["sub"][$co]["status"] = true; 
				}
			} 
			break;

	}
   
   $dataPriv = $auth->IsMenuAllowed($menu);
      
   for($i=0,$e=0,$n=count($menu);$i<$n;$i++){
      $menu[$i]["status"] = ($dataPriv[$menu[$i]["priv"]]) ? true:false;   
      for($j=0,$e=0,$m=count($menu[$i]["sub"]);$j<$m;$j++){
          if($dataPriv[$menu[$i]["sub"][$j]["priv"]]==true) {
               $menu[$i]["status"] = true;
               break;
          }
      }
   }
        
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>

<link href="<?php echo $APLICATION_ROOT;?>com/gambar/icon.png" rel="Shortcut Icon" >
<link href="<?php echo $APLICATION_ROOT;?>lib/css/expressa.css" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>lib/script/frameLeft.js"></script>
<script language=JavaScript>	
	function showme(jj)
	{
		var child = document.getElementById('child_'+jj);		

		hh=child.style.display;

		if (hh=="none") {
			next="block";
			nv="relative";			
		} else {
			next="none";
			nv="absolute";
		}
		child.style.display = next;
	}
</script>
<script language="javascript">
function Logout()
{
    if(confirm('Are You Sure to LogOut?')) window.parent.document.location.href='logout.php';
    else return false;
}
</script>

<?php include("com/acordion.php"); ?>
</head>
<body>

<!--<img src="com/gambar/logo.gif" width="100%">-->

<div class="arrowlistmenu">
<h3 class="menuheader expandable">Menu <?php echo $panel ;?></h3>
<ul class="categoryitems">
<li><?php for($i=0,$n=count($menu);$i<$n;$i++){?>
      <?php if($menu[$i]["status"]==true) { ?>
      <?php if(count($menu[$i]["sub"])<1) {?><a target="<?php if($panel=="connect") echo "_top"; else echo "mainFrame"?>" href="<?php echo $menu[$i]["href"]?>"><?php }?><font color="#333333" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $menu[$i]["head"];?></strong></font></a>
      <?php } ?> <?php } ?>
</li>
</ul>

<h3 class="menuheader expandable">Log Out</h3>
<ul class="categoryitems">
<li>
<a href="" onClick="javascript: return Logout();">LogOut</a>
</li>
</ul>

</div>

</body>
</html>
