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
     
     //$userData = $auth->GetUserData();
     
          
     $sql = "select a.penjualan_nomer,a.penjualan_ppn,a.id_petugas,b.item_nama,
             b.transaksi_jumlah,b.transaksi_harga_jual,b.transaksi_total,d.satuan_jual_nama from pos_penjualan a
             left join pos_transaksi b on a.penjualan_id=b.id_penjualan
             left join pos_item c on b.id_item=c.item_id
             left join pos_satuan_jual d on d.satuan_jual_id=c.id_satuan_jual
              where penjualan_id = ".QuoteValue(DPE_CHAR,$_GET["penjualan_id"]);
     $rs = $dtaccess->Execute($sql);
     $dataPenjualan = $dtaccess->FetchAll($rs);
      $sql = "SELECT usr_id,usr_name FROM global_auth_user 
                  WHERE usr_id = ".QuoteValue(DPE_NUMERIC,$dataPenjualan[0]["id_petugas"]);
      $rs = $dtaccess->Execute($sql);
      $dataUserName = $dtaccess->Fetch($rs);
    
     $sql = "select konf_pic from mp_konfigurasi where konf_id=0";
     $rs = $dtaccess->Execute($sql);
     $dataKonfigurasi = $dtaccess->Fetch($rs);
     
     $sql = "select * from mp_konfigurasi where konf_id=0";
     $rs = $dtaccess->Execute($sql);
     $dataHeader = $dtaccess->Fetch($rs);
     
     $logoName = $lokasi."/".$dataKonfigurasi["konf_pic"];

     if ($dataHeader["konf_cetak"]=='B')
     {
     function RenderHTML($email=false){
          global $dtaccess,$pembayaranId,$dataKonfigurasi,$dataPenjualan,$monthName,
                 $lokasi,$dataPembayaran,$dataPenjualan,$dataPembayaranRegistrasi,$totalPotongan,
                 $total,$totalDiterima,$logoName,$dataUserName,$dataHeader;          
       
       
          $html = '
          
          <html>
          <head>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
          <title>.:: Printing -- CPAD Billing System ::.</title>
          
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
               font-size: 12px;
               font-weight: lighter;
               height:18px;
          }
          
          .tableheader {
               font-family: Verdana, Arial, Helvetica, sans-serif;
               font-size: 14px;
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
               
                window.print();
          </script>
          
          </head>
          <body>
          
          <table width="800" border="0" cellpadding="0" cellspacing="0">
               <tr >
                    <td rowspan=4 width="30"></td>
                    <td align="right" valign="bottom"><font size="3"><strong>'.$dataHeader["konf_header_satu"].'</strong></font></td>
               </tr>
               <tr style="height:9px;"><td colspan=3><hr ></td></tr>
               <tr><td align="right" valign="top"><strong>'.$dataHeader["konf_header_dua"].'</strong></td></tr>
               <tr><td align="right" valign="top"><strong>'.$dataHeader["konf_header_tiga"].'</strong></td></tr>
          </table>
          
          <BR clear="both"/>
          
          <table width="800" border="0" cellpadding="0" cellspacing="0">
          <tr>
               <td align="left" colspan="2"><strong><font size="3">NOTA</font></strong>&nbsp;&nbsp;&nbsp;
               <font size="2">No. '.$dataPenjualan[0]["penjualan_nomer"].'</font></td>
               <td align="right">&nbsp;&nbsp;'.format_date_long(date("Y-m-d")).'</td>       
          </tr>
          
          </table>
          
          
          <BR clear="both"/>
          
          <table width="800" border="1" cellpadding="0" cellspacing="0">
          <tr>
               <td width="1%" align="center">No</td>
               <td width="40%" align="center">Nama Barang</td>
               <td width="10%" align="center">Jumlah</td>
               <td width="10%" align="center">Satuan</td>
               <td width="15%" align="center">Harga</td>
               <td width="20%" align="center">Sub Total</td>
              
 
          </tr>
         ';
                  for($i=0,$n=count($dataPenjualan);$i<$n;$i++){ 
                  	
		
                      $html .= '<tr>';
                      $html .= '<td align="center">'.($i+1).'</td>';
                      $html .= '<td align="left">'.$dataPenjualan[$i]["item_nama"].'</td>';
                      $html .= '<td align="center">'.$dataPenjualan[$i]["transaksi_jumlah"].'</td>';
                      $html .= '<td align="center">'.$dataPenjualan[$i]["satuan_jual_nama"].'</td>';
                      $html .= '<td align="right">'.currency_format($dataPenjualan[$i]["transaksi_harga_jual"]).'</td>';
                      $html .= '<td align="right">'.currency_format($dataPenjualan[$i]["transaksi_total"]).'</td>';
                      $total=$total+$dataPenjualan[$i]["transaksi_total"];   
                    $html .= '</tr>';
                    }
                     $html .='
          <tr style="border-bottom-style:double"> 
             <td colspan="5" align="right" class="tablecontent">&nbsp;&nbsp;<strong>Jumlah&nbsp;Rp.</strong></td>
             <td align="right" class="tablecontent-odd"  colspan="2"><strong>'.currency_format($total).'</strong>&nbsp;&nbsp;</td>         
          </tr>
          </table><br>
          <table>
          <tr> 
             <td align="left"><strong>Tanda Terima</strong></td> 
             <td width="100"></td>          
             <td></td>
          </tr>
          <tr> 
             <td align="left">&nbsp;</td> 
             <td width="100"></td>          
             <td></td>
          </tr>
          <tr> 
             <td align="left">&nbsp;</td> 
             <td width="100"></td>          
             <td></td>
          </tr>
          <tr> 
             <td align="left">('.$dataUserName["usr_name"].')</td> 
             <td width="100"></td>          
             <td></td>
          </tr>
          </table> 
          ';
          
          if(!$email) $html .= '
          <BR clear="both">
          ';
          
          $html .= '
          </body>
          </html>
           
          ';          
          
          return $html;
     }
     }
     else if ($dataHeader["konf_cetak"]=='K')
     {
     function RenderHTML($email=false){
          global $dtaccess,$pembayaranId,$dataKonfigurasi,$dataPenjualan,$monthName,
                 $lokasi,$dataPembayaran,$dataPenjualan,$dataPembayaranRegistrasi,$totalPotongan,
                 $total,$totalDiterima,$logoName,$dataUserName,$dataHeader;          
       
       
          $html = '
          
          <html>
          <head>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
          <title>.:: Printing -- CPAD Billing System ::.</title>
          
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
               font-size: 12px;
               font-weight: lighter;
               height:18px;
          }
          
          .tableheader {
               font-family: Verdana, Arial, Helvetica, sans-serif;
               font-size: 14px;
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
               
                window.print();
          </script>
          
          </head>
          <body>
          
          <table width="230" border="0" cellpadding="0" cellspacing="0">
               <tr >
                    <td align="center" valign="bottom"><font size="3"><strong>'.$dataHeader["konf_header_satu"].'</strong></font></td>
               </tr>
               
               <tr><td align="center" valign="top"><strong>'.$dataHeader["konf_header_dua"].'</strong></td></tr>
               <tr><td align="center" valign="top"><strong>'.$dataHeader["konf_header_tiga"].'</strong></td></tr>
               <td align="center">Tgl : '.format_date(date("Y-m-d")).'&nbsp;&nbsp;&nbsp;Jam : '.format_date_long(date("H:i:s")).'</td>
               <tr style="height:9px;"><td colspan=3><hr ></td></tr>
          </table>
          
          <BR clear="both"/>
          
          <table width="200" border="0" cellpadding="0" cellspacing="0">
          <tr>
               <td align="center" colspan="2"><font size="3">NO : '.$dataPenjualan[0]["penjualan_nomer"].'</font></td>
                    
          </tr>
          
          </table>
          
          
          <BR clear="both"/>
          
          <table width="200" border="0" cellpadding="0" cellspacing="0">
          ';
                  for($i=0,$n=count($dataPenjualan);$i<$n;$i++){ 
                  	
		
                      $html .= '<tr>';
                      $html .= '<td align="center">'.$dataPenjualan[$i]["transaksi_jumlah"].'&nbsp</td>';
                      $html .= '<td align="left">'.$dataPenjualan[$i]["item_nama"].'</td>';
                      $html .= '<td align="right">&nbsp;</td>';
                      $html .= '<td align="right">'.currency_format($dataPenjualan[$i]["transaksi_total"]).'</td>';
                      $total=$total+$dataPenjualan[$i]["transaksi_total"];   
                    $html .= '</tr>';
                    }
                     $html .='
          <tr> 
             <td colspan="3" align="right" class="tablecontent">&nbsp;&nbsp;Tax :</td>
             <td align="right" class="tablecontent-odd"  colspan="2">'.currency_format($dataPenjualan[0]["penjualan_ppn"]).'</td>         
          </tr>
          <tr> 
             <td colspan="3" align="right" class="tablecontent">&nbsp;&nbsp;</td>
             <td align="right" class="tablecontent-odd"  colspan="2">---------------------</td>         
          </tr>
          <tr> 
             <td colspan="3" align="right" class="tablecontent">&nbsp;&nbsp;<font size="3"><strong>TOTAL&nbsp;.</strong></font></td>
             <td align="right" class="tablecontent-odd"  colspan="2"><font size="3"><strong>'.currency_format($total+$dataPenjualan[0]["penjualan_ppn"]).'</font></strong>&nbsp;&nbsp;</td>         
          </tr>
          </table><br>
          <table width="200">
          <tr> 
             <td align="left"><strong></strong></td> 
             <td ></td>          
             <td></td>
          </tr>
          <tr> 
             <td colspan="3" align="center">--- Terima Kasih ---</td> 
          </tr>
          </table> 
          ';
          
          if(!$email) $html .= '
          <BR clear="both">
          ';
          
          $html .= '
          </body>
          </html>
           
          ';          
          
          return $html;
     }
     }
	

     echo RenderHTML();
?>


