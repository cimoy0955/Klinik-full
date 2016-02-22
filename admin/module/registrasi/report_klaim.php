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
 
     $thisPage = "report_klaim.php";

     if(!$auth->IsAllowed("report_klaim",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_klaim",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     

	$sql = "select * from klinik.klinik_split order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
     
          
     $skr = date("d-m-Y");
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr;
     if(!$_POST["cust_usr_jenis"]) $_POST["cust_usr_jenis"] = PASIEN_BAYAR_ASKES;
     
     if($_POST["tgl_awal"]) $sql_where[] = "CAST(a.reg_klaim_when as DATE) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "CAST(a.reg_klaim_when as DATE) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     if($_POST["cust_usr_jenis"]) $sql_where[] = "b.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
     if($_POST["cust_usr_kode"]) $sql_where[] = "d.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
     
	$sql_where = implode(" and ",$sql_where);
	
     $sql = "select a.reg_klaim_nominal, cast(a.reg_klaim_when as date) as tanggal, b.reg_id, 
               c.paket_klaim_nama, d.cust_usr_nama, d.cust_usr_kode, e.tot_klaim, e.span 
               from klinik.klinik_registrasi_klaim a 
               join klinik.klinik_registrasi b on a.id_reg = b.reg_id
               join klinik.klinik_paket_klaim c on c.paket_klaim_id = a.id_paket_klaim
               join global.global_customer_user d on b.id_cust_usr = d.cust_usr_id
               join (
                    select id_reg, count(reg_klaim_id) as span, sum(reg_klaim_nominal) as tot_klaim
                    from klinik.klinik_registrasi_klaim
                    group by id_reg
               ) as e on e.id_reg = b.reg_id";
			
	$sql .= " where ".$sql_where." order by a.reg_klaim_when, d.cust_usr_nama, c.paket_klaim_nama"; 
     $dataTable = $dtaccess->FetchAll($sql);    
	
	$counter=0;
		
     $tbHeader[0][0][TABLE_ISI] = "No";
     $tbHeader[0][0][TABLE_WIDTH] = "3%";
	
     $tbHeader[0][1][TABLE_ISI] = "No. Reg";
     $tbHeader[0][1][TABLE_WIDTH] = "7%";
	
     $tbHeader[0][2][TABLE_ISI] = "Nama";
     $tbHeader[0][2][TABLE_WIDTH] = "30%"; 
	
     $tbHeader[0][3][TABLE_ISI] = "Tanggal";
     $tbHeader[0][3][TABLE_WIDTH] = "15%"; 
	
     $tbHeader[0][4][TABLE_ISI] = "Jenis Layanan";
     $tbHeader[0][4][TABLE_WIDTH] = "15%"; 
     
     $tbHeader[0][5][TABLE_ISI] = "Nominal";
     $tbHeader[0][5][TABLE_WIDTH] = "15%";
	
     $tbHeader[0][6][TABLE_ISI] = "Sub Total";
     $tbHeader[0][6][TABLE_WIDTH] = "15%";
	

     for($i=0,$no=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
     
          if($dataTable[$i]["reg_id"]!=$dataTable[$i-1]["reg_id"]) {
          
               $class = ($no%2==0)?"tablecontent-odd":"tablecontent";
          
               $tbContent[$i][$counter][TABLE_ISI] = $no+1;
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $tbContent[$i][$counter][TABLE_VALIGN] = "middle";
               $tbContent[$i][$counter][TABLE_ROWSPAN] = $dataTable[$i]["span"];
               $tbContent[$i][$counter][TABLE_CLASS] = $class;
               $counter++;
     	
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $tbContent[$i][$counter][TABLE_VALIGN] = "middle";
               $tbContent[$i][$counter][TABLE_ROWSPAN] = $dataTable[$i]["span"];
               $tbContent[$i][$counter][TABLE_CLASS] = $class;
               $counter++;
     	
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $tbContent[$i][$counter][TABLE_VALIGN] = "middle";
               $tbContent[$i][$counter][TABLE_ROWSPAN] = $dataTable[$i]["span"];
               $tbContent[$i][$counter][TABLE_CLASS] = $class;
               $counter++;
     	
               $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["tanggal"]);
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $tbContent[$i][$counter][TABLE_VALIGN] = "middle";
               $tbContent[$i][$counter][TABLE_ROWSPAN] = $dataTable[$i]["span"];
               $tbContent[$i][$counter][TABLE_CLASS] = $class;
               $counter++;
               
               $tbContent[$i][$counter+2][TABLE_ISI] = currency_format($dataTable[$i]["tot_klaim"]);
               $tbContent[$i][$counter+2][TABLE_ALIGN] = "right";
               $tbContent[$i][$counter+2][TABLE_VALIGN] = "middle";
               $tbContent[$i][$counter+2][TABLE_ROWSPAN] = $dataTable[$i]["span"];
               $tbContent[$i][$counter+2][TABLE_CLASS] = $class;
               
               $no++;
          }
     	
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["paket_klaim_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_CLASS] = $class;
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["reg_klaim_nominal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $tbContent[$i][$counter][TABLE_CLASS] = $class;
          $counter++;
		
		$total += $dataTable[$i]["reg_klaim_nominal"];
     }
     
     $counter = 0;
	$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
     $tbBottom[0][$counter][TABLE_COLSPAN] = 6;
     $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI] = currency_format($total);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
     
     $tableHeader = "Report Biaya Klaim";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran_klaim_'.$_POST["tgl_awal"].'.xls');
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

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td colspan="7">&nbsp;<?php echo $tableHeader;?></td>
     </tr>
</table>

<?php if(!$_POST["btnExcel"]) { ?>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
     <tr>
          <td width="10%" class="tablecontent">&nbsp;Kode</td>
          <td width="40%" class="tablecontent-odd">
               <input type="text" id="cust_usr_kode" name="cust_usr_kode" size="15" maxlength="10" value="<?php echo $_POST["cust_usr_kode"];?>"/>
          </td>
		<td class="tablecontent" width="10%">Jenis Pasien</td>
          <td class="tablecontent-odd" width="40%">
			<select name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);">
                    <?php foreach($bayarPasien as $key => $value) { 
                         if($key!=PASIEN_BAYAR_SWADAYA) {?>
                              <option value="<?php echo $key;?>" <?php if($_POST["cust_usr_jenis"]==$key) echo "selected";?>><?php echo $value;?></option>
                         <?php }
                    } ?>
			</select>
		</td>
	</tr>
	<tr>
          <td width="10%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="40%" class="tablecontent-odd" colspan="3">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               -
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />               
          </td>
	</tr>
	<tr>
          <td class="tablecontent" colspan="6">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
			<input type="submit" name="btnExcel" value="Export Excel" class="button">
          </td>
     </tr>
</table>

<?php echo $view->SetFocus('cust_usr_kode');?>
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

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>


<?php echo $view->RenderBodyEnd(); ?>
