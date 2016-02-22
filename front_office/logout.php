<?php
    require_once("root.inc.php");
    require_once($ROOT."library/auth.cls.php");
    $auth = new CAuth();
    $auth->Logout();
    header("location: ../");
    exit();
?>
