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
	
	$plx = new InoLiveX("CheckDataVisus");
	
     if(!$auth->IsAllowed("setup_visus",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_visus",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataVisus($visusNama,$visusId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.visus_id FROM klinik.klinik_visus a 
                    WHERE upper(a.visus_nama) = ".QuoteValue(DPE_CHAR,strtoupper($visusNama));
                    
          if($visusId) $sql .= " and a.visus_id <> ".QuoteValue(DPE_CHAR,$visusId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAda = $dtaccess->Fetch($rs);
          
		return $dataAda["visus_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["visus_id"])  $visusId = & $_POST["visus_id"];

     $backPage = "visus_view.php";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $visusId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_visus a 
				where visus_id = ".QuoteValue(DPE_CHAR,$visusId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $view->CreatePost($row_edit);
		$tmpVisus = explode("/",$_POST["visus_nama"]);
		$_POST["visus_nama1"] = $tmpVisus[0];
		$_POST["visus_nama2"] = $tmpVisus[1];
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
               $visusId = & $_POST["visus_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
			$_POST["visus_nama"] = $_POST["visus_nama1"]."/".$_POST["visus_nama2"];
               if(!$_POST["visus_is_buta"]) $_POST["visus_is_buta"] = "n";

               $dbTable = "klinik.klinik_visus";
               
               $dbField[0] = "visus_id";   // PK
               $dbField[1] = "visus_nama";
               $dbField[2] = "visus_is_buta";
			
               if(!$visusId) $visusId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$visusId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["visus_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["visus_is_buta"]);
			
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
          $visusId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($visusId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_visus  
                         where visus_id = ".QuoteValue(DPE_CHAR,$visusId[$i]);
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
	
	if(!frm.visus_nama1.value || !frm.visus_nama2.value){
		alert('Nama Harus Diisi');
		frm.visus_nama1.focus();
          return false;
	}	
	
	if(CheckDataVisus(frm.visus_nama1.value+'/'+frm.visus_nama2.value,frm.visus_id.value,'type=r')){
		alert('Visus Sudah Ada');
		frm.visus_nama1.focus();
		frm.visus_nama1.select();
		return false;
	}
	
	return true;
          
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Visus</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Visus Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("visus_nama1","visus_nama1","5","5",$_POST["visus_nama1"],"inputField", null,false);?>
				/
                    <?php echo $view->RenderTextBox("visus_nama2","visus_nama2","5","5",$_POST["visus_nama2"],"inputField", null,false);?>
               </td>
          </tr>
		<tr>
			<td align="right" class="tablecontent"><strong>Buta</strong></td>
			<td><?php echo $view->RenderCheckBox("visus_is_buta","visus_is_buta","y",($_POST["btnDelete"])?"passDisable":null,($_POST["visus_is_buta"] == "y")?"checked":"")?></td>
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

<script>document.frmEdit.visus_nama1.focus();</script>
<?php echo $view->RenderHidden("visus_id","visus_id",$visusId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
