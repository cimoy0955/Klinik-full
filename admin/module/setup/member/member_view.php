<?php
    require_once("root.inc.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");

    $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();

    $editPage = "member_edit.php";
    $detailPage = "member_edit_det.php";
    $thisPage = "member_view.php";

    $logoPriv["y"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_okay.png" alt="OK" title="OK" border="0">';
    $logoPriv["n"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_nokay.png" alt="NO" title="NO" border="0">';

    //*-- config table ---*//
    $tableHeader = "Member";

    $tableSubHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
    $tableSubHeader[0]["width"] = "1%";
    $tableSubHeader[0]["align"] = "center";
    
    $tableSubHeader[1]["name"]  = "Edit";
    $tableSubHeader[1]["width"] = "1%";
    $tableSubHeader[1]["align"] = "center";

	  $tableSubHeader[2]["name"]  = "No";
    $tableSubHeader[2]["width"] = "1%";
    $tableSubHeader[2]["align"] = "center";

    $tableSubHeader[3]["name"]  = "Nama";
    $tableSubHeader[3]["width"] = "20%";
    $tableSubHeader[3]["align"] = "center";
    
    $tableSubHeader[4]["name"]  = "User Name";
    $tableSubHeader[4]["width"] = "20%";
    $tableSubHeader[4]["align"] = "center";

	  $tableSubHeader[5]["name"]  = "Alamat";
    $tableSubHeader[5]["width"] = "20%";
    $tableSubHeader[5]["align"] = "center";

	  $tableSubHeader[6]["name"]  = "Telp";
    $tableSubHeader[6]["width"] = "20%";
    $tableSubHeader[6]["align"] = "center";

	  $tableSubHeader[7]["name"]  = "Handphone";
    $tableSubHeader[7]["width"] = "20%";
    $tableSubHeader[7]["align"] = "center";
    
    $tableSubHeader[8]["name"]  = "Sisa Jam";
    $tableSubHeader[8]["width"] = "20%";
    $tableSubHeader[8]["align"] = "center";
    
    $tableSubHeader[9]["name"]  = "Masa Berlaku";
    $tableSubHeader[9]["width"] = "20%";
    $tableSubHeader[9]["align"] = "center";

    $tableContent[0]["name"]  = "id_usr";
    $tableContent[0]["wrap"] = "nowrap";
    $tableContent[0]["align"] = "center";

    $tableContent[1]["name"]  = "member_id";
    $tableContent[1]["wrap"] = "nowrap";
    $tableContent[1]["align"] = "center";

    $tableContent[2]["name"]  = "member_nama";
    $tableContent[2]["wrap"] = "nowrap";
    $tableContent[2]["align"] = "center";

	  $tableContent[3]["name"]  = "member_nama";
    $tableContent[3]["wrap"] = "nowrap";
    $tableContent[3]["align"] = "center";
    
    $tableContent[4]["name"]  = "usr_loginname";
    $tableContent[4]["wrap"] = "nowrap";
    $tableContent[4]["align"] = "center";

    $tableContent[5]["name"]  = "member_alamat";
    $tableContent[5]["wrap"] = "nowrap";
    $tableContent[5]["align"] = "center";
 
	  $tableContent[6]["name"]  = "member_telepon";
    $tableContent[6]["wrap"] = "nowrap";
    $tableContent[6]["align"] = "center";

	  $tableContent[7]["name"]  = "member_hp";
    $tableContent[7]["wrap"] = "nowrap";
    $tableContent[7]["align"] = "center";
    
    $tableContent[8]["name"]  = "trans_time_expire";
    $tableContent[8]["wrap"] = "nowrap";
    $tableContent[8]["align"] = "center";
    
    $tableContent[9]["name"]  = "member_expire";
    $tableContent[9]["wrap"] = "nowrap";
    $tableContent[9]["align"] = "center";

    $jumContent = count($tableSubHeader);
    $startContent = 2;

    if(!$auth->IsAllowed("manage_member")){
        die("access_denied");
        exit(1);
    }
/* SELECT a . * , b.usr_loginname, c.trans_time_expire
FROM mp_member a
LEFT JOIN global_auth_user b ON a.id_usr = b.usr_id
LEFT JOIN mp_member_trans c ON a.member_id = c.id_member
AND c.trans_time_expire >0
WHERE a.member_tipe = 'M'
AND b.usr_status = 'y'
AND member_id = '1849e0c14fa6912db808b45faef90894'
ORDER BY a.member_nama
LIMIT 0 , 30 */
    $sql = "select a.*,b.usr_loginname,c.trans_time_expire
            from mp_member a
            left join global_auth_user b on a.id_usr = b.usr_id 
            left join mp_member_trans c on a.member_id = c.id_member  
            and c.trans_time_expire > 0 
            where a.member_tipe='M'  and b.usr_status = 'y' 
            order by a.member_nama";

    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs);
    
?>
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inventori.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
</head>

<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td><?php echo $tableHeader;?></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr class="subheader">
        <?php for($i=0;$i<$jumContent;$i++){ ?>
            <td width="<?php echo $tableSubHeader[$i]["width"];?>" align="<?php echo $tableSubHeader[$i]["align"];?>"><?php echo $tableSubHeader[$i]["name"];?></td>
        <?php } ?>
    </tr>
    <?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
        <tr class="<?php if($i%2==0) echo "tablecontent-odd";else echo "tablecontent";?>">
            <td align="center"><div align="center"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"></div></td>
            <td align="center"><a href='<?php echo $editPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[1]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_edit.png" alt="Edit" title="Edit" border="0"></a></td>
            <!--<td align="center"><a href='<?php echo $detailPage?>?id=<?php echo $enc->Encode($dataTable[$i][$tableContent[2]["name"]]);?>'><img hspace="2" width="16" height="16" src="<?php echo $APLICATION_ROOT;?>images/b_select.png" alt="Detail" title="Detail" border="0"></a></td>-->
                <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<?php echo ($i+1).".";?>&nbsp;</td>
		   <?php for($j=$startContent+1;$j<$jumContent-2;$j++){ ?>
                <td <?php echo $tableContent[$j]["wrap"];?> align="<?php echo $tableContent[$j]["align"];?>">&nbsp;<?php echo $dataTable[$i][$tableContent[$j]["name"]];?>&nbsp;</td>
            <?php } ?>
                <td <?php echo $tableContent[8]["wrap"];?> align="<?php echo $tableContent[8]["align"];?>">&nbsp;<?php echo FormatTime($dataTable[$i][$tableContent[8]["name"]]);?>&nbsp;</td>
                <td <?php echo $tableContent[9]["wrap"];?> align="<?php echo $tableContent[9]["align"];?>">&nbsp;<?php echo format_date($dataTable[$i][$tableContent[9]["name"]]);?>&nbsp;</td>
        </tr>
    <?php } ?>
    <tr class="tablesmallheader"> 
        <td colspan="<?php echo ($startContent);?>"><div align="center">
            <input type="submit" name="btnDelete" value="Hapus" class="button">
        <div></td>
        <td colspan="<?php echo ($jumContent-$startContent);?>"><div align="left">
            <input type="button" name="btnAdd" value="Tambah" class="button" onClick="document.location.href='<?php echo $editPage;?>'"> 
        <div></td>
    </tr>
    </table>
</form>
</body>
</html>
