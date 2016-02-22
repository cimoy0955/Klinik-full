<?php
    require_once("root.inc.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");
	  require_once($ROOT."library/currFunc.lib.php");
	  require_once($ROOT."library/dateFunc.lib.php");

    $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();
    $_POST["id_petugas"]= $auth->GetUserId();
    
    $editPage = "kasir_operasional_edit.php";
    $thisPage = "kasir_operasional_view.php";

    $logoPriv["y"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_okay.png" alt="OK" title="OK" border="0">';
    $logoPriv["n"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_nokay.png" alt="NO" title="NO" border="0">';

    //*-- config table ---*//
    $tableHeader = "Kasir Operasional";

    $tableSubHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
    $tableSubHeader[0]["width"] = "1%";
    $tableSubHeader[0]["align"] = "center";
    
    $tableSubHeader[1]["name"]  = "Edit";
    $tableSubHeader[1]["width"] = "1%";
    $tableSubHeader[1]["align"] = "center";

	  $tableSubHeader[2]["name"]  = "Tanggal";
    $tableSubHeader[2]["width"] = "5%";
    $tableSubHeader[2]["align"] = "center";

	  $tableSubHeader[3]["name"]  = "User";
    $tableSubHeader[3]["width"] = "5%";
    $tableSubHeader[3]["align"] = "center";
    
    $tableSubHeader[4]["name"]  = "Nominal";
    $tableSubHeader[4]["width"] = "10%";
    $tableSubHeader[4]["align"] = "center";

	  $tableSubHeader[5]["name"]  = "Keterangan";
    $tableSubHeader[5]["width"] = "70%";
    $tableSubHeader[5]["align"] = "center";

    $tableContent[0]["name"]  = "trans_id";
    $tableContent[0]["wrap"] = "nowrap";
    $tableContent[0]["align"] = "center";

    $tableContent[1]["name"]  = "trans_id";
    $tableContent[1]["wrap"] = "nowrap";
    $tableContent[1]["align"] = "center";

	  $tableContent[2]["name"]  = "trans_create";
    $tableContent[2]["wrap"] = "nowrap";
    $tableContent[2]["align"] = "center";

	  $tableContent[3]["name"]  = "usr_name";
    $tableContent[3]["wrap"] = "nowrap";
    $tableContent[3]["align"] = "center";

    $tableContent[4]["name"]  = "trans_harga_total";
    $tableContent[4]["wrap"] = "nowrap";
    $tableContent[4]["align"] = "right";

    $tableContent[5]["name"]  = "trans_ket";
    $tableContent[5]["wrap"] = "nowrap";
    $tableContent[5]["align"] = "left";

  

    $jumContent = count($tableSubHeader);
    $startContent = 2;

    if(!$auth->IsAllowed("kasir_operasional")){
        die("access_denied");
        exit(1);
    }

    $sql = "select a.*,b.usr_name from mp_member_trans a 
            join global_auth_user b  on a.id_petugas=b.usr_id where 
            a.trans_jenis='O' and a.trans_create >= ".QuoteValue(DPE_DATE,getDateToday())."
            and a.id_petugas = ".QuoteValue(DPE_NUMERIC,$_POST["id_petugas"]);
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
		  <td <?php echo $tableContent[2]["wrap"];?> align="<?php echo $tableContent[2]["align"];?>">&nbsp;
			<?php echo FormatDateFromTimestamp($dataTable[$i][$tableContent[2]["name"]]);?>&nbsp;</td>	
      <td <?php echo $tableContent[3]["wrap"];?> align="<?php echo $tableContent[3]["align"];?>">&nbsp;
			<?php echo $dataTable[$i][$tableContent[3]["name"]];?>&nbsp;</td>			   

			<td <?php echo $tableContent[4]["wrap"];?> align="<?php echo $tableContent[4]["align"];?>">&nbsp;
			<?php echo currency_format($dataTable[$i][$tableContent[4]["name"]]);?>&nbsp;</td>
			<td <?php echo $tableContent[5]["wrap"];?> align="<?php echo $tableContent[5]["align"];?>">&nbsp;
			<?php echo $dataTable[$i][$tableContent[5]["name"]];?>&nbsp;</td>		

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