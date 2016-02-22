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
     $table = new InoTable("table","100%","right");
     
     $viewPage = "paket_view.php";
     $editPage = "paket_edit.php";
	
     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.paket_id FROM lab_paket a 
                    WHERE upper(a.paket_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          $datakategori = $dtaccess->Fetch($rs);
          
		return $datakategori["paket_id"];
     }
	     
  if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["paket_id"])  $kategoriId = & $_POST["paket_id"];

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $kategoriId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from lab_paket where 
                  paket_id = ".QuoteValue(DPE_CHAR,$kategoriId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["paket_id"] = $row_edit["paket_id"];
          $_POST["paket_nama"] = $row_edit["paket_nama"];
          $_POST["paket_pemeriksaan"] = $row_edit["paket_pemeriksaan"];

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
               $kategoriId = & $_POST["paket_id"];
               $_x_mode = "Edit";
          }
          
           
         
          if ($err_code == 0) {
               $dbTable = "laboratorium.lab_paket";
               
               $dbField[0] = "paket_id";   // PK
               $dbField[1] = "paket_nama"; 
			
               if(!$kategoriId) $kategoriId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$kategoriId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["paket_nama"]); 
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LAB);
   
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
          $kategoriId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($kategoriId);$i<$n;$i++){
               $sql = "delete from lab_paket 
                         where paket_id = ".QuoteValue(DPE_CHAR,$kategoriId[$i]);
               $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          }
          
          header("location:".$viewPage);
          exit();    
     } 
     
     $sql = "select * from lab_kegiatan a 
             left join lab_kategori b on b.kategori_id = a.id_kategori
             left join lab_bonus c on c.bonus_id = a.id_bonus
             order by b.kategori_nama ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     $dataTable = $dtaccess->FetchAll($rs);
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>
<div onKeyDown="CaptureEvent(event);">
<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 
     
     if(!frm.paket_nama.value){
		alert('Nama kategori Harus Diisi');
		frm.paket_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.paket_nama.value,'type=r')){
			alert('Nama kategori Sudah Ada');
			frm.paket_nama.focus();
			frm.paket_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}

function CaptureEvent(evt){
     var keyCode = document.layers ? evt.which : document.all ? evt.keyCode : evt.keyCode;     	
     
     if(keyCode==113) {  // -- f2 buat fokus ke tambah service ---
          document.frmEdit.paket_nama.focus();
     }
     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Paket Pemeriksaan</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Paket Pemeriksaan</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
         
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama </strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("paket_nama","paket_nama","60","100",$_POST["paket_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='paket_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.menu_nama.focus();</script>
<?php echo $view->RenderHidden("paket_id","paket_id",$kategoriId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
