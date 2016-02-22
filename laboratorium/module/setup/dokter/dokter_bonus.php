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
     
     $viewPage = "dokter_view.php";
     $bonusPage = "dokter_bonus.php";
     
     if($_POST["x_mode"]) $_x_mode = $_POST["x_mode"]; 
     
     $sql = "select * from lab_bonus order by bonus_id";
     $rs_bonus = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     $data_bonus = $dtaccess->FetchAll($rs_bonus);
     
     if($_GET["id"] && !$_POST["dokter_id"]) $_POST["dokter_id"] = $enc->Decode($_GET["id"]);
     
     $sql = "select * from lab_bonus_dokter where id_dokter=".QuoteValue(DPE_CHAR,$_POST["dokter_id"])." order by id_bonus";
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     $data_edit = $dtaccess->FetchAll($rs_edit);
     
     $sql = "select dokter_nama from lab_dokter where dokter_id=".QuoteValue(DPE_CHAR,$_POST["dokter_id"]);
     $rs_dokter = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     $data_dokter = $dtaccess->Fetch($rs_dokter);
     
     
     if($_POST["btnSave"] || $_POST["btnUpdate"]){
     
      $dbTable = "lab_bonus_dokter";
      
      $dbField[0] = "bonus_dokter_id";
      $dbField[1] = "id_dokter";
      $dbField[2] = "id_bonus";
      $dbField[3] = "bonus_dokter_persen";
      
      foreach($_POST["bonus_persen"] as $key=>$value){
        if(!$_POST["bonus_dokter_id"][$key]) $_POST["bonus_dokter_id"][$key] = $dtaccess->GetTransID("lab_bonus_dokter","bonus_dokter_id",DB_SCHEMA_LAB);
        $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["bonus_dokter_id"][$key]);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["dokter_id"]);
        $dbValue[2] = QuoteValue(DPE_CHAR,$key);
        $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["bonus_persen"][$key]));
        
        $dbKey[0] = 0;
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LAB);
        
        if($_POST["btnSave"]) $dtmodel->Insert() or die("insert  error");
        elseif($_POST["btnUpdate"]) $dtmodel->Update() or die("update  error");
        
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);
      }
      
      unset($dbTable);
      unset($dbField);
      
      header("location:".$viewPage);
      exit(); 
     }
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Bonus Dokter</td>
    </tr>
</table>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="45%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Bonus Dokter</strong></legend>
        <table width="100%" border="1" cellpadding="1" cellspacing="1">
            <tr>
                <td class="tablecontent" width="30%" style="text-align:right">Nama Dokter&nbsp;</td>
                <td class="tablecontent-odd" width="70%" style="text-align:left">
                  <div name="div_nama">&nbsp;<?php echo $data_dokter["dokter_nama"];?></div>
                  <?php echo $view->RenderHidden("dokter_id","dokter_id",$_POST["dokter_id"]); ?>  
                </td>
            </tr>
            <?php for($i=0;$i<count($data_bonus);$i++) {?>
              <?php if(!$_POST["bonus_persen_[".$data_bonus[$i]["bonus_id"]."]"]) $_POST["bonus_persen_[".$data_bonus[$i]["bonus_id"]."]"] =0;?>
              <tr>
                <td class="tablecontent" width="30%" style="text-align:right">
                  <?php echo $data_bonus[$i]["bonus_nama"];?>&nbsp;
                  <?php echo $view->RenderHidden("bonus_dokter_id[".$data_bonus[$i]["bonus_id"]."]","bonus_dokter_id[".$data_bonus[$i]["bonus_id"]."]",$data_edit[$i]["bonus_dokter_id"]); ?> 
                </td>
                <td class="tablecontent-odd" width="70%" style="text-align:left">&nbsp;
                  <?php echo $view->RenderTextBox("bonus_persen[".$data_bonus[$i]["bonus_id"]."]","bonus_persen[".$data_bonus[$i]["bonus_id"]."]","10","2",currency_format($data_edit[$i]["bonus_dokter_persen"]),"curedit",null,true); ?>&nbsp;%
                  <?php //echo $data_bonus[$i]["bonus_id"];?>
                </td>
              </tr>
            <?php }?>
            <tr>
                <td colspan="2" style="text-align:right">
                  <input type="submit" name="<? echo ($_x_mode=="Edit")?"btnUpdate":"btnSave";?>" id="btnSave" value="Simpan" class="button" />&nbsp;
                  <input type="button" name="btnKembali" value="Kembali" class="button" onClick="document.location.href='<?php echo $viewPage;?>'" />
                  </td>
            </tr>
        </table>
     </fieldset>
     <?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode); ?>
</form>
<?php echo $view->RenderBodyEnd(); ?>