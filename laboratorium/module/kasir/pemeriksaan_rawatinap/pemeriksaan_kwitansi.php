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
     
 	    if(!$auth->IsAllowed("pemeriksaan_lab",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("pemeriksaan_lab",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
  $pemeriksaanId = $_GET["pemeriksaan_id"];
  
  $sql = "select a.pemeriksaan_kwt, a.pemeriksaan_total, a.id_dokter, b.periksa_det_total, c.kegiatan_nama , d.reg_id,
   e.cust_usr_kode, ((current_date - e.cust_usr_tanggal_lahir)/365) as umur, e.cust_usr_nama
from laboratorium.lab_pemeriksaan a
left join laboratorium.lab_pemeriksaan_detail b on b.id_pemeriksaan = a.pemeriksaan_id
left join laboratorium.lab_kegiatan c on c.kegiatan_id = b.id_kegiatan
left join klinik.klinik_registrasi d on d.reg_id = a.id_reg
left join global.global_customer_user e on e.cust_usr_id = d.id_cust_usr
where pemeriksaan_id = ".QuoteValue(DPE_CHAR,$pemeriksaanId);
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
  $data_pemeriksaan = $dtaccess->FetchAll($rs);   
  
  $totalItem = 0;
  $totalService = 0;
  $GrandTotal = 0;
?>
<html>     
  <head>         
    <title>laporan Diagnosis     
    </title>    
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
    <style type="text/css">html{margin:0;padding:0;}body{margin:0;padding:0;}@media screen{#calender{display:block;}#inputbutton{display:block;}}@media print{#inputbutton {display:none;}#calender{display:none;}}     
    </style>     
  </head>  
  <!--<body onLoad="cetak()">-->  
  <body>    
    <!--
        <?php echo $data_pemeriksaan[0]["pemeriksaan_kwt"]?>kwt<br />
        <?php echo $data_pemeriksaan[0]["reg_id"]?>reg<br />
        <?php echo $data_pemeriksaan[0]["cust_usr_nama"]?>nama<br />
        <?php echo $data_pemeriksaan[0]["pemeriksaan_total"]?>total<br />
        <br><br><br><br>
            <?php for($i=0;$i<count($data_pemeriksaan);$i++){ ?>
        <?php echo $data_pemeriksaan[$i]["kegiatan_nama"]; ?>seharga <?php echo $data_pemeriksaan[$i]["periksa_det_total"]; ?>
        <?php } ?>-->    
    <table width="100%" height="50%" border="1">      
      <tr>        
        <td rowspan="6">Gambar</td><td>No. Kwt</td>        
        <td width="1">:</td><td>          
          <?php echo $data_pemeriksaan[0]["pemeriksaan_kwt"]?></td><td>No. Reg :           
          <?php echo $data_pemeriksaan[0]["cust_usr_kode"]?></td>      
      </tr>      
      <tr><td>Sudah terima dari</td>        
        <td width="1">:</td><td>          
          <?php echo $data_pemeriksaan[0]["cust_usr_nama"]?></td><td><strong>ASLI</strong></td>      
      </tr>      
      <tr><td>Banyaknya uang</td>        
        <td width="1">:</td>        
        <td colspan="3">          
          <?php echo $data_pemeriksaan[0]["pemeriksaan_total"]?></td>      
      </tr>      
      <tr>      
        <td colspan="4">Untuk Pembayaran Pemeriksaan Laboratorium :</td>      
      </tr>      
      <tr>      
        <td colspan="4" height="40%">
        <?php for($i=0;$i<count($data_pemeriksaan);$i++){ ?>
        &nbsp;<?php echo $data_pemeriksaan[$i]["kegiatan_nama"]; ?> <br />
        <?php } ?>
        </td>      
      </tr>    
    </table>  
  </body>
</html>