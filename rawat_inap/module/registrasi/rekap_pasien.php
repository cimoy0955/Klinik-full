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
 
     $thisPage = "report_pasien.php";

    if(!$auth->IsAllowed("rawat_inap",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("rawat_inap",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $skr = date("d-m-Y");
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr;
     
     $sql_where[] = "1=1";
     if($_POST["tgl_awal"]) $sql_where[] = "a.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "a.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
     $sql = "select count(reg_id) as total from klinik.klinik_registrasi a";

     // === nyari jumlah pasien baru
     $sql_where_baru = $sql_where;
     $sql_where_baru[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BARU);
     $sqlBaru = $sql." where ".implode(" and ",$sql_where_baru);
     
     $dataPasienBaru = $dtaccess->Fetch($sqlBaru);
     // -- end ---

     // === nyari jumlah pasien lama
     $sql_where_lama = $sql_where;
     $sql_where_lama[] = "a.reg_status_pasien = ".QuoteValue(DPE_CHAR,PASIEN_LAMA);
     $sqlLama = $sql." where ".implode(" and ",$sql_where_lama);
     
     $dataPasienLama = $dtaccess->Fetch($sqlLama);
     // -- end ---

     // === nyari jumlah pasien askes
     $sql_where_askes = $sql_where;
     $sql_where_askes[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_ASKES);
     $sqlAskes = $sql." where ".implode(" and ",$sql_where_askes);
     
     $dataPasienAskes = $dtaccess->Fetch($sqlAskes);
     // -- end ---


     // === nyari jumlah pasien pns
     $sql_where_pns = $sql_where;
     $sql_where_pns[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_PNS);
     $sqlPns = $sql." where ".implode(" and ",$sql_where_pns);
     
     $dataPasienPns = $dtaccess->Fetch($sqlPns);
     // -- end ---

     // === nyari jumlah pasien jamkesnas
     $sql_where_jamkes_pusat = $sql_where;
     $sql_where_jamkes_pusat[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESNAS_PUSAT);
     $sqlJamkesPusat = $sql." where ".implode(" and ",$sql_where_jamkes_pusat);
     
     $dataPasienJamkesPusat = $dtaccess->Fetch($sqlJamkesPusat);
     // -- end ---

     // === nyari jumlah pasien jamkesnas 
     $sql_where_jamkes_daerah = $sql_where;
     $sql_where_jamkes_daerah[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_JAMKESNAS_DAERAH);
     $sqlJamkesDaerah = $sql." where ".implode(" and ",$sql_where_jamkes_daerah);
     
     $dataPasienJamkesDaerah = $dtaccess->Fetch($sqlJamkesDaerah);
     // -- end ---
     

     // === nyari jumlah pasien swadaya
     $sql_where_swadaya = $sql_where;
     $sql_where_swadaya[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA);
     $sqlSwadaya = $sql." where ".implode(" and ",$sql_where_swadaya);
     
     $dataPasienSwadaya = $dtaccess->Fetch($sqlSwadaya);
     // -- end ---
     
          
     $tableHeader = "Rekap Pasien";
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

<BR>

     <table width="50%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td class="tablecontent" width="50%" align="left">Pasien Baru</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataPasienBaru["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Pasien Lama</td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasienLama["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Total Pasien</td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo ($dataPasienLama["total"]+$dataPasienBaru["total"]);?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Pasien Askes</td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasienAskes["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Pasien PNS</td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasienPns["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Pasien Jamkesnas Pusat</td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasienJamkesPusat["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Pasien Jamkes Daerah</td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasienJamkesDaerah["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent"  width="50%" align="left">Pasien Swadaya</td>
               <td class="tablecontent-odd"  width="50%" align="center"><?php echo $dataPasienSwadaya["total"];?></td>	     
          </tr>
     </table>


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
