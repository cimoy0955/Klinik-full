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
     if(!$_POST["id_petugas"]) $_POST["id_petugas"]="--";
     $cetakPage = "cashflow_harian_cetak.php?id_petugas=".$_POST["id_petugas"]."&tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"];
     
     
     $tipe["W"] = "Pemasukan Warnet";
     $tipe["M"] = "Pemasukan Multiplayer";
     $tipe["I"] = "Pemasukan Wifi";
     $tipe["P"] = "Pemasukan Point of Sale";
     $tipe["O"] = "Operasional";
     $tipe["A"] = "Kas Awal";
     
     $sql_where[] = "a.trans_create >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "a.trans_create <= ".QuoteValue(DPE_DATE,DateAdd(date_db($_POST["tanggal_akhir"]),1));
     if ($_POST["id_petugas"]<> "--") $sql_where[] = "a.id_petugas = ".QuoteValue(DPE_NUMERIC,$_POST["id_petugas"]);
     $sql_where = implode(" and ",$sql_where);
     
     $sql = "select a.trans_nama, a.trans_ket,a.trans_create, a.trans_petugas,a.trans_harga_total, c.usr_loginname, a.trans_jenis, c.usr_name    
               from mp_member_trans a
               left join mp_member b on a.id_member = b.member_id 
               left join global_auth_user c on b.id_usr = c.usr_id ";
     $sql .= " where ".$sql_where;
     $sql .= " order by a.trans_jenis,a.trans_create asc";

	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataTable = $dtaccess->FetchAll($rs);

     //*-- config table ---*//
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "CashFlow Harian";

	// --- construct new table ---- //
	$tbHeader[0][0][TABLE_ISI] = "No";
	$tbHeader[0][0][TABLE_WIDTH] = "5%";

	$tbHeader[0][1][TABLE_ISI] = "Waktu";
	$tbHeader[0][1][TABLE_WIDTH] = "20%";

	$tbHeader[0][2][TABLE_ISI] = "Nama";
	$tbHeader[0][2][TABLE_WIDTH] = "30%";

	$tbHeader[0][3][TABLE_ISI] = "Pemasukan";
	$tbHeader[0][3][TABLE_WIDTH] = "15%";

	$tbHeader[0][4][TABLE_ISI] = "Pengeluaran";
	$tbHeader[0][4][TABLE_WIDTH] = "10%";
	
	$tbHeader[0][5][TABLE_ISI] = "Keterangan";
	$tbHeader[0][5][TABLE_WIDTH] = "20%";
	
	$tbHeader[0][6][TABLE_ISI] = "Petugas";
	$tbHeader[0][6][TABLE_WIDTH] = "10%";


	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	  if ($dataTable[$i]["trans_jenis"]=='I' || $dataTable[$i]["trans_jenis"]=='W' || $dataTable[$i]["trans_jenis"]=='M') {
	  $nama = ($dataTable[$i]["usr_loginname"]) ? $dataTable[$i]["trans_nama"]." (".$dataTable[$i]["usr_loginname"].")" : "GUEST(".$dataTable[$i]["trans_nama"].")"; 
    } else { $nama=$dataTable[$i]["trans_nama"]; }
	     
		if ($dataTable[$i]["trans_jenis"]=='O') 
    { 
        $total -= $dataTable[$i]["trans_harga_total"]; 
        $totalPengeluaran += $dataTable[$i]["trans_harga_total"];
        $kolomPendapatan="";
        $kolomPengeluaran=$dataTable[$i]["trans_harga_total"];
    } else {
		    $total += $dataTable[$i]["trans_harga_total"]; 
		    $totalPendapatan += $dataTable[$i]["trans_harga_total"];
        $kolomPendapatan=$dataTable[$i]["trans_harga_total"];
        $kolomPengeluaran="";
    }
		
		if ($dataTable[$i]["trans_jenis"]!='O') {
		    $keterangan=$tipe[$dataTable[$i]["trans_jenis"]]; 
    } else {
		    $keterangan=$dataTable[$i]["trans_ket"];
		}
		
	
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	  
	  $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["trans_create"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $nama;
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $kolomPendapatan;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $kolomPengeluaran;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $keterangan;
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["trans_petugas"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;
	}

		
	$tbBottom[0][0][TABLE_ISI]     = "&nbsp";
	$tbBottom[0][0][TABLE_ALIGN]   = "right";
	$tbBottom[0][0][TABLE_COLSPAN] = 3;

	$tbBottom[0][1][TABLE_ISI]   = currency_format($totalPendapatan);
	$tbBottom[0][1][TABLE_ALIGN] = "right";
	
  $tbBottom[0][2][TABLE_ISI]   = currency_format($totalPengeluaran);
	$tbBottom[0][2][TABLE_ALIGN] = "right";
	
	$tbBottom[0][3][TABLE_ISI]     = "&nbsp;";
	$tbBottom[0][3][TABLE_ALIGN]   = "right";
	$tbBottom[0][3][TABLE_COLSPAN] = 2;
	
	$tbBottom[1][0][TABLE_ISI]     = "Total Pendapatan: ";
	$tbBottom[1][0][TABLE_ALIGN]   = "right";
	$tbBottom[1][0][TABLE_COLSPAN] = 4;

	$tbBottom[1][1][TABLE_ISI]   = currency_format($total);
	$tbBottom[1][1][TABLE_ALIGN] = "right";

	$tbBottom[1][2][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[1][2][TABLE_ALIGN]   = "right";
	$tbBottom[1][2][TABLE_COLSPAN] = 2;
	
	$sql = "select * from global_auth_user where id_rol <> ".ROLE_TIPE_MEMBER; 
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataUser = $dtaccess->FetchAll($rs);
  $usr[0] = $view->RenderOption("--","[Pilih Petugas]",$show);
  for($i=0,$n=count($dataUser);$i<$n;$i++){
         unset($show);
         if($_POST["id_petugas"]==$dataUser[$i]["usr_id"]) $show = "selected";
         $usr[$i+1] = $view->RenderOption($dataUser[$i]["usr_id"],$dataUser[$i]["usr_name"],$show);               
    } 
	
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
          <td width="10%" class="tablecontent">&nbsp;Periode</td>
          <td width="27%">
			<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
               - 
			<?php echo $view->RenderTextBox("tanggal_akhir","tanggal_akhir","12","12",$_POST["tanggal_akhir"],"inputField", "readonly",false);?>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/></td>
			<td width="10%" class="tablecontent">&nbsp;Petugas</td>
          <td width="27%">
      <?php echo $view->RenderComboBox("id_petugas","id_petugas",$usr,null,null,false);?>    
          </td>
          <td class="tablecontent">
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
 
