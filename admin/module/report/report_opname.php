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
     $skr = format_date(getDateToday());
     
    
     if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}
     
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
         
	
     
    $cetakPage = "report_opname_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"]."&penjualan_tipe=".$_POST["penjualan_tipe"];
     
     if ($_POST["tanggal_awal"]) $sql_where[] = " a.opname_tanggal >=".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     if ($_POST["tanggal_akhir"]) $sql_where[] = " a.opname_tanggal <=".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
	   $sql = "select a.*,c.obat_nama            
             from apotik_opname a join apotik_obat_master c on c.obat_id = a.id_item";
     if($sql_where) $sql .= " where ".implode(" and ",$sql_where);
     $sql .= " order by a.opname_tanggal asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
	   $dataTable = $dtaccess->FetchAll($rs);
	   
     //*-- config table ---*//
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "Laporan opname";

		// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Waktu";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Item";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Stok Tercatat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Stok Real";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Selisih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Keterangan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	
  for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	   //hitung total
	  $total+=$dataTable[$i]["opname_total"];
	  
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	
	  $tbContent[$i][$counter][TABLE_ISI] = format_date_long($dataTable[$i]["opname_tanggal"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["obat_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
  
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["opname_stok_tercatat"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["opname_stok_real"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	
	  $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["opname_miss"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	
	  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["opname_keterangan"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	}

	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "right";
	$tbBottom[0][$counter][TABLE_COLSPAN] = count($tbHeader[0]);
	$counter++;
	
	
	
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td>&nbsp;<?php echo $PageHeader;?></td>
     </tr>
</table>

<BR/>


<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
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


		
<BR/>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</body>
</html>
 
