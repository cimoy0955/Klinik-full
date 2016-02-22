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
          
          $sql = "SELECT a.prosedur_id FROM klinik.klinik_prosedur a 
                    WHERE upper(a.prosedur_nomor) = ".QuoteValue(DPE_CHAR,strtoupper($icdNomor));
                    
          if($icdId) $sql .= " and a.prosedur_id <> ".QuoteValue(DPE_CHAR,$icdId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaIcd = $dtaccess->Fetch($rs);
          
		return $dataAdaIcd["prosedur_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["prosedur_id"])  $icdId = & $_POST["prosedur_id"];
     
     

     $backPage = "icd_view.php";

     $tableHeader = "&nbsp;ICD 10";
	
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $icdId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_prosedur a 
				where prosedur_id = ".QuoteValue(DPE_CHAR,$icdId);
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
               $icdId = & $_POST["prosedur_id"];
               $_x_mode = "Edit";
          }

          if ($_POST["btnSave"]) {
               $sql = "select prosedur_nama from klinik_prosedur where prosedur_kode = ".QuoteValue(DPE_CHAR,$_POST["prosedur_kode"]);
               $rs_check = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
               $dataKode = $dtaccess->Fetch($rs_check);
               if ($dataKode) {
                    $err_code = setbit($err_code,1);
               } else {
                    $err_code = clearbit($err_code,1);
               }
               unset($rs_check);

               $sql = "select prosedur_kode from klinik_prosedur where upper(prosedur_nama) = ".QuoteValue(DPE_CHAR,strtoupper($_POST["prosedur_nama"]));
               $rs_check = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
               $dataNama = $dtaccess->Fetch($rs_check);
               if ($dataNama) {
                    $err_code = setbit($err_code,2);
               } else {
                    $err_code = clearbit($err_code,2);
               }
               unset($rs_check);
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_prosedur";
               
               $dbField[0] = "prosedur_id";   // PK
               $dbField[1] = "prosedur_kode";
               $dbField[2] = "prosedur_nama";
			
               if(!$icdId) $icdId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$icdId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["prosedur_kode"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["prosedur_nama"]);
			
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
               $sql = "delete from klinik.klinik_prosedur  
                         where prosedur_id = ".QuoteValue(DPE_CHAR,$icdId[$i]);
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
     if(!frm.prosedur_kode.value){
		alert('Nomor Harus Diisi');
		frm.prosedur_kode.focus();
          return false;
	}
	
	if(!frm.prosedur_nama.value){
		alert('Nama Harus Diisi');
		frm.prosedur_nama.focus();
          return false;
	}	
/*	
	if(CheckDataIcd(frm.prosedur_nomor.value,frm.prosedur_id.value,'type=r')){
		alert('Nomor Sudah Ada');
		frm.prosedur_nomor.focus();
		frm.prosedur_nomor.select();
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
               <td align="right" class="tablecontent" width="30%"><strong>Code</strong>&nbsp;<?php if(readbit($err_code,1)) {?>&nbsp;<span style="color:red;">(*)</span><?}?></td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("prosedur_kode","prosedur_kode","10","20",$_POST["prosedur_kode"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Prosedur</strong>&nbsp;<?php if(readbit($err_code,2)) {?>&nbsp;<span style="color:red;">(*)</span><?}?></td>
               <td width="70%">
				<?php echo $view->RenderTextBox("prosedur_nama","prosedur_nama","50","100",$_POST["prosedur_nama"],"inputField", null,false);?>                    
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

<script>document.frmEdit.prosedur_nomor.focus();</script>
<?php echo $view->RenderHidden("prosedur_id","prosedur_id",$icdId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,1)) { ?>
<br>
<font color="green"><strong>Kode sama dengan prosedur <?php echo $dataKode["prosedur_nama"]; ?>.</strong></font>
<? } ?>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="green"><strong>Nama sama dengan kode prosedur <?php echo $dataNama["prosedur_kode"]; ?>.</strong></font>
<? } ?>
</span>
</form>

<?php echo $view->RenderBodyEnd(); ?>
