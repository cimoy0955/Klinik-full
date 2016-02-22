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
      $skr = getdateToday();
     $thn_skr = substr($skr,0,4);
     $bln_skr = date('m');
     
 	    if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
   
/*   $skr = date("d-m-Y");
   if(!$_GET["_bulan"]) $_GET["_bulan"] = $bln_skr;
   if(!$_GET["_tahun"]) $_GET["_tahun"] = $thn_skr;

   if($_GET["_bulan"]) $sql_where[] = " extract(month from b.pemeriksaan_create) =".QuoteValue(DPE_CHAR,$_GET["_bulan"]);
   if($_GET["_tahun"]) $sql_where[] = " extract(year from b.pemeriksaan_create) =".QuoteValue(DPE_CHAR,$_GET["_tahun"]);
*/

 $skr = date("d-m-Y");
//	if(!$_GET["tanggal_awal"]) $_GET["tanggal_awal"] = $skr;
//	if(!$_GET["tanggal_akhir"]) $_GET["tanggal_akhir"] = $skr;

	//if ($_POST["tanggal_awal"] && !$_GET["tanggal_akhir"]) $sql_where[] = "a.pembelian_tanggal = ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));

	if ($_GET["tanggal_awal"] && $_GET["tanggal_akhir"]) $sql_where[] = "b.pemeriksaan_create between ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));

  
    
    if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);

  $sql = "select e.bonus_id,e.bonus_nama,d.id_divisi,sum(a.periksa_det_total) as nominal_total, count(a.periksa_det_id) as periksa_total
             from lab_pemeriksaan_detail a
             left join lab_pemeriksaan b on a.id_pemeriksaan = b.pemeriksaan_id
             left join lab_kegiatan c on a.id_kegiatan = c.kegiatan_id
             left join lab_dokter d on b.id_dokter = d.dokter_id
             left join lab_bonus e on e.bonus_id = c.id_bonus";
      $sql .= " where ".$sql_where;        
  //   if($sql_where) $sql .= " where ".implode(" and ",$sql_where);
     $sql .= "group by d.id_divisi,e.bonus_nama,e.bonus_id
            order by e.bonus_nama,d.id_divisi";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
	   $dataTable = $dtaccess->FetchAll($rs);
	   
	   for($i=0;$i<count($dataTable);$i++)
     {
      if(!$dataTable[$i]["id_divisi"]) $divisi_nya = "--"; else $divisi_nya = $dataTable[$i]["id_divisi"];
      
        $nominal_total[$dataTable[$i]["bonus_id"]][$divisi_nya] = $dataTable[$i]["nominal_total"];
        $pasien_total[$dataTable[$i]["bonus_id"]][$divisi_nya] = $dataTable[$i]["periksa_total"];
     }
	   
	   $sql = "select bonus_id,bonus_nama from lab_bonus";
	   $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
	   $dataBonus = $dtaccess->FetchAll($rs);

	//*-- config table ---*//
	$table = new InoTable("table1","100%","left",null,0,2,1,null);     
	$PageHeader = "LAPORAN KEUANGAN";

		// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Kategori";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Jumlah Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Total";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
  for($i=0,$j=0,$counter=0,$n=count($dataBonus);$i<$n;$i++,$counter=0){
	   
		$tbContent[$j][$counter][TABLE_ISI] = $i+1;
		$tbContent[$j][$counter][TABLE_ALIGN] = "right";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablesmallheader";
		$counter++;
	
	  $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;".$dataBonus[$i]["bonus_nama"];
		$tbContent[$j][$counter][TABLE_ALIGN] = "left";
		$tbContent[$j][$counter][TABLE_COLSPAN] = "3";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablesmallheader";
		$counter=0; $j++;
		
		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter++;

		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;a.&nbsp;Permintaan Sendiri";
		$tbContent[$j][$counter][TABLE_ALIGN] = "left";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter++;
  
		$tbContent[$j][$counter][TABLE_ISI] = currency_format($pasien_total[$dataBonus[$i]["bonus_id"]]["--"]);
		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter++;
  
		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;Rp.&nbsp;".currency_format($nominal_total[$dataBonus[$i]["bonus_id"]]["--"]);
		$tbContent[$j][$counter][TABLE_ALIGN] = "right";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter=0;$j++;
  
		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter++;

		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;b.&nbsp;".$divisi_dokter[DIVISI_DOKTER_DALAM];
		$tbContent[$j][$counter][TABLE_ALIGN] = "left";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter++;
  
		$tbContent[$j][$counter][TABLE_ISI] = currency_format($pasien_total[$dataBonus[$i]["bonus_id"]][DIVISI_DOKTER_DALAM]);
		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter++;
  
		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;Rp.&nbsp;".currency_format($nominal_total[$dataBonus[$i]["bonus_id"]][DIVISI_DOKTER_DALAM]);
		$tbContent[$j][$counter][TABLE_ALIGN] = "right";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter=0;$j++;
	
		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter++;

		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;c.&nbsp;".$divisi_dokter[DIVISI_DOKTER_LUAR];
		$tbContent[$j][$counter][TABLE_ALIGN] = "left";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter++;
  
		$tbContent[$j][$counter][TABLE_ISI] = currency_format($pasien_total[$dataBonus[$i]["bonus_id"]][DIVISI_DOKTER_LUAR]);
		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter++;
  
		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;Rp.&nbsp;".currency_format($nominal_total[$dataBonus[$i]["bonus_id"]][DIVISI_DOKTER_LUAR]);
		$tbContent[$j][$counter][TABLE_ALIGN] = "right";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
		$counter=0;$j++;
		
		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent";
		$counter++;

		$tbContent[$j][$counter][TABLE_ISI] = "&nbsp;Total";
		$tbContent[$j][$counter][TABLE_ALIGN] = "left";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent";
		$counter++;
				
    $tbContent[$j][$counter][TABLE_ISI] = ($pasien_total[$dataBonus[$i]["bonus_id"]])?currency_format(array_sum($pasien_total[$dataBonus[$i]["bonus_id"]])):"0";
		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent";
		$counter++;		
		
    $tbContent[$j][$counter][TABLE_ISI] = ($nominal_total[$dataBonus[$i]["bonus_id"]])?"&nbsp;Rp.&nbsp;".currency_format(array_sum($nominal_total[$dataBonus[$i]["bonus_id"]])):"&nbsp;Rp.&nbsp;0";
		$tbContent[$j][$counter][TABLE_ALIGN] = "right";
		$tbContent[$j][$counter][TABLE_CLASS] = "tablecontent";
		$counter++;$j++;
		
		$nominal_total_semua += $nominal_total[$dataBonus[$i]["bonus_id"]]["--"] + $nominal_total[$dataBonus[$i]["bonus_id"]][DIVISI_DOKTER_DALAM] + $nominal_total[$dataBonus[$i]["bonus_id"]][DIVISI_DOKTER_LUAR];
		$pasien_total_semua += $pasien_total[$dataBonus[$i]["bonus_id"]]["--"] + $pasien_total[$dataBonus[$i]["bonus_id"]][DIVISI_DOKTER_DALAM] + $pasien_total[$dataBonus[$i]["bonus_id"]][DIVISI_DOKTER_LUAR];
}
  $counter=0;
	
  $tbBottom[0][$counter][TABLE_ISI]     = "&nbsp;Total Penerimaan Laboratorium";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "right";
	$tbBottom[0][$counter][TABLE_COLSPAN] = 2;
	$counter++;
			
  $tbBottom[0][$counter][TABLE_ISI] = currency_format($pasien_total_semua);
	$tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$tbBottom[0][$counter][TABLE_CLASS] = "tablecontent";
	$counter++;		
	
  $tbBottom[0][$counter][TABLE_ISI] = "&nbsp;Rp.&nbsp;".currency_format($nominal_total_semua);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$tbBottom[0][$counter][TABLE_CLASS] = "tablecontent";
	

       
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
      <td rowspan="3" width="25%" class="tablecontent"><img src="<?echo $APLICATION_ROOT;?>/images/logo_bkmm.gif" width="100" height="80"/></td>
      <td style="text-align:left;font-size:18px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LABORATORIUM</td>
    </tr>
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">BKMM</td>
    </tr>
    <!--<tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jl. Gayung Kebonsari Timur 49, Surabaya</td>
    </tr>-->
  </table>
<br>
<br>
<br>
<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="500">    
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN KEUANGAN</td>
    </tr>
  </table>
<br>
<br>
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