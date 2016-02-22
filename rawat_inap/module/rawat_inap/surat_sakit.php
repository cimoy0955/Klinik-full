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
     

 	if(!$auth->IsAllowed("kasir",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kasir",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "kasir_view.php";


	
	if($_GET["id_cust_usr"]) {
		$sql = "select cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin,b.cust_usr_alergi, ((current_date - cust_usr_tanggal_lahir)/365) as umur , cust_usr_alamat as alamat1, cust_usr_alamat_luar as alamat2
                    from global.global_customer_user b 
                    where b.cust_usr_id = ".QuoteValue(DPE_CHAR,$_GET["id_cust_usr"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
          

		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		$_POST["cust_usr_nama"] = $dataPasien["cust_usr_nama"];
		$_POST["umur"] = $dataPasien["umur"];
		$_POST["alamat1"] = $dataPasien["alamat1"];
		$_POST["alamat2"] = $dataPasien["alamat2"];		
	}

	$fotoName = $APLICATION_ROOT."images/logo_kasir.png";
  
  if($_POST["rawat_anestesis_dosis"]){
  $istirahat = $_POST["rawat_anestesis_dosis"];
  }
  
  if($_POST["rahasia"]){
  $rahasia = $_POST["rahasia"];
  }
  
  if($_POST["tgl_awal"]){
  $tanggalan = format_date($_POST["tgl_awal"]);
  }
  
  if($_POST["btnPrint"]){

		$sql = "select cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin,b.cust_usr_alergi, ((current_date - cust_usr_tanggal_lahir)/365) as umur , cust_usr_alamat as alamat1, cust_usr_alamat_luar as alamat2
                    from global.global_customer_user b 
                    where b.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["rahasia"]);
          $dataPasien= $dtaccess->Fetch($sql);

          

		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		$_POST["cust_usr_nama"] = $dataPasien["cust_usr_nama"];
		$_POST["umur"] = $dataPasien["umur"];
		$_POST["cust_usr_alamat"] = $dataPasien["cust_usr_alamat"];
		$_POST["alamat1"] = $dataPasien["alamat1"];
		$_POST["alamat2"] = $dataPasien["alamat2"];	
	}
  
?>

<?php echo $view->RenderBody("",true); ?>


<style type="text/css">
body {
    font-family:      Arial, Verdana, Helvetica, sans-serif;
    margin: 0px;
	font-size: 14px;
}

.tableisi {
	border: none #000000 0px; 
	padding:4px;
	border-collapse:collapse;
}

.tableisi td {
	border: solid #000000 1px; 
	padding:4px;

}

.tablenota {
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
     font-size:      10px;
	font-weight: bolder;
}

table {
    font-family:      Arial, Verdana, Helvetica, sans-serif;
	font-size: 14px;
}
</style>

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

<!--<table width="375" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="center" style="font-size:14px"><img hspace="2" width="350" height="200" name="img_foto" id="img_foto" src="<?php echo $fotoName;?>"  border="0"></td>
	</tr>
</table> 

<BR><BR>-->
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">

<table width="375" border="1" cellpadding="4" cellspacing="1">
	<tr>
		<td align="center" style="font-size:14px"><STRONG><U>SURAT KETERANGAN SAKIT</U></STRONG></td>
	</tr>
</table> 

<div>
<p> Yang bertanda tangan di bawah ini menerangkan bahwa : </p>
</div>

<table width="300" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" width="20%">NAMA</td>
		<td align="center" width="5%">:</td>
		<td align="left" width="75%"><?php echo $_POST["cust_usr_nama"]; ?></td>
	</tr>	
	<tr>
		<td align="left">UMUR</td>
		<td align="center">:</td>
		<td align="left"><?php echo $_POST["umur"]; ?></td>
	</tr>	
	<tr>
		<td align="left">ALAMAT</td>
		<td align="center">:</td>
		<td align="left"><?php 
				if($_POST["alamat1"]){
		    echo nl2br($_POST["alamat1"]);
        }
		    else {
		    echo nl2br($_POST["alamat2"]);
		    }
        ?></td>
	</tr>	
</table>

<BR>


<table width="375" border="0" cellpadding="3" cellspacing="1" align="left">
	<tr>
		<td align="left" colspan=3>Pada pemeriksaan dokter saat ini dalam keadaan sakit dan perlu beristirahat selama</td>
	</tr>
  
  <?php if(!$_POST["rawat_anestesis_dosis"]) {?>
	<tr>
		<td align="left" width="25%">Lama Istirahat</td>
		<td align="center" width="5%">:</td>
		<td align="left" id="calender"><?php echo $view->RenderTextBox("rawat_anestesis_dosis","rawat_anestesis_dosis","5","10",$_POST["rawat_anestesis_dosis"],"inputField", "calender" , null,INP_NUMERIC);?> &nbsp;hari</td>
	</tr>	
	<tr>
		<td align="left">Tanggal Mulai</td>
		<td align="center">:</td>
		<td align="left">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td>
	</tr>
		<input type="hidden" name="rahasia"  value="<?php echo $_GET["id_cust_usr"] ;?>">
	</table>

<BR style="clear:both">
	
<div style="width:375;text-align:justify">
Pada pemeriksaan dokter saat ini dalam keadaan sakit dan perlu beristirahat selama
hari
dari tanggal . . . s/d tanggal . . .
</div>
  
  <?php } else { ?>
		<tr>
		<td align="left" width="25%">Lama Istirahat</td>
		<td align="center" width="5%">:</td>
		<td align="left"><?php echo $istirahat;?> &nbsp;hari</td>
	</tr>	
	<tr>
		<td align="left">Tanggal Mulai</td>
		<td align="center">:</td>
		<td align="left">
<?php echo format_date($tanggalan) ;?>
          </td>
	</tr>	
	
</table>

<BR style="clear:both">
	
<div style="width:375;text-align:justify">
Pada pemeriksaan dokter saat ini dalam keadaan sakit dan perlu beristirahat 
dari tanggal <?php echo format_date($tanggalan) ; ?> s/d tanggal <?php echo format_date(DateAdd($tanggalan,$istirahat)) ;?>
</div>
<?php } ?>
<!--<input type="button" id="inputbutton" onClick="window.print();"" value="CETAK">-->
 <br /><br />
 <div style="margin:0 0 0 160px;">
  <?php if(!$_POST["rawat_anestesis_dosis"]) {?>
			<input type="submit" name="btnPrint" value="Simpan" class="inputField" >
	<?php } else {?>
	<input type="button" id="inputbutton" onClick="window.print();" value="CETAK">
	<?php } ?>
	</div>

</form>

<?php if(!$_POST["rawat_anestesis_dosis"]) {?>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
	<?php } ?>
<?php echo $view->RenderBodyEnd(); ?>
