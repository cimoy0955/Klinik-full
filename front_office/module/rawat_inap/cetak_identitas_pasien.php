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
 
     if($_GET["id"]) $_POST["cust_usr_id"] = $_GET["id"];
	
	$sql = "select a.*,b.*,((current_date - cust_usr_tanggal_lahir)/365) as umur from global.global_customer_user a left join global.global_kota b on a.cust_usr_kota = b.kota_id where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
	$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
	
	// ---- Data registrasi terakhir ---- 
	$sql = "select a.reg_id, a.reg_jenis_pasien, a.reg_rujukan, a.reg_no_sep, a.reg_no_kartubpjs, b.kategori_nama, d.kamar_nama
			from klinik.klinik_registrasi a
			left join klinik.klinik_rawatinap f on f.id_reg = a.reg_id
			left join klinik.klinik_kamar_kategori b on b.kategori_id = f.id_kategori_kamar
			left join klinik.klinik_kamar d on d.kamar_id = f.id_kamar
			where a.id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])."   
			order by reg_when_update desc limit 1"; 
	$dataReg = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
	
?>
<html>
<head>

<title>Cetak Kartu Pasien</title> 
<script> 
	window.print();  
</script>
<style type="text/css">
 
    body {
	   font-family:      Arial, Verdana, Helvetica, sans-serif;
	   margin: 0px;
	    font-size: 10px;
    }
    
    .tableisi {
	   font-family:      Verdana, Arial, Helvetica, sans-serif;
	   font-size:        10px;
	    border: none #000000 0px; 
	    padding:4px;
	    border-collapse:collapse;
    }
    
    
    .tableisi td {
	    border: solid #000000 1px; 
	    padding:4px;
	    font-weight: bold;
    }
    
    .tableisitoc td {
	    border: none; 
	    padding:4px;
	    font-weight: normal;
	    font-size: smaller;
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
	    font-size:      14px;
	    font-weight: bolder;
	    border-collapse:collapse;
    }
    
    
    .judul1 {
	    font-size: 12px;
	    font-weight: bolder;
    }
    .judul2 {
	    font-size: 14px;
	    font-weight: bolder;
    }
    .judul3 {
	    font-size: 12px;
	    font-weight: normal;
    }
    
    .judul4 {
	    font-size: 11px;
	    font-weight: bold;
	    background-color : #CCCCCC;
	    text-align : center;
    }
    .judul5 {
	    font-size: 11px;
	    font-weight: bold;
	    background-color : #040404;
	    text-align : center;
	    color : #FFFFFF;
    } 

</style>
</head>
<body>
     <table border="0" width="100%" cellpadding="0" cellspacing="0">
	  <tr>
	       <td style="font-size: 12px;text-align: center;">PEMERINTAH PROPINSI JAWA TIMUR</td>
	  </tr>
	  <tr>
	       <td style="font-size: 14px;text-align: center;">RUMAH SAKIT MATA MASYARAKAT (RSMM JATIM)</td>
	  </tr>
	  <tr>
	       <td style="font-size: 10px;text-align: center;">Jln. Gayung Kebonsari Timur 49 Surabaya&nbsp;&nbsp;&nbsp;Telp./Fax: (031)8283508-10</td>
	  </tr>
     </table>
     <br /><br />
     <table border="0" width="100%">
	  <tr>
	       <td style="font-size: 20px;text-decoration: underline;text-align: center;font-weight: bold;" colspan="2">IDENTITAS PASIEN RAWAT INAP</td>
	  </tr>
	  <tr>
	       <td style="font-size: 12px;">TANGGAL MASUK :&nbsp;&nbsp;&nbsp;&nbsp;<?php echo format_date_long(GetDateToday()); ?></td>
	       <td style="font-size: 12px;">TANGGAL KELUAR :&nbsp;&nbsp;..........................</td>
	  </tr>
     </table>
     <table class="tableisi" width="100%">
	  <tr>
	       <td>
		    NO<br />REGISTER
	       </td>
	       <td colspan="3">
		    <?php echo $dataPasien["cust_usr_kode"];?>
	       </td>
	       <td>
		    KIRIMAN<br />DARI UNIT
	       </td>
	       <td colspan="2">
		    POLI 2 / POLI 5 / OK / UGD
	       </td>
	  </tr>
	  <tr>
	       <td>
		    NAMA<br />LENGKAP
	       </td>
	       <td colspan="3">
		    <?php echo $dataPasien["cust_usr_nama"];?>
	       </td>
	       <td>
		    JENIS KEL
	       </td>
	       <td colspan="2">
		    <?php echo strtoupper($dataPasien["cust_usr_jenis_kelamin"]);?>
	       </td>
	  </tr>
	  <tr>
	       <td>
		    TEMPAT/<br />TANGGAL LAHIR
	       </td>
	       <td colspan="3">
		    <?php echo $dataPasien["cust_usr_tempat_lahir"];?>&nbsp;/&nbsp;<?php echo format_date_long($dataPasien["cust_usr_tanggal_lahir"]);?>
	       </td>
	       <td>
		    UMUR
	       </td>
	       <td colspan="2">
		    <?php echo HitungUmur($dataPasien["cust_usr_tanggal_lahir"]);?>
	       </td>
	  </tr>
	  <tr>
	       <td>
		    &nbsp;<br>ALAMAT1
	       </td>
	       <td colspan="6">
		    <?php echo nl2br($dataPasien["cust_usr_alamat"]);?>
	       </td>
	  </tr>
	  <tr>
	       <td>
		    &nbsp;<br>ALAMAT2
	       </td>
	       <td colspan="6">
		    &nbsp;<br />
	       </td>
	  </tr>
	  <tr>
	       <td>
		    KOTA/KAB
	       </td>
	       <td colspan="3">
		    <?php echo $dataPasien["kota_nama"];?>
	       </td>
	       <td>
		    KODE<br />POS
	       </td>
	       <td colspan="2">
		    <?php echo $dataPasien["cust_usr_kodepos"];?>
	       </td>
	  </tr>
	  <tr>
	       <td>
		    NO TELP<br />RUMAH
	       </td>
	       <td colspan="2">
		    <?php echo $dataPasien["cust_usr_telp"];?>
	       </td>
	       <td>
		    NO. PONSEL
	       </td>
	       <td colspan="3">
		    <?php echo $dataPasien["cust_usr_hp"];?>
	       </td>
	  </tr>
	  <tr>
	       <td>
		    BAWA RUJUKAN
	       </td>
	       <td align="center">
		    YA
	       </td>
	       <td align="center">
		    TIDAK
	       </td>
	       <td>
		    RUJUKAN DARI
	       </td>
	       <td colspan="3">
		    &nbsp;
	       </td>
	  </tr>
	  <tr>
	       <td>
		    BIAYA
	       </td>
	       <td align="center">
		    BAYAR<br>SENDIRI
	       </td>
	       <td align="center">
		    BPJS<br>MANDIRI
	       </td>
	       <td align="center">
		    BPJS<br>ABRI
	       </td>
	       <td align="center">
		    BPJS<br>PNS
	       </td>
	       <td align="center">
		    BPJS<br>JAMKESMAS
	       </td>
	       <td valign="top">
		    LAIN-LAIN
	       </td>
	  </tr>
	  <tr>
	       <td valign="top">
		    NO. KARTU<br />BPJS
	       </td>
	       <td colspan="6" valign="top">
		    <?php echo $dataReg["reg_no_kartubpjs"];?>&nbsp;<br />&nbsp;
	       </td>
	  </tr>
	  <tr>
	       <td valign="top">
		    NO. SEP
	       </td>
	       <td colspan="3" valign="top">
		    <?php echo $dataReg["reg_no_kartubpjs"];?>&nbsp;<br />&nbsp;
	       </td>
	       <td valign="top">
		    TGL. SEP
	       </td>
	       <td colspan="2" valign="top">
		    <?php echo $dataReg["reg_tgl_kartubpjs"];?>&nbsp;<br />&nbsp;
	       </td>
	  </tr>
	  <tr>
	       <td valign="top">
		    KELAS <br>
		    RAWAT
	       </td>
	       <td colspan="2" valign="top"> 
		    &nbsp;<?php echo $dataReg["kategori_nama"];?>
	       </td>
	       <td coLspan="2" valign="top">
		    PINDAH KELAS
	       </td>
	       <td colspan="2">
		    &nbsp;
	       </td>
	  </tr>
	  <tr>
	       <td valign="top">
		    RUANGAN
	       </td>
	       <td colspan="2" valign="top"> 
		    &nbsp;<?php echo $dataReg["kamar_nama"];?><br>&nbsp;
	       </td>
	       <td coLspan="2" valign="top">
		    PINDAH RUANG
	       </td>
	       <td colspan="2" valign="top">
		    &nbsp;
	       </td>
	  </tr>
	  <tr>
	       <td colspan="7">
		    <table style="border: none; width: 100%;" class="tableisitoc">
			 <tr>
			 <td colspan="4">
			      <p style="text-decoration: underline; font-weight: bold; text-align: center;">KETERANGAN PERSETUJUAN RAWAT INAP</p>
			      <p>Yang bertangda tangan di bawah ini:<br>
			      Nama:........................ Pasien/keluarga pasien diatas, dengan ini menyatakan:
			      <ol>
				   <li>memberikan persetujuan untuk dilakukan Rawat Inap dan tindakan prosedur medis dan pengobatan di RSMM sesuai dengan standard SOP yang berlaku.</li>
				   <li>Bersedia menaati peraturan di RSMM</li>
				   <li>
					Bersedia membayar semua biaya perawatan yang diterima pasien tersebut
				   </li>
			      </ol></p>  
			 </td>
			 </tr>
			 <tr>
			      <td style="text-align: center;">
				   Yg Bersangkutan<br><br><br><br><br><br>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
			      </td>
			      <td style="text-align: center;">
				   Perawat<br><br><br><br><br><br>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
			      </td>
			      <td style="text-align: center;">
				   Dokter<br><br><br><br><br><br>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
			      </td>
			      <td style="text-align: center;">
				   Saksi<br><br><br><br><br><br>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
			      </td>
			 </tr>
		    </table>
	       </td>
	  </tr>
     </table>
</body>
</html>