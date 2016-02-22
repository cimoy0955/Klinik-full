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
	
     if(!$auth->IsAllowed("setup_jenis_pasien",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_jenis_pasien",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.cust_tipe_id FROM global.global_customer_tipe a 
                    WHERE upper(a.cust_tipe_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql);
          $dataAdaCustomerTipe = $dtaccess->Fetch($rs);
          
		return $dataAdaCustomerTipe["cust_tipe_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["cust_tipe_id"])  $custTipeId = & $_POST["cust_tipe_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $custTipeId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from global.global_customer_tipe a 
				where cust_tipe_id = ".$custTipeId;
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["cust_tipe_nama"] = $row_edit["cust_tipe_nama"];
          $_POST["cust_tipe_bayar_diskon"] = $row_edit["cust_tipe_bayar_diskon"];
          $_POST["cust_tipe_bayar_tipe"] = $row_edit["cust_tipe_bayar_tipe"];
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
               $custTipeId = & $_POST["cust_tipe_id"];
               $_x_mode = "Edit";
          }

          $_POST["cust_tipe_bayar_diskon"] = StripCurrency($_POST["cust_tipe_bayar_diskon"]);
         
          if ($err_code == 0) {
               $dbTable = "global.global_customer_tipe";
               
               $dbField[0] = "cust_tipe_id";   // PK
               $dbField[1] = "cust_tipe_nama";
               $dbField[2] = "cust_tipe_bayar_diskon";
               $dbField[3] = "cust_tipe_bayar_tipe";
			
               if(!$custTipeId) $custTipeId = $dtaccess->GetNewID("global.global_customer_tipe","cust_tipe_id");   
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$custTipeId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["cust_tipe_nama"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["cust_tipe_bayar_diskon"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["cust_tipe_bayar_tipe"]);
			
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
               
               header("location:jenis_pasien_view.php");
               exit();        
          }
     }
 
     if ($_POST["btnDelete"]) {
          $custTipeId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($custTipeId);$i<$n;$i++){
               $sql = "delete from global.global_customer_tipe 
                         where cust_tipe_id = ".$custTipeId[$i];
               $dtaccess->Execute($sql);
          }
          
          header("location:jenis_pasien_view.php");
          exit();    
     }
		
	$tipe[0] = $view->RenderOption("F","Flat (Rp.)",($_POST["cust_tipe_bayar_tipe"]=="F")?"selected":"");
	$tipe[1] = $view->RenderOption("P","Persen (%)",($_POST["cust_tipe_bayar_tipe"]=="P")?"selected":"");
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{
     custTipeBayarDiskon = frm.cust_tipe_bayar_diskon.value.toString().replace(/\,/g,"");
     
     if(!frm.cust_tipe_nama.value){
		alert('Nama Jenis Pasien Harus Diisi');
		frm.cust_tipe_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.cust_tipe_nama.value,'type=r')){
			alert('Nama Jenis Pasien Sudah Ada');
			frm.cust_tipe_nama.focus();
			frm.cust_tipe_nama.select();
			return false;
		}
	}
	
	/*if(!custTipeBayarDiskon || custTipeBayarDiskon<=0){
		alert('Nominal Diskon Harus Diisi Lebih Besar Dari 0');
		frm.cust_tipe_bayar_diskon.focus();
          frm.cust_tipe_bayar_diskon.select();
		return false;
	}*/
	
	if(frm.cust_tipe_bayar_tipe.value=='P') {
		if(custTipeBayarDiskon>100){
			alert('Nominal Diskon Tidak Boleh Lebih Dari 100 %');
			frm.cust_tipe_bayar_diskon.focus();
			frm.cust_tipe_bayar_diskon.select();
			return false;
		}	
	}
     
     document.frmEdit.submit();     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Jenis Pasien</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Jenis Pasien Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("cust_tipe_nama","cust_tipe_nama","50","100",$_POST["cust_tipe_nama"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Tipe Diskon<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%">
				<?php echo $view->RenderComboBox("cust_tipe_bayar_tipe","cust_tipe_bayar_tipe",$tipe,null,null);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nominal Diskon<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("cust_tipe_bayar_diskon","cust_tipe_bayar_diskon","16","15",$_POST["cust_tipe_bayar_diskon"],"curedit", null, true);?>
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='jenis_pasien_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.cust_tipe_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("cust_tipe_id","cust_tipe_id",$custTipeId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
