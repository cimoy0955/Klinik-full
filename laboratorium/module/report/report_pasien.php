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

     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y"); 
     $sql_where[] = "c.pemeriksaan_create::timestamp::date = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));

     $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin, a.reg_id, a.reg_jenis_pasien, a.reg_status_pasien, c.pemeriksaan_id, c.pemeriksaan_create, d.pemeriksaan_hasil, e.kegiatan_nama, e.kegiatan_satuan 
          from lab_pemeriksaan c
          join lab_pemeriksaan_detail d on d.id_pemeriksaan = c.pemeriksaan_id
          join lab_kegiatan e on e.kegiatan_id = d.id_kegiatan
          left join klinik.klinik_registrasi a on a.reg_id = c.id_reg
          left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " order by c.pemeriksaan_create";
     $rs = $dtaccess->Execute($sql, DB_SCHEMA_LAB);
     $dataTable = $dtaccess->FetchAll($rs);
     // echo $sql;

     // -- cari ndata untuk rowspan -- //
     $sql = "select id_pemeriksaan, count(*) as jumlah_periksa 
          from lab_pemeriksaan_detail a
          left join lab_pemeriksaan c on c.pemeriksaan_id = a.id_pemeriksaan";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= "group by id_pemeriksaan";
     $rsPeriksa = $dtaccess->Execute($sql, DB_SCHEMA_LAB);
     while($dataPeriksa = $dtaccess->Fetch($rsPeriksa)){
          $rowspan[$dataPeriksa["id_pemeriksaan"]] = $dataPeriksa["jumlah_periksa"];
     }

     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Pasien Pemeriksaan";

     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";      
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";  
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jns Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Pemeriksaan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Hasil";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%"; 
          
     for($i=0,$baris=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          if ($dataTable[$i]["pemeriksaan_id"] != $dataTable[$i-1]["pemeriksaan_id"]) {
               $baris=$i;

               $tbContent[$baris][$counter][TABLE_ISI] = ($baris + 1);
               $tbContent[$baris][$counter][TABLE_ALIGN] = "right";
               $tbContent[$baris][$counter][TABLE_ROWSPAN] = $rowspan[$dataTable[$i]["pemeriksaan_id"]];
               $tbContent[$baris][$counter][TABLE_CLASS] = $classnya;
               $tbContent[$baris][$counter][TABLE_VALIGN] = "top";
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";
               $tbContent[$baris][$counter][TABLE_ROWSPAN] = $rowspan[$dataTable[$i]["pemeriksaan_id"]];
               $tbContent[$baris][$counter][TABLE_CLASS] = $classnya;
               $tbContent[$baris][$counter][TABLE_VALIGN] = "top";
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $tbContent[$baris][$counter][TABLE_ROWSPAN] = $rowspan[$dataTable[$i]["pemeriksaan_id"]];
               $tbContent[$baris][$counter][TABLE_CLASS] = $classnya;
               $tbContent[$baris][$counter][TABLE_VALIGN] = "top";
               $counter++;

               $tbContent[$baris][$counter][TABLE_ISI] = HitungUmur($dataTable[$i]["cust_usr_tanggal_lahir"]);
               $tbContent[$baris][$counter][TABLE_ALIGN] = "center";          
               $tbContent[$baris][$counter][TABLE_ROWSPAN] = $rowspan[$dataTable[$i]["pemeriksaan_id"]];
               $tbContent[$baris][$counter][TABLE_CLASS] = $classnya;
               $tbContent[$baris][$counter][TABLE_VALIGN] = "top";
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $tbContent[$baris][$counter][TABLE_ROWSPAN] = $rowspan[$dataTable[$i]["pemeriksaan_id"]];
               $tbContent[$baris][$counter][TABLE_CLASS] = $classnya;
               $tbContent[$baris][$counter][TABLE_VALIGN] = "top";
               $counter++;
               
               $tbContent[$baris][$counter][TABLE_ISI] = $bayarPasien[$dataTable[$i]["reg_jenis_pasien"]];
               $tbContent[$baris][$counter][TABLE_ALIGN] = "left";          
               $tbContent[$baris][$counter][TABLE_ROWSPAN] = $rowspan[$dataTable[$i]["pemeriksaan_id"]];
               $tbContent[$baris][$counter][TABLE_CLASS] = $classnya;
               $tbContent[$baris][$counter][TABLE_VALIGN] = "top";
               $counter++;
               $baris++;
          }
               
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kegiatan_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";
          $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pemeriksaan_hasil"]."&nbsp;".$dataTable[$i]["kegiatan_satuan"]."&nbsp;&nbsp;";
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";              
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";      
          $tbContent[$i][$counter][TABLE_CLASS] = $classnya;
          $counter++;
          
          }
     
     $colspan = 8;
     
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
