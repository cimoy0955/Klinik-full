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
 
     $thisPage = "report_tindakan.php";

     if(!$auth->IsAllowed("klinik",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("klinik",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     
     if($_POST["reg_status"]==STATUS_OPERASI_JADWAL) { 
          $sql_where[] = "c.tanggal_jadwal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));  
     } else if($_POST["reg_status"]==STATUS_BEDAH){ 
          $sql_where[] = "d.op_tanggal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     } else if($_POST["reg_status"]==STATUS_PREOP) {
          $sql_where[] = "e.preop_tanggal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"])); 
     } else {
          $sql_where[] = " c.tanggal_jadwal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]))." or d.op_tanggal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]))." or 
               e.preop_tanggal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     } 

     $sql = "select b.cust_usr_kode, b.cust_usr_nama, a.reg_status, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin, 
               a.reg_jenis_pasien, a.reg_status_pasien, c.jadwal_id, d.op_id, e.preop_id 
               from klinik.klinik_registrasi a 
               join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               left join ( select preop_id as jadwal_id,id_reg,cast(preop_tanggal_jadwal as date) as tanggal_jadwal from klinik.klinik_preop where preop_tanggal_jadwal is not null ) c on c.id_reg = a.reg_id
               left join ( select op_id, id_reg,op_tanggal from klinik.klinik_perawatan_operasi) d on d.id_reg = a.reg_id 
               left join ( select preop_id, id_reg,cast(preop_waktu as date) as preop_tanggal from klinik.klinik_preop where preop_tanggal_jadwal is null) e on e.id_reg = a.reg_id  ";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " order by a.reg_status_pasien, b.cust_usr_nama"; 
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Pasien Tindakan";

     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Passien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          if($dataTable[$i]["jadwal_id"])
               $statusPasien[$i] = STATUS_OPERASI_JADWAL;
          else if($dataTable[$i]["op_id"])
               $statusPasien[$i] = STATUS_BEDAH;
          else
               $statusPasien[$i] = STATUS_PREOP;
               
          $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = HitungUmur($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $bayarPasien[$dataTable[$i]["reg_jenis_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $rawatStatus[$statusPasien[$i]];
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
          header('Content-Disposition: attachment; filename=report_tindakan_'.$_POST["tgl_awal"].'.xls');
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
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<?php if(!$_POST["btnExcel"]) { ?>
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td width="15%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="20%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td>
          <td width="15%" class="tablecontent">&nbsp;Jenis Tindakan</td>
          <td width="25%" class="tablecontent-odd">
			<select name="reg_status" id="reg_status" onKeyDown="return tabOnEnter(this, event);">
                    <option value="" >[ Pilih Tindakan ]</option> 
                    <option value="<?php echo STATUS_OPERASI_JADWAL;?>" <?php if($_POST["reg_status"]==STATUS_OPERASI_JADWAL) echo "selected"; ?>><?php echo $rawatStatus[STATUS_OPERASI_JADWAL];?></option>
                    <option value="<?php echo STATUS_BEDAH;?>" <?php if($_POST["reg_status"]==STATUS_BEDAH) echo "selected"; ?>><?php echo $rawatStatus[STATUS_BEDAH];?></option>
                    <option value="<?php echo STATUS_PREOP;?>" <?php if($_POST["reg_status"]==STATUS_PREOP) echo "selected"; ?>><?php echo $rawatStatus[STATUS_PREOP];?></option>
			</select>
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

<?php echo $view->RenderBodyEnd(); ?>
