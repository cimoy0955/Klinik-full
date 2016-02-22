<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $thisPage = "report_pasien.php";

     if(!$auth->IsAllowed("report_registrasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_registrasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $skr = date("d-m-Y");
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr;
     
     //$sql_where[] = "1=1";
     if($_POST["tgl_awal"]) $sql_where[] = "a.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "a.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
    $sql = "select count(reg_id) as total from klinik.klinik_registrasi a";

     // === nyari jumlah pasien baru rawat jalan ----
     $sql_where_baru[] = implode(" and ",$sql_where);
     $sql_where_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_baru[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlBaru = $sql." where ".implode(" and ",$sql_where_baru); 
     $dataPasien["jalan"][PASIEN_BARU] = $dtaccess->Fetch($sqlBaru);
     // -- end ---

     // === nyari jumlah pasien lama rawat jalan ----
     $sql_where_lama[] = implode(" and ",$sql_where);
     $sql_where_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_lama[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlLama = $sql." where ".implode(" and ",$sql_where_lama);
     $dataPasien["jalan"][PASIEN_LAMA] = $dtaccess->Fetch($sqlLama);
     // -- end ---
     //echo $sqlBaru."&nbsp;".$sqlLama;
     // === nyari jumlah pasien baru rawat jalan ----
     $sql_where_jamkesda_baru[] = implode(" and ",$sql_where);
     $sql_where_jamkesda_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_jamkesda_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_PNS);
     $sql_where_jamkesda_baru[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlJamkesdaBaru = $sql." where ".implode(" and ",$sql_where_jamkesda_baru); 
     $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_BPJS_PNS] = $dtaccess->Fetch($sqlJamkesdaBaru);
     
     // -- end ---

     // === nyari jumlah pasien lama rawat jalan ----
     $sql_where_jamkesda_lama[] = implode(" and ",$sql_where);
     $sql_where_jamkesda_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_jamkesda_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_PNS);
     $sql_where_jamkesda_lama[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlJamkesdaLama = $sql." where ".implode(" and ",$sql_where_jamkesda_lama);
     $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_PNS] = $dtaccess->Fetch($sqlJamkesdaLama);
     // -- end ---
     
     // === nyari jumlah pasien baru rawat jalan ----
     $sql_where_jamkesmas_baru[] = implode(" and ",$sql_where);
     $sql_where_jamkesmas_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_jamkesmas_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_ASTEK);
     $sql_where_jamkesmas_baru[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlJamkesmasBaru = $sql." where ".implode(" and ",$sql_where_jamkesmas_baru); 
     $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_BPJS_ASTEK] = $dtaccess->Fetch($sqlJamkesmasBaru);
     // -- end ---

     // === nyari jumlah pasien lama rawat jalan ----
     $sql_where_jamkesmas_lama[] = implode(" and ",$sql_where);
     $sql_where_jamkesmas_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_jamkesmas_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_ASTEK);
     $sql_where_jamkesmas_lama[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlJamkesmasLama = $sql." where ".implode(" and ",$sql_where_jamkesmas_lama);
     $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_ASTEK] = $dtaccess->Fetch($sqlJamkesmasLama);
     // -- end ---
     
     // === nyari jumlah pasien baru rawat jalan ----
     $sql_where_jamkesmaskota_baru[] = implode(" and ",$sql_where);
     $sql_where_jamkesmaskota_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_jamkesmaskota_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_JAMKESMAS);
     $sql_where_jamkesmaskota_baru[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlJamkesmaskotaBaru = $sql." where ".implode(" and ",$sql_where_jamkesmaskota_baru); 
     $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_BPJS_JAMKESMAS] = $dtaccess->Fetch($sqlJamkesmaskotaBaru);
     // -- end ---

     // === nyari jumlah pasien lama rawat jalan ----
     $sql_where_jamkesmaskota_lama[] = implode(" and ",$sql_where);
     $sql_where_jamkesmaskota_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_jamkesmaskota_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_JAMKESMAS);
     $sql_where_jamkesmaskota_lama[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlJamkesmaskotaLama = $sql." where ".implode(" and ",$sql_where_jamkesmaskota_lama);
     $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_JAMKESMAS] = $dtaccess->Fetch($sqlJamkesmaskotaLama);
     // -- end ---
     
      // === nyari jumlah pasien baru rawat jalan ----
     $sql_where_askes_baru[] = implode(" and ",$sql_where);
     $sql_where_askes_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_askes_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESDA_PROV);
     $sql_where_askes_baru[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlAskesBaru = $sql." where ".implode(" and ",$sql_where_askes_baru); 
     $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_PROV] = $dtaccess->Fetch($sqlAskesBaru);
     // -- end ---
     
     // === nyari jumlah pasien lama rawat jalan ----
     $sql_where_askes_lama[] = implode(" and ",$sql_where);
     $sql_where_askes_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_askes_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESDA_PROV);
     $sql_where_askes_lama[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlAskesLama = $sql." where ".implode(" and ",$sql_where_askes_lama);
     $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_PROV] = $dtaccess->Fetch($sqlAskesLama);
     // -- end ---
     
     // === nyari jumlah pasien baru rawat jalan ----
     $sql_where_pns_baru[] = implode(" and ",$sql_where);
     $sql_where_pns_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_pns_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESDA_KAB);
     $sql_where_pns_baru[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlPNSBaru = $sql." where ".implode(" and ",$sql_where_pns_baru); 
     $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_KAB] = $dtaccess->Fetch($sqlPNSBaru);
     // -- end ---

     // === nyari jumlah pasien lama rawat jalan ----
     $sql_where_pns_lama[] = implode(" and ",$sql_where);
     $sql_where_pns_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_pns_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESDA_KAB);
     $sql_where_pns_lama[] = "(a.reg_tipe_rawat in ('M','D','U')  or a.reg_tipe_rawat is null)";
     $sqlPNSLama = $sql." where ".implode(" and ",$sql_where_pns_lama);
     $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_KAB] = $dtaccess->Fetch($sqlPNSLama);
     // -- end ---
     
     // === nyari jumlah pasien baru rawat inap ----
     $sql_where_baru_inap[] = implode(" and ",$sql_where);
     $sql_where_baru_inap[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_baru_inap[] = "reg_tipe_rawat = 'I'";
     $sqlBaruInap = $sql." where ".implode(" and ",$sql_where_baru_inap);
     $dataPasien["inap"][PASIEN_BARU] = $dtaccess->Fetch($sqlBaruInap);
     // -- end ---

     // === nyari jumlah pasien lama rawat inap ----
     $sql_where_lama_inap[] = implode(" and ",$sql_where);
     $sql_where_lama_inap[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_lama_inap[] = "reg_tipe_rawat = 'I'";
     $sqlLamaInap = $sql." where ".implode(" and ",$sql_where_lama_inap);
     $dataPasien["inap"][PASIEN_LAMA] = $dtaccess->Fetch($sqlLamaInap);
     // -- end ---
     
     // === nyari jumlah pasien baru rawat inap ----
     $sql_where_jamkesda_inap_baru[] = implode(" and ",$sql_where);
     $sql_where_jamkesda_inap_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_jamkesda_inap_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_PNS);
     $sql_where_jamkesda_inap_baru[] = "reg_tipe_rawat = 'I' ";
     $sqlJamkesdaInapBaru = $sql." where ".implode(" and ",$sql_where_jamkesda_inap_baru); 
     $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_BPJS_PNS] = $dtaccess->Fetch($sqlJamkesdaInapBaru);
     // -- end ---

     // === nyari jumlah pasien lama rawat inap ----
     $sql_where_jamkesda_inap_lama[] = implode(" and ",$sql_where);
     $sql_where_jamkesda_inap_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_jamkesda_inap_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_PNS);
     $sql_where_jamkesda_inap_lama[] = "reg_tipe_rawat = 'I'";
     $sqlJamkesdaInapLama = $sql." where ".implode(" and ",$sql_where_jamkesda_inap_lama);
     $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_PNS] = $dtaccess->Fetch($sqlJamkesdaInapLama);
     // -- end ---
     
     // === nyari jumlah pasien baru rawat inap ----
     $sql_where_jamkesmas_inap_baru[] = implode(" and ",$sql_where);
     $sql_where_jamkesmas_inap_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_jamkesmas_inap_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_ASTEK);
     $sql_where_jamkesmas_inap_baru[] = "reg_tipe_rawat = 'I'";
     $sqlJamkesmasInapBaru = $sql." where ".implode(" and ",$sql_where_jamkesmas_inap_baru); 
     $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_BPJS_ASTEK] = $dtaccess->Fetch($sqlJamkesmasInapBaru);
     // -- end ---

     // === nyari jumlah pasien lama rawat inap ----
     $sql_where_jamkesmas_inap_lama[] = implode(" and ",$sql_where);
     $sql_where_jamkesmas_inap_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_jamkesmas_inap_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_ASTEK);
     $sql_where_jamkesmas_inap_lama[] = "reg_tipe_rawat = 'I'";
     $sqlJamkesmasInapLama = $sql." where ".implode(" and ",$sql_where_jamkesmas_inap_lama);
     $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_ASTEK] = $dtaccess->Fetch($sqlJamkesmasInapLama);
     // -- end ---
     
     // === nyari jumlah pasien baru rawat inap ----
     $sql_where_jamkesmaskota_inap_baru[] = implode(" and ",$sql_where);
     $sql_where_jamkesmaskota_inap_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_jamkesmaskota_inap_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_JAMKESMAS);
     $sql_where_jamkesmaskota_inap_baru[] = "reg_tipe_rawat = 'I'";
     $sqlJamkesmaskotaInapBaru = $sql." where ".implode(" and ",$sql_where_jamkesmaskota_inap_baru); 
     $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_BPJS_JAMKESMAS] = $dtaccess->Fetch($sqlJamkesmaskotaInapBaru);
     // -- end ---

     // === nyari jumlah pasien lama rawat inap ----
     $sql_where_jamkesmaskota_inap_lama[] = implode(" and ",$sql_where);
     $sql_where_jamkesmaskota_inap_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_jamkesmaskota_inap_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_BPJS_JAMKESMAS);
     $sql_where_jamkesmaskota_inap_lama[] = "reg_tipe_rawat = 'I'";
     $sqlJamkesmaskotaInapLama = $sql." where ".implode(" and ",$sql_where_jamkesmaskota_inap_lama);
     $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_JAMKESMAS] = $dtaccess->Fetch($sqlJamkesmaskotaInapLama);
     
     // === nyari jumlah pasien baru rawat inap ----
     $sql_where_askes_inap_baru[] = implode(" and ",$sql_where);
     $sql_where_askes_inap_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_askes_inap_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESDA_PROV);
     $sql_where_askes_inap_baru[] = "reg_tipe_rawat = 'I'";
     $sqlAskesInapBaru = $sql." where ".implode(" and ",$sql_where_askes_inap_baru); 
     $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_PROV] = $dtaccess->Fetch($sqlAskesInapBaru);
     // -- end ---

     // === nyari jumlah pasien lama rawat inap ----
     $sql_where_askes_inap_lama[] = implode(" and ",$sql_where);
     $sql_where_askes_inap_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_askes_inap_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESDA_PROV);
     $sql_where_askes_inap_lama[] = "reg_tipe_rawat = 'I'";
     $sqlAskesInapLama = $sql." where ".implode(" and ",$sql_where_askes_inap_lama);
     $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_PROV] = $dtaccess->Fetch($sqlAskesInapLama);
     // -- end ---
     
          // === nyari jumlah pasien baru rawat inap ----
     $sql_where_pns_inap_baru[] = implode(" and ",$sql_where);
     $sql_where_pns_inap_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_pns_inap_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESDA_KAB);
     $sql_where_pns_inap_baru[] = "reg_tipe_rawat = 'I'";
     $sqlPNSInapBaru = $sql." where ".implode(" and ",$sql_where_pns_inap_baru); 
     $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_KAB] = $dtaccess->Fetch($sqlPNSInapBaru);
     // -- end ---

     // === nyari jumlah pasien lama rawat inap ----
     $sql_where_pns_inap_lama[] = implode(" and ",$sql_where);
     $sql_where_pns_inap_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_pns_inap_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESDA_KAB);
     $sql_where_pns_inap_lama[] = "reg_tipe_rawat = 'I'";
     $sqlPNSInapLama = $sql." where ".implode(" and ",$sql_where_pns_inap_lama);
     $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_KAB] = $dtaccess->Fetch($sqlPNSInapLama);
     // -- end ---
     
     // === nyari jumlah pasien baru Komplimen rawat jalan ---
     $sql_where_komplimen_baru[] = implode(" and ",$sql_where);
     $sql_where_komplimen_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_komplimen_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_KOMPLIMEN);
     $sql_where_komplimen_baru[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlKomplimenBaru = $sql." where ".implode(" and ",$sql_where_komplimen_baru); 
     $dataPasien["jalan"][PASIEN_BARU][PASIEN_KOMPLIMEN] = $dtaccess->Fetch($sqlKomplimenBaru);
     // -- end ---
     
     // === nyari jumlah pasien lama Komplimen rawat jalan ---
     $sql_where_komplimen_lama[] = implode(" and ",$sql_where);
     $sql_where_komplimen_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_komplimen_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_KOMPLIMEN);
     $sql_where_komplimen_lama[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlKomplimenLama = $sql." where ".implode(" and ",$sql_where_komplimen_lama); 
     $dataPasien["jalan"][PASIEN_LAMA][PASIEN_KOMPLIMEN] = $dtaccess->Fetch($sqlKomplimenLama);
     // -- end ---
     
     // === nyari jumlah pasien Komplimen rawat inap baru ---
     $sql_where_komplimen_inap_baru[] = implode(" and ",$sql_where);
     $sql_where_komplimen_inap_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_komplimen_inap_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_KOMPLIMEN);
     $sql_where_komplimen_inap_baru[] = "reg_tipe_rawat = 'I'";
     $sqlKomplimenInapBaru = $sql." where ".implode(" and ",$sql_where_komplimen_inap_baru);
     $dataPasien["inap"][PASIEN_BARU][PASIEN_KOMPLIMEN] = $dtaccess->Fetch($sqlKomplimenInapBaru);
     // -- end ---   
          
     // === nyari jumlah pasien Komplimen rawat inap ---
     $sql_where_komplimen_inap[] = implode(" and ",$sql_where);
     $sql_where_komplimen_inap[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_komplimen_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_KOMPLIMEN);
     $sql_where_komplimen_inap[] = "reg_tipe_rawat = 'I'";
     $sqlKomplimenInap = $sql." where ".implode(" and ",$sql_where_komplimen_inap);
     $dataPasien["inap"][PASIEN_LAMA][PASIEN_KOMPLIMEN] = $dtaccess->Fetch($sqlKomplimenInap);
     // -- end ---   
          
     // === nyari jumlah pasien lain-lain rawat jalan ----
     $sql_where_dinkot[] = implode(" and ",$sql_where);
     $sql_where_dinkot[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_dinkot[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_LAIN);
     $sql_where_dinkot[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlDinkot = $sql." where ".implode(" and ",$sql_where_dinkot);
     $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_LAIN] = $dtaccess->Fetch($sqlDinkot);
     // -- end ---
     
     // === nyari jumlah pasien lain-lain rawat jalan ----
     $sql_where_dinkot[] = implode(" and ",$sql_where);
     $sql_where_dinkot[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_dinkot[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_LAIN);
     $sql_where_dinkot[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlDinkot = $sql." where ".implode(" and ",$sql_where_dinkot);
     $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_LAIN] = $dtaccess->Fetch($sqlDinkot);
     // -- end ---
     
     // === nyari jumlah pasien lain-lain  rawat inap ----
     $sql_where_dinkot_inap[] = implode(" and ",$sql_where);
     $sql_where_dinkot_inap[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_dinkot_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_LAIN);
     $sql_where_dinkot_inap[] = "reg_tipe_rawat = 'I'";
     $sqlDinkotInap = $sql." where ".implode(" and ",$sql_where_dinkot_inap);
     $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_LAIN] = $dtaccess->Fetch($sqlDinkotInap); 
     // -- end ---
     
     // === nyari jumlah pasien lain-lain  rawat inap ----
     $sql_where_dinkot_inap[] = implode(" and ",$sql_where);
     $sql_where_dinkot_inap[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_dinkot_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_LAIN);
     $sql_where_dinkot_inap[] = "reg_tipe_rawat = 'I'";
     $sqlDinkotInap = $sql." where ".implode(" and ",$sql_where_dinkot_inap);
     $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_LAIN] = $dtaccess->Fetch($sqlDinkotInap);
     // -- end ---
     
     // === nyari jumlah pasien swadaya  rawat jalan ----
     $sql_where_swadaya_baru[] = implode(" and ",$sql_where);
     $sql_where_swadaya_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_swadaya_baru[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA);
     $sql_where_swadaya_baru[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlSwadayaBaru = $sql." where ".implode(" and ",$sql_where_swadaya_baru);
     $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_SWADAYA] = $dtaccess->Fetch($sqlSwadayaBaru);
     // -- end ---
     
     // === nyari jumlah pasien swadaya  rawat jalan ----
     $sql_where_swadaya_lama[] = implode(" and ",$sql_where);
     $sql_where_swadaya_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_swadaya_lama[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA);
     $sql_where_swadaya_lama[] = "(a.reg_tipe_rawat in ('M','D','U') or a.reg_tipe_rawat is null)";
     $sqlSwadayaLama = $sql." where ".implode(" and ",$sql_where_swadaya_lama);
     $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_SWADAYA] = $dtaccess->Fetch($sqlSwadayaLama);
     // -- end ---
     
     // === nyari jumlah pasien swadaya  rawat inap ----
     $sql_where_swadaya_inap[] = implode(" and ",$sql_where);
     $sql_where_swadaya_inap[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sql_where_swadaya_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA);
     $sql_where_swadaya_inap[] = "reg_tipe_rawat ='I'";
     $sqlSwadayaInap = $sql." where ".implode(" and ",$sql_where_swadaya_inap);
     $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_SWADAYA] = $dtaccess->Fetch($sqlSwadayaInap);
     // -- end ---
     
     // === nyari jumlah pasien swadaya  rawat inap ----
     $sql_where_swadaya_inap[] = implode(" and ",$sql_where);
     $sql_where_swadaya_inap[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sql_where_swadaya_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA);
     $sql_where_swadaya_inap[] = "reg_tipe_rawat ='I'";
     $sqlSwadayaInap = $sql." where ".implode(" and ",$sql_where_swadaya_inap);
     $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_SWADAYA] = $dtaccess->Fetch($sqlSwadayaInap);
     // -- end ---
     
     /*
     // === nyari jumlah pasien Dinas Luar  rawat jalan ----
     $sql_where_dinlur = $sql_where;
     $sql_where_dinlur[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_DINASLUAR);
     $sqlDinlur = $sql." where ".implode(" and reg_tipe_rawat is null and ",$sql_where_dinlur);
     $dataPasienDinlur = $dtaccess->Fetch($sqlDinlur);
     // -- end ---
     
     // === nyari jumlah pasien  Dinas Luar  rawat inap ----
     $sql_where_dinlur_inap = $sql_where;
     $sql_where_dinlur_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_DINASLUAR);
     $sqlDinlurInap = $sql." where ".implode(" and reg_tipe_rawat = 'I' and ",$sql_where_dinlur_inap);
     $dataPasienDinlurInap = $dtaccess->Fetch($sqlDinlurInap);
     // -- end ---
     
     // === nyari jumlah pasien askes rawat jalan ---
     $sql_where_askes = $sql_where;
     $sql_where_askes[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_ASKES);
     $sqlAskes = $sql." where ".implode(" and reg_tipe_rawat is null and ",$sql_where_askes);
     $dataPasienAskes = $dtaccess->Fetch($sqlAskes);
     // -- end ---
     
     // === nyari jumlah pasien askes rawat inap ----
     $sql_where_askes_inap = $sql_where;
     $sql_where_askes_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_ASKES);
     $sqlAskesInap = $sql." where ".implode(" and reg_tipe_rawat = 'I' and ",$sql_where_askes_inap);
     $dataPasienAskesInap = $dtaccess->Fetch($sqlAskesInap);
     // -- end ---
     
     // === nyari jumlah pasien jamkesnas rawat jalan ----
     $sql_where_jamkes_pusat = $sql_where;
     $sql_where_jamkes_pusat[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESNAS_PUSAT);
     $sqlJamkesPusat = $sql." where ".implode(" and reg_tipe_rawat is null and ",$sql_where_jamkes_pusat);
     $dataPasienJamkesPusat = $dtaccess->Fetch($sqlJamkesPusat);
     // -- end ---
     
     // === nyari jumlah pasien jamkesnas rawat ianp ----
     $sql_where_jamkes_pusat_inap = $sql_where;
     $sql_where_jamkes_pusat_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESNAS_PUSAT);
     $sqlJamkesPusatInap = $sql." where ".implode(" and reg_tipe_rawat ='I' and ",$sql_where_jamkes_pusat_inap);
     $dataPasienJamkesPusatInap = $dtaccess->Fetch($sqlJamkesPusatInap);
     // -- end ---

     // === nyari jumlah pasien jamkesnas rawat jalan ----
     $sql_where_jamkes_daerah = $sql_where;
     $sql_where_jamkes_daerah[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESNAS_DAERAH);
     $sqlJamkesDaerah = $sql." where ".implode(" and reg_tipe_rawat is null and ",$sql_where_jamkes_daerah);
     $dataPasienJamkesDaerah = $dtaccess->Fetch($sqlJamkesDaerah);
     // -- end ---
     
     // === nyari jumlah pasien jamkesnas  rawat inap ---
     $sql_where_jamkes_daerah_inap = $sql_where;
     $sql_where_jamkes_daerah_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESNAS_DAERAH);
     $sqlJamkesDaerahInap = $sql." where ".implode(" and reg_tipe_rawat ='I' and ",$sql_where_jamkes_daerah_inap);
     $dataPasienJamkesDaerahInap = $dtaccess->Fetch($sqlJamkesDaerahInap);
     // -- end ---
     

     // === nyari jumlah pasien swadaya  rawat jalan ----
     $sql_where_swadaya = $sql_where;
     $sql_where_swadaya[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA);
     $sqlSwadaya = $sql." where ".implode(" and reg_tipe_rawat is null and ",$sql_where_swadaya);
     $dataPasienSwadaya = $dtaccess->Fetch($sqlSwadaya);
     // -- end ---
     
     // === nyari jumlah pasien swadaya  rawat inap ----
     $sql_where_swadaya_inap = $sql_where;
     $sql_where_swadaya_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA);
     $sqlSwadayaInap = $sql." where ".implode(" and reg_tipe_rawat ='I' and ",$sql_where_swadaya_inap);
     $dataPasienSwadayaInap = $dtaccess->Fetch($sqlSwadayaInap);
     // -- end ---
     
          // === nyari jumlah pasien PNS  rawat jalan ----
     $sql_where_pns = $sql_where;
     $sql_where_pns[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_PNS);
     $sqlPns = $sql." where ".implode(" and reg_tipe_rawat is null and ",$sql_where_pns);
     $dataPasienPns = $dtaccess->Fetch($sqlPns);
     // -- end ---
     
     // === nyari jumlah pasien PNS  rawat inap ----
     $sql_where_pns_inap = $sql_where;
     $sql_where_pns_inap[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_PNS);
     $sqlPnsInap = $sql." where ".implode(" and reg_tipe_rawat ='I' and ",$sql_where_pns_inap);
     $dataPasienPnsInap = $dtaccess->Fetch($sqlPnsInap);
     // -- end ---
  */
     
          
     $tableHeader = "Rekap Pasien";
?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<script language="JavaScript">
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Awal Harus Diisi");
          return false;
     }

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
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td width="10%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="35%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               -
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               
          </td>
          <td class="tablecontent">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>
</table>

<BR>

     <table width="100%" border="1" cellpadding="1" cellspacing="1">
     <tr> 
     <!--- table kiri --->
     <td width="50%">
          <table border="1" width="100%" valign="top"> 
          <tr align ="center" >
               <td colspan="2"><b>Rekap Pasien Rawat Jalan </b></td>
          </tr>    
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_SWADAYA]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_SWADAYA]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_SWADAYA]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_SWADAYA]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_SWADAYA];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_SWADAYA]["total"] + $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_SWADAYA]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_PNS]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_BPJS_PNS]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_PNS]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_PNS]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_BPJS_PNS];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_BPJS_PNS]["total"] + $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_PNS]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_ASTEK]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_BPJS_ASTEK]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_ASTEK]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_ASTEK]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_BPJS_ASTEK];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_BPJS_ASTEK]["total"] + $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_ASTEK]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_JAMKESMAS]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_BPJS_JAMKESMAS]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_JAMKESMAS]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_JAMKESMAS]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_BPJS_JAMKESMAS];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_BPJS_JAMKESMAS]["total"] + $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_JAMKESMAS]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_JAMKESDA_PROV]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_PROV]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_JAMKESDA_PROV]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_PROV]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_JAMKESDA_PROV];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_PROV]["total"] + $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_PROV]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_JAMKESDA_KAB]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_KAB]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_JAMKESDA_KAB]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_KAB]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_JAMKESDA_KAB];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_KAB]["total"] + $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_KAB]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_KOMPLIMEN]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_BARU][PASIEN_KOMPLIMEN]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_KOMPLIMEN]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_LAMA][PASIEN_KOMPLIMEN]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_KOMPLIMEN];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["jalan"][PASIEN_BARU][PASIEN_KOMPLIMEN]["total"] + $dataPasien["jalan"][PASIEN_LAMA][PASIEN_KOMPLIMEN]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_LAIN]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_LAIN]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_LAIN]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_LAIN]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_LAIN];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["jalan"][PASIEN_BARU][PASIEN_BAYAR_LAIN]["total"] + $dataPasien["jalan"][PASIEN_LAMA][PASIEN_BAYAR_LAIN]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU];?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_BARU]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA];?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["jalan"][PASIEN_LAMA]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien</td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["jalan"][PASIEN_BARU]["total"] + $dataPasien["jalan"][PASIEN_LAMA]["total"]);?></td>	     
          </tr>
          </table> 
          </td>

     <!--- table kanan --->
     <td width="50%">
          <table border="1" width="100%" valign="top">
          <tr align ="center" >
               <td colspan="2"><b>Rekap Pasien Rawat Inap </b></td>
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_SWADAYA]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_SWADAYA]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_SWADAYA]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_SWADAYA]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_SWADAYA];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_SWADAYA]["total"] + $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_SWADAYA]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_PNS]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_BPJS_PNS]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_PNS]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_PNS]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_BPJS_PNS];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_BPJS_PNS]["total"] + $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_PNS]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_ASTEK]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_BPJS_ASTEK]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_ASTEK]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_ASTEK]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_BPJS_ASTEK];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_BPJS_ASTEK]["total"] + $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_ASTEK]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_JAMKESMAS]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_BPJS_JAMKESMAS]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_BPJS_JAMKESMAS]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_JAMKESMAS]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_BPJS_JAMKESMAS];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_BPJS_JAMKESMAS]["total"] + $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_BPJS_JAMKESMAS]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_JAMKESDA_PROV]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_PROV]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_JAMKESDA_PROV]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_PROV]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_JAMKESDA_PROV];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_PROV]["total"] + $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_PROV]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_JAMKESDA_KAB]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_KAB]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_JAMKESDA_KAB]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_KAB]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_JAMKESDA_KAB];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_JAMKESDA_KAB]["total"] + $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_JAMKESDA_KAB]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_KOMPLIMEN]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_BARU][PASIEN_KOMPLIMEN]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_KOMPLIMEN]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_LAMA][PASIEN_KOMPLIMEN]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_KOMPLIMEN];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["inap"][PASIEN_BARU][PASIEN_KOMPLIMEN]["total"] + $dataPasien["inap"][PASIEN_LAMA][PASIEN_KOMPLIMEN]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU]."&nbsp;".$bayarPasien[PASIEN_BAYAR_LAIN]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_LAIN]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA]."&nbsp;".$bayarPasien[PASIEN_BAYAR_LAIN]?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_LAIN]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien <?php echo $bayarPasien[PASIEN_BAYAR_LAIN];?> </td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["inap"][PASIEN_BARU][PASIEN_BAYAR_LAIN]["total"] + $dataPasien["inap"][PASIEN_LAMA][PASIEN_BAYAR_LAIN]["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_BARU];?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_BARU]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent-odd"  width="50%" align="left">Pasien&nbsp;<?php echo $statusPasien[PASIEN_LAMA];?></td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasien["inap"][PASIEN_LAMA]["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien</td>
               <td class="tablecontent"  width="50%" align="center"><?php echo ($dataPasien["inap"][PASIEN_BARU]["total"] + $dataPasien["inap"][PASIEN_LAMA]["total"]);?></td>	     
          </tr>
          </table>
          </td>

     </tr>     
     </table>


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
