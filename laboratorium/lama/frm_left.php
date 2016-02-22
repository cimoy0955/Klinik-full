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
			
		
		// --- menu point of sales ---
		case "pemeriksaan":
			$menu[$countMenu]["head"] = "Pemeriksaan";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/pemeriksaan/pemeriksaan_edit.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++; 
/*
			$menu[$countMenu]["head"] = "Penjualan";
			$menu[$countMenu]["priv"] = "optik_pos";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/pos/trans_jual_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
		
			$menu[$countMenu]["head"] = "Stok Opname";
			$menu[$countMenu]["priv"] = "optik_pos";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/pos/opname_edit.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	*/	
			break;
			
		// --- menu loket ---
		case "loket":
			$menu[$countMenu]["head"] = "Registrasi";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/registrasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Edit Jenis Pasien";
			$menu[$countMenu]["priv"] = "edit_jenis_pasien";
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
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Kasir";
			$menu[$countMenu]["priv"] = "kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/kasir_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			
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
			
			$menu[$countMenu]["head"] = "Surat Ket Sakit";
			$menu[$countMenu]["priv"] = "surat_ket_sakit";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/ket_sakit_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
	
			$menu[$countMenu]["head"] = "Surat Rujukan";
			$menu[$countMenu]["priv"] = "surat_rujukan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/rujukan_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "S.Ket Kesehatan Mata";
			$menu[$countMenu]["priv"] = "surat_ket_kesehatan_mata";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/mata_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
			break;
			
		case "report":
			$menu[$countMenu]["head"] = "Laporan Keuangan";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/rekap_bulanan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Laporan Bonus Dokter";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/bonus_dokter.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			/*
			$menu[$countMenu]["head"] = "Laporan Penjualan";
			$menu[$countMenu]["priv"] = "report_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_penjualan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Laporan Stok Opname";
			$menu[$countMenu]["priv"] = "report_optik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/report/report_opname.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
      
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
			
			$menu[$countMenu]["head"] = "Report Jadwal Operasi";
			$menu[$countMenu]["priv"] = "report_jadwal_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_jadwal_operasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Operasi";
			$menu[$countMenu]["priv"] = "report_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_operasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Evaluasi Operasi";
			$menu[$countMenu]["priv"] = "report_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_operasi_evaluasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Operasi Hari Ini";
			$menu[$countMenu]["priv"] = "report_op_hari";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_op_hari.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap Pasien";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/rekap_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap Refraksi";
			$menu[$countMenu]["priv"] = "report_refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/rekap_refraksi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Report Visus";
			$menu[$countMenu]["priv"] = "report_refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/report_visus.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
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
			
      $menu[$countMenu]["head"] = "Report Point Pegawai";
			$menu[$countMenu]["priv"] = "report_point_pegawai";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_point.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Absensi Pegawai";
			$menu[$countMenu]["priv"] = "report_absensi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/absensi/report_absensi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 

      $menu[$countMenu]["head"] = "Report Absensi Pegawai Harian";
			$menu[$countMenu]["priv"] = "report_absensi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/absensi/report_absensi_perhari.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
	
			$menu[$countMenu]["head"] = "Report Kasir";
			$menu[$countMenu]["priv"] = "report_kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_loket.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
               
      $menu[$countMenu]["head"] = "Report Kasir per Kas";
			$menu[$countMenu]["priv"] = "report_kasir_per_kas";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_loket_per_kas.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
			$menu[$countMenu]["head"] = "Report Biaya Klaim";
			$menu[$countMenu]["priv"] = "report_klaim";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_klaim.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
			$menu[$countMenu]["head"] = "Report Klaim per Kas";
			$menu[$countMenu]["priv"] = "report_klaim_per_kas";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_klaim_per_kas.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;   
			
      $menu[$countMenu]["head"] = "Klaim JamKesMas Pusat";
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
			
      $menu[$countMenu]["head"] = "Surat Kesehatan Mata";
			$menu[$countMenu]["priv"] = "report_surat_mata";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_mata.php";
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
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/bonus/bonus_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Master Pemeriksaan";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/kegiatan/kegiatan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Master Dokter";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/dokter/dokter_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
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

<link href="<?php echo $APLICATION_ROOT;?>images/inosoft-icon.ico" rel="Shortcut Icon" >
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
</head>

<body class="menubody">
<?php for($i=0,$n=count($menu);$i<$n;$i++){?>

<!-- parent menu -->
<div id="<?php echo "parent_".$i?>" style="position:relative;visibility:visible;display:block;">
<table cellpadding=5 cellspacing=0 width=100%>
	<?php if($menu[$i]["status"]==true) { ?>
		<tr class="menuleft" <?php if(count($menu[$i]["sub"])>0) {?>OnClick="showme('<?php echo $i?>');" style="cursor:pointer;"<?php }?>> 
			<td>&nbsp;<?php if(count($menu[$i]["sub"])<1) {?><a target="<?php if($panel=="connect") echo "_top"; else echo "mainFrame"?>" href="<?php echo $menu[$i]["href"]?>"><?php }?><font color="#333333" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $menu[$i]["head"];?></strong></font></a></td> 
		</tr>
	<?php }?>
</table>
</div>

<!-- child menu -->
<div id="<?php echo "child_".$i?>" style="position:relative;visibility:visible;display:none;"> 
	<table border="0" cellspacing="0" cellpadding="2" width="97%">			
		<?php for($j=0,$k=count($menu[$i]["sub"]);$j<$k;$j++){?>
			<?php if($dataPriv[$menu[$i]["sub"][$j]["priv"]]==true) {?>
				<tr class="menuleft_bawah"> 
					<td align="right" width=10">-</td>
					<td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="mainFrame" href="<?php echo $menu[$i]["sub"][$j]["href"]?>"><font color="#333333" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $menu[$i]["sub"][$j]["item"]?></strong></font></a></td>
				</tr>
			<?php }?>
		<?php }?>
	</table>
</div>

<?php } ?>

<!-- untuk collapse frame-->
<table width="170" border="0" cellpadding="0" cellspacing="0">
 <tr>
  <td height="22"   class="menubottom"><img src="images/transparent.gif" alt="" width=163 height=22 hspace="0" vspace="0"></td>
  </tr>
</table>

<table width="160" border="0" cellpadding="0" cellspacing="0" bordercolor="#0000FF">
  <tr> 
       <td nowrap><div id="_div_left_" style="cursor:pointer"><img style="cursor:hand" src="images/bd_firstpage.png" name="img_collapse" id="_left_img_" width="20" height="13" hspace="0" border="0" align="right" title="Collapse" onClick="javascript: window.parent.collapseLeft();changeLeftImage();">&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
    </tr>
</table>

</body>
</html>
