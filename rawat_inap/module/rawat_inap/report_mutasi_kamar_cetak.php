<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");

         
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $skr = getDateToday();
     
 	    if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}

	
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}
   
   $skr = date("d-m-Y");


	if ($_GET["tanggal_awal"] && $_GET["tanggal_akhir"]) $sql_where[] = "mutasi_tanggal between ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));

  $sql_where[] = "mutasi_kamar_id is not null";
    
    if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);
     
	$sql = "select mutasi_kamar_id,mutasi_tanggal,cust_usr_nama,cust_usr_kode,
          e.kategori_nama as klas1,f.kamar_kode as kode_kamar1 ,f.kamar_nama as nama_kamar1,g.bed_kode as bed1,
          klas2, nama_kamar2, bed2
          from klinik.klinik_mutasi_kamar a
          left join klinik.klinik_registrasi b on b.reg_id = a.id_reg
          left join global.global_customer_user c on b.id_cust_usr = c.cust_usr_id
          left join klinik.klinik_kamar_kategori e on e.kategori_id = a.id_kategori
          left join klinik.klinik_kamar f on f.kamar_id = a.id_kamar
          left join klinik.klinik_kamar_bed g on g.bed_id = a.id_bed
          left join ( select kategori_nama as klas2,kategori_id from klinik.klinik_kamar_kategori ) h on h.kategori_id = a.id_kategori_tujuan
          left join ( select kamar_nama as nama_kamar2,kamar_id from klinik.klinik_kamar ) i on i.kamar_id = a.id_kamar_tujuan
          left join ( select bed_kode as bed2,bed_id from klinik.klinik_kamar_bed ) j on j.bed_id = a.id_bed_tujuan";
             
    if ($sql_where) 
	$sql .= " where ".$sql_where;
	$sql .= " order by mutasi_tanggal asc";
	//echo $sql;
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataTable = $dtaccess->FetchAll($rs);
	   
	//*-- config table ---*//
	$table = new InoTable("table1","100%","left",null,0,2,1,null);     
	$PageHeader = "LAPORAN MUTASI KAMAR";

	//*-- config table ---*//
	$table = new InoTable("table1","100%","left",null,0,2,1,null);     
	$PageHeader = "LAPORAN MUTASI KAMAR";

	// --- construct new table ---- //
	$tbHeader[0][0][TABLE_ISI] = "No";
	$tbHeader[0][0][TABLE_WIDTH] = "3%";

	$tbHeader[0][1][TABLE_ISI] = "Tanggal";
	$tbHeader[0][1][TABLE_WIDTH] = "10%";

	$tbHeader[0][2][TABLE_ISI] = "Kode Pasien";
	$tbHeader[0][2][TABLE_WIDTH] = "10%";

	$tbHeader[0][3][TABLE_ISI] = "Nama Pasien";
	$tbHeader[0][3][TABLE_WIDTH] = "20%";

	$tbHeader[0][4][TABLE_ISI] = "Klas Asal";
	$tbHeader[0][4][TABLE_WIDTH] = "10%";

	$tbHeader[0][5][TABLE_ISI] = "Kamar Asal";
	$tbHeader[0][5][TABLE_WIDTH] = "10%";

	$tbHeader[0][6][TABLE_ISI] = "Bed Asal";
	$tbHeader[0][6][TABLE_WIDTH] = "10%";

	$tbHeader[0][7][TABLE_ISI] = "Mutasi Ke Klas";
	$tbHeader[0][7][TABLE_WIDTH] = "20%";

	$tbHeader[0][8][TABLE_ISI] = "Mutasi Ke Kamar";
	$tbHeader[0][8][TABLE_WIDTH] = "20%";

	$tbHeader[0][9][TABLE_ISI] = "Mutasi Ke Bed";
	$tbHeader[0][9][TABLE_WIDTH] = "20%";


	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){		
	
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["mutasi_tanggal"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["klas1"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nama_kamar1"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed1"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["klas2"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nama_kamar2"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed2"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

	}
       
?>

<!--<?php echo $view->RenderBody("inosoft_prn.css",true,null,false,"inosoft_prn.css"); ?>--> 

<script language="javascript" type="text/javascript">

window.print();

</script>

<style>
@media print {
     #tableprint { display:none; }
}
</style>


<form name="frmView" method="POST" action="<?php echo $thisPage; ?>">

<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="500">
    <tr>
      <!--<td rowspan="3" width="25%" class="tablecontent"><img src="<?echo $APLICATION_ROOT;?>/images/logo_bkmm.gif" width="100" height="80"/></td>-->
      <td style="text-align:left;font-size:18px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN MUTASI KAMAR</td>
    </tr>
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">R.S. Airlangga</td>
    </tr>
    <!--<tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jl. Gayung Kebonsari Timur 49, Surabaya</td>
    </tr>-->
  </table>
<br>
<br>
<br>
<!--<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="500">    
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN PEMBELIAN</td>
    </tr>
  </table>-->
     <table width="100%" border="0" cellpadding="0" cellspacing="0">
     	<tr>
     		<td>
     			<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?> 
     		</td>
     	</tr>
     </table>

</form>
</body>
<!--<?php echo $view->RenderBodyEnd(); ?>-->

</html>