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
     $tableICD = new InoTable("tableINA","100%","left");
     $tableINA = new InoTable("tableICD","100%","left");
     $tableRekap = new InoTable("tableRekap","100%","left");
     $tableDokter = new InoTable("tableDokter","100%","left");
     $jml_pasien_ina = 0;
     $jml_pasien_icd = 0;
 
     $thisPage = "report_perawatan.php";

     if(!$auth->IsAllowed("pemeriksaan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("pemeriksaan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $skr = date("d-m-Y");
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr;
     
     //$sql_where[] = "1=1";
     
     if($_POST["tgl_awal"]) $sql_where[] = "a.rawat_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "a.rawat_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     

     // === nyari jumlah pasien baru
     $sqlPasien = "select count(rawat_id) as total from klinik.klinik_perawatan a";
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where);
     //echo $sqlPasien;
     $dataPasienTotal = $dtaccess->Fetch($sqlPasien);
     // -- end ---

     

     // === nyari jumlah icd
     $sqlPasienICD = "select c.icd_id,c.icd_nomor,c.icd_nama,count(a.id_reg) as jumlah_perawatan
                    from klinik.klinik_perawatan a
                    left join klinik.klinik_perawatan_icd b on a.rawat_id = b.id_rawat
                    left join klinik.klinik_icd c on b.id_icd = c.icd_nomor";/*c.icd_jenis = '1' */
     $sqlPasienICD .= " where ".implode(" and ",$sql_where)." and c.icd_nomor is not null";
     $sqlPasienICD .= " group by c.icd_id,c.icd_nomor,c.icd_nama ";
     $rsPasienICD = $dtaccess->Execute($sqlPasienICD);
     $dataPasienICD = $dtaccess->FetchAll($rsPasienICD);
     //echo $sqlPasien;
     // -- end ---
     
     // === nyari jumlah ina drg
     $sqlPasienINA = "select c.prosedur_id,c.prosedur_kode,c.prosedur_nama,count(a.id_reg) as jumlah_perawatan
                    from klinik.klinik_perawatan_prosedur b
                    left join klinik.klinik_perawatan a on a.rawat_id = b.id_rawat
                    left join klinik.klinik_prosedur c on b.id_prosedur = c.prosedur_kode";
     $sqlPasienINA .= " where ".implode(" and ",$sql_where);
     $sqlPasienINA .=" group by c.prosedur_id,c.prosedur_kode,c.prosedur_nama ";
     //echo $sqlPasien;
     $rsPasienINA = $dtaccess->Execute($sqlPasienINA);
     $dataPasienINA = $dtaccess->FetchAll($rsPasienINA);
     // -- end ---
     
     // === nyari jumlah dokter
     $sqlDokter = "select c.pgw_nama,count(a.id_reg) as jumlah_perawatan 
                    from klinik.klinik_perawatan_dokter b 
                    left join klinik.klinik_perawatan a on a.rawat_id = b.id_rawat 
                    left join hris.hris_pegawai c on b.id_pgw = c.pgw_id";
     $sqlDokter .= " where ".implode(" and ",$sql_where);
     $sqlDokter .= "group by c.pgw_nama";
     $rsDokter = $dtaccess->Execute($sqlDokter);
     $dataDokter = $dtaccess->FetchAll($rsDokter);
     // --- end ----

     // === nyari jumlah perawat
     $sqlSuster = "select c.pgw_nama,count(a.id_reg) as jumlah_perawatan 
                    from klinik.klinik_perawatan_suster b 
                    left join klinik.klinik_perawatan a on a.rawat_id = b.id_rawat 
                    left join hris.hris_pegawai c on b.id_pgw = c.pgw_id";
     $sqlSuster .= " where ".implode(" and ",$sql_where);
     $sqlSuster .= "group by c.pgw_nama";
     $rsSuster = $dtaccess->Execute($sqlSuster);
     $dataSuster = $dtaccess->FetchAll($rsSuster);
     // --- end ----
     
     // === nyari jumlah tindakan
    /* $sqlPasien = "select count(rawat_id) as total from klinik.klinik_perawatan a";
     $sql_where_tindakan = $sql_where;
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_tindakan);
     
     $dataPasienTindakan = $dtaccess->Fetch($sqlPasien);
    */ // -- end ---

     $sqlNext = "select rawat_next, count(id_reg) as jumlah_next
               from klinik.klinik_perawatan a";
     $sqlNext .= " where ".implode(" and ",$sql_where);          
     $sqlNext .= "group by rawat_next";
     $rsNext = $dtaccess->Execute($sqlNext);
     $dataNext = $dtaccess->FetchAll($rsNext);
     
     // === nyari jumlah Point Petugas 
     $sqlRefraksist = "select count(a.id_pgw) as total
                         from ( 
                              select distinct rawat_tanggal, b.id_pgw 
                              from klinik.klinik_perawatan_suster b
                              join klinik.klinik_perawatan a on b.id_rawat = a.rawat_id"; 
     $sqlRefraksist = $sqlRefraksist." where ".implode(" and ",$sql_where)." 
                         ) a";

     $dataSusterTotal = $dtaccess->Fetch($sqlRefraksist);
     // -- end ---
     
     //-- bikin tabel baru untuk ICD euy --//     
     $tableHeader = "Rekap Pemeriksaan";
     $counterHeader = 0;
     $tbHeader1[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader1[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
     
     $tbHeader1[0][$counterHeader][TABLE_ISI] = "No ICD 10";
     $tbHeader1[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
      
     $tbHeader1[0][$counterHeader][TABLE_ISI] = "NAMA ICD";
     $tbHeader1[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader1[0][$counterHeader][TABLE_ISI] = "Jumlah Pasien";
     $tbHeader1[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     for($i=0,$j=count($dataPasienICD),$counterContent=0;$i<$j;$i++,$counterContent=0){
          if($dataPasienICD[$i]["icd_nomor"]!=null){
               $tbContent1[$i][$counterContent][TABLE_ISI] = $i+1;
               $tbContent1[$i][$counterContent][TABLE_WIDTH] = "3%";
               $tbContent1[$i][$counterContent][TABLE_ALIGN] = "center";
               $counterContent++;
               
               $tbContent1[$i][$counterContent][TABLE_ISI] = $dataPasienICD[$i]["icd_nomor"];
               $tbContent1[$i][$counterContent][TABLE_WIDTH] = "3%";
               $tbContent1[$i][$counterContent][TABLE_ALIGN] = "center";
               $counterContent++;
                
               $tbContent1[$i][$counterContent][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;".$dataPasienICD[$i]["icd_nama"];
               $tbContent1[$i][$counterContent][TABLE_WIDTH] = "15%";
               $tbContent1[$i][$counterContent][TABLE_ALIGN] = "left";
               $counterContent++;
          
               $tbContent1[$i][$counterContent][TABLE_ISI] = $dataPasienICD[$i]["jumlah_perawatan"]."&nbsp;&nbsp;&nbsp;";
               $tbContent1[$i][$counterContent][TABLE_ALIGN] = "right";
               $tbContent1[$i][$counterContent][TABLE_WIDTH] = "5%";     
               $counterContent++;
               $jml_pasien_icd += $dataPasienICD[$i]["jumlah_perawatan"];
          }
     }
     $tbBottom1[0][0][TABLE_ISI] = "Jumlah&nbsp;&nbsp;";
     $tbBottom1[0][0][TABLE_COLSPAN] = 3;
     $tbBottom1[0][0][TABLE_ALIGN] = "right";
     
     $tbBottom1[0][1][TABLE_ISI] = $jml_pasien_icd."&nbsp;&nbsp;&nbsp;";
     $tbBottom1[0][1][TABLE_ALIGN] = "right";
     //-- END --//
     
      //-- bikin tabel baru untuk INADRG euy --// 
      $counterHeader = 0;
     $tbHeader2[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader2[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
     
     $tbHeader2[0][$counterHeader][TABLE_ISI] = "Kode INA DRG";
     $tbHeader2[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
      
     $tbHeader2[0][$counterHeader][TABLE_ISI] = "NAMA";
     $tbHeader2[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader2[0][$counterHeader][TABLE_ISI] = "Jumlah Pasien";
     $tbHeader2[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     for($i=0,$j=count($dataPasienINA),$counterContent=0;$i<$j;$i++,$counterContent=0){
          
          $tbContent2[$i][$counterContent][TABLE_ISI] = $i+1;
          $tbContent2[$i][$counterContent][TABLE_WIDTH] = "3%";
          $tbContent2[$i][$counterContent][TABLE_ALIGN] = "center";
          $counterContent++;
          
          $tbContent2[$i][$counterContent][TABLE_ISI] = $dataPasienINA[$i]["prosedur_kode"];
          $tbContent2[$i][$counterContent][TABLE_WIDTH] = "3%";
          $tbContent2[$i][$counterContent][TABLE_ALIGN] = "center";
          $counterContent++;
           
          $tbContent2[$i][$counterContent][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;".$dataPasienINA[$i]["prosedur_nama"];
          $tbContent2[$i][$counterContent][TABLE_WIDTH] = "15%";
          $tbContent2[$i][$counterContent][TABLE_ALIGN] = "left";
          $counterContent++;
     
          $tbContent2[$i][$counterContent][TABLE_ISI] = $dataPasienINA[$i]["jumlah_perawatan"]."&nbsp;&nbsp;&nbsp;";
          $tbContent2[$i][$counterContent][TABLE_ALIGN] = "right";
          $tbContent2[$i][$counterContent][TABLE_WIDTH] = "5%";     
          $counterContent++;
          $jml_pasien_ina += $dataPasienINA[$i]["jumlah_perawatan"];
     }
     $tbBottom2[0][0][TABLE_ISI] = "Jumlah&nbsp;&nbsp;";
     $tbBottom2[0][0][TABLE_COLSPAN] = 3;
     $tbBottom2[0][0][TABLE_ALIGN] = "right";
     
     $tbBottom2[0][1][TABLE_ISI] = $jml_pasien_ina."&nbsp;&nbsp;&nbsp;";
     $tbBottom2[0][1][TABLE_ALIGN] = "right";
     //-- END --//
     
     
     //-- bikin tabel untuk rekap pemeriksaan --//$counterHeader = 0;
     $tbHeader3[0][0][TABLE_ISI] = "REKAP";
     $tbHeader3[0][0][TABLE_COLSPAN] = "2"; $counterHeader++;
     
     $tbContent3[0][0][TABLE_ISI] = "Total Pasien&nbsp;";
     $tbContent3[0][0][TABLE_CLASS] = "tablecontent";
     $tbContent3[0][0][TABLE_WIDTH] = "50%";
     $tbContent3[0][0][TABLE_ALIGN] = "left";
     
     $tbContent3[0][1][TABLE_ISI] = $dataPasienTotal["total"];
     $tbContent3[0][1][TABLE_CLASS] = "tablecontent-odd";
     $tbContent3[0][1][TABLE_WIDTH] = "50%";
     $tbContent3[0][1][TABLE_ALIGN] = "center";
     
     $tbContent3[1][0][TABLE_ISI] = "Total Pasien dengan ICD&nbsp;";
     $tbContent3[1][0][TABLE_CLASS] = "tablecontent";
     $tbContent3[1][0][TABLE_WIDTH] = "50%";
     $tbContent3[1][0][TABLE_ALIGN] = "left";
     
     $tbContent3[1][1][TABLE_ISI] = $jml_pasien_icd;
     $tbContent3[1][1][TABLE_CLASS] = "tablecontent-odd";
     $tbContent3[1][1][TABLE_WIDTH] = "50%";
     $tbContent3[1][1][TABLE_ALIGN] = "center";
     
     $tbContent3[2][0][TABLE_ISI] = "Total Pasien dengan Tindakan&nbsp;";
     $tbContent3[2][0][TABLE_CLASS] = "tablecontent";
     $tbContent3[2][0][TABLE_WIDTH] = "50%";
     $tbContent3[2][0][TABLE_ALIGN] = "left";
     
     $tbContent3[2][1][TABLE_ISI] = $jml_pasien_ina;
     $tbContent3[2][1][TABLE_CLASS] = "tablecontent-odd";
     $tbContent3[2][1][TABLE_WIDTH] = "50%";
     $tbContent3[2][1][TABLE_ALIGN] = "center";
      
     $tbContent3[3][0][TABLE_ISI] = "Point Petugas&nbsp;";
     $tbContent3[3][0][TABLE_CLASS] = "tablecontent";
     $tbContent3[3][0][TABLE_WIDTH] = "50%";
     $tbContent3[3][0][TABLE_ALIGN] = "left";
     
     $tbContent3[3][1][TABLE_ISI] = ($dataSusterTotal["total"]>0)?round($dataPasienTotal["total"]/$dataSusterTotal["total"],2):0;
     $tbContent3[3][1][TABLE_CLASS] = "tablecontent-odd";
     $tbContent3[3][1][TABLE_WIDTH] = "50%";
     $tbContent3[3][1][TABLE_ALIGN] = "center";
     
     $tbBottom3[0][0][TABLE_ISI] = "&nbsp;";
     $tbBottom3[0][0][TABLE_COLSPAN] = '2';
     //-- END TABEL REKAP PEMERIKSAAN --//
     
     $r = 0; $s = 0;
//     -- construct table Rekap Dokter & Suster --
     $tbContent4[$r][$s][TABLE_ISI] = "Rekap Dokter";
     $tbContent4[$r][$s][TABLE_COLSPAN] = "2";
     $tbContent4[$r][$s][TABLE_CLASS] = "tablesmallheader";
     $r++;
     
     for($i=0;$i<count($dataDokter);$i++){
          $tbContent4[$r][$s][TABLE_ISI] = $dataDokter[$i]["pgw_nama"];
          $tbContent4[$r][$s][TABLE_CLASS] = "tablecontent";
          $tbContent4[$r][$s][TABLE_WIDTH] = "50%";
          $tbContent4[$r][$s][TABLE_ALIGN] = "left";
          $s++;
          
          $tbContent4[$r][$s][TABLE_ISI] = $dataDokter[$i]["jumlah_perawatan"];
          $tbContent4[$r][$s][TABLE_CLASS] = "tablecontent-odd";
          $tbContent4[$r][$s][TABLE_WIDTH] = "50%";
          $tbContent4[$r][$s][TABLE_ALIGN] = "left";
          $s=0; $r++;
     }
     
     $tbContent4[$r][$s][TABLE_ISI] = "Rekap Perawat";
     $tbContent4[$r][$s][TABLE_COLSPAN] = "2";
     $tbContent4[$r][$s][TABLE_CLASS] = "tablesmallheader";
     $r++;
     
     for($i=0;$i<count($dataSuster);$i++){
          $tbContent4[$r][$s][TABLE_ISI] = $dataSuster[$i]["pgw_nama"];
          $tbContent4[$r][$s][TABLE_CLASS] = "tablecontent";
          $tbContent4[$r][$s][TABLE_WIDTH] = "50%";
          $tbContent4[$r][$s][TABLE_ALIGN] = "left";
          $s++;
          
          $tbContent4[$r][$s][TABLE_ISI] = $dataSuster[$i]["jumlah_perawatan"];
          $tbContent4[$r][$s][TABLE_CLASS] = "tablecontent-odd";
          $tbContent4[$r][$s][TABLE_WIDTH] = "50%";
          $tbContent4[$r][$s][TABLE_ALIGN] = "left";
          $s=0; $r++;
     }
     
?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
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
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td width="10%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="35%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               -
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               
          </td>
          <td class="tablecontent">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>
</table>

<div style="float:left;width:33%;">
     <div style="height:auto;overflow:auto">
     <?php
     if($_POST["btnLanjut"]) echo $tableICD->RenderView($tbHeader1,$tbContent1,$tbBottom1);
     ?>
     </div>
</div>
<div style="float:left;width:33%;">
     <div style="height:auto;overflow:auto">
     <?php 
     if($_POST["btnLanjut"]) echo $tableINA->RenderView($tbHeader2,$tbContent2,$tbBottom2);
     ?>
     </div>
</div>
<div style="float:left;width:33%;">
     <div style="height:auto;overflow:auto">
     <?php 
     echo $tableRekap->RenderView($tbHeader3,$tbContent3,$tbBottom3);
     echo "<br />".$tableDokter->RenderView($tbHeader4,$tbContent4,$tbBottom3);
     ?>
     </div>
</div>
     <!--<table width="49%" border="1" cellpadding="1" cellspacing="1">
          <tr>
              <td colspan="2" style="text-align: center;">REKAP</td>
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataPasienTotal["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien dengan ICD</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $jml_pasien_icd;?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien dengan Tindakan</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $jml_pasien_ina;?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Point Petugas</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo ($dataSusterTotal["total"]>0)?round($dataPasienTotal["total"]/$dataSusterTotal["total"],2):0;?></td>	     
          </tr>
     </table>-->
</div>

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