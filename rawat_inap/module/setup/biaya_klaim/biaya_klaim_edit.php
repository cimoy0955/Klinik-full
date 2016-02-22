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
	
     if(!$auth->IsAllowed("setup_biaya_klaim",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_biaya_klaim",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.paket_klaim_id FROM klinik.klinik_paket_klaim a 
                    WHERE upper(a.paket_klaim_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql);
          $dataPaket = $dtaccess->Fetch($rs);
          
		return $dataPaket["paket_klaim_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["paket_klaim_id"])  $opPaketId = & $_POST["paket_klaim_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $opPaketId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_paket_klaim a 
				where paket_klaim_id = ".QuoteValue(DPE_CHAR,$opPaketId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["paket_klaim_nama"] = $row_edit["paket_klaim_nama"];
		
          $sql = "select a.* from klinik.klinik_paket_klaim_split a 
				where id_paket_klaim = ".QuoteValue(DPE_CHAR,$opPaketId);
          $rs = $dtaccess->Execute($sql);
          $dataPaket = $dtaccess->FetchAll($rs);
		
		for($i=0,$n=count($dataPaket);$i<$n;$i++) {
			$_POST["txtNom"][$dataPaket[$i]["id_split"]] = $dataPaket[$i]["klaim_split_nominal"];
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
               $opPaketId = & $_POST["paket_klaim_id"];
               $_x_mode = "Edit";
          } 
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_paket_klaim";
               
               $dbField[0] = "paket_klaim_id";   // PK
               $dbField[1] = "paket_klaim_nama";  
			
               if(!$opPaketId) $opPaketId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$opPaketId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["paket_klaim_nama"]);   
			
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
                  
			$sql = "delete from klinik.klinik_paket_klaim_split
					where id_paket_klaim = ".QuoteValue(DPE_CHAR,$opPaketId);
			$dtaccess->Execute($sql);
			
			$dbTable = "klinik.klinik_paket_klaim_split";
			
			$dbField[0] = "klaim_split_id";   // PK
			$dbField[1] = "id_paket_klaim";
			$dbField[2] = "klaim_split_nominal";
			$dbField[3] = "id_split";
			
			//foreach($_POST["txtNom"] as $split => $value) {
			
				$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
				$dbValue[1] = QuoteValue(DPE_CHAR,$opPaketId);
				$dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($value));
				$dbValue[3] = QuoteValue(DPE_CHAR,$split);
				
				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		
				$dtmodel->Insert() or die("insert  error");	
			
				unset($dtmodel);
				unset($dbValue);
				unset($dbKey);
				$beaNominal += StripCurrency($value);
			}
			
			$sql = "update klinik.klinik_paket_klaim set paket_klaim_total = ".QuoteValue(DPE_NUMERIC,$beaNominal)." where paket_klaim_id = ".QuoteValue(DPE_CHAR,$opPaketId);
			$dtaccess->Execute($sql);
			
               header("location:biaya_klaim_view.php");
               exit();        
          }
   //  }
 
     if ($_POST["btnDelete"]) {
          $opPaketId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($opPaketId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_paket_klaim 
                         where paket_klaim_id = ".QuoteValue(DPE_CHAR,$opPaketId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:biaya_klaim_view.php");
          exit();    
     } 

	$sql = "select * from klinik.klinik_split order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm) {
     
     if(!frm.paket_klaim_nama.value){
		alert('Nama Paket Biaya Klaim  Harus Diisi');
		frm.paket_klaim_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.paket_klaim_nama.value,'type=r')){
			alert('Nama  Paket Biaya Klaim  Sudah Ada');
			frm.paket_klaim_nama.focus();
			frm.paket_klaim_nama.select();
			return false;
		}
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
<table width="100%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Paket Biaya Klaim Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="15%"><strong>Nama<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("paket_klaim_nama","paket_klaim_nama","50","100",$_POST["paket_klaim_nama"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent"><strong>Rincian Biaya</strong>&nbsp;</td>
			<td>
				<table width="100%" border="1" cellpadding="1" cellspacing="1">
					<tr>
						<?php for($j=0,$k=count($dataSplit);$j<$k;$j++){ ?>
							<td class="subheader" align="center">
								<?php echo $dataSplit[$j]["split_nama"];?>
							</td>
						<?php } ?>
					</tr> 
					<tr>
						<?php for($j=0,$k=count($dataSplit);$j<$k;$j++){ ?>
							<td align="center">
								<?php echo $view->RenderTextBox("txtNom[".$dataSplit[$j]["split_id"]."]","txtNom_".$dataSplit[$j]["split_id"],"10","10",currency_format($_POST["txtNom"][$dataSplit[$j]["split_id"]]),"curedit", null,true);?>
							</td>
						<?php } ?>
					</tr>
				</table>
			</td>
          </tr> 
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='biaya_klaim_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.paket_klaim_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("paket_klaim_id","paket_klaim_id",$opPaketId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
