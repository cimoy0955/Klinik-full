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
          
         
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}

     $skr = date("d-m-Y");
     
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     if(!$_POST["saran_tipe"]) $_POST["saran_tipe"]="--";
     
     $cetakPage = "saran_kritik_cetak.php?saran_tipe=".$_POST["saran_tipe"]."&tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"];
     
     $tipe["W"] = "Warnet";
     $tipe["M"] = "Multiplayer";
     
     
     $sql_where[] = "a.saran_create >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "a.saran_create <= ".QuoteValue(DPE_DATE,DateAdd(date_db($_POST["tanggal_akhir"]),1));
     if ($_POST["saran_tipe"]<> "--") $sql_where[] = "a.saran_tipe = ".QuoteValue(DPE_CHAR,$_POST["saran_tipe"]);
     $sql_where = implode(" and ",$sql_where);
     
     $sql = "select * from mp_saran a left join mp_member b on a.id_member=b.member_id";
     $sql .= " where ".$sql_where;
     $sql .= " order by a.saran_create asc";
     
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataTable = $dtaccess->FetchAll($rs);

     //*-- config table ---*//
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "Saran dan Kritik";

	// --- construct new table ---- //
	$tbHeader[0][0][TABLE_ISI] = "No";
	$tbHeader[0][0][TABLE_WIDTH] = "5%";

	$tbHeader[0][1][TABLE_ISI] = "Waktu";
	$tbHeader[0][1][TABLE_WIDTH] = "20%";

	$tbHeader[0][2][TABLE_ISI] = "Nama Member";
	$tbHeader[0][2][TABLE_WIDTH] = "20%";

	$tbHeader[0][3][TABLE_ISI] = "Masukan";
	$tbHeader[0][3][TABLE_WIDTH] = "40%";

	$tbHeader[0][4][TABLE_ISI] = "Tipe";
	$tbHeader[0][4][TABLE_WIDTH] = "10%";
	



	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	  
		if ($dataTable[$i]["member_nama"])
		     $namaMember=$dataTable[$i]["member_nama"];
		else
         $namaMember="GUEST";     
	  
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	  
	  $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["saran_create"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $namaMember;
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["saran_isi"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $tipe[$dataTable[$i]["saran_tipe"]];
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		
	}

		
	$tbBottom[0][0][TABLE_ISI]     = "Total Pendapatan: ";
	$tbBottom[0][0][TABLE_ALIGN]   = "right";
	$tbBottom[0][0][TABLE_COLSPAN] = 4;

	$tbBottom[0][1][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[0][1][TABLE_ALIGN]   = "right";
	$tbBottom[0][1][TABLE_COLSPAN] = 2;
	

  $tipe[0] = $view->RenderOption("--","[Pilih Tipe]",$show);
  if($_POST["saran_tipe"]=="M") $show = "selected";
  $tipe[1] = $view->RenderOption("M","Multiplayer",$show);
  unset($show);
  if($_POST["saran_tipe"]=="W") $show = "selected";
  $tipe[2] = $view->RenderOption("W","Warnet",$show);
  unset($show);
	
?>

<?php echo $view->RenderBody("inventori.css",true); ?>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td>&nbsp;<?php echo $PageHeader;?></td>
     </tr>
</table>

<BR/>


<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td width="10%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="27%">
			<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
               - 
			<?php echo $view->RenderTextBox("tanggal_akhir","tanggal_akhir","12","12",$_POST["tanggal_akhir"],"inputField", "readonly",false);?>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/></td>
			<td width="10%" class="tablecontent">&nbsp;Tipe</td>
          <td width="27%">
      <?php echo $view->RenderComboBox("saran_tipe","saran_tipe",$tipe,null,null,false);?> 
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
 
