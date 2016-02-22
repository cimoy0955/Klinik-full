<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     
     
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     
     $priv["1"] = "checked";
     $priv["0"] = "";
     
     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     if($_GET["id_rol"]) {
          $_POST["id_rol"] = $_GET["id_rol"];
          $idRolEnc = $_GET["id_rol"];
     }
     
     if ($_POST["id_rol_enc"]) $idRolEnc = $_POST["id_rol_enc"]; 
     
     $backPage = "role_act_view.php?id=".$idRolEnc;
     
     if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
     else $_x_mode = "New";
     
     if($_x_mode=="Edit") $privMode = PRIV_UPDATE;
     else $privMode = PRIV_DELETE;    
     
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["id_priv"] = $_GET["id"];
          }
          
          $sql = "select a.*,b.rol_name, c.priv_name from global_auth_role_priv a
                    join global_auth_role b on a.id_rol = b.rol_id
                    join global_auth_privilege c on a.id_priv = c.priv_id
                    where a.id_priv = ".$_POST["id_priv"]." and a.id_rol = ".$_POST["id_rol"];
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["rol_priv_access"] = $row_edit["rol_priv_access"];
          $jabNama = $row_edit["rol_name"];
          $privNama = $row_edit["priv_name"];
          $privCreate = $_POST["rol_priv_access"]{PRIV_CREATE};
          $privRead = $_POST["rol_priv_access"]{PRIV_READ};
          $privUpdate = $_POST["rol_priv_access"]{PRIV_UPDATE};
          $privDelete = $_POST["rol_priv_access"]{PRIV_DELETE};
     }
     
     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
     if ($_POST["btnUpdate"]) {
          if($_POST["btnUpdate"]){
               $jabprivId = & $_POST["id"];
               $_x_mode = "Edit";
          }             
     
          if ($err_code == 0) {
               
               if($_POST["rol_create"]) $privAkses = "1";
               else $privAkses = "0";
               
               if($_POST["rol_read"]) $privAkses .= "1";
               else $privAkses .= "0";
               
               if($_POST["rol_update"]) $privAkses .= "1";
               else $privAkses .= "0";
               
               if($_POST["rol_delete"]) $privAkses .= "1";
               else $privAkses .= "0";
         
               $dbTable = "global_auth_role_priv";
               
               $dbField[0] = "id_rol";   // PK
               $dbField[1] = "id_priv";
               $dbField[2] = "rol_priv_access";
         
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$_POST["id_rol"]);
               $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["id_priv"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$privAkses);
         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dbKey[1] = 1; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
         
               $dtmodel->Update() or die("update  error");	
         
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               header("location:role_act_view.php?id=".$_POST["id_rol"]);
               exit();        
          }
     }
     
     if ($_POST["btnDelete"]) {
          $privId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($privId);$i<$n;$i++){
               $sql = "delete from global_auth_role_priv where id_priv = ".$privId[$i]." and id_rol = ".$_POST["id_rol"];
               $dtaccess->Execute($sql);
          }
          
          header("location:role_act_view.php?id=".$_POST["id_rol"]);
          exit();    
     }
     
     $PageHeader = $jabNama." - ".$privNama;

?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/elements.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/func_curr.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
</head>

<body>
<table width="100%" border="1" cellpadding="1" cellspacing="1">
	<tr class="tableheader">
        <td align="left" colspan=2 class="tblHeader"><?php echo $PageHeader;?></td>
    </tr>
</table>

<form name="frmEdit" method="POST" privion="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
<tr>
    <td>
    <fieldset>
    <legend><strong>Privilage&nbsp;Role&nbsp;Setup</strong></legend>
		<table width="100%" border="1" cellpadding="1" cellspacing="1">
			<tr>
				<td width="30%" class="tablecontent" align="right">Access Mode&nbsp;</td>
				<td>
					<input onKeyDown="return tabOnEnter_select_with_button(this, event);" type="checkbox" name="rol_create" id="rol_create"  class="inputField" value="1" <?php echo $priv[$privCreate];?>><label for="rol_create">CREATE</label><br>
					<input onKeyDown="return tabOnEnter_select_with_button(this, event);" type="checkbox" name="rol_read" id="rol_read" class="inputField" value="1" <?php echo $priv[$privRead];?>><label for="rol_read">READ</label><br>
					<input onKeyDown="return tabOnEnter_select_with_button(this, event);" type="checkbox" name="rol_update" id="rol_update" class="inputField" value="1" <?php echo $priv[$privUpdate];?>><label for="rol_update">UPDATE</label><br>
					<input onKeyDown="return tabOnEnter_select_with_button(this, event);" type="checkbox" name="rol_delete" id="rol_delete" class="inputField" value="1" <?php echo $priv[$privDelete];?>><label for="rol_delete">DELETE</label><br>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="right" class="tblCol">
					<input type="submit" class="button" name="btnUpdate" value="S a v e" class="inputField"/>
					<input type="button" name="btnBack" value="B a c k" class="button" onClick="document.location.href='<?php echo $backPage;?>'"/>
				</td>
			</tr>
		</table>
</fieldset>
  </td>
</tr>
</table>

<script>document.frmEdit.rol_create.focus();</script>

<input type="hidden" name="id_priv" value="<?php echo $_POST["id_priv"];?>" />
<input type="hidden" name="id_rol" value="<?php echo $_POST["id_rol"];?>" />
<input type="hidden" name="id_rol_enc" value="<?php echo $idRolEnc;?>" />
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
</form>
</body>
</html>
<?
    $dtaccess->Close();
?>
