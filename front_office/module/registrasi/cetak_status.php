<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/tree.cls.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
	$tree = new CTree("global.global_customer","cust_id",TREE_LENGTH);
     $userData = $auth->GetUserData();
     
     $plx = new InoLiveX("CheckKode");     

// 	if(!$auth->IsAllowed("cetak_kartu_pasien",PRIV_CREATE)){
//          die("access_denied");
//          exit(1);
//    } else if($auth->IsAllowed("cetak_kartu_pasien",PRIV_CREATE)===1){
//          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
//          exit(1);
//     }

     $_x_mode = "New";
     $thisPage = "cetak_status.php"; 
     $findPage = "pasien_find.php?";
	
     function CheckKode($kode,$custUsrId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT a.cust_usr_id FROM global.global_customer_user a 
                    WHERE upper(a.cust_usr_kode) = ".QuoteValue(DPE_CHAR,strtoupper($kode));
                    
          if($custUsrId) $sql .= " and a.cust_usr_id <> ".QuoteValue(DPE_CHAR,$custUsrId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaKode = $dtaccess->Fetch($rs);
          
		return $dataAdaKode["cust_usr_id"];
     } 

	
	if($_POST["btnLanjut"]) {
		$sql = "select a.*,c.reg_status,b.cust_nama,reg_jenis_pasien,reg_id  from global.global_customer_user a
				join global.global_customer b on a.id_cust = b.cust_id
				left join klinik.klinik_registrasi c on c.id_cust_usr = a.cust_usr_id 
				where a.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"])."
				order by reg_when_update desc "; 
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);

		$_POST["cust_nama"] = htmlspecialchars($dataPasien["cust_nama"]); 
		$_POST["cust_usr_id"] = $dataPasien["cust_usr_id"]; 
		$_POST["cust_usr_nama"] = htmlspecialchars($dataPasien["cust_usr_nama"]); 
		$_POST["cust_usr_tempat_lahir"] = $dataPasien["cust_usr_tempat_lahir"]; 
		$_POST["cust_usr_tanggal_lahir"] = format_date($dataPasien["cust_usr_tanggal_lahir"]); 
		$_POST["cust_usr_jenis_kelamin"] = $dataPasien["cust_usr_jenis_kelamin"]; 
		$_POST["cust_usr_status_nikah"] = $dataPasien["cust_usr_status_nikah"]; 
		$_POST["cust_usr_agama"] = $dataPasien["cust_usr_agama"];          
		$_POST["cust_usr_warganegara"] = $dataPasien["cust_usr_warganegara"]; 
		if($_POST["cust_usr_warganegara"]!="WNI" && $_POST["cust_usr_warganegara"]!="WNI Keturunan") $_POST["wna"] = $_POST["cust_usr_warganegara"];
		$_POST["cust_usr_golongan_darah"] = $dataPasien["cust_usr_golongan_darah"]; 
		$_POST["cust_usr_alamat"] = htmlspecialchars($dataPasien["cust_usr_alamat"]); 
		$_POST["cust_usr_telp"] = $dataPasien["cust_usr_telp"]; 
		$_POST["cust_usr_hp"] = $dataPasien["cust_usr_hp"]; 
		$_POST["cust_usr_foto"] = $dataPasien["cust_usr_foto"]; 
		$_POST["cust_usr_kota"] = $dataPasien["cust_usr_kota"]; 
		$_POST["cust_usr_propinsi"] = $dataPasien["cust_usr_propinsi"]; 
		$_POST["cust_usr_kodepos"] = $dataPasien["cust_usr_kodepos"]; 
		$_POST["cust_usr_tinggi"] = $dataPasien["cust_usr_tinggi"]; 
		$_POST["cust_usr_berat"] = $dataPasien["cust_usr_berat"]; 
		$_POST["cust_usr_pekerjaan"] = $dataPasien["cust_usr_pekerjaan"]; 
		$_POST["cust_id"] = $dataPasien["id_cust"]; 
		$_POST["cust_usr_alergi"] = $dataPasien["cust_usr_alergi"]; 
		$_POST["cust_usr_jenis"] = $dataPasien["reg_jenis_pasien"]; 
		$_POST["reg_status_pasien"] = $dataPasien["reg_status"]{0}; 
		$_POST["cust_usr_kota_asal"] = htmlspecialchars($dataPasien["cust_usr_kota_asal"]); 

          $_x_mode = "Edit";
	}
	     
     
	$lokasi = $APLICATION_ROOT."images/foto_pasien";
	if($_POST["cust_usr_foto"]) $fotoName = $lokasi."/".$_POST["cust_usr_foto"];
     else $fotoName = $lokasi."/default.jpg";     
	 
	// ----- update data ----- //
	if ($_POST["btnUpdate"]) { 
          $userCustId = $_POST["cust_usr_id"];  
		unset($_POST["cust_usr_id"]);
		$_x_mode = "save";

	} 
       
?>

<?php echo $view->RenderBody("inosoft.css",true); ?> 
<?php echo $view->InitThickBox(); ?> 


<script language="Javascript">

<? $plx->Run(); ?>

var dataRol = Array();


var _wnd_stat;

function BukaWindow(url,judul)
{
    if(!_wnd_stat) {
			_wnd_stat = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=610,height=600,left=100,top=100');
	} else {
		if (_wnd_stat.closed) {
			_wnd_stat = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=610,height=600,left=100,top=100');
		} else {
			_wnd_stat.focus();
		}
	}
     return false;
}
 
<?php if($_x_mode=="save"){ ?>
	BukaWindow('cetakstatus_ulang.php?id=<?php echo $userCustId;?>','Status Pasien Pasien');
	document.location.href='<?php echo $thisPage;?>';
<?php } ?>
 
function CheckSimpan(frm) {
     if(!frm.cust_usr_kode.value) {
          alert("Kode Pasien Harus Diisi");
          return false;
     }
     
     if(!frm.cust_usr_nama.value) {
          alert('Nama Harus Diisi');
          return false;
     }  
     
     if(CheckKode(frm.cust_usr_kode.value,frm.cust_usr_id.value,'type=r')){
		alert('Kode Pasien Sudah Ada');
		frm.cust_usr_kode.focus();
		frm.cust_usr_kode.select();
		return false;
	}  
}
 
</script>

<style type="text/css">
.bDisable{
	color: #0F2F13;
	border: 1px solid #c2c6d3;
	background-color: #e2dede;
}
</style>

<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Cetak Status Pasien</td>
	</tr>
</table> 


	
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
     <tr>
		<td width= "5%" align="left" class="tablecontent">Kode Pasien</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="25" maxlength="25" value="<?php echo $_POST["cust_usr_kode"];?>"/>
               <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
               <input type="submit" name="btnLanjut" value="Lanjut" class="button"/> 
          </td>
</table>
<?php if(!$_POST["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>Kode Pasien Tidak Ditemukan</strong></font>
<?php } ?>

<? if (!$_POST["cust_usr_jenis"] && $_POST["cust_usr_id"]) { ?>
<br>
<font color="red"><strong>Pasien belum melakukan Regristrasi.</strong></font>
<? } ?>  

<script>document.frmFind.cust_usr_kode.focus();</script>

</form> 
<?php if($_POST["cust_usr_id"] && $_POST["reg_status_pasien"]) { ?>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data"  onSubmit="return CheckSimpan(this)">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
	<tr>
          <td colspan="3" align="center" class="subHeader">DATA PRIBADI</td>
	</tr>
     <tr>
		<td width= "20%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "40%" align="left" class="tablecontent-odd">
			<?php echo $_POST["cust_usr_kode"];?> 
	</tr>	
	<tr>
		<td width= "20%" align="left" class="tablecontent">Nama Lengkap</td>
		<td width= "50%" align="left" class="tablecontent-odd"><?php echo $_POST["cust_usr_nama"];?> 
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">Nama KK</td>
		<td width= "50%" align="left" class="tablecontent-odd"><?php echo $_POST["cust_nama"];?>
		</td>
	</tr>
	<tr>
		<td width= "20%"class="tablecontent">Tempat Lahir / Tanggal Lahir <?if(readbit($err_code,1)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "40%" class="tablecontent-odd"><?php echo $_POST["cust_usr_tempat_lahir"];?> / <?php echo $_POST["cust_usr_tanggal_lahir"];?>
		</td>
	</tr>
	<tr>
		<td width= "20%" class="tablecontent">Alamat</td>
		<td class="tablecontent-odd">
			<table border=1 cellpadding=1 cellspacing=0 width="100%">
				<tr>
					<td colspan="2"><?php echo $_POST["cust_usr_alamat"];?> 
					</td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Kode Pos</td>
                         <td><?php echo $_POST["cust_usr_kodepos"];?> 
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Telepon</td>
                         <td><?php echo $_POST["cust_usr_telp"];?>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Hp</td>
                         <td><?php echo $_POST["cust_usr_hp"];?>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">Kota Asal</td>
		<td width= "50%" align="left" class="tablecontent-odd" colspan=2><?php echo $_POST["cust_usr_kota_asal"];?>
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Jenis Kelamin</td>
		<td colspan="2" class="tablecontent-odd"><?php echo $_POST["cust_usr_jenis_kelamin"];?>
          </td>
	</tr>     
	<tr>
		<td class="tablecontent">Jenis Pasien</td>
          <td colspan="2" class="tablecontent-odd" ><?php echo $bayarPasien[$_POST["cust_usr_jenis"]];?>
		</td>
	</tr>
     <tr>
		<td colspan="3" align="center" class="tablecontent-odd">&nbsp;</td>
	</tr>	
	<tr>
          <td colspan="3" align="center" class="tableheader">
               <input type="submit" name="btnUpdate" id="btnSave" value="Cetak" class="button"/>
          </td>
    </tr>
</table>

<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="cust_usr_id" id="cust_usr_id" value="<?php echo $_POST["cust_usr_id"];?>"/>
<input type="hidden" name="cust_id" value="<?php echo $_POST["cust_id"];?>"/>
<input type="hidden" name="cust_usr_jenis" value="<?php echo $_POST["cust_usr_jenis"];?>"/>
<input type="hidden" name="nama" value="<?php echo $_POST["nama"];?>"/>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
</span>
<script>document.frmEdit.cust_usr_kode.focus();</script>

</form> 
<?php } ?>
<?php echo $view->RenderBodyEnd(); ?>
