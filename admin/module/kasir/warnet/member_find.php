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


$plx = new InoLiveX("GetData");

function GetData($in_nama=null,$in_login=null){
	global $dtaccess, $APLICATION_ROOT;

	
	$table = new InoTable("table1","100%","center",null,0,1,1,null,"tblForm");
	
	if($in_nama) $sql_where = " and UPPER(member_nama) like '%".strtoupper($in_nama)."%'"; 
	if($in_login) $sql_where = " and UPPER(usr_loginname) like '%".strtoupper($in_login)."%'"; 

	// --- cari data krsnya ---
	$sql = "select member_id, member_nama, member_aktif
			from mp_member a
			join global_auth_user b on a.id_usr = b.usr_id 
			where member_tipe='".MEMBER_TIPE_MEMBER."'";
	$sql .= $sql_where;			
	$sql .= " order by a.member_nama";
			
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);     
	$dataTable = $dtaccess->FetchAll($rs);
	
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Member";
	$tbHeader[0][$counter][TABLE_WIDTH] = "64%";
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
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["member_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$APLICATION_ROOT.'images/r_arrowgrnsm.gif" border="0" alt="Pilih" title="Pilih" width="12" height="12" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["member_nama"])).'\',\''.$dataTable[$i]["member_id"].'\')"/>';
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

function sendValue(nama,id) {
	self.parent.document.getElementById('member_nama').value = nama;
	self.parent.document.getElementById('id_member').value = id;
	self.parent.tb_remove();
}

function Search(nama,login) {
	GetData(nama,login,'target=dv_hasil');
}

</script>

<form name="frmSearch">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;Member</center></td>
			</tr>
			<tr>
				<td align="right" class="tablecontent">Nama Member</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent">Login Member</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_login","_login",50,200,$_POST["_login"],false,false);?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><center>
					<input type="button" name="btnSearch" value="Cari" class="button" onClick="Search(document.getElementById('_name').value,document.getElementById('_login').value)"/>
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

