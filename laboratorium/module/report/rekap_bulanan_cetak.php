<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");

     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     //$_POST["id_petugas"]= $auth->GetUserId();   
     //$usrId = $auth->GetUserId();   
     
     $skr = getdateToday();
    
  	 if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $skr = date("d-m-Y");
     if(!$_GET["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     else $_POST["tanggal_awal"] = $_GET["tanggal_awal"];
     if(!$_GET["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     else $_POST["tanggal_akhir"] = $_GET["tanggal_akhir"];
     
      $sql_where[] = "b.pemeriksaan_create >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "b.pemeriksaan_create <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     
          if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);
     
	   $sql = "select e.bonus_id,e.bonus_nama,sum(a.periksa_det_total) as nominal_total, count(a.periksa_det_id) as periksa_total
             from lab_pemeriksaan_detail a
             left join lab_pemeriksaan b on a.id_pemeriksaan = b.pemeriksaan_id
             left join lab_kegiatan c on a.id_kegiatan = c.kegiatan_id
             left join lab_bonus e on e.bonus_id = c.id_bonus";
       $sql .= " where ".$sql_where;       

     $sql .= "group by e.bonus_nama,e.bonus_id
            order by e.bonus_nama";
    // echo $sql;       
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
     $table = new InoTable("table1","99%","left",null,0,2,1,null);     
     $PageHeader = "Laporan Rekap Keuangan";

		// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Kategori";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Jumlah Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
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
  $tbBottom[0][$counter][TABLE_ISI]     = "&nbsp;";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "right";
	$counter++;
	
  $tbBottom[0][$counter][TABLE_ISI]     = "&nbsp;Total Penerimaan Laboratorium";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "right";
	$counter++;
			
  $tbBottom[0][$counter][TABLE_ISI] = currency_format($pasien_total_semua);
	$tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$tbBottom[0][$counter][TABLE_CLASS] = "tablecontent";
	$counter++;		
	
  $tbBottom[0][$counter][TABLE_ISI] = "&nbsp;Rp.&nbsp;".currency_format($nominal_total_semua);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$tbBottom[0][$counter][TABLE_CLASS] = "tablecontent";
	
	$counter=0;	
	$tbBottom[1][$counter][TABLE_ISI]     = '&nbsp;';
	$tbBottom[1][$counter][TABLE_ALIGN]   = "right";
	$tbBottom[1][$counter][TABLE_COLSPAN] = count($tbHeader[0]);
	$counter++;

	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Laporan Rekap Keuangan Laboratorium</title>
	<script type="text/javascript">
		window.print();
	</script>
	<style type="text/css">
		table {
			border-collapse: collapse;
		}
	</style>
</head>
<body>
	<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="99%">
	    <tr>
	      <td width="25%" class="tablecontent"><img src="<?echo $APLICATION_ROOT;?>/images/logo_bkmm.gif" width="140" height="80"/></td>
	      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">PEMERINTAH PROPINSI JAWA TIMUR<br />
			<span style="font-weight: bold; text-transform: uppercase;">Rumah Sakit Mata Masyarakat</span><br />
			<small>Jl. Gayung Kebonsari Timur No. 49 Surabaya<br />
	    Tlp. (031) 9920 8000 e-mail: rsmmjawatimur@gmail.com
	    </small><hr />
	      </td>
	    </tr>
	    <tr>
	    <td>&nbsp;</td>
	      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;height: 25px;" class="tablecontent">LAPORAN REKAP KEUANGAN LABORATORIUM</td>
	    </tr>
	</table><br />
	<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</body>
</html>