<?php
    require_once("root.inc.php");
    require_once($ROOT."library/bitFunc.lib.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");
    //require_once($APLICATION_ROOT."module/db.inc.php");    
    
    $dtaccess = new DataAccess();
    $enc = new textEncrypt();
    $auth = new CAuth();
  	
    if($_x_mode=="New") $privMode = PRIV_CREATE;
  	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
  	elseif($_x_mode=="Delete") $privMode = PRIV_DELETE;  	
    else $privMode = PRIV_READ ;

    if(!$auth->IsAllowed($privFiles[$_SERVER["PHP_SELF"]],$privMode)){
        //die("access_denied");
        //exit(1);
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>.:: Heal ExSys Accounting ::. Cari Nomor Akun</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link href="<?php echo($APLICATION_ROOT);?>library/css/inosoft.css" rel="stylesheet" type="text/css">
    <script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/elements.js"></script>
    <script type="text/javascript">
    <!--//
    function sendValue(_value_,id_prk,nama_prk) {
        window.opener.document.<?php echo($_GET["_confirm"])?>.value = _value_;
		window.opener.document.frmEdit.id_prk.value = id_prk;
		window.opener.document.frmEdit.nama_prk.value = nama_prk;
        window.close();
    }
    //-->
    </script>
</head>
<body topmargin="0" leftmargin="0" OnLoad="javascript: document.frmSearch._name.focus();">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
    <tr><td>
<form name="frmSearch" method="get" action="<?php echo($_SERVER["PHP_SELF"]);?>">
<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
    <tr class="tablesmallheader" >
        <td colspan="2"><center>Pencarian&nbsp;Akun</center></td>
    </tr>
    <tr>
        <td align="right" class="tablecontent">Nama&nbsp;Akun</td>
        <td class="tablecontent"><input type="text" value="<?php echo($_GET["_name"]);?>" name="_name" /></td>
    </tr>
    <tr>
        <td align="right" class="tablecontent" >Kode&nbsp;Akun</td>
        <td class="tablecontent"><input type="text" value="<?php echo($_GET["_code"]);?>" name="_code" /></td>
    </tr>
    <tr>
        <td colspan="2"><center><input type="submit" name="btnSearch" value="Cari" class="button" /><input type="button" name="btnClose" value="Tutup" OnClick="javascript: window.close();" class="button" /></center></td>
    </tr>
    <input type="hidden" name="_confirm" value="<?php echo($_GET["_confirm"])?>" />
    <input type="hidden" name="_mode" value="<?php echo($_GET["_mode"])?>" />
    <input type="hidden" name="_dept" value="<?php echo($_GET["_dept"])?>" />
    <input type="hidden" name="_akt" value="<?php echo($_GET["_akt"])?>" />
</table>
</form>
    </td></tr>
    <tr><td>
<?php
    if (($_GET["_cat"] == "yes") || isset($_GET["btnSearch"])) {

        $sql = "SELECT a.id_prk, a.no_prk, a.nama_prk, a.isakt_prk FROM gl.gl_perkiraan a";       
		
		if($_GET["_code"]) $sql_where[]= " a.no_prk LIKE '".$_GET["_code"]."%'";        
		if($_GET["_name"]) $sql_where[]= " UPPER(a.nama_prk) LIKE UPPER('%".$_GET["_name"]."%')";
		
		if($sql_where){
			$sql_where = implode(" and ",$sql_where);
			$sql = $sql ." where ".$sql_where;
			
		}       
		$sql .= " ORDER BY a.no_prk";
        $rs_search = $dtaccess->Execute($sql) or die(errorPage());
        if ($rs_search->RecordCount()) {
?>
    <fieldset>
    <legend><strong>Kode Perkiraan</strong></legend>
    <table cellpadding="1" cellspacing="1" border="0" width="100%">
        <tr class="tablesmallheader" >
            <td width="1%" nowrap><center>&nbsp;&nbsp;Kode&nbsp;Akun&nbsp;&nbsp;</center></td>
            <td nowrap><center>&nbsp;&nbsp;Nama&nbsp;Akun&nbsp;&nbsp;</center></td>
            <td width="1%" nowrap>&nbsp;</td>
        </tr>
<?php
            $x = 0;
            while($row_search = $rs_search->FetchRow()) {
                $pjg_id = strlen($row_search["id_prk"]);
                $panjang = (strlen($row_search["id_prk"])/2)-1;
                if ( $panjang > 0 ) $treku = str_repeat("&#8230;&#8230;",$panjang);
                else $treku = "";
?>
        <tr>
            <td class="tablecontent<?php if (!($x % 2)) { ?>-odd<?php } ?>" width="1%" nowrap>&nbsp;&nbsp;<?php echo($row_search["no_prk"]);?>&nbsp;&nbsp;</td>
            <td class="tablecontent<?php if (!($x % 2)) { ?>-odd<?php } ?>" nowrap>&nbsp;&nbsp;<?php echo($treku."&nbsp;"); if (($pjg_id == "2") || ($pjg_id == "4")) { ?><strong><?php } if (($pjg_id == "4") || ($pjg_id == "6")) { ?><i><?php } ?><?php echo($row_search["nama_prk"]);?><?php if (($pjg_id == "2") || ($pjg_id == "4")) { ?></strong><?php } if (($pjg_id == "4") || ($pjg_id == "6")) { ?></i><?php } ?>&nbsp;&nbsp;</td>
            <td class="tablecontent<?php if (!($x % 2)) { ?>-odd<?php } ?>" width="1%" nowrap><center>&nbsp;&nbsp;<?php if (($_GET["_akt"] == "no") || ((strtolower($row_search["isakt_prk"]) == "y") && ($_GET["_akt"] == "yes"))) { ?><img src="<?php echo $APLICATION_ROOT;?>images/bd_import_tbl.png" border="0" alt="Pilih" title="Pilih" width="12" height="12" class="img-button" OnClick="javascript: sendValue('<?php echo($row_search["no_prk"]);?>','<?php echo($row_search["id_prk"]);?>','<?php echo($row_search["nama_prk"]);?>');" /><?php } ?>&nbsp;&nbsp;</center></td>
        </tr>
<?php
                $x++;
            }
?>
    </table>
    </fieldset>
<?php
        }
    }
?>
    </td></tr></table>
</body>
