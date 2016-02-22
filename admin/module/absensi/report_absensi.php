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
 
     $thisPage = "report_absensi.php";

     if(!$auth->IsAllowed("report_absensi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_absensi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
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
     $PageHeader = "Report Absensi";

	
	$imgOK["0"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/on.gif" alt="OK" title="OK" border="0">';
	$imgOK["1"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/off.gif" alt="NO" title="NO" border="0">';


     if($_POST["id_pgw"]) {
	  
$awalGaji = $_POST["tahun"]."-".$_POST["bulan"]."-01";
          
	  $sql = "select rekap_absen_tanggal, absen_cin, absen_cout
		  from hris.hris_absen_rekap
                    where id_pgw = ".$_POST["id_pgw"]."
		    and rekap_absen_tanggal >= ".QuoteValue(DPE_DATE,$awalGaji);
      
     //             substring(CAST(absen_cin as varchar) from 12 for 8) as jam_cek_in, absen_cin,
       //           substring(CAST(absen_cout as varchar) from 12 for 8) as jam_cek_out, absen_cout
     //and date(to_char(absen_cin,'dd-mm-yyyy')) >= ".QuoteValue(DPE_DATE,$awalGaji)."  
     //			and date(to_char(absen_cin,'dd-mm-yyyy')) <= ".QuoteValue(DPE_DATE,$akhirGaji)." 
     //               order by date(to_char(absen_cin,'dd-mm-yyyy'))";
          
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
          $dataTable = $dtaccess->FetchAll($rs);
     
     // $sql = "select * from hris.hris_absen_rekap a 
     //               where id_pgw = ".$_POST["id_pgw"]."     
     //			and rekap_absen_tanggal >= ".QuoteValue(DPE_DATE,$awalGaji)."  
     //			and rekap_absen_tanggal <= ".QuoteValue(DPE_DATE,$akhirGaji)." 
     //               order by rekap_absen_tanggal"; 
     //     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     //     $dataTable = $dtaccess->FetchAll($rs);

//echo $sql;

          // --- construct new table ---- //
          $tbHeader[0][0][TABLE_ISI] = "No";
          $tbHeader[0][0][TABLE_WIDTH] = "5%";
               
          $tbHeader[0][1][TABLE_ISI] = "Hari";
          $tbHeader[0][1][TABLE_WIDTH] = "10%";
               
          $tbHeader[0][2][TABLE_ISI] = "Tanggal";
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
        
        $jamSelisih=TimestampDiff($dataTable[$i]["absen_cin"],$dataTable[$i]["absen_cout"]);
     
        $jam=floor($jamSelisih/3600);
        $menit=floor(($jamSelisih%3600)/60);
        //$hasilJam=$jam." jam ".$menit." menit";
	 
	
	
        $totJam += $jam;
        $totMenit += $menit;
        $hasilTotJam=$totJam." jam ".$totMenit." menit";
        if ($totMenit>60)
        {
	  $sisamenit=floor($totMenit/60);
	  $totMenit=floor($totMenit%60);
	  $totJam=$totJam+$sisaMenit;
	  $hasilTotJam=$totJam." jam ".$totMenit." menit";
        }
        
        if (TimeDiff($dataTable[$i]["jam_cek_in"],"08:00:00")<0) $telat++;
        if (GetDay($dataTable[$i]["tgl_cek_in"]==6))
	  {
	       $jamKerja=$jamKerja+7;
	       if (TimeDiff("17:00:00",$dataTable[$i]["jam_cek_out"])<0) $telatOut++;
	       if ($jam<7)
	       { 
		    $kurangJamAwal=6-$jam;
		    $kurangMenitAwal=60-$menit;
		    $kurangjam-=$kurangJamAwal;
		    $kurangmenit-=$kurangMenitAwal;
	       }  else {
		    $kurangJamAwal=6-$jam;
		    $kurangMenitAwal=60-$menit;
		    $kurangjam+=$kurangJamAwal;
		    $kurangmenit+=$kurangMenitAwal;
	       }
	  }
        
        else {
          $jamKerja=$jamKerja+8;
          if (TimeDiff("16:00:00",$dataTable[$i]["jam_cek_out"])<0) $telatOut++;
          if ($jam<8) 
	       { 
		    $kurangJamAwal=7-$jam;
		    $kurangMenitAwal=60-$menit;
		    $kurangjam-=$kurangJamAwal;
		    $kurangmenit-=$kurangMenitAwal;
	       } else {
		    $kurangJamAwal=6-$jam;
		    $kurangMenitAwal=60-$menit;
		    $kurangjam+=$kurangJamAwal;
		    $kurangmenit+=$kurangMenitAwal;
          }
        }
        
        
        if ($dataTable[$i]["jam_cek_in"]!="00:00:00") $jam_cek_in=$dataTable[$i]["jam_cek_in"];
        else $jam_cek_in="";
	
        //if ($hasilJam="0 jam 0 menit")
        //else $hasilJam=" ";
        
        
        $tbContent[$j][$counter][TABLE_ISI] = $j+1;
     		$tbContent[$j][$counter][TABLE_ALIGN] = "right";
     		$counter++;
		    
     		$tbContent[$j][$counter][TABLE_ISI] = GetDayName($dataTable[$i]["rekap_absen_tanggal"]);
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		$counter++;
		    
     		$tbContent[$j][$counter][TABLE_ISI] = format_date($dataTable[$i]["rekap_absen_tanggal"]);
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		$counter++;
     		
     		$tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["absen_cin"];
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		$counter++;
     		
     		
     		$tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["absen_cout"];
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
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

<?php echo $view->RenderBody("inosoft.css",true); ?>
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
     	<td width="10%" align="left" class="tablecontent">Pegawai</td>
     	<td width="20%" align="left" class="tablecontent-odd">
     		<select name="id_pgw">
     			<?php for($i=0,$n=count($dataPegawai);$i<$n;$i++) { ?>			
     				<option value="<?php echo $dataPegawai[$i]["pgw_id"];?>" <?php if($dataPegawai[$i]["pgw_id"]==$_POST["id_pgw"]) echo "selected";?>><?php echo $dataPegawai[$i]["pgw_nama"];?></option>
     			<?php } ?>
     		</select>
     	</td>
     	<?php } ?>
          <td width="15%" class="tablecontent">&nbsp;Periode</td>
          <td width="30%" class="tablecontent-odd">
     		<select onKeyDown="return tabOnEnter(this, event);" class="inputField" name="bulan">
     			<?php for($i=1;$i<=12;$i++){ ?>
     				<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["bulan"]) echo "selected";?>><?php echo $monthName[$i];?></option>
     			<?php } ?>
     		</select>
     		<select onKeyDown="return tabOnEnter(this, event);" class="inputField" name="tahun">
     			<?php for($i=2008,$akhirTahun=date("Y");$i<=$akhirTahun;$i++){ ?>
     				<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["tahun"]) echo "selected";?>><?php echo $i;?></option>
     			<?php } ?>
     		</select>
          </td>     
          <td class="tablecontent">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>
</table>

</form>

<BR/>
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
<input type="hidden" name="id_pgw" value="<?php echo $_POST["id_pgw"]; ?>" />
<input type="hidden" name="awal_gaji" value="<?php echo $awalGaji; ?>" />
<input type="hidden" name="akhir_gaji" value="<?php echo $akhirGaji; ?>" />
<input type="hidden" name="bulan" value="<?php echo $namaBulan; ?>" />
<input type="hidden" name="tahun" value="<?php echo $_POST["tahun"]; ?>" />
<input type="hidden" name="telat" value="<?php echo $telat; ?>" />
<input type="hidden" name="kekuranganlengkap" value="<?php echo $kekuranganLengkap; ?>" />
<input type="hidden" name="telatOut" value="<?php echo $telatOut; ?>" />
</form>
</body>
</html>
 
