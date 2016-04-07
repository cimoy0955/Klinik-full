<?php
     require_once("root.inc.php");
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
     $table = new InoTable("table","90%","left");

     if (!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date('d-m-Y');
     $sql_where[] = "a.fol_lunas = ".QuoteValue(DPE_CHAR,"y"); 
     
     if($_POST["tgl_awal"]) $sql_where[] = "CAST(a.fol_dibayar_when as DATE) = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     $sql_where[] = "d.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA);
     $sql_where = implode(" and ",$sql_where);

     $sql = "select cast(fol_dibayar_when as date), status_id, sum(fol_dibayar) as sub_total 
            from klinik.klinik_folio a 
            left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
            left join global.global_status_pasien c on CAST(c.status_id as char) = b.biaya_jenis 
            left join klinik.klinik_registrasi d on d.reg_id = a.id_reg";
     $sql .= " where ".$sql_where; 
     $sql .= " group by 1, 2 order by 1, 2";
     // echo $sql;
     $dataFolio = $dtaccess->FetchAll($sql);
     for ($i=0; $i < count($dataFolio); $i++) { 
          if (!$dataFolio[$i]["status_id"]) {
               $dataFolio[$i]["status_id"] = 8;
          }
          $viewData[$dataFolio[$i]["status_id"]] = $dataFolio[$i]["sub_total"];
     } 

     if (isset($viewData)) {
          $grandTotal = array_sum($viewData);
     }

     $sql = "select status_id, status_nama, status_rekening from global.global_status_pasien order by status_id";
     $rs_dataKas = $dtaccess->Execute($sql);
     $dataKas = $dtaccess->FetchAll($rs_dataKas);

     $counterHeader=0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "3";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Untuk Pembayaran";
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "4";

     $counterHeader=0;
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Kode Rekening";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "25%"; 
     $tbHeader[1][$counterHeader][TABLE_ROWSPAN] = "2"; 
     $counterHeader++;
     
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Uraian Rincian Obyek";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "25%";
     $tbHeader[1][$counterHeader][TABLE_ROWSPAN] = "2"; 
     $counterHeader++;

     $tbHeader[1][$counterHeader][TABLE_ISI] = "Jumlah (Rp.)";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "25%"; 
     $tbHeader[1][$counterHeader][TABLE_COLSPAN] = "2"; 

     $counterHeader=0;
     $tbHeader[2][$counterHeader][TABLE_ISI] = "Umum";
     $tbHeader[2][$counterHeader][TABLE_WIDTH] = "25%"; 
     $counterHeader++;

     $tbHeader[2][$counterHeader][TABLE_ISI] = "Paviliun";
     $tbHeader[2][$counterHeader][TABLE_WIDTH] = "25%"; 
     $counterHeader++;

     for ($i=0, $counter=0; $i < count($dataKas); $i++, $counter=0) { 
          if ($i == 0) {
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";
               $tbContent[$i][$counter][TABLE_ROWSPAN] = count($dataKas);
               $counter++;
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataKas[$i]["status_rekening"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataKas[$i]["status_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "Rp.&nbsp;".currency_format($viewData[$dataKas[$i]["status_id"]]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
     }

     $counterBottom=0;
     $tbBottom[0][$counterBottom][TABLE_ISI] = "Sub Total&nbsp;&nbsp;";
     $tbBottom[0][$counterBottom][TABLE_ALIGN] = "right";
     $tbBottom[0][$counterBottom][TABLE_COLSPAN] = "3";
     $counterBottom++;

     $tbBottom[0][$counterBottom][TABLE_ISI] = "Rp.&nbsp;".currency_format($grandTotal);
     $tbBottom[0][$counterBottom][TABLE_ALIGN] = "right";
     $counterBottom++;

     $tbBottom[0][$counterBottom][TABLE_ISI] = "&nbsp;";
     $tbBottom[0][$counterBottom][TABLE_ALIGN] = "right";
     $counterBottom++;

     $counterBottom=0;
     $tbBottom[1][$counterBottom][TABLE_ISI] = "TOTAL&nbsp;&nbsp;&nbsp;";
     $tbBottom[1][$counterBottom][TABLE_ALIGN] = "right";
     $tbBottom[1][$counterBottom][TABLE_COLSPAN] = "3";
     $counterBottom++;

     $tbBottom[1][$counterBottom][TABLE_ISI] = "Rp.&nbsp;".currency_format($grandTotal);
     $tbBottom[1][$counterBottom][TABLE_ALIGN] = "right";
     $tbBottom[1][$counterBottom][TABLE_COLSPAN] = "2";

?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<style type="text/css">
     @media screen {
          .no_print {
               display: block;
          }

          .printed {
               display: none;
          }
     }

     @media print{
          .no_print { 
               display: none; 
          }

          .printed {
               display: block;
          }


          table, tr, td {
               border-collapse: collapse;
               font-size: 10px;
          }
     }
</style>
<div class="no_print">
     <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td>&nbsp;Rekapitulasi Setoran Rawat Jalan dan Inap</td>
          </tr>
     </table>

     <form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
     <table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
          <tr class="tablecontent">
               <td width="10%">&nbsp;Tanggal</td>
               <td width="35%">
                    <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>" readonly="readonly"/>
                    <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               </td>
               <td width="10%">&nbsp;</td>
               <td width="45%">&nbsp;</td>
          </tr>
          <tr>
               <td class="tablecontent" colspan="6">
                    <input type="submit" name="btnLanjut" value="Lanjut" class="button">
                    <input type="button" name="btnCetak" value="Cetak" class="button" onclick="window.print();">
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
     </script>
</div>
<div class="printed">
     <table width="90%" border="0" align="center">
          <tr>
               <td style="font-size: 18px; text-align: center;">PEMERINTAH PROPINSI JAWA TIMUR</td>
          </tr>
          <tr>
               <td style="font-size: 16px; text-align: center;">Rekapitulasi Setoran Rawat Jalan dan Inap</td>
          </tr>
     </table>
</div>
<div style="margin-top: 25px; margin-bottom: 25px;">
     <table border="0" width="90%" align="center">
          <tr>
               <td width="20%">Organisasi</td>
               <td width="2%">:</td>
               <td width="78%">Rumah Sakit Mata Masyarakat Jawa Timur</td>
          </tr>
          <tr>
               <td width="20%">Tanggal</td>
               <td width="2%">:</td>
               <td width="78%"><?php echo format_date_long(date_db($_POST["tgl_awal"])); ?></td>
          </tr>
          <tr>
               <td width="20%">No. Rekapitulasi</td>
               <td width="2%">:</td>
               <td width="78%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/101.6/PN-SWA/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/<?php echo substr($_POST["tgl_awal"], -4); ?></td>
          </tr>
          <tr>
               <td width="20%">Uang Sebesar</td>
               <td width="2%">:</td>
               <td width="78%">Rp.&nbsp;<?php echo currency_format($grandTotal);?></td>
          </tr>
          <tr>
               <td width="20%">Dengan Huruf</td>
               <td width="2%">:</td>
               <td width="78%"><?php echo HasilHuruf($grandTotal); ?></td>
          </tr>
     </table>
</div>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

<div class="printed">
     <table border="0" width="90%" align="center">
          <tr style="height: 100px;vertical-align: bottom;">
               <td width="33.3%" align="center">
                    Kepala Sub. Bagian Tata Usaha
               </td>
               <td width="33.3%" align="center">
                    Bendahara Penerimaan Pembantu
               </td>
               <td width="33.3%" align="center">
                    Kasir Penerima
               </td>
          </tr>
          <tr>
               <td width="33.3%" align="center">
                    RSMM Jawa Timur
               </td>
               <td width="33.3%" align="center">
                    RSMM Jawa Timur
               </td>
               <td width="33.3%" align="center">
                    RSMM Jawa Timur
               </td>
          </tr>
          <tr style="height: 100px;vertical-align: bottom;">
               <td style="font-weight: bold;text-decoration: underline; text-align: center;">
                    VITRI ARIYANI, S.K.M. M.Kes.
               </td>
               <td style="font-weight: bold;text-decoration: underline; text-align: center;">
                    JUNNY FARI HANUM, R.O.
               </td>
               <td style="font-weight: bold;text-decoration: underline; text-align: center;">
                    RISTU APRILIAWANTI, S.E.
               </td>
          </tr>
          <tr>
               <td style=" text-align: center;">
                    NIP. 19721015 199602 2 001
               </td>
               <td style=" text-align: center;">
                    NIP. 19680615 199403 2 013
               </td>
               <td style=" text-align: center;">
                    NIP. 19740425 200701 2 009
               </td>
          </tr>
     </table>
</div>

<?php echo $view->RenderBodyEnd();?>