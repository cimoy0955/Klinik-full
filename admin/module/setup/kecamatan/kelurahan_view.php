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
    if($_GET["id"]){
        $jabEnc = $_GET["id"];
        $propId = $enc->Decode($_GET["id"]);
    }
    if($_GET["idkota"]){        
        $kotaId = $enc->Decode($_GET["idkota"]);
    }


    $editPage = "kelurahan_edit.php?id=".$jabEnc;
    $thisPage = "kelurahan_view.php?id=".$jabEnc;

    //*-- config table ---*//  

    $tabletblHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
    $tabletblHeader[0]["width"] = "1%";
    $tabletblHeader[0]["align"] = "center";

    $tabletblHeader[1]["name"]  = "Edit";
    $tabletblHeader[1]["width"] = "1%";
    $tabletblHeader[1]["align"] = "center";
    
    $tabletblHeader[2]["name"]  = "Nama Kelurahan";
    $tabletblHeader[2]["width"] = "20%";
    $tabletblHeader[2]["align"] = "center"; 

    $tableContent[0]["name"]  = "kel_id";
    $tableContent[0]["wrap"] = "nowrap";
    $tableContent[0]["align"] = "left";

    $tableContent[1]["name"]  = "kel_id";
    $tableContent[1]["wrap"] = "nowrap";
    $tableContent[1]["align"] = "left";

    $tableContent[2]["name"]  = "kel_nama";
    $tableContent[2]["wrap"] = "nowrap";
    $tableContent[2]["align"] = "left";

    $jumContent = count($tabletblHeader);
    $startContent = 2;
    
    $sql = "select * from global.global_kelurahan";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataTable = $dtaccess->FetchAll($rs);
    
    if($propId){
        $sql = "select a.*,b.* from global.global_kelurahan a, global.global_kecamatan b where a.id_kec = b.kec_id and a.id_kec = ".QuoteValue(DPE_NUMERIC,$propId)." order by a.kel_nama";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataTable = $dtaccess->FetchAll($rs);
    }
    $PageHeader = "Nama Kecamatan : ".$dataTable[0]["kec_nama"];
?>
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
</head>

<body>
<table width="60%" border="1" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td><?php echo $PageHeader;?></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
    <table width="60%" border="1" cellpadding="1" cellspacing="1">
    <tr class="subheader">
        <?php for($i=0;$i<$jumContent;$i++){ ?>
            <td width="<?php echo $tabletblHeader[$i]["width"];?>" align="<?php echo $tabletblHeader[$i]["align"];?>"><?php echo $tabletblHeader[$i]["name"];?></td>
        <?php } ?>
    </tr>
    <?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
        <tr class="<?php if($i%2==0) echo "tablecontent";else echo "tablecontent-odd";?>">
            <td align="center"><div align="center"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"></div></td>
           <td align="center"><a href='<?php echo $editPage?>&idkota=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_edit.png" alt="Edit" title="Edit" border="0"></a></td> 
           <?php for($j=$startContent;$j<$jumContent;$j++){ ?>
                <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<?php echo $dataTable[$i][$tableContent[$j]["name"]];?>&nbsp;</td>
            <?php } ?>
        </tr>
    <?php } ?>
    <tr class="tblCol"> 
        <td colspan="<?php echo ($startContent);?>"><div align="center">
            <input type="submit" name="btnDelete" value="Hapus" class="inputField">
        <div></td>
        <td colspan="<?php echo ($jumContent-$startContent);?>"><div align="left">
            <input type="button" name="btnAdd" value="Tambah" class="inputField" onClick="document.location.href='<?php echo $editPage;?>'"> 
			<input type="button" name="btnNew" value="Kembali" class="inputField" onClick="document.location.href='kecamatan_view.php?id=<?php echo $enc->Encode($propId);?>'"/>
        <div></td>
    </tr>
    </table>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<input type="hidden" name="kec_id" value="<?php echo $prop?>" />
<input type="hidden" name="btnDelete" value="<?php $_POST["btnDelete"]?>" />
<? } ?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
</form>
</body>
</html>
