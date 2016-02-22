<?php
	require_once("root.inc.php");
	require_once($ROOT."library/upload.func.php");

	$fileElementName = "fileToUpload";
	$lokasi = $ROOT."admin/images/logo";
     
     $arr_mime = array("image/gif","image/pjpeg","image/jpeg","image/png", "image/x-icon");
	
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
