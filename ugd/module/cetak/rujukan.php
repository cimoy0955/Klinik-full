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
	   
	   $sql = "select a.*, cast(surat_rujukan_when as date) as tanggal, ((current_date - cust_usr_tanggal_lahir)/365) as umur,
			 cust_usr_nama, cust_usr_alamat, pgw_nama, pgw_gelar_belakang, pgw_gelar_muka, cust_usr_jenis_kelamin 
			 from klinik.klinik_surat_rujukan a
			 join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
			 join hris.hris_pegawai c on c.pgw_id = a.id_pgw 
			 where a.surat_rujukan_id = ".QuoteValue(DPE_CHAR,$_POST["surat_id"]); 
	   $dataTable = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL); 
    }
	

?>

<html>
<head>

<title>Surat Rujukan</title>

<style type="text/css">
body {
    font-family:      Times New Roman, Verdana, Helvetica, sans-serif;
    margin: 0px;
    font-size: 15px; 
}

.tableisi {
    font-family:      Times New Roman, Arial, Helvetica, sans-serif;
    font-size:        15px;
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
	height:50px;
}

.judul {
     font-size:      12px;
	font-weight: bolder;
	border-collapse:collapse;
}


.judul1 {
	font-size: 15px;
	font-weight: bolder; 
} 
.judul2 {
	font-size: 12px;
	font-weight: normal;
}

.judul3 {
	font-size: 16px;
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

<table width="494" border="1" cellpadding="2" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center">
	   <table width="100%" border="0" cellpadding="2" cellspacing="0">
		  <tr>
		  <td align="center">
			 <img src="<?php echo $APLICATION_ROOT;?>images/logo_bkmm.gif" width="85" height="50"> </td>
			 <td align="center" id="judul"> 
			 <span class="judul"> <strong>PEMERINTAH PROPINSI JAWA TIMUR<br>DINAS KESEHATAN</strong><br>      </span>
			 <span class="judul1">BALAI KESEHATAN MATA MASYARAKAT <br>PROPINSI JAWA TIMUR</span><br><span class="judul2">Jl.Gayung Kebonsari Timur No.49  Surabaya Telp.031-8283508 <br>Fax.031-8283508 Email bkmmjatim@dinkesjatim.go.id</span>
		  </td>
	   </table>
    </td>
  </tr> 
</table>
<br>
<table width="494" border="0" cellpadding="5" cellspacing="5" class="tableisi">
  <tr>
    <td colspan="4" class="judul3"><u>SURAT RUJUKAN DIAGNOSIS</u><br><br></td> 
  </tr>
  <tr>
    <td colspan="4" align="right">Surabaya, <?php echo format_date($dataTable["tanggal"]);?><br><br></td> 
  </tr>
  <tr>
    <td colspan="4">Yth. TS.Dokter <?php echo $dataTable["surat_rujukan_dokter"];?></td> 
  </tr>
  <tr>
    <td colspan="4">Rumah Sakit <?php echo $dataTable["surat_rujukan_rs"];?></td> 
  </tr>
  <tr>
    <td colspan="4">di <?php echo $dataTable["surat_rujukan_alamat_rs"];?></td> 
  </tr>
  <tr>
    <td colspan="4"><br></td> 
  </tr> 
  <tr>
    <td colspan="4">Dengan hormat,</td> 
  </tr>
  <tr>
    <td colspan="4">Mohon pemeriksaan<br></td> 
  </tr> 
  <tr>
    <td colspan="4" align="justify" style="line-height:20px"><?php echo nl2br($dataTable["surat_rujukan_ket"]);?></td> 
  </tr> 
  <tr>
    <td colspan="4">atas penderita dibawah ini</td> 
  </tr> 
  <tr>
    <td width="10%">Nama</td> 
    <td colspan="3">: &nbsp;<?php echo $dataTable["cust_usr_nama"];?></td> 
  </tr> 
  <tr>
    <td width="10%">Umur</td> 
    <td width="40%">: &nbsp;<?php echo $dataTable["umur"];?></td> 
    <td width="15%">&nbsp;&nbsp;Jenis Kel</td> 
    <td width="40%">: &nbsp;<?php echo $jenis[$dataTable["cust_usr_jenis_kelamin"]];?></td> 
  </tr> 
  <tr>
    <td colspan="4">Dengan diagnosis <?php echo $dataTable["surat_rujukan_diagnosis"];?></td> 
  </tr> 
  <tr>
    <td colspan="4"><br></td> 
  </tr> 
  <tr>
    <td colspan="4">Terima kasi atas perhatiannya</td> 
  </tr> 
  <tr>
    <td colspan="4">Salam sejawat</td> 
  </tr>  
  <tr>
    <td colspan="4" class="ttd">&nbsp;</td> 
  </tr> 
  <tr>
    <td colspan="4">(<?php echo $dataTable["pgw_gelar_muka"]." ".$dataTable["pgw_nama"]." ".$dataTable["pgw_gelar_belakang"];?>)</td> 
  </tr> 
</table> 
</body>
</html>
