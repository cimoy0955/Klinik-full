<?php
require_once("root.inc.php");
require_once($ROOT."library/auth.cls.php");
require_once($ROOT."library/textEncrypt.cls.php");
require_once($ROOT."library/datamodel.cls.php");
require_once($APLICATION_ROOT."library/view.cls.php");

ob_start();

$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);

$userData = $auth->GetUserData();

?>

<?php echo $view->RenderBody("inosoft.css",false); ;?>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td>&nbsp;&nbsp;My Dashboard</td>
    </tr>
</table>
 


<?php echo $view->RenderBodyEnd(); ?>
