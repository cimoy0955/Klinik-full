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
     $table = new InoTable("table","100%","left");
 
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
	
     $sql = "select a.id_biaya, sum(a.fol_dibayar) as dibayarnya, count(a.fol_id) as jumlah_dibayar from klinik.klinik_folio a
              join klinik.klinik_registrasi d on d.reg_id = a.id_reg and a.id_cust_usr = d.id_cust_usr ";
    $sql .= " where ".$sql_where; 
  $sql .= " group by a.id_biaya";
  // echo $sql;
     $dataFolioPerLayanan = $dtaccess->FetchAll($sql);

     $sql = " select biaya_id, biaya_nama from klinik.klinik_biaya";
     $rs_namabiaya = $dtaccess->Execute($sql);
     while($dataBiayanya = $dtaccess->Fetch($rs_namabiaya)){
        $namaBiaya[$dataBiayanya['biaya_id']] = $dataBiayanya['biaya_nama'];
     }
     unset($rs_namabiaya);
     $m=0;

     $tgl_nya = explode('-', $_POST["tgl_akhir"]);
     $bln_nya = format_date_long($_POST["tgl_akhir"]);
     $bln_nya = explode(' ', $bln_nya);
	
	$counter=0;
		
     $tbHeader[0][0][TABLE_ISI] = "Bulan";
     $tbHeader[0][0][TABLE_WIDTH] = "25%";
	
     $tbHeader[0][1][TABLE_ISI] = "Tanggal";
     $tbHeader[0][1][TABLE_WIDTH] = "25%";
	
     $tbHeader[0][2][TABLE_ISI] = "Nama Layanan";
     $tbHeader[0][2][TABLE_WIDTH] = "25%"; 
	
     $tbHeader[0][$n+5][TABLE_ISI] = "Total";
     $tbHeader[0][$n+5][TABLE_WIDTH] = "25%";
	
     
     for($i=0,$counter=0,$n=count($dataFolioPerLayanan);$i<$n;$i++,$counter=0){
      if($i==0){
	       $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;'.$bln_nya[1];
	       $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	       $counter++;
	 
	       $tbContent[$i][$counter][TABLE_ISI] = "&nbsp".$tgl_nya[0];
	       $tbContent[$i][$counter][TABLE_ALIGN] = "left";
	       $counter++;
	     }else{
        $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
         $tbContent[$i][$counter][TABLE_ALIGN] = "right";
         $counter++;
   
         $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
         $tbContent[$i][$counter][TABLE_ALIGN] = "left";
         $counter++;
       }
          $tbContent[$i][$counter][TABLE_ISI] = $namaBiaya[$dataFolioPerLayanan[$i]["id_biaya"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
        
          $tbContent[$i][$counter][TABLE_ISI] = $dataFolioPerLayanan[$i]["jumlah_dibayar"].'<br>'.'Rp. '.currency_format($dataFolioPerLayanan[$i]["dibayarnya"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
	     $nDibayar += $dataFolioPerLayanan[$i]["jumlah_dibayar"];
       $totDibayar += $dataFolioPerLayanan[$i]["dibayarnya"];
     }
     
    
     $tbBottom[0][0][TABLE_ISI] = $bln_nya[1];
     $tbBottom[0][0][TABLE_WIDTH] = "30%";
     $tbBottom[0][0][TABLE_COLSPAN] = 3;
     $tbBottom[0][0][TABLE_ALIGN] = "center";
     $tbBottom[0][1][TABLE_ISI] = $nDibayar;
     $tbBottom[0][1][TABLE_WIDTH] = "30%";
     $tbBottom[0][1][TABLE_ALIGN] = "right";
     
     $tbBottom[1][0][TABLE_ISI] = $bln_nya[1];
     $tbBottom[1][0][TABLE_WIDTH] = "30%";
     $tbBottom[1][0][TABLE_COLSPAN] = 3;
     $tbBottom[1][0][TABLE_ALIGN] = "center";
     $tbBottom[1][1][TABLE_ISI] = 'Rp. '.currency_format($totDibayar);
     $tbBottom[1][1][TABLE_WIDTH] = "30%";
     $tbBottom[1][1][TABLE_ALIGN] = "right";
     
     $tbBottom[2][0][TABLE_ISI] = "Total";
     $tbBottom[2][0][TABLE_WIDTH] = "30%";
     $tbBottom[2][0][TABLE_COLSPAN] = 3;
     $tbBottom[2][0][TABLE_ALIGN] = "center";
     $tbBottom[2][1][TABLE_ISI] = $nDibayar;
     $tbBottom[2][1][TABLE_WIDTH] = "30%";
     $tbBottom[2][1][TABLE_ALIGN] = "right";

     $tbBottom[3][0][TABLE_ISI] = "Total";
     $tbBottom[3][0][TABLE_WIDTH] = "30%";
     $tbBottom[3][0][TABLE_COLSPAN] = 3;
     $tbBottom[3][0][TABLE_ALIGN] = "center";
     $tbBottom[3][1][TABLE_ISI] = 'Rp. '.currency_format($totDibayar);
     $tbBottom[3][1][TABLE_WIDTH] = "30%";
     $tbBottom[3][1][TABLE_ALIGN] = "right";

     $tableHeader = "Report Pendapatan Berdasarkan Layanan";

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
          <td width="15%">&nbsp;Tanggal</td>
          <td width="35%">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               -
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               
          </td>
          <td width="10%">&nbsp;Jenis Pasien</td>
          <td width="40%">
			<select name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);" onchange="CariLayanan(document.getElementById('cust_usr_jenis').value)">
                    <option value="" >[ Pilih Jenis Pasien ]</option>
                    <?php foreach($bayarPasien as $key => $value) { ?>
                         <option value="<?php echo $key;?>" <?php if($_POST["cust_usr_jenis"]==$key) echo "selected";?>><?php echo $value;?></option>
                    <?php } ?>
			</select>
          </td> 
     </tr>
     <!-- <tr class="tablecontent">
		<td>&nbsp;Layanan</td>
          <td colspan="3">
			<div id="div_layanan">
				<?php if($_POST["cust_usr_jenis"] && $_POST["cust_usr_jenis"]!=PASIEN_BAYAR_SWADAYA) {
					echo $bayarNama[BIAYA_KARTU];
				} else { ?>
					<select name="id_biaya">
						 <option value="">[ Pilih Layanan Biaya ]</option>
						 <?php for($i=0,$n=count($dataBiaya);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataBiaya[$i]["biaya_id"];?>" <?php if($_POST["id_biaya"]==$dataBiaya[$i]["biaya_id"]) echo "selected";?>><?php echo $rawatStatus[$dataBiaya[$i]["biaya_jenis"]]." - ".$dataBiaya[$i]["biaya_nama"];?></option>
						 <?php } ?>
							 <option value="<?php echo STATUS_OPERASI;?>" <?php if($_POST["id_biaya"]==STATUS_OPERASI) echo "selected";?>><?php echo $biayaStatus[STATUS_OPERASI];?></option>
					 </select>
				<?php } ?>
			</div>
		</td>
	</tr> -->
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
     <table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="<?php echo (count($dataSplit)+6)?>"><strong>BUKU PENERIMAAN BIAYA PELAYANAN KASIR<br/>BKMM PROP. JATIM<br/>BUKU PENERIMAAN UMUM TAHUN <?php echo $dataTable[0]["tahun"]?></strong></td>
          </tr>
     </table>
<?php }?>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>


<?php echo $view->RenderBodyEnd(); ?>
