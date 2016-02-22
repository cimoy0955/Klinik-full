<?php
    require_once("root.inc.php");
    require_once($APLICATION_ROOT."library/bitFunc.lib.php");
    require_once($APLICATION_ROOT."library/auth.cls.php");
    require_once($APLICATION_ROOT."library/textEncrypt.cls.php");
    require_once($APLICATION_ROOT."library/datamodel.cls.php");
    
    
    $dtaccess = new DataAccess();
    $enc = new textEncrypt();
    $auth = new CAuth();
    $err_code = 0;
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["jab_id"])  $jabId = & $_POST["jab_id"];
 
    if ($_GET["id"]) {
        if ($_POST["btnDelete"]) { 
            $_x_mode = "Delete";
        } else { 
            $_x_mode = "Edit";
            $jabId = $enc->Decode($_GET["id"]);
        }
        $sql = "select * from akad_jabatan where jab_id = ".$jabId;
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $_POST["jab_nama"] = $row_edit["jab_nama"];
    }
    
    if($_x_mode=="New") $privMode = PRIV_CREATE;
    elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
    else $privMode = PRIV_DELETE;

    if(!$auth->IsAllowed("setup_user",$privMode)){
        die("access_denied");
        exit(1);
    }

    if ($_POST["btnNew"]) {
        header("location: ".$_SERVER["PHP_SELF"]);
        exit();
    }
   
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
		if($_POST["btnUpdate"]){
			$jabId = & $_POST["jab_id"];
			$_x_mode = "Edit";
		}
        
        $err_code = 3;
        //--- Checking Data ---//
        if ($_POST["jab_nama"]) $err_code = clearbit($err_code,1); 
        else $err_code = setbit($err_code,1);
        
        if ($_POST["btnSave"]) 
            $sql = sprintf("SELECT jab_id FROM akad_jabatan WHERE jab_nama = '%s'",$_POST["jab_nama"]);
        else
            $sql = sprintf("SELECT jab_id FROM akad_jabatan WHERE jab_nama = '%s' AND jab_id <> '%s'",$_POST["jab_nama"],$_POST["jab_id"]);
            
        $rs_check = $dtaccess->Execute($sql,DB_SCHEMA);
        if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
        else $err_code = clearbit($err_code,2); 
        
        $dtaccess->Clear($rs_check);
         
        //--- End Checking Data ---//

        if ($err_code == 0) {
            $dbTable = "akad_jabatan";
            
            $dbField[0] = "jab_id";   // PK
            $dbField[1] = "jab_nama";

            if(!$jabId) $jabId = $dtaccess->GetNewID("akad_jabatan","jab_id",DB_SCHEMA);
            $dbValue[0] = QuoteValue(DPE_NUMERIC,$jabId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["jab_nama"]);

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
            
            header("location:jab_view.php");
            exit();        
        }
    }

    if ($_POST["btnDelete"]) {
        $jabId = & $_POST["cbDelete"];
        for($i=0,$n=count($jabId);$i<$n;$i++){
            $sql = "delete from akad_jabatan where jab_id = ".$jabId[$i];
            $dtaccess->Execute($sql,DB_SCHEMA);
        }
        header("location:jab_view.php");
        exit();    
    }
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/pmb.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/elements.js"></script>
</head>

<body>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="60%" border="0" cellpadding="1" cellspacing="1">
   <tr>
        <td align="left" colspan=2 class="tblHeader">Master Jabatan</td>
    </tr>
   <tr>
        <td width="30%" align="right" class="tblMainCol"><strong>Nama<?if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
        <td width="70%" class="tblCol"><input type="text" class="inputField" name="jab_nama" size="30" maxlength="50" value="<?php echo $_POST["jab_nama"];?>" onKeyDown="return tabOnEnter_select(this, event);"></td>
    </tr>
    <tr>
        <td colspan="2" align="right" class="tblCol">
            <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Simpan" class="inputField"/>
            <input type="button" name="btnNew" value="Kembali" class="inputField" onClick="document.location.href='jab_view.php'">
        </td>
    </tr>
</table>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="jab_id" value="<?php echo $jabId?>" />
<? } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
</form>
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? } ?>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Jabatan&nbsp;udah&nbsp;ada.</strong></font>
<? } ?>
</body>
</html>
<?
    $dtaccess->Close();
?>
