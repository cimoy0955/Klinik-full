<?php
require_once("root.inc.php");
require_once($ROOT."library/dataaccess.cls.php");
require_once($ROOT."library/textEncrypt.cls.php");

$enc = new TextEncrypt();

$tes = $enc->Encode("14");
echo $tes."<br>";
$tes2 = $enc->Decode($tes);
echo $tes2."<br>";

/*$connString = "host=localhost port=5432 user=its password=itsjabrik dbname=heal";
$dbConn = pg_connect($connString);
if($dbConn!=false){
    echo "Koneksi Sukses";
}else{
    echo "Koneksi Gagal";
}
// Connecting, selecting database
$dbconn = pg_connect("host=localhost dbname=heal user=its password=itsjabrik")
    or die('Could not connect: ' . pg_last_error());

// Performing SQL query
$query = 'SELECT * FROM global.global_app';
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

// Printing results in HTML
echo "<table>\n";
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "\t<tr>\n";
    foreach ($line as $col_value) {
        echo "\t\t<td>$col_value</td>\n";
    }
    echo "\t</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);
*/
$dataAccess = new DataAccess();

$sql = "select * from global.global_app";
$rs = $dataAccess->Execute($sql);
$dataTable = $dataAccess->FetchAll($rs);

for($i=0;$i<count($dataTable);$i++){
    echo $dataTable[$i]["app_nama"]."<br />";
}

?>