<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $thisPage = "report_point.php";

     if(!$auth->IsAllowed("report_point_pegawai",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_point_pegawai",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y"); 
 
     if(!$_POST["ruang_proses"] || $_POST["ruang_proses"]==STATUS_REFRAKSI) {
          
          //--- untuk cari point pegawai di refaksi----  
          if($_POST["tgl_awal"]) $sql_ref[] = "ref_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));  
          if($_POST["tgl_akhir"]) $sql_ref[] = "ref_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
          
          $sql = " select id_ref, id_pgw, ref_tanggal 
                         from klinik.klinik_refraksi a
                         join klinik.klinik_refraksi_suster b on b.id_ref = a.ref_id ";
          $sql.= " where ".implode(" and ",$sql_ref);
          $sql .= " order by ref_tanggal, id_ref, id_pgw "; 
          $rs = $dtaccess->Execute($sql);
          $RefSuster = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($RefSuster);$i<$n;$i++) {
               if($RefSuster[$i]["id_ref"]!=$RefSuster[$i-1]["id_ref"]) { 
                    $RefSusTgl[$RefSuster[$i]["ref_tanggal"]]++;
               } 
               
               $RefSusPgw[$RefSuster[$i]["ref_tanggal"]][$RefSuster[$i]["id_pgw"]]++;
               
               if($RefSusPgw[$RefSuster[$i]["ref_tanggal"]][$RefSuster[$i]["id_pgw"]]==1) {
                    $RefSusTotPgw[$RefSuster[$i]["ref_tanggal"]]++; 
               }
               
               $point[$RefSuster[$i]["id_pgw"]][$RefSuster[$i]["ref_tanggal"]] = $RefSusTgl[$RefSuster[$i]["ref_tanggal"]]/$RefSusTotPgw[$RefSuster[$i]["ref_tanggal"]];
               $ruang[$RefSuster[$i]["id_pgw"]] = $ruangProses[STATUS_REFRAKSI]; 
          }

          $sql = " select count(id_ref) as total, id_pgw, ref_tanggal
                         from klinik.klinik_refraksi a
                         join klinik.klinik_refraksi_admin b on b.id_ref = a.ref_id ";
          $sql .= " where ".implode(" and ",$sql_ref);
          $sql .= " group by id_pgw, ref_tanggal  "; 
          $rs = $dtaccess->Execute($sql);
          $RefAdmin = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($RefAdmin);$i<$n;$i++) { 
               $point[$RefAdmin[$i]["id_pgw"]][$RefAdmin[$i]["ref_tanggal"]] += $RefAdmin[$i]["total"];
               $ruang[$RefAdmin[$i]["id_pgw"]] = $ruangProses[STATUS_REFRAKSI];
          }
     }
      
     
     if(!$_POST["ruang_proses"] || $_POST["ruang_proses"]==STATUS_PEMERIKSAAN) {
          
          //--- untuk cari point pegawai di pemeriksaan -----
          if($_POST["tgl_awal"]) $sql_rawat[] = "rawat_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));  
          if($_POST["tgl_akhir"]) $sql_rawat[] = "rawat_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
          
          $sql = " select id_rawat, id_pgw, rawat_tanggal
                         from klinik.klinik_perawatan a
                         join klinik.klinik_perawatan_suster b on b.id_rawat = a.rawat_id ";
          $sql.= " where ".implode(" and ",$sql_rawat);
          $sql .= " order by rawat_tanggal, id_rawat, id_pgw "; 
          $rs = $dtaccess->Execute($sql);
          $RawatSuster = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($RawatSuster);$i<$n;$i++) {
               if($RawatSuster[$i]["id_rawat"]!=$RawatSuster[$i-1]["id_rawat"]) { 
                    $rawatSusTgl[$RawatSuster[$i]["rawat_tanggal"]]++;
               } 
               
               $rawatSusPgw[$RawatSuster[$i]["rawat_tanggal"]][$RawatSuster[$i]["id_pgw"]]++;
               
               if($rawatSusPgw[$RawatSuster[$i]["rawat_tanggal"]][$RawatSuster[$i]["id_pgw"]]==1) {
                    $rawatSusTotPgw[$RawatSuster[$i]["rawat_tanggal"]]++; 
               }
               
               $point[$RawatSuster[$i]["id_pgw"]][$RawatSuster[$i]["rawat_tanggal"]] = $rawatSusTgl[$RawatSuster[$i]["rawat_tanggal"]]/$rawatSusTotPgw[$RawatSuster[$i]["rawat_tanggal"]];
               $ruang[$RawatSuster[$i]["id_pgw"]] = $ruangProses[STATUS_PEMERIKSAAN];
          }
          
          $sql = " select count(id_rawat) as total, id_pgw, rawat_tanggal
                         from klinik.klinik_perawatan a
                         join klinik.klinik_perawatan_dokter b on b.id_rawat = a.rawat_id ";
          $sql.= " where ".implode(" and ",$sql_rawat);
          $sql .= " group by id_pgw, rawat_tanggal  "; 
          $rs = $dtaccess->Execute($sql);
          $RawatDokter = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($RawatDokter);$i<$n;$i++) { 
               $point[$RawatDokter[$i]["id_pgw"]][$RawatDokter[$i]["rawat_tanggal"]] += $RawatDokter[$i]["total"];
               $ruang[$RawatDokter[$i]["id_pgw"]] = $ruangProses[STATUS_PEMERIKSAAN];
          }


          $sql = " select count(id_rawat) as total, id_pgw, rawat_tanggal
                         from klinik.klinik_perawatan a
                         join klinik.klinik_perawatan_admin b on b.id_rawat = a.rawat_id ";
          $sql.= " where ".implode(" and ",$sql_rawat);
          $sql .= " group by id_pgw, rawat_tanggal  "; 
          $rs = $dtaccess->Execute($sql);
          $RawatAdmin = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($RawatAdmin);$i<$n;$i++) { 
               $point[$RawatAdmin[$i]["id_pgw"]][$RawatAdmin[$i]["rawat_tanggal"]] += $RawatAdmin[$i]["total"];
               $ruang[$RawatAdmin[$i]["id_pgw"]] = $ruangProses[STATUS_PEMERIKSAAN];
          }
     }
     
     if(!$_POST["ruang_proses"] || $_POST["ruang_proses"]==STATUS_DIAGNOSTIK) {
          //--- untuk cari point pegawai di diagnostik -----
          if($_POST["tgl_awal"]) $sql_diag[] = "cast(diag_waktu as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));  
          if($_POST["tgl_akhir"]) $sql_diag[] = "cast(diag_waktu as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
          
          $sql = " select id_diag, id_pgw, cast(diag_waktu as date) as diag_tanggal 
                         from klinik.klinik_diagnostik a
                         join klinik.klinik_diagnostik_suster b on b.id_diag = a.diag_id ";
          $sql.= " where ".implode(" and ",$sql_diag);
          $sql .= " order by cast(diag_waktu as date), id_diag, id_pgw "; 
          $rs = $dtaccess->Execute($sql);
          $DiagSuster = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($DiagSuster);$i<$n;$i++) {
               if($DiagSuster[$i]["id_diag"]!=$DiagSuster[$i-1]["id_diag"]) { 
                    $DiagSusTgl[$DiagSuster[$i]["diag_tanggal"]]++;
               } 
               
               $DiagSusPgw[$DiagSuster[$i]["diag_tanggal"]][$DiagSuster[$i]["id_pgw"]]++;
               
               if($DiagSusPgw[$DiagSuster[$i]["diag_tanggal"]][$DiagSuster[$i]["id_pgw"]]==1) {
                    $DiagSusTotPgw[$DiagSuster[$i]["diag_tanggal"]]++; 
               }
               
               $point[$DiagSuster[$i]["id_pgw"]][$DiagSuster[$i]["diag_tanggal"]] = $DiagSusTgl[$DiagSuster[$i]["diag_tanggal"]]/$DiagSusTotPgw[$DiagSuster[$i]["diag_tanggal"]];
               $ruang[$DiagSuster[$i]["id_pgw"]] = $ruangProses[STATUS_DIAGNOSTIK];
               
          }
          
          $sql = " select count(id_diag) as total, id_pgw, cast(diag_waktu as date) as diag_tanggal 
                         from klinik.klinik_diagnostik a
                         join klinik.klinik_diagnostik_dokter b on b.id_diag = a.diag_id ";
          $sql.= " where ".implode(" and ",$sql_diag);
          $sql .= " group by id_pgw, cast(diag_waktu as date)  "; 
          $rs = $dtaccess->Execute($sql);
          $DiagDokter = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($DiagDokter);$i<$n;$i++) { 
               $point[$DiagDokter[$i]["id_pgw"]][$DiagDokter[$i]["diag_tanggal"]] += $DiagDokter[$i]["total"];
               $ruang[$DiagDokter[$i]["id_pgw"]] = $ruangProses[STATUS_DIAGNOSTIK];
          }

          $sql = " select count(id_diag) as total, id_pgw, cast(diag_waktu as date) as diag_tanggal
                         from klinik.klinik_diagnostik a
                         join klinik.klinik_diagnostik_admin b on b.id_diag = a.diag_id ";
          $sql.= " where ".implode(" and ",$sql_diag);
          $sql .= " group by id_pgw, cast(diag_waktu as date)"; 
          $rs = $dtaccess->Execute($sql);
          $DiagAdmin = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($DiagAdmin);$i<$n;$i++) { 
               $point[$DiagAdmin[$i]["id_pgw"]][$DiagAdmin[$i]["diag_tanggal"]] += $DiagAdmin[$i]["total"];
               $ruang[$DiagAdmin[$i]["id_pgw"]] = $ruangProses[STATUS_DIAGNOSTIK];
          }
     }
     
     if(!$_POST["ruang_proses"] || $_POST["ruang_proses"]==STATUS_PREOP) {
          //--- untuk cari point pegawai di preop -----
          if($_POST["tgl_awal"]) $sql_preop[] = "cast(preop_waktu as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));  
          if($_POST["tgl_akhir"]) $sql_preop[] = "cast(preop_waktu as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
          
          $sql = " select id_preop, id_pgw, cast(preop_waktu as date) as preop_tanggal 
                         from klinik.klinik_preop a
                         join klinik.klinik_preop_suster b on b.id_preop = a.preop_id ";
          $sql.= " where ".implode(" and ",$sql_preop);
          $sql .= " order by cast(preop_waktu as date), id_preop, id_pgw "; 
          $rs = $dtaccess->Execute($sql);
          $PreopSuster = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($PreopSuster);$i<$n;$i++) {
               if($PreopSuster[$i]["id_preop"]!=$PreopSuster[$i-1]["id_preop"]) { 
                    $PreopSusTgl[$PreopSuster[$i]["preop_tanggal"]]++;
               } 
               
               $PreopSusPgw[$PreopSuster[$i]["preop_tanggal"]][$PreopSuster[$i]["id_pgw"]]++;
               
               if($PreopSusPgw[$PreopSuster[$i]["preop_tanggal"]][$PreopSuster[$i]["id_pgw"]]==1) {
                    $PreopSusTotPgw[$PreopSuster[$i]["preop_tanggal"]]++; 
               }
               
               $point[$PreopSuster[$i]["id_pgw"]][$PreopSuster[$i]["preop_tanggal"]] = $PreopSusTgl[$PreopSuster[$i]["preop_tanggal"]]/$PreopSusTotPgw[$PreopSuster[$i]["preop_tanggal"]];
               $ruang[$PreopSuster[$i]["id_pgw"]] = $ruangProses[STATUS_PREOP];
          }
          
          $sql = " select count(id_preop) as total, id_pgw, cast(preop_waktu as date) as preop_tanggal 
                         from klinik.klinik_preop a
                         join klinik.klinik_preop_dokter b on b.id_preop = a.preop_id ";
          $sql.= " where ".implode(" and ",$sql_preop);
          $sql .= " group by id_pgw, cast(preop_waktu as date)  "; 
          $rs = $dtaccess->Execute($sql);
          $PreopDokter = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($PreopDokter);$i<$n;$i++) { 
               $point[$PreopDokter[$i]["id_pgw"]][$PreopDokter[$i]["preop_tanggal"]] += $PreopDokter[$i]["total"];
               $ruang[$PreopDokter[$i]["id_pgw"]] = $ruangProses[STATUS_PREOP]; 
          }

          $sql = " select count(id_preop) as total, id_pgw, cast(preop_waktu as date) as preop_tanggal
                         from klinik.klinik_preop a
                         join klinik.klinik_preop_admin b on b.id_preop = a.preop_id ";
          $sql.= " where ".implode(" and ",$sql_preop);
          $sql .= " group by id_pgw, cast(preop_waktu as date)"; 
          $rs = $dtaccess->Execute($sql);
          $PreopAdmin = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($PreopAdmin);$i<$n;$i++) { 
               $point[$PreopAdmin[$i]["id_pgw"]][$PreopAdmin[$i]["preop_tanggal"]] += $PreopAdmin[$i]["total"];
               $ruang[$PreopAdmin[$i]["id_pgw"]] = $ruangProses[STATUS_PREOP];
          }
     }
     
     if(!$_POST["ruang_proses"] || $_POST["ruang_proses"]==STATUS_PREMEDIKASI) {
          //--- untuk cari point pegawai di premedikasi -----
          if($_POST["tgl_awal"]) $sql_preme[] = "cast(preme_waktu as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));  
          if($_POST["tgl_akhir"]) $sql_preme[] = "cast(preme_waktu as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
          
          $sql = " select id_preme, id_pgw, cast(preme_waktu as date) as preme_tanggal 
                         from klinik.klinik_premedikasi a
                         join klinik.klinik_premedikasi_suster b on b.id_preme = a.preme_id ";
          $sql.= " where ".implode(" and ",$sql_preme);
          $sql .= " order by cast(preme_waktu as date), id_preme, id_pgw "; 
          $rs = $dtaccess->Execute($sql);
          $PremeSuster = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($PremeSuster);$i<$n;$i++) {
               if($PremeSuster[$i]["id_preme"]!=$PremeSuster[$i-1]["id_preme"]) { 
                    $PremeSusTgl[$PremeSuster[$i]["preme_tanggal"]]++;
               } 
               
               $PremeSusPgw[$PremeSuster[$i]["preme_tanggal"]][$PremeSuster[$i]["id_pgw"]]++;
               
               if($PremeSusPgw[$PremeSuster[$i]["preme_tanggal"]][$PremeSuster[$i]["id_pgw"]]==1) {
                    $PremeSusTotPgw[$PremeSuster[$i]["preme_tanggal"]]++; 
               }
               
               $point[$PremeSuster[$i]["id_pgw"]][$PremeSuster[$i]["preme_tanggal"]] = $PremeSusTgl[$PremeSuster[$i]["preme_tanggal"]]/$PremeSusTotPgw[$PremeSuster[$i]["preme_tanggal"]];
               $ruang[$PremeSuster[$i]["id_pgw"]] = $ruangProses[STATUS_PREMEDIKASI];
               
          }
          
          $sql = " select count(id_preme) as total, id_pgw, cast(preme_waktu as date) as preme_tanggal 
                         from klinik.klinik_premedikasi a
                         join klinik.klinik_premedikasi_dokter b on b.id_preme = a.preme_id ";
          $sql.= " where ".implode(" and ",$sql_preme);
          $sql .= " group by id_pgw, cast(preme_waktu as date)  "; 
          $rs = $dtaccess->Execute($sql);
          $PremeDokter = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($PremeDokter);$i<$n;$i++) { 
               $point[$PremeDokter[$i]["id_pgw"]][$PremeDokter[$i]["preme_tanggal"]] += $PremeDokter[$i]["total"];
               $ruang[$PremeDokter[$i]["id_pgw"]] = $ruangProses[STATUS_PREMEDIKASI]; 
          }

          $sql = " select count(id_preme) as total, id_pgw, cast(preme_waktu as date) as preme_tanggal 
                         from klinik.klinik_premedikasi a
                         join klinik.klinik_premedikasi_admin b on b.id_preme = a.preme_id ";
          $sql.= " where ".implode(" and ",$sql_preme);
          $sql .= " group by id_pgw, cast(preme_waktu as date)  "; 
          $rs = $dtaccess->Execute($sql);
          $PremeAdmin = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($PremeAdmin);$i<$n;$i++) { 
               $point[$PremeAdmin[$i]["id_pgw"]][$PremeAdmin[$i]["preme_tanggal"]] += $PremeAdmin[$i]["total"];
               $ruang[$PremeAdmin[$i]["id_pgw"]] = $ruangProses[STATUS_PREMEDIKASI]; 
          }
     }
     
     if(!$_POST["ruang_proses"] || $_POST["ruang_proses"]==STATUS_OPERASI) {
          
          //--- untuk cari point pegawai di operasi -----
          if($_POST["tgl_awal"]) $sql_op[] = "cast(op_waktu as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));  
          if($_POST["tgl_akhir"]) $sql_op[] = "cast(op_waktu as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
          
          $sql = " select id_op, id_pgw, cast(op_waktu as date) as op_tanggal 
                         from klinik.klinik_operasi a
                         join klinik.klinik_operasi_suster b on b.id_op = a.op_id ";
          $sql.= " where ".implode(" and ",$sql_op);
          $sql .= " order by cast(op_waktu as date), id_op, id_pgw "; 
          $rs = $dtaccess->Execute($sql);
          $OpSuster = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($OpSuster);$i<$n;$i++) {
               if($OpSuster[$i]["id_op"]!=$OpSuster[$i-1]["id_op"]) { 
                    $OpSusTgl[$OpSuster[$i]["op_tanggal"]]++;
               } 
               
               $OpSusPgw[$OpSuster[$i]["op_tanggal"]][$OpSuster[$i]["id_pgw"]]++;
               
               if($OpSusPgw[$OpSuster[$i]["op_tanggal"]][$OpSuster[$i]["id_pgw"]]==1) {
                    $OpSusTotPgw[$OpSuster[$i]["op_tanggal"]]++; 
               }
               
               $point[$OpSuster[$i]["id_pgw"]][$OpSuster[$i]["op_tanggal"]] = $OpSusTgl[$OpSuster[$i]["op_tanggal"]]/$OpSusTotPgw[$OpSuster[$i]["op_tanggal"]];
               $ruang[$OpSuster[$i]["id_pgw"]] = $ruangProses[STATUS_OPERASI];
          }
          
          $sql = " select count(id_op) as total, id_pgw, cast(op_waktu as date) as op_tanggal 
                         from klinik.klinik_operasi a
                         join klinik.klinik_operasi_dokter b on b.id_op = a.op_id ";
          $sql.= " where ".implode(" and ",$sql_op);
          $sql .= " group by id_pgw,cast(op_waktu as date)  "; 
          $rs = $dtaccess->Execute($sql);
          $OpDokter = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($OpDokter);$i<$n;$i++) { 
               $point[$OpDokter[$i]["id_pgw"]][$OpDokter[$i]["op_tanggal"]] += $OpDokter[$i]["total"];
               $ruang[$OpDokter[$i]["id_pgw"]] = $ruangProses[STATUS_OPERASI];
          }

          $sql = " select count(id_op) as total, id_pgw, cast(op_waktu as date) as op_tanggal 
                         from klinik.klinik_operasi a
                         join klinik.klinik_operasi_admin b on b.id_op = a.op_id ";
          $sql.= " where ".implode(" and ",$sql_op);
          $sql .= " group by id_pgw,cast(op_waktu as date)  "; 
          $rs = $dtaccess->Execute($sql);
          $OpAdmin = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($OpAdmin);$i<$n;$i++) { 
               $point[$OpAdmin[$i]["id_pgw"]][$OpAdmin[$i]["op_tanggal"]] += $OpAdmin[$i]["total"];
               $ruang[$OpAdmin[$i]["id_pgw"]] = $ruangProses[STATUS_OPERASI];
          }
     }
       
     if(!$_POST["ruang_proses"] || $_POST["ruang_proses"]==STATUS_BEDAH) {
          //--- untuk cari point pegawai di bedah minor -----
          if($_POST["tgl_awal"]) $sql_bedah[] = "op_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));  
          if($_POST["tgl_akhir"]) $sql_bedah[] = "op_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
          
          $sql = " select op_id, id_dokter, id_suster, op_tanggal 
                         from klinik.klinik_perawatan_operasi a ";
          $sql.= " where ".implode(" and ",$sql_bedah);
          $sql .= " order by id_suster, id_dokter, op_tanggal  "; 
          $rs = $dtaccess->Execute($sql); 
          $BedahDokter = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($BedahDokter);$i<$n;$i++) { 
               $point[$BedahDokter[$i]["id_dokter"]][$BedahDokter[$i]["op_tanggal"]]++;
               $ruang[$BedahDokter[$i]["id_dokter"]] = $ruangProses[STATUS_BEDAH];
               
               $point[$BedahDokter[$i]["id_suster"]][$BedahDokter[$i]["op_tanggal"]]++; 
               $ruang[$BedahDokter[$i]["id_suster"]] = $ruangProses[STATUS_BEDAH];
          }

          $sql = " select count(id_op) as total, id_pgw, op_tanggal 
                         from klinik.klinik_perawatan_operasi a
                         join klinik.klinik_bedah_admin b on b.id_op = a.op_id ";
          $sql.= " where ".implode(" and ",$sql_bedah);
          $sql .= " group by id_pgw,op_tanggal  "; 
          $rs = $dtaccess->Execute($sql);
          $BedahAdmin = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($BedahAdmin);$i<$n;$i++) { 
               $point[$BedahAdmin[$i]["id_pgw"]][$BedahAdmin[$i]["op_tanggal"]] += $BedahAdmin[$i]["total"];
               $ruang[$BedahAdmin[$i]["id_pgw"]] = $ruangProses[STATUS_OPERASI];
          }
          
     }
     
     //--- untuk cari data pegawai ----- 
     if($_POST["pgw_jenis_pegawai"]) $sql_where[] = "pgw_jenis_pegawai = ".QuoteValue(DPE_NUMERIC,$_POST["pgw_jenis_pegawai"]);
     if($_POST["pgw_kode"]) $sql_where[] = "pgw_nip = ".QuoteValue(DPE_CHAR,$_POST["pgw_kode"]);
     
     $sql = " select pgw_id, pgw_nip, pgw_nama, pgw_jenis_pegawai 
                    from hris.hris_pegawai a ";
                    
     if($sql_where) { 
          $sql.= " where ".implode(" and ",$sql_where);    
     }
     $sql .= " order by pgw_jenis_pegawai,pgw_nip "; 
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     $totHari = DateDiff($_POST["tgl_awal"],$_POST["tgl_akhir"])+1;
     
     for($i=0,$mulai=0,$n=$totHari;$i<$n;$i++,$mulai++) {
          $_POST["tanggal"][$i] = DateAdd(date_db($_POST["tgl_awal"]),$mulai); 
     }
     
     $row = -1;
     for($a=0,$n=count($dataTable);$a<$n;$a++) {
          for($i=0,$m=count($_POST["tanggal"]);$i<$m;$i++){ 
               if($point[$dataTable[$a]["pgw_id"]][$_POST["tanggal"][$i]]) { 
                    $hsl[$dataTable[$a]["pgw_id"]]++; 
                    if($hsl[$dataTable[$a]["pgw_id"]]==1) {
                         $row++;
                         $id[$row] = $dataTable[$a]["pgw_id"];
                         $nama[$row] = $dataTable[$a]["pgw_nama"];
                         $nip[$row] = $dataTable[$a]["pgw_nip"];
                         $jenis[$row] = $dataTable[$a]["pgw_jenis_pegawai"];
                    }
               }
          } 
     }  
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Point Pegawai"; 
     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%"; 
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = 2;    
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "NIP";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = 2;    
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pegawai";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";    
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = 2;       
     $counterHeader++;
        
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";    
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = 2;       
     $counterHeader++;
        
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail Point";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = $totHari;    
     $counterHeader++;
     
     $width = 50/$totHari;
     for($i=0,$countHeader=0,$n=count($_POST["tanggal"]);$i<$n;$i++) {
          $tbHeader[1][$countHeader][TABLE_ISI] = format_date($_POST["tanggal"][$i]);
          $tbHeader[1][$countHeader][TABLE_WIDTH] = $width."%"; 
          $countHeader++;
     }
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = 2;    
     $counterHeader++;
     
     if(!$_POST["ruang_proses"]) { 
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Ruang";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
          $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = 2;     
          $counterHeader++;
     } 
     
     for($i=0,$counter=0,$n=$row;$i<=$n;$i++,$counter=0){ 
          $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $nip[$i];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $jenisPegawai[$jenis[$i]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $nama[$i];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++; 
          
          for($a=0,$m=count($_POST["tanggal"]);$a<$m;$a++) {
               
               $tbContent[$i][$counter][TABLE_ISI] = $point[$id[$i]][$_POST["tanggal"][$a]]?currency_format($point[$id[$i]][$_POST["tanggal"][$a]],2):0;
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;
               $totalPoint[$id[$i]] += $point[$id[$i]][$_POST["tanggal"][$a]];
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($totalPoint[$id[$i]],2);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++; 
          
          if(!$_POST["ruang_proses"]) { 
               $tbContent[$i][$counter][TABLE_ISI] = $ruang[$id[$i]];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;
          }
     }
     
     $colspan = count($tbHeader[0])+$totHari-1;
     
     if(!$_POST["btnExcel"]){
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
          $tbBottom[0][0][TABLE_ALIGN] = "center";
     }
     
     if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_surat_sakit_'.$_POST["tgl_awal"].'.xls');
     }

?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>
<script language="JavaScript">
function CheckSimpan(frm) { 

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }
     
     if(!CheckDate(frm.tgl_akhir.value)) {
          return false;
     }
}

</script>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<?php if(!$_POST["btnExcel"]) { ?>
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <!-- <tr>
          <td width="15%" class="tablecontent">&nbsp;Kode Pegawai</td>
          <td width="35%" class="tablecontent-odd">
               <?php echo $view->RenderTextBox("pgw_kode","pgw_kode","15","30",$_POST["pgw_kode"],null);?>
          </td>  
     </tr> -->
     <tr>
          <td align="left" class="tablecontent" width="15%">&nbsp;Ruang Proses</td>
          <td width="85%" class="tablecontent-odd"> 
               <select name="ruang_proses" id="biaya_pasien_jenis" onKeyDown="return tabOnEnter(this, event);">
                    <option value="" >[ Pilih Ruang Proses ]</option>
                    <?php foreach($ruangProses as $key => $value) { ?> 
                              <option value="<?php echo $key;?>" <?php if($_POST["ruang_proses"]==$key) echo "selected";?>><?php echo $value;?></option>
                    <?php } ?>
               </select>
          </td>
     </tr> 
     <tr>
          <td align="left" class="tablecontent" width="15%">&nbsp;Jenis Pegawai</td>
          <td width="85%" class="tablecontent-odd"> 
               <select name="pgw_jenis_pegawai" id="biaya_pasien_jenis" onKeyDown="return tabOnEnter(this, event);">
                    <option value="" >[ Pilih Jenis Pegawai ]</option>
                    <?php foreach($jenisPegawai as $key => $value) { ?>
                         <option value="<?php echo $key;?>" <?php if($_POST["pgw_jenis_pegawai"]==$key) echo "selected";?>><?php echo $value;?></option>
                    <?php } ?>
               </select>
          </td>
     </tr> 
     <tr>
          <td width="15%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="35%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               &nbsp;&nbsp;- &nbsp;&nbsp;
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td> 
     </tr>
     <tr> 
          <td class="tablecontent" colspan="2">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>
</table>
<?php } ?>
<br>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</form>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<?php echo $view->RenderBodyEnd(); ?>
