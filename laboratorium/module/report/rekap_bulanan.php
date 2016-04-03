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
     $thn_skr = substr($skr,0,4);
     $bln_skr = date('m');
    
  	 if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $skr = date("d-m-Y");
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     $cetakPage = "rekap_bulanan_cetak.php?tanggal_awal='"
     .$_POST["tanggal_awal"]."'&tanggal_akhir='".$_POST["tanggal_akhir"]."'";
     
      $sql_where[] = "b.pemeriksaan_create >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "b.pemeriksaan_create <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     
          if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);
     
    // if($_POST["_bulan"]) $sql_where[] = " extract(month from b.pemeriksaan_create) =".QuoteValue(DPE_CHAR,$_POST["_bulan"]);
  //   if($_POST["_tahun"]) $sql_where[] = " extract(year from b.pemeriksaan_create) =".QuoteValue(DPE_CHAR,$_POST["_tahun"]);
	   $sql = "select e.bonus_id,e.bonus_nama,sum(a.periksa_det_total) as nominal_total, count(a.periksa_det_id) as periksa_total
             from lab_pemeriksaan_detail a
             left join lab_pemeriksaan b on a.id_pemeriksaan = b.pemeriksaan_id
             left join lab_kegiatan c on a.id_kegiatan = c.kegiatan_id
             left join lab_bonus e on e.bonus_id = c.id_bonus";
       $sql .= " where ".$sql_where;       
    // if($sql_where) $sql .= " where ".implode(" and ",$sql_where);
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
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
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
	$tbBottom[1][$counter][TABLE_ISI]     = '&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="cetakLaporan(\''.$_POST["tanggal_awal"].'\', \''.$_POST["tanggal_akhir"].'\')">&nbsp;&nbsp;';//
	$tbBottom[1][$counter][TABLE_ALIGN]   = "right";
	$tbBottom[1][$counter][TABLE_COLSPAN] = count($tbHeader[0]);
	$tbBottom[1][$counter][TABLE_CLASS]   = "tableprint";
	$counter++;
	
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<script type="text/javascript">
	var _wnd_new;

	function BukaWindow(url,judul)
	{
	    if(!_wnd_new) {
				_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=600,top=35;left=150');
		} else {
			if (_wnd_new.closed) {
				_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=600,top=35;left=150');
			} else {
				_wnd_new.focus();
			}
		}
	     return false;
	}

	function cetakLaporan(tgl_awal, tgl_akhir){
		BukaWindow('rekap_bulanan_cetak.php?tanggal_awal='+tgl_awal+'&tanggal_akhir='+tgl_akhir,'Cetak Laporan Rekap Keuangan Laboratorium');
	}

</script>
	<table width="100%" border="1" cellpadding="0" cellspacing="0">
	     <tr class="tableheader">
	          <td>&nbsp;<?php echo $PageHeader;?></td>
	     </tr>
	</table>

	<br />

	<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
	<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
	     <tr>
	          <td width="10%" class="tablecontent">&nbsp;Periode : </td>
	          <td width="30%" class="tablecontent" colspan="3">
				<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
				<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
	               - 
				<?php echo $view->RenderTextBox("tanggal_akhir","tanggal_akhir","12","12",$_POST["tanggal_akhir"],"inputField", "readonly",false);?>
				<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/></td>
	          <td>
	               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
	          </td>
	     </tr>
	</table>
	</form>
	<script type="text/javascript">
	    Calendar.setup({
	        inputField     :    "tanggal_awal",      // id of the input field
	        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
	        showsTime      :    false,            // will display a time selector
	        button         :    "img_awal",   // trigger for the calendar (button ID)
	        singleClick    :    true,           // double-click mode
	        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	    });
	    
	    Calendar.setup({
	        inputField     :    "tanggal_akhir",      // id of the input field
	        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
	        showsTime      :    false,            // will display a time selector
	        button         :    "img_akhir",   // trigger for the calendar (button ID)
	        singleClick    :    true,           // double-click mode
	        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	    });
	</script>



		
<BR/>
	<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</body>
</html>
 
