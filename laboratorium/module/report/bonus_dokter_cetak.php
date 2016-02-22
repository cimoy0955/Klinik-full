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
     
 	    if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
	 $skr = date("d-m-Y");
//	if(!$_GET["tanggal_awal"]) $_GET["tanggal_awal"] = $skr;
//	if(!$_GET["tanggal_akhir"]) $_GET["tanggal_akhir"] = $skr;

	//if ($_POST["tanggal_awal"] && !$_GET["tanggal_akhir"]) $sql_where[] = "a.pembelian_tanggal = ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));

	if ($_GET["tanggal_awal"] && $_GET["tanggal_akhir"]) $sql_where[] = "a.bonus_hasil_tanggal between ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));

  
    
    if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);
	
  $sql = "select * from laboratorium.lab_hasil_bonus a
             left join laboratorium.lab_dokter b on b.dokter_id = a.id_dokter
             left join laboratorium.lab_kegiatan c on c.kegiatan_id = a.id_kegiatan 
             left join laboratorium.lab_kategori d on d.kategori_id = c.id_kategori
             left join laboratorium.lab_bonus e on c.id_bonus = e.bonus_id";
      $sql .= " where ".$sql_where;
     $sql .= " order by a.bonus_hasil_tanggal asc";        
             
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
	   $dataTable = $dtaccess->FetchAll($rs);
	   
    //*-- config table ---*//
     $table = new InoTable("table1","95%","left",null,1,2,1,null);     
     $PageHeader = "Laporan Bonus Dokter";

	// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Tanggal";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Dokter";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "&nbsp;";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$counter++;
	
  $counter=0;
	$tbHeader[1][$counter][TABLE_ISI] = "Detail";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "No.";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Kegiatan Nama";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Kategori Bonus";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Bonus";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;

	for($i=0,$m=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$m++,$counter=0){
	   
	  
	  if($dataTable[$i]["bonus_hasil_id"]!=$dataTable[$i-1]["bonus_hasil_id"] && $dataTable[$i]["pasien_nama"]!=$dataTable[$i-1]["pasien_nama"]){

	  
		$tbContent[$m][$counter][TABLE_ISI] = $i+1;
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
	  
	  $tbContent[$m][$counter][TABLE_ISI] = format_date($dataTable[$i]["bonus_hasil_tanggal"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["dokter_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["pasien_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		$j=0;$m++;$counter=0;
    }
		
    $j++;
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
    $tbContent[$m][$counter][TABLE_ISI] = $j."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["kegiatan_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["bonus_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
    		
    $tbContent[$m][$counter][TABLE_ISI] = "Rp.&nbsp;".currency_format($dataTable[$i]["bonus_hasil_nominal"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;	
		
		$jml += $dataTable[$i]["bonus_hasil_nominal"];
		
	}

	
	
	$counter=0;
	
	$tbBottom[0][$counter][TABLE_ISI]     = "Total";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "center";
	$tbBottom[0][$counter][TABLE_COLSPAN] = 4;
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = "Rp.&nbsp;".currency_format($jml);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
      
	
	
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];

       
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

<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="100%">
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
<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="100%">    
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN BONUS DOKTER</td>
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