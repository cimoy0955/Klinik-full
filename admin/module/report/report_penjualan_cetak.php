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

	if ($_POST["tanggal_awal"] && !$_GET["tanggal_akhir"]) $sql_where[] = "a.penjualan_create = ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));

	if ($_POST["tanggal_awal"] && $_GET["tanggal_akhir"]) $sql_where[] = "a.penjualan_create between ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));

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
     $table = new InoTable("table1","100%","left",null,1,2,1,null);     
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
	
	$tbBottom[0][$counter][TABLE_ISI]     = "Total";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "center";
	$tbBottom[0][$counter][TABLE_COLSPAN] = 3;
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

<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="100%">
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
<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="100%">    
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN PENJUALAN</td>
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