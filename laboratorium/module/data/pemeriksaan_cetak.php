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
  
  $sql = "select a.*,f.kategori_nama,cast(a.pemeriksaan_create as date) as tanggal_periksa, a.pemeriksaan_create, c.pgw_nama, d.usr_name,b.pemeriksaan_hasil, b.periksa_det_total,b.periksa_det_id,b.id_kegiatan, b.id_pemeriksaan, b.pemeriksaan_nilai_normal,e.*, g.cust_usr_nama, g.cust_usr_jenis_kelamin, g.cust_usr_alamat, g.cust_usr_kode, g.cust_usr_tanggal_lahir, h.reg_jenis_pasien
          from lab_pemeriksaan a
          left join lab_pemeriksaan_detail b on b.id_pemeriksaan = a.pemeriksaan_id
          left join hris.hris_pegawai c on c.pgw_id = a.id_dokter
          left join global.global_auth_user d on d.usr_id=a.who_update
          left join lab_kegiatan e on e.kegiatan_id = b.id_kegiatan
          left join lab_kategori f on f.kategori_id = e.id_kategori
          left join klinik.klinik_registrasi h on h.reg_id = a.id_reg
          left join global.global_customer_user g on g.cust_usr_id = h.id_cust_usr
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
    <title>Kuitansi Pemeriksaan Laboratorium</title>
    <script type="text/javascript">
      function cetak(){
        if(confirm('cetak nota?')){
          window.print();  
        }
        kembali();
        }
      
      function kembali(){
        document.location.href = 'pemeriksaan_lihat.php';
      }
    </script>
    <style type="text/css">
    table {
      border-collapse: collapse;
    }
    </style>
  </head>
  <body onLoad="cetak();">
  
  <table border="0" cellpadding="2" cellspacing="0" style="align:left" width="80%">
    <tr>
      <td rowspan="3" width="35%" class="tablecontent"><img src="<?echo $APLICATION_ROOT;?>/images/logo_bkmm1.gif" width="160" height="80"/></td>
      <td style="text-align:left;font-size:18px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LABORATORIUM RSMM</td>
    </tr>
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Rumah Sakit Mata Masyarakat</td>
    </tr>
    <tr>
      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jl. Gayung Kebonsari Timur 49, Surabaya</td>
    </tr>
  </table>
  <br />
  <br />
  <table border="0" cellpadding="2" cellspacing="0" style="align:left" width="98%">
    <tr>
      <td class="tablecontent">Kode Pasien</td>
      <td class="tablecontent-odd">:&nbsp;<?php echo $data_pemeriksaan[0]["cust_usr_kode"];?></td>
      <td class="tablecontent">Jenis Kelamin/Umur</td>
      <td class="tablecontent-odd">:&nbsp;<?php echo ($data_pemeriksaan[0]["cust_usr_jenis_kelamin"]);?>/<?php echo HitungUmur($data_pemeriksaan[0]["cust_usr_tanggal_lahir"]);?></td>
    </tr>
    <tr>
      <td class="tablecontent">Nama</td>
      <td class="tablecontent-odd">:&nbsp;<?php echo $data_pemeriksaan[0]["cust_usr_nama"];?></td>
      <td class="tablecontent">Tanggal Terima</td>
      <td class="tablecontent-odd">:&nbsp;<?php echo format_date_long($data_pemeriksaan[0]["tanggal_periksa"]);?></td>
    </tr>
    <tr>
      <td class="tablecontent">Alamat</td>
      <td class="tablecontent-odd">:&nbsp;<?php echo ($data_pemeriksaan[0]["cust_usr_alamat"]);?></td>
      <td class="tablecontent">Waktu Terima</td>
      <td class="tablecontent-odd">:&nbsp;<?php 
      $waktu = explode(" ",$data_pemeriksaan[0]["pemeriksaan_create"]);
      echo $waktu[1];?></td>
    </tr>
    <tr>
      <td class="tablecontent">Dokter</td>
      <td class="tablecontent-odd">:&nbsp;<?php echo ($data_pemeriksaan[0]["pgw_nama"])?$data_pemeriksaan[0]["pgw_nama"]:"Permintaan Sendiri";?></td>
      <td class="tablecontent">Tanggal Selesai</td>
      <td class="tablecontent-odd">:&nbsp;<?php echo format_date_long($data_pemeriksaan[0]["tanggal_periksa"]);?></td>
    </tr>
    <tr>
      <td class="tablecontent">Jenis Pasien</td>
      <td class="tablecontent-odd">:&nbsp;<?php echo $bayarPasien[$data_pemeriksaan[0]["reg_jenis_pasien"]];?></td>
      <td class="tablecontent">Waktu Terima</td>
      <td class="tablecontent-odd">:&nbsp;<?php 
      $waktu = explode(" ",$data_pemeriksaan[0]["pemeriksaan_create"]);
      echo $waktu[1];?></td>
    </tr>
  </table>
  <br />
  <table border="1" cellpadding="1" cellspacing="0" style="align:left" width="98%" style="border-style:double;">
  <tr bgcolor="gray">
    <td align="center"><font color="white">Pemeriksaan</font></td>
    <td align="center"><font color="white">Hasil</font></td>
    <td align="center"><font color="white">Nilai Normal</font></td>
    </tr>    
    <?php for($i=0;$i<count($data_pemeriksaan);$i++){ ?>
      <?php if($i % 2 == 0){ ?>
        <tr>
      <?php } else { ?>
        <tr style="background-color: #efefef;">
      <?php } ?>
          <td width="30%" style="padding-left:30px;"><?php echo $data_pemeriksaan[$i]["kegiatan_nama"]; ?></td>
          <td style="padding-left:10px;"><?php echo ($data_pemeriksaan[$i]["pemeriksaan_hasil"]);?>&nbsp;</td>
          <td style="padding-left:10px;"><?php echo ($data_pemeriksaan[$i]["pemeriksaan_nilai_normal"]);?>&nbsp;<?php echo ($data_pemeriksaan[$i]["kegiatan_satuan"]);?></td>
        </tr>
    <?php }?>
  </table>
  
  <br />
  <table border="1" cellpadding="1" cellspacing="0" style="align:left" width="98%" style="border-style:double;">
   <!-- <tr>
      <td class="tablecontent" width="30%">&nbsp;Jumlah</td>
      <td class="tablecontent" style="text-align:right">&nbsp;Rp.&nbsp;<?php //echo currency_format($data_pemeriksaan[0]["pemeriksaan_total"]); ?></td>
    </tr> -->
  </table>
  <br />
  <table border="0" align="left" cellpadding="1" cellspacing="1" width="98%">
    <tr>
      <td height="120" style="font-family:sans-serif;text-align:right;text-decoration:underline;" class="tablecontent">Penanggung Jawab:</td>
      <td height="120" style="font-family:sans-serif;text-align:right;text-decoration:underline;" class="tablecontent">Pemeriksa:</td>
    </tr>
    <tr>
      <td height="120" style="font-family:sans-serif;text-align:right;text-decoration:underline;" class="tablecontent" colspan="2"><br /><br /></td>
    </tr>
    <tr>
    <td height="120" style="font-family:sans-serif;text-align:right;text-decoration:underline;" class="tablecontent">&nbsp;</td>
      <td height="120" style="font-family:sans-serif;text-align:right;text-decoration:underline;" class="tablecontent"><?php echo $data_pemeriksaan[0]["usr_name"];?></td>
    </tr>
    </table>
  </body>
</html>