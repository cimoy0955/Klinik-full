<?php
   require_once("root.inc.php");
   require_once($ROOT."library/auth.cls.php");
   require_once($ROOT."library/textEncrypt.cls.php");
   require_once($APLICATION_ROOT."library/config/global.cfg.php");
   
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
    
				
		// --- menu loket ---
		case "loket":
			$menu[$countMenu]["head"] = "Registrasi";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/registrasi.php?tipe=".RAWAT_JALAN;
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
			$countMenu++;

			$menu[$countMenu]["head"] = "Edit Status Pasien";
			$menu[$countMenu]["priv"] = "edit_status_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/edit_pasien_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
			$countMenuInap = 0;
			$menuInap[$countMenuInap]["head"] = "Reg. Pasien RI Baru";
			$menuInap[$countMenuInap]["priv"] = "registrasi";
			$menuInap[$countMenuInap]["href"] = $APLICATION_ROOT."module/rawat_inap/registrasi_rawat_inap.php";
			$menuInap[$countMenuInap]["status"] = true;        
			$countMenuInap++;
			
			$menuInap[$countMenuInap]["head"] = "Reg. Rawat Inap";
			$menuInap[$countMenuInap]["priv"] = "registrasi";
			$menuInap[$countMenuInap]["href"] = $APLICATION_ROOT."module/rawat_inap/registrasi.php";
			$menuInap[$countMenuInap]["status"] = true;        
			$countMenuInap++;
			
			$menuInap[$countMenuInap]["head"] = "Pasien Inap Harian";
			$menuInap[$countMenuInap]["priv"] = "report_registrasi";
			$menuInap[$countMenuInap]["href"] = $APLICATION_ROOT."module/registrasi/report_pasien_rawat_inap.php";
			$menuInap[$countMenuInap]["status"] = true;        
			$countMenuInap++;
			
			$countMenuUGD = 0;
			$menuUGD[$countMenuUGD]["head"] = "Registrasi UGD";
			$menuUGD[$countMenuUGD]["priv"] = "registrasi";
			$menuUGD[$countMenuUGD]["href"] = $APLICATION_ROOT."module/registrasi/registrasi.php?tipe=".RAWAT_UGD;
			$menuUGD[$countMenuUGD]["status"] = true;        
			$countMenuUGD++;
			
			$countMenuUmum = 0;
			$menuUmum[$countMenuUmum]["head"] = "Registrasi Pasien Umum";
			$menuUmum[$countMenuUmum]["priv"] = "registrasi";
			$menuUmum[$countMenuUmum]["href"] = $APLICATION_ROOT."module/registrasi/registrasi.php?tipe=".RAWAT_UMUM;
			$menuUmum[$countMenuUmum]["status"] = true;        
			$countMenuUmum++;
						
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
			
		/*	$menu[$countMenu]["head"] = "Surat Ket Sakit";
			$menu[$countMenu]["priv"] = "surat_ket_sakit";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/ket_sakit_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; */
	
			$menu[$countMenu]["head"] = "Surat Rujukan";
			$menu[$countMenu]["priv"] = "surat_rujukan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/rujukan_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			/*$menu[$countMenu]["head"] = "S.Ket Kesehatan Mata";
			$menu[$countMenu]["priv"] = "surat_ket_kesehatan_mata";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/mata_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; */
			
			break;
			
		case "report":
			$menu[$countMenu]["head"] = "Report Pasien";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Pendf. Rawat Inap";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_pendf_pasien_inap.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Rawat Inap";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_pasien_rawat_inap.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			break;
		
		// --- menu setup ---
		case "setup":
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
			
			$menu[$countMenu]["head"] = "Paket Operasi";
			$menu[$countMenu]["priv"] = "setup_paket_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/paket_operasi/paket_operasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Paket Biaya Klaim";
			$menu[$countMenu]["priv"] = "setup_biaya_klaim";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/biaya_klaim/biaya_klaim_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Paket Biaya Pasien";
			$menu[$countMenu]["priv"] = "setup_biaya_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/biaya_pasien/biaya_pasien_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Jenis Operasi";
			$menu[$countMenu]["priv"] = "setup_jenis_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/jenis_operasi/jenis_operasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "ICD 10";
			$menu[$countMenu]["priv"] = "setup_icd";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/icd/icd_view.php?jenis=1";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "ICD 9";
			$menu[$countMenu]["priv"] = "setup_icd";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/icd/icd_view.php?jenis=2";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "INA DRG";
			$menu[$countMenu]["priv"] = "setup_ina";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/ina/ina_view.php?jenis=1";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Setup Biaya";
			$menu[$countMenu]["priv"] = "setup_biaya";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/biaya/biaya_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
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
			
			$menu[$countMenu]["head"] = "Visus";
			$menu[$countMenu]["priv"] = "setup_visus";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/visus/visus_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Jenis Biaya";
			$menu[$countMenu]["priv"] = "setup_tagihan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/tagihan/tagihan_view.php";
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
		/*	
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

<?php if($menuInap){?>
<div class="arrowlistmenu">
<h3 class="menuheader expandable">Menu Rawat Inap</h3>
<ul class="categoryitems">
<li><?php for($i=0,$n=count($menuInap);$i<$n;$i++){?>
      <?php if($menuInap[$i]["status"]==true) { ?>
      <?php if(count($menuInap[$i]["sub"])<1) {?><a target="<?php if($panel=="connect") echo "_top"; else echo "mainFrame"?>" href="<?php echo $menuInap[$i]["href"]?>"><?php }?><font color="#333333" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $menuInap[$i]["head"];?></strong></font></a>
      <?php } ?> <?php } ?>
</li>
</ul>
<?php }?>
<?php if($menuUGD){?>
<div class="arrowlistmenu">
<h3 class="menuheader expandable">Menu Rawat UGD</h3>
<ul class="categoryitems">
<li><?php for($i=0,$n=count($menuUGD);$i<$n;$i++){?>
      <?php if($menuUGD[$i]["status"]==true) { ?>
      <?php if(count($menuUGD[$i]["sub"])<1) {?><a target="<?php if($panel=="connect") echo "_top"; else echo "mainFrame"?>" href="<?php echo $menuUGD[$i]["href"]?>"><?php }?><font color="#333333" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $menuUGD[$i]["head"];?></strong></font></a>
      <?php } ?> <?php } ?>
</li>
</ul>
<?php }?>
<?php if($menuUmum){?>
<div class="arrowlistmenu">
<h3 class="menuheader expandable">Menu Rawat Umum</h3>
<ul class="categoryitems">
<li><?php for($i=0,$n=count($menuUmum);$i<$n;$i++){?>
      <?php if($menuUmum[$i]["status"]==true) { ?>
      <?php if(count($menuUmum[$i]["sub"])<1) {?><a target="<?php if($panel=="connect") echo "_top"; else echo "mainFrame"?>" href="<?php echo $menuUmum[$i]["href"]?>"><?php }?><font color="#333333" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $menuUmum[$i]["head"];?></strong></font></a>
      <?php } ?> <?php } ?>
</li>
</ul>
<?php }?>
<h3 class="menuheader expandable">Log Out</h3>
<ul class="categoryitems">
<li>
<a href="" onClick="javascript: return Logout();">LogOut</a>
</li>
</ul>

</div>

</body>
</html>
