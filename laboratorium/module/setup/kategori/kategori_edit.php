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
     
     $viewPage = "kategori_view.php";
     $editPage = "kategori_edit.php";
     $hiddenPage = "kategori_view_hidden.php";
	
     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.kategori_id FROM lab_kategori a 
                    WHERE upper(a.kategori_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          $datakategori = $dtaccess->Fetch($rs);
          
		return $datakategori["kategori_id"];
     }
	     
  if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["kategori_id"])  $kategoriId = & $_POST["kategori_id"];

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $kategoriId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from lab_kategori where 
                  kategori_id = ".QuoteValue(DPE_CHAR,$kategoriId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["kategori_id"] = $row_edit["kategori_id"];
          $_POST["kategori_nama"] = $row_edit["kategori_nama"];

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
               $kategoriId = & $_POST["kategori_id"];
               $_x_mode = "Edit";
          }
          
           
         
          if ($err_code == 0) {
               $dbTable = "laboratorium.lab_kategori";
               
               $dbField[0] = "kategori_id";   // PK
               $dbField[1] = "kategori_nama"; 
			
               if(!$kategoriId) $kategoriId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$kategoriId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kategori_nama"]); 
			
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
               $sql = "update lab_kategori set is_active = 'n'
                         where kategori_id = ".QuoteValue(DPE_CHAR,$kategoriId[$i]);
               $dtaccess->Execute($sql,DB_SCHEMA_LAB);

               $sql = "update lab_kegiatan set is_active = 'n'
                         where id_kategori = ".QuoteValue(DPE_CHAR,$kategoriId[$i]);
               $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          }
          
          header("location:".$viewPage);
          exit();    
     } 


     if ($_POST["btnEnable"]) {
          $kegiatanId = & $_POST["cbEnable"];
          
          for($i=0,$n=count($kegiatanId);$i<$n;$i++){
               $sql = "update lab_kategori set is_active = 'y'
                         where kategori_id = ".QuoteValue(DPE_CHAR,$kegiatanId[$i]);
               $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          }
          
          header("location:".$hiddenPage);
          exit();    
     }
     
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>
<div onKeyDown="CaptureEvent(event);">
<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 
     
     if(!frm.kategori_nama.value){
		alert('Nama kategori Harus Diisi');
		frm.kategori_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.kategori_nama.value,'type=r')){
			alert('Nama kategori Sudah Ada');
			frm.kategori_nama.focus();
			frm.kategori_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}

function CaptureEvent(evt){
     var keyCode = document.layers ? evt.which : document.all ? evt.keyCode : evt.keyCode;     	
     
     if(keyCode==113) {  // -- f2 buat fokus ke tambah service ---
          document.frmEdit.kategori_nama.focus();
     }
     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Kategori Pemeriksaan</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Kategori Pemeriksaan</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
         
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama </strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("kategori_nama","kategori_nama","60","100",$_POST["kategori_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='kategori_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.menu_nama.focus();</script>
<?php echo $view->RenderHidden("kategori_id","kategori_id",$kategoriId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
