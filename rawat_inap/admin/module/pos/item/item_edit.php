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
     $err_code = 0;
     
     $userData = $auth->GetUserData();
	
	  $plx = new InoLiveX("CheckData");
	
     if(!$auth->IsAllowed("pos_item",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("pos_item",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckData($itemNama,$itemId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT item_id FROM pos.pos_item a 
                    WHERE upper(a.item_nama) = ".QuoteValue(DPE_CHAR,strtoupper($itemNama));
                    
          if ($itemId) $sql .= " and a.item_id <> ".QuoteValue(DPE_CHAR,$itemId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaItem = $dtaccess->Fetch($rs);
        
		return $dataAdaItem["item_id"];
     }
  
    
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["item_id"])  $itemId = & $_POST["item_id"];

     $backPage = "item_view.php?";
     $lokasi = $ROOT."admin/images/item";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $itemId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from pos.pos_item where item_id = ".QuoteValue(DPE_CHAR,$itemId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["item_nama"] = $row_edit["item_nama"];
          $_POST["item_harga_jual"] = currency_format($row_edit["item_harga_jual"]);
          $_POST["item_tipe"] = $row_edit["item_tipe"];
          $_POST["item_stock_warning"] = $row_edit["item_stock_warning"];
          $_POST["id_grup_item"] = $row_edit["id_grup_item"];
          $fotoName = $lokasi."/".$row_edit["item_icon"];
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
               $itemId = & $_POST["item_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "pos.pos_item";
               
               $dbField[0] = "item_id";   // PK
               $dbField[1] = "item_nama";
               $dbField[2] = "item_harga_jual";
               $dbField[3] = "item_tipe";
               $dbField[4] = "item_stock_warning";
               $dbField[5] = "id_grup_item";
               $dbField[6] = "item_icon";
               if ($_POST["btnSave"]) $dbField[7] = "item_harga_beli";
               if ($_POST["btnSave"]) $dbField[8] = "item_jumlah";
              
			
               if(!$itemId) $itemId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$itemId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["item_nama"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga_jual"]));
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["item_tipe"]);
               $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["item_stock_warning"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_grup_item"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["item_icon"]);
               if ($_POST["btnSave"]) $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga_beli"]));
               if ($_POST["btnSave"]) $dbValue[8] = QuoteValue(DPE_NUMERIC,$_POST["item_jumlah"]);
             
			
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
               
               if ($_POST["btnSave"]) {
               $dbTable = "pos.pos_transaksi";
               
               $dbField[0] = "transaksi_id";   // PK
               $dbField[1] = "transaksi_create";
               $dbField[2] = "id_item";
               $dbField[3] = "transaksi_jumlah";
               $dbField[4] = "transaksi_tipe";
               $dbField[5] = "transaksi_saldo";
               $dbField[6] = "id_petugas";
               $dbField[7] = "id_dep";
               $dbField[8] = "transaksi_harga_beli";
               $dbField[9] = "transaksi_harga_jual";
               $dbField[10] = "transaksi_total";
              
			
               if(!$transaksiId) $transaksiId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
               $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[2] = QuoteValue(DPE_CHAR,$itemId);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,0);
               $dbValue[4] = QuoteValue(DPE_CHAR,'A');
               $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["item_jumlah"]);
               $dbValue[6] = QuoteValue(DPE_NUMERIC,$userData["id"]);
               $dbValue[7] = QuoteValue(DPE_CHAR,APP_OUTLET);
               $dbValue[8] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga_beli"]));
               $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga"]));
               $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga_beli"])*$_POST["item_jumlah"]);
             
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
            
               $dtmodel->Insert() or die("insert  error");	
               
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               }
               
               header("location:".$backPage);
               exit();        
          }
     }
 
     if ($_POST["btnDelete"]) {
          $itemId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($itemId);$i<$n;$i++){
               $sql = "delete from pos.pos_item  
                         where item_id = ".QuoteValue(DPE_CHAR,$itemId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }
     
     $tipe[0] = $view->RenderOption("--","Pilih Tipe",$show);	
     if($_POST["item_tipe"]=="V") $show = "selected";
     $tipe[1] = $view->RenderOption("V","Volume Based",$show);
     unset($show);
     if($_POST["item_tipe"]=="N") $show = "selected";
     $tipe[2] = $view->RenderOption("N","Non Volume Based",$show);
     unset($show);
     if($_POST["item_tipe"]=="P") $show = "selected";
     $tipe[2] = $view->RenderOption("P","Pulsa Based",$show);
     unset($show);
     
     
     $sql = "select * from pos.pos_grup_item";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataGrupItem = $dtaccess->FetchAll($rs);
     $gitem[0] = $view->RenderOption("--","[Pilih Grup Item]",$show);
     for($i=0,$n=count($dataGrupItem);$i<$n;$i++){
         unset($show);
         if($_POST["id_grup_item"]==$dataGrupItem[$i]["grup_item_id"]) $show = "selected";
         $gitem[$i+1] = $view->RenderOption($dataGrupItem[$i]["grup_item_id"],$dataGrupItem[$i]["grup_item_nama"],$show);               
    } 

?>

<?php echo $view->RenderBody("inventori.css",false); ?>
<?php echo $view->InitUpload(); ?>

<script type="text/javascript">

	function ajaxFileUpload()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajaxFileUpload
		(
			{
				url:'item_pic.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							alert(data.msg);
						
                                   document.getElementById('item_icon').value= data.file;
                                   document.img_item.src='<?php echo $lokasi."/";?>'+data.file;
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

	}

</script>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>



function CheckDataSave(frm)
{     
	
	if(!frm.item_nama.value){
		alert('Nama Item Harus Diisi');
		frm.item_nama.focus();
          return false;
	}	
	
	
	if(frm.item_tipe.value=='--'){
		alert('Tipe Item Harus Dipilih');
		frm.item_tipe.focus();
          return false;
	}

	if(CheckData(frm.item_nama.value,frm.item_id.value,'type=r')){
		alert('Nama Item Sudah Ada');
		frm.item_nama.focus();
		frm.item_nama.select();
		return false;
	}
	
	return true;
          
}

</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Setup Item</td>
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
            <td align="right" class="tablecontent">Tipe Item</td>
               <td>
                 <?php echo $view->RenderComboBox("item_tipe","item_tipe",$tipe,null,null,null);?>
               </td>
         </tr>
         <tr>
            <td align="right" class="tablecontent">Kategori Item</td>
               <td>
                 <?php echo $view->RenderComboBox("id_grup_item","id_grup_item",$gitem,null,null,null);?>
               </td>
         </tr>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("item_nama","item_nama","60","100",$_POST["item_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
            <td align="right" class="tablecontent"><strong>Tipe</td>
               <td>
                 <?php echo $view->RenderComboBox("item_tipe","item_tipe",$tipe,null,null,null);?>
               </td>
         </tr>
          <?php if($_x_mode=="New") { ?>
          <tr>
               <td align="right" class="tablecontent"><strong>Harga Beli</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("item_harga_beli","item_harga_beli","30","60",$_POST["item_harga_beli"],"inputField", null,true);?>
               </td>
          </tr>
          <?}?>
          <tr>
               <td align="right" class="tablecontent"><strong>Harga Jual</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("item_harga_jual","item_harga_jual","30","60",$_POST["item_harga_jual"],"inputField", null,true);?>
               </td>
          </tr>
          <?php if($_x_mode=="New") { ?>
          <tr>
               <td align="right" class="tablecontent"><strong>Stok Awal</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("item_jumlah","item_jumlah","20","40",$_POST["item_jumlah"],"inputField", null,false);?>
               </td>
          </tr>
          <?php } ?>
          <tr>
               <td align="right" class="tablecontent"><strong>Stok Warning</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("item_stock_warning","item_stock_warning","20","40",$_POST["item_stock_warning"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Icon</strong>&nbsp;</td>
               <td>
                    <img hspace="2" width="30" height="30" name="img_item" id="img_item" src="<?php echo $fotoName;?>" valign="middle" border="1">
                    <input type="hidden" name="item_icon" id="item_icon" value="<?php echo $_POST["item_icon"];?>">
                    <input id="fileToUpload" type="file" size="25" name="fileToUpload" class="inputField">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Icon</button>
                    <span id="loading" style="display:none;"><img width="16" height="16"  id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></span>
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='item_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.item_nama.focus();</script>
<?php echo $view->RenderHidden("item_id","item_id",$itemId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
