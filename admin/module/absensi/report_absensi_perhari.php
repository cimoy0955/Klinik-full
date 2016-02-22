<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $table2 = new InoTable("table","100%","left");
 
     $thisPage = "report_absensi_perhari.php";

     if(!$auth->IsAllowed("report_absensi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_absensi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $skr = date("d-m-Y");
     if(!$_POST["lihat_tanggal"]) $_POST["lihat_tanggal"] = $skr;
     
     if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_absensi_pegawai'.$_POST["lihat_tanggal"].'.xls');
          }

     if($_POST["btnCetak"]) {
               header("Location:report_absen_cetak.php?id_pegawai=".$_POST["id_pgw"].
               "&tgl_awal=".$_POST["awal_gaji"].
               "&tgl_akhir=".$_POST["akhir_gaji"].
               "&telat=".$_POST["telat"].
               "&bulan=".$_POST["bulan"].
               "&tahun=".$_POST["tahun"].
               "&kekuranganlengkap=".$_POST["kekuranganlengkap"].
               "&telatOut=".$_POST["telatOut"]);
              exit(0);
          }
     
     $monthDay = array("","31","28","31","30","31","30","31","31","30",
				"31","30","31");

				
     if(!$_POST["bulan"]) $_POST["bulan"] = date("n");
     if(!$_POST["tahun"]) $_POST["tahun"] = date("Y");
     
     if(!$_POST["id_pgw"]) $_POST["id_pgw"] = $userData["id_pgw"];
     
     
     //*-- config table ---*//
     $table = new InoTable("table1","100%","center",null,1,2,1,null);     
     $PageHeader = "Report Absensi per Tanggal";

	
	$imgOK["0"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/on.gif" alt="OK" title="OK" border="0">';
	$imgOK["1"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/off.gif" alt="NO" title="NO" border="0">';


     if($_POST["btnLanjut"] || $_POST["btnExcel"]){
	  
//$awalGaji = $_POST["tahun"]."-".$_POST["bulan"]."-01";
    $lihatTanggal = $_POST["lihat_tanggal"];
          
	  $sql = "select a.rekap_absen_tanggal, a.absen_cin, a.absen_cout,
	           b.pgw_nama, b.pgw_nip
		        from hris.hris_absen_rekap a
		        left join hris.hris_pegawai b on a.id_pgw=b.pgw_id
		        where rekap_absen_tanggal = '".date_db($lihatTanggal)."'
            order by b.pgw_nip";
      
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
          $dataTable = $dtaccess->FetchAll($rs);
     
    
          // --- construct new table ---- //
          $tbHeader[0][0][TABLE_ISI] = "No";
          $tbHeader[0][0][TABLE_WIDTH] = "5%";
               
          $tbHeader[0][1][TABLE_ISI] = "ID Number";
          $tbHeader[0][1][TABLE_WIDTH] = "10%";
               
          $tbHeader[0][2][TABLE_ISI] = "Nama";
          $tbHeader[0][2][TABLE_WIDTH] = "10%";
               
          $tbHeader[0][3][TABLE_ISI] = "Check In";
          $tbHeader[0][3][TABLE_WIDTH] = "10%";
          
          $tbHeader[0][4][TABLE_ISI] = "Check Out";
          $tbHeader[0][4][TABLE_WIDTH] = "10%";
          
          //$tbHeader[0][5][TABLE_ISI] = "Lama Kerja";
          //$tbHeader[0][5][TABLE_WIDTH] = "10%";
          //
         
               
        
        $j=0;
       
        //bulan ditambahai
        if ($_POST["bulan"]<10) $_POST["bulan"]="0".$_POST["bulan"];
        for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
      
        if ($dataTable[$i]["jam_cek_in"]!="00:00:00") $jam_cek_in=$dataTable[$i]["jam_cek_in"];
        else $jam_cek_in="";
	
        //if ($hasilJam="0 jam 0 menit")
        //else $hasilJam=" ";
        
        
        $tbContent[$j][$counter][TABLE_ISI] = $j+1;
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		$tbContent[$j][$counter][TABLE_WIDTH] = "1%";
     		$counter++;
		    
     		$tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["pgw_nip"];
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		$tbContent[$j][$counter][TABLE_WIDTH] = "24%";
     		$counter++;
		    
     		$tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["pgw_nama"];
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		$tbContent[$j][$counter][TABLE_WIDTH] = "25%";
     		$counter++;
     		
     		$tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["absen_cin"];
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		$tbContent[$j][$counter][TABLE_WIDTH] = "25%";
     		$counter++;
     		
     		
     		$tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["absen_cout"];
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		$tbContent[$j][$counter][TABLE_WIDTH] = "25%";
     		$counter++;
     		/*($jam)?$jam="";
		($menit)?$menit="";*/
     		//$tbContent[$j][$counter][TABLE_ISI] = $jam." jam ".$menit." menit";/*($hasilJam)?$hasilJam:""; */
     		//$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		//$counter++;
     		//
     	
     		$j++;
     		if (DateDiff($dataTable[$i]["tgl_cek_in"],$dataTable[$i+1]["tgl_cek_in"])>1
            )
        {
         $total=DateDiff($dataTable[$i]["tgl_cek_in"],$dataTable[$i+1]["tgl_cek_in"]);
         for($l=1;$l<$total;$l++){
         
         if (GetMonthToday(DateAdd($dataTable[$i]["tgl_cek_in"],$l))==$_POST["bulan"])
         {
           $counter=0;
           $tbContent[$j][$counter][TABLE_ISI] = $j+1;
       		 $tbContent[$j][$counter][TABLE_ALIGN] = "right";
       		 $counter++;
  
         		$tbContent[$j][$counter][TABLE_ISI] = GetDayName(DateAdd($dataTable[$i]["tgl_cek_in"],$l),0,10);
         		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
         		$counter++;
    
         		$tbContent[$j][$counter][TABLE_ISI] = format_date(DateAdd($dataTable[$i]["tgl_cek_in"],$l));
         		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
         		$counter++;
         		
         		$tbContent[$j][$counter][TABLE_ISI] = "";
         		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
         		$counter++;
         		
         		$tbContent[$j][$counter][TABLE_ISI] = "";
         		$tbContent[$j][$counter][TABLE_ALIGN] = "left";
         		$counter++;
    
         		$tbContent[$j][$counter][TABLE_ISI] = "";
         		$tbContent[$j][$counter][TABLE_ALIGN] = "left";
         		$counter++;
    
         		$tbContent[$j][$counter][TABLE_ISI] = "";
         		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
         		$counter++;
         		
         		$tbContent[$j][$counter][TABLE_ISI] = ""; 
         		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
         		$counter++;
         		$absenId = $dtaccess->GetTransID();
		        $sql = "insert into hris.hris_absen_rekap(absen_id,absen_cin,id_pgw) 
            values ('".$absenId."','".DateAdd($dataTable[$i]["tgl_cek_in"],$l)."',".QuoteValue(DPE_CHAR,$_POST["id_pgw"]).")";
            $rs = $dtaccess->Execute($sql);
         		$j++;
         	}	
       		}
     		  
        } 
     		
     		
      }
//    $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnCetak" value="Cetak" class="button">&nbsp;';  
//    $tbBottom[0][1][TABLE_ALIGN] = "left";
//    
//    $tbBottom[0][1][TABLE_ISI] = "Total";
//		$tbBottom[0][1][TABLE_ALIGN] = "right";
//		$tbBottom[0][1][TABLE_COLSPAN] = 6;
//          
//    $tbBottom[0][2][TABLE_ISI] = $hasilTotJam;
//		$tbBottom[0][2][TABLE_ALIGN] = "center";
//          
//    
          
     }
     
     $sql = "select pgw_id, pgw_nama from hris.hris_pegawai where pgw_tanggal_keluar is null order by pgw_nama";
     $rs = $dtaccess->Execute($sql);
     $dataPegawai = $dtaccess->FetchAll($rs);
?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>
<?php if(!$_POST["btnExcel"]) { ?>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td>&nbsp;<?php echo $PageHeader;?></td>
     </tr>
</table>

<BR/>


<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <?php if($auth->IsAllowed("report_absensi",PRIV_READ)){ ?>
     	<td width="10%" align="left" class="tablecontent">Tanggal</td>
     	<td width="20%" align="left" class="tablecontent-odd">
     		<input type="text" id="lihat_tanggal" name="lihat_tanggal" size="15" maxlength="10" value="<?php echo $_POST["lihat_tanggal"];?>" onKeyDown="return tabOnEnter(this, event);"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_lihat_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
			(dd-mm-yyy)
     	</td>
     	<?php } ?>
          <td class="tablecontent">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>
     	
</table>
<table>
<tr>
          <td class="tablecontent" colspan="6">
			       <input type="submit" name="btnExcel" value="Export Excel" class="button">
          </td>
     </tr>
</table>
</form>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "lihat_tanggal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_lihat_tanggal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>


<?php } ?>

<?php if($_POST["btnExcel"]) {?>
     <table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="<?php echo (count($dataSplit)+6)?>"><strong>LAPORAN ABSENSI PEGAWAI HARIAN</strong></td>
               <?php echo $table2->RenderView($tbHeader,$tbContent,$tbBottom); ?>
          </tr>
     </table>
<?php }?>

<?php if($_POST["btnLanjut"]) echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>


<?php if(!$_POST["btnExcel"]) {?>
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<input type="hidden" name="id_pgw" value="<?php echo $_POST["id_pgw"]; ?>" />
<input type="hidden" name="awal_gaji" value="<?php echo $awalGaji; ?>" />
<input type="hidden" name="akhir_gaji" value="<?php echo $akhirGaji; ?>" />
<input type="hidden" name="bulan" value="<?php echo $namaBulan; ?>" />
<input type="hidden" name="tahun" value="<?php echo $_POST["tahun"]; ?>" />
<input type="hidden" name="telat" value="<?php echo $telat; ?>" />
<input type="hidden" name="kekuranganlengkap" value="<?php echo $kekuranganLengkap; ?>" />
<input type="hidden" name="telatOut" value="<?php echo $telatOut; ?>" />
</form>
<?php } ?>
</body>
</html>
 
