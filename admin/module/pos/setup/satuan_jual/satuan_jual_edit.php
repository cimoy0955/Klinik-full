<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/inoLiveX.php");
	   require_once($APLICATION_ROOT."library/view.cls.php");	
	   require_once($ROOT."library/currFunc.lib.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
	   $usrId = $auth->GetUserId();
     $err_code = 0;
     
     $userData = $auth->GetUserData();
	
	  $plx = new InoLiveX("CheckData");
	  
     if(!$auth->IsAllowed("setup_satuan_jual",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_satuan_jual",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	   
	   $lokasi = $ROOT."admin/images/item";
	   
	function CheckData($grupmenuNama,$grupmenuId=null)//nanti ae
	{
          global $dtaccess;
          
          $sql = "SELECT satuan_jual_id FROM pos_satuan_jual a 
                    WHERE upper(a.satuan_jual_nama) = ".QuoteValue(DPE_CHAR,strtoupper($grupmenuNama));
                    
          if ($grupmenuId) $sql .= " and a.satuan_jual_id <> ".QuoteValue(DPE_CHAR,$grupmenuId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdamenu = $dtaccess->Fetch($rs);
        
		return $dataAdamenu["satuan_jual_id"];
     }
  

    
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["satuan_jual_id"])  $grupmenuId = & $_POST["satuan_jual_id"];

     $backPage = "satuan_jual_view.php?";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $grupmenuId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from pos_satuan_jual where 
                  satuan_jual_id = ".QuoteValue(DPE_CHAR,$grupmenuId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["satuan_jual_nama"] = $row_edit["satuan_jual_nama"];
          

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
               $grupmenuId = & $_POST["satuan_jual_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "pos_satuan_jual";
               
               $dbField[0] = "satuan_jual_id";   // PK
               $dbField[1] = "satuan_jual_nama";
           
            
               if(!$grupmenuId) $grupmenuId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$grupmenuId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["satuan_jual_nama"]);
              
               
             
			
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
          $grupmenuId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($grupmenuId);$i<$n;$i++){
               $sql = "delete from pos_satuan_jual  
                         where satuan_jual_id = ".QuoteValue(DPE_CHAR,$grupmenuId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }
     
    

?>

<?php echo $view->RenderBody("inventori.css",false); ?>
<?php echo $view->InitUpload(); ?>

<script language="javascript" type="text/javascript">





<? $plx->Run(); ?>

function CheckDataSave(frm)
{     
	
	if(!frm.satuan_jual_nama.value){
		alert('Nama Kategori menu Harus Diisi');
		frm.satuan_jual_nama.focus();
          return false;
	}	
	
	
	return true;
          
}

</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Satuan Penjualan</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Satuan Penjualan</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
         
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama Satuan</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("satuan_jual_nama","satuan_jual_nama","60","100",$_POST["satuan_jual_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='satuan_jual_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.menu_nama.focus();</script>
<?php echo $view->RenderHidden("satuan_jual_id","satuan_jual_id",$grupmenuId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
