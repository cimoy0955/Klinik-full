<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($ROOT."library/currFunc.lib.php");
	   require_once($APLICATION_ROOT."library/view.cls.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0;
	
	   
	
     if(!$auth->IsAllowed("setup_konfigurasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_konfigurasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["game_id"])  $gameId = & $_POST["game_id"];

     
   $lokasi = $ROOT."admin/images/logo";

    
         
   $_x_mode = "Edit";
   
  if (!$_POST["btnUpdate"]) {        
  $sql = "select * from mp_konfigurasi where konf_id = 0";
  $rs_edit = $dtaccess->Execute($sql);
  $row_edit = $dtaccess->Fetch($rs_edit);
  $dtaccess->Clear($rs_edit);
  
  $_POST["konf_nama"] = $row_edit["konf_nama"];
  if ($row_edit["konf_shift"]>0) $_POST["konf_shift"] = $row_edit["konf_shift"];
  $_POST["konf_header_satu"] = $row_edit["konf_header_satu"];
  $_POST["konf_header_dua"] = $row_edit["konf_header_dua"];
  $_POST["konf_header_tiga"] = $row_edit["konf_header_tiga"];
  $_POST["konf_harga_member"] = currency_format($row_edit["konf_harga_member"]);
  $_POST["konf_jam_member"] = $row_edit["konf_jam_member"];
  $_POST["konf_game_lokasi"] = $row_edit["konf_game_lokasi"];
  $_POST["konf_masa_guest"] = $row_edit["konf_masa_guest"];
  $_POST["konf_masa_member_mp"] = $row_edit["konf_masa_member_mp"];
  $_POST["konf_harga_warnet"] = $row_edit["konf_harga_warnet"];
  $_POST["konf_outlet"] = $row_edit["konf_outlet"];
  $_POST["konf_pic"] = $row_edit["konf_pic"];
  $_POST["id_gudang"] = $row_edit["konf_gudang"];
  $fotoName = $lokasi."/".$row_edit["konf_pic"];
  }

	
   
     if ($_POST["btnUpdate"]) {          
     $fotoName = $lokasi."/".$_POST["konf_pic"];    
         
          if ($err_code == 0) {
               $dbTable = "mp_konfigurasi";
               
               $dbField[0] = "konf_id";   // PK
               $dbField[1] = "konf_nama";
               $dbField[2] = "konf_header_satu";
               $dbField[3] = "konf_header_dua";
               $dbField[4] = "konf_header_tiga";
               $dbField[5] = "konf_harga_member";
               $dbField[6] = "konf_jam_member";
               $dbField[7] = "konf_game_lokasi";          
               $dbField[8] = "konf_masa_guest";
               $dbField[9] = "konf_masa_member_mp";
               $dbField[10] = "konf_harga_warnet";
               $dbField[11] = "konf_outlet";
               $dbField[12] = "konf_gudang";
               if ($_POST["konf_pic"]) $dbField[13] = "konf_pic";
			 
               $dbValue[0] = QuoteValue(DPE_NUMERIC,0);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["konf_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["konf_header_satu"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["konf_header_dua"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["konf_header_tiga"]);
               $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["konf_harga_member"]);
               $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["konf_jam_member"]);
               $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["konf_game_lokasi"]);
               $dbValue[8] = QuoteValue(DPE_NUMERIC,$_POST["konf_masa_guest"]);
               $dbValue[9] = QuoteValue(DPE_NUMERIC,$_POST["konf_masa_member_mp"]);
               $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["konf_harga_warnet"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["konf_outlet"]);
               $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
               if ($_POST["konf_pic"]) $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["konf_pic"]);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               $dtmodel->Update() or die("update  error");	
               
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);   
               $simpan=1;
               //header("location:konfigurasi_edit.php");
               //exit();  
          }
     }
 
    
     
     //master gudang
     $sql = "select * from pos_gudang";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
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
				url:'konfigurasi_pic.php',
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
						
                                   document.getElementById('konf_pic').value= data.file;
                                   document.img_konf.src='<?php echo $lokasi."/";?>'+data.file;
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



function CheckDataSave(frm)
{     
	
	if(!frm.konf_nama.value){
		alert('Nama Harus Diisi');
		frm.konf_nama.focus();
          return false;
	}	

    if(!frm.konf_game_lokasi.value){
		alert('Game Lokasi Harus Diisi');
		frm.konf_game_lokasi.focus();
          return false;
	}
	
	
	
	if(!frm.konf_pic.value){
		alert('Icon Harus Diupload');
		frm.konf_pic.focus();
          return false;
	}	
	
	
	return true;
          
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Konfigurasi POS</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Konfigurasi POS</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="40%"><strong>Nama</strong>&nbsp;</td>
               <td width="60%">
				            <?php echo $view->RenderTextBox("konf_nama","konf_nama","50","100",$_POST["konf_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Header Satu</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_header_satu","konf_header_satu","50","100",$_POST["konf_header_satu"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Header Dua</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_header_dua","konf_header_dua","50","100",$_POST["konf_header_dua"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Header Tiga</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_header_tiga","konf_header_tiga","50","100",$_POST["konf_header_tiga"],"inputField", null,false);?>
               </td>
          </tr>
           <tr>
               <td align="right" class="tablecontent"><strong>Kode Outlet</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_outlet","konf_outlet","50","100",$_POST["konf_outlet"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
            <td align="right" class="tablecontent">Gudang Yang Dipakai</td>
               <td>
                 <?php echo $view->RenderComboBox("id_gudang","id_gudang",$GGudang,null,null,null);?>
               </td>
         </tr>
                    <tr>
               <td align="right" class="tablecontent"><strong>Logo</strong>&nbsp;</td>
               <td>
                    <img hspace="2" width="139" height="58" name="img_konf" id="img_konf" src="<?php echo $fotoName;?>" valign="middle" border="1">
                    <input type="hidden" name="konf_pic" id="konf_pic" value="<?php echo $_POST["konf_pic"];?>">
                    <input id="fileToUpload" type="file" size="25" name="fileToUpload" class="inputField">
                    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload Logo</button>
                    <span id="loading" style="display:none;"><img width="16" height="16"  id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></span>
               </td>
          </tr>
     </table>
     </fieldset>
     <fieldset>
     <legend><strong>Konfigurasi Warnet/Game</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
                    <tr>
               <td align="right" class="tablecontent"><strong>Harga Member</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_harga_member","konf_harga_member","20","100",$_POST["konf_harga_member"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Jam Member</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_jam_member","konf_jam_member","10","100",$_POST["konf_jam_member"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Game Lokasi</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_game_lokasi","konf_game_lokasi","30","100",$_POST["konf_game_lokasi"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Masa Berlaku Guest</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_masa_guest","konf_masa_guest","10","100",$_POST["konf_masa_guest"],"inputField", null,false);?> Hari
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Masa Berlaku Member MP</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_masa_member_mp","konf_masa_member_mp","10","100",$_POST["konf_masa_member_mp"],"inputField", null,false);?> Bulan
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Harga Warnet per 15 menit</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("konf_harga_warnet","konf_harga_warnet","20","100",$_POST["konf_harga_warnet"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,"btnUpdate","btnUpdate","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"history.back()\"");?>                   
                    <?php //echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='index.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.game_nama.focus();</script>
<?php if($simpan) { ?>
    <font color="red"><strong>Konfigurasi telah disimpan</strong></font>
<?php } ?>
<?php echo $view->RenderHidden("game_id","game_id",$gameId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
