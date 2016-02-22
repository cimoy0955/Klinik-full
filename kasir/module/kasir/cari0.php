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
$query = "select biaya_id,biaya_nama,biaya_total,biaya_kode from klinik.klinik_biaya where upper(biaya_kode) like '".strtoupper($kata)."%' and biaya_kode is not null limit 10 ";
$rs = $dtaccess->Execute($query);
$dataTable = $dtaccess->FetchAll($rs);
  //echo $query;
echo '<ul>';
for($i=0,$n=count($dataTable);$i<$n;$i++) {
    // echo $dataTable[$i]["icd_nama"].'<br />';
    echo '<li onClick="isi(\''.$dataTable[$i]["biaya_id"].'\',\''.$dataTable[$i]["biaya_nama"].'\',\''.$dataTable[$i]["biaya_nama"].'\',\''.$dataTable[$i]["biaya_total"].'\');" style="cursor:pointer">'.$dataTable[$i]["icd_nomor"].'</li>';
  
}
echo '</ul>';
?>
