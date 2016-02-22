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

	$priv["1"] = "checked";
	$priv["0"] = "";
    
    if($_GET["id_jab"]) $_POST["id_jab"] = $enc->Decode($_GET["id_jab"]);

    if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";

    if($_x_mode=="Edit") $privMode = PRIV_UPDATE;
    else $privMode = PRIV_DELETE;

    if(!$auth->IsAllowed("setup_action",$privMode)){
        die("access_denied");
        exit(1);
    }

    $addPage = "jab_act_add.php?id_jab=".$enc->Encode($_POST["id_jab"]);

    if ($_GET["id"]) {
        if ($_POST["btnDelete"]) { 
            $_x_mode = "Delete";
        } else { 
            $_x_mode = "Edit";
            $_POST["id_act"] = $enc->Decode($_GET["id"]);
        }
        $sql = "select a.*,b.jab_nama, c.act_tampil from akad_jabatan_action a join akad_jabatan b on a.id_jab = b.jab_id join akad_action c on a.id_act = c.act_id where a.id_act = ".$_POST["id_act"]." and a.id_jab = ".$_POST["id_jab"];
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $_POST["jab_act_akses"] = $row_edit["jab_act_akses"];
        $jabNama = $row_edit["jab_nama"];
        $actNama = $row_edit["act_tampil"];
        $privCreate = $_POST["jab_act_akses"]{PRIV_CREATE};
        $privRead = $_POST["jab_act_akses"]{PRIV_READ};
        $privUpdate = $_POST["jab_act_akses"]{PRIV_UPDATE};
        $privDelete = $_POST["jab_act_akses"]{PRIV_DELETE};
    }
    
    if ($_POST["btnNew"]) {
        header("location: ".$addPage);
        exit();
    }
   
    if ($_POST["btnUpdate"]) {
		if($_POST["btnUpdate"]){
			$jabActId = & $_POST["jab_act_id"];
			$_x_mode = "Edit";
		}
        
        $err_code = 0;
        //--- Checking Data ---//
        //--- End Checking Data ---//

        if ($err_code == 0) {
            if($_POST["jab_create"]) $actAkses = "1";
            else $actAkses = "0";
            if($_POST["jab_read"]) $actAkses .= "1";
            else $actAkses .= "0";
            if($_POST["jab_update"]) $actAkses .= "1";
            else $actAkses .= "0";
            if($_POST["jab_delete"]) $actAkses .= "1";
            else $actAkses .= "0";
            
            $dbTable = "akad_jabatan_action";
            
            $dbField[0] = "id_jab";   // PK
            $dbField[1] = "id_act";
            $dbField[2] = "jab_act_akses";

            $dbValue[0] = QuoteValue(DPE_NUMERIC,$_POST["id_jab"]);
            $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["id_act"]);
            $dbValue[2] = QuoteValue(DPE_CHAR,$actAkses);

            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dbKey[1] = 1; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

            $dtmodel->Update() or die("update  error");	

            unset($dtmodel);
            unset($dbField);
            unset($dbValue);
            unset($dbKey);
            
            header("location:jab_act_view.php?id=".$enc->Encode($_POST["id_jab"]));
            exit();        
        }
    }

    if ($_POST["btnDelete"]) {
        $actId = & $_POST["cbDelete"];
        for($i=0,$n=count($actId);$i<$n;$i++){
            $sql = "delete from akad_jabatan_action where id_act = ".$actId[$i]." and id_jab = ".$_POST["id_jab"];
            $dtaccess->Execute($sql,DB_SCHEMA);
        }
        header("location:jab_act_view.php?id=".$enc->Encode($_POST["id_jab"]));
        exit();    
    }

    $PageHeader = "Action Jabatan : ".$jabNama;

?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/pmb.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
</head>

<body>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="60%" border="0" cellpadding="1" cellspacing="1">
   <tr>
        <td align="left" colspan=2 class="tblHeader">Jabatan <?php echo $jabNama." - ".$actNama;?></td>
    </tr>
    <tr>
        <td width="25%" class="tblMainCol">Akses Mode</td>
        <td class="tblCol">
            <input type="checkbox" name="jab_create" id="jab_create"  class="inputField" value="1" <?php echo $priv[$privCreate];?>><label for="jab_create">CREATE</label><br>
            <input type="checkbox" name="jab_read" id="jab_read" class="inputField" value="1" <?php echo $priv[$privRead];?>><label for="jab_read">READ</label><br>
            <input type="checkbox" name="jab_update" id="jab_update" class="inputField" value="1" <?php echo $priv[$privUpdate];?>><label for="jab_update">UPDATE</label><br>
            <input type="checkbox" name="jab_delete" id="jab_delete" class="inputField" value="1" <?php echo $priv[$privDelete];?>><label for="jab_delete">DELETE</label><br>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="right" class="tblCol">
            <input type="submit" name="btnUpdate" value="Simpan" class="inputField"/>
        </td>
    </tr>
</table>

<input type="hidden" name="id_act" value="<?php echo $_POST["id_act"];?>" />
<input type="hidden" name="id_jab" value="<?php echo $_POST["id_jab"];?>" />
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
</form>
</body>
</html>
<?
    $dtaccess->Close();
?>
