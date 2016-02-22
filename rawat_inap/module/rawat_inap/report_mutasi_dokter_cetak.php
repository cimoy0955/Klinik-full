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

  $sql_where[] = "mutasi_dokter_id is not null";
    
    if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);
     
	$sql = "select mutasi_dokter_id,mutasi_tanggal,mutasi_keterangan,cust_usr_nama,cust_usr_kode, pgw_nama as nama1, nama2
          from klinik.klinik_mutasi_dokter a
          left join klinik.klinik_registrasi b on b.reg_id = a.id_reg
          left join global.global_customer_user c on b.id_cust_usr = c.cust_usr_id
          left join hris.hris_pegawai e on e.pgw_id = a.id_pgw_sebelum
          left join ( select pgw_nama as nama2,pgw_id from hris.hris_pegawai ) h on h.pgw_id = a.id_pgw_sesudah";
                 
    if ($sql_where) 
	$sql .= " where ".$sql_where;
	$sql .= " order by mutasi_tanggal asc";
	//echo $sql;
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataTable = $dtaccess->FetchAll($rs);
	   
	//*-- config table ---*//
	$table = new InoTable("table1","100%","left",null,0,2,1,null);     
	$PageHeader = "LAPORAN MUTASI DOKTER";

	// --- construct new table ---- //
	$tbHeader[0][0][TABLE_ISI] = "No";
	$tbHeader[0][0][TABLE_WIDTH] = "3%";

	$tbHeader[0][1][TABLE_ISI] = "Tanggal";
	$tbHeader[0][1][TABLE_WIDTH] = "10%";

	$tbHeader[0][2][TABLE_ISI] = "Kode Pasien";
	$tbHeader[0][2][TABLE_WIDTH] = "10%";

	$tbHeader[0][3][TABLE_ISI] = "Nama Pasien";
	$tbHeader[0][3][TABLE_WIDTH] = "17%";

	$tbHeader[0][4][TABLE_ISI] = "Dokter Sebelumnya";
	$tbHeader[0][4][TABLE_WIDTH] = "20%";

	$tbHeader[0][5][TABLE_ISI] = "Mutasi Ke Dokter";
	$tbHeader[0][5][TABLE_WIDTH] = "20%";

	$tbHeader[0][6][TABLE_ISI] = "Keterangan";
	$tbHeader[0][6][TABLE_WIDTH] = "30%";


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

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nama1"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nama2"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["mutasi_keterangan"];
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
      <td style="text-align:left;font-size:18px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN MUTASI DOKTER</td>
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