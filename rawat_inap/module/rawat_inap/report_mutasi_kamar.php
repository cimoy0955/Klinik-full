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

    if(!$auth->IsAllowed("rawat_inap",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("rawat_inap",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
     
	   $cetakPage = "report_mutasi_kamar_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"];
	$skr = date("d-m-Y");
	if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
	if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;

	if ($_POST["tanggal_awal"] && !$_POST["tanggal_akhir"]) $sql_where[] = "a.mutasi_tanggal = ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));

	if ($_POST["tanggal_awal"] && $_POST["tanggal_akhir"]) $sql_where[] = "a.mutasi_tanggal between ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));

	$sql_where[] = "mutasi_kamar_id is not null";
    
    if ($sql_where[0]) 
	$sql_where = implode(" and ",$sql_where);
     
	$sql = "select mutasi_kamar_id,mutasi_tanggal,cust_usr_nama,cust_usr_kode,
          e.kategori_nama as klas1,f.kamar_kode as kode_kamar1 ,f.kamar_nama as nama_kamar1,g.bed_kode as bed1,
          klas2, nama_kamar2, bed2
          from klinik.klinik_mutasi_kamar a
          left join klinik.klinik_registrasi b on b.reg_id = a.id_reg
          left join global.global_customer_user c on b.id_cust_usr = c.cust_usr_id
          left join klinik.klinik_kamar_kategori e on e.kategori_id = a.id_kategori
          left join klinik.klinik_kamar f on f.kamar_id = a.id_kamar
          left join klinik.klinik_kamar_bed g on g.bed_id = a.id_bed
          left join ( select kategori_nama as klas2,kategori_id from klinik.klinik_kamar_kategori ) h on h.kategori_id = a.id_kategori_tujuan
          left join ( select kamar_nama as nama_kamar2,kamar_id from klinik.klinik_kamar ) i on i.kamar_id = a.id_kamar_tujuan
          left join ( select bed_kode as bed2,bed_id from klinik.klinik_kamar_bed ) j on j.bed_id = a.id_bed_tujuan";
            
    if ($sql_where) 
	$sql .= " where ".$sql_where;
	$sql .= " order by mutasi_tanggal asc";
	//echo $sql;
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataTable = $dtaccess->FetchAll($rs);

	//*-- config table ---*//
	$table = new InoTable("table1","100%","left",null,0,2,1,null);     
	$PageHeader = "LAPORAN MUTASI KAMAR";

	// --- construct new table ---- //
	$tbHeader[0][0][TABLE_ISI] = "No";
	$tbHeader[0][0][TABLE_WIDTH] = "3%";

	$tbHeader[0][1][TABLE_ISI] = "Tanggal";
	$tbHeader[0][1][TABLE_WIDTH] = "10%";

	$tbHeader[0][2][TABLE_ISI] = "Kode Pasien";
	$tbHeader[0][2][TABLE_WIDTH] = "10%";

	$tbHeader[0][3][TABLE_ISI] = "Nama Pasien";
	$tbHeader[0][3][TABLE_WIDTH] = "20%";

	$tbHeader[0][4][TABLE_ISI] = "Klas Asal";
	$tbHeader[0][4][TABLE_WIDTH] = "10%";

	$tbHeader[0][5][TABLE_ISI] = "Kamar Asal";
	$tbHeader[0][5][TABLE_WIDTH] = "10%";

	$tbHeader[0][6][TABLE_ISI] = "Bed Asal";
	$tbHeader[0][6][TABLE_WIDTH] = "10%";

	$tbHeader[0][7][TABLE_ISI] = "Mutasi Ke Klas";
	$tbHeader[0][7][TABLE_WIDTH] = "20%";

	$tbHeader[0][8][TABLE_ISI] = "Mutasi Ke Kamar";
	$tbHeader[0][8][TABLE_WIDTH] = "20%";

	$tbHeader[0][9][TABLE_ISI] = "Mutasi Ke Bed";
	$tbHeader[0][9][TABLE_WIDTH] = "20%";


	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){		
	
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["mutasi_tanggal"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["klas1"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nama_kamar1"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed1"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["klas2"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nama_kamar2"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed2"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

	}
		
	$tbBottom[0][0][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[0][0][TABLE_ALIGN]   = "left";
	$tbBottom[0][0][TABLE_COLSPAN] = 8;	
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Laporan Mutasi Kamar</td>
	</tr>
</table> 
<br>
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" >
	<tr>
		<td width="10%" class="tablecontent">&nbsp;Periode</td>
		<td width="30%">
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


		
<BR>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</body>
</html>
 
