<?php
     require_once("root.inc.php");
    
	   require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	$auth = new CAuth();
     $err_code = 0;
     
     $viewPage  = "pembayaran_view.php";
     $editPage  = "pembayaran_edit.php";
     $biayaPage = "rawat_dokter_find.php?";
	   $splitPage = "rawat_suster_find.php?";
	   $accPage   = "rawat_bidan_find.php?";
	
	
	
     if(!$auth->IsAllowed("report_registrasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_registrasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	

	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["convert_id"])  $poliId = & $_POST["convert_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $convertId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.*,c.biaya_nama,d.split_nama, e.nama_prk, f.nama_prk as nama_prk2 
             from acc.acc_convert a
             left join klinik.klinik_biaya_split b on a.id_bea_split=b.bea_split_id
             left join klinik.klinik_biaya c on b.id_biaya=c.biaya_id
             left join klinik.klinik_split d on b.id_split=d.split_id 
             left join gl.gl_perkiraan e on e.id_prk = a.id_prk
             left join gl.gl_perkiraan f on f.id_prk = a.id_prk_kredit
				     where a.convert_id = ".QuoteValue(DPE_CHAR,$convertId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);


          $_POST["biaya_nama"] = $row_edit["biaya_nama"];
          $_POST["split_nama"] = $row_edit["split_nama"];
          $_POST["id_prk"] = $row_edit["id_prk"];
          $_POST["biaya_jenis"] = $row_edit["biaya_jenis"];
          $_POST["id_prk_kredit"] = $row_edit["id_prk_kredit"];
          $_POST["nama_prk"] = $row_edit["nama_prk"];
          $_POST["nama_prk2"] = $row_edit["nama_prk2"];
          
     
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
               $convertId = & $_POST["convert_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $dbTable = "acc.acc_convert";
               
               $dbField[0] = "convert_id";   // PK
               $dbField[1] = "id_prk";
               $dbField[2] = "id_prk_kredit";
               
			
               if(!$convertId) $convertId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$convertId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_prk"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_prk2"]);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
               
                  header("location:".$viewPage);
                  exit();
          }
     }
 
    
     
    
     
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

function CheckDataSave(frm)
{ 
     
     if(!frm.poli_nama.value){
		alert('Nama Jenis Poli Harus Diisi');
		frm.poli_nama.focus();
          return false;
	}

     document.frmEdit.submit();     
}

var _wnd_Acc;	

	function OpenAccount(_val_,_val_code,_val_mode,_val_cat,_val_akt) 
    {
        var _child_url = '<?php echo($APLICATION_ROOT);?>/module/acc/registrasi/findaccount.php?_confirm='+_val_+'&_code='+_val_code+'&_mode='+_val_mode+'&_cat='+_val_cat+'&_akt='+_val_akt;
        if(!_wnd_Acc) {
            _wnd_Acc = window.open(_child_url,'_sequence_','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=600,height=550,left=300,top=200,dependent');
        } else {
            if(_wnd_Acc.closed) {
                _wnd_Acc = window.open(_child_url,'_sequence_','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=600,height=550,left=300,top=200,dependent');
            } else {
                _wnd_Acc.focus();
            }
        }
       return false;
    }

var _wnd_Acc2;	

	function OpenAccount2(_val_2,_val_code2,_val_mode2,_val_cat2,_val_akt2) 
    {
        var _child_url2 = '<?php echo($APLICATION_ROOT);?>/module/acc/registrasi/findaccount2.php?_confirm='+_val_2+'&_code='+_val_code2+'&_mode='+_val_mode2+'&_cat='+_val_cat2+'&_akt='+_val_akt2;
        if(!_wnd_Acc2) {
            _wnd_Acc2 = window.open(_child_url2,'_sequence_','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=600,height=550,left=300,top=200,dependent');
        } else {
            if(_wnd_Acc2.closed) {
                _wnd_Acc2 = window.open(_child_url2,'_sequence_','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=600,height=550,left=300,top=200,dependent');
            } else {
                _wnd_Acc2.focus();
            }
        }
       return false;
    }
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Account Registrasi </td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Akuntansi</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama Biaya</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("biaya_nama","biaya_nama","50","100",$_POST["biaya_nama"],"inputField", "readonly",false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama Split</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("split_nama","split_nama","50","100",$_POST["split_nama"],"inputField", "readonly",false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Biaya Jenis</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("biaya_jenis","biaya_jenis","50","100",$bayarPasien[$_POST["biaya_jenis"]],"inputField", "readonly",false);?>
               </td>
          </tr>
          
          <tr>
      			<td class="tablecontent" align="right" nowrap>Kode Akun Debit&nbsp;&nbsp;</td>
      			<td nowrap><input name="no_prk" type="text" size="25" maxlength="20" value="<?php echo $_POST["id_prk"];?>" /><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="14" height="14" OnClick="javascript:OpenAccount('frmEdit.no_prk',document.frmEdit.no_prk.value,'dept','yes','yes');" title="Pilih Akun" alt="Pilih Akun" class="img-button"/>			
      			</td>			
      	  </tr>
      		<tr> 
      			<td width="20%" align="right" nowrap class="tablecontent">Nama Perkiraan Debit&nbsp;&nbsp;</td>
      			<td width="80%" class="tblCol"><input readonly onKeyDown="return tabOnEnter_select_with_button(this, event);" type="text" class="inputField" name="nama_prk" size="50" maxlength="100" value="<?php echo $row_edit["nama_prk"];?>"/></td>		
      		</tr>
      	
          <tr>
      			<td class="tablecontent" align="right" nowrap>Kode Akun Kredit&nbsp;&nbsp;</td>
      			<td nowrap><input name="no_prk2" type="text" size="25" maxlength="20" value="<?php echo $_POST["id_prk_kredit"];?>" /><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="14" height="14" OnClick="javascript:OpenAccount2('frmEdit.no_prk2',document.frmEdit.no_prk2.value,'dept','yes','yes');" title="Pilih Akun" alt="Pilih Akun" class="img-button"/>			
      			</td>			
      	  </tr>
      		<tr> 
      			<td width="20%" align="right" nowrap class="tablecontent">Nama Perkiraan Kredit&nbsp;&nbsp;</td>
      			<td width="80%" class="tblCol"><input readonly onKeyDown="return tabOnEnter_select_with_button(this, event);" type="text" class="inputField" name="nama_prk2" size="50" maxlength="100" value="<?php echo $row_edit["nama_prk2"];?>"/></td>		
      		</tr>	
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='".$viewPage."';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>
<input type="hidden" name="id_prk" value="<?php echo $_POST["id_prk"];?>" />
<input type="hidden" name="id_prk2" value="<?php echo $_POST["id_prk_kredit"];?>" />
<script>document.frmEdit.poli_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("convert_id","convert_id",$convertId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
