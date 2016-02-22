<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/tree.cls.php");
     require_once($APLICATION_ROOT."library/view.cls.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();


$kata = $_POST['q'];
$query = "select prosedur_nama,prosedur_kode,prosedur_id from klinik.klinik_prosedur where upper(prosedur_kode) like '".strtoupper($kata)."%' limit 10 ";
$rs = $dtaccess->Execute($query);
$dataTable = $dtaccess->FetchAll($rs);
  //echo $query;
for($i=0,$n=count($dataTable);$i<$n;$i++) {
    // echo $dataTable[$i]["prosedur_nama"].'<br />';
    echo '<li onClick="isi11(\''.$dataTable[$i]["prosedur_nama"].'\',\''.$dataTable[$i]["prosedur_id"].'\',\''.$dataTable[$i]["prosedur_kode"].'\');" style="cursor:pointer">'.$dataTable[$i]["prosedur_kode"].'</li>';
  
}
?>
