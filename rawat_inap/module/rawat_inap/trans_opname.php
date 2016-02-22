<?php
      require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");    
     require_once($APLICATION_ROOT."library/view.cls.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table","70%","left");
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0;
	   
	   $userData = $auth->GetUserData();
	   
	
     if(!$auth->IsAllowed("rawat_inap",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("rawat_inap",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["game_id"])  $gameId = & $_POST["game_id"];
  if(!$_POST["opname_tanggal"]) $_POST["opname_tanggal"] = format_date(getDateToday());
     
   $lokasi = $ROOT."pos/gambar/logo";

    
         
   $_x_mode = "Edit";
   
  if (!$_POST["btnUpdate"]) {        
    
  $sql = "select * from logistik.logistik_item";
  $rs_edit = $dtaccess->Execute($sql);
  $dataItem = $dtaccess->FetchAll($rs_edit); 
     
  }

	
   
     if ($_POST["btnUpdate"]) 
     {          

     
     for($i=0,$counter=0,$n=count($dataItem);$i<$n;$i++,$counter=0)
     {
     $dbTable = "logistik.logistik_item";
     
     $dbField[0] = "item_id";   // PK
     $dbField[1] = "item_nama";
     $dbField[2] = "item_stok";
     
     
     $itemId = $dtaccess->GetTransID();   
     $dbValue[0] = QuoteValue(DPE_CHAR,$itemId);
     $dbValue[1] = QuoteValue(DPE_CHAR,$dataItem[$i]["item_nama"]);
     $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["stokHand".$i]);
    
  
     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  
     $dtmodel->Update() or die("update  error");	
     
     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey);   
     

     }
     
     $simpan=1;
   
     }
   
 
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>




<script language="javascript" type="text/javascript">



function CheckDataSave(frm)
{     
	
	if(!frm.opname_tanggal.value){
		alert('Tanggal Harus Diisi');
		frm.opname_tanggal.focus();
          return false;
	}
	return true;
          
}
</script>

<table width="100%" >
     <tr class="tableheader">
        <td width="100%">&nbsp;Saldo Stok</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" >
	<tr>
               <td colspan="3"><?php echo $view->RenderLabel("lblTanggal","lblTanggal",Tanggal, null,false);?>
                    <?php echo $view->RenderTextBox("opname_tanggal","opname_tanggal","15","30",$_POST["opname_tanggal"],"inputField", "readonly",null,false);?>
                    <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_opname_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               </td>
          </tr>
          <tr>
               <td colspan="3">&nbsp;</td>
               
          </tr>
          <tr>
<td width="70%">
    <fieldset>
		  <table width="100%"> 
			  
          <tr>
               <td align="center" class="subheader" width="40%">Nama Item</td>
               <td align="center" class="subheader" width="20%">Stok Barang</td>
              
				                                
             
          </tr>
          <?php for($i=0,$counter=0,$n=count($dataItem);$i<$n;$i++,$counter=0){ ?>
          <tr  class="<?php if($i%2==0) echo 'tablecontent-odd'; else echo 'tablecontent'; ?>">
               <td align="center">
                    <?php echo $view->RenderLabel("item_nama","item_nama",$dataItem[$i]["item_nama"], null,false);?>
               </td>
               <td>
                    <?php echo $view->RenderTextBox("stokHand$i","stokHand$i","20","40",$dataItem[$i]["item_stok"],"inputField", null,false);?>
               </td>
              
          </tr>
          <?php } ?>
         
          <tr>
               <td colspan="3" align="right">
                    <?php if(!$simpan) echo $view->RenderButton(BTN_SUBMIT,"btnUpdate","btnUpdate","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                                        
               </td>
          </tr> 
		</table>
		</fieldset>
		</td>
	</tr>
</table>

<script>document.frmEdit.game_nama.focus();</script>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "opname_tanggal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_opname_tanggal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php if($simpan) { ?>
    <font color="red"><strong>Opname telah disimpan</strong></font>
<?php } ?>
<?php echo $view->RenderHidden("game_id","game_id",$gameId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
