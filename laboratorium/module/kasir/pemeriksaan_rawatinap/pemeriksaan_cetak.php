<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $skr = getDateToday();
     
 	    if(!$auth->IsAllowed("laboratorium",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("laboratorium",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

  $pemeriksaanId = $_GET["pemeriksaan_id"];
  
  $sql = "select a.*,f.kategori_nama,cast(a.pemeriksaan_create as date) as tanggal_periksa,
  c.dokter_nama,c.id_divisi,d.usr_name,e.kegiatan_nama, b.pemeriksaan_hasil, b.pemeriksaan_nilai_normal ,
  ((current_date - g.cust_usr_tanggal_lahir)/365) as umur, g.cust_usr_nama, g.cust_usr_alamat as alamat1, g.cust_usr_alamat_luar as alamat2, g.cust_usr_kode
          from laboratorium.lab_pemeriksaan a
          left join laboratorium.lab_pemeriksaan_detail b on b.id_pemeriksaan = a.pemeriksaan_id
          left join laboratorium.lab_dokter c on c.dokter_id = a.id_dokter
          left join global.global_auth_user d on d.usr_id=a.who_update
          left join laboratorium.lab_kegiatan e on e.kegiatan_id = b.id_kegiatan
          left join laboratorium.lab_kategori f on f.kategori_id = e.id_kategori
          left join global.global_customer_user g on g.cust_usr_id = a.id_cust_usr
          where pemeriksaan_id = ".QuoteValue(DPE_CHAR,$pemeriksaanId)."
          order by f.kategori_nama";
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
  $data_pemeriksaan = $dtaccess->FetchAll($rs);   
  
  $totalItem = 0;
  $totalService = 0;
  $GrandTotal = 0;
?>
<html>
  <head>
    <title>laporan Diagnosis</title>
    <script type="text/javascript">
      function cetak(){
        if(confirm('cetak hasil?')){
          window.print();  
        }
        kembali();
        }
      
      function kembali(){
        document.location.href = 'pemeriksaan_edit.php';
      }
    </script>
<style type="text/css">
@media screen{
#calender{
display:block;
}

#inputbutton{
display:block;
}
}

@media print{
#inputbutton {
display:none;
}

#calender{
display:none;
}
}
</style>
  </head>
  <body onLoad="cetak();">
  
  <table border="0" cellpadding="2" cellspacing="0" style="align:left" width="500">
    <tr>
      <td rowspan="3" width="25%" class="tablecontent"><img src="<?echo $APLICATION_ROOT;?>/images/logo_bkmm1.gif" width="160" height="80"/></td>
      <td style="text-align:left;font-size:18px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LABORATORIUM</td>
    </tr>
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">BKMM</td>
    </tr>
    <!--<tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jl. Gayung Kebonsari Timur 49, Surabaya</td>
    </tr>-->
  </table>
  <br />
  <br />
  <table border="0" cellpadding="2" cellspacing="0" style="align:left" width="500">
    <tr>
      <td class="tablecontent">Tanggal</td>
      <td class="tablecontent">:&nbsp;<?php echo format_date_long($data_pemeriksaan[0]["tanggal_periksa"]);?>
    </tr>
    <tr>
      <td class="tablecontent">Pasien</td>
      <td class="tablecontent">:&nbsp;<?php echo $data_pemeriksaan[0]["pemeriksaan_pasien_nama"];?>
    </tr>
    <tr>
      <td class="tablecontent">Dokter</td>
      <td class="tablecontent">:&nbsp;<?php echo ($data_pemeriksaan[0]["dokter_nama"])?$data_pemeriksaan[0]["dokter_nama"]:"Permintaan Sendiri";?></td>
    </tr>
  <!--  <tr>
      <td class="tablecontent">Divisi</td>
      <td class="tablecontent">:&nbsp;<?php echo $divisi_dokter[$data_pemeriksaan[0]["id_divisi"]]?></td>
    </tr> -->
  </table>
  <br />
  <table border="1" cellpadding="1" cellspacing="0" style="align:left" width="400" style="border-style:double;">
	<tr bgcolor="gray">
    <td align="center"><font color="white">Pemeriksaan</font></td>
    <td align="center"><font color="white">Hasil</font></td>
    <td align="center"><font color="white">Nilai Normal</font></td>
    </tr>    
    <?php for($i=0;$i<count($data_pemeriksaan);$i++){ ?>
           <tr>
      <td width="30%" style="padding-left:30px;"><?php echo $data_pemeriksaan[$i]["kegiatan_nama"]; ?></td>
      <td style="padding-left:10px;"><?php echo ($data_pemeriksaan[$i]["pemeriksaan_hasil"]);?>&nbsp;</td>
      <td style="padding-left:10px;"><?php echo ($data_pemeriksaan[$i]["pemeriksaan_nilai_normal"]);?>&nbsp;</td>
    </tr>
    <?php }?>
  </table>
  
  <br />
  <table border="1" cellpadding="1" cellspacing="0" style="align:left" width="400" style="border-style:double;">
   <!-- <tr>
      <td class="tablecontent" width="30%">&nbsp;Jumlah</td>
      <td class="tablecontent" style="text-align:right">&nbsp;Rp.&nbsp;<?php echo currency_format($data_pemeriksaan[0]["pemeriksaan_total"]); ?></td>
    </tr> -->
  </table>
  <br />
  <table border="0" align="left" cellpadding="1" cellspacing="1" width="500">
    <tr>
      <td height="120" style="font-family:sans-serif;text-align:right;text-decoration:underline;" class="tablecontent"><?php echo $data_pemeriksaan[0]["usr_name"];?></td>
    </tr>
  </body>
</html>