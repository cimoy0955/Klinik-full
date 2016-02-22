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
         
         
	   if(!$auth->IsAllowed("pos_laba_rugi",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("pos_laba_rugi",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
         exit(1);
     }
   
     //ambil data Outlet
     $usrId = $auth->GetUserId(); 
   
     
     $skr = date("d-m-Y");
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     if(!$_POST["id_dep"]) $_POST["id_dep"]=$_POST["outlet"];
     if(!$_POST["id_petugas"]) $_POST["id_petugas"]="--";
     if(!$_POST["penjualan_tipe"]) $_POST["penjualan_tipe"]="--";
     $cetakPage = "labarugi_cetak.php?id_petugas=".$_POST["id_petugas"]."&tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"]."&penjualan_tipe=".$_POST["penjualan_tipe"];
     
     
     $tipe["W"] = "Pemasukan Warnet";
     $tipe["M"] = "Pemasukan Multiplayer";
     $tipe["P"] = "Pemasukan Point of Sale";
     $tipe["O"] = "Operasional";
     $tipe["A"] = "Kas Awal";
     
     $sql_where[] = "a.penjualan_create >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "a.penjualan_create <= ".QuoteValue(DPE_DATE,DateAdd(date_db($_POST["tanggal_akhir"]),1));
     if ($_POST["id_petugas"]<> "--") $sql_where[] = "a.id_petugas = ".QuoteValue(DPE_NUMERIC,$_POST["id_petugas"]);
     if ($_POST["penjualan_tipe"]<> "--") $sql_where[] = "a.penjualan_tipe = ".QuoteValue(DPE_CHAR,$_POST["penjualan_tipe"]);
     $sql_where = implode(" and ",$sql_where);
     
     $sql = "select distinct c.item_nama, c.id_item,c.transaksi_jumlah,c.transaksi_jumlah,c.transaksi_harga_beli,
             c.id_penjualan,c.transaksi_harga_jual,c.transaksi_total, 
             a.penjualan_create, a.penjualan_nomer,
             a.penjualan_petugas,a.penjualan_ppn,a.penjualan_id,
             a.penjualan_tipe, a.penjualan_total, a.penjualan_customer
             from pos_penjualan a 
             left join pos_transaksi c on c.id_penjualan = a.penjualan_id
             left join pos_item d on d.item_id = c.id_item";
     $sql .= " where ".$sql_where;
     $sql .= " order by a.penjualan_create asc";
     
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataTable = $dtaccess->FetchAll($rs);
  
  //*-- config table ---*//
  $table = new InoTable("table1","80%","left",null,0,2,1,null);     
  $PageHeader = "Laba Rugi";
  
  

	// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Waktu";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "No. Nota";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$counter++;
  
  $tbHeader[0][$counter][TABLE_ISI] = "";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$counter++;
	
  $counter=0;
	$tbHeader[1][$counter][TABLE_ISI] = "Detail";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "No.";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Menu";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Jumlah";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Harga Jual";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Harga Beli";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Laba Rugi";
	$tbHeader[1][$counter][TABLE_WIDTH] = "20%";
  $counter++;

	for($i=0,$m=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$m++,$counter=0){
	  //terjemahkan tipe
     if ($dataTable[$i]["penjualan_tipe"]=='T') $tipeBayar="TUNAI";
     else $tipeBayar="NON TUNAI";
     
	  
	  if($dataTable[$i]["penjualan_id"]!=$dataTable[$i-1]["penjualan_id"]){
	  //hitung total
	  $total+=$dataTable[$i]["penjualan_total"];
	  
	  //hitung total Tax
	  $totalTax+=$dataTable[$i]["penjualan_ppn"];
	  
		$tbContent[$m][$counter][TABLE_ISI] = $i+1;
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
	  
	  $tbContent[$m][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["penjualan_create"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomer"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
   
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
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
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;

		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["transaksi_jumlah"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
    
     $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["transaksi_harga_jual"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
    
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["transaksi_harga_beli"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;		
		
    $totalKeuntungan=$totalKeuntungan+($dataTable[$i]["transaksi_harga_jual"]-$dataTable[$i]["transaksi_harga_beli"])*$dataTable[$i]["transaksi_jumlah"];
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format(($dataTable[$i]["transaksi_harga_jual"]-$dataTable[$i]["transaksi_harga_beli"])*$dataTable[$i]["transaksi_jumlah"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
	
		
	}
	
	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[0][$counter][TABLE_COLSPAN]   = "2";

	$counter++;
	
	//$tbBottom[1][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnExcel" value="Export Excel" class="button" onClick="Export()">&nbsp;';
	//$tbBottom[1][$counter][TABLE_ALIGN]   = "left";

	//$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]     = "Total";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "right";
	$tbBottom[0][$counter][TABLE_COLSPAN] = 3;
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = '&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($totalKeuntungan);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	
	
  
  
  //Tipe Bayar  
  $tipe[0] = $view->RenderOption("--","[Pilih Tipe Bayar]",$show);
  unset($show);
  if($_POST["penjualan_tipe"]=='T') $show = "selected";
  $tipe[1] = $view->RenderOption("T","Tunai",$show);
  unset($show);
  if($_POST["penjualan_tipe"]=='N') $show = "selected";
  $tipe[2] = $view->RenderOption("N"," Non Tunai",$show);
  
  $sql = "select * from global_auth_user where id_rol <> ".ROLE_TIPE_MEMBER; 
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataUser = $dtaccess->FetchAll($rs);
  $usr[0] = $view->RenderOption("--","[Pilih Petugas]",$show);
  for($i=0,$n=count($dataUser);$i<$n;$i++){
         unset($show);
         if($_POST["id_petugas"]==$dataUser[$i]["usr_id"]) $show = "selected";
         $usr[$i+1] = $view->RenderOption($dataUser[$i]["usr_id"],$dataUser[$i]["usr_name"],$show);               
    } 
      
	
	
	$id_petugas=$_POST["id_petugas"];
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];
	$penjualanTipe=$_POST["penjualan_tipe"];
?>

<script language="Javascript" type="text/javascript">
function Export()
     {
          document.location.href = 'cashflow_harian_export.php?export=excel&id_petugas=<?php echo $id_petugas;?>&tanggal_awal=<?php echo $tglAwal?>&tanggal_akhir=<?php echo $tglAkhir?>&id_dep=<?php echo $id_dep?>&penjualan_tipe=<?php echo $penjualanTipe?>';
     }
</script>

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
     </tr>
     <tr>
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
 
