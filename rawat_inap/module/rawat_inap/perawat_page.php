<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");    
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     
     if(!$_POST["perawat_tanggal_kontrol"]) $_POST["perawat_tanggal_kontrol"] = getDateToday();
     if(!$_POST["pukul_jam"]) $_POST["pukul_jam"] = date('H');
     if(!$_POST["pukul_menit"]) $_POST["pukul_menit"] = date('i');
     
     
 	if(!$auth->IsAllowed("rawat_inap",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("rawat_inap",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     } 
    
     $_x_mode = "New";
     $thisPage = "perawat_page.php";
     $icdPage = "icd_find.php?";
     $inaPage = "ina_find.php?";
     $terapiPage = "obat_find.php?";
     $tindakanPage = "tindakan_find.php?";
     $dokterPage = "rawat_dokter_find.php?";
	   $susterPage = "rawat_suster_find.php?";
     $backPage = "perawatan_view.php?";

     $tableRefraksi = new InoTable("table1","99%","center");

      //ambil id poli
      $sql = "select usr_poli from global.global_auth_user where usr_id = ".QuoteValue(DPE_NUMERIC,$userId);   
      $rs_edit = $dtaccess->Execute($sql);
      $row_edit = $dtaccess->Fetch($rs_edit);  
      $poliId=$row_edit["usr_poli"];
      //echo $sql;
      
      $plx = new InoLiveX("GetPerawatan,GetTonometri,GetDosis,CariTindakan");     

      function GetTonometri($scale,$weight) {
          global $dtaccess; 
               
          $sql = "select tono_pressure from klinik.klinik_tonometri where tono_scale = ".QuoteValue(DPE_NUMERIC,$scale)." and tono_weight = ".QuoteValue(DPE_NUMERIC,$weight);
          $dataTable = $dtaccess->Fetch($sql);
          return $dataTable["tono_pressure"];

     }     

     function GetPerawatan() {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
          
          $sql = "select cust_usr_nama,a.reg_id,a.reg_status,a.reg_waktu,a.reg_jadwal, 
                  c.rawatinap_tanggal_masuk,c.rawatinap_id,d.biaya_nama,e.kamar_kode,f.bed_kode
                  from klinik_registrasi a
                  left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            			left join klinik_rawatinap c on c.id_reg = a.reg_id
            			left join klinik_biaya d on d.biaya_jenis = c.id_kategori_kamar
            			left join klinik_kamar e on e.kamar_id = c.id_kamar and e.id_kategori = c.id_kategori_kamar
            			left join klinik_kamar_bed f on f.bed_id = c.id_bed and f.id_kamar = c.id_kamar
                  where a.reg_status like '".STATUS_RAWATINAP.STATUS_MENGINAP."' order by reg_status desc, biaya_nama, kamar_kode, rawatinap_tanggal_masuk asc, rawatinap_waktu_masuk asc";
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

          /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Bed";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;*/

          
          
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
          
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["biaya_nama"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kamar_kode"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          
       /*  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed_kode"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;*/
          }
			
          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
	  //return $sql;
		
	}
     
     function GetDosis($fisik,$akhir,$id=null) {
          global $dtaccess, $view;
          
          $sql = "select dosis_id, dosis_nama from logistik.logistik_dosis where id_fisik = ".QuoteValue(DPE_NUMERIC,$fisik);
          $dataTable = $dtaccess->FetchAll($sql);

          $optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show); 
          for($i=0,$n=count($dataTable);$i<$n;$i++) {
               $show = ($id==$dataTable[$i]["dosis_id"]) ? "selected":"";
               $optDosis[$i+1] = $view->RenderOption($dataTable[$i]["dosis_id"],$dataTable[$i]["dosis_nama"],$show); 
          }
          
          return $view->RenderComboBox("id_dosis[]","id_dosis_".$akhir,$optDosis,null,null,null);
     }
     //----get tindakan----//
     function CariTindakan($namaTindakan) {
          global $dtaccess;

          $sql = "select a.biaya_id, a.biaya_nama, a.biaya_total
          from klinik.klinik_biaya a
                    where upper(a.biaya_nama) = ".QuoteValue(DPE_CHAR,strtoupper($namaTindakan));
           // return $sql;
          $rs = $dtaccess->Execute($sql);
          $dataTindakan = $dtaccess->Fetch($rs);
          
        return $dataTindakan["biaya_id"]."~~".$dataTindakan["biaya_nama"]."~~".$dataTindakan["biaya_total"];
     }       
     //----get tindakan----//
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
               if(!$_POST["id_suster"][$i]) $_POSTc["id_suster"][$i] = $row["pgw_id"];
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
          
          //------------------------Frm pemeriksaan------------------------//
                    
            $pemeriksaanLink = "perawatan_perawat.php?id_cust_usr=".$enc->Encode($dataPasien["id_cust_usr"])."&id_reg=".$enc->Encode($_GET["id_reg"]);
          
          
          $sql = "select * from klinik.klinik_perawatan where rawat_id = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          
          $dataPemeriksaan = $dtaccess->Fetch($sql);
          //if($dataPemeriksaan) $_x_mode = "Diag";

          $view->CreatePost($dataPemeriksaan);


          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_suster"][$i] = $row["pgw_id"];
               $_POST["rawat_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
          
          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_bidan a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_bidan"][$i] = $row["pgw_id"];
               $_POST["rawat_bidan_nama"][$i] = $row["pgw_nama"];
               $i++;
          }


          $sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_dokter a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql);
          $i=0;
          while($row=$dtaccess->Fetch($rs)) {
               $_POST["id_dokter"][$i] = $row["pgw_id"];
               $_POST["rawat_dokter_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
          

          

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


          // --- ina od
          $sql = "select ina_nama,ina_kode, ina_id from klinik.klinik_perawatan_ina a
                    join klinik.klinik_ina b on a.id_ina = b.ina_id where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." and rawat_ina_odos = 'OD'
                    order by rawat_ina_urut";
          //$rs = $dtaccess->Execute($sql);
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
          //$rs = $dtaccess->Execute($sql);
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
		   
       
     //Di Hidden dahulu biar ngga ribet untuk Obat 
            // --- terapi obat   ****    =.='a
          $sql = "select obat_id, obat_nama,id_petunjuk,id_rawat,petunjuk_nama from klinik.klinik_perawatan_terapi a
                    left join apotik.apotik_obat_master b on a.id_item = b.obat_id
                    left join apotik.apotik_obat_petunjuk c on c.petunjuk_id = b.id_petunjuk
                    where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." 
                    order by rawat_item_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->FetchAll($rs)) {
               $_POST["obat_id"][$i] = $row["obat_id"];
               $_POST["obat_nama"][$i] = $row["obat_nama"];
               $_POST["id_petunjuk"][$i] = $row["id_petunjuk"];
               $i++;
          }     

          //------------------------End Frm pemeriksaan------------------------//
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
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, 
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan, d.rawatinap_waktu_masuk
                    from klinik.klinik_registrasi a
		    left join klinik.klinik_refraksi c on a.reg_id = c.id_reg
		    left join klinik.klinik_rawatinap d on a.reg_id = d.id_reg
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          //echo $sql;
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
    $_POST["cust_usr_jenis"] = $dataPasien["cust_usr_jenis"];
    $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
		$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];

          
         $pemeriksaanLink = "perawatan_perawat.php?id_cust_usr=".$enc->Encode($dataPasien["id_cust_usr"])."&id_reg=".$enc->Encode($_GET["id_reg"]);  
         
 $sql = "select * from klinik.klinik_perawatan where rawat_flag = 'I' and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." order by rawat_waktu";
       //echo $sql;
      $dataPemeriksaan = $dtaccess->FetchAll($sql);

		for($i=0,$n=count($dataPemeriksaan);$i<$n;$i++) {

			$dataPemeriksaan[$i]["foto"] = $lokasi."/".$dataPemeriksaan[$i]["rawat_mata_foto"];
			$dataPemeriksaan[$i]["sketsa"] = $lokasi."/".$dataPemeriksaan[$i]["rawat_mata_sketsa"];
			
			$sql = "select distinct pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
					join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"]);
			//echo $sql;
      $rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["suster"][$j] = $row["pgw_nama"];
				$j++;
			}
	
	
			$sql = "select pgw_nama, pgw_id from klinik.klinik_perawatan_dokter a
					join hris.hris_pegawai b on a.id_pgw = b.pgw_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"]);
			$rs = $dtaccess->Execute($sql);
			
			unset($row);
			$row=$dtaccess->Fetch($rs);
			$dataPemeriksaan[$i]["dokter"] = $row["pgw_nama"];


			$sql = "select item_nama from klinik.klinik_perawatan_terapi a
						left join logistik.logistik_item b on cast(a.id_item as varchar)= b.item_id 
            where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"]);
			$rs = $dtaccess->Execute($sql);
//echo $sql;
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["terapi_obat"][$j] = $row["item_nama"];
				$dataPemeriksaan[$i]["terapi_dosis"][$j] = $row["petunjuk_nama"];
				$j++;
                    
			}	
	
	 	$sql = "select biaya_nama, biaya_total from klinik.klinik_biaya a
	 	left join klinik.klinik_perawatan_tindakan b on a.biaya_id = b.id_tindakan
						where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"]);
			$rs = $dtaccess->Execute($sql);

			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["tindakan_nama"][$j] = $row["biaya_nama"];
				$dataPemeriksaan[$i]["tindakan_total"][$j] = $row["biaya_total"];
				$j++;
                    
			}	

			$sql = "select icd_nomor, icd_nama from klinik.klinik_perawatan_icd a
						left join klinik.klinik_icd c on a.id_icd = c.icd_nomor where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"])." 
                              and rawat_icd_odos = 'OD'";
			$rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["icd_od_nomor"][$j] = $row["icd_nomor"];
				$dataPemeriksaan[$i]["icd_od_nama"][$j] = $row["icd_nama"];
				$j++;
			}	

			$sql = "select icd_nomor, icd_nama from klinik.klinik_perawatan_icd a
						left join klinik.klinik_icd c on a.id_icd = c.icd_nomor where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"])." 
                              and rawat_icd_odos = 'OS'";
			$rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["icd_os_nomor"][$j] = $row["icd_nomor"];
				$dataPemeriksaan[$i]["icd_os_nama"][$j] = $row["icd_nama"];
				$j++;
			}	
	

			$sql = "select ina_kode, ina_nama from klinik.klinik_perawatan_ina a
						left join klinik.klinik_ina c on a.id_ina = c.ina_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"])."
                              and rawat_ina_odos = 'OD'";
			$rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["ina_od_kode"][$j] = $row["ina_kode"];
				$dataPemeriksaan[$i]["ina_od_nama"][$j] = $row["ina_nama"];
				$j++;
			}	
			
	

			$sql = "select ina_kode, ina_nama from klinik.klinik_perawatan_ina a
						left join klinik.klinik_ina c on a.id_ina = c.ina_id where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan[$i]["rawat_id"])."
                              and rawat_ina_odos = 'OS'";
			$rs = $dtaccess->Execute($sql);
			
			$j=0; unset($row);
			while($row=$dtaccess->Fetch($rs)) {
				$dataPemeriksaan[$i]["ina_os_kode"][$j] = $row["ina_kode"];
				$dataPemeriksaan[$i]["ina_os_nama"][$j] = $row["ina_nama"];
				$j++;
			}	
			
		}
		}
		$view->CreatePost($dataPemeriksaan);

	
//	echo $_POST["obat_id"]."-".$_POST["obat_id"];    
	
	 // --- terapi obat   ****    =.='a
          $sql = "select item_id, item_nama,id_petunjuk,id_rawat from klinik.klinik_perawatan_terapi a
                    left join logistik.logistik_item b on cast(a.id_item as varchar) = b.item_id
                    
                    where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"])." 
                    order by rawat_item_urut";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->FetchAll($rs)) {
               $_POST["item_id"][$i] = $row["item_id"];
               $_POST["item_nama"][$i] = $row["item_nama"];
               $_POST["id_petunjuk"][$i] = $row["id_petunjuk"];
               $i++;
          }
	
	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {

	$lunas = ($_POST["cust_usr_jenis"]==PASIEN_BAYAR_SWADAYA)?'n':'y';
          
          // --- delete data e dulu ---
          if($_POST["btnSave"]) {               
             //  $sql = "delete from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
             //  $dtaccess->Execute($sql);
          }
//print_r($_POST["biaya_id"]);
//die();          
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
          $dbField[12] = "rawat_lab_alergi";
          $dbField[13] = "rawat_mata_od_palpebra";
          $dbField[14] = "rawat_mata_os_palpebra";
          $dbField[15] = "rawat_mata_od_conjunctiva";
          $dbField[16] = "rawat_mata_os_conjunctiva";
          $dbField[17] = "rawat_mata_od_cornea";
          $dbField[18] = "rawat_mata_os_cornea";
          $dbField[19] = "rawat_mata_od_coa";
          $dbField[20] = "rawat_mata_os_coa";
          $dbField[21] = "rawat_mata_od_iris";
          $dbField[22] = "rawat_mata_os_iris";
          $dbField[23] = "rawat_mata_od_pupil";
          $dbField[24] = "rawat_mata_os_pupil";
          $dbField[25] = "rawat_mata_od_lensa";
          $dbField[26] = "rawat_mata_os_lensa";
          $dbField[27] = "rawat_mata_od_ocular";
          $dbField[28] = "rawat_mata_os_ocular";
          $dbField[29] = "rawat_mata_od_retina";
          $dbField[30] = "rawat_mata_os_retina";
          $dbField[31] = "id_cust_usr";
          $dbField[32] = "rawat_tonometri_weight_od";
          $dbField[33] = "rawat_tonometri_pressure_od";
          $dbField[34] = "rawat_mata_foto";          
          $dbField[35] = "rawat_mata_sketsa";
          $dbField[36] = "rawat_tonometri_od";
          $dbField[37] = "rawat_tonometri_os";
          $dbField[38] = "rawat_anestesis_jenis";
          $dbField[39] = "rawat_anestesis_obat";
          $dbField[40] = "rawat_anestesis_dosis";
          $dbField[41] = "rawat_anestesis_komp";
          $dbField[42] = "rawat_anestesis_pre";
          $dbField[43] = "rawat_operasi_jenis";
          $dbField[44] = "rawat_operasi_paket";
          $dbField[45] = "rawat_tonometri_weight_os";
          $dbField[46] = "rawat_tonometri_pressure_os";
          $dbField[47] = "rawat_tonometri_scale_os";
          $dbField[48] = "rawat_color_blindness";
          $dbField[49] = "rawat_catatan";
          $dbField[50] = "rawat_irigasi";
          $dbField[51] = "rawat_epilasi";
          $dbField[52] = "rawat_suntikan";
          $dbField[53] = "rawat_probing";
          $dbField[54] = "rawat_flouorecsin";
          $dbField[55] = "rawat_kesehatan";
          $dbField[56] = "rawat_kacamata_refraksi";
          $dbField[57] = "rawat_mata_od_koreksi_spheris";
          $dbField[58] = "rawat_mata_od_koreksi_cylinder";
          $dbField[59] = "rawat_mata_od_koreksi_sudut";
          $dbField[60] = "rawat_mata_os_koreksi_spheris";
          $dbField[61] = "rawat_mata_os_koreksi_cylinder";
          $dbField[62] = "rawat_mata_os_koreksi_sudut";
          $dbField[63] = "rawat_tanggal";
          $dbField[64] = "rawat_od_vitreus"; 
          $dbField[65] = "rawat_os_vitreus";
          $dbField[66] = "rawat_flag"; 
          $dbField[67] = "rawat_flag_komen"; 
          $dbField[68] = "rawat_waktu_kontrol";
          $dbField[69] = "rawat_lab_suhu";
          
           if($_POST["btnSave"]) $dbField[70] = "rawat_waktu";
           
        //  if(!$_POST["rawat_id"]) 
          $waktu = $_POST["pukul_jam"].":".$_POST["pukul_menit"];
          $_POST["rawat_id"] = $dtaccess->GetTransID();
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
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_alergi"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_palpebra"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_palpebra"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_conjunctiva"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_conjunctiva"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_cornea"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_cornea"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_coa"]);
          $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_coa"]);
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_iris"]);
          $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_iris"]);
          $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_pupil"]);
          $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_pupil"]);
          $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_lensa"]);
          $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_lensa"]);
          $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_ocular"]);
          $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_ocular"]);
          $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_retina"]);
          $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_retina"]);
          $dbValue[31] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[32] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_weight_od"]);
          $dbValue[33] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_pressure_od"]);
          $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_foto"]);          
          $dbValue[35] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_sketsa"]);
          $dbValue[36] = QuoteValue(DPE_CHAR,$_POST["rawat_tonometri_od"]);
          $dbValue[37] = QuoteValue(DPE_CHAR,$_POST["rawat_tonometri_os"]);
          $dbValue[38] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_jenis"]);
          $dbValue[39] = QuoteValue(DPE_NUMERICKEY,$_POST["rawat_anestesis_obat"]);
          $dbValue[40] = QuoteValue(DPE_CHAR,$_POST["rawat_anestesis_dosis"]);
          $dbValue[41] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_komp"]);
          $dbValue[42] = QuoteValue(DPE_CHARKEY,$_POST["rawat_anestesis_pre"]);
          $dbValue[43] = QuoteValue(DPE_CHARKEY,$_POST["rawat_operasi_jenis"]);
          $dbValue[44] = QuoteValue(DPE_CHARKEY,$_POST["rawat_operasi_paket"]);
          $dbValue[45] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_weight_os"]);
          $dbValue[46] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_pressure_os"]);
          $dbValue[47] = QuoteValue(DPE_NUMERIC,$_POST["rawat_tonometri_scale_os"]);
          $dbValue[48] = QuoteValue(DPE_CHAR,$_POST["rawat_color_blindness"]);
          $dbValue[49] = QuoteValue(DPE_CHAR,$_POST["rawat_catatan"]);
          $dbValue[50] = QuoteValue(DPE_CHAR,$_POST["rawat_irigasi"]);
          $dbValue[51] = QuoteValue(DPE_CHAR,$_POST["rawat_epilasi"]);
          $dbValue[52] = QuoteValue(DPE_CHAR,$_POST["rawat_suntikan"]);
          $dbValue[53] = QuoteValue(DPE_CHAR,$_POST["rawat_probing"]);
          $dbValue[54] = QuoteValue(DPE_CHAR,$_POST["rawat_flouorecsin"]);
          $dbValue[55] = QuoteValue(DPE_CHAR,$_POST["rawat_kesehatan"]);
          $dbValue[56] = QuoteValue(DPE_CHAR,$_POST["rawat_kacamata_refraksi"]);
          $dbValue[57] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_spheris"]);
          $dbValue[58] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_cylinder"]);
          $dbValue[59] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_od_koreksi_sudut"]);
          $dbValue[60] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_spheris"]);
          $dbValue[61] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_cylinder"]);
          $dbValue[62] = QuoteValue(DPE_CHAR,$_POST["rawat_mata_os_koreksi_sudut"]);
          $dbValue[63] = QuoteValue(DPE_DATE,date_db($_POST["perawat_tanggal_kontrol"]));
          $dbValue[64] = QuoteValue(DPE_CHAR,$_POST["rawat_od_vitreus"]); 
          $dbValue[65] = QuoteValue(DPE_CHAR,$_POST["rawat_os_vitreus"]); 
          $dbValue[66] = QuoteValue(DPE_CHAR,'I'); 
          $dbValue[67] = QuoteValue(DPE_CHAR,'RAWAT INAP'); 
          $dbValue[68] = QuoteValue(DPE_CHAR,$waktu);
          $dbValue[69] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_suhu"]);
          
          if($_POST["btnSave"]) $dbValue[70] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
    //      if ($_POST["btnSave"]) {
              $dtmodel->Insert() or die("insert  error");	
    //      } elseif ($_POST["btnUpdate"]) {
    //          $dtmodel->Update() or die("update  error");	
    //      }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
	  $sql_update = "update klinik.klinik_registrasi set reg_status = ".QuoteValue(DPE_CHAR,$_POST["cmbNext"]).", reg_waktu = CURRENT_TIME where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dtaccess->Execute($sql_update); 
          
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
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_od_kode"][$i]);
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
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_icd_os_kode"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHAR,"OS");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["rawat_icd_os_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }

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
		//	$dbField[5] = "reg_ina_jenis";
			$dbField[6] = "reg_ina_when";
			$dbField[7] = "reg_ina_who";
				
				$regInaId = $dtaccess->GetTransID();
				$dbValue[0] = QuoteValue(DPE_CHARKEY,$regInaId);
				$dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_klaim_nama"][0]);
				$dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["rawat_ina_klaim_id"][0]);
				$dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
				$dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
			//	$dbValue[5] = QuoteValue(DPE_CHAR,STATUS_PEMERIKSAAN);
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
          $dbField[5] = "terapi_jumlah_item";
          
          for($i=0,$n=count($_POST["item_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["item_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$i);
               $dbValue[4] = QuoteValue(DPE_CHARKEY,$_POST["id_petunjuk"][$i]);
               $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jumlah"][$i]));

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["item_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
               
    
               $sql = "select a.item_id, a.item_stok from klinik.klinik_stok_item a where a.item_id = ".QuoteValue(DPE_CHARKEY,$_POST["item_id"][$i]);
               $dataUpdateObat = $dtaccess->Fetch($sql);
               
               if($dataUpdateObat){
               $dataUpdateObat["item_stok"] = $dataUpdateObat["item_stok"] - $_POST["jumlah"][$i];
               $sql2 = "update klinik.klinik_stok_item set item_stok = ".QuoteValue(DPE_NUMERIC,$dataUpdateObat["item_stok"])." where item_id =".QuoteValue(DPE_CHAR,$dataUpdateObat["item_id"]);
               $dtaccess->Execute($sql2);               
               }
          }
          
           unset($dbField);
              
              
              	
            $sql = "select rawat_item_id, id_item, terapi_jumlah_item from klinik.klinik_perawatan_terapi where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]); 
            $rs = $dtaccess->Execute($sql);
            $dataDetail = $dtaccess->FetchAll($rs);
            
       /*for($a=0,$b=count($dataDetail);$a<$b;$a++){
          $sql = "select item_stok_ditangan from inventori.inv_item
                    where item_id=".QuoteValue(DPE_CHAR,$dataDetail[$a]["id_item"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $stok = $dtaccess->Fetch($rs);
           
          $stok_baru = $stok["obat_stok"] - $dataDetail[$a]["terapi_jumlah_item"];
          
          $dbTable = "logistik.logistik_item";
          
          $dbField[0]  = "item_id";   
          $dbField[1]  = "item_stok";
                    
          $dbValue[0] = QuoteValue(DPE_CHAR,$dataDetail[$a]["id_item"]);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$stok_baru); 
           
          // echo $dataDetail[$i]["id_item"];
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          $dtmodel->Update() or die("insert  error");	
          
           unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          
         }*/
         
      //----insert terapi ke klinik_folio----//   
         
          if($_POST["item_id"][0]){
          for($i=0,$n=count($_POST["item_id"]);$i<$n;$i++) {  

$sql = "select a.item_harga_jual, b.terapi_jumlah_item from inventori.inv_item a
        left join klinik.klinik_perawatan_terapi b on a.item_id=b.id_item
                    where b.id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataDetail = $dtaccess->Fetch($rs);
           
          $biaya = $dataDetail["obat_harga_jual"] * $dataDetail["terapi_jumlah_item"];
          
 
          $dbTable = "klinik.klinik_folio";
          $dbField[0] = "fol_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "fol_nama";
          $dbField[3] = "fol_nominal";
          $dbField[4] = "fol_jenis";
          $dbField[5] = "id_cust_usr";
          $dbField[6] = "fol_waktu";
          $dbField[7] = "fol_lunas";
          $dbField[8] = "id_biaya";   
           $dbField[9] = "fol_jumlah";
                    $dbField[10] = "fol_nominal_satuan";   
           

               $folId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$folId);
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["item_nama"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($biaya));
               $dbValue[4] = QuoteValue(DPE_CHARKEY,OBAT_TERAPI_INAP);
               $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["id_cust_usr"]);
               $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[7] = QuoteValue(DPE_CHARKEY,$lunas);
               $dbValue[8] = QuoteValue(DPE_CHARKEY,$_POST["item-id"][$i]);
               $dbValue[9] = QuoteValue(DPE_NUMERIC,$dataDetail["terapi_jumlah_item"]);
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($biaya));
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               $dtmodel->Insert() or die("insert  error");
               
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
               
               	$sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,$_POST["item_id"][$i]);
					$splitData = $dtaccess->FetchAll($sql,DB_SCHEMA);
					
					for($a=0,$b=count($splitData);$a<$b;$a++) { 
						$dbTable = "klinik_folio_split";
					
						$dbField[0] = "folsplit_id";   // PK
						$dbField[1] = "id_fol";
						$dbField[2] = "id_split";
						$dbField[3] = "folsplit_nominal";
							  
						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
						$dbValue[2] = QuoteValue(DPE_CHAR,$splitData[$a]["id_split"]);
						$dbValue[3] = QuoteValue(DPE_NUMERIC,$splitData[$a]["bea_split_nominal"]);
						 
						$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
						$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
						
						$dtmodel->Insert() or die("insert error"); 
						
						unset($dtmodel);
						unset($dbField);
						unset($dbValue);
						unset($dbKey); 
    					} 
    					
    					}
				//----End folio terapi----// 
          
          
          // -- ini insert ke tabel rawat tindakan
		$sql = "delete from klinik.klinik_perawatan_tindakan where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_tindakan";
          $dbField[0] = "rawat_tindakan_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_tindakan";
          $dbField[3] = "rawat_tindakan_total";
          
          for($i=0,$n=count($_POST["biaya_id"]);$i<$n;$i++) {
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["rawat_id"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["biaya_id"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_total"][$i]));

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["biaya_id"][$i]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
     
     	//$lunas = ($_POST["cust_usr_jenis"]==PASIEN_BAYAR_SWADAYA)?'n':'y';
            
          if($_POST["biaya_id"][0]){
          for($i=0,$n=count($_POST["biaya_id"]);$i<$n;$i++) {  

          $dbTable = "klinik.klinik_folio";
          $dbField[0] = "fol_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "fol_nama";
          $dbField[3] = "fol_nominal";
          $dbField[4] = "fol_jenis";
          $dbField[5] = "id_cust_usr";
          $dbField[6] = "fol_waktu";
          $dbField[7] = "fol_lunas";
          $dbField[8] = "id_biaya";
           $dbField[9] = "fol_jumlah";
                    $dbField[10] = "fol_nominal_satuan";
     
               $folId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$folId);
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$_POST["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$_POST["biaya_nama"][$i]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_total"][$i]));
               $dbValue[4] = QuoteValue(DPE_CHARKEY,$_POST["biaya_jenis"][$i]);
               $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["id_cust_usr"]);
               $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[7] = QuoteValue(DPE_CHARKEY,$lunas);
               $dbValue[8] = QuoteValue(DPE_CHARKEY,$_POST["biaya_id"][$i]);
               $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_total"][$i]));
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               $dtmodel->Insert() or die("insert  error");
               
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
				
        	$sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,$_POST["biaya_id"][$i]);
					$splitData = $dtaccess->FetchAll($sql,DB_SCHEMA);
					
					for($a=0,$b=count($splitData);$a<$b;$a++) { 
						$dbTable = "klinik_folio_split";
					
						$dbField[0] = "folsplit_id";   // PK
						$dbField[1] = "id_fol";
						$dbField[2] = "id_split";
						$dbField[3] = "folsplit_nominal";
							  
						$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
						$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
						$dbValue[2] = QuoteValue(DPE_CHAR,$splitData[$a]["id_split"]);
						$dbValue[3] = QuoteValue(DPE_NUMERIC,$splitData[$a]["bea_split_nominal"]);
						 
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

          // --- insrt dokter ---
	  if($_POST["id_dokter"]) {
	       $sql = "delete from klinik.klinik_perawatan_dokter 
		       where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
	       $dtaccess->Execute($sql);
               
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
	  
	  if($_POST["id_suster"]){
	       $sqlDelete = "delete from klinik.klinik_perawatan_suster 
			     where id_rawat = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]); 
	       
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
          }
		$sql = "update global.global_customer_user set cust_usr_alergi = ".QuoteValue(DPE_CHAR,$_POST["rawat_lab_alergi"])." where cust_usr_id = ".$_POST["id_cust_usr"];
          $dtaccess->Execute($sql);
          
          
          if($_POST["_x_mode"]!="Edit") {
               
          //     $sql = "update klinik.klinik_registrasi set reg_status = '".$_POST["cmbNext"].STATUS_ANTRI."', reg_waktu = CURRENT_TIME where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          //     $dtaccess->Execute($sql); 
               
               // --- nyimpen paket klaim e ---
             /*if($_POST["cmbNext"]==STATUS_SELESAI && $_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA) { 
               
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
             */  
          }
          
          
          if($_POST["_x_mode"]!="Edit") echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          exit();

	}
	
	foreach($rawatKeadaan as $key => $value) {
          unset($show);
          if($_POST["rawat_keadaan_umum"]==$key) $show="selected";
		$optionsKeadaan[] = $view->RenderOption($key,$value,$show);
	}


     $sql = "select diag_id from klinik.klinik_diagnostik where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     //$dataDiag = $dtaccess->Fetch($sql);
     
     $count=0;	
	$optionsNext[$count] = $view->RenderOption(STATUS_RAWATINAP.STATUS_MENGINAP,"Tidak Ada",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_DIAGNOSTIK_TIPE.STATUS_ANTRI,"Ke Ruang Diagnostik",$show); $count++;
	$optionsNext[$count] = $view->RenderOption(STATUS_PREOP.STATUS_ANTRI,"Ke Ruang PreOP",$show); $count++;
	//$optionsNext[$count] = $view->RenderOption(STATUS_INAP_OPERASI_JADWAL,"Penjadwalan Operasi",$show); $count++;
	//$optionsNext[$count] = $view->RenderOption(STATUS_INAP_PREOP,"Pre Operasi",$show); $count++;
	//$optionsNext[$count] = $view->RenderOption(STATUS_INAP_BEDAH,"Bedah Minor",$show); $count++;
	//$optionsNext[$count] = $view->RenderOption(STATUS_RAWATINAP,"Rawat Inap",$show); $count++;
	//$optionsNext[$count] = $view->RenderOption(STATUS_INAP_APOTEK,"Apotek",$show); $count++;

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
     
     /*
     $sql = "select op_paket_id, op_paket_nama from klinik.klinik_operasi_paket";
     $dataOperasiPaket= $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi paket
     $optOperasiPaket[0] = $view->RenderOption("","[Pilih Paket Operasi]",$show); 
     for($i=0,$n=count($dataOperasiPaket);$i<$n;$i++) {
          $show = ($_POST["rawat_operasi_paket"]==$dataOperasiPaket[$i]["op_paket_id"]) ? "selected":"";
          $optOperasiPaket[$i+1] = $view->RenderOption($dataOperasiPaket[$i]["op_paket_id"],$dataOperasiPaket[$i]["op_paket_nama"],$show); 
     }

     // --- nyari datanya anestesis---
     $sql = "select distinct(anes_jenis_nama), anes_jenis_id  from klinik.klinik_anestesis_jenis";
     $dataAnestesisJenis = $dtaccess->FetchAll($sql);

     $sql = "select anes_komp_id, anes_komp_nama from klinik.klinik_anestesis_komplikasi";
     $dataAnestesisKomplikasi = $dtaccess->FetchAll($sql);

     $sql = "select anes_pre_id, anes_pre_nama from klinik.klinik_anestesis_premedikasi";
     $dataAnestesisPremedikasi = $dtaccess->FetchAll($sql);

     $sql = "select item_id, item_nama from logistik.logistik_item where id_menu = ".QuoteValue(DPE_NUMERIC,3);
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
     */
     	for($r=0;$r<24;$r++){
    $opt_jam[] = $view->RenderOption((strlen($r)==1)?"0".$r:$r,(strlen($r)==1)?"0".$r:$r,($r==$_POST["pukul_jam"])?"selected":"");
  }
  
  for($r=0;$r<60;$r++){
    $opt_menit[] = $view->RenderOption((strlen($r)==1)?"0".$r:$r,(strlen($r)==1)?"0".$r:$r,($r==$_POST["pukul_menit"])?"selected":"");
  }
     
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
     
               
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
          $dbField[12] = "rawat_lab_alergi";
          $dbField[13] = "rawat_catatan";
          $dbField[14] = "rawat_tanggal";
          $dbField[15] = "rawat_waktu_kontrol";
          $dbField[16] = "rawat_diagnosa_keperawatan";
          $dbField[17] = "rawat_observasi";
          $dbField[18] = "id_cust_usr";
          $dbField[19] = "rawat_flag";
          $dbField[20] = "rawat_flag_komen";
          $dbField[21] = "rawat_lab_suhu";
          
          $waktu = $_POST["pukul_jam"].":".$_POST["pukul_menit"];
          $rawat = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$rawat);   // PK
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
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_alergi"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["rawat_catatan"]);
          $dbValue[14] = QuoteValue(DPE_DATE,date_db($_POST["perawat_tanggal_kontrol"])); 
          $dbValue[15] = QuoteValue(DPE_CHAR,$waktu);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["rawat_diagnosa_keperawatan"]);
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["rawat_observasi"]);
          $dbValue[18] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,'I');
          $dbValue[20] = QuoteValue(DPE_CHAR,'RAWAT INAP');
          $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["rawat_lab_suhu"]);

          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
       //   if ($_POST["btnSave"]) {
              $dtmodel->Insert() or die("insert  error");	
      //    } elseif ($_POST["btnUpdate"]) {
       //       $dtmodel->Update() or die("update  error");	
       //   }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
	  
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

function MasukkanTindakan(frm,kode3) 
{    
     var akhir = eval(document.getElementById('hid_tot').value);    
     hasilKode3=CariTindakan(kode3,'type=r');
     hasilAkhir3=hasilKode3.split('~~');
     if(!hasilAkhir3[0]) {
         frm.tindakan_kode_0.focus();  
          alert('Tindakan dengan kode \''+kode3+'\' tidak ditemukan');
          return false;
     }
     document.getElementById('biaya_id_'+akhir+'' ).value=hasilAkhir3[0];
     document.getElementById('biaya_nama_'+akhir+'').value=hasilAkhir3[1];
     document.getElementById('biaya_total_'+akhir+'').value=formatCurrency(hasilAkhir3[2]);
		 frm.biaya_kode_0.focus();
            //'tr_terapi_'+akhir+'' }
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

//function SetDosis(akhir) {
//     GetDosis(akhir,'target=id_petunjuk_'+akhir);
//     return true;
//}

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
                         'input', {type:'hidden', value:'', name:'item_id[]', id:'item_id_'+akhir+''}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:21, maxLength:100,name:'petunjuk_nama[]', id:'petunjuk_nama_'+akhir+''}
                    ],
               'td', { align: 'center', style: 'color: black;' },
                    [
                         'input', {type:'curedit', value:'', size:5, maxLength:100,name:'jumlah[]', id:'jumlah_'+akhir+''}
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
     document.getElementById('hid_id_del').value += document.getElementById('item_id_'+akhir).value;
     
     $('#tr_terapi_'+akhir).remove();
}


function Tambah1(){
     var akhir = eval(document.getElementById('hid_tot').value)+1;
     
     $('#tb_tindakan').createAppend(
          'tr', { class  : 'tablecontent-odd',id:'tr_tindakan_'+akhir+'' },
                ['td', { align: 'left', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'', size:20, maxLength:100, name:'biaya_nama[]', id:'biaya_nama_'+akhir},[],
                         'a',{ href:'<?php echo $tindakanPage;?>&el='+akhir+'&TB_iframe=true&height=400&width=450&modal=true',class:'thickbox', title:'Cari Tindakan'},
                         [
                              'img', {src:'<?php echo $APLICATION_ROOT?>images/bd_insrow.png', hspace:2, height:20, width:18, align:'middle', style:'cursor:pointer', border:0}
                         ],
                         'input', {type:'hidden', value:'', name:'biaya_id[]', id:'biaya_id_'+akhir}
                          
                    ],
               'td', { align: 'center', style: 'color: black;' },
                    [
                         'input', {type:'text', value:'0', size:21, maxLength:100, name:'biaya_total[]', id:'biaya_total_'+akhir, class:'curedit', onkeyup:"this.value=formatCurrency(this.value);" }
                    ],
               'td', { align: 'center', style: 'color: black;' },
                   
                       [
                            'input', {type:'button', class:'button', value:'Hapus', name:'btnDel['+akhir+']', id:'btnDel_'+akhir}
                       ]      
                ]                      
     );
     
     $('#btnDel_'+akhir+'').click( function() { Delete1(akhir) } );
     document.getElementById('biaya_nama_'+akhir).readOnly = true;
          
     document.getElementById('hid_tot').value = akhir;
     tb_init('a.thickbox');
}

function Delete1(akhir){
     document.getElementById('hid_id_del').value += document.getElementById('biaya_id_'+akhir).value;
     
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
</script>
<!-- link css untuk tampilan tab ala winXP -->
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>/library/css/winxp.css" />
<!-- link jscript untuk fungsi-fungsi tab dasar -->
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>/library/script/listener.js"></script> 
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>/library/script/tabs.js"></script>  
<?php if(!$_GET["id"]) { ?>
	<div id="antri_kanan" style="width:100%;">
		<div class="tableheader">Proses Pemeriksaan Pasien</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php //echo GetPerawatan(); ?></div>
	</div>
<?php } ?>
<?php if($_GET["id_reg"] && $_GET["id_rawatinap"]) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan="2" class="tableheader">Input Data Dokter</td>
		<td align="right" colspan=2 class="tableheader">Input Data Perawat</td>
 <!--	<tr>
	  <td align="right" class="tablecontent" width="20%">Tanggal Kontrol&nbsp;</td>
	  <td align="left" class="tablecontent-odd" width="80%">
	     <?php echo $view->RenderTextBox("tanggal_kontrol","tanggal_kontrol","15","15",format_date($_POST["tanggal_kontrol"]),"inputfield"); ?>
	     <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
	  </td>
	</tr> -->
</table> 
<div class="tabsystem">
     <?php for($i=0;$i<count($dataPemeriksaan);$i++){?>
     <div class="tabpage">
	  <h2><?php echo FormatFromTimeStamp($dataPemeriksaan[$i]["rawat_waktu"]);?></h2>
	  <iframe style="width:100%;height:100%;" marginwidth="0" marginheight="0" id="awglogin" name="awglogin" src="<?php echo $APLICATION_ROOT;?>module/rekap_medik/rm_rawatinap.php?rawat_id=<?php echo $enc->Encode($dataPemeriksaan[$i]["rawat_id"]); ?>" scrolling="auto" align="center" frameborder="0"></iframe>
     </div>
     <?php }?>
   <div class="tabpage tdefault">
  <h2>Pemeriksaan</h2>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="60%">


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
                                        <?php echo $view->RenderTextBox("rawat_suster_nama[]","rawat_suster_nama_0".$i,"30","100",$_POST["rawat_suster_nama"][$i],"inputField", "readonly",false);?>
                                        <?php //if($edit) {?>
                                             <a href="<?php echo $susterPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                                        <?php //}?>
                                        <input type="hidden" id="id_suster_<?php echo $i;?>" name="id_suster[]" value="<?php echo $_POST["id_suster"][$i];?>"/>
                                   </td>
                                   <td align="left" class="tablecontent-odd" width="30%">
                                        <?php// if($edit) {?>
                                             <?php if($i==0) { ?>
                                                  <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="SusterTambah();">
                                             <?php } else { ?>
                                                  <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="SusterDelete(<?php echo $i;?>);">
                                             <?php } ?>
                                        <?php// }?>
                                        <input name="suster_tot" id="suster_tot" type="hidden" value="<?php echo $n;?>">
                                   </td>
                              </tr>
                         <?php } ?>
                    <?php } ?>
                    <?php echo $view->RenderHidden("hid_suster_del","hid_suster_del",'');?>
				</table>
               </td>
          </tr>
	</table>
     </fieldset>

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
               <td width= "30%" align="left" class="tablecontent">Tanggal</td>
               <td width= "70%" align="left" class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("perawat_tanggal_kontrol","perawat_tanggal_kontrol","15","15",format_date($_POST["perawat_tanggal_kontrol"]),"inputfield"); ?>
	                <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               </td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent">Pukul</td>
               <td width= "70%" align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("pukul_jam","pukul_jam",$opt_jam,"inputField"); ?>&nbsp;:&nbsp;<?php echo $view->RenderComboBox("pukul_menit","pukul_menit",$opt_menit,"inputField"); ?></td>
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
               <td align="left" class="tablecontent">Suhu</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_suhu","rawat_lab_suhu","15","15",$_POST["rawat_lab_suhu"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pernafasan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_nafas","rawat_lab_nafas","15","15",$_POST["rawat_lab_nafas"],"inputField", null,false);?></td>
          </tr>
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
     <legend><strong>Upload Foto</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "50%" align="left" class="tablecontent">Gambar 1</td>
               <td width= "50%" align="left" class="tablecontent">Gambar 2</td>
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
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Gambar 1</button>
               </td>
               <td width= "50%" align="center">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUploadSketsa();">Upload Gambar 2</button>
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
      <legend><strong>Diagnosa Keperawatan</strong></legend>
    <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr>
	  <td align="left" class="tablecontent" width="20%">Diagnosa Keperawatan</td>
          <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextarea("rawat_diagnosa_keperawatan","rawat_diagnosa_keperawatan","5","50",$_POST["rawat_diagnosa_keperawatan"],"inputField", null,false);?></td>
        </tr>
    </table>

    <legend><strong>Observasi</strong></legend>
    <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr>
	  <td align="left" class="tablecontent" width="20%">Observasi</td>
          <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextarea("rawat_observasi","rawat_observasi","5","50",$_POST["rawat_observasi"],"inputField", null,false);?></td>
        </tr>
    </table>
     </fieldset>
   
   <!-- update 25-0914 -->
   <!-- modifying ICD & Procedures fields -->
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
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[0]","rawat_icd_od_kode_0","10","100",$_POST["rawat_icd_od_kode"][0],"inputField", null,false,"onkeyup=\"lihat(this.value);\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[1]","rawat_icd_od_kode_1","10","100",$_POST["rawat_icd_od_kode"][1],"inputField", null,false," onkeyup=\"lihat1(this.value)\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_icd_od_kode[2]","rawat_icd_od_kode_2","10","100",$_POST["rawat_icd_od_kode"][2],"inputField", null,false," onkeyup=\"lihat4(this.value)\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[0]","rawat_icd_os_kode_0","10","100",$_POST["rawat_icd_os_kode"][0],"inputField", null,false,"onkeyup=\"lihat2(this.value)\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[1]","rawat_icd_os_kode_1","10","100",$_POST["rawat_icd_os_kode"][1],"inputField", null,false,"onkeyup=\"lihat3(this.value)\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_icd_os_kode[2]","rawat_icd_os_kode_2","10","100",$_POST["rawat_icd_os_kode"][2],"inputField", null,false," onkeyup=\"lihat6(this.value)\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[0]","rawat_prosedur_kode_0","10","100",$_POST["rawat_prosedur_kode"][0],"inputField", null,false,"onkeyup=\"lookProc(this.value);\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[1]","rawat_prosedur_kode_1","10","100",$_POST["rawat_prosedur_kode"][0],"inputField", null,false,"onkeyup=\"lookProc1(this.value);\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[2]","rawat_prosedur_kode_2","10","100",$_POST["rawat_prosedur_kode"][2],"inputField", null,false,"onkeyup=\"lookProc2(this.value);\"");?>
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
                    <?php echo $view->RenderTextBox("rawat_prosedur_kode[3]","rawat_prosedur_kode_3","10","100",$_POST["rawat_prosedur_kode"][3],"inputField", null,false,"onkeyup=\"lookProc3(this.value);\"");?>
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
     <!-- end update -->
   
  
     <fieldset>
     <legend><strong>Terapi Obat</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
          <tr class="subheader">
               <td width="50%" align="center">Nama Obat</td>
               <td width="20%" align="center">Dosis</td>
               <td width="20%" align="center">Jumlah</td>
               <td width="10%" align="center">&nbsp;</td>
          </tr>	
          <?php if(!$_POST["item_nama"]) { ?>
               <tr  class="tablecontent-odd" id="tr_terapi_0">
                    <td align="left" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("item_nama[0]","item_nama_0","20","100",$_POST["item_nama"][0],"inputField", "readonly",false);?>
                         <a href="<?php echo $terapiPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" /></a>
                         <input type="hidden" name="item_id[0]" id="item_id_0" value="<?php echo $_POST["item_id"][0]?>" />                                  
                         <input type="hidden" name="id_petunjuk[0]" id="id_petunjuk_0" value="<?php echo $_POST["id_petunjuk"][0]?>" />                    
                    </td>
                    <td align="left" class="tablecontent-odd">
                    <input readonly size="21" maxlength="100" name="petunjuk_nama[0]" id="petunjuk_nama_0" value="<?php echo $_POST["petunjuk_nama"][0]?>" />
                    </td>
                    <td align="right" class="tablecontent-odd">
                    <input type="curedit" size="5" maxlength="100" name="jumlah[0]" id="jumlah_0" value="<?php echo currency_format($_POST["jumlah"][0])?>" />
                    </td>
                    <td align="center" class="tablecontent-odd">
                         <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="Tambah();">
                         <input name="hid_tot" id="hid_tot" type="hidden" value="0">
                    </td>                    
               </tr>
          <?php } else { ?>
               <?php for($i=0,$n=count($_POST["item_id"]);$i<$n;$i++) { ?>
                    <tr id="tr_terapi_<?php echo $i;?>">
                         <td align="left" class="tablecontent-odd">
                              <?php echo $view->RenderTextBox("item_nama[]","item_nama_".$i,"20","100",$_POST["item_nama"][$i],"inputField", "readonly",false);?>
                              <a href="<?php echo $terapiPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Obat"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Obat" alt="Cari Obat" /></a>
                              <input type="hidden" id="item_id_<?php echo $i;?>" name="item_id[]" value="<?php echo $_POST["item_id"][$i];?>"/>
                              <input type="hidden" id="id_petunjuk_<?php echo $i;?>" name="id_petunjuk[]" value="<?php echo $_POST["id_petunjuk"][$i];?>"/>                        
                         </td>
                         <td align="left" class="tablecontent-odd">
                              <input readonly size="35" maxlength="100" name="petunjuk_nama[]" id="petunjuk_nama_<?php echo $i;?>" value="<?php echo $_POST["petunjuk_nama"][$i]?>" />
                         </td>
                         <td align="right" class="tablecontent-odd">
                              <input type="curedit" size="5" maxlength="100" name="jumlah[]" id="jumlah_<?php echo $i;?>" value="<?php echo currency_format($_POST["jumlah"][$i])?>" />
                         </td>
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
     <legend><strong>Tindakan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_tindakan"> 
          <tr class="subheader">
               <td width="50%" align="center">Jenis Tindakan</td>
               <td width="30%" align="center">Biaya</td>
               <td width="10%" align="center">&nbsp;</td>
          </tr>	
          <?php if(!$_POST["biaya_nama"]) { ?>
               <tr  class="tablecontent-odd" id="tr_tindakan_0">
                    <td align="left" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("biaya_nama[0]","biaya_nama_0","20","100",$_POST["biaya_nama"][0],"inputField", "",false,"onChange=\"javascript:MasukkanTindakan(this.form,this.value);\"");?>
                         <a href="<?php echo $tindakanPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Tindakan"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" /></a>
                         <input type="hidden" name="biaya_id[0]" id="biaya_id_0" value="<?php echo $_POST["biaya_id"][0]?>" />                    
                         <input type="hidden" name="biaya_jenis[0]" id="biaya_jenis_0" value="<?php echo $_POST["biaya_jenis"][0]?>" />                    
                    </td>
                    <td align="right" class="tablecontent-odd">
                              <?php echo $view->RenderTextBox("biaya_total[0]","biaya_total_0","35","100",currency_format($_POST["biaya_total"][0]),"curedit", "readonly",true);?>
                    </td>
                    <td align="center" class="tablecontent-odd">
                         <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="Tambah1(0);">
                         <input name="hid_tot" id="hid_tot" type="hidden" value="0">
                    </td>                    
               </tr>
          <?php } else { ?>
               <?php for($i=0,$n=count($_POST["biaya_id"]);$i<$n;$i++) { ?>
                    <tr id="tr_tindakan_<?php echo $i;?>">
                         <td align="left" class="tablecontent-odd">
                              <?php echo $view->RenderTextBox("biaya_nama[]","biaya_nama_".$i,"20","100",$_POST["biaya_nama"][$i],"inputField", "readonly",false);?>
                              <a href="<?php echo $tindakanPage;?>&el=<?php echo $i;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Tindakan"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Tindakan" alt="Cari Tindakan" /></a>
                              <input type="hidden" id="biaya_id_<?php echo $i;?>" name="biaya_id[]" value="<?php echo $_POST["biaya_id"][$i];?>"/>
                              <input type="hidden" id="biaya_jenis_<?php echo $i;?>" name="biaya_jenis[]" value="<?php echo $_POST["biaya_jenis"][$i];?>"/>                         
                         </td>
                         <td align="right" class="tablecontent-odd">
                              <?php echo $view->RenderTextBox("biaya_total[]","biaya_total_".$i,"35","100",currency_format($_POST["biaya_total"][$i]),"curedit", "readonly",true);?>
                         </td>
                        
                         <td align="left" class="tablecontent-odd" width="30%">
                              <?php if($i==0) { ?>
                              <input class="button" name="btnAdd" id="btnAdd" type="button" value="Tambah" onClick="Tambah1(<?php echo $i;?>);">
                              <?php } else { ?>
                              <input class="button" name="btnDel[<?php echo $i;?>]" id="btnDel_<?php echo $i;?>" type="button" value="Hapus" onClick="Delete1(<?php echo $i;?>);">
                              <?php } ?>
                              <input name="hid_tot" id="hid_tot" type="hidden" value="<?php echo $n;?>">
                         </td>
                    </tr>
               <?php } ?>
          <?php } ?>
     </table>
     </fieldset>
	

<!--
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
	</table>
     </fieldset>

-->
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
	       <td align="left" width="30%" class="tablecontent">Acara Hari Ini</td>
               <td align="left" width="20%"><?php echo $view->RenderComboBox("cmbNext","cmbNext",$optionsNext,null,null,null);?></td>
	  </tr>
	  <tr>
	       <td colspan="2" align="left" class="tablecontent-odd">
               <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" id="btnSave" value="Simpan" class="button"/>
          </td>
    </tr>
	</table>
     </fieldset>

     </td>
         <td width="40%" height="100%" valign="top">
	  <table width="100%" border="0" cellpadding="4" cellspacing="1">
	       <tr>
		    <td width="5%" style="font-size:20px;font-weight:bold;vertical-align: middle;">S</td>
		    <td><textarea cols="50" rows="25"></textarea></td>
	       </tr>
	       <tr>
		    <td width="5%" style="font-size:20px;font-weight:bold;vertical-align: middle;">O</td>
		    <td><textarea cols="50" rows="25"></textarea></td>
	       </tr>
	       <tr>
		    <td width="5%" style="font-size:20px;font-weight:bold;vertical-align: middle;">A</td>
		    <td><textarea cols="50" rows="25"></textarea></td>
	       </tr>
	       <tr>
		    <td width="5%" style="font-size:20px;font-weight:bold;vertical-align: middle;">P</td>
		    <td><textarea cols="50" rows="25"></textarea></td>
	       </tr>
	  </table>
	  <!--<iframe style="width:100%;height:100%;" marginwidth="0" marginheight="0" id="ifrmDiag" name="ifrmDiag" src="<?php echo $pemeriksaanLink;?>" scrolling="auto" align="center" frameborder="0"></iframe>-->
	 </td>
</tr>	

</table>


<?php echo $view->SetFocus("rawat_keluhan");?>

<input type="hidden" name="_x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"];?>"/>
<input type="hidden" name="cust_usr_jenis" value="<?php echo $_POST["cust_usr_jenis"];?>"/>
<?php echo $view->RenderHidden("hid_id_del","hid_id_del",'');?>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? }  ?>
</span>
</div>
<?php if(!$dataPasien["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>Kode Pasien Tidak Ditemukan</strong></font>
<?php } ?>
   
 
</div>
</form>

<script>
    Calendar.setup({
        inputField     :    "perawat_tanggal_kontrol",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
</form>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
