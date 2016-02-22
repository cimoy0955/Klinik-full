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
	
     $viewPage = "biaya_view.php?";
     $thisPage = "biaya_edit.php?";
     
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
     if(!$auth->IsAllowed("setup_biaya",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_biaya",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.biaya_id FROM klinik.klinik_biaya a 
                    WHERE upper(a.biaya_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql);
          $dataPaket = $dtaccess->Fetch($rs);
          
		return $dataPaket["biaya_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["biaya_id"])  $opPaketId = $_POST["biaya_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $opPaketId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_biaya a 
				where biaya_id = ".QuoteValue(DPE_CHAR,$opPaketId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["biaya_nama"] = $row_edit["biaya_nama"];
          $_POST["biaya_jenis"] = $row_edit["biaya_jenis"];
	  $_POST["biaya_total"] = $row_edit["biaya_total"];
	  $_POST["biaya_kode"] = $row_edit["biaya_kode"];
	  $_POST["biaya_jenis"] = $row_edit["biaya_jenis"];
		
          $sql = "select a.* from klinik.klinik_biaya_split a 
				where id_biaya = ".QuoteValue(DPE_CHAR,$opPaketId);
          $rs = $dtaccess->Execute($sql);
          $dataPaket = $dtaccess->FetchAll($rs);
		
		for($i=0,$n=count($dataPaket);$i<$n;$i++) {
			$_POST["txtNom"][$dataPaket[$i]["id_split"]] = $dataPaket[$i]["bea_split_nominal"];
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
               $opPaketId = & $_POST["biaya_id"];
               $_x_mode = "Edit";
          }
	  
	  if($_POST["biaya_nama"]) $err_code = clearbit($err_code,1);
	  else $err_code = setbit($err_code,1);
	  
	  if($_POST["biaya_jenis"]!='--') $err_code = clearbit($err_code,5);
	  else $err_code = setbit($err_code,5);
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_biaya";
               
               $dbField[0] = "biaya_id";   // PK
               $dbField[1] = "biaya_nama";
               $dbField[2] = "biaya_jenis";
               $dbField[3] = "biaya_total";
               $dbField[4] = "biaya_kode";
               
			
               if(!$opPaketId) $opPaketId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$opPaketId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["biaya_nama"]);  
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_jenis"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_total"]));
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["biaya_kode"]);
			
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
                  
			$sql = "delete from klinik.klinik_biaya_split
					where id_biaya = ".QuoteValue(DPE_CHAR,$opPaketId);
			$dtaccess->Execute($sql);
			
			$dbTable = "klinik.klinik_biaya_split";
			
			$dbField[0] = "bea_split_id";   // PK
			$dbField[1] = "id_biaya";
			$dbField[2] = "bea_split_nominal";
			$dbField[3] = "id_split";
			
			foreach($_POST["txtNom"] as $split => $value) {
			
			  $beaSplit = $dtaccess->GetTransID();
			  $beaSplitArr[] = $beaSplit;
			  
				$dbValue[0] = QuoteValue(DPE_CHAR,$beaSplit);
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
			unset($dbField);
			
			$dbTable = "acc.acc_convert";
			
			$dbField[0] = "convert_id";   // PK
			$dbField[1] = "biaya_jenis";
			$dbField[2] = "id_bea_split";			
			
			foreach($beaSplitArr as $splot => $valoe) {
			  
        for($i=1,$n=9;$i<$n;$i++){
			  $convId = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHAR,$convId);
				$dbValue[1] = QuoteValue(DPE_CHAR,$i);
				$dbValue[2] = QuoteValue(DPE_CHAR,$valoe);
				
				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		
				$dtmodel->Insert() or die("insert  error");	
			
				unset($dtmodel);
				unset($dbValue);
				unset($dbKey);
        }
        			
			}
			
			$sql = "update klinik.klinik_biaya set biaya_total = ".QuoteValue(DPE_NUMERIC,$beaNominal)." where biaya_id = ".QuoteValue(DPE_CHAR,$opPaketId);
			// $dtaccess->Execute($sql);
			
               header("location:biaya_view.php");
               exit();        
          }
    }
 
     if ($_POST["btnDelete"]) {
          $opPaketId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($opPaketId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_biaya 
                         where biaya_id = ".QuoteValue(DPE_CHAR,$opPaketId[$i]);
               $dtaccess->Execute($sql,DB_SCHEMA);
          }
          
          header("location:biaya_view.php");
          exit();    
     } 

	$sql = "select * from klinik.klinik_split where split_flag like '".SPLIT_PERAWATAN."' order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
     
     /*
      * bikin option untuk combo box jenis layanan
      */
     $optJenis[] = $view->RenderOption("--","[-- jenis layanan --]",(!$_POST["biaya_jenis"]) ? "selected":"",null);
     foreach($biayaStatus as $key => $value){
	  $optJenis[] = $view->RenderOption($key,$value,($_POST["biaya_jenis"] == $key) ? "selected" : "",null);
     }
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm) {
     
     if(!frm.biaya_nama.value){
		alert('Nama Layanan  Harus Diisi');
		frm.biaya_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.biaya_nama.value,'type=r')){
			alert('Nama Layanan  Sudah Ada');
			frm.biaya_nama.focus();
			frm.biaya_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}

function setBiayaTotal() {
     document.getElementById('biaya_total').value = formatCurrency((stripCurrency(document.getElementById('txtNom_1').value) * 1) + (stripCurrency(document.getElementById('txtNom_2').value) * 1));
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Biaya </td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Biaya</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
     	<tr>
               <td align="right" class="tablecontent" width="15%"><strong>Kode<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="85%">
                    <?php echo $view->RenderTextBox("biaya_kode","biaya_kode","50","100",$_POST["biaya_kode"],"inputField", null,false);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="15%"><strong>Nama<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="85%">
                    <?php echo $view->RenderTextBox("biaya_nama","biaya_nama","50","100",$_POST["biaya_nama"],"inputField", null,false);?>
               </td>
          </tr>
	  <tr>
	       <td align="right" class="tablecontent"><strong>Jenis Layanan<?php if(readbit($err_code,5)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
	       <td>
		    <?php echo $view->RenderComboBox("biaya_jenis","biaya_jenis",$optJenis,"inputField",null,null);?>
	       </td>
	  </tr>
       <tr>
          <td class="subheader" align="center">
                 TOTAL
          </td>
          <td align="center">
               <?php echo $view->RenderTextBox("biaya_total","biaya_total","10","10",currency_format($_POST["biaya_total"]),"curedit", null,true,"onfocus=\"setBiayaTotal()\";");?>
          </td>
     </tr>            
          <!-- <tr>
               <td align="right" class="tablecontent"><strong>Rincian Biaya</strong>&nbsp;</td>
			<td>
				<table width="100%" border="1" cellpadding="1" cellspacing="1">
					<tr>
						<?php for($j=0,$k=count($dataSplit);$j<$k;$j++){ ?>
							<td class="subheader" align="center">
								<?php echo $dataSplit[$j]["split_nama"];?>
							</td>
						<?php } ?>
								<?php for($j=0,$k=count($dataSplit);$j<$k;$j++){ ?>
							<td align="center">
								<?php echo $view->RenderTextBox("txtNom[".$dataSplit[$j]["split_id"]."]","txtNom_".$dataSplit[$j]["split_id"],"10","10",currency_format($_POST["txtNom"][$dataSplit[$j]["split_id"]]),"curedit", null,true,"onblur=\"setBiayaTotal()\";");?>
							</td>
						<?php } ?>						
				</table>
			</td>
          </tr>  -->

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

<script>document.frmEdit.biaya_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { 
echo $view->RenderHidden("biaya_id","biaya_id",$opPaketId);
// echo $view->RenderHidden("biaya_jenis","biaya_jenis",$_POST["biaya_jenis"]);
} 

echo $view->RenderHidden("x_mode","x_mode",$_x_mode);
?>
</form>
<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,1)) { ?>
<br>
<font color="green"><strong>Nama Biaya harus diisi</strong></font>
<? } ?>
<? if (readbit($err_code,5)) { ?>
<br>
<font color="green"><strong>Jenis Biaya harus dipilih</strong></font>
<? } ?>
</span>
<?php echo $view->RenderBodyEnd(); ?>
