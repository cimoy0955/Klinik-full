<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
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
     
     if($_POST["tgl_awal"]) $sql_where[] = "CAST(a.fol_dibayar_when as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "CAST(a.fol_dibayar_when as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
          $sql_where_2[] = "2=2";
     
     if($_POST["tgl_awal"]) $sql_where_2[] = "CAST(a.trans_create as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where_2[] = "CAST(a.trans_create as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
     $sql_where_3[] = "3=3";
     
     if($_POST["tgl_awal"]) $sql_where_3[] = "CAST(a.penjualan_tanggal as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where_3[] = "CAST(a.penjualan_tanggal as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     


   

     // === nyari jumlah pasien baru
     $sqlRegis = "select sum(fol_dibayar) as total from klinik.klinik_folio a";
     $sql_where_pasien = $sql_where;
     $sqlRegis = $sqlRegis." where  fol_jenis = 'A' and ".implode(" and ",$sql_where_pasien);
     
     $dataRegis = $dtaccess->Fetch($sqlRegis);
     // -- end ---          
     
     // === nyari jumlah total bayar tindakan
     $sqlTindakanBayar = "select sum(fol_dibayar) as total from klinik.klinik_folio a";
     $sql_where_tindakan = $sql_where;
     $sqlTindakanBayar = $sqlTindakanBayar." where  fol_jenis like '%T%' and ".implode(" and ",$sql_where_tindakan);
    
     $dataTindakanTotalBayar = $dtaccess->Fetch($sqlTindakanBayar);
     // -- end ---
     
     // === nyari jumlah total bayar diagnostik
     $sqlDiagnostikBayar = "select sum(fol_dibayar) as total from klinik.klinik_folio a";
     $sql_where_diagnostik = $sql_where;
     $sqlDiagnostikBayar = $sqlDiagnostikBayar." where  fol_jenis like '%D%' and ".implode(" and ",$sql_where_diagnostik);
    
     $dataDiagnostikTotalBayar = $dtaccess->Fetch($sqlDiagnostikBayar);
     // -- end ---
     
     // === nyari jumlah total bayar PreOp
     $sqlPreopBayar = "select sum(fol_dibayar) as total from klinik.klinik_folio a";
     $sql_where_preop = $sql_where;
     $sqlPreopBayar = $sqlPreopBayar." where  fol_jenis like '%P%' and ".implode(" and ",$sql_where_preop);
    
     $dataPreopTotalBayar = $dtaccess->Fetch($sqlPreopBayar);
     // -- end ---
     
     // === nyari jumlah total bayar operasi
     $sqlOperasiBayar = "select sum(fol_dibayar) as total from klinik.klinik_folio a";
     $sql_where_operasi = $sql_where;
     $sqlOperasiBayar = $sqlOperasiBayar." where  fol_jenis like '%O%' and ".implode(" and ",$sql_where_operasi);
    
     $dataOperasiTotalBayar = $dtaccess->Fetch($sqlOperasiBayar);
     // -- end ---
     
     // === nyari jumlah total bayar bedah
     $sqlBedahBayar = "select sum(fol_dibayar) as total from klinik.klinik_folio a";
     $sql_where_bedah = $sql_where;
     $sqlBedahBayar = $sqlBedahBayar." where  fol_jenis like '%B%' and ".implode(" and ",$sql_where_bedah);
    
     $dataBedahTotalBayar = $dtaccess->Fetch($sqlBedahBayar);
     // -- end ---
     
     // === nyari jumlah total bayar kamar
     $sqlKamarBayar = "select sum(fol_dibayar) as total from klinik.klinik_folio a";
     $sql_where_kamar = $sql_where;
     $sqlKamarBayar = $sqlKamarBayar." where  fol_jenis like '%K%' and ".implode(" and ",$sql_where_kamar);
    
     $dataKamarTotalBayar = $dtaccess->Fetch($sqlKamarBayar);
     // -- end ---
     
     // === nyari jumlah total bayar terapi
     $sqlTerapiBayar = "select sum(fol_dibayar) as total from klinik.klinik_folio a";
     $sql_where_terapi = $sql_where;
     $sqlTerapiBayar = $sqlTerapiBayar." where  fol_jenis like '%Q%' and ".implode(" and ",$sql_where_terapi);
    
     $dataTerapiTotalBayar = $dtaccess->Fetch($sqlTerapiBayar);
     // -- end ---
     
     // === nyari jumlah total bayar apotik swadaya
     $sqlSwadayaBayar = "select sum(trans_harga_jual) as total from apotik_swadaya.swadaya_transaksi a";
     $sql_where_swadaya = $sql_where_2;
     $sqlSwadayaBayar = $sqlSwadayaBayar." where  trans_tipe = 'J' and ".implode(" and ",$sql_where_swadaya);
    
     $dataSwadayaTotalBayar = $dtaccess->Fetch($sqlSwadayaBayar);
     // -- end ---
     
     // === nyari jumlah total bayar optik
     $sqlOptikBayar = "select sum(CAST(a.penjualan_total as numeric)) as total from optik.optik_penjualan a";
     $sql_where_optik = $sql_where_3;
     $sqlOptikBayar = $sqlOptikBayar." where ".implode(" and ",$sql_where_optik);
    
     $dataOptikTotalBayar = $dtaccess->Fetch($sqlOptikBayar);
     // -- end ---
  
     // === nyari jumlah total bayar biaya tambahan
     $sqlTambahanBayar = "select sum(fol_dibayar) as total from klinik.klinik_folio a";
     $sql_where_tambahan = $sql_where;
     $sqlTambahanBayar = $sqlTambahanBayar." where  fol_jenis like '%X%' and ".implode(" and ",$sql_where_tambahan);
   
     $dataTambahanTotalBayar = $dtaccess->Fetch($sqlTambahanBayar);
     // -- end ---

     $tableHeader = "Report Global Pembayaran";
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
               <td class="tablecontent" width="50%" align="left">Total Registrasi</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataRegis["total"]);?></td>	     
          </tr> 
                
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Tindakan Rawat Inap Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataTindakanTotalBayar["total"]);?></td>	     
          </tr> 
          
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Tindakan Diagnostik Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataDiagnostikTotalBayar["total"]);?></td>	     
          </tr> 
          
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Tindakan PreOp Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataPreopTotalBayar["total"]);?></td>	     
          </tr> 
          
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Tindakan Operasi Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataOperasiTotalBayar["total"]);?></td>	     
          </tr> 
          
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Tindakan Bedah Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataBedahTotalBayar["total"]);?></td>	     
          </tr>
          
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Kamar Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataKamarTotalBayar["total"]);?></td>	     
          </tr> 
          
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Terapi Obat Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataTerapiTotalBayar["total"]);?></td>	     
          </tr>  
                   
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Apotik Swadaya Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataSwadayaTotalBayar["total"]);?></td>	     
          </tr>        
          
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Optik Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataOptikTotalBayar["total"]);?></td>	     
          </tr>
          
          <tr>
               <td class="tablecontent" width="50%" align="left">Total Biaya Tambahan Bayar</td>
               <td width="50%" class="tablecontent-odd"  align="center">Rp. <?php echo currency_format($dataTambahanTotalBayar["total"]);?></td>	     
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
