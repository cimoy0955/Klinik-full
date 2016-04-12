<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/tree.cls.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     
 	if(!$auth->IsAllowed("pemeriksaan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("pemeriksaan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "perawatan.php";
     $icdPage = "icd_find.php?";
     $procPage = "proc_find.php?";
     $inaPage = "ina_find.php?";
     $terapiPage = "obat_find.php?";
     $dokterPage = "rawat_dokter_find.php?";
     $susterPage = "rawat_suster_find.php?";
     $tindakanPage = "tindakan_find.php?";
     $backPage = "perawatan_view.php?";
     $adminFindPage = "rawat_admin_find.php?";

     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetPerawatan,SetPerawatan,GetTonometri,GetDosis");     

     function GetTonometri($scale,$weight) {
          global $dtaccess; 
               
          $sql = "select tono_pressure from klinik.klinik_tonometri where tono_scale = ".QuoteValue(DPE_NUMERIC,$scale)." and tono_weight = ".QuoteValue(DPE_NUMERIC,$weight);
          $dataTable = $dtaccess->Fetch($sql);
          return $dataTable["tono_pressure"];

     }     

     function GetPerawatan($status) {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT, $bayarPasien2; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, a.reg_jadwal,a.reg_jenis_pasien
                    from klinik.klinik_registrasi a
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.reg_status like '".STATUS_PEMERIKSAAN.$status."' and a.reg_tipe_umur = 'D' and (a.reg_tipe_rawat = ".QuoteValue(DPE_CHAR,RAWAT_JALAN)." or a.reg_tipe_rawat is null)
		    order by reg_waktu asc, reg_status desc, reg_tanggal asc";
		    
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
/*

		if($status==0) {
			$tbHeader[0][$counterHeader][TABLE_ISI] = "Bayar";
			$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
			$counterHeader++;
		}*/
		
		$tbHeader[0][$counterHeader][TABLE_ISI] = "Jadwal";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;
          
          
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
			if($status==0) {
				/*if(!$dataTable[$i]["fol_lunas"])*/ $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesPerawatan(\''.$dataTable[$i]["reg_id"].'\')"/>';
			} else {
				$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
			}
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$bayarPasien2[$dataTable[$i]["reg_jenis_pasien"]];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
                         
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          
			/*if($status==0) {
				if(!$dataTable[$i]["fol_lunas"]) $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/on.gif" style="cursor:pointer" alt="Lunas" title="Lunas" border="0"/>';
				else $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/off.gif" style="cursor:pointer" alt="Belum Lunas" title="Belum Lunas" border="0"/>';
				$tbContent[$i][$counter][TABLE_ALIGN] = "center";
				$counter++;
			}*/
			
			if($dataTable[$i]["reg_jadwal"]=='y') $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="15" height="15" src="'.$APLICATION_ROOT.'images/off.gif" alt="Terjadwal Operasi Hari Ini" title="Terjadwal Operasi Hari Ini" border="0"/>';
			else $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/on.gif" alt="Tidak Terjadwal Operasi Hari Ini" title="Tidak Terjadwal Operasi Hari Ini" border="0"/>';
               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }

          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
	  // return $sql;
	}

     function SetPerawatan($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_PEMERIKSAAN.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
     
     function GetDosis($fisik,$akhir,$id=null) {
          global $dtaccess, $view;
          
          $sql = "select dosis_id, dosis_nama from inventori.inv_dosis where id_fisik = ".QuoteValue(DPE_CHAR,$fisik);
          $dataTable = $dtaccess->FetchAll($sql);
//echo $sql;
          $optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show); 
          for($i=0,$n=count($dataTable);$i<$n;$i++) {
               $show = ($id==$dataTable[$i]["dosis_id"]) ? "selected":"";
               $optDosis[$i+1] = $view->RenderOption($dataTable[$i]["dosis_id"],$dataTable[$i]["dosis_nama"],$show); 
          }
          
          return $view->RenderComboBox("id_dosis[]","id_dosis_".$akhir,$optDosis,null,null,null);
     }
     
  /*   
     if(!$_POST["btnSave"] && !$_POST["btnUpdate"]) {
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
	
	if(!$_POST["btnSave"] && !$_POST["btnUpdate"]) {
	
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
    */

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
      
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, 
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                    from klinik.klinik_registrasi a
				            left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
         
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
    $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
		$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
  
          $diagLink = "perawatan_diag.php?id_cust_usr=".$enc->Encode($dataPasien["id_cust_usr"])."&id_reg=".$enc->Encode($_GET["id_reg"]);
          
          $sql = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          
          $dataPemeriksaan = $dtaccess->Fetch($sql);
          // if($dataPemeriksaan) $_x_mode = "Diag";
	  
          $view->CreatePost($dataPemeriksaan);

         /*
          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster"][$i] = $row["pgw_id"];
               $_POST["rawat_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
          */

          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $row=$dtaccess->Fetch($rs);
          $_POST["id_dokter"] = $row["pgw_id"];
          $_POST["rawat_dokter_nama"] = $row["pgw_nama"];
          unset($rs);
          unset($row);

          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_admin a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          
          
          $row=$dtaccess->Fetch($rs);
          $_POST["id_admin"] = $row["pgw_id"];
          $_POST["rawat_admin_nama"] = $row["pgw_nama"];
          
          unset($rs);
          unset($row);

          // --- icd od
          $sql = "select icd_nama,icd_nomor, icd_id from klinik.klinik_perawatan_icd a
                    join klinik.klinik_icd b on a.id_icd = b.icd_nomor where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_icd_odos = 'OD'
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
                    join klinik.klinik_icd b on a.id_icd = b.icd_nomor where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_icd_odos = 'OS'
                    order by rawat_icd_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_icd_os_id"][$i] = $row["icd_id"];
               $_POST["rawat_icd_os_kode"][$i] = $row["icd_nomor"];
               $_POST["rawat_icd_os_nama"][$i] = $row["icd_nama"];
               $i++;

          }

          // --- prosedur
          $sql = "select prosedur_kode, prosedur_nama from klinik.klinik_perawatan_prosedur a
                    join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." order by rawat_prosedur_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["rawat_prosedur_kode"][$i] = $row["prosedur_kode"];
               $_POST["rawat_prosedur_nama"][$i] = $row["prosedur_nama"];
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
          $sql = "select * from klinik.klinik_perawatan_terapi a
                    left join stocks.tb_item b on a.id_item = b.id
                    left join stocks.item_price c on c.item_kode = b.item_kode
                    where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." 
                    order by rawat_item_urut";
                    // echo $sql;
          $rs = $dtaccess->Execute($sql);
          $i=0;
          
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_item"][$i] = $row["id_item"];
               $_POST["item_nama"][$i] = $row["item_nama"];
               $_POST["txtDosis_1"][$i] = $row["terapi_jumlah_item"];
	       $_POST["id_fisik"][$i] = $row["id_fisik"];
	       $_POST["txtJumlah_1"][$i] = currency_format($row["price_list_rate"]);
               $i++;
          }
	  
	   // --- tindakan tambahan
          $sql = "select * from klinik.klinik_perawatan_tindakan a
                    left join klinik.klinik_biaya b on a.id_tindakan = b.biaya_id
                    where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." 
                    order by rawat_tindakan_urut";//a.id_tindakan = b.biaya_id 
          $rs = $dtaccess->Execute($sql);
          $i=0;
          //echo $sql;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["tindakan_id"][$i] = $row["biaya_id"];
               $_POST["tindakan_nama"][$i] = $row["biaya_nama"];
	       $_POST["tindakan_total"][$i] = currency_format($row["biaya_total"]);
               $i++;
          }
          
          $sql = "select a.dokter_1,a.perawat_1,a.perawat_2,a.perawat_3,a.perawat_4,a.perawat_5,
          a.petugas_id, b.pgw_nama as dokter1, c.pgw_nama as perawat1 , 
d.pgw_nama as perawat2, e.pgw_nama as perawat3 , f.pgw_nama as perawat4, g.pgw_nama as perawat5, h.pgw_nama as perawat6
from global.global_petugas a
left join hris.hris_pegawai b on b.pgw_id = a.dokter_1
left join hris.hris_pegawai c on c.pgw_id = a.perawat_1
left join hris.hris_pegawai d on d.pgw_id = a.perawat_2
left join hris.hris_pegawai e on e.pgw_id = a.perawat_3
left join hris.hris_pegawai f on f.pgw_id = a.perawat_4
left join hris.hris_pegawai g on g.pgw_id = a.perawat_5
left join hris.hris_pegawai h on h.pgw_id = a.perawat_6
where id_app = ".QuoteValue(DPE_NUMERIC,'1');		
      $rs = $dtaccess->Execute($sql);
			$row=$dtaccess->Fetch($rs); 
			$_POST["id_dokter"] = $row["dokter_1"];
			$_POST["rawat_dokter_nama"] = $row["dokter1"];
		if($row["perawat1"]){
    	$_POST["id_suster"][0] = $row["perawat_1"];
			$_POST["rawat_suster_nama"][0] = $row["perawat1"];
		}
		  if($row["perawat2"]){
    	$_POST["id_suster"][1] = $row["perawat_2"];
			$_POST["rawat_suster_nama"][1] = $row["perawat2"];
		}	
			if($row["perawat3"]){
			$_POST["id_suster"][2] = $row["perawat_3"];
			$_POST["rawat_suster_nama"][2] = $row["perawat3"];
		}	
			if($row["perawat4"]){
			$_POST["id_suster"][3] = $row["perawat_4"];
			$_POST["rawat_suster_nama"][3] = $row["perawat4"];
		}	
			if($row["perawat5"]){
			$_POST["id_suster"][4] = $row["perawat_5"];
			$_POST["rawat_suster_nama"][4] = $row["perawat5"];
		}
    	if($row["perawat6"]){
			$_POST["id_suster"][5] = $row["perawat_6"];
			$_POST["rawat_suster_nama"][5] = $row["perawat6"];
		}	



   
     $sqlRefraksi = "select c.ref_pinhole_od, c.ref_pinhole_os, c.ref_mata_os_koreksi_spheris,
              c.ref_mata_od_koreksi_spheris, c.ref_mata_od_koreksi_cylinder, c.ref_mata_os_koreksi_cylinder,
              c.ref_mata_od_koreksi_sudut, c.ref_mata_os_koreksi_sudut,
              c.id_visus_nonkoreksi_od,c.id_visus_nonkoreksi_os,c.id_visus_koreksi_od,c.id_visus_koreksi_os,
              d.visus_nama as nk_od, e.visus_nama as nk_os, f.visus_nama as k_od, g.visus_nama as k_os,
              c.ref_prisma_koreksi_dioptri, c.ref_prisma_koreksi_base1, c.ref_prisma_koreksi_base2
              from klinik.klinik_registrasi a 
              join klinik.klinik_refraksi c on c.id_reg = a.reg_id 
              left join klinik.klinik_visus d on d.visus_id=c.id_visus_nonkoreksi_od
              left join klinik.klinik_visus e on e.visus_id=c.id_visus_nonkoreksi_os
              left join klinik.klinik_visus f on f.visus_id=c.id_visus_koreksi_od
              left join klinik.klinik_visus g on g.visus_id=c.id_visus_koreksi_os";
     $sqlRefraksi.= " where a.reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
     $sqlRefraksi.= " order by a.reg_status_pasien"; 
      $rs = $dtaccess->Execute($sqlRefraksi);
     $dataRefraksi = $dtaccess->Fetch($rs);
     //echo $sqlRefraksi;
//echo $dataRefraksi["ref_mata_os_koreksi_spheris"];
$_POST["ref_mata_od_koreksi_spheris"] = $dataRefraksi["ref_mata_od_koreksi_spheris"];
$_POST["ref_mata_os_koreksi_spheris"] = $dataRefraksi["ref_mata_os_koreksi_spheris"];
$_POST["ref_mata_od_koreksi_cylinder"] = $dataRefraksi["ref_mata_od_koreksi_cylinder"];
$_POST["ref_mata_os_koreksi_cylinder"] = $dataRefraksi["ref_mata_os_koreksi_cylinder"];
$_POST["ref_mata_od_koreksi_sudut"] = $dataRefraksi["ref_mata_od_koreksi_sudut"];
$_POST["ref_mata_os_koreksi_sudut"] = $dataRefraksi["ref_mata_os_koreksi_sudut"];
$_POST["nk_od"] = $dataRefraksi["nk_od"];
$_POST["nk_os"] = $dataRefraksi["nk_os"];
$_POST["k_od"] = $dataRefraksi["k_od"];
$_POST["k_os"] = $dataRefraksi["k_os"];

$_POST["id_visus_nonkoreksi_od"] = $dataRefraksi["id_visus_nonkoreksi_od"];
$_POST["id_visus_nonkoreksi_os"] = $dataRefraksi["id_visus_nonkoreksi_os"];
$_POST["id_visus_koreksi_od"] = $dataRefraksi["id_visus_koreksi_od"];
$_POST["id_visus_koreksi_os"] = $dataRefraksi["id_visus_koreksi_os"];
	} 
	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
	
          
          // --- delete data e dulu ---
          if($_POST["btnSave"]) {               
               $sql = "delete from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
          }
          
          $m=0;
          $dbTable = "klinik.klinik_perawatan";
          $dbField[$m] = "rawat_id";    $m++;// PK
          $dbField[$m] = "id_reg"; $m++;
          $dbField[$m] = "rawat_keluhan"; $m++;
          $dbField[$m] = "rawat_keadaan_umum"; $m++;
          $dbField[$m] = "rawat_tonometri_scale_od"; $m++;
          $dbField[$m] = "rawat_anel"; $m++;
          $dbField[$m] = "rawat_schimer"; $m++;
          $dbField[$m] = "rawat_lab_gula_darah"; $m++;
          $dbField[$m] = "rawat_lab_darah_lengkap"; $m++;
          $dbField[$m] = "rawat_lab_tensi"; $m++;
          $dbField[$m] = "rawat_lab_nadi"; $m++;
          $dbField[$m] = "rawat_lab_nafas"; $m++;
          $dbField[$m] = "rawat_lab_alergi"; $m++;
          $dbField[$m] = "rawat_mata_od_palpebra"; $m++;
          $dbField[$m] = "rawat_mata_os_palpebra"; $m++;
          $dbField[$m] = "rawat_mata_od_conjunctiva"; $m++;
          $dbField[$m] = "rawat_mata_os_conjunctiva"; $m++;
          $dbField[$m] = "rawat_mata_od_cornea"; $m++;
          $dbField[$m] = "rawat_mata_os_cornea"; $m++;
          $dbField[$m] = "rawat_mata_od_coa"; $m++;
          $dbField[$m] = "rawat_mata_os_coa"; $m++;
          $dbField[$m] = "rawat_mata_od_iris"; $m++;
          $dbField[$m] = "rawat_mata_os_iris"; $m++;
          $dbField[$m] = "rawat_mata_od_pupil"; $m++;
          $dbField[$m] = "rawat_mata_os_pupil"; $m++;
          $dbField[$m] = "rawat_mata_od_lensa"; $m++;
          $dbField[$m] = "rawat_mata_os_lensa"; $m++;
          $dbField[$m] = "rawat_mata_od_ocular"; $m++;
          $dbField[$m] = "rawat_mata_os_ocular"; $m++;
          $dbField[$m] = "rawat_mata_od_retina"; $m++;
          $dbField[$m] = "rawat_mata_os_retina"; $m++;
          $dbField[$m] = "id_cust_usr"; $m++;
          $dbField[$m] = "rawat_tonometri_weight_od"; $m++;
          $dbField[$m] = "rawat_tonometri_pressure_od"; $m++;
          $dbField[$m] = "rawat_mata_foto";         $m++;  
          $dbField[$m] = "rawat_mata_sketsa"; $m++;
          $dbField[$m] = "rawat_tonometri_od"; $m++;
          $dbField[$m] = "rawat_tonometri_os"; $m++;
          $dbField[$m] = "rawat_anestesis_jenis"; $m++;
          $dbField[$m] = "rawat_anestesis_obat"; $m++;
          $dbField[$m] = "rawat_anestesis_dosis"; $m++;
          $dbField[$m] = "rawat_anestesis_komp"; $m++; 
          $dbField[$m] = "rawat_anestesis_pre"; $m++;
          $dbField[$m] = "rawat_operasi_jenis"; $m++;
          $dbField[$m] = "rawat_operasi_paket"; $m++;
          $dbField[$m] = "rawat_tonometri_weight_os"; $m++;
          $dbField[$m] = "rawat_tonometri_pressure_os"; $m++;
          $dbField[$m] = "rawat_tonometri_scale_os"; $m++;
          $dbField[$m] = "rawat_color_blindness"; $m++;
          $dbField[$m] = "rawat_catatan"; $m++;
          $dbField[$m] = "rawat_irigasi"; $m++;
          $dbField[$m] = "rawat_epilasi"; $m++;
          $dbField[$m] = "rawat_suntikan"; $m++;
          $dbField[$m] = "rawat_probing"; $m++;
          $dbField[$m] = "rawat_flouorecsin"; $m++;
          $dbField[$m] = "rawat_kesehatan"; $m++;
          $dbField[$m] = "rawat_kacamata_refraksi"; $m++;
          $dbField[$m] = "rawat_mata_od_koreksi_spheris"; $m++;
          $dbField[$m] = "rawat_mata_od_koreksi_cylinder"; $m++;
          $dbField[$m] = "rawat_mata_od_koreksi_sudut"; $m++;
          $dbField[$m] = "rawat_mata_os_koreksi_spheris"; $m++;
          $dbField[$m] = "rawat_mata_os_koreksi_cylinder"; $m++;
          $dbField[$m] = "rawat_mata_os_koreksi_sudut"; $m++;
          $dbField[$m] = "rawat_tanggal"; $m++;
          $dbField[$m] = "rawat_od_vitreus";  $m++;
          $dbField[$m] = "rawat_os_vitreus";  $m++;
          $dbField[$m] = "rawat_nct_od"; $m++; 
          $dbField[$m] = "rawat_nct_os"; $m++;
          if($_POST["btnSave"]) { $dbField[$m] = "rawat_next"; $m++; }
                    
          if($_POST["btnSave"]) { $dbField[$m] = "rawat_waktu"; $m++; }
          if($_POST["cmbRujuk"] && $_POST["cmbRujuk"]!='--') {$dbField[$m] = "rawat_rujukan_id"; $m++;}
          if($_POST["rawat_rujukan"] && $_POST["rawat_rujukan"]!='') {$dbField[$m] = "rawat_rujukan"; $m++;}
          
          $m=0;
          if(!$_POST["rawat_id"]) $_POST["rawat_id"] = $dtaccess->GetTransID();
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   $m++; // PK
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["id_reg"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_keluhan"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_keadaan_umum"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_scale_od"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_anel"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_schimer"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_gula_darah"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_darah_lengkap"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_tensi"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_nadi"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_nafas"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_alergi"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_palpebra"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_palpebra"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_conjunctiva"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_conjunctiva"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_cornea"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_cornea"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_coa"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_coa"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_iris"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_iris"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_pupil"]); $m++; 
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_pupil"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_lensa"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_lensa"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_ocular"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_ocular"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_retina"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_retina"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_weight_od"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_pressure_od"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_foto"]);           $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_sketsa"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_tonometri_od"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_tonometri_os"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_jenis"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_NUMERICKEY,$_POST["rawat_anestesis_obat"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_anestesis_dosis"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_komp"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_pre"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHARKEY,$_POST["rawat_operasi_jenis"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHARKEY,$_POST["rawat_operasi_paket"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_weight_os"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_pressure_os"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_scale_os"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_color_blindness"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_catatan"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_irigasi"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_epilasi"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_suntikan"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_probing"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_flouorecsin"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_kesehatan"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_kacamata_refraksi"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_spheris"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_cylinder"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_sudut"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_spheris"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_cylinder"]); $m++; 
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_sudut"]); $m++;
          $dbValue[$m] = QuoteValue(DPE_DATE,date("Y-m-d")); $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_od_vitreus"]);  $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_os_vitreus"]);  $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_nct_od"]);  $m++;
          $dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_nct_os"]);  $m++;
          if($_POST["cmbNext"]) {$dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["cmbNext"]);  $m++;}
          if($_POST["btnSave"]) {$dbValue[$m] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s")); $m++; }
          if($_POST["cmbRujuk"] && $_POST["cmbRujuk"]!='--') {$dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["cmbRujuk"]); $m++;}
          if($_POST["rawat_rujukan"] && $_POST["rawat_rujukan"]!='') {$dbValue[$m] = QuoteValue(DPE_CHAR,$_POST["rawat_rujukan"]); $m++;}

          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          if ($_POST["btnSave"]) {
              $dtmodel->Insert() or die("insert  error");	
          } elseif ($_POST["btnUpdate"]) {
              $dtmodel->Update() or die("update  error");	
          }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          // insert ke tabel klinik_history_pasien
          $dbSchema = "klinik";
          $dbTable = "klinik_history_pasien";

          $dbField[0] = "history_id";
          $dbField[1] = "id_reg";
          $dbField[2] = "history_status_pasien";
          $dbField[3] = "history_when_out";

          $history_id = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$history_id);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

          $dbKey[0] = 0;

          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,$dbSchema);

          $dtmodel->Insert() or die("insert error");

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          // end insert 
          
          //---- INSERT PETUGAS MEDIS ----///
  
          $dbTable = "global.global_petugas";
          
          $dbField[0] = "petugas_id";   // PK
          $dbField[1] = "id_app";
          $dbField[2] = "dep_nama";
          $dbField[3] = "perawat_1";
          $dbField[4] = "perawat_2";
          $dbField[5] = "perawat_3";
          $dbField[6] = "perawat_4";
          $dbField[7] = "perawat_5";
          $dbField[8] = "perawat_6";
          $dbField[9] = "perawat_7";
          $dbField[10] = "perawat_8";
          $dbField[11] = "perawat_9";
          $dbField[12] = "perawat_10";
          $dbField[13] = "dokter_1";
          
          for($i=0,$n=count($_POST["id_suster"][$i]);$i<$n;$i++) {
	       $dbValue[0] = QuoteValue(DPE_CHAR,'1');   // PK
	       $dbValue[1] = QuoteValue(DPE_NUMERIC,'1');
	       $dbValue[2] = QuoteValue(DPE_CHAR,'pemeriksaan');
	       $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][0]);
	       $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][1]);
	       $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][2]);
	       $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][3]);
	       $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][4]);
	       $dbValue[8] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][5]);
	       $dbValue[9] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][6]);
	       $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][7]);
	       $dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][8]);
	       $dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["id_suster"][9]);
	       $dbValue[13] = QuoteValue(DPE_NUMERIC,$_POST["id_dokter"]);
	       
	       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
     
	       $dtmodel->Update() or die("update  error");	
	       
	       unset($dtmodel);
	       unset($dbTable);
	       unset($dbValue);
	       unset($dbKey);
	  }   
	  unset($dbField);
          
          
          //---------INSERT KE REFRAKSI-----------//
          
        $sql = "select ref_id from klinik.klinik_refraksi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
        $rs = $dtaccess->Execute($sql);
        $dataRef = $dtaccess->Fetch($rs); 
          
          $dbTable = "klinik.klinik_refraksi";
          
          $dbField[0] = "ref_id";   // PK
          $dbField[1] = "id_visus_nonkoreksi_od";
          $dbField[2] = "ref_mata_od_koreksi_spheris";
          $dbField[3] = "ref_mata_od_koreksi_cylinder";
          $dbField[4] = "ref_mata_od_koreksi_sudut";
          $dbField[5] = "id_visus_koreksi_od";
          $dbField[6] = "id_visus_nonkoreksi_os";
          $dbField[7] = "ref_mata_os_koreksi_spheris";
          $dbField[8] = "ref_mata_os_koreksi_cylinder";
          $dbField[9] = "ref_mata_os_koreksi_sudut";
          $dbField[10] = "id_visus_koreksi_os";
          
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$dataRef["ref_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_visus_nonkoreksi_od"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_spheris"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_cylinder"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["ref_mata_od_koreksi_sudut"]);
          $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["id_visus_koreksi_od"]);
          $dbValue[6] = QuoteValue(DPE_CHARKEY,$_POST["id_visus_nonkoreksi_os"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_spheris"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_cylinder"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["ref_mata_os_koreksi_sudut"]);
          $dbValue[10] = QuoteValue(DPE_CHARKEY,$_POST["id_visus_koreksi_os"]);
          
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);


              $dtmodel->Update() or die("update  error");	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          //---------END INSERT REFRAKSI-----------//
          // -- ini insert ke tabel rawat icd
		$sql = "delete from klinik.klinik_perawatan_icd where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_icd";
          $dbField[0] = "rawat_icd_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_icd";
          $dbField[3] = "rawat_icd_urut";
          $dbField[4] = "rawat_icd_odos";
          
          for($i=0,$n=count($_POST["rawat_icd_od_kode"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_od_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OD");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_od_kode"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }


          for($i=0,$n=count($_POST["rawat_icd_os_kode"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_os_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OS");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_os_kode"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
	  unset($dbField);
	  //-- insert ke tabel rawat prosedur --//
	  $sql = "delete from klinik.klinik_perawatan_prosedur where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_prosedur";
          $dbField[0] = "rawat_prosedur_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_prosedur";
          $dbField[3] = "rawat_prosedur_urut";
          
          for($i=0,$n=count($_POST["rawat_prosedur_kode"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_prosedur_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_prosedur_kode"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
	  unset($dbField);
	  
	  //-- end insert tabel rawat prosedur --//

			
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
				unset($dbField);
				
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
				unset($dbField);
				
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
	  unset($dbField);
          
          // -- ini insert ke tabel rawat terapi
		$sql = "delete from klinik.klinik_perawatan_terapi where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_terapi";
          $dbField[0] = "rawat_item_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_item";
          $dbField[3] = "rawat_item_urut";
          $dbField[4] = "id_dosis";
          $dbField[5] = "terapi_jumlah_item";
          
          for($i=0,$n=count($_POST["id_item"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["id_item"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHARKEY,"33253f946bbd83a8e7733aa2be3952a4");
               $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["txtDosis_1"][$i]); 
                
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["id_item"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
	  unset($dbField);

       $sql = "delete from klinik.klinik_perawatan_tindakan where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $dtaccess->Execute($sql); 
	  
	  $dbTable = "klinik.klinik_perawatan_tindakan";
	  $dbField[0] = "rawat_tindakan_id";   // PK
	  $dbField[1] = "id_rawat";
	  $dbField[2] = "id_tindakan";
	  $dbField[3] = "rawat_tindakan_total";
	  $dbField[4] = "rawat_tindakan_bayar";
	  $dbField[5] = "rawat_tindakan_urut";
	  
	  for($i=0,$n=count($_POST["tindakan_id"]);$i<$n;$i++) {
	       $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
	       $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
	       $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["tindakan_id"][$i]);
	       $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["tindakan_total"][$i]));
	       $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["tindakan_total"][$i])); 
	       $dbValue[5] = QuoteValue(DPE_NUMERIC,$i);
		
	       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

	       if($_POST["tindakan_id"][$i]) $dtmodel->Insert() or die("insert  error");
	       
	       unset($dtmodel);
	       unset($dbValue);
	       unset($dbKey);
	  }
	  unset($dbField);
	  unset($dbTable);
          

          // --- insrt suster ---
          /*
               // --- cari data pemeriksaan hari ini ---
               $sql = "select rawat_id
                         from klinik.klinik_perawatan
                         where rawat_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"));
               
               if(!$edit) $sql .= " and rawat_id = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]); 
               $rs = $dtaccess->Execute($sql);
               $dataRawat = $dtaccess->FetchAll($rs);               
               
          $sqlDelete = "delete from klinik.klinik_perawatan_suster 
                         where id_rawat in ( ".$sql." )";
          */
          
          $sqlDelete = "delete from klinik.klinik_perawatan_suster where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]); 
          
          $dtaccess->Execute($sqlDelete);               
          
          //for($i=0,$n=count($dataRawat);$i<$n;$i++) {
	  foreach($_POST["id_suster"] as $key => $value){
	       if($value) {
		    $dbTable = "klinik_perawatan_suster";
	       
		    $dbField[0] = "rawat_suster_id";   // PK
		    $dbField[1] = "id_rawat";
		    $dbField[2] = "id_pgw";
			   
		    $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
		    //$dbValue[1] = QuoteValue(DPE_CHAR,$dataRawat[$i]["rawat_id"]);
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

          // -- insert ke tabel admininstrator pemeriksaan
        if($_POST["id_admin"])    {
               $sqlDelete = "delete from klinik.klinik_perawatan_admin where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
               $dtaccess->Execute($sqlDelete);
          
                $dbTable = "klinik_perawatan_admin";
                     
                $dbField[0] = "rawat_admin_id";   // PK
                $dbField[1] = "id_rawat";
                $dbField[2] = "id_pgw";
                       
                $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
                $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_admin"]);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                
                $dtmodel->Insert() or die("insert error"); 
                
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);
           }

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
          
		$sql = "update global.global_customer_user set cust_usr_alergi = ".QuoteValue(DPE_CHAR,$_POST["rawat_lab_alergi"])." where cust_usr_id = ".$_POST["id_cust_usr"];
          $dtaccess->Execute($sql);
          
          
          if($_POST["_x_mode"]=="New") {
               
               $sql = "update klinik.klinik_registrasi set reg_status = '".$_POST["cmbNext"]."', reg_tanggal = '".date('Y-m-d')."', reg_waktu = CURRENT_TIME where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql); 
               
               // --- nyimpen paket klaim e ---
               if($_POST["cmbNext"]==STATUS_SELESAI && $_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA) { 
               
                    $sql = "select b.paket_klaim_id, paket_klaim_total 
                              from klinik.klinik_biaya_pasien a
                              join klinik.klinik_paket_klaim b on a.id_paket_klaim = b.paket_klaim_id  
                              where a.biaya_pasien_status = ".QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN)."
                              and a.biaya_pasien_jenis = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]);
                    $rs = $dtaccess->Execute($sql);
               
               $dtaccess->Execute($sql);
               
                    
                    // --- delete dulu data yg lama ---
                    $sql = "delete from klinik.klinik_registrasi_klaim
                              where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                    $dtaccess->Execute($sql);                    
                    
                    while($row = $dtaccess->Fetch($rs)) {
                    
                         $dbTable = "klinik_registrasi_klaim";
               
                         $dbField1[0] = "reg_klaim_id";   // PK
                         $dbField1[1] = "id_reg";
                         $dbField1[2] = "id_paket_klaim";
                         $dbField1[3] = "reg_klaim_nominal";
                         $dbField1[4] = "reg_klaim_when";
                         $dbField1[5] = "reg_klaim_who";
                         $dbField1[6] = "reg_klaim_jenis";
                              
			 $klaimId =  $dtaccess->GetTransID();
			 $dbValue[0] = QuoteValue(DPE_CHAR,$klaimId);
			 $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
			 $dbValue[2] = QuoteValue(DPE_CHAR,$row["paket_klaim_id"]);
			 $dbValue[3] = QuoteValue(DPE_NUMERIC,$row["paket_klaim_total"]);
			 $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
			 $dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
			 $dbValue[6] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
                         
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField1,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField1);
                         unset($dbValue);
                         unset($dbKey);
                         
                         
                         // --- ngisi tbl split e ---
                         $sql = "select klaim_split_id, klaim_split_nominal 
                                   from klinik.klinik_paket_klaim_split 
                                   where klaim_split_nominal > 0 
                                   and id_paket_klaim = ".QuoteValue(DPE_CHAR,$row["paket_klaim_id"]);
                         $rsSplit = $dtaccess->Execute($sql);
                         
                         while($rowSplit = $dtaccess->Fetch($rsSplit)) {
                         
                              $dbTable = "klinik_registrasi_klaim_split";
               
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
     
                    
                    //---ngisi diagnostik ---
                   /* if($_POST["cmbNext"]==STATUS_DIAGNOSTIK) {
                     
                     $sql = "update klinik.klinik_folio set fol_lunas = 'n' where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_jenis = ".QuoteValue(DPE_CHAR,STATUS_DIAGNOSTIK);
							$dtaccess->Execute($sql);
							//echo $sql;	

						//	$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_DIAGNOSTIK."'  where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               		//$dtaccess->Execute($sql);
	}*/
               // -- insert ke folio jika data gula diisi ---
               $sql = "select diag_id from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataDiag = $dtaccess->Fetch($sql);
	       
	       
               if(!$dataDiag) {
		    
		    if($_POST["rawat_kesehatan"]) $sql_where[] = QuoteValue(DPE_CHAR,"71");
		    if($_POST["rawat_flouorecsin"]) $sql_where[] = QuoteValue(DPE_CHAR,"38");
		    if($_POST["rawat_epilasi"]) $sql_where[] = QuoteValue(DPE_CHAR,"44");
		    if($_POST["rawat_probing"]) $sql_where[] = QuoteValue(DPE_CHAR,"42");
		    if($_POST["rawat_schimer"]) $sql_where[] = QuoteValue(DPE_CHAR,"41");
		    if($_POST["rawat_nct_od"] || $_POST["rawat_nct_os"]) $sql_where[] = QuoteValue(DPE_CHAR,"55");
		    if($_POST["tindakan_id"]) {
			 foreach($_POST["tindakan_id"] as $value){
			      $sql_where[] = QuoteValue(DPE_CHAR,$value);
			 }
		    }
		    if($_POST["id_item"]) {
			 foreach($_POST["id_item"] as $value){
			      $sql_where[] = QuoteValue(DPE_CHAR,$value);
			 }
		    }

		    
		    $sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
				    and id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"])." and fol_jenis = '".STATUS_PEMERIKSAAN."' and id_biaya not in (".implode(",",$sql_where).")";
		    $dtaccess->Execute($sql);
		    unset ($sql_where);
		    unset ($dbField);
		    if($_POST["rawat_kesehatan"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_UJIMATA);
              if($_POST["rawat_anel"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_ANEL);
		    if($_POST["rawat_flouorecsin"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_FLUORECSIN);
		    if($_POST["rawat_schimer"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_SCHIMER);
		    if($_POST["rawat_nct_od"] || $_POST["rawat_nct_os"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_NCT);
              if($_POST["rawat_mata_od_retina"] || $_POST["rawat_mata_os_retina"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_FUNDUSCOPY);

		    if($sql_where) {
			 $sql = "select * from klinik.klinik_biaya a where ".implode(" or ",$sql_where);
			 $dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
			 //($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 
			 /* insert ke folio tagihan */
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
			 
			 $folWaktu = date("Y-m-d H:i:s");
			 $lunas = 'n';
	       
			 //
			 //$dbTable1 = "klinik_folio_split";
			 //	
			 //$dbField1[0] = "folsplit_id";   // PK
			 //$dbField1[1] = "id_fol";
			 //$dbField1[2] = "id_split";
			 //$dbField1[3] = "folsplit_nominal";
			 //
			 for($i=0,$n=count($dataBiaya);$i<$n;$i++) {
			      if($dataBiaya[$i]["biaya_id"]!=$dataBiaya[$i-1]["biaya_id"]){
				   //$sql = "select id_biaya from klinik.klinik_folio where id_biaya=".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"])." and id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_jenis=".QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
				   //$rs = $dtaccess->Execute($sql);
				   //$cekFolio = $dtaccess->Fetch($rs);
				   //if(!$cekFolio){
					$folId = $dtaccess->GetTransID();
					$dbValue[0] = QuoteValue(DPE_CHAR,$folId);
					$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
					$dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_nama"]);
					$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
					$dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
					$dbValue[5] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
					$dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
					$dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
					$dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
					$dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
					$dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
					
					$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
					$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
					
					$dtmodel->Insert() or die("insert error"); 
					
					unset($dtmodel);
					unset($dbValue);
					unset($dbKey);
				   //}
			      }
			      //
			      //$dbValue1[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
			      //$dbValue1[1] = QuoteValue(DPE_CHAR,$folId);
			      //$dbValue1[2] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["id_split"]);
			      //$dbValue1[3] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["bea_split_nominal"]);
			      // 
			      //$dtmodel = new DataModel($dbTable1,$dbField1,$dbValue1,$dbKey,DB_SCHEMA_KLINIK);
			      //
			      //$dtmodel->Insert() or die("insert error"); 
			      //
			      //unset($dtmodel);
			      //unset($dbValue1);
			      //unset($dbKey1); 
			 }
			 //
			 //unset($dbTable1);
			 //
			 //unset($dbField1);
		    }
		    unset($dbField);
	       }
		    
	       /*
		* insert data tagihan tindakan tambahan ke tabel folio 
		*/
	       if($_POST["tindakan_id"]){
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
		    
		    $folWaktu = date("Y-m-d H:i:s");
		    $lunas = 'n';
	  
		    for($k=0;$k<count($_POST["tindakan_id"]);$k++){
			 //$sql = "select id_biaya from klinik.klinik_folio where id_biaya=".QuoteValue(DPE_CHAR,$_POST["tindakan_id"][$k])." and id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_jenis=".QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
			 //$rs = $dtaccess->Execute($sql);
			 if($_POST["tindakan_id"][$k]){
			      $sql = "select * from klinik.klinik_biaya where biaya_id = ".QuoteValue(DPE_CHAR,$_POST["tindakan_id"][$k]); 
			      $dataBiaya = $dtaccess->Fetch($sql,DB_SCHEMA);  
			      
			      $folId = $dtaccess->GetTransID();
			      $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
			      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
			      $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_nama"]);
			      $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
			      $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_id"]);
			      $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
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
			      unset($dbValue);
			      unset($dbKey);
			 }
		    }
		    unset($dbField);
	       }
		    
	       
	       // -- end of insert tindakan tambahan to tabel folio -- //
	       
	       /*
		* insert data tagihan terapi ke tabel klinik folio
		*/
	       if($_POST["id_item"]){
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
		    
		    $folWaktu = date("Y-m-d H:i:s");
		    $lunas = 'n';
	  
		    for($i=0,$n=count($_POST["id_item"]);$i<$n;$i++) {
			 //$sql = "select id_biaya from klinik.klinik_folio where id_biaya=".QuoteValue(DPE_CHAR,$_POST["id_item"][$k])." and id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_jenis=".QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
			 //$rs = $dtaccess->Execute($sql);
			 //if(!($dtaccess->Fetch($rs))){
			 if($_POST["id_item"][$i]!=null){
			      $folId = $dtaccess->GetTransID();
			      $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
			      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
			      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_nama"][$i]);
			      $dbValue[3] = QuoteValue(DPE_NUMERIC,(StripCurrency($_POST["txtJumlah_1"][$i])*$_POST["txtDosis_1"][$i]));
			      $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_item"][$i]);
			      $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
			      $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
			      $dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
			      $dbValue[8] = QuoteValue(DPE_CHAR,'n');
			      $dbValue[9] = QuoteValue(DPE_NUMERIC,$_POST["txtDosis_1"][$i]);
			      $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah_1"][$i]));
							    
			      //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
			      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
			      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
			      
			      $dtmodel->Insert() or die("insert error"); 
			      
			      unset($dtmodel);
			      unset($dbValue);
			      unset($dbKey);
			 }
		    }
		    unset($dbField);
	       }
          }
          
          if ($_POST["_x_mode"] == "Edit") echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          else{
               if ($_POST["cmbNext"] == STATUS_SELESAI.STATUS_ANTRI) {
                    $sql_cekFolio = "select count(*) as belum_lunas from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n'";
                    $data_cekFolio = $dtaccess->Fetch($sql_cekFolio);
                    if($data_cekFolio["belum_lunas"] > 0) echo "<script> alert('Pasien memiliki tagihan belum terbayar. Silahkan diarahkan ke kasir untuk membayar tagihan.')</script>";
               }
               echo "<script>document.location.href='".$thisPage."';</script>";
          }
          exit();

     }
	
     foreach($rawatKeadaan as $key => $value) {
       unset($show);
       if($_POST["rawat_keadaan_umum"]==$key) $show="selected";
	     $optionsKeadaan[] = $view->RenderOption($key,$value,$show);
     }


     $sql = "select diag_id from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     $dataDiag = $dtaccess->Fetch($sql);
     
     $count=0;	
	$optionsNext[$count] = $view->RenderOption(STATUS_SELESAI.STATUS_ANTRI,"Tidak Perlu Tindakan",$show); $count++;
	if(!$dataDiag){ $optionsNext[$count] = $view->RenderOption(STATUS_DIAGNOSTIK_TIPE.STATUS_ANTRI,"Ke Ruang Diagnostik",$show); $count++; }
	$optionsNext[$count] = $view->RenderOption(STATUS_OPERASI_JADWAL.STATUS_ANTRI,"Penjadwalan Operasi",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_PREOP.STATUS_ANTRI,"Pre Operasi",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_BEDAH.STATUS_ANTRI,"Bedah Minor",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_RAWATINAP.STATUS_ANTRI,"Rawat Inap",$show); $count++;
     $optionsNext[$count] = $view->RenderOption(STATUS_LABORATORIUM.STATUS_ANTRI,"Laboratorium",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_APOTEK.STATUS_ANTRI,"Ke Apotik dan selesai",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_SELESAI.STATUS_PROSES,"Dirujuk Ke",$show); $count++;

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
     
     
     $sql = "select op_paket_id, op_paket_nama from klinik.klinik_operasi_paket order by op_paket_nama";
     $dataOperasiPaket= $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi paket
     $optOperasiPaket[0] = $view->RenderOption("","[Pilih Paket Operasi]",$show); 
     for($i=0,$n=count($dataOperasiPaket);$i<$n;$i++) {
          $show = ($_POST["rawat_operasi_paket"]==$dataOperasiPaket[$i]["op_paket_id"]) ? "selected":"";
          $optOperasiPaket[$i+1] = $view->RenderOption($dataOperasiPaket[$i]["op_paket_id"],$dataOperasiPaket[$i]["op_paket_nama"],$show); 
     }

     // --- nyari datanya anestesis---
     $sql = "select anes_jenis_id, anes_jenis_nama from klinik.klinik_anestesis_jenis order by anes_jenis_nama";
     $dataAnestesisJenis = $dtaccess->FetchAll($sql);

     $sql = "select anes_komp_id, anes_komp_nama from klinik.klinik_anestesis_komplikasi";
     $dataAnestesisKomplikasi = $dtaccess->FetchAll($sql);

     $sql = "select anes_pre_id, anes_pre_nama from klinik.klinik_anestesis_premedikasi";
     $dataAnestesisPremedikasi = $dtaccess->FetchAll($sql);

     $sql = "select item_id, item_nama from logistik.logistik_item where id_kat_item = ".QuoteValue(DPE_CHAR,KAT_OBAT_ANESTESIS);
     $dataAnestesisObat = $dtaccess->FetchAll($sql); //echo $sql;

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
     
     $optionsRujuk[0] = $view->RenderOption("--","[Pilih Rujukan]",null,null,"optrujukdefault");
     $sql_rujukan = 'select * from klinik.klinik_rujukan';
     $rs_rujukan = $dtaccess->Execute($sql_rujukan);
     $m=1;
     while($dataRujukan = $dtaccess->Fetch($rs_rujukan)){
	  $optionsRujuk[$m] = $view->RenderOption($dataRujukan["rujuk_id"],$dataRujukan["rujuk_nama"],null); $m++;
     }
     

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
						}else{
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

function ProsesPerawatan(id) {
	SetPerawatan(id,'type=r');
	timer();
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
                         'input', {type:'text', value:'', size:3, maxLength:25, name:'txtDosis_1[]', id:'txtDosis_1_'+akhir}
                    ],
              'td',  { align: 'center', style: 'color: black;' },   
                      [
                 'input', {type:'text', value:'', size:20, maxLength:100, name:'txtJumlah_1[]', id:'txtJumlah_1_'+akhir},[],
                      ],
               'td', { align: 'center', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel1['+akhir+']', id:'btnDel1_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel1_'+akhir+'').click( function() { Delete(akhir) } );
     $('#txtDosis_1_'+akhir+'').css("text-align","right");
     $('#txtJumlah_1_'+akhir+'').css("text-align","right");
     document.getElementById('item_nama_'+akhir).readOnly = true;
          
     document.getElementById('hid_tot').value = akhir;
     tb_init('a.thickbox');
}

function Delete(akhir){
     document.getElementById('hid_id_del').value += document.getElementById('id_item_'+akhir).value;
     
     $('#tr_terapi_'+akhir).remove();
}


function TambahTindakan(){
     var akhir = eval(document.getElementById('hid_tot_tindakan').value)+1;
     
     $('#tb_tindakan').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_tindakan_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:20, maxLength:100, name:'tindakan_nama[]', id:'tindakan_nama_'+akhir},[],
                         'a',{ href:'<?php echo $tindakanPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'tindakan_id[]', id:'tindakan_id_'+akhir+''}
                    ],
              'td',  { align: 'center', style: 'color: black;' },   
                      [
                 'input', {type:'text', value:'', size:20, maxLength:100, name:'tindakan_total[]', id:'tindakan_total_'+akhir},[],
                      ],
               'td', { align: 'center', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel2['+akhir+']', id:'btnDel2_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel2_'+akhir+'').click( function() { DeleteTindakan(akhir) } );
     $('#tindakan_total_'+akhir+'').css("text-align","right");
     document.getElementById('tindakan_nama_'+akhir).readOnly = true;
          
     document.getElementById('hid_tot_tindakan').value = akhir;
     tb_init('a.thickbox');
}

function DeleteTindakan(akhir){
     document.getElementById('hid_id_del_tindakan').value += document.getElementById('tindakan_id_'+akhir).value;
     
     $('#tr_tindakan_'+akhir).remove();
}

function SusterTambah(){
     var akhir = eval(document.getElementById('suster_tot').value)+1;
     
     $('#tb_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'rawat_suster_nama[]', id:'rawat_suster_nama_'+akhir},[],
                         'a',{ href:'<?php echo $susterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_suster[]', id:'id_suster_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { SusterDelete(akhir) } );
     document.getElementById('rawat_suster_nama_'+akhir).readOnly = true;
          
     document.getElementById('suster_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterDelete(akhir){
     document.getElementById('hid_suster_del').value += document.getElementById('id_suster_'+akhir).value;
     
     $('#tr_suster_'+akhir).remove();
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

//-------------autocomplete Dewi-------------//
	
var drz;
function lihat(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest").style.visibility = "hidden";
    }else{
        drz = buatajax();
        var url="cari.php";
        drz.onreadystatechange=stateChanged;
        var params = "q="+eval;
        drz.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz.setRequestHeader("Content-length", params.length);
        drz.setRequestHeader("Connection", "close");
        drz.send(params);
    }

}

function buatajax(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged(){

var data;
    if (drz.readyState==4 && drz.status==200){
        data=drz.responseText;
        if(data.length>0){
            document.getElementById("kotaksugest").innerHTML = data;
            document.getElementById("kotaksugest").style.visibility = "";
        }else{
            document.getElementById("kotaksugest").innerHTML = "";
            document.getElementById("kotaksugest").style.visibility = "hidden";
        }
    }
}

function isi(rawat_icd_od_nama_0,rawat_icd_od_id_0,rawat_icd_od_kode_0){

    document.getElementById("rawat_icd_od_nama_0").value = rawat_icd_od_nama_0;
    document.getElementById("rawat_icd_od_id_0").value = rawat_icd_od_id_0;
    document.getElementById("rawat_icd_od_kode_0").value = rawat_icd_od_kode_0;
    document.getElementById("kotaksugest").style.visibility = "hidden";
    document.getElementById("kotaksugest").innerHTML = "";
}


//------

	
var drz1;
function lihat1(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest1").style.visibility = "hidden";
    }else{
        drz1 = buatajax1();
        var url="cari1.php";
        drz1.onreadystatechange=stateChanged1;
        var params1 = "q="+eval;
        drz1.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz1.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz1.setRequestHeader("Content-length", params1.length);
        drz1.setRequestHeader("Connection", "close");
        drz1.send(params1);
    }

}

function buatajax1(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged1(){

var data1;
    if (drz1.readyState==4 && drz1.status==200){
        data1=drz1.responseText;
        if(data1.length>0){
            document.getElementById("kotaksugest1").innerHTML = data1;
            document.getElementById("kotaksugest1").style.visibility = "";
        }else{
            document.getElementById("kotaksugest1").innerHTML = "";
            document.getElementById("kotaksugest1").style.visibility = "hidden";
        }
    }
}

function isi1(rawat_icd_od_nama_1,rawat_icd_od_id_1,rawat_icd_od_kode_1){

    document.getElementById("rawat_icd_od_nama_1").value = rawat_icd_od_nama_1;
    document.getElementById("rawat_icd_od_id_1").value = rawat_icd_od_id_1;
    document.getElementById("rawat_icd_od_kode_1").value = rawat_icd_od_kode_1;
    document.getElementById("kotaksugest1").style.visibility = "hidden";
    document.getElementById("kotaksugest1").innerHTML = "";
}

//------

	
var drz2;
function lihat2(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest2").style.visibility = "hidden";
    }else{
        drz2 = buatajax2();
        var url="cari2.php";
        drz2.onreadystatechange=stateChanged2;
        var params2 = "q="+eval;
        drz2.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz2.setRequestHeader("Content-length", params2.length);
        drz2.setRequestHeader("Connection", "close");
        drz2.send(params2);
    }

}

function buatajax2(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged2(){

var data2;
    if (drz2.readyState==4 && drz2.status==200){
        data2=drz2.responseText;
        if(data2.length>0){
            document.getElementById("kotaksugest2").innerHTML = data2;
            document.getElementById("kotaksugest2").style.visibility = "";
        }else{
            document.getElementById("kotaksugest2").innerHTML = "";
            document.getElementById("kotaksugest2").style.visibility = "hidden";
        }
    }
}

function isi2(rawat_icd_os_nama_0,rawat_icd_os_id_0,rawat_icd_os_kode_0){

    document.getElementById("rawat_icd_os_nama_0").value = rawat_icd_os_nama_0;
    document.getElementById("rawat_icd_os_id_0").value = rawat_icd_os_id_0;
    document.getElementById("rawat_icd_os_kode_0").value = rawat_icd_os_kode_0;
    document.getElementById("kotaksugest2").style.visibility = "hidden";
    document.getElementById("kotaksugest2").innerHTML = "";
}



//------

	
var drz3;
function lihat3(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest3").style.visibility = "hidden";
    }else{
        drz3 = buatajax3();
        var url="cari3.php";
        drz3.onreadystatechange=stateChanged3;
        var params3 = "q="+eval;
        drz3.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz3.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz3.setRequestHeader("Content-length", params3.length);
        drz3.setRequestHeader("Connection", "close");
        drz3.send(params3);
    }

}

function buatajax3(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged3(){

var data3;
    if (drz3.readyState==4 && drz3.status==200){
        data3=drz3.responseText;
        if(data3.length>0){
            document.getElementById("kotaksugest3").innerHTML = data3;
            document.getElementById("kotaksugest3").style.visibility = "";
        }else{
            document.getElementById("kotaksugest3").innerHTML = "";
            document.getElementById("kotaksugest3").style.visibility = "hidden";
        }
    }
}

function isi3(rawat_icd_os_nama_1,rawat_icd_os_id_1,rawat_icd_os_kode_1){

    document.getElementById("rawat_icd_os_nama_1").value = rawat_icd_os_nama_1;
    document.getElementById("rawat_icd_os_id_1").value = rawat_icd_os_id_1;
    document.getElementById("rawat_icd_os_kode_1").value = rawat_icd_os_kode_1;
    document.getElementById("kotaksugest3").style.visibility = "hidden";
    document.getElementById("kotaksugest3").innerHTML = "";
}


//------

	
var drz4;
function lihat4(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest4").style.visibility = "hidden";
    }else{
        drz4 = buatajax4();
        var url="cari4.php";
        drz4.onreadystatechange=stateChanged4;
        var params4 = "q="+eval;
        drz4.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz4.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz4.setRequestHeader("Content-length", params4.length);
        drz4.setRequestHeader("Connection", "close");
        drz4.send(params4);
    }

}

function buatajax4(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged4(){

var data4;
    if (drz4.readyState==4 && drz4.status==200){
        data4=drz4.responseText;
        if(data4.length>0){
            document.getElementById("kotaksugest4").innerHTML = data4;
            document.getElementById("kotaksugest4").style.visibility = "";
        }else{
            document.getElementById("kotaksugest4").innerHTML = "";
            document.getElementById("kotaksugest4").style.visibility = "hidden";
        }
    }
}

function isi4(rawat_ina_od_nama_0,rawat_ina_od_id_0,rawat_ina_od_kode_0){

    document.getElementById("rawat_icd_od_nama_2").value = rawat_ina_od_nama_0;
    document.getElementById("rawat_icd_od_id_2").value = rawat_ina_od_id_0;
    document.getElementById("rawat_icd_od_kode_2").value = rawat_ina_od_kode_0;
    document.getElementById("kotaksugest4").style.visibility = "hidden";
    document.getElementById("kotaksugest4").innerHTML = "";
}


//------

	
var drz5;
function lihat5(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest5").style.visibility = "hidden";
    }else{
        drz5 = buatajax5();
        var url="cari5.php";
        drz5.onreadystatechange=stateChanged5;
        var params5 = "q="+eval;
        drz5.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz5.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz5.setRequestHeader("Content-length", params5.length);
        drz5.setRequestHeader("Connection", "close");
        drz5.send(params5);
    }

}

function buatajax5(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged5(){

var data5;
    if (drz5.readyState==4 && drz5.status==200){
        data5=drz5.responseText;
        if(data5.length>0){
            document.getElementById("kotaksugest5").innerHTML = data5;
            document.getElementById("kotaksugest5").style.visibility = "";
        }else{
            document.getElementById("kotaksugest5").innerHTML = "";
            document.getElementById("kotaksugest5").style.visibility = "hidden";
        }
    }
}

function isi5(rawat_ina_od_nama_1,rawat_ina_od_id_1,rawat_ina_od_kode_1){

    document.getElementById("rawat_icd_od_nama_3").value = rawat_ina_od_nama_1;
    document.getElementById("rawat_icd_od_id_3").value = rawat_ina_od_id_1;
    document.getElementById("rawat_icd_od_kode_3").value = rawat_ina_od_kode_1;
    document.getElementById("kotaksugest5").style.visibility = "hidden";
    document.getElementById("kotaksugest5").innerHTML = "";
}


//------

	
var drz6;
function lihat6(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest6").style.visibility = "hidden";
    }else{
        drz6 = buatajax6();
        var url="cari6.php";
        drz6.onreadystatechange=stateChanged6;
        var params6 = "q="+eval;
        drz6.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz6.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz6.setRequestHeader("Content-length", params6.length);
        drz6.setRequestHeader("Connection", "close");
        drz6.send(params6);
    }

}

function buatajax6(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged6(){

var data6;
    if (drz6.readyState==4 && drz6.status==200){
        data6=drz6.responseText;
        if(data6.length>0){
            document.getElementById("kotaksugest6").innerHTML = data6;
            document.getElementById("kotaksugest6").style.visibility = "";
        }else{
            document.getElementById("kotaksugest6").innerHTML = "";
            document.getElementById("kotaksugest6").style.visibility = "hidden";
        }
    }
}

function isi6(rawat_ina_os_nama_0,rawat_ina_os_id_0,rawat_ina_os_kode_0){

    document.getElementById("rawat_icd_os_nama_2").value = rawat_ina_os_nama_0;
    document.getElementById("rawat_icd_os_id_2").value = rawat_ina_os_id_0;
    document.getElementById("rawat_icd_os_kode_2").value = rawat_ina_os_kode_0;
    document.getElementById("kotaksugest6").style.visibility = "hidden";
    document.getElementById("kotaksugest6").innerHTML = "";
}


//------

	
var drz7;
function lihat7(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest7").style.visibility = "hidden";
    }else{
        drz7 = buatajax7();
        var url="cari7.php";
        drz7.onreadystatechange=stateChanged7;
        var params7 = "q="+eval;
        drz7.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz7.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz7.setRequestHeader("Content-length", params7.length);
        drz7.setRequestHeader("Connection", "close");
        drz7.send(params7);
    }

}

function buatajax7(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged7(){

var data7;
    if (drz7.readyState==4 && drz7.status==200){
        data7=drz7.responseText;
        if(data7.length>0){
            document.getElementById("kotaksugest7").innerHTML = data7;
	    document.getElementById("kotaksugest7").style.visibility = "";
        }else{
            document.getElementById("kotaksugest7").innerHTML = "";
            document.getElementById("kotaksugest7").style.visibility = "hidden";
        }
    }
}

function isi7(rawat_ina_os_nama_1,rawat_ina_os_id_1,rawat_ina_os_kode_1){

    document.getElementById("rawat_icd_os_nama_3").value = rawat_ina_os_nama_1;
    document.getElementById("rawat_icd_os_id_3").value = rawat_ina_os_id_1;
    document.getElementById("rawat_icd_os_kode_3").value = rawat_ina_os_kode_1;
    document.getElementById("kotaksugest7").style.visibility = "hidden";
    document.getElementById("kotaksugest7").innerHTML = "";
}

var drz8;
function lookProc(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest8").style.visibility = "hidden";
    }else{
        drz8 = buatajax8();
        var url="cari_prosedur0.php";
        drz8.onreadystatechange=stateChanged8;
        var params = "q="+eval;
        drz8.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz8.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz8.setRequestHeader("Content-length", params.length);
        drz8.setRequestHeader("Connection", "close");
        drz8.send(params);
    }

}

function buatajax8(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged8(){

var data8;
    if (drz8.readyState==4 && drz8.status==200){
        data8=drz8.responseText;
        if(data8.length>0){
            document.getElementById("kotaksugest8").innerHTML = data8;
            document.getElementById("kotaksugest8").style.visibility = "";
        }else{
            document.getElementById("kotaksugest8").innerHTML = "";
            document.getElementById("kotaksugest8").style.visibility = "hidden";
        }
    }
}

function isi8(nama,id,kode){
    document.getElementById("rawat_prosedur_nama_0").value = nama;
    document.getElementById("rawat_prosedur_id_0").value = id;
    document.getElementById("rawat_prosedur_kode_0").value = kode;
    document.getElementById("kotaksugest8").style.visibility = "hidden";
    document.getElementById("kotaksugest8").innerHTML = "";
}

var drz9;
function lookProc1(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest9").style.visibility = "hidden";
    }else{
        drz9 = buatajax9();
        var url="cari_prosedur1.php";
        drz9.onreadystatechange=stateChanged9;
        var params = "q="+eval;
        drz9.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz9.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz9.setRequestHeader("Content-length", params.length);
        drz9.setRequestHeader("Connection", "close");
        drz9.send(params);
    }

}

function buatajax9(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged9(){

var data9;
    if (drz9.readyState==4 && drz9.status==200){
        data9=drz9.responseText;
        if(data9.length>0){
            document.getElementById("kotaksugest9").innerHTML = data9;
            document.getElementById("kotaksugest9").style.visibility = "";
        }else{
            document.getElementById("kotaksugest9").innerHTML = "";
            document.getElementById("kotaksugest9").style.visibility = "hidden";
        }
    }
}

function isi9(nama,id,kode){

    document.getElementById("rawat_prosedur_nama_1").value = nama;
    document.getElementById("rawat_prosedur_id_1").value = id;
    document.getElementById("rawat_prosedur_kode_1").value = kode;
    document.getElementById("kotaksugest9").style.visibility = "hidden";
    document.getElementById("kotaksugest9").innerHTML = "";
}

var drz10;
function lookProc2(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest10").style.visibility = "hidden";
    }else{
        drz10 = buatajax10();
        var url="cari_prosedur2.php";
        drz10.onreadystatechange=stateChanged10;
        var params = "q="+eval;
        drz10.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz10.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz10.setRequestHeader("Content-length", params.length);
        drz10.setRequestHeader("Connection", "close");
        drz10.send(params);
    }

}

function buatajax10(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged10(){

var data10;
    if (drz10.readyState==4 && drz10.status==200){
        data10=drz10.responseText;
        if(data10.length>0){
            document.getElementById("kotaksugest10").innerHTML = data10;
            document.getElementById("kotaksugest10").style.visibility = "";
        }else{
            document.getElementById("kotaksugest10").innerHTML = "";
            document.getElementById("kotaksugest10").style.visibility = "hidden";
        }
    }
}

function isi10(nama,id,kode){
    document.getElementById("rawat_prosedur_nama_2").value = nama;
    document.getElementById("rawat_prosedur_id_2").value = id;
    document.getElementById("rawat_prosedur_kode_2").value = kode;
    document.getElementById("kotaksugest10").style.visibility = "hidden";
    document.getElementById("kotaksugest10").innerHTML = "";
}

var drz11;
function lookProc3(eval){

    if(eval.length==0){
        document.getElementById("kotaksugest11").style.visibility = "hidden";
    }else{
        drz11 = buatajax11();
        var url="cari_prosedur3.php";
        drz11.onreadystatechange=stateChanged11;
        var params = "q="+eval;
        drz11.open("POST",url,true);
        //beberapa http header harus kita set kalau menggunakan POST
        drz11.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        drz11.setRequestHeader("Content-length", params.length);
        drz11.setRequestHeader("Connection", "close");
        drz11.send(params);
    }

}

function buatajax11(){
    if (window.XMLHttpRequest){
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged11(){

var data11;
    if (drz11.readyState==4 && drz11.status==200){
        data11=drz11.responseText;
        if(data11.length>0){
            document.getElementById("kotaksugest11").innerHTML = data11;
            document.getElementById("kotaksugest11").style.visibility = "";
        }else{
            document.getElementById("kotaksugest11").innerHTML = "";
            document.getElementById("kotaksugest11").style.visibility = "hidden";
        }
    }
}

function isi11(nama,id,kode){
    document.getElementById("rawat_prosedur_nama_3").value = nama;
    document.getElementById("rawat_prosedur_id_3").value = id;
    document.getElementById("rawat_prosedur_kode_3").value = kode;
    document.getElementById("kotaksugest11").style.visibility = "hidden";
    document.getElementById("kotaksugest11").innerHTML = "";
}

	//-------------End autocomplete Dewi-------------//

function setTextField(id){
     if (id=="rawat_anel") {
	  document.getElementById("rawat_anel").style.display = "block";
	  document.getElementById("rawat_schimer").style.display = "none";
	  document.getElementById("rawat_irigasi").style.display = "none";
	  document.getElementById("rawat_epilasi").style.display = "none";
	  document.getElementById("rawat_probing").style.display = "none";
	  document.getElementById("rawat_flouorecsin").style.display = "none";
	  document.getElementById("rawat_kesehatan").style.display = "none";
	  document.getElementById("rawat_anel").focus();
     }else if (id=="rawat_schimer") {
	  document.getElementById("rawat_anel").style.display = "none";
	  document.getElementById("rawat_schimer").style.display = "block";
	  document.getElementById("rawat_irigasi").style.display = "none";
	  document.getElementById("rawat_epilasi").style.display = "none";
	  document.getElementById("rawat_probing").style.display = "none";
	  document.getElementById("rawat_flouorecsin").style.display = "none";
	  document.getElementById("rawat_kesehatan").style.display = "none";
	  document.getElementById("rawat_schimer").focus();
     }else if (id=="rawat_irigasi") {
	  document.getElementById("rawat_anel").style.display = "none";
	  document.getElementById("rawat_schimer").style.display = "none";
	  document.getElementById("rawat_irigasi").style.display = "block";
	  document.getElementById("rawat_epilasi").style.display = "none";
	  document.getElementById("rawat_probing").style.display = "none";
	  document.getElementById("rawat_flouorecsin").style.display = "none";
	  document.getElementById("rawat_kesehatan").style.display = "none";
	  document.getElementById("rawat_irigasi").focus();
     }else if (id=="rawat_epilasi") {
	  document.getElementById("rawat_anel").style.display = "none";
	  document.getElementById("rawat_schimer").style.display = "none";
	  document.getElementById("rawat_irigasi").style.display = "none";
	  document.getElementById("rawat_epilasi").style.display = "block";
	  document.getElementById("rawat_probing").style.display = "none";
	  document.getElementById("rawat_flouorecsin").style.display = "none";
	  document.getElementById("rawat_kesehatan").style.display = "none";
	  document.getElementById("rawat_epilasi").focus();
     }else if (id=="rawat_probing") {
	  document.getElementById("rawat_anel").style.display = "none";
	  document.getElementById("rawat_schimer").style.display = "none";
	  document.getElementById("rawat_irigasi").style.display = "none";
	  document.getElementById("rawat_epilasi").style.display = "none";
	  document.getElementById("rawat_probing").style.display = "block";
	  document.getElementById("rawat_flouorecsin").style.display = "none";
	  document.getElementById("rawat_kesehatan").style.display = "none";
	  document.getElementById("rawat_probing").focus();
     }else if (id=="rawat_flouorecsin") {
	  document.getElementById("rawat_anel").style.display = "none";
	  document.getElementById("rawat_schimer").style.display = "none";
	  document.getElementById("rawat_irigasi").style.display = "none";
	  document.getElementById("rawat_epilasi").style.display = "none";
	  document.getElementById("rawat_probing").style.display = "none";
	  document.getElementById("rawat_flouorecsin").style.display = "block";
	  document.getElementById("rawat_kesehatan").style.display = "none";
	  document.getElementById("rawat_flouorecsin").focus();
     }else if (id=="rawat_kesehatan") {
	  document.getElementById("rawat_anel").style.display = "none";
	  document.getElementById("rawat_schimer").style.display = "none";
	  document.getElementById("rawat_irigasi").style.display = "none";
	  document.getElementById("rawat_epilasi").style.display = "none";
	  document.getElementById("rawat_probing").style.display = "none";
	  document.getElementById("rawat_flouorecsin").style.display = "none";
	  document.getElementById("rawat_kesehatan").style.display = "block";
	  document.getElementById("rawat_kesehatan").focus();
     }
}


function setDisplay(id) {
     var disp = Array();
     
     disp['none'] = 'block';
     disp['block'] = 'none';
     
     document.getElementById(id).style.display = disp[document.getElementById(id).style.display];
}

function showTextBox(ckbox) {
     if (ckbox=="E1") {
	  document.getElementById('cmbRujuk').style.display = "inline-block";
	  document.getElementById('cmbRujuk').focus();
     }else{
	  document.getElementById('optrujukdefault').selected = "true";
	  document.getElementById('cmbRujuk').style.display = "none";
	  document.getElementById('rawat_rujukan').value = "";
	  document.getElementById('rawat_rujukan').style.display = "none";
     }
}

function showKtr(ckbox) {
     if (ckbox=="7" || ckbox=="8" || ckbox=="9" || ckbox=="10") {
	  document.getElementById('rawat_rujukan').style.display = "inline-block";
	  document.getElementById('rawat_rujukan').focus();
     }else{
	  document.getElementById('rawat_rujukan').value = "";
	  document.getElementById('rawat_rujukan').style.display = "none";
     }
}
</script>



<?php if(!$_GET["id"]) { ?>
<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Pemeriksaan</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php //echo GetPerawatan(STATUS_ANTRI); ?></div>
	</div>
	
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Pemeriksaan</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php // echo GetPerawatan(STATUS_PROSES); ?></div>
	</div>
</div>

<?php } ?>



<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Pemeriksaan</td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
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
          <!--<tr>
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
          </tr>-->
          <tr>
               <td align="left" class="tablecontent">Alergi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_alergi","rawat_lab_alergi","25","100",$_POST["rawat_lab_alergi"],"inputField", null,false);?></td>
          </tr>
          <!--<tr>
               <td align="left" class="tablecontent"><span style="color:red">Rekap Medik</span></td>
               <td align="left" class="tablecontent-odd">
                    <a onClick="BukaWindow('rekap_medik.php?id_reg=<?php echo $_POST["id_reg"];?>&id_cust_usr=<?php echo $_POST["id_cust_usr"];?>','Rekap Medik')" href="#"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Rekap Medik" alt="Rekap Medik"/></a>
               </td>
          </tr>-->
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
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>
          </tr>
     
               <tr>
               <td width="20%"  class="tablecontent" align="left">Perawat</td>
               <td align="left" class="tablecontent-odd" width="80%">
				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php if(!$_POST["rawat_suster_nama"]) { ?>
					<tr id="tr_suster_0">
						<td align="left" class="tablecontent-odd" width="70%">
							<?php echo $view->RenderTextBox("rawat_suster_nama[]","rawat_suster_nama_0","30","100",$_POST["rawat_suster_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
							<input type="hidden" id="id_suster_0" name="id_suster[]" value="<?php echo $_POST["id_suster"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="30%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
							<input name="suster_tot" id="suster_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $view->RenderTextBox("rawat_suster_nama[]","rawat_suster_nama_".$i,"30","100",$_POST["rawat_suster_nama"][$i],"inputField", "readonly",false);?>
								<?php //if($edit) {?>
									<a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
								<?php //} ?>
                                        <input type="hidden" id="id_suster_<?php echo $i;?>" name="id_suster[]" value="<?php echo $_POST["id_suster"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
								<?php //if($edit) {?>
									<?php if($i==0) { ?>
									<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
									<?php } else { ?>
									<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterDelete(<?php echo $i;?>);">
									<?php } ?>
								<?php //} ?>
                                        <input name="suster_tot" id="suster_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_suster_del","hid_suster_del",'');?>
				</table>
               </td>
          </tr> 
          <tr>
               <td width="20%"  class="tablecontent" align="left">Administrasi</td>
               <td align="left" class="tablecontent-odd" width="80%">
                    <table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_admin">
                         <tr id="tr_admin_0">
                              <td align="left" class="tablecontent-odd" width="40%">
                                   <?php echo $view->RenderTextBox("rawat_admin_nama","rawat_admin_nama","30","100",$_POST["rawat_admin_nama"],"inputField", "readonly",false);?>       
                                   <?php echo $view->RenderHidden("id_admin","id_admin",$_POST["id_admin"]);?>
                                   <a href="<?php echo $adminFindPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Admin"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Admin" alt="Cari Admin" /></a>
                              </td>
                         </tr>
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
     <legend><a href="#periksa" id="periksa" onClick="setDisplay('tbPeriksa');"><strong>Pemeriksaan Mata</strong></a></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbPeriksa" style="display:none">
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
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_od_vitreus","rawat_od_vitreus","30","30",$_POST["rawat_od_vitreus"],"inputField", null,false);?></td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_os_vitreus","rawat_os_vitreus","30","30",$_POST["rawat_os_vitreus"],"inputField", null,false);?></td>
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
               <td align="left" width="30%" class="tablecontent">NCT OD</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_nct_od","rawat_nct_od","5","5",$_POST["rawat_nct_od"],"inputField", null,false);?> mmHG
               </td>
          </tr>
          <tr>
               <td align="left" width="30%" class="tablecontent">NCT OS</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_nct_os","rawat_nct_os","5","5",$_POST["rawat_nct_os"],"inputField", null,false);?> mmHG
               </td>
          </tr>
          <!-- user request 28 Mar 14 -->
	    <!-- change aneltest, etc. text input into combobox -->
	    <tr>
               <td align="left" class="tablecontent">
		    <select name="rawat_test" id="rawat_test" class="inputField" onChange="setTextField(this.value);">
			   <option value="rawat_anel" <?php echo ($_POST["rawat_test"]=="rawat_anel")?"selected":"";?>>Anel Test</option>
			   <option value="rawat_schimer" <?php echo ($_POST["rawat_test"]=="rawat_schimer")?"selected":"";?>>Schimer Test</option>
			   <option value="rawat_irigasi" <?php echo ($_POST["rawat_test"]=="rawat_irigasi")?"selected":"";?>>Irigasi Bola Mata</option>
			   <option value="rawat_epilasi" <?php echo ($_POST["rawat_test"]=="rawat_epilasi")?"selected":"";?>>Epilasi</option>
			   <option value="rawat_probing" <?php echo ($_POST["rawat_test"]=="rawat_probing")?"selected":"";?>>Probing</option>
			   <option value="rawat_flouorecsin" <?php echo ($_POST["rawat_test"]=="rawat_flouorecsin")?"selected":"";?>>Flouorecsin Test</option>
			   <option value="rawat_kesehatan" <?php echo ($_POST["rawat_test"]=="rawat_kesehatan")?"selected":"";?>>Uji Kesehatan Mata</option>
		    </select>
		   </td>
               <td align="left" class="tablecontent-odd">
		    <span id="textField">
		    <?php echo $view->RenderTextBox("rawat_anel","rawat_anel","15","15",$_POST["rawat_anel"],"inputField", "style=\"display:block\"",false);?><?php echo $view->RenderTextBox("rawat_schimer","rawat_schimer","15","15",$_POST["rawat_schimer"],"inputField",  "style=\"display:none\"",false);?><?php echo $view->RenderTextBox("rawat_irigasi","rawat_irigasi","15","15",$_POST["rawat_irigasi"],"inputField",  "style=\"display:none\"",false);?><?php echo $view->RenderTextBox("rawat_epilasi","rawat_epilasi","15","15",$_POST["rawat_epilasi"],"inputField",  "style=\"display:none\"",false);?><?php echo $view->RenderTextBox("rawat_suntikan","rawat_suntikan","15","15",$_POST["rawat_suntikan"],"inputField",  "style=\"display:none\"",false);?><?php echo $view->RenderTextBox("rawat_probing","rawat_probing","15","15",$_POST["rawat_probing"],"inputField",  "style=\"display:none\"",false);?><?php echo $view->RenderTextBox("rawat_flouorecsin","rawat_flouorecsin","15","15",$_POST["rawat_flouorecsin"],"inputField",  "style=\"display:none\"",false);?><?php echo $view->RenderTextBox("rawat_kesehatan","rawat_kesehatan","15","15",$_POST["rawat_kesehatan"],"inputField",  "style=\"display:none\"",false);?></span></td>
          </tr>
	    <!-- end update -->
	    <!--<tr>
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
	    -->
<!--          <tr>
               <td align="left" class="tablecontent">Suntikan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_suntikan","rawat_suntikan","15","15",$_POST["rawat_suntikan"],"inputField", null,false);?></td>
          </tr>
     -->
          <!--<tr>
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
          </tr>-->
          <tr>
               <td align="left" class="tablecontent">Color Blindness</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_color_blindness","rawat_color_blindness","2","20",$_POST["rawat_color_blindness"],"inputField", null,false);?></td>
          </tr>
	</table>
     </fieldset>

     <!-- user request 14 Mar '14 -->
     <!-- moving eyes images upload to the right column-->
     <!-- moving catatan field to the right column-->
     
     <!-- user request 14 Mar '14 -->
     <!-- adding autocomplete on ICD code fields -->
     <!-- removing autocomplete on ICD name fields -->
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
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[0]","rawat_icd_od_kode_0","10","100",$_POST["rawat_icd_od_kode"][0],"inputField", "autocomplete=\"off\"",false,"onkeyup=\"lihat(this.value);\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[0]" id="rawat_icd_od_id_0" value="<?php echo $_POST["rawat_icd_od_id"][0]?>" />
			  <div id="kotaksugest" style="position:absolute; background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
               </td>
               <td align="left" class="tablecontent-odd"><div> 
              <input type="text" size= "50" name="rawat_icd_od_nama[0]" id="rawat_icd_od_nama_0" value="<?php echo $_POST["rawat_icd_od_nama"][0]?>" />
                </div>
                
         </td> 
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[1]","rawat_icd_od_kode_1","10","100",$_POST["rawat_icd_od_kode"][1],"inputField", "autocomplete=\"off\"",false," onkeyup=\"lihat1(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[1]" id="rawat_icd_od_id_1" value="<?php echo $_POST["rawat_icd_od_id"][1]?>" />                    
                <div id=kotaksugest1 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_icd_od_nama[1]" id="rawat_icd_od_nama_1" value="<?php echo $_POST["rawat_icd_od_nama"][1]?>" />
                </div>
         </td> 
          </tr>     
          <tr>
               <td align="left" class="tablecontent">3</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[2]","rawat_icd_od_kode_2","10","100",$_POST["rawat_icd_od_kode"][2],"inputField", "autocomplete=\"off\"",false," onkeyup=\"lihat4(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=2&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_od_id[2]" id="rawat_icd_od_id_2" value="<?php echo $_POST["rawat_icd_od_id"][2]?>" />                    
                <div id=kotaksugest4 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_icd_od_nama[2]" id="rawat_icd_od_nama_2" value="<?php echo $_POST["rawat_icd_od_nama"][2]?>" />
                </div>
         </td> 
          </tr>  
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
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[0]","rawat_icd_os_kode_0","10","100",$_POST["rawat_icd_os_kode"][0],"inputField", "autocomplete=\"off\"",false,"onkeyup=\"lihat2(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[0]" id="rawat_icd_os_id_0" value="<?php echo $_POST["rawat_icd_os_id"][0]?>" />                    
                <div id=kotaksugest2 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
		   <td align="left" class="tablecontent-odd"><div>      
		   <input type="text" size= "50" name="rawat_icd_os_nama[0]" id="rawat_icd_os_nama_0" value="<?php echo $_POST["rawat_icd_os_nama"][0]?>" />
               </div>
         </td> 
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[1]","rawat_icd_os_kode_1","10","100",$_POST["rawat_icd_os_kode"][1],"inputField", "autocomplete=\"off\"",false,"onkeyup=\"lihat3(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[1]" id="rawat_icd_os_id_1" value="<?php echo $_POST["rawat_icd_os_id"][1]?>" />                    
                <div id=kotaksugest3 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_icd_os_nama[1]" id="rawat_icd_os_nama_1" value="<?php echo $_POST["rawat_icd_os_nama"][1]?>" />
                </div>
         </td> 
          </tr>           
          <tr>
               <td align="left" class="tablecontent">3</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[2]","rawat_icd_os_kode_2","10","100",$_POST["rawat_icd_os_kode"][2],"inputField", "autocomplete=\"off\"",false," onkeyup=\"lihat6(this.value)\"");?>
                    <a href="<?php echo $icdPage;?>&tipe=od&el=2&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_icd_os_id[2]" id="rawat_icd_os_id_2" value="<?php echo $_POST["rawat_icd_os_id"][2]?>" />                    
                <div id="kotaksugest6" style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
                </div>
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_icd_os_nama[2]" id="rawat_icd_os_nama_2" value="<?php echo $_POST["rawat_icd_os_nama"][2]?>" />
                </div>
         </td> 
          </tr>    
	</table>
     </fieldset>
     <!-- user request 14 Mar '14 -->
     <!-- adding procedure field -->
     <fieldset>
	    <legend><strong>Prosedur</strong></legend>
	    <table width="100%" border="1" cellpadding="4" cellspacing="1">
		   <tr>
			  <td align="center" class="subheader" width="5%"></td>
			  <td align="center" class="subheader" width="25%">Kode</td>
			  <td align="center" class="subheader">Keterangan</td>
		   </tr>
		   <tr>
			  <td align="center" class="tablecontent" width="5%">1</td>
			  <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[0]","rawat_prosedur_kode_0","10","100",$_POST["rawat_prosedur_kode"][0],"inputField", "autocomplete=\"off\"",false,"onkeyup=\"lookProc(this.value);\"");?>
                    <a href="<?php echo $procPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_prosedur_id[0]" id="rawat_prosedur_id_0" value="<?php echo $_POST["rawat_prosedur_id"][0]?>" />                    
			  <div id=kotaksugest8 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
			  </td>
			  <td align="left" class="tablecontent-odd"><div>      
			  <input type="text" size= "50" name="rawat_prosedur_nama[0]" id="rawat_prosedur_nama_0" value="<?php echo $_POST["rawat_prosedur_nama"][0]?>" />
			  </div>
		   </tr>
		   <tr>
			  <td align="center" class="tablecontent" width="5%">2</td>
			  <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[1]","rawat_prosedur_kode_1","10","100",$_POST["rawat_prosedur_kode"][0],"inputField", "autocomplete=\"off\"",false,"onkeyup=\"lookProc1(this.value);\"");?>
                    <a href="<?php echo $procPage;?>&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_prosedur_id[1]" id="rawat_prosedur_id_1" value="<?php echo $_POST["rawat_prosedur_id"][1]?>" />                    
			  <div id=kotaksugest9 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
			  </td>
			  <td align="left" class="tablecontent-odd"><div>      
			  <input type="text" size= "50" name="rawat_prosedur_nama[1]" id="rawat_prosedur_nama_1" value="<?php echo $_POST["rawat_prosedur_nama"][1]?>" />
			  </div>
		   </tr>
		   <tr>
			  <td align="center" class="tablecontent" width="5%">3</td>
			  <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[2]","rawat_prosedur_kode_2","10","100",$_POST["rawat_prosedur_kode"][2],"inputField", "autocomplete=\"off\"",false,"onkeyup=\"lookProc2(this.value);\"");?>
                    <a href="<?php echo $procPage;?>&el=2&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_prosedur_id[2]" id="rawat_prosedur_id_2" value="<?php echo $_POST["rawat_prosedur_id"][2]?>" />                    
			  <div id=kotaksugest10 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
			  </td>
			  <td align="left" class="tablecontent-odd"><div>      
			  <input type="text" size= "50" name="rawat_prosedur_nama[2]" id="rawat_prosedur_nama_2" value="<?php echo $_POST["rawat_prosedur_nama"][2]?>" />
			  </div>
		   </tr>
		   <tr>
			  <td align="center" class="tablecontent" width="5%">4</td>
			  <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[3]","rawat_prosedur_kode_3","10","100",$_POST["rawat_prosedur_kode"][3],"inputField", "autocomplete=\"off\"",false,"onkeyup=\"lookProc3(this.value);\"");?>
                    <a href="<?php echo $procPage;?>&el=3&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="rawat_prosedur_id[3]" id="rawat_prosedur_id_3" value="<?php echo $_POST["rawat_prosedur_id"][3]?>" />                    
			  <div id=kotaksugest11 style="position:absolute;background-color:#eeeeee;width:120px;visibility:hidden;z-index:100">
			  </div>
			  </td>
			  <td align="left" class="tablecontent-odd"><div>      
			  <input type="text" size= "50" name="rawat_prosedur_nama[3]" id="rawat_prosedur_nama_3" value="<?php echo $_POST["rawat_prosedur_nama"][3]?>" />
			  </div>
		   </tr>
	    </table>
     </fieldset>
<!--
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
                    <?php echo $view->RenderTextBox("rawat_ina_od_kode[0]","rawat_ina_od_kode_0","10","100",$_POST["rawat_ina_od_kode"][0],"inputField", null,false);?>
                    <a href="<?php echo $inaPage;?>&tipe=od&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_od_id[0]" id="rawat_ina_od_id_0" value="<?php echo $_POST["rawat_ina_od_id"][0]?>" />                    
               </td>
               <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_ina_od_nama[0]" id="rawat_ina_od_nama_0" value="<?php echo $_POST["rawat_ina_od_nama"][0]?>" onkeyup="lihat4(this.value)" />
                </div>
                <div id=kotaksugest4 style="position:absolute;
                background-color:lightblue;width:420;visibility:hidden;z-index:100">
                </div>
         </td> 
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_ina_od_kode[1]","rawat_ina_od_kode_1","10","100",$_POST["rawat_ina_od_kode"][1],"inputField", null,false);?>
                    <a href="<?php echo $inaPage;?>&tipe=od&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_od_id[1]" id="rawat_ina_od_id_1" value="<?php echo $_POST["rawat_ina_od_id"][1]?>" />                    
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_ina_od_nama[1]" id="rawat_ina_od_nama_1" value="<?php echo $_POST["rawat_ina_od_nama"][1]?>" onkeyup="lihat5(this.value)" />
                </div>
                <div id=kotaksugest5 style="position:absolute;
                background-color:lightblue;width:420;visibility:hidden;z-index:100">
                </div>
         </td> 
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
                    <?php echo $view->RenderTextBox("rawat_ina_os_kode[0]","rawat_ina_os_kode_0","10","100",$_POST["rawat_ina_os_kode"][0],"inputField", null,false);?>
                    <a href="<?php echo $inaPage;?>&tipe=os&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_os_id[0]" id="rawat_ina_os_id_0" value="<?php echo $_POST["rawat_ina_os_id"][0]?>" />                    
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_ina_os_nama[0]" id="rawat_ina_os_nama_0" value="<?php echo $_POST["rawat_ina_os_nama"][0]?>" onkeyup="lihat6(this.value)" />
                </div>
                <div id=kotaksugest6 style="position:absolute;
                background-color:lightblue;width:420;visibility:hidden;z-index:100">
                </div>
         </td> 
          </tr>
          <tr>
               <td align="left" class="tablecontent">2</td>
               <td align="left" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("rawat_ina_os_kode[1]","rawat_ina_os_kode_1","10","100",$_POST["rawat_ina_os_kode"][1],"inputField", null,false);?>
                    <a href="<?php echo $inaPage;?>&tipe=os&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari INA"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari INA" alt="Cari INA" /></a>
                    <input type="hidden" name="rawat_ina_os_id[1]" id="rawat_ina_os_id_1" value="<?php echo $_POST["rawat_ina_os_id"][1]?>" />                    
               </td>
   <td align="left" class="tablecontent-odd"><div>      
              <input type="text" size= "50" name="rawat_ina_os_nama[1]" id="rawat_ina_os_nama_1" value="<?php echo $_POST["rawat_ina_os_nama"][1]?>" onkeyup="lihat7(this.value)" />
                </div>
                <div id=kotaksugest7 style="position:absolute;
                background-color:lightblue;width:420;visibility:hidden;z-index:100">
                </div>
         </td> 
          </tr>
          
	</table>
     </fieldset>
	-->

     <fieldset>
     <legend><strong>Terapi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
          <tr class="subheader">
               <td width="30%" align="center">Nama Obat</td>
               <td width="30%" align="center">Jumlah</td>
               <td width="30%" align="center">Nominal</td>
               <td width="10%" align="center">&nbsp;</td>
          </tr>	
          <?php if(!$_POST["item_nama"]) { ?>
               <tr id="tr_terapi_0">
                    <td align="left" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("item_nama[0]","item_nama_0","20","100",$_POST["item_nama"][0],"inputField", "readonly",false);?>
                         <a href="<?php echo $terapiPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" /></a>
                         <input type="hidden" name="id_item[]" id="id_item_0" value="<?php echo $_POST["id_item"]?>" />                    
                    </td>
                    <td align="center" class="tablecontent-odd"><!--<span id="sp_item_0">--><?php echo $view->RenderTextBox("txtDosis_1[0]","txtDosis_1_0","3","25",$_POST["txtDosis_1"][0],"curedit", "",true);?><!--</span>--></td>
                    <td align="center" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtJumlah_1[0]","txtJumlah_1_0","20","100",$_POST["txtJumlah_1"][0],"curedit", "",false);?>
                        </td>			
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
                         <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("txtDosis_1[$i]","txtDosis_1_".$i,"3","25",$_POST["txtDosis_1"][$i],"curedit", "",true);?><!--<span id="sp_item_<?php /*echo $i;*/?>"><?php /*echo GetDosis($_POST["id_fisik"][$i],$i,$_POST["id_dosis"][$i]);*/?></span>--></td>
                         <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtJumlah_1[]","txtJumlah_1_".$i,"20","100",$_POST["txtJumlah_1"][$i],"curedit", "",false);?>
                        </td>			
                         <td align="left" class="tablecontent-odd" width="30%">
                              <?php if($i==0) { ?>
                              <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="Tambah();">
                              <?php } else { ?>
                              <input class="button" name="btnDel1[<?php echo $i;?>]" id="btnDel1_<?php echo $i;?>" type="button" value="Hapus" onClick="Delete(<?php echo $i;?>);">
                              <?php } ?>
                              <input name="hid_tot" id="hid_tot" type="hidden" value="<?php echo $n;?>">
                         </td>
                    </tr>
               <?php } ?>
          <?php } ?>
                              <?php echo $view->RenderHidden("hid_id_del","hid_id_del",'');?>
     </table>
     </fieldset>
     
     <fieldset>
     <legend><strong>Tindakan Tambahan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_tindakan"> 
          <tr class="subheader">
               <td width="30%" align="center">Nama Tindakan</td>
               <td width="30%" align="center">Jumlah</td>
               <td width="10%" align="center">&nbsp;</td>
          </tr>	
          <?php if(!$_POST["tindakan_id"]) { ?>
               <tr id="tr_tindakan_0">
                    <td align="left" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("tindakan_nama[0]","tindakan_nama_0","20","100",$_POST["tindakan_nama"][0],"inputField", "readonly",false);?>
                         <a href="<?php echo $tindakanPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" /></a>
                         <input type="hidden" name="tindakan_id[]" id="tindakan_id_0" value="<?php echo $_POST["tindakan_id"][0]?>" />                    
                    </td>
                    <td align="center" width="70%" class="tablecontent-odd" >
                             <?php echo $view->RenderTextBox("tindakan_total[0]","tindakan_total_0","20","100",$_POST["tindakan_total"][0],"curedit", "",false);?>
                        </td>			
                    <td align="center" class="tablecontent-odd">
                         <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="TambahTindakan();">
                         <input name="hid_tot_tindakan" id="hid_tot_tindakan" type="hidden" value="0">
                    </td>                    
               </tr>
          <?php } else { ?>
               <?php for($i=0,$n=count($_POST["tindakan_id"]);$i<$n;$i++) { ?>
                    <tr id="tr_tindakan_<?php echo $i;?>">
                         <td align="left" class="tablecontent-odd" width="70%">
                              <?php echo $view->RenderTextBox("tindakan_nama[]","tindakan_nama_".$i,"30","100",$_POST["tindakan_nama"][$i],"inputField", "readonly",false);?>
                              <a href="<?php echo $tindakanPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Obat" alt="Cari Obat" /></a>
                              <input type="hidden" id="tindakan_id_<?php echo $i;?>" name="tindakan_id[]" value="<?php echo $_POST["tindakan_id"][$i];?>"/>
                         </td>
                         <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("tindakan_total[]","tindakan_total_".$i,"20","100",$_POST["tindakan_total"][$i],"curedit", "",false);?>
                        </td>			
                         <td align="left" class="tablecontent-odd" width="30%">
                              <?php if($i==0) { ?>
                              <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="TambahTindakan();">
                              <?php } else { ?>
                              <input class="button" name="btnDel2[<?php echo $i;?>]" id="btnDel2_<?php echo $i;?>" type="button" value="Hapus" onClick="DeleteTindakan(<?php echo $i;?>);">
                              <?php } ?>
                              <input name="hid_tot_tindakan" id="hid_tot_tindakan" type="hidden" value="<?php echo $n;?>">
                         </td>
                    </tr>
               <?php } ?>
          <?php } ?>
                              <?php echo $view->RenderHidden("hid_id_del_tindakan","hid_id_del_tindakan",'');?>
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
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_operasi_paket","rawat_operasi_paket",$optOperasiPaket,null,null,null);?></td>
          </tr>
	  <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_jenis","rawat_anestesis_jenis",$optAnestesisJenis,null,null,null);?></td>
          </tr>
          <!--<tr>
               <td align="left" class="tablecontent" width="35%">Paket Biaya</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_operasi_paket","rawat_operasi_paket",$optOperasiPaket,null,null,null);?></td>
          </tr>-->
	</table>
     </fieldset>


     <!--<fieldset>
     <legend><strong>Rencana Anestesis</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          

          <tr>
               <td align="left" class="tablecontent" width="35%">Jenis Obat Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_obat","rawat_anestesis_obat",$optAnestesisObat,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Dosis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderTextBox("rawat_anestesis_dosis","rawat_anestesis_dosis","50","200",$_POST["rawat_anestesis_dosis"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Komplikasi Anestesis</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_komp","rawat_anestesis_komp",$optAnestesisKomplikasi,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="35%">Premedikasi</td>
               <td align="left" class="tablecontent-odd" width="65%"><?php echo $view->RenderComboBox("rawat_anestesis_pre","rawat_anestesis_pre",$optAnestesisPremedikasi,null,null,null);?></td>
          </tr>
     

	</table>
     </fieldset>-->

<!--
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
-->
     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
               <?php if(!$_GET["id"]) { ?>
                    <td align="left" width="30%" class="tablecontent">Tahap Berikutnya</td>
                    <td align="left" width="50%"><?php echo $view->RenderComboBox("cmbNext","cmbNext",$optionsNext,null,null,"onchange=\"showTextBox(this.value);\"");?>&nbsp;<?php echo $view->RenderComboBox("cmbRujuk","cmbRujuk",$optionsRujuk,null,"style=\"display: none\"","onchange=\"showKtr(this.value);\"");?>&nbsp;<?php echo $view->RenderTextBox("rawat_rujukan","rawat_rujukan","15","10",$_POST["rawat_rujukan"],"inputField","style=\"display: none\"",false,null);?></td>
               <?php } ?>
			<td align="left"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>

     </td>
     
     <td width="40%" height="100%" valign="top">
	    <iframe style="width:100%;height:50%;" marginwidth="0" marginheight="0" id="ifrmDiag" name="ifrmDiag" src="<?php echo $diagLink;?>" scrolling="auto" align="center" frameborder="0"></iframe>
	    <br />
	    <fieldset>
	    <legend><strong>Upload Foto</strong></legend>
	    <table style="width:100%;height:25%;" border="1" cellpadding="4" cellspacing="1">
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
	    <table style="width:100%;height:*;" border="1" cellpadding="4" cellspacing="1"> 
		   <tr>
			  <td align="left" class="tablecontent" width="20%">Catatan</td>
			  <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("rawat_catatan","rawat_catatan","5","50",$_POST["rawat_catatan"],"inputField", null,false);?></td>
		   </tr>
	    </table>
	    </fieldset>


     </td>
</tr>	

</table>

<?php echo $view->SetFocus("rawat_keluhan");?>

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

</form>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
