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
     
     $cetakPage = "report_penjualan_cetak.php?";
         
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}
     
	   $skr = date("d-m-Y");
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     $cetakPage = "report_penjualan_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"]."&penjualan_tipe=".$_POST["penjualan_tipe"];
 
     
     $sql_where[] = "a.penjualan_create >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "a.penjualan_create <= ".QuoteValue(DPE_DATE,DateAdd(date_db($_POST["tanggal_akhir"]),1));
     
	$sql_where[] = "c.obat_nama is not null and a.penjualan_terbayar = 'y'";
    
    if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);
     
     $sql = "select distinct c.obat_nama,b.trans_jumlah,
              b.trans_harga_jual,b.trans_total,b.trans_tipe,b.trans_racik_jumlah,b.trans_racik_perintah, 
             a.penjualan_create, a.penjualan_nomor,a.penjualan_total,a.cust_usr_nama,a.penjualan_id,b.id_penjualan
             from apotik_penjualan a 
             left join apotik_transaksi b on b.id_penjualan = a.penjualan_id and b.trans_tipe = 'J'
             left join apotik_obat_master c on b.id_item = c.obat_id ";
     $sql .= " where ".$sql_where;
     $sql .= " order by a.penjualan_create asc";
     
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
	$dataTable = $dtaccess->FetchAll($rs);
  
  //*-- config table ---*//
  $table = new InoTable("table1","80%","left",null,0,2,1,null);     
  $PageHeader = "Laporan Penjualan";
  
  

	// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Tanggal";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "No. Nota";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Pelanggan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Total Pendapatan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "&nbsp;";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$counter++;
	
  $counter=0;
	$tbHeader[1][$counter][TABLE_ISI] = "Detail";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "No.";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Menu";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Jumlah";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Harga Satuan";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Sub Total";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;

	for($i=0,$m=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$m++,$counter=0){
	   
	  
	  if($dataTable[$i]["penjualan_id"]!=$dataTable[$i-1]["penjualan_id"]){
	  //hitung total
	  //$total+=$dataTable[$i]["penjualan_total"];
	  
	  //hitung total Tax
	 // $totalTax+=$dataTable[$i]["penjualan_ppn"];
	  
		$tbContent[$m][$counter][TABLE_ISI] = $i+1;
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
	  
	  $tbContent[$m][$counter][TABLE_ISI] = format_date($dataTable[$i]["penjualan_create"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomor"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
   
		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_total"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		$totalJual += $dataTable[$i]["penjualan_total"];
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		$j=0;$m++;$counter=0;
    }
		
    $j++;
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
    $tbContent[$m][$counter][TABLE_ISI] = $j."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["obat_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["trans_jumlah"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		$jml += $dataTable[$i]["trans_jumlah"];
    		
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["trans_harga_jual"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;		
		
		$total_transaksi_harga_jual = $dataTable[$i]["trans_harga_jual"] * $dataTable[$i]["trans_jumlah"];
		$total += $total_transaksi_harga_jual;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($total_transaksi_harga_jual);
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
	}

	
	
	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[0][$counter][TABLE_COLSPAN]   = "2";

	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]     = "Total";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "center";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($jml);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($totalJual);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($total);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
      
	
	
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];
	
	
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
          <td width="10%" class="tablecontent">&nbsp;Periode : </td>
          <td width="30%" class="tablecontent" colspan="3">
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
 
