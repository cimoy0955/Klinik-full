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
 
     $thisPage = "report_pemeriksaan.php";

     if(!$auth->IsAllowed("report_pemeriksaan_ugd",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_pemeriksaan_ugd",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y"); 
     $sql_where[] = "c.rawat_tanggal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
    $sql_where[] = "a.reg_ugd = 'y' ";

     $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin, 
               a.reg_jenis_pasien, a.reg_status_pasien, c.rawat_id  
               from klinik.klinik_registrasi a 
               join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               join klinik.klinik_perawatan c on c.id_reg = a.reg_id ";
     $sql.= " and ".implode(" and ",$sql_where);
     $sql.= " order by a.reg_status_pasien, b.cust_usr_nama";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Pasien Pemeriksaan";

     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";     
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "ICD OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "ICD OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "INA DRG OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "INA DRG OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";     
     $counterHeader++;

     $counterHeader = 0;
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Nomor";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Prosedur";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Nomor";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Prosedur";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Prosedur";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Prosedur";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     

     
     for($i=0,$baris=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          $sql = "select c.icd_nomor as icd_nomor_od, c.icd_nama as icd_nama_od
                    from klinik.klinik_perawatan_icd a 
                    inner join klinik.klinik_icd c on a.id_icd = c.icd_id 
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataTable[$i]["rawat_id"])."
                    and a.rawat_icd_odos = 'OD'";
          unset($dataICD_OD);
          $dataICD_OD = $dtaccess->FetchAll($sql);
          $totICD_OD = count($dataICD_OD);
          
          $sql = "select c.icd_nomor as icd_nomor_os, c.icd_nama as icd_nama_os
                    from klinik.klinik_perawatan_icd a 
                    inner join klinik.klinik_icd c on a.id_icd = c.icd_id 
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataTable[$i]["rawat_id"])."
                    and a.rawat_icd_odos = 'OS'";
          unset($dataICD_OS);
          $dataICD_OS = $dtaccess->FetchAll($sql);
          $totICD_OS = count($dataICD_OS);

          $sql = "select c.ina_kode as ina_kode_od, c.ina_nama as ina_nama_od, d.ina_kode as ina_kode_os, d.ina_nama as ina_nama_os  
                    from klinik.klinik_perawatan_ina a 
                    inner join klinik.klinik_ina c on a.id_ina = c.ina_id 
                    left join klinik.klinik_perawatan_ina b on a.id_rawat = b.id_rawat and a.rawat_ina_urut = b.rawat_ina_urut and b.rawat_ina_odos = 'OS'
                    inner join klinik.klinik_ina d on b.id_ina = d.ina_id 
                    where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataTable[$i]["rawat_id"])."
                    and a.rawat_ina_odos = 'OD'";
          unset($dataINA);
          $dataINA = $dtaccess->FetchAll($sql);
          $totINA = count($dataINA);

          $tot = ($totINA>$totICD) ? $totINA : $totICD;
          
          
          $tbContent[$baris][$counter][TABLE_ISI] = ($i + 1);
          $tbContent[$baris][$counter][TABLE_ALIGN] = "right";
          $tbContent[$baris][$counter][TABLE_ROWSPAN] = $tot;
          $counter++;
          
          $tbContent[$baris][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$baris][$counter][TABLE_ALIGN] = "left";
          $tbContent[$baris][$counter][TABLE_ROWSPAN] = $tot;
          $counter++;
          
          $tbContent[$baris][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$baris][$counter][TABLE_ROWSPAN] = $tot;
          $counter++;
          
          $tbContent[$baris][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
          $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$baris][$counter][TABLE_ROWSPAN] = $tot;
          $counter++;
          
          $tbContent[$baris][$counter][TABLE_ISI] = HitungUmur($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$baris][$counter][TABLE_ALIGN] = "center";          
          $tbContent[$baris][$counter][TABLE_ROWSPAN] = $tot;
          $counter++;
          
          $tbContent[$baris][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$baris][$counter][TABLE_ROWSPAN] = $tot;
          $counter++;
          
          $tbContent[$baris][$counter][TABLE_ISI] = $bayarPasien[$dataTable[$i]["reg_jenis_pasien"]];
          $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$baris][$counter][TABLE_ROWSPAN] = $tot;
          $counter++;
          
          $tbContent[$baris][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];
          $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$baris][$counter][TABLE_ROWSPAN] = $tot;
          $counter++;
          
          for($j=0;$j<$tot;$j++,$counter=0) {
               
               $tbContent[$baris][$counter][TABLE_ISI] = $dataICD_OD[$j]["icd_nomor_od"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = $dataICD_OD[$j]["icd_nama_od"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;

               $tbContent[$baris][$counter][TABLE_ISI] = $dataICD_OS[$j]["icd_nomor_os"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = $dataICD_OS[$j]["icd_nama_os"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;

               $tbContent[$baris][$counter][TABLE_ISI] = $dataINA[$j]["ina_kode_od"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = $dataINA[$j]["ina_nama_od"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = $dataINA[$j]["ina_kode_os"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = $dataINA[$j]["ina_nama_os"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $baris++;
          }
          
          if($j==0) {

               $tbContent[$baris][$counter][TABLE_ISI] = "&nbsp;";
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;

               $tbContent[$baris][$counter][TABLE_ISI] = "&nbsp;";
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;

               $tbContent[$baris][$counter][TABLE_ISI] = "&nbsp;";
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = "&nbsp;";
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = "&nbsp;";
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;

               $tbContent[$baris][$counter][TABLE_ISI] = "&nbsp;";
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;

               $tbContent[$baris][$counter][TABLE_ISI] = "&nbsp;";
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = "&nbsp;";
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $counter++;
               
               $baris++;               
          }
     }
     
     $colspan = 16;
     
     if(!$_POST["btnExcel"]){
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
          $tbBottom[0][0][TABLE_ALIGN] = "center";
     }
     
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pemeriksaan_'.$_POST["tgl_awal"].'.xls');
     }

?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>
<script language="JavaScript">
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }
}

</script>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
     <tr>
      <td>
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<?php if(!$_POST["btnExcel"]) { ?>
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td width="15%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="20%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
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
    </td>
  </tr>
</table>
<?php echo $view->RenderBodyEnd(); ?>
