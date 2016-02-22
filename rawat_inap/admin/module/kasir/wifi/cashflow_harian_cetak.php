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
     
          
     $sql = "select a.trans_nama, a.trans_ket,a.trans_create, a.trans_petugas,a.trans_harga_total, c.usr_loginname, a.trans_jenis, c.usr_name    
               from mp_member_trans a
               left join mp_member b on a.id_member = b.member_id 
               left join global_auth_user c on b.id_usr = c.usr_id
               where a.trans_create >= ".QuoteValue(DPE_DATE,getDateToday())."
               and id_petugas = ".QuoteValue(DPE_NUMERIC,$_GET["id_petugas"])."
               order by a.trans_jenis,a.trans_create asc";
     $rs = $dtaccess->Execute($sql);
     $dataCashflow = $dtaccess->FetchAll($rs);

     
     $nominal_registrasi=currency_format($dataPembayaranRegistrasi["pembayaran_siswa_nominal"]);
	   $totalPotongan=$dataPembayaranRegistrasi["pembayaran_siswa_voucher"]+$dataPembayaran["pembayaran_siswa_voucher"];
	   $totalTerima=$dataPembayaranRegistrasi["pembayaran_siswa_nominal"]+$dataPembayaran["pembayaran_siswa_nominal"];
	   $totalDiterima=$totalTerima-$totalPotongan;
     
     $sql = "select * from mp_konfigurasi where konf_id=0";
     $rs = $dtaccess->Execute($sql);
     $dataKonfigurasi = $dtaccess->Fetch($rs);
     
     $logoName = $lokasi."/".$dataKonfigurasi["konf_pic"];
	   
     
     function RenderHTML($email=false){
          global $dtaccess,$pembayaranId,$dataKonfigurasi,$dataCashflow,$monthName,
                 $lokasi,$dataPembayaran,$dataPembayaranRegistrasi,$totalPotongan,
                 $total,$totalDiterima,$logoName;          
       
       
          $html = '
          
          <html>
          <head>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
          <title>.:: Welcome -- Software BCREIN ::.</title>
          
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
                    <td rowspan=4 width="30"><img src="'.$logoName.'"></td>
                    <td align="right" valign="bottom"><font size="3"><strong>'.$dataKonfigurasi["konf_header_satu"].'</strong></font></td>
               </tr>
               <tr style="height:9px;"><td colspan=3><hr ></td></tr>
               <tr><td align="right" valign="top"><strong>'.$dataKonfigurasi["konf_header_dua"].'</strong></td></tr>
               <tr><td align="right" valign="top"><strong>'.$dataKonfigurasi["konf_header_tiga"].'</strong></td></tr>
          </table>
          
          <BR clear="both"/>
          
          <table width="800" border="0" cellpadding="0" cellspacing="0">
          <tr>
               <td align="center" colspan="2"><strong><font size="3"><u>LAPORAN CASHFLOW HARIAN</u></font></strong></td>       
          </tr>
          <tr>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
          </tr>
          <tr>
               <td align="left"><strong>Tanggal</strong></td>
               <td align="left">:&nbsp;&nbsp;'.format_date_long(date("Y-m-d")).'</td>
          </tr>
          <tr>
               <td align="left"><strong>Petugas</strong></td>
               <td align="left">:&nbsp;&nbsp;'.$dataCashflow[0]["trans_petugas"].'</td>
          </tr>
          </table>
          
          
          <BR clear="both"/>
          
          <table width="800" border="1" cellpadding="0" cellspacing="0">
          <tr>
               <td width="1%" align="center">NO</td>
               <td width="20%" align="center">NAMA</td>
               <td width="19%" align="center">WAKTU</td>
               <td width="10%" align="center">PEMASUKAN</td>
               <td width="10%" align="center">PENGELUARAN</td>
               <td width="40%" align="center">KETERANGAN</td>
 
          </tr>
         ';
                  for($i=0,$n=count($dataCashflow);$i<$n;$i++){ 
                  	if ($dataCashflow[$i]["trans_jenis"]=='O') 
                    { 
                        $total -= $dataCashflow[$i]["trans_harga_total"];
                        $totalPengeluaran += $dataCashflow[$i]["trans_harga_total"];
                    } else {
		                     $total += $dataCashflow[$i]["trans_harga_total"];
                         $totalPendapatan += $dataCashflow[$i]["trans_harga_total"]; 
                    };
		
                      $html .= '<tr>';
                      $html .= '<td align="center">'.($i+1).'</td>';
                      $html .= '<td>'.$dataCashflow[$i]["trans_nama"].'</td>';
                      $html .= '<td align="center">'.FormatTimestamp($dataCashflow[$i]["trans_create"]).'</td>';
                       
                      if ($dataCashflow[$i]["trans_jenis"]=='O') {
                        $html .= '<td align="right">&nbsp</td>'; 
                        $html .= '<td align="right">'.currency_format($dataCashflow[$i]["trans_harga_total"]).'</td>';
                      } else {
                        $html .= '<td align="right">'.currency_format($dataCashflow[$i]["trans_harga_total"]).'</td>';
                        $html .= '<td align="right">&nbsp</td>'; 
                      }  
                      if ($dataCashflow[$i]["trans_jenis"]=='M')
                      {
                        $html .= '<td>&nbsp;Pemasukan Multiplayer</td>';
                      } elseif ($dataCashflow[$i]["trans_jenis"]=='W') {
                        $html .= '<td>&nbsp;Pemasukan Warnet</td>';
                      } elseif ($dataCashflow[$i]["trans_jenis"]=='A') {
                        $html .= '<td>&nbsp;Kas Awal</td>';
                       } elseif ($dataCashflow[$i]["trans_jenis"]=='P') {
                        $html .= '<td>&nbsp;Pemasukan Point of Sale</td>';  
                      } else   {  
                      $html .= '<td>&nbsp;'.$dataCashflow[$i]["trans_ket"].'</td>';
                      }
                         
                    $html .= '</tr>';
                    }
                     $html .='
          
          <tr"> 
             <td colspan="3" align="right" class="tablecontent">&nbsp;&nbsp;&nbsp;</strong></td>
             <td align="right" class="tablecontent-odd" ><strong>'.currency_format($totalPendapatan).'</strong>&nbsp;&nbsp;</td>
             <td align="right" class="tablecontent-odd" ><strong>'.currency_format($totalPengeluaran).'</strong>&nbsp;&nbsp;</td>
             <td align="left" class="tablecontent">&nbsp;&nbsp;&nbsp;</strong></td>            
          </tr>     
          <tr style="border-bottom-style:double"> 
             <td colspan="3" align="right" class="tablecontent">&nbsp;&nbsp;<strong>Total&nbsp;Pendapatan</strong></td>
             <td align="right" class="tablecontent-odd"  colspan="2"><strong>'.currency_format($total).'</strong>&nbsp;&nbsp;</td>
             <td align="left" class="tablecontent">&nbsp;&nbsp;&nbsp;</strong></td>            
          </tr>
          </table>';
          
          if(!$email) $html .= '
          <BR clear="both">
          <table width="500" border="0" cellpadding="0" cellspacing="0" id="tableprint">
          <tr>
               <td align="right"s>
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


