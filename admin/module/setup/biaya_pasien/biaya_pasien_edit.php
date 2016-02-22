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
	
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
     if(!$auth->IsAllowed("setup_biaya_pasien",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_biaya_pasien",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($statusLm=null,$jenisLm=null,$status,$jenis,$mode)
	{
          global $dtaccess;
          
          $sql = "SELECT a.biaya_pasien_id FROM klinik.klinik_biaya_pasien a 
                    WHERE upper(a.biaya_pasien_status) = ".QuoteValue(DPE_CHAR,strtoupper($status))."
				and upper(a.biaya_pasien_jenis) = ".QuoteValue(DPE_CHAR,strtoupper($jenis));
				
		if(($statusLm==$status && $jenisLm==$jenis) && $mode=='Edit')
			$sql .= " and biaya_pasien_status <> ".QuoteValue(DPE_CHAR,$statusLm)."
				and biaya_pasien_jenis <> ".QuoteValue(DPE_CHAR,$jenisLm);
		
          $rs = $dtaccess->Execute($sql);
          $dataPaket = $dtaccess->Fetch($rs);
          
		return $dataPaket["biaya_pasien_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["biaya_pasien_id"])  $biayaId = & $_POST["biaya_pasien_id"];
 
     if ($_GET["status"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $statusId = $enc->Decode($_GET["status"]);
               $jenisId = $enc->Decode($_GET["jenis"]);
          }
         
          $sql = "select a.* from klinik.klinik_biaya_pasien a 
				where biaya_pasien_status = ".QuoteValue(DPE_CHAR,$statusId)."
				and biaya_pasien_jenis =  ".QuoteValue(DPE_CHAR,$jenisId); 
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->FetchAll($rs_edit); 
            
		for($i=0,$n=count($row_edit);$i<$n;$i++) { 
			$_POST["biaya_pasien_status"] = $row_edit[$i]["biaya_pasien_status"];
			$_POST["biaya_pasien_jenis"] = $row_edit[$i]["biaya_pasien_jenis"];
			$_POST["biaya"][$row_edit[$i]["id_paket_klaim"]] = $row_edit[$i]["id_paket_klaim"]; 
		} 
		
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
               $biayaId = & $_POST["biaya_pasien_id"];
               $_x_mode = "Edit";
          } 
         
          if ($err_code == 0) {
			$sql = "delete from klinik.klinik_biaya_pasien
					where biaya_pasien_status = ".QuoteValue(DPE_CHAR,$_POST["biaya_pasien_status"])."
					and biaya_pasien_jenis =  ".QuoteValue(DPE_CHAR,$_POST["biaya_pasien_jenis"]); 
			$dtaccess->Execute($sql);
			
			if($_POST["status"]) {
				$sql = "delete from klinik.klinik_biaya_pasien
						where biaya_pasien_status = ".QuoteValue(DPE_CHAR,$_POST["status"])."
						and biaya_pasien_jenis =  ".QuoteValue(DPE_CHAR,$_POST["jenis"]); 
				$dtaccess->Execute($sql); 
			}
			
			$dbTable = "klinik.klinik_biaya_pasien";
			
			$dbField[0] = "biaya_pasien_id";   // PK
			$dbField[1] = "biaya_pasien_status";
			$dbField[2] = "biaya_pasien_jenis";
			$dbField[3] = "id_paket_klaim";
			
			if($_POST["biaya"]) { 
				foreach($_POST["biaya"] as $key => $value) { 
					if(!$_POST["id"][$key]) $_POST["id"][$key] = $dtaccess->GetTransID();
					$dbValue[0] = QuoteValue(DPE_CHAR,$_POST["id"][$key]);
					$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["biaya_pasien_status"]);
					$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_pasien_jenis"]);
					$dbValue[3] = QuoteValue(DPE_CHAR,$value);
					
					$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
					$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
			
					$dtmodel->Insert() or die("insert  error");	
				
					unset($dtmodel);
					unset($dbValue);
					unset($dbKey); 
				}
				 
			}
			
               header("location:biaya_pasien_view.php");
               exit();        
          }
     }
 
     if ($_POST["btnDelete"]) {
          $biayaId = & $_POST["cbDelete"];
		
          for($i=0,$n=count($biayaId);$i<$n;$i++){
			$id = explode("-",$biayaId[$i]);
			
               $sql = "delete from klinik.klinik_biaya_pasien 
                         where biaya_pasien_status = ".QuoteValue(DPE_CHAR,$id[0])." and biaya_pasien_jenis = ".QuoteValue(DPE_CHAR,$id[1]);
               $dtaccess->Execute($sql);
          }
          
          header("location:biaya_pasien_view.php");
          exit();    
     } 

	$sql = "select * from klinik.klinik_paket_klaim 
			order by paket_klaim_nama ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataKlaim = $dtaccess->FetchAll($rs);     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm) {
     
     if(!frm.biaya_pasien_status.value){
		alert('Status Harus Dipilih');
		frm.biaya_pasien_status.focus();
          return false;
	}
	
     if(!frm.biaya_pasien_jenis.value){
		alert('Jenis Pasien Harus Dipilih');
		frm.biaya_pasien_jenis.focus();
          return false;
	}
	 
	if(CheckDataCustomerTipe(frm.status.value,frm.jenis.value,frm.biaya_pasien_status.value,frm.biaya_pasien_jenis.value,frm.x_mode.value,'type=r')){
		alert('Status & Jenis Pasien Sudah Ada'); 
		frm.biaya_pasien_status.focus();
		return false;
	}
	
     document.frmEdit.submit();     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Paket Biaya Klaim </td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Paket Biaya Klaim Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Paket Biaya<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%"> 
				<select name="biaya_pasien_status" id="biaya_pasien_status" onKeyDown="return tabOnEnter(this, event);"> 
					<option value="" >[ Pilih Status ]</option>
					<?php foreach($biayaStatus as $key => $value) { ?>
						<option value="<?php echo $key;?>" <?php if($_POST["biaya_pasien_status"]==$key) echo "selected";?>><?php echo $value;?></option>
					<?php } ?>
				</select>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent" width="15%"><strong>Jenis Pasien<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="85%"> 
				<select name="biaya_pasien_jenis" id="biaya_pasien_jenis" onKeyDown="return tabOnEnter(this, event);">
					<option value="" >[ Pilih Jenis Pasien ]</option>
					<?php foreach($bayarPasien as $key => $value) { ?>
						<option value="<?php echo $key;?>" <?php if($_POST["biaya_pasien_jenis"]==$key) echo "selected";?>><?php echo $value;?></option>
					<?php } ?>
				</select>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent"><strong>Paket Biaya Klaim</strong>&nbsp;</td>
			<td>
				<?php for($i=0,$n=count($dataKlaim);$i<$n;$i++) { ?>
					<?php echo $view->RenderCheckBox("biaya[".$dataKlaim[$i]["paket_klaim_id"]."]","biaya_".$dataKlaim[$i]["paket_klaim_id"],$dataKlaim[$i]["paket_klaim_id"],"inputField",($_POST["biaya"][$dataKlaim[$i]["paket_klaim_id"]]==$dataKlaim[$i]["paket_klaim_id"])?"checked":"");?>&nbsp;<?php echo $view->RenderLabel("biaya[".$dataKlaim[$i]["paket_klaim_id"]."]","biaya_".$dataKlaim[$i]["paket_klaim_id"],$dataKlaim[$i]["paket_klaim_nama"]);?><br>
				<?php } ?>
			</td>
          </tr> 
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='biaya_pasien_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.biaya_pasien_status.focus();</script> 
<?php echo $view->RenderHidden("jenis","jenis",$_POST["biaya_pasien_jenis"]);?>
<?php echo $view->RenderHidden("status","status",$_POST["biaya_pasien_status"]);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
