<?php
require_once("root.inc.php");
require_once($ROOT."library/auth.cls.php");
require_once($ROOT."library/textEncrypt.cls.php");
require_once($ROOT."library/dataaccess.cls.php");
require_once($ROOT."library/dateFunc.lib.php");
require_once($ROOT."library/currFunc.lib.php");
require_once($ROOT."library/inoLiveX.php");
require_once($APLICATION_ROOT."library/view.cls.php");

$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new TextEncrypt();     
$auth = new CAuth();
$table = new InoTable("table","100%","left");

$thisPage = "report_rekap_loket.php";

$skr = date("d-m-Y");
if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $_POST["tgl_awal"];
if(!$_POST["cb_shift"]) $_POST["cb_shift"] = "1";
if($_POST["tgl_awal"] && ($_POST["cb_shift"] == "3" || $_POST["cb_shift"] == "4")){
    $_POST["tgl_akhir"] = date_db(DateAdd(date_db($_POST["tgl_awal"]),1));
}


/* bagian ini digunakan untuk mendefinisikan shift kerja
 * dalam variabel array
 * shift 1: 07.00 - 14.59
 * shift 2: 15.00 - 20.59
 * shift 3: 21.00 - 06.59
 * shift 4: perhitungan waktu shift 24jam
 */
$shiftStart["1"] = "07:00:00";
$shiftStart["2"] = "15:00:00";
$shiftStart["3"] = "21:00:00";
$shiftStart["4"] = "07:00:00";
$shiftEnd["1"] = "14:59:59";
$shiftEnd["2"] = "20:59:59";
$shiftEnd["3"] = "06:59:59";
$shiftEnd["4"] = "06:59:59";
/* end of shift array */

if(!$auth->IsAllowed("report_kasir",PRIV_READ)){
    die("access_denied");
    exit(1);
} elseif($auth->IsAllowed("report_kasir",PRIV_READ)===1){
    echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
    exit(1);
}

$sql = "select biaya_id, biaya_nama, biaya_total, biaya_kode
        from klinik_biaya where upper(biaya_kode) in ('RJ3-27','LB3-18','OK3-14','OK3-35','LB3-38','OK321','LB3-37','OK3-06','OK3-31','OK3-05','OK3-25',
        'OK3-29','OK3-37','LB3-22','OK3-30','LB3-12','OP3-03','OP3-39','OK3-24','OP3-14','OP3-13','RI4-01','RI4-O3','RI4-O3','OP3-16','RJ3-28','OP3-10',
        'RI3-03','OP3-17','OP3-17','OP3-31','OP3-36','RI3-01','RI3-03','OP3-22','OP3-20','RI3-04','OP3-26','OP3-34','APT-00')";
$rs_biaya = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);

$rekapTanggal = $_POST["tgl_awal"]."&nbsp;".$shiftStart[$_POST["cb_shift"]]."&nbsp;-&nbsp;".$_POST["tgl_akhir"]." ".$shiftEnd[$_POST["cb_shift"]];
//echo $sql;

/*
 * variabel bantu untuk bikin tabel
 */
$row = 0;
$col = 0;
$GrandTotal = 0;

/*
 * building the table
 */
$tblContent[$row][$col][TABLE_ISI]      = "1";
$tblContent[$row][$col][TABLE_WIDTH]    = "3%";
$tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
$tblContent[$row][$col][TABLE_ALIGN]    = "center";
$col++;

$tblContent[$row][$col][TABLE_ISI]      = "&nbsp;1.02&nbsp;0107&nbsp;4&nbsp;1&nbsp;4&nbsp;16&nbsp;025";
$tblContent[$row][$col][TABLE_WIDTH]    = "15%";
$tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
$tblContent[$row][$col][TABLE_ALIGN]    = "center";
$col++;

$tblContent[$row][$col][TABLE_ISI]      = "&nbsp;Jasa Layanan Lain-lain";
$tblContent[$row][$col][TABLE_WIDTH]    = "50%";
$tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
$tblContent[$row][$col][TABLE_ALIGN]    = "left";
$col++;

$tblContent[$row][$col][TABLE_ISI]      = "&nbsp;";
$tblContent[$row][$col][TABLE_WIDTH]    = "3%";
$tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
$tblContent[$row][$col][TABLE_ALIGN]    = "right";
$col++;

$tblContent[$row][$col][TABLE_ISI]      = "&nbsp;";
$tblContent[$row][$col][TABLE_WIDTH]    = "12%";
$tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
$tblContent[$row][$col][TABLE_ALIGN]    = "right";
$col++;

$tblContent[$row][$col][TABLE_ISI]      = "&nbsp;";
$tblContent[$row][$col][TABLE_WIDTH]    = "15%";
$tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
$tblContent[$row][$col][TABLE_ALIGN]    = "center";
$col++;

while($dataBiaya = $dtaccess->Fetch($rs_biaya)){
    $sql = "select id_biaya, count(fol_id) as jumlah_layanan, sum(fol_nominal) as total_nominal
        from klinik_folio 
        where id_biaya = ".QuoteValue(DPE_CHAR,$dataBiaya["biaya_id"])."
        and fol_dibayar_when >= ".QuoteValue(DPE_TIMESTAMP,date_db($_POST["tgl_awal"])." ".$shiftStart[$_POST["cb_shift"]])."
        and fol_dibayar_when <= ".QuoteValue(DPE_TIMESTAMP,date_db($_POST["tgl_akhir"])." ".$shiftEnd[$_POST["cb_shift"]])."
        group by id_biaya";
    $rs_folio = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    $dataFolio = $dtaccess->Fetch($rs_folio);
    
    $col = 0;
    $row++;
    $tblContent[$row][$col][TABLE_ISI]      = "&nbsp;";
    $tblContent[$row][$col][TABLE_WIDTH]    = "5%";
    $tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
    $tblContent[$row][$col][TABLE_ALIGN]    = "center";
    $col++;
    
    $tblContent[$row][$col][TABLE_ISI]      = "&nbsp;";
    $tblContent[$row][$col][TABLE_WIDTH]    = "15%";
    $tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
    $tblContent[$row][$col][TABLE_ALIGN]    = "center";
    $col++;
    
    $tblContent[$row][$col][TABLE_ISI]      = "&nbsp;".$row."&nbsp;".$dataBiaya["biaya_nama"];
    $tblContent[$row][$col][TABLE_WIDTH]    = "50%";
    $tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
    $tblContent[$row][$col][TABLE_ALIGN]    = "left";
    $col++;
    
    if($dataBiaya["biaya_kode"]=='APT-00') $tblContent[$row][$col][TABLE_ISI] = "1";
    else $tblContent[$row][$col][TABLE_ISI]      = $dataFolio["jumlah_layanan"]."&nbsp;&nbsp;&nbsp;";
    $tblContent[$row][$col][TABLE_WIDTH]    = "3%";
    $tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
    $tblContent[$row][$col][TABLE_ALIGN]    = "center";
    $col++;
    
    if($dataBiaya["biaya_kode"]=='APT-00') $tblContent[$row][$col][TABLE_ISI]      = "<div style=\"float: left;display: inline-block;\">Rp.</div>".currency_format($dataFolio["total_nominal"])."&nbsp;";
    else $tblContent[$row][$col][TABLE_ISI]      = "<div style=\"float: left;display: inline-block;\">Rp.</div>".currency_format($dataBiaya["biaya_total"])."&nbsp;";
    $tblContent[$row][$col][TABLE_WIDTH]    = "12%";
    $tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
    $tblContent[$row][$col][TABLE_ALIGN]    = "right";
    $col++;
    
    $tblContent[$row][$col][TABLE_ISI]      = "<div style=\"float: left;display: inline-block;\">Rp.</div>".currency_format($dataFolio["total_nominal"])."&nbsp;";
    $tblContent[$row][$col][TABLE_WIDTH]    = "15%";
    $tblContent[$row][$col][TABLE_CLASS]    = "tablecontent-odd";
    $tblContent[$row][$col][TABLE_ALIGN]    = "right";
    $col++;
    
    $GrandTotal += $dataFolio["total_nominal"];
}
    
    $col = 0;
    $tblHeader[0][$col][TABLE_ISI]      = "NO.";
    $tblHeader[0][$col][TABLE_WIDTH]    = "3%";
    $col++;
    
    $tblHeader[0][$col][TABLE_ISI]      = "KODE REKENING";
    $tblHeader[0][$col][TABLE_WIDTH]    = "15%";
    $col++;
    
    $tblHeader[0][$col][TABLE_ISI]      = "KETERANGAN";
    $tblHeader[0][$col][TABLE_WIDTH]    = "50%";
    $col++;
    
    $tblHeader[0][$col][TABLE_ISI]      = "QTY";
    $tblHeader[0][$col][TABLE_WIDTH]    = "3%";
    $col++;
    
    $tblHeader[0][$col][TABLE_ISI]      = "BIAYA<br />LAYANAN";
    $tblHeader[0][$col][TABLE_WIDTH]    = "12%";
    $col++;
    
    $tblHeader[0][$col][TABLE_ISI]      = "JUMLAH";
    $tblHeader[0][$col][TABLE_WIDTH]    = "15%";
    $col++;
    
    $tblBottom[0][0][TABLE_ISI]         = "&nbsp;";
    $tblBottom[0][0][TABLE_COLSPAN]     = $col-1;
    
    $tblBottom[0][1][TABLE_ISI]         = "<div style=\"float: left;display: inline-block;\">Rp.</div>".currency_format($GrandTotal)."&nbsp;";
    $tblBottom[0][1][TABLE_ALIGN]       = "right";
    
/* end of building table */

/*
 * perintah untuk export excel
 */
if($_POST["btnExcel"]){
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=report_pembayaran_loket_'.$_POST["tgl_awal"].'.xls');
}

/*
 * perintah untuk cetak halaman
 */
if($_POST["btnCetak"]){
    echo "<script> window.print();</script>";
}

if(!$_POST["btnExcel"]) {
    echo $view->RenderBody("inosoft.css",true);
}
?>
<script language="JavaScript">
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
        alert("Tanggal Awal Harus Diisi");
        return false;
     }else{
        return true;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }else{
        return true;
     }

     if(!CheckDate(frm.tgl_akhir.value)) {
          return false;
     }else{
        return true;
     }
}
</script>
<style>
    @media print{
        body {
            font-size: 10px;
        }
        table {
            border-collapse: collapse;
            font-size: 10px;
        }
        
        table, th, td {
            border: 0.25px solid black;
        }
        
        .tablecontent-odd {
            border-bottom: none;
            border-top: none;
            font-size:10px;
        }
        
        .tabel_acc {
            font-size: 10px;
            display: inline-block;
            border: none;
        }
        
        .tabel_acc .jabatan {
            text-align:center;
            border: none;
            width: 30%;
        }
        
        .tabel_acc .nip {
            text-align:center;
            border: none;
            width: 30%;
        }
        
        .tabel_acc .nama {
            text-align:center;
            border: none;
            width: 30%;
            text-decoration: underline;
            font-weight: bold;
        }
        .tablenoborder {
            border: none;
        }
        
        .noShowOnPrint {
            display: none;
        }
    }
    
    @media screen{
        .tabel_acc {
            display: none;
        }
        
        .noShowOnPrint {
            display: block;
        }
    }
</style>

<?php if((!$_POST["btnExcel"]) && (!$_POST["btnCetak"])) { ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td colspan="<?php echo (count($dataSplit)+6)?>">&nbsp;<?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
     <tr class="tablecontent">
        <td width="15%">&nbsp;Tanggal</td>
        <td width="35%">
            <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
            <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
            <!--
            <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
            <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
             -->
        </td>
    </tr>
     <tr class="tablecontent">
        <td width="15%">&nbsp;Shift</td>
        <td>
            <?php
            for($i=1;$i<=3;$i++){
                $optShift[] = $view->RenderOption($i,"Shift ".$i,($_POST["cb_shift"]==$i)?"selected":null,null);
            }
            $optShift[] = $view->RenderOption("4","Semua Shift ",($_POST["cb_shift"]=="4")?"selected":null,null);
            echo $view->RenderComboBox("cb_shift","cb_shift",$optShift,"inputField",null,null);
            ?>    
        </td>
     </tr>
    <tr>
        <td class="tablecontent" colspan="6">
            <input type="submit" name="btnLanjut" value="Lanjut" class="button">
	    <input type="submit" name="btnCetak" value="Cetak" class="button">
	    <input type="submit" name="btnExcel" value="Export Excel" class="button">
        </td>
    </tr>
</table>

<BR>

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
   /* Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });*/
</script>
<?php } ?>
<table style="border: none;width: 100%;">
    <tr>
        <td colspan="2" style="text-align: center;text-decoration: underline;font-family: sans-serif;font-weight: bold;" class="tablenoborder">REKAPITULASI SETORAN PELAYANAN</td> 
    </tr>
    <tr>
        <td style="width: 25%;" class="tablenoborder">
            Unit Organisasi
        </td>
        <td style="width: 75%;" class="tablenoborder" colspan="2">
            :&nbsp;Balai Kesehatan Mata Masyarakat (BKMM) Surabaya
        </td>
    </tr>
    <tr>
        <td style="width: 25%;" class="tablenoborder">
            Tanggal
        </td>
        <td style="width: 75%;" class="tablenoborder" colspan="2">
            :&nbsp;<?php echo $rekapTanggal;?>
        </td>
    </tr>
    <tr>
        <td style="width: 25%;" class="tablenoborder">
            Jumlah
        </td>
        <td style="width: 75%;" class="tablenoborder"  colspan="2">
            :&nbsp;Rp.&nbsp;&nbsp;<?php echo currency_format($GrandTotal);?>
        </td>
    </tr>
    <tr>
        <td style="width: 25%;" class="tablenoborder">
            Terbilang
        </td>
        <td style="width: 75%;" class="tablenoborder"  colspan="2">
            :&nbsp;<?php echo HasilHuruf($GrandTotal);?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <?php echo $table->RenderView($tblHeader,$tblContent,$tblBottom); ?>
        </td>
    </tr>
    <?php if($_POST["btnCetak"]){ ?>
    <tr class="noShowOnPrint">
        <td colspan="2">
            <?php echo $view->RenderButton(BTN_BUTTON,"btnKembali","btnKembali","Kembali","inputField",null,"onclick=\"document.location.href='report_rekap_loket.php'\"",null); ?>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td colspan="2"  class="tablenoborder">
        <table class="tabel_acc" style="width: 100%;padding: 0px;">
            <tr style="height: 120px;">
                <td class="jabatan">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kepala Sub Bagian Tata Usaha&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />
                    RSMM Surabaya
                </td>
                <td class="jabatan">
                    &nbsp;&nbsp;&nbsp;Bendahara Penerimaan Pembantu&nbsp;&nbsp;&nbsp;<br />
                    RSMM Surabaya
                </td>
                <td class="jabatan">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kasir Penerima&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />
                    RSMM Surabaya
                </td>
            </tr>
            <tr>
                <td class="nama">GATOT EDI SOEGIANTO</td>
                <td class="nama">JUNNY FARI HANUM, R.O.</td>
                <td class="nama">RESTU APRILIAWANTI, S.E.</td>
            </tr>
            <tr>
                <td class="nip">NIP 19590204 198410 1 002</td>
                <td class="nip">NIP 19680816 199403 2 013</td>
                <td class="nip">NIP 19740425 200701 2 009</td>
            </tr>
        </table>
        </td>
    </tr>
</table>
<?php echo $view->RenderBodyEnd(); ?>
