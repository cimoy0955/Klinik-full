<?php
	require_once("root.inc.php");
	require_once($ROOT."library/upload.func.php");

	$fileElementName = "fileToUpload2";

	if($_GET["modenya"]=="usg"){
		$lokasi = $APLICATION_ROOT."images/foto_usg";
	}elseif($_GET["modenya"]=="fundus"){
		$lokasi = $APLICATION_ROOT."images/foto_fundus";
	}elseif($_GET["modenya"]=="humpre"){
		$lokasi = $APLICATION_ROOT."images/foto_humpre";
	}elseif($_GET["modenya"]=="oct"){
		$lokasi = $APLICATION_ROOT."images/foto_oct";
	}
	
	$arr_mime = array("image/gif","image/pjpeg","image/jpeg","image/png");
	
	$error = InoUpload($_FILES[$fileElementName],$lokasi,$fileElementName,null,$newName,$arr_mime);

	$msg .= "Upload Success...";
	$msg .= " File Name: " . $_FILES[$fileElementName]['name'] . ", ";
	$msg .= " File Size: " . @filesize($_FILES[$fileElementName]['tmp_name']);

	echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg . "',\n";
	echo				"file: '" . $newName . "'\n";
	echo "}";
?>
