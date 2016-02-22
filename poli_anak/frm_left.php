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

     $sql = "select * from global.global_petunjuk  a 
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
  
	// --- menu pendaftaran ---
		case "registrasi":
		  /*
			$menu[$countMenu]["head"] = "Re-Registrasi";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/registrasi.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			*/
			$menu[$countMenu]["head"] = "Antrian";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/antrian.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			break;
	 // --- menu report ---
		case "laporan":
/*
			$menu[$countMenu]["head"] = "Report Global";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_global.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Global Detail";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_global_detail.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
*/
			$menu[$countMenu]["head"] = "Report Pasien";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			/*$menu[$countMenu]["head"] = "Report Refraksi";
			$menu[$countMenu]["priv"] = "report_refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/report_refraksi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/
			/*
			$menu[$countMenu]["head"] = "Report Pemeriksaan";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/report_pemeriksaan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Tindakan";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_tindakan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Jadwal Operasi";
			$menu[$countMenu]["priv"] = "report_jadwal_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_jadwal_operasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Operasi";
			$menu[$countMenu]["priv"] = "report_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_operasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Evaluasi Operasi";
			$menu[$countMenu]["priv"] = "report_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_operasi_evaluasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Operasi Hari Ini";
			$menu[$countMenu]["priv"] = "report_op_hari";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_op_hari.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap Pasien";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/rekap_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	*/
//			$menu[$countMenu]["head"] = "Rekap Refraksi";
//			$menu[$countMenu]["priv"] = "report_refraksi";
//			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/rekap_refraksi.php";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++;
	
//			$menu[$countMenu]["head"] = "Report Visus";
//			$menu[$countMenu]["priv"] = "report_refraksi";
//			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/report_visus.php";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++;
	/*
			$menu[$countMenu]["head"] = "Rekap Pemeriksaan";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/report_perawatan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap Diagnostik";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/diagnostik/report_diagnostik.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Point Pegawai";
			$menu[$countMenu]["priv"] = "report_point_pegawai";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_point.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			*/
//			$menu[$countMenu]["head"] = "Report Absensi Pegawai";
//			$menu[$countMenu]["priv"] = "report_absensi";
//			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/absensi/report_absensi.php";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++; 

//      $menu[$countMenu]["head"] = "Report Absensi Pegawai Harian";
//			$menu[$countMenu]["priv"] = "report_absensi";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++; 
	/*
			$menu[$countMenu]["head"] = "Report Kasir";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_loket.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
               
               
//      $menu[$countMenu]["head"] = "Report Kasir per Kas";
//			$menu[$countMenu]["priv"] = "report_kasir_per_kas";
//			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_loket_per_kas.php";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++; 
			
			
      $menu[$countMenu]["head"] = "Report Biaya Klaim";
			$menu[$countMenu]["priv"] = "report_klaim";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_klaim.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
			$menu[$countMenu]["head"] = "Klaim per Kas";
			$menu[$countMenu]["priv"] = "report_klaim_per_kas";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_klaim_per_kas.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;   
			
      $menu[$countMenu]["head"] = "Klaim JamKesMas";
			$menu[$countMenu]["priv"] = "report_jamkesmas";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/report_jamkesmas.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
      $menu[$countMenu]["head"] = "Surat Ket Sakit";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_sakit.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;  
			*/
      $menu[$countMenu]["head"] = "Surat Rujukan";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_rujukan.php";
			$menu[$countMenu]["status"] = true;  
			$countMenu++; 
	/*		 
      $menu[$countMenu]["head"] = "Report 10 Penyakit Diutamakan";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_penyakit_diutamakan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;   
      
      $countMenu++;  
      $menu[$countMenu]["head"] = "Report 10 Penyakit Terbanyak";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_penyakit_terbanyak.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;     
	
*/
			
//      $menu[$countMenu]["head"] = "Surat Kesehatan Mata";
//			$menu[$countMenu]["priv"] = "report_surat_mata";
//			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_mata.php";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++; 
		/*	
			$menu[$countMenu]["head"] = "Report Pasien Dinas Luar";
			$menu[$countMenu]["priv"] = "dinas_luar";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_pasien_dinasluar.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
      */
			break;
			
		
			case "dokter":
			/*
			$menu[$countMenu]["head"] = "Refraksi";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/refraksi.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
		*/
			$menu[$countMenu]["head"] = "Pemeriksaan";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/perawatan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Tindakan";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/tindakan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Rujukan";
			$menu[$countMenu]["priv"] = "poli_anak";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/rujukan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
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
