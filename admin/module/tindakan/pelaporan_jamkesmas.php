<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $thisPage = "pelaporan_jamkesmas.php";
     $editPage = "pelaporan_jamkesmas_edit.php";

     if(!$auth->IsAllowed("pelaporan_jamkesmas",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("pelaporan_jamkesmas",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     //if($_POST["cust_usr_jenis"]) $sql_where[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
     $sql_where[] = "a.reg_tanggal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));

     $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin, 
               a.reg_jenis_pasien, a.reg_status_pasien, a.reg_keterangan, a.reg_waktu, a.reg_tanggal, a.reg_id,
               c.jamkesmas_jns_rawat, c.jamkesmas_cara_pulang, c.jamkesmas_p1, c.jamkesmas_p2, c.jamkesmas_h2, c.jamkesmas_tarif, 
               f.icd_nomor
               from klinik.klinik_registrasi a 
               join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               left join klinik.klinik_laporan_jamkesmas c on a.reg_id=c.id_reg
               inner join klinik.klinik_perawatan d on a.reg_id=d.id_reg
               inner join klinik.klinik_perawatan_icd e on d.rawat_id=e.id_rawat
               inner join klinik.klinik_icd f on e.id_icd=f.icd_id";
     $sql.= " where a.reg_jenis_pasien='4'and ".implode(" and ",$sql_where);
     $sql.= " order by c.jamkesmas_jns_rawat desc, a.reg_status_pasien, b.cust_usr_nama, a.reg_waktu";
    //echo $sql;
     
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Pelaporan Jamkesmas";

     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kdrs";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Klsrs";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Norm";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Klsrawat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jnsrawat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tglmsk";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tglklr";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Los";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "UmurThn";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "JK";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "CaraPlg";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Berat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dutama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "D2";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "P1";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "P2";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tarif Klaim";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $cc=1;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
        //||($dataTable[$i]["reg_tanggal"]!=$dataTable[$i-1]["reg_tanggal"])&&($dataTable[$i]["icd_nomor"]!=$dataTable[$i-1]["icd_nomor"])){
        //if($dataTable[$i]["reg_id"]!=$dataTable[$i-1]["reg_id"]){
          $tbContent[$i][$counter][TABLE_ISI] = $cc;
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          $cc++;
          
          $sql_query="select count(id_reg) as jml_data from klinik.klinik_laporan_jamkesmas where id_reg=".QuoteValue(DPE_CHAR,$dataTable[$i]["reg_id"]);
          $rs_cek=$dtaccess->Execute($sql_query);
          $data_cek=$dtaccess->Fetch($rs_cek);
          if($data_cek["jml_data"]==0){$mode="Add";}else{$mode="Edit";}
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?mode='.$mode.'&id='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
               
          $tbContent[$i][$counter][TABLE_ISI] = "5201010";
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "3";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "3";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "0";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jamkesmas_jns_rawat"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = date_db($dataTable[$i]["reg_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          //ngecek jenis perawatan, kalo operasi/1: tanggal pulang <- tanggal masuk + 1 hari,
          //kalo rawat jalan/2: tanggal pulang <- tanggal masuk
          if(!$dataTable[$i]["jamkesmas_jns_rawat"]){
            $tgl_keluar= '';
          }elseif($dataTable[$i]["jamkesmas_jns_rawat"]==1){
            $tgl_keluar = $dataTable[$i]["reg_tanggal"];
            $tgl_keluar = DateAdd($tgl_keluar,1);
            $los = 2;
          }elseif($dataTable[$i]["jamkesmas_jns_rawat"]==2){
            $tgl_keluar = $dataTable[$i]["reg_tanggal"];
            $los = 1;
          }       
          
          $tbContent[$i][$counter][TABLE_ISI] = format_date($tgl_keluar);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $los;
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = HitungUmur($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          if($dataTable[$i]["cust_usr_jenis_kelamin"]=="L"){ $jkelamin = 1;}else{$jkelamin = 2;};
          $tbContent[$i][$counter][TABLE_ISI] = $jkelamin;
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jamkesmas_cara_pulang"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_nomor"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jamkesmas_h2"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jamkesmas_p1"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jamkesmas_p2"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["jamkesmas_tarif"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
        }
          
        
     //}
     
     $colspan = count($tbHeader[0]);
     
     if(!$_POST["btnExcel"]){
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
          $tbBottom[0][0][TABLE_ALIGN] = "center";
     }
     
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=laporan_jamkesmas_'.$_POST["tgl_awal"].'.xls');
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
<?php if(!$_POST["btnExcel"]) { ?>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
    <tr>
      <td>
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">

<table align="center" border="1" cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td width="10%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="20%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td>
          <td class="tablecontent">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</table>
  </td>
  </tr>
</table>
<?php }elseif($_POST["btnExcel"]){
    echo "<table border='0' width='600'>";
    for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
      echo "<tr><td>";
      echo "5201010.3.".$dataTable[$i]["cust_usr_kode"].".3.0..".$dataTable[$i]["jamkesmas_jns_rawat"].".".date_db($dataTable[$i]["reg_tanggal"]);
      if(!$dataTable[$i]["jamkesmas_jns_rawat"]){
            $tgl_keluar= '';
          }elseif($dataTable[$i]["jamkesmas_jns_rawat"]=1){
            $tgl_keluar = $dataTable[$i]["reg_tanggal"];
            $tgl_keluar = DateAdd($tgl_keluar,1);
            $los = 2;
          }elseif($dataTable[$i]["jamkesmas_jns_rawat"]=2){
            $tgl_keluar = $dataTable[$i]["reg_tanggal"];
            $los = 1;
          } 
      if($dataTable[$i]["cust_usr_jenis_kelamin"]=="L"){$jkelamin="1";}else{$jkelamin="2";};
      echo ".".$tgl_keluar.".".$los.".".$jkelamin.".".$dataTable[$i]["jamkesmas_cara_pulang"].".".$dataTable[$i]["icd_nomor"];
      echo "</td></tr>";
    }
    echo "</table>";
} ?>

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
