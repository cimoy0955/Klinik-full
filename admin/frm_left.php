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
		case "cp":
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
			
		case "laporan":

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

			$menu[$countMenu]["head"] = "Report Pasien";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			/*$menu[$countMenu]["head"] = "Report Refraksi";
			$menu[$countMenu]["priv"] = "report_refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/report_refraksi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/
			
			$menu[$countMenu]["head"] = "Report Pemeriksaan";
			$menu[$countMenu]["priv"] = "report_pemeriksaan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/report_pemeriksaan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Tindakan";
			$menu[$countMenu]["priv"] = "report_tindakan";
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
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/rekap_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
				
			$menu[$countMenu]["head"] = "Rekap Pasien per Kota";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/rekap_pasien_perkota.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
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
	
			$menu[$countMenu]["head"] = "Rekap Pemeriksaan";
			$menu[$countMenu]["priv"] = "report_perawatan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/report_perawatan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap Diagnostik";
			$menu[$countMenu]["priv"] = "report_diagnostik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/diagnostik/report_diagnostik.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
      $menu[$countMenu]["head"] = "Point Pegawai";
			$menu[$countMenu]["priv"] = "report_point_pegawai";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_point.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
//			$menu[$countMenu]["head"] = "Report Absensi Pegawai";
//			$menu[$countMenu]["priv"] = "report_absensi";
//			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/absensi/report_absensi.php";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++; 

//      $menu[$countMenu]["head"] = "Report Absensi Pegawai Harian";
//			$menu[$countMenu]["priv"] = "report_absensi";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++; 
	
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
			$menu[$countMenu]["priv"] = "report_surat_sakit";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_sakit.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;  
			
      $menu[$countMenu]["head"] = "Surat Rujukan";
			$menu[$countMenu]["priv"] = "report_surat_rujukan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_rujukan.php";
			$menu[$countMenu]["status"] = true;  
            
			$countMenu++;  
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
			
		
			case "keuangan":
			$menu[$countMenu]["head"] = "Report Kasir";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_loket.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Kasir per Kas";
			$menu[$countMenu]["priv"] = "report_kasir_per_kas";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_loket_per_kas.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
			
			$menu[$countMenu]["head"] = "Edit Kwitansi";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/edit_kwitansi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			break;
			
		case "accounting":
			$menu[$countMenu]["head"] = "Registrasi";
			$menu[$countMenu]["priv"] = "report_registrasi";  //sementara
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/acc/registrasi/registrasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
	
			$menu[$countMenu]["head"] = "Pembayaran";
			$menu[$countMenu]["priv"] = "report_registrasi";  //sementara
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/acc/pembayaran/pembayaran_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			break;  
		
		// --- menu setup ---
		case "master":
			$menu[$countMenu]["head"] = "Pegawai";
			$menu[$countMenu]["priv"] = "setup_pegawai";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/pegawai/pegawai_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Jenis Pasien";
			$menu[$countMenu]["priv"] = "setup_jenis_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/jenis_pasien/jenis_pasien_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			/*
			$menu[$countMenu]["head"] = "Jenis Biaya";
			$menu[$countMenu]["priv"] = "setup_biaya_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/jenis_biaya/jenis_biaya_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			*/
			$menu[$countMenu]["head"] = "Jenis Biaya Pemeriksaan";
			$menu[$countMenu]["priv"] = "setup_biaya_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/jenis_biaya_pemeriksaan/jenis_biaya_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Setup Biaya";
			$menu[$countMenu]["priv"] = "setup_biaya";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/biaya/biaya_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Jenis Operasi";
			$menu[$countMenu]["priv"] = "setup_jenis_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/jenis_operasi/jenis_operasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Paket Operasi";
			$menu[$countMenu]["priv"] = "setup_paket_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/paket_operasi/paket_operasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Metode Operasi";
			$menu[$countMenu]["priv"] = "setup_jenis_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/metode_operasi/metode_operasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "INA DRG";
			$menu[$countMenu]["priv"] = "setup_ina";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/ina/ina_view.php?jenis=1";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "ICD 10";
			$menu[$countMenu]["priv"] = "setup_icd";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/icd10/icd_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "ICD 9";
			$menu[$countMenu]["priv"] = "setup_icd";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/icd9/icd_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Setup Kelas";
			$menu[$countMenu]["priv"] = "setup_kamar";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/kamar/kelas_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
		
			$menu[$countMenu]["head"] = "Setup Kamar";
			$menu[$countMenu]["priv"] = "setup_kamar";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/kamar/kamar_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;

			
			$menu[$countMenu]["head"] = "Paket Biaya Klaims";
			$menu[$countMenu]["priv"] = "setup_biaya_klaim";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/biaya_klaim/biaya_klaim_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
		
			$menu[$countMenu]["head"] = "Paket Biaya Pasien";
			$menu[$countMenu]["priv"] = "setup_biaya_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/biaya_pasien/biaya_pasien_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Setup 10 Penyakit";
			$menu[$countMenu]["priv"] = "setup_ina";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/penyakit/penyakit_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Tindakan Rawat Inap";
			$menu[$countMenu]["priv"] = "setup_tindakan_rawat_inap";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/tindakan_rawat_inap/tindakan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			/*
			$menu[$countMenu]["head"] = "Dokter";
			$menu[$countMenu]["priv"] = "setup_dokter";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/dokter/dokter_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			*/
			/*
			$menu[$countMenu]["head"] = "Obat";
			$menu[$countMenu]["priv"] = "item";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/obat/item_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Dosis";
			$menu[$countMenu]["priv"] = "setup_dosis";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/dosis/dosis_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			*/
//			$menu[$countMenu]["head"] = "Visus";
//			$menu[$countMenu]["priv"] = "setup_visus";
//			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/visus/visus_view.php";
//			$menu[$countMenu]["status"] = true;	
//			$countMenu++;
			
			$menu[$countMenu]["head"] = "Biaya Tambahan";
			$menu[$countMenu]["priv"] = "setup_tagihan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/tagihan/tagihan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			
			
			$menu[$countMenu]["head"] = "Setup Level ICU";
			$menu[$countMenu]["priv"] = "setup_kamar";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/icu/icu_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Jenis Rujukan";
			$menu[$countMenu]["priv"] = "setup_biaya";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/rujukan/rujukan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Rujukan Rumah Sakit";
			$menu[$countMenu]["priv"] = "setup_biaya";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/rujukan/rujukan_rs_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Rujukan Dokter";
			$menu[$countMenu]["priv"] = "setup_biaya";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/rujukan/rujukan_dokter_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
		
			/*$menu[$countMenu]["head"] = "Import Diagnosa ICD";
			$menu[$countMenu]["priv"] = "setup_tagihan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/import_data/import_diag_icd.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Import Prosedur";
			$menu[$countMenu]["priv"] = "setup_tagihan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/import_data/import_prosedur.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Import Biaya Baru";
			$menu[$countMenu]["priv"] = "setup_tagihan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/import_data/import_biayabaru.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;*/
			
		
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
