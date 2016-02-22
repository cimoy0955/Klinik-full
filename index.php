<?php
     //error_reporting(E_ALL);
     //ini_set('display_errors',1);
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     header("Content-type: text/html; charset=utf-8");
     //echo $ROOT;
     //$auth = new CAuth();
     //$userData = $auth->GetUserData();
     //$enc = new textEncrypt();
     
     include("login.php");
     exit();
     
?>