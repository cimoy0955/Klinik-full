<?php
    require_once("root.inc.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");
    require_once($ROOT."library/currFunc.lib.php");
    require_once($ROOT."library/dateFunc.lib.php");
    
$dtaccess = new DataAccess();
    $enc = new textEncrypt();
	  $auth = new CAuth();
    $err_kode = 0;
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["trans_id"])  $transUangId = & $_POST["trans_id"];
	
	//data paten kasir
  $_POST["trans_create"] = date("Y-m-d H:i:s");
  $_POST["id_petugas"]= $auth->GetUserId();
  
  
    if ($_GET["id"]) {
        if ($_POST["btnDelete"]) { 
            $_x_mode = "Delete";
        } else { 
            $_x_mode = "Edit";
            $transUangId = $enc->Decode($_GET["id"]);
        }
        $sql = "select * from mp_member_trans where trans_id like '".$transUangId."'";
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $_POST["trans_create"] = FormatDateFromTimestamp($row_edit["trans_create"]);
		    $_POST["id_petugas"] = $row_edit["id_petugas"];
		    $_POST["trans_harga_total"] = $row_edit["trans_harga_total"];
		    $_POST["trans_ket"] = $row_edit["trans_ket"];
		
    }
    

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;

    if(!$auth->IsAllowed($privFiles[$_SERVER["PHP_SELF"]],$privMode)){
        die("access_denied");
        exit(1);
    }


    if ($_POST["btnNew"]) {
        header("location: ".$_SERVER["PHP_SELF"]);
        exit();
    }
   
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
		if($_POST["btnUpdate"]){
			$transUangId = & $_POST["trans_uang_id"];
			$_x_mode = "Edit";
		}
        
        //Cari Nama User
        $sql = "select * from global_auth_user where usr_id =".$_POST["id_petugas"];
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataUser = $dtaccess->Fetch($rs);
      
        if(!$_POST["meja_aktif"]) $_POST["meja_aktif"]="n";
        //--- End Checking Data ---//

        if ($err_kode == 0) {
            $dbTable = "mp_member_trans";
            
            $dbField[0] = "trans_id";   // PK
            $dbField[1] = "trans_create";
			      $dbField[2] = "id_petugas";
			      $dbField[3] = "trans_harga_total";
			      $dbField[4] = "trans_ket";
			      $dbField[5] = "trans_jenis";
			      $dbField[6] = "trans_nama";
			      $dbField[7] = "trans_petugas";
			      $dbField[8] = "id_dep";


            if(!$transUangId) $transUangId = $dtaccess->GetTransID(); 

            $dbValue[0] = QuoteValue(DPE_CHAR,$transUangId);
            $dbValue[1] = QuoteValue(DPE_DATE,$_POST["trans_create"]);
			      $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["id_petugas"]);
			      $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["trans_harga_total"]));
			      $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["trans_ket"]);
			      $dbValue[5] = QuoteValue(DPE_CHAR,'O');
			      $dbValue[6] = QuoteValue(DPE_CHAR,$dataUser["usr_name"]);
			      $dbValue[7] = QuoteValue(DPE_CHAR,$dataUser["usr_name"]);
			      $dbValue[8] = QuoteValue(DPE_CHAR,APP_OUTLET);


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
            
            header("location:kasir_operasional_view.php");
            exit();        
        }
    }

    if ($_POST["btnDelete"]) {
        $transUangId = & $_POST["cbDelete"];
        for($i=0,$n=count($transUangId);$i<$n;$i++){
            $sql = "delete from mp_member_trans where trans_id like '".$transUangId[$i]."'";
            $dtaccess->Execute($sql,DB_SCHEMA);
        }
        header("location:kasir_operasional_view.php");
        exit();    
    }

   $sql = "select * from global_auth_user where usr_id = ".$_POST["id_petugas"]; 
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataUser = $dtaccess->Fetch($rs);
	
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>library/css/calendar-system.css">

<!-- calendar script -->

<script type="text/javascript" src="<?php echo $ROOT;?>library/script/jscalendar/calendar.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>library/script/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>library/script/jscalendar/calendar-setup.js"></script>
<!-- end -->
<script type="text/javascript" src="<?php echo $ROOT;?>library/script/func_curr.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>library/script/elements.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inventori.css">
</head>

<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td align="center" width="50%" class="navActive">Edit Operasional</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="0" cellpadding="1" cellspacing="1">
<tr>
    <td>
    <fieldset>
    <legend><strong>&nbsp;Operasional</strong></legend>
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
		<tr>
            <td align="right" class="tablecontent"><strong>User</strong></td>
            <td><input type="text" readonly id="id_petugas" name="id_petugas" size="25" maxlength="25" value="<?php echo $dataUser["usr_name"];?>" onKeyDown="return tabOnEnter(this, event);"/></td>
        </tr>
		<tr>
		<tr>
            <td align="right" class="tablecontent"><strong>Nominal</strong></td>
            <td><input onFocus="this.select();" onKeyDown="return tabOnEnter(this, event);" onKeyUp="this.value=formatCurrency(this.value);" type="text" name="trans_harga_total" size="20" maxlength="20" value="<?php echo currency_format($_POST["trans_harga_total"])?>"></td>
        </tr>
		<tr>
            <td align="right" class="tablecontent"><strong>Keperluan</strong></td>
            <td><textarea name="trans_ket" rows="3" cols="100"><?php echo $_POST["trans_ket"];?></textarea></td>
        </tr>
        <tr>
            <td colspan="2" align="right">
                <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Save" class="button"/>
                <input type="submit" name="btnNew" value="New" class="button"/>
				<input type="button" name="btnBack" value="Kembali" class="button" onClick="document.location.href='<?php echo "kasir_operasional_view.php";?>'">
            </td>
        </tr>
    </table>
    </fieldset>
    </td>
</tr>
</table>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="trans_uang_id" value="<?php echo $transUangId?>" />

<? } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
</form>

</body>
</html>
<?
    $dtaccess->Close();
?>
