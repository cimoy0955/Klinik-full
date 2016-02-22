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
     
     if($_GET["RoomId"]) $roomId = $enc->Decode($_GET["RoomId"]);
     
	//echo $roomId;
	$plx = new InoLiveX("CheckDataItem");
	
     if(!$auth->IsAllowed("setup_kamar",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_kamar",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataItem($bedKode,$bedId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.bed_id FROM klinik.klinik_kamar_bed a 
                    WHERE upper(a.bed_kode) = ".QuoteValue(DPE_CHAR,strtoupper($bedKode));
                    
          if($bedId) $sql .= " and a.bed_id <> ".QuoteValue(DPE_NUMERIC,$bedId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaItem = $dtaccess->Fetch($rs);
          
		return $dataAdaItem["bed_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["bed_id"])  $bedId = & $_POST["bed_id"];

     $backPage = "bed_view.php";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $bedId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.bed_id,a.bed_kode from klinik.klinik_kamar_bed a 
				where bed_id = ".QuoteValue(DPE_CHAR,$bedId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["bed_id"] = $row_edit["bed_id"];
          $_POST["bed_kode"] = $row_edit["bed_kode"];
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
               $bedId = & $_POST["bed_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_kamar_bed";
               
               $dbField[0] = "bed_id";   // PK
               $dbField[1] = "bed_kode";
               $dbField[2] = "id_kamar";
			
               if(!$bedId) $bedId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$bedId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["bed_kode"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["room_id"]);
			
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
               
               header("location:".$backPage."?RoomId=".$enc->Encode($_POST["room_id"])."&RoomName=".$_POST["room_name"]);
               exit();        
          }
     }
 
     if ($_POST["btnDelete"]) {
          $bedId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($bedId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_kamar_bed  
                         where bed_id = ".QuoteValue(DPE_CHAR,$bedId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage."?RoomId=".$enc->Encode($_POST["room_id"])."&RoomName=".$_POST["room_name"]);
          exit();    
     }	
/*
     $sql = "select kategori_id,kategori_nama from klinik.klinik_kamar_bed
               order by kategori_id";
     $rs = $dtaccess->Execute($sql);
     $datakategori = $dtaccess->FetchAll($rs);

     for($i=0,$n=count($datakategori);$i<$n;$i++) {
          unset($show);
          if($_POST["bed_kategori"]==$datakategori[$i]["kategori_id"]) $show = "selected";
          $kategori[$i] = $view->RenderOption($datakategori[$i]["kategori_id"],$datakategori[$i]["kategori_nama"],$show);
     } */
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">


function CheckDataSave(frm)
{     
	
	if(!frm.bed_nama.value){
		alert('Nama Harus Diisi');
		frm.bed_nama.focus();
          return false;
	}	
	
	return true;
          
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit bed</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Bed Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Kode</strong>&nbsp;</td>
               <td width="70%">
				<?php echo $view->RenderTextBox("bed_kode","bed_kode","50","100",$_POST["bed_kode"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <a href="<?php echo "$backPage?RoomId=".$enc->Encode($roomId)."&RoomName=".$_GET["RoomName"];?>" style="border:none;text-decoration:none;"><?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false);?></a>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.kamar_kode.focus();</script>
<?php echo $view->RenderHidden("room_id","room_id",$roomId);?>
<?php echo $view->RenderHidden("room_name","room_name",$_GET["RoomName"]);?>
<?php echo $view->RenderHidden("bed_id","bed_id",$bedId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
