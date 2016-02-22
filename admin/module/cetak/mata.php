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

    $_x_mode = "New";
    $jenis[P] = "Perempuan";
    $jenis[L] = "Laki-laki";
    
    if($_GET["id"]) $_POST["surat_id"] = $_GET["id"];
    
    if($_POST["surat_id"]) {
	   
	   $sql = "select a.*, cast(surat_mata_when as date) as tanggal, cust_usr_nama, cust_usr_jenis_kelamin, 
			 ((current_date - cust_usr_tanggal_lahir)/365) as umur,cust_usr_alamat, pgw_nama, pgw_gelar_belakang, pgw_gelar_muka, pgw_nip 
			 from klinik.klinik_surat_mata a
			 join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
			 join hris.hris_pegawai c on c.pgw_id = a.id_pgw 
			 where a.surat_mata_id = ".QuoteValue(DPE_CHAR,$_POST["surat_id"]);
	   $dataTable = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
	   
	   if($dataTable["surat_sakit_tanggal"])
		  $tanggal = DateAdd($dataTable["surat_sakit_tanggal"],$dataTable["surat_sakit_lama"]);
	   
    }
	

?>

<html>
<head>

<title>Surat Kesehatan Mata</title>

<style type="text/css">
body {
    font-family:      Times New Roman, Verdana, Helvetica, sans-serif;
    margin: 0px;
    font-size: 13px; 
}

.tableisi {
    font-family:      Times New Roman, Arial, Helvetica, sans-serif;
    font-size:        13px;
    border: none #000000 0px; 
    padding:4px;
    border-collapse:collapse;
} 

.tablenota {
    font-family:      Verdana, Arial, Helvetica, sans-serif;
    font-size:        10px;
	border: solid #000000 1px; 
	padding:4px;
	border-collapse:collapse;
}

.tablenota .judul  {
	border: solid #000000 1px; 
	padding:4px;
}

.tablenota .isi {
	border-right: solid black 1px;
	padding:4px;
}

.ttd {
	height:40px;
}

.judul {
     font-size:      12px;
	font-weight: bolder;
	border-collapse:collapse;
}


.judul1 {
	font-size: 14px;
	font-weight: bolder; 
} 
.judul2 {
	font-size: 11px;
	font-weight: normal;
}

.judul3 {
	font-size: 15px;
	font-weight: bolder;
	text-align:center;
} 
 
</style>

<?php echo $view->InitUpload(); ?>

<script>
$(document).ready( function() { 
    window.print();
});
      
</script>
</head>

<body> 

<table width="453" border="1" cellpadding="0" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center">
	   <table border="0" cellpadding="2" cellspacing="0">
		  <tr>
		  <td align="center">
			 <img src="<?php echo $APLICATION_ROOT;?>images/logo_bkmm.gif" width="85" height="50"> </td>
		  <td align="center" id="judul"> 
			 <span class="judul"> <strong>PEMERINTAH PROPINSI JAWA TIMUR <br>DINAS KESEHATAN</strong><br>      </span>
			 <span class="judul1">BALAI KESEHATAN MATA MASYARAKAT <br>PROPINSI JAWA TIMUR</span><br><span class="judul2">Jl.Gayung Kebonsari Timur No.49  Surabaya Telp.031-8283508 <br>Fax.031-8283508 Email bkmmjatim@dinkesjatim.go.id</span>
		  </td>
	   </table>
    </td>
  </tr> 
</table>
<br>
<table width="453" border="0" cellpadding="2" cellspacing="5" class="tableisi">
  <tr>
    <td colspan="2" class="judul3"><u>SURAT KETERANGAN <br>KESEHATAN MATA</u><br><br></td> 
  </tr>
  <tr>
    <td colspan="2" align="justify" style="line-height:20px">Yang bertanda tangan di bawah ini menyatakan dengan sesungguhnya bahwa pada pemeriksaan mata saat ini :</td> 
  </tr>
  <tr>
    <td width="20%">Nama&nbsp; :&nbsp;<?php echo $dataTable["cust_usr_nama"];?></td>
  </tr> 
  <tr>
    <td width="22%">Umur&nbsp; :&nbsp;<?php echo $dataTable["umur"]; ?></td> 
    <td width="24%">Jenis Kelamin&nbsp; :&nbsp;<?php echo $jenis[$dataTable["cust_usr_jenis_kelamin"]]; ?></td>
  </tr> 
  <tr>
    <td colspan="2">Alamat&nbsp; : <?php echo $dataTable["cust_usr_alamat"]; ?></td> 
  </tr> 
  <tr>
    <td colspan="2">Kami dapatkan :</td> 
  </tr> 
  <tr>
    <td colspan="2">- Visual Acuity</td> 
  </tr> 
  <tr>
	   <td>&nbsp;&nbsp; Visual OD&nbsp; : <?php echo $dataTable["surat_mata_od"];?></td>  
	   <td>Koreksi&nbsp; : <?php echo $dataTable["surat_mata_koreksi_od"];?></td>  
  </tr> 
  <tr>
	   <td>&nbsp;&nbsp; Visual OS&nbsp; : <?php echo $dataTable["surat_mata_os"];?></td>  
	   <td>Koreksi&nbsp; : <?php echo $dataTable["surat_mata_koreksi_os"];?></td>  
  </tr> 
  <tr>
    <td colspan="2">- Visual Field &nbsp;: <?php echo $dataTable["surat_mata_field"];?></td> 
  </tr> 
  <tr>
    <td colspan="2">- Ocular Motility &nbsp;: <?php echo $dataTable["surat_mata_ocular"];?></td> 
  </tr> 
  <tr>
    <td colspan="2">- Color Blindness &nbsp;: <?php echo $dataTable["surat_mata_color"];?></td> 
  </tr> 
  <tr>
    <td colspan="2">Kelainan Lain &nbsp;: <?php echo $dataTable["surat_mata_lain"];?></td> 
  </tr> 
  <tr>
    <td colspan="2">Diagnosis &nbsp;: <?php echo $dataTable["surat_mata_diagnosis"];?></td> 
  </tr> 
  <tr>
    <td colspan="2">Terapi &nbsp;: <?php echo $dataTable["surat_mata_terapi"];?></td> 
  </tr>  
  <tr>
    <td colspan="2"><br></td> 
  </tr> 
  <tr>
    <td>PEMERIKSA</td>
    <td align="rgiht">Surabaya, <?php echo format_date_long($dataTable["tanggal"]);?></td> 
  </tr> 
  <tr>
    <td colspan="2" class="ttd">&nbsp;</td> 
  </tr> 
  <tr>
    <td colspan="2"><u>(<?php echo $dataTable["pgw_gelar_muka"]." ".$dataTable["pgw_nama"]." ".$dataTable["pgw_gelar_belakang"];?>)</u><br>NIP.<?php echo $dataTable["pgw_nip"];?></td> 
  </tr>  
  <tr>
    <td colspan="2" class="ttd">&nbsp;</td> 
  </tr> 
</table> 
</body>
</html>
