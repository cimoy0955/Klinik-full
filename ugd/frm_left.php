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
			
		
		// --- menu dokter ---
		case "dokter":
		/*$menu[$countMenu]["head"] = "Refraksi";
			$menu[$countMenu]["priv"] = "refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/refraksi.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++; 

			$menu[$countMenu]["head"] = "Edit Refraksi";
			$menu[$countMenu]["priv"] = "refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/refraksi_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
		
			$menu[$countMenu]["head"] = "Pemeriksaan";
			$menu[$countMenu]["priv"] = "perawatan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/perawatan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
		
			$menu[$countMenu]["head"] = "Edit Pemeriksaan";
			$menu[$countMenu]["priv"] = "perawatan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/perawatan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;*/
		
			$menu[$countMenu]["head"] = "Pemeriksaan UGD";
			$menu[$countMenu]["priv"] = "perawatan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/ugd/perawatan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
		
			$menu[$countMenu]["head"] = "Edit Pemeriksaan UGD";
			$menu[$countMenu]["priv"] = "perawatan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/ugd/perawatan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	    /*
			$menu[$countMenu]["head"] = "Diagnostik";
			$menu[$countMenu]["priv"] = "diagnostik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/diagnostik/diagnostik.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	

			$menu[$countMenu]["head"] = "Edit Diagnostik";
			$menu[$countMenu]["priv"] = "diagnostik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/diagnostik/diag_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;

			$menu[$countMenu]["head"] = "Tindakan";
			$menu[$countMenu]["priv"] = "tindakan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/tindakan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Edit Bedah Minor/Injeksi";
			$menu[$countMenu]["priv"] = "tindakan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/bedah_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Premedikasi";
			$menu[$countMenu]["priv"] = "premedikasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/premedikasi.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Edit Premedikasi";
			$menu[$countMenu]["priv"] = "premedikasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/premedikasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Edit Operasi";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/operasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rawat Inap";
			$menu[$countMenu]["priv"] = "premedikasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/rawatinap_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Edit Status Pasien";
			$menu[$countMenu]["priv"] = "edit_status_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/edit_pasien_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
	    $menu[$countMenu]["head"] = "Pelaporan Jamkesmas";
			$menu[$countMenu]["priv"] = "pelaporan_jamkesmas";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/pelaporan_jamkesmas.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
	    $menu[$countMenu]["head"] = "RM Rawat Jalan";
			$menu[$countMenu]["priv"] = "perawatan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rekap_medik/rm_rawat_jalan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
	    $menu[$countMenu]["head"] = "RM Rawat Inap";
			$menu[$countMenu]["priv"] = "perawatan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rekap_medik/rm_rawat_inap.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	    */
	    $menu[$countMenu]["head"] = "Rekap Medik UGD";
			$menu[$countMenu]["priv"] = "perawatan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rekap_medik/rm_ugd.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;

			$menu[$countMenu]["head"] = "Stok Saldo";
			$menu[$countMenu]["priv"] = "perawatan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rekap_medik/trans_opname.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			break;
			
		// --- menu loket ---
		case "loket":
			$menu[$countMenu]["head"] = "Registrasi";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/registrasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;

			$menu[$countMenu]["head"] = "Antrian Rawat Jalan";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/antrian.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Registrasi Rawat Inap";
			$menu[$countMenu]["priv"] = "edit_status_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/edit_pasien_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Antrian Rawat Inap";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/registrasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Antrian UGD";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/ugd/registrasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Registrasi ICU";
			$menu[$countMenu]["priv"] = "edit_status_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/reg_icu_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Antrian ICU";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/icu/registrasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Registrasi PICU";
			$menu[$countMenu]["priv"] = "edit_status_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/reg_picu_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Antrian PICU";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/picu/registrasi.php";
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
	
			$menu[$countMenu]["head"] = "Kasir";
			$menu[$countMenu]["priv"] = "kasir";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/kasir/kasir_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			break;
			
    // --- menu rawat inap ---
		case "rawatinap": 
			$menu[$countMenu]["head"] = "Perawat";
			$menu[$countMenu]["priv"] = "rawat_inap";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/perawat_page.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Dokter";
			$menu[$countMenu]["priv"] = "rawat_inap";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/dokter_page.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Check Out";
			$menu[$countMenu]["priv"] = "rawat_inap";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/check_out.php";
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
			
			$menu[$countMenu]["head"] = "S.Ket Kesehatan";
			$menu[$countMenu]["priv"] = "surat_ket_kesehatan_mata";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/mata_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
			break;
			
		case "report":
			$menu[$countMenu]["head"] = "Report Pasien";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			/*
			$menu[$countMenu]["head"] = "Report Refraksi";
			$menu[$countMenu]["priv"] = "report_refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/report_refraksi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/
			
			$menu[$countMenu]["head"] = "Pemeriksaan UGD";
			$menu[$countMenu]["priv"] = "report_pemeriksaan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/report_pemeriksaan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			/*
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
	
//			$menu[$countMenu]["head"] = "Rekap Refraksi";
//			$menu[$countMenu]["priv"] = "report_refraksi";
//			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/rekap_refraksi.php";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++;
	
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
			
//      $menu[$countMenu]["head"] = "Surat Kesehatan Mata";
//			$menu[$countMenu]["priv"] = "report_surat_mata";
//			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_mata.php";
//			$menu[$countMenu]["status"] = true;        
//			$countMenu++; 
			
			$menu[$countMenu]["head"] = "Report Pasien Dinas Luar";
			$menu[$countMenu]["priv"] = "dinas_luar";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_pasien_dinasluar.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/

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
			
			$menu[$countMenu]["head"] = "Biaya Tambahan";
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
			
			$menu[$countMenu]["head"] = "Setup Level ICU";
			$menu[$countMenu]["priv"] = "setup_kamar";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/setup/icu/icu_view.php";
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
