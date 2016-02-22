<?php
    require_once("root.inc.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");

    $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();


    /*if(!$auth->IsAllowed("setup_user",PRIV_READ)){
         die("Maaf, Anda tidak punya hak akses untuk submenu ini");
         exit(1);
    }elseif($auth->IsAllowed("setup_user",PRIV_READ)===1){
         die("Tekan F5, Login kembali");
         exit(1);
    }*/

    $editPage = "jab_edit.php";
    $detPage = "jab_act_view.php";
    $thisPage = "jab_view.php";

    //*-- config table ---*//
    $PageHeader = "Master Jabatan PMB";

    $tabletblHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
    $tabletblHeader[0]["width"] = "1%";
    $tabletblHeader[0]["align"] = "center";
    
    $tabletblHeader[1]["name"]  = "Edit";
    $tabletblHeader[1]["width"] = "1%";
    $tabletblHeader[1]["align"] = "center";

    $tabletblHeader[2]["name"]  = "Detail";
    $tabletblHeader[2]["width"] = "1%";
    $tabletblHeader[2]["align"] = "center";

    $tabletblHeader[3]["name"]  = "Nama Jabatan";
    $tabletblHeader[3]["width"] = "20%";
    $tabletblHeader[3]["align"] = "center";
    

    $tableContent[0]["name"]  = "jab_id";
    $tableContent[0]["wrap"] = "nowrap";
    $tableContent[0]["align"] = "left";

    $tableContent[1]["name"]  = "jab_id";
    $tableContent[1]["wrap"] = "nowrap";
    $tableContent[1]["align"] = "left";

    $tableContent[2]["name"]  = "jab_id";
    $tableContent[2]["wrap"] = "nowrap";
    $tableContent[2]["align"] = "left";

    $tableContent[3]["name"]  = "jab_nama";
    $tableContent[3]["wrap"] = "nowrap";
    $tableContent[3]["align"] = "left";

    $jumContent = count($tabletblHeader);
    $startContent = 3;

    $sql = "select * from akad_jabatan where jab_id <> 0 order by jab_nama";
	//echo $sql;
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs);
?>
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/pmb.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
</head>

<body>
<table width="50%" border="0" cellpadding="0" cellspacing="0">
    <tr class="PageHeader">
        <td><?php echo $PageHeader;?></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
    <table width="50%" border="0" cellpadding="1" cellspacing="1">
    <tr class="tblHeader">
        <?php for($i=0;$i<$jumContent;$i++){ ?>
            <td width="<?php echo $tabletblHeader[$i]["width"];?>" align="<?php echo $tabletblHeader[$i]["align"];?>"><?php echo $tabletblHeader[$i]["name"];?></td>
        <?php } ?>
    </tr>
    <?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
        <tr class="<?php if($i%2==0) echo "tblCol";else echo "tblCol-odd";?>">
            <td align="center"><div align="center"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"></div></td>
            <td align="center"><a href='<?php echo $editPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_edit.png" alt="Edit" title="Edit" border="0"></a></td>
            <td align="center"><a href='<?php echo $detPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_prop.png" alt="Detail" title="Detail" border="0"></a></td>
           <?php for($j=$startContent;$j<$jumContent;$j++){ ?>
                <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<font size="2" style="font-family: courier new,courier,monospace;"><?php echo $dataTable[$i][$tableContent[$j]["name"]];?></font>&nbsp;</td>
            <?php } ?>
        </tr>
    <?php } ?>
    <tr class="tblCol"> 
        <td colspan="<?php echo ($startContent);?>"><div align="center">
            <input type="submit" name="btnDelete" value="Hapus" class="inputField">
        <div></td>
        <td colspan="<?php echo ($jumContent-$startContent);?>"><div align="left">
            <input type="button" name="btnAdd" value="Tambah" class="inputField" onClick="document.location.href='<?php echo $editPage;?>'"> 
        <div></td>
    </tr>
    </table>
</form>
</body>
</html>
