<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");    
     require_once($APLICATION_ROOT."library/view.cls.php");


$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);


$plx = new InoLiveX("GetData");

function GetData($in_total,$in_kode,$in_nama,$in_order=null){
	global $dtaccess, $APLICATION_ROOT, $idPrj, $idCust;

	
	$table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
	
	
	if($in_nama) $sql_where[] = "UPPER(biaya_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($in_nama)."%");
	if($in_total) $sql_where[] = "biaya_total = ".QuoteValue(DPE_NUMERIC,$in_total);
	if($in_kode) $sql_where[] ="upper(biaya_kode) like ".QuoteValue(DPE_CHAR,"%".strtoupper($in_kode)."%");
        $sql_where[] = "biaya_kode like 'RF%'";
	 
	 
	 $sql_where = implode(" and ",$sql_where);	 
/* 
          
          $sql .= " order by UPPER(a.biaya_nama), a.biaya_kode";
      */        
      // --- cari datanya ---
      $sql = "select * from klinik.klinik_biaya";
      
      if($sql_where){
	     $sql = $sql." where ".$sql_where;
	}		
      
    if($in_order != 'null') $sql .= " order by ".$in_order;
    else $sql .= " order by biaya_kode";
			
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);     
	$dataTable = $dtaccess->FetchAll($rs);
	
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "<a href=\"#\" onclick=\"Search(document.getElementById('_total').value,document.getElementById('_kode').value,document.getElementById('_name').value,'biaya_kode');\" style=\"color:#ffffff;\">Kode</a>";
	$tbHeader[0][$counter][TABLE_WIDTH] = "14%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "<a href=\"#\" onclick=\"Search(document.getElementById('_total').value,document.getElementById('_kode').value,document.getElementById('_name').value,'biaya_nama');\" style=\"color:#ffffff;\">Nama</a>";
	$tbHeader[0][$counter][TABLE_WIDTH] = "70%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "<a href=\"#\" onclick=\"Search(document.getElementById('_total').value,document.getElementById('_kode').value,document.getElementById('_name').value,'biaya_total');\" style=\"color:#ffffff;\">Biaya</a>";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		/*
	$tbHeader[0][$counter][TABLE_ISI] = "Stok";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
*/
	$tbHeader[0][$counter][TABLE_ISI] = "Pilih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {
		
		($i%2==0)? $class="tablecontent":$class="tablecontent-odd";

		$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["biaya_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["biaya_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
				
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["biaya_total"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		/*
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["tindakan_stok"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		*/
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$APLICATION_ROOT.'images/r_arrowgrnsm.gif" border="0" alt="Pilih" title="Pilih" width="12" height="12" class="img-button" OnClick="javascript: sendValue(\''.$dataTable[$i]["biaya_id"].'\',\''.currency_format($dataTable[$i]["biaya_total"]).'\',\''.addslashes(htmlentities($dataTable[$i]["biaya_nama"])).'\')"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}


?>
<?php echo $view->RenderBody("inosoft.css",true); ?>

<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(id,total,nama) {
		
	self.parent.document.getElementById('tindakan_nama_<?php echo $_GET["el"];?>').value = nama;
	self.parent.document.getElementById('tindakan_total_<?php echo $_GET["el"];?>').value = total;
	self.parent.document.getElementById('tindakan_id_<?php echo $_GET["el"];?>').value = id;
	self.parent.tb_remove();
}

function Search(total,kode,nama,order) {
	GetData(total,kode,nama,order,'target=dv_hasil');
}

</script>

<form name="frmSearch">
<table border="1" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="1" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;</center></td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Nama</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",30,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Kode</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_kode","_kode",30,200,$_POST["_kode"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Harga</td>
			<td class="tablecontent">
					<?php echo $view->RenderTextBox("_total","_total",30,200,$_POST["_total"],false,false);?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><center>
					<input type="button" name="btnSearch" value="Cari" class="button" onClick="Search(document.getElementById('_total').value,document.getElementById('_kode').value,document.getElementById('_name').value,'null')"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove()" class="button" /></center>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</form>

<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_name",true);?>

<?php echo $view->RenderBodyEnd(); ?>