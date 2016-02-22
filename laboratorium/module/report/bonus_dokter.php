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
     $cetakPage = "bonus_dokter_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"];
      
	   $sql_where[] = "a.bonus_hasil_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "a.bonus_hasil_tanggal <= ".QuoteValue(DPE_DATE,DateAdd(date_db($_POST["tanggal_akhir"]),1));
     
     //$sql_where[] = "c.item_nama is not null";
     
       if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);
     
     $sql = "select * from laboratorium.lab_hasil_bonus a
             left join laboratorium.lab_dokter b on b.dokter_id = a.id_dokter
             left join laboratorium.lab_kegiatan c on c.kegiatan_id = a.id_kegiatan 
             left join laboratorium.lab_kategori d on d.kategori_id = c.id_kategori
             left join laboratorium.lab_bonus e on c.id_bonus = e.bonus_id";
      $sql .= " where ".$sql_where;
     $sql .= " order by a.bonus_hasil_tanggal asc";        
     //echo $sql;        
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
	   $dataTable = $dtaccess->FetchAll($rs);
	   
     //*-- config table ---*//
     $table = new InoTable("table1","95%","left",null,0,2,1,null);     
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
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[0][$counter][TABLE_COLSPAN]   = "2";

	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]     = "Total Bonus";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "center";
	$tbBottom[0][$counter][TABLE_COLSPAN]   = 2;
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = "Rp.&nbsp;".currency_format($jml);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
      
	
	
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];
	
	
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
 
