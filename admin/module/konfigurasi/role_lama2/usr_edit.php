<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."program/fungsi/bit.php");
     require_once($ROOT."program/fungsi/login.php");
     require_once($ROOT."program/fungsi/encrypt.php");
     require_once($ROOT."program/fungsi/datamodel.php");
    
    
    $dtaccess = new DataAccess();
    $enc = new Encrypt();
    $auth = new CAuth();
    $err_code = 0;
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["usr_id"])  $usrId = & $_POST["usr_id"];
 
    if ($_GET["id"]) {
        if ($_POST["btnDelete"]) { 
            $_x_mode = "Delete";
        } else { 
            $_x_mode = "Edit";
            $usrId = $enc->Decode($_GET["id"]);
        }
        $sql = "select * from sek_user where usr_id = ".$usrId;
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $_POST["usr_loginname"] = $row_edit["usr_loginname"];
        $_POST["usr_nama"] = $row_edit["usr_nama"];
        $_POST["id_jab"] = $row_edit["id_jab"];
        $_POST["usr_status"] = $row_edit["usr_status"];
    }

    if($_x_mode=="New") $privMode = PRIV_CREATE;
    elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
    else $privMode = PRIV_DELETE;

    if(!$auth->IsAllowed($privFiles[$_SERVER["PHP_SELF"]],$privMode)){
        //die("access_denied");
        //exit(1);
    }

    if ($_POST["btnNew"]) {
        header("location: ".$_SERVER["PHP_SELF"]);
        exit();
    }
   
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
		if($_POST["btnUpdate"]){
			$usrId = & $_POST["usr_id"];
			$_x_mode = "Edit";
		}
        
        $err_code = 31;
        //--- Checking Data ---//
        if ($_POST["usr_loginname"]) $err_code = clearbit($err_code,1); 
        else $err_code = setbit($err_code,1);
        
        if ($_POST["btnSave"]) 
            $sql = sprintf("SELECT usr_id FROM sek_user WHERE upper(usr_loginname) = '%s'",strtoupper($_POST["usr_loginname"]));
        else
            $sql = sprintf("SELECT usr_id FROM sek_user WHERE upper(usr_loginname) = '%s' AND usr_id <> '%s'",strtoupper($_POST["usr_loginname"]),$_POST["usr_id"]);
            
        $rs_check = $dtaccess->Execute($sql,DB_SCHEMA);
        if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
        else $err_code = clearbit($err_code,2); 
        
        $dtaccess->Clear($rs_check);
        
        if ($_POST["usr_nama"]) $err_code = clearbit($err_code,3); 
        else $err_code = setbit($err_code,3);
        
        if ($_POST["btnSave"]) 
            $sql = sprintf("SELECT usr_id FROM sek_user WHERE upper(usr_nama) = '%s'",strtoupper($_POST["usr_nama"]));
        else
            $sql = sprintf("SELECT usr_id FROM sek_user WHERE upper(usr_nama) = '%s' AND usr_id <> '%s'",strtoupper($_POST["usr_nama"]),$_POST["usr_id"]);
            
        $rs_check = $dtaccess->Execute($sql,DB_SCHEMA);
        if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,4);
        else $err_code = clearbit($err_code,4); 
        
        $dtaccess->Clear($rs_check);
          
        if ($_POST["usr_password"] == $_POST["usr_password2"]) $err_code = clearbit($err_code,5); 
        else $err_code = setbit($err_code,5);

        if($_POST["btnSave"]) $_POST["usr_status"] = "y";
        elseif(!$_POST["usr_status"]) $_POST["usr_status"] = "n";
      //--- End Checking Data ---//

        if ($err_code == 0) {
            $dbTable = "sek_user";
            
            $dbField[0] = "usr_id";   // PK
            $dbField[1] = "usr_loginname";
            $dbField[2] = "usr_nama";
            $dbField[3] = "id_jab";
            $dbField[4] = "usr_status";
            if($_POST["is_password"]) $dbField[5] = "usr_password";

            if(!$usrId) $usrId = $dtaccess->GetNewID("sek_user","usr_id",DB_SCHEMA);
            $dbValue[0] = QuoteValue(DPE_NUMERIC,$usrId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["usr_loginname"]);
            $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["usr_nama"]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_jab"]);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["usr_status"]);
            if($_POST["is_password"]) $dbValue[5] = QuoteValue(DPE_CHAR,md5($_POST["usr_password"]));

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
            
            header("location:usr_view.php");
            exit();        
        }
    }
         if ($_GET["del"]) {
          $usrId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from sek_user where usr_id = ".QuoteValue(DPE_CHAR,$usrId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:usr_view.php");
          exit();    
     }

    /*if ($_POST["btnDelete"]) {
        $usrId = & $_POST["cbDelete"];
        for($i=0,$n=count($usrId);$i<$n;$i++){
            $sql = "delete from sek_user where usr_id = ".$usrId[$i];
            $dtaccess->Execute($sql,DB_SCHEMA);
        }

        header("location:usr_view.php");
        exit();    
    }*/

    $sql = "select * from sek_jabatan where jab_id <> 0";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataJabatan = $dtaccess->FetchAll($rs);
?>

<script language="javascript" type="text/javascript">

function Kembali()
{
  document.location.href='usr_view.php';
}

</script>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>program/fungsi/css/expose.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/elements.js"></script>

<script language="Javascript">
function GantiPassword(frm, elm)
{
    if(elm.checked){
        frm.usr_password.disabled = false;
        frm.usr_password2.disabled = false;
        frm.usr_password2.style.backgroundColor = '#E6EDFB';
        frm.usr_password.style.backgroundColor = '#E6EDFB';
        frm.usr_password.focus();
    } else {
        frm.usr_password.disabled = true;
        frm.usr_password2.disabled = true;
        frm.usr_password2.style.backgroundColor = '#d9d9d9';
        frm.usr_password.style.backgroundColor = '#d9d9d9';
    }
}
</script>

<style type="text/css">
.passDisable{
	color: #0F2F13;
	border: 1px solid #3772E8;
	background-color: #d9d9d9;
}
</style>
</head>

<body>
<table width="100%" >
     <tr class="tableheaderatas">
          <td><?php echo $tableHeader;?></td>
     </tr>
      <tr>
        <td align="left" colspan=2 class="tableheaderatas">Master Pengguna</td>
    </tr>
</table>
<table width="100%">
     <tr class="tableheaderatas">
          <td align="center"><img src="<?php echo $ROOT;?>program/gambar/kembali.png" style="cursor:pointer"; onCLick="javascript:Kembali();"></td>
     </tr>
</table>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%">
<tr>
     <td>
     <fieldset>
     <legend><img src="<?php echo $ROOT;?>program/gambar/manage_login.png"></legend>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="60%" border="0" cellpadding="1" cellspacing="1">
  
    <tr>
        <td width="35%" align="right" class="tablecontent-odd"><strong>Nama Jabatan</strong></td>
        <td  width="65%" class="tablecontent-odd">
            <select class="inputField" name="id_jab">
                <?php for($i=0,$n=count($dataJabatan);$i<$n;$i++){ ?>
                    <option class="inputField" value="<?php echo $dataJabatan[$i]["jab_id"];?>" <?php if($dataJabatan[$i]["jab_id"]==$_POST["id_jab"]) echo "selected";?>><?php echo $dataJabatan[$i]["jab_nama"];?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
   <tr>
        <td width="35%" align="right" class="tablecontent"><strong>Nama Pengguna<?if(readbit($err_code,4) || readbit($err_code,3)){?>&nbsp;<font color="blue">(*)</font><?}?></strong></td>
        <td width="65%" class="tablecontent"><input type="text" class="inputField" name="usr_nama" size="30" maxlength="50" value="<?php echo $_POST["usr_nama"];?>"/></td>
    </tr>
   <tr>
        <td width="35%" align="right" class="tablecontent-odd"><strong>Nama Login<?if(readbit($err_code,2)){?>&nbsp;<font color="green">(*)</font><?}?><?if(readbit($err_code,1) ){?>&nbsp;<font color="green">(*)</font><?}?></strong></td>
        <td width="65%" class="tablecontent-odd"><input type="text" class="inputField" name="usr_loginname" size="30" maxlength="50" value="<?php echo $_POST["usr_loginname"];?>"/></td>
   </tr>
   <tr>
        <td width="35%" align="right" class="tablecontent"><strong>Password</strong></td>
        <td width="65%" class="tablecontent">
            <input type="password" class="<?php if($_x_mode == "Edit") echo "passDisable";else echo "inputField";?>" name="usr_password" size="30" maxlength="50" <?php if($_x_mode == "Edit"){ ?>disabled<?php } ?>/>
            <?php if($_x_mode == "Edit"){ ?>
            <input type="checkbox" class="inputField" name="is_password" id="is_password" onClick="GantiPassword(this.form,this);"/><label for="is_password">Ganti Password</label>
            <?php }elseif($_x_mode == "New"){ ?>
            <input type="hidden" name="is_password" id="is_password" value="y">
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td width="35%" align="right" class="tablecontent-odd"><strong>Ulangi Password<?if(readbit($err_code,5)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
        <td width="65%" class="tablecontent-odd"><input type="password" class="<?php if($_x_mode == "Edit") echo "passDisable";else echo "inputField";?>" name="usr_password2" size="30" maxlength="50" <?php if($_x_mode == "Edit"){ ?>disabled<?php } ?>/></td>
    </tr>
    <?php if($_x_mode == "Edit"){ ?>
    <tr>
        <td width="35%" align="right" class="tablecontent"><strong>Status</strong></td>
        <td width="65%" class="tablecontent">
            <input type="checkbox" class="inputField" name="usr_status" id="usr_status" value="y" <?php if($_POST["usr_status"]=="y") echo "checked";?>/><label for="usr_status">Aktif</label>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td colspan="2" align="center" class="">
            <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Simpan" class="inputField"/>
            
        </td>
    </tr>
</table>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="usr_id" value="<?php echo $usrId?>" />
<? } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
</form>
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? } ?>
<? if (readbit($err_code,4)) { ?>
<br>
<font color="blue"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Pengguna&nbsp;udah&nbsp;ada.</strong></font>
<? } ?>
<? if (readbit($err_code,3)) { ?>
<br>
<font color="blue"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Pengguna tidak boleh Kosong.</strong></font>
<? } ?>
<? if (readbit($err_code,1)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Login tidak boleh Kosong.</strong></font>
<? } ?>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Login&nbsp;udah&nbsp;ada.</strong></font>
<? } ?>

<? if (readbit($err_code,5)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Password Missmatch</strong></font>
<? } ?>
</body>
</html>
<?
    $dtaccess->Close();
?>
