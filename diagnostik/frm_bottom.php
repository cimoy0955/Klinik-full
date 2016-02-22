<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
    
     $dtaccess = new DataAccess();
     $auth = new CAuth();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>

<link href="<?php echo $APLICATION_ROOT;?>images/inosoft-icon.ico" rel="Shortcut Icon" >
<link href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css" rel="stylesheet" type="text/css">


<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-color: #e0e0e0;
}
.style6 {font-size: 9px}
-->
</style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></HEAD>

<BODY >
<div align="right" class="style6">
  <div align="center">&copy;2008 Inosoft Trans Sistem-   Kebijakan Penggunaan - Kebijakan Privasi </div>
</div>
</BODY>
</HTML>
