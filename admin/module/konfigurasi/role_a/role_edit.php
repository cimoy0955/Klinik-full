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
    
     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
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
          
          $sql = "select * from global_auth_role where rol_id = ".$rolId;
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          
          $dtaccess->Clear($rs_edit);
          $_POST["rol_name"] = $row_edit["rol_name"];
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
               $rolId = & $_POST["rol_id"];
               $_x_mode = "Edit";
		}
        
          $err_code = 3;
          //--- Checking Data ---//
          if ($_POST["rol_name"]) $err_code = clearbit($err_code,1); 
          else $err_code = setbit($err_code,1);
          
          if ($_POST["btnSave"]) 
               $sql = "SELECT rol_id FROM global_auth_role WHERE rol_name = ".QuoteValue(DPE_CHAR,$_POST["rol_name"]);
          else
               $sql = "SELECT rol_id FROM global_auth_role WHERE rol_name = ".QuoteValue(DPE_CHAR,$_POST["rol_name"])." AND rol_id <> ".QuoteValue(DPE_NUMERIC,$_POST["rol_id"]);
              
          $rs_check = $dtaccess->Execute($sql);
          
          if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
          else $err_code = clearbit($err_code,2); 
          
          $dtaccess->Clear($rs_check);
           
          //--- End Checking Data ---//
  
          if ($err_code == 0) {
               $dbTable = "global_auth_role";
               
               $dbField[0] = "rol_id";   // PK
               $dbField[1] = "rol_name";
   
               if(!$rolId) $rolId = $dtaccess->GetNewID("global_auth_role","rol_id",DB_SCHEMA_GLOBAL);
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

    if ($_POST["btnDelete"]) {
        $rolId = & $_POST["cbDelete"];
        for($i=0,$n=count($rolId);$i<$n;$i++){
            $sql = "delete from global_auth_role where rol_id = ".$rolId[$i];
            $dtaccess->Execute($sql);
        }
        header("location:role_view.php");
        exit();    
    }
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/elements.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/func_curr.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
</head>

<body>
<table width="100%" border="1" cellpadding="1" cellspacing="1">
   <tr class="tableheader">
        <td>&nbsp;User Role Edit</td>
    </tr>
</table>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
<tr>
    <td>
    <fieldset>
    <legend><strong>User Role Config</strong></legend>
			<table width="100%" border="1" cellpadding="1" cellspacing="1">
			   <tr>
			        <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Name<?if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?>&nbsp;</strong></td>
			        <td width="70%"><input onKeyDown="return tabOnEnter(this, event);" type="text" name="rol_name" size="50" maxlength="100" value="<?php echo $_POST["rol_name"];?>"/></td>
			    </tr>
			    <tr>
			        <td colspan="2" align="right">
					    <input type="submit" class="button" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="S a v e" class="inputField"/>
						<input type="button" name="btnBack" value="B a c k" class="button" onClick="document.location.href='role_view.php'"/>
			        </td>
			    </tr>
			</table>
  </fieldset>
  </td>
</tr>
</table>

<script>document.frmEdit.rol_name.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="rol_id" value="<?php echo $rolId?>" />
<? } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
</form>
<? if ($err_code != 0) { ?>
<font color="red"><strong>&nbsp;Check The Field With (*)</strong></font>
<? } ?>
<? if (readbit($err_code,1)) { ?>
<br>
<font color="purple"><strong>&nbsp;Hint&nbsp;:&nbsp;Field Must Be Filled.</strong></font>
<? } ?>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="purple"><strong>&nbsp;Hint&nbsp;:&nbsp;Role Name was Exist.</strong></font>
<? } ?>
</body>
</html>
<?
    $dtaccess->Close();
?>
