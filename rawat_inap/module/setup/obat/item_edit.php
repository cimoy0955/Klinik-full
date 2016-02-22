<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
	require_once($APLICATION_ROOT."library/view.cls.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	$auth = new CAuth();
     $err_code = 0;
	
	$plx = new InoLiveX("CheckDataItem");
	
     if(!$auth->IsAllowed("item",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("item",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataItem($itemKode,$itemId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.item_id FROM inventori.inv_item a 
                    WHERE upper(a.item_kode) = ".QuoteValue(DPE_CHAR,strtoupper($itemKode));
                    
          if($itemId) $sql .= " and a.item_id <> ".QuoteValue(DPE_NUMERIC,$itemId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaItem = $dtaccess->Fetch($rs);
          
		return $dataAdaItem["item_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["item_id"])  $itemId = & $_POST["item_id"];
     

     $backPage = "item_view.php";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $itemId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.item_id,item_nama,item_kode,id_kat_item,item_fisik from inventori.inv_item a 
				where item_id = ".QuoteValue(DPE_CHAR,$itemId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["item_fisik"] = $row_edit["item_fisik"];
          $_POST["item_kode"] = $row_edit["item_kode"];
          $_POST["item_nama"] = $row_edit["item_nama"];
          $_POST["id_kat_item"] = $row_edit["id_kat_item"];
     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $itemId = & $_POST["item_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "inventori.inv_item";
               
               $dbField[0] = "item_id";   // PK
               $dbField[1] = "item_kode";
               $dbField[2] = "item_nama";
               $dbField[3] = "id_kat_item";
               $dbField[4] = "item_fisik";
			
               if(!$itemId) $itemId = $dtaccess->GetNewID("inventori.inv_item","item_id",DB_SCHEMA);
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$itemId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["item_kode"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_nama"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_kat_item"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["item_fisik"]);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               header("location:".$backPage);
               exit();        
          }
     }
 
     if ($_POST["btnDelete"]) {
          $itemId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($itemId);$i<$n;$i++){
               $sql = "delete from inventori.inv_item  
                         where item_id = ".QuoteValue(DPE_CHAR,$itemId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }	

     // -- cari Kaetegori ---
     $sql = "select kat_item_id,kat_item_nama from inventori.inv_kat_item
               order by kat_item_id";
     $rs = $dtaccess->Execute($sql);
     $dataKatItem = $dtaccess->FetchAll($rs);

     for($i=0,$n=count($dataKatItem);$i<$n;$i++) {
          unset($show);
          if($_POST["id_kat_item"]==$dataKatItem[$i]["kat_item_id"]) $show = "selected";
          $katItem[$i] = $view->RenderOption($dataKatItem[$i]["kat_item_id"],$dataKatItem[$i]["kat_item_nama"],$show);
     }

     // -- cari fisik---
     $sql = "select fisik_id,fisik_nama from inventori.inv_fisik
               order by fisik_id";
     $rs = $dtaccess->Execute($sql);
     $dataFisik = $dtaccess->FetchAll($rs);

     for($i=0,$n=count($dataFisik);$i<$n;$i++) {
          unset($show);
          if($_POST["item_fisik"]==$dataFisik[$i]["fisik_id"]) $show = "selected";
          $fisik[$i] = $view->RenderOption($dataFisik[$i]["fisik_id"],$dataFisik[$i]["fisik_nama"],$show);
     }
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{     
     if(!frm.item_kode.value){
		alert('Kode Harus Diisi');
		frm.item_kode.focus();
          return false;
	}
	
	if(!frm.item_nama.value){
		alert('Nama Harus Diisi');
		frm.item_nama.focus();
          return false;
	}	
	
	if(CheckDataItem(frm.item_kode.value,frm.item_id.value,'type=r')){
		alert('Kode Sudah Ada');
		frm.item_kode.focus();
		frm.item_kode.select();
		return false;
	}
	
	return true;
          
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Obat</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Obat Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Kategori</strong>&nbsp;</td>
               <td width="70%">
                         <?php echo $view->RenderComboBox("id_kat_item","id_kat_item",$katItem,null,null);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Kode</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("item_kode","item_kode","10","20",$_POST["item_kode"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama</strong>&nbsp;</td>
               <td width="70%">
				<?php echo $view->RenderTextBox("item_nama","item_nama","50","100",$_POST["item_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Bentuk</strong>&nbsp;</td>
               <td width="70%">
                         <?php echo $view->RenderComboBox("item_fisik","item_fisik",$fisik,null,null);?>
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='item_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.item_kode.focus();</script>
<?php echo $view->RenderHidden("item_id","item_id",$itemId);?>
<?php echo $view->RenderHidden("item_jenis","item_jenis",$_POST["item_jenis"]);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
