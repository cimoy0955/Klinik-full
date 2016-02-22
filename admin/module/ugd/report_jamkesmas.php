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
 
     $thisPage = "report_jamkesmas.php";

     if(!$auth->IsAllowed("report_jamkesmas",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_jamkesmas",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     

	$sql = "select * from klinik.klinik_split order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
      
     $skr = date("d-m-Y");
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr; 
     
     if($_POST["tgl_awal"]) $sql_where[] = "CAST(reg_ina_when as DATE) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "CAST(reg_ina_when as DATE) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"])); 
     if($_POST["cust_usr_kode"]) $sql_where[] = "cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
     
	$sql_where = implode(" and ",$sql_where);
	
     $sql = "select a.reg_ina_id, c.cust_usr_nama, c.cust_usr_kode, reg_ina_nominal, 
               CAST(a.reg_ina_when as DATE) as tanggal 
               from klinik.klinik_registrasi_ina a 
               inner join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id 
			join klinik.klinik_registrasi d on d.reg_id = a.id_reg and a.id_cust_usr = d.id_cust_usr ";
	$sql .= " where ".$sql_where; 
     $dataTable = $dtaccess->FetchAll($sql);
     // -- end ---

     $sql = "select b.*, c.id_split 
               from klinik.klinik_registrasi_ina_split b
               inner join klinik.klinik_registrasi_ina a on b.id_reg_ina = a.reg_ina_id
			join klinik.klinik_registrasi d on d.reg_id = a.id_reg and d.id_cust_usr = a.id_cust_usr
			join klinik.klinik_ina_split c on c.ina_split_id = b.id_ina_split
			join global.global_customer_user e on e.cust_usr_id = a.id_cust_usr ";
	$sql .= " where ".$sql_where;
	$rs = $dtaccess->Execute($sql);
	while($row = $dtaccess->Fetch($rs)) { 
		$dataFolSplit[$row["id_reg_ina"]][$row["id_split"]] = $row["reg_ina_split_nominal"];
	}
	
	$counter=0;
		
     $tbHeader[0][0][TABLE_ISI] = "No";
     $tbHeader[0][0][TABLE_WIDTH] = "1%";
	
     $tbHeader[0][1][TABLE_ISI] = "No. Reg";
     $tbHeader[0][1][TABLE_WIDTH] = "7%";
	
     $tbHeader[0][2][TABLE_ISI] = "Nama";
     $tbHeader[0][2][TABLE_WIDTH] = "15%"; 
	
     $tbHeader[0][3][TABLE_ISI] = "Tanggal";
     $tbHeader[0][3][TABLE_WIDTH] = "5%";  
	
	for($i=0,$n=count($dataSplit);$i<$n;$i++){
		$tbHeader[0][$i+4][TABLE_ISI] = $dataSplit[$i]["split_nama"];
		$tbHeader[0][$i+4][TABLE_WIDTH] = "10%";
	} 
	
     $tbHeader[0][$n+4][TABLE_ISI] = "Total";
     $tbHeader[0][$n+4][TABLE_WIDTH] = "20%";
	

     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
	
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
	
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
	
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
	
		for($j=0,$k=count($dataSplit);$j<$k;$j++){
			$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataFolSplit[$dataTable[$i]["reg_ina_id"]][$dataSplit[$j]["split_id"]]);
			$tbContent[$i][$counter][TABLE_ALIGN] = "right";
			$counter++;
			$totSplit[$dataSplit[$j]["split_id"]] += $dataFolSplit[$dataTable[$i]["reg_ina_id"]][$dataSplit[$j]["split_id"]];
		}

          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["reg_ina_nominal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
		
		$total += $dataTable[$i]["reg_ina_nominal"];
     }
     
     $counter = 0;
	$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
     $tbBottom[0][$counter][TABLE_COLSPAN] = 4;
     $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	for($i=0,$n=count($dataSplit);$i<$n;$i++){
		$tbBottom[0][$counter][TABLE_ISI] = currency_format($totSplit[$dataSplit[$i]["split_id"]]);
		$tbBottom[0][$counter][TABLE_ALIGN] = "right";
		$counter++;
	}

	
	$tbBottom[0][$counter][TABLE_ISI] = currency_format($total);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
     
     $tableHeader = "Report Klaim JamKesMas Pusat";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran_klaim_jamkesmas_'.$_POST["tgl_awal"].'.xls');
     }
	 
?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>

<script language="JavaScript">
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Awal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }

     if(!CheckDate(frm.tgl_akhir.value)) {
          return false;
     }
}

</script>

<?php if(!$_POST["btnExcel"]) { ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td colspan="<?php echo (count($dataSplit)+6)?>">&nbsp;<?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
     <tr>
          <td width="10%" class="tablecontent">&nbsp;Kode</td>
          <td width="80%" class="tablecontent-odd">
               <input type="text" id="cust_usr_kode" name="cust_usr_kode" size="15" maxlength="10" value="<?php echo $_POST["cust_usr_kode"];?>"/>
          </td> 
	</tr>
	<tr>
          <td width="10%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="80%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               -
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />               
          </td>
	</tr>
	<tr>
          <td class="tablecontent" colspan="2">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
			<input type="submit" name="btnExcel" value="Export Excel" class="button">
          </td>
     </tr>
</table>

<BR>

</form>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php } ?>

<?php if($_POST["btnExcel"]) {?>
     <table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="<?php echo (count($dataSplit)+5)?>"><strong>REPORT KLAIM JAMKESMAS PUSAT</strong></td>
          </tr>
     </table>
<?php }?>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>


<?php echo $view->RenderBodyEnd(); ?>
