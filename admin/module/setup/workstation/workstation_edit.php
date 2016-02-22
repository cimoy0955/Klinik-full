<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/inoLiveX.php");
	require_once($APLICATION_ROOT."library/view.cls.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0;
	
	  $plx = new InoLiveX("CheckData");
	
     if(!$auth->IsAllowed("setup_workstation",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_workstation",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckData($workstationNama,$workstasionId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT meja_id FROM mp_meja a 
                    WHERE upper(a.meja_nama) = ".QuoteValue(DPE_CHAR,strtoupper($workstationNama));
                    
          if ($workstasionId) $sql .= " and a.meja_id <> ".QuoteValue(DPE_CHAR,$workstasionId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaWorkstation = $dtaccess->Fetch($rs);
        
		return $dataAdaWorkstation["meja_id"];
     }
  
    
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["meja_id"])  $mejaId = & $_POST["meja_id"];

     $backPage = "workstation_view.php?";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $mejaId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from mp_meja where meja_id = ".QuoteValue(DPE_CHAR,$mejaId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["meja_nama"] = $row_edit["meja_nama"];
          $_POST["meja_keterangan"] = $row_edit["meja_keterangan"];
          $_POST["meja_aktif"] = $row_edit["meja_aktif"];
          $_POST["meja_order"] = $row_edit["meja_order"];
          $_POST["meja_ip"] = $row_edit["meja_ip"];
          $_POST["meja_tipe"] = $row_edit["meja_tipe"];
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
               $mejaId = & $_POST["meja_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "mp_meja";
               
               $dbField[0] = "meja_id";   // PK
               $dbField[1] = "meja_nama";
               $dbField[2] = "meja_keterangan";
               $dbField[3] = "meja_aktif";
               $dbField[4] = "meja_order";
               $dbField[5] = "meja_ip";
               $dbField[6] = "meja_tipe";
			
               if(!$mejaId) $mejaId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$mejaId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["meja_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["meja_keterangan"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["meja_aktif"]);
               $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["meja_order"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["meja_ip"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["meja_tipe"]);
			
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
          $mejaId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($mejaId);$i<$n;$i++){
               $sql = "delete from mp_meja  
                         where meja_id = ".QuoteValue(DPE_CHAR,$mejaId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }	
     
     $tipe[0] = $view->RenderOption("--","Pilih Status",$show);
     if($_POST["meja_aktif"]=="y") $show = "selected";
     $tipe[1] = $view->RenderOption("y","Aktif",$show);
     unset($show);
     if($_POST["meja_aktif"]=="n") $show = "selected";
     $tipe[2] = $view->RenderOption("n","Tidak Aktif",$show);
     unset($show);
     
     $tipeWork[0] = $view->RenderOption("--","Pilih Tipe",$show);
     if($_POST["meja_tipe"]=="M") $show = "selected";
     $tipeWork[1] = $view->RenderOption("M","Multiplayer",$show);
     unset($show);
     if($_POST["meja_tipe"]=="W") $show = "selected";
     $tipeWork[2] = $view->RenderOption("W","Warnet",$show);
     unset($show); 
?>

<?php echo $view->RenderBody("inventori.css",false); ?>
<?php echo $view->InitUpload(); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{     
	
	if(!frm.meja_nama.value){
		alert('Nama Harus Diisi');
		frm.meja_nama.focus();
          return false;
	}	

	if(CheckData(frm.meja_nama.value,frm.meja_id.value,'type=r')){
		alert('Nama Workstation Sudah Ada');
		frm.meja_nama.focus();
		frm.meja_nama.select();
		return false;
	}
	
	return true;
          
}

</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Workstation</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Workstation</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
            <td align="right" class="tablecontent"><strong>Tipe</td>
               <td>
                 <?php echo $view->RenderComboBox("meja_tipe","meja_tipe",$tipeWork,null,null,null);?>
               </td>
         </tr>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>No. Urut</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("meja_order","meja_order","10","100",$_POST["meja_order"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("meja_nama","meja_nama","30","100",$_POST["meja_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Ip Addreas</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("meja_ip","meja_ip","50","100",$_POST["meja_ip"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
            <td align="right" class="tablecontent"><strong>Aktif</td>
               <td>
                 <?php echo $view->RenderComboBox("meja_aktif","meja_aktif",$tipe,null,null,null);?>
               </td>
         </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Keterangan</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextArea("meja_keterangan","meja_keterangan","3","100",$_POST["meja_keterangan"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='workstation_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.meja_nama.focus();</script>
<?php echo $view->RenderHidden("meja_id","meja_id",$mejaId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
