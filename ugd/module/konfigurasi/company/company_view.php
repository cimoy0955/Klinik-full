<?php
     require_once("root.inc.php");
	require_once($APLICATION_ROOT."library/bitFunc.lib.php");
	require_once($APLICATION_ROOT."library/auth.cls.php");
	require_once($APLICATION_ROOT."library/textEncrypt.cls.php");
	require_once($APLICATION_ROOT."library/datamodel.cls.php");	
	require_once($APLICATION_ROOT."library/dateFunc.lib.php");
	require_once($APLICATION_ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
	require_once($APLICATION_ROOT."library/inoLiveX.php");
 
     $enc = new TextEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $err_code = 0;
     $table = new InoTable("table1","100%","center");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $plx = new InoLiveX("");
     
     $thisPage = "company_view.php";

     if(!$auth->IsAllowed("setup_company",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("setup_company",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";

	if($_POST["sup_id"]) $supId = & $_POST["sup_id"];
     else $supId = 0;

	if($_GET["id_det_sup"]) $idDetSup = $enc->Decode($_GET["id_det_sup"]);
	if($_POST["id_det_sup"]) $idDetSup = $_POST["id_det_sup"];
     
     if ($idDetSup && !$_POST["btnAdd"]) {
		$sql = "select a.*, 
                    b.kota_id 
                    from global.global_supplier_detail a 
                    left join global.vglobal_kota b on b.neg_id = a.id_neg 
                    and b.prop_id = a.id_prop 
                    and b.kota_id = a.id_kota 
                    where a.sup_det_id = ".QuoteValue(DPE_NUMERIC,$idDetSup);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          
          $dtaccess->Clear($rs_edit);

		$_POST["sup_det_alamat"] = $row_edit["sup_det_alamat"];
          $_POST["sup_det_telp"] = $row_edit["sup_det_telp"];
          $_POST["sup_det_fax"] = $row_edit["sup_det_fax"];
          $_POST["kota_id"] = $row_edit["kota_id"];
          $_POST["sup_det_kode_job"] = $row_edit["sup_det_kode_job"];
		$_POST["sup_det_kode_invoice"] = $row_edit["sup_det_kode_invoice"];
          $_POST["sup_det_email"] = $row_edit["sup_det_email"];
		$_POST["sup_det_kode_area"] = $row_edit["sup_det_kode_area"];
     }
     
     if($supId==0 && !$_POST["btnUpdate"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $supId = $supId;
          }
          
          $sql = "select a.* from global.global_supplier a 
                    where a.sup_id = ".QuoteValue(DPE_NUMERIC,$supId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          
          $dtaccess->Clear($rs_edit);
     
          $supId = $row_edit["sup_id"];
          $_POST["sup_nama"] = $row_edit["sup_nama"];          
     }
     
     if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Company";
     
     // -- table config untuk peserta persekutuan --
     $counterHeader = 0;
     if($auth->IsAllowed("setup_company",PRIV_DELETE)){
          $tableSubHeader[$counterHeader]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tableSubHeader[$counterHeader]["width"] = "1%";
          $tableSubHeader[$counterHeader]["align"] = "center";
          $counterHeader++;
     }
     
     if($auth->IsAllowed("setup_company",PRIV_UPDATE)){
          $tableSubHeader[$counterHeader]["name"]  = "Edit";
          $tableSubHeader[$counterHeader]["width"] = "1%";
          $tableSubHeader[$counterHeader]["align"] = "center";
          $counterHeader++;
     }
     
	$tableSubHeader[$counterHeader]["name"]  = "Address";
	$tableSubHeader[$counterHeader]["width"] = "28%";
	$tableSubHeader[$counterHeader]["align"] = "center";
     $counterHeader++;
     
	$tableSubHeader[$counterHeader]["name"]  = "Phone";
	$tableSubHeader[$counterHeader]["width"] = "10%";
	$tableSubHeader[$counterHeader]["align"] = "center";
     $counterHeader++;
     
	$tableSubHeader[$counterHeader]["name"]  = "Fax";
	$tableSubHeader[$counterHeader]["width"] = "10%";
	$tableSubHeader[$counterHeader]["align"] = "center";
     $counterHeader++;
	
	$tableSubHeader[$counterHeader]["name"]  = "Country";
	$tableSubHeader[$counterHeader]["width"] = "9%";
	$tableSubHeader[$counterHeader]["align"] = "center";
     $counterHeader++;
     
     $tableSubHeader[$counterHeader]["name"]  = "City";
	$tableSubHeader[$counterHeader]["width"] = "9%";
	$tableSubHeader[$counterHeader]["align"] = "center";
     $counterHeader++;
     
     $tableSubHeader[$counterHeader]["name"]  = "Email";
	$tableSubHeader[$counterHeader]["width"] = "15%";
	$tableSubHeader[$counterHeader]["align"] = "center";
     $counterHeader++;
     
     $tableSubHeader[$counterHeader]["name"]  = "Kode Job";
	$tableSubHeader[$counterHeader]["width"] = "7%";
	$tableSubHeader[$counterHeader]["align"] = "center";
	$counterHeader++;
	
	$tableSubHeader[$counterHeader]["name"]  = "Kode Invoice";
	$tableSubHeader[$counterHeader]["width"] = "7%";
	$tableSubHeader[$counterHeader]["align"] = "center";
	$counterHeader++;
	
	$tableSubHeader[$counterHeader]["name"]  = "Kode Area";
	$tableSubHeader[$counterHeader]["width"] = "7%";
	$tableSubHeader[$counterHeader]["align"] = "center";

	$jumContent = count($tableSubHeader);	     
     
	if ($_POST["btnUpdate"]) {
		$supId = & $_POST["sup_id"];
		$_x_mode = "Edit";
		
          $err_code = 1;
		//--- Checking Data ---//
          if($_POST["sup_nama"]) $err_code = clearbit($err_code,1);
		else $err_code = setbit($err_code,1);
          //--- End Checking Data ---//

		if ($err_code == 0) {
			$dbTable = "global_supplier";
               
               $dbField[0] = "sup_id";   // PK
               $dbField[1] = "sup_nama";
               
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$supId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["sup_nama"]);
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               $dtmodel->Update() or die("update  error");	
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
		}	
	}
     
     if ($_POST["btnAdd"]) {	
		$err_code = 15;
          //--- Checking Data ---//
          $err_code = clearbit($err_code,1);
          
          if ($_POST["sup_det_alamat"]) $err_code = clearbit($err_code,2); 
          else $err_code = setbit($err_code,2);
          
          if ($_POST["sup_det_kode_job"]) $err_code = clearbit($err_code,3); 
          else $err_code = setbit($err_code,3);
		
		if ($_POST["sup_det_kode_invoice"]) $err_code = clearbit($err_code,4); 
          else $err_code = setbit($err_code,4);
          //--- End Checking Data ---//	
		
		if ($err_code == 0) {
               if($_POST["kota_id"]) {
				$sql = "select b.prop_id, c.neg_id 
						from global.global_kota a 
						join global.global_propinsi b on b.prop_id = a.id_prop 
						join global.global_negara c on c.neg_id = b.id_neg 
						where a.kota_id = ".QuoteValue(DPE_NUMERIC,$_POST["kota_id"]);
				$rs = $dtaccess->Execute($sql);
				$data = $dtaccess->Fetch($rs);

				$_POST["neg_id"] = $data["neg_id"];
				$_POST["prop_id"] = $data["prop_id"];
				$_POST["kota_id"] = $_POST["kota_id"];

			} elseif(!$_POST["kota_id"]) {
				$_POST["neg_id"] = 'Null';
				$_POST["prop_id"] = 'Null';
				$_POST["kota_id"] = 'Null';
			}
               
               $dbTable = "global_supplier_detail";
               
               $dbField[0] = "sup_det_id";   // PK
               $dbField[1] = "sup_det_alamat";
               $dbField[2] = "sup_det_telp";
			$dbField[3] = "sup_det_fax";
               $dbField[4] = "id_neg";
               $dbField[5] = "id_prop";
               $dbField[6] = "id_kota";
               $dbField[7] = "id_sup";
               $dbField[8] = "sup_det_kode_job";
               $dbField[9] = "sup_det_email";
			$dbField[10] = "sup_det_kode_area";
			$dbField[11] = "sup_det_kode_invoice";
               
			if(!$idDetSup) {
                    $supDetId = $dtaccess->GetNewID("global_supplier_detail","sup_det_id",DB_SCHEMA_GLOBAL);
               } else {
                    $supDetId = $idDetSup;
               }               
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$supDetId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["sup_det_alamat"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["sup_det_telp"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["sup_det_fax"]);
               $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["neg_id"]);
               $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["prop_id"]);
               $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["kota_id"]);
               $dbValue[7] = QuoteValue(DPE_NUMERIC,$supId);
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["sup_det_kode_job"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["sup_det_email"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["sup_det_kode_area"]);
			$dbValue[11] = QuoteValue(DPE_CHAR,$_POST["sup_det_kode_invoice"]);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               if (!$idDetSup) {
                    $dtmodel->Insert() or die("insert error");
               } else {
                    $dtmodel->Update() or die("update error");
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
			$_POST["sup_det_alamat"] = "";				
			$_POST["sup_det_telp"] = "";
               $_POST["sup_det_fax"] = "";
               $_POST["sup_det_kode_job"] = "";
               $_POST["sup_det_email"] = "";
			$_POST["kota_id"] = "";
               $_POST["kota_nama"] = "";
               $_POST["prop_id"] = "";
			$_POST["prop_nama"] = "";
               $_POST["neg_id"] = "";
               $_POST["neg_nama"] = "";
               $_POST["sup_det_kode_area"] = "";
			$_POST["sup_det_kode_invoice"] = "";
			$idDetSup="";
		}		
	}
     
     if ($_POST["btnDelete"]) {
          $supDetId = & $_POST["cbDelete"];          
          for($i=0,$n=count($supDetId);$i<$n;$i++){
               $sql = "delete from global_supplier_detail 
                         where sup_det_id = ".QuoteValue(DPE_NUMERIC,$supDetId[$i]);
               $dtaccess->Execute($sql);
          }          
     }
     
     if($supId==0) {
		// --- cari data alamat ---
		$sql = "select a.*, b.neg_nama, b.kota_nama 
                    from global.global_supplier_detail a 
                    left join global.vglobal_kota b on b.neg_id = a.id_neg
                    and b.prop_id = a.id_prop 
                    and b.kota_id = a.id_kota 
                    where a.id_sup = ".QuoteValue(DPE_NUMERIC,$supId);
                    
          if($idDetSup) $sql .= " and a.sup_det_id <> ".QuoteValue(DPE_NUMERIC,$idDetSup);
               
          $sql .= " order by sup_det_id";
          $rs = $dtaccess->Execute($sql);
          $dataTable = $dtaccess->FetchAll($rs);			
	}
	
	// --- buat optionnya combo city ---
     $sql = "select a.* from global.vglobal_kota a 
               order by a.kota_nama";
     $rs = $dtaccess->Execute($sql);
     $dataCity = $dtaccess->FetchAll($rs);
     
?>

<?php echo $view->RenderBody("inosoft.css",true);?>

</head>

<body>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="1" cellspacing="1">
     <tr>
          <td width="30%" align="right" class="tablecontent"><strong>Name<?php if(readbit($err_code,1)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
          <td width="70%">
               <input onKeyDown="return tabOnEnter(this, event);" type="text" name="sup_nama" size="50" maxlength="100" value="<?php echo $_POST["sup_nama"];?>"/>
               <?php if($auth->IsAllowed("setup_company",PRIV_UPDATE)) { ?>
				&nbsp;
				<?php echo $view->RenderButton(BTN_SUBMIT,"btnUpdate","btnUpdate","S a v e","button",false);?>
			<?php } ?>
          </td>
     </tr>
     <tr>
          <td colspan="2">
               <? if (readbit($err_code,1)) { ?>
                    <font color="red"><strong>&nbsp;Check The Field With (*)</strong></font>
                    <br>
                    <font color="purple"><strong>&nbsp;Hint&nbsp;:&nbsp;Field Must Be Filled.</strong></font>
               <? } ?>
          </td>
     </tr>
     <tr>
          <td colspan="2">
          <fieldset>
          <legend><strong>&nbsp;Company Detail&nbsp;</legend></strong>
          <table width="100%" border="1" cellpadding="1" cellspacing="1">
               <tr>
                    <td width="30%" align="right" class="tablecontent"><strong>Address<?php if(readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
                    <td width="70%">
                         <?php echo $view->RenderTextArea("sup_det_alamat","sup_det_alamat","3","60",$_POST["sup_det_alamat"],"inputField");?>
                    </td>
               </tr>
               <tr>
                    <td align="right" class="tablecontent"><strong>Phone</strong>&nbsp;</td>
                    <td>
                         <?php echo $view->RenderTextBox("sup_det_telp","sup_det_telp","50","75",$_POST["sup_det_telp"],"inputField", null,false);?>
                    </td>
               </tr>
               <tr>
                    <td align="right" class="tablecontent"><strong>Fax</strong>&nbsp;</td>
                    <td>
                         <?php echo $view->RenderTextBox("sup_det_fax","sup_det_fax","50","75",$_POST["sup_det_fax"],"inputField", null,false);?>
                    </td>
               </tr>
               <tr>
                    <td align="right" class="tablecontent"><strong>City</strong>&nbsp;</td>
                    <td>
                         <select name="kota_id" onKeyDown="return tabOnEnter(this, event);">
						<option value="" <?php if($_POST["kota_id"]=="") echo "selected";?>>[ Select City ]</option>
						<?php for($i=0, $n=count($dataCity); $i<$n; $i++) { ?>
							<option value="<?php echo $dataCity[$i]["kota_id"];?>" <?php if($_POST["kota_id"]==$dataCity[$i]["kota_id"]) echo "selected";?>>
								<?php echo $dataCity[$i]["kota_nama"];?>
							</option>
						<?php } ?>
					</select>									
                    </td>
               </tr>
               <tr>
                    <td align="right" class="tablecontent"><strong>Email</strong>&nbsp;</td>
                    <td>
                         <?php echo $view->RenderTextBox("sup_det_email","sup_det_email","50","75",$_POST["sup_det_email"],"inputField", null,false);?>
                    </td>
               </tr>
               <tr>
                    <td align="right" class="tablecontent"><strong>Kode Job<?php if(readbit($err_code,3)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
                    <td>
                         <?php echo $view->RenderTextBox("sup_det_kode_job","sup_det_kode_job","3","5",$_POST["sup_det_kode_job"],"inputField", null,false);?>                         
                    </td>
               </tr>
			<tr>
                    <td align="right" class="tablecontent"><strong>Kode Invoice<?php if(readbit($err_code,4)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
                    <td>
                         <?php echo $view->RenderTextBox("sup_det_kode_invoice","sup_det_kode_invoice","2","1",$_POST["sup_det_kode_invoice"],"inputField", null,false);?>                         
                    </td>
               </tr>
			<tr>
                    <td align="right" class="tablecontent"><strong>Kode Area</strong>&nbsp;</td>
                    <td>
                         <?php echo $view->RenderTextBox("sup_det_kode_area","sup_det_kode_area","8","15",$_POST["sup_det_kode_area"],"inputField", null,false);?>
                         <?php if($auth->IsAllowed("setup_company",PRIV_CREATE) || $auth->IsAllowed("setup_company",PRIV_UPDATE)) { ?>
						&nbsp;
						<?php echo $view->RenderButton(BTN_SUBMIT,"btnAdd","btnAdd","S a v e","button",false)?>
					<?php } ?>
                    </td>
               </tr>
               <tr>
                    <td colspan="2">
                         <? if (readbit($err_code,2) || readbit($err_code,3) || readbit($err_code,4)) { ?>
                              <font color="red"><strong>&nbsp;Check The Field With (*)</strong></font>
                              <br>
                              <font color="purple"><strong>&nbsp;Hint&nbsp;:&nbsp;Field Must Be Filled.</strong></font>
                         <? } ?>
                    </td>
               </tr>
          </table>
          
          <table width="100%" border="1" cellpadding="1" cellspacing="1">
               <tr class="subheader">
                    <?php for($i=0;$i<$jumContent;$i++){ ?>
                         <td width="<?php echo $tableSubHeader[$i]["width"];?>" align="<?php echo $tableSubHeader[$i]["align"];?>"><?php echo $tableSubHeader[$i]["name"];?></td>
                    <?php } ?>
               </tr>
               <?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
                    <tr class="<?php if($i%2==0) echo "tablecontent-odd";else echo "tablecontent";?>">
                         <?php if($auth->IsAllowed("setup_company",PRIV_DELETE)){ ?>
                              <td align="center"><div align="center"><?php echo $view->RenderCheckBox("cbDelete[]","cbDelete[]",$dataTable[$i]["sup_det_id"])?></div></td>
                         <?php } ?>
                         <?php if($auth->IsAllowed("setup_company",PRIV_UPDATE)){ ?>
                              <td align="center"><a href='<?php echo $thisPage?>?id_det_sup=<?php echo $enc->Encode($dataTable[$i]["sup_det_id"]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_edit.png" alt="Edit" title="Edit" border="0"></a></td>									
                         <?php } ?>
                         <td wrap>&nbsp;<?php echo nl2br($dataTable[$i]["sup_det_alamat"]);?></td>
                         <td wrap>&nbsp;<?php echo $dataTable[$i]["sup_det_telp"];?></td>
                         <td wrap>&nbsp;<?php echo $dataTable[$i]["sup_det_fax"];?></td>
                         <td nowrap>&nbsp;<?php echo $dataTable[$i]["neg_nama"];?></td>
                         <td nowrap>&nbsp;<?php echo $dataTable[$i]["kota_nama"];?></td>
                         <td wrap>&nbsp;<?php echo $dataTable[$i]["sup_det_email"];?></td>
                         <td wrap align="center">&nbsp;<?php echo $dataTable[$i]["sup_det_kode_job"];?></td>
					<td wrap align="center">&nbsp;<?php echo $dataTable[$i]["sup_det_kode_invoice"];?></td>
					<td wrap align="center">&nbsp;<?php echo $dataTable[$i]["sup_det_kode_area"];?></td>					
                    </tr>
               <?php } ?>
          
               <tr class="tablesmallheader"> 
                  <td colspan="<?php echo $jumContent;?>">
                         <?php if($auth->IsAllowed("setup_company",PRIV_DELETE)){ ?>
                              &nbsp;&nbsp;<?php echo $view->RenderButton(BTN_SUBMIT,"btnDelete","btnDelete","D e l e t e","button",false)?>
                         <?php } ?>
                  </td>  	   
               </tr>
          </table>
          </fieldset>
          </td>
     </tr>
</table>

<script>document.frmEdit.sup_nama.focus();</script>

<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="sup_id" value="<?php echo $supId?>" />
<input type="hidden" name="id_det_sup" value="<?php echo $idDetSup?>" />

</form>

</body>
</html>
<?
    $dtaccess->Close();
?>