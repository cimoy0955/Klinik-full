<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","90%","left");
 
      $namaBulan = array('1' => 'JANUARI', '2' => 'FEBRUARI', '3' => 'MARET', '4' => 'APRIL', '5' => 'MEI', '6' => 'JUNI', '7' => 'JULI', '8' => 'AGUSTUS', '9' => 'SEPTEMBER', '10' => 'OKTOBER', '11' => 'NOVEMBER', '12' => 'DESEMBER');
     $thisPage = "report_kasir_perkas_bulanan.php";

     if(!$auth->IsAllowed("report_kasir",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_kasir",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
          
     $skr = date("m");
     $skrYear = date("Y");
     if(!$_POST["q_bulan"]) $_POST["q_bulan"] = $skr;
     if(!$_POST["in_tahun"]) $_POST["in_tahun"] = $skrYear;
     $sql_where[] = "a.fol_lunas = ".QuoteValue(DPE_CHAR,"y"); 
     
    if($_POST["q_bulan"]) $sql_where[] = "date_part('month', a.fol_dibayar_when) = ".QuoteValue(DPE_CHAR,$_POST["q_bulan"]);
     
     
    if($_POST["in_tahun"]) $sql_where[] = "date_part('year', a.fol_dibayar_when) = ".QuoteValue(DPE_CHAR,$_POST["in_tahun"]);
     
	$sql_where = implode(" and ",$sql_where);
	
  // --- cari data sub total folio
     $sql = "select date_part('month', fol_dibayar_when) as bulan, status_id, sum(fol_dibayar) as sub_total 
            from klinik.klinik_folio a 
            left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
            left join global.global_status_pasien c on CAST(c.status_id as char) = b.biaya_jenis 
            left join klinik.klinik_registrasi d on d.reg_id = a.id_reg";
    $sql .= " where ".$sql_where; 
  $sql .= " group by 1, 2 order by 1, 2";
   // echo $sql;
     $dataFolio = $dtaccess->FetchAll($sql);
     for ($i=0; $i < count($dataFolio); $i++) { 
        if (!$dataFolio[$i]["status_id"]) {
          $dataFolio[$i]["status_id"] = 8;
        }
        $viewData[$dataFolio[$i]["bulan"]][$dataFolio[$i]["status_id"]] = $dataFolio[$i]["sub_total"];
     }

    // --- cari data status kas nya
     $sql = "select status_id, status_nama from global.global_status_pasien order by status_id";
     $rs_dataKas = $dtaccess->Execute($sql);
     $dataKas = $dtaccess->FetchAll($rs_dataKas);
     // unset($rs_namabiaya);
     

	
	$counter=0;
		
     $tbHeader[0][0][TABLE_ISI] = "Bulan";
     $tbHeader[0][0][TABLE_WIDTH] = "25%";
	
     $tbHeader[0][2][TABLE_ISI] = "Pos Kas";
     $tbHeader[0][2][TABLE_WIDTH] = "25%"; 
	
     $tbHeader[0][3][TABLE_ISI] = "Total";
     $tbHeader[0][3][TABLE_WIDTH] = "25%";
	
     $i=0;
     $k=0;
     $grandTotal = 0;
       
        for ($j=0, $counter=0; $j <= count($dataKas); $j++, $k++, $counter=0) { 
          if ( $j == 0 ) {
            $tbContent[$k][$counter][TABLE_ISI] = '&nbsp;'.$namaBulan[$dataFolio[$i]["bulan"]];
            $tbContent[$k][$counter][TABLE_ALIGN] = "left";
            $tbContent[$k][$counter][TABLE_VALIGN] = "top";
            $tbContent[$k][$counter][TABLE_ROWSPAN] = 11;
            $counter++;
          }

          if ($j < count($dataKas)) {
            $tbContent[$k][$counter][TABLE_ISI] = "&nbsp;".$dataKas[$j]["status_nama"];
            $tbContent[$k][$counter][TABLE_ALIGN] = "left";
            $counter++;
          
            $tbContent[$k][$counter][TABLE_ISI] = 'Rp. '.currency_format($viewData[$dataFolio[$i]["bulan"]][$dataKas[$j]["status_id"]]).'&nbsp;';
            $tbContent[$k][$counter][TABLE_ALIGN] = "right";
            $counter++;
            $subtotal += $viewData[$dataFolio[$i]["bulan"]][$dataKas[$j]["status_id"]];
            $subtotalkas[$dataKas[$j]["status_id"]] += $viewData[$dataFolio[$i]["bulan"]][$dataKas[$j]["status_id"]];
          }

          if ($j == count($dataKas)) {
            $tbContent[$k][$counter][TABLE_ISI] = "&nbsp;Total";
            $tbContent[$k][$counter][TABLE_ALIGN] = "left";
            $counter++;
          
            $tbContent[$k][$counter][TABLE_ISI] = 'Rp. '.currency_format($subtotal)."&nbsp;";
            $tbContent[$k][$counter][TABLE_ALIGN] = "right";
            $counter++;
            $grandTotal += $subtotal;
          }
          
        }

      for ($l=0, $counterBottom=0; $l <= count($dataKas); $l++, $counterBottom=0) { 
        if ($l < count($dataKas)) {
          $tbBottom[$l][$counterBottom][TABLE_ISI] = "&nbsp;".$dataKas[$l]["status_nama"];
          $tbBottom[$l][$counterBottom][TABLE_ALIGN] = "left";
          $tbBottom[$l][$counterBottom][TABLE_COLSPAN] = "2";
          $counterBottom++;

          $tbBottom[$l][$counterBottom][TABLE_ISI] = "Rp&nbsp;".currency_format($subtotalkas[$dataKas[$l]["status_id"]])."&nbsp;";
          $tbBottom[$l][$counterBottom][TABLE_ALIGN] = "right";
          $counterBottom++;       
        }else{
          $tbBottom[$l][$counterBottom][TABLE_ISI] = "&nbsp;Total";
          $tbBottom[$l][$counterBottom][TABLE_ALIGN] = "left";
          $tbBottom[$l][$counterBottom][TABLE_COLSPAN] = "2";
          $counterBottom++;

          $tbBottom[$l][$counterBottom][TABLE_ISI] = "Rp&nbsp;".currency_format($grandTotal)."&nbsp;";
          $tbBottom[$l][$counterBottom][TABLE_ALIGN] = "right";
          $counterBottom++;       
        }
      }

     $tableHeader = "Laporan Kasir Per Jenis Kas";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_kasi_perkas_bulan'.$_POST["q_bulan"].'_tahun'.$_POST["in_tahun"].'.xls');
     }
	
     $sql_tahun = 'select distinct extract(year from fol_dibayar_when) as in_tahun
                    from klinik.klinik_folio 
                    order by in_tahun';

      $rs_tahun = $dtaccess->Execute($sql_tahun);
      while($data_tahun = $dtaccess->Fetch($rs_tahun)){
        if ($data_tahun!=null) {
          $optTahun[] = $view->RenderOption($data_tahun["in_tahun"],$data_tahun["in_tahun"],($data_tahun["in_tahun"]==$_POST["in_tahun"]) ? "selected" : "");
        }
      }
?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>

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

<?php if(!$_POST["btnExcel"]) { ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td colspan="<?php echo (count($dataSplit)+6)?>">&nbsp;<?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
     <tr class="tablecontent">
          <td width="10%">&nbsp;Bulan</td>
          <td width="15%">
               <select name="q_bulan" class="inputField">
                <?php foreach ($namaBulan as $bulanNum => $bulanNama) {
                    $newMonth = str_pad($bulanNum, 2, "0", STR_PAD_LEFT);
                  ?>
                 <option value="<?php echo $newMonth;?>" <?php echo ($newMonth==$_POST["q_bulan"]) ? "selected" : "" ;?> ><?php echo $bulanNama;?></option>
                <?php }?>
               </select>
          </td>
          <td width="10%" class="tablecontent">&nbsp;Tahun</td>
          <td width="55%" class="tablecontent">
            <?php echo $view->RenderComboBox("in_tahun","in_tahun",$optTahun);?>
          </td> 
     </tr>
	<tr>
          <td class="tablecontent" colspan="6">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
			<input type="submit" name="btnExcel" value="Export Excel" class="button">
          </td>
     </tr>
</table>

<BR>

</form>
<?php } ?>

<?php if($_POST["btnExcel"]) {?>
     <table width="90%" border="1" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="<?php echo (count($dataSplit)+6)?>"><strong>LAPORAN KASIR PERJENIS KAS</strong></td>
          </tr>
     </table>
<?php }?>
<?php echo "Tahun:&nbsp;".$_POST["in_tahun"]; ?><br />
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>


<?php echo $view->RenderBodyEnd(); ?>
