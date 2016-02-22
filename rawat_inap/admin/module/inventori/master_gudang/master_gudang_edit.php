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
	  
     if(!$auth->IsAllowed("setup_gudang",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_gudang",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	   

	   
	function CheckData($gudangNama,$gudangId=null)//nanti ae
	{
          global $dtaccess;
          
          $sql = "SELECT gudang_id FROM pos_gudang a 
                    WHERE upper(a.gudang_nama) = ".QuoteValue(DPE_CHAR,strtoupper($gudangNama));
                    
          if ($gudangId) $sql .= " and a.gudang_id <> ".QuoteValue(DPE_CHAR,$gudangId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdamenu = $dtaccess->Fetch($rs);
        
		return $dataAdamenu["gudang_id"];
     }
  

    
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["gudang_id"])  $gudangId = & $_POST["gudang_id"];

     $backPage = "master_gudang_view.php?";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $gudangId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from pos_gudang where 
                  gudang_id = ".QuoteValue(DPE_CHAR,$gudangId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["gudang_nama"] = $row_edit["gudang_nama"];
          $_POST["gudang_kode"] = $row_edit["gudang_kode"];
          

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
               $gudangId = & $_POST["master_gudang_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "pos_gudang";
               
               $dbField[0] = "gudang_id";   // PK
               $dbField[1] = "gudang_kode";
               $dbField[2] = "gudang_nama";
           
            
               if(!$gudangId) $gudangId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$gudangId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["gudang_kode"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["gudang_nama"]);
              
               
             
			
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
          $gudangId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($gudangId);$i<$n;$i++){
               $sql = "delete from pos_gudang  
                         where gudang_id = ".QuoteValue(DPE_CHAR,$gudangId[$i]);
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
	
	if(!frm.gudang_nama.value){
		alert('Nama Gudang Harus Diisi');
		frm.master_gudang_nama.focus();
          return false;
	}	
	
	if(!frm.gudang_kode.value){
		alert('Kode Gudang Harus Diisi');
		frm.master_gudang_nama.focus();
          return false;
	}
	
	
	return true;
          
}

</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Master Gudang</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Master Gudang</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Kode Gudang</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("gudang_kode","gudang_kode","30","100",$_POST["gudang_kode"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama Gudang</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("gudang_nama","gudang_nama","60","100",$_POST["gudang_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='master_gudang_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.menu_nama.focus();</script>
<?php echo $view->RenderHidden("master_gudang_id","master_gudang_id",$gudangId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
