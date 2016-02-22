<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");

         
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $skr = getDateToday();
     
 	    if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}

	
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}
   
   $skr = date("d-m-Y");
	if(!$_GET["tanggal_awal"]) $_GET["tanggal_awal"] = $skr;
	if(!$_GET["tanggal_akhir"]) $_GET["tanggal_akhir"] = $skr;

	if ($_POST["tanggal_awal"] && !$_GET["tanggal_akhir"]) $sql_where[] = "a.pembelian_tanggal = ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));

	if ($_POST["tanggal_awal"] && $_GET["tanggal_akhir"]) $sql_where[] = "a.pembelian_tanggal between ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));

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
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "Laporan Pembelian";

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

       
?>

<!--<?php echo $view->RenderBody("inosoft_prn.css",true,null,false,"inosoft_prn.css"); ?>--> 

<script language="javascript" type="text/javascript">

window.print();

</script>

<style>
@media print {
     #tableprint { display:none; }
}
</style>


<form name="frmView" method="POST" action="<?php echo $thisPage; ?>">

<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="500">
    <tr>
      <td rowspan="3" width="25%" class="tablecontent"><img src="<?echo $APLICATION_ROOT;?>/images/logo_bkmm.gif" width="100" height="80"/></td>
      <td style="text-align:left;font-size:18px;font-family:sans-serif;font-weight:bold;" class="tablecontent">APOTEK</td>
    </tr>
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">R.S. Airlangga</td>
    </tr>
    <!--<tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jl. Gayung Kebonsari Timur 49, Surabaya</td>
    </tr>-->
  </table>
<br>
<br>
<br>
<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="500">    
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN PEMBELIAN</td>
    </tr>
  </table>
<br>
<br>
     <table width="100%" border="0" cellpadding="0" cellspacing="0">
     	<tr>
     		<td>
     			<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?> 
     		</td>
     	</tr>
     </table>

</form>
</body>
<!--<?php echo $view->RenderBodyEnd(); ?>-->

</html>