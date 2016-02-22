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
if(!$_POST["kelas"]) $_POST["kelas"] = $_GET["kelas"];

$plx = new InoLiveX("GetData");

function GetData($in_nama=null,$in_kode=null,$in_kelas){
	global $dtaccess, $APLICATION_ROOT;

	
	$table = new InoTable("table1","100%","center",null,0,0,0,null,"tblForm");
	
	
	if($in_nama) $sql_where[] = "UPPER(tarif_ri_deskripsi) like '%".strtoupper($in_name)."%'";
	if($in_kode) $sql_where[] = "UPPER(tarif_ri_code) like '".strtoupper($in_kode)."%'";
	 
	$sql_where = implode($sql_where," and ");
/* 
          
          $sql .= " order by UPPER(a.biaya_nama), a.biaya_kode";
      */        
	// --- cari datanya ---
	$sql = "select * from klinik.klinik_tarif_ri ";
	
	if($sql_where){
               $sql = $sql." where ".$sql_where;
          }		
	$sql .= " order by tarif_ri_code, tarif_ri_deskripsi";
			
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);     
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

	$tbHeader[0][$counter][TABLE_ISI] = "Deskripsi";
	$tbHeader[0][$counter][TABLE_WIDTH] = "45%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Biaya";
	$tbHeader[0][$counter][TABLE_WIDTH] = "35%";
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
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["tarif_ri_code"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["tarif_ri_deskripsi"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
	        if($in_kelas=="i"){
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;Rp. ".currency_format($dataTable[$i]["tarif_ri_kelas_1"]);
		$biayaRI = $dataTable[$i]["tarif_ri_kelas_1"];
		}elseif($in_kelas=="ii"){
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;Rp. ".currency_format($dataTable[$i]["tarif_ri_kelas_2"]);
		$biayaRI = $dataTable[$i]["tarif_ri_kelas_2"];
		}elseif($in_kelas=="iii"){
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;Rp. ".currency_format($dataTable[$i]["tarif_ri_kelas_3"]);
		$biayaRI = $dataTable[$i]["tarif_ri_kelas_3"];
		}
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer;" src="'.$APLICATION_ROOT.'images/r_arrowgrnsm.gif" border="0" alt="Pilih" title="Pilih" width="12" height="12" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["tarif_ri_deskripsi"])).'\',\''.$dataTable[$i]["tarif_ri_id"].'\',\''.$dataTable[$i]["tarif_ri_code"].'\',\''.currency_format($biayaRI).'\')"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}


?>
<?php echo $view->RenderBody("inosoft.css",true,'onload="Search(document.getElementById(\'_name\').value,document.getElementById(\'_kode\').value,\''.$_GET["kelas"].'\');"');?>


<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(nama,id,kode,harga) {
	self.parent.document.getElementById('ina_nama').value = nama;
	self.parent.document.getElementById('ina_id').value = id;
	self.parent.document.getElementById('ina_kode').value = kode;
	self.parent.document.getElementById('ina_nominal').value = harga;
	self.parent.tb_remove();
}

function Search(nama,kode,kelas) {
	GetData(nama,kode,kelas,'target=dv_hasil');
}

</script>

<form name="frmSearch">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;biaya</center></td>
			</tr>
			<tr>
				<td align="right" class="tablecontent">Kode biaya</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_kode","_kode",30,200,$_POST["_kode"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent">Nama biaya</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",30,200,$_POST["_name"],false,false);?>
					<input type="hidden" name="kelas" value="<?php echo $_POST["kelas"];?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class="tablecontent"><center>
					<input type="button" name="btnSearch" value="Cari" class="button" onClick="Search(document.getElementById('_name').value,document.getElementById('_kode').value,document.getElementById('kelas').value);"/>
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

