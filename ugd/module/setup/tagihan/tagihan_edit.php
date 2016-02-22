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
	
	   $plx = new InoLiveX("CheckDataBiaya");
	
     if(!$auth->IsAllowed("setup_tagihan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_tagihan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

	function CheckDataBiaya($biayaKode,$biayaId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.b_tambahan_id FROM klinik.klinik_biaya_tambahan a ";
                    
          if($biayaId) $sql .= "WHERE and a.b_tambahan_id <> ".QuoteValue(DPE_NUMERIC,$biayaId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdabiaya = $dtaccess->Fetch($rs);
          
		return $dataAdabiaya["b_tambahan_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["b_tambahan_id"])  $biayaId = & $_POST["b_tambahan_id"];
     

     $backPage = "tagihan_view.php";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $biayaId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from klinik.klinik_biaya_tambahan a 
				          where b_tambahan_id = ".QuoteValue(DPE_CHAR,$biayaId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["b_tambahan_id"] = $row_edit["b_tambahan_id"];
          $_POST["b_tambahan_nama"] = $row_edit["b_tambahan_nama"];
          $_POST["b_tambahan_harga"] = $row_edit["b_tambahan_harga"];
          $_POST["b_tambahan_ops"] = $row_edit["b_tambahan_ops"];
          $_POST["b_tambahan_jasmed"] = $row_edit["b_tambahan_jasmed"];
          
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
               $biayaId = & $_POST["b_tambahan_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_biaya_tambahan";
               
               $dbField[0] = "b_tambahan_id";   // PK
               $dbField[1] = "b_tambahan_nama";
               $dbField[2] = "b_tambahan_harga";
               $dbField[3] = "b_tambahan_ops";
               $dbField[4] = "b_tambahan_jasmed";
             
               if(!$biayaId) $biayaId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["b_tambahan_nama"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["b_tambahan_harga"]));
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["b_tambahan_ops"]));
               $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["b_tambahan_jasmed"]));
               			
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
          $biayaId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($biayaId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_biaya_tambahan  
                         where b_tambahan_id = '".$biayaId[$i]."'";
          echo $sql;
              // $dtaccess->Execute($sql);
          }
          
         // header("location:".$backPage);
         // exit();    
     }	

    
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{     
	
	if(!frm.b_tambahan_nama.value){
		alert('Nama Harus Diisi');
		frm.b_tambahan_nama.focus();
          return false;
	}	
	
	//if(CheckDatabiaya(frm.biaya_kode.value,frm.b_tambahan_id.value,'type=r')){
	//	alert('Kode Sudah Ada');
	//	frm.biaya_kode.focus();
	//	frm.biaya_kode.select();
	//	return false;
	//}
	
	return true;
          
}

function hitungTotal()
{
    var jasmedFormat = document.getElementById('b_tambahan_jasmed').value.toString().replace(/\,/g,"");
    var opsFormat = document.getElementById('b_tambahan_ops').value.toString().replace(/\,/g,"");
    jasmedFormat=jasmedFormat*1;
    opsFormat=opsFormat*1;
    document.getElementById('b_tambahan_harga').value = formatCurrency(jasmedFormat + opsFormat);
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Obat</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>biaya Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama Biaya</strong>&nbsp;</td>
               <td width="70%">
				            <?php echo $view->RenderTextBox("b_tambahan_nama","b_tambahan_nama","50","100",$_POST["b_tambahan_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>JASMED</strong>&nbsp;</td>
               <td width="70%">
                         <?php echo $view->RenderTextBox("b_tambahan_jasmed","b_tambahan_jasmed","50","100",currency_format($_POST["b_tambahan_jasmed"]),"inputField", null,true,"onChange=hitungTotal()");?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Operasional</strong>&nbsp;</td>
               <td width="70%">
                         <?php echo $view->RenderTextBox("b_tambahan_ops","b_tambahan_ops","50","100",currency_format($_POST["b_tambahan_ops"]),"inputField", null,true,"onChange=hitungTotal()");?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Total Biaya</strong>&nbsp;</td>
               <td width="70%">
                         <?php echo $view->RenderTextBox("b_tambahan_harga","b_tambahan_harga","50","100",currency_format($_POST["b_tambahan_harga"]),"inputField", null,true);?>
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='tagihan_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.b_tambahan_nama.focus();</script>
<?php echo $view->RenderHidden("b_tambahan_id","b_tambahan_id",$biayaId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
