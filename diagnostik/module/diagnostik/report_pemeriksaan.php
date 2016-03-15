<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     require_once($APLICATION_ROOT."library/config/global.cfg.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $thisPage = "report_pemeriksaan.php";

     if(!$auth->IsAllowed("pemeriksaan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("pemeriksaan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y"); 
     $sql_where[] = "CAST (c.diag_waktu AS DATE) between ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]))." and ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));

     $sql = "select b.cust_usr_kode as Kode, b.cust_usr_nama as Nama, b.cust_usr_alamat as Alamat, b.cust_usr_tanggal_lahir as TglLahir, b.cust_usr_jenis_kelamin as Sex, 
               a.reg_jenis_pasien as JenisPasien, a.reg_status_pasien as StatusPasien,
               c.diag_acial_od as AcialOD, c.diag_acial_os as AcialOS, c.diag_iol_od as IOL_OD, c.diag_iol_os as IOL_OS, e.bio_av_nama as AV_Constant, c.diag_deviasi as StdDev, d.bio_rumus_nama as RumusBiometri,c.diag_ekg as EKG,c.diag_fundus as FundusAngiografi, c.diag_opthalmoscop as IndirectOpthalmoscopy, c.diag_oct as OCT, c.diag_yag as YAG,c.diag_argon as ARGON, c.diag_slt as SLT, c.diag_humpre as HUMPREY, c.diag_lab_gula_darah as GDA, c.diag_lab_darah_lengkap as GDL
               from klinik.klinik_diagnostik c
               left join klinik.klinik_registrasi a on c.id_reg = a.reg_id
               left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               left join klinik.klinik_biometri_rumus d on c.diag_rumus = d.bio_rumus_id
               left join klinik.klinik_biometri_av e on e.bio_av_id = c.diag_av_constant";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " order by a.reg_status_pasien, b.cust_usr_nama";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     // echo $sql;
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Pasien Pemeriksaan";

     if($dataTable){
          // --- construct new table ---- //
          $counterHeader = 0;
               
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
          
          foreach($dataTable[0] as $key=>$value){       
               $tbHeader[0][$counterHeader][TABLE_ISI] = $key;
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";  
               $counterHeader++;
          }     
          for($i=0,$baris=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0,$baris++){
               $tbContent[$baris][$counter][TABLE_ISI] = ($i + 1);
               $tbContent[$baris][$counter][TABLE_ALIGN] = "right";
               $tbContent[$baris][$counter][TABLE_CLASS] = $classnya;
               $counter++;
               foreach($dataTable[$i] as $key => $value){
                    if($key=="sex") {$isinya = $jenisKelamin[$dataTable[$i][$key]];}
                    elseif($key=="tgllahir") {$isinya = date_db($dataTable[$i][$key]);}
                    elseif($key=="jenispasien") {$isinya = $bayarPasien[$dataTable[$i][$key]];}
                    elseif($key=="statuspasien") {$isinya = $statusPasien[$dataTable[$i][$key]];}
                    else {$isinya = $dataTable[$i][$key];}
                    $tbContent[$baris][$counter][TABLE_ISI] = $isinya;
                    $tbContent[$baris][$counter][TABLE_ALIGN] = "center";
                    $tbContent[$baris][$counter][TABLE_CLASS] = $classnya;
                    $counter++;
               }
               
               
          }
          
          $colspan = 16;
          
          if(!$_POST["btnExcel"]){
               $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
               $tbBottom[0][0][TABLE_WIDTH] = "100%";
               $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
               $tbBottom[0][0][TABLE_ALIGN] = "center";
               
               $tbBottom[0][1][TABLE_ISI] .= '&nbsp;';
               $tbBottom[0][1][TABLE_WIDTH] = "100%";
               $tbBottom[0][1][TABLE_COLSPAN] = (count($dataTable[0]) - $colspan + 1);
               $tbBottom[0][1][TABLE_ALIGN] = "center";
          }
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
    </td>
  </tr>
</table>
<?php echo $view->RenderBodyEnd(); ?>
