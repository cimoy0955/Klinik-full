<?php
require_once("root.inc.php");
require_once($ROOT."library/auth.cls.php");
require_once($ROOT."library/textEncrypt.cls.php");
require_once($ROOT."library/datamodel.cls.php");
require_once($ROOT."library/dateFunc.lib.php");
require_once($ROOT."library/inoLiveX.php");
require_once($APLICATION_ROOT."library/view.cls.php");


$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);


$plx = new InoLiveX("GetData");

function GetData($in_nama,$in_kode,$in_alamat,$in_umur,$in_jk){
	global $dtaccess, $APLICATION_ROOT, $idPrj, $idCust;

	
	$table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
	
	$sql_where[] = "1=1"; 
	if($in_nama) $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
	if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_kode."%"));
	if($in_alamat) $sql_where[] = "UPPER(cust_usr_alamat) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_alamat."%"));
	if($in_umur) $sql_where[] = "((current_date - cust_usr_tanggal_lahir)/365)=".$in_umur;
	if($in_jk) $sql_where[] = "UPPER(cust_usr_jenis_kelamin) = ".QuoteValue(DPE_CHAR,strtoupper($in_jk));
	$sql_where = implode(" and ",$sql_where);

	// --- cari data krsnya ---
	$sql = "select cust_usr_id, cust_usr_nama, cust_usr_kode from global.global_customer_user a";
	$sql .= " where ".$sql_where;			
	$sql .= " order by a.cust_usr_nama";
			
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);     
	$dataTable = $dtaccess->FetchAll($rs);
	
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Kode";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "80%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

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
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$APLICATION_ROOT.'images/r_arrowgrnsm.gif" border="0" alt="Pilih" title="Pilih" width="12" height="12" class="img-button" OnClick="javascript: sendValue(\''.$dataTable[$i]["cust_usr_id"].'\',\''.$dataTable[$i]["cust_usr_kode"].'\')"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}

$optionJK[0] = $view->RenderOption("L","Laki-laki",$show);
$optionJK[1] = $view->RenderOption("P","Perempuan",$show);

?>
<?php echo $view->RenderBody("inosoft.css",true); ?>

<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(id,kode) {
		
	self.parent.document.getElementById('id_cust_usr').value = id;
	self.parent.document.getElementById('cust_usr_kode').value = kode;
	self.parent.tb_remove();
}

function Search() {
	var nama = document.getElementById('_name').value;
	var kode = document.getElementById('_kode').value;
	var alamat= document.getElementById('_alamat').value;
	var umur = document.getElementById('_umur').value;
	var jk = document.getElementById('_jk').value;

	GetData(nama,kode,alamat,umur,jk,'target=dv_hasil');
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
				<td align="right" class="tablecontent" width="30%">Nama Pasien</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",30,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">No. Reg</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_kode","_kode",30,200,$_POST["_kode"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Alamat</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_alamat","_alamat",30,200,$_POST["_alamat"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Umur</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_umur","_umur",30,200,$_POST["_umur"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Jenis Kelamin</td>
				<td class="tablecontent">
					<?php echo $view->RenderComboBox("_jk","_jk",$optionJK);?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><center>
					<input type="button" name="btnSearch" value="Cari" class="button" onClick="Search()"/>
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