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
$query = "select icd_nama,icd_nomor,icd_id from klinik.klinik_icd where upper(icd_nomor) like '".strtoupper($kata)."%' limit 10 ";
$rs = $dtaccess->Execute($query);
$dataTable = $dtaccess->FetchAll($rs);
  //echo $query;
for($i=0,$n=count($dataTable);$i<$n;$i++) {
    // echo $dataTable[$i]["icd_nama"].'<br />';
    echo '<li onClick="isi2(\''.$dataTable[$i]["icd_nama"].'\',\''.$dataTable[$i]["icd_id"].'\',\''.$dataTable[$i]["icd_nomor"].'\');" style="cursor:pointer">'.$dataTable[$i]["icd_nomor"].'</li><br />';
  
}
?>
