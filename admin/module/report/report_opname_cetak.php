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

	if ($_POST["tanggal_awal"] && !$_GET["tanggal_akhir"]) $sql_where[] = "a.opname_tanggal = ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));

	if ($_POST["tanggal_awal"] && $_GET["tanggal_akhir"]) $sql_where[] = "a.opname_tanggal between ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));

  $sql = "select a.*,c.obat_nama            
             from apotik_opname a join apotik_obat_master c on c.obat_id = a.id_item";
     if($sql_where) $sql .= " where ".implode(" and ",$sql_where);
     $sql .= " order by a.opname_tanggal asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
	   $dataTable = $dtaccess->FetchAll($rs);
	   
     //*-- config table ---*//
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "Laporan Opname";

	//*-- config table ---*//
	$table = new InoTable("table1","100%","left",null,0,2,1,null);     
	$PageHeader = "LAPORAN PEMBELIAN";

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
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN OPNAME</td>
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