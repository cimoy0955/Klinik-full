<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");

     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     //$_POST["id_petugas"]= $auth->GetUserId();   
     //$usrId = $auth->GetUserId();   
     
     //$cetakPage = "report_pembelian_cetak.php?";
  //   // --- link untuk cetak ---
  //        $cetakPage = "sisipan_cetak.php?siswa_nis=".$_POST["siswa_nis"]
         
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}
     
	   $cetakPage = "report_pembelian_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"];
	$skr = date("d-m-Y");
	if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
	if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;

	if ($_POST["tanggal_awal"] && !$_POST["tanggal_akhir"]) $sql_where[] = "a.pembelian_create = ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));

	if ($_POST["tanggal_awal"] && $_POST["tanggal_akhir"]) $sql_where[] = "a.pembelian_create between ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));

	$sql_where[] = "c.obat_nama is not null";
    
    if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);
     
	$sql = "select a.pembelian_create, a.pembelian_nomor,
			a.pembelian_toko, c.obat_nama, b.trans_harga_beli, 
			b.trans_jumlah
			from apotik_pembelian a 
			left join apotik_transaksi b on a.pembelian_id = b.id_pembelian and b.trans_tipe = 'B' 
			left join apotik_obat_master c on b.id_item = c.obat_id ";
            
    if ($sql_where) 
	$sql .= " where ".$sql_where;
	$sql .= " order by a.pembelian_create asc";
	//echo $sql;
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
	$dataTable = $dtaccess->FetchAll($rs);

	//*-- config table ---*//
	$table = new InoTable("table1","100%","left",null,0,2,1,null);     
	$PageHeader = "LAPORAN PEMBELIAN";

	// --- construct new table ---- //
	$tbHeader[0][0][TABLE_ISI] = "No";
	$tbHeader[0][0][TABLE_WIDTH] = "5%";

	$tbHeader[0][1][TABLE_ISI] = "Tanggal";
	$tbHeader[0][1][TABLE_WIDTH] = "15%";

	$tbHeader[0][2][TABLE_ISI] = "No. Nota";
	$tbHeader[0][2][TABLE_WIDTH] = "20%";

	$tbHeader[0][3][TABLE_ISI] = "Nama Perusahaan/Pembeli";
	$tbHeader[0][3][TABLE_WIDTH] = "30%";

	$tbHeader[0][4][TABLE_ISI] = "Nama Obat";
	$tbHeader[0][4][TABLE_WIDTH] = "10%";

	$tbHeader[0][5][TABLE_ISI] = "Harga Beli";
	$tbHeader[0][5][TABLE_WIDTH] = "20%";

	$tbHeader[0][6][TABLE_ISI] = "Jumlah";
	$tbHeader[0][6][TABLE_WIDTH] = "30%";

	$tbHeader[0][7][TABLE_ISI] = "Total";
	$tbHeader[0][7][TABLE_WIDTH] = "30%";


	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){		
	
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["pembelian_create"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembelian_nomor"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembelian_toko"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["obat_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["trans_harga_beli"])."&nbsp;&nbsp;";
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;

		$total_transaksi_harga_beli = $total_transaksi_harga_beli+ ($dataTable[$i]["trans_harga_beli"] * $dataTable[$i]["trans_jumlah"]);

		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["trans_jumlah"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$total_transaksi_jumlah = $total_transaksi_jumlah+$dataTable[$i]["trans_jumlah"];
		
		$total = $dataTable[$i]["trans_harga_beli"] * $dataTable[$i]["trans_jumlah"];
		
		$jumlah = $jumlah + $total;
		
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($total);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;

	}
		
	$tbBottom[0][0][TABLE_ISI]     = "Total Keseluruhan : ";
	$tbBottom[0][0][TABLE_ALIGN]   = "right";
	$tbBottom[0][0][TABLE_COLSPAN] = 6;

//	$tbBottom[0][1][TABLE_ISI]   = currency_format($total_transaksi_harga_beli)."&nbsp;&nbsp;";
//	$tbBottom[0][1][TABLE_ALIGN] = "right";

	$tbBottom[0][1][TABLE_ISI]     = currency_format($total_transaksi_jumlah);
	$tbBottom[0][1][TABLE_ALIGN]   = "center";
	$tbBottom[0][1][TABLE_COLSPAN] = 1;
	
	$tbBottom[0][2][TABLE_ISI]     = currency_format($jumlah);
	$tbBottom[0][2][TABLE_ALIGN]   = "center";
	$tbBottom[0][2][TABLE_COLSPAN] = 1;

	$tbBottom[1][0][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[1][0][TABLE_ALIGN]   = "right";
	$tbBottom[1][0][TABLE_COLSPAN] = 8;	
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>

<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" >
	<tr>
		<td width="10%" class="tablecontent">&nbsp;Periode</td>
		<td width="30%">
			<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
			- 
			<?php echo $view->RenderTextBox("tanggal_akhir","tanggal_akhir","12","12",$_POST["tanggal_akhir"],"inputField", "readonly",false);?>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/></td>		 
	  <td>
			<input type="submit" name="btnLanjut" value="Lanjut" class="button">
		</td> 
	</tr>
</table>
</form>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tanggal_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });

    Calendar.setup({
        inputField     :    "tanggal_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>


		
<BR>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</body>
</html>
 
