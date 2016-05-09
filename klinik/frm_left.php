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
			$menu[$countMenu]["head"] = "Konfigurasi";
			$menu[$countMenu]["priv"] = "setup_hakakses";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/konfigurasi/konfigurasi/medis_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;

			
	/*		$menu[$countMenu]["head"] = "Hak Akses";
			$menu[$countMenu]["priv"] = "setup_hakakses";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/konfigurasi/hakakses/hakakses_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			
			$menu[$countMenu]["head"] = "Ganti Password";
			$menu[$countMenu]["priv"] = "ganti_password";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/konfigurasi/ganti_password/ganti_password.php";
			$menu[$countMenu]["status"] = true;	    */
			
			break;
			
		
		// --- menu dokter ---
		case "dokter":
		/*	$menu[$countMenu]["head"] = "Refraksi";
			$menu[$countMenu]["priv"] = "refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/refraksi.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++; 

			$menu[$countMenu]["head"] = "Edit Refraksi";
			$menu[$countMenu]["priv"] = "refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/refraksi_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/

/*
			$menu[$countMenu]["head"] = "Tipe Diagnostik";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/diagnostik/tipe_diagnostik.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
*/	
			
			$menu[$countMenu]["head"] = "Pemeriksaan";
			$menu[$countMenu]["priv"] = "pemeriksaan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/perawatan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
		
			$menu[$countMenu]["head"] = "Edit Pemeriksaan";
			$menu[$countMenu]["priv"] = "pemeriksaan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/perawatan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			

			$menu[$countMenu]["head"] = "Pre Operasi & Jadwal";
			$menu[$countMenu]["priv"] = "pre_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/preop.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Edit Pre Operasi";
			$menu[$countMenu]["priv"] = "pre_operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/preop_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			/*$menu[$countMenu]["head"] = "Tipe Diagnostik";
			$menu[$countMenu]["priv"] = "diagnostik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/diagnostik/tipe_diagnostik.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
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
			
			$menu[$countMenu]["head"] = "Tipe Tindakan";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/tipe_tindakan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;*/

			$menu[$countMenu]["head"] = "Tindakan";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/tindakan.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Edit Bedah Minor/Injeksi";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/bedah_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Premedikasi & Operasi";
			$menu[$countMenu]["priv"] = "premedikasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/operasi.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Edit Premedikasi";
			$menu[$countMenu]["priv"] = "premedikasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/premedikasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
	
			// $menu[$countMenu]["head"] = "Operasi";
			// $menu[$countMenu]["priv"] = "operasi";
			// $menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/operasi.php";
			// $menu[$countMenu]["status"] = true;	
			// $countMenu++;

			$menu[$countMenu]["head"] = "Edit Operasi";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/operasi_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;
	
			/*$menu[$countMenu]["head"] = "Rawat Inap";
			$menu[$countMenu]["priv"] = "premedikasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/perawatan_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;*/
			
			$menu[$countMenu]["head"] = "Edit Status Pasien";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/edit_pasien_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;

			$menu[$countMenu]["head"] = "Rekam Medik";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rek_med/rekmed_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;

			$menu[$countMenu]["head"] = "Surat Sakit";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rek_med/surat_sakit_view.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;

	    /*$menu[$countMenu]["head"] = "Pelaporan Jamkesmas";
		  $menu[$countMenu]["priv"] = "pelaporan_jamkesmas";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/pelaporan_jamkesmas.php";
			$menu[$countMenu]["status"] = true;	
			$countMenu++;*/
			break;
			
		// --- menu loket ---
		/*case "loket":
			$menu[$countMenu]["head"] = "Registrasi";
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
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/perawatan_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Dokter";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/dokter_page.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Check Out";
			$menu[$countMenu]["priv"] = "registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/rawat_inap/check_out.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			break;*/
			
		// --- menu cetak ---
		case "cetak": 
		/*	$menu[$countMenu]["head"] = "Cetak Kartu Pasien";
			$menu[$countMenu]["priv"] = "cetak_kartu_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/cetak_kartu.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Cetak Status Pasien";
			$menu[$countMenu]["priv"] = "cetak_status_pasien";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/cetak_status.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/
			
			$menu[$countMenu]["head"] = "Surat Ket Sakit";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/ket_sakit_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
	
			$menu[$countMenu]["head"] = "Surat Rujukan";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/rujukan_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "S.Ket Kesehatan Mata";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/mata_view.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
			break;
			
		case "report":
		/*	$menu[$countMenu]["head"] = "Report Pasien";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Refraksi";
			$menu[$countMenu]["priv"] = "report_refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/report_refraksi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/
			
			$menu[$countMenu]["head"] = "Report Pemeriksaan";
			$menu[$countMenu]["priv"] = "pemeriksaan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/report_pemeriksaan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			$menu[$countMenu]["head"] = "Report Pasien Ruang 3";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_tindakan_2.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Jadwal Operasi";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_jadwal_operasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Rekap Poli 5";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_poli5.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Premedikasi";
			$menu[$countMenu]["priv"] = "premedikasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_premedikasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			

			$menu[$countMenu]["head"] = "Report Operasi";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_operasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Evaluasi Operasi";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_operasi_evaluasi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			
			$menu[$countMenu]["head"] = "Report Operasi Hari Ini";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_op_hari.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap OK Jenis Kelamin";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/rekap_ok_sex_tahunan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap OK Jenis Pasien";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/rekap_ok_jenis_tahunan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap OK berdasarkan Kinerja Dokter";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/rekap_ok_dokter_tahunan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap OK berdasarkan Lensa";
			$menu[$countMenu]["priv"] = "operasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/rekap_ok_dokter_lensa_tahunan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
	/*		$menu[$countMenu]["head"] = "Rekap Pasien";
			$menu[$countMenu]["priv"] = "report_registrasi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/rekap_pasien.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap Refraksi";
			$menu[$countMenu]["priv"] = "report_refraksi";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/rekap_refraksi.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;*/
	
			$menu[$countMenu]["head"] = "Report Visus";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/refraksi/report_visus.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
	
			$menu[$countMenu]["head"] = "Rekap Pemeriksaan";
			$menu[$countMenu]["priv"] = "pemeriksaan";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/report_perawatan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
		
		/*	$menu[$countMenu]["head"] = "Rekap Diagnostik";
			$menu[$countMenu]["priv"] = "report_diagnostik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/diagnostik/report_diagnostik.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			*/
               $menu[$countMenu]["head"] = "Report Point Pegawai";
			$menu[$countMenu]["priv"] = "report_point_pegawai";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/tindakan/report_point.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;
			/*
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
			$countMenu++; */  
			
      $menu[$countMenu]["head"] = "Klaim JamKesMas Pusat";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/perawatan/report_jamkesmas.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
      $menu[$countMenu]["head"] = "Surat Ket Sakit";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_sakit.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;  
			
      $menu[$countMenu]["head"] = "Surat Rujukan";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_rujukan.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;  
			
      $menu[$countMenu]["head"] = "Surat Kesehatan Mata";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/cetak/report_surat_mata.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++; 
			
			$menu[$countMenu]["head"] = "Report Pasien Dinas Luar";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_pasien_dinasluar.php";
			$menu[$countMenu]["status"] = true;        
			$countMenu++;

			$menu[$countMenu]["head"] = "Report History Pasien";
			$menu[$countMenu]["priv"] = "klinik";
			$menu[$countMenu]["href"] = $APLICATION_ROOT."module/registrasi/report_history_pasien.php";
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


function setDisplay(id) {
     var disp = Array();
     
     disp['none'] = 'block';
     disp['block'] = 'none';
     
     document.getElementById(id).style.display = disp[document.getElementById(id).style.display];
}

</script>

<?php include("com/acordion.php"); ?>
</head>

<body>

<!--<img src="com/gambar/logo.gif" width="100%">-->

<div class="arrowlistmenu">
<h3 class="menuheader expandable">Menu <?php echo $panel ;?></h3>
<ul class="categoryitems">
<li><?php if($panel=="dokter"){
	       echo "<a href=\"#\" onClick=\"setDisplay('rg2');\"><font style=\"color:#3f3f3f;font-size:14px;\" face=\"Arial, Helvetica, sans-serif; \"><strong>Ruang 2</font></strong></a>\n";
	       echo "<ul id=\"rg2\" style=\"display:none\"><li>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[0]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[0]["head"]."</strong></font></a>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[1]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[1]["head"]."</strong></font></a></li></ul>\n";
	       echo "<a href=\"#\" onClick=\"setDisplay('rg3');\"><font style=\"color:3f3f3f;font-size:14px;\" face=\"Arial, Helvetica, sans-serif;\"><strong>Ruang 3</font></strong></a>\n";
	       echo "<ul id=\"rg3\" style=\"display:none\"><li>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[4]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[4]["head"]."</strong></font></a>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[5]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[5]["head"]."</strong></font></a></li></ul>\n";
	       echo "<a href=\"#\" onClick=\"setDisplay('rg5');\"><font style=\"color:#3f3f3f;font-size:14px;\" face=\"Arial, Helvetica, sans-serif;\"><strong>Ruang 5</font></strong></a>\n";
	       echo "<ul id=\"rg5\" style=\"display:none\"><li>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[2]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[2]["head"]."</strong></font></a>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[3]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[3]["head"]."</strong></font></a></li></ul>\n";
	        echo "<a href=\"#\" onClick=\"setDisplay('rgOK');\"><font style=\"color:#3f3f3f; font-size:14px;\" face=\"Arial, Helvetica, sans-serif;\"><strong>Ruang OK</font></strong></a>\n";
	       echo "<ul id=\"rgOK\" style=\"display:none\"><li>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[6]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[6]["head"]."</strong></font></a>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[8]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[8]["head"]."</strong></font></a>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[7]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[7]["head"]."</strong></font></a>\n";
	       echo "</li></ul>\n";
	       echo "<a href=\"#\" onClick=\"setDisplay('cetak');\"><font style=\"color:#3f3f3f; font-size:14px;\" face=\"Arial, Helvetica, sans-serif;\"><strong>Cetak</font></strong></a>\n";
	       echo "<ul id=\"cetak\" style=\"display:none\"><li>";
	       echo "<a target=\"mainFrame\" href=\"".$menu[10]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[10]["head"]."</strong></font></a>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[11]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[11]["head"]."</strong></font></a>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[12]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[12]["head"]."</strong></font></a></li></ul>\n";
	       echo "<a target=\"mainFrame\" href=\"".$menu[9]["href"]."\"><font color=\"#333333\" size=\"2\" face=\"Arial, Helvetica, sans-serif\"><strong>".$menu[9]["head"]."</strong></font></a>\n";
	    }else{
	       for($i=0,$n=count($menu);$i<$n;$i++){
	       if($menu[$i]["status"]==true) {
	       if(count($menu[$i]["sub"])<1) {?><a target="<?php if($panel=="connect") echo "_top"; else echo "mainFrame"?>" href="<?php echo $menu[$i]["href"]?>"><?php }?><font color="#333333" size="2" face="Arial, Helvetica, sans-serif"><strong><?php echo $menu[$i]["head"];?></strong></font></a>
      <?php } } } ?>
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
