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
          
          $sql = "SELECT item_id FROM pos_item a 
                    WHERE upper(a.item_nama) = ".QuoteValue(DPE_CHAR,strtoupper($itemNama));
                    
          if ($itemId) $sql .= " and a.item_id <> ".QuoteValue(DPE_CHAR,$itemId);
          
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_POS);
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
         
          $sql = "select a.* from pos_item a 
                  where item_id = ".QuoteValue(DPE_CHAR,$itemId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_POS);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["item_nama"] = $row_edit["item_nama"];
          $_POST["item_tipe"] = $row_edit["item_tipe"];
          $_POST["item_kode"] = $row_edit["item_kode"];
          $_POST["item_jumlah"] = currency_format($row_edit["item_jumlah"]);
          $_POST["item_harga_beli"] = currency_format($row_edit["item_harga_beli"]);
          $_POST["item_harga_jual"] = currency_format($row_edit["item_harga_jual"]);
          $_POST["item_tipe"] = $row_edit["item_tipe"];
          $_POST["item_stock_warning"] = $row_edit["item_stock_warning"];
          $_POST["id_grup_item"] = $row_edit["id_grup_item"];
          $_POST["id_satuan_beli"] = $row_edit["id_satuan_beli"];
          $_POST["id_satuan_jual"] = $row_edit["id_satuan_jual"];
          $_POST["item_isi"] = $row_edit["item_isi"];
          $_POST["id_dep"] = $row_edit["id_dep"];
          $_POST["item_pic"] = $row_edit["item_pic"];
          $_POST["item_order"] = $row_edit["item_order"];
          $fotoName = $lokasi."/".$row_edit["item_pic"];
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
               
               //ambil data outlet dan data gudang
               $sql = "select konf_outlet,konf_gudang from mp_konfigurasi 
                  where konf_id = 0";
               $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_POS);
               $konfigurasi = $dtaccess->Fetch($rs_edit);
          
          
               $dbTable = "pos_item";
               
               $dbField[0] = "item_id";   // PK
               $dbField[1] = "item_jumlah";
               $dbField[2] = "item_nama";
               $dbField[3] = "item_pic";
               $dbField[4] = "item_kode";
               $dbField[5] = "item_stock_warning";
               $dbField[6] = "id_dep";
               $dbField[7] = "item_order";
               $dbField[8] = "id_grup_item";
               $dbField[9] = "item_harga_jual";
               $dbField[10] = "item_harga_beli";
               $dbField[11] = "id_gudang";
               $dbField[12] = "id_satuan_beli";
               $dbField[13] = "id_satuan_jual";
               $dbField[14] = "item_isi";
               $dbField[15] = "item_tipe";
               
               
              
			
               if(!$itemId) $itemId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$itemId);
               $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_jumlah"]));
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_nama"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["item_pic"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["item_kode"]);
               $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["item_stock_warning"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$konfigurasi["konf_outlet"]);
               $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["item_order"]);
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_grup_item"]);
               $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga_jual"]));
               $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_harga_beli"]));
               $dbValue[11] = QuoteValue(DPE_CHAR,$konfigurasi["konf_outlet"]);
               $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["id_satuan_beli"]);
               $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_satuan_jual"]);
               $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["item_isi"]));
               $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["item_tipe"]);
               
              
             
			
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
               $dbTable = "pos_stok_item";
               
               $dbField[0] = "stok_item_id";   // PK
               $dbField[1] = "id_item";
               $dbField[2] = "id_gudang";
               $dbField[3] = "id_dep";
               $dbField[4] = "stok_item_jumlah";
               $dbField[5] = "stok_item_flag";
               
               $stokItemId = $dtaccess->GetTransID();  
               $dbValue[0] = QuoteValue(DPE_CHAR,$stokItemId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$itemId);
               $dbValue[2] = QuoteValue(DPE_CHAR,$konfigurasi["konf_gudang"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$konfigurasi["konf_outlet"]);
               $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["item_jumlah"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,'A');
             
			
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
     }
 
     if ($_POST["btnDelete"]) {
          $itemId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($itemId);$i<$n;$i++){
               $sql = "delete from pos_item  
                         where item_id = ".QuoteValue(DPE_CHAR,$itemId[$i]);
               $dtaccess->Execute($sql,DB_SCHEMA_POS);
          }
          
          header("location:".$backPage);
          exit();    
     }
     
     //grup item
     $sql = "select * from pos_grup_item";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_POS);
     $dataGrupItem = $dtaccess->FetchAll($rs);
     $gitem[0] = $view->RenderOption("--","[Pilih Kategori Item]",$show);
     for($i=0,$n=count($dataGrupItem);$i<$n;$i++){
         unset($show);
         if($_POST["id_grup_item"]==$dataGrupItem[$i]["grup_item_id"]) $show = "selected";
         $gitem[$i+1] = $view->RenderOption($dataGrupItem[$i]["grup_item_id"],$dataGrupItem[$i]["grup_item_nama"],$show);               
    } 
    
     //satuan harga beli
     $sql = "select * from pos_satuan_beli";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_POS);
     $dataSatuanBeli = $dtaccess->FetchAll($rs);
     $GSatBeli[0] = $view->RenderOption("--","[Pilih Satuan Pembelian]",$show);
     for($i=0,$n=count($dataSatuanBeli);$i<$n;$i++){
         unset($show);
         if($_POST["id_satuan_beli"]==$dataSatuanBeli[$i]["satuan_beli_id"]) $show = "selected";
         $GSatBeli[$i+1] = $view->RenderOption($dataSatuanBeli[$i]["satuan_beli_id"],$dataSatuanBeli[$i]["satuan_beli_nama"],$show);               
    } 
    
    //satuan harga jual
     $sql = "select * from pos_satuan_jual";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_POS);
     $dataSatuanJual = $dtaccess->FetchAll($rs);
     $GSatJual[0] = $view->RenderOption("--","[Pilih Satuan Penjualan]",$show);
     for($i=0,$n=count($dataSatuanJual);$i<$n;$i++){
         unset($show);
         if($_POST["id_satuan_jual"]==$dataSatuanJual[$i]["satuan_jual_id"]) $show = "selected";
         $GSatJual[$i+1] = $view->RenderOption($dataSatuanJual[$i]["satuan_jual_id"],$dataSatuanJual[$i]["satuan_jual_nama"],$show);               
    } 
    
    //satuan harga jual
     $sql = "select * from pos_satuan_jual";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_POS);
     $dataSatuanJual = $dtaccess->FetchAll($rs);
     $GSatJual[0] = $view->RenderOption("--","[Pilih Satuan Penjualan]",$show);
     for($i=0,$n=count($dataSatuanJual);$i<$n;$i++){
         unset($show);
         if($_POST["id_satuan_jual"]==$dataSatuanJual[$i]["satuan_jual_id"]) $show = "selected";
         $GSatJual[$i+1] = $view->RenderOption($dataSatuanJual[$i]["satuan_jual_id"],$dataSatuanJual[$i]["satuan_jual_nama"],$show);               
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
    
    //master gudang
     $sql = "select * from pos_gudang";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_POS);
     $dataGudang = $dtaccess->FetchAll($rs);
     $GGudang[0] = $view->RenderOption("--","[Pilih Gudang]",$show);
     for($i=0,$n=count($dataGudang);$i<$n;$i++){
         unset($show);
         if($_POST["id_gudang"]==$dataGudang[$i]["gudang_id"]) $show = "selected";
         $GGudang[$i+1] = $view->RenderOption($dataGudang[$i]["gudang_id"],$dataGudang[$i]["gudang_nama"],$show);               
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
						
                                   document.getElementById('item_pic').value= data.file;
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
	
	if(!frm.item_kode.value){
		alert('Kode Item Harus Diisi');
		frm.item_kode.focus();
          return false;
	}
	
	if(!frm.item_harga_jual.value){
		alert('Harga Jual Item Harus Diisi');
		frm.item_harga_jual.focus();
          return false;
	}
	
	if(frm.id_grup_item.value=='--'){
		alert('Kategorii Item Harus Diisi');
		frm.id_grup_item.focus();
          return false;
	}
	
	if(frm.id_satuan_beli.value=='--'){
		alert('Satuan Pembelian Harus Diisi');
		frm.id_satuan_beli.focus();
          return false;
	}
	
	if(frm.id_satuan_jual.value=='--'){
		alert('Satuan Penjualan Harus Diisi');
		frm.id_satuan_jual.focus();
          return false;
	}
	
	if(frm.item_isi.value=='--'){
		alert('Isi Item Pembelian Harus Diisi');
		frm.item_isi.focus();
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

<table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Setup Item</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Keterangan Item</strong></legend>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
        <tr>
            <td align="right" class="tablecontent" width="20%">Tipe Item</td>
               <td>
                 <?php echo $view->RenderComboBox("item_tipe","item_tipe",$tipe,null,null,null);?>
               </td>
         </tr>
        <tr>
            <td align="right" class="tablecontent" width="20%">Kategori Item</td>
               <td>
                 <?php echo $view->RenderComboBox("id_grup_item","id_grup_item",$gitem,null,null,null);?>
               </td>
         </tr>
         <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Kode</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("item_kode","item_kode","60","100",$_POST["item_kode"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("item_nama","item_nama","60","100",$_POST["item_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Urutan (optional)</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("item_order","item_order","60","100",$_POST["item_order"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Icon</strong>&nbsp;</td>
               <td>
                    <img hspace="2" width="60" height="60" name="img_item" id="img_item" src="<?php echo $fotoName;?>" valign="middle" border="1">
                    <input type="hidden" name="item_pic" id="item_pic" value="<?php echo $_POST["item_pic"];?>">
                    <input id="fileToUpload" type="file" size="25" name="fileToUpload" class="inputField">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Icon</button>
                    <span id="loading" style="display:none;"><img width="16" height="16"  id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></span>
               </td>
          </tr> 
     </table>
     </fieldset>
     <fieldset>
     <legend><strong>Stok Barang</strong></legend>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
           <?php if($_x_mode=="New") { ?>
           <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Jumlah</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("item_jumlah","item_jumlah","20","40",$_POST["item_jumlah"],"inputField", null,false);?>
               </td>
          </tr>
          <?php } ?>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Stok Warning</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("item_stock_warning","item_stock_warning","20","40",$_POST["item_stock_warning"],"inputField", null,false);?>
               </td>
          </tr>
     </table>
     </fieldset>
     <fieldset>
     <legend><strong>Harga</strong></legend>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
     <?php if($_x_mode=="New") { ?>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Harga Beli</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("item_harga_beli","item_harga_beli","30","60",$_POST["item_harga_beli"],"inputField", null,true);?>
               </td>
          </tr>
    <?php } ?>
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Harga Jual</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("item_harga_jual","item_harga_jual","30","60",$_POST["item_harga_jual"],"inputField", null,true);?>
               </td>
          </tr>
     </table>
     </fieldset>
     <fieldset>
     <legend><strong>Satuan dan Isi</strong></legend>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
         <tr>
            <td align="right" class="tablecontent" width="20%">Satuan Pembelian</td>
               <td>
                 <?php echo $view->RenderComboBox("id_satuan_beli","id_satuan_beli",$GSatBeli,null,null,null);?>
               </td>
         </tr>
         <tr>
            <td align="right" class="tablecontent" width="20%">Satuan Penjualan</td>
               <td>
                 <?php echo $view->RenderComboBox("id_satuan_jual","id_satuan_jual",$GSatJual,null,null,null);?>
               </td>
         </tr>
         <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Isi</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("item_isi","item_isi","20","20",$_POST["item_isi"],"inputField", null,true);?>
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
   </tr>
   <tr>
       <td colspan="2" align="right">
            <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
            <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='item_view.php';\"");?>                    
       </td>
  </tr>
</table>

<script>document.frmEdit.item_nama.focus();</script>
<?php echo $view->RenderHidden("item_id","item_id",$itemId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
