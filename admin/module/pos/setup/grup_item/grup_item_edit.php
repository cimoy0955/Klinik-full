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
	   $usrId = $auth->GetUserId();
     $err_code = 0;
     
     $userData = $auth->GetUserData();
	
	  $plx = new InoLiveX("CheckData");
	  
     if(!$auth->IsAllowed("pos_grup_item",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("pos_grup_item",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	   
	   $lokasi = $ROOT."admin/images/item";
	   
	function CheckData($grupmenuNama,$grupmenuId=null)//nanti ae
	{
          global $dtaccess;
          
          $sql = "SELECT grup_item_id FROM pos_grup_item a 
                    WHERE upper(a.grup_item_nama) = ".QuoteValue(DPE_CHAR,strtoupper($grupmenuNama));
                    
          if ($grupmenuId) $sql .= " and a.grup_item_id <> ".QuoteValue(DPE_CHAR,$grupmenuId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdamenu = $dtaccess->Fetch($rs);
        
		return $dataAdamenu["grup_item_id"];
     }
  

    
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["grup_item_id"])  $grupmenuId = & $_POST["grup_item_id"];

     $backPage = "grup_item_view.php?";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $grupmenuId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from pos_grup_item where 
                  id_dep = ".QuoteValue(DPE_CHAR,$_POST["outlet"])."
                  and grup_item_id = ".QuoteValue(DPE_CHAR,$grupmenuId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["grup_item_nama"] = $row_edit["grup_item_nama"];
          $_POST["grup_pic"] = $row_edit["grup_pic"];
          $fotoName = $lokasi."/".$row_edit["grup_pic"];

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
               $grupmenuId = & $_POST["grup_item_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "pos_grup_item";
               
               $dbField[0] = "grup_item_id";   // PK
               $dbField[1] = "grup_item_nama";
               $dbField[2] = "id_dep";
               if ($_POST["grup_pic"]) $dbField[3] = "grup_pic";
            
               if(!$grupmenuId) $grupmenuId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$grupmenuId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["grup_item_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["outlet"]);
               if ($_POST["grup_pic"]) $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["grup_pic"]);
               
             
			
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
          $grupmenuId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($grupmenuId);$i<$n;$i++){
               $sql = "delete from pos_grup_item  
                         where grup_item_id = ".QuoteValue(DPE_CHAR,$grupmenuId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }
     
    

?>

<?php echo $view->RenderBody("inventori.css",false); ?>
<?php echo $view->InitUpload(); ?>

<script language="javascript" type="text/javascript">

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
				url:'grup_item_pic.php',
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
						
                                   document.getElementById('grup_pic').value= data.file;
                                   document.img_grup_item.src='<?php echo $lokasi."/";?>'+data.file;
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



<? $plx->Run(); ?>

function CheckDataSave(frm)
{     
	
	if(!frm.grup_item_nama.value){
		alert('Nama Kategori menu Harus Diisi');
		frm.grup_item_nama.focus();
          return false;
	}	
	
	
	return true;
          
}

</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Kategori Menu</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Ketegori Menu</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
         
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Kategori Nama</strong>&nbsp;</td>
               <td width="80%">
				            <?php echo $view->RenderTextBox("grup_item_nama","grup_item_nama","60","100",$_POST["grup_item_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Gambar</strong>&nbsp;</td>
               <td>
                    <img hspace="2" width="100" height="100" name="img_grup_item" id="img_grup_item" src="<?php echo $fotoName;?>" valign="middle" border="1">
                    <input type="hidden" name="grup_pic" id="grup_pic" value="<?php echo $_POST["grup_pic"];?>">
                    <input id="fileToUpload" type="file" size="25" name="fileToUpload" class="inputField">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Gambar</button>
                    <span id="loading" style="display:none;"><img width="25" height="25"  id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></span>
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='grup_item_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.menu_nama.focus();</script>
<?php echo $view->RenderHidden("grup_item_id","grup_item_id",$grupmenuId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
