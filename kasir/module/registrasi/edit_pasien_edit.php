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
	
     if(!$auth->IsAllowed("edit_status_pasien",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("edit_status_pasien",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $regId = $enc->Decode($_GET["id"]); 
          }
         
          $sql = "select a.reg_status, cust_usr_nama, cust_usr_kode
				from klinik.klinik_registrasi a
				join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
				where reg_id = ".QuoteValue(DPE_CHAR,$regId); 
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
		
		$_POST["cust_usr_nama"] = $row_edit["cust_usr_nama"];
		$_POST["cust_usr_kode"] = $row_edit["cust_usr_kode"];
		$_POST["reg_status"] = $row_edit["reg_status"]{0};
		    
     } 
   
     if ($_POST["btnSave"]) {         
          $regId = & $_POST["reg_id"]; 
          
		$sql = "update klinik.klinik_registrasi set reg_status = ".QuoteValue(DPE_CHAR,$_POST["reg_status"]."0")." where reg_id = ".QuoteValue(DPE_CHAR,$regId);
		$dtaccess->Execute($sql);
		
		header("location:edit_pasien_view.php");
		exit();      
     }
      
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">
 
function CheckDataSave(frm) {
      
	return true;    
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Status Pasien </td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Status Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>No.Registrasi</strong>&nbsp;</td>
               <td width="70%"> 
				<?php echo $_POST["cust_usr_kode"];?>
               </td>
          </tr>  
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama Pasien</strong>&nbsp;</td>
               <td width="70%"> 
				<?php echo $_POST["cust_usr_nama"];?>
               </td>
          </tr>  
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Status Pasien</strong>&nbsp;</td>
               <td width="70%"> 
				<select name="reg_status" id="reg_status" onKeyDown="return tabOnEnter(this, event);">  
					<?php foreach($biayaStatus as $key => $value) { ?>
						<option value="<?php echo $key;?>" <?php if($_POST["reg_status"]==$key) echo "selected";?>><?php echo $value;?></option>
					<?php } ?>
					<option value="<?php echo STATUS_SELESAI;?>" <?php if($_POST["reg_status"]==STATUS_SELESAI) echo "selected";?>><?php echo $rawatStatus[STATUS_SELESAI];?></option>
				</select>
               </td>
          </tr>  
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='edit_pasien_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.reg_status.focus();</script> 
<?php echo $view->RenderHidden("reg_id","reg_id",$regId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
