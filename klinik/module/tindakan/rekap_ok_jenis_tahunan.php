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

     if($_POST["in_tahun"]) $sql_where[] = "extract(year from a.op_waktu) = ".QuoteValue(DPE_CHAR,$_POST["in_tahun"]);

    // --- begin: cari rekap pasien swadana bulanan --- //
     $sql = "select date_part('month', a.op_waktu) as n_mon,
                  to_char(a.op_waktu,'Mon') as mon,
                  extract(year from a.op_waktu) as yyyy,
                  count(a.op_id) as monthly_count
              from klinik.klinik_operasi a
              left join klinik.klinik_registrasi b on a.id_reg = b.reg_id ";
      $sql_where_swa = $sql_where;
     $sql_where_swa[] = "b.reg_jenis_pasien = ".QuoteValue(DPE_NUMERICKEY,PASIEN_BAYAR_SWADAYA); 
     $sql.= " where ".implode(" and ",$sql_where_swa);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', a.op_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataSwa = $dtaccess->Fetch($rs)){
      $dataTable['swa'][$dataSwa['n_mon']] = $dataSwa['monthly_count'];
     }
     unset($rs);
     // --- end: cari rekap pasien swadana bulanan --- //

    // --- begin: cari rekap pasien bpjs bulanan --- //
     $sql = "select date_part('month', a.op_waktu) as n_mon,
                  to_char(a.op_waktu,'Mon') as mon,
                  extract(year from a.op_waktu) as yyyy,
                  count(a.op_id) as monthly_count
              from klinik.klinik_operasi a
              left join klinik.klinik_registrasi b on a.id_reg = b.reg_id ";
      $sql_where_female = $sql_where;
     $sql_where_female[] = "b.reg_jenis_pasien in (".QuoteValue(DPE_NUMERICKEY,PASIEN_BAYAR_BPJS_PNS).",".QuoteValue(DPE_NUMERICKEY,PASIEN_BAYAR_BPJS_ASTEK).",".QuoteValue(DPE_NUMERICKEY,PASIEN_BAYAR_BPJS_JAMKESMAS).") "; 
     $sql.= " where ".implode(" and ",$sql_where_female);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', a.op_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataFemale = $dtaccess->Fetch($rs)){
      $dataTable['bpjs'][$dataFemale['n_mon']] = $dataFemale['monthly_count'];
     }
     unset($dataFemale);
     unset($rs);
     // --- end: cari rekap pasien bpjs bulanan --- //

    // --- begin: cari rekap pasien bpjs bulanan --- //
     $sql = "select date_part('month', a.op_waktu) as n_mon,
                  to_char(a.op_waktu,'Mon') as mon,
                  extract(year from a.op_waktu) as yyyy,
                  count(a.op_id) as monthly_count
              from klinik.klinik_operasi a
              left join klinik.klinik_registrasi b on a.id_reg = b.reg_id ";
      $sql_where_female = $sql_where;
     $sql_where_female[] = "b.reg_jenis_pasien not in (".QuoteValue(DPE_NUMERICKEY,PASIEN_BAYAR_SWADAYA).",".QuoteValue(DPE_NUMERICKEY,PASIEN_BAYAR_BPJS_PNS).",".QuoteValue(DPE_NUMERICKEY,PASIEN_BAYAR_BPJS_ASTEK).",".QuoteValue(DPE_NUMERICKEY,PASIEN_BAYAR_BPJS_JAMKESMAS).") "; 
     $sql.= " where ".implode(" and ",$sql_where_female);
     $sql.= " group by 1,2,3
            order by yyyy, date_part('month', a.op_waktu)";
     $rs = $dtaccess->Execute($sql);
     while($dataFemale = $dtaccess->Fetch($rs)){
      $dataTable['dkk'][$dataFemale['n_mon']] = $dataFemale['monthly_count'];
     }
     unset($dataFemale);
     unset($rs);
     // --- end: cari rekap pasien bpjs bulanan --- //

     //*-- config table ---*//
     $tableHeader = "&nbsp;Rekap Tahunan Pasien OK berdasar Jenis Tagihan Pasien";

     if($_POST["btnLanjut"] || $_POST["btnExcel"]){
               // --- construct new table ---- //
               $counterHeader = 0;
                    
               $tbHeader[0][$counterHeader][TABLE_ISI] = "KODE";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
               $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
               $counterHeader++;
                    
               $tbHeader[0][$counterHeader][TABLE_ISI] = "NO";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
               $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
               $counterHeader++;
                   
               $tbHeader[0][$counterHeader][TABLE_ISI] = "BULAN";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%";
               $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
               $counterHeader++;
                   
               $tbHeader[0][$counterHeader][TABLE_ISI] = "PASIEN";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "50%";
               $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "3";
               $counterHeader++;
               
               $tbHeader[0][$counterHeader][TABLE_ISI] = "JUMLAH";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%";
               $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
               $counterHeader++;
     
               $counterHeader = 0;
     
               $tbHeader[1][$counterHeader][TABLE_ISI] = "SWADANA";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "12%";
               $counterHeader++;
     
               $tbHeader[1][$counterHeader][TABLE_ISI] = "DKK";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "12%";
               $counterHeader++;
          
               $tbHeader[1][$counterHeader][TABLE_ISI] = "BPJS";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "12%";
               $counterHeader++;
     
               for($i=0,$counter=0,$n=12,$sum_of_month=0;$i<$n;$i++,$counter=0,$sum_of_month=0){
                if($i==0){$tbContent[$i][$counter][TABLE_ISI] = 'A';
                 $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                 $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                 $tbContent[$i][$counter][TABLE_ROWSPAN] = "13";
                 $counter++;}
     
                $tbContent[$i][$counter][TABLE_ISI] = ($i + 1);
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
     
                 $tbContent[$i][$counter][TABLE_ISI] = $namaBulan[$i+1];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['swa'][$i+1]) ? $dataTable['swa'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_month += $dataTable['swa'][$i+1];
                $sum_of_ok['swa'] += $dataTable['swa'][$i+1];
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['dkk'][$i+1]) ? $dataTable['dkk'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_month += $dataTable['dkk'][$i+1];
                $sum_of_ok['dkk'] += $dataTable['dkk'][$i+1];
     
                $tbContent[$i][$counter][TABLE_ISI] = ($dataTable['bpjs'][$i+1]) ? $dataTable['bpjs'][$i+1] : "0";
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_month += $dataTable['bpjs'][$i+1];
                $sum_of_ok['bpjs'] += $dataTable['bpjs'][$i+1];
     
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
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_ok['swa'];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_all += $sum_of_ok['swa'];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_ok["dkk"];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_all += $sum_of_ok["dkk"];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_ok['bpjs'];
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
                $sum_of_all += $sum_of_ok['bpjs'];
     
                $tbContent[$i][$counter][TABLE_ISI] = $sum_of_all;
                $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
                $counter++;
     
               $colspan = 7;
               
               if(!$_POST["btnExcel"]){
                    $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
                    $tbBottom[0][0][TABLE_WIDTH] = "100%";
                    $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
                    $tbBottom[0][0][TABLE_ALIGN] = "center";
               }
          }
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pemeriksaan_'.$_POST["tgl_awal"].'.xls');
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
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
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
