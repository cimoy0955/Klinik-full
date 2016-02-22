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
	
     if(!$auth->IsAllowed("setup_dosis",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_dosis",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataItem($dosisKode,$dosisId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.dosis_id FROM inventori.inv_dosis a 
                    WHERE upper(a.dosis_kode) = ".QuoteValue(DPE_CHAR,strtoupper($dosisKode));
                    
          if($dosisId) $sql .= " and a.dosis_id <> ".QuoteValue(DPE_NUMERIC,$dosisId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaItem = $dtaccess->Fetch($rs);
          
		return $dataAdaItem["dosis_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["dosis_id"])  $dosisId = & $_POST["dosis_id"];

     $backPage = "dosis_view.php";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $dosisId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.dosis_id,dosis_nama,id_fisik from inventori.inv_dosis a 
				where dosis_id = ".QuoteValue(DPE_CHAR,$dosisId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["dosis_nama"] = $row_edit["dosis_nama"];
          $_POST["id_fisik"] = $row_edit["id_fisik"];
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
               $dosisId = & $_POST["dosis_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "inventori.inv_dosis";
               
               $dbField[0] = "dosis_id";   // PK
               $dbField[1] = "dosis_nama";
               $dbField[2] = "id_fisik";
			
               if(!$dosisId) $dosisId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$dosisId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["dosis_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_fisik"]);
			
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
          $dosisId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($dosisId);$i<$n;$i++){
               $sql = "delete from inventori.inv_dosis  
                         where dosis_id = ".QuoteValue(DPE_CHAR,$dosisId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }	

     $sql = "select fisik_id,fisik_nama from inventori.inv_fisik
               order by fisik_id";
     $rs = $dtaccess->Execute($sql);
     $dataFisik = $dtaccess->FetchAll($rs);

     for($i=0,$n=count($dataFisik);$i<$n;$i++) {
          unset($show);
          if($_POST["dosis_fisik"]==$dataFisik[$i]["fisik_id"]) $show = "selected";
          $fisik[$i] = $view->RenderOption($dataFisik[$i]["fisik_id"],$dataFisik[$i]["fisik_nama"],$show);
     }
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">


function CheckDataSave(frm)
{     
	
	if(!frm.dosis_nama.value){
		alert('Nama Harus Diisi');
		frm.dosis_nama.focus();
          return false;
	}	
	
	return true;
          
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Dosis</td>
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
               <td align="right" class="tablecontent" width="30%"><strong>Bentuk</strong>&nbsp;</td>
               <td width="70%">
                         <?php echo $view->RenderComboBox("id_fisik","id_fisik",$fisik,null,null);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama</strong>&nbsp;</td>
               <td width="70%">
				<?php echo $view->RenderTextBox("dosis_nama","dosis_nama","50","100",$_POST["dosis_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='dosis_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.dosis_kode.focus();</script>
<?php echo $view->RenderHidden("dosis_id","dosis_id",$dosisId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
