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
	
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
     if(!$auth->IsAllowed("setup_paket_operasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_paket_operasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.icu_id FROM klinik.klinik_level_icu a 
                    WHERE upper(a.icu_level) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql);
          $dataPaket = $dtaccess->Fetch($rs);
          
		return $dataPaket["icu_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["icu_id"])  $icuId = & $_POST["icu_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $icuId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from klinik.klinik_level_icu a 
				where icu_id = ".QuoteValue(DPE_CHAR,$icuId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["icu_level"] = $row_edit["icu_level"];
           $_POST["icu_id"] = $row_edit["icu_id"];

		
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
               $icuId = & $_POST["icu_id"];
               $_x_mode = "Edit";
          } 
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_level_icu";
               
               $dbField[0] = "icu_id";   // PK
               $dbField[1] = "icu_level";  
			
               if(!$icuId) $icuId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$icuId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["icu_level"]);   
			
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
                  
			
               header("location:icu_view.php");
               exit();        
          }
     }
 
     if ($_POST["btnDelete"]) {
          $icuId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($icuId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_level_icu 
                         where icu_id = ".QuoteValue(DPE_CHAR,$icuId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:icu_view.php");
          exit();    
     } 

	$sql = "select * from klinik.klinik_split order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm) {
     
     if(!frm.icu_level.value){
		alert('Nama Level ICU Operasi Harus Diisi');
		frm.icu_level.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.icu_level.value,'type=r')){
			alert('Nama  Level ICU Sudah Ada');
			frm.icu_level.focus();
			frm.icu_level.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Level ICU</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Level ICU Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="15%"><strong>Nama Level<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("icu_level","icu_level","50","100",$_POST["icu_level"],"inputField", null,false);?>
               </td>
          </tr> 
				</table>
			</td>
          </tr> 
          <tr>
               <td colspan="2" align="left">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='icu_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.icu_level.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("icu_id","icu_id",$icuId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
