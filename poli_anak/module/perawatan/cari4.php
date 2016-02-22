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
$query = "select ina_nama,ina_kode,ina_id from klinik.klinik_ina where ina_nama like '%".$kata."%' limit 10 ";
$rs = $dtaccess->Execute($query);
$dataTable = $dtaccess->FetchAll($rs);
  //echo $query;
for($i=0,$n=count($dataTable);$i<$n;$i++) {
    // echo $dataTable[$i]["ina_nama"].'<br />';
    echo '<li onClick="isi4(\''.$dataTable[$i]["ina_nama"].'\',\''.$dataTable[$i]["ina_id"].'\',\''.$dataTable[$i]["ina_kode"].'\');" style="cursor:pointer">'.$dataTable[$i]["ina_nama"].'</li>';
  
}
?>
