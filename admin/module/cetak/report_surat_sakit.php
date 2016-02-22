<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $thisPage = "report_surat_sakit.php";

     if(!$auth->IsAllowed("report_surat_sakit",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_surat_sakit",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y");
      
     if($_POST["tgl_awal"]) $sql_where[] = "cast(surat_sakit_when as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));  
     if($_POST["tgl_akhir"]) $sql_where[] = "cast(surat_sakit_when as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));  

     $sql = "select b.cust_usr_kode, b.cust_usr_nama, cust_usr_tanggal_lahir, a.*, pgw_nama,cast(surat_sakit_when as date) as tanggal
               from klinik.klinik_surat_ket_sakit a 
               join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               join hris.hris_pegawai c on c.pgw_id = a.id_pgw ";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " order by surat_sakit_when, cust_usr_kode"; 
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Surat Keterangan Sakit"; 
     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Ijin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Lama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Surat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";     
     $counterHeader++; 
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){ 
          $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = HitungUmur($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["surat_sakit_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["surat_sakit_lama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pgw_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  
     }
     
     $colspan = count($tbHeader[0]);
     
     if(!$_POST["btnExcel"]){
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
          $tbBottom[0][0][TABLE_ALIGN] = "center";
     }
     
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_surat_sakit_'.$_POST["tgl_awal"].'.xls');
     }

?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>
<script language="JavaScript">
function CheckSimpan(frm) { 

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
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<?php if(!$_POST["btnExcel"]) { ?>
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td width="15%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="35%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               &nbsp;&nbsp;- &nbsp;&nbsp;
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td> 
          <td class="tablecontent">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>
</table>
<?php } ?>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

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
</script>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<?php echo $view->RenderBodyEnd(); ?>
