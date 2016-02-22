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
     
     $viewPage = "rujukan_view.php";
     $editPage = "rujukan_edit.php";
	
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.rujukan_id global.global_rujukan a 
                    WHERE upper(a.rujukan_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $datamerk = $dtaccess->Fetch($rs);
          
		return $datamerk["rujukan_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["rujukan_id"])  $petunjukId = & $_POST["rujukan_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $petunjukId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from global.global_rujukan a 
				where rujukan_id = ".QuoteValue(DPE_CHAR,$petunjukId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["rujukan_nama"] = $row_edit["rujukan_nama"];
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
               $petunjukId = & $_POST["petunjuk_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $dbTable = "global.global_rujukan";
               
               $dbField[0] = "rujukan_id";   // PK
               $dbField[1] = "rujukan_nama"; 
			
               if(!$petunjukId) $petunjukId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$petunjukId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rujukan_nama"]); 
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
               
                  header("location:".$viewPage);
                  exit();
          }
     }
 
     if ($_POST["btnDelete"]) {
          $petunjukId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($petunjukId);$i<$n;$i++){
               $sql = "delete from global.global_rujukan
                         where rujukan_id = ".QuoteValue(DPE_CHAR,$petunjukId[$i]);
               $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          }
          
          header("location:".$viewPage);
          exit();    
     } 
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 
     
     if(!frm.petunjuk_nama.value){
		alert('Petunjuk Obat Harus Diisi');
		frm.petunjuk_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.petunjuk_nama.value,'type=r')){
			alert('Nama merk Optik Sudah Ada');
			frm.petunjuk_nama.focus();
			frm.petunjuk_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Rujukan</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Merk Optik Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("rujukan_nama","rujukan_nama","50","100",$_POST["rujukan_nama"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='".$viewPage."';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.petunjuk_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("rujukan_id","rujukan_id",$petunjukId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
