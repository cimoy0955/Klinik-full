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
$query = "select a.id, a.item_nama, a.item_kode, b.price_list_rate, b.price_list 
            from stocks.tb_item a
            left join stocks.item_price b on b.item_kode = a.item_kode 
            where a.item_kode like '%".$kata."%' and price_list = 'BPJS'
            order by a.item_kode
            limit 10";
$rs = $dtaccess->Execute($query);
$dataTable = $dtaccess->FetchAll($rs);
  // echo $query;
echo '<ul>';
for($i=0,$n=count($dataTable);$i<$n;$i++) {
    // echo $dataTable[$i]["icd_nama"].'<br />';
    echo '<li onClick="isi02(\''.$dataTable[$i]["id"].'\',\''.$dataTable[$i]["item_kode"].'\',\''.$dataTable[$i]["item_nama"].'\',\''.$dataTable[$i]["price_list_rate"].'\');" style="cursor:pointer">'.$dataTable[$i]["item_kode"].'&nbsp;|&nbsp;'.$dataTable[$i]["item_nama"].'</li>';
  
}
echo '</ul>';
?>
