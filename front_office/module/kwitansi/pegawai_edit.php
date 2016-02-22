<?php
     // --- LIBRARY  ----
     require_once("root.inc.php");
     require_once($APLICATION_ROOT."library/bitFunc.lib.php");
     require_once($APLICATION_ROOT."library/auth.cls.php");
     require_once($APLICATION_ROOT."library/textEncrypt.cls.php");
     require_once($APLICATION_ROOT."library/datamodel.cls.php");
     require_once($APLICATION_ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     require_once($APLICATION_ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/tree.cls.php");
     
     // ----INISIALISAI AWAL LIBRARY  ---
     $dtaccess = new DataAccess();
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $tree = new CTree("gaji.gaji_pegawai","pgw_id",TREE_LENGTH);
     $skr=date("Y-m-d");
     $username = $auth->GetUserName();
     $lokasi = $APLICATION_ROOT."images/foto_pegawai";
     $table = new inoTable("table","800","left");
     $flag = 'y';
     
     // Full Ajax ----
     $plx = new inoLiveX("GetNip,GetDataJabatan,HapusJabatan,HistPegawaiSimpan,GetDataAnak,HistAnakSimpan,HapusAnak,
     GetDataOrtu,HistOrtuSimpan,HapusOrtu,HistIstriSimpan,GetDataIstri,HapusIstri,GetDataPendidikan,HapusPendidikan,
     HistPendidikanSimpan,HapusPenghargaan,GetDataPenghargaan,HistPenghargaanSimpan,GetDataPemberhentian,HistPemberhentianSimpan,
     HapusPemberhentian,GetDataCuti,HistCutiSimpan,HapusCuti,GetDataHukuman,HistHukumanSimpan,HapusHukuman,GetDataEselon,HistEselonSimpan,HapusEselon,
     GetDataGolongan,HistGolonganSimpan,HapusGolongan,GetDataBerkala,HistBerkalaSimpan,HapusBerkala");
     
     // ----AJAX / JQUERY   ---
     function GetNip($nip)
	   {
          global $dtaccess;
          $sql = "SELECT a.pgw_id FROM gaji.gaji_pegawai a 
                  WHERE upper (a.pgw_nip_baru) = ".QuoteValue(DPE_CHAR,strtoupper($nip));                        
          if($pgwId) $sql .= " and a.pgw_id <> ".QuoteValue(DPE_NUMERIC,$pgwId);
         	
		      $rs = $dtaccess->Execute($sql);
          $data = $dtaccess->Fetch($rs);
		      return $data["pgw_id"];
     }
    
              // --- AJAX untuk menampilkan tabel transaksi pemesanan   ---- 
     // Tab Untuk menampilkan History jabatan ---
    function GetDataJabatan($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT;	
  		$sql = "select a.*,b.jabatan_nama as riw_jab_lama
              from gaji.gaji_riwayat_jabatan a
              left join gaji.gaji_jabatan b on a.riw_jab_lama=b.jabatan_id
              where id_pgw=".$pgwId." order by a.riw_jab_tgl_sk desc"; 
  		$rs_jabatan = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataJabatan = $dtaccess->FetchAll($rs_jabatan);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Tgl SK";
  		$tbHeader[0][2][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "No SK";
  		$tbHeader[0][3][TABLE_WIDTH] = "15%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Jabatan Sekarang";
  		$tbHeader[0][4][TABLE_WIDTH] = "30%";
  			
  		$tbHeader[0][5][TABLE_ISI] = "TMT Jabatan";
  		$tbHeader[0][5][TABLE_WIDTH] = "15%";

  		for($i=0,$counter=0,$n=count($dataJabatan);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusJabatan(\''.$dataJabatan[$i]["riw_jab_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataJabatan[$i]["riw_jab_tgl_sk"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataJabatan[$i]["riw_jab_no_sk"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataJabatan[$i]["riw_jab_lama"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataJabatan[$i]["riw_tmt_jabatan"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  		}
		  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }   
    
    // Tab untuk menampilkan History  dAta anak ---
    function GetDataAnak($pgwId) 
    {
  		global $dtaccess,$table,$view,$dataKelamin,$dataNikah,$APLICATION_ROOT;
  		$sql = "select a.*
              from gaji.gaji_pegawai_data_anak a
              where id_pgw=".$pgwId." order by a.pgw_anak_id desc"; 
  		$rs_anak = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataAnak = $dtaccess->FetchAll($rs_anak);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Nama";
  		$tbHeader[0][2][TABLE_WIDTH] = "20%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "Jenis Kelamin";
  		$tbHeader[0][3][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Tempat Lahir";
  		$tbHeader[0][4][TABLE_WIDTH] = "10%";
  			
  		$tbHeader[0][5][TABLE_ISI] = "Tanggal Lahir";
  		$tbHeader[0][5][TABLE_WIDTH] = "10%";
      
      $tbHeader[0][6][TABLE_ISI] = "Pendidikan";
  		$tbHeader[0][6][TABLE_WIDTH] = "20%";
  		
  		$tbHeader[0][7][TABLE_ISI] = "Pekerjaan";
  		$tbHeader[0][7][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][8][TABLE_ISI] = "Nikah";
  		$tbHeader[0][8][TABLE_WIDTH] = "10%";
  		
  		for($i=0,$counter=0,$n=count($dataAnak);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusAnak(\''.$dataAnak[$i]["pgw_anak_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataAnak[$i]["pgw_anak_nama"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataKelamin[$dataAnak[$i]["pgw_anak_jenis_kelamin"]];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 	
      			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataAnak[$i]["pgw_anak_kota_lahir"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
    		
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataAnak[$i]["pgw_anak_tanggal_lahir"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $dataAnak[$i]["pgw_anak_pendidikan"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataAnak[$i]["pgw_anak_kerja"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataNikah[$dataAnak[$i]["pgw_anak_nikah"]];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  		}	  
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }
     
    // Tab untuk menampilkan History  dAta Ortu ---
    function GetDataOrtu($pgwId) 
    {
  		global $dtaccess,$table,$view,$statusOrtu,$APLICATION_ROOT;
  		$sql = "select a.*
              from gaji.gaji_data_ortu a
              where id_pgw=".$pgwId." order by a.pgw_ortu_id desc"; 
  		$rs_ortu = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataOrtu = $dtaccess->FetchAll($rs_ortu);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Nama Ayah";
  		$tbHeader[0][2][TABLE_WIDTH] = "30%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "Status Ayah";
  		$tbHeader[0][3][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Nama Ibu";
  		$tbHeader[0][4][TABLE_WIDTH] = "30%";
  			
  		$tbHeader[0][5][TABLE_ISI] = "Status Ibu";
  		$tbHeader[0][5][TABLE_WIDTH] = "10%";

  		for($i=0,$counter=0,$n=count($dataOrtu);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusOrtu(\''.$dataOrtu[$i]["pgw_ortu_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataOrtu[$i]["pgw_nama_ayah"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $statusOrtu[$dataOrtu[$i]["pgw_status_ayah"]];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 	
      			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataOrtu[$i]["pgw_nama_ibu"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
    		
  			$tbContent[$i][$counter][TABLE_ISI] = $statusOrtu[$dataOrtu[$i]["pgw_status_ibu"]];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;        
  		}	  
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }
    
     // Tab untuk menampilkan History  dAta Suami Istri ---
    function GetDataIstri($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT;
  		$sql = "select a.* from gaji.gaji_pegawai_suami_istri a
              where id_pgw=".$pgwId." order by a.pgw_suami_istri_id desc"; 
  		$rs_istri = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataIstri = $dtaccess->FetchAll($rs_istri);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "NIP Istri/Suami";
  		$tbHeader[0][2][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "NIP Baru Istri/Suami";
  		$tbHeader[0][3][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Nama Istri/Suami";
  		$tbHeader[0][4][TABLE_WIDTH] = "20%";
  			
  		$tbHeader[0][5][TABLE_ISI] = "Tanggal Lahir Istri/Suami";
  		$tbHeader[0][5][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][6][TABLE_ISI] = "Tanggal Perkawinan";
  		$tbHeader[0][6][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][7][TABLE_ISI] = "Tanggal Cerai";
  		$tbHeader[0][7][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][8][TABLE_ISI] = "Tanggal Meninggal";
  		$tbHeader[0][8][TABLE_WIDTH] = "10%";

  		for($i=0,$counter=0,$n=count($dataIstri);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusIstri(\''.$dataIstri[$i]["pgw_suami_istri_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataIstri[$i]["pgw_nip_istri_suami"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataIstri[$i]["pgw_nip_baru_suami_istri"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 	
      			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataIstri[$i]["pgw_nama_istri_suami"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
    		
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataIstri[$i]["pgw_tgl_lahir_istrisuami"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = format_date($dataIstri[$i]["pgw_tgl_perkawinan"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $dataIstri[$i]["pgw_tgl_cerai"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $dataIstri[$i]["tgl_meninggal"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;      
  		}	  
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }
     
     // Tab untuk menampilkan History  data pendidikan ---
    function GetDataPendidikan($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT,$Tingkat_pendidikan;
  		$sql = "select a.*
              from gaji.gaji_riwayat_pendidikan a
              where id_pgw=".$pgwId." order by a.pendidikan_tahun desc"; 
  		$rs_pendidikan = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataPendidikan = $dtaccess->FetchAll($rs_pendidikan);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Tingkat Pendidikan";
  		$tbHeader[0][2][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "Lulusan";
  		$tbHeader[0][3][TABLE_WIDTH] = "25%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Tahun Lulus";
  		$tbHeader[0][4][TABLE_WIDTH] = "10%";
 	
  		for($i=0,$counter=0,$n=count($dataPendidikan);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusPendidikan(\''.$dataPendidikan[$i]["pendidikan_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $Tingkat_pendidikan[$dataPendidikan[$i]["pendidikan_tingkat"]];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $Tingkat_pendidikan[$dataPendidikan[$i]["pendidikan_tingkat"]]."&nbsp;".$dataPendidikan[$i]["pendidikan_nama"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 
        
        $tbContent[$i][$counter][TABLE_ISI] = $dataPendidikan[$i]["pendidikan_tahun"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;	
      			}
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }
     
     // Tab untuk menampilkan History  data penghargaan ---
    function GetDataPenghargaan($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT;
  		$sql = "select a.* from gaji.gaji_riwayat_penghargaan a
              where id_pgw=".$pgwId." order by a.pgw_penghargaan_id"; 
  		$rs_penghargaan = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataPenghargaan = $dtaccess->FetchAll($rs_penghargaan);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Kode";
  		$tbHeader[0][2][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "Nama Penghargaan";
  		$tbHeader[0][3][TABLE_WIDTH] = "25%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Tahun ";
  		$tbHeader[0][4][TABLE_WIDTH] = "10%";

      $tbHeader[0][5][TABLE_ISI] = "Keterangan ";
  		$tbHeader[0][5][TABLE_WIDTH] = "25%";
 	
  		for($i=0,$counter=0,$n=count($dataPenghargaan);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusPenghargaan(\''.$dataPenghargaan[$i]["pgw_penghargaan_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataPenghargaan[$i]["pgw_kode_penghargaan"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataPenghargaan[$i]["pgw_nama_penghargaan"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 
        
        $tbContent[$i][$counter][TABLE_ISI] = $dataPenghargaan[$i]["pgw_tahun_penghargaan"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;	
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataPenghargaan[$i]["pgw_ket_penghargaan"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;	
      			}
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }
     
   // Tab untuk menampilkan History  data pemberhentian---
    function GetDataPemberhentian($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT;
  		$sql = "select a.*
              from gaji.gaji_riwayat_pemberhentian a
              where id_pgw=".$pgwId." order by a.pgw_pemberhentian_id"; 
  		$rs_pemberhentian = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataPemberhentian = $dtaccess->FetchAll($rs_pemberhentian);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Jenis Pemberhentian";
  		$tbHeader[0][2][TABLE_WIDTH] = "20%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "No Sk";
  		$tbHeader[0][3][TABLE_WIDTH] = "15%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Tgl Sk";
  		$tbHeader[0][4][TABLE_WIDTH] = "10%";
 	
  		for($i=0,$counter=0,$n=count($dataPemberhentian);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusPemberhentian(\''.$dataPemberhentian[$i]["pgw_pemberhentian_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataPemberhentian[$i]["pgw_jenis_berhenti"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataPemberhentian[$i]["pgw_no_sk_berhenti"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 
        
        $tbContent[$i][$counter][TABLE_ISI] = format_date($dataPemberhentian[$i]["pgw_tgl_sk"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;	
      			}
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }
     
  // Tab untuk menampilkan History  data cuti ---
    function GetDataCuti($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT;
  		$sql = "select a.*,b.* from gaji.gaji_riwayat_cuti a
              left join gaji.gaji_master_cuti b on b.pgw_master_cuti_id=a.pgw_jenis_cuti
              where id_pgw=".$pgwId." order by a.pgw_cuti_id"; 
  		$rs_cuti = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataCuti = $dtaccess->FetchAll($rs_cuti);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Jenis Cuti";
  		$tbHeader[0][2][TABLE_WIDTH] = "20%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "Lama Cuti";
  		$tbHeader[0][3][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Mulai Cuti";
  		$tbHeader[0][4][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][5][TABLE_ISI] = "Akhir Cuti";
  		$tbHeader[0][5][TABLE_WIDTH] = "10%";
 	
  		for($i=0,$counter=0,$n=count($dataCuti);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusCuti(\''.$dataCuti[$i]["pgw_cuti_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataCuti[$i]["pgw_master_cuti_nama"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataCuti[$i]["pgw_lama_cuti"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 
        
        $tbContent[$i][$counter][TABLE_ISI] = format_date($dataCuti[$i]["pgw_cuti_mulai"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = format_date($dataCuti[$i]["pgw_cuti_akhir"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;	
      			}
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }
     
     // Tab untuk menampilkan History  dAta anak ---
    function GetDataHukuman($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT;
  		$sql = "select a.*,b.gol_gol from gaji.gaji_hukuman_pegawai a
              join gaji.gaji_golongan b on b.gol_id=a.gol_ruang
              where id_pgw=".$pgwId." order by a.pgw_jenis_hukuman_id desc"; 
  		$rs_hukuman = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataHukuman = $dtaccess->FetchAll($rs_hukuman);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Jenis Hukuman";
  		$tbHeader[0][2][TABLE_WIDTH] = "20%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "Nomer SK-HD";
  		$tbHeader[0][3][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Tanggal SK-HD";
  		$tbHeader[0][4][TABLE_WIDTH] = "10%";
  			
  		$tbHeader[0][5][TABLE_ISI] = "TMT HUK Disiplin";
  		$tbHeader[0][5][TABLE_WIDTH] = "10%";
      
      $tbHeader[0][6][TABLE_ISI] = "Masa Hukuman";
  		$tbHeader[0][6][TABLE_WIDTH] = "20%";
  		
  		$tbHeader[0][7][TABLE_ISI] = "Golongan";
  		$tbHeader[0][7][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][8][TABLE_ISI] = "Akhir Hukuman";
  		$tbHeader[0][8][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][9][TABLE_ISI] = "Nomer PP";
  		$tbHeader[0][9][TABLE_WIDTH] = "10%";
  		
  		for($i=0,$counter=0,$n=count($dataHukuman);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusHukuman(\''.$dataHukuman[$i]["pgw_jenis_hukuman_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataHukuman[$i]["pgw_jenis_hukuman"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataHukuman[$i]["pgw_no_skhd"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 	
      			
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataHukuman[$i]["pgw_tanggal_skhd"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
    		
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataHukuman[$i]["pgw_tmt_hukdis"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $dataHukuman[$i]["pgw_masa_hukuman_tahun"]."&nbsp;Tahun";
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataHukuman[$i]["gol_gol"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataHukuman[$i]["pgw_akhir_hukuman"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataHukuman[$i]["pgw_no_pp"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  		}	  
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }
				
		// Tab untuk menampilkan Riwayat Eselon ---
    function GetDataEselon($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT;
  		$sql = "select a.* from gaji.gaji_riwayat_eselon a
              where id_pgw=".$pgwId." order by a.riw_eselon_tgl_sk desc"; 
  		$rs_eselon = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataEselon = $dtaccess->FetchAll($rs_eselon);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Tanggal SK";
  		$tbHeader[0][2][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "No SK";
  		$tbHeader[0][3][TABLE_WIDTH] = "15%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Eselon Baru";
  		$tbHeader[0][4][TABLE_WIDTH] = "20%";
  			
  		$tbHeader[0][5][TABLE_ISI] = "TMT Eselon";
  		$tbHeader[0][5][TABLE_WIDTH] = "10%";

  		for($i=0,$counter=0,$n=count($dataEselon);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusEselon(\''.$dataEselon[$i]["riw_eselon_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataEselon[$i]["riw_eselon_tgl_sk"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataEselon[$i]["riw_eselon_no_sk"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 	
      			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataEselon[$i]["riw_eselon_nama"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
    		
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataEselon[$i]["riw_tmt_eselon"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;        
  		}	  
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }	
     
    // Tab untuk menampilkan Riwayat Eselon ---
    function GetDataGolongan($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT;
  		$sql = "select a.*,b.gol_gol,b.gol_pangkat
              from gaji.gaji_riwayat_kepangkatan a
              join gaji.gaji_golongan b on b.gol_id=a.riw_golongan_pangkat
              where id_pgw=".$pgwId." order by a.riw_golongan_tgl_sk desc"; 
  		$rs_pangkat = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataPangkat = $dtaccess->FetchAll($rs_pangkat);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Tanggal SK";
  		$tbHeader[0][2][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "No SK";
  		$tbHeader[0][3][TABLE_WIDTH] = "15%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Pangkat Baru";
  		$tbHeader[0][4][TABLE_WIDTH] = "20%";
  			
  		$tbHeader[0][5][TABLE_ISI] = "TMT Golongan";
  		$tbHeader[0][5][TABLE_WIDTH] = "10%";

  		for($i=0,$counter=0,$n=count($dataPangkat);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusGolongan(\''.$dataPangkat[$i]["riw_golongan_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataPangkat[$i]["riw_golongan_tgl_sk"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataPangkat[$i]["riw_golongan_no_sk"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 	
      			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataPangkat[$i]["gol_pangkat"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
    		
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataPangkat[$i]["riw_tmt_golongan"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;        
  		}	  
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }	
     
  // Tab untuk menampilkan Riwayat Eselon ---
    function GetDataBerkala($pgwId) 
    {
  		global $dtaccess,$table,$view,$APLICATION_ROOT;
  		$sql = "select a.*,b.gol_gol,c.gaji_masa_kerja
              from gaji.gaji_riwayat_berkala a
              join gaji.gaji_golongan b on b.gol_id=a.riw_golongan_baru
              join gaji.gaji_gaji c on c.gaji_id=a.riw_masa_kerja
              where id_pgw=".$pgwId." order by a.riw_gaji_berkala_tgl_sk desc"; 
  		$rs_berkala = $dtaccess->Execute($sql,DB_SCHEMA);
  		$dataBerkala = $dtaccess->FetchAll($rs_berkala);
  		$tbHeader[0][0][TABLE_ISI] = "Hps";
      $tbHeader[0][0][TABLE_WIDTH] = "1%";
      
  		$tbHeader[0][1][TABLE_ISI] = "No";
  		$tbHeader[0][1][TABLE_WIDTH] = "1%";
  	
  		$tbHeader[0][2][TABLE_ISI] = "Tanggal SK";
  		$tbHeader[0][2][TABLE_WIDTH] = "10%";
  		
  		$tbHeader[0][3][TABLE_ISI] = "No SK";
  		$tbHeader[0][3][TABLE_WIDTH] = "15%";
  		
  		$tbHeader[0][4][TABLE_ISI] = "Golongan Baru";
  		$tbHeader[0][4][TABLE_WIDTH] = "20%";
  		
  		$tbHeader[0][5][TABLE_ISI] = "Masa Kerja Baru";
  		$tbHeader[0][5][TABLE_WIDTH] = "20%";
  			
  		$tbHeader[0][6][TABLE_ISI] = "TMT Berkala";
  		$tbHeader[0][6][TABLE_WIDTH] = "10%";

  		for($i=0,$counter=0,$n=count($dataBerkala);$i<$n;$i++,$counter=0){
  				
  			$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer" onClick="hapusBerkala(\''.$dataBerkala[$i]["riw_gaji_berkala_id"].'\');" hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0">';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
        
        $tbContent[$i][$counter][TABLE_ISI] = $i+1;
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataBerkala[$i]["riw_gaji_berkala_tgl_sk"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataBerkala[$i]["riw_gaji_berkala_no_sk"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++; 	
      			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataBerkala[$i]["gol_gol"];
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
  			
  			$tbContent[$i][$counter][TABLE_ISI] = $dataBerkala[$i]["gaji_masa_kerja"]."&nbsp;Tahun";
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;
    		
  			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataBerkala[$i]["riw_tgl_berkala"]);
  			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  			$counter++;        
  		}	  
	  return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }				

    // --- AJAX untuk melakukan penyimpanan pada history Jabatan ---- 
    function HistPegawaiSimpan($pgwId,$riwTmtJab,$riwJabLama,$riwJabTgl,$riwJabNoSk) {
		global $dtaccess,$userData; 
	    $riwJabId = $dtaccess->GetTransID();
      	  
		  $sql_jabatan = "insert into gaji.gaji_riwayat_jabatan (riw_jab_id,id_pgw,riw_tmt_jabatan,riw_jab_lama,
               riw_jab_tgl_sk,riw_jab_no_sk) values 
               ('".$riwJabId."',".$pgwId.",'".date_db($riwTmtJab)."',".$riwJabLama.",
               '".date_db($riwJabTgl)."','".$riwJabNoSk."')";
      $rs_jabatan = $dtaccess->Execute($sql_jabatan); 
      
      $sql_jabatan = "update gaji.gaji_pegawai set pgw_jabatan = '".$riwJabLama."', pgw_tmt_jabatan = '".date_db($riwTmtJab)."' where pgw_id = ".QuoteValue(DPE_NUMERIC,$pgwId);
      $rs_jabatan = $dtaccess->Execute($sql_jabatan);
        
		 }  
		 
		 // --- AJAX untuk melakukan penyimpanan pada history Data Anak ---- 
    function HistAnakSimpan($riwAnakNama,$riwAnakKel,$riwAnakKot,$riwAnakTgl,$riwAnakPend,$riwAnakJob,$riwAnakNik,$pgwId) {
		global $dtaccess,$userData; 
	    $riwAnakId = $dtaccess->GetTransID();
      	  
		  $sql_anak = "insert into gaji.gaji_pegawai_data_anak (pgw_anak_id,pgw_anak_nama,pgw_anak_jenis_kelamin,
              pgw_anak_kota_lahir,pgw_anak_tanggal_lahir,pgw_anak_pendidikan,pgw_anak_kerja,pgw_anak_nikah,id_pgw
              ) values 
               ('".$riwAnakId."','".$riwAnakNama."','".$riwAnakKel."','".$riwAnakKot."',
               '".date_db($riwAnakTgl)."','".$riwAnakPend."','".$riwAnakJob."','".$riwAnakNik."',".$pgwId.")";
      $rs_anak = $dtaccess->Execute($sql_anak);   
		 }  
		 
		 // --- AJAX untuk melakukan penyimpanan pada history OrangTua ---- 
    function HistOrtuSimpan($riwAyh,$riwStatAyah,$riwIbu,$riwStatIbu,$pgwId) {
		global $dtaccess,$userData; 
	    $riwOrtuId = $dtaccess->GetTransID();
      	  
		  $sql_ortu = "insert into gaji.gaji_data_ortu (pgw_ortu_id,pgw_nama_ayah,pgw_status_ayah,pgw_nama_ibu,
               pgw_status_ibu,id_pgw) values 
               ('".$riwOrtuId."','".$riwAyh."','".$riwStatAyah."',
               '".$riwIbu."','".$riwStatIbu."',".$pgwId.")";
      $rs_ortu = $dtaccess->Execute($sql_ortu);   
		 }
     
     // --- AJAX untuk melakukan penyimpanan pada history Istri ---- 
    function HistIstriSimpan($riwNip,$riwNipBaru,$riwNama,$riwTglLahir,$riwTglKawin,$riwTglCerai,$riwTglMati,$pgwId) {
		global $dtaccess,$userData; 
	    $riwIstriId = $dtaccess->GetTransID();
      	  
		  $sql_istri = "insert into gaji.gaji_pegawai_suami_istri (pgw_suami_istri_id,pgw_nip_istri_suami,pgw_nip_baru_suami_istri,pgw_nama_istri_suami,
               pgw_tgl_lahir_istrisuami,pgw_tgl_perkawinan,pgw_tgl_cerai,tgl_meninggal,id_pgw) values 
               ('".$riwIstriId."','".$riwNip."','".$riwNipBaru."',
               '".$riwNama."','".date_db($riwTglLahir)."','".date_db($riwTglKawin)."','".$riwTglCerai."','".$riwTglMati."',".$pgwId.")";
      $rs_istri = $dtaccess->Execute($sql_istri);   
		 }  
		 
		       // --- AJAX untuk melakukan penyimpanan pada history Data Pendidikan ---- 
    function HistPendidikanSimpan($pgwId,$riwTingkatPend,$riwLulusan,$riwTahun) {
		global $dtaccess,$userData; 
	    $riwPendidikanId = $dtaccess->GetTransID();
      	  
		  $sql_pendidikan = "insert into gaji.gaji_riwayat_pendidikan (pendidikan_id,id_pgw,pendidikan_tingkat,pendidikan_nama,pendidikan_tahun
                    ) values 
               ('".$riwPendidikanId."',".$pgwId.",'".$riwTingkatPend."','".$riwLulusan."','".$riwTahun."')";
      $rs_pendidikan = $dtaccess->Execute($sql_pendidikan);   
		 } 
		 
		// --- AJAX untuk melakukan penyimpanan pada history Data Penghargaaan ---- 
    function HistPenghargaanSimpan($riwKodePeng,$riwNamaPeng,$riwTahunPeng,$riwKetPeng,$pgwId) {
		global $dtaccess,$userData; 
	    $riwPengId = $dtaccess->GetTransID();
      	  
		  $sql_penghargaan = "insert into gaji.gaji_riwayat_penghargaan (pgw_penghargaan_id,pgw_kode_penghargaan,pgw_nama_penghargaan,pgw_tahun_penghargaan,pgw_ket_penghargaan,id_pgw
              ) values 
               ('".$riwPengId."','".$riwKodePeng."','".$riwNamaPeng."','".$riwTahunPeng."','".$riwKetPeng."',".$pgwId.")";
      $rs_penghargaan = $dtaccess->Execute($sql_penghargaan);   
		 }
     
     // --- AJAX untuk melakukan penyimpanan pada history Data Pemberhentian ---- 
    function HistPemberhentianSimpan($riwJnsBerhenti,$riwNoBerhenti,$riwTglSk,$pgwId) {
		global $dtaccess,$userData; 
	    $riwBerhentiId = $dtaccess->GetTransID();
      	  
		  $sql_berhenti = "insert into gaji.gaji_riwayat_pemberhentian (pgw_pemberhentian_id,pgw_jenis_berhenti,pgw_no_sk_berhenti,pgw_tgl_sk,id_pgw
              ) values 
               ('".$riwBerhentiId."','".$riwJnsBerhenti."','".$riwNoBerhenti."','".date_db($riwTglSk)."',".$pgwId.")";
      $rs_berhenti = $dtaccess->Execute($sql_berhenti);   
		 }  
		 
		 // --- AJAX untuk melakukan penyimpanan pada history Data Cuti ---- 
    function HistCutiSimpan($riwCutiJenis,$riwLamaCuti,$riwCutiMlai,$riwCutiAkhir,$pgwId) {
		global $dtaccess,$userData; 
	    $riwCutiId = $dtaccess->GetTransID();
      	  
		  $sql_cuti = "insert into gaji.gaji_riwayat_cuti (pgw_cuti_id,pgw_jenis_cuti,pgw_lama_cuti,pgw_cuti_mulai,pgw_cuti_akhir,id_pgw
              ) values 
               ('".$riwCutiId."','".$riwCutiJenis."','".$riwLamaCuti."','".date_db($riwCutiMlai)."','".date_db($riwCutiAkhir)."',".$pgwId.")";
      $rs_cuti = $dtaccess->Execute($sql_cuti);   
		 } 
		 
		 // --- AJAX untuk melakukan penyimpanan pada history Hukuman Disiplin ---- 
    function HistHukumanSimpan($pgwId,$riwJenHuk,$riwNoSkhd,$riwHukTgl,$riwTmtHuk,$riwHukTaon,$riwHukGol,$riwHukAkhr,$riwNoPp) {
		global $dtaccess,$userData; 
	    $riwHukId = $dtaccess->GetTransID();
      	  
		  $sql_hukuman = "insert into gaji.gaji_hukuman_pegawai (pgw_jenis_hukuman_id,id_pgw,pgw_jenis_hukuman,pgw_no_skhd,pgw_tanggal_skhd,
               pgw_tmt_hukdis,pgw_masa_hukuman_tahun,gol_ruang,pgw_akhir_hukuman,pgw_no_pp) values 
               ('".$riwHukId."',".$pgwId.",'".$riwJenHuk."','".$riwNoSkhd."','".date_db($riwHukTgl)."',
               '".date_db($riwTmtHuk)."','".$riwHukTaon."','".$riwHukGol."','".date_db($riwHukAkhr)."','".$riwNoPp."')";
      $rs_hukuman = $dtaccess->Execute($sql_hukuman);   
		 }  
		 
     // --- AJAX untuk melakukan penyimpanan pada riwayat Eselon ---- 
    function HistEselonSimpan($riwEslTgl,$riwNoEsl,$riwEslKode,$riwEslNama,$riwTmtEsl,$pgwId) {
		global $dtaccess,$userData; 
	    $riwEselonId = $dtaccess->GetTransID();
      $flag = 'y';	  
		  $sql_Esel = "insert into gaji.gaji_riwayat_eselon (riw_eselon_id,riw_eselon_tgl_sk,riw_eselon_no_sk,riw_eselon_kode,riw_eselon_nama,riw_tmt_eselon,id_pgw,flag_eselon) values 
               ('".$riwEselonId."','".date_db($riwEslTgl)."','".$riwNoEsl."','".$riwEslKode."','".$riwEslNama."','".date_db($riwTmtEsl)."',".$pgwId.",'".$flag."')";
      $rs_Esel = $dtaccess->Execute($sql_Esel);   
		
      $sql= "update gaji.gaji_pegawai set pgw_eselon = '".$riwEslKode."', pgw_tmt_eselon = '".date_db($riwTmtEsl)."' where pgw_id = ".QuoteValue(DPE_NUMERIC,$pgwId);
      $rs = $dtaccess->Execute($sql);
     }	 
     
       // --- AJAX untuk melakukan penyimpanan pada riwayat Kepangkatan ---- 
    function HistGolonganSimpan($pgwId,$riwPangTgl,$riwNoPang,$riwPangBar,$riwTmtPang) {
		global $dtaccess,$userData; 
	    $riwPangkatId = $dtaccess->GetTransID();
      	  
		  $sql_Pangkat = "insert into gaji.gaji_riwayat_kepangkatan (riw_golongan_id,riw_golongan_tgl_sk,riw_golongan_no_sk,riw_golongan_pangkat,riw_tmt_golongan,id_pgw) values 
               ('".$riwPangkatId."','".date_db($riwPangTgl)."','".$riwNoPang."','".$riwPangBar."','".date_db($riwTmtPang)."',".$pgwId.")";
      $rs_Pangkat = $dtaccess->Execute($sql_Pangkat);   
		
      $sql_update = "update gaji.gaji_pegawai set pgw_golongan = '".$riwPangBar."', pgw_tmt_golongan = '".date_db($riwTmtPang)."' where pgw_id = ".QuoteValue(DPE_NUMERIC,$pgwId);
      $rs_update = $dtaccess->Execute($sql_update);
     }	  
          
       // --- AJAX untuk melakukan penyimpanan pada riwayat Gaji Berkala ---- 
    function HistBerkalaSimpan($pgwId,$riwBerkalaTgl,$riwNoBerkala,$riwGolBer,$riwMasBerkala,$riwTmtBerkala) {
		global $dtaccess,$userData; 
	    $riwBerkalaId = $dtaccess->GetTransID();
      	  
		  $sql_Berkala = "insert into gaji.gaji_riwayat_berkala (riw_gaji_berkala_id,riw_gaji_berkala_tgl_sk,riw_gaji_berkala_no_sk,riw_golongan_baru,riw_masa_kerja,riw_tgl_berkala,id_pgw) values 
               ('".$riwBerkalaId."','".date_db($riwBerkalaTgl)."','".$riwNoBerkala."','".$riwGolBer."','".$riwMasBerkala."','".date_db($riwTmtBerkala)."',".$pgwId.")";
      $rs_Berkala = $dtaccess->Execute($sql_Berkala);   
		
      $sql_kala = "update gaji.gaji_pegawai set pgw_golongan = '".$riwGolBer."', id_gaji = '".$riwMasBerkala."', pgw_tmt_berkala = '".date_db($riwTmtBerkala)."' where pgw_id = ".QuoteValue(DPE_NUMERIC,$pgwId);
      $rs_kala = $dtaccess->Execute($sql_kala);
     }	 
	  
    // --- AJAX untuk melakukan penghapusan pada history Jabatan ----
    function HapusJabatan($riwJabId) {
		global $dtaccess,$enc; 
		  $sql_jab = "delete from gaji.gaji_riwayat_jabatan 
            where riw_jab_id = ".QuoteValue(DPE_CHAR,$riwJabId);
      $rs_jab = $dtaccess->Execute($sql_jab);
    }
    
    // --- AJAX untuk melakukan penghapusan pada history Anak ----
    function HapusAnak($riwAnakId) {
		global $dtaccess,$enc; 
		  $sql_anak = "delete from gaji.gaji_pegawai_data_anak 
            where pgw_anak_id = ".QuoteValue(DPE_CHAR,$riwAnakId);
      $rs_anak = $dtaccess->Execute($sql_anak);
    }
    
    // --- AJAX untuk melakukan penghapusan pada history OrangTua ----
    function HapusOrtu($riwOrtuId) {
		global $dtaccess,$enc; 
		  $sql_ortu = "delete from gaji.gaji_data_ortu 
            where pgw_ortu_id = ".QuoteValue(DPE_CHAR,$riwOrtuId);
      $rs_ortu = $dtaccess->Execute($sql_ortu);
    } 
    
    // --- AJAX untuk melakukan penghapusan pada history Istri ----
    function HapusIstri($riwIstriId) {
		global $dtaccess,$enc; 
		  $sql_istri = "delete from gaji.gaji_pegawai_suami_istri 
            where pgw_suami_istri_id = ".QuoteValue(DPE_CHAR,$riwIstriId);
      $rs_istri = $dtaccess->Execute($sql_istri);
    } 
    
       // --- AJAX untuk melakukan penghapusan pada history pendidikan ----
    function HapusPendidikan($riwPendidikanId) {
		global $dtaccess,$enc; 
		  $sql_ilmu = "delete from gaji.gaji_riwayat_pendidikan 
            where pendidikan_id = ".QuoteValue(DPE_CHAR,$riwPendidikanId);
      $rs_pendidikan = $dtaccess->Execute($sql_ilmu);
    }
    
       // --- AJAX untuk melakukan penghapusan pada history penghargaan ----
    function HapusPenghargaan($riwPengId) {
		global $dtaccess,$enc; 
		  $sql_harga = "delete from gaji.gaji_riwayat_penghargaan 
            where pgw_penghargaan_id = ".QuoteValue(DPE_CHAR,$riwPengId);
      $rs_penghargaan = $dtaccess->Execute($sql_harga);
    }
    
       // --- AJAX untuk melakukan penghapusan pada history pemberhentian ----
    function HapusPemberhentian($riwBerhentiId) {
		global $dtaccess,$enc; 
		  $sql_advoice = "delete from gaji.gaji_riwayat_pemberhentian 
            where pgw_pemberhentian_id = ".QuoteValue(DPE_CHAR,$riwBerhentiId);
      $rs_pemberhentian = $dtaccess->Execute($sql_advoice);
    }
    
      // --- AJAX untuk melakukan penghapusan pada history profesi ----
    function HapusProfesi($riwProfId) {
		global $dtaccess,$enc; 
		  $sql_profesi = "delete from gaji.gaji_riwayat_profesi 
            where profesi_id = ".QuoteValue(DPE_CHAR,$riwProfId);
      $rs_profesi = $dtaccess->Execute($sql_profesi);
    }
   
      // --- AJAX untuk melakukan penghapusan pada history Cuti----
    function HapusCuti($riwCutiId) {
		global $dtaccess,$enc; 
		  $sql_cuti = "delete from gaji.gaji_riwayat_cuti 
            where pgw_cuti_id = ".QuoteValue(DPE_CHAR,$riwCutiId);
      $rs_cuti = $dtaccess->Execute($sql_cuti);
    }
    
     // --- AJAX untuk melakukan penghapusan pada history Cuti----
    function HapusHukuman($riwHukId) {
		global $dtaccess,$enc; 
		  $sql_sanksi = "delete from gaji.gaji_hukuman_pegawai 
            where pgw_jenis_hukuman_id = ".QuoteValue(DPE_CHAR,$riwHukId);
      $rs_hukuman = $dtaccess->Execute($sql_sanksi);
    }
    
    // --- AJAX untuk melakukan penghapusan pada riwayat Eselon----
    function HapusEselon($riwEselonId) {
		global $dtaccess,$enc; 
		  $sql_ese = "delete from gaji.gaji_riwayat_eselon 
            where riw_eselon_id = ".QuoteValue(DPE_CHAR,$riwEselonId);
      $rs_eselon = $dtaccess->Execute($sql_ese);
    }
    
    // --- AJAX untuk melakukan penghapusan pada riwayat Pangkat ---
    function HapusGolongan($riwPangkatId) {
		global $dtaccess,$enc; 
		  $sql_gol = "delete from gaji.gaji_riwayat_kepangkatan 
            where riw_golongan_id = ".QuoteValue(DPE_CHAR,$riwPangkatId);
      $rs_golongan = $dtaccess->Execute($sql_gol);
    }
    
      // --- AJAX untuk melakukan penghapusan pada riwayat Pangkat ---
    function HapusBerkala($riwBerkalaId) {
		global $dtaccess,$enc; 
		  $sql_kala = "delete from gaji.gaji_riwayat_berkala 
            where riw_gaji_berkala_id = ".QuoteValue(DPE_CHAR,$riwBerkalaId);
      $rs_berkala = $dtaccess->Execute($sql_kala);
    }
    
     // --- Ambil Variabel ---
     if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
     else $_x_mode = "New";
     
     // create pegawai ID
     if($_POST["pgw_id"])  
     {
       $pgwId = & $_POST["pgw_id"];  
     }
     else if($_GET["pgw_id"])  
     {
       $pgwId = & $_POST["pgw_id"];  
     }
     else 
     { 
        $pgwId = $dtaccess->GetNewID("gaji_pegawai","pgw_id",DB_SCHEMA);
     }

       
     if($_POST["usr_id"])  $usrId = & $_POST["usr_id"];     

     //--- Tampil ---
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $pgwId = $enc->Decode($_GET["id"]);
          }
          $sql = "select * from gaji.gaji_pegawai
               where pgw_id = ".$pgwId;
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["pgw_nip"] = $row_edit["pgw_nip"];
          $_POST["pgw_nip_baru"] = $row_edit["pgw_nip_baru"];
          $_POST["pgw_nama"] = $row_edit["pgw_nama"];
          $_POST["id_satker"] = $row_edit["id_satker"];        
          $_POST["pgw_tempat_lahir"] = $row_edit["pgw_tempat_lahir"];
          $_POST["pgw_tanggal_lahir"] = format_date($row_edit["pgw_tanggal_lahir"]);
          $_POST["id_agama"] = $row_edit["pgw_agama"];
          $_POST["pgw_jenis_kelamin"] = $row_edit["pgw_jenis_kelamin"];
          $_POST["id_stat_peg"] = $row_edit["pgw_status_pegawai"];
          $_POST["id_jenis_kepeg"] = $row_edit["pgw_jenis_kepegawaian"];
          $_POST["id_tunjangan"] = $row_edit["pgw_eselon"];
          $_POST["pgw_kode_guru"] = $row_edit["pgw_kode_guru"];
                $_POST["pgw_status_nikah"] = $row_edit["pgw_status_nikah"];
                $_POST["pgw_jumlah_istri"] = $row_edit["pgw_jumlah_istri"];
                $_POST["pgw_jumlah_anak"] = $row_edit["pgw_jumlah_anak"];
                $_POST["pgw_jumlah_istri_tanggungan"] = $row_edit["pgw_jumlah_istri_tanggungan"];
                $_POST["pgw_jumlah_anak_tanggungan"] = $row_edit["pgw_jumlah_anak_tanggungan"];
                $_POST["pgw_golongan"] = $row_edit["pgw_golongan"];
                $_POST["pgw_gaji_pokok"] = $row_edit["pgw_gaji_pokok"];
                $_POST["pgw_tunjangan_struktural"] = $row_edit["pgw_tunjangan_struktural"];
                $_POST["pgw_tunjangan_fungsional"] = $row_edit["pgw_tunjangan_fungsional"];
                $_POST["pgw_tmt_golongan"] = format_date($row_edit["pgw_tmt_golongan"]);
                $_POST["pgw_tmt_cpns"] = format_date($row_edit["pgw_tmt_cpns"]);
                $_POST["pgw_tmt_pns"] = format_date($row_edit["pgw_tmt_pns"]);
          $_POST["pgw_tmt_eselon"] = format_date($row_edit["pgw_tmt_eselon"]);
          $_POST["pgw_tg_berkala"] = format_date($row_edit["pgw_tg_berkala"]);		
          $_POST["pgw_alamat"] = $row_edit["pgw_alamat"];
          $_POST["pgw_keterangan"] = $row_edit["pgw_keterangan"];
          $_POST["id_jenjang"] = $row_edit["pgw_jenjang_jabatan"];
          $_POST["pgw_masa_kerja"] = $row_edit["id_gaji"];
          $_POST["pgw_foto"] = $row_edit["pgw_foto"];
          $_POST["pgw_kerja_lainnya"] = $row_edit["pgw_kerja_lainnya"];
          $_POST["pgw_penghasilan_lainnya"] = $row_edit["pgw_penghasilan_lainnya"];
          $_POST["pgw_pensiun"] = $row_edit["pgw_pensiun"];
                $_POST["pgw_tanggal_mutasi"] = format_date($row_edit["pgw_tanggal_mutasi"]);
                $_POST["pgw_is_tunjangan_umum"] = $row_edit["pgw_is_tunjangan_umum"];
		      $_POST["pgw_lokasi_kerja"] =  $row_edit["pgw_lokasi_kerja"]; 
                        $_POST["id_agm"] = $row_edit["pgw_agama"];
                        $_POST["id_jabatan"] = $row_edit["pgw_jabatan"];
                        $_POST["pgw_kartu_pegawai"] = $row_edit["pgw_kartu_pegawai"];
                        $_POST["pgw_karis_karsu"] = $row_edit["pgw_karis_karsu"];
                        $_POST["pgw_no_askes"] = $row_edit["pgw_no_askes"];
                        $_POST["id_kantor"] = $row_edit["pgw_kanreg"];
                        $_POST["pgw_kabupaten"] = $row_edit["pgw_kabupaten"];
                        $_POST["pgw_tanggal_sttl"] = format_date($row_edit["pgw_tanggal_sttl"]);
                        $_POST["pgw_tanggal_dokter"] = format_date($row_edit["pgw_tanggal_dokter"]);
                        $_POST["pgw_no_skcpns"] = $row_edit["pgw_no_skcpns"];
                        $_POST["pgw_no_skpns"] = $row_edit["pgw_no_skpns"];
                        $_POST["pgw_no_sttpl"] = $row_edit["pgw_no_sttpl"];
                        $_POST["pgw_srt_dokter"] = $row_edit["pgw_srt_dokter"];
                        $_POST["pgw_tanggal_skcpns"] = format_date($row_edit["pgw_tanggal_skcpns"]);
                        $_POST["pgw_tanggal_skpns"] = format_date($row_edit["pgw_tanggal_skpns"]);
                        $_POST["id_pendidikan"] = $row_edit["id_pendidikan"];
                        $_POST["pgw_gelar_muka"] = $row_edit["pgw_gelar_muka"];
                        $_POST["pgw_gelar_belakang"] = $row_edit["pgw_gelar_belakang"];
                        $_POST["pgw_tmt_jabatan"] = format_date($row_edit["pgw_tmt_jabatan"]);
                       $_POST["pgw_tmt_berkala"] = format_date($row_edit["pgw_tmt_berkala"]);

		
		    //--- tampilan Jasa Pengawai ------------
		$sql = "select * from gaji_pegawai_jasa where id_pgw =".QuoteValue(DPE_CHAR,$pgwId);
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataKunjung = $dtaccess->FetchAll($rs);
		$dtaccess->Clear($rs);
		
		for($i=0,$n=count($dataKunjung);$i<$n;$i++) {
			$_POST["pgw_jasa_nama"][$i+1] = $dataKunjung[$i]["pgw_jasa_nama"];
			$_POST["pgw_jasa_tahun"][$i+1] = $dataKunjung[$i]["pgw_jasa_tahun"];
			$_POST["pgw_jasa_instansi"][$i+1] = $dataKunjung[$i]["pgw_jasa_instansi"];
		}

          //--- tampilan Gaji Tunjangan/Eselon Pengawai ------------
                $sql = "select * from gaji_tunjangan 
                        where tunjangan_id=".$row_edit["pgw_eselon"];
                $rs = $dtaccess->Execute($sql,DB_SCHEMA);
                $dataTunjangan = $dtaccess->Fetch($rs);
          
                $_POST["eselon_name"] = $dataTunjangan["tunjangan_eselon"];
          
          // --- select data gaji_auth_user --- -----------------------
               $sql = "select * from gaji_auth_user 
                       where id_pgw = ".QuoteValue(DPE_NUMERIC,$pgwId);
               $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
               $row_edit = $dtaccess->Fetch($rs_edit);
               $dtaccess->Clear($rs_edit);
               
               $usrId = $row_edit["usr_id"];
               $_POST["usr_loginname"] = $row_edit["usr_loginname"];
               $_POST["usr_name"] = $row_edit["usr_name"];
               $_POST["id_rol"] = $row_edit["id_rol"];
               $_POST["usr_status"] = $row_edit["usr_status"];   
          // --- End select data gaji_auth_user --- //
          
     }
    
     if($_POST["pgw_foto"]) $fotoName = $lokasi."/".$_POST["pgw_foto"];
     else $fotoName = $lokasi."/default.jpg";
     
     if($_x_mode=="New") $privMode = PRIV_CREATE;
     elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
     else $privMode = PRIV_DELETE;
     
     if(!$auth->IsAllowed("data_pegawai",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("data_pegawai",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
  
	//--- Save dan Update Data ------
   if ($_POST["btnSave"] || $_POST["btnUpdate"]) {

          if($_POST["btnUpdate"]){
               $pgwId = & $_POST["pgw_id"];
               $usrId = & $_POST["usr_id"];
               $_x_mode = "Edit";
          }
          

    // --- balikin ke format awal ----
          $_POST["pgw_gaji_pokok"] = StripCurrency($_POST["pgw_gaji_pokok"]);
          $_POST["pgw_tunjangan_struktural"] = StripCurrency($_POST["pgw_tunjangan_struktural"]);
          $_POST["pgw_tunjangan_fungsional"] = StripCurrency($_POST["pgw_tunjangan_fungsional"]);
          $_POST["pgw_penghasilan_lainnya"] = StripCurrency($_POST["pgw_penghasilan_lainnya"]);
          $_POST["pgw_pensiun"] = StripCurrency($_POST["pgw_pensiun"]);                   
                       
          	// ---- Checking Data ---- //	
  
          if(!$_POST["pgw_is_tunjangan_umum"]) $_POST["pgw_is_tunjangan_umum"]="n";	
          if($_POST["btnSave"]) $_POST["usr_status"] = "y";
          elseif(!$_POST["usr_status"]) $_POST["usr_status"] = "n";
          //--- End Checking Data ---//
          
          if($err_code == 0) {
               if(!$pgwId) $pgwId = $dtaccess->GetNewID("gaji_pegawai","pgw_id",DB_SCHEMA);
               
               // ambil data master paramater gaji
               $sql = "select * from gaji_parameter_gaji 
                         where par_gaji_id = '0'";
               $rs = $dtaccess->Execute($sql,DB_SCHEMA);
               $dataParGaji = $dtaccess->Fetch($rs);
               
               // -- ambil data parameter gaji golongan ---
               $sql = "select a.pgw_nip,a.pgw_nip_baru,b.*,c.*,d.* from gaji_pegawai a
                         join gaji_golongan b on a.pgw_golongan = b.gol_id
                         join gaji_golongan_kategori c on b.id_gol_kat = c.gol_kat_id
                         join gaji_parameter_gaji_golongan d on d.id_gol_kat = c.gol_kat_id
                         where a.pgw_id = ".$pgwId;
               $rs = $dtaccess->Execute($sql,DB_SCHEMA);
               $dataParGajiGol = $dtaccess->Fetch($rs);
               
               //--- BAGIAN RUMUS - RUMUS ---               
               // Tunjangan Keluarga
               $tunjanganIstri    = (($dataParGaji["par_gaji_tunj_istri"]/100)*$_POST["pgw_gaji_pokok"])*($_POST["pgw_jumlah_istri_tanggungan"]);
               $tunjanganAnak	   = (($dataParGaji["par_gaji_tunj_anak"]/100)*$_POST["pgw_gaji_pokok"])*($_POST["pgw_jumlah_anak_tanggungan"]);
               $tunjanganKeluarga = ($tunjanganIstri+$tunjanganAnak);
               
               // Tunjangan
               // Tunj. TPP = {TPP(Rp.)}+ {TPP(%)*[Gaji Pokok+Tunj. Istri+Tunj. Anak]}
               $tunjanganTPP = $dataParGajiGol["param_gol_tpp_rp"]+(($dataParGajiGol["param_gol_tpp"]/100)*($_POST["pgw_gaji_pokok"]+$tunjanganKeluarga));
               
               // Tunj. Umum Staff tidak dapat bila sudah dapat Tunj. Struktural tp ad yg khusus shg dikasih cek combo pd data pegawai
               if ($_POST["pgw_is_tunjangan_umum"]=='n') $tunjanganUmumStaf=0;
               else $tunjanganUmumStaf =  $dataParGajiGol["param_gol_tunj_umum_staf"];                              
               $tunjanganIrjaTT   = ($dataParGajiGol["param_gol_tji"]/100)*($_POST["pgw_gaji_pokok"]);
               $tunjanganDaerahTerpencil = ($dataParGajiGol["param_gol_tjdt"]/100)*($_POST["pgw_gaji_pokok"]);
               $tunjanganBeras	= ($dataParGaji["par_gaji_total_beras"]*$dataParGaji["par_gaji_tunj_beras"])*(1+$_POST["pgw_jumlah_istri_tanggungan"]+$_POST["pgw_jumlah_anak_tanggungan"]);
               
               // Lembur
               $uangLembur = $dataParGajiGol["param_gol_lembur"];	
               
               // Jumlah Bruto = Gaji Pokok + Tunjangan Keluarga + Tunjangan
               $jumlahBruto  = ($_POST["pgw_gaji_pokok"])+($tunjanganKeluarga)+($tunjanganTPP+$tunjanganUmumStaf+$tunjanganIrjaTT+$tunjanganDaerahTerpencil+$_POST["pgw_tunjangan_struktural"]+$_POST["pgw_tunjangan_fungsional"]+$tunjanganBeras);
               
               // Jumlah Pengurangan
               if(($jumlahBruto*($dataParGaji["par_gaji_persen_biaya_jab"]/100)) > $dataParGaji["par_gaji_max_jab"]) {
                    $biayaJabatan = $dataParGaji["par_gaji_max_jab"];
               } else {
                    $biayaJabatan = ($jumlahBruto*($dataParGaji["par_gaji_persen_biaya_jab"]/100));
               }
               
               $gajiPokokTunjanganKeluarga = $_POST["pgw_gaji_pokok"]+$tunjanganKeluarga;
               
               if(($gajiPokokTunjanganKeluarga*($dataParGaji["par_gaji_persen_iuran_pensiun"]/100)) > $dataParGaji["par_gaji_max_iuran_pensiun"]) {
                    $iuranPensiun = $dataParGaji["par_gaji_max_iuran_pensiun"];
               } else {
                    $iuranPensiun = ($gajiPokokTunjanganKeluarga*($dataParGaji["par_gaji_persen_iuran_pensiun"]/100));
               }
               
               $jumlahPengurangan = ($biayaJabatan)+($iuranPensiun);
               
               // Neto Sebulan
               $netoSebulan = ($jumlahBruto-$jumlahPengurangan);
               
               // Neto Setahun
               $netoSetahun = $netoSebulan*12;
               
               // PTKP Setahun
               $ptkpSetahun = $dataParGaji["par_gaji_ptkp_peg"]+($dataParGaji["par_gaji_ptkp_istri"]*$_POST["pgw_jumlah_istri_tanggungan"])+($dataParGaji["par_gaji_ptkp_anak"]*$_POST["pgw_jumlah_anak_tanggungan"]);
               
               // Pengurangan Neto Setahun Dengan PTKP Setahun	
               $penguranganNetoSetahunDgnPtkpSetahun = $netoSetahun-$ptkpSetahun;
               
               // PKP Setahun$dbField[65] = "pgw_gelar_belakang";
               if($penguranganNetoSetahunDgnPtkpSetahun > 0)  $pkpSetahun = $penguranganNetoSetahunDgnPtkpSetahun;
               else $pkpSetahun = 0;
               
               $pkpNew = intval($pkpSetahun);
               
               // Pembulatan pada PKP
               $duaAngkaBottom = substr($pkpNew,-2);               
               if($duaAngkaBottom > 0 && $duaAngkaBottom <> 50) $bulatan = 100 - $duaAngkaBottom;
               else $bulatan = 0;               
               $pkpBulat = $pkpNew+$bulatan;
               
               //--- ambil data aturan PPh --
               $sql = "select a.* from gaji_aturan_pph a 
                         order by a.aturan_pph_persen asc"; 
               $rs = $dtaccess->Execute($sql,DB_SCHEMA);
               $dataAturanPPH = $dtaccess->FetchAll($rs);
               
               //--- Rumus2 Slip Gaji --               
               $parameter5 = $pkpBulat-$dataAturanPPH[0]["aturan_pph_pkp_akhir"];
               $parameter10 = $pkpBulat-$dataAturanPPH[1]["aturan_pph_pkp_awal"];
               $parameter15 = $pkpBulat-$dataAturanPPH[2]["aturan_pph_pkp_awal"];
               $parameter25 = $pkpBulat-$dataAturanPPH[3]["aturan_pph_pkp_awal"];
               $parameter35 = $pkpBulat-$dataAturanPPH[4]["aturan_pph_pkp_awal"];
               
               // rumus PPH u/ 5%
               if ($parameter5 < 0) $pph5 = ($dataAturanPPH[0]["aturan_pph_persen"]/100)*$pkpBulat;
               elseif ($parameter5 >= 0) $pph5 = ($dataAturanPPH[0]["aturan_pph_persen"]/100)*$dataAturanPPH[0]["aturan_pph_pkp_akhir"];
               
               // rumus PPH u/ 10%
               if ($parameter10 > 0 && $parameter15 < 0) $pph10 = ($dataAturanPPH[1]["aturan_pph_persen"]/100)*($parameter10);
               elseif ($parameter10 > 0 && $parameter15 > 0) $pph10 = ($dataAturanPPH[1]["aturan_pph_persen"]/100)*($dataAturanPPH[1]["aturan_pph_pkp_akhir"]-$dataAturanPPH[1]["aturan_pph_pkp_awal"]);
               
               // rumus PPH u/ 15%
               if ($parameter15 > 0 && $parameter25 < 0) $pph15 = ($dataAturanPPH[2]["aturan_pph_persen"]/100)*($parameter15);
               elseif ($parameter15 > 0 && $parameter25 > 0) $pph15 = ($dataAturanPPH[2]["aturan_pph_persen"]/100)*($dataAturanPPH[2]["aturan_pph_pkp_akhir"]-$dataAturanPPH[2]["aturan_pph_pkp_awal"]);
               
               // rumus PPH u/ 25%
               if ($parameter25 > 0 && $parameter35 < 0) $pph25 = ($dataAturanPPH[3]["aturan_pph_persen"]/100)*($parameter25);
               elseif ($parameter25 > 0 && $parameter35 > 0) $pph25 = ($dataAturanPPH[3]["aturan_pph_persen"]/100)*($dataAturanPPH[3]["aturan_pph_pkp_akhir"]-$dataAturanPPH[3]["aturan_pph_pkp_awal"]);
               
               // rumus PPH u/ 35%
               if ($parameter35 > 0) $pph35 = ($dataAturanPPH[4]["aturan_pph_persen"]/100)*($parameter35);
               
               $pphSetahun = $pph5+$pph10+$pph15+$pph25+$pph35;
               
               $tunjanganPPH = $pphSebulan = $pphSetahun/12;
               
               // Jumlah Kotor = Gaji Pokok + Tunjangan
               $jumlahKotorLama = $_POST["pgw_gaji_pokok"]+($tunjanganKeluarga+$tunjanganTPP+$tunjanganIrjaTT+$tunjanganDaerahTerpencil+$_POST["pgw_tunjangan_struktural"]+$_POST["pgw_tunjangan_fungsional"]+$tunjanganBeras+$tunjanganPPH+$tunjanganUmumStaf);
               
               if($jumlahKotorLama < 1000000) $tambahanTunjanganUmum = (1000000-$jumlahKotorLama);
               else $tambahanTunjanganUmum = 0;
               
               // Jumlah Penghasilan Kotor = Gaji Pokok + Tunjangan (setelah dicek apakah kurang dari 1 juta)
               $jumlahKotorBaru = $jumlahKotorLama+$tambahanTunjanganUmum;
               
               // Potongan
               $potBeras = 0;
               // rumus PFK 10%
               $iuranPFK  = ($_POST["pgw_gaji_pokok"]+$tunjanganKeluarga)*(10/100);
               $pajak     = $pphSebulan;
               $sewaRumah = $dataPotonganGaji["pot_gaji_sewa"];
               $hutang    = $dataPotonganGaji["pot_gaji_hutang"];
               
               // Tabungan Rumah
               $tabunganRumah = $dataParGajiGol["param_gol_tab_rumah"];
               $lainLain  = $dataPotonganGaji["pot_gaji_lain_lain"];
               
               // Potongan Lainnya
               $potonganAmal    = $dataParGajiGol["param_gol_amal"];
               $potonganLainnya = $potonganAmal+$lainLain;
               
               // Jumlah Potongan + PPH Sebulan
               $jumlahPotongan = ($potBeras+$iuranPFK+$pajak+$sewaRumah+$hutang+$tabunganRumah+$potonganLainnya+$pphSebulan);
               
               // Jumlah Bersih = Jumlah Kotor Baru - Jumlah Potongan
               $jumlahBersih = $jumlahKotorBaru-$jumlahPotongan;
               $jumBersih = intval($jumlahBersih);
               // Pembulatan di Slip Gaji
               $duaAngkaBelakang = substr($jumBersih,-2);
               
               if ($duaAngkaBelakang > 0 && $duaAngkaBelakang <> 50) $pembulatan = 100 - $duaAngkaBelakang;
               elseif ($duaAngkaBelakang == 50) $pembulatan = 50;
               else $pembulatan = 0;
               
               //--- Jumlah Bersih = Jumlah Yang Dibayarkan ---
               // Jumlah Yang Dibayarkan = jumlah bersih + pembulatan(setelah dibulatkan)
               $jumlahYgDibayarkan = (intval($jumlahBersih))+$pembulatan;
               
               // u/ set bole dikosongi
               if(!$_POST["pgw_tunjangan_struktural"]) $_POST["pgw_tunjangan_struktural"] = "0";
               if(!$_POST["pgw_tunjangan_fungsional"]) $_POST["pgw_tunjangan_fungsional"] = "0";
               if(!$_POST["id_tunjangan"]) $_POST["id_tunjangan"] = "0";
               if(!$_POST["pgw_jumlah_istri"]) $_POST["pgw_jumlah_istri"] = "0";
               if(!$_POST["pgw_jumlah_anak"]) $_POST["pgw_jumlah_anak"] = "0";
               if(!$_POST["pgw_jumlah_istri_tanggungan"]) $_POST["pgw_jumlah_istri_tanggungan"] = "0";
               if(!$_POST["pgw_jumlah_anak_tanggungan"]) $_POST["pgw_jumlah_anak_tanggungan"] = "0";
               if(!$_POST["pgw_kerja_lainnya"]) $_POST["pgw_kerja_lainnya"] = "";
               if(!$_POST["pgw_penghasilan_lainnya"]) $_POST["pgw_penghasilan_lainnya"] = "0";
               if(!$_POST["pgw_pensiun"]) $_POST["pgw_pensiun"] = "0";
               if(!$_POST["pgw_gaji_pokok"]) $_POST["pgw_gaji_pokok"] = 0;
               if($_POST["pgw_masa_kerja"]=="--") $_POST["pgw_masa_kerja"] ="null";
                              
               $dbTable = "gaji_pegawai";
               
                $dbField[0] = "pgw_id";   // PK
               $dbField[1] = "pgw_nip";
               $dbField[2] = "pgw_nama";
               $dbField[3] = "id_satker";
               $dbField[4] = "pgw_tempat_lahir";
               $dbField[5] = "pgw_tanggal_lahir";
               $dbField[6] = "pgw_agama";
               $dbField[7] = "pgw_jenis_kelamin";
               $dbField[8] = "pgw_status_pegawai";
               $dbField[9] = "pgw_eselon";
               $dbField[10] = "pgw_kode_guru";
     $dbField[11] = "pgw_status_nikah";
     $dbField[12] = "pgw_jumlah_istri";
     $dbField[13] = "pgw_jumlah_anak";
     $dbField[14] = "pgw_jenjang_jabatan";
     $dbField[15] = "pgw_golongan";
     $dbField[16] = "pgw_gaji_pokok";
     $dbField[17] = "pgw_tunjangan_struktural";
     $dbField[18] = "pgw_tunjangan_fungsional";
     $dbField[19] = "pgw_tmt_golongan";
     $dbField[20] = "pgw_tmt_eselon";
               $dbField[21] = "pgw_tg_berkala";
               $dbField[22] = "pgw_alamat";
               $dbField[23] = "pgw_keterangan";
               $dbField[24] = "pgw_foto";
               $dbField[25] = "id_gaji";
               $dbField[26] = "pgw_kerja_lainnya";
               $dbField[27] = "pgw_penghasilan_lainnya";
               $dbField[28] = "pgw_pensiun";
               $dbField[29] = "pgw_jumlah_istri_tanggungan";
               $dbField[30] = "pgw_jumlah_anak_tanggungan";
     $dbField[31] = "pgw_tanggal_mutasi";
     $dbField[32] = "pgw_tunjangan_istri";
     $dbField[33] = "pgw_tunjangan_anak";
     $dbField[34] = "pgw_tunjangan_tpp";
     $dbField[35] = "pgw_tunjangan_umum";
     $dbField[36] = "pgw_tunjangan_beras";
     $dbField[37] = "pgw_tunjangan_pajak";
     $dbField[38] = "pgw_iuran_pfk";
     $dbField[39] = "pgw_is_tunjangan_umum";
     $dbField[40] = "pgw_who_update";
     $dbField[41] = "pgw_last_update";
     $dbField[42] = "pgw_nip_baru";
     $dbField[43] = "pgw_tmt_cpns";
     $dbField[44] = "pgw_tmt_pns";
     $dbField[45] = "pgw_jenis_kepegawaian";
     $dbField[46] = "pgw_jabatan";
     $dbField[47] = "pgw_kartu_pegawai";
     $dbField[48] = "pgw_karis_karsu";
             $dbField[49] = "pgw_no_askes";
             $dbField[50] = "pgw_lokasi_kerja";
             $dbField[51] = "pgw_kanreg";
             $dbField[52] = "pgw_kabupaten";
             $dbField[53] = "pgw_tanggal_sttpl";
             $dbField[54] = "pgw_tanggal_dokter";
                  $dbField[55] = "pgw_no_skcpns";
                  $dbField[56] = "pgw_no_skpns";
                  $dbField[57] = "pgw_no_sttpl";
                  $dbField[58] = "pgw_srt_dokter";
                  $dbField[59] = "pgw_tanggal_skpns";
                  $dbField[60] = "pgw_tanggal_skcpns";
                  $dbField[61] = "id_pendidikan";
                  $dbField[62] = "pgw_has_been_update";
                  $dbField[63] = "pgw_when_update_pegawai";
                  $dbField[64] = "pgw_gelar_muka";
                  $dbField[65] = "pgw_gelar_belakang";
                  $dbField[66] = "pgw_tmt_jabatan";
                  $dbField[67] = "pgw_tmt_berkala";


                  if($_POST["id_pendidikan"]){
                  $status = 'y';
                  }
                  if($_POST["id_pendidikan"]){
                  $time=date("Y-m-d H:i:s");
                  }   
                 
             $dbValue[0] = QuoteValue(DPE_CHAR,$pgwId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pgw_nip"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["pgw_nama"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_satker"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["pgw_tempat_lahir"]);
               $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_lahir"]));
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_agm"]);
               $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["pgw_jenis_kelamin"]);
               $dbValue[8] = QuoteValue(DPE_NUMERIC,$_POST["id_stat_peg"]);
               $dbValue[9] = QuoteValue(DPE_NUMERIC,$_POST["id_tunjangan"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["pgw_kode_guru"]);
     $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["pgw_status_nikah"]);            
     $dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["pgw_jumlah_istri"]);
     $dbValue[13] = QuoteValue(DPE_NUMERIC,$_POST["pgw_jumlah_anak"]);
     $dbValue[14] = QuoteValue(DPE_NUMERIC,$_POST["id_jenjang"]);
     $dbValue[15] = QuoteValue(DPE_NUMERIC,$_POST["pgw_golongan"]);
     $dbValue[16] = QuoteValue(DPE_NUMERIC,$_POST["pgw_gaji_pokok"]);
     $dbValue[17] = QuoteValue(DPE_NUMERIC,$_POST["pgw_tunjangan_struktural"]);
     $dbValue[18] = QuoteValue(DPE_NUMERIC,$_POST["pgw_tunjangan_fungsional"]);
     $dbValue[19] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tmt_golongan"]));
     $dbValue[20] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tmt_eselon"]));
               $dbValue[21] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tg_berkala"]));
               $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["pgw_alamat"]);
               $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["pgw_keterangan"]);
               $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["pgw_foto"]);
               $dbValue[25] = QuoteValue(DPE_NUMERIC,$_POST["pgw_masa_kerja"]);
               $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["pgw_kerja_lainnya"]);
               $dbValue[27] = QuoteValue(DPE_NUMERIC,$_POST["pgw_penghasilan_lainnya"]);
               $dbValue[28] = QuoteValue(DPE_NUMERIC,$_POST["pgw_pensiun"]);
               $dbValue[29] = QuoteValue(DPE_NUMERIC,$_POST["pgw_jumlah_istri_tanggungan"]);
               $dbValue[30] = QuoteValue(DPE_NUMERIC,$_POST["pgw_jumlah_anak_tanggungan"]);
     $dbValue[31] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_mutasi"]));
     $dbValue[32] = QuoteValue(DPE_NUMERIC,round($tunjanganIstri));
     $dbValue[33] = QuoteValue(DPE_NUMERIC,round($tunjanganAnak));
     $dbValue[34] = QuoteValue(DPE_NUMERIC,round($tunjanganTPP));
     $dbValue[35] = QuoteValue(DPE_NUMERIC,$tunjanganUmumStaf);
     $dbValue[36] = QuoteValue(DPE_NUMERIC,round($tunjanganBeras));
     $dbValue[37] = QuoteValue(DPE_NUMERIC,round($tunjanganPPH));
     $dbValue[38] = QuoteValue(DPE_NUMERIC,round($iuranPFK));
     $dbValue[39] = QuoteValue(DPE_CHAR,$_POST["pgw_is_tunjangan_umum"]);
     $dbValue[40] = QuoteValue(DPE_CHAR,$username);
               $dbValue[41] = QuoteValue(DPE_DATE,$skr);
     $dbValue[42] = QuoteValue(DPE_CHAR,$_POST["pgw_nip_baru"]);
     $dbValue[43] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tmt_cpns"]));
     $dbValue[44] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tmt_pns"]));
     $dbValue[45] = QuoteValue(DPE_NUMERIC,$_POST["id_jenis_kepeg"]);
     $dbValue[46] = QuoteValue(DPE_NUMERIC,$_POST["id_jabatan"]);
     $dbValue[47] = QuoteValue(DPE_CHAR,$_POST["pgw_kartu_pegawai"]);
     $dbValue[48] = QuoteValue(DPE_CHAR,$_POST["pgw_karis_karsu"]);
               $dbValue[49] = QuoteValue(DPE_CHAR,$_POST["pgw_no_askes"]);
               $dbValue[50] = QuoteValue(DPE_CHAR,$_POST["pgw_lokasi_kerja"]);
               $dbValue[51] = QuoteValue(DPE_NUMERIC,$_POST["id_kantor"]);
               $dbValue[52] = QuoteValue(DPE_CHAR,$_POST["pgw_kabupaten"]);
               $dbValue[53] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_sttpl"]));
               $dbValue[54] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_dokter"]));
               $dbValue[55] = QuoteValue(DPE_CHAR,$_POST["pgw_no_skcpns"]);
               $dbValue[56] = QuoteValue(DPE_CHAR,$_POST["pgw_no_skpns"]);
               $dbValue[57] = QuoteValue(DPE_CHAR,$_POST["pgw_no_sttpl"]);
               $dbValue[58] = QuoteValue(DPE_CHAR,$_POST["pgw_srt_dokter"]);
     $dbValue[59] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_skpns"]));
     $dbValue[60] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tanggal_skcpns"]));
     $dbValue[61] = QuoteValue(DPE_NUMERIC,$_POST["id_pendidikan"]);
     $dbValue[62] = QuoteValue(DPE_CHAR,$status);
     $dbValue[63] = QuoteValue(DPE_DATE,$time);
$dbValue[64] = QuoteValue(DPE_CHAR,$_POST["pgw_gelar_muka"]);
$dbValue[65] = QuoteValue(DPE_CHAR,$_POST["pgw_gelar_belakang"]);
$dbValue[66] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tmt_jabatan"]));
$dbValue[67] = QuoteValue(DPE_DATE,date_db($_POST["pgw_tmt_berkala"]));         
                        
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);           
               
               // -- buat masukin data kedb
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	          
               } elseif ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               // --- insert atau update data di gaji_auth_user --- //
       /*             $dbTable = "gaji.gaji_auth_user";
                    
                    $dbField[0] = "usr_id";   // PK
                    $dbField[1] = "usr_loginname";
                    $dbField[2] = "usr_name";
                    $dbField[3] = "id_rol";
                    $dbField[4] = "usr_status";
                    $dbField[5] = "id_pgw";
                    if($_POST["is_password"]) $dbField[6] = "usr_password";
                    
                    if(!$usrId) $dbValue[0] = QuoteValue(DPE_NUMERIC,$dtaccess->GetNewID("gaji_auth_user","usr_id",DB_SCHEMA));
                    else $dbValue[0] = QuoteValue(DPE_NUMERIC,$usrId);
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["usr_loginname"]);
                    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
                    $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_rol"]);
                    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["usr_status"]);
                    $dbValue[5] = QuoteValue(DPE_NUMERIC,$pgwId);
                    if($_POST["is_password"]) $dbValue[6] = QuoteValue(DPE_CHAR,md5($_POST["usr_password"]));
                    
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                    
                    if (!$usrId) {
                         $dtmodel->Insert() or die("insert  error");	    
                    } else {
                         $dtmodel->Update() or die("update  error");	
                    }
                    
                    unset($dtmodel);
                    unset($dbField);
                    unset($dbValue);
                    unset($dbKey);                                               */
               // --- End insert atau update data di gaji_auth_user --- //
       
               header("location:pegawai_view.php");
               exit();
              // --- kembali ke tampilan awal --- //  
               
          }
     }    
      //  print_r($dbValue);
      //  die();     
     

     if ($_POST["btnDelete"]) {
          $pgwId = & $_POST["cbDelete"];
          for($i=0,$n=count($pgwId);$i<$n;$i++){
               $sql = "delete from gaji_pegawai where pgw_id = ".$pgwId[$i];
               $dtaccess->Execute($sql,DB_SCHEMA);
          }
          header("location:pegawai_view.php");
          exit();    
     }

 
     // -- master Kantor Regional ---
	   $sql = "select * from gaji_master_kanreg order by pgw_kanreg_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataKantorReg = $dtaccess->FetchAll($rs);     
     
     // -- cari jenis Kepegawaian ---
	   $sql = "select * from gaji_master_jenis_pegawai order by pgw_jenis_pegawai_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataJenisPegawai = $dtaccess->FetchAll($rs);
     
     // -- cari SKPD ---
	   $sql = "select * from gaji.gaji_satker satker_id where satker_sub is null order by satker_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSatker = $dtaccess->FetchAll($rs);
     
     // cari agama ----
     $sql = "select * from gaji_master_agama order by agm_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataAgama = $dtaccess->FetchAll($rs);
     
     // cari golongan ---- 
     $sql = "select * from gaji_golongan order by gol_kode";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataGolongan = $dtaccess->FetchAll($rs);
     
     // cari gaji ----
     $sql = "select * from gaji_master_status_pegawai";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataStatusPegawai = $dtaccess->FetchAll($rs);

     // cari gaji tunjangan ----
     $sql = "select * from gaji_tunjangan";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataKodeEselon = $dtaccess->FetchAll($rs);
     
     // cari jejang jabatan ---
     $sql = "select * from gaji_master_jenjang_jabatan ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataJenjangJabatan = $dtaccess->FetchAll($rs);
     
     // cari jabatan ----
     $sql = "select * from gaji_jabatan order by jabatan_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataMasterJabatan = $dtaccess->FetchAll($rs);
     
     //--- cari masa kerja ---
     $sql = "select a.*,b.* from gaji_gaji a 
               join gaji_golongan b on a.id_gol = b.gol_id 
               order by b.gol_kode, a.gaji_masa_kerja";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataMasaKerja = $dtaccess->FetchAll($rs);
     
     //--cari potongan untuk CPNS --
     $sql = "select par_gaji_cpns from gaji_parameter_gaji 
               where par_gaji_id = '0'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataCPNS = $dtaccess->Fetch($rs);
     
     // cari user rule ---
     $sql = "select * from gaji_auth_role 
               where rol_id <> 1";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataJabatan = $dtaccess->FetchAll($rs);
     
      // cari CUTI ---
     $sql = "select * from gaji_master_cuti 
               order by pgw_master_cuti_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataCutiPeg = $dtaccess->FetchAll($rs);
     
       // cari data pendidikan ---
     $sql = "select * from gaji_master_tingkat_pendidikan 
               order by tingkat_pendidikan_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTingkatPendidikan = $dtaccess->FetchAll($rs);
 
?>



<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/elements.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/func_curr.js"></script>
<!-- link css untuk tampilan tab ala winXP -->
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>/library/css/winxp.css" />
<!-- link jscript untuk fungsi-fungsi tab dasar -->
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>/library/script/listener.js"></script> 
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>/library/script/tabs.js"></script>


<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/pmb.css">
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $APLICATION_ROOT;?>library/script/jscalendar/css/calendar-system.css" title="calendar-system" />
<!-- calendar script -->
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/jscalendar/calendar.js"></script>
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/jscalendar/calendar-setup.js"></script>
<!-- end -->

<?php echo $view->RenderBody("inventori.css",false); ?>
<script language="Javascript">
<? $plx->Run(); ?>

function CheckSimpan(frm) { 
     
     if(!frm.pgw_nip_baru.value) {
          alert('NIP Baru Harus Diisi');
          return false;
     }
     
     if(!frm.pgw_nama.value) {
          alert('Nama Harus Diisi');
          return false;
     }    
     
     if(!frm.id_satker.value) {
          alert('SKPD Harus Dipilih');
          return false;
     }
     
     if(!frm.id_pendidikan.value) {
          alert('Pendidikan Terakhir Harus Dipilih');
          return false;
     }
 
     if(!frm.id_stat_peg.value) {
          alert('Status Pegawai Harus Dipilih');
          return false;
     }
     
     if(!frm.id_tunjangan.value) {
          alert('Kode Eselon Harus Dipilih');
          return false;
     }  
        
     if(!frm.pgw_alamat.value) {
          alert('Alamat Harus Diisi');
          return false;
     }     
     
     if(!frm.pgw_golongan.value) {
          alert('Golongan Harus Dipilih');
          return false;
     }

     if(!frm.id_jenjang.value) {
          alert('Jenis Jabatan Harus Dipilih');
          return false;
     }

     if(!frm.pgw_masa_kerja.value) {
     alert('Masa Kerja Harus Dipilih');
     return false;
     }
 
  <?php if($_x_mode == "New"){?>    
		
    if(GetNip(frm.pgw_nip_baru.value,'type=r')){ 
		alert('NIP Baru Telah Terdaftar');
		return false; }
		
	<?php } ?>  	
	} 
	
var mTimer,mulai=0;
var pgwId = '<?php echo $pgwId; ?>';

// -- Nampilkan History Jabatan -- //
function timer(){
var sqlhapus;
clearInterval(mTimer);      
   
 if((mulai % 1) == 0){
 GetDataJabatan(pgwId,'target=dv_tabel'); 
 } 
 mulai++;
 mTimer = setTimeout("timer()", 1000);
}
timer();

function tambahHistPegawai(pgwId) 
{
  //ambil data pada text box dan Combo Box
  var riw_tmt_jabatan=document.getElementById('riw_tmt_jabatan').value;
  var riw_jab_lama=document.getElementById('riw_jab_lama').value;
  var riw_jab_tgl_sk=document.getElementById('riw_jab_tgl_sk').value;
  var riw_jab_no_sk=document.getElementById('riw_jab_no_sk').value;
  
  if (!riw_jab_tgl_sk) //error Checking Apabila Tanggal SK tidak diisi
  { 
   alert('Tanggal SK Harus Diisi')
  }
  else if (!riw_jab_no_sk) //error Checking Apabila Tanggal SK tidak diisi
  { 
   alert('Nomer SK Harus Diisi')
  }
  else if (riw_jab_lama=='--') //error Checking Apabila Jabatan Lama tidak diisi
  { 
   alert('Jabatan Lama Harus Diisi')
  }
  else if (!riw_tmt_jabatan) //error Checking Apabila Jabatan Baru tidak diisi
  { 
   alert('TMT Jabatan Harus Diisi')
  }
  else
  {
    HistPegawaiSimpan(pgwId,riw_tmt_jabatan,riw_jab_lama,riw_jab_tgl_sk,riw_jab_no_sk,'target=dv_tabel');
    document.getElementById('riw_jab_tgl_sk').value="";
    document.getElementById('riw_jab_no_sk').value="";
    document.getElementById('riw_jab_lama').value="--";
    document.getElementById('riw_tmt_jabatan').value="";   
  }
} 
function hapusJabatan(riw_jab_id) {
    HapusJabatan(riw_jab_id,'target=dv_tabel');
} 

               // -- Nampilkan History Anak -- //
var mTimer1,Dmulai=0;
var pgwId = '<?php echo $pgwId; ?>';
function timer1(){
var sqlhapus1;
 clearInterval(mTimer1);      
   
   if((Dmulai % 1) == 0){
 GetDataAnak(pgwId,'target=dv_tabel_1');
 }
 Dmulai++;
 mTimer1 = setTimeout("timer1()", 1000);

}
timer1(); 


function tambahHistAnak(pgwId) 
{
  //ambil data 
  var pgw_anak_nama=document.getElementById('pgw_anak_nama').value;
  var pgw_anak_jenis_kelamin=document.getElementById('pgw_anak_jenis_kelamin').value;
  var pgw_anak_kota_lahir=document.getElementById('pgw_anak_kota_lahir').value;
  var pgw_anak_tanggal_lahir=document.getElementById('pgw_anak_tanggal_lahir').value;
  var pgw_anak_pendidikan=document.getElementById('pgw_anak_pendidikan').value;
  var pgw_anak_kerja=document.getElementById('pgw_anak_kerja').value;
  var pgw_anak_nikah=document.getElementById('pgw_anak_nikah').value;
  
  if (!pgw_anak_nama) //error Checking Apabila Nama Aanak tidak diisi
  { 
   alert('Nama Anak Harus Diisi')
  }
  else if (!pgw_anak_kota_lahir) //error Checking Kota Lahir tidak diisi
  { 
   alert('Tempat Lahir Anak Harus Diisi')
  }
  else if (!pgw_anak_tanggal_lahir) //error Checking Tanggal Lahir tidak diisi
  { 
   alert('Tanggal Lahir Anak Harus Diisi')
  }
  else if (!pgw_anak_pendidikan) //error Checking Apabila Pendidikan tidak diisi
  { 
   alert('Pendidikan Anak Harus Diisi')
  }
  else if (!pgw_anak_kerja) //error Checking Apabila Kerja tidak diisi
  { 
   alert('Pekerjaan Anak Harus Diisi')
  }
  else
  {
    HistAnakSimpan(pgw_anak_nama,pgw_anak_jenis_kelamin,pgw_anak_kota_lahir,pgw_anak_tanggal_lahir,pgw_anak_pendidikan
    ,pgw_anak_kerja,pgw_anak_nikah,pgwId,'target=dv_tabel_1');
    document.getElementById('pgw_anak_nama').value="";
    document.getElementById('pgw_anak_jenis_kelamin').value="";
    document.getElementById('pgw_anak_kota_lahir').value="";
    document.getElementById('pgw_anak_tanggal_lahir').value="";
    document.getElementById('pgw_anak_pendidikan').value="";
    document.getElementById('pgw_anak_kerja').value="";
    document.getElementById('pgw_anak_nikah').value="";    
  }
} 
function hapusAnak(pgw_anak_id) {
    HapusAnak(pgw_anak_id,'target=dv_tabel_1');
}  


// -- Nampilkan History Ortu -- //
var mTimer2,mulai1=0;
var pgwId = '<?php echo $pgwId; ?>';
function timer2(){
var sqlhapus2;
 clearInterval(mTimer2);      
   
   if((mulai1 % 1) == 0){
 GetDataOrtu(pgwId,'target=dv_tabel_2');
 }
 mulai1++;
 mTimer2 = setTimeout("timer2()", 1000);

}
timer2(); 
function tambahHistOrtu(pgwId) 
{
  //ambil data 
  var pgw_nama_ayah=document.getElementById('pgw_nama_ayah').value;
  var pgw_status_ayah=document.getElementById('pgw_status_ayah').value;
  var pgw_nama_ibu=document.getElementById('pgw_nama_ibu').value;
  var pgw_status_ibu=document.getElementById('pgw_status_ibu').value;
  
  if (!pgw_nama_ayah) 
  { 
   alert('Nama Ayah Harus Diisi')
  }
  else if (!pgw_nama_ibu) 
  { 
   alert('Nama Ibu Harus Diisi')
  }
  else
  {
    HistOrtuSimpan(pgw_nama_ayah,pgw_status_ayah,pgw_nama_ibu,pgw_status_ibu,pgwId,'target=dv_tabel_2');
    document.getElementById('pgw_nama_ayah').value="";
    document.getElementById('pgw_status_ayah').value="";
    document.getElementById('pgw_nama_ibu').value="";
    document.getElementById('pgw_status_ibu').value="";    
  }
} 
function hapusOrtu(pgw_ortu_id) {
    HapusOrtu(pgw_ortu_id,'target=dv_tabel_2');
}


  
// -- Nampilkan History Istri -- //
var mTimer3,mulai2=0;
var pgwId = '<?php echo $pgwId; ?>';
function timer3(){
var sqlhapus3;
 clearInterval(mTimer3);      
   
  if((mulai2 % 1) == 0){
 GetDataIstri(pgwId,'target=dv_tabel_3');
 }
 mulai2++;
 mTimer3 = setTimeout("timer3()", 1000);

}
timer3(); 

function tambahHistIstri(pgwId) 
{
  //ambil data 
  var pgw_nip_istri_suami=document.getElementById('pgw_nip_istri_suami').value;
  var pgw_nip_baru_suami_istri=document.getElementById('pgw_nip_baru_suami_istri').value;
  var pgw_nama_istri_suami=document.getElementById('pgw_nama_istri_suami').value;
  var pgw_tgl_lahir_istrisuami=document.getElementById('pgw_tgl_lahir_istrisuami').value;
  var pgw_tgl_perkawinan=document.getElementById('pgw_tgl_perkawinan').value;
  var pgw_tgl_cerai=document.getElementById('pgw_tgl_cerai').value;
  var tgl_meninggal=document.getElementById('tgl_meninggal').value;
  
  if (!pgw_nip_istri_suami)
  { 
   alert('NIP  Harus Diisi')
  }
    else if (!pgw_nip_baru_suami_istri)
  { 
   alert('NIP Baru Harus Diisi')
  }
  else if (!pgw_anak_tanggal_lahir) 
  { 
   alert('Tanggal Lahir Anak Harus Diisi')
  }
  else if (!pgw_nama_istri_suami) 
  { 
   alert('Nama Harus Diisi')
  }
  else if (!pgw_tgl_lahir_istrisuami) 
  { 
   alert('Tanggal Lahir Harus Diisi')
  }
  else if (!pgw_tgl_perkawinan) 
  { 
   alert('Tanggal Perkawinan Harus Diisi')
  }  
  else
  {
    HistIstriSimpan(pgw_nip_istri_suami,pgw_nip_baru_suami_istri,pgw_nama_istri_suami,pgw_tgl_lahir_istrisuami,pgw_tgl_perkawinan
    ,pgw_tgl_cerai,tgl_meninggal,pgwId,'target=dv_tabel_3');
    document.getElementById('pgw_nip_istri_suami').value="";
    document.getElementById('pgw_nip_baru_suami_istri').value="";
    document.getElementById('pgw_nama_istri_suami').value="";
    document.getElementById('pgw_tgl_lahir_istrisuami').value="";
    document.getElementById('pgw_tgl_perkawinan').value="";
    document.getElementById('pgw_tgl_cerai').value="";
    document.getElementById('tgl_meninggal').value="";    
  }
} 
function hapusIstri(pgw_suami_istri_id) {
    HapusIstri(pgw_suami_istri_id,'target=dv_tabel_3');
}


// --Nampilkan History Pendidikan-- //
var mTimer4,mulai3=0;
var pgwId = '<?php echo $pgwId; ?>';
function timer4(){
var sqlhapus4;
 clearInterval(mTimer4);      
   
   if((mulai3 % 1) == 0){
 GetDataPendidikan(pgwId,'target=dv_tabel_4');
 }
 mulai3++;
 mTimer4 = setTimeout("timer4()", 1000);

}
timer4(); 

function tambahHistPendidikan(pgwId) 
{
  //ambil data
  var pendidikan_tingkat=document.getElementById('pendidikan_tingkat').value;
  var pendidikan_nama=document.getElementById('pendidikan_nama').value;
  var pendidikan_tahun=document.getElementById('pendidikan_tahun').value;
  
  if (!pendidikan_tingkat)
  { 
   alert('Tingkat Pendidikan Harus Diisi')
  }
    else if (!pendidikan_nama)
  { 
   alert('Nama Pendidikan Harus Diisi')
  }
  else if (!pendidikan_tahun)
  { 
   alert('Tahun Kelulusan Harus Diisi')
  } 
  else
  {
    HistPendidikanSimpan(pgwId,pendidikan_tingkat,pendidikan_nama,pendidikan_tahun,'target=dv_tabel_4');
    document.getElementById('pendidikan_tingkat').value="";
    document.getElementById('pendidikan_nama').value="";
    document.getElementById('pendidikan_tahun').value=""; 
  }
} 

function hapusPendidikan(pendidikan_id) {
    HapusPendidikan(pendidikan_id,'target=dv_tabel_4');
}                   
  

// --Nampilkan History Penghargaan-- //
var mTimer5,mulai4=0;
var pgwId = '<?php echo $pgwId; ?>';
function timer5(){
var sqlhapus5;
 clearInterval(mTimer5);      
   
  if((mulai4 % 1) == 0){
 GetDataPenghargaan(pgwId,'target=dv_tabel_5');
 }
 mulai4++;
 mTimer5 = setTimeout("timer5()", 1000);

}
timer5(); 

function tambahHistPenghargaan(pgwId) 
{
  //ambil data 
  var pgw_kode_penghargaan=document.getElementById('pgw_kode_penghargaan').value;
  var pgw_nama_penghargaan=document.getElementById('pgw_nama_penghargaan').value;
  var pgw_tahun_penghargaan=document.getElementById('pgw_tahun_penghargaan').value;
  var pgw_ket_penghargaan=document.getElementById('pgw_ket_penghargaan').value; 
  if (!pgw_kode_penghargaan) //error Checking Apabila Tanggal SK tidak diisi
  { 
   alert('Kode Penghargaan Harus Diisi')
  }
    else if (!pgw_nama_penghargaan) //error Checking Apabila Jabatan Baru tidak diisi
  { 
   alert('Nama Penghargaan Harus Diisi')
  }
  else if (!pgw_tahun_penghargaan) //error Checking Apabila Jabatan Lama tidak diisi
  { 
   alert('Tahun Penghargaan Harus Diisi')
  }  
  else
  {
    HistPenghargaanSimpan(pgw_kode_penghargaan,pgw_nama_penghargaan,pgw_tahun_penghargaan,pgw_ket_penghargaan,pgwId,'target=dv_tabel_5');
    document.getElementById('pgw_kode_penghargaan').value="";
    document.getElementById('pgw_nama_penghargaan').value="";
    document.getElementById('pgw_tahun_penghargaan').value="";
    document.getElementById('pgw_ket_penghargaan').value=""; 
  }
} 

function hapusPenghargaan(pgw_penghargaan_id) {
    HapusPenghargaan(pgw_penghargaan_id,'target=dv_tabel_5');
}  
   
// --Nampilkan History Pemberhentiaan----
var mTimer6,mulai5=0;
var pgwId = '<?php echo $pgwId; ?>';
function timer6(){
var sqlhapus6;
 clearInterval(mTimer6);      
   
   if((mulai5 % 1) == 0){
 GetDataPemberhentian(pgwId,'target=dv_tabel_6');
 }
 mulai5++;
 mTimer6 = setTimeout("timer6()", 1000);

}
timer6(); 

function tambahHistPemberhentian(pgwId) 
{
  //ambil data 

  var pgw_jenis_berhenti=document.getElementById('pgw_jenis_berhenti').value;
  var pgw_no_sk_berhenti=document.getElementById('pgw_no_sk_berhenti').value;
  var pgw_tgl_sk=document.getElementById('pgw_tgl_sk').value;
  
  if (!pgw_jenis_berhenti)
  { 
   alert('Jenis Berhenti Harus Diisi')
  }
    else if (!pgw_no_sk_berhenti)
  { 
   alert('No SK Harus Diisi')
  }
  else if (!pgw_tgl_sk)
  { 
   alert('Tanggal SK Berhenti Harus Diisi')
  }  
  else
  {
    HistPemberhentianSimpan(pgw_jenis_berhenti,pgw_no_sk_berhenti,pgw_tgl_sk,pgwId,'target=dv_tabel_6');
    document.getElementById('pgw_jenis_berhenti').value="";
    document.getElementById('pgw_no_sk_berhenti').value="";
    document.getElementById('pgw_tgl_sk').value="";
  }
} 

function hapusPemberhentian(pgw_pemberhentian_id) {
    HapusPemberhentian(pgw_pemberhentian_id,'target=dv_tabel_6');
}


var mTimer7,mulai6=0;
var pgwId = '<?php echo $pgwId; ?>';
function timer7(){
var sqlhapus7;
 clearInterval(mTimer7);      
   
   if((mulai6 % 1) == 0){
 GetDataCuti(pgwId,'target=dv_tabel_7');
 }
 mulai6++;
 mTimer7 = setTimeout("timer7()", 1000);

}
timer7(); 

function tambahHistCuti(pgwId) 
{
  //ambil data 
              
  var pgw_jenis_cuti=document.getElementById('pgw_jenis_cuti').value;
  var pgw_lama_cuti=document.getElementById('pgw_lama_cuti').value;
  var pgw_cuti_mulai=document.getElementById('pgw_cuti_mulai').value;
  var pgw_cuti_akhir=document.getElementById('pgw_cuti_akhir').value;
  
 if (pgw_jenis_cuti=='--')
  { 
   alert('Jenis Cuti Harus Dipilih')
  }
  else if (!pgw_lama_cuti)
  { 
   alert('Lama Cuti Harus Diisi')
  }
  else if (!pgw_cuti_mulai)
  { 
   alert('Instansi Harus Diisi')
  }
   else if (!pgw_cuti_akhir)
  { 
   alert('Instansi Harus Diisi')
  }  
  else
  {
    HistCutiSimpan(pgw_jenis_cuti,pgw_lama_cuti,pgw_cuti_mulai,pgw_cuti_akhir,pgwId,'target=dv_tabel_7');
    document.getElementById('pgw_jenis_cuti').value="--";
    document.getElementById('pgw_lama_cuti').value="";
    document.getElementById('pgw_cuti_mulai').value="";
    document.getElementById('pgw_cuti_akhir').value="";
  }
} 

function hapusCuti(pgw_cuti_id) {
    HapusCuti(pgw_cuti_id,'target=dv_tabel_7');
}  

	
// --Nampilkan History Hukuman Disiplin   -----
var mTimer8,mulai7=0;
var pgwId = '<?php echo $pgwId; ?>';
function timer8(){
var sqlhapus8;
 clearInterval(mTimer8);      
   
   if((mulai7 % 1) == 0){
 GetDataHukuman(pgwId,'target=dv_tabel_8');
 }     
 mulai7++;
 mTimer8 = setTimeout("timer8()", 1000);

}
timer8(); 

function tambahHistHukuman(pgwId) 
{
              
  var pgw_jenis_hukuman=document.getElementById('pgw_jenis_hukuman').value;
  var pgw_no_skhd=document.getElementById('pgw_no_skhd').value;
  var pgw_tanggal_skhd=document.getElementById('pgw_tanggal_skhd').value;
  var pgw_tmt_hukdis=document.getElementById('pgw_tmt_hukdis').value;
  var pgw_masa_hukuman_tahun=document.getElementById('pgw_masa_hukuman_tahun').value;
  var gol_ruang=document.getElementById('gol_ruang').value;
  var pgw_akhir_hukuman=document.getElementById('pgw_akhir_hukuman').value;
  var pgw_no_pp=document.getElementById('pgw_no_pp').value;
 
 if (!pgw_jenis_hukuman)
  { 
   alert('Jenis Hukuman Harus Ditulis')
  }
  else if (!pgw_no_skhd)
  { 
   alert('Nomer SK-HD Harus Diisi')
  }
  else if (!pgw_tanggal_skhd)
  { 
   alert('Tanggal SK-HD Harus Diisi')
  }
    else if (!pgw_tmt_hukdis)
  { 
   alert('Tanggal TMT Hukuman Harus Diisi')
  } 
    else if (!pgw_masa_hukuman_tahun)
  { 
   alert('Masa Hukuman Tahun Harus Diisi')
  }
    else if (gol_ruang=='--')
  { 
   alert('Golongan Harus Dipilih')
  } 
   else if (!pgw_akhir_hukuman)
  { 
   alert('Tanggal Akhir Hukuman Harus Diisi')
  }
   else if (!pgw_no_pp)
  { 
   alert('No PP Harus Diisi')
  }  
  else
  {
    HistHukumanSimpan(pgwId,pgw_jenis_hukuman,pgw_no_skhd,pgw_tanggal_skhd,pgw_tmt_hukdis,pgw_masa_hukuman_tahun,gol_ruang,pgw_akhir_hukuman,pgw_no_pp,'target=dv_tabel_8');
    document.getElementById('pgw_jenis_hukuman').value="";
    document.getElementById('pgw_no_skhd').value="";
    document.getElementById('pgw_tanggal_skhd').value="";
    document.getElementById('pgw_tmt_hukdis').value="";
    document.getElementById('pgw_masa_hukuman_tahun').value="";
    document.getElementById('gol_ruang').value="--";
    document.getElementById('pgw_akhir_hukuman').value="";
    document.getElementById('pgw_no_pp').value="";
  }
} 

function hapusHukuman(pgw_jenis_hukuman_id) {
    HapusHukuman(pgw_jenis_hukuman_id,'target=dv_tabel_8');
}   

// --Nampilkan Riwayat Eselon ----
var mTimer9,mulai8=0;
var pgwId = '<?php echo $pgwId; ?>';

function timer9(){
var sqlhapus9;
 clearInterval(mTimer9);      
   
   if((mulai8 % 1) == 0){
 GetDataEselon(pgwId,'target=dv_tabel_9');
 }
 mulai8++;
 mTimer9 = setTimeout("timer9()", 1000);

}
timer9(); 

function tambahHistEselon(pgwId) 
{
  //ambil data 
  var riw_eselon_tgl_sk=document.getElementById('riw_eselon_tgl_sk').value;
  var riw_eselon_no_sk=document.getElementById('riw_eselon_no_sk').value;
  var riw_eselon_kode=document.getElementById('riw_eselon_kode').value;
  var riw_eselon_nama=document.getElementById('riw_eselon_nama').value;
  var riw_tmt_eselon=document.getElementById('riw_tmt_eselon').value;
  
  if (!riw_eselon_tgl_sk) 
  { 
   alert('Tanggal SK Harus Diisi')
  }
  else if (!riw_eselon_no_sk) 
  { 
   alert('Nomor SK Harus Diisi')
  }
  else if (riw_eselon_kode=='--') 
  { 
   alert('Pilih Kode Harus Diisi')
  }
  else if (!riw_tmt_eselon) 
  { 
   alert('TMT Eselon Harus Diisi')
  }
  else
  {
    HistEselonSimpan(riw_eselon_tgl_sk,riw_eselon_no_sk,riw_eselon_kode,riw_eselon_nama,riw_tmt_eselon,pgwId,'target=dv_tabel_9');
    document.getElementById('riw_eselon_tgl_sk').value="";
    document.getElementById('riw_eselon_no_sk').value="";
    document.getElementById('riw_eselon_kode').value="--";
    document.getElementById('riw_eselon_nama').value="";
    document.getElementById('riw_tmt_eselon').value="";

        
  }
} 
function hapusEselon(riw_eselon_id) {
    HapusEselon(riw_eselon_id,'target=dv_tabel_9');
}

// --Nampilkan Riwayat Pangkat   ----
var mTimer11,mulai10=0;
var pgwId = '<?php echo $pgwId; ?>';

function timer11(){      
var sqlhapus11;
 clearInterval(mTimer11);      
   
   if((mulai10 % 1) == 0){
 GetDataGolongan(pgwId,'target=dv_tabel_11');
 }
 mulai10++;
 mTimer11 = setTimeout("timer11()", 1000);

}
timer11(); 

function tambahHistGolongan(pgwId) 
{
  //ambil data 
  var riw_golongan_tgl_sk=document.getElementById('riw_golongan_tgl_sk').value;
  var riw_golongan_no_sk=document.getElementById('riw_golongan_no_sk').value;
  var riw_golongan_pangkat=document.getElementById('riw_golongan_pangkat').value;
  var riw_tmt_golongan=document.getElementById('riw_tmt_golongan').value;
  
  if (!riw_golongan_tgl_sk) 
  { 
   alert('Tanggal SK Harus Diisi')
  }
  else if (!riw_golongan_no_sk) 
  { 
   alert('Nomor SK Harus Diisi')
  }
  else if (riw_golongan_pangkat=='--') 
  { 
   alert('Pilih Pangkat Harus Diisi')
  }
  else if (!riw_tmt_golongan) 
  { 
   alert('TMT Golongan Harus Diisi')
  }
  else
  {
    HistGolonganSimpan(pgwId,riw_golongan_tgl_sk,riw_golongan_no_sk,riw_golongan_pangkat,riw_tmt_golongan,'target=dv_tabel_11');
    document.getElementById('riw_golongan_tgl_sk').value="";
    document.getElementById('riw_golongan_no_sk').value="";
    document.getElementById('riw_golongan_pangkat').value="--";
    document.getElementById('riw_tmt_golongan').value="";  
  }
} 
function hapusGolongan(riw_golongan_id) {
    HapusGolongan(riw_golongan_id,'target=dv_tabel_11');
}


// --Nampilkan Riwayat Gaji Berkala     ----
var mTimer12,mulai11=0;
var pgwId = '<?php echo $pgwId; ?>';

function timer12(){
var sqlhapus12;
 clearInterval(mTimer12);      
   
   if((mulai11 % 1) == 0){
 GetDataBerkala(pgwId,'target=dv_tabel_12');
 }
 mulai11++;
 mTimer12 = setTimeout("timer12()", 1000);

}
timer12(); 

function tambahHistBerkala(pgwId) 
{
  //ambil data 
  var riw_gaji_berkala_no_sk=document.getElementById('riw_gaji_berkala_no_sk').value;
  var riw_gaji_berkala_tgl_sk=document.getElementById('riw_gaji_berkala_tgl_sk').value;
  var riw_golongan_baru=document.getElementById('riw_golongan_baru').value;
  var riw_masa_kerja=document.getElementById('riw_masa_kerja').value;
  var riw_tgl_berkala=document.getElementById('riw_tgl_berkala').value;
  
  if (!riw_gaji_berkala_no_sk) 
  { 
   alert('Nomor SK Harus Diisi')
  }
  else if (!riw_gaji_berkala_tgl_sk) 
  { 
   alert('Tanggal SK Harus Diisi')
  }
  else if (riw_golongan_baru=='--') 
  { 
   alert('Golongan Harus Diisi')
  }
  else if (riw_masa_kerja=='--') 
  { 
   alert('Masa Kerja Harus Diisi')
  }
  else if (!riw_tgl_berkala) 
  { 
   alert('TMT Berkala Harus Diisi')
  }
  else
  {
    HistBerkalaSimpan(pgwId,riw_gaji_berkala_tgl_sk,riw_gaji_berkala_no_sk,riw_golongan_baru,riw_masa_kerja,riw_tgl_berkala,'target=dv_tabel_12');
    document.getElementById('riw_gaji_berkala_no_sk').value="";
    document.getElementById('riw_gaji_berkala_tgl_sk').value="";
    document.getElementById('riw_golongan_baru').value="--";
    document.getElementById('riw_masa_kerja').value="--";
    document.getElementById('riw_tgl_berkala').value="";    
  }
} 
function hapusBerkala(riw_gaji_berkala_id) {
    HapusBerkala(riw_gaji_berkala_id,'target=dv_tabel_12');
}      

	
</script>




<script language="Javascript">

var dataKodeEselon = Array();
var	dataTunjanganStruktural = Array();
var	dataGajiPokok = Array();

<?php for($i=0,$n=count($dataKodeEselon);$i<$n;$i++){ ?>
    dataKodeEselon[<?php echo $dataKodeEselon[$i]["tunjangan_id"];?>] = '<?php echo $dataKodeEselon[$i]["tunjangan_eselon"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataKodeEselon);$i<$n;$i++){ ?>
    dataTunjanganStruktural[<?php echo $dataKodeEselon[$i]["tunjangan_id"];?>] = '<?php echo $dataKodeEselon[$i]["tunjangan_struktural"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataMasaKerja);$i<$n;$i++){ ?>
    dataGajiPokok[<?php echo $dataMasaKerja[$i]["gaji_id"];?>] = '<?php echo $dataMasaKerja[$i]["gaji_pokok_baru"];?>'
<?php } ?>

function GantiEselon(frm,nilai,golongan)
{
  frm.eselon_name.value = dataKodeEselon[nilai];
	frm.pgw_tunjangan_struktural.value = dataTunjanganStruktural[nilai];    
}

function GantiEselon1(frm,nilai,golongan)
{
  frm.riw_eselon_nama.value = dataKodeEselon[nilai];
	frm.pgw_tunjangan_strukural.value = dataTunjanganStruktural[nilai];
    
}
function GantiGajiPokok(frm,nilai,jenis,cpns)
{
//frm.pgw_gaji_pokok.value = dataGajiPokok[nilai];
	if (jenis==2) 
	{ 
		frm.pgw_gaji_pokok.value = formatCurrency((cpns/100)*dataGajiPokok[nilai]); 
	}
	else
	{
		frm.pgw_gaji_pokok.value = formatCurrency(dataGajiPokok[nilai]); 
	}
    
}

function GantiGajiPokok1(frm,nilai,jenis,cpns)
{
//frm.pgw_gaji_pokok.value = dataGajiPokok[nilai];
	if (jenis==2) 
	{ 
		frm.pgw_gaji_pokok.value = formatCurrency((cpns/100)*dataGajiPokok[nilai]); 
	}
	else
	{
		frm.pgw_gaji_pokok.value = formatCurrency(dataGajiPokok[nilai]); 
	}   
}
function GantiGajiPokokCPNS(frm,jenis,nilai)
{
    var gaji=frm.pgw_gaji_pokok.value.toString().replace(/\,/g,"");
	if (jenis==2) 
	{ 
		frm.pgw_gaji_pokok.value = formatCurrency((nilai/100)*gaji); 
	}
	else
	{
		frm.pgw_gaji_pokok.value = formatCurrency(dataGajiPokok[frm.pgw_masa_kerja.value]); 
	}   
}
<?php 
    $counter = 0;
    $numDataMasaKerja = count($dataMasaKerja);

    for($i=0,$n=count($dataGolongan);$i<$n;$i++){ 
        $isiGolongan = "\"('[Pilih Masa Kerja]','--')\",";
        if (($dataMasaKerja[$counter]["id_gaji"]) == ($dataGolongan[$i]["gaji_id"])) {
            for($j=$counter;$j<$numDataMasaKerja;$j++,$counter++){         
                $isiGolongan .= "\"('".$dataMasaKerja[$j]["gaji_masa_kerja"]."','".$dataMasaKerja[$j]["gaji_id"]."')\",";
                if($dataMasaKerja[$j]["id_gol"]!=$dataMasaKerja[$j+1]["id_gol"]) break;
            } 
            $counter+=1;
        } 
        $isiGolongan = substr($isiGolongan,0,-1);
        ?>
        var ArrSubKat<?php echo ($i+1);?> = new Array(<?php echo $isiGolongan;?>);
<?php  unset($isiGolongan); 
    } 
?>
      
function SetMasaKerja(selected) 
{
    var inForm = document.frmEdit.pgw_masa_kerja;
    if(selected!="0") var selectedArray = eval("ArrSubKat"+selected);
    
    while (1 < inForm.options.length) {
        inForm.options[(inForm.options.length - 1)] = null;
    }
    
    if(selected!="0") {
        for (var i=0; i < selectedArray.length; i++) {
            eval("inForm.options[i]=" + "new Option" + selectedArray[i]);
        }
    }
}
function SetMasaKerja1(selected) 
{
    var inForm = document.frmEdit.riw_masa_kerja;
    if(selected!="0") var selectedArray = eval("ArrSubKat"+selected);
    
    while (1 < inForm.options.length) {
        inForm.options[(inForm.options.length - 1)] = null;
    }
    
    if(selected!="0") {
        for (var i=0; i < selectedArray.length; i++) {
            eval("inForm.options[i]=" + "new Option" + selectedArray[i]);
        }
    }
}

</script>

<script>
var _wnd_new;
function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=500,height=150,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=500,height=150,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
</script>

</head>
<body>
 
<table width="100%" border="0" cellpadding="1" cellspacing="1">
     <tr class="tableheader">
          <td align="left" colspan=2 class="tblHeader">Edit Pegawai</td>
     </tr>
</table>

<div class="tabsystem"> 
   <div class="tabpage tdefault">
  <h2>Data Pegawai</h2>
  <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data"  onSubmit="return CheckSimpan(this)" />
<table width="100%" border="0" cellpadding="1" cellspacing="1">
     <tr>
     <td>
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
               <input type="hidden" name="pgw_foto" value="<?php echo $_POST["pgw_foto"];?>">

               <tr>
                    <td width="30%" align="left" class="tblMainCol" cellspacing="0"><strong>N I P LAMA</strong></td>
                    <td width="70%" class="tblCol"><input type="text" id="pgw_nip" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_nip" size="30" maxlength="9" value="<?php echo $_POST["pgw_nip"];?>"/></td>         
                    <td width="30%" rowspan="6"><img hspace="1" width="100" height="150" name="img_logo" src="<?php echo $fotoName;?>"  border="1" onDblClick="BukaWindow('pegawai_pic.php?orifoto='+ document.frmEdit.pgw_foto.value + '&nama='+document.frmEdit.pgw_nip.value,'UploadFoto')"></td>   
                    <td width="30%" rowspan="4"></td>
               </tr>     
               <tr>
                    <td width="30%" align="left" class="tblMainCol" cellspacing="0"><strong>N I P BARU</strong></td>     
                    <td width="70%" class="tblCol"><input type="text" id="pgw_nip_baru" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_nip_baru" size="30" maxlength="18" value="<?php echo $_POST["pgw_nip_baru"];?>"/>&nbsp;<font color="red">*</font></td>                
               </tr>
               <tr> 
               <td width="30%" align="left" class="tblMainCol" cellspacing="0"><strong>No Seri Karpeg</strong></td>
                    <td width="70%" class="tblCol"><input type="text" id="pgw_kartu_pegawai" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_kartu_pegawai" size="30" maxlength="50" value="<?php echo $_POST["pgw_kartu_pegawai"];?>"/>&nbsp;<font color="red">*</font> </td>
               </tr>
               <tr>          
              <td width="30%" align="left" class="tblMainCol" cellspacing="0"><strong>No ASKES</strong></td>
               <td width="70%" class="tblCol"><input type="text" id="pgw_no_askes" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_no_askes" size="30" maxlength="50" value="<?php echo $_POST["pgw_no_askes"];?>"/></td>
               </tr>              
               	<tr>
		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">No Karis/Karsu</td>
		          <td width= "70%" class="tblCol" cellpadding="0"><input  type="text" class="inputField" name="pgw_karis_karsu" size="30" maxlength="50" value="<?php echo $_POST["pgw_karis_karsu"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
	            </tr>
	            <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Nama</strong></td>
                    <td width="70%" class="tblCol"><input type="text" id="pgw_nama" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_nama" size="50" maxlength="50" value="<?php echo $_POST["pgw_nama"];?>"/>&nbsp;<font color="red">*</font></td>
               </tr>
                <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Gelar Depan</strong></td>
                    <td width="70%" class="tblCol">
                    <input type="text" id="pgw_gelar_muka" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_gelar_muka" size="10" maxlength="10" value="<?php echo $_POST["pgw_gelar_muka"];?>"/>&nbsp; &nbsp; &nbsp; &nbsp; <strong>Gelar Belakang</strong>
                    <input type="text" id="pgw_gelar_belakang" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_gelar_belakang" size="10" maxlength="10" value="<?php echo $_POST["pgw_gelar_belakang"];?>"/></td>
               </tr>
 	              <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Alamat</strong></td>
                    <td width="70%" class="tblCol"><input type="text" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_alamat" id="pgw_alamat" size="54" maxlength="54" value="<?php echo $_POST["pgw_alamat"];?>"/> &nbsp;<font color="red">*</font></td>
               </tr>
               <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Kabupaten</strong></td>
                    	<td width="70%" class="tblCol"><input type="text" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_kabupaten" id="pgw_kabupaten" size="30" maxlength="54" value="<?php echo $_POST["pgw_kabupaten"];?>"/> </td>
               </tr>
                    <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>SKPD</strong></td>
                    <td class="tblCol">
                         <select name="id_satker" id="id_satker" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" <?php if($_POST["btnDelete"]){?>disabled<?}?>>
                              <option value="<?php if ($_POST["id_satker"]=="--") echo"selected"?>">[ Pilih SKPD ]</option>
                              <?php for($i=0,$n=count($dataSatker);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataSatker[$i]["satker_id"];?>" <?php if($_POST["id_satker"]==$dataSatker[$i]["satker_id"]) echo "selected"; ?>><?php echo $dataSatker[$i]["satker_nama"];?></option>
                              <?php } ?>
                         </select>
                    </td>
               </tr>    
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Tempat / Tanggal Lahir</strong></td>
                    <td width="70%" class="tblCol"><input type="text" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tempat_lahir" size="30" maxlength="30" value="<?php echo $_POST["pgw_tempat_lahir"];?>"/>&nbsp;/&nbsp;
                    <input onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tanggal_lahir" id="pgw_tanggal_lahir" type=text size="12" maxlength="10" value="<?php echo $_POST["pgw_tanggal_lahir"]; ?>">
                    <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_tanggal_lahir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />&nbsp;(dd-mm-yyyy)
                   </td>
               </tr>
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Agama</strong></td>
                    <td  width="70%" class="tblCol">
                         <select class="inputField" name="id_agm" onKeyDown="return tabOnEnter(this, event);">
                             <option  value="<?php if ($_POST["id_agm"]=="--") echo"selected"?>">[ Pilih Agama ]</option>
                              <?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataAgama[$i]["agm_nama"];?>" <?php if($dataAgama[$i]["agm_nama"]==$_POST["id_agm"]) echo "selected";?>><?php echo ($i+1).". ".$dataAgama[$i]["agm_nama"];?></option>
                              <?php } ?>
                         </select>
                    </td>
               </tr>
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Jenis Kelamin</strong></td>  
                    <td width= "50%" align="left" class="tblCol" cellpadding="0">
                         <input onKeyDown="return tabOnEnter(this, event);" type="radio" name="pgw_jenis_kelamin" id="laki" value="L" <?php if($_POST["pgw_jenis_kelamin"]=="L" || !$_POST["pgw_jenis_kelamin"]) echo "checked";?>><label for="laki" >Laki - Laki</label>&nbsp;
                         <input onKeyDown="return tabOnEnter(this, event);" type="radio" name="pgw_jenis_kelamin" id="perempuan" value="P" <?php if($_POST["pgw_jenis_kelamin"]=="P") echo "checked";?>><label for="perempuan" >Perempuan</label>&nbsp;
                    </td>	
               </tr>
            <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Tingkat Pendidikan</strong></td>
                    <td  width="70%" class="tblCol">
                         <select class="inputField" name="id_pendidikan" id="id_pendidikan"  onKeyDown="return tabOnEnter(this, event);">
                              <option value="<?php if ($_POST["id_pendidikan"]=="--") echo"selected"?>">[ Tingkat Pendidikan ]</option>
                              <?php for($i=0,$n=count($dataTingkatPendidikan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataTingkatPendidikan[$i]["tingkat_pendidikan_id"];?>" <?php if($dataTingkatPendidikan[$i]["tingkat_pendidikan_id"]==$_POST["id_pendidikan"]) echo "selected";?>><?php echo ($i+1).". ".$dataTingkatPendidikan[$i]["tingkat_pendidikan_nama"];?></option>
                              <?php } ?>
                         </select>
                     &nbsp;<font color="red">*</font>
                    </td>
               </tr>
               <tr>
                <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Status Pegawai</strong></td>
                    <td  width="70%" class="tblCol">
                         <select class="inputField" name="id_stat_peg" id="id_stat_peg" onChange="GantiGajiPokokCPNS(this.form,this.options[this.selectedIndex].value,<?php echo $dataCPNS['par_gaji_cpns'];?>)" onKeyDown="return tabOnEnter(this, event);">
                              <option value="<?php if ($_POST["id_stat_peg"]=="--") echo"selected"?>">[ Status ]</option>
                              <?php for($i=0,$n=count($dataStatusPegawai);$i<$n;$i++){ ?>
                              <option class="inputField" value="<?php echo $dataStatusPegawai[$i]["stat_peg_id"];?>" <?php if($dataStatusPegawai[$i]["stat_peg_id"]==$_POST["id_stat_peg"]) echo "selected";?>><?php echo ($i+1).". ".$dataStatusPegawai[$i]["stat_peg_nama"];?></option>
                              <?php } ?>
                         </select>
                     &nbsp;<font color="red">*</font>
                    </td>
               </tr>
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Status Nikah</strong></td>
                    <td width= "50%" align="left" class="tblCol" cellpadding="0">
                         <input onKeyDown="return tabOnEnter(this, event);" type="radio" name="pgw_status_nikah" id="stn" class="inputField" value="N" <?php if($_POST["pgw_status_nikah"]=="N" || !$_POST["pgw_status_nikah"]) echo "checked";?>><label for="stn">Belum Menikah</label>&nbsp;
                         <input onKeyDown="return tabOnEnter(this, event);" type="radio" name="pgw_status_nikah" id="sty" class="inputField" value="Y" <?php if($_POST["pgw_status_nikah"]=="Y") echo "checked";?>><label for="sty">Menikah</label>&nbsp;
                         <input onKeyDown="return tabOnEnter(this, event);" type="radio" name="pgw_status_nikah" id="std" class="inputField" value="D" <?php if($_POST["pgw_status_nikah"]=="D") echo "checked";?>><label for="std">Duda</label>&nbsp;
                         <input onKeyDown="return tabOnEnter_select_with_button(this, event);" type="radio" name="pgw_status_nikah" id="stj" class="inputField" value="J" <?php if($_POST["pgw_status_nikah"]=="J") echo "checked";?>><label for="stj">Janda</label>
                    </td>
               </tr>
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>TMT PNS</strong></td>
                    <td class="tblCol" >
                         <input onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tmt_pns" id="pgw_tmt_pns" type=text size="12" maxlength="10" value="<?php echo $_POST["pgw_tmt_pns"]; ?>">
                         <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_tanggal_tmt_pns" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />&nbsp;(dd-mm-yyyy)
                    </td>
               </tr>
                <tr>
		            <td class="tblMainCol">Tanggal SKPNS</td>
		            <td class="tblCol" >
						<input type="text" class="inputField" id="pgw_tanggal_skpns" name="pgw_tanggal_skpns" size="15" maxlength="10" value="<?php echo $_POST["pgw_tanggal_skpns"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_skpns" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					 (dd-mm-yyy)
		        </td>
              </tr> 
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>TMT CPNS</strong></td>
                    <td class="tblCol" >
                         <input onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tmt_cpns" id="pgw_tmt_cpns" type=text size="12" maxlength="10" value="<?php echo $_POST["pgw_tmt_cpns"]; ?>">
                         <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_tanggal_tmt_cpns" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />&nbsp;(dd-mm-yyyy)
                    </td>
               </tr>  
               <tr>
		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">No.SKCPNS</td>
		          <td width= "50%" align="left" class="tblCol" cellpadding="0">
              <input  type="text" class="inputField" name="pgw_no_skcpns" size="30" maxlength="50" value="<?php echo $_POST["pgw_no_skcpns"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
	            </tr>
	    	      <tr>
		          <td class="tblMainCol">Tanggal SKCPNS</td>
		          <td class="tblCol" >
						<input type="text" class="inputField" id="pgw_tanggal_skcpns" name="pgw_tanggal_skcpns" size="15" maxlength="10" value="<?php echo $_POST["pgw_tanggal_skcpns"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_skcpns" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					 (dd-mm-yyy)
		      </td>
		      </tr>
		      <tr>
		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">No.SKPNS</td>
		          <td width= "50%" align="left" class="tblCol" cellpadding="0">
              <input  type="text" class="inputField" name="pgw_no_skpns" size="30" maxlength="50" value="<?php echo $_POST["pgw_no_skpns"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
	            </tr> 
            <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Kode Eselon</strong></td>
                    <td  width="70%" class="tblCol">	
                         <select name="id_tunjangan" id="id_tunjangan" class="inputField" onChange="GantiEselon(this.form,this.options[this.selectedIndex].value,this.options[this.selectedIndex].text)" onKeyDown="return tabOnEnter(this, event);">
                              <option value="<?php if ($_POST["id_tunjangan"]=="--") echo"selected"?>">[Pilih Kode Eselon]</option>	
                              <?php for($i=0,$n=count($dataKodeEselon);$i<$n;$i++){ ?>
                               <option class="inputField" value="<?php echo $dataKodeEselon[$i]["tunjangan_id"];?>" <?php if($dataKodeEselon[$i]["tunjangan_id"]==$_POST["id_tunjangan"]) echo "selected";?>><?php echo $dataKodeEselon[$i]["tunjangan_kode"];?></option>
                               <?php } ?>
                         </select>
                          &nbsp;<font color="red">*</font>
                    </td>
               </tr>    
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Eselon</strong></td>
                    <td width="70%" class="tblCol"><input type="text" onKeyDown="return tabOnEnter(this, event);" class="inputField" name="eselon_name" size="5" maxlength="5" value="<?php echo $_POST["eselon_name"];?>"/></td>
               </tr>
                <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>TMT Eselon</strong></td>
                    <td class="tblCol" >
                         <input onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tmt_eselon" id="pgw_tmt_eselon" type=text size="12" maxlength="10" value="<?php echo $_POST["pgw_tmt_eselon"]; ?>">
                         <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_tanggal_tmt_eselon" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />&nbsp;(dd-mm-yyyy)
                    </td>
               </tr>          
             <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>TMT Jabatan</strong></td>
                    <td class="tblCol" >
                         <input onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tmt_jabatan" id="pgw_tmt_jabatan" type=text size="12" maxlength="10" value="<?php echo $_POST["pgw_tmt_jabatan"]; ?>">
                         <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_tanggal_tmt_jabatan" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />&nbsp;(dd-mm-yyyy)
                    </td>
               </tr>    
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Jenis Jabatan</strong></td>
                    <td  width="70%" class="tblCol">
                         <select class="inputField" name="id_jenjang" onKeyDown="return tabOnEnter_select_with_button(this, event);">
  <option value="<?php if ($_POST["id_jenjang"]=="--") echo"selected"?>">[Pilih Jenis Jabatan]</option>
                              <?php for($i=0,$n=count($dataJenjangJabatan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataJenjangJabatan[$i]["jenjang_id"];?>" <?php if($dataJenjangJabatan[$i]["jenjang_id"]==$_POST["id_jenjang"]) echo "selected";?>><?php echo $dataJenjangJabatan[$i]["jenjang_nama"];?></option>
                              <?php } ?>
                         </select>
 &nbsp;<font color="red">*</font>
                    </td>
               </tr>
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Jabatan</strong></td>
                    <td  width="70%" class="tblCol">
                         <select class="inputField" name="id_jabatan" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                              <option value="<?php if ($_POST["id_jabatan"]=="--") echo"selected"?>">[Pilih Jabatan]</option>
                              <?php for($i=0,$n=count($dataMasterJabatan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataMasterJabatan[$i]["jabatan_id"];?>" <?php if($dataMasterJabatan[$i]["jabatan_id"]==$_POST["id_jabatan"]) echo "selected";?>><?php echo $dataMasterJabatan[$i]["jabatan_nama"];?></option>
                              <?php } ?>
                         </select>

                    </td>
               </tr>
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Golongan</strong></td>
                    <td class="tblCol" >
                         <select name="pgw_golongan" id="pgw_golongan" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" onChange="SetMasaKerja(this.selectedIndex);" <?php if($_POST["btnDelete"]){?>disabled<?}?> onKeyDown="return tabOnEnter(this, event);">
                              <option value="<?php if ($_POST["pgw_golongan"]=="--") echo"selected"?>">[Pilih Golongan]</option>
                              <?php for($i=0,$n=count($dataGolongan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataGolongan[$i]["gol_id"];?>" <?php if($_POST["pgw_golongan"]==$dataGolongan[$i]["gol_id"]) echo "selected"; ?>><?php echo $dataGolongan[$i]["gol_gol"];?></option>
                              <?php } ?>
                         </select>
                    </td>
               </tr>
               		   <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Pangkat</strong></td>
                    <td class="tblCol" >  <?php for($i=0,$n=count($dataGolongan);$i<$n;$i++){ ?>
						<?php if($_POST["pgw_golongan"]==$dataGolongan[$i]["gol_id"]) echo $dataGolongan[$i]["gol_pangkat"];?>
					<? } ?>
					</td>
			    </tr>
               <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Masa Kerja</strong></td>
                    <td  width="70%" class="tblCol">
                         <select name="pgw_masa_kerja" id="pgw_masa_kerja" onKeyDown="return tabOnEnter(this, event);" class="inputField" onChange="GantiGajiPokok(this.form,this.options[this.selectedIndex].value,this.form.id_stat_peg.value,'<?php echo $dataCPNS['par_gaji_cpns'];?>')" <?php if($_POST["btnDelete"]){?>disabled<?}?> onKeyDown="return tabOnEnter(this, event);">
                              <option  value="<?php if ($_POST["pgw_masa_kerja"]=="--") echo"selected"?>">[Pilih Masa Kerja]</option>
                              <?php for($i=0,$n=count($dataMasaKerja);$i<$n;$i++){ ?>
                                   <?php if($_POST["pgw_golongan"]==$dataMasaKerja[$i]["gol_id"]){ ?>
                                        <option value="<?php echo $dataMasaKerja[$i]["gaji_id"];?>" <?php if($_POST["pgw_masa_kerja"]==$dataMasaKerja[$i]["gaji_id"]) echo "selected"; ?>><?php echo $dataMasaKerja[$i]["gaji_masa_kerja"];?></option>
                                   <?php } ?>
                              <?php } ?>
                         </select>
                         &nbsp;<font color="red">*</font>
                    </td>
               </tr>
             <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Gaji Pokok</strong></td>
                    <td width="70%" class="tblCol"><input readonly type="text" onKeyDown="return tabOnEnter(this, event);" onKeyUp="this.value=formatCurrency(this.value);" class="inputField" name="pgw_gaji_pokok" size="15" maxlength="15" value="<?php echo currency_format($_POST["pgw_gaji_pokok"]);?>"/></td>
               </tr>
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>TMT Golongan</strong></td>
                    <td class="tblCol" >
                         <input onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tmt_golongan" id="pgw_tmt_golongan" type=text size="12" maxlength="10" value="<?php echo $_POST["pgw_tmt_golongan"]; ?>">
                         <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_tanggal_tmt_golongan" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />&nbsp;(dd-mm-yyyy)
                    </td>
               </tr>
                <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>TMT Berkala</strong></td>
                    <td class="tblCol" >
                         <input onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tmt_berkala" id="pgw_tmt_berkala" type=text size="12" maxlength="10" value="<?php echo $_POST["pgw_tmt_berkala"]; ?>">
                         <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_tanggal_tmt_berkala" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />&nbsp;(dd-mm-yyyy)
                    </td>
               </tr>
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Tg. Berkala</strong></td>
                    <td class="tblCol" >
                         <input onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tg_berkala" id="pgw_tg_berkala" type=text size="12" maxlength="10" value="<?php echo $_POST["pgw_tg_berkala"]; ?>">
                         <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_tanggal_tg_berkala" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />&nbsp;(dd-mm-yyyy)
                    </td>
               </tr>
              <!-- 
               <tr>
                  <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Pekerjaan Lainnya</strong></td>
                    	<td width="70%" class="tblCol"><input type="text" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_kerja_lainnya" size="54" maxlength="54" value="<?php echo $_POST["pgw_kerja_lainnya"];?>"/></td>
               </tr>
               <tr>
                  <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Penghasilan Lainnya</strong></td>
                    	<td width="70%" class="tblCol"><input type="text" onKeyUp="this.value=formatCurrency(this.value);" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_penghasilan_lainnya" size="15" maxlength="15" value="<?php echo currency_format($_POST["pgw_penghasilan_lainnya"]);?>"/></td>
               </tr>
               <tr>
                  <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Mempunyai Pensiun</strong></td>
                    	<td width="70%" class="tblCol">
                      <input type="text" onKeyUp="this.value=formatCurrency(this.value);" onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_pensiun" size="15" maxlength="15" value="<?php echo currency_format($_POST["pgw_pensiun"]);?>"/></td>
               </tr>
              <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Tanggal Mutasi</strong></td>
                    <td class="tblCol" >
                         <input onKeyDown="return tabOnEnter_select_with_button(this, event);" class="inputField" name="pgw_tanggal_mutasi" id="pgw_tanggal_mutasi" type=text size="12" maxlength="10" value="<?php echo $_POST["pgw_tanggal_mutasi"]; ?>">
                         <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_tanggal_mutasi" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />&nbsp;(dd-mm-yyyy)
                    </td>
               </tr>
               <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Keterangan Mutasi</strong></td>
                    <td class="tblCol"><textarea class="inputField" cols="25" rows="3" name="pgw_keterangan"><?php echo $_POST["pgw_keterangan"];?></textarea></td>
               </tr> 
               <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Nama Jabatan</strong></td>
                    <td class="tblCol"> 
                         <select class="inputField" name="id_rol">
                              <?php for($i=0,$n=count($dataJabatan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataJabatan[$i]["rol_id"];?>" <?php if($dataJabatan[$i]["rol_id"]==$_POST["id_rol"]) echo "selected";?>><?php echo $dataJabatan[$i]["rol_name"];?></option>
                              <?php } ?>
                         </select>
                    </td>
               </tr>  -->
               <tr>
                    <td colspan="2" align="center" class="tblCol">
                         <input type="submit" class="button" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Simpan" class="inputField"/>
                         <input type="button" name="btnBack" value="Kembali" class="button" onClick="document.location.href='pegawai_view.php'"/>
                    </td>
               </tr>        
          </table> 
          </td>
     </tr>
</table>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="pgw_id" value="<?php echo $pgwId?>" />
<input type="hidden" name="usr_id" value="<?php echo $usrId?>" />
<? } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_gaji" value="<?php echo $gajiId?>" />
<input type="hidden" name="satker_id" value="<?php echo $_POST["id_satker"];?>" />



  <script type="text/javascript">
    Calendar.setup({
        inputField     :    "pgw_tanggal_lahir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_lahir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });

    Calendar.setup({
        inputField     :    "pgw_tmt_golongan",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_tmt_golongan",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });

   Calendar.setup({
        inputField     :    "pgw_tmt_eselon",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_tmt_eselon",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    }); 
    
      Calendar.setup({
        inputField     :    "pgw_tmt_jabatan",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_tmt_jabatan",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });     

    Calendar.setup({
        inputField     :    "pgw_tg_berkala",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_tg_berkala",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });

   Calendar.setup({
        inputField     :    "pgw_tmt_berkala",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_tmt_berkala",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });   

      Calendar.setup({
        inputField     :    "pgw_tmt_pns",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_tmt_pns",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "pgw_tmt_cpns",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_tmt_cpns",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
        Calendar.setup({
        inputField     :    "pgw_tanggal_skcpns",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_skcpns",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "pgw_tanggal_skpns",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_skpns",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
</script>
</div>

 <div class="tabpage">
    <h2>Riw. Jabatan</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>                     
           <legend><strong>RIWAYAT JABATAN</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
            <tr>
            		<td class="tblMainCol">Tanggal SK</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="riw_jab_tgl_sk" name="riw_jab_tgl_sk" size="15" maxlength="10" value="<?php echo $_POST["riw_jab_tgl_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_riw_jab_tgl_sk" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
              <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">No. SK</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="riw_jab_no_sk" name="riw_jab_no_sk" size="30" maxlength="50" value="<?php echo $_POST["riw_jab_no_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
             <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Jabatan Sekarang</strong></td>
                    <td class="tblCol"> 
                         <select class="inputField" id="riw_jab_lama" name="riw_jab_lama">
                                    <option class="inputField" value="--" <?php if($_POST["riw_jab_lama"]=='--') echo "selected";?>>Pilih Jabatan Sekarang</option>
                              <?php for($i=0,$n=count($dataMasterJabatan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataMasterJabatan[$i]["jabatan_id"];?>" <?php if($dataMasterJabatan[$i]["jabatan_id"]==$_POST["riw_jab_lama"]) echo "selected";?>><?php echo $dataMasterJabatan[$i]["jabatan_nama"];?></option>
                              <?php } ?>
                         </select>
                    </td>
               </tr>
                        <tr>
            		<td class="tblMainCol">TMT JABATAN</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="riw_tmt_jabatan" name="riw_tmt_jabatan" size="15" maxlength="10" value="<?php echo $_POST["riw_tmt_jabatan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_riw_tmt_jabatan" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistPegawai(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataJabatan($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>

    <script type="text/javascript">
    Calendar.setup({
        inputField     :    "riw_jab_tgl_sk",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_riw_jab_tgl_sk",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
     Calendar.setup({
        inputField     :    "riw_tmt_jabatan",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_riw_tmt_jabatan",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    </script>
        </div>       
        
   
   <div class="tabpage">
    <h2>Riw. Anak</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>
           <legend><strong>HISTORY ANAK</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
                          <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Nama Anak </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_anak_nama" name="pgw_anak_nama" size="30" maxlength="50" value="<?php echo $_POST["pgw_anak_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
              <tr>
            	            <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Jenis Kelamin</strong></td>  
            	 <td class="tblCol">
            	  <select class="inputField" id="pgw_anak_jenis_kelamin" name="pgw_anak_jenis_kelamin">
                         <option class="inputField" name="pgw_anak_jenis_kelamin" id="pgw_anak_jenis_kelamin" value="L" <?php if($_POST["pgw_anak_jenis_kelamin"]=="L") echo "selected";?>><label>Laki - Laki</label>&nbsp;
                         <option class="inputField" name="pgw_anak_jenis_kelamin" id="pgw_anak_jenis_kelamin" value="P" <?php if($_POST["pgw_anak_jenis_kelamin"]=="P") echo "selected";?>><label>Perempuan</label>&nbsp;
                    </select>
                   </td>
                    </tr>	    
               <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Tempat Lahir</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_anak_kota_lahir" name="pgw_anak_kota_lahir" size="30" maxlength="50" value="<?php echo $_POST["pgw_anak_kota_lahir"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
            <tr>
            		<td class="tblMainCol">Tanggal Lahir</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_anak_tanggal_lahir" name="pgw_anak_tanggal_lahir" size="15" maxlength="10" value="<?php echo $_POST["pgw_anak_tanggal_lahir"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_riw_anak" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
            	            	<tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Pendidikan </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_anak_pendidikan" name="pgw_anak_pendidikan" size="30" maxlength="50" value="<?php echo $_POST["pgw_anak_pendidikan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
             <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Pekerjaan </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_anak_kerja" name="pgw_anak_kerja" size="30" maxlength="50" value="<?php echo $_POST["pgw_anak_kerja"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
                  <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Status Anak</strong></td>
                    <td class="tblCol">
                     <select class="inputField" id="pgw_anak_nikah" name="pgw_anak_nikah">
                         <option class="inputField" name="pgw_anak_nikah" id="pgw_anak_nikah" value="Y" <?php if($_POST["pgw_anak_nikah"]=="Y") echo "selected";?>><label>Menikah</label>&nbsp;
                         <option class="inputField" name="pgw_anak_nikah" id="pgw_anak_nikah" value="N" <?php if($_POST["pgw_anak_nikah"]=="N") echo "selected";?>><label>Belum Menikah</label>&nbsp;
                    </select>
                    </td>
               </tr>  
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistAnak(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_1" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataAnak($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>

    <script type="text/javascript">
    Calendar.setup({
        inputField     :    "pgw_anak_tanggal_lahir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_riw_anak",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    </script> 
         </div>	   
         
         <div class="tabpage">
    <h2>R. Suami Istri</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>
           <legend><strong>HISTORY SUAMI ISTRI</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
                        
                          <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">NIP Istri / Suami </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_nip_istri_suami" name="pgw_nip_istri_suami" size="30" maxlength="50" value="<?php echo $_POST["pgw_nip_istri_suami"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
            	<tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">NIP Baru Istri / Suami </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_nip_baru_suami_istri" name="pgw_nip_baru_suami_istri" size="30" maxlength="50" value="<?php echo $_POST["pgw_nip_baru_suami_istri"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
            	
            	<tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Nama Istri / Suami </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_nama_istri_suami" name="pgw_nama_istri_suami" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama_istri_suami"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>

              <tr>
            		<td class="tblMainCol">Tanggal Lahir Suami Istri</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_tgl_lahir_istrisuami" name="pgw_tgl_lahir_istrisuami" size="15" maxlength="10" value="<?php echo $_POST["pgw_tgl_lahir_istrisuami"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_lahir_istri" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
              <tr>
            		<td class="tblMainCol">Tanggal Perkawinan</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_tgl_perkawinan" name="pgw_tgl_perkawinan" size="15" maxlength="10" value="<?php echo $_POST["pgw_tgl_perkawinan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_kawin" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
              <tr>
            		<td class="tblMainCol">Tanggal Cerai</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_tgl_cerai" name="pgw_tgl_cerai" size="15" maxlength="10" value="<?php echo $_POST["pgw_tgl_cerai"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_cerai" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
              <tr>
            		<td class="tblMainCol">Tanggal Meninggal</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="tgl_meninggal" name="tgl_meninggal" size="15" maxlength="10" value="<?php echo $_POST["tgl_meninggal"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_mati" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>  
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistIstri(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_3" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataIstri($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>

       <script type="text/javascript">
    Calendar.setup({
        inputField     :    "pgw_tgl_lahir_istrisuami",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_lahir_istri",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
        Calendar.setup({
        inputField     :    "pgw_tgl_perkawinan",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_kawin",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
        Calendar.setup({
        inputField     :    "pgw_tgl_cerai",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_cerai",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
        Calendar.setup({
        inputField     :    "tgl_meninggal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_mati",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    </script>
        </div>

       
       <div class="tabpage">
    <h2>His.Ortu</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>
           <legend><strong>HISTORY ORANG TUA</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
                          <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Nama Ayah </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_nama_ayah" name="pgw_nama_ayah" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama_ayah"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
              <tr>
            	            <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Status Ayah</strong></td>  
            	 <td class="tblCol">
            	  <select class="inputField" id="pgw_status_ayah" name="pgw_status_ayah">
                         <option class="inputField" name="pgw_status_ayah" id="pgw_status_ayah" value="Y" <?php if($_POST["pgw_status_ayah"]=="Y") echo "selected";?>><label>Hidup</label>&nbsp;
                         <option class="inputField" name="pgw_status_ayah" id="pgw_status_ayah" value="N" <?php if($_POST["pgw_status_ayah"]=="N") echo "selected";?>><label>Almarhum</label>&nbsp;
                    </select>
                   </td>
                    </tr>
                     <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Nama Ibu</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_nama_ibu" name="pgw_nama_ibu" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama_ibu"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
              <tr>
            	            <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Status Ibu</strong></td>  
            	 <td class="tblCol">
            	  <select class="inputField" id="pgw_status_ibu" name="pgw_status_ibu">
                         <option class="inputField" name="pgw_status_ibu" id="pgw_status_ibu" value="Y" <?php if($_POST["pgw_status_ibu"]=="Y") echo "selected";?>><label>Hidup</label>&nbsp;
                         <option class="inputField" name="pgw_status_ibu" id="pgw_status_ibu" value="N" <?php if($_POST["pgw_status_ibu"]=="N") echo "selected";?>><label>Almarhum</label>&nbsp;
                    </select>
                   </td>
                    </tr>	    
               </tr>   
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistOrtu(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_2" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataOrtu($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>
    </div>
    
            
<div class="tabpage">
    <h2>R. Pendidikan</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>
           <legend><strong>HISTORY PENDIDIKAN</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
              <tr>
                    <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Tingkat Pendidikan</strong></td>
                    <td  width="70%" class="tblCol">
                         <select class="inputField" name="pendidikan_tingkat" id="pendidikan_tingkat"  onKeyDown="return tabOnEnter(this, event);">
                              <option value="<?php if ($_POST["id_pendidikan"]=="--") echo"selected"?>">[ Tingkat Pendidikan ]</option>
                              <?php for($i=0,$n=count($dataTingkatPendidikan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataTingkatPendidikan[$i]["tingkat_pendidikan_id"];?>" <?php if($dataTingkatPendidikan[$i]["tingkat_pendidikan_id"]==$_POST["pendidikan_tingkat"]) echo "selected";?>><?php echo ($i+1).". ".$dataTingkatPendidikan[$i]["tingkat_pendidikan_nama"];?></option>
                              <?php } ?>
                         </select>
                      &nbsp; &nbsp; <input  type="text" class="inputField" id="pendidikan_nama" name="pendidikan_nama" size="30" maxlength="50" value="<?php echo $_POST["pendidikan_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                   </td>
                    </tr>	  
            	      <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Tahun Lulus</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pendidikan_tahun" name="pendidikan_tahun" size="30" maxlength="50" value="<?php echo $_POST["pendidikan_tahun"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr> 
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistPendidikan(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
               
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_4" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataPendidikan($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>
    </div>  
    
    <div class="tabpage">
    <h2>R. Pemberhentian</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>
           <legend><strong>RIWAYAT PEMBERHENTIAN</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
                          <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Jenis Berhenti </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_jenis_berhenti" name="pgw_jenis_berhenti" size="30" maxlength="50" value="<?php echo $_POST["pgw_jenis_berhenti"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
            	            	<tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">No. SK </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_no_sk_berhenti" name="pgw_no_sk_berhenti" size="30" maxlength="50" value="<?php echo $_POST["pgw_no_sk_berhenti"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
             <tr>
            		<td class="tblMainCol">Tanggal SK</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_tgl_sk" name="pgw_tgl_sk" size="15" maxlength="10" value="<?php echo $_POST["pgw_tgl_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_sk_berhenti" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td> 
                </tr> 
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistPemberhentian(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_6" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataPemberhentian($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>

       <script type="text/javascript">
    Calendar.setup({
        inputField     :    "pgw_tgl_sk",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_sk_berhenti",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    </script>
        </div> 
        
        <br />   <div class="tabpage">
    <h2>R. Penghargaan</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>
           <legend><strong>RIWAYAT PENGHARGAAN</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
                          <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Kode </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_kode_penghargaan" name="pgw_kode_penghargaan" size="10" maxlength="50" value="<?php echo $_POST["pgw_kode_penghargaan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
            	</tr>
            	            	<tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Nama Penghargaan </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_nama_penghargaan" name="pgw_nama_penghargaan" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama_penghargaan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
             <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Tahun </td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_tahun_penghargaan" name="pgw_tahun_penghargaan" size="5" maxlength="4" value="<?php echo $_POST["pgw_tahun_penghargaan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
             <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Keterangan</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_ket_penghargaan" name="pgw_ket_penghargaan" size="30" maxlength="50" value="<?php echo $_POST["pgw_ket_penghargaan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>    
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistPenghargaan(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_5" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataPenghargaan($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>
    </div>   
    
<div class="tabpage">
    <h2>Riw. Cuti</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>
           <legend><strong>HISTORY CUTI</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">

             <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Jenis Cuti</strong></td>
                    <td class="tblCol"> 
                         <select class="inputField" id="pgw_jenis_cuti" name="pgw_jenis_cuti">
                                    <option class="inputField" value="--" <?php if($_POST["pgw_jenis_cuti"]=='--') echo "selected";?>>Pilih Jenis Cuti</option>
                              <?php for($i=0,$n=count($dataCutiPeg);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataCutiPeg[$i]["pgw_master_cuti_id"];?>" <?php if($dataCutiPeg[$i]["pgw_master_cuti_id"]==$_POST["pgw_jenis_cuti"]) echo "selected";?>><?php echo $dataCutiPeg[$i]["pgw_master_cuti_nama"];?></option>
                              <?php } ?>
                         </select>
                    </td>
               </tr>
              <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Lama Cuti</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_lama_cuti" name="pgw_lama_cuti" size="5" maxlength="50" value="<?php echo $_POST["pgw_lama_cuti"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
                           <tr>
            		<td class="tblMainCol">Tanggal Cuti Mulai</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_cuti_mulai" name="pgw_cuti_mulai" size="15" maxlength="10" value="<?php echo $_POST["pgw_cuti_mulai"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_riw_cuti_mulai" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
            	
                           <tr>
            		<td class="tblMainCol">Tanggal Cuti Akhir</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_cuti_akhir" name="pgw_cuti_akhir" size="15" maxlength="10" value="<?php echo $_POST["pgw_cuti_akhir"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_riw_cuti_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
            	
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistCuti(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_7" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataCuti($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>

    <script type="text/javascript">
    Calendar.setup({
        inputField     :    "pgw_cuti_mulai",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_riw_cuti_mulai",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
      Calendar.setup({
        inputField     :    "pgw_cuti_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_riw_cuti_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    </script>
       </div>
           
<div class="tabpage">
    <h2>Huk. Disiplin</h2>
       
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>
           <legend><strong>HUKUMAN DISIPLIN</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
           <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Jenis Hukuman</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_jenis_hukuman" name="pgw_jenis_hukuman" size="30" maxlength="50" value="<?php echo $_POST["pgw_jenis_hukuman"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
               <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">NomOr SK-HD</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_no_skhd" name="pgw_no_skhd" size="30" maxlength="50" value="<?php echo $_POST["pgw_no_skhd"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
            <tr>
            		<td class="tblMainCol">Tanggal SKHD</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_tanggal_skhd" name="pgw_tanggal_skhd" size="15" maxlength="10" value="<?php echo $_POST["pgw_tanggal_skhd"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tanggal_skhd" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
            	<tr>
            		<td class="tblMainCol">TMT HUK Disiplin</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_tmt_hukdis" name="pgw_tmt_hukdis" size="15" maxlength="10" value="<?php echo $_POST["pgw_tmt_hukdis"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tanggal_tmt_hukdis" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
            	  <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Masa Hukuman</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0">
                  <input  type="text" class="inputField" id="pgw_masa_hukuman_tahun" name="pgw_masa_hukuman_tahun" size="5" maxlength="4" value="<?php echo $_POST["pgw_masa_hukuman_tahun"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/> Tahun
                  </td>
            	</tr>  
            	<tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Golongan</strong></td>
                    <td class="tblCol"> 
                         <select class="inputField" id="gol_ruang" name="gol_ruang">
                                    <option class="inputField" value="--" <?php if($_POST["gol_ruang"]=='--') echo "selected";?>>Pilih Golongan</option>
                              <?php for($i=0,$n=count($dataGolongan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataGolongan[$i]["gol_id"];?>" <?php if($dataGolongan[$i]["gol_id"]==$_POST["gol_ruang"]) echo "selected";?>><?php echo $dataGolongan[$i]["gol_gol"];?></option>
                              <?php } ?>
                         </select>
                    </td>
               </tr>
               <tr>
            		<td class="tblMainCol">Tanggal Akhir Hukuman</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="pgw_akhir_hukuman" name="pgw_akhir_hukuman" size="15" maxlength="10" value="<?php echo $_POST["pgw_akhir_hukuman"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tanggal_akhir_hukuman" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
               <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">No PP</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0"><input  type="text" class="inputField" id="pgw_no_pp" name="pgw_no_pp" size="30" maxlength="50" value="<?php echo $_POST["pgw_no_pp"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>  
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistHukuman(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
               <div id="dv_tabel_8" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataHukuman($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>

     <script type="text/javascript">
    Calendar.setup({
        inputField     :    "pgw_tanggal_skhd",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_skhd",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "pgw_tmt_hukdis",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_tmt_hukdis",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
        Calendar.setup({
        inputField     :    "pgw_akhir_hukuman",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal_akhir_hukuman",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    </script>
          </div>          
        
         <div class="tabpage">
    <h2>R. Eselon</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>                     
           <legend><strong>RIWAYAT ESELON</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
            <tr>
            		<td class="tblMainCol">Tanggal SK</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="riw_eselon_tgl_sk" name="riw_eselon_tgl_sk" size="15" maxlength="10" value="<?php echo $_POST["riw_eselon_tgl_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_sk_eselon" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
              <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">No. SK</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0">
                  <input  type="text" class="inputField" id="riw_eselon_no_sk" name="riw_eselon_no_sk" size="30" maxlength="50" value="<?php echo $_POST["riw_eselon_no_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr> 
             <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Kode Eselon</strong></td>
                    <td class="tblCol"> 
                         <select class="inputField" id="riw_eselon_kode" name="riw_eselon_kode" onChange="GantiEselon1(this.form,this.options[this.selectedIndex].value,this.options[this.selectedIndex].text)">
                                    <option class="inputField" value="--" <?php if($_POST["riw_eselon_kode"]=='--') echo "selected";?>>Pilih Jabatan Lama</option>
                              <?php for($i=0,$n=count($dataKodeEselon);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataKodeEselon[$i]["tunjangan_id"];?>" <?php if($dataKodeEselon[$i]["tunjangan_id"]==$_POST["riw_eselon_kode"]) echo "selected";?>><?php echo $dataKodeEselon[$i]["tunjangan_kode"];?></option>
                              <?php } ?>
                         </select>    
                    </td>
               </tr>
                <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">Eselon Baru</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0">
                  <input  type="text" class="inputField" id="riw_eselon_nama" name="riw_eselon_nama" size="30" maxlength="50" value="<?php echo $_POST["riw_eselon_nama"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
             <tr>
            		<td class="tblMainCol">TMT ESELON</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="riw_tmt_eselon" name="riw_tmt_eselon" size="15" maxlength="10" value="<?php echo $_POST["riw_tmt_eselon"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tmt_eselon" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
               <tr>
                 <td>                                                                                                                                                                
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistEselon(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_9" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataEselon($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>

    <script type="text/javascript">
    Calendar.setup({
        inputField     :    "riw_eselon_tgl_sk",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_sk_eselon",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });

    Calendar.setup({
        inputField     :    "riw_tmt_eselon",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tmt_eselon",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    </script>
        </div>  
        
                
                
        <div class="tabpage">
    <h2>R. Kepangkatan</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
                 <fieldset>                     
           <legend><strong>RIWAYAT KEPANGKATAN</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">

            <tr>
            		<td class="tblMainCol">Tanggal SK</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="riw_golongan_tgl_sk" name="riw_golongan_tgl_sk" size="15" maxlength="10" value="<?php echo $_POST["riw_golongan_tgl_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_sk_golongan" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
              <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">No. SK</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0">
                  <input  type="text" class="inputField" id="riw_golongan_no_sk" name="riw_golongan_no_sk" size="30" maxlength="50" value="<?php echo $_POST["riw_golongan_no_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
             <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Pangkat Baru</strong></td>
                    <td class="tblCol"> 
                         <select class="inputField" id="riw_golongan_pangkat" name="riw_golongan_pangkat" >
                                    <option class="inputField" value="--" <?php if($_POST["riw_golongan_pangkat"]=='--') echo "selected";?>>Pilih Pangkkat Baru</option>
                              <?php for($i=0,$n=count($dataGolongan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataGolongan[$i]["gol_id"];?>" <?php if($dataGolongan[$i]["gol_id"]==$_POST["riw_golongan_pangkat"]) echo "selected";?>><?php echo $dataGolongan[$i]["gol_pangkat"];?></option>
                              <?php } ?>
                         </select>
                    </td>
               </tr>
             <tr>
            		<td class="tblMainCol">TMT GOLONGAN</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="riw_tmt_golongan" name="riw_tmt_golongan" size="15" maxlength="10" value="<?php echo $_POST["riw_tmt_golongan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tmt_golongan" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistGolongan(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_11" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataGolongan($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>

    <script type="text/javascript">
    Calendar.setup({
        inputField     :    "riw_golongan_tgl_sk",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_sk_golongan",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });

    Calendar.setup({
        inputField     :    "riw_tmt_golongan",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tmt_golongan",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    </script>
        </div>  

<div class="tabpage">
    <h2>R. Gaji Berkala</h2>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td>
               <fieldset>                     
           <legend><strong>RIWAYAT GAJI BERKALA</strong></legend>
           <table width="100%" border="0" cellpadding="1" cellspacing="1">
            <tr>
            		<td class="tblMainCol">Tanggal SK</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="riw_gaji_berkala_tgl_sk" name="riw_gaji_berkala_tgl_sk" size="15" maxlength="10" value="<?php echo $_POST["riw_gaji_berkala_tgl_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_sk_berkala" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
              <tr>
    		          <td width= "30%" align="left" class="tblMainCol" cellspacing="0">No. SK</td>
    		          <td width= "50%" align="left" class="tblCol" cellpadding="0">
                  <input  type="text" class="inputField" id="riw_gaji_berkala_no_sk" name="riw_gaji_berkala_no_sk" size="30" maxlength="50" value="<?php echo $_POST["riw_gaji_berkala_no_sk"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
            	</tr>
               <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Golongan Baru</strong></td>
                    <td class="tblCol"> 
                         <select class="inputField" id="riw_golongan_baru" name="riw_golongan_baru"  onChange="SetMasaKerja1(this.selectedIndex);" <?php if($_POST["btnDelete"]){?>disabled<?}?> onKeyDown="return tabOnEnter(this, event);">
                                    <option class="inputField" value="--" <?php if($_POST["riw_golongan_baru"]=='--') echo "selected";?>>Pilih Golongan Baru</option>
                              <?php for($i=0,$n=count($dataGolongan);$i<$n;$i++){ ?>
                                   <option class="inputField" value="<?php echo $dataGolongan[$i]["gol_id"];?>" <?php if($dataGolongan[$i]["gol_id"]==$_POST["riw_golongan_baru"]) echo "selected";?>><?php echo $dataGolongan[$i]["gol_gol"];?></option>
                              <?php } ?>
                         </select>
                    </td>
               </tr>
                   <tr>
                   <td width= "30%" align="left" class="tblMainCol" cellspacing="0"><strong>Masa Kerja</strong></td>
                    <td  width="70%" class="tblCol">
                         <select name="riw_masa_kerja" id="riw_masa_kerja" onKeyDown="return tabOnEnter(this, event);" class="inputField" onChange="GantiGajiPokok1(this.form,this.options[this.selectedIndex].value,this.form.id_stat_peg.value,'<?php echo $dataCPNS['par_gaji_cpns'];?>')" <?php if($_POST["btnDelete"]){?>disabled<?}?> onKeyDown="return tabOnEnter(this, event);">
                              <option  value="<?php if ($_POST["riw_masa_kerja"]=="--") echo"selected"?>">[Pilih Masa Kerja]</option>
                              <?php for($i=0,$n=count($dataMasaKerja);$i<$n;$i++){ ?>
                                   <?php if($_POST["pgw_golongan"]==$dataMasaKerja[$i]["gol_id"]){ ?>
                                        <option value="<?php echo $dataMasaKerja[$i]["gaji_id"];?>" <?php if($_POST["riw_masa_kerja"]==$dataMasaKerja[$i]["gaji_id"]) echo "selected"; ?>><?php echo $dataMasaKerja[$i]["gaji_masa_kerja"];?></option>
                                   <?php } ?>
                              <?php } ?>
                         </select>
                         &nbsp;<font color="red">*</font>
                    </td>
               </tr>
             <tr>
            		<td class="tblMainCol">TMT BERKALA</td>
            		<td class="tblCol" >
      						<input type="text" class="inputField" id="riw_tgl_berkala" name="riw_tgl_berkala" size="15" maxlength="10" value="<?php echo $_POST["riw_tgl_berkala"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
      						<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tmt_berkala" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
      					 (dd-mm-yyy)
            		</td>
            	</tr>
               <tr>
                 <td>      
                      <img src="<?php echo $APLICATION_ROOT;?>images/tombol.PNG" style="cursor:pointer" width="82" height="27" onClick="tambahHistBerkala(<?php echo $pgwId;?>);">
                 </td>
               </tr>
               </table>
                       </fieldset>
               <tr>
               <td colspan="2">
                <div id="dv_tabel_12" style="border:1px #D9F5CB solid; width:800; height:200; overflow:auto;">
                <?php echo GetDataBerkala($pgwId); ?>
              </div></td>
              </tr>
             </td>
            </tr>  
        </table>

    <script type="text/javascript">
    Calendar.setup({
        inputField     :    "riw_gaji_berkala_tgl_sk",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_sk_berkala",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });

    Calendar.setup({
        inputField     :    "riw_tgl_berkala",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tmt_berkala",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    </script>
        </div>        





        

          
</form>                   
</div>   
</body>
</html> 
<?
   $dtaccess->Close();
?>    


