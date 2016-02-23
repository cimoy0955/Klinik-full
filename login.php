<?php

require_once("root.inc.php");
require_once($ROOT."library/dataaccess.cls.php");
require_once($ROOT."library/auth.cls.php");

$dtaccess = new DataAccess();

$sql = "select * from global.global_app";
$rs = $dtaccess->Execute($sql);
$dataTable = $dtaccess->FetchAll($rs);

if($_GET["user"]) $_POST["txtUser"] = $_GET["user"];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>:: Rumah Sakit Mata Masyrakat ::</title>
<link rel="stylesheet" type="text/css" href="com/main.css" />
<script language="JavaScript" type="text/javascript" src="./library/script/elements.js"></script>
<script type="text/javascript">
  function Ganti(eval){
    if(eval=='18'){
      document.getElementById('frmLogin').setAttribute("action","./tekkadan/u0");
    }
  }
</script>
</head>
<BODY>
<div class="header"><div class="a"><img src="com/RSMM-long-01.png" width="330" height="67" /></div>
</div>

<div class="subheader"><img src="com/hijau.png" /></div>


<!--  ISI  -->
<div class="isi"> 
<div class="kiri">
<h3>Selamat Datang di Rumah Sakit Mata Masyarakat </h3>

<p class="judul">Mata lebih berharga dari Permata</p>
<p class="normal">
<img src="com/gedung.gif" /></p>

<p>Awal didirikan Balai Kesehatan Mata Masyarakat CeHC adalah pada tanggal 18 April 1992. Semenjak diberlakukan otonomi daerah pada tahun 2001 sesuai peraturan pemerintah Balai Kesehatan Mata Masyarakat menjadi UPT Dinas Kesehatan Propinsi
Jawa Timur.</p>

<p>BKMM/CeHC adalah pusat layanan kesehatan mata dibawah naungan Dinas Kesehatan Propinsi Jawa Timur sebagai pelayanan antara kesehatan masyarakat dasar (Puskesmas) dengan pelayanan kesehatan masyarakat spesialistik mata (Community Opthalmology) yang merupakan jembatan dari kesenjangan antara kebutuhan pelayanan medis spesialistik.
</p>



<marquee>
<div class="slider">
<img src="com/slide/img1.jpg"  />
<img src="com/slide/img2.jpg"  />
<img src="com/slide/img3.jpg"  />
<img src="com/slide/img4.jpg"  />
<img src="com/slide/img5.jpg"  />
<img src="com/slide/img6.jpg"  />
<img src="com/slide/img7.jpg"  />
<img src="com/slide/img8.jpg"  />
</div>
</marquee>

</div>

<div class="kanan">
<form name="frmLogin" id="frmLogin" action="check.php" method="post">
<table border="0" cellpadding="2" cellspacing="2">
<tr>
	<td>Username :</td>
	<td><input class="input" type="text" name="user" value="<?php echo $_POST["txtUser"];?>" OnKeyDown="return tabOnEnter_select_with_button(this, event);" /></td>
</tr>
<tr>
	<td>Password :</td>
	<td><input type="password" name="passwd" onKeyDown="return tabOnEnter(this, event);" class="input"/></td>
</tr>
<tr>
	<td>Modul :</td>
	<td>
<select class="input" name="cmbSystem" onKeyDown=" return tabOnEnter(this, event); " onchange="Ganti(this.value);"  >
<?php for($i=0;$i<count($dataTable);$i++){?>
 <option value="<?php echo $dataTable[$i]["app_id"];?>" onKeyDown="return tabOnEnter(this, event);"><?php echo $dataTable[$i]["app_nama"];?></option>
<?php }?>
<!--<option value="klinik" onKeyDown="return tabOnEnter(this, event);">Klinik</option>
<option value="logistik" onKeyDown="return tabOnEnter(this, event);">Logistik</option>
<option value="apotik" onKeyDown="return tabOnEnter(this, event);">Apotik</option>
<option value="laboratorium" onKeyDown="return tabOnEnter(this, event);">Laboratorium</option> 
<option value="admin" onKeyDown="return tabOnEnter(this, event);">Admin</option>-->

</select>
	</td>
</tr>
<tr>
	<td colspan="2" align="center"><input type="submit" name="btnSubmit" value=" " class="submit"></td>
</tr>
                                        <?php if($_GET["msg"]){ ?>
                                   <tr>
                                        <td align="left" colspan="2"><label>
                                             <font color="red" size=1>
                                             <?php 
                                                  if($_GET["msg"]=="kode_eror01") echo "Login Gagal.<br />Username atau Password salah."; 
                                                  elseif($_GET["msg"]=="kode_eror02") echo "Akses Ditolak.<br />User tidak berhak masuk aplikasi.";
                                             ?></font>
                                        </label></td>
                                   </tr>
                                      <?php } ?>

</table>
</form>
</div>
<div class="kanan2">
     <object type="application/x-shockwave-flash" data="player_flv_maxi.swf" width="240" height="180">
     <param name="movie" value="player_flv_maxi.swf" />
     <param name="FlashVars" value="flv=video.flv 
&showstop=1&showvolume=1&showtime=1&bgcolor1=006600&bgcolor2=006600&playercolor=006600"/>
     </object>
</div>
<div class="footer">
  <table border="0" cellpadding="3" cellspacing="3" width="789" height="160">
    <tr>
      <td width="204" valign="top"><div class="dancing">Berita Terbaru</div>
          <ul>
            <li>BKMM CeHC LULUS MENJADI BLUD</li>
        </ul></td>
      <td width="283" valign="top"><div class="dancing">Fasilitas</div>
          <ul>
            <li>Rawat Jalan Mata </li>
            <li>Rawat Bedah </li>
            <li>Pelayanan Optik dan Apotik </li>
            <li>Penemuan Gejala Glaukoma </li>
            <li>Humphrey</li>
        </ul></td>
      <td width="272" valign="top"><div class="dancing">Pelayanan Medis </div>
          <ul>
            <li>Katarak dan Bedah Refraktif</li>
            <li>Kaca mata anak</li>
            <li>Optalmologi Comunity</li>

        </ul></td>
    </tr>
  </table>
</div>

<div class="super">
<a href="http://expressa.co.id" target="_blank"><img src="com/logoBesar-bgt.png"/></a>
</div>

</div>
<!--  END ISI  -->

</body>
</html>
