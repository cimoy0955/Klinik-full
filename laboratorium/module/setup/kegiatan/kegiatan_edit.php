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
     
     $viewPage = "kegiatan_view.php";
     $editPage = "kegiatan_edit.php";
     $hiddenPage = "kegiatan_view_hidden.php";
	
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.kegiatan_id FROM lab_kegiatan a 
                    WHERE upper(a.kegiatan_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          $datakegiatan = $dtaccess->Fetch($rs);
          
		return $datakegiatan["kegiatan_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["kegiatan_id"])  $kegiatanId = & $_POST["kegiatan_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $kegiatanId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from lab_kegiatan a 
				        where kegiatan_id = ".QuoteValue(DPE_CHAR,$kegiatanId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["kegiatan_nama"] = $row_edit["kegiatan_nama"];
          $_POST["id_kategori"] = $row_edit["id_kategori"];
          $_POST["id_bonus"] = $row_edit["id_bonus"];
          $_POST["kegiatan_satuan"] = $row_edit["kegiatan_satuan"];
          $_POST["kegiatan_biaya"] = currency_format($row_edit["kegiatan_biaya"]);
          $_POST["kegiatan_nilai_normal_dewasa_laki"] = $row_edit["kegiatan_nilai_normal_dewasa_laki"];
          $_POST["kegiatan_nilai_normal_dewasa_wanita"] = $row_edit["kegiatan_nilai_normal_dewasa_wanita"];
          $_POST["kegiatan_nilai_normal_anak"] = $row_edit["kegiatan_nilai_normal_anak"];
      
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
               $kegiatanId = & $_POST["kegiatan_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $dbTable = "lab_kegiatan";
               
               $dbField[0] = "kegiatan_id";   // PK
               $dbField[1] = "id_kategori"; 
               $dbField[2] = "id_bonus"; 
               $dbField[3] = "kegiatan_nama"; 
               $dbField[4] = "kegiatan_satuan"; 
               $dbField[5] = "kegiatan_biaya"; 
               $dbField[6] = "kegiatan_nilai_normal_dewasa_laki"; 
               $dbField[7] = "kegiatan_nilai_normal_dewasa_wanita"; 
               $dbField[8] = "kegiatan_nilai_normal_anak"; 
               $dbField[9] = "is_active"; 

               if(!$kegiatanId) $kegiatanId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$kegiatanId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kegiatan_kategori"]); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["kegiatan_bonus"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["kegiatan_nama"]);  
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["kegiatan_satuan"]); 
               $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["kegiatan_biaya"]));
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["kegiatan_nilai_normal_dewasa_laki"]);  
               $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["kegiatan_nilai_normal_dewasa_wanita"]); 
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["kegiatan_nilai_normal_anak"]); 
               $dbValue[9] = QuoteValue(DPE_CHAR,"y"); 

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LAB);
   
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
 
     if ($_POST["btnDelete"]) {
          $kegiatanId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($kegiatanId);$i<$n;$i++){
               $sql = "update lab_kegiatan set is_active = 'n'
                         where kegiatan_id = ".QuoteValue(DPE_CHAR,$kegiatanId[$i]);
               $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          }
          
          header("location:".$viewPage);
          exit();    
     }
     
     if ($_POST["btnEnable"]) {
          $kegiatanId = & $_POST["cbEnable"];
          
          for($i=0,$n=count($kegiatanId);$i<$n;$i++){
               $sql = "update lab_kegiatan set is_active = 'y'
                         where kegiatan_id = ".QuoteValue(DPE_CHAR,$kegiatanId[$i]);
               $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          }
          
          header("location:".$hiddenPage);
          exit();    
     }
     
     $sql = "select * from lab_bonus where is_active = 'y' order by bonus_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     while($data_bonus = $dtaccess->Fetch($rs)){
          unset($show);
          if($data_bonus["bonus_id"]==$_POST["id_bonus"]) $show = "selected";
          $opt_bonus[] = $view->RenderOption($data_bonus["bonus_id"],$data_bonus["bonus_nama"],$show);
     }
     
     $sql = "select * from lab_kategori where is_active = 'y' order by kategori_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     while($data_kategori = $dtaccess->Fetch($rs)){
          unset($show);
          if($data_kategori["kategori_id"]==$_POST["id_kategori"]) $show = "selected";
          $opt_kat[] = $view->RenderOption($data_kategori["kategori_id"],$data_kategori["kategori_nama"],$show);
     } 
     
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 
     
     if(!frm.kegiatan_nama.value){
		alert('Nama kegiatan Harus Diisi');
		frm.kegiatan_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.kegiatan_nama.value,'type=r')){
			alert('Nama kegiatan Sudah Ada');
			frm.kegiatan_nama.focus();
			frm.kegiatan_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Layanan</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Kegiatan Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
              <td align="right" class="tablecontent" width="30%">Kategori Pemeriksaan</td>
              <td width="70%">
                    <?php echo $view->RenderComboBox("kegiatan_kategori","kegiatan_kategori",$opt_kat,"inputfield");?>
               </td>
          </tr>
          <tr>
              <td align="right" class="tablecontent" width="30%">Kategori Bonus Dokter</td>
              <td width="70%">
                    <?php echo $view->RenderComboBox("kegiatan_bonus","kegiatan_bonus",$opt_bonus,"inputfield");?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama Pemeriksaan<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("kegiatan_nama","kegiatan_nama","50","100",$_POST["kegiatan_nama"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nilai Normal Dewasa Laki-laki</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("kegiatan_nilai_normal_dewasa_laki","kegiatan_nilai_normal_dewasa_laki","50","100",$_POST["kegiatan_nilai_normal_dewasa_laki"],"inputField", null,true);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nilai Normal Dewasa Wanita</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("kegiatan_nilai_normal_dewasa_wanita","kegiatan_nilai_normal_dewasa_wanita","50","100",$_POST["kegiatan_nilai_normal_dewasa_wanita"],"inputField", null,true);?>&nbsp;<label style="font-size: small; font-style: italic; font-color: #afafaf;">Kosongkan jika tidak ada</label>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nilai Normal Anak</strong></td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("kegiatan_nilai_normal_anak","kegiatan_nilai_normal_anak","50","100",$_POST["kegiatan_nilai_normal_anak"],"inputField", null,true);?>&nbsp;<label style="font-size: small; font-style: italic; font-color: #afafaf;">Kosongkan jika tidak ada</label>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Satuan&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("kegiatan_satuan","kegiatan_satuan","20","100",$_POST["kegiatan_satuan"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Biaya Pemeriksaan&nbsp;</strong></td>
               <td width="70%">&nbsp;Rp.&nbsp;
                    <?php echo $view->RenderTextBox("kegiatan_biaya","kegiatan_biaya","20","60",$_POST["kegiatan_biaya"],"inputField", null,true);?>
               </td>
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

<script>document.frmEdit.kegiatan_kode.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("kegiatan_id","kegiatan_id",$kegiatanId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
