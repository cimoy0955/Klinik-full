<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."program/fungsi/login.php");
     require_once($ROOT."program/fungsi/encrypt.php");
     require_once($ROOT."program/fungsi/datamodel.php");

    $enc = new Encrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();

    if(!$auth->IsAllowed($privFiles[$_SERVER["PHP_SELF"]],PRIV_READ)){
        //die("access_denied");
        //exit(1);
    }

    $statusLogin["y"] = "Aktif";
    $statusLogin["n"] = "Non Aktif";

    $editPage = "usr_edit.php";
    $thisPage = "usr_view.php";

    //*-- config table ---*//
    $PageHeader = "User Account";

    $tabletblHeader[0]["name"]  = "Nama Pengguna";
    $tabletblHeader[0]["width"] = "20%";
    $tabletblHeader[0]["align"] = "center";
    
    $tabletblHeader[1]["name"]  = "Nama Jabatan";
    $tabletblHeader[1]["width"] = "20%";
    $tabletblHeader[1]["align"] = "center";

    $tabletblHeader[2]["name"]  = "Nama Login";
    $tabletblHeader[2]["width"] = "20%";
    $tabletblHeader[2]["align"] = "center";

    $tabletblHeader[3]["name"]  = "Status Login";
    $tabletblHeader[3]["width"] = "20%";
    $tabletblHeader[3]["align"] = "center";

    $tabletblHeader[4]["name"]  = "Edit";
    $tabletblHeader[4]["width"] = "5%";
    $tabletblHeader[4]["align"] = "center";

    $tabletblHeader[5]["name"]  = "Hapus";
    $tabletblHeader[5]["width"] = "10%";
    $tabletblHeader[5]["align"] = "center";


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

    $tableContent[4]["name"]  = "usr_loginname";
    $tableContent[4]["wrap"] = "nowrap";
    $tableContent[4]["align"] = "left";

    $tableContent[5]["name"]  = "usr_status";
    $tableContent[5]["wrap"] = "nowrap";
    $tableContent[5]["align"] = "left";

    $jumContent = count($tabletblHeader);
    $startContent = 2;  // -- mulai bener2 listnya --

    $sql = "select a.*, b.jab_nama from sek_user a left join sek_jabatan b on a.id_jab = b.jab_id where a.usr_id <> 0";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs);
?>
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>program/fungsi/css/expose.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
<script language="JavaScript">
function Edit()
{
  document.location.href='usr_edit.php';
}

</script>
</head>

<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr class="tableheaderatas">
        <td><?php echo $PageHeader;?></td>
    </tr>
</table>
<table width="100%">
     <tr class="tableheaderatas">
          <td align="center"><img src="<?php echo $ROOT;?>program/gambar/tambah_1.png" style="cursor:pointer"; onCLick="javascript:Edit();"></td>
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
            
           
            <?php for($j=$startContent;$j<$jumContent-1;$j++){ ?>
            <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<?php echo $dataTable[$i][$tableContent[$j]["name"]];?>&nbsp;</td>
            <?php } ?>
            <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<?php echo $statusLogin[$dataTable[$i][$tableContent[$j]["name"]]];?>&nbsp;</td>
            <td align="center"><a href='<?php echo $editPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="25" height="25" src="<?php echo $APLICATION_ROOT;?>gambar/edit.png" alt="Edit" title="Edit" border="0"></a></td>
            <td align="center"><a href='<?php echo $editPage?>?del=1&id=<?php echo $enc->Encode($dataTable[$i][$tableContent[0]["name"]]);?>'><img hspace="2" width="25" height="25" src="<?php echo $APLICATION_ROOT;?>gambar/hapus.png" alt="Hapus" title="Hapus" border="0"></a></td>
        </tr>
    <?php } ?>
   
    </table>
</form>
</body>
</html>
