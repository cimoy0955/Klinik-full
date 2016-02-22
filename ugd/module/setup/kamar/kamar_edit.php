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
	
	$plx = new InoLiveX("CheckDataItem");
	
     if(!$auth->IsAllowed("setup_kamar",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_kamar",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataItem($kamarKode,$kamarId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.kamar_id FROM klinik.klinik_kamar a 
                    WHERE upper(a.kamar_kode) = ".QuoteValue(DPE_CHAR,strtoupper($kamarKode));
                    
          if($kamarId) $sql .= " and a.kamar_id <> ".QuoteValue(DPE_NUMERIC,$kamarId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaItem = $dtaccess->Fetch($rs);
          
		return $dataAdaItem["kamar_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["kamar_id"])  $kamarId = & $_POST["kamar_id"];

     $backPage = "kamar_view.php";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $kamarId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.kamar_id,kamar_nama,kamar_kode,id_kategori from klinik.klinik_kamar a 
				where kamar_id = ".QuoteValue(DPE_CHAR,$kamarId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["kamar_id"] = $row_edit["kamar_id"];
          $_POST["kamar_kode"] = $row_edit["kamar_kode"];
          $_POST["kamar_nama"] = $row_edit["kamar_nama"];
          $_POST["id_kategori"] = $row_edit["id_kategori"];
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
               $kamarId = & $_POST["kamar_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_kamar";
               
               $dbField[0] = "kamar_id";   // PK
               $dbField[1] = "kamar_kode";
               $dbField[2] = "kamar_nama";
               $dbField[3] = "id_kategori";
			
               if(!$kamarId) $kamarId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$kamarId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kamar_kode"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["kamar_nama"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_kategori"]);
			
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
          $kamarId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($kamarId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_kamar  
                         where kamar_id = ".QuoteValue(DPE_CHAR,$kamarId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }	

     $sql = "select kategori_id,kategori_nama from klinik.klinik_kamar_kategori
               order by kategori_id";
     $rs = $dtaccess->Execute($sql);
     $datakategori = $dtaccess->FetchAll($rs);

     for($i=0,$n=count($datakategori);$i<$n;$i++) {
          unset($show);
          if($_POST["kamar_kategori"]==$datakategori[$i]["kategori_id"]) $show = "selected";
          $kategori[$i] = $view->RenderOption($datakategori[$i]["kategori_id"],$datakategori[$i]["kategori_nama"],$show);
     }
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">


function CheckDataSave(frm)
{     
	
	if(!frm.kamar_nama.value){
		alert('Nama Harus Diisi');
		frm.kamar_nama.focus();
          return false;
	}	
	
	return true;
          
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit kamar</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Kamar</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Kategori Kelas Kamar</strong>&nbsp;</td>
               <td width="70%">
                         <?php echo $view->RenderComboBox("id_kategori","id_kategori",$kategori,null,null);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Kode</strong>&nbsp;</td>
               <td width="70%">
				<?php echo $view->RenderTextBox("kamar_kode","kamar_kode","50","100",$_POST["kamar_kode"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama</strong>&nbsp;</td>
               <td width="70%">
				<?php echo $view->RenderTextBox("kamar_nama","kamar_nama","50","100",$_POST["kamar_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='kamar_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.kamar_kode.focus();</script>
<?php echo $view->RenderHidden("kamar_id","kamar_id",$kamarId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
