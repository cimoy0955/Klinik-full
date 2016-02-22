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
     $skr = format_date(getDateToday());
     
 /*   if(!$auth->IsAllowed("report_rawat_inap",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("report_rawat_inap",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
  */   
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;

    $cetakPage = "report_pengaduan_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"]."&penjualan_tipe=".$_POST["penjualan_tipe"];
     
     if ($_POST["tanggal_awal"]) $sql_where[] = " rawatinap_tanggal_masuk <=".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     #if ($_POST["tanggal_akhir"]) $sql_where[] = " rawatinap_tanggal_keluar <=".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     $sql_where[] = " a.reg_status like '".STATUS_RAWATINAP."%' ";
     $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin, 
               a.reg_jenis_pasien, a.reg_status_pasien, a.reg_waktu, c.rawatinap_id, c.rawatinap_tanggal_masuk, c.rawatinap_diet, c.rawatinap_tanggal_keluar, c.rawatinap_waktu_masuk , c.rawatinap_waktu_keluar,
                d.kamar_nama, e.kategori_nama , e.kategori_harga, f.bed_kode, g.biaya_nama
               from klinik.klinik_registrasi a
               join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               left join klinik.klinik_rawatinap c on c.id_reg = a.reg_id
               left join klinik.klinik_kamar d on d.kamar_id = c.id_kamar
               left join klinik.klinik_kamar_kategori e on e.kategori_id = c.id_kategori_kamar
               left join klinik.klinik_kamar_bed f on f.bed_id = c.id_bed
	       left join klinik.klinik_biaya g on g.biaya_jenis = c.id_kategori_kamar";
     if($sql_where) $sql .= " where ".implode(" and ",$sql_where)."and rawatinap_tanggal_keluar is null";
     $sql .= " order by rawatinap_tanggal_masuk asc";
     
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
	   $dataTable = $dtaccess->FetchAll($rs);
	   
     //*-- config table ---*//
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "Laporan Rawat Inap Harian";

		// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
	  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Tanggal Masuk";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Nama";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Jenis Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Kelas";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Kamar";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Bed";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
    	
	$tbHeader[0][$counter][TABLE_ISI] = "Diet Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
    	
  for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	  
	  
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	
	  $tbContent[$i][$counter][TABLE_ISI] = format_date_long($dataTable[$i]["rawatinap_tanggal_masuk"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $bayarPasien[$dataTable[$i]["reg_jenis_pasien"]];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["biaya_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kamar_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $dietPasien[$dataTable[$i]["rawatinap_diet"]];;
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
	}

/*	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "right";
	$tbBottom[0][$counter][TABLE_COLSPAN] = count($tbHeader[0]);
	$counter++;
	*/
	
	
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
		<td width="10%" class="tablecontent">&nbsp;Tanggal</td>
		<td width="30%">
			<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
			<!-- 
			<?php echo $view->RenderTextBox("tanggal_akhir","tanggal_akhir","12","12",$_POST["tanggal_akhir"],"inputField", "readonly",false);?>
			<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/></td>		 
	  <td>-->
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
    /*
    Calendar.setup({
        inputField     :    "tanggal_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });*/
</script>


		
<BR />
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</body>
</html>
 
