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
     
     $viewPage = "jenis_biaya_view.php";
     $editPage = "jenis_biaya_edit.php";
	
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
     if(!$auth->IsAllowed("setup_biaya",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_biaya",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.rujukan_id klinik.klinik_split a 
                    WHERE upper(a.split_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $datasplit = $dtaccess->Fetch($rs);
          
		return $datasplit["split_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["split_id"])  $splitId = & $_POST["split_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $splitId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_split a 
				where split_id = ".QuoteValue(DPE_CHAR,$splitId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["split_nama"] = $row_edit["split_nama"];
          $_POST["split_flag"] = $row_edit["split_flag"];
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
               $splitId = & $_POST["split_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_split";
               
               $dbField[0] = "split_id";   // PK
               $dbField[1] = "split_nama";
               $dbField[2] = "split_flag"; 
			
               if(!$splitId) $splitId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$splitId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["split_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["split_flag"]);  
			
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
          $splitId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($splitId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_split
                         where split_id = ".QuoteValue(DPE_CHAR,$splitId[$i]);
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
     
     if(!frm.split_nama.value){
		alert('Nama Jenis Biaya Harus Diisi');
		frm.split_nama.focus();
          return false;
	}

     document.frmEdit.submit();     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Jenis Biaya</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Jenis Biaya</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("split_nama","split_nama","50","100",$_POST["split_nama"],"inputField", null,false);?>
               </td>
          </tr>
<tr class="tablecontent">
          <td align="right" width="10%">&nbsp;Layanan</td>
              <td width="35%">
                  <select name="split_flag">
    				  <option value="">[ Semua Layanan Biaya ]</option>
    				<?php foreach($namaSplit as $key=>$value) {?>
    					<option value="<?php echo $key;?>" <?php if($_POST["split_flag"]==$key) echo "selected";?>><?php echo $value;?></option>
    				<?php } ?>
    			       </select>
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

<script>document.frmEdit.split_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("split_id","split_id",$splitId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
