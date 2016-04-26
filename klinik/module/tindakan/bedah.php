<?php
/*   Catatan 13 April 2016:
 *   Inputan petugas injeksi disimpan di tabel klinik.klinik_operasi_suster
 *   Inputan asisten perawan disimpan di tabel klinik.klinik_perawatan_operasi_suster_asisten
 */
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/tree.cls.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $statusInjeksi = false;


 	if(!$auth->IsAllowed("klinik",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("klinik",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "bedah.php";
     $icdPage = "op_icd_find.php?";
     $inaPage = "op_ina_find.php?";
     $dokterPage = "op_dokter_find.php?";
     $susterPage = "op_suster_find.php?";
     $asistenSusterPage = "op_asisten_suster_find.php?";
     $terapiPage = "obat_find.php?";
     $adminFindPage = "bedah_admin_find.php?";
     $procPage = "proc_find.php?";

     $backPage = "bedah_view.php?";

	$page[STATUS_OPERASI_JADWAL] = "operasi_jadwal.php";
	$page[STATUS_BEDAH] = "bedah.php";
	$page[STATUS_PREOP] = "preop.php";


     $tableRefraksi = new InoTable("table1","99%","center");

     $plx = new InoLiveX("GetTindakan,SetTindakan,GetTonometri,GetDosis");

     function GetTonometri($scale,$weight) {
          global $dtaccess;

          $sql = "select tono_pressure from klinik.klinik_tonometri where tono_scale = ".QuoteValue(DPE_NUMERIC,$scale)." and tono_weight = ".QuoteValue(DPE_NUMERIC,$weight);
          $dataTable = $dtaccess->Fetch($sql);
          return $dataTable["tono_pressure"];

     }

     function GetDosis($item,$akhir,$id=null) {
          global $dtaccess, $view;

		$sql = "select item_fisik from inventori.inv_item where item_id = ".QuoteValue(DPE_NUMERIC,$item);
		$dataItem = $dtaccess->Fetch($sql);

          $sql = "select a.dosis_id, a.dosis_nama, b.fisik_nama
                    from inventori.inv_dosis a
                    join inventori.inv_fisik b on b.fisik_id = a.id_fisik
                    where a.id_fisik = ".QuoteValue(DPE_NUMERIC,$dataItem["item_fisik"]);
          $dataTable = $dtaccess->FetchAll($sql);

          $optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show);
          for($i=0,$n=count($dataTable);$i<$n;$i++) {
               $show = ($id==$dataTable[$i]["dosis_id"]) ? "selected":"";
               $optDosis[$i+1] = $view->RenderOption($dataTable[$i]["dosis_id"],$dataTable[$i]["dosis_nama"]." ".$dataTable[$i]["fisik_nama"],$show);
          }

          return $view->RenderComboBox("id_dosis[]","id_dosis".$akhir,$optDosis,null,null,null);
     }

     function GetTindakan($status) {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT,$rawatStatus,$page;

          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.reg_status like '".STATUS_BEDAH.$status."' and a.reg_tipe_umur='D'
                    order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
			if($status==0) {
				$tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesPerawatan(\''.$dataTable[$i]["reg_id"].'\',\''.$dataTable[$i]["reg_status"]{0}.'\')"/>';
			} else {
				$tbContent[$i][$counter][TABLE_ISI] = "<a href=\"".$page[$dataTable[$i]["reg_status"]{0}]."?id_reg=".$dataTable[$i]["reg_id"]."&status=".$dataTable[$i]["reg_status"]{0}."\"><img hspace=\"2\" width=\"16\" height=\"16\" src=\"".$APLICATION_ROOT."images/b_select.png\" alt=\"Proses\" title=\"Proses\" border=\"0\"/></a>";
			}
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = $rawatStatus[$dataTable[$i]["reg_status"]{0}];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }

          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);

     }

     function SetTindakan($id,$status) {
		global $dtaccess;

		$sql = "update klinik.klinik_registrasi set reg_status = '".$status.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);

		return true;
	}

	if(!$_POST["btnSave"] && !$_POST["btnUpdate"]) {
          // --- buat cari suster yg tugas hari ini ---
          $sql = "select distinct pgw_nama, pgw_id
                    from klinik.klinik_operasi_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id
                    join klinik.klinik_perawatan_operasi c on c.op_id = a.id_op
                    where c.op_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"));
          $rs = $dtaccess->Execute($sql);
          //echo $sql;
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster_terapi"][$i]) $_POST["id_suster_terapi"][$i] = $row["pgw_id"];
               if(!$_POST["op_suster_terapi_nama"][$i]) $_POST["op_suster_terapi_nama"][$i] = $row["pgw_nama"];
               $i++;
          }


          // --- buat cari asisten suster yg tugas hari ini ---
          /*$sql = "select distinct pgw_nama, pgw_id
                    from klinik.klinik_operasi_suster_asisten a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id
                    join klinik.klinik_perawatan_operasi c on c.op_id = a.id_op
                    where c.op_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"));
          $rs = $dtaccess->Execute($sql);

          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_asisten_suster_terapi"][$i]) $_POST["id_asisten_suster_terapi"][$i] = $row["pgw_id"];
               if(!$_POST["op_asisten_suster_terapi_nama"][$i]) $_POST["op_asisten_suster_terapi_nama"][$i] = $row["pgw_nama"];
               $i++;
          }   */
     }

     if ($_GET["id"]) {
          // === buat ngedit ---
          if ($_POST["btnDelete"]) {
               $_x_mode = "Delete";
          } else {
               $_x_mode = "Edit";
               $_POST["op_id"] = $enc->Decode($_GET["id"]);
          }

          $sql = "select a.id_reg from klinik.klinik_perawatan_operasi a
				where op_id = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $row_edit = $dtaccess->Fetch($sql);

          $_GET["id_reg"] = $row_edit["id_reg"];
     }

     if($_GET["id_reg"]) {
	  $sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien,
	      ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan
	      from klinik.klinik_registrasi a
	      left join klinik.klinik_refraksi c on a.reg_id = c.id_reg
	      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
	      where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);

	  $_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
	  $_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
          $_POST["id_reg"] = $_GET["id_reg"];
          $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];

          $sql = "select b.visus_nama as ref_mata_od_nonkoreksi_visus, d.visus_nama as ref_mata_od_koreksi_visus,
                    ref_mata_od_koreksi_spheris,ref_mata_od_koreksi_cylinder,ref_mata_od_koreksi_sudut,
                    c.visus_nama as ref_mata_os_nonkoreksi_visus, e.visus_nama as ref_mata_os_koreksi_visus,
                    ref_mata_os_koreksi_spheris,ref_mata_os_koreksi_cylinder,ref_mata_os_koreksi_sudut
                    from klinik.klinik_refraksi a
                    left join klinik.klinik_visus b on a.id_visus_nonkoreksi_od = b.visus_id
                    left join klinik.klinik_visus c on a.id_visus_nonkoreksi_os = c.visus_id
                    left join klinik.klinik_visus d on a.id_visus_koreksi_od = d.visus_id
                    left join klinik.klinik_visus e on a.id_visus_koreksi_os = e.visus_id
                    where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataRefraksi = $dtaccess->Fetch($sql);

          $sql = "select * from klinik.klinik_perawatan_operasi where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataOperasi= $dtaccess->Fetch($sql);

          $view->CreatePost($dataOperasi);

	  $tmpJamMulai = explode(":", $_POST["op_jam_mulai"]);
	  $_POST["op_mulai_jam"] = $tmpJamMulai[0];
	  $_POST["op_mulai_menit"] = $tmpJamMulai[1];

	  $tmpJamSelesai = explode(":", $_POST["op_jam_selesai"]);
	  $_POST["op_selesai_jam"] = $tmpJamSelesai[0];
	  $_POST["op_selesai_menit"] = $tmpJamSelesai[1];

	  $sql = "select rawat_id, rawat_tonometri_scale_od, rawat_tonometri_weight_od, rawat_tonometri_pressure_od,
                    rawat_anel, rawat_schimer, rawat_operasi_paket
                    from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
	  $rs = $dtaccess->Execute($sql);
	  //echo $sql."<br />";
          $dataPemeriksaan = $dtaccess->Fetch($rs);
          $_POST["rawat_operasi_paket"] = $dataPemeriksaan["rawat_operasi_paket"];
	  //$_POST["op_paket_biaya"] = $dataPemeriksaan["rawat_operasi_paket"];
	  $_POST["rawat_id"] = $dataPemeriksaan["rawat_id"];
	  //echo $dataPemeriksaan["rawat_operasi_paket"]."<br />";
	  // echo $_POST["op_paket_biaya"]."<br />";

       $sql = "select a.*, b.item_nama from klinik.klinik_bedah_terapi a
          left join stocks.tb_item b on a.id_item = b.id
          where id_bedah = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
     $dataTerapi = $dtaccess->FetchAll($sql);
     for ($i=0; $i < count($dataTerapi); $i++) {     
          $_POST["item_nama_cok"][$i] = $dataTerapi[$i]["item_nama"];
          $_POST["id_item_cok"][$i] = $dataTerapi[$i]["id_item"];
          $_POST["txtDosis_cok_1"][$i] = $dataTerapi[$i]["terapi_jumlah"];
          $_POST["txtSatuan"][$i] = currency_format($dataTerapi[$i]["terapi_nominal_satuan"]);
          $_POST["txtJumlah_cok_1"][$i] = currency_format($dataTerapi[$i]["terapi_nominal_total"]);
     }
     $_POST["terapi_id"] = $dataTerapi["terapi_id"];

          $sql = "select b.icd_nomor, b.icd_nama
                    from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_nomor
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    and a.rawat_icd_odos = 'OD'
                    order by a.rawat_icd_urut";
          $dataDiagIcdOD = $dtaccess->FetchAll($sql);

          $sql = "select b.icd_nomor, b.icd_nama
                    from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_nomor
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    and a.rawat_icd_odos = 'OS'
                    order by a.rawat_icd_urut";
          $dataDiagIcdOS = $dtaccess->FetchAll($sql);

          $sql = "select b.prosedur_kode, b.prosedur_nama, b.prosedur_id
                    from klinik.klinik_perawatan_prosedur a join klinik.klinik_prosedur b on a.id_prosedur = b.prosedur_kode
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
                    order by a.rawat_prosedur_urut";
          $dataProsedur = $dtaccess->FetchAll($sql);
	  //echo $sql."<br />";
	  for($i=0;$i<count($dataProsedur);$i++){
	       $_POST["rawat_prosedur_kode"][$i] = $dataProsedur[$i]["prosedur_kode"];
	       $_POST["rawat_prosedur_nama"][$i] = $dataProsedur[$i]["prosedur_nama"];
	       $_POST["rawat_prosedur_id"][$i] = $dataProsedur[$i]["prosedur_id"];
	  }

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

          $sql = "select * from klinik_bedah_regulasi_glaucoma where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $row = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $dataGlaucoma = $dtaccess->Fetch($row);

          $_POST["bedah_regulasi_id"] = $dataGlaucoma["bedah_regulasi_id"];
          $_POST["reg_glaucoma_OD_awal"] = $dataGlaucoma["bedah_regulasi_awal_od"];
          $_POST["reg_glaucoma_OS_awal"] = $dataGlaucoma["bedah_regulasi_awal_os"];
          $_POST["reg_glaucoma_OD_regulasi"] = $dataGlaucoma["bedah_regulasi_proses_od"];
          $_POST["reg_glaucoma_OS_regulasi"] = $dataGlaucoma["bedah_regulasi_proses_os"];
          $_POST["reg_glaucoma_OD_TIO"] = $dataGlaucoma["bedah_regulasi_hasil_od"];
          $_POST["reg_glaucoma_OS_TIO"] = $dataGlaucoma["bedah_regulasi_hasil_os"];

          $sql = "select pgw_nama, pgw_id from klinik.klinik_operasi_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_op = ".QuoteValue(DPE_CHAR,$dataOperasi["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster_terapi"][$i] = $row["pgw_id"];
               $_POST["op_suster_terapi_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
          unset($rs);
          unset($row);

          $sql = "select pgw_nama, pgw_id from klinik.klinik_bedah_admin a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          
          
          $row=$dtaccess->Fetch($rs);
          $_POST["id_admin"] = $row["pgw_id"];
          $_POST["op_admin_nama"] = $row["pgw_nama"];
          
          unset($rs);
          unset($row);




          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_operasi_suster_asisten a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_op = ".QuoteValue(DPE_CHAR,$dataOperasi["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_asisten_suster_terapi"][$i] = $row["pgw_id"];
               $_POST["op_asisten_suster_terapi_nama"][$i] = $row["pgw_nama"];
               $i++;
          }

          $sql = "select * from klinik.klinik_perawatan_duranteop a
                    where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $dataDuranteop = $dtaccess->Fetch($rs);
          $_POST["id_durop_komp"] = $dataDuranteop["id_durop_komp"];


          $sql = "select * from klinik.klinik_perawatan_injeksi a
                    where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $rs = $dtaccess->Execute($sql);
          $dataDetailInjeksi = $dtaccess->FetchAll($rs);


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
	  $sql = "select b.ina_kode as op_ina_kode, b.ina_nama as op_ina_nama
			  from klinik.klinik_ina b
			  where b.ina_id = ".QuoteValue(DPE_CHAR,$dataOperasi["id_ina"]);
	  $dataIna = $dtaccess->Fetch($sql);
	  $view->CreatePost($dataIna);

	  $sql = "select biaya_id, biaya_nama from klinik.klinik_biaya where biaya_kode like 'OP%' order by biaya_nama";
	  $rs_paket = $dtaccess->Execute($sql);
	  $dataOperasiPaket= $dtaccess->FetchAll($rs_paket);
	  //echo $sql."<br />";
	  //echo $_POST["op_paket_biaya"];
	  // -- bikin combonya operasi paket
	  $optOperasiPaket[0] = $view->RenderOption("","[Pilih Paket Operasi]",$shownya);
	  for($i=0,$n=count($dataOperasiPaket);$i<$n;$i++) {
	       $shownya = ($_POST["op_paket_biaya"]==$dataOperasiPaket[$i]["biaya_id"])?"selected":"";
	       $optOperasiPaket[$i+1] = $view->RenderOption($dataOperasiPaket[$i]["biaya_id"],$dataOperasiPaket[$i]["biaya_nama"],$shownya);
	  }
     }


     if($_POST["btnSave"] || $_POST["btnUpdate"]) {

          if($_POST["btnSave"]) {
               $sql = "delete from klinik.klinik_perawatan_operasi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);
		}
	  unset($dbTable,$dbField,$dbValue,$dbKey);
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

          $dbTable = "klinik.klinik_bedah_regulasi_glaucoma";
          $dbField[0] = "bedah_regulasi_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "bedah_regulasi_awal_od";
          $dbField[3] = "bedah_regulasi_awal_os";
          $dbField[4] = "bedah_regulasi_proses_od";
          $dbField[5] = "bedah_regulasi_proses_os";
          $dbField[6] = "bedah_regulasi_hasil_od";
          $dbField[7] = "bedah_regulasi_hasil_os";

          if(!$_POST["bedah_regulasi_id"]) {
               $_POST["bedah_regulasi_id"] = $dtaccess->GetTransID();
               $_x_mode = "Save";
          }else{
               $_x_mode = "Update";
          }

          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["bedah_regulasi_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["reg_glaucoma_OD_awal"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["reg_glaucoma_OS_awal"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["reg_glaucoma_OD_regulasi"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["reg_glaucoma_OS_regulasi"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["reg_glaucoma_OD_TIO"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["reg_glaucoma_OS_TIO"]);


          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          if ($_x_mode == "Save") {
              $dtmodel->Insert() or die("insert  error");
          } elseif ($_x_mode == "Update") {
              $dtmodel->Update() or die("update  error");
          }

          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);


          // --- insert ke tabel perawatan_operasi_icd
          $sql = "delete from klinik.klinik_perawatan_operasi_icd where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);

          $dbTable = "klinik.klinik_perawatan_operasi_icd";

          $dbField[0] = "rwt_op_icd_id";
          $dbField[1] = "id_op";
          $dbField[2] = "id_icd";
          if($_POST["op_icd_kode"]){
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
	       unset($dbField);
          }
          // -- ini insert ke tabel durante OP
		$sql = "delete from klinik.klinik_perawatan_duranteop where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
		$dtaccess->Execute($sql);
       unset($dbField);
	  $dbTable = "klinik.klinik_perawatan_duranteop";
	  $dbField[0] = "id_op";
	  $dbField[1] = "id_durop_komp";

	  if($_POST["id_durop_komp"]){

	       $dbValue[0] = QuoteValue(DPE_CHARKEY,$_POST["op_id"]);
	       $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_durop_komp"]);

	       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	       $dbKey[1] = 1;

	       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

	       $dtmodel->Insert() or die("insert  error");

	       unset($dtmodel);
	       unset($dbValue);
	       unset($dbKey);
	  }
	  unset($dbField);



          // --- insert ke tabel perawatan injeksi ---
          $sql = "delete from klinik.klinik_perawatan_injeksi where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);


          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

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
               unset($dbField);

               if($statusInjeksi) {
                    // --- insert d folio n folio_split ---
                    for ($i=0; $i < count($_POST["id_injeksi"]); $i++) { 
                         if ($_POST["id_injeksi"][$i] == '1') {
                              $kodenya = BIAYA_IM;
                         } elseif ($_POST["id_injeksi"][$i] == '2') {
                              $kodenya = BIAYA_IV;
                         } elseif ($_POST["id_injeksi"][$i] == '4' || $_POST["id_injeksi"][$i] == '5') {
                              $kodenya = BIAYA_PRABULBER_SUBCONJUNCTIVA;
                         } elseif ($_POST["id_injeksi"][$i] == '3') {
                              $kodenya = BIAYA_SUBCUTAN;
                         } 
                         
                         $sql_injeksi_where[] = "upper(biaya_kode) = ".QuoteValue(DPE_CHAR,$kodenya);
                    }

                    $sql = "select biaya_id, biaya_nama, biaya_total, biaya_jenis
                              from klinik.klinik_biaya
                              where ".implode(" or ",$sql_injeksi_where);
                              // echo $sql;
                    $rs = $dtaccess->Execute($sql);
                    $dataBiaya = $dtaccess->FetchAll($rs);

                    $lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_KOMPLIMEN)?'n':'y';
unset($dbTable,$dbField,$dbValue,$dbKey);
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

                    for ($i=0; $i < count($dataBiaya); $i++) { 
                         $sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_biaya = ".QuoteValue(DPE_CHARKEY,$dataBiaya[$i]["biaya_id"]);
                         $dtaccess->Execute($sql);
                         
                         $folioId = $dtaccess->GetTransID();
                         $dbValue[0] = QuoteValue(DPE_CHARKEY,$folioId);
                         $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
                         $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_nama"]);
                         $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
                         $dbValue[4] = QuoteValue(DPE_CHARKEY,$dataBiaya[$i]["biaya_id"]);
                         $dbValue[5] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_jenis"]);
                         $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
                         $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
                         $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
                         $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                         $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);

                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value

                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                         $dtmodel->Insert() or die("insert  error");

                         unset($dtmodel);
                         unset($dbValue);
                         unset($dbKey);
                    }
                    unset($dbField);

/*
                    $sql = "select bea_split_nominal, id_split
                              from klinik.klinik_biaya_split
                              where id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_INJEKSI)."
                              and bea_split_nominal > 0";
                    $rs = $dtaccess->Execute($sql);
unset($dbTable,$dbField,$dbValue,$dbKey);
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
		    unset($dbField);*/
               }
          }

          // --- insrt suster ---
          $sql = "delete from klinik.klinik_operasi_suster where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);

          if($_POST["id_suster_terapi"]) {
               foreach($_POST["id_suster_terapi"] as $key => $value){
                    if($value) {
			 unset($dbTable,$dbField,$dbValue,$dbKey);
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



          // --- insrt asisten suster ---
          $sql = "delete from klinik.klinik_perawatan_operasi_suster_asisten where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
          $dtaccess->Execute($sql);

          if($_POST["id_asisten_suster_terapi"]) {
               foreach($_POST["id_asisten_suster_terapi"] as $key => $value){
                    if($value) {
                         $dbTable = "klinik_perawatan_operasi_suster_asisten";

                         $dbField[0] = "op_asisten_suster_id";   // PK
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

          if($_POST["id_admin"])    {
               $sqlDelete = "delete from klinik.klinik_bedah_admin where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
               $dtaccess->Execute($sqlDelete);
          
                $dbTable = "klinik_bedah_admin";
                     
                $dbField[0] = "bedah_admin_id";   // PK
                $dbField[1] = "id_op";
                $dbField[2] = "id_pgw";
                       
                $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["op_id"]);
                $dbValue[2] = QuoteValue(DPE_NUMERICKEY,$_POST["id_admin"]);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                
                $dtmodel->Insert() or die("insert error"); 
                
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);
           }

           if ($_POST["id_item_cok"]) {
               $sqlDelete = "delete from klinik.klinik_bedah_terapi where id_bedah = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
               $dtaccess->Execute($sqlDelete);
                          
                unset($dbTable,$dbField,$dbValue,$dbKey);
                $dbTable = "klinik_bedah_terapi";

                $dbField[0] = "terapi_id";   // PK
                $dbField[1] = "id_bedah";
                $dbField[2] = "id_reg";
                $dbField[3] = "id_item";
                $dbField[4] = "terapi_jumlah";
                $dbField[5] = "terapi_nominal_satuan";
                $dbField[6] = "terapi_nominal_total";

                for($i=0;$i<count($_POST["id_item_cok"]);$i++) {
                  $terapi_id = $dtaccess->GetTransID();
                  $dbValue[0] = QuoteValue(DPE_CHAR,$terapi_id);
                  $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["op_id"]);
                  $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                  $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_item_cok"][$i]);
                  $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["txtDosis_cok_1"][$i]);
                  $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtSatuan"][$i]));
                  $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah_cok_1"][$i]));
                  
                  //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);

                  $dtmodel->Insert() or die("insert error");

                  unset($dtmodel);
                  unset($dbValue);
                  unset($dbKey);
                }
                unset($dbField);
           }

          
          if($_POST["btnSave"]) {
               $sql = "update klinik.klinik_registrasi set reg_status = '".$_POST["cmbNext"].STATUS_ANTRI."', reg_waktu = CURRENT_TIME where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dtaccess->Execute($sql);

			$sql = "delete from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
					and fol_jenis = ".QuoteValue(DPE_CHAR,STATUS_BEDAH);
			$dtaccess->Execute($sql);
			$folWaktu = date("Y-m-d H:i:s");
			// --- nyimpen folio paket operasi ----
			 // if($_POST["op_paket_biaya"]) {

			 //      $sql = "select * from klinik.klinik_operasi_paket where op_paket_id = ".QuoteValue(DPE_CHAR,$_POST["op_paket_biaya"]);

			 //      $dataBiaya = $dtaccess->Fetch($sql,DB_SCHEMA);

			 //      //$lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n';
			 //      $lunas = 'n';
			 //      unset($dbTable,$dbField,$dbValue,$dbKey);

			 //      $dbTable = "klinik_folio";

			 //      $dbField[0] = "fol_id";   // PK
			 //      $dbField[1] = "id_reg";
			 //      $dbField[2] = "fol_nama";
			 //      $dbField[3] = "fol_nominal";
			 //      $dbField[4] = "fol_lunas";
			 //      $dbField[5] = "fol_jenis";
			 //      $dbField[6] = "id_cust_usr";
			 //      $dbField[7] = "fol_waktu";
			 //      $dbField[8] = "fol_jumlah";
			 //      $dbField[9] = "fol_nominal_satuan";

			 //      $folId = $dtaccess->GetTransID();
			 //      $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
			 //      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
			 //      $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["op_paket_nama"]);
			 //      $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["op_paket_total"]);
			 //      $dbValue[4] = QuoteValue(DPE_CHAR,$lunas);
			 //      $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_BEDAH);
			 //      $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
			 //      $dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
			 //      $dbValue[8] = QuoteValue(DPE_NUMERIC,'1');
			 //      $dbValue[9] = QuoteValue(DPE_NUMERIC,$dataBiaya["op_paket_total"]);

			 //      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
			 //      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);

			 //      $dtmodel->Insert() or die("insert error");

			 //      unset($dtmodel);
			 //      unset($dbValue);
			 //      unset($dbKey);
			 //      unset($dbField);

			 //      $sql = "select * from klinik.klinik_operasi_paket_split
				// 	      where id_op_paket = ".QuoteValue(DPE_CHAR,$dataBiaya["op_paket_id"])." and op_paket_split_nominal > 0";
			 //      $dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
			 //      for($a=0,$b=count($dataSplit);$a<$b;$a++) {
				//    unset($dbTable,$dbField,$dbValue,$dbKey);
				//    $dbTable = "klinik_folio_split";
				//    $dbField[0] = "folsplit_id";   // PK
				//    $dbField[1] = "id_fol";
				//    $dbField[2] = "id_split";
				//    $dbField[3] = "folsplit_nominal";
				//    $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
				//    $dbValue[1] = QuoteValue(DPE_CHAR,$folId);
				//    $dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$a]["id_split"]);
				//    $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["op_paket_split_nominal"]);
				//    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				//    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
				//    $dtmodel->Insert() or die("insert error");
				//    unset($dtmodel);
				//    unset($dbField);
				//    unset($dbValue);
				//    unset($dbKey);
			 //      }
			 // }

			 /*
			  *  simpen data tagihan obat tambahan
			  */
			 if($_POST["id_item_cok"]){
                     unset($dtmodel,$dbTable,$dbField,$dbValue,$dbKey);
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

			      for($i=0;$i<count($_POST["id_item_cok"]);$i++) {
				   $folId = $dtaccess->GetTransID();
				   $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
				   $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
				   $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_nama_cok"][$i]);
				   $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtJumlah_cok_1"][$i]));
				   $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_item_cok"][$i]);
				   $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_BEDAH);
				   $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
				   $dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
				   $dbValue[8] = QuoteValue(DPE_CHAR,'n');
				   $dbValue[9] = QuoteValue(DPE_NUMERIC,$_POST["txtDosis_cok_1"][$i]);
				   $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtSatuan"][$i]));

				   //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
				   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);

				   $dtmodel->Insert() or die("insert error");

				   unset($dtmodel);
				   unset($dbValue);
				   unset($dbKey);
			      }
			      unset($dbField);
			 }
			 // -- end of insert tagihan obat tambahan
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
			 unset($dbTable,$dbField,$dbValue,$dbKey);

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
unset($dbTable,$dbField,$dbValue,$dbKey);
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
unset($dbTable,$dbField,$dbValue,$dbKey);
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
			      unset($dbTable,$dbField,$dbValue,$dbKey);
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
          }

	  //-- insert ke tabel rawat prosedur --//
	  if($_POST["rawat_prosedur_kode"]){
	       $sql = "delete from klinik.klinik_perawatan_prosedur where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);

	       $dtaccess->Execute($sql);
	       unset($dbTable,$dbField,$dbValue,$dbKey);
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
	       unset($dbTable);
	  }
	  //-- end insert tabel rawat prosedur --//

          if($_POST["btnSave"]) echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          exit();
     }

     $sql = "select op_jenis_nama from klinik.klinik_operasi_jenis where op_jenis_id = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_operasi_jenis"]);
     $dataJenisTindakan= $dtaccess->Fetch($sql);

     $sql = "select anes_pre_id, anes_pre_nama from klinik.klinik_anestesis_premedikasi";
     $dataAnestesisPremedikasi = $dtaccess->FetchAll($sql);
/*
     $sql = "select op_paket_id, op_paket_nama from klinik.klinik_operasi_paket";
     $rs_paket = $dtaccess->Execute($sql);
     $dataOperasiPaket= $dtaccess->FetchAll($rs_paket);

     // -- bikin combonya operasi paket
     $optOperasiPaket[0] = $view->RenderOption("","[Pilih Paket Operasi]",$show);
     for($i=0,$n=count($dataOperasiPaket);$i<$n;$i++) {
          $show = ($_POST["op_paket_biaya"]==$dataOperasiPaket[$i]["op_paket_id"])?"selected":"";
          $optOperasiPaket[$i+1] = $view->RenderOption($dataOperasiPaket[$i]["op_paket_id"],$dataOperasiPaket[$i]["op_paket_nama"],$show);
     }
  */
     // --- nyari datanya operasi jenis + harganya---
     $sql = "select op_jenis_id, op_jenis_nama from klinik.klinik_operasi_jenis order by op_jenis_nama";
     $dataOperasiJenis = $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi Jenis
     $optOperasiJenis[0] = $view->RenderOption("","[Pilih Metode]",$show);
     for($i=0,$n=count($dataOperasiJenis);$i<$n;$i++) {
          $show = ($_POST["id_op_jenis"]==$dataOperasiJenis[$i]["op_jenis_id"]) ? "selected":"";
          $optOperasiJenis[$i+1] = $view->RenderOption($dataOperasiJenis[$i]["op_jenis_id"],$dataOperasiJenis[$i]["op_jenis_nama"],$show);
     }


     $optTerapiObat[0] = $view->RenderOption("","[Pilih Obat Terapi]",$show);
     for($i=0,$n=count($dataAnestesisObat);$i<$n;$i++) {
          $show = ($_POST["rawat_anestesis_obat"]==$dataAnestesisObat[$i]["item_id"]) ? "selected":"";
          $optTerapiObat[$i+1] = $view->RenderOption($dataAnestesisObat[$i]["item_id"],$dataAnestesisObat[$i]["item_nama"],$show);
     }

     // --- buat option nama obat ---
     $sql = "select item_id, item_nama
               from inventori.inv_item
               where id_kat_item = ".QuoteValue(DPE_CHAR,KAT_OBAT_INJEKSI)."
               order by item_nama";
     $dataObat = $dtaccess->FetchAll($sql);

     // --- buat option teknik injeksi ---
     $sql = "select * from klinik.klinik_injeksi order by injeksi_id";
     $dataInjeksi = $dtaccess->FetchAll($sql);

      //-- untuk combo box tahap berikutnya --//
      $count=0;
     $optionsNext[$count] = $view->RenderOption(STATUS_PEMERIKSAAN,$rawatStatus[STATUS_PEMERIKSAAN],$show); $count++;
     $optionsNext[$count] = $view->RenderOption(STATUS_PREOP,$rawatStatus[STATUS_PREOP],$show); $count++;
     $optionsNext[$count] = $view->RenderOption(STATUS_RAWATINAP,$rawatStatus[STATUS_RAWATINAP],$show); $count++;
     $optionsNext[$count] = $view->RenderOption(STATUS_LABORATORIUM,$rawatStatus[STATUS_LABORATORIUM],$show); $count++;
     $optionsNext[$count] = $view->RenderOption(STATUS_APOTEK,$rawatStatus[STATUS_APOTEK]." & ".$rawatStatus[STATUS_SELESAI],$show); $count++;

     // --- nyari datanya komplikasi durante ---
     $sql = "select durop_komp_id, durop_komp_nama from klinik.klinik_duranteop_komplikasi";
     $dataDurop = $dtaccess->FetchAll($sql);
	//-- user request 28 Mar 14 --//
	//-- combo box for durante OP --//
     $optDurop[] = $view->RenderOption("--","--",$show);
	for($i=0,$n=count($dataDurop);$i<$n;$i++){
	  /*if($_POST["id_durop_komp"]) 
	  elseif(!$_POST["id_durop_komp"] && $dataDurop[$i]["durop_komp_id"]==5) $show = "selected";*/
       $show = ($_POST["id_durop_komp"]==$dataDurop[$i]["durop_komp_id"])?"selected":"";
	  $optDurop[] = $view->RenderOption($dataDurop[$i]["durop_komp_id"],$dataDurop[$i]["durop_komp_nama"],$show);
	  unset($show);
	}


?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>

<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){
     clearInterval(mTimer);
     GetTindakan(0,'target=antri_kiri_isi');
     GetTindakan(1,'target=antri_kanan_isi');
     mTimer = setTimeout("timer()", 10000);
}

function ProsesPerawatan(id,status) {
	SetTindakan(id,status,'type=r');
	timer();
}

function hitungNominal(jml,el){
     var satuan = stripCurrency(document.getElementById('txtSatuan_'+el).value) * 1;
     jml = jml * 1;
     document.getElementById('txtJumlah_cok_1_'+el).value = formatCurrency(jml * satuan);
}

function CheckData(frm) {

     if(frm.reg_jenis_pasien.value==<?php echo PASIEN_BAYAR_JAMKESNAS_PUSAT?> && !frm.op_ina_kode.value){
          alert('INA DRG Harus di Pilih');
          frm.op_ina_kode.focus();

          return false;
     }

     if(document.getElementById('rawat_lab_alergi').value) { alert('Alergi Terisi'); }
     return true;
}

function ItemDosis(item,akhir){
     GetDosis(item,akhir,'target=div_dosis_'+akhir);
     return true;
}

function Tambah(){
     var akhir = eval(document.getElementById('hid_tot_terapi').value)+1;

     $('#tb_terapi_cok').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_terapi_cok_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'item_nama_cok['+akhir+']', id:'item_nama_cok_'+akhir},[],
                         'a',{ href:'<?php echo $terapiPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_item_cok['+akhir+']', id:'id_item_cok_'+akhir+''}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:20, maxLength:100, name:'txtSatuan['+akhir+']', id:'txtSatuan_'+akhir}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:3, maxLength:25, name:'txtDosis_cok_1['+akhir+']', id:'txtDosis_cok_1_'+akhir}
                    ],
              'td',  { align: 'left', style: 'color: black;' },
                      [
                 'input', {type:'text', value:'', size:20, maxLength:100, name:'txtJumlah_cok_1['+akhir+']', id:'txtJumlah_cok_1_'+akhir},[],
                      ],
               'td', { align: 'center', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel1_cok['+akhir+']', id:'btnDel1_cok_'+akhir}
                       ]
                ]
     );

     $('#btnDel1_cok_'+akhir+'').click( function() { ItemDelete(akhir) } );
     $('#txtDosis_cok_1_'+akhir+'').keyup( function() { hitungNominal(this.value, akhir) });
     $('#txtDosis_cok_1_'+akhir+'').css("text-align","right");
     $('#txtJumlah_cok_1_'+akhir+'').css("text-align","right");
     $('#txtSatuan_'+akhir+'').css("text-align","right");
     document.getElementById('item_nama_cok_'+akhir).readOnly = true;
     document.getElementById('txtSatuan_'+akhir).readOnly = true;
     document.getElementById('txtJumlah_cok_1_'+akhir).readOnly = true;

     document.getElementById('hid_tot_terapi').value = akhir;
     tb_init('a.thickbox');
}

function ItemDelete(akhir){
     document.getElementById('hid_id_del_terapi').value += document.getElementById('id_item_cok_'+akhir).value;

     $('#tr_terapi_'+akhir).remove();
}

//
//function IcdTambah(){
//     var akhir = eval(document.getElementById('icd_tot').value)+1;
//
//     $('#tb_icd').createAppend(
//          'tr', { class  : 'tablecontent-odd',id:'tr_icd_'+akhir+'' },
//                ['td', { align: 'left', style: 'color: black;' },
//                    [
//                         'input', {type:'text', value:'', size:10, maxLength:100, name:'op_icd_kode[]', id:'op_icd_kode_'+akhir},[],
//                         'a',{ href:'<?php echo $icdPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari ICD'},
//                         [
//                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
//                         ],
//                         'input', {type:'hidden', value:'', name:'id_icd[]', id:'id_icd_'+akhir+''}
//                    ],
//               'td', { align: 'left', style: 'color: black;' },
//                    [
//                         'input', {type:'text', value:'', size:50, maxLength:100, name:'op_icd_nama[]', id:'op_icd_nama_'+akhir},[],
//
//                    ],
//               'td', { align: 'left', style: 'color: black;' },
//                       [
//                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
//                       ]
//                ]
//     );
//
//     $('#btnDel_'+akhir+'').click( function() { IcdDelete(akhir) } );
//     document.getElementById('op_icd_kode_'+akhir).readOnly = true;
//     document.getElementById('op_icd_nama_'+akhir).readOnly = true;
//
//     document.getElementById('icd_tot').value = akhir;
//     tb_init('a.thickbox');
//}
//
//function IcdDelete(akhir){
//     $('#tr_icd_'+akhir).remove();
//}

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
                         'select', {name:'id_injeksi['+akhir+']', id:'id_injeksi_'+akhir+''} ,[],
                         'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir+''}
                    ]
                ]
     );

     $('#btnDel_'+akhir+'').click( function() { Delete(akhir) } );


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

function Delete(akhir){
     //document.getElementById('hid_suster_del').value += document.getElementById('id_suster_'+akhir).value;

     $('#tr_injeksi_'+akhir).remove();
}



function SusterTambah(){
     var akhir = eval(document.getElementById('suster_tot').value)+1;

     $('#tb_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:30, maxLength:100, name:'op_suster_terapi_nama[]', id:'op_suster_terapi_nama_'+akhir},[],
                         'a',{ href:'<?php echo $susterPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Suster'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'id_suster_terapi[]', id:'id_suster_terapi_'+akhir+''}
                    ],
               'td', { align: 'left', style: 'color: black;' },
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]
                ]
     );

     $('#btnDel_'+akhir+'').click( function() { SusterDelete(akhir) } );
     document.getElementById('op_suster_terapi_nama_'+akhir).readOnly = true;

     document.getElementById('suster_tot').value = akhir;
     tb_init('a.thickbox');
}

function SusterDelete(akhir){
     document.getElementById('hid_suster_terapi_del').value += document.getElementById('id_suster_terapi_'+akhir).value;

     $('#tr_suster_'+akhir).remove();
}

function AsistenSusterTambah(){
     var akhir = eval(document.getElementById('asisten_suster_tot').value)+1;

     $('#tb_asisten_suster').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_asisten_suster_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:40, maxLength:100, name:'op_asisten_suster_terapi_nama[]', id:'op_asisten_suster_terapi_nama_'+akhir},[],
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
//
//function TambahObat(){
//     var akhir = eval(document.getElementById('hid_tot').value)+1;
//
//     $('#tb_terapi').createAppend(
//          'tr', { class  : 'tablecontent-odd',id:'tr_terapi_'+akhir+'' },
//                ['td', { align: 'left', style: 'color: black;' },
//                    [
//                         'input', {type:'text', value:'', size:20, maxLength:100, name:'item_nama[]', id:'item_nama_'+akhir},[],
//                         'a',{ href:'<?php echo $terapiPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Obat'},
//                         [
//                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
//                         ],
//                         'input', {type:'hidden', value:'', name:'id_item[]', id:'id_item_'+akhir+''}
//                    ],
//               'td', { align: 'center', style: 'color: black;' },
//                    [
//                         'span', {id:'sp_item_'+akhir+''}
//                    ],
//              'td',  { align: 'center', style: 'color: black;' },
//                      [
//                 'input', {type:'text', value:'', size:20, maxLength:100, name:'txtJumlah_1[]', id:'txtJumlah_1_'+akhir},[],
//                      ],
//               'td', { align: 'center', style: 'color: black;' },
//                       [
//                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel1['+akhir+']', id:'btnDel1_'+akhir}
//                       ]
//                ]
//     );
//
//     $('#btnDel1_'+akhir+'').click( function() { DeleteObat(akhir) } );
//     document.getElementById('item_nama_'+akhir).readOnly = true;
//
//     document.getElementById('hid_tot').value = akhir;
//     tb_init('a.thickbox');
//}
//
//function DeleteObat(akhir){
//     document.getElementById('hid_id_del').value += document.getElementById('id_item_'+akhir).value;
//
//     $('#tr_terapi_'+akhir).remove();
//}

//-- auto-complete jenis tindakan --//
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

function isi(nama,id,kode){
    document.getElementById("op_jenis_nama").value =nama;
    document.getElementById("id_op_jenis").value = id
    document.getElementById("op_jenis_kode").value = kod;
    document.getElementById("kotaksugest").style.visibiliy = "hidden";
    document.getElementById("kotaksugest").innerHTML = "";
}
//-- end auto-complete jenis tindakan --//

timer();

function setDisplay(id) {
     if(id=="5"){
	  document.getElementById("durop_kom_ket").style.display = "inline-block";
     }else{
	  document.getElementById("durop_kom_ket").style.display = "none";
     }
}

// -- auto complete -- //
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
// -- end of auto complete -- //

</script>


<?php if(!$_GET["id"]) { ?>

<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Tindakan</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetTindakan(STATUS_ANTRI); ?></div>
	</div>

	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Tindakan</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetTindakan(STATUS_PROSES); ?></div>
	</div>
</div>

<?php } ?>



<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data <?php echo $rawatStatus[$_POST["status"]]; ?></td>
	</tr>
</table>


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
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
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_icd_od_nomor"][0]." ".$_POST["rawat_icd_od_nama"][0];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_icd_od_nomor"][1]." ".$_POST["rawat_icd_od_nama"][1];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_icd_od_nomor"][2]." ".$_POST["rawat_icd_od_nama"][2];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - ICD - OS</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_icd_os_nomor"][0]." ".$_POST["rawat_icd_os_nama"][0];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_icd_os_nomor"][1]." ".$_POST["rawat_icd_os_nama"][1];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_icd_os_nomor"][2]." ".$_POST["rawat_icd_os_nama"][2];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Prosedur</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%" align="center">1</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_prosedur_kode"][0]." ".$_POST["rawat_prosedur_nama"][0];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%" align="center">2</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_prosedur_kode"][1]." ".$_POST["rawat_prosedur_nama"][1];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%" align="center">3</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_prosedur_kode"][2]." ".$_POST["rawat_prosedur_nama"][2];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%" align="center">4</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_prosedur_kode"][3]." ".$_POST["rawat_prosedur_nama"][3];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Regulasi Glaucoma</strong></legend>
     <table width="80%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="5%">&nbsp;</td>
               <td align="center" class="tablecontent" width="30%">Awal</td>
               <td align="center" class="tablecontent" width="30%">Regulasi</td>
               <td align="center" class="tablecontent" width="30%">Hasil TIO</td>
          </tr>
          <tr>
               <td align="center" class="tablecontent" width="5%">OD</td>
               <td align="center" class="tablecontent-odd" width="30%"><?php echo $view->RenderTextBox("reg_glaucoma_OD_awal","reg_glaucoma_OD_awal","30","100",$_POST["reg_glaucoma_OD_awal"],"inputField",null,false); ?></td>
               <td align="center" class="tablecontent-odd" width="30%"><?php echo $view->RenderTextBox("reg_glaucoma_OD_regulasi","reg_glaucoma_OD_regulasi","30","100",$_POST["reg_glaucoma_OD_regulasi"],"inputField",null,false); ?></td>
               <td align="center" class="tablecontent-odd" width="30%"><?php echo $view->RenderTextBox("reg_glaucoma_OD_TIO","reg_glaucoma_OD_TIO","30","100",$_POST["reg_glaucoma_OD_TIO"],"inputField",null,false); ?></td>
          </tr>
         <tr>
              <td align="center" class="tablecontent" width="5%">OS</td>
               <td align="center" class="tablecontent-odd" width="30%"><?php echo $view->RenderTextBox("reg_glaucoma_OS_awal","reg_glaucoma_OS_awal","30","100",$_POST["reg_glaucoma_OS_awal"],"inputField",null,false); ?></td>
               <td align="center" class="tablecontent-odd" width="30%"><?php echo $view->RenderTextBox("reg_glaucoma_OS_regulasi","reg_glaucoma_OS_regulasi","30","100",$_POST["reg_glaucoma_OS_regulasi"],"inputField",null,false); ?></td>
               <td align="center" class="tablecontent-odd" width="30%"><?php echo $view->RenderTextBox("reg_glaucoma_OS_TIO","reg_glaucoma_OS_TIO","30","100",$_POST["reg_glaucoma_OS_TIO"],"inputField",null,false); ?></td>
          </tr>
     </table>
     <?php echo $view->RenderHidden("bedah_regulasi_id","bedah_regulasi_id",($_POST["bedah_regulasi_id"])?$_POST["bedah_regulasi_id"]:"");?>
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
                                   echo $view->RenderComboBox("id_injeksi[$i]","id_injeksi_0",$optInjeksi);
                              ?>
                         </td>
                         <td align="center" width="15%">
                              <?php if($i==0) { ?>
                                   <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="InjeksiTambah();">
                              <?php } else { ?>
                                   <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="Delete(<?php echo $i;?>);">
                              <?php } ?>
                              <input name="hid_tot_injeksi" id="hid_tot_injeksi" type="hidden" value="<?php echo $n;?>">
                         </td>
                    </tr>
               <?php } ?>
          <?php }?>
     </table>
     <br />
     <table width="100%" border="1" cellpadding="2" cellspacing="1">
          <tr>
               <td width="30%"  class="tablecontent" align="left">Petugas Injeksi</td>
               <td align="left" class="tablecontent-odd" width="80%" colspan=6>
				<table width="100%" border="1" cellpadding="1" cellspacing="1" id="tb_suster">
                         <?php if(!$_POST["op_suster_terapi_nama"]) { ?>
					<tr id="tr_suster_0">
						<td align="left" class="tablecontent-odd" width="40%">
							<?php echo $view->RenderTextBox("op_suster_terapi_nama[]","op_suster_terapi_nama_0","30","100",$_POST["op_suster_terapi_nama"][0],"inputField", "readonly",false);?>
							<a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
							<input type="hidden" id="id_suster_terapi_0" name="id_suster_terapi[]" value="<?php echo $_POST["id_suster_terapi"];?>"/>
			               </td>
						<td align="left" class="tablecontent-odd" width="60%">
							<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
							<input name="suster_tot" id="suster_tot" type="hidden" value="0">
			               </td>
					</tr>
                    <?php } else  { ?>
                         <?php for($i=0,$n=count($_POST["id_suster_terapi"]);$i<$n;$i++) { ?>
                              <tr id="tr_suster_<?php echo $i;?>">
                                   <td align="left" class="tablecontent-odd" width="70%">
                                        <?php echo $view->RenderTextBox("op_suster_terapi_nama[]","op_suster_terapi_nama_".$i,"30","100",$_POST["op_suster_terapi_nama"][$i],"inputField", "readonly",false);?>
								<a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                                        <input type="hidden" id="id_suster_terapi_<?php echo $i;?>" name="id_suster_terapi[]" value="<?php echo $_POST["id_suster_terapi"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
								<?php if($i==0) { ?>
								<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
								<?php } else { ?>
								<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterDelete(<?php echo $i;?>);">
									<?php } ?>

                                        <input name="suster_tot" id="suster_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_suster_terapi_del","hid_suster_terapi_del",'');?>
				</table>
	       </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Rencana Tindakan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd" width="80%"><?php echo $view->RenderComboBox("op_paket_biaya","op_paket_biaya",$optOperasiPaket,null,null,null);?><!--<?php //echo $view->RenderComboBox("rawat_operasi_jenis","rawat_operasi_jenis",$optOperasiJenis,null,null,null);?>--></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Data Laporan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="15%" class="tablecontent">Dokter</td>
               <td align="left" class="tablecontent-odd" width="25%">
                    <?php echo $view->RenderTextBox("op_dokter_nama","op_dokter_nama","20","100",$_POST["op_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>
               <td width="10%" class="tablecontent" align="left">Asisten Perawat</td>
               <td align="left" class="tablecontent-odd" width="40%" colspan=6>
				<table width="100%" border="1" cellpadding="1" cellspacing="1" id="tb_asisten_suster">
                         <?php if(!$_POST["op_asisten_suster_terapi_nama"]) { ?>
					<tr id="tr_asisten_suster_0">
						<td align="left" class="tablecontent-odd" width="75%">
							<?php echo $view->RenderTextBox("op_asisten_suster_terapi_nama[]","op_asisten_suster_terapi_nama_0","40","100",$_POST["op_asisten_suster_terapi_nama"][0],"inputField", "readonly",false);?>
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
                                   <td align="left" class="tablecontent-odd" width="75%">
                                        <?php echo $view->RenderTextBox("op_asisten_suster_terapi_nama[]","op_asisten_suster_terapi_nama_".$i,"40","100",$_POST["op_asisten_suster_terapi_nama"][$i],"inputField", "readonly",false);?>
								
								<a href="<?php echo $asistenSusterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Asisten Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Asisten Suster" alt="Cari Asisten Suster" /></a>
                                        <input type="hidden" id="id_asisten_suster_terapi_<?php echo $i;?>" name="id_asisten_suster_terapi[]" value="<?php echo $_POST["id_asisten_suster_terapi"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="25%">
								<?php if($i==0) { ?>
								<input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="AsistenSusterTambah();">
								<?php } else { ?>
								<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="AsistenSusterDelete(<?php echo $i;?>);">
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
               <td width="15%"  class="tablecontent" align="left">Administrasi</td>
               <td align="left" class="tablecontent-odd" width="85%"  colspan=3>
                    <table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_admin">
                         <tr id="tr_admin_0">
                              <td align="left" class="tablecontent-odd" width="40%">
                                   <?php echo $view->RenderTextBox("op_admin_nama","op_admin_nama","30","100",$_POST["op_admin_nama"],"inputField", "readonly",false);?>       
                                   <?php echo $view->RenderHidden("id_admin","id_admin",$_POST["id_admin"]);?>
                                   <a href="<?php echo $adminFindPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Admin"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Admin" alt="Cari Admin" /></a>
                              </td>
                         </tr>
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
               <td align="left" class="tablecontent">Prosedur</td>
               <td align="left" class="tablecontent-odd" colspan="3">
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
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[1]","rawat_prosedur_kode_1","10","100",$_POST["rawat_prosedur_kode"][1],"inputField", "autocomplete=\"off\"",false,"onkeyup=\"lookProc1(this.value);\"");?>
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
          </tr>
          <!--<tr>
               <td align="left" width="20%" class="tablecontent">Kode ICDM</td>
               <td align="left" class="tablecontent-odd" width="20%" colspan="3">
                    <table width="100%" border="1" cellpadding="2" cellspacing="1" id="tb_icd">-->
                         <?php if(!$dataIcd) {?>
                              <!--<tr id="tr_icd_0">
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
                              </tr>-->
                         <?php } else {
                              for($i=0,$n=count($dataIcd);$i<$n;$i++) {?>
                                   <!--<tr id="tr_icd_<?php echo $i?>">
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
          							   <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="IcdTambah();">-->
          							<?php } else {?>
          							   <!--<input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="IcdDelete(<?php echo $i;?>);">-->
          							<?php }?>
          							<!--<input name="icd_tot" id="icd_tot" type="hidden" value="<?php echo $n?>">
          			               </td>
                                   </tr>-->
                              <?php }
                         }
                         #echo $view->RenderHidden("hid_icd_del","hid_icd_del",'');?>
                    <!--</table>
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
          </tr>-->
          <tr>
               <td align="left" class="tablecontent">Metode</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <?php echo $view->RenderComboBox("id_op_jenis","id_op_jenis",$optOperasiJenis,null,null,null);?>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Komplikasi Durante OP</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <?php echo $view->RenderComboBox("id_durop_komp","id_durop_komp",$optDurop,"inputField",null,"onChange=\"setDisplay(this.value);\"");?>
		    <input type="text" name="durop_kom_ket" id="durop_komp_ket" width="20" maxlength="255" style="display:none" value="<?php echo $_POST["durop_komp_ket"];?>" class="inputField" />
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pesan Khusus Dari Operator</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <?php echo $view->RenderTextBox("op_pesan","op_pesan","50","100",$_POST["op_pesan"],"inputField",null,false);?>
               </td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Terapi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi_cok">
          <tr class="subheader">
               <td width="25%" align="center">Nama Obat</td>
               <td width="25%" align="center">Harga Satuan</td>
               <td width="15%" align="center">Jumlah</td>
               <td width="25%" align="center">Nominal</td>
               <td width="10%" align="center">&nbsp;</td>
          </tr>
          <?php if(!$_POST["item_nama_cok"]) { ?>
               <tr id="tr_terapi_cok_0">
                    <td align="left" class="tablecontent-odd" width="50%">
                         <?php echo $view->RenderTextBox("item_nama_cok[0]","item_nama_cok_0","30","100",$_POST["item_nama_cok"][0],"inputField", "readonly",false);?>
                         <a href="<?php echo $terapiPage;?>&el=0&jenis=<?php echo $dataPasien["reg_jenis_pasien"];?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" /></a>
                         <input type="hidden" name="id_item_cok[0]" id="id_item_cok_0" value="<?php echo $_POST["id_item"][0]?>" />
                    </td>
                    <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("txtSatuan[0]","txtSatuan_0","20","100",$_POST["txtSatuan"][0],"curedit", "readonly",true);?></td>
                    <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("txtDosis_cok_1[0]","txtDosis_cok_1_0","3","25",$_POST["txtDosis_cok_1"][0],"curedit", "",true,"onkeyup=\"hitungNominal(this.value,".$i.");\"");?></td>
                    <td align="center" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtJumlah_cok_1[0]","txtJumlah_cok_1_0","20","100",$_POST["txtJumlah_cok_1"][0],"curedit", "",false);?>
                        </td>
                    <td align="center" class="tablecontent-odd">
                         <input class="button" name="btnAdd_cok" id="btnAdd_cok" type="button" value="Tambah" onClick="Tambah();">
                         <input name="hid_tot_terapi" id="hid_tot_terapi" type="hidden" value="0">
                    </td>
               </tr>
          <?php } else { ?>
               <?php for($i=0,$n=count($_POST["id_item_cok"]);$i<$n;$i++) { ?>
                    <tr id="tr_terapi_<?php echo $i;?>">
                         <td align="left" class="tablecontent-odd" width="50%">
                              <?php echo $view->RenderTextBox("item_nama_cok[]","item_nama_cok_".$i,"30","100",$_POST["item_nama_cok"][$i],"inputField", "readonly",false);?>
                              <a href="<?php echo $terapiPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Obat" alt="Cari Obat" /></a>
                              <input type="hidden" id="id_item_cok_<?php echo $i;?>" name="id_item_cok[<?php echo $i;?>]" value="<?php echo $_POST["id_item_cok"][$i];?>"/>
                         </td>
                         <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("txtSatuan[$i]","txtSatuan_".$i,"20","100",$_POST["txtSatuan"][$i],"curedit", "readonly",true);?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("txtDosis_cok_1[$i]","txtDosis_cok_1_".$i,"3","25",$_POST["txtDosis_cok_1"][$i],"curedit", null,true,"onkeyup=\"hitungNominal(this.value,".$i.");\"");?></td>
                         <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtJumlah_cok_1[$i]","txtJumlah_cok_1_".$i,"20","100",$_POST["txtJumlah_cok_1"][$i],"curedit", "readonly",false);?>
                        </td>
                         <td align="center" class="tablecontent-odd" width="30%">
                              <?php if($i==0) { ?>
                              <input class="button" name="btnAdd_cok" id="btnAdd_cok" type="button" value="Tambah" onClick="Tambah();">
                              <?php } else { ?>
                              <input class="button" name="btnDel1_cok[<?php echo $i;?>]" id="btnDel1_cok_<?php echo $i;?>" type="button" value="Hapus" onClick="ItemDelete(<?php echo $i;?>);">
                              <?php } ?>
                              <input name="hid_tot_terapi" id="hid_tot_terapi" type="hidden" value="<?php echo $n;?>">
                         </td>
                    </tr>
               <?php } ?>
          <?php } ?>
                              <?php echo $view->RenderHidden("hid_id_del_terapi","hid_id_del_terapi",'');?>
     </table>
     <?php echo $view->RenderHidden("terapi_id","terapi_id",$_POST["terapi_id"]);?>
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
			<td align="center" colspan="2"  class="tablecontent-odd"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>
</table>

<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="op_id" value="<?php echo $_POST["op_id"];?>"/>
<input type="hidden" name="status" value="<?php echo $_POST["status"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"]?>"/>

</form>
<?php } ?>
<?php echo $view->RenderBodyEnd(); ?>
