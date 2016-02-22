<?php
    require_once("root.inc.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");
    require_once($ROOT."library/upload.func.php");
    
    
    $dtaccess = new DataAccess();
    $enc = new textEncrypt();
    $auth = new CAuth();
    $err_code = 2;

        
    // --- buat foto ---
    if($_GET["orifoto"]) $oriFoto = $_GET["orifoto"];
    elseif($_POST["orifoto"]) $oriFoto = $_POST["orifoto"];
    
	if($_GET["nama"]) $namaFoto = & $_GET["nama"];
	elseif($_POST["nama"]) $namaFoto = & $_POST["nama"];

    $lokasi = $APLICATION_ROOT."images/foto_pgw";
    $maxSize = 500000;
    // --- ---

    if($_POST["btnSave"]){
        $temp = explode("_",$oriFoto);
        $counter = ($temp[2]+1);
        // -- check foto --
        if($_FILES["fotopas"]["tmp_name"]){
            switch($_FILES["fotopas"]["type"]){
                case "image/gif":
                    $destName = "pgw_".$namaFoto."_".$counter."_.gif";
                    break;
                case "image/jpeg":
                case "image/pjpeg":
                    $destName = "pgw_".$namaFoto."_".$counter."_.jpg";
                    break;
                case "image/png":
                    $destName = "pgw_".$namaFoto."_".$counter."_.png";
                    break;
            }
            if(CheckUpload($_FILES["fotopas"], $lokasi, $maxSize, $destName)){
                $err_code = 0; 
                if($oriFoto) unlink($lokasi."/".$oriFoto);
            } else $err_code = 1;
        }
    }
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
<?php if($err_code == 0){ ?>
    <script>
        window.opener.document.frmEdit.pgw_foto.value='<?php echo $destName;?>';
        window.opener.document.img_foto.src='<?php echo $lokasi."/".$destName;?>';
        window.close();
    </script>
<?php } ?>

</head>

<body>
<form name="frmEdit" method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]?>">
    <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
        <td align="left" colspan=2 class="tableHeader">UPLOAD GAMBAR</td>
    </tr>
    <tr class="tablecontent">
        <td width="30%" align="right"><strong>Foto</strong></td>
        <td width="70%"><input type="file" name="fotopas" size="20"></td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <input type="submit" name="btnSave" value="Upload" class="inputField"/>
        </td>
    </tr>

    </table>
<input type="hidden" name="orifoto" value="<?php echo $oriFoto;?>">
<input type="hidden" name="nama" value="<?php echo $namaFoto;?>">
<?php if($err_code==1){ ?>
    <font color="red">Upload Gagal!</font>
<?php } ?>
</form>

</body>
</html>
<?
    $dtaccess->Close();
?>
