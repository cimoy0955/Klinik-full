<?php
    require_once("root.inc.php");
    require_once($APLICATION_ROOT."library/auth.cls.php");
    require_once($APLICATION_ROOT."library/textEncrypt.cls.php");
    require_once($APLICATION_ROOT."library/datamodel.cls.php");

    $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();
    

    if(!$auth->IsAllowed("setup_action",PRIV_READ)){
         die("Maaf, Anda tidak punya hak akses untuk submenu ini");
         exit(1);
    }elseif($auth->IsAllowed("setup_action",PRIV_READ)===1){
         die("Tekan F5, Login kembali");
         exit(1);
    }

    if($_GET["id"]){
        $jabEnc = $_GET["id"];
        $jabId = $enc->Decode($_GET["id"]);
    }

    $editPage = "jab_act_edit.php?id_jab=".$jabEnc;
    $addPage = "jab_act_add.php?id_jab=".$jabEnc;
    $thisPage = "jab_act_view.php?id=".$jabEnc;
    
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

    $logoPriv["1"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_okay.png" alt="OK" title="OK" border="0">';
    $logoPriv["0"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_nokay.png" alt="NO" title="NO" border="0">';

    if($jabId){
        $sql = "select a.*, b.jab_nama, c.act_tampil from akad_jabatan_action a join akad_jabatan b on a.id_jab = b.jab_id join akad_action c on a.id_act = c.act_id where a.id_jab = ".QuoteValue(DPE_NUMERIC,$jabId)."order by act_tampil";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataTable = $dtaccess->FetchAll($rs);

        $sql = "select * from akad_jabatan where jab_id = ".QuoteValue(DPE_NUMERIC,$jabId);
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataTable1 = $dtaccess->FetchAll($rs);
    }

    $PageHeader = "Action Jabatan : ".$dataTable1[0]["jab_nama"];

?>
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/pmb.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
</head>

<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr class="PageHeader">
        <td><?php echo $PageHeader;?></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr class="tblHeader">
        <?php for($i=0;$i<$jumContent;$i++){ ?>
            <td width="<?php echo $tabletblHeader[$i]["width"];?>" align="<?php echo $tabletblHeader[$i]["align"];?>"><?php echo $tabletblHeader[$i]["name"];?></td>
        <?php } ?>
    </tr>
    <?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
        <tr class="<?php if($i%2==0) echo "tblCol";else echo "tblCol-odd";?>">
            <td align="center"><div align="center"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"></div></td>
            <td align="center"><a href='<?php echo $editPage?>&id=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_edit.png" alt="Edit" title="Edit" border="0"></a></td>
            <?php for($j=$startContent;$j<($jumContent-4);$j++){ ?>
                <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<font size="2" style="font-family: courier new,courier,monospace;"><?php echo $dataTable[$i][$tableContent[$j]["name"]];?></font>&nbsp;</td>
            <?php } ?>
            <?php for($j=($jumContent-4);$j<$jumContent;$j++){ ?>
                <td nowrap align="center">&nbsp;<?php echo $logoPriv[$dataTable[$i]["jab_act_akses"]{$j-3}];?>&nbsp;</td>
            <?php } ?>
        </tr>
    <?php } ?>
    <tr class="tblCol"> 
        <td colspan="<?php echo ($startContent);?>"><div align="center">
            <input type="submit" name="btnDelete" value="Hapus" class="inputField">
        <div></td>
        <td colspan="<?php echo ($jumContent-$startContent);?>"><div align="left">
            <input type="button" name="btnAdd" value="Tambah" class="inputField" onClick="document.location.href='<?php echo $addPage;?>'"> 
			<input type="button" name="btnNew" value="Kembali" class="inputField" onClick="document.location.href='jab_view.php'">
        <div></td>
    </tr>
    </table>
</form>
</body>
</html>
