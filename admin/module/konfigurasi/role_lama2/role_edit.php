<?php
    require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
    
    $dtaccess = new DataAccess();
    $enc = new TextEncrypt();
    $auth = new CAuth();
    $err_code = 0;
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["rol_id"])  $rolId = & $_POST["rol_id"];
 
    if ($_GET["id"]) {
        if ($_POST["btnDelete"]) { 
            $_x_mode = "Delete";
        } else { 
            $_x_mode = "Edit";
            $rolId = $enc->Decode($_GET["id"]);
        }
        $sql = "select * from global.global_auth_role where rol_id = ".$rolId;
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $_POST["rol_name"] = $row_edit["rol_name"];
    }
    
  if($_x_mode=="New") $privMode = PRIV_CREATE;
    elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
    else $privMode = PRIV_DELETE;

    /*if(!$auth->IsAllowed("setup_user",PRIV_READ)){
          die("access_denied");
         exit(1);
         }*/

    if ($_POST["btnNew"]) {
        header("location: ".$_SERVER["PHP_SELF"]);
        exit();
    }
   
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
		if($_POST["btnUpdate"]){
			$rolId = & $_POST["rol_id"];
			$_x_mode = "Edit";
		}
        
        $err_code = 3;
        //--- Checking Data ---//
        if ($_POST["rol_name"]) $err_code = clearbit($err_code,1); 
        else $err_code = setbit($err_code,1);
        
        if ($_POST["btnSave"]) 
            $sql = sprintf("SELECT rol_id FROM global.global_auth_role WHERE rol_name = '%s'",$_POST["rol_name"]);
        else
            $sql = sprintf("SELECT rol_id FROM global.global_auth_role WHERE rol_name = '%s' AND rol_id <> '%s'",$_POST["rol_name"],$_POST["rol_id"]);
            
        $rs_check = $dtaccess->Execute($sql,DB_SCHEMA);
        if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
        else $err_code = clearbit($err_code,2); 
        
        $dtaccess->Clear($rs_check);
         
        //--- End Checking Data ---//

        if ($err_code == 0) {
            $dbTable = "global.global_auth_role";
            
            $dbField[0] = "rol_id";   // PK
            $dbField[1] = "rol_name";

            if(!$rolId) $rolId = $dtaccess->GetNewID("global.global_auth_role","rol_id",DB_SCHEMA);
            $dbValue[0] = QuoteValue(DPE_NUMERIC,$rolId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["rol_name"]);

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
            
            header("location:role_view.php");
            exit();        
        }
    }
      if ($_GET["del"]) {
          $rolId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_auth_role where rol_id = ".QuoteValue(DPE_CHAR,$rolId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:role_view.php");
          exit();    
     }
     
     /*
    if ($_POST["btnDelete"]) {
        $roleId = & $_POST["cbDelete"];
        for($i=0,$n=count($roleId);$i<$n;$i++){
            $sql = "delete from sek_roleatan where role_id = ".$roleId[$i];
            $dtaccess->Execute($sql,DB_SCHEMA);
        }
        header("location:role_view.php");
        exit();    
    }*/
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>program/fungsi/css/expose.css">
</head>

<body>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="60%" border="0" cellpadding="1" cellspacing="1">
   <tr>
        <td align="left" colspan=2 class="tableheaderatas">Master Jabatan</td>
    </tr>
   <tr>
        <td width="30%" align="right" class="tablecontent-odd"><strong>Nama<?if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
        <td width="70%" class="tablecontent-odd"><input type="text" class="inputField" name="rol_name" size="30" maxlength="50" value="<?php echo $_POST["rol_name"];?>"/></td>
    </tr>
    <tr>
        <td colspan="2" align="left" class="tablecontent">
            <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Simpan" class="inputField"/>
            <input type="submit" name="btnNew" value="Tambah" class="inputField"/>
        </td>
    </tr>
</table>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="rol_id" value="<?php echo $rolId?>" />
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
