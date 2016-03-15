<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     require_once($APLICATION_ROOT."library/config/global.cfg.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");

     $namaBulan = array('1' => 'JANUARI', '2' => 'FEBRUARI', '3' => 'MARET', '4' => 'APRIL', '5' => 'MEI', '6' => 'JUNI', '7' => 'JULI', '8' => 'AGUSTUS', '9' => 'SEPTEMBER', '10' => 'OKTOBER', '11' => 'NOVEMBER', '12' => 'DESEMBER');
 
     $thisPage = "report_pemeriksaan.php";

     if(!$auth->IsAllowed("pemeriksaan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("pemeriksaan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if($_POST["in_tahun"]) $sql_where[] = "extract(year from diag_waktu) = ".QuoteValue(DPE_CHAR,$_POST["in_tahun"]);

    // --- begin: cari rekap fundus bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_fundus
            from klinik.klinik_diagnostik ";
      $sql_where_fundus = $sql_where;
     $sql_where_fundus[] = "(diag_fundus <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_fundus);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataFundus = $dtaccess->Fetch($rs)){
      $dataTable['fundus'][$dataFundus['n_mon']] = $dataFundus['count_fundus'];
     }
     unset($rs);
     // --- end: cari rekap fundus bulanan --- //

    // --- begin: cari rekap keratometri bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_keratometri
            from klinik.klinik_diagnostik ";
      $sql_where_keratometri = $sql_where;
     $sql_where_keratometri[] = "(diag_k1_od <> '' or diag_k2_od <> ''  or diag_k1_os <> ''  or diag_k2_os <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_keratometri);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($datakeratometri = $dtaccess->Fetch($rs)){
      $dataTable['keratometri'][$datakeratometri['n_mon']] = $datakeratometri['count_keratometri'];
     }
     unset($rs);
     // --- end: cari rekap keratometri bulanan --- //

    // --- begin: cari rekap biometri bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_biometri
            from klinik.klinik_diagnostik ";
      $sql_where_biometri = $sql_where;
     $sql_where_biometri[] = "(diag_acial_od <> '' or diag_acial_os <> ''  or diag_iol_od <> ''  or diag_iol_os <> '' or diag_av_constant is not null or diag_deviasi <> '' or diag_rumus is not null)"; 
     $sql.= " where ".implode(" and ",$sql_where_biometri);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($databiometri = $dtaccess->Fetch($rs)){
      $dataTable['biometri'][$databiometri['n_mon']] = $databiometri['count_biometri'];
     }
      unset($rs);
     // --- end: cari rekap biometri bulanan --- //

    // --- begin: cari rekap usg bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_usg
            from klinik.klinik_diagnostik ";
      $sql_where_usg = $sql_where;
     $sql_where_usg[] = "(diag_coa <> '' or diag_lensa <> ''  or diag_retina <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_usg);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($datausg = $dtaccess->Fetch($rs)){
      $dataTable['usg'][$datausg['n_mon']] = $datausg['count_usg'];
     }
     unset($rs);
     // --- end: cari rekap usg bulanan --- //

    // --- begin: cari rekap opthalmoscop bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_opthal
            from klinik.klinik_diagnostik ";
      $sql_where_opthal = $sql_where;
     $sql_where_opthal[] = "(diag_opthalmoscop <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_opthal);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataopthal = $dtaccess->Fetch($rs)){
      $dataTable['opthal'][$dataopthal['n_mon']] = $dataopthal['count_opthal'];
     }
     unset($rs);
     // --- end: cari rekap opthalmoscop bulanan --- //

    // --- begin: cari rekap ekg bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_ekg
            from klinik.klinik_diagnostik ";
      $sql_where_ekg = $sql_where;
     $sql_where_ekg[] = "(diag_ekg <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_ekg);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataekg = $dtaccess->Fetch($rs)){
      $dataTable['ekg'][$dataekg['n_mon']] = $dataekg['count_ekg'];
     }
     unset($rs);
     // --- end: cari rekap ekg bulanan --- //

    // --- begin: cari rekap oct bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_oct
            from klinik.klinik_diagnostik ";
      $sql_where_oct = $sql_where;
     $sql_where_oct[] = "(diag_oct <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_oct);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataoct = $dtaccess->Fetch($rs)){
      $dataTable['oct'][$dataoct['n_mon']] = $dataoct['count_oct'];
     }
     unset($rs);
     // --- end: cari rekap oct bulanan --- //

    // --- begin: cari rekap yag bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_yag
            from klinik.klinik_diagnostik ";
      $sql_where_yag = $sql_where;
     $sql_where_yag[] = "(diag_yag <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_yag);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($datayag = $dtaccess->Fetch($rs)){
      $dataTable['yag'][$datayag['n_mon']] = $datayag['count_yag'];
     }
     unset($rs);
     // --- end: cari rekap yag bulanan --- //

    // --- begin: cari rekap argon bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_argon
            from klinik.klinik_diagnostik ";
      $sql_where_argon = $sql_where;
     $sql_where_argon[] = "(diag_argon <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_argon);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataargon = $dtaccess->Fetch($rs)){
      $dataTable['argon'][$dataargon['n_mon']] = $dataargon['count_argon'];
     }
     unset($rs);
     // --- end: cari rekap argon bulanan --- //

    // --- begin: cari rekap glaukoma bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_glaukoma
            from klinik.klinik_diagnostik ";
      $sql_where_glaukoma = $sql_where;
     $sql_where_glaukoma[] = "(diag_glaukoma <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_glaukoma);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataglaukoma = $dtaccess->Fetch($rs)){
      $dataTable['glaukoma'][$dataglaukoma['n_mon']] = $dataglaukoma['count_glaukoma'];
     }
     unset($rs);
     // --- end: cari rekap glaukoma bulanan --- //

    // --- begin: cari rekap humpre bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_humpre
            from klinik.klinik_diagnostik ";
      $sql_where_humpre = $sql_where;
     $sql_where_humpre[] = "(diag_humpre <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_humpre);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($datahumpre = $dtaccess->Fetch($rs)){
      $dataTable['humpre'][$datahumpre['n_mon']] = $datahumpre['count_humpre'];
     }
     unset($rs);
     // --- end: cari rekap humpre bulanan --- //

    // --- begin: cari rekap gambar_fundus bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_gambar_fundus
            from klinik.klinik_diagnostik ";
      $sql_where_gambar_fundus = $sql_where;
     $sql_where_gambar_fundus[] = "(diag_gambar_fundus <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_gambar_fundus);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($datagambar_fundus = $dtaccess->Fetch($rs)){
      $dataTable['gambar_fundus'][$datagambar_fundus['n_mon']] = $datagambar_fundus['count_gambar_fundus'];
     }
     unset($rs);
     // --- end: cari rekap gambar_fundus bulanan --- //

    // --- begin: cari rekap laser slt bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_slt
            from klinik.klinik_diagnostik ";
      $sql_where_slt = $sql_where;
     $sql_where_slt[] = "(diag_slt <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_slt);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataSLT = $dtaccess->Fetch($rs)){
      $dataTable['slt'][$dataSLT['n_mon']] = $dataSLT['count_slt'];
     }
     unset($rs);
     // --- end: cari rekap laser slt bulanan --- //

    // --- begin: cari rekap lpi bulanan --- //
     $sql = "select date_part('month', diag_waktu) as n_mon,
                to_char(diag_waktu,'Mon') as mon,
                extract(year from diag_waktu) as yyyy,
                count(diag_id) as count_lpi
            from klinik.klinik_diagnostik ";
      $sql_where_lpi = $sql_where;
     $sql_where_lpi[] = "(diag_lpi <> '')"; 
     $sql.= " where ".implode(" and ",$sql_where_lpi);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', diag_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($datalpi = $dtaccess->Fetch($rs)){
      $dataTable['lpi'][$datalpi['n_mon']] = $datalpi['count_lpi'];
     }
     unset($rs);
     // --- end: cari rekap lpi bulanan --- //

     //*-- config table ---*//
     $tableHeader = "&nbsp;Rekap Tahunan Pasien Diagnostik";
     if ($_POST["in_tahun"]) {
       $tableHeader .= "&nbsp;-&nbsp;TAHUN&nbsp;".$_POST["in_tahun"];
     }
     

     if($_POST["btnLanjut"] || $_POST["btnExcel"]){
               // --- construct new table ---- //
               $counterHeader = 0;
                    
               $tbHeader[0][$counterHeader][TABLE_ISI] = "KODE";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%";
               $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
               $counterHeader++;
                    
               $tbHeader[0][$counterHeader][TABLE_ISI] = "NO";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
               $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
               $counterHeader++;
                   
               $tbHeader[0][$counterHeader][TABLE_ISI] = "BULAN";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%";
               $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
               $counterHeader++;
                   
               $tbHeader[0][$counterHeader][TABLE_ISI] = "DIAGNOSTIK CANGGIH";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "62%";
               $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "7";
               $counterHeader++;
               
               $tbHeader[0][$counterHeader][TABLE_ISI] = "JUMLAH";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%";
               $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
               $counterHeader++;
     
               $counterHeader = 0;
     
               $tbHeader[1][$counterHeader][TABLE_ISI] = "HFA";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";
               $counterHeader++;
     
               $tbHeader[1][$counterHeader][TABLE_ISI] = "USG";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";
               $counterHeader++;
     
               $tbHeader[1][$counterHeader][TABLE_ISI] = "FF";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";
               $counterHeader++;
     
               $tbHeader[1][$counterHeader][TABLE_ISI] = "YL";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";
               $counterHeader++;
     
               $tbHeader[1][$counterHeader][TABLE_ISI] = "AL";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";
               $counterHeader++;
     
               $tbHeader[1][$counterHeader][TABLE_ISI] = "OCT";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";
               $counterHeader++;
     
               $tbHeader[1][$counterHeader][TABLE_ISI] = "LPI";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";
               $counterHeader++;

     
               for($i=0,$counter=0,$n=12,$sum_of_month=0;$i<$n;$i++,$counter=0,$sum_of_month=0){
                if($i==0){
                  $tbContent[$i][$counter][TABLE_ISI] = 'A';
                  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                  $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                  $tbContent[$i][$counter][TABLE_ROWSPAN] = "13";
                  $counter++;
                }
     
                $tbContent[$i][$counter][TABLE_ISI] = ($i + 1);
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
     
                 $tbContent[$i][$counter][TABLE_ISI] = $namaBulan[$i+1];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['humpre'][$i+1]) ? $dataTable['humpre'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;          
                $sum_of_month += $dataTable['humpre'][$i+1];
                $sum_of_diag['humpre'] += $dataTable['humpre'][$i+1];
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['usg'][$i+1]) ? $dataTable['usg'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_month += $dataTable['usg'][$i+1];
                $sum_of_diag['usg'] += $dataTable['usg'][$i+1];
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['gambar_fundus'][$i+1]) ? $dataTable['gambar_fundus'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_month += $dataTable['gambar_fundus'][$i+1];
                $sum_of_diag['gambar_fundus'] += $dataTable['gambar_fundus'][$i+1];
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['yag'][$i+1]) ? $dataTable['yag'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_month += $dataTable['yag'][$i+1];
                $sum_of_diag['yag'] += $dataTable['yag'][$i+1];
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['argon'][$i+1]) ? $dataTable['yag'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_month += $dataTable['argon'][$i+1];
                $sum_of_diag['argon'] += $dataTable['argon'][$i+1];
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['oct'][$i+1]) ? $dataTable['oct'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_month += $dataTable['oct'][$i+1];
                $sum_of_diag['oct'] += $dataTable['oct'][$i+1];
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['lpi'][$i+1]) ? $dataTable['lpi'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_month += $dataTable['lpi'][$i+1];
                $sum_of_diag['lpi'] += $dataTable['lpi'][$i+1];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_month;
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
     
               }
               $counter = 0;

               $tbContent[$i][$counter][TABLE_ISI] = "TOTAL";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $tbContent[$i][$counter][TABLE_COLSPAN] = "2";
                $counter++;
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_diag['humpre'];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;          
                $sum_of_all += $sum_of_diag['humpre'];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_diag['usg'];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_all += $sum_of_diag['usg'];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_diag['gambar_fundus'];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_all += $sum_of_diag['gambar_fundus'];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_diag['yag'];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_all += $sum_of_diag['yag'];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_diag['argon'];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_all += $sum_of_diag['argon'];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_diag['oct'];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_all += $sum_of_diag['oct'];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_diag['lpi'];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_all += $sum_of_diag['lpi'];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_all;
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
     
               $colspan = 11;
               
               if(!$_POST["btnExcel"]){
                    $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" >&nbsp;';//onClick="document.location.href=\''.$editPage.'\'"
                    $tbBottom[0][0][TABLE_WIDTH] = "100%";
                    $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
                    $tbBottom[0][0][TABLE_ALIGN] = "center";
               }
          }
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pemeriksaan_'.$_POST["in_tahun"].'.xls');
     }
     
// --- bikin option in_tahun nya --- //
     $sql_tahun = 'select distinct extract(year from diag_waktu) as in_tahun
                    from klinik.klinik_diagnostik 
                    order by in_tahun';
      $rs_tahun = $dtaccess->Execute($sql_tahun);
      while($data_tahun = $dtaccess->Fetch($rs_tahun)){
        $optTahun[] = $view->RenderOption($data_tahun["in_tahun"],$data_tahun["in_tahun"],($data_tahun["in_tahun"]==$_POST["in_tahun"]) ? "selected" : "");
      }
     
?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>
<script language="JavaScript">
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }
}

</script>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
  <tr class="tableheader">
    <td><?php echo $tableHeader;?></td>
  </tr>
  <tr>
    <td>
      <form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" >
      <?php if(!$_POST["btnExcel"]) { ?>
      <table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
           <tr>
                <td width="15%" class="tablecontent">&nbsp;Tahun Pemeriksaan</td>
                <td width="20%" class="tablecontent-odd">
                  <?php echo $view->RenderComboBox("in_tahun","in_tahun",$optTahun);?>
                </td> 
                <td class="tablecontent">
                     <input type="submit" name="btnLanjut" value="Lanjut" class="button">
                </td>
           </tr>
      </table>
      <?php } ?>
          
      <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>


      </form>

    </td>
  </tr>
</table>
<?php echo $view->RenderBodyEnd(); ?>
