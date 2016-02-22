<?php
   require_once("root.inc.php");
   require_once($ROOT."library/auth.cls.php");
   require_once($ROOT."library/textEncrypt.cls.php");
   
   $auth = new CAuth();
   $enc = new textEncrypt();
   $userData = $auth->GetUserData();
     $dtaccess = new DataAccess();
   
   if(!$_GET["panel"]) $panel = "pemeriksaan";
   else $panel = $_GET["panel"];
   
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
    
		
		// --- menu point of sales ---
		case "pemeriksaan":
			$menu[$countMenu]["head"] = "Pemeriksaan";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/pemeriksaan/pemeriksaan_edit.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++; 

			$menu[$countMenu]["head"] = "Edit Pemeriksaan";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/data/pemeriksaan_lihat.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++; 
/*			
			$menu[$countMenu]["head"] = "Pemeriksaan Rawat Inap";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/pemeriksaan_rawatinap/pemeriksaan_edit.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	*/	
			break;
			
		
			
		// --- menu cetak ---
		case "cetak": 
			$menu[$countMenu]["head"] = "Cetak Kartu Pasien";
			$menu[$countMenu]["priv"] = "cetak_kartu_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/cetak_kartu.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Cetak Status Pasien";
			$menu[$countMenu]["priv"] = "cetak_status_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/cetak_status.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			
			break;
			
		case "report":
			$menu[$countMenu]["head"] = "Laporan Keuangan";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/rekap_bulanan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Laporan Bonus Dokter";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/bonus_dokter.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Laporan Pasien";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			/*
			$menu[$countMenu]["head"] = "Rekap Pasien";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/rekap_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			*/
			break;
		
		// --- menu setup ---
		case "setup":
		/*	$menu[$countMenu]["head"] = "Jenis";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/jenis/jenis_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Merk";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/merk/merk_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Ukuran";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/ukuran/ukuran_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Warna";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/warna/warna_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			*/
			
			$menu[$countMenu]["head"] = "Kategori Pemeriksaan";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/kategori/kategori_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
      $menu[$countMenu]["head"] = "Kategori Bonus Dokter";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/bonus/bonus_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Master Pemeriksaan";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/kegiatan/kegiatan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			/*
			$menu[$countMenu]["head"] = "Master Dokter";
			$menu[$countMenu]["priv"] = "laboratorium";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/dokter/dokter_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			*/
			// $menu[$countMenu]["head"] = "Master Paket";
			// $menu[$countMenu]["priv"] = "laboratorium";
			// $menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/paket/paket_view.php";
			// $menu[$countMenu]["status"] = true;	
			// $countMenu++;
			/*
			$menu[$countMenu]["head"] = "INA DRG";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/ina/ina_view.php?jenis=1";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Setup Biaya";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/biaya/biaya_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Obat";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/obat/item_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Dosis";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/dosis/dosis_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Visus";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/visus/visus_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Biaya Tambahan";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/tagihan/tagihan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Setup Kelas";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/kamar/kelas_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
		
			$menu[$countMenu]["head"] = "Setup Kamar";
			$menu[$countMenu]["priv"] = "setup_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/kamar/kamar_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
		
			$menu[$countMenu]["head"] = "Setup Bed";
			$menu[$countMenu]["priv"] = "setup_kamar";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/kamar/bed_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			*/
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
					$menu[$i]["sub"][$co]["status"] = false; 
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

<link href="<?php echo $APLICATION_ROOT;?>com/images/icon.png" rel="Shortcut Icon" >
<link href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/frameLeft.js"></script>

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

<img src="com/images/logo.gif" width="100%">

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