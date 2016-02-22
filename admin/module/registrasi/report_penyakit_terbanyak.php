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
 
     $thisPage = "report_penyakit_terbanyak.php";

     if(!$auth->IsAllowed("report_registrasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_registrasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
          $skr = date("d-m-Y");
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr;
     
     $sql_where[] = "1=1";
     if($_POST["tgl_awal"]) $sql_where[] = "b.rawat_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "b.rawat_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));

     
     $sql_where = implode(" and ",$sql_where);
     $sql = "select d.icd_nomor,d.icd_nama ,d.icd_nomor, count(c.id_icd) as total
                from klinik.klinik_registrasi a 
            left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
            left join klinik.klinik_perawatan_icd c on b.rawat_id = c.id_rawat
            left join klinik.klinik_icd d on d.icd_nomor = c.id_icd ";

       $sql .=" where d.icd_nomor is not null and ".$sql_where;
	     $sql .= " group by d.icd_nomor,d.icd_nama,d.icd_nomor order by total desc limit 10 offset 0";
       $rs = $dtaccess->Execute($sql);
       $dataTable = $dtaccess->FetchAll($rs);
     //echo $sql;
       
     if(count($dataTable)>10){
         $n = 10;
     }else{
         $n = count($dataTable);
     }
     
     for($i=0,$counter=0;$i<$n;$i++,$counter=0){

       $sql = "select count(c.id_icd) as total
            from klinik.klinik_registrasi a 
            left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
            left join klinik.klinik_perawatan_icd c on b.rawat_id = c.id_rawat
            left join klinik.klinik_icd d on d.icd_nomor = c.id_icd ";
       $sql .="where a.reg_status_pasien = 'B' and d.icd_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["icd_nomor"]);
       $sql .= " and ".$sql_where;
       $rs = $dtaccess->Execute($sql);
       $dataBaru = $dtaccess->Fetch($rs);
       
       $icdBaru[] = $dataBaru['total']; 
     
     $sql = "select count(c.id_icd) as total
            from klinik.klinik_registrasi a 
            left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
            left join klinik.klinik_perawatan_icd c on b.rawat_id = c.id_rawat
            left join klinik.klinik_icd d on d.icd_nomor = c.id_icd ";
       $sql .="where a.reg_status_pasien = 'L' and d.icd_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["icd_nomor"]);
       $sql .= " and ".$sql_where;
       $rs = $dtaccess->Execute($sql);
       $dataLama = $dtaccess->Fetch($rs);
       
       $icdLama[] = $dataLama['total'];
       
        $sql = "select count(c.id_icd) as total
            from klinik.klinik_registrasi a 
            left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
            left join klinik.klinik_perawatan_icd c on b.rawat_id = c.id_rawat
            left join klinik.klinik_icd d on d.icd_nomor = c.id_icd ";
       $sql .="where d.icd_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["icd_nomor"]);
       $sql .= " and ".$sql_where;
       $rs = $dtaccess->Execute($sql);
       $dataTotal = $dtaccess->Fetch($rs); 
       $icdTotal[] = $dataTotal['total'];
       
       $sql = "select count(c.id_icd) as total
            from klinik.klinik_registrasi a 
            join klinik.klinik_dinas_luar z on z.id_reg = a.reg_id
            left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
            left join klinik.klinik_perawatan_icd c on b.rawat_id = c.id_rawat
            left join klinik.klinik_icd d on d.icd_nomor = c.id_icd ";
       $sql .="where d.icd_nomor = ".QuoteValue(DPE_CHAR,$dataTable[$i]["icd_nomor"]);
       $sql .= " and ".$sql_where;
       $rs = $dtaccess->Execute($sql);
       $dataLuar = $dtaccess->Fetch($rs);
       
       $icdLuar[] = $dataLuar['total'];
       }
    // echo $sql;
    
    
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report 10 Penyakit Terbanyak";

     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Baru";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Lama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Luar Gedung";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
    // for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
    
    if(count($dataTable)>10){
         $n = 10;
     }else{
         $n = count($dataTable);
     }
     
     for($i=0,$counter=0;$i<$n;$i++,$counter=0){
     
          $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_nomor"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $icdBaru[$i];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $icdLama[$i];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $icdTotal[$i];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $icdLuar[$i];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
     }
     
     $colspan = count($tbHeader[0]);
     
     if(!$_POST["btnExcel"]){
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = "3";
          $tbBottom[0][0][TABLE_ALIGN] = "center";
     }
     
               $tbBottom[0][1][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnCetak" value="Cetak" class="button" onClick="CheckSimpan()">&nbsp;';
          $tbBottom[0][1][TABLE_WIDTH] = "100%";
          $tbBottom[0][1][TABLE_COLSPAN] = "3";
          $tbBottom[0][1][TABLE_ALIGN] = "center";
     
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_penyakit_terbanyak.xls');
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

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=1000,top=35;left=150');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=1000,top=35;left=150');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}

function CheckSimpan() {
     if(confirm('Cetak Invoice?')) BukaWindow('cetak_10_penyakit_terbanyak.php?tgl_awal=<?php echo date_db($_POST["tgl_awal"]);?>&tgl_akhir=<?php echo date_db($_POST["tgl_akhir"]);?>','Invoice');
     return true;
}

</script>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>
    <?php $_POST['tgl_awal'];?>
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
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
