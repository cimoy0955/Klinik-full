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
	
	$plx = new InoLiveX("CheckDataIcd");
	
     if(!$auth->IsAllowed("setup_icd",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_icd",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataIcd($icdNomor,$icdId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.icd_id FROM klinik.klinik_icd a 
                    WHERE upper(a.icd_nomor) = ".QuoteValue(DPE_CHAR,strtoupper($icdNomor));
                    
          if($icdId) $sql .= " and a.icd_id <> ".QuoteValue(DPE_CHAR,$icdId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaIcd = $dtaccess->Fetch($rs);
          
		return $dataAdaIcd["icd_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["icd_id"])  $icdId = & $_POST["icd_id"];
     
     

     $backPage = "icd_view.php";

     $tableHeader = "&nbsp;ICD 9";
	
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $icdId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_icd a 
				where icd_id = ".QuoteValue(DPE_CHAR,$icdId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $view->CreatePost($row_edit);
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
               $icdId = & $_POST["icd_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_icd";
               
               $dbField[0] = "icd_id";   // PK
               $dbField[1] = "icd_nomor";
               $dbField[2] = "icd_nama";
			
               if(!$icdId) $icdId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$icdId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["icd_nomor"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["icd_nama"]);
			
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
          $icdId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($icdId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_icd  
                         where icd_id = ".QuoteValue(DPE_CHAR,$icdId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }	
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{     
     if(!frm.icd_nomor.value){
		alert('Nomor Harus Diisi');
		frm.icd_nomor.focus();
          return false;
	}
	
	if(!frm.icd_nama.value){
		alert('Nama Harus Diisi');
		frm.icd_nama.focus();
          return false;
	}	
/*	
	if(CheckDataIcd(frm.icd_nomor.value,frm.icd_id.value,'type=r')){
		alert('Nomor Sudah Ada');
		frm.icd_nomor.focus();
		frm.icd_nomor.select();
		return false;
	}
*/	
	return true;
          
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;<?php echo $tableHeader;?></td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>ICD Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Code</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("icd_nomor","icd_nomor","10","20",$_POST["icd_nomor"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Diagnosa</strong>&nbsp;</td>
               <td width="70%">
				<?php echo $view->RenderTextBox("icd_nama","icd_nama","50","100",$_POST["icd_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='".$backPage."';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.icd_nomor.focus();</script>
<?php echo $view->RenderHidden("icd_id","icd_id",$icdId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
