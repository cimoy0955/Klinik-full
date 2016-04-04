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
 
     $thisPage = "report_loket.php";

     $plx = new InoLiveX("GetLayanan");     
     if(!$auth->IsAllowed("report_kasir",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_kasir",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     function GetLayanan($jenisId)
	{
          global $dtaccess,$view,$bayarNama,$rawatStatus;
		
		if($jenisId!=PASIEN_BAYAR_SWADAYA) {
			$str = $bayarNama[BIAYA_KARTU];
		} else {
			
			$sql = "select biaya_id, biaya_nama, biaya_jenis 
					from klinik.klinik_biaya a
					order by biaya_id "; 
			$dataBiaya = $dtaccess->FetchAll($sql);
			unset($layanan);
			$layanan[0] = $view->RenderOption("","[ Pilih Layanan Biaya ]",$show);
			$i = 1;
			for($i=0,$n=count($dataBiaya);$i<$n;$i++){ 
				unset($show); 
				$layanan[$i+1] = $view->RenderOption($dataBiaya[$i]["biaya_id"],$rawatStatus[$dataBiaya[$i]["biaya_jenis"]]." - ".$dataBiaya[$i]["biaya_nama"],$show); 
			}
			
			$layanan[count($dataBiaya)+1] = $view->RenderOption(STATUS_OPERASI,$rawatStatus[STATUS_OPERASI],$show);
			
			$str = $view->RenderComboBox("id_biaya","id_biaya",$layanan,null,null);
		}
		
		return $str;
     } 
	
	$sql = "select * from klinik.klinik_split order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
     
          
     $skr = date("d-m-Y");
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr; 
     $sql_where[] = "a.fol_lunas = ".QuoteValue(DPE_CHAR,"y"); 
     
    if($_POST["tgl_awal"]) $sql_where[] = "CAST(a.fol_dibayar_when as DATE) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
    if($_POST["tgl_akhir"]) $sql_where[] = "CAST(a.fol_dibayar_when as DATE) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
     
		if($_POST['cust_usr_jenis']) $sql_where[] = "d.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
	
 //     if($_POST["id_biaya"] && $_POST["id_biaya"]!=STATUS_OPERASI) $sql_where[] = "a.id_biaya = ".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
	// if($_POST["id_biaya"]==STATUS_OPERASI) $sql_where[] = "a.fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
     
	$sql_where = implode(" and ",$sql_where);
	
  // --- cari data sub total folio
     $sql = "select cast(fol_dibayar_when as date), status_id, sum(fol_dibayar) as sub_total 
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
        $viewData[$dataFolio[$i]["fol_dibayar_when"]][$dataFolio[$i]["status_id"]] = $dataFolio[$i]["sub_total"];
     }

    // --- cari data status kas nya
     $sql = "select status_id, status_nama from global.global_status_pasien order by status_id";
     $rs_dataKas = $dtaccess->Execute($sql);
     $dataKas = $dtaccess->FetchAll($rs_dataKas);
     // unset($rs_namabiaya);
     
     // --- dari data folio per tanggal
     $sql = "select distinct(cast(fol_dibayar_when as date)) from klinik.klinik_folio where fol_lunas = 'y' and CAST(fol_dibayar_when as DATE) between ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     $dataTanggal = $dtaccess->FetchAll($sql);
     // echo $sql;

	
	$counter=0;
		
     $tbHeader[0][0][TABLE_ISI] = "Bulan";
     $tbHeader[0][0][TABLE_WIDTH] = "25%";
	
     $tbHeader[0][1][TABLE_ISI] = "Tanggal";
     $tbHeader[0][1][TABLE_WIDTH] = "25%";
	
     $tbHeader[0][2][TABLE_ISI] = "Pos Kas";
     $tbHeader[0][2][TABLE_WIDTH] = "25%"; 
	
     $tbHeader[0][3][TABLE_ISI] = "Total";
     $tbHeader[0][3][TABLE_WIDTH] = "25%";
	
     $i=0;
     $k=0;
     $grandTotal = 0;
     while ( $i < count($dataTanggal)) {
        $tgl_nya = explode('-', $dataTanggal[$i]["fol_dibayar_when"]);
        $bln_nya = format_date_long($dataTanggal[$i]["fol_dibayar_when"]);
        $bln_nya = explode(' ', $bln_nya);
        $subtotal = 0;
        for ($j=0, $counter=0; $j <= count($dataKas); $j++, $k++, $counter=0) { 
          if ( $j == 0 ) {
            $tbContent[$k][$counter][TABLE_ISI] = '&nbsp;'.$bln_nya[1];
            $tbContent[$k][$counter][TABLE_ALIGN] = "left";
            $tbContent[$k][$counter][TABLE_VALIGN] = "top";
            $tbContent[$k][$counter][TABLE_ROWSPAN] = 11;
            $counter++;

            $tbContent[$k][$counter][TABLE_ISI] = "&nbsp;".$tgl_nya[2];
            $tbContent[$k][$counter][TABLE_ALIGN] = "left";
            $tbContent[$k][$counter][TABLE_VALIGN] = "top";
            $tbContent[$k][$counter][TABLE_ROWSPAN] = 11;
            $counter++;
          }

          if ($j < count($dataKas)) {
            $tbContent[$k][$counter][TABLE_ISI] = "&nbsp;".$dataKas[$j]["status_nama"];
            $tbContent[$k][$counter][TABLE_ALIGN] = "left";
            $counter++;
          
            $tbContent[$k][$counter][TABLE_ISI] = 'Rp. '.currency_format($viewData[$dataTanggal[$i]["fol_dibayar_when"]][$dataKas[$j]["status_id"]]).'&nbsp;';
            $tbContent[$k][$counter][TABLE_ALIGN] = "right";
            $counter++;
            $subtotal += $viewData[$dataTanggal[$i]["fol_dibayar_when"]][$dataKas[$j]["status_id"]];
            $subtotalkas[$dataKas[$j]["status_id"]] += $viewData[$dataTanggal[$i]["fol_dibayar_when"]][$dataKas[$j]["status_id"]];
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
        $i++;
     }

      for ($l=0, $counterBottom=0; $l <= count($dataKas); $l++, $counterBottom=0) { 
        if ($l < count($dataKas)) {
          $tbBottom[$l][$counterBottom][TABLE_ISI] = "&nbsp;".$dataKas[$l]["status_nama"];
          $tbBottom[$l][$counterBottom][TABLE_ALIGN] = "left";
          $tbBottom[$l][$counterBottom][TABLE_COLSPAN] = "3";
          $counterBottom++;

          $tbBottom[$l][$counterBottom][TABLE_ISI] = "Rp&nbsp;".currency_format($subtotalkas[$dataKas[$l]["status_id"]])."&nbsp;";
          $tbBottom[$l][$counterBottom][TABLE_ALIGN] = "right";
          $counterBottom++;       
        }else{
          $tbBottom[$l][$counterBottom][TABLE_ISI] = "&nbsp;Total";
          $tbBottom[$l][$counterBottom][TABLE_ALIGN] = "left";
          $tbBottom[$l][$counterBottom][TABLE_COLSPAN] = "3";
          $counterBottom++;

          $tbBottom[$l][$counterBottom][TABLE_ISI] = "Rp&nbsp;".currency_format($grandTotal)."&nbsp;";
          $tbBottom[$l][$counterBottom][TABLE_ALIGN] = "right";
          $counterBottom++;       
        }
      }

     $tableHeader = "Laporan Kasir Per Jenis Kas";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran_loket_'.$_POST["tgl_awal"].'.xls');
     }
	
     $sql = "select biaya_id, biaya_nama, biaya_jenis 
               from klinik.klinik_biaya a
			order by biaya_id "; 
     $dataBiaya = $dtaccess->FetchAll($sql);
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
<? $plx->Run(); ?>
function CariLayanan(id){ 
	document.getElementById('div_layanan').innerHTML = GetLayanan(id,'type=r');
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
          <td width="10%">&nbsp;Tanggal</td>
          <td width="35%">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               -
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               
          </td>
          <td width="10%">&nbsp;</td>
          <td width="45%">&nbsp;</td>
          <!-- <td width="10%">&nbsp;Jenis Pasien</td>
          <td width="40%">
			<select name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);" onchange="CariLayanan(document.getElementById('cust_usr_jenis').value)">
                    <option value="" >[ Pilih Jenis Pasien ]</option>
                    <?php foreach($bayarPasien as $key => $value) { ?>
                         <option value="<?php echo $key;?>" <?php if($_POST["cust_usr_jenis"]==$key) echo "selected";?>><?php echo $value;?></option>
                    <?php } ?>
			</select>
          </td>  -->
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
<?php } ?>

<?php if($_POST["btnExcel"]) {?>
     <table width="90%" border="1" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="<?php echo (count($dataSplit)+6)?>"><strong>LAPORAN KASIR PERJENIS KAS</strong></td>
          </tr>
     </table>
<?php }?>
<?php echo "Tahun:&nbsp;".$tgl_nya[0]; ?><br />
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>


<?php echo $view->RenderBodyEnd(); ?>
