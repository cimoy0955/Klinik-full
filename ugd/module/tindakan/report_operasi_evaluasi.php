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
     $bln = date("m-Y");
     
	if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = "01-".$bln;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr; 
     
     if($_POST["tgl_awal"]) $sql_where[] = "ref_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "ref_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"])); 
     if($_POST["cust_usr_kode"]) $sql_where[] = "cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
     
	if($sql_where) $sql_where = implode(" and ",$sql_where);
	
     $sql = "select a.id_reg, a.ref_tanggal, h.op_id , 
			c.cust_usr_nama, c.cust_usr_kode,
			d.visus_nama as visus_koreksi_od, 
			e.visus_nama as visus_koreksi_os, 
			f.visus_nama as visus_nonkoreksi_od, 
			g.visus_nama as visus_nonkoreksi_os  
               from klinik.klinik_refraksi a 
               inner join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
			inner join klinik.klinik_visus d on a.id_visus_koreksi_od = d.visus_id
			inner join klinik.klinik_visus e on a.id_visus_koreksi_os = e.visus_id
			inner join klinik.klinik_visus f on a.id_visus_nonkoreksi_od = f.visus_id
			inner join klinik.klinik_visus g on a.id_visus_nonkoreksi_os = g.visus_id
			left join klinik.klinik_operasi h on a.id_reg = h.id_reg";
	if($sql_where) $sql .= " where ".$sql_where;
	$sql .= " order by cust_usr_kode, ref_tanggal"; 
     $dataTable = $dtaccess->FetchAll($sql);
     // -- end ---
	
	$counter=0;
		
     $tbHeader[0][0][TABLE_ISI] = "No";
     $tbHeader[0][0][TABLE_WIDTH] = "1%";
     $tbHeader[0][0][TABLE_ROWSPAN] = "2";
	
     $tbHeader[0][1][TABLE_ISI] = "No. Reg";
     $tbHeader[0][1][TABLE_WIDTH] = "7%";
     $tbHeader[0][1][TABLE_ROWSPAN] = "2";
	
     $tbHeader[0][2][TABLE_ISI] = "Nama";
     $tbHeader[0][2][TABLE_WIDTH] = "15%"; 
     $tbHeader[0][2][TABLE_ROWSPAN] = "2";
	
     $tbHeader[0][3][TABLE_ISI] = "Tanggal";
     $tbHeader[0][3][TABLE_WIDTH] = "5%";  
     $tbHeader[0][3][TABLE_ROWSPAN] = "2";
	
     $tbHeader[0][4][TABLE_ISI] = "OP";
     $tbHeader[0][4][TABLE_WIDTH] = "5%";  
     $tbHeader[0][4][TABLE_ROWSPAN] = "2";
	
     $tbHeader[0][5][TABLE_ISI] = "Visus Tanpa Koreksi";
     $tbHeader[0][5][TABLE_WIDTH] = "5%";  
     $tbHeader[0][5][TABLE_COLSPAN] = "2";
	
     $tbHeader[0][6][TABLE_ISI] = "Visus Dengan Koreksi";
     $tbHeader[0][6][TABLE_WIDTH] = "5%";  
     $tbHeader[0][6][TABLE_COLSPAN] = "2";
	
     $tbHeader[1][0][TABLE_ISI] = "OD";
     $tbHeader[1][0][TABLE_WIDTH] = "5%";  

     $tbHeader[1][1][TABLE_ISI] = "OS";
     $tbHeader[1][1][TABLE_WIDTH] = "5%";  

     $tbHeader[1][2][TABLE_ISI] = "OD";
     $tbHeader[1][2][TABLE_WIDTH] = "5%";  

     $tbHeader[1][3][TABLE_ISI] = "OS";
     $tbHeader[1][3][TABLE_WIDTH] = "5%";  

     for($i=0,$counter=0,$baris=1,$n=count($dataTable);$i<$n;$i++,$counter=0,$countRow++){
		$class = ($baris%2==0) ? "tablecontent":"tablecontent-odd";
		
		if($dataTable[$i]["cust_usr_kode"]!=$dataTable[$i-1]["cust_usr_kode"]){

			$barisSpan = $i;
			$countRow=1;
			
			$tbContent[$i][$counter][TABLE_ISI] = $baris;
			$tbContent[$i][$counter][TABLE_ALIGN] = "right";
			$tbContent[$i][$counter][TABLE_CLASS] = $class;
			$counter++;
		
			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
			$tbContent[$i][$counter][TABLE_CLASS] = $class;
			$counter++;
		
			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
			$tbContent[$i][$counter][TABLE_CLASS] = $class;
	          $counter++;
		}

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["ref_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
          $counter++; 
	
		if($dataTable[$i]["op_id"]) $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="15" height="15" src="'.$APLICATION_ROOT.'images/off.gif" alt="Operasi Hari Ini" title="Operasi Hari Ini" border="0"/>';
		else $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
          $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["visus_koreksi_od"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
          $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["visus_koreksi_os"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
          $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["visus_nonkoreksi_od"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
          $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["visus_nonkoreksi_os"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
          $counter++; 

		if($dataTable[$i]["cust_usr_kode"]!=$dataTable[$i+1]["cust_usr_kode"]){
			$tbContent[$barisSpan][0][TABLE_ROWSPAN] = $countRow;
			$tbContent[$barisSpan][1][TABLE_ROWSPAN] = $countRow;
			$tbContent[$barisSpan][2][TABLE_ROWSPAN] = $countRow;
			$baris++;
		}		
     }
     
	
	$tbBottom[0][0][TABLE_ISI] = "";
	$tbBottom[0][0][TABLE_ALIGN] = "right";
	$tbBottom[0][0][TABLE_COLSPAN] = 9;
	$counter++;
     
     $tableHeader = "Report Evaluasi Operasi";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_operasi_evaluasi_'.$_POST["tgl_awal"].'.xls');
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
          <td width="10%" class="tablecontent">&nbsp;No. RM</td>
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
