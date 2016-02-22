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
     
     $viewPage = "tindakan_view.php";
     $editPage = "tindakan_edit.php";
	
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
     if(!$auth->IsAllowed("setup_tindakan_rawat_inap",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_tindakan_rawat_inap",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT select * from klinik.klinik_biaya
                    WHERE biaya_jenis like '%T%' and upper(a.biaya_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $datasplit = $dtaccess->Fetch($rs);
          
		return $datasplit["biaya_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["biaya_id"])  $biayaId = & $_POST["biaya_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $biayaId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_biaya a 
			           	where biaya_jenis like '%T%' and biaya_id = ".QuoteValue(DPE_CHAR,$biayaId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["biaya_nama"] = $row_edit["biaya_nama"];
          $_POST["biaya_jenis"] = $row_edit["biaya_jenis"];  
          
          $sql = "select a.* from klinik.klinik_biaya_split a 
			            	where id_biaya = ".QuoteValue(DPE_CHAR,$biayaId);
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
               $biayaId = & $_POST["biaya_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_biaya";
               
               $dbField[0] = "biaya_id";   // PK
               $dbField[1] = "biaya_nama"; 
               $dbField[2] = "biaya_jenis";
             
			
               if(!$biayaId) $biayaId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["biaya_nama"]); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_jenis"]);   
        
			
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
               
                                 
			$sql = "delete from klinik.klinik_biaya_split
					where id_biaya = ".QuoteValue(DPE_CHAR,$biayaId);
			$dtaccess->Execute($sql);
			
			$dbTable = "klinik.klinik_biaya_split";
			
			$dbField[0] = "bea_split_id";   // PK
			$dbField[1] = "id_biaya";
			$dbField[2] = "bea_split_nominal";
			$dbField[3] = "id_split";

			
			
			foreach($_POST["txtNom"] as $split => $value) {
			
				$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
				$dbValue[1] = QuoteValue(DPE_CHAR,$biayaId);
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
			$sql = "update klinik.klinik_biaya set biaya_total = ".QuoteValue(DPE_NUMERIC,$beaNominal)." where biaya_id = ".QuoteValue(DPE_CHAR,$biayaId);
			$dtaccess->Execute($sql);

                  header("location:".$viewPage);
                  exit();
          }
     }
     
    $sql = "select * from klinik.klinik_split where split_flag like '".SPLIT_PERAWATAN."' order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);  
 
     if ($_POST["btnDelete"]) {
          $biayaId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($biayaId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_biaya
                         where split_id = ".QuoteValue(DPE_CHAR,$biayaId[$i]);
               $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          }
          
          header("location:".$viewPage);
          exit();    
     } 
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 
     
     if(!frm.biaya_nama.value){
		alert('Nama Jenis Biaya Harus Diisi');
		frm.biaya_nama.focus();
          return false;
	}

     document.frmEdit.submit();     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Tindakan</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Tindakan</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="15%"><strong>Nama<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("biaya_nama","biaya_nama","50","100",$_POST["biaya_nama"],"inputField", null,false);?>
               </td>
          </tr>
       <!--   <tr>
               <td align="right" class="tablecontent" width="15%"><strong>Kode</td>
               <td width="85%" colspan="">
                    <?php echo $view->RenderTextBox("biaya_kode","biaya_kode","50","100",$_POST["biaya_kode"],"inputField", null,false);?>
               </td>
          </tr>-->
          <tr class="tablecontent">
          <td align="right" width="10%">&nbsp;Kelas</td>
              <td width="35%">
                  <select name="biaya_jenis">
    				  <option value="">[ Pilih Kelas Tindakan ]</option>
    				<?php foreach($namaTindakan as $key=>$value) {?>
    					<option value="<?php echo $key;?>" <?php if($_POST["biaya_jenis"]==$key) echo "selected";?>><?php echo $value;?></option>
    				<?php } ?>
    			       </select>
              </td> 
        </tr> 

          <tr>
               <td align="right" class="tablecontent"><strong>Rincian Biaya</strong>&nbsp;</td>
			<td>
				<table width="100%" border="1" cellpadding="1" cellspacing="1">
					<tr>
						<?php for($j=0,$k=count($dataSplit);$j<$k;$j++){ ?>
							<td class="tablecontent" align="center">
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
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='tindakan_view.php';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.biaya_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("biaya_id","biaya_id",$biayaId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
