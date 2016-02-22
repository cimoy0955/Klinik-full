<?php
require_once("root.inc.php");
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
$usrId = $auth->GetUserId();


$plx = new InoLiveX("GetData");


//echo $outlet;

function GetData($in_nama=null){
	global $dtaccess,$outlet,$APLICATION_ROOT;

	
	$table = new InoTable("table1","100%","center",null,0,1,1,null,"tblForm");
	
	// --- cari data menunya ---

	if($in_nama) $sql_where .= " where UPPER(item_nama) like '%".strtoupper($in_nama)."%'"; 

	$sql = "select * from pos_item";
	if($sql_where){
	$sql .= $sql_where;
  }			
	$sql .= " order by item_nama";
	
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);     
	$dataTable = $dtaccess->FetchAll($rs);
	
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
  
  $tbHeader[0][$counter][TABLE_ISI] = "Kode";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Menu";
	$tbHeader[0][$counter][TABLE_WIDTH] = "35%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

  $tbHeader[0][$counter][TABLE_ISI] = "Harga";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Pilih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {
		
		($i%2==0)? $class="tablecontent":$class="tablecontent-odd";

		$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;
		
    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;Rp.&nbsp;".currency_format($dataTable[$i]["item_harga_jual"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$APLICATION_ROOT.'images/r_arrowgrnsm.gif" border="0" alt="Pilih" title="Pilih" width="12" height="12" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["item_nama"])).'\',\''.$dataTable[$i]["item_id"].'\',\''.currency_format($dataTable[$i]["item_harga_jual"]).'\',\''.$dataTable[$i]["item_kode"].'\')"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}


?>
<?php echo $view->RenderBody("inventori.css",true); ;?>


<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(nama,id,harga,kode) {
	self.parent.document.getElementById('item_nama').value = nama;
	self.parent.document.getElementById('item_id').value = id;
	self.parent.document.getElementById('txtHargaSatuan').value = harga;
	self.parent.document.getElementById('item_kode').value = kode;
	self.parent.document.getElementById('txtJumlah').focus();
	self.parent.tb_remove();
}

function Search(nama) {
	GetData(nama,'target=dv_hasil');
}

</script>

<form name="frmSearch">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;Menu</center></td>
			</tr>
			<tr>
				<td align="right" class="tablecontent">Nama Menu</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><center>
					<input type="button" name="btnSearch" value="Cari" class="button" onClick="Search(document.getElementById('_name').value)"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="button" /></center>
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

