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
 
     $thisPage = "report_perawatan.php";

     if(!$auth->IsAllowed("report_perawatan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_perawatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $skr = date("d-m-Y");
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr;
     
     $sql_where[] = "1=1";
     
     if($_POST["tgl_awal"]) $sql_where[] = "CAST(a.diag_waktu as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "CAST(a.diag_waktu as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     

     // === nyari jumlah pasien baru
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_pasien = $sql_where;
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_pasien);
     
     $dataPasienTotal = $dtaccess->Fetch($sqlPasien);
     // -- end ---

     
     // ---total keratometri
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_keratometri = $sql_where;
     $sql_where_keratometri[] = "(diag_k1_od <> '' or diag_k2_od <> ''  or diag_k1_os <> ''  or diag_k2_os <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_keratometri);
     
     $dataKeratometri = $dtaccess->Fetch($sqlPasien);
     // -- end ---
          
     
     // ---total biometri
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_biometri = $sql_where;
     $sql_where_biometri[] = "(diag_acial_od <> '' or diag_acial_os <> ''  or diag_iol_od <> ''  or diag_iol_os <> '' or diag_av_constant is not null or diag_deviasi <> '' or diag_rumus is not null)"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_biometri);
     
     $dataBiometri = $dtaccess->Fetch($sqlPasien);
     // -- end ---
          
     
     // ---total usg
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_usg = $sql_where;
     $sql_where_usg[] = "(diag_coa <> '' or diag_lensa <> ''  or diag_retina <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_usg);
     
     $dataUSG= $dtaccess->Fetch($sqlPasien);
     // -- end ---

     // ---total othal
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_othal = $sql_where;
     $sql_where_othal[] = "(diag_opthalmoscop <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_othal);
     
     $dataOthal = $dtaccess->Fetch($sqlPasien);
     // -- end ---


     // ---total ekg
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_ekg = $sql_where;
     $sql_where_ekg[] = "(diag_ekg <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_ekg);
     
     $dataEkg = $dtaccess->Fetch($sqlPasien);
     // -- end ---

     // ---total fundus
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_fundus = $sql_where;
     $sql_where_fundus[] = "(diag_fundus <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_fundus);
     
     $dataFundus = $dtaccess->Fetch($sqlPasien);
     // -- end ---

     // ---total oct
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_oct = $sql_where;
     $sql_where_oct[] = "(diag_oct <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_oct);
     
     $dataOct = $dtaccess->Fetch($sqlPasien);
     // -- end ---

     // ---total yag
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_yag = $sql_where;
     $sql_where_yag[] = "(diag_yag <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_yag);
     
     $dataYag = $dtaccess->Fetch($sqlPasien);
     // -- end ---

     // ---total argon
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_argon = $sql_where;
     $sql_where_argon[] = "(diag_argon <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_argon);
     
     $dataArgon = $dtaccess->Fetch($sqlPasien);
     // -- end ---

     // ---total glaukoma
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_glaukoma = $sql_where;
     $sql_where_glaukoma[] = "(diag_glaukoma <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_glaukoma);
     
     $dataGlaukoma = $dtaccess->Fetch($sqlPasien);
     // -- end ---

     // ---total humpre
     $sqlPasien = "select count(diag_id) as total from klinik.klinik_diagnostik a";
     $sql_where_humpre = $sql_where;
     $sql_where_humpre[] = "(diag_humpre <> '')"; 
     
     $sqlPasien = $sqlPasien." where ".implode(" and ",$sql_where_humpre);
     
     $dataHumpre = $dtaccess->Fetch($sqlPasien);
     // -- end ---
          
          
     $tableHeader = "Report Diagnostik";
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
               <td class="tablecontent" width="50%" align="left">Total Pasien</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataPasienTotal["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien Keratometri</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataKeratometri["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien Biometri</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataBiometri["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien USG</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataUSG["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien Indirect Opthalmoscopy</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataOthal["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien EKG</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataEkg["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien Fundus Angiografi</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataFundus["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien Optical Coherence Tomographi</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataOct["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien Yag Laser</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataYag["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien Argon Laser</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataArgon["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien Laser Glaukoma</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataGlaukoma["total"];?></td>	     
          </tr>
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Pasien Humprey</td>
               <td width="50%" class="tablecontent-odd"  align="center"><?php echo $dataHumpre["total"];?></td>	     
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
