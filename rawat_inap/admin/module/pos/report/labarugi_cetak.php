 <?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
    
     $lokasi = $ROOT."admin/images/logo";
     
     $sql_where[] = "a.penjualan_create >= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));
     $sql_where[] = "a.penjualan_create <= ".QuoteValue(DPE_DATE,DateAdd(date_db($_GET["tanggal_akhir"]),1));
     if ($_GET["id_petugas"]<> "--") $sql_where[] = "a.id_petugas = ".QuoteValue(DPE_NUMERIC,$_GET["id_petugas"]);
     if ($_GET["id_dep"]<> "--") $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$_GET["id_dep"]);
     if ($_GET["penjualan_tipe"]<> "--") $sql_where[] = "a.penjualan_tipe = ".QuoteValue(DPE_CHAR,$_GET["penjualan_tipe"]);
     
     $penjualanTipe=$_GET["penjualan_tipe"];
     $penjualanPetugas=$_GET["id_petugas"];
     $penjualanOutlet=$_GET["id_dep"];
     
     $sql_where = implode(" and ",$sql_where);
          
     $sql = "select distinct c.item_nama, c.id_item,c.transaksi_jumlah,c.transaksi_jumlah,c.id_penjualan,
              c.transaksi_harga_jual,c.transaksi_harga_beli,c.transaksi_total, 
             a.penjualan_create, a.penjualan_nomer,
             a.penjualan_petugas,a.penjualan_ppn,a.penjualan_id,
             a.penjualan_tipe, a.penjualan_total, a.penjualan_customer,a.id_petugas,
             b.dep_nama 
             from pos_penjualan a 
             left join global_departemen b on a.id_dep = b.dep_id
             left join pos_transaksi c on c.id_penjualan = a.penjualan_id
             left join pos_item d on d.item_id = c.id_item";
     $sql .= " where ".$sql_where;
     $sql .= " order by a.penjualan_create asc";
     $rs = $dtaccess->Execute($sql);
     $dataCashflow = $dtaccess->FetchAll($rs);
    
      
     $sql = "select * from mp_konfigurasi where konf_id=0";   
     $rs = $dtaccess->Execute($sql);
     $dataHeader = $dtaccess->Fetch($rs);
    
     
     $logoName = $lokasi."/".$dataHeader["dep_pic"];
	   
     
     function RenderHTML($email=false){
          global $dtaccess,$pembayaranId,$dataKonfigurasi,$dataCashflow,$monthName,
                 $lokasi,$dataPembayaran,$dataPembayaranRegistrasi,$totalPotongan,
                 $total,$totalDiterima,$logoName,$dataHeader,$penjualanTipe,
                 $penjualanPetugas,$penjualanOutlet;          
       
       
          $html = '
          
          <html>
          <head>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
          <title>.:: Welcome -- Sistem Billing Expressa ::.</title>
          
          <style>
          body {
               font-family: Verdana, Arial, Helvetica, sans-serif;
               font-size: 10px;
               font-weight: lighter;
          }
          
          
          table {
               border-collapse : collapse;
          }
          
          tr {
               font-family: Verdana, Arial, Helvetica, sans-serif;
               font-size: 10px;
               font-weight: lighter;
               height:18px;
          }
          
          .tableheader {
               font-family: Verdana, Arial, Helvetica, sans-serif;
               font-size: 12px;
               background-color:    #c2c6d3;
               font-weight: bold;
               text-transform: uppercase;
               height: 25px;
               background-position: left top;
          }
          
          @media print {
               #tableprint { display:none; }
          }
          </style>
          
          <script>
               function Print() {
                    window.print();
               }
               
               function Email(id) {
                    document.location.href = "gaji_cetak.php?id=" + id + "&export=email";
               }
          </script>
          
          </head>
          <body>
          
          <table width="800" border="0" cellpadding="0" cellspacing="0">
               <tr >
                    <td rowspan=4 width="30"><img src="'.$logoName.'" width="139" height="58"></td>
                    <td align="right" valign="bottom"><font size="3"><strong>'.$dataHeader["konf_header_satu"].'</strong></font></td>
               </tr>
               <tr style="height:9px;"><td colspan=3><hr ></td></tr>
               <tr><td align="right" valign="top"><strong>'.$dataHeader["konf_header_dua"].'</strong></td></tr>
               <tr><td align="right" valign="top"><strong>'.$dataHeader["konf_header_tiga"].'</strong></td></tr>
          </table>
          
          <BR clear="both"/>
          
          <table width="800" border="0" cellpadding="0" cellspacing="0">
          <tr>
               <td align="center" colspan="2"><strong><font size="3"><u>LAPORAN LABA RUGI</u></font></strong></td>       
          </tr>
          <tr>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
          </tr>';
          if ($penjualanTipe == "T") {
          $html .='<tr>
               <td align="left">Tipe bayar : Tunai</td>
               <td align="left">&nbsp;</td>
          </tr>'; }
          if ($penjualanTipe == "N") {
          $html .='<tr>
               <td align="left">Tipe bayar : Non Tunai</td>
               <td align="left">&nbsp;</td>
          </tr>'; }
          if ($penjualanPetugas <> "--") {
          $html .='<tr>
               <td align="left">Petugas : '.$dataCashflow[0]["penjualan_petugas"].'</td>
               <td align="left">&nbsp;</td>
          </tr>'; }
          $html .='</table>
         
          
          <BR clear="both"/>
          
          <table width="800" border="1" cellpadding="0" cellspacing="0">
          <tr>
               <td width="1%" align="center">NO</td>
               <td width="20%" align="center">WAKTU</td>
               <td width="19%" align="center">NO NOTA</td>
               <td width="10%" align="center"></td>
               <td width="10%" align="center"></td>
               <td width="10%" align="center"></td>
               <td width="10%" align="center"></td>';
 
          $html .='</tr>';
          $html .='<tr>
                   <td width="1%" align="center"></td>
                   <td width="20%" align="center">NO</td>
                   <td width="19%" align="center">NAMA MENU</td>
                   <td width="10%" align="center">JUMLAH</td>
                   <td width="10%" align="center">HARGA JUAL</td>
                   <td width="10%" align="center">HARGA BELI</td>
                   <td width="10%" align="center">LABA RUGI</td>';
          
                  for($i=0,$n=count($dataCashflow);$i<$n;$i++){ 
                  	    
                        if ($dataCashflow[$i]["penjualan_tipe"]=='T') $tipeBayar="Tunai";
                        else $tipeBayar="Non Tunai";
                      if($dataCashflow[$i]["penjualan_id"]!=$dataCashflow[$i-1]["penjualan_id"]){
                        $total += $dataCashflow[$i]["penjualan_total"];
                  	    $totalTax += $dataCashflow[$i]["penjualan_ppn"];
                      $html .= '<tr>';
                      $html .= '<td align="center">'.($i+1).'</td>';
                      $html .= '<td align="center">'.FormatTimestamp($dataCashflow[$i]["penjualan_create"]).'</td>';
                      $html .= '<td align="left">'.$dataCashflow[$i]["penjualan_nomer"].'</td>';
                      $html .= '<td align="center">&nbsp;</td>';
                      $html .= '<td align="center">&nbsp;</td>';
                      $html .= '<td align="center">&nbsp;</td>';
                      $html .= '<td align="center">&nbsp;</td>';
                      $html .= '</tr>';
                      $j=0;
                      }
                      $j++;
                      $html .= '<tr>';
                      $html .= '<td style="text-align:right">&nbsp;</td>';
                      $html .= '<td style="text-align:right">'.$j.'&nbsp;</td>';
                      $html .= '<td style="text-align:right">&nbsp;'.$dataCashflow[$i]["item_nama"].'</td>';
                      $html .= '<td style="text-align:right">&nbsp;'.currency_format($dataCashflow[$i]["transaksi_jumlah"]).'</td>';
                      $html .= '<td style="text-align:right">&nbsp;'.currency_format($dataCashflow[$i]["transaksi_harga_jual"]).'</td>';
                      $html .= '<td style="text-align:right">&nbsp;'.currency_format($dataCashflow[$i]["transaksi_harga_beli"]).'</td>';
                      $html .= '<td style="text-align:right">&nbsp;'.currency_format(($dataCashflow[$i]["transaksi_harga_jual"]-$dataCashflow[$i]["transaksi_harga_beli"])*$dataCashflow[$i]["transaksi_jumlah"]).'</td>';
                      $totalKeuntungan=$totalKeuntungan+($dataCashflow[$i]["transaksi_harga_jual"]-$dataCashflow[$i]["transaksi_harga_beli"])*$dataCashflow[$i]["transaksi_jumlah"];
                      
                    }
                
    
          $html .='
               
          <tr style="border-bottom-style:double"> 
             <td colspan="6" align="right" class="tablecontent">&nbsp;&nbsp;<strong>Total</strong></td>
             <td align="right" class="tablecontent-odd"><strong>'.currency_format($totalKeuntungan).'</strong></td>';
                     
        
          $html .='
          </tr>';
          $html .='
          </table>';
          
          if(!$email) $html .= '
          <BR clear="both">
          <table width="500" border="0" cellpadding="0" cellspacing="0" id="tableprint">
          <tr>
               <td align="right">
                    <input type="button" name="btnPrint" id="btnPrint" value="Cetak" onClick="Print();">
                    
               </td>
          </tr>
          </table>
          ';
          
          $html .= '
          </body>
          </html>
           
          ';          
          
          return $html;
     }

     
	

     echo RenderHTML();
?>


