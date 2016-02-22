<?php
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
	header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");  
	header("Pragma: no-cache");

     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
    
     $auth = new CAuth();
     $userData = $auth->GetUserData();
     $enc = new textEncrypt();
       
     if($auth->IsAllowed()===1){
         header("location:".$ROOT);
         exit();
     }
     
     $mainFrame = "frm_main.php";
     
?>
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="<?php echo $APLICATION_ROOT;?>images/inosoft-icon.ico" rel="Shortcut Icon" >
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/frameLeft.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/frameTop.js"></script>

<frameset id="_top_frame" rows="89,*,15" cols="*" framespacing="1" frameborder="yes" border="1" bordercolor="#999999">
  <frame src="frm_top.php" name="topFrame" frameborder="yes" scrolling="no" noresize bordercolor="#CCCCCC" >
  <frameset id="_left_frame" cols="175,*" framespacing="0" frameborder="no" border="1" bordercolor="#CCCCCC">
    <frame src="frm_left.php?panel=setup" name="leftFrame" frameborder="yes" scrolling="yes" bordercolor="#CCCCCC">
    <frame src="<?php echo $mainFrame?>" name="mainFrame" scrolling="auto">
  </frameset>
  <frame src="frm_bottom.php" name="bottomFrame" frameborder="no" scrolling="no" noresize bordercolor="#CCCCCC" >
</frameset>
