<?php
    require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");

    $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();
    
   /* if(!$auth->IsAllowed("setup_user",PRIV_READ)){
          die("access_denied");
         exit(1);
         }*/

    if($_GET["id"]){
        $rolEnc = $_GET["id"];
        $rolId = $_GET["id"];
    }

    $editPage = "role_act_edit.php?id_rol=".$rolEnc;
    $addPage = "role_act_add.php?id_rol=".$rolEnc;
    $thisPage = "role_act_view.php?id=".$rolEnc;
    
    //*-- config table ---*//

    $tabletblHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
    $tabletblHeader[0]["width"] = "1%";
    $tabletblHeader[0]["align"] = "center";
    
    $tabletblHeader[1]["name"]  = "";
    $tabletblHeader[1]["width"] = "1%";
    $tabletblHeader[1]["align"] = "center";

    $tabletblHeader[2]["name"]  = "Tampil";
    $tabletblHeader[2]["width"] = "20%";
    $tabletblHeader[2]["align"] = "center";

    $tabletblHeader[3]["name"]  = "Create";
    $tabletblHeader[3]["width"] = "10%";
    $tabletblHeader[3]["align"] = "center";

    $tabletblHeader[4]["name"]  = "Read";
    $tabletblHeader[4]["width"] = "10%";
    $tabletblHeader[4]["align"] = "center";

    $tabletblHeader[5]["name"]  = "Update";
    $tabletblHeader[5]["width"] = "10%";
    $tabletblHeader[5]["align"] = "center";

    $tabletblHeader[6]["name"]  = "Delete";
    $tabletblHeader[6]["width"] = "10%";
    $tabletblHeader[6]["align"] = "center";


    $tableContent[0]["name"]  = "id_act";
    $tableContent[0]["wrap"] = "nowrap";
    $tableContent[0]["align"] = "left";

    $tableContent[1]["name"]  = "id_act";
    $tableContent[1]["wrap"] = "nowrap";
    $tableContent[1]["align"] = "left";

    $tableContent[2]["name"]  = "act_tampil";
    $tableContent[2]["wrap"] = "nowrap";
    $tableContent[2]["align"] = "left";

    $jumContent = count($tabletblHeader);
    $startContent = 2;  // -- mulai bener2 listnya --

    $logoPriv["1"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'gambar/s_okay.png" alt="OK" title="OK" border="0">';
    $logoPriv["0"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'gambar/s_nokay.png" alt="NO" title="NO" border="0">';

    if($rolId){
        $sql = "select a.*, b.rol_nama, c.act_tampil from sek_rolatan_action a 
        join sek_rolatan b on a.id_rol = b.rol_id 
        join sek_action c on a.id_act = c.act_id where a.id_rol = ".QuoteValue(DPE_NUMERIC,$rolId);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataTable = $dtaccess->FetchAll($rs);

        $sql = "select * from sek_rolatan where rol_id = ".QuoteValue(DPE_NUMERIC,$rolId);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataTable1 = $dtaccess->FetchAll($rs);
    }

    $PageHeader = "Action rolatan : ".$dataTable1[0]["rol_nama"];

?>
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>program/fungsi/css/expose.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>program/fungsi/script/ew.js"></script>
</head>

<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr class="tableheaderatas">
        <td><?php echo $PageHeader;?></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr class="subheader">
        <?php for($i=0;$i<$jumContent;$i++){ ?>
            <td width="<?php echo $tabletblHeader[$i]["width"];?>" align="<?php echo $tabletblHeader[$i]["align"];?>"><?php echo $tabletblHeader[$i]["name"];?></td>
        <?php } ?>
    </tr>
    <?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
       <tr class="<?php if($i%2==0) echo "tablecontent";else echo "tablecontent-odd";?>">
            <td align="center"><div align="center"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"></div></td>
            <td align="center"><a href='<?php echo $editPage?>&id=<?php echo $dataTable[$i][$tableContent[1]["name"]];?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>gambar/b_edit.png" alt="Edit" title="Edit" border="0"></a></td>
            <?php for($j=$startContent;$j<($jumContent-4);$j++){ ?>
                <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<?php echo $dataTable[$i][$tableContent[$j]["name"]];?>&nbsp;</td>
            <?php } ?>
            <?php for($j=($jumContent-4);$j<$jumContent;$j++){ ?>
                <td nowrap align="center">&nbsp;<?php echo $logoPriv[$dataTable[$i]["rol_act_akses"]{$j-3}];?>&nbsp;</td>
            <?php } ?>
        </tr>
    <?php } ?>
    <tr class="tblCol"> 
        <td colspan="<?php echo ($startContent);?>"><div align="center">
            <input type="submit" name="btnDelete" value="Hapus" class="inputField">
        <div></td>
        <td colspan="<?php echo ($jumContent-$startContent);?>"><div align="left">
            <input type="button" name="btnAdd" value="Tambah" class="inputField" onClick="document.location.href='<?php echo $addPage;?>'"> 
        <div></td>
    </tr>
    </table>
</form>
</body>
</html>
