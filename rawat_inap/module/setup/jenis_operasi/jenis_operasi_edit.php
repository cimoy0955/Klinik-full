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
	
     if(!$auth->IsAllowed("setup_jenis_operasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_jenis_operasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.op_jenis_id FROM klinik.klinik_operasi_jenis a 
                    WHERE upper(a.op_jenis_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql);
          $dataJenis = $dtaccess->Fetch($rs);
          
		return $dataJenis["op_jenis_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["op_jenis_id"])  $opJenisId = & $_POST["op_jenis_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $opJenisId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_operasi_jenis a 
				where op_jenis_id = ".QuoteValue(DPE_CHAR,$opJenisId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["op_jenis_nama"] = $row_edit["op_jenis_nama"]; 
          $_POST["op_jenis_deskripsi"] = $row_edit["op_jenis_deskripsi"]; 
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
               $opJenisId = & $_POST["op_jenis_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_operasi_jenis";
               
               $dbField[0] = "op_jenis_id";   // PK
               $dbField[1] = "op_jenis_nama"; 
               $dbField[2] = "op_jenis_deskripsi"; 
			
               if(!$opJenisId) $opJenisId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$opJenisId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["op_jenis_nama"]); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["op_jenis_deskripsi"]); 
			
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
               
               header("location:jenis_operasi_view.php");
               exit();        
          }
     }
 
     if ($_POST["btnDelete"]) {
          $opJenisId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($opJenisId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_operasi_jenis 
                         where op_jenis_id = ".QuoteValue(DPE_CHAR,$opJenisId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:jenis_operasi_view.php");
          exit();    
     } 
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 
     
     if(!frm.op_jenis_nama.value){
		alert('Nama Jenis Operasi Harus Diisi');
		frm.op_jenis_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.op_jenis_nama.value,'type=r')){
			alert('Nama  Jenis Operasi Sudah Ada');
			frm.op_jenis_nama.focus();
			frm.op_jenis_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Jenis Operasi</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Jenis Operasi Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("op_jenis_nama","op_jenis_nama","50","100",$_POST["op_jenis_nama"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Deskripsi</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextArea("op_jenis_deskripsi","op_jenis_deskripsi","5","60",$_POST["op_jenis_deskripsi"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='jenis_operasi_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.op_jenis_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("op_jenis_id","op_jenis_id",$opJenisId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
