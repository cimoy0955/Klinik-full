<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");


     $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();

    if(!$auth->IsAllowed("setup_kamar",PRIV_READ)){
        die("access_denied");
        exit(1);
    }



    $editPage = "kecamatan_edit.php";
    $detPage = "kelurahan_view.php";
    $thisPage = "kecamatan_view.php";

    //*-- config table ---*//
    $PageHeader = "Master Kecamatan";

    $tabletblHeader[0]["name"]  = "Nama Kecamatan";
    $tabletblHeader[0]["width"] = "20%";
    $tabletblHeader[0]["align"] = "center";
    
    $tabletblHeader[1]["name"]  = "Detail";
    $tabletblHeader[1]["width"] = "1%";
    $tabletblHeader[1]["align"] = "center";

    $tabletblHeader[2]["name"]  = "Edit";
    $tabletblHeader[2]["width"] = "1%";
    $tabletblHeader[2]["align"] = "center";

    $tabletblHeader[3]["name"]  = "Hapus";
    $tabletblHeader[3]["width"] = "1%";
    $tabletblHeader[3]["align"] = "center";

    $tableContent[0]["name"]  = "kec_id";
    $tableContent[0]["wrap"] = "nowrap";
    $tableContent[0]["align"] = "left";

    $tableContent[1]["name"]  = "kec_id";
    $tableContent[1]["wrap"] = "nowrap";
    $tableContent[1]["align"] = "left";

    $tableContent[2]["name"]  = "kec_id";
    $tableContent[2]["wrap"] = "nowrap";
    $tableContent[2]["align"] = "left";

    $tableContent[3]["name"]  = "kec_nama";
    $tableContent[3]["wrap"] = "nowrap";
    $tableContent[3]["align"] = "center";

    $jumContent = count($tabletblHeader);
    $startContent = 3;

    $sql = "select * from global.global_kecamatan order by kec_nama";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataTable = $dtaccess->FetchAll($rs);
?>
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
<script language="JavaScript">
function Edit()
{
  document.location.href='kecamatan_edit.php';
}

</script>
</head>

<body>
<table width="100%" >
     <tr class="tableheader">
          <td><?php echo $PageHeader;?></td>
     </tr>
</table>
<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
    <table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="subheader">
        <?php for($i=0;$i<$jumContent;$i++){ ?>
            <td width="<?php echo $tabletblHeader[$i]["width"];?>" align="<?php echo $tabletblHeader[$i]["align"];?>"><?php echo $tabletblHeader[$i]["name"];?></td>
        <?php } ?>
    </tr>
    <?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
        <tr class="<?php if($i%2==0) echo "tablecontent";else echo "tablecontent-odd";?>">
                <td <?php echo $tableContent[3]["wrap"];?> align="<?php echo $tableContent[3]["align"];?>">&nbsp;<?php echo $dataTable[$i][$tableContent[3]["name"]];?>&nbsp;</td>
            <td align="center"><a href='<?php echo $detPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[2]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_book.gif" alt="Detail" title="Detail" border="0"></a></td>            
            <td align="center"><a href='<?php echo $editPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_edit.png" alt="Edit" title="Edit" border="0"></a></td>
            <td align="center"><a href='<?php echo $editPage?>?del=1&id=<?php echo $enc->Encode($dataTable[$i][$tableContent[0]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_drop.png" alt="Hapus" title="Hapus" border="0"></a></td>
        </tr>
    <?php } ?>
    <tr> 
        <td colspan="<?php echo ($jumContent);?>"><div align="left">
            <input type="button" name="btnAdd" value="Tambah" class="button" onClick="document.location.href='<?php echo $editPage;?>?parent='">        
        <div></td>
    </tr>
    </table>
</form>
</body>
</html>
