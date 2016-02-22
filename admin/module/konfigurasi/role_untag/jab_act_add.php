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

    if(!$auth->IsAllowed("setup_action",PRIV_CREATE)){
        die("access_denied");
        exit(1);
    }

	$priv["1"] = "checked";
	$priv["0"] = "";
    
    if($_GET["id_jab"]) $_POST["id_jab"] = $enc->Decode($_GET["id_jab"]);
    $addPage = "jab_act_add.php?id_jab=".$enc->Encode($_POST["id_jab"]);
	
    $tabletblHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
    $tabletblHeader[0]["width"] = "1%";
    $tabletblHeader[0]["align"] = "center";
    
    $tabletblHeader[1]["name"]  = "Tampil";
    $tabletblHeader[1]["width"] = "20%";
    $tabletblHeader[1]["align"] = "center";

    $tabletblHeader[2]["name"]  = "Create";
    $tabletblHeader[2]["width"] = "10%";
    $tabletblHeader[2]["align"] = "center";

    $tabletblHeader[3]["name"]  = "Read";
    $tabletblHeader[3]["width"] = "10%";
    $tabletblHeader[3]["align"] = "center";

    $tabletblHeader[4]["name"]  = "Update";
    $tabletblHeader[4]["width"] = "10%";
    $tabletblHeader[4]["align"] = "center";

    $tabletblHeader[5]["name"]  = "Delete";
    $tabletblHeader[5]["width"] = "10%";
    $tabletblHeader[5]["align"] = "center";


    $tableContent[0]["name"]  = "act_id";
    $tableContent[0]["wrap"] = "nowrap";
    $tableContent[0]["align"] = "left";

    $tableContent[1]["name"]  = "act_tampil";
    $tableContent[1]["wrap"] = "nowrap";
    $tableContent[1]["align"] = "left";

    $jumContent = count($tabletblHeader);
    $startContent = 1;  // -- mulai bener2 listnya --

    $sql = "select a.* from akad_action a 
            left join akad_jabatan_action b 
            on a.act_id = b.id_act 
            and b.id_jab = ".$_POST["id_jab"]." 
            where b.id_act is null";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs);

    $sql = "select jab_nama from akad_jabatan where jab_id = ".$_POST["id_jab"];
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataJabatan = $dtaccess->Fetch($rs);

    if ($_POST["btnSave"]) {
        
        $err_code = 0;

        if ($err_code == 0) {
            $dbTable = "akad_jabatan_action";
            
            $dbField[0] = "id_jab";   // PK
            $dbField[1] = "id_act";
            $dbField[2] = "jab_act_akses";

            $actId = & $_POST["cbDelete"];
            for($i=0,$n=count($actId);$i<$n;$i++){
                if($_POST["jab_create_".$actId[$i]]) $actAkses = "1";
                else $actAkses = "0";
                if($_POST["jab_read_".$actId[$i]]) $actAkses .= "1";
                else $actAkses .= "0";
                if($_POST["jab_update_".$actId[$i]]) $actAkses .= "1";
                else $actAkses .= "0";
                if($_POST["jab_delete_".$actId[$i]]) $actAkses .= "1";
                else $actAkses .= "0";
            
                $dbValue[0] = QuoteValue(DPE_NUMERIC,$_POST["id_jab"]);
                $dbValue[1] = QuoteValue(DPE_CHAR,$actId[$i]);
                $dbValue[2] = QuoteValue(DPE_CHAR,$actAkses);

                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dbKey[1] = 1; // -- set key buat clause wherenya , valuenya = index array buat field / value

                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                $dtmodel->Insert() or die("insert  error");	

                unset($dtmodel);
                unset($dbValue);
                unset($dbKey);
                unset($actAkses);
            }

            header("location:jab_act_view.php?id=".$enc->Encode($_POST["id_jab"]));
            exit();        
        }
    }
    $PageHeader = "Action Jabatan : ".$dataJabatan["jab_nama"];

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


<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr class="PageHeader">
    <td colspan="<?php echo $jumContent;?>"><?php echo $PageHeader;?></td>
</tr>
<tr class="tblHeader">
    <?php for($i=0;$i<$jumContent;$i++){ ?>
        <td width="<?php echo $tabletblHeader[$i]["width"];?>" align="<?php echo $tabletblHeader[$i]["align"];?>"><?php echo $tabletblHeader[$i]["name"];?></td>
    <?php } ?>
</tr>
<?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
    <tr class="<?php if($i%2==0) echo "tblCol";else echo "tblCol-odd";?>">
        <td align="center"><div align="center"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"></div></td>
        <?php for($j=$startContent;$j<($jumContent-4);$j++){ ?>
            <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<font size="2" style="font-family: courier new,courier,monospace;"><?php echo $dataTable[$i][$tableContent[$j]["name"]];?></font>&nbsp;</td>
        <?php } ?>
        <td nowrap align="center">&nbsp;<input type="checkbox" name="jab_create_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
        <td nowrap align="center">&nbsp;<input type="checkbox" name="jab_read_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
        <td nowrap align="center">&nbsp;<input type="checkbox" name="jab_update_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
        <td nowrap align="center">&nbsp;<input type="checkbox" name="jab_delete_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
    </tr>
<?php } ?>
<tr class="tblCol"> 
    <td colspan="<?php echo ($jumContent);?>"><div align="left">
        <input type="submit" name="btnSave" value="Simpan" class="inputField"> 
		<input type="button" name="btnNew" value="Kembali" class="inputField" onClick="document.location.href='jab_act_view.php?id=<?php echo $enc->Encode($_POST["id_jab"]);?>'">
    <div></td>
</tr>
</table>
<input type="hidden" name="id_jab" value="<?php echo $_POST["id_jab"];?>" />
</form>

</body>
</html>
<?
    $dtaccess->Close();
?>
