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
     
     if(!$auth->IsAllowed("registrasi",PRIV_READ)){
     	die("access_denied");
     	exit(1);
     	
     } elseif($auth->IsAllowed("registrasi",PRIV_READ)===1){
     	echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
     	exit(1);
     }
     
     $isAllowedDel = $auth->IsAllowed("registrasi",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("registrasi",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("registrasi",PRIV_CREATE);
     
     $editPage = "pasien_edit.php?";
     
     
     $table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
     
     // -- paging config ---//
     $recordPerPage = 50;
     if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
     else $currPage = 1;
     $startPage = ($currPage-1)*$recordPerPage;
     $endPage = $startPage + $recordPerPage;
     // -- end paging config ---//
     
     
     if ($_POST["btnDelete"]) {
     	$custId = & $_POST["cbDelete"];
     	
     	for($i=0,$n=count($custId);$i<$n;$i++){
     		$sql = "delete from global.global_customer_user 
     				where cust_usr_id = ".QuoteValue(DPE_CHAR,$custId[$i]);
     		$dtaccess->Execute($sql);
     	}
     	$_POST["btnSearch"] = 1;
     }	
     
     
	$sql_where[] = "1=1"; 
	if($_POST["_nama"]) $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["_nama"]."%"));
	if($_POST["_kode"]) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["_kode"]."%"));
	if($_POST["_alamat"]) $sql_where[] = "UPPER(cust_usr_alamat) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["_alamat"]."%"));
	if($_POST["_umur"]) $sql_where[] = "((current_date - cust_usr_tanggal_lahir)/365)=".$_POST["_umur"];
	if($_POST["_jk"]) $sql_where[] = "UPPER(cust_usr_jenis_kelamin) = ".QuoteValue(DPE_CHAR,strtoupper($_POST["_jk"]));
	$sql_where = implode(" and ",$sql_where);
	
	// --- cari data pasiennya ---
	$sql = "select cust_usr_id, cust_usr_nama, cust_usr_kode , cust_usr_alamat from global.global_customer_user a";
	$sql .= " where ".$sql_where;			
	$sql .= " order by a.cust_usr_nama";
			     			
	$rs = $dtaccess->Query($sql,$recordPerPage,$startPage,DB_SCHEMA_HRIS);
	$dataTable = $dtaccess->FetchAll($rs);
	
	// --- ngitung jml data e ---
	$sql = "select count(cust_usr_id) as total
               from global.global_customer_user
               where ".$sql_where;
     $rsNum = $dtaccess->Execute($sql);
     $numRows = $dtaccess->Fetch($rsNum);
     
	$counter = 0;          
	
	if($isAllowedDel){
		$tbHeader[0][$counter][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
		$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
		$counter++;
	}
	
	if($isAllowedUpdate){
		$tbHeader[0][$counter][TABLE_ISI] = "Edit";
		$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
		$counter++;
	}

	
  $tbHeader[0][$counter][TABLE_ISI] = "Barcode";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Kode";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Alamat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "50%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {
		
		($i%2==0)? $class="tablecontent":$class="tablecontent-odd";
	
		if($isAllowedDel) {
			$tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["cust_usr_id"].'">';               
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
		}
		
		if($isAllowedUpdate) {
			$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["cust_usr_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
		}
		
	   $tbContent[$i][$counter][TABLE_ISI] = '<a href="#"><img hspace="0" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_lampiran2.gif" OnClick="javascript:OpenPrintBarcode_1('.$dataTable[$i]["cust_usr_id"].')" alt="Print Barcode" title="Print Barcode" border="0"></a>';
	   $tbContent[$i][$counter][TABLE_ALIGN] = "center";
	   $counter++;
               
		$tbContent[$i][$counter][TABLE_ISI] = ($startPage+$i+1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
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
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".nl2br($dataTable[$i]["cust_usr_alamat"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
     	
     
     $optionJK[0] = $view->RenderOption("","[All]",$show);
     $optionJK[1] = $view->RenderOption("L","Laki-laki",$show);
     $optionJK[2] = $view->RenderOption("P","Perempuan",$show);

?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<script language="JavaScript">
function OpenPrintBarcode_1(cust_usr_id) 
{
	var _child_url = 'pasien_print_single.php?idpas='+cust_usr_id;
	var new_win;new_win=window.open(_child_url,'wndprint','resize=yes,menubar=yes,scrollbars=yes,width=300,height=150,left=10,top=10');new_win.focus();
}
</script>
<form name="frmSearch" action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
<table border="1" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="1" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian Pasien&nbsp;</center></td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Nama Pasien</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_nama","_nama",30,200,$_POST["_nama"],false,false);?>
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
					<input type="submit" name="btnSearch" value="Cari" class="button"/>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>

<?php if($dataTable) { ?>
<input type="submit" name="btnDelete" value="Delete" class="button" OnClick="javascript:DeleteDetil();">
<?php echo $view->RenderButton(BTN_BUTTON,"btnAdd","btnAdd","Add","button",false,"onClick=\"window.document.location.href='$editPage'\"");?>&nbsp;

<?php echo $view->RenderPaging($numRows["total"], $recordPerPage, $currPage ); ?>

<div id="dv_hasil"><?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom);?></div>

<input type="submit" name="btnDelete" value="Delete" class="button" OnClick="javascript:DeleteDetil();">
<?php echo $view->RenderButton(BTN_BUTTON,"btnAdd","btnAdd","Add","button",false,"onClick=\"window.document.location.href='$editPage'\"");?>&nbsp;

<?php } ?>
</form>
	
<?php echo $view->SetFocus("_nama",true);?>

<?php echo $view->RenderBodyEnd(); ?>