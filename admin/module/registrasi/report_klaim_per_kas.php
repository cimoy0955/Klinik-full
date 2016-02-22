<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $thisPage = "report_loket_per_kas.php";

     if(!$auth->IsAllowed("report_kasir_per_kas",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_kasir_per_kas",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $plx = new InoLiveX("GetDay");
     
  /*   function GetDay($month) {
          global $view, $monthDay;
          
          $bulan[0] = $view->RenderOption("--","[ All ]");
          for($i=1,$n=$monthDay[$month];$i<=$n;$i++){
               $bulan[$i] = $view->RenderOption($i,$i);
          }
          
          $str = $view->RenderComboBox("tanggal","tanggal",$bulan);
          
          return $str;
     }*/
     

	$sql = "select * from klinik.klinik_split order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
     
     
     if(!$_POST["bulan"]) $_POST["bulan"] = date('n');
     if(!$_POST["tahun"]) $_POST["tahun"] = date('Y');
     
     if($_POST["tanggal"] && $_POST["tanggal"]!="--") $sql_where[] = "extract(day from a.reg_klaim_when) = '".$_POST["tanggal"]."'";
     if($_POST["bulan"] && $_POST["bulan"]!="--") $sql_where[] = "extract(month from a.reg_klaim_when) = '".$_POST["bulan"]."'";
     if($_POST["tahun"]) $sql_where[] = "extract(year from a.reg_klaim_when) = '".$_POST["tahun"]."'";
     if($_POST["reg_klaim_jenis"]) $sql_where[] = "a.reg_klaim_jenis = ".QuoteValue(DPE_CHAR,$_POST["reg_klaim_jenis"]);
     if($_POST["cust_usr_jenis"]) $sql_where[] = "c.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
     
     
	$sql_where = implode(" and ",$sql_where);
	
     $sql = "select extract(month from a.reg_klaim_when) as bulan, sum(b.reg_klaim_split_nominal) as tot_nominal, 
               d.id_split 
               from klinik.klinik_registrasi_klaim a 
               join klinik.klinik_registrasi_klaim_split b on a.reg_klaim_id = b.id_reg_klaim 
               join klinik.klinik_registrasi c on c.reg_id = a.id_reg
               join klinik.klinik_paket_klaim_split d on b.id_klaim_split = d.klaim_split_id";
               
	$sql .= " where ".$sql_where;
     $sql .= " group by extract(month from a.reg_klaim_when), d.id_split 
               order by extract(month from a.reg_klaim_when), d.id_split";
                
     $rs = $dtaccess->Execute($sql);
     
	while($row = $dtaccess->Fetch($rs)) {
          
          $dataFolio[$row["bulan"]][$row["id_split"]] = $row["tot_nominal"];
          
	}
	
	$counter=0;	
     $tbHeader[0][0][TABLE_ISI] = "Bulan";
     $tbHeader[0][0][TABLE_WIDTH] = "15%";
	
	for($i=0,$n=count($dataSplit);$i<$n;$i++){
		$tbHeader[0][$i+1][TABLE_ISI] = $dataSplit[$i]["split_nama"];
		$tbHeader[0][$i+1][TABLE_WIDTH] = "10%";
	}
	
     $tbHeader[0][$n+1][TABLE_ISI] = "Total";
     $tbHeader[0][$n+1][TABLE_WIDTH] = "15%";
	
     
     if($_POST["bulan"] && $_POST["bulan"]!="--") {
          $awalBulan = $_POST["bulan"];
          $akhirBulan = ($_POST["bulan"]+1); 
     } else {
          $awalBulan = 1;
          $akhirBulan = 13;
     } 
     
     for($i=$awalBulan,$baris=0,$counter=0,$n=$akhirBulan;$i<$n;$i++,$baris++,$counter=0){
     
          $tbContent[$baris][$counter][TABLE_ISI] = $monthName[$i];
          $tbContent[$baris][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          unset($totBulan);
		for($j=0,$k=count($dataSplit);$j<$k;$j++){
			$tbContent[$baris][$counter][TABLE_ISI] = currency_format($dataFolio[$i][$dataSplit[$j]["split_id"]]);
			$tbContent[$baris][$counter][TABLE_ALIGN] = "right";
			$counter++;
			
			$totSplit[$dataSplit[$j]["split_id"]] += $dataFolio[$i][$dataSplit[$j]["split_id"]];
               $totBulan += $dataFolio[$i][$dataSplit[$j]["split_id"]];
               $totalAll += $dataFolio[$i][$dataSplit[$j]["split_id"]];
		}

          $tbContent[$baris][$counter][TABLE_ISI] = currency_format($totBulan);
          $tbContent[$baris][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
     }
     
     
     $counter = 0;
     $tbBottom[0][$counter][TABLE_ISI] = "&nbsp;";
     $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	for($i=0,$n=count($dataSplit);$i<$n;$i++){
		$tbBottom[0][$counter][TABLE_ISI] = currency_format($totSplit[$dataSplit[$i]["split_id"]]);
		$tbBottom[0][$counter][TABLE_ALIGN] = "right";
		$counter++;
	}

	
	$tbBottom[0][$counter][TABLE_ISI] = currency_format($totalAll);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
     
     $tableHeader = "Report Biaya Klaim per Kas";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran_klaim_per_kas_'.$_POST["tgl_awal"].'.xls');
     }
	
?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>

<script language="JavaScript">

<?$plx->Run();?>

function GetDayMonth(month) {

     document.getElementById("div_tanggal").innerHTML = GetDay(month,"type=r"); 
}

</script>

<?php if(!$_POST["btnExcel"]) { ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td colspan="<?php echo (count($dataSplit)+6)?>">&nbsp;<?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
     <tr class="tablecontent">
          <td width="15%">&nbsp;Periode</td>
          <td width="40%">
               <select name="bulan" id="bulan" onKeyDown="return tabOnEnter(this, event);"  onChange="GetDayMonth(document.getElementById('bulan').value)">
                    <option value="--">[ Semua Bulan ]</option>
                    <?php foreach($monthName as $key => $value) { 
                         if ($value) {?>
                              <option value="<?php echo $key;?>" <?php if($_POST["bulan"]==$key) echo "selected";?>><?php echo $value;?></option>                         
                         <?php } 
                    } ?>
			</select>&nbsp;&nbsp;
			<span id="div_tanggal">
     			<select name="tanggal" id="tanggal" onKeyDown="return tabOnEnter(this, event);">
                         <option value="--">[ All ]</option>
                         <?php for($i=1,$n=($monthDay[$_POST["bulan"]]);$i<=$n;$i++) {?>
                                   <option value="<?php echo $i;?>" <?php if($_POST["tanggal"]==$i) echo "selected";?>><?php echo $i;?></option>                         
                         <?php }?>
     			</select>
               </span>&nbsp;&nbsp;
			<select name="tahun" id="tahun" onKeyDown="return tabOnEnter(this, event);">
                    <?php for($i=(date('Y')-3),$n=(date('Y')+3);$i<=$n;$i++) {?>
                         <option value="<?php echo $i;?>" <?php if($_POST["tahun"]==$i) echo "selected";?>><?php echo $i;?></option>                         
                    <?php }?>
			</select>
          </td>
          <td width="10%">&nbsp;Layanan</td>
          <td width="35%">
              <select name="reg_klaim_jenis">
				<option value="">[ Semua Layanan Biaya ]</option>
				<?php foreach($biayaStatus as $key=>$value) {?>
					<option value="<?php echo $key;?>" <?php if($_POST["reg_klaim_jenis"]==$key) echo "selected";?>><?php echo $value;?></option>
				<?php } ?>
			</select>
          </td> 
     </tr>
     <tr class="tablecontent">
		<td>&nbsp;Jenis Pasien</td>
          <td colspan="3">
			<select name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);">
                    <?php foreach($bayarPasien as $key => $value) { 
                         if($key!=PASIEN_BAYAR_SWADAYA) {?>
                              <option value="<?php echo $key;?>" <?php if($_POST["cust_usr_jenis"]==$key) echo "selected";?>><?php echo $value;?></option>
                         <?php }
                    } ?>
			</select>
		</td>
	</tr>
	<tr>
          <td class="tablecontent" colspan="6">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
			<input type="submit" name="btnExcel" value="Export Excel" class="button">
          </td>
     </tr>
</table>

<BR>

</form>

<?php } ?>

<?php if($_POST["btnExcel"]) {?>
     <table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="<?php echo (count($dataSplit)+2)?>"><strong>LAPORAN BIAYA KLAIM PERJENIS KAS</strong></td>
          </tr>
     </table>
<?php }?>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>


<?php echo $view->RenderBodyEnd(); ?>
