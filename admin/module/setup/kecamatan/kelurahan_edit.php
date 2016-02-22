<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
    
    
    $dtaccess = new DataAccess();
    $enc = new TextEncrypt();
    $err_code = 0;
    $auth = new CAuth();


    
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
      
	if($_POST["kel_id"])  $kotaId = & $_POST["kel_id"];
    if($_GET["id"]) $propId = & $enc->Decode($_GET["id"]);
    elseif($_POST["id_kec"]) $propId = & $_POST["id_kec"];
    
   if(!$auth->IsAllowed("setup_kamar",PRIV_READ)){
        die("access_denied");
        exit(1);
    }

    if ($_GET["idkota"]) {
        if ($_POST["btnDelete"]) { 
            $_x_mode = "Delete";
        } else { 
            $_x_mode = "Edit";
            $kotaId = $enc->Decode($_GET["idkota"]);
        }
	   
        $sql = "select * from global.global_kelurahan where kel_id=".$kotaId;
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $row_edit = $dtaccess->Fetch($rs_edit);
        $dtaccess->Clear($rs_edit);
        $_POST["kel_nama"] = $row_edit["kel_nama"];
    }

    if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
		if($_POST["btnUpdate"]){
			$kotaId = & $_POST["kel_id"];
			$_x_mode = "Edit";
		}
        
        $err_code = 3;
        //--- Checking Data ---//
        if ($_POST["kel_nama"]) $err_code = clearbit($err_code,1); 
        else $err_code = setbit($err_code,1);

        if ($_POST["btnSave"] ) 
            $sql = sprintf("SELECT kel_id FROM global.global_kelurahan WHERE upper(kel_nama) = '%s' and id_kec = '%s' ",strtoupper($_POST["kel_nama"]),$propId);
        else
            $sql = sprintf("SELECT kel_id FROM global.global_kelurahan WHERE upper(kel_nama) = '%s' AND kel_id <> '%s'",strtoupper($_POST["kel_nama"]),$_POST["kel_id"]);
            
        $rs_check = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
        else $err_code = clearbit($err_code,2); 
        
        $dtaccess->Clear($rs_check);
        //--- End Checking Data ---//

        if ($err_code == 0) {
            $dbTable = "global.global_kelurahan";
            $dbField[0] = "kel_id";   // PK
            $dbField[1] = "kel_nama";
            $dbField[2] = "id_kec";

            /* DPE_NUMERIC,DPE_CHAR,DPE_DATE */

            if(!$kotaId) $kotaId = $dtaccess->GetNewID("global.global_kelurahan","kel_id",DB_SCHEMA_GLOBAL);
            $dbValue[0] = QuoteValue(DPE_NUMERIC,$kotaId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kel_nama"]);
            $dbValue[2] = QuoteValue(DPE_NUMERIC,$propId);
            
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
            header("location:kelurahan_view.php?id=".$enc->Encode($_POST["id_kec"]));
            exit();        
        }
    }

    if ($_POST["btnDelete"]) {
        $kotaId = & $_POST["cbDelete"];
        for($i=0,$n=count($kotaId);$i<$n;$i++){
            $sql = "delete from global.global_kelurahan where kel_id = ".$kotaId[$i];
            $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        }
        header("location:kelurahan_view.php?id=".$enc->Encode($propId));
        exit();    
    }

    $sql = "select kec_nama from global.global_kecamatan where kec_id =".$propId;
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataTable = $dtaccess->FetchAll($rs);
    if(!$_POST["kec_nama"]) $_POST["kec_nama"] = $dataTable[0]["kec_nama"];
    $propnama = $_POST["kec_nama"] ;    
    $PageHeader = "Kecamatan : ".$propnama; 
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
</head>
<body>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="50%" border="1" cellpadding="1" cellspacing="1">
   <tr>
        <td align="left" colspan=2 class="tableheader"><?php echo $PageHeader;?></td>
    </tr>
    <tr>
        <td width="50%" align="left" class="tablecontent"><strong>Nama Kelurahan <?php if(readbit($err_code,1) || readbit($err_code,2) ){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>        
         <td width="500%" class="tablecontent"><input type="text" class="inputField" name="kel_nama" size="30" maxlength="50" value="<?php echo $_POST["kel_nama"];?>"/></td>
    </tr>
    <tr>
        <td colspan="2" align="center" class="">
            <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Simpan" class="inputField"/>
			<input type="button" name="btnNew" value="Kembali" class="inputField" onClick="document.location.href='kelurahan_view.php?id=<?php echo $enc->Encode($propId);?>&idkota=<?php echo $enc->Encode($kotaId);?>'"/>
        </td>
    </tr>
</table>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="kel_id" value="<?php echo $kotaId?>" />
<? } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_kec" value="<?php echo $propId?>" />
</form>
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<br>
<? } ?>
<? if (readbit($err_code,1)) { ?>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Kelurahan tidak boleh Kosong.</strong></font>
<? } ?>
<? if (readbit($err_code,1)) { ?>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Kelurahan tidak boleh Sama.</strong></font>
<? } ?>
</body>
</html>
<?
    $dtaccess->Close();
?>
