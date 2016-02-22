<?php
    require_once("root.inc.php");
    require_once($APLICATION_ROOT."library/auth.cls.php");
    require_once($APLICATION_ROOT."library/textEncrypt.cls.php");
    require_once($APLICATION_ROOT."library/datamodel.cls.php");

    $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();


    if(!$auth->IsAllowed("setup_user",PRIV_READ)){
         die("Maaf, Anda tidak punya hak akses untuk submenu ini");
         exit(1);
    }elseif($auth->IsAllowed("setup_user",PRIV_READ)===1){
         die("Tekan F5, Login kembali");
         exit(1);
    }
    

    $statusLogin["y"] = "Aktif";
    $statusLogin["n"] = "Non Aktif";

    $editPage = "usr_edit.php";
    $thisPage = "usr_view.php";

    //*-- config table ---*//
    $PageHeader = "User Account";

    $tabletblHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
    $tabletblHeader[0]["width"] = "1%";
    $tabletblHeader[0]["align"] = "center";
    
    $tabletblHeader[1]["name"]  = "";
    $tabletblHeader[1]["width"] = "1%";
    $tabletblHeader[1]["align"] = "center";

    $tabletblHeader[2]["name"]  = "Nama Pengguna";
    $tabletblHeader[2]["width"] = "20%";
    $tabletblHeader[2]["align"] = "center";

    $tabletblHeader[3]["name"]  = "Nama Jabatan";
    $tabletblHeader[3]["width"] = "20%";
    $tabletblHeader[3]["align"] = "center";

    $tabletblHeader[4]["name"]  = "Jurusan";
    $tabletblHeader[4]["width"] = "15%";
    $tabletblHeader[4]["align"] = "center";

    $tabletblHeader[5]["name"]  = "Nama Login";
    $tabletblHeader[5]["width"] = "20%";
    $tabletblHeader[5]["align"] = "center";

    $tabletblHeader[6]["name"]  = "Status Login";
    $tabletblHeader[6]["width"] = "10%";
    $tabletblHeader[6]["align"] = "center";


    $tableContent[0]["name"]  = "usr_id";
    $tableContent[0]["wrap"] = "nowrap";
    $tableContent[0]["align"] = "left";

    $tableContent[1]["name"]  = "usr_id";
    $tableContent[1]["wrap"] = "nowrap";
    $tableContent[1]["align"] = "left";

    $tableContent[2]["name"]  = "usr_nama";
    $tableContent[2]["wrap"] = "nowrap";
    $tableContent[2]["align"] = "left";

    $tableContent[3]["name"]  = "jab_nama";
    $tableContent[3]["wrap"] = "nowrap";
    $tableContent[3]["align"] = "left";

    $tableContent[4]["name"]  = "struk_nama";
    $tableContent[4]["wrap"] = "nowrap";
    $tableContent[4]["align"] = "left";

    $tableContent[5]["name"]  = "usr_loginname";
    $tableContent[5]["wrap"] = "nowrap";
    $tableContent[5]["align"] = "left";

    $tableContent[6]["name"]  = "usr_status";
    $tableContent[6]["wrap"] = "nowrap";
    $tableContent[6]["align"] = "left";

    $jumContent = count($tabletblHeader);
    $startContent = 2;  // -- mulai bener2 listnya --

    $sql = "select a.*, b.jab_nama, c.struk_nama from akad_user a
               left join akad_jabatan b on a.id_jab = b.jab_id
               left join universitas.univ_struktural c on c.struk_id = a.id_struk
               where b.jab_id <> 0 order by jab_nama";
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
            <td align="center"><a href='<?php echo $editPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_edit.png" alt="Edit" title="Edit" border="0"></a></td>
           <?php for($j=$startContent;$j<$jumContent-1;$j++){ ?>
                <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<?php echo $dataTable[$i][$tableContent[$j]["name"]];?>&nbsp;</td>
            <?php } ?>
            <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<?php echo $statusLogin[$dataTable[$i][$tableContent[$j]["name"]]];?>&nbsp;</td>
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
