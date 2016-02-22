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
	
	$plx = new InoLiveX("CheckDataIna");
	
     if(!$auth->IsAllowed("setup_ina",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_ina",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataIna($inaKode,$inaId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.ina_id FROM klinik.klinik_ina a 
                    WHERE upper(a.ina_kode) = ".QuoteValue(DPE_CHAR,strtoupper($inaKode));
                    
          if($inaId) $sql .= " and a.ina_id <> ".QuoteValue(DPE_CHAR,$inaId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaina = $dtaccess->Fetch($rs);
          
		return $dataAdaina["ina_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["ina_id"])  $inaId = & $_POST["ina_id"];
     if($_GET["jenis"]) $_POST["ina_jenis"] = $_GET["jenis"]; 

     $backPage = "ina_view.php?jenis=".$_POST["ina_jenis"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $inaId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_ina a 
				where ina_id = ".QuoteValue(DPE_CHAR,$inaId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["ina_kode"] = $row_edit["ina_kode"];
          $_POST["ina_nama"] = $row_edit["ina_nama"];
		
          $sql = "select a.* from klinik.klinik_ina_split a 
				where id_ina = ".QuoteValue(DPE_CHAR,$inaId);
          $rs = $dtaccess->Execute($sql);
          $dataPaket = $dtaccess->FetchAll($rs);
		
		for($i=0,$n=count($dataPaket);$i<$n;$i++) {
			$_POST["txtNom"][$dataPaket[$i]["id_split"]] = $dataPaket[$i]["ina_split_nominal"];
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
               $inaId = & $_POST["ina_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_ina";
               
               $dbField[0] = "ina_id";   // PK
               $dbField[1] = "ina_kode";
               $dbField[2] = "ina_nama";
               $dbField[3] = "ina_jenis";
			
               if(!$inaId) $inaId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$inaId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["ina_kode"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["ina_nama"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["ina_jenis"]); 
			
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
			
			$sql = "delete from klinik.klinik_ina_split
					where id_ina = ".QuoteValue(DPE_CHAR,$inaId);
			$dtaccess->Execute($sql);
			
			$dbTable = "klinik.klinik_ina_split";
			
			$dbField[0] = "ina_split_id";   // PK
			$dbField[1] = "id_ina";
			$dbField[2] = "ina_split_nominal";
			$dbField[3] = "id_split";
			
//			foreach($_POST["txtNom"] as $split => $value) {
				
				$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
				$dbValue[1] = QuoteValue(DPE_CHAR,$inaId);
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
			
			$sql = "update klinik.klinik_ina set ina_nominal = ".QuoteValue(DPE_NUMERIC,$beaNominal)." where ina_id = ".QuoteValue(DPE_CHAR,$inaId);
			$dtaccess->Execute($sql);
               
               header("location:".$backPage);
               exit();        
          }
    // }
 
     if ($_POST["btnDelete"]) {
          $inaId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($inaId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_ina  
                         where ina_id = ".QuoteValue(DPE_CHAR,$inaId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }	
	$sql = "select * from klinik.klinik_split order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);  
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{     
     if(!frm.ina_kode.value){
		alert('Nomor Harus Diisi');
		frm.ina_kode.focus();
          return false;
	}
	
	if(!frm.ina_nama.value){
		alert('Nama Harus Diisi');
		frm.ina_nama.focus();
          return false;
	}	
	
	if(CheckDataIna(frm.ina_kode.value,frm.ina_id.value,'type=r')){
		alert('Nomor Sudah Ada');
		frm.ina_kode.focus();
		frm.ina_kode.select();
		return false;
	}
	
	return true;
          
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit ina drg </td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>ina drg Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="10%"><strong>Kode</strong>&nbsp;</td>
               <td width="80%">
                    <?php echo $view->RenderTextBox("ina_kode","ina_kode","10","20",$_POST["ina_kode"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="10%"><strong>Nama</strong>&nbsp;</td>
               <td width="80%">
				<?php echo $view->RenderTextBox("ina_nama","ina_nama","50","100",$_POST["ina_nama"],"inputField", null,false);?>                    
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
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='".$backPage."';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.ina_kode.focus();</script>
<?php echo $view->RenderHidden("ina_id","ina_id",$inaId);?>
<?php echo $view->RenderHidden("ina_jenis","ina_jenis",$_POST["ina_jenis"]);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
