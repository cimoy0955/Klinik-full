<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/tree.cls.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     
     if(!$_POST["tanggal_kontrol"]) $_POST["tanggal_kontrol"] = getDateToday();
     
 	if(!$auth->IsAllowed("perawatan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("perawatan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "dokter_page.php";
     $icdPage = "icd_find.php?";
     $inaPage = "ina_find.php?";
     $terapiPage = "obat_find.php?";
	   $dokterPage = "rawat_dokter_find.php?";
	   $susterPage = "rawat_suster_find.php?";
     $backPage = "perawatan_view.php?";

     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetPerawatan,GetTonometri,GetDosis");     

     function GetTonometri($scale,$weight) {
          global $dtaccess; 
               
          $sql = "select tono_pressure from klinik.klinik_tonometri where tono_scale = ".QuoteValue(DPE_NUMERIC,$scale)." and tono_weight = ".QuoteValue(DPE_NUMERIC,$weight);
          $dataTable = $dtaccess->Fetch($sql);
          return $dataTable["tono_pressure"];

     }     

     function GetPerawatan() {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
          
          $sql = "select cust_usr_nama,a.reg_id,a.reg_status,a.reg_waktu,a.reg_jadwal, 
                  c.rawatinap_tanggal_masuk,c.rawatinap_id,d.kategori_nama,e.kamar_kode,f.bed_kode
                  from klinik_registrasi a
                  left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            			left join klinik_rawatinap c on c.id_reg = a.reg_id
            			left join klinik_kamar_kategori d on d.kategori_id = c.id_kategori_kamar
            			left join klinik_kamar e on e.kamar_id = c.id_kamar and e.id_kategori = c.id_kategori_kamar
            			left join klinik_kamar_bed f on f.bed_id = c.id_bed and f.id_kamar = c.id_kamar
                  where a.reg_status like '".STATUS_RAWATINAP.STATUS_MENGINAP."' order by reg_status desc, kategori_nama, kamar_kode, rawatinap_tanggal_masuk asc, rawatinap_waktu_masuk asc";
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $dataTable = $dtaccess->FetchAll($rs);
          
          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Kelas";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Kamar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Bed";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

          
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
				
	       $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?id_reg='.$dataTable[$i]["reg_id"].'&id_rawatinap='.$dataTable[$i]["rawatinap_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;

         $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
         $tbContent[$i][$counter][TABLE_ALIGN] = "right";
         $counter++;
         
         $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "left";
         $counter++;
         
         $tbContent[$i][$counter][TABLE_ISI] = format_date_long($dataTable[$i]["rawatinap_tanggal_masuk"]);
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kategori_nama"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kamar_kode"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed_kode"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          }
			
          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}

     
     function GetDosis($fisik,$akhir,$id=null) {
          global $dtaccess, $view;
          
          $sql = "select dosis_id, dosis_nama from inventori.inv_dosis where id_fisik = ".QuoteValue(DPE_NUMERIC,$fisik);
          $dataTable = $dtaccess->FetchAll($sql);

          $optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show); 
          for($i=0,$n=count($dataTable);$i<$n;$i++) {
               $show = ($id==$dataTable[$i]["dosis_id"]) ? "selected":"";
               $optDosis[$i+1] = $view->RenderOption($dataTable[$i]["dosis_id"],$dataTable[$i]["dosis_nama"],$show); 
          }
          
          return $view->RenderComboBox("id_dosis[]","id_dosis_".$akhir,$optDosis,null,null,null);
     }
     
     if(!$_POST["btnSaveRawat"] && !$_POST["btnUpdateRawat"]) {
          // --- buat cari suster yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id 
                    from klinik.klinik_perawatan_suster a 
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat 
                    where c.rawat_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"));
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["rawat_suster_nama"][$i]) $_POST["rawat_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     }
	
	if(!$_POST["btnSaveRawat"] && !$_POST["btnUpdateRawat"]) {
	
     	// --- buat cari pemeriksaan yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
                    where c.rawat_tanggal = ".QuoteValue(DPE_DATE,date('Y-m-d')); 
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["ref_suster_nama"][$i]) $_POST["ref_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     
     }


     if ($_GET["id"]) {
          // === buat ngedit ---          
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["rawat_id"] = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.id_reg from klinik.klinik_perawatan a 
				where rawat_id = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $_GET["id_reg"] = $row_edit["id_reg"];
     }     
     
     
     // --- cari input pemeriksaan pertama hari ini ---
     $sql = "select a.rawat_id 
               from klinik.klinik_perawatan a 
               where a.rawat_tanggal = ".QuoteValue(DPE_CHAR,date('Y-m-d'))." 
               order by rawat_waktu asc limit 1"; 
     $rs = $dtaccess->Execute($sql);
     $firstData = $dtaccess->Fetch($rs);
	
     $edit = (($firstData["rawat_id"]==$_POST["rawat_id"])||!$firstData["rawat_id"])?true:false;
      
	if($_GET["id_reg"] && $_GET["id_rawatinap"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, 
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                    from klinik.klinik_registrasi a
				            left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          //echo $sql;
        	$_POST["id_reg"] = $_GET["id_reg"]; 
        	$_POST["id_rawatinap"] = $_GET["id_rawatinap"];
        	$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
          $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
        	$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
              
          $sql = "select * from klinik_rawatinap_kontrol_dokter where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
                  and id_rawatinap=".QuoteValue(DPE_CHAR,$_POST["id_rawatinap"])." and kontrol_tanggal=".QuoteValue(DPE_DATE,$_POST["tanggal_kontrol"]);
          $rs_cek = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $data_kontrol = $dtaccess->Fetch($rs_cek);
          
          $_POST["kontrol_id"] = $data_kontrol["kontrol_id"];
          
          $diagLink = "perawatan_diag.php?id_cust_usr=".$enc->Encode($dataPasien["id_cust_usr"])."&id_reg=".$enc->Encode($_GET["id_reg"]);
          
          //-- pencarian di tabel klinik_perawatan --//
          $sql = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$data_kontrol["id_reg"]);
          
          $dataPemeriksaan = $dtaccess->Fetch($sql);
          if($dataPemeriksaan) $_x_mode = "Diag";

          $view->CreatePost($dataPemeriksaan);


          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster_rawat"][$i] = $row["pgw_id"];
               $_POST["rawat_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }


          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_dokter"] = $row["pgw_id"];
          $_POST["rawat_dokter_nama"] = $row["pgw_nama"];

          // --- icd od
          $sql = "select icd_nama,icd_nomor, icd_id from klinik.klinik_perawatan_icd a
                    join klinik.klinik_icd b on a.id_icd = b.icd_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_icd_odos = 'OD'
                    order by rawat_icd_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_icd_od_id"][$i] = $row["icd_id"];
               $_POST["rawat_icd_od_kode"][$i] = $row["icd_nomor"];
               $_POST["rawat_icd_od_nama"][$i] = $row["icd_nama"];
               $i++;
          }

          // --- icd os
          $sql = "select icd_nama,icd_nomor, icd_id from klinik.klinik_perawatan_icd a
                    join klinik.klinik_icd b on a.id_icd = b.icd_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_icd_odos = 'OS'
                    order by rawat_icd_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_icd_os_id"][$i] = $row["icd_id"];
               $_POST["rawat_icd_os_kode"][$i] = $row["icd_nomor"];
               $_POST["rawat_icd_os_nama"][$i] = $row["icd_nama"];
               $i++;
          }


          // --- ina od
          $sql = "select ina_nama,ina_kode, ina_id from klinik.klinik_perawatan_ina a
                    join klinik.klinik_ina b on a.id_ina = b.ina_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_ina_odos = 'OD'
                    order by rawat_ina_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_ina_od_id"][$i] = $row["ina_id"];
               $_POST["rawat_ina_od_kode"][$i] = $row["ina_kode"];
               $_POST["rawat_ina_od_nama"][$i] = $row["ina_nama"];
               $i++;
          }
          
          
          // --- ina os
          $sql = "select ina_nama,ina_kode, ina_id from klinik.klinik_perawatan_ina a
                    join klinik.klinik_ina b on a.id_ina = b.ina_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_ina_odos = 'OS'
                    order by rawat_ina_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_ina_os_id"][$i] = $row["ina_id"];
               $_POST["rawat_ina_os_kode"][$i] = $row["ina_kode"];
               $_POST["rawat_ina_os_nama"][$i] = $row["ina_nama"];
               $i++;
          }

		//-- ina klaim--
          $sql = "select ina_nama, ina_kode, ina_id 
				from klinik.klinik_registrasi_ina a
                    join klinik.klinik_ina b on a.id_ina = b.ina_id
				where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])."
				and reg_ina_jenis = ".QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
          $rs = $dtaccess->Execute($sql);
		$dataKlaim = $dtaccess->Fetch($rs);
		
		$_POST["rawat_ina_klaim_id"][0] = $dataKlaim["ina_id"];
		$_POST["rawat_ina_klaim_nama"][0] = $dataKlaim["ina_nama"];
		$_POST["rawat_ina_klaim_kode"][0] = $dataKlaim["ina_kode"];
		
            // --- terapi obat
          $sql = "select item_id, item_nama,item_fisik, id_dosis from klinik.klinik_perawatan_terapi a
                    left join inventori.inv_item b on a.id_item = b.item_id
                    where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." 
                    order by rawat_item_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_item"][$i] = $row["id_item"];
               $_POST["item_fisik"][$i] = $row["item_fisik"];
               $_POST["item_nama"][$i] = $row["item_nama"];
               $_POST["id_dosis"][$i] = $row["id_dosis"];
               $i++;
          }
          
          //-- pencarian di tabel klinik_diagnostik --//
          $sql = "select a.* from klinik.klinik_diagnostik a 
				where diag_id = ".QuoteValue(DPE_CHAR,$data_kontrol["id_diag"]);
          $row_edit_diag = $dtaccess->Fetch($sql);
          
          $view->CreatePost($row_edit_diag);
          
          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$data_kontrol["id_diag"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster"][$i] = $row["pgw_id"];
               $_POST["diag_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }

          $sql = "select pgw_nama, pgw_id from klinik.klinik_diagnostik_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_dokter"] = $row["pgw_id"];
          $_POST["diag_dokter_nama"] = $row["pgw_nama"];
          
          
          //-- pencarian di tabel klinik_refraksi --//
          $sql = "select a.* from klinik.klinik_refraksi a 
				where ref_id = ".QuoteValue(DPE_CHAR,$data_kontrol["id_refraksi"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $view->CreatePost($row_edit);
          
          $tmpPinholeOD = explode("/", $_POST["ref_pinhole_od"]);
          $_POST["ref_pinhole_od1"] = $tmpPinholeOD[0];
          $_POST["ref_pinhole_od2"] = $tmpPinholeOD[1];

          $tmpPinholeOS = explode("/", $_POST["ref_pinhole_os"]);
          $_POST["ref_pinhole_os1"] = $tmpPinholeOS[0];
          $_POST["ref_pinhole_os2"] = $tmpPinholeOS[1];

          $sql = "select pgw_nama, pgw_id from klinik.klinik_refraksi_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_ref = ".QuoteValue(DPE_CHAR,$_POST["ref_id"]);
          $rs = $dtaccess->Execute($sql);
          
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster"][$i] = $row["pgw_id"];
               $_POST["ref_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
          
          //-- pencarian untuk tab bedah --//
          $sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien,  
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                    from klinik.klinik_registrasi a
				            left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
      		$sql = "select b.visus_nama as ref_mata_od_nonkoreksi_visus, d.visus_nama as ref_mata_od_koreksi_visus,
                    ref_mata_od_koreksi_spheris,ref_mata_od_koreksi_cylinder,ref_mata_od_koreksi_sudut, 
                    c.visus_nama as ref_mata_os_nonkoreksi_visus, e.visus_nama as ref_mata_os_koreksi_visus,
                    ref_mata_os_koreksi_spheris,ref_mata_os_koreksi_cylinder,ref_mata_os_koreksi_sudut
                    from klinik.klinik_refraksi a
                    left join klinik.klinik_visus b on a.id_visus_nonkoreksi_od = b.visus_id
                    left join klinik.klinik_visus c on a.id_visus_nonkoreksi_os = c.visus_id
                    left join klinik.klinik_visus d on a.id_visus_koreksi_od = d.visus_id
                    left join klinik.klinik_visus e on a.id_visus_koreksi_os = e.visus_id
                    where a.ref_id = ".QuoteValue(DPE_CHAR,$data_kontrol["id_refraksi"]);
          $dataRefraksi = $dtaccess->Fetch($sql); 
     
          $sql = "select rawat_id, rawat_tonometri_scale_od, rawat_tonometri_weight_od, rawat_tonometri_pressure_od, 
                    rawat_anel, rawat_schimer, rawat_operasi_jenis  
                    from klinik.klinik_perawatan where rawat_id = ".QuoteValue(DPE_CHAR,$data_kontrol["id_perawatan"]);
          $dataPemeriksaan = $dtaccess->Fetch($sql);
          
          $sql = "select b.icd_nomor, b.icd_nama
                    from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_id
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    and a.rawat_icd_odos = 'OD' 
                    order by a.rawat_icd_urut";
          $dataDiagIcdOD = $dtaccess->FetchAll($sql);
     
          $sql = "select b.icd_nomor, b.icd_nama
                    from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_id
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    and a.rawat_icd_odos = 'OS' 
                    order by a.rawat_icd_urut";
          $dataDiagIcdOS = $dtaccess->FetchAll($sql);
     
          $sql = "select b.ina_kode, b.ina_nama
                    from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    and a.rawat_ina_odos = 'OD' 
                    order by a.rawat_ina_urut";
          $dataDiagInaOD = $dtaccess->FetchAll($sql);
     
          $sql = "select b.ina_kode, b.ina_nama
                    from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    and a.rawat_ina_odos = 'OS' 
                    order by a.rawat_ina_urut";
          $dataDiagInaOS = $dtaccess->FetchAll($sql);
          
          $sql = "select * from klinik.klinik_perawatan_operasi where op_id = ".QuoteValue(DPE_CHAR,$data_kontrol["id_reg"]);
          $dataOperasi= $dtaccess->Fetch($sql);
          
          $view->CreatePost($dataOperasi);

		$tmpJamMulai = explode(":", $_POST["op_jam_mulai"]);
		$_POST["op_mulai_jam"] = $tmpJamMulai[0];
		$_POST["op_mulai_menit"] = $tmpJamMulai[1];
		
		$tmpJamSelesai = explode(":", $_POST["op_jam_selesai"]);
		$_POST["op_selesai_jam"] = $tmpJamSelesai[0];
		$_POST["op_selesai_menit"] = $tmpJamSelesai[1];


          $sql = "select pgw_nama, pgw_id from hris.hris_pegawai b where pgw_id = ".QuoteValue(DPE_NUMERIC,$_POST["id_dokter"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_dokter"] = $row["pgw_id"];
          $_POST["op_dokter_nama"] = $row["pgw_nama"];

          $sql = "select pgw_nama, pgw_id from hris.hris_pegawai b where pgw_id = ".QuoteValue(DPE_NUMERIC,$_POST["id_suster"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_suster"] = $row["pgw_id"];
          $_POST["op_suster_nama"] = $row["pgw_nama"];

          $sql = "select pgw_nama, pgw_id from klinik.klinik_operasi_suster a 
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_op = ".QuoteValue(DPE_CHAR,$dataOperasi["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster_terapi"][$i] = $row["pgw_id"];
               $_POST["op_suster_terapi_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
       
          $sql = "select * from klinik.klinik_perawatan_duranteop a 
                    where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_durop_komp"][$row["id_durop_komp"]] = "y";
          }
          
          
          $sql = "select * from klinik.klinik_perawatan_injeksi a 
                    where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $dataDetailInjeksi = $dtaccess->FetchAll($rs);
	

		$sql = "select b.icd_nomor as op_icd_kode, b.icd_nama as op_icd_nama, a.id_icd  
				from klinik.klinik_perawatan_operasi_icd a  
                    join klinik.klinik_icd b on a.id_icd = b.icd_id 
				where a.id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
		$dataIcd = $dtaccess->FetchAll($sql);


		$sql = "select b.ina_kode as op_ina_kode, b.ina_nama as op_ina_nama 
				from klinik.klinik_ina b 
				where b.ina_id = ".QuoteValue(DPE_CHAR,$dataOperasi["id_ina"]);
		$dataIna = $dtaccess->Fetch($sql);
		$view->CreatePost($dataIna);
		
		
		
		//-- pencarian data di tabel preop --//
				$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.*,  
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  c.ref_keluhan 
                    from klinik.klinik_registrasi a
				join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
             
          $diagLink = "perawatan_diag.php?id_cust_usr=".$enc->Encode($dataPasien["id_cust_usr"])."&id_reg=".$enc->Encode($_GET["id_reg"]);

		$sql = "select b.visus_nama as ref_mata_od_nonkoreksi_visus, d.visus_nama as ref_mata_od_koreksi_visus,
				ref_mata_od_koreksi_spheris,ref_mata_od_koreksi_cylinder,ref_mata_od_koreksi_sudut, 
				c.visus_nama as ref_mata_os_nonkoreksi_visus, e.visus_nama as ref_mata_os_koreksi_visus,
				ref_mata_os_koreksi_spheris,ref_mata_os_koreksi_cylinder,ref_mata_os_koreksi_sudut 
				from klinik.klinik_refraksi a
				left join klinik.klinik_visus b on a.id_visus_nonkoreksi_od = b.visus_id
				left join klinik.klinik_visus c on a.id_visus_nonkoreksi_os = c.visus_id
				left join klinik.klinik_visus d on a.id_visus_koreksi_od = d.visus_id
				left join klinik.klinik_visus e on a.id_visus_koreksi_os = e.visus_id
				where a.ref_id = ".QuoteValue(DPE_CHAR,$data_kontrol["id_refraksi"]);
          $dataRefraksi = $dtaccess->Fetch($sql); 
     
          $sql = "select *
                    from klinik.klinik_perawatan where rawat_id = ".QuoteValue(DPE_CHAR,$data_kontrol["id_rawat"]);
          $dataPemeriksaan = $dtaccess->Fetch($sql); 
	
		$sql = "select b.icd_nomor, b.icd_nama
				from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_id
				where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
				and a.rawat_icd_odos = 'OD' 
				order by a.rawat_icd_urut";
		$dataDiagIcdOD = $dtaccess->FetchAll($sql);
	
		$sql = "select b.icd_nomor, b.icd_nama
				from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_id
				where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
				and a.rawat_icd_odos = 'OS' 
				order by a.rawat_icd_urut";
		$dataDiagIcdOS = $dtaccess->FetchAll($sql);
	
		$sql = "select b.ina_kode, b.ina_nama
				from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
				where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
				and a.rawat_ina_odos = 'OD' 
				order by a.rawat_ina_urut";
		$dataDiagInaOD = $dtaccess->FetchAll($sql);
	
		$sql = "select b.ina_kode, b.ina_nama
				from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
				where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
				and a.rawat_ina_odos = 'OS' 
				order by a.rawat_ina_urut";
		$dataDiagInaOS = $dtaccess->FetchAll($sql);

		$sql = "select a.*, b.bio_av_nama 
				from klinik.klinik_diagnostik a
				left join klinik.klinik_biometri_av b on a.diag_av_constant = b.bio_av_id 
                    where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]); 
          $dataDiagnostik= $dtaccess->Fetch($sql);
		
          if(!$dataDiagnostik) {
               $sql = "select reg_id from klinik.klinik_registrasi a
                         where a.id_cust_usr = ".$_POST["id_cust_usr"]." and
                         a.reg_tanggal < ".QuoteValue(DPE_DATE,$dataPasien["reg_tanggal"]);
              // $dataRegB4 = $dtaccess->Fetch($sql);
              // echo $sql;
               if($dataRegB4["reg_id"]) {
                    unset($dataDiagnostik);
                    $sql = "select a.*, b.bio_av_nama 
                              from klinik.klinik_diagnostik a
                              left join klinik.klinik_biometri_av b on a.diag_av_constant = b.bio_av_id 
                              where a.id_reg = ".QuoteValue(DPE_CHAR,$dataRegB4["reg_id"]); 
                    $dataDiagnostik= $dtaccess->Fetch($sql);
               }               
          }
          
		$sql = "select * from klinik.klinik_preop where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dataPreOp = $dtaccess->Fetch($sql);
		
		$view->CreatePost($dataPreOp);
	
		$_POST["preop_keluhan"] = ($dataPreOp["preop_keluhan"]) ? $dataPreOp["preop_keluhan"]:$dataPemeriksaan["rawat_keluhan"];
		$_POST["preop_keadaan_umum"] = ($dataPreOp["preop_keadaan_umum"]) ? $dataPreOp["preop_keadaan_umum"]:$dataPemeriksaan["rawat_keadaan_umum"];
		$_POST["preop_lab_tensi"] = ($dataPreOp["preop_lab_tensi"]) ? $dataPreOp["preop_lab_tensi"]:$dataPemeriksaan["rawat_lab_tensi"];
		$_POST["preop_lab_nadi"] = ($dataPreOp["preop_lab_nadi"]) ? $dataPreOp["preop_lab_nadi"]:$dataPemeriksaan["rawat_lab_nadi"];
		$_POST["preop_lab_nafas"] = ($dataPreOp["preop_lab_nafas"]) ? $dataPreOp["preop_lab_nafas"]:$dataPemeriksaan["rawat_lab_nafas"];
		$_POST["preop_mata_lokal"] = ($dataPreOp["preop_mata_lokal"]) ? $dataPreOp["preop_mata_lokal"]:$dataPemeriksaan["rawat_mata_lokal"];
		$_POST["preop_lab_gula_darah"] = ($dataPreOp["preop_lab_gula_darah"]) ? $dataPreOp["preop_lab_gula_darah"]:$dataPemeriksaan["rawat_lab_gula_darah"];
		$_POST["preop_lab_darah_lengkap"] = ($dataPreOp["preop_lab_darah_lengkap"]) ? $dataPreOp["preop_lab_darah_lengkap"]:$dataPemeriksaan["rawat_lab_darah_lengkap"];
		$_POST["preop_tonometri_scale_od"] = ($dataPreOp["preop_tonometri_scale_od"]) ? $dataPreOp["preop_tonometri_scale_od"]:$dataPemeriksaan["rawat_tonometri_scale_od"];
		$_POST["preop_tonometri_weight_od"] = ($dataPreOp["preop_tonometri_weight_od"]) ? $dataPreOp["preop_tonometri_weight_od"]:$dataPemeriksaan["rawat_tonometri_weight_od"];
		$_POST["preop_tonometri_pressure_od"] = ($dataPreOp["preop_tonometri_pressure_od"]) ? $dataPreOp["preop_tonometri_pressure_od"]:$dataPemeriksaan["rawat_tonometri_pressure_od"];
		$_POST["preop_tonometri_scale_os"] = ($dataPreOp["preop_tonometri_scale_os"]) ? $dataPreOp["preop_tonometri_scale_os"]:$dataPemeriksaan["rawat_tonometri_scale_os"];
		$_POST["preop_tonometri_weight_os"] = ($dataPreOp["preop_tonometri_weight_os"]) ? $dataPreOp["preop_tonometri_weight_os"]:$dataPemeriksaan["rawat_tonometri_weight_os"];
		$_POST["preop_tonometri_pressure_os"] = ($dataPreOp["preop_tonometri_pressure_os"]) ? $dataPreOp["preop_tonometri_pressure_os"]:$dataPemeriksaan["rawat_tonometri_pressure_os"];
		$_POST["preop_k1_od"] = ($dataPreOp["preop_k1_od"]) ? $dataPreOp["preop_k1_od"]:$dataPemeriksaan["rawat_k1_od"];
		$_POST["preop_k1_os"] = ($dataPreOp["preop_k1_os"]) ? $dataPreOp["preop_k1_os"]:$dataPemeriksaan["rawat_k1_os"];
		$_POST["preop_k2_od"] = ($dataPreOp["preop_k2_od"]) ? $dataPreOp["preop_k2_od"]:$dataPemeriksaan["rawat_k2_od"];
		$_POST["preop_k2_os"] = ($dataPreOp["preop_k2_os"]) ? $dataPreOp["preop_k2_os"]:$dataPemeriksaan["rawat_k2_os"];
		$_POST["preop_acial_od"] = ($dataPreOp["preop_acial_od"]) ? $dataPreOp["preop_acial_od"]:$dataPemeriksaan["rawat_acial_od"];
		$_POST["preop_acial_os"] = ($dataPreOp["preop_acial_os"]) ? $dataPreOp["preop_acial_os"]:$dataPemeriksaan["rawat_acial_os"];
		$_POST["preop_iol_od"] = ($dataPreOp["preop_iol_od"]) ? $dataPreOp["preop_iol_od"]:$dataPemeriksaan["rawat_iol_od"];
		$_POST["preop_iol_os"] = ($dataPreOp["preop_iol_os"]) ? $dataPreOp["preop_iol_os"]:$dataPemeriksaan["rawat_iol_os"];
		$_POST["preop_av_constant"] = ($dataPreOp["preop_av_constant"]) ? $dataPreOp["preop_av_constant"]:$dataPemeriksaan["rawat_av_constant"];
		$_POST["preop_deviasi"] = ($dataPreOp["preop_deviasi"]) ? $dataPreOp["preop_deviasi"]:$dataPemeriksaan["rawat_deviasi"];
		$_POST["preop_rumus"] = ($dataPreOp["preop_rumus"]) ? $dataPreOp["preop_rumus"]:$dataPemeriksaan["rawat_rumus"];
		$_POST["preop_operasi_paket"] = ($dataPreOp["preop_operasi_paket"]) ? $dataPreOp["preop_operasi_paket"]:$dataPemeriksaan["rawat_operasi_paket"];
		$_POST["preop_operasi_jenis"] = ($dataPreOp["preop_operasi_jenis"]) ? $dataPreOp["preop_operasi_jenis"]:$dataPemeriksaan["rawat_operasi_jenis"];
		
		if($dataPreOp) {
			
			$sql = "select pgw_nama, pgw_id from klinik.klinik_preop_suster a
					join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_preop = ".QuoteValue(DPE_CHAR,$dataPreOp["preop_id"]);
			$rs = $dtaccess->Execute($sql);
			$i=0;
			while($row=$dtaccess->Fetch($rs)) {
				$_POST["id_suster"][$i] = $row["pgw_id"];
				$_POST["preop_suster_nama"][$i] = $row["pgw_nama"];
				$i++;
			}
			
			$sql = "select pgw_nama, pgw_id from klinik.klinik_preop_dokter a
					join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_preop = ".QuoteValue(DPE_CHAR,$dataPreOp["preop_id"]);
			$rs = $dtaccess->Execute($sql);
			$row=$dtaccess->Fetch($rs);
			$_POST["id_dokter"] = $row["pgw_id"];
			$_POST["preop_dokter_nama"] = $row["pgw_nama"];
		}
          
      //-- nambah data ke tabel rawatinap_kontrol_dokter kalo gak dapet kontrol_id --//
      if(!$_POST["kontrol_id"]) {
          $dbTable = "klinik_rawatinap_kontrol_dokter";
          $dbField[0] = "kontrol_id";
          $dbField[1] = "kontrol_tanggal";
          $dbField[2] = "id_reg";
          $dbField[3] = "id_rawatinap";
          
          $_POST["kontrol_id"] = $dtaccess->GetTransID("klinik_rawat_inap_kontrol_dokter","kontrol_id",DB_SCHEMA_KLINIK);
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["kontrol_id"]);
          $dbValue[1] = QuoteValue(DPE_DATE,$_POST["tanggal_kontrol"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_rawatinap"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
            
          $dtmodel->Insert() or die("insert  error");	
              
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
      }
	} 
	
	//--  PROSES PENYIMPANAN UNTUK TAB RAWAT / PEMERIKSAAN --//
	if ($_POST["btnSaveRawat"] || $_POST["btnUpdateRawat"]) {
	
          // --- delete data e dulu ---
          if($_POST["btnSaveRawat"]) {               
               $sql = "delete from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
          }
          
          $dbTable = "klinik.klinik_perawatan";
          $dbField[0] = "rawat_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "rawat_keluhan";
          $dbField[3] = "rawat_keadaan_umum";
          $dbField[4] = "rawat_tonometri_scale_od";
          $dbField[5] = "rawat_anel";
          $dbField[6] = "rawat_schimer";
          $dbField[7] = "rawat_lab_gula_darah";
          $dbField[8] = "rawat_lab_darah_lengkap";
          $dbField[9] = "rawat_lab_tensi";
          $dbField[10] = "rawat_lab_nadi";
          $dbField[11] = "rawat_lab_nafas";
          $dbField[12] = "rawat_mata_od_palpebra";
          $dbField[13] = "rawat_mata_os_palpebra";
          $dbField[14] = "rawat_mata_od_conjunctiva";
          $dbField[15] = "rawat_mata_os_conjunctiva";
          $dbField[16] = "rawat_mata_od_cornea";
          $dbField[17] = "rawat_mata_os_cornea";
          $dbField[18] = "rawat_mata_od_coa";
          $dbField[19] = "rawat_mata_os_coa";
          $dbField[20] = "rawat_mata_od_iris";
          $dbField[21] = "rawat_mata_os_iris";
          $dbField[22] = "rawat_mata_od_pupil";
          $dbField[23] = "rawat_mata_os_pupil";
          $dbField[24] = "rawat_mata_od_lensa";
          $dbField[25] = "rawat_mata_os_lensa";
          $dbField[26] = "rawat_mata_od_ocular";
          $dbField[27] = "rawat_mata_os_ocular";
          $dbField[28] = "rawat_mata_od_retina";
          $dbField[29] = "rawat_mata_os_retina";
          $dbField[30] = "id_cust_usr";
          $dbField[31] = "rawat_tonometri_weight_od";
          $dbField[32] = "rawat_tonometri_pressure_od";
          $dbField[33] = "rawat_mata_foto";          
          $dbField[34] = "rawat_mata_sketsa";
          $dbField[35] = "rawat_tonometri_od";
          $dbField[36] = "rawat_tonometri_os";
          $dbField[37] = "rawat_anestesis_jenis";
          $dbField[38] = "rawat_anestesis_obat";
          $dbField[39] = "rawat_anestesis_dosis";
          $dbField[40] = "rawat_anestesis_komp";
          $dbField[41] = "rawat_anestesis_pre";
          $dbField[42] = "rawat_operasi_jenis";
          $dbField[43] = "rawat_operasi_paket";
          $dbField[44] = "rawat_tonometri_weight_os";
          $dbField[45] = "rawat_tonometri_pressure_os";
          $dbField[46] = "rawat_tonometri_scale_os";
          $dbField[47] = "rawat_color_blindness";
          $dbField[48] = "rawat_catatan";
          $dbField[49] = "rawat_irigasi";
          $dbField[50] = "rawat_epilasi";
          $dbField[51] = "rawat_suntikan";
          $dbField[52] = "rawat_probing";
          $dbField[53] = "rawat_flouorecsin";
          $dbField[54] = "rawat_kesehatan";
          $dbField[55] = "rawat_kacamata_refraksi";
          $dbField[56] = "rawat_mata_od_koreksi_spheris";
          $dbField[57] = "rawat_mata_od_koreksi_cylinder";
          $dbField[58] = "rawat_mata_od_koreksi_sudut";
          $dbField[59] = "rawat_mata_os_koreksi_spheris";
          $dbField[60] = "rawat_mata_os_koreksi_cylinder";
          $dbField[61] = "rawat_mata_os_koreksi_sudut";
          $dbField[62] = "rawat_tanggal";
          $dbField[63] = "rawat_od_vitreus"; 
          $dbField[64] = "rawat_os_vitreus"; 
                    
          if($_POST["btnSaveRawat"]) $dbField[65] = "rawat_waktu";
          
          
          if(!$_POST["rawat_id"]) $_POST["rawat_id"] = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["rawat_keluhan"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["rawat_keadaan_umum"]);
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_scale_od"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["rawat_anel"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["rawat_schimer"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_gula_darah"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_darah_lengkap"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_tensi"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_nadi"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_nafas"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_palpebra"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_palpebra"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_conjunctiva"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_conjunctiva"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_cornea"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_cornea"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_coa"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_coa"]);
          $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_iris"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_iris"]);
          $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_pupil"]);
          $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_pupil"]);
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_lensa"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_lensa"]);
          $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_ocular"]);
          $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_ocular"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_retina"]);
          $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_retina"]);
          $dbValue[30] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[31] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_weight_od"]);
          $dbValue[32] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_pressure_od"]);
          $dbValue[33] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_foto"]);          
          $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_sketsa"]);
          $dbValue[35] = QuoteValue(DPE_CHAR,$_POST["rawat_tonometri_od"]);
          $dbValue[36] = QuoteValue(DPE_CHAR,$_POST["rawat_tonometri_os"]);
          $dbValue[37] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_jenis"]);
          $dbValue[38] = QuoteValue(DPE_NUMERICKEY,$_POST["rawat_anestesis_obat"]);
          $dbValue[39] = QuoteValue(DPE_CHAR,$_POST["rawat_anestesis_dosis"]);
          $dbValue[40] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_komp"]);
          $dbValue[41] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_pre"]);
          $dbValue[42] = QuoteValue(DPE_CHARKEY,$_POST["rawat_operasi_jenis"]);
          $dbValue[43] = QuoteValue(DPE_CHARKEY,$_POST["rawat_operasi_paket"]);
          $dbValue[44] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_weight_os"]);
          $dbValue[45] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_pressure_os"]);
          $dbValue[46] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_scale_os"]);
          $dbValue[47] = QuoteValue(DPE_CHAR,$_POST["rawat_color_blindness"]);
          $dbValue[48] = QuoteValue(DPE_CHAR,$_POST["rawat_catatan"]);
          $dbValue[49] = QuoteValue(DPE_CHAR,$_POST["rawat_irigasi"]);
          $dbValue[50] = QuoteValue(DPE_CHAR,$_POST["rawat_epilasi"]);
          $dbValue[51] = QuoteValue(DPE_CHAR,$_POST["rawat_suntikan"]);
          $dbValue[52] = QuoteValue(DPE_CHAR,$_POST["rawat_probing"]);
          $dbValue[53] = QuoteValue(DPE_CHAR,$_POST["rawat_flouorecsin"]);
          $dbValue[54] = QuoteValue(DPE_CHAR,$_POST["rawat_kesehatan"]);
          $dbValue[55] = QuoteValue(DPE_CHAR,$_POST["rawat_kacamata_refraksi"]);
          $dbValue[56] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_spheris"]);
          $dbValue[57] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_cylinder"]);
          $dbValue[58] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_sudut"]);
          $dbValue[59] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_spheris"]);
          $dbValue[60] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_cylinder"]);
          $dbValue[61] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_sudut"]);
          $dbValue[62] = QuoteValue(DPE_DATE,date("Y-m-d"));
          $dbValue[63] = QuoteValue(DPE_CHAR,$_POST["rawat_od_vitreus"]); 
          $dbValue[64] = QuoteValue(DPE_CHAR,$_POST["rawat_os_vitreus"]); 
          
          if($_POST["btnSaveRawat"]) $dbValue[65] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if ($_POST["btnSaveRawat"]) {
              $dtmodel->Insert() or die("insert  error");	
          } elseif ($_POST["btnUpdateRawat"]) {
              $dtmodel->Update() or die("update  error");	
          }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          //-- update tabel kontrol dokter --//
          $sql = "update klinik_rawatinap_kontrol_dokter set id_perawatan=".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." 
                  where kontrol_id=".QuoteValue(DPE_CHAR,$_POST["kontrol_id"]);
          $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          
          // -- ini insert ke tabel rawat icd
      		$sql = "delete from klinik.klinik_perawatan_icd where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
      		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_icd";
          $dbField[0] = "rawat_icd_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_icd";
          $dbField[3] = "rawat_icd_urut";
          $dbField[4] = "rawat_icd_odos";
          
          for($i=0,$n=count($_POST["rawat_icd_od_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_od_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OD");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_od_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }


          for($i=0,$n=count($_POST["rawat_icd_os_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_os_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OS");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_os_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }

			
		// -- ini insert ke tabel registrasi ina 
		$sql = "delete from klinik.klinik_registrasi_ina
				where id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql);
		
          if($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_JAMKESNAS_PUSAT) {
	
			$dbTable = "klinik.klinik_registrasi_ina";
			$dbField[0] = "reg_ina_id";   // PK
			$dbField[1] = "reg_ina_nama";
			$dbField[2] = "id_ina"; 
			$dbField[3] = "id_cust_usr";
			$dbField[4] = "id_reg";
			$dbField[5] = "reg_ina_jenis";
			$dbField[6] = "reg_ina_when";
			$dbField[7] = "reg_ina_who";
				
				$regInaId = $dtaccess->GetTransID();
				$dbValue[0] = QuoteValue(DPE_CHARKEY,$regInaId);
				$dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_klaim_nama"][0]);
				$dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_klaim_id"][0]);
				$dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
				$dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
				$dbValue[5] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
				$dbValue[6] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
				$dbValue[7] = QuoteValue(DPE_CHAR,$userData["name"]);
	
				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	
				$dtmodel->Insert() or die("insert  error");
				
				unset($dtmodel);
				unset($dbValue);
				unset($dbKey);
				unset($dbTable);
				
				$sql = "select * from klinik.klinik_ina_split
						where id_ina = ".QuoteValue(DPE_CHAR,$_POST["rawat_ina_klaim_id"][0])." and ina_split_nominal > 0 "; 
				$dataInaSplit = $dtaccess->FetchAll($sql);
				
				$dbTable = "klinik.klinik_registrasi_ina_split";
				
				$dbField[0] = "reg_ina_split_id";   // PK
				$dbField[1] = "id_reg";
				$dbField[2] = "id_reg_ina";
				$dbField[3] = "id_ina_split";
				$dbField[4] = "reg_ina_split_nominal";
				
				for($i=0,$n=count($dataInaSplit);$i<$n;$i++) {
					$dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
					$dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
					$dbValue[2] = QuoteValue(DPE_CHARKEY,$regInaId);
					$dbValue[3] = QuoteValue(DPE_CHARKEY,$dataInaSplit[$i]["ina_split_id"]);
					$dbValue[4] = QuoteValue(DPE_NUMERIC,$dataInaSplit[$i]["ina_split_nominal"]);
			
					$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
					$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
			
					$dtmodel->Insert() or die("insert  error");
					
					unset($dtmodel);
					unset($dbValue);
					unset($dbKey);
					
					$totalInoSplit += $dataInaSplit[$i]["ina_split_nominal"];
				}
				
				// -- ini update total nya 
				$sql = "update klinik.klinik_registrasi_ina set reg_ina_nominal = ".QuoteValue(DPE_NUMERIC,$totalInoSplit)."
						where reg_ina_id = ".QuoteValue(DPE_CHAR,$regInaId);
				$dtaccess->Execute($sql); 
				
		}
          
          // -- ini insert ke tabel rawat icd
		$sql = "delete from klinik.klinik_perawatan_ina where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_ina";
          $dbField[0] = "rawat_ina_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_ina";
          $dbField[3] = "rawat_ina_urut";
          $dbField[4] = "rawat_ina_odos";
          
          for($i=0,$n=count($_POST["rawat_ina_od_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_od_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHARKEY,"OD");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_ina_od_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }

          for($i=0,$n=count($_POST["rawat_ina_os_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_os_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHARKEY,"OS");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_ina_os_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }

          
          // -- ini insert ke tabel rawat terapi
      		$sql = "delete from klinik.klinik_perawatan_terapi where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
      		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_terapi";
          $dbField[0] = "rawat_item_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_item";
          $dbField[3] = "rawat_item_urut";
          $dbField[4] = "id_dosis";
          
          for($i=0,$n=count($_POST["id_item"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["id_item"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHARKEY,$_POST["id_dosis"][$i]);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["id_item"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
          
          $sqlDelete = "delete from klinik.klinik_perawatan_suster where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]); 
          
          $dtaccess->Execute($sqlDelete);               
          
          //for($i=0,$n=count($dataRawat);$i<$n;$i++) {
               foreach($_POST["id_suster_rawat"] as $key => $value){
                    if($value) {
                         $dbTable = "klinik_perawatan_suster";
                    
                         $dbField[0] = "rawat_suster_id";   // PK
                         $dbField[1] = "id_rawat";
                         $dbField[2] = "id_pgw";
                                
                         $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
                         $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$value);
                         
                         //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField);
                         unset($dbValue);
                         unset($dbKey);
                    }
               }
          //}

          // --- insrt dokter ---
          $sql = "delete from klinik.klinik_perawatan_dokter where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_dokter"]) {
               
               $dbTable = "klinik_perawatan_dokter";
               
               $dbField[0] = "rawat_dokter_id";   // PK
               $dbField[1] = "id_rawat";
               $dbField[2] = "id_pgw";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_dokter"]);
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }
          
		     
          if($_POST["_x_mode"]!="Edit") {
               
               // --- nyimpen paket klaim e ---
               if($_POST["cmbNext"]==STATUS_SELESAI && $_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA) { 
               
                    $sql = "select b.paket_klaim_id, paket_klaim_total 
                              from klinik.klinik_biaya_pasien a
                              join klinik.klinik_paket_klaim b on a.id_paket_klaim = b.paket_klaim_id  
                              where a.biaya_pasien_status = ".QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN)."
                              and a.biaya_pasien_jenis = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]);
                    $rs = $dtaccess->Execute($sql);
                    
                    // --- delete dulu data yg lama ---
                    $sql = "delete from klinik.klinik_registrasi_klaim
                              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                    $dtaccess->Execute($sql);                    
                    
                    while($row = $dtaccess->Fetch($rs)) {
                    
                         $dbTable = "klinik.klinik_registrasi_klaim";
               
                         $dbField[0] = "reg_klaim_id";   // PK
                         $dbField[1] = "id_reg";
                         $dbField[2] = "id_paket_klaim";
                         $dbField[3] = "reg_klaim_nominal";
                         $dbField[4] = "reg_klaim_when";
                         $dbField[5] = "reg_klaim_who";
                         $dbField[6] = "reg_klaim_jenis";
                              
                              $klaimId =  $dtaccess->GetTransID();
                              $dbValue[0] = QuoteValue(DPE_CHAR,$klaimId);
                              $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                              $dbValue[2] = QuoteValue(DPE_CHAR,$row["paket_klaim_id"]);
                              $dbValue[3] = QuoteValue(DPE_NUMERIC,$row["paket_klaim_total"]);
                              $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                              $dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
                              $dbValue[6] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
                         
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField);
                         unset($dbValue);
                         unset($dbKey);
                         
                         
                         // --- ngisi tbl split e ---
                         $sql = "select klaim_split_id, klaim_split_nominal 
                                   from klinik.klinik_paket_klaim_split 
                                   where klaim_split_nominal > 0 
                                   and id_paket_klaim = ".QuoteValue(DPE_CHAR,$row["paket_klaim_id"]);
                         $rsSplit = $dtaccess->Execute($sql);
                         
                         while($rowSplit = $dtaccess->Fetch($rsSplit)) {
                         
                              $dbTable = "klinik.klinik_registrasi_klaim_split";
               
                              $dbField[0] = "reg_klaim_split_id";   // PK
                              $dbField[1] = "id_reg";
                              $dbField[2] = "id_klaim_split";
                              $dbField[3] = "id_reg_klaim";
                              $dbField[4] = "reg_klaim_split_nominal";
                                     
                                   $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                                   $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                                   $dbValue[2] = QuoteValue(DPE_CHAR,$rowSplit["klaim_split_id"]);
                                   $dbValue[3] = QuoteValue(DPE_CHAR,$klaimId);
                                   $dbValue[4] = QuoteValue(DPE_NUMERIC,$rowSplit["klaim_split_nominal"]);
                              
                              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                              
                              $dtmodel->Insert() or die("insert error"); 
                              
                              unset($dtmodel);
                              unset($dbField);
                              unset($dbValue);
                              unset($dbKey);
                              
                         }
                    }
               }
     
               
               // -- insert ke folio jika data gula diisi ---
               $sql = "select diag_id from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataDiag = $dtaccess->Fetch($sql);
          
     
               if(!$dataDiag) {
		
				$sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
						and id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])." and fol_jenis = '".STATUS_PEMERIKSAAN."'";
				$dtaccess->Execute($sql);

                    if($_POST["rawat_kesehatan"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_UJIMATA);

				if($sql_where) {
					$sql = "select * from klinik.klinik_biaya where ".implode(" or ",$sql_where);
					$dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
					$folWaktu = date("Y-m-d H:i:s");
				}               
               
	               $lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 


                    
				for($i=0,$n=count($dataBiaya);$i<$n;$i++) {
					$dbTable = "klinik_folio";
				
					$dbField[0] = "fol_id";   // PK
					$dbField[1] = "id_reg";
					$dbField[2] = "fol_nama";
					$dbField[3] = "fol_nominal";
					$dbField[4] = "id_biaya";
					$dbField[5] = "fol_jenis";
					$dbField[6] = "id_cust_usr";
					$dbField[7] = "fol_waktu";
					$dbField[8] = "fol_lunas";
					$dbField[9] = "fol_jumlah";
                    $dbField[10] = "fol_nominal_satuan";
						  
					$folId = $dtaccess->GetTransID();
					$dbValue[0] = QuoteValue(DPE_CHAR,$folId);
					$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
					$dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_nama"]);
					$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
					$dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
					$dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_jenis"]);
					$dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
					$dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
					$dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
					$dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
					
					//if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
					$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
					$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
					
					$dtmodel->Insert() or die("insert error"); 
					
					unset($dtmodel);
					unset($dbField);
					unset($dbValue);
					unset($dbKey);

				 
					$sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"])." and bea_split_nominal > 0";
					$dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
					
					for($a=0,$b=count($dataSplit);$a<$b;$a++) { 
						$dbTable = "klinik_folio_split";
					
						$dbField[0] = "folsplit_id";   // PK
						$dbField[1] = "id_fol";
						$dbField[2] = "id_split";
						$dbField[3] = "folsplit_nominal";
							  
						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
						$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$a]["id_split"]);
						$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["bea_split_nominal"]);
						 
						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
						
						$dtmodel->Insert() or die("insert error"); 
						
						unset($dtmodel);
						unset($dbField);
						unset($dbValue);
						unset($dbKey); 
    					} 
    
    				}
            }
               
          }
          
          echo "<script>document.location.href='".$thisPage."?id_reg=".$_POST["id_reg"]."&id_rawatinap=".$_POST["id_rawatinap"]."';</script>";
          exit();
          
  }
	//-- END OF PROSES PENYIMPANAN UNTUK TAB RAWAT / PEMERIKSAAN --//
	
	//-- PROSES PENYIMPANAN UNTUK TAB DIAGNOSTIK --//
		if ($_POST["btnSaveDiag"] || $_POST["btnUpdateDiag"]) {
	
          if($_POST["btnSaveDiag"]) {
               $sql = "delete from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
          }

          $dbTable = "klinik.klinik_diagnostik";
          $dbField[0] = "diag_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "diag_k1_nilai";
          $dbField[3] = "diag_k1_od";
          $dbField[4] = "diag_k1_os";
          $dbField[5] = "diag_k2_nilai";
          $dbField[6] = "diag_k2_od";
          $dbField[7] = "diag_k2_os";
          $dbField[8] = "diag_acial_od";
          $dbField[9] = "diag_acial_os";
          $dbField[10] = "diag_iol_od";
          $dbField[11] = "diag_iol_os";
          $dbField[12] = "diag_coa";
          $dbField[13] = "diag_lensa";
          $dbField[14] = "diag_retina";
          $dbField[15] = "diag_kesimpulan";
          $dbField[16] = "id_cust_usr";
          $dbField[17] = "diag_gambar_usg";
          $dbField[18] = "diag_gambar_fundus";
          $dbField[19] = "diag_gambar_humpre";
          $dbField[20] = "diag_av_constant";
          $dbField[21] = "diag_deviasi";
          $dbField[22] = "diag_rumus";
          $dbField[23] = "diag_waktu";
          $dbField[24] = "diag_ekg";
          $dbField[25] = "diag_fundus";
          $dbField[26] = "diag_opthalmoscop";
          $dbField[27] = "diag_oct";
          $dbField[28] = "diag_yag";
          $dbField[29] = "diag_argon";
          $dbField[30] = "diag_glaukoma";
          $dbField[31] = "diag_humpre";
          $dbField[32] = "diag_gambar_oct";
          $dbField[33] = "diag_lab_gula_darah";
          $dbField[34] = "diag_lab_darah_lengkap";
          
          if(!$_POST["diag_id"]) $_POST["diag_id"] = $dtaccess->GetTransID();
          
		      $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["diag_k1_nilai"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["diag_k1_od"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["diag_k1_os"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["diag_k2_nilai"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["diag_k2_od"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["diag_k2_os"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["diag_acial_od"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["diag_acial_os"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["diag_iol_od"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["diag_iol_os"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["diag_coa"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["diag_lensa"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["diag_retina"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["diag_kesimpulan"]);
          $dbValue[16] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_usg"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_fundus"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_humpre"]);
          $dbValue[20] = QuoteValue(DPE_CHARKEY,$_POST["diag_av_constant"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["diag_deviasi"]);
          $dbValue[22] = QuoteValue(DPE_CHARKEY,$_POST["diag_rumus"]);
          $dbValue[23] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["diag_ekg"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["diag_fundus"]);
          $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["diag_opthalmoscop"]);
          $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["diag_oct"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["diag_yag"]);
          $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["diag_argon"]);
          $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["diag_glaukoma"]);
          $dbValue[31] = QuoteValue(DPE_CHAR,$_POST["diag_humpre"]);
          $dbValue[32] = QuoteValue(DPE_CHAR,$_POST["diag_gambar_oct"]);
          $dbValue[33] = QuoteValue(DPE_CHAR,$_POST["diag_lab_gula_darah"]);
          $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["diag_lab_darah_lengkap"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if ($_POST["btnSaveDiag"]) {
              $dtmodel->Insert() or die("insert  error");	
          } else if ($_POST["btnUpdateDiag"]) {
              $dtmodel->Update() or die("update  error");	
          }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);     

          //-- update tabel kontrol dokter --//
          $sql = "update klinik_rawatinap_kontrol_dokter set id_diag=".QuoteValue(DPE_CHAR,$_POST["diag_id"])." 
                  where kontrol_id=".QuoteValue(DPE_CHAR,$_POST["kontrol_id"]);
          $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          
          // --- insrt suster ---
          $sql = "delete from klinik.klinik_diagnostik_suster where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
          $dtaccess->Execute($sql);
          
          foreach($_POST["id_suster_diag"] as $key => $value){
               if($value) {
                    $dbTable = "klinik_diagnostik_suster";
               
                    $dbField[0] = "diag_suster_id";   // PK
                    $dbField[1] = "id_diag";
                    $dbField[2] = "id_pgw";
                           
                    $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
                    $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$value);
                    
                    //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                    
                    $dtmodel->Insert() or die("insert error"); 
                    
                    unset($dtmodel);
                    unset($dbField);
                    unset($dbValue);
                    unset($dbKey);
               }
          }

          // --- insrt dokter ---
          $sql = "delete from klinik.klinik_diagnostik_dokter where id_diag = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_dokter"]) {
               
               $dbTable = "klinik_diagnostik_dokter";
               
               $dbField[0] = "diag_dokter_id";   // PK
               $dbField[1] = "id_diag";
               $dbField[2] = "id_pgw";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["diag_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_dokter"]);
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }
          

          if($_POST["btnSaveDiag"]) {
                                        
               // ---- inset ket folio ---//
               if($_POST["diag_k1_od"] || $_POST["diag_k1_os"]|| $_POST["diag_k2_od"] || $_POST["diag_k2_os"]) 
                    $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_KERATOMETRI);
                    
               if($_POST["diag_acial_od"] || $_POST["diag_acial_os"]|| $_POST["diag_iol_od"] || $_POST["diag_iol_os"] || $_POST["diag_av_constant"] || $_POST["diag_deviasi"] || $_POST["diag_rumus"]) 
                    $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_BIOMETRI);
                    
               if($_POST["diag_coa"] || $_POST["diag_lensa"]|| $_POST["diag_diag_retina"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_USG);
               if($_POST["diag_lab_gula_darah"] || $_POST["diag_lab_darah_lengkap"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_GULA);

               if($_POST["diag_ekg"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_EKG);
               if($_POST["diag_fundus"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_FUNDUS);
               if($_POST["diag_opthalmoscop"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_OPTHALMOSCOPY);
               if($_POST["diag_oct"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_OCT);
               if($_POST["diag_yag"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_YAG);
               if($_POST["diag_argon"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_ARGON);
               if($_POST["diag_glaukoma"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_GLAUKOMA);
               if($_POST["diag_humpre"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_HUMPREY);

               
               if($sql_where) {
                    $sql = "select * from klinik.klinik_biaya where ".implode(" or ",$sql_where);
                    $dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
                    $folWaktu = date("Y-m-d H:i:s");
               }               
               
               $lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 
               
               for($i=0,$n=count($dataBiaya);$i<$n;$i++) {
				$dbTable = "klinik_folio";
			
				$dbField[0] = "fol_id";   // PK
				$dbField[1] = "id_reg";
				$dbField[2] = "fol_nama";
				$dbField[3] = "fol_nominal";
				$dbField[4] = "id_biaya";
				$dbField[5] = "fol_jenis";
				$dbField[6] = "id_cust_usr";
				$dbField[7] = "fol_waktu";
				$dbField[8] = "fol_lunas";
				$dbField[9] = "fol_jumlah";
                    $dbField[10] = "fol_nominal_satuan";
						  
				$folId = $dtaccess->GetTransID();
                    $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                    $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_nama"]);
                    $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
                    $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
                    $dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_jenis"]);
                    $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
                    $dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
                    $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
                    $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
                    
                    //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                    
                    $dtmodel->Insert() or die("insert error"); 
                    
                    unset($dtmodel);
                    unset($dbValue);
                    unset($dbKey);
                    unset($dbField); 
				 
				$sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"])." and bea_split_nominal > 0";
				$dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
				
				for($a=0,$b=count($dataSplit);$a<$b;$a++) { 
					$dbTable = "klinik_folio_split";
				
					$dbField[0] = "folsplit_id";   // PK
					$dbField[1] = "id_fol";
					$dbField[2] = "id_split";
					$dbField[3] = "folsplit_nominal";
						  
					$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
					$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
					$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$a]["id_split"]);
					$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["bea_split_nominal"]);
					 
					$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
					$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
					
					$dtmodel->Insert() or die("insert error"); 
					
					unset($dtmodel);
					unset($dbField);
					unset($dbValue);
					unset($dbKey); 
				} 
               }
          }          
          
          echo "<script>document.location.href='".$thisPage."?id_reg=".$_POST["id_reg"]."&id_rawatinap=".$_POST["id_rawatinap"]."';</script>";
          exit();   

	}
	//-- END OF PROSES PENYIMPANAN UNTUK TAB DIAGNOSTIK --//
	
	//-- PROSES PENYIMPANAN UNTUK TAB REFRAKSI --//
		if ($_POST["btnSaveRef"] || $_POST["btnUpdateRef"]) {
	
          if($_POST["btnSaveRef"]) {
               $sql = "delete from klinik.klinik_refraksi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
          }

          $dbTable = "klinik.klinik_refraksi";
          $dbField[0] = "ref_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "ref_keluhan";
          $dbField[3] = "id_visus_nonkoreksi_od";
          $dbField[4] = "ref_mata_od_koreksi_spheris";
          $dbField[5] = "ref_mata_od_koreksi_cylinder";
          $dbField[6] = "ref_mata_od_koreksi_sudut";
          $dbField[7] = "id_visus_koreksi_od";
          $dbField[8] = "id_visus_nonkoreksi_os";
          $dbField[9] = "ref_mata_os_koreksi_spheris";
          $dbField[10] = "ref_mata_os_koreksi_cylinder";
          $dbField[11] = "ref_mata_os_koreksi_sudut";
          $dbField[12] = "id_visus_koreksi_os";
          $dbField[13] = "ref_pinhole_od";
          $dbField[14] = "ref_pinhole_os";
          $dbField[15] = "ref_streak_koreksi_spheris_od";
          $dbField[16] = "ref_streak_koreksi_cylinder_od";
          $dbField[17] = "ref_streak_koreksi_sudut_od";
          $dbField[18] = "ref_lenso_koreksi_spheris_od";
          $dbField[19] = "ref_lenso_koreksi_cylinder_od";
          $dbField[20] = "ref_lenso_koreksi_sudut_od";
          $dbField[21] = "ref_ark_koreksi_spheris_od";
          $dbField[22] = "ref_ark_koreksi_cylinder_od";
          $dbField[23] = "ref_ark_koreksi_sudut_od";
          $dbField[24] = "ref_prisma_koreksi_dioptri";
          $dbField[25] = "ref_prisma_koreksi_base1";
          $dbField[26] = "ref_prisma_koreksi_base2";
          $dbField[27] = "id_cust_usr";
          $dbField[28] = "ref_streak_koreksi_spheris_os";
          $dbField[29] = "ref_streak_koreksi_cylinder_os";
          $dbField[30] = "ref_streak_koreksi_sudut_os";
          $dbField[31] = "ref_lenso_koreksi_spheris_os";
          $dbField[32] = "ref_lenso_koreksi_cylinder_os";
          $dbField[33] = "ref_lenso_koreksi_sudut_os";
          $dbField[34] = "ref_ark_koreksi_spheris_os";
          $dbField[35] = "ref_ark_koreksi_cylinder_os";
          $dbField[36] = "ref_ark_koreksi_sudut_os";
          $dbField[37] = "ref_who_update";          
          $dbField[38] = "ref_tanggal";          
          if($_POST["btnSaveRef"]) $dbField[39] = "ref_when_update";
          
          
          if(!$_POST["ref_id"]) $_POST["ref_id"] = $dtaccess->GetTransID();
          
          $_POST["ref_pinhole_od"] = $_POST["ref_pinhole_od1"]."/".$_POST["ref_pinhole_od2"];
          $_POST["ref_pinhole_os"] = $_POST["ref_pinhole_os1"]."/".$_POST["ref_pinhole_os2"];
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["ref_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["ref_keluhan"]);
          $dbValue[3] = QuoteValue(DPE_CHARKEY,$_POST["id_visus_nonkoreksi_od"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_spheris"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_cylinder"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_sudut"]);
          $dbValue[7] = QuoteValue(DPE_CHARKEY,$_POST["id_visus_koreksi_od"]);
          $dbValue[8] = QuoteValue(DPE_CHARKEY,$_POST["id_visus_nonkoreksi_os"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_spheris"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_cylinder"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_sudut"]);
          $dbValue[12] = QuoteValue(DPE_CHARKEY,$_POST["id_visus_koreksi_os"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["ref_pinhole_od"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["ref_pinhole_os"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_spheris_od"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_cylinder_od"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_sudut_od"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_spheris_od"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_cylinder_od"]);
          $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_sudut_od"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_spheris_od"]);
          $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_cylinder_od"]);
          $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_sudut_od"]);
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["ref_prisma_koreksi_dioptri"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["ref_prisma_koreksi_base1"]);
          $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["ref_prisma_koreksi_base2"]);
          $dbValue[27] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_spheris_os"]);
          $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_cylinder_os"]);
          $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["ref_streak_koreksi_sudut_os"]);
          $dbValue[31] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_spheris_os"]);
          $dbValue[32] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_cylinder_os"]);
          $dbValue[33] = QuoteValue(DPE_CHAR,$_POST["ref_lenso_koreksi_sudut_os"]);
          $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_spheris_os"]);
          $dbValue[35] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_cylinder_os"]);
          $dbValue[36] = QuoteValue(DPE_CHAR,$_POST["ref_ark_koreksi_sudut_os"]);
          $dbValue[37] = QuoteValue(DPE_CHAR,$userData["name"]);          
          $dbValue[38] = QuoteValue(DPE_DATE,$_POST["ref_tanggal"]);          
          if($_POST["btnSaveRef"]) $dbValue[39] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if ($_POST["btnSaveRef"]) {
              $dtmodel->Insert() or die("insert  error");	
          } else if ($_POST["btnUpdateRef"]) {
              $dtmodel->Update() or die("update  error");	
          }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          //-- update tabel kontrol dokter --//
          $sql = "update klinik_rawatinap_kontrol_dokter set id_refraksi=".QuoteValue(DPE_CHAR,$_POST["ref_id"])." 
                  where kontrol_id=".QuoteValue(DPE_CHAR,$_POST["kontrol_id"]);
          $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          
          $sqlDelete = "delete from klinik.klinik_refraksi_suster where id_ref = ".QuoteValue(DPE_CHAR,$_POST["ref_id"]);
          $dtaccess->Execute($sqlDelete);
          
          //for($i=0,$n=count($dataRefraksi);$i<$n;$i++) {
               foreach($_POST["id_suster_ref"] as $key => $value){
                    if($value) {
                         $dbTable = "klinik_refraksi_suster";
                    
                         $dbField[0] = "ref_suster_id";   // PK
                         $dbField[1] = "id_ref";
                         $dbField[2] = "id_pgw";
                                
                         $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                         //$dbValue[1] = QuoteValue(DPE_CHAR,$dataRefraksi[$i]["ref_id"]);
                         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["ref_id"]);
                         $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$value);
                         
                         //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField);
                         unset($dbValue);
                         unset($dbKey);
                    }
               }
          //}
          
          
          if($_POST["btnSaveRef"]) { 
               // -- insert ke folio jika data ark diisi ---
               if($_POST["ref_ark_koreksi_spheris_od"] || $_POST["ref_ark_koreksi_cylinder_od"] || $_POST["ref_ark_koreksi_sudut_od"] || $_POST["ref_ark_koreksi_spheris_os"] || $_POST["ref_ark_koreksi_cylinder_os"] || $_POST["ref_ark_koreksi_sudut_os"]) {
                    $sql = "select * from klinik.klinik_biaya where biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_ARK); 
                    $dataBiaya = $dtaccess->Fetch($sql,DB_SCHEMA);  
                    
                    $lunas = ($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_SWADAYA)?'n':'y';
                    
                    $dbTable = "klinik_folio";
               
                    $dbField[0] = "fol_id";   // PK
                    $dbField[1] = "id_reg";
                    $dbField[2] = "fol_nama";
                    $dbField[3] = "fol_nominal";
                    $dbField[4] = "id_biaya";
                    $dbField[5] = "fol_jenis";
                    $dbField[6] = "id_cust_usr";
                    $dbField[7] = "fol_waktu";
                    $dbField[8] = "fol_lunas";
                    $dbField[9] = "fol_jumlah";
                    $dbField[10] = "fol_nominal_satuan";
                    
				$folId = $dtaccess->GetTransID();
                    $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                    $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_nama"]);
                    $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
                    $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_id"]);
                    $dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_jenis"]);
                    $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
                    $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                    $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
                    $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
                    
                    //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                    
                    $dtmodel->Insert() or die("insert error"); 
                    
                    unset($dtmodel);
                    unset($dbField);
                    unset($dbValue);
                    unset($dbKey);
				
				$sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_ARK)." and bea_split_nominal > 0";
				$dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
				
				for($a=0,$b=count($dataSplit);$a<$b;$a++) {
					$dbTable = "klinik_folio_split";
				
					$dbField[0] = "folsplit_id";   // PK
					$dbField[1] = "id_fol";
					$dbField[2] = "id_split";
					$dbField[3] = "folsplit_nominal";
						  
					$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
					$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
					$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$a]["id_split"]);
					$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["bea_split_nominal"]);
					 
					$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
					$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
					
					$dtmodel->Insert() or die("insert error"); 
					
					unset($dtmodel);
					unset($dbField);
					unset($dbValue);
					unset($dbKey);
				}
               }
                
          }
          
          echo "<script>document.location.href='".$thisPage."?id_reg=".$_POST["id_reg"]."&id_rawatinap=".$_POST["id_rawatinap"]."';</script>";
          exit();

	}
	//-- END OF PROSES PENYIMPANAN UNTUK TAB REFRAKSI --//
	
	//-- PROSES PENYIMPANAN UNTUK TAB PRE-OP --//
	if ($_POST["btnSavePreop"] || $_POST["btnUpdatePreop"]) {
		if(!$_POST["preop_regulasi_berhasil"]) $_POST["preop_regulasi_berhasil"] = "n";
		if(!$_POST["preop_regulasi"]) $_POST["preop_regulasi"] = "n";
		
		if($_POST["btnSavePreop"]) {
               $sql = "delete from klinik.klinik_preop where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
		}
		
          $dbTable = "klinik.klinik_preop";
          $dbField[0] = "preop_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "preop_keluhan";
          $dbField[3] = "preop_keadaan_umum";
          $dbField[4] = "preop_tonometri_scale_od";
          $dbField[5] = "preop_lab_gula_darah";
          $dbField[6] = "preop_lab_darah_lengkap";
          $dbField[7] = "preop_lab_tensi";
          $dbField[8] = "preop_lab_nadi";
          $dbField[9] = "preop_lab_nafas";
          $dbField[10] = "preop_mata_lokal";
          $dbField[11] = "id_cust_usr";
          $dbField[12] = "preop_tonometri_weight_od";
          $dbField[13] = "preop_tonometri_pressure_od";
          $dbField[14] = "preop_waktu";
          $dbField[15] = "preop_tonometri_od";
          $dbField[16] = "preop_tonometri_os";
          $dbField[17] = "preop_tonometri_weight_os";
          $dbField[18] = "preop_tonometri_pressure_os";
          $dbField[19] = "preop_tonometri_scale_os";
          $dbField[20] = "preop_regulasi";
          $dbField[21] = "preop_regulasi_gula_obat";
          $dbField[22] = "preop_regulasi_gula_hasil";
          $dbField[23] = "preop_regulasi_tono_obat";
          $dbField[24] = "preop_regulasi_tono_hasil";
          $dbField[25] = "preop_regulasi_berhasil";
          $dbField[26] = "preop_catatan_ok";
          $dbField[27] = "preop_terapi_obat";
          $dbField[28] = "preop_terapi_saran";
          $dbField[29] = "preop_anestesis_jenis";
          $dbField[30] = "preop_anestesis_obat";
          $dbField[31] = "preop_anestesis_dosis";
          $dbField[32] = "preop_anestesis_komp";
          $dbField[33] = "preop_anestesis_pre";
          $dbField[34] = "preop_iol_jenis";
          $dbField[35] = "preop_iol_merk";
          $dbField[36] = "preop_k1_od";
          $dbField[37] = "preop_k1_os";
          $dbField[38] = "preop_k2_od";
          $dbField[39] = "preop_k2_os";
          $dbField[40] = "preop_acial_od";
          $dbField[41] = "preop_acial_os";
          $dbField[42] = "preop_iol_od";
          $dbField[43] = "preop_iol_os";
          $dbField[44] = "preop_av_constant";
          $dbField[45] = "preop_deviasi";
          $dbField[46] = "preop_rumus";
          $dbField[47] = "preop_operasi_paket";
          $dbField[48] = "preop_operasi_jenis";
          
          if(!$_POST["preop_id"]) $_POST["preop_id"] = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["preop_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["preop_keluhan"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["preop_keadaan_umum"]);
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_scale_od"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["preop_lab_gula_darah"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["preop_lab_darah_lengkap"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["preop_lab_tensi"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["preop_lab_nadi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["preop_lab_nafas"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["preop_mata_lokal"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_weight_od"]);
          $dbValue[13] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_pressure_od"]);
          $dbValue[14] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["preop_tonometri_od"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["preop_tonometri_os"]);
          $dbValue[17] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_weight_os"]);
          $dbValue[18] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_pressure_os"]);
          $dbValue[19] = QuoteValue(DPE_NUMERIC,$_POST["preop_tonometri_scale_os"]);
          $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_gula_obat"]);
          $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_gula_hasil"]);
          $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_tono_obat"]);
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_tono_hasil"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["preop_regulasi_berhasil"]);
          $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["preop_catatan_ok"]);
          $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["preop_terapi_obat"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["preop_terapi_saran"]);
          $dbValue[29] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_jenis"]);
          $dbValue[30] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_obat"]);
          $dbValue[31] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_dosis"]);
          $dbValue[32] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_komp"]);
          $dbValue[33] = QuoteValue(DPE_CHARKEY,$_POST["preop_anestesis_pre"]);
          $dbValue[34] = QuoteValue(DPE_CHARKEY,$_POST["preop_iol_jenis"]);
          $dbValue[35] = QuoteValue(DPE_CHARKEY,$_POST["preop_iol_merk"]);
          $dbValue[36] = QuoteValue(DPE_CHAR,$_POST["preop_k1_od"]);
          $dbValue[37] = QuoteValue(DPE_CHAR,$_POST["preop_k1_os"]);
          $dbValue[38] = QuoteValue(DPE_CHAR,$_POST["preop_k2_od"]);
          $dbValue[39] = QuoteValue(DPE_CHAR,$_POST["preop_k2_os"]);
          $dbValue[40] = QuoteValue(DPE_CHAR,$_POST["preop_acial_od"]);
          $dbValue[41] = QuoteValue(DPE_CHAR,$_POST["preop_acial_os"]);
          $dbValue[42] = QuoteValue(DPE_CHAR,$_POST["preop_iol_od"]);
          $dbValue[43] = QuoteValue(DPE_CHAR,$_POST["preop_iol_os"]);
          $dbValue[44] = QuoteValue(DPE_CHARKEY,$_POST["preop_av_constant"]);
          $dbValue[45] = QuoteValue(DPE_CHAR,$_POST["preop_deviasi"]);
          $dbValue[46] = QuoteValue(DPE_CHARKEY,$_POST["preop_rumus"]);
          $dbValue[47] = QuoteValue(DPE_CHARKEY,$_POST["preop_operasi_paket"]);
          $dbValue[48] = QuoteValue(DPE_CHARKEY,$_POST["preop_operasi_jenis"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if ($_POST["btnSavePreop"]) {
              $dtmodel->Insert() or die("insert  error");	
          } elseif ($_POST["btnUpdatePreop"]) {
              $dtmodel->Update() or die("update  error");	
          }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey); 
		 
          //-- update tabel kontrol dokter --//
          $sql = "update klinik_rawatinap_kontrol_dokter set id_preop=".QuoteValue(DPE_CHAR,$_POST["preop_id"])." 
                  where kontrol_id=".QuoteValue(DPE_CHAR,$_POST["kontrol_id"]);
          $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          
               $sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
                         and id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])." and fol_jenis = '".STATUS_PREOP."'";
               $dtaccess->Execute($sql);

          
               if($_POST["preop_lab_gula_darah"] || $_POST["preop_lab_darah_lengkap"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_GULA_PREOP);               
               if($_POST["preop_regulasi"]=="y" && ($_POST["preop_regulasi_gula_obat"] || $_POST["preop_regulasi_gula_hasil"])) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_GULAREGULASI_PREOP);               
               	

               if($sql_where) {
					$sql = "select * from klinik.klinik_biaya where ".implode(" or ",$sql_where);
					$dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
					$folWaktu = date("Y-m-d H:i:s");
			}
               $lunas = ($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_SWADAYA)?'n':'y';

               for($i=0,$n=count($dataBiaya);$i<$n;$i++) {
                    $dbTable = "klinik_folio";
               
                    $dbField[0] = "fol_id";   // PK
                    $dbField[1] = "id_reg";
                    $dbField[2] = "fol_nama";
                    $dbField[3] = "fol_nominal";
                    $dbField[4] = "id_biaya";
                    $dbField[5] = "fol_jenis";
                    $dbField[6] = "id_cust_usr";
                    $dbField[7] = "fol_waktu";
                    $dbField[8] = "fol_lunas";
                    $dbField[9] = "fol_jumlah";
                    $dbField[10] = "fol_nominal_satuan";
                           
                    $folId = $dtaccess->GetTransID();
                    $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                    $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_nama"]);
                    $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
                    $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
                    $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_PREOP);
                    $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
                    $dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
                    $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
                    $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
                    
                    //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                    
                    $dtmodel->Insert() or die("insert error"); 
                    
                    unset($dtmodel);
                    unset($dbField);
                    unset($dbValue);
                    unset($dbKey);

                
                    $sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"])." and bea_split_nominal > 0";
                    $dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
                    
                    for($a=0,$b=count($dataSplit);$a<$b;$a++) { 
                         $dbTable = "klinik_folio_split";
                    
                         $dbField[0] = "folsplit_id";   // PK
                         $dbField[1] = "id_fol";
                         $dbField[2] = "id_split";
                         $dbField[3] = "folsplit_nominal";
                                
                         $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                         $dbValue[1] = QuoteValue(DPE_CHAR,$folId);
                         $dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$a]["id_split"]);
                         $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["bea_split_nominal"]);
                          
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField);
                         unset($dbValue);
                         unset($dbKey); 
                    } 

               }
		
		 // --- insrt suster ---
          $sql = "delete from klinik.klinik_preop_suster where id_preop = ".QuoteValue(DPE_CHAR,$_POST["preop_id"]);
          $dtaccess->Execute($sql);
          
          foreach($_POST["id_suster_preop"] as $key => $value){
               if($value) {
                    $dbTable = "klinik_preop_suster";
               
                    $dbField[0] = "preop_suster_id";   // PK
                    $dbField[1] = "id_preop";
                    $dbField[2] = "id_pgw";
                           
                    $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["preop_id"]);
                    $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$value);
                    
                    //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                    
                    $dtmodel->Insert() or die("insert error"); 
                    
                    unset($dtmodel);
                    unset($dbField);
                    unset($dbValue);
                    unset($dbKey);
               }
          }
		

          // --- insrt dokter ---
          $sql = "delete from klinik.klinik_preop_dokter where id_preop = ".QuoteValue(DPE_CHAR,$_POST["preop_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_dokter"]) {
               
               $dbTable = "klinik_preop_dokter";
               
               $dbField[0] = "preop_dokter_id";   // PK
               $dbField[1] = "id_preop";
               $dbField[2] = "id_pgw";
                      
               $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["preop_id"]);
               $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_dokter"]);
               
               //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               $dtmodel->Insert() or die("insert error"); 
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }

          echo "<script>document.location.href='".$thisPage."?id_reg=".$_POST["id_reg"]."&id_rawatinap=".$_POST["id_rawatinap"]."';</script>";
          exit();   

	}
	//-- END OF PROSES PENYIMPANAN UNTUK TAB PRE-OP --//
	
	//-- PROSES PENYIMPANAN UNTUK TAB BEDAH --//
	 if($_POST["btnSaveOP"] || $_POST["btnUpdateOP"]) {
          
          if($_POST["btnSaveOP"]) {
               $sql = "delete from klinik.klinik_perawatan_operasi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
		}

          $dbTable = "klinik.klinik_perawatan_operasi";
          $dbField[0] = "op_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "op_jam_mulai";
          $dbField[3] = "op_jam_selesai";
          $dbField[4] = "op_tanggal";
          $dbField[5] = "id_op_jenis";
          $dbField[6] = "id_ina";
          $dbField[7] = "op_pesan";
          $dbField[8] = "op_tipe";
          $dbField[9] = "id_dokter";
          $dbField[10] = "id_suster";
          $dbField[11] = "id_cust_usr";
          $dbField[12] = "op_paket_biaya";
                    
          if(!$_POST["op_id"]) $_POST["op_id"] = $dtaccess->GetTransID();
          $_POST["op_jam_mulai"] = $_POST["op_mulai_jam"].":".$_POST["op_mulai_menit"].":00";
          $_POST["op_jam_selesai"] = $_POST["op_selesai_jam"].":".$_POST["op_selesai_menit"].":00";
          $_POST["op_tanggal"] = date("Y-m-d");
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["op_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_DATETIME,$_POST["op_jam_mulai"]);
          $dbValue[3] = QuoteValue(DPE_DATETIME,$_POST["op_jam_selesai"]);
          $dbValue[4] = QuoteValue(DPE_DATE,$_POST["op_tanggal"]);
          $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["id_op_jenis"]);
          $dbValue[6] = QuoteValue(DPE_CHARKEY,$_POST["id_ina"]);
          $dbValue[7] = QuoteValue(DPE_CHARKEY,$_POST["op_pesan"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,"B");
          $dbValue[9] = QuoteValue(DPE_NUMERICKEY,$_POST["id_dokter"]);
          $dbValue[10] = QuoteValue(DPE_NUMERICKEY,$_POST["id_suster"]);
          $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
          $dbValue[12] = QuoteValue(DPE_CHARKEY,$_POST["op_paket_biaya"]);
          

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if ($_POST["btnSaveOP"]) {
              $dtmodel->Insert() or die("insert  error");	
          } elseif ($_POST["btnUpdateOP"]) {
              $dtmodel->Update() or die("update  error");	
          }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          //-- update tabel kontrol dokter --//
          $sql = "update klinik_rawatinap_kontrol_dokter set id_op=".QuoteValue(DPE_CHAR,$_POST["op_id"])." 
                  where kontrol_id=".QuoteValue(DPE_CHAR,$_POST["kontrol_id"]);
          $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          
          // --- insert ke tabel perawatan_operasi_icd
          $sql = "delete from klinik.klinik_perawatan_operasi_icd where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);
          
          $dbTable = "klinik.klinik_perawatan_operasi_icd";
          
          $dbField[0] = "rwt_op_icd_id";
          $dbField[1] = "id_op";
          $dbField[2] = "id_icd";
          
          foreach($_POST["op_icd_kode"] as $key=>$value) {
               if($value) {
               
                    $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
                    $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["op_id"]);
                    $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["id_icd"][$key]);
     
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dbKey[1] = 1; 
                    
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
                    $dtmodel->Insert() or die("insert  error");
                    
                    unset($dtmodel);
                    unset($dbValue);
                    unset($dbKey);
               }
          }
          
          // -- ini insert ke tabel durante OP
		$sql = "delete from klinik.klinik_perawatan_duranteop where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_duranteop";
          $dbField[0] = "id_op";
          $dbField[1] = "id_durop_komp";
          
          if($_POST["id_durop_komp"]) {
               foreach($_POST["id_durop_komp"] as $key=>$value) {
                    
                    $dbValue[0] = QuoteValue(DPE_CHARKEY,$_POST["op_id"]);
                    $dbValue[1] = QuoteValue(DPE_CHARKEY,$key);
     
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dbKey[1] = 1; 
                    
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
                    $dtmodel->Insert() or die("insert  error");
                    
                    unset($dtmodel);
                    unset($dbValue);
                    unset($dbKey);
               }
          }
          
          
          // --- insert ke tabel perawatan injeksi ---
          $sql = "delete from klinik.klinik_perawatan_injeksi where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);
          
          $sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
                    and id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_INJEKSI);
          $dtaccess->Execute($sql);
          
                    
          if($_POST["id_item"]) {
          
               $dbTable = "klinik.klinik_perawatan_injeksi";
               $dbField[0] = "rawat_injeksi_id";
               $dbField[1] = "id_op";
               $dbField[2] = "id_dosis";
               $dbField[3] = "id_injeksi";
               $dbField[4] = "id_item";
          
               foreach($_POST["id_item"] as $key=>$value) {
                    
                    if($value) {
                         $rawatInjeksiId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHARKEY,$rawatInjeksiId);
                         $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["op_id"]);
                         $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["id_dosis"][$key]);
                         $dbValue[3] = QuoteValue(DPE_CHARKEY,$_POST["id_injeksi"][$key]);
                         $dbValue[4] = QuoteValue(DPE_NUMERICKEY,$value);
          
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dbKey[1] = 1; 
                         
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
                         $dtmodel->Insert() or die("insert  error");
                         
                         unset($dtmodel);
                         unset($dbValue);
                         unset($dbKey);
                         
                         $statusInjeksi = true;               
                    }
               }
               
               if($statusInjeksi) {
                    // --- insert d folio n folio_split ---
                    $sql = "select biaya_nama, biaya_total, biaya_jenis  
                              from klinik.klinik_biaya 
                              where biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_INJEKSI);
                    $rs = $dtaccess->Execute($sql);
                    $dataBiaya = $dtaccess->Fetch($rs);
                    
                    $lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 
                    
                    $dbTable = "klinik.klinik_folio";
                    $dbField[0] = "fol_id";
                    $dbField[1] = "id_reg";
                    $dbField[2] = "fol_nama";
                    $dbField[3] = "fol_nominal";
                    $dbField[4] = "id_biaya";
                    $dbField[5] = "fol_jenis";
                    $dbField[6] = "id_cust_usr";
                    $dbField[7] = "fol_waktu";
                    $dbField[8] = "fol_lunas";
                    $dbField[9] = "fol_jumlah";
                    $dbField[10] = "fol_nominal_satuan";
                    
                         $folioId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHARKEY,$folioId);
                         $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
                         $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_nama"]);
                         $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
                         $dbValue[4] = QuoteValue(DPE_CHARKEY,BIAYA_INJEKSI);
                         $dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_jenis"]);
                         $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);                    
                         $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                         $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
                         $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
          
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value 
                    
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
                    $dtmodel->Insert() or die("insert  error");
                    
                    unset($dtmodel);
                    unset($dbValue);
                    unset($dbKey);
                    
                    
                    $sql = "select bea_split_nominal, id_split
                              from klinik.klinik_biaya_split
                              where id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_INJEKSI)."
                              and bea_split_nominal > 0";
                    $rs = $dtaccess->Execute($sql);
                    
                    $dbTable = "klinik.klinik_folio_split";
                    $dbField[0] = "folsplit_id";
                    $dbField[1] = "id_fol";
                    $dbField[2] = "id_split";
                    $dbField[3] = "folsplit_nominal";
                    
                    while($row = $dtaccess->Fetch($rs)) {
                    
                         $splitId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHARKEY,$splitId);
                         $dbValue[1] = QuoteValue(DPE_CHARKEY,$folioId);
                         $dbValue[2] = QuoteValue(DPE_CHAR,$row["id_split"]);
                         $dbValue[3] = QuoteValue(DPE_NUMERIC,$row["bea_split_nominal"]);
                         
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value 
                    
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
                         $dtmodel->Insert() or die("insert  error");
                         
                         unset($dtmodel);
                         unset($dbValue);
                         unset($dbKey);
                         
                    }
               }
          }
          
          // --- insrt suster ---
          $sql = "delete from klinik.klinik_operasi_suster where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);
          
          if($_POST["id_suster_op"]) {
               foreach($_POST["id_suster_op"] as $key => $value){
                    if($value) {
                         $dbTable = "klinik_operasi_suster";
                    
                         $dbField[0] = "op_suster_id";   // PK
                         $dbField[1] = "id_op";
                         $dbField[2] = "id_pgw";
                                
                         $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["op_id"]);
                         $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$value);
                         
                         //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField);
                         unset($dbValue);
                         unset($dbKey);
                    }
               }
          }
          
          /*if($_POST["btnSaveOP"]) {
            if($_POST["cmbNext"]==STATUS_RAWATINAP){
               $sql = "update klinik.klinik_registrasi set reg_status = '".$_POST["cmbNext"].STATUS_ANTRI."', reg_waktu = CURRENT_TIME where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql); 
            }else{
               $sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_SELESAI."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql); 
            }*/
               
			$sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
					and fol_jenis = ".QuoteValue(DPE_CHAR,STATUS_BEDAH);
			$dtaccess->Execute($sql);
			
			
			// --- nyimpen folio paket operasi ----
			if($_POST["op_paket_biaya"]) {
			
				$sql = "select * from klinik.klinik_operasi_paket where op_paket_id = ".QuoteValue(DPE_CHAR,$_POST["op_paket_biaya"]); 
				$dataBiaya = $dtaccess->Fetch($sql,DB_SCHEMA);
				$folWaktu = date("Y-m-d H:i:s");
				$lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 
				
	
				$dbTable = "klinik_folio";
					
				$dbField[0] = "fol_id";   // PK
				$dbField[1] = "id_reg";
				$dbField[2] = "fol_nama";
				$dbField[3] = "fol_nominal";
				$dbField[4] = "fol_lunas";
				$dbField[5] = "fol_jenis";
				$dbField[6] = "id_cust_usr";
				$dbField[7] = "fol_waktu"; 
				$dbField[8] = "fol_jumlah";
        $dbField[9] = "fol_nominal_satuan";
				
				$folId = $dtaccess->GetTransID();
				$dbValue[0] = QuoteValue(DPE_CHAR,$folId);
				$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
				$dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["op_paket_nama"]);
				$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["op_paket_total"]);
				$dbValue[4] = QuoteValue(DPE_CHAR,$lunas);
				$dbValue[5] = QuoteValue(DPE_CHAR,STATUS_BEDAH);
				$dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
				$dbValue[7] = QuoteValue(DPE_DATE,$folWaktu); 
				$dbValue[8] = QuoteValue(DPE_NUMERIC,'1');
			$dbValue[9] = QuoteValue(DPE_NUMERIC,$dataBiaya["op_paket_total"]);
				 
				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
				
				$dtmodel->Insert() or die("insert error"); 
				
				unset($dtmodel);
				unset($dbValue);
				unset($dbKey);
				unset($dbField);  
				 
				$sql = "select * from klinik.klinik_operasi_paket_split
						where id_op_paket = ".QuoteValue(DPE_CHAR,$dataBiaya["op_paket_id"])." and op_paket_split_nominal > 0"; 
				$dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
				
				for($a=0,$b=count($dataSplit);$a<$b;$a++) { 
					$dbTable = "klinik_folio_split";
				
					$dbField[0] = "folsplit_id";   // PK
					$dbField[1] = "id_fol";
					$dbField[2] = "id_split";
					$dbField[3] = "folsplit_nominal";
					 
					$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
					$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
					$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$a]["id_split"]);
					$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["op_paket_split_nominal"]);
					 
					$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
					$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
					
					$dtmodel->Insert() or die("insert error"); 
					
					unset($dtmodel);
					unset($dbField);
					unset($dbValue);
					unset($dbKey); 
				}
			}
			
			
			
               // --- nyimpen paket klaim e ---
               if($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA) { 
               
                    $sql = "select b.paket_klaim_id, paket_klaim_total 
                              from klinik.klinik_biaya_pasien a
                              join klinik.klinik_paket_klaim b on a.id_paket_klaim = b.paket_klaim_id  
                              where a.biaya_pasien_status = ".QuoteValue(DPE_CHAR,STATUS_BEDAH)."
                              and a.biaya_pasien_jenis = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]);
                    $rs = $dtaccess->Execute($sql);
                    
                    // --- delete dulu data yg lama ---
                    $sql = "delete from klinik.klinik_registrasi_klaim
                              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                    $dtaccess->Execute($sql);                    
                    
                    while($row = $dtaccess->Fetch($rs)) {
                    
                         $dbTable = "klinik.klinik_registrasi_klaim";
               
                         $dbField[0] = "reg_klaim_id";   // PK
                         $dbField[1] = "id_reg";
                         $dbField[2] = "id_paket_klaim";
                         $dbField[3] = "reg_klaim_nominal";
                         $dbField[4] = "reg_klaim_when";
                         $dbField[5] = "reg_klaim_who";
                         $dbField[6] = "reg_klaim_jenis";
                              
                              $klaimId =  $dtaccess->GetTransID();
                              $dbValue[0] = QuoteValue(DPE_CHAR,$klaimId);
                              $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                              $dbValue[2] = QuoteValue(DPE_CHAR,$row["paket_klaim_id"]);
                              $dbValue[3] = QuoteValue(DPE_NUMERIC,$row["paket_klaim_total"]);
                              $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                              $dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
                              $dbValue[6] = QuoteValue(DPE_CHAR,STATUS_BEDAH);
                         
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField);
                         unset($dbValue);
                         unset($dbKey);
                         
                         
                         // --- ngisi tbl split e ---
                         $sql = "select klaim_split_id, klaim_split_nominal 
                                   from klinik.klinik_paket_klaim_split 
                                   where klaim_split_nominal > 0 
                                   and id_paket_klaim = ".QuoteValue(DPE_CHAR,$row["paket_klaim_id"]);
                         $rsSplit = $dtaccess->Execute($sql);
                         
                         while($rowSplit = $dtaccess->Fetch($rsSplit)) {
                         
                              $dbTable = "klinik.klinik_registrasi_klaim_split";
               
                              $dbField[0] = "reg_klaim_split_id";   // PK
                              $dbField[1] = "id_reg";
                              $dbField[2] = "id_klaim_split";
                              $dbField[3] = "id_reg_klaim";
                              $dbField[4] = "reg_klaim_split_nominal";
                                     
                                   $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                                   $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                                   $dbValue[2] = QuoteValue(DPE_CHAR,$rowSplit["klaim_split_id"]);
                                   $dbValue[3] = QuoteValue(DPE_CHAR,$klaimId);
                                   $dbValue[4] = QuoteValue(DPE_NUMERIC,$rowSplit["klaim_split_nominal"]);
                              
                              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                              
                              $dtmodel->Insert() or die("insert error"); 
                              
                              unset($dtmodel);
                              unset($dbField);
                              unset($dbValue);
                              unset($dbKey);
                              
                         }
                    }
               }
               
               // --- ngisi klaim INA klo pasien JAMKESMAS PUSAT ---
			if($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_JAMKESNAS_PUSAT && $_POST["id_ina"]){
			
                    // --- delete data e dulu ---
                    $sql = "delete from klinik.klinik_registrasi_ina
                              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
                              and reg_ina_jenis = ".QuoteValue(DPE_CHAR,STATUS_OPERASI);
                    $rs = $dtaccess->Execute($sql);                    
                    
                    $sql = "select ina_nama, ina_nominal
                              from klinik.klinik_ina
                              where ina_id = ".QuoteValue(DPE_CHAR,$_POST["id_ina"]);
                    $rsIna = $dtaccess->Execute($sql);
                    
                    while($rowIna = $dtaccess->Fetch($rsIna)) {
                    
                         $dbTable = "klinik_registrasi_ina";
				
          			$dbField[0] = "reg_ina_id";   // PK
          			$dbField[1] = "reg_ina_nama";
          			$dbField[2] = "id_ina";
          			$dbField[3] = "reg_ina_nominal";
          			$dbField[4] = "id_cust_usr";
                         $dbField[5] = "id_reg";
                         $dbField[6] = "reg_ina_jenis";
                         $dbField[7] = "reg_ina_when";
                         $dbField[8] = "reg_ina_who";
          			
          				$regInaId = $dtaccess->GetTransID();
          				$dbValue[0] = QuoteValue(DPE_CHAR,$regInaId);
          				$dbValue[1] = QuoteValue(DPE_CHAR,$rowIna["ina_nama"]);
          				$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_ina"]);
          				$dbValue[3] = QuoteValue(DPE_NUMERIC,$rowIna["ina_nominal"]); 
          				$dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
                              $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                              $dbValue[6] = QuoteValue(DPE_CHAR,STATUS_OPERASI);
                              $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                              $dbValue[8] = QuoteValue(DPE_CHAR,$userData["name"]);
          			 
          			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
          			
          			$dtmodel->Insert() or die("insert error"); 
          			
          			unset($dtmodel);
          			unset($dbValue);
          			unset($dbKey);
          			unset($dbField);  
          			
          			
          			// --- nyimpen split e ---
          			$sql = "select ina_split_id, ina_split_nominal 
                                   from klinik.klinik_ina_split
                                   where id_ina = ".QuoteValue(DPE_CHAR,$_POST["id_ina"])."
                                   and ina_split_nominal > 0";
                         $rsSplit = $dtaccess->Execute($sql);
                         
                         while($rowSplit = $dtaccess->Fetch($rsSplit)) {
                         
                              $dbTable = "klinik_registrasi_ina_split";
				
               			$dbField[0] = "reg_ina_split_id";   // PK
               			$dbField[1] = "id_reg";
               			$dbField[2] = "id_reg_ina";
               			$dbField[3] = "id_ina_split";
               			$dbField[4] = "reg_ina_split_nominal";
               			
               				$regInaSplitId = $dtaccess->GetTransID();
               				$dbValue[0] = QuoteValue(DPE_CHAR,$regInaSplitId);
               				$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               				$dbValue[2] = QuoteValue(DPE_CHAR,$regInaId);
               				$dbValue[3] = QuoteValue(DPE_CHAR,$rowSplit["ina_split_id"]); 
               				$dbValue[4] = QuoteValue(DPE_NUMERIC,$rowSplit["ina_split_nominal"]);
               			 
               			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               			
               			$dtmodel->Insert() or die("insert error"); 
               			
               			unset($dtmodel);
               			unset($dbValue);
               			unset($dbKey);
               			unset($dbField);  
                         }
                         
                    }
          }

          echo "<script>document.location.href='".$thisPage."?id_reg=".$_POST["id_reg"]."&id_rawatinap=".$_POST["id_rawatinap"]."';</script>";
          exit();   
     }
	//-- END OF PROSES PENYIMPANAN UNTUK TAB BEDAH --//
	
	foreach($rawatKeadaan as $key => $value) {
          unset($show);
          if($_POST["rawat_keadaan_umum"]==$key) $show="selected";
		$optionsKeadaan[] = $view->RenderOption($key,$value,$show);
	}


     $sql = "select diag_id from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     $dataDiag = $dtaccess->Fetch($sql);
     
     $count=0;	
	$optionsNext[$count] = $view->RenderOption(STATUS_CEKOUT,"Pulang",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_RAWATINAP,"Lanjutkan Rawat Inap",$show); $count++;

     $lokasi = $APLICATION_ROOT."images/foto_perawatan";
	$fotoName = ($_POST["rawat_mata_foto"]) ? $lokasi."/".$_POST["rawat_mata_foto"] : $lokasi."/default.jpg";
	$sketsaName = ($_POST["rawat_mata_sketsa"]) ? $lokasi."/".$_POST["rawat_mata_sketsa"] : $lokasi."/default.jpg";


     // --- nyari datanya operasi jenis + harganya---
     $sql = "select op_jenis_id, op_jenis_nama from klinik.klinik_operasi_jenis";
     $dataOperasiJenis = $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi Jenis
     $optOperasiJenis[0] = $view->RenderOption("","[Pilih Jenis Operasi]",$show); 
     for($i=0,$n=count($dataOperasiJenis);$i<$n;$i++) {
          $show = ($_POST["rawat_operasi_jenis"]==$dataOperasiJenis[$i]["op_jenis_id"]) ? "selected":"";
          $optOperasiJenis[$i+1] = $view->RenderOption($dataOperasiJenis[$i]["op_jenis_id"],$dataOperasiJenis[$i]["op_jenis_nama"],$show); 
     }
     
     
     $sql = "select op_paket_id, op_paket_nama from klinik.klinik_operasi_paket";
     $dataOperasiPaket= $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi paket
     $optOperasiPaket[0] = $view->RenderOption("","[Pilih Paket Operasi]",$show); 
     for($i=0,$n=count($dataOperasiPaket);$i<$n;$i++) {
          $show = ($_POST["rawat_operasi_paket"]==$dataOperasiPaket[$i]["op_paket_id"]) ? "selected":"";
          $optOperasiPaket[$i+1] = $view->RenderOption($dataOperasiPaket[$i]["op_paket_id"],$dataOperasiPaket[$i]["op_paket_nama"],$show); 
     }

     // --- nyari datanya anestesis---
     $sql = "select anes_jenis_id, anes_jenis_nama from klinik.klinik_anestesis_jenis";
     $dataAnestesisJenis = $dtaccess->FetchAll($sql);

     $sql = "select anes_komp_id, anes_komp_nama from klinik.klinik_anestesis_komplikasi";
     $dataAnestesisKomplikasi = $dtaccess->FetchAll($sql);

     $sql = "select anes_pre_id, anes_pre_nama from klinik.klinik_anestesis_premedikasi";
     $dataAnestesisPremedikasi = $dtaccess->FetchAll($sql);

     $sql = "select item_id, item_nama from inventori.inv_item where id_kat_item = ".QuoteValue(DPE_CHAR,KAT_OBAT_ANESTESIS);
     $dataAnestesisObat = $dtaccess->FetchAll($sql);

     // -- bikin combonya anestesis
     $optAnestesisJenis[0] = $view->RenderOption("","[Pilih Jenis Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisJenis);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_jenis"]==$dataAnestesisJenis[$i]["anes_jenis_id"]) ? "selected":"";
          $optAnestesisJenis[$i+1] = $view->RenderOption($dataAnestesisJenis[$i]["anes_jenis_id"],$dataAnestesisJenis[$i]["anes_jenis_nama"],$show); 
     }

     $optAnestesisKomplikasi[0] = $view->RenderOption("","[Pilih Komplikasi Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisKomplikasi);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_komp"]==$dataAnestesisKomplikasi[$i]["anes_komp_id"]) ? "selected":"";
          $optAnestesisKomplikasi[$i+1] = $view->RenderOption($dataAnestesisKomplikasi[$i]["anes_komp_id"],$dataAnestesisKomplikasi[$i]["anes_komp_nama"],$show); 
     }

     $optAnestesisPremedikasi[0] = $view->RenderOption("","[Pilih Premedikasi Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisPremedikasi);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_pre"]==$dataAnestesisPremedikasi[$i]["anes_pre_id"]) ? "selected":"";
          $optAnestesisPremedikasi[$i+1] = $view->RenderOption($dataAnestesisPremedikasi[$i]["anes_pre_id"],$dataAnestesisPremedikasi[$i]["anes_pre_nama"],$show); 
     }

     $optAnestesisObat[0] = $view->RenderOption("","[Pilih Obat Anestesis]",$show); 
     for($i=0,$n=count($dataAnestesisObat);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_obat"]==$dataAnestesisObat[$i]["item_id"]) ? "selected":"";
          $optAnestesisObat[$i+1] = $view->RenderOption($dataAnestesisObat[$i]["item_id"],$dataAnestesisObat[$i]["item_nama"],$show); 
     }

     $optTerapiObat[0] = $view->RenderOption("","[Pilih Obat Terapi]",$show); 
     for($i=0,$n=count($dataAnestesisObat);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_obat"]==$dataAnestesisObat[$i]["item_id"]) ? "selected":"";
          $optTerapiObat[$i+1] = $view->RenderOption($dataAnestesisObat[$i]["item_id"],$dataAnestesisObat[$i]["item_nama"],$show); 
     }
     
     
     $sql = "select * from klinik.klinik_visus order by visus_id";
     $dataVisus = $dtaccess->FetchAll($sql);
     
     $optVisusKoreksiOD[0] = $view->RenderOption("","[Pilih Visus]",null);
     $optVisusKoreksiOS[0] = $view->RenderOption("","[Pilih Visus]",null);
     $optVisusNonKoreksiOD[0] = $view->RenderOption("","[Pilih Visus]",null);
     $optVisusNonKoreksiOS[0] = $view->RenderOption("","[Pilih Visus]",null);

	for($i=0,$n=count($dataVisus);$i<$n;$i++) {
          unset($show);
          if($_POST["id_visus_koreksi_od"] == $dataVisus[$i]["visus_id"]) $show = "selected";
          $optVisusKoreksiOD[$i+1] = $view->RenderOption($dataVisus[$i]["visus_id"],$dataVisus[$i]["visus_nama"],$show);

          unset($show);
          if($_POST["id_visus_koreksi_os"] == $dataVisus[$i]["visus_id"]) $show = "selected";
          $optVisusKoreksiOS[$i+1] = $view->RenderOption($dataVisus[$i]["visus_id"],$dataVisus[$i]["visus_nama"],$show);

          unset($show);
          if($_POST["id_visus_nonkoreksi_od"] == $dataVisus[$i]["visus_id"]) $show = "selected";
          $optVisusNonKoreksiOD[$i+1] = $view->RenderOption($dataVisus[$i]["visus_id"],$dataVisus[$i]["visus_nama"],$show);

          unset($show);
          if($_POST["id_visus_nonkoreksi_os"] == $dataVisus[$i]["visus_id"]) $show = "selected";
          $optVisusNonKoreksiOS[$i+1] = $view->RenderOption($dataVisus[$i]["visus_id"],$dataVisus[$i]["visus_nama"],$show);
     }
     
     $optionsBase1[0] = $view->RenderOption("1","TRUE",$show);
     $optionsBase1[1] = $view->RenderOption("0","FALSE",$show);

     $optionsBase2[0] = $view->RenderOption("1","TRUE",$show);
     $optionsBase2[1] = $view->RenderOption("0","FALSE",$show);

          // --- nyari datanya IOL ---
     $sql = "select iol_jenis_id, iol_jenis_nama from klinik.klinik_iol_jenis";
     $dataIOLJenis = $dtaccess->FetchAll($sql);

     $sql = "select iol_merk_id, iol_merk_nama from klinik.klinik_iol_merk";
     $dataIOLMerk = $dtaccess->FetchAll($sql);

     $optIOLJenis[0] = $view->RenderOption("","[Pilih Jenis IOL]",$show); 
     for($i=0,$n=count($dataIOLJenis);$i<$n;$i++) {
          $show = ($_POST["preop_iol_jenis"]==$dataIOLJenis[$i]["iol_jenis_id"]) ? "selected":"";
          $optIOLJenis[$i+1] = $view->RenderOption($dataIOLJenis[$i]["iol_jenis_id"],$dataIOLJenis[$i]["iol_jenis_nama"],$show); 
     }

     $optIOLMerk[0] = $view->RenderOption("","[Pilih Merk IOL]",$show); 
     for($i=0,$n=count($dataIOLMerk);$i<$n;$i++) {
          $show = ($_POST["preop_iol_merk"]==$dataIOLMerk[$i]["iol_merk_id"]) ? "selected":"";
          $optIOLMerk[$i+1] = $view->RenderOption($dataIOLMerk[$i]["iol_merk_id"],$dataIOLMerk[$i]["iol_merk_nama"],$show); 
     }

     // --- nyari datanya rumuys ---
     $sql = "select bio_rumus_id, bio_rumus_nama from klinik.klinik_biometri_rumus order by bio_rumus_nama";
     $dataRumus = $dtaccess->FetchAll($sql);
 
     // -- bikin combonya rumus
     $optRumus[0] = $view->RenderOption("","[Pilih Rumus Yg Dipakai]",$show); 
     for($i=0,$n=count($dataRumus);$i<$n;$i++) {
          $show = ($_POST["preop_rumus"]==$dataRumus[$i]["bio_rumus_id"]) ? "selected":"";
          $optRumus[$i+1] = $view->RenderOption($dataRumus[$i]["bio_rumus_id"],$dataRumus[$i]["bio_rumus_nama"],$show); 
     }
     
     // --- nyari datanya rumuys ---
     $sql = "select bio_av_id, bio_av_nama from klinik.klinik_biometri_av order by bio_av_nama";
     $dataAv = $dtaccess->FetchAll($sql);

     // -- bikin combonya av
     $optAv[0] = $view->RenderOption("","[Pilih AV Constant Yg Dipakai]",$show); 
     for($i=0,$n=count($dataAv);$i<$n;$i++) {
          $show = ($_POST["preop_av_constant"]==$dataAv[$i]["bio_av_id"]) ? "selected":"";
          $optAv[$i+1] = $view->RenderOption($dataAv[$i]["bio_av_id"],$dataAv[$i]["bio_av_nama"],$show); 
     }
    
     $optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show);
     
     
     // --- buat option nama obat ---
     $sql = "select item_id, item_nama   
               from inventori.inv_item 
               where id_kat_item = ".QuoteValue(DPE_CHAR,KAT_OBAT_INJEKSI)."  
               order by item_id";
     $dataObat = $dtaccess->FetchAll($sql);     
     
     
     // --- buat option teknik injeksi ---
     $sql = "select * from klinik.klinik_injeksi order by injeksi_id";
     $dataInjeksi = $dtaccess->FetchAll($sql);
     
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>


<script type="text/javascript">

	function ajaxFileUpload()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajaxFileUpload
		(
			{
				url:'perawatan_upload.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							alert(data.msg);
                                   document.getElementById('rawat_mata_foto').value= data.file;
                                   document.img_foto.src='<?php echo $lokasi."/";?>'+data.file;
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

	}

	function ajaxFileUploadSketsa()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajaxFileUpload
		(
			{
				url:'perawatan_upload.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							alert(data.msg);
                                   document.getElementById('rawat_mata_sketsa').value= data.file;
                                   document.img_sketsa.src='<?php echo $lokasi."/";?>'+data.file;
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

	}
</script>	

<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetPerawatan(0,'target=antri_kiri_isi');     
     GetPerawatan(1,'target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}


function SetTonometriOD(){
     var scale = document.getElementById('rawat_tonometri_scale_od');
     var weight = document.getElementById('rawat_tonometri_weight_od');

     if(scale.value && isNaN(scale.value)){
          scale.value = '';
          return false;
     }

     if(weight.value && isNaN(weight.value)){
          weight.value = '';
          return false;
     }

     GetTonometri(scale.value,weight.value,'target=rawat_tonometri_pressure_od');
     return true;
}

function SetTonometriOS(){
     var scale = document.getElementById('rawat_tonometri_scale_os');
     var weight = document.getElementById('rawat_tonometri_weight_os');

     if(scale.value && isNaN(scale.value)){
          scale.value = '';
          return false;
     }

     if(weight.value && isNaN(weight.value)){
          weight.value = '';
          return false;
     }

     GetTonometri(scale.value,weight.value,'target=rawat_tonometri_pressure_os');
     return true;
}

function SetDosis(fisik,akhir) {
     GetDosis(fisik,akhir,'target=sp_item_'+akhir);
     return true;
}

function GantiRegulasi() {
     if(document.getElementById('preop_regulasi').checked) {
          document.getElementById('tbRegulasi').style.display="block";
          document.getElementById('tbCatatan').style.display="none";
          document.getElementById('tbTerapi').style.display="none";
     } else {
          document.getElementById('tbRegulasi').style.display="none";
          document.getElementById('tbCatatan').style.display="block";
          document.getElementById('tbTerapi').style.display="none";
     }
}

function CheckData(frm) {
	
	<?php if($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_JAMKESNAS_PUSAT) { ?> 
		if(!frm.rawat_ina_klaim_id_0.value){
			alert('Klaim INA DRG Harus diisi');
			frm.rawat_ina_klaim_kode_0.focus();
			return false;
		} 
	<?php } ?>


     return true;
}

timer();


function Tambah(){
     var akhir = eval(document.getElementById('hid_tot').value)+1;
     
     $('#tb_terapi').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_terapi_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:20, maxLength:100, name:'item_nama[]', id:'item_nama_'+akhir},[],
                         'a',{ href:'<?php echo $terapiPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_item[]', id:'id_item_'+akhir+''}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                    [
                         'span', {id:'sp_item_'+akhir+''}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { Delete(akhir) } );
     document.getElementById('item_nama_'+akhir).readOnly = true;
          
     document.getElementById('hid_tot').value = akhir;
     tb_init('a.thickbox');
}

function Delete(akhir){
     document.getElementById('hid_id_del').value += document.getElementById('id_item_'+akhir).value;
     
     $('#tr_terapi_'+akhir).remove();
}


function SusterRawatTambah(){
     var akhir = eval(document.getElementById('suster_rawat_tot').value)+1;
     
     $('#tb_suster_rawat').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_rawat_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'rawat_suster_nama[]', id:'rawat_suster_nama_'+akhir},[],
                         'a',{ href:'<?php echo $susterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_suster_rawat[]', id:'id_suster_rawat_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { SusterRawatDelete(akhir) } );
     document.getElementById('rawat_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_rawat_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterRawatDelete(akhir){
     document.getElementById('hid_suster_rawat_del').value += document.getElementById('id_suster_rawat_'+akhir).value;
     
     $('#tr_suster_rawat_'+akhir).remove();
}


function SusterOPTambah(){
     var akhir = eval(document.getElementById('suster_op_tot').value)+1;
     
     $('#tb_suster_op').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_op_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'op_suster_nama[]', id:'op_suster_nama_'+akhir},[],
                         'a',{ href:'<?php echo $susterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_suster_op[]', id:'id_suster_op_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { SusterOPDelete(akhir) } );
     document.getElementById('op_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_op_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterOPDelete(akhir){
     document.getElementById('hid_suster_op_del').value += document.getElementById('id_suster_op_'+akhir).value;
     
     $('#tr_suster_op_'+akhir).remove();
}

function IcdTambah(){
     var akhir = eval(document.getElementById('icd_tot').value)+1;
     
     $('#tb_icd').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_icd_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:10, maxLength:100, name:'op_icd_kode[]', id:'op_icd_kode_'+akhir},[],
                         'a',{ href:'<?php echo $icdPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari ICD'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_icd[]', id:'id_icd_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:50, maxLength:100, name:'op_icd_nama[]', id:'op_icd_nama_'+akhir},[],
                         
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { IcdDelete(akhir) } );
     document.getElementById('op_icd_kode_'+akhir).readOnly = true;
     document.getElementById('op_icd_nama_'+akhir).readOnly = true;
          
     document.getElementById('icd_tot').value = akhir;
     tb_init('a.thickbox');
}

function IcdDelete(akhir){     
     $('#tr_icd_'+akhir).remove();
}

function AsistenSusterTambah(){
     var akhir = eval(document.getElementById('asisten_suster_tot').value)+1;
     
     $('#tb_asisten_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_asisten_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'op_asisten_suster_terapi_nama[]', id:'op_asisten_suster_terapi_nama_'+akhir},[],
                         'a',{ href:'<?php echo $asistenSusterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Asisten Suster'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_asisten_suster_terapi[]', id:'id_asisten_suster_terapi_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { AsistenSusterDelete(akhir) } );
     document.getElementById('op_asisten_suster_terapi_nama_'+akhir).readOnly = true;
          
     document.getElementById('asisten_suster_tot').value = akhir;
     tb_init('a.thickbox');
}

function AsistenSusterDelete(akhir){
     document.getElementById('hid_asisten_suster_terapi_del').value += document.getElementById('id_asisten_suster_terapi_'+akhir).value;
     
     $('#tr_asisten_suster_'+akhir).remove();
}

function SusterPreopTambah(){
     var akhir = eval(document.getElementById('suster_preop_tot').value)+1;
     
     $('#tb_suster_preop').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_preop_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'preop_suster_nama[]', id:'preop_suster_nama_'+akhir},[],
                         'a',{ href:'<?php echo $susterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_suster_preop[]', id:'id_suster_preop_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { SusterPreopDelete(akhir) } );
     document.getElementById('preop_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_preop_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterPreopDelete(akhir){
     document.getElementById('hid_suster_preop_del').value += document.getElementById('id_suster_preop_'+akhir).value;
     
     $('#tr_suster_preop_'+akhir).remove();
}

function SusterDiagTambah(){
     var akhir = eval(document.getElementById('suster_diag_tot').value)+1;
     
     $('#tb_suster_diag').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_diag_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'diag_suster_nama[]', id:'diag_suster_nama_'+akhir},[],
                         'a',{ href:'<?php echo $susterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_suster_diag[]', id:'id_suster_diag_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { SusterDiagDelete(akhir) } );
     document.getElementById('diag_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_diag_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterDiagDelete(akhir){
     document.getElementById('hid_suster_diag_del').value += document.getElementById('id_suster_diag_'+akhir).value;
     
     $('#tr_suster_diag_'+akhir).remove();
}

function RefraksionistTambah(){
     var akhir = eval(document.getElementById('suster_ref_tot').value)+1;
     
     $('#tb_suster_ref').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_ref_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'ref_suster_nama[]', id:'ref_suster_nama_'+akhir},[],
                         'a',{ href:'<?php echo $susterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_suster_ref[]', id:'id_suster_ref_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { RefraksionistDelete(akhir) } );
     document.getElementById('ref_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_ref_tot').value = akhir;
     tb_init('a.thickbox');
}

function RefraksionistDelete(akhir){
     document.getElementById('hid_suster_ref_del').value += document.getElementById('id_suster_ref_'+akhir).value;
     
     $('#tr_suster_ref_'+akhir).remove();
}


function ItemDosis(item,akhir){
     GetDosis(item,akhir,'target=div_dosis_'+akhir);
     return true;
}

function InjeksiTambah(){
     var akhir = eval(document.getElementById('hid_tot_injeksi').value)+1;
     
     $('#tb_injeksi').createAppend(
          'tr', { id:'tr_injeksi_'+akhir+'' },
                ['td', { className: 'tablecontent', align: 'center', style: 'color: black;' },
                    [
                         'label',{} ,[]
                    ],
               'td', { className: 'tablecontent-odd', style: 'color: black;' },
                    [
                         'select', {name:'id_item[]', id:'id_item_'+akhir+'', onchange:'ItemDosis(this.options[this.selectedIndex].value,'+akhir+')' } ,[]
                    ],
               'td', { className: 'tablecontent', align: 'center', style: 'color: black;' },
                    [
                         'label',{} ,[]
                    ],
               'td', { className: 'tablecontent-odd', style: 'color: black;' },
                    [
                         'span', {id:'div_dosis_'+akhir+''}
                    ],
               'td', { className: 'tablecontent', align: 'center', style: 'color: black;' },
                    [
                         'label',{} ,[]
                    ],
               'td', { className: 'tablecontent-odd', style: 'color: black;' },
                    [
                         'select', {name:'id_injeksi[]', id:'id_injeksi_'+akhir+''} ,[],
                         'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir+''}
                    ]
                ]                    
     );
     
     $('#btnDel_'+akhir+'').click( function() { InjeksiDelete(akhir) } );
     
     
     document.getElementById('id_item_'+akhir).options[0]= new Option('[Pilih Obat]', '');
     <?php for($j=0, $o=count($dataObat); $j<$o; $j++) { ?>
          document.getElementById('id_item_'+akhir).options[<?php echo ($j+1);?>]= new Option('<?php echo $dataObat[$j]["item_nama"];?>', '<?php echo $dataObat[$j]["item_id"];?>');
     <?php } ?>
     
     
     document.getElementById('id_injeksi_'+akhir).options[0]= new Option('[Pilih Teknik Injeksi]', '');
     <?php for($j=0, $o=count($dataInjeksi); $j<$o; $j++) { ?>
          document.getElementById('id_injeksi_'+akhir).options[<?php echo ($j+1);?>]= new Option('<?php echo $dataInjeksi[$j]["injeksi_nama"];?>', '<?php echo $dataInjeksi[$j]["injeksi_id"];?>');
     <?php } ?>     
          
     document.getElementById('hid_tot_injeksi').value = akhir;     
}

function InjeksiDelete(akhir){
     document.getElementById('hid_injeksi_del').value += document.getElementById('id_injeksi_'+akhir).value;
     
     $('#tr_injeksi_'+akhir).remove();
}

function ChangeDisplay(id) {
     var disp = Array();
     
     disp['none'] = 'block';
     disp['block'] = 'none';
     
     document.getElementById(id).style.display = disp[document.getElementById(id).style.display];
}


var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}



var _wnd_cetak;

function BukaWindowCetak(url,judul)
{
    if(!_wnd_cetak) {
			_wnd_cetak = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=600,left=100,top=100');
	} else {
		if (_wnd_cetak.closed) {
			_wnd_cetak = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=600,left=100,top=100');
		} else {
			_wnd_cetak.focus();
		}
	}
     return false;
}
</script>
<!-- link css untuk tampilan tab ala winXP -->
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>/library/css/winxp.css" />
<!-- link jscript untuk fungsi-fungsi tab dasar -->
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>/library/script/listener.js"></script> 
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>/library/script/tabs.js"></script>  

<?php if(!$_GET["id"]) { ?>
	<div id="antri_kanan" style="width:100%;">
		<div class="tableheader">Proses Kontrol Harian</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetPerawatan(); ?></div>
	</div>
<?php } ?>


<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan="2" class="tableheader">Input Data Kontrol Harian</td>
	</tr>
	<tr>
	  <td align="right" class="tablecontent" width="20%">Tanggal Kontrol&nbsp;</td>
	  <td align="left" class="tablecontent-odd" width="80%">
	     <?php echo $view->RenderTextBox("tanggal_kontrol","tanggal_kontrol","15","15",format_date($_POST["tanggal_kontrol"]),"inputfield"); ?>
	     <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
	  </td>
	</tr>
</table> 

<div class="tabsystem"> 
  <div class="tabpage tdefault">
    <h2>Pemeriksaan</h2>
<form name="frmPemeriksaan" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="60%">

     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "30%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Umur</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Keluhan Pasien</td>
               <td width= "40%" align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_keluhan","rawat_keluhan","50","200",$_POST["rawat_keluhan"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Keadaan Umum</td>
               <td width= "40%" align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_keadaan_umum","rawat_keadaan_umum",$optionsKeadaan,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Tensi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_tensi","rawat_lab_tensi","15","15",$_POST["rawat_lab_tensi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Nadi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_nadi","rawat_lab_nadi","15","15",$_POST["rawat_lab_nadi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pernafasan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_nafas","rawat_lab_nafas","15","15",$_POST["rawat_lab_nafas"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent"><span style="color:red">Rekap Medik</span></td>
               <td align="left" class="tablecontent-odd">
                    <a onClick="BukaWindow('rekap_medik.php?id_reg=<?php echo $_POST["id_reg"];?>&id_cust_usr=<?php echo $_POST["id_cust_usr"];?>','Rekap Medik')" href="#"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Rekap Medik" alt="Rekap Medik"/></a>
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Petugas</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="20%"  class="tablecontent" align="left">Dokter</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("rawat_dokter_nama","rawat_dokter_nama","30","100",$_POST["rawat_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter_rawat" name="id_dokter_rawat" value="<?php echo $_POST["id_dokter_rawat"];?>"/>
               </td>
          </tr>
     
          <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster_rawat">
      <?php if(!$_POST["rawat_suster_nama"]) { ?>
					<tr id="tr_suster_rawat_0">
						<td align="left" class="tablecontent-odd" width="70%">
							<?php echo $view->RenderTextBox("rawat_suster_nama[]","rawat_suster_nama_0","30","100",$_POST["rawat_suster_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
							<input type="hidden" id="id_suster_rawat_0" name="id_suster_rawat[]" value="<?php echo $_POST["id_suster_rawat"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="30%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterRawatTambah();">
							<input name="suster_rawat_tot" id="suster_rawat_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster_rawat"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $view->RenderTextBox("rawat_suster_nama[]","rawat_suster_nama_".$i,"30","100",$_POST["rawat_suster_nama"][$i],"inputField", "readonly",false);?>
                                        <?php //if($edit) {?>
                                             <a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                                        <?php //}?>
                                        <input type="hidden" id="id_suster_rawat_<?php echo $i;?>" name="id_suster_rawat[]" value="<?php echo $_POST["id_suster_rawat"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
                                        <?php// if($edit) {?>
                                             <?php if($i==0) { ?>
                                                  <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterRawatTambah();">
                                             <?php } else { ?>
                                                  <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterRawatDelete(<?php echo $i;?>);">
                                             <?php } ?>
                                        <?php// }?>
                                        <input name="suster_rawat_tot" id="suster_rawat_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_suster_rawat_del","hid_suster_rawat_del",'');?>
				</table>
               </td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Pemeriksaan Mata</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="30%" align="center">Pemeriksaan</td>
               <td width="30%" align="center">OD</td>
               <td width="30%" align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Palpebra</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_palpebra","rawat_mata_od_palpebra","30","30",$_POST["rawat_mata_od_palpebra"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_palpebra","rawat_mata_os_palpebra","30","30",$_POST["rawat_mata_os_palpebra"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Conjunctiva</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_conjunctiva","rawat_mata_od_conjunctiva","30","30",$_POST["rawat_mata_od_conjunctiva"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_conjunctiva","rawat_mata_os_conjunctiva","30","30",$_POST["rawat_mata_os_conjunctiva"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Cornea</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_cornea","rawat_mata_od_cornea","30","30",$_POST["rawat_mata_od_cornea"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_cornea","rawat_mata_os_cornea","30","30",$_POST["rawat_mata_os_cornea"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">COA</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_coa","rawat_mata_od_coa","30","30",$_POST["rawat_mata_od_coa"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_coa","rawat_mata_os_coa","30","30",$_POST["rawat_mata_os_coa"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Iris</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_iris","rawat_mata_od_iris","30","30",$_POST["rawat_mata_od_iris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_iris","rawat_mata_os_iris","30","30",$_POST["rawat_mata_os_iris"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pupil</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_pupil","rawat_mata_od_pupil","30","30",$_POST["rawat_mata_od_pupil"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_pupil","rawat_mata_os_pupil","30","30",$_POST["rawat_mata_os_pupil"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Lensa</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_lensa","rawat_mata_od_lensa","30","30",$_POST["rawat_mata_od_lensa"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_lensa","rawat_mata_os_lensa","30","30",$_POST["rawat_mata_os_lensa"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Vitreus</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_od_vitreus","rawat_od_vitreus","3","27",$_POST["rawat_od_vitreus"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_os_vitreus","rawat_os_vitreus","3","27",$_POST["rawat_os_vitreus"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Ocular Movement</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_ocular","rawat_mata_od_ocular","30","30",$_POST["rawat_mata_od_ocular"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_ocular","rawat_mata_os_ocular","30","30",$_POST["rawat_mata_os_ocular"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Funduscopy (Retina)</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_retina","rawat_mata_od_retina","30","30",$_POST["rawat_mata_od_retina"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_retina","rawat_mata_os_retina","30","30",$_POST["rawat_mata_os_retina"],"inputField", null,false);?></td>
          </tr> 
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Tindakan Pemeriksaan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="30%" class="tablecontent">Tonometri OD</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_tonometri_scale_od","rawat_tonometri_scale_od","5","5",$_POST["rawat_tonometri_scale_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> / 
                    <?php echo $view->RenderTextBox("rawat_tonometri_weight_od","rawat_tonometri_weight_od","5","5",$_POST["rawat_tonometri_weight_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> g = 
                    <?php echo $view->RenderTextBox("rawat_tonometri_pressure_od","rawat_tonometri_pressure_od","5","5",$_POST["rawat_tonometri_pressure_od"],"inputField", "readonly",false);?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" width="30%" class="tablecontent">Tonometri OS</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_tonometri_scale_os","rawat_tonometri_scale_os","5","5",$_POST["rawat_tonometri_scale_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> / 
                    <?php echo $view->RenderTextBox("rawat_tonometri_weight_os","rawat_tonometri_weight_os","5","5",$_POST["rawat_tonometri_weight_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> g = 
                    <?php echo $view->RenderTextBox("rawat_tonometri_pressure_os","rawat_tonometri_pressure_os","5","5",$_POST["rawat_tonometri_pressure_os"],"inputField", "readonly",false);?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Anel Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_anel","rawat_anel","15","15",$_POST["rawat_anel"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Schimer Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_schimer","rawat_schimer","15","15",$_POST["rawat_schimer"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Irigasi Bola Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_irigasi","rawat_irigasi","15","15",$_POST["rawat_irigasi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Epilasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_epilasi","rawat_epilasi","15","15",$_POST["rawat_epilasi"],"inputField", null,false);?></td>
          </tr>
<!--          <tr>
               <td align="left" class="tablecontent">Suntikan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_suntikan","rawat_suntikan","15","15",$_POST["rawat_suntikan"],"inputField", null,false);?></td>
          </tr>
     -->
          <tr>
               <td align="left" class="tablecontent">Probing</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_probing","rawat_probing","15","15",$_POST["rawat_probing"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Flouorecsin Test</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_flouorecsin","rawat_flouorecsin","15","15",$_POST["rawat_flouorecsin"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Uji Kesehatan Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_kesehatan","rawat_kesehatan","15","15",$_POST["rawat_kesehatan"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Color Blindness</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_color_blindness","rawat_color_blindness","5","50",$_POST["rawat_color_blindness"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Upload Foto</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "50%" align="left" class="tablecontent">Gambar Mata</td>
               <td width= "50%" align="left" class="tablecontent">Sketsa Mata</td>
          </tr>
          <tr>
               <td width= "50%" align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $fotoName;?>"  border="1">
                    <input type="hidden" name="rawat_mata_foto" id="rawat_mata_foto" value="<?php echo $_POST["rawat_mata_foto"];?>">
               </td>
               <td width= "50%" align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_sketsa" id="img_sketsa" src="<?php echo $sketsaName;?>"  border="1">
                    <input type="hidden" name="rawat_mata_sketsa" id="rawat_mata_sketsa" value="<?php echo $_POST["rawat_mata_sketsa"];?>">
               </td>
          </tr>
          <tr>
               <td colspan=2 align="center">
                    <div id="loading" style="display:none;"><img id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></div> 
                    <input id="fileToUpload" type="file" size="35" name="fileToUpload" class="inputField">
               </td>
          </tr>
          <tr>
               <td width= "50%" align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Gambar Mata</button>
               </td>
               <td width= "50%" align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUploadSketsa();">Upload Sketsa Mata</button>
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Catatan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
          <tr>
               <td align="left" class="tablecontent" width="20%">Catatan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_catatan","rawat_catatan","5","50",$_POST["rawat_catatan"],"inputField", null,false);?></td>
          </tr>
     </table>
     </fieldset>
     <fieldset>
     <legend><strong>Diagnosis - ICD - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[0]","rawat_icd_od_kode_0","10","100",$_POST["rawat_icd_od_kode"][0],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[0]" id="rawat_icd_od_id_0" value="<?php echo $_POST["rawat_icd_od_id"][0]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_icd_od_nama[0]","rawat_icd_od_nama_0","50","100",$_POST["rawat_icd_od_nama"][0],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[1]","rawat_icd_od_kode_1","10","100",$_POST["rawat_icd_od_kode"][1],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[1]" id="rawat_icd_od_id_1" value="<?php echo $_POST["rawat_icd_od_id"][1]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_icd_od_nama[1]","rawat_icd_od_nama_1","50","100",$_POST["rawat_icd_od_nama"][1],"inputField", "readonly",false);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnosis - ICD - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[0]","rawat_icd_os_kode_0","10","100",$_POST["rawat_icd_os_kode"][0],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[0]" id="rawat_icd_os_id_0" value="<?php echo $_POST["rawat_icd_os_id"][0]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_icd_os_nama[0]","rawat_icd_os_nama_0","50","100",$_POST["rawat_icd_os_nama"][0],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[1]","rawat_icd_os_kode_1","10","100",$_POST["rawat_icd_os_kode"][1],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[1]" id="rawat_icd_os_id_1" value="<?php echo $_POST["rawat_icd_os_id"][1]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_icd_os_nama[1]","rawat_icd_os_nama_1","50","100",$_POST["rawat_icd_os_nama"][1],"inputField", "readonly",false);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnosis - INA DRG - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">INA DRG</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_ina_od_kode[0]","rawat_ina_od_kode_0","10","100",$_POST["rawat_ina_od_kode"][0],"inputField", "readonly",false);?>
                    <a href="<?php echo $inaPage;?>&tipe=od&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_od_id[0]" id="rawat_ina_od_id_0" value="<?php echo $_POST["rawat_ina_od_id"][0]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_ina_od_nama[0]","rawat_ina_od_nama_0","50","100",$_POST["rawat_ina_od_nama"][0],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_ina_od_kode[1]","rawat_ina_od_kode_1","10","100",$_POST["rawat_ina_od_kode"][1],"inputField", "readonly",false);?>
                    <a href="<?php echo $inaPage;?>&tipe=od&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_od_id[1]" id="rawat_ina_od_id_1" value="<?php echo $_POST["rawat_ina_od_id"][1]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_ina_od_nama[1]","rawat_ina_od_nama_1","50","100",$_POST["rawat_ina_od_nama"][1],"inputField", "readonly",false);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnosis - INA DRG - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">INA DRG</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_ina_os_kode[0]","rawat_ina_os_kode_0","10","100",$_POST["rawat_ina_os_kode"][0],"inputField", "readonly",false);?>
                    <a href="<?php echo $inaPage;?>&tipe=os&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_os_id[0]" id="rawat_ina_os_id_0" value="<?php echo $_POST["rawat_ina_os_id"][0]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_ina_os_nama[0]","rawat_ina_os_nama_0","50","100",$_POST["rawat_ina_os_nama"][0],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_ina_os_kode[1]","rawat_ina_os_kode_1","10","100",$_POST["rawat_ina_os_kode"][1],"inputField", "readonly",false);?>
                    <a href="<?php echo $inaPage;?>&tipe=os&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_os_id[1]" id="rawat_ina_os_id_1" value="<?php echo $_POST["rawat_ina_os_id"][1]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_ina_os_nama[1]","rawat_ina_os_nama_1","50","100",$_POST["rawat_ina_os_nama"][1],"inputField", "readonly",false);?></td>
          </tr>
	</table>
     </fieldset>
	

     <fieldset>
     <legend><strong>Terapi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
          <tr class="subheader">
               <td width="30%" align="center">Nama Obat</td>
               <td width="30%" align="center">Dosis</td>
               <td width="10%" align="center">&nbsp;</td>
          </tr>	
          <?php if(!$_POST["item_nama"]) { ?>
               <tr  class="tablecontent-odd" id="tr_terapi_0">
                    <td align="left" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("item_nama[0]","item_nama_0","20","100",$_POST["item_nama"][0],"inputField", "readonly",false);?>
                         <a href="<?php echo $terapiPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" /></a>
                         <input type="hidden" name="id_item[0]" id="id_item_0" value="<?php echo $_POST["id_item"][0]?>" />                    
                    </td>
                    <td align="center" class="tablecontent-odd"><span id="sp_item_0"></span></td>
                    <td align="center" class="tablecontent-odd">
                         <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="Tambah();">
                         <input name="hid_tot" id="hid_tot" type="hidden" value="0">
                    </td>                    
               </tr>
          <?php } else { ?>
               <?php for($i=0,$n=count($_POST["id_item"]);$i<$n;$i++) { ?>
                    <tr id="tr_terapi_<?php echo $i;?>">
                         <td align="left" class="tablecontent-odd" width="70%">
                              <?php echo $view->RenderTextBox("item_nama[]","item_nama_".$i,"30","100",$_POST["item_nama"][$i],"inputField", "readonly",false);?>
                              <a href="<?php echo $terapiPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Obat" alt="Cari Obat" /></a>
                              <input type="hidden" id="id_item_<?php echo $i;?>" name="id_item[]" value="<?php echo $_POST["id_item"][$i];?>"/>
                         </td>
                         <td align="center" class="tablecontent-odd"><span id="sp_item_<?php echo $i;?>"><?php echo GetDosis($_POST["item_fisik"][$i],$i,$_POST["id_dosis"][$i]);?></span></td>
                         <td align="left" class="tablecontent-odd" width="30%">
                              <?php if($i==0) { ?>
                              <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="Tambah();">
                              <?php } else { ?>
                              <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="Delete(<?php echo $i;?>);">
                              <?php } ?>
                              <input name="hid_tot" id="hid_tot" type="hidden" value="<?php echo $n;?>">
                         </td>
                    </tr>
               <?php } ?>
          <?php } ?>
     </table>
     </fieldset>
	
	

     <fieldset>
     <legend><strong>Terapi Kacamata</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
          <tr class="subheader">
               <td width="5%" align="center">Mata</td>
               <td width="15%" align="center">Spheris</td>
               <td width="15%" align="center">Cylinder</td>
               <td width="15%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_koreksi_spheris","rawat_mata_od_koreksi_spheris","15","15",$_POST["rawat_mata_od_koreksi_spheris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_koreksi_cylinder","rawat_mata_od_koreksi_cylinder","15","15",$_POST["rawat_mata_od_koreksi_cylinder"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_od_koreksi_sudut","rawat_mata_od_koreksi_sudut","15","15",$_POST["rawat_mata_od_koreksi_sudut"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_koreksi_spheris","rawat_mata_os_koreksi_spheris","15","15",$_POST["rawat_mata_os_koreksi_spheris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_koreksi_cylinder","rawat_mata_os_koreksi_cylinder","15","15",$_POST["rawat_mata_os_koreksi_cylinder"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_mata_os_koreksi_sudut","rawat_mata_os_koreksi_sudut","15","15",$_POST["rawat_mata_os_koreksi_sudut"],"inputField", null,false);?></td>
          </tr>
     </table>
     </fieldset>


	<?php if($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_JAMKESNAS_PUSAT) { ?>
		<fieldset>
		<legend><strong>PAKET KLAIM INA DRG</strong></legend>
		<table width="100%" border="1" cellpadding="4" cellspacing="1">
			<tr> 
				<td align="center" class="subheader" width="25%">INA DRG</td>
				<td align="center" class="subheader">Keterangan</td>
			</tr>
			<tr> 
				<td align="left" class="tablecontent-odd">
					<?php echo $view->RenderTextBox("rawat_ina_klaim_kode[0]","rawat_ina_klaim_kode_0","10","100",$_POST["rawat_ina_klaim_kode"][0],"inputField", "readonly",false);?>
					<a href="<?php echo $inaPage;?>&tipe=klaim&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
					<input type="hidden" name="rawat_ina_klaim_id[0]" id="rawat_ina_klaim_id_0" value="<?php echo $_POST["rawat_ina_klaim_id"][0]?>" />                    
				</td>
				<td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_ina_klaim_nama[0]","rawat_ina_klaim_nama_0","50","100",$_POST["rawat_ina_klaim_nama"][0],"inputField", "readonly",false);?></td>
			</tr> 
		</table>
		</fieldset>
	<?php } ?>

     <fieldset>
     <legend><strong>Rencana Tindakan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_operasi_jenis","rawat_operasi_jenis",$optOperasiJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Paket Biaya</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_operasi_paket","rawat_operasi_paket",$optOperasiPaket,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Rencana Anestesis</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_jenis","rawat_anestesis_jenis",$optAnestesisJenis,null,null,null);?></td>
          </tr>
<!--
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Obat Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php //echo $view->RenderComboBox("rawat_anestesis_obat","rawat_anestesis_obat",$optAnestesisObat,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Dosis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php //echo $view->RenderTextBox("rawat_anestesis_dosis","rawat_anestesis_dosis","50","200",$_POST["rawat_anestesis_dosis"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Komplikasi Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php //echo $view->RenderComboBox("rawat_anestesis_komp","rawat_anestesis_komp",$optAnestesisKomplikasi,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Premedikasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php //echo $view->RenderComboBox("rawat_anestesis_pre","rawat_anestesis_pre",$optAnestesisPremedikasi,null,null,null);?></td>
          </tr>
     
-->
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Cetak Surat</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center">
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnUpdate","btnUpdate","Surat Keterangan Sakit","button",false,'onClick="BukaWindowCetak(\'surat_sakit.php?id_cust_usr='.$_POST["id_cust_usr"].'&id_reg='.$_POST["id_reg"].'\',\'Cetak Invoice\')"',null);?>
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit" || $_x_mode == "Diag") ? "btnUpdateRawat" : "btnSaveRawat","btnSaveRawat","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>

     </td>
     
     <td width="40%" height="100%" valign="top"><iframe style="width:100%;height:100%;" marginwidth="0" marginheight="0" id="ifrmDiag" name="ifrmDiag" src="<?php echo $diagLink;?>" scrolling="auto" align="center" frameborder="0"></iframe></td>
</tr>	

</table>

<?php echo $view->SetFocus("rawat_keluhan");?>
<input type="hidden" name="kontrol_id" value="<?php echo $_POST["kontrol_id"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="id_rawatinap" value="<?php echo $_POST["id_rawatinap"];?>"/>
<input type="hidden" name="_x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"];?>"/>
<?php echo $view->RenderHidden("hid_id_del","hid_id_del",'');?>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
</span>
  </div>
  <div class="tabpage">
    <h2>Diagnostik</h2>
<form name="frmDiagnostik" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "20%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "20%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Umur</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
        <!--  <tr>
               <td width= "20%" align="left" class="tablecontent">Alergi</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label style="color:red"><?php //echo $dataPasien["cust_usr_alergi"]; ?></label></td>
          </tr>-->
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Petugas</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="20%"  class="tablecontent" align="left">Dokter</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("diag_dokter_nama","diag_dokter_nama","30","100",$_POST["diag_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter_diag" name="id_dokter_diag" value="<?php echo $_POST["id_dokter_diag"];?>"/>
               </td>
          </tr>
     
          <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster_diag">
                         <?php if(!$_POST["diag_suster_nama"]) { ?>
					<tr id="tr_suster_diag_0">
						<td align="left" class="tablecontent-odd" width="70%">
							<?php echo $view->RenderTextBox("diag_suster_nama[]","diag_suster_nama_0","30","100",$_POST["diag_suster_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
							<input type="hidden" id="id_suster_diag_0" name="id_suster_diag[]" value="<?php echo $_POST["id_suster_diag"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="30%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterDiagTambah();">
							<input name="suster_diag_tot" id="suster_diag_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_diag_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $view->RenderTextBox("diag_suster_nama[]","diag_suster_nama_".$i,"30","100",$_POST["diag_suster_nama"][$i],"inputField", "readonly",false);?>
								<?php //if($edit) {?>
									<a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
								<?php //} ?>
                                        <input type="hidden" id="id_suster_diag_<?php echo $i;?>" name="id_suster_diag[]" value="<?php echo $_POST["id_suster_diag"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
								<?php //if($edit) {?>
									<?php if($i==0) { ?>
									<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterDiagTambah();">
									<?php } else { ?>
									<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterDiagDelete(<?php echo $i;?>);">
									<?php } ?>
								<?php// } ?>
                                        <input name="suster_diag_tot" id="suster_diag_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_suster_diag_del","hid_suster_diag_del",'');?>
				</table>
               </td>
          </tr>
	</table>
     </fieldset>



     <fieldset>
     <legend><strong>Keratometri</strong></legend>
     <table width="80%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="5%" align="center">&nbsp;</td>
               <td width="30%" align="center">OD</td>
               <td width="30%" align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">K1</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k1_od","diag_k1_od","30","30",$_POST["diag_k1_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k1_os","diag_k1_os","30","30",$_POST["diag_k1_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">K2</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k2_od","diag_k2_od","30","30",$_POST["diag_k2_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_k2_os","diag_k2_os","30","30",$_POST["diag_k2_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Biometri</strong></legend>
     <table width="70%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="25%" align="center">&nbsp;</td>
               <td width="35%" align="center">OD</td>
               <td width="35%" align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Acial Length</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_od","diag_acial_od","30","30",$_POST["diag_acial_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_acial_os","diag_acial_os","30","30",$_POST["diag_acial_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power IOL</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_od","diag_iol_od","30","30",$_POST["diag_iol_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_iol_os","diag_iol_os","30","30",$_POST["diag_iol_os"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">AV Constant</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("diag_av_constant","diag_av_constant",$optAv,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Standart Deviasi</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderTextBox("diag_deviasi","diag_deviasi","10","30",$_POST["diag_deviasi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Rumus yang dipakai</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("diag_rumus","diag_rumus",$optRumus,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>LAB</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Gula Darah Acak</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_lab_gula_darah","diag_lab_gula_darah","50","100",$_POST["diag_lab_gula_darah"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Darah Lengkap</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_lab_darah_lengkap","diag_lab_darah_lengkap","5","50",$_POST["diag_lab_darah_lengkap"],"inputField", null,null);?><td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>USG</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent">COA</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_coa","diag_coa","15","15",$_POST["diag_coa"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Lensa</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_lensa","diag_lensa","15","15",$_POST["diag_lensa"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Retina</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_retina","diag_retina","15","15",$_POST["diag_retina"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Kesimpulan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("diag_kesimpulan","diag_kesimpulan","5","50",$_POST["diag_kesimpulan"],"inputField", null,null);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Tindakan Medik di R. Diagnostik</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">EKG</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_ekg","diag_ekg","30","15",$_POST["diag_ekg"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Fundus Angiografi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_fundus","diag_fundus","30","15",$_POST["diag_fundus"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Indirect Opthalmoscopy</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_opthalmoscop","diag_opthalmoscop","30","50",$_POST["diag_opthalmoscop"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Optical Coherence Tomographi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_oct","diag_oct","30","50",$_POST["diag_oct"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Yag Laser</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_yag","diag_yag","30","50",$_POST["diag_yag"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Argon Laser</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_argon","diag_argon","30","50",$_POST["diag_argon"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Laser Glaukoma</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_glaukoma","diag_glaukoma","30","50",$_POST["diag_glaukoma"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Humprey</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_humpre","diag_humpre","30","50",$_POST["diag_humpre"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Non Contact Biometri</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_nc_biometri","diag_nc_biometri","30","50",$_POST["diag_nc_biometri"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Non Contact Tonometri</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_nc_tonometri","diag_nc_tonometri","30","50",$_POST["diag_nc_tonometri"],"inputField", null,null);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Upload Foto</strong></legend>
     <table width="60%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="25%" align="left" class="tablecontent">Gambar USG</td>
               <td width="25%" align="left" class="tablecontent">Gambar Fundus</td>
               <td width="25%" align="left" class="tablecontent">Gambar Humpreys</td>
               <td width="25%" align="left" class="tablecontent">Gambar OCT</td>
          </tr>
          <tr>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_usg" id="img_usg" src="<?php echo $fotoUsg;?>"  border="1">
                    <input type="hidden" name="diag_gambar_usg" id="diag_gambar_usg" value="<?php echo $_POST["diag_gambar_usg"];?>">
               </td>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_fundus" id="img_fundus" src="<?php echo $fotoFundus;?>"  border="1">
                    <input type="hidden" name="diag_gambar_fundus" id="diag_gambar_fundus" value="<?php echo $_POST["diag_gambar_fundus"];?>">
               </td>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_humpre" id="img_humpre" src="<?php echo $fotoHumpre;?>"  border="1">
                    <input type="hidden" name="diag_gambar_humpre" id="diag_gambar_humpre" value="<?php echo $_POST["diag_gambar_humpre"];?>">
               </td>
               <td align="center"  class="tablecontent-odd">
                    <img hspace="2" width="120" height="150" name="img_oct" id="img_oct" src="<?php echo $fotoOct;?>"  border="1">
                    <input type="hidden" name="diag_gambar_oct" id="diag_gambar_oct" value="<?php echo $_POST["diag_gambar_oct"];?>">
               </td>
          </tr>
          <tr>
               <td colspan=4 align="center">
                    <div id="loading" style="display:none;"><img id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></div> 
                    <input id="fileToUpload" type="file" size="35" name="fileToUpload" class="inputField">
               </td>
          </tr>
          <tr>
               <td align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload('diag_usg.php','diag_gambar_usg','img_usg');">Upload USG</button>
               </td>
               <td align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload('diag_fundus.php','diag_gambar_fundus','img_fundus');">Upload Fundus</button>
               </td>
               <td align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload('diag_humpre.php','diag_gambar_humpre','img_humpre');">Upload Humpre</button>
               </td>
               <td align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload('diag_oct.php','diag_gambar_oct','img_oct');">Upload OCT</button>
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdateDiag" : "btnSaveDiag","btnSaveDiag","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>	

</table>

<?php echo $view->SetFocus("diag_k1_nilai");?>
<input type="hidden" name="kontrol_id" value="<?php echo $_POST["kontrol_id"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="id_rawatinap" value="<?php echo $_POST["id_rawatinap"];?>"/>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="diag_id" value="<?php echo $_POST["diag_id"];?>"/>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
</span>

</form>
</div>
<div class="tabpage">
    <h2>Refraksi</h2>
<form name="frmRefraksi" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "20%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "20%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Umur</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Keluhan Pasien</td>
               <td width= "40%" align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_keluhan","ref_keluhan","70","200",$_POST["ref_keluhan"],"inputField", null,false);?></textarea></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Petugas</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
<!--          <tr>
               <td width="20%"  class="tablecontent" align="left">Dokter</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("ref_dokter_nama","ref_dokter_nama","30","100",$_POST["ref_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>
          </tr>
     -->
          <tr>
               <td width="20%"  class="tablecontent" align="left">Refraksionist</td>
               <td align="left" class="tablecontent-odd" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster_ref">
                         <?php if(!$_POST["ref_suster_nama"]) { ?>
					<tr id="tr_suster_ref_0">
						<td align="left" class="tablecontent-odd" width="40%">
							<?php echo $view->RenderTextBox("ref_suster_nama[]","ref_suster_nama_0","30","100",$_POST["ref_suster_nama"][0],"inputField", "readonly",false);?>							
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>						
							<input type="hidden" id="id_suster_ref_0" name="id_suster_ref[]" value="<?php echo $_POST["id_suster_ref"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="60%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="RefraksionistTambah();">
							<input name="suster_ref_tot" id="suster_ref_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="40%">
                                        <?php echo $view->RenderTextBox("ref_suster_nama[]","ref_suster_nama_".$i,"30","100",$_POST["ref_suster_nama"][$i],"inputField", "readonly",false);?>
                                        <?php //if($edit) {?>
                                             <a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                                        <?php// }?>
                                        <input type="hidden" id="id_suster_ref_<?php echo $i;?>" name="id_suster_ref[]" value="<?php echo $_POST["id_suster"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="60%">
                                        <?php //if($edit) {?>
                                             <?php if($i==0) { ?>
                                                  <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="RefraksionistTambah();">
                                             <?php } else { ?>
                                                  <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="RefraksionistDelete(<?php echo $i;?>);">
                                             <?php } ?>
                                        <?php //}?>
                                        <input name="suster_ref_tot" id="suster_ref_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
				</table>
               </td>
          </tr>
	</table>
     </fieldset>
     
     
     <fieldset>
     <legend><strong>Pemeriksaan Refraksi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="5%" rowspan=2 align="center">Mata</td>
               <td width="30%" rowspan=2 align="center">Visus Tanpa Koreksi</td>
               <td width="35%" colspan=3 align="center">Koreksi</td>
               <td width="30%" rowspan=2 align="center">Visus Dengan Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="15%" align="center">Spheris</td>
               <td width="15%" align="center">Cylinder</td>
               <td width="15%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd" nowrap>
                    <?php echo $view->RenderComboBox("id_visus_nonkoreksi_od","id_visus_nonkoreksi_od",$optVisusNonKoreksiOD,null,null,null);?>
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_od_koreksi_spheris","ref_mata_od_koreksi_spheris","15","15",$_POST["ref_mata_od_koreksi_spheris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_od_koreksi_cylinder","ref_mata_od_koreksi_cylinder","15","15",$_POST["ref_mata_od_koreksi_cylinder"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_od_koreksi_sudut","ref_mata_od_koreksi_sudut","15","15",$_POST["ref_mata_od_koreksi_sudut"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd" nowrap>
                    <?php echo $view->RenderComboBox("id_visus_koreksi_od","id_visus_koreksi_od",$optVisusKoreksiOD,null,null,null);?>
               </td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd" nowrap>
                    <?php echo $view->RenderComboBox("id_visus_nonkoreksi_os","id_visus_nonkoreksi_os",$optVisusNonKoreksiOS,null,null,null);?>
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_os_koreksi_spheris","ref_mata_os_koreksi_spheris","15","15",$_POST["ref_mata_os_koreksi_spheris"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_os_koreksi_cylinder","ref_mata_os_koreksi_cylinder","15","15",$_POST["ref_mata_os_koreksi_cylinder"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_mata_os_koreksi_sudut","ref_mata_os_koreksi_sudut","15","15",$_POST["ref_mata_os_koreksi_sudut"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd" nowrap>
                    <?php echo $view->RenderComboBox("id_visus_koreksi_os","id_visus_koreksi_os",$optVisusKoreksiOS,null,null,null);?>
               </td>
          </tr>
	</table>
     </fieldset>



     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbPinhole');">Pinhole</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbPinhole" style="display:none">
          <tr>
               <td align="left" width="10%" class="tablecontent">OD</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("ref_pinhole_od1","ref_pinhole_od1","3","5",$_POST["ref_pinhole_od1"],"inputField", null,false);?> /
                    <?php echo $view->RenderTextBox("ref_pinhole_od2","ref_pinhole_od2","3","5",$_POST["ref_pinhole_od2"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">OS</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("ref_pinhole_os1","ref_pinhole_os1","3","5",$_POST["ref_pinhole_os1"],"inputField", null,false);?> /
                    <?php echo $view->RenderTextBox("ref_pinhole_os2","ref_pinhole_os2","3","5",$_POST["ref_pinhole_os2"],"inputField", null,false);?>
               </td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbRetinoscopy');">Streak Retinoscopy</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbRetinoscopy" style="display:none">
          <tr class="subheader">
               <td width="100%" colspan=4 align="center">Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="10%" align="center">&nbsp;</td>
               <td width="30%" align="center">Spheris</td>
               <td width="30%" align="center">Cylinder</td>
               <td width="30%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_spheris_od","ref_streak_koreksi_spheris_od","15","15",$_POST["ref_streak_koreksi_spheris_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_cylinder_od","ref_streak_koreksi_cylinder_od","15","15",$_POST["ref_streak_koreksi_cylinder_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_sudut_od","ref_streak_koreksi_sudut_od","15","15",$_POST["ref_streak_koreksi_sudut_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_spheris_os","ref_streak_koreksi_spheris_os","15","15",$_POST["ref_streak_koreksi_spheris_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_cylinder_os","ref_streak_koreksi_cylinder_os","15","15",$_POST["ref_streak_koreksi_cylinder_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_streak_koreksi_sudut_os","ref_streak_koreksi_sudut_os","15","15",$_POST["ref_streak_koreksi_sudut_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbLensometri');">Lensometri</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbLensometri" style="display:none">
          <tr class="subheader">
               <td width="100%" colspan=4 align="center">Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="10%" align="center">&nbsp;</td>
               <td width="30%" align="center">Spheris</td>
               <td width="30%" align="center">Cylinder</td>
               <td width="30%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_spheris_od","ref_lenso_koreksi_spheris_od","15","15",$_POST["ref_lenso_koreksi_spheris_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_cylinder_od","ref_lenso_koreksi_cylinder_od","15","15",$_POST["ref_lenso_koreksi_cylinder_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_sudut_od","ref_lenso_koreksi_sudut_od","15","15",$_POST["ref_lenso_koreksi_sudut_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_spheris_os","ref_lenso_koreksi_spheris_os","15","15",$_POST["ref_lenso_koreksi_spheris_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_cylinder_os","ref_lenso_koreksi_cylinder_os","15","15",$_POST["ref_lenso_koreksi_cylinder_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_lenso_koreksi_sudut_os","ref_lenso_koreksi_sudut_os","15","15",$_POST["ref_lenso_koreksi_sudut_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbArk');">ARK</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbArk" style="display:none">
          <tr class="subheader">
               <td width="100%" colspan=4 align="center">Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="10%" align="center">&nbsp;</td>
               <td width="30%" align="center">Spheris</td>
               <td width="30%" align="center">Cylinder</td>
               <td width="30%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_spheris_od","ref_ark_koreksi_spheris_od","15","15",$_POST["ref_ark_koreksi_spheris_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_cylinder_od","ref_ark_koreksi_cylinder_od","15","15",$_POST["ref_ark_koreksi_cylinder_od"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_sudut_od","ref_ark_koreksi_sudut_od","15","15",$_POST["ref_ark_koreksi_sudut_od"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_spheris_os","ref_ark_koreksi_spheris_os","15","15",$_POST["ref_ark_koreksi_spheris_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_cylinder_os","ref_ark_koreksi_cylinder_os","15","15",$_POST["ref_ark_koreksi_cylinder_os"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_ark_koreksi_sudut_os","ref_ark_koreksi_sudut_os","15","15",$_POST["ref_ark_koreksi_sudut_os"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong><a style="cursor:pointer" onClick="ChangeDisplay('tbPrisma');">Koreksi Prisma</a></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbPrisma" style="display:none">
          <tr class="subheader">
               <td width="30%" align="center">Dioptri</td>
               <td width="30%" align="center">Base Up/Down</td>
               <td width="30%" align="center">Base Up/Down</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("ref_prisma_koreksi_dioptri","ref_prisma_koreksi_dioptri","15","15",$_POST["ref_prisma_koreksi_dioptri"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderComboBox("ref_prisma_koreksi_base1","ref_prisma_koreksi_base1",$optionsBase1,null,null,null);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderComboBox("ref_prisma_koreksi_base2","ref_prisma_koreksi_base2",$optionsBase2,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdateRef" : "btnSaveRef","btnSaveRef","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>	
</table>

<?php echo $view->SetFocus("ref_keluhan");?>
<input type="hidden" name="kontrol_id" value="<?php echo $_POST["kontrol_id"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="id_rawatinap" value="<?php echo $_POST["id_rawatinap"];?>"/>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="ref_tanggal" value="<?php echo $_POST["ref_tanggal"];?>"/>
<input type="hidden" name="ref_id" value="<?php echo $_POST["ref_id"];?>"/>
<?php echo $view->RenderHidden("hid_suster_ref_del","hid_suster_ref_del",'');?>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
</span>
</form>
  </div>
  <div class="tabpage">
    <h2>Pre-OP</h2>
    <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "20%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "20%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Umur</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
	</table>
     </fieldset>
	
     <fieldset>
     <legend><strong>Petugas</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
          <tr>
               <td align="left" width="20%" class="tablecontent">Dokter</td>
               <td align="left" class="tablecontent-odd" width="30%" colspan="2"> 
                    <?php echo $view->RenderTextBox("preop_dokter_nama","preop_dokter_nama","30","100",$_POST["preop_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>
		</tr>
			
     
          <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster_preop">
                         <?php if(!$_POST["preop_suster_nama"]) { ?>
					<tr id="tr_suster_preop_0">
						<td align="left" class="tablecontent-odd" width="70%">
							<?php echo $view->RenderTextBox("preop_suster_nama[]","preop_suster_nama_0","30","100",$_POST["preop_suster_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
							<input type="hidden" id="id_suster_preop_0" name="id_suster_preop[]" value="<?php echo $_POST["id_suster_preop"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="30%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterPreopTambah();">
							<input name="suster_preop_tot" id="suster_preop_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster_preop"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_preop_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $view->RenderTextBox("preop_suster_nama[]","preop_suster_nama_".$i,"30","100",$_POST["preop_suster_nama"][$i],"inputField", "readonly",false);?>
								<?php if($edit) {?>
									<a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
								<?php } ?>
                                        <input type="hidden" id="id_suster_preop_<?php echo $i;?>" name="id_suster_preop[]" value="<?php echo $_POST["id_suster_preop"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
								<?php if($edit) {?>
									<?php if($i==0) { ?>
									<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterPreopTambah();">
									<?php } else { ?>
									<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterPreopDelete(<?php echo $i;?>);">
									<?php } ?>
								<?php } ?>
                                        <input name="suster_preop_tot" id="suster_preop_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_suster_preop_del","hid_suster_preop_del",'');?>
				</table>
               </td>
          </tr> 
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Data Refraksi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="5%" rowspan=2 align="center">Mata</td>
               <td width="30%" rowspan=2 align="center">Visus Tanpa Koreksi</td>
               <td width="35%" colspan=3 align="center">Koreksi</td>
               <td width="30%" rowspan=2 align="center">Visus Dengan Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="15%" align="center">Spheris</td>
               <td width="15%" align="center">Cylinder</td>
               <td width="15%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_od_nonkoreksi_visus"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_sudut"];?></td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_od_koreksi_visus"];?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_os_nonkoreksi_visus"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_sudut"];?></td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_os_koreksi_visus"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - ICD - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOD[0]["icd_nomor"]." ".$dataDiagIcdOD[0]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOD[1]["icd_nomor"]." ".$dataDiagIcdOD[1]["icd_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - ICD - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOS[0]["icd_nomor"]." ".$dataDiagIcdOS[0]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOS[1]["icd_nomor"]." ".$dataDiagIcdOS[1]["icd_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - INA - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOD[0]["ina_kode"]." ".$dataDiagInaOD[0]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOD[1]["ina_kode"]." ".$dataDiagInaOD[1]["ina_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - INA - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOS[0]["ina_kode"]." ".$dataDiagInaOS[0]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 4</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOS[1]["ina_kode"]." ".$dataDiagInaOS[1]["ina_nama"];?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Keratometri</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td align="center" width="20%">&nbsp;</td>
               <td align="center" width="40%">OD</td>
               <td align="center" width="40%">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">K1</td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_k1_od" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_k1_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_k1_os" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_k1_os"];?>"></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">K2</td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_k2_od" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_k2_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_k2_os" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_k2_os"];?>"></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Biometri</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td align="center" width="20%">&nbsp;</td>
               <td align="center" width="40%">OD</td>
               <td align="center" width="40%">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Acial Length</td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_acial_od" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_acial_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_acial_os" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_acial_os"];?>"></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power IOL</td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_iol_od" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_iol_od"];?>"></td>
               <td align="left" class="tablecontent-odd"><input type="text" name="preop_iol_os" size="30" maxlenght="100" value="<?php echo $dataDiagnostik["diag_iol_os"];?>"></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">AV Constant</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("preop_av_constant","preop_av_constant",$optAv,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Standart Deviasi</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderTextBox("preop_deviasi","preop_deviasi","10","30",$dataDiagnostik["diag_deviasi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Rumus yang dipakai</td>
               <td align="left" class="tablecontent-odd" colspan=2><?php echo $view->RenderComboBox("preop_rumus","preop_rumus",$optRumus,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>



     <fieldset>
     <legend><strong>Data Pasien Hari Ini </strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width="20%" align="left" class="tablecontent">Keluhan Pasien</td>
               <td width="80%" align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_keluhan","preop_keluhan","50","200",$_POST["preop_keluhan"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td width="20%" align="left" class="tablecontent">Keadaan Umum</td>
               <td width="80%" align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("preop_keadaan_umum","preop_keadaan_umum",$optionsKeadaan,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Tensimeter</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_tensi","preop_lab_tensi","15","15",$_POST["preop_lab_tensi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Nadi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nadi","preop_lab_nadi","15","15",$_POST["preop_lab_nadi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pernafasan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_nafas","preop_lab_nafas","15","15",$_POST["preop_lab_nafas"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Status Lokal Mata</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_mata_lokal","preop_mata_lokal","15","15",$_POST["preop_mata_lokal"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Gula Darah Acak</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_lab_gula_darah","preop_lab_gula_darah","50","100",$_POST["preop_lab_gula_darah"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Darah Lengkap</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("preop_lab_darah_lengkap","preop_lab_darah_lengkap","5","50",$_POST["preop_lab_darah_lengkap"],"inputField", null,null);?><td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Tonometri OD</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("preop_tonometri_scale_od","preop_tonometri_scale_od","5","5",$_POST["preop_tonometri_scale_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> / 
                    <?php echo $view->RenderTextBox("preop_tonometri_weight_od","preop_tonometri_weight_od","5","5",$_POST["preop_tonometri_weight_od"],"inputField", null,false,'onBlur="return SetTonometriOD();"');?> g = 
                    <?php echo $view->RenderTextBox("preop_tonometri_pressure_od","preop_tonometri_pressure_od","5","5",$_POST["preop_tonometri_pressure_od"],"inputField", "readonly",false);?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Tonometri OS</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("preop_tonometri_scale_os","preop_tonometri_scale_os","5","5",$_POST["preop_tonometri_scale_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> / 
                    <?php echo $view->RenderTextBox("preop_tonometri_weight_os","preop_tonometri_weight_os","5","5",$_POST["preop_tonometri_weight_os"],"inputField", null,false,'onBlur="return SetTonometriOS();"');?> g = 
                    <?php echo $view->RenderTextBox("preop_tonometri_pressure_os","preop_tonometri_pressure_os","5","5",$_POST["preop_tonometri_pressure_os"],"inputField", "readonly",false);?> mmHG
               </td>
          </tr>
	</table>
     </fieldset>




     <fieldset>
     <legend><strong>Rencana Pemakaian IOL</strong></legend>
     <table width="40%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis IOL</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_iol_jenis","preop_iol_jenis",$optIOLJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Merk</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_iol_merk","preop_iol_merk",$optIOLMerk,null,null,null);?></td>
          </tr>
<!--
          <tr>
               <td align="left" class="tablecontent" width="35%">Serial Number</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("preop_iol_sn","preop_iol_sn","50","200",$_POST["preop_iol_sn"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Type</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("preop_iol_type","preop_iol_type","50","200",$_POST["preop_iol_type"],"inputField", null,false);?></td>
          </tr>
-->     

	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Rencana Tindakan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_operasi_jenis","rawat_operasi_jenis",$optOperasiJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Paket Biaya</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_operasi_paket","rawat_operasi_paket",$optOperasiPaket,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Anestesis</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_anestesis_jenis","preop_anestesis_jenis",$optAnestesisJenis,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Obat Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_anestesis_obat","preop_anestesis_obat",$optAnestesisObat,null,null,'onChange="SetDosis(this.value);"');?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Dosis</td>
			<td align="left" class="tablecontent-odd" width="65%"><span id="sp_dosis">
			<?php if($_POST["preop_anestesis_dosis"]) { ?>
				<?php echo GetDosis($_POST["preop_anestesis_obat"],$_POST["preop_anestesis_dosis"]);?>
			<?php } else  { ?>
				<?php echo $view->RenderComboBox("preop_anestesis_dosis","preop_anestesis_dosis",$optDosis,null,null,null);?>
			<?php } ?>
			</span></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Komplikasi Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_anestesis_komp","preop_anestesis_komp",$optAnestesisKomplikasi,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Premedikasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("preop_anestesis_pre","preop_anestesis_pre",$optAnestesisPremedikasi,null,null,null);?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Regulasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="20%" class="tablecontent">Perlu Regulasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderCheckBox("preop_regulasi","preop_regulasi","y","null",($_POST["preop_regulasi"] == "y")?"checked":"",'onClick="GantiRegulasi();"')?>&nbsp;&nbsp;&nbsp;</td>
          </tr>
     </table>
     
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbRegulasi" style="display:block">
          <tr class="subheader">
               <td width="15%" align="center">No</td>
               <td width="45%" align="center">Jenis Regulasi</td>
               <td width="45%" align="center">Dengan Obat</td>
               <td width="45%" align="center">Hasil Regulasi</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">1</td>
               <td align="left" class="tablecontent">Gula Darah</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_regulasi_gula_obat","preop_regulasi_gula_obat","30","30",$_POST["preop_regulasi_gula_obat"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_regulasi_gula_hasil","preop_regulasi_gula_hasil","30","30",$_POST["preop_regulasi_gula_hasil"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent">Tonometri</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_regulasi_tono_obat","preop_regulasi_tono_obat","30","30",$_POST["preop_regulasi_tono_obat"],"inputField", null,false);?></td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_regulasi_tono_hasil","preop_regulasi_tono_hasil","30","30",$_POST["preop_regulasi_tono_hasil"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent-odd" colspan=4><label for="preop_regulasi_berhasil">Berhasil Diregulasi?</label><?php echo $view->RenderCheckBox("preop_regulasi_berhasil","preop_regulasi_berhasil","y","null",($_POST["preop_regulasi_berhasil"] == "y")?"checked":"",'onClick="GantiBerhasil();"')?>&nbsp;&nbsp;&nbsp;</td>
          </tr>
	</table>

     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbCatatan" style="display:none">
          <tr>
               <td align="left" width="20%" class="tablecontent">Catatan Untuk OK</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_catatan_ok","preop_catatan_ok","100","100",$_POST["preop_catatan_ok"],"inputField", null,false);?></td>
          </tr>
     </table>
     
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbTerapi" style="display:none">
          <tr class="subheader">
               <td width="100%" align="center" colspan=3>Tabel Terapi</td>
          </tr>	
          <tr>
               <td align="right" class="tablecontent">1</td>
               <td align="left" class="tablecontent">Obat</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_terapi_obat","preop_terapi_obat","30","30",$_POST["preop_terapi_obat"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">2</td>
               <td align="left" class="tablecontent">Saran</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("preop_terapi_saran","preop_terapi_saran","30","30",$_POST["preop_terapi_saran"],"inputField", null,false);?></td>
          </tr>
	</table>
     	
     </fieldset>


     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdatePreop" : "btnSavePreop","btnSavePreop","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>

     </td>
</tr>	

</table>
<input type="hidden" name="kontrol_id" value="<?php echo $_POST["kontrol_id"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="id_rawatinap" value="<?php echo $_POST["id_rawatinap"];?>"/>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="preop_id" value="<?php echo $_POST["preop_id"];?>"/>
<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
</span>
</form>
  </div>
  <div class="tabpage">
    <h2>Bedah Minor</h2>
  <form name="frmBedah" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="80%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "20%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "20%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Umur</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
     </table>
     </fieldset>

     </td>
</tr>	
</table>

<BR>

<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Refraksi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td width="5%" rowspan=2 align="center">Mata</td>
               <td width="30%" rowspan=2 align="center">Visus Tanpa Koreksi</td>
               <td width="35%" colspan=3 align="center">Koreksi</td>
               <td width="30%" rowspan=2 align="center">Visus Dengan Koreksi</td>
          </tr>	
          <tr class="subheader">
               <td width="15%" align="center">Spheris</td>
               <td width="15%" align="center">Cylinder</td>
               <td width="15%" align="center">Sudut</td>
          </tr>	
          <tr>
               <td align="center" class="tablecontent">OD</td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_od_nonkoreksi_visus"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_sudut"];?></td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_od_koreksi_visus"];?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_os_nonkoreksi_visus"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_sudut"];?></td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_os_koreksi_visus"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - ICD - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOD[0]["icd_nomor"]." ".$dataDiagIcdOD[0]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOD[1]["icd_nomor"]." ".$dataDiagIcdOD[1]["icd_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - ICD - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOS[0]["icd_nomor"]." ".$dataDiagIcdOS[0]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcdOS[1]["icd_nomor"]." ".$dataDiagIcdOS[1]["icd_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - INA - OD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOD[0]["ina_kode"]." ".$dataDiagInaOD[0]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOD[1]["ina_kode"]." ".$dataDiagInaOD[1]["ina_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - INA - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOS[0]["ina_kode"]." ".$dataDiagInaOS[0]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 4</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagInaOS[1]["ina_kode"]." ".$dataDiagInaOS[1]["ina_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Rencana Tindakan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataJenisTindakan["op_jenis_nama"];?></td>
          </tr>
	</table>
     </fieldset>
     
     <fieldset>
     <legend><strong>Inputan Injeksi</strong></legend>
     <table width="100%" border="1" cellpadding="2" cellspacing="1" id="tb_injeksi">
          <?php if(!$dataDetailInjeksi) {?>
               <tr id="tr_injeksi_<?php echo $i;?>">
                    <td width="10%" class="tablecontent">Nama Obat</td>
                    <td width="20%" class="tablecontent-odd">
                         <?php 
                              $optObat[0] = $view->RenderOption("","[Pilih Obat]",$show); 
                              for($i=0,$n=count($dataObat);$i<$n;$i++) {
                                   $optObat[$i+1] = $view->RenderOption($dataObat[$i]["item_id"],$dataObat[$i]["item_nama"]); 
                              }                                                             
                              echo $view->RenderComboBox("id_item[0]","id_item_0",$optObat,null,null,"onChange='ItemDosis(this.options[this.selectedIndex].value,0)'");
                         ?>
                    </td>
                    <td width="10%" class="tablecontent">Dosis</td>
                    <td width="20%" class="tablecontent-odd">
                         <span id="div_dosis_0">
                         </span>
                    </td>
                    <td width="10%" class="tablecontent">Teknik Injeksi</td>
                    <td width="30%" class="tablecontent-odd">
                         <?php
                              $optInjeksi[0] = $view->RenderOption("","[Pilih Teknik Injeksi]",$show); 
                              for($i=0,$n=count($dataInjeksi);$i<$n;$i++) {
                                   $optInjeksi[$i+1] = $view->RenderOption($dataInjeksi[$i]["injeksi_id"],$dataInjeksi[$i]["injeksi_nama"]); 
                              }                               
                              echo $view->RenderComboBox("id_injeksi[0]","id_injeksi_0",$optInjeksi);
                         ?>
                         <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="InjeksiTambah();">
                         <input name="hid_tot_injeksi" id="hid_tot_injeksi" type="hidden" value="0">
                    </td>               
               </tr>
          <?php } else {?>
               <?php for($i=0,$n=count($dataDetailInjeksi);$i<$n;$i++) { ?>
                    <tr>
                         <td width="10%" class="tablecontent"><?php echo ($i==0)?"Nama Obat":"&nbsp;"?></td>
                         <td width="20%" class="tablecontent-odd">
                              <?php 
                                   $optObat[0] = $view->RenderOption("","[Pilih Obat]",$show); 
                                   for($j=0,$k=count($dataObat);$j<$k;$j++) {
                                        $show = ($dataDetailInjeksi[$i]["id_item"]==$dataObat[$j]["item_id"]) ? "selected":"";
                                        $optObat[$j+1] = $view->RenderOption($dataObat[$j]["item_id"],$dataObat[$j]["item_nama"],$show); 
                                   }
                                    
                                   echo $view->RenderComboBox("id_item[]","id_item_".$i,$optObat,null,null,"onChange='ItemDosis(this.options[this.selectedIndex].value,".$i.")'");
                              ?>
                         </td>
                         <td width="10%" class="tablecontent"><?php echo ($i==0)?"Dosis":"&nbsp;"?></td>
                         <td width="20%" class="tablecontent-odd">
                              <span id="div_dosis_<?php echo $i?>">
                                   <?php echo GetDosis($dataDetailInjeksi[$i]["id_item"],$i,$dataDetailInjeksi[$i]["id_dosis"]);?>
                              </span>
                         </td>                         
                         <td width="10%" class="tablecontent"><?php echo ($i==0)?"Teknik Injeksi":"&nbsp;"?></td>
                         <td width="15%" class="tablecontent-odd">
                              <?php
                                   $optInjeksi[0] = $view->RenderOption("","[Pilih Teknik Injeksi]",$show); 
                                   for($j=0,$k=count($dataInjeksi);$j<$k;$j++) {
                                        $show = ($dataDetailInjeksi[$i]["id_injeksi"]==$dataInjeksi[$j]["injeksi_id"]) ? "selected":"";
                                        $optInjeksi[$j+1] = $view->RenderOption($dataInjeksi[$j]["injeksi_id"],$dataInjeksi[$j]["injeksi_nama"],$show); 
                                   }
                                    
                                   echo $view->RenderComboBox("id_injeksi[0]","id_injeksi_0",$optInjeksi);
                              ?>
                         </td>
                         <td align="center" width="15%">
                              <?php if($i==0) { ?>
                                   <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="InjeksiTambah();">
                              <?php } else { ?>
                                   <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="InjeksiDelete(<?php echo $i;?>);">
                              <?php } ?>
                              
                              <input name="hid_tot_injeksi" id="hid_tot_injeksi" type="hidden" value="<?php echo $n;?>">
                         </td>
                    </tr>
               <?php } ?>
          <?php }?>
          <?php echo $view->RenderHidden("hid_injeksi_del","hid_injeksi_del",'');?>
     </table>
     <br />
     
     <table width="100%" border="1" cellpadding="2" cellspacing="1">
          <tr>
               <td width="30%"  class="tablecontent" align="left">Petugas Injeksi</td>
               <td align="left" class="tablecontent-odd" width="80%" colspan=6>
				<table width="100%" border="1" cellpadding="1" cellspacing="1" id="tb_suster_op">
                         <?php if(!$_POST["op_suster_nama"]) { ?>
					<tr id="tr_suster_op_0">
						<td align="left" class="tablecontent-odd" width="40%">
							<?php echo $view->RenderTextBox("op_suster_nama[]","op_suster_nama_0","30","100",$_POST["op_suster_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
							<input type="hidden" id="id_suster_op_0" name="id_suster_op_[]" value="<?php echo $_POST["id_suster_op_"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="60%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterOPTambah();">
							<input name="suster_op_tot" id="suster_op_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster_op_"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_op_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $view->RenderTextBox("op_suster_nama[]","op_suster_nama_".$i,"30","100",$_POST["op_suster_nama"][$i],"inputField", "readonly",false);?>
								<?php if($edit) { ?>
									<a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
								<?php } ?>
                                        <input type="hidden" id="id_suster_op_<?php echo $i;?>" name="id_suster_op_[]" value="<?php echo $_POST["id_suster_op_"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
								<?php if($edit) { ?>
									<?php if($i==0) { ?>
									<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterOPTambah();">
									<?php } else { ?>
									<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterOPDelete(<?php echo $i;?>);">
									<?php } ?>
								<?php } ?>
                                        <input name="suster_op_tot" id="suster_op_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_suster_op_del","hid_suster_op_del",'');?>
				</table>
               </td>
          </tr>          
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Data Laporan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="20%" class="tablecontent">Dokter</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_dokter_nama","op_dokter_nama","20","100",$_POST["op_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>

                
               <td width="20%" class="tablecontent" align="left">Asisten Perawat</td>
               <td align="left" class="tablecontent-odd" width="30%" colspan=6>
				<table width="100%" border="1" cellpadding="1" cellspacing="1" id="tb_asisten_suster">
                         <?php if(!$_POST["op_asisten_suster_terapi_nama"]) { ?>
					<tr id="tr_asisten_suster_0">
						<td align="left" class="tablecontent-odd" width="75%">
							<?php echo $view->RenderTextBox("op_asisten_suster_terapi_nama[]","op_asisten_suster_terapi_nama_0","30","100",$_POST["op_asisten_suster_terapi_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $asistenSusterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Asisten Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Asisten Suster" /></a>
							<input type="hidden" id="id_asisten_suster_terapi_0" name="id_asisten_suster_terapi[]" value="<?php echo $_POST["id_asisten_suster_terapi"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="25%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="AsistenSusterTambah();">
							<input name="asisten_suster_tot" id="asisten_suster_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_asisten_suster_terapi"]);$i<$n;$i++) { ?>
                              <tr id="tr_asisten_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="60%">
                                        <?php echo $view->RenderTextBox("op_asisten_suster_terapi_nama[]","op_asisten_suster_terapi_nama_".$i,"30","100",$_POST["op_asisten_suster_terapi_nama"][$i],"inputField", "readonly",false);?>
								<?php if($edit) { ?>
									<a href="<?php echo $asistenSusterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Asisten Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Asisten Suster" alt="Cari Asisten Suster" /></a>
								<?php } ?>
                                        <input type="hidden" id="id_asisten_suster_terapi_<?php echo $i;?>" name="id_asisten_suster_terapi[]" value="<?php echo $_POST["id_asisten_suster_terapi"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="40%">
								<?php if($edit) { ?>
									<?php if($i==0) { ?>
									<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="AsistenSusterTambah();">
									<?php } else { ?>
									<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="AsistenSusterDelete(<?php echo $i;?>);">
									<?php } ?>
								<?php } ?>
                                        <input name="asisten_suster_tot" id="asisten_suster_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_asisten_suster_terapi_del","hid_asisten_suster_terapi_del",'');?>
				</table>
               </td>
          
          </tr>
          <tr>
               <td align="left" class="tablecontent">Jam</td>
               <td align="left" class="tablecontent-odd" colspan=3>
				<select name="op_mulai_jam" class="inputField" >
					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_mulai_jam"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
					</select>:
					<select name="op_mulai_menit" class="inputField" >
					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_mulai_menit"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				s/d
				<select name="op_selesai_jam" class="inputField" >
					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_jam"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
					</select>:
					<select name="op_selesai_menit" class="inputField" >
					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_menit"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderComboBox("id_op_jenis","id_op_jenis",$optOperasiJenis,null,null,null);?>               
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Kode ICDM</td>
               <td align="left" class="tablecontent-odd" width="20%" colspan="3">
                    <table width="100%" border="1" cellpadding="2" cellspacing="1" id="tb_icd">
                         <?php if(!$dataIcd) {?>
                              <tr id="tr_icd_0">
                                   <td width="">
                                        <?php echo $view->RenderTextBox("op_icd_kode[0]","op_icd_kode_0","10","100",$_POST["op_icd_kode"][0],"inputField", "readonly",false);?>
                                        <a href="<?php echo $icdPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                                        <input type="hidden" id="id_icd_0" name="id_icd[0]" value="<?php echo $_POST["id_icd"][0];?>"/>
                                   </td>
                                   <td>
                                        <?php echo $view->RenderTextBox("op_icd_nama[0]","op_icd_nama_0","50","100",$_POST["op_icd_nama"][0],"inputField", "readonly",false);?>                    
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
     							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="IcdTambah();">
     							<input name="icd_tot" id="icd_tot" type="hidden" value="0">
     			               </td>
                              </tr>
                         <?php } else {
                              for($i=0,$n=count($dataIcd);$i<$n;$i++) {?>
                                   <tr id="tr_icd_<?php echo $i?>">
                                        <td>
                                             <?php echo $view->RenderTextBox("op_icd_kode[]","op_icd_kode_".$i,"10","100",$dataIcd[$i]["op_icd_kode"],"inputField", "readonly",false);?>
                                             <a href="<?php echo $icdPage;?>&el=<?php echo $i?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                                             <input type="hidden" id="id_icd_<?php echo $i?>" name="id_icd[]" value="<?php echo $dataIcd[$i]["id_icd"];?>"/>
                                        </td>
                                        <td>
                                             <?php echo $view->RenderTextBox("op_icd_nama[]","op_icd_nama_".$i,"50","100",$dataIcd[$i]["op_icd_nama"],"inputField", "readonly",false);?>
                                        </td>
                                        <td align="left" class="tablecontent-odd" width="30%">
                                             <?php if($i==0) {?>
          							   <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="IcdTambah();">
          							<?php } else {?>
          							   <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="IcdDelete(<?php echo $i;?>);">
          							<?php }?>
          							
          							<input name="icd_tot" id="icd_tot" type="hidden" value="<?php echo $n?>">
          			               </td>
                                   </tr>
                              <?php } 
                         }
                         echo $view->RenderHidden("hid_icd_del","hid_icd_del",'');?>
                    </table>
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">INA DRG</td>
               <td align="left" class="tablecontent-odd" width="20%"> 
                    <?php echo $view->RenderTextBox("op_ina_kode","op_ina_kode","10","100",$_POST["op_ina_kode"],"inputField", "readonly",false);?>
                    <a href="<?php echo $inaPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" id="id_ina" name="id_ina" value="<?php echo $_POST["id_ina"];?>"/>
               </td>

               <td align="left" width="20%" class="tablecontent">Jenis Procedure</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_ina_nama","op_ina_nama","50","100",$_POST["op_ina_nama"],"inputField", "readonly",false);?>               
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Paket Biaya</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <?php echo $view->RenderComboBox("op_paket_biaya","op_paket_biaya",$optOperasiPaket,null,null,null);?>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Komplikasi Durante OP</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <?php for($i=0,$n=count($dataDurop);$i<$n;$i++) { ?>                    
                         <?php echo $view->RenderCheckBox("id_durop_komp[".$dataDurop[$i]["durop_komp_id"]."]","id_durop_komp_".$dataDurop[$i]["durop_komp_id"],"y","null",($_POST["id_durop_komp"][$dataDurop[$i]["durop_komp_id"]] == "y")?"checked":"");?>
                         <label for="id_durop_komp_<?php echo $dataDurop[$i]["durop_komp_id"];?>"><?php echo $dataDurop[$i]["durop_komp_nama"];?></label><BR>
                    <?php } ?>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pesan Khusus Dari Operator</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderTextArea("op_pesan","op_pesan","3","40",$_POST["op_pesan"]);?>               
               </td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
     <tr>
        <?php if(!$_GET["id"]) { ?>
                    <td align="left" width="20%" class="tablecontent">Tahap Berikutnya</td>
                    <td align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("cmbNext","cmbNext",$optionsNext,null,null,null);?></td>
               <?php } ?>
     </tr>
		<tr>
			<td align="center" colspan="2"  class="tablecontent-odd"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdateOP" : "btnSaveOP","btnSaveOP","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>	
</table>
<input type="hidden" name="kontrol_id" value="<?php echo $_POST["kontrol_id"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="id_rawatinap" value="<?php echo $_POST["id_rawatinap"];?>"/>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="op_id" value="<?php echo $_POST["op_id"];?>"/>
<input type="hidden" name="status" value="<?php echo $_POST["status"];?>"/>
</form>
  </div>
</div>
<input type="hidden" name="kontrol_id" value="<?php echo $_POST["kontrol_id"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="id_rawatinap" value="<?php echo $_POST["id_rawatinap"];?>"/>
<input type="hidden" name="id_perawatan" value="<?php echo $_POST["rawat_id"];?>"/>
<input type="hidden" name="id_diag" value="<?php echo $_POST["diag_id"];?>"/>
<input type="hidden" name="id_op" value="<?php echo $_POST["op_id"];?>"/>
<input type="hidden" name="id_preop" value="<?php echo $_POST["preop_id"];?>"/>
<input type="hidden" name="id_refraksi" value="<?php echo $_POST["ref_id"];?>"/>


</form>
<script>
    Calendar.setup({
        inputField     :    "tanggal_kontrol",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php } ?>
<?php echo $view->SetFocus("rawat_keluhan");?>
<?php echo $view->RenderBodyEnd(); ?>