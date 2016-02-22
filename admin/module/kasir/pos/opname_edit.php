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
     
     $findPage = "item_find_opname.php?";
     $editPage = "opname_edit.php";
     $skr = getDateToday();
     
	
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.opname_id FROM apotik_opname a 
                    WHERE upper(a.opname_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
          $dataopname = $dtaccess->Fetch($rs);
          
		return $dataopname["opname_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["opname_id"])  $opnameId = & $_POST["opname_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $opnameId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from apotik_opname a 
				where opname_id = ".QuoteValue(DPE_CHAR,$opnameId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["opname_nama"] = $row_edit["opname_nama"];
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
               $opnameId = & $_POST["opname_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
              //-- simpan ke tabel opname --//
               $dbTable = "apotik_opname";
               
               $dbField[0] = "opname_id";   // PK
               $dbField[1] = "opname_tanggal";
               $dbField[2] = "opname_keterangan";
               $dbField[3] = "opname_miss";
               $dbField[4] = "opname_stok_tercatat"; 
               $dbField[5] = "opname_stok_real";
               $dbField[6] = "id_item";
			
               if(!$opnameId) $opnameId = $dtaccess->GetTransId("apotik_opname","opname_id",DB_SCHEMA_APOTIK);   
               $dbValue[0] = QuoteValue(DPE_CHAR,$opnameId);
               $dbValue[1] = QuoteValue(DPE_DATE,$skr); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["opname_keterangan"]); 
			         $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["opname_stok_selisih"]); 
			         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["opname_stok_catat"]); 
			         $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["opname_stok_real"]); 
			         $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_item"]); 
			         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_APOTIK);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               //-- simpan ke tabel transaksi --//
              $dbTable = "apotik_transaksi";
               
               $dbField[0] = "trans_id";   // PK
               $dbField[1] = "trans_create";
               $dbField[2] = "id_item";
               $dbField[3] = "trans_jumlah";
               $dbField[4] = "trans_tipe"; 
               $dbField[5] = "trans_keterangan";
               $dbField[6] = "id_opname";
			
               if(!$transId) $transId = $dtaccess->GetTransId("apotik_transaksi","trans_id",DB_SCHEMA_APOTIK);   
               $dbValue[0] = QuoteValue(DPE_CHAR,$transId);
               $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s")); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_item"]); 
			         $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["opname_stok_real"]); 
			         $dbValue[4] = QuoteValue(DPE_CHAR,'O'); 
			         $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["opname_keterangan"]); 
			         $dbValue[6] = QuoteValue(DPE_CHAR,$opnameId); 
			         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_APOTIK);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
              
               //-- update jumlah stok ke tabel master item --//
               $dbTable = "apotik_obat_master";
               
               $dbField[0] = "obat_id";
               $dbField[1] = "obat_stok";
               
               $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["id_item"]);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["opname_stok_real"]);
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_APOTIK);
               
               $dtmodel->Update() or die("update  error");	
               
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);
                unset($_POST["opname_keterangan"]);
                unset($_POST["opname_stok_selisih"]);
                unset($_POST["opname_stok_catat"]);
                unset($_POST["opname_stok_real"]);
                unset($_POST["id_item"]);
                unset($_POST["btnSave"]);
               
                header("location:".$editPage);
                exit();
          }
     }
 
 /*    if ($_POST["btnDelete"]) {
          $opopnameId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($opopnameId);$i<$n;$i++){
               $sql = "delete from apotik_opname 
                         where opname_id = ".QuoteValue(DPE_CHAR,$opopnameId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$viewPage);
          exit();    
     } 
     */
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>
<?php echo $view->InitThickBox(); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 
     
     if(!frm.opname_nama.value){
		alert('Nama opname apotik Harus Diisi');
		frm.opname_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.opname_nama.value,'type=r')){
			alert('Nama opname apotik Sudah Ada');
			frm.opname_nama.focus();
			frm.opname_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}

function hitungSelisih()
{
  var vstok1 = document.getElementById('opname_stok_catat').value.toString().replace(/\,/g,"")*1;
  var vstok2 = document.getElementById('opname_stok_real').value.toString().replace(/\,/g,"")*1;
  
  document.getElementById('opname_stok_selisih').value = vstok1-vstok2;
  document.getElementById('opname_keterangan').focus();
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Stok Opname</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Stok Opname</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
         <!-- <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Kode Item<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%">
                    <?php //echo $view->RenderTextBox("opname_item_kode","opname_item_kode","30","100",$_POST["opname_item_kode"],"inputField", null,false);?>
               </td>
          </tr> -->
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("opname_item_nama","opname_item_nama","35","255",$_POST["opname_item_nama"],"inputField", "readonly",false);?>
                    <?php echo $view->RenderHidden("id_item","id_item",$_POST["id_item"]); ?>
                    <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih item"><img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih item" alt="Pilih item" /></a>    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Stok Tercatat</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("opname_stok_catat","opname_stok_catat","20","100",$_POST["opname_stok_catat"],"inputField", "readonly",true);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Stok Real</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("opname_stok_real","opname_stok_real","20","100",$_POST["opname_stok_real"],"inputField", null,true,"onChange=\"hitungSelisih();\"");?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Selisih</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("opname_stok_selisih","opname_stok_selisih","20","100",$_POST["opname_stok_selisih"],"inputField", "readonly",true);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Keterangan</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextArea("opname_keterangan","opname_keterangan","3","50",$_POST["opname_keterangan"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php //echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='".$viewPage."';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.opname_item_kode.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("opname_id","opname_id",$opnameId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
