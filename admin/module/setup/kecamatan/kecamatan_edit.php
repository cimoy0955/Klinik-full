<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");

    
       $tableHeader = "&nbsp;Master Kecamatan";
       
       
    $dtaccess = new DataAccess();
    $enc = new TextEncrypt();
    $err_code = 0;
    $auth = new CAuth();


    
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["kec_id"])  $prop = & $_POST["kec_id"];
 
    if ($_GET["id"]) {
        if ($_POST["btnDelete"]) { 
            $_x_mode = "Delete";
        } else { 
            $_x_mode = "Edit";
            $prop = $enc->Decode($_GET["id"]);
        }
        $sql = "select * from global.global_kecamatan where kec_id=".$prop;
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $_POST["kec_nama"] = $row_edit["kec_nama"];
    }
    
    if($_x_mode=="New") $privMode = PRIV_CREATE;
    elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
    else $privMode = PRIV_DELETE;

    if(!$auth->IsAllowed("setup_kamar",$privMode)){
        die("access_denied");
        exit(1);
    }

    
    if ($_POST["btnNew"]) {
        header("location: ".$_SERVER["PHP_SELF"]);
        exit();
    }
   
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) {

		if($_POST["btnUpdate"]){
			$prop = & $_POST["kec_id"];
			$_x_mode = "Edit";
		}
        
        $err_code = 3;
        //--- Checking Data ---//
        if ($_POST["kec_nama"]) $err_code = clearbit($err_code,1); 
        else $err_code = setbit($err_code,1);
        
        if ($_POST["btnSave"]) 
            $sql = sprintf("SELECT kec_id FROM global.global_kecamatan WHERE upper(kec_nama) = '%s'",strtoupper($_POST["kec_nama"]));
        else
            $sql = sprintf("SELECT kec_id FROM global.global_kecamatan WHERE upper(kec_nama) = '%s' AND kec_id <> '%s'",strtoupper($_POST["kec_nama"]),$_POST["kec_id"]);
            
        $rs_check = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
        else $err_code = clearbit($err_code,2); 
        
        $dtaccess->Clear($rs_check);
         
        //--- End Checking Data ---//

        if ($err_code == 0) {
            $dbTable = "global.global_kecamatan";
            
            $dbField[0] = "kec_id";   // PK
            $dbField[1] = "kec_nama";

            /* DPE_NUMERIC,DPE_CHAR,DPE_DATE */

            if(!$prop) $prop = $dtaccess->GetNewID("global.global_kecamatan","kec_id",DB_SCHEMA_GLOBAL);
            $dbValue[0] = QuoteValue(DPE_NUMERIC,$prop);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kec_nama"]);

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
            
            header("location:kecamatan_view.php");
            exit();        
        }
    }

    if ($_GET["del"]) {
          $prop = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_kecamatan where kec_id = ".QuoteValue(DPE_CHAR,$prop);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:kecamatan_view.php");
          exit();    
     }
     /*
    if ($_POST["btnDelete"]) {
        $prop = & $_POST["cbDelete"];
        for($i=0,$n=count($prop);$i<$n;$i++){
            $sql = "delete from universitas.univ_propinsi where kec_id = ".$prop[$i];
            $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        }
        header("location:propinsi_view.php");
        exit();    
    }*/
  
?>
<script language="javascript" type="text/javascript">

function Kembali()
{
  document.location.href='kecamatan_view.php';
}

</script>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
</head>

<body>
<table width="100%" >
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="60%">
<tr>
     <td>
     <fieldset>
     <legend>Setup Kecamatan</legend>
<table width="45%" border="0" cellpadding="1" cellspacing="1">
   <tr>
        <td width="38%" align="left" class="tablecontent-odd"><strong>Nama Kecamatan <?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
        <td width="62%" class="tablecontent-odd"><input type="text" class="inputField" name="kec_nama" size="30" maxlength="50" value="<?php echo $_POST["kec_nama"];?>"/></td>
    </tr>

    <tr>
        <td colspan="2" align="center" class="tblCol">
            <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Simpan" class="inputField"/>
				<input type="button" name="btnNew" value="Kembali" class="inputField" onClick="document.location.href='kecamatan_view.php' ">
    </tr>
</table>
       </fieldset>
     </td>
</tr>
</table>


<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="kec_id" value="<?php echo $prop?>" />
<? } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
</form>
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? } ?>
<? if (readbit($err_code,1)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Kecamatan tidak boleh Kosong.</strong></font>
<? } ?>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Kecamatan tidak boleh Sama.</strong></font>
<? } ?>
</body>
</html>
<?
    $dtaccess->Close();
?>
