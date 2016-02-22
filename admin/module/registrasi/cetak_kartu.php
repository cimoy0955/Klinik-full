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

 	if(!$auth->IsAllowed("cetak_kartu_pasien",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("cetak_kartu_pasien",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "cetak_kartu.php"; 
     $findPage = "pasien_find.php?";
     $cariPage = "kk_find.php?";
	
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
		$sql = "select a.*,c.reg_status,b.cust_nama,reg_jenis_pasien,reg_id,d.fol_waktu 
              from global.global_customer_user a
      			left join global.global_customer b on a.id_cust = b.cust_id
      				left join klinik.klinik_registrasi c on c.id_cust_usr = a.cust_usr_id 
      				left join klinik.klinik_folio d on d.id_reg = c.reg_id
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
		$fol_waktunya = $dataPasien["fol_waktu"];

          $_x_mode = "Edit";
	}
	     
     
	$lokasi = $APLICATION_ROOT."images/foto_pasien";
	if($_POST["cust_usr_foto"]) $fotoName = $lokasi."/".$_POST["cust_usr_foto"];
     else $fotoName = $lokasi."/default.jpg";     
	 
	// ----- update data ----- //
	if ($_POST["btnUpdate"]) { 
          $userCustId = $_POST["cust_usr_id"]; 
          
		if(!$_POST["cust_nama"]) $_POST["cust_nama"] = $_POST["cust_usr_nama"];
		$sql = "select cust_id, cust_nama from global.global_customer 
                    where upper(cust_nama) = ".QuoteValue(DPE_CHAR,strtoupper($_POST["cust_nama"])); 
		$dataCust = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
		if($dataCust) $custId = $dataCust["cust_id"];
		  
          // --- insert ke tbl client user ---
          $dbTable = "global_customer_user";
          
          $dbField[0] = "cust_usr_id";   // PK
          $dbField[1] = "cust_usr_nama";
          $dbField[2] = "id_cust";
          $dbField[3] = "cust_usr_tempat_lahir";
          $dbField[4] = "cust_usr_tanggal_lahir";
          $dbField[5] = "cust_usr_alamat";            
          $dbField[6] = "cust_usr_kodepos";            
          $dbField[7] = "cust_usr_telp";
          $dbField[8] = "cust_usr_hp";
          $dbField[9] = "cust_usr_jenis_kelamin";
          $dbField[10] = "cust_usr_status_nikah";
          $dbField[11] = "cust_usr_agama";            
          $dbField[12] = "cust_usr_golongan_darah";            
          $dbField[13] = "cust_usr_tinggi";            
          $dbField[14] = "cust_usr_berat";            
          $dbField[15] = "cust_usr_foto";
          $dbField[16] = "cust_usr_pekerjaan";    
          $dbField[17] = "cust_usr_kode";
          $dbField[18] = "cust_usr_alergi";
          $dbField[19] = "cust_usr_kota_asal";
          
          if(!$_POST["cust_usr_agama"] || $_POST["cust_usr_agama"]=="--") $_POST["cust_usr_agama"] = 'null';
           
          $dbValue[0] = QuoteValue(DPE_NUMERIC,$userCustId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$custId);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["cust_usr_tempat_lahir"]);
          $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggal_lahir"]));
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat"]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kodepos"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["cust_usr_telp"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["cust_usr_hp"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis_kelamin"]);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["cust_usr_status_nikah"]);
          $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["cust_usr_agama"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["cust_usr_golongan_darah"]);
          $dbValue[13] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_tinggi"]);
          $dbValue[14] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_berat"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["cust_usr_foto"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["cust_usr_pekerjaan"]); 
          $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
          $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alergi"]);
          $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kota_asal"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
           
          $dtmodel->Update() or die("update error");
          
          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
            
		$sql = "select reg_id, reg_status from klinik.klinik_registrasi 
                    where id_cust_usr = ".QuoteValue(DPE_NUMERIC,$userCustId)." order by reg_tanggal desc, reg_waktu desc limit 1";
		$dataReg = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
		if($dataReg) $regId = $dataReg["reg_id"]; 
                  
		$sql = "select * from klinik.klinik_biaya where biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_KARTU);
          $dataBiaya = $dtaccess->Fetch($sql,DB_SCHEMA);  
		
		$sql = "select * from klinik.klinik_folio where id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_KARTU)."
				and id_reg = ".QuoteValue(DPE_CHAR,$regId)." and cast(fol_waktu as date) = ".QuoteValue(DPE_DATE,date('Y-m-d'));
          $dataBayar = $dtaccess->Fetch($sql,DB_SCHEMA);
		if($dataBayar) $folId = $dataBayar["fol_id"];
		
		if($_POST["cust_usr_jenis"]==PASIEN_BAYAR_SWADAYA) {
			$lunas = 'n';
		} else {
			$lunas = 'y';
			$dbField[9] = "fol_dibayar"; 
			$dbField[10] = "fol_dibayar_when";
			
			$dbValue[9] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]); 
			$dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
		}
		
		$dbTable = "klinik_folio";
		
		$dbField[0] = "fol_id";   // PK
		$dbField[1] = "id_reg";
		$dbField[2] = "fol_nama";
		$dbField[3] = "fol_nominal";
		$dbField[4] = "id_biaya";
		$dbField[5] = "fol_jenis";
		$dbField[6] = "id_cust_usr";
		$dbField[7] = "fol_waktu";
		$dbField[8] = "fol_lunas"; 
		$dbField[9] = "fol_jumlah";
				$dbField[10] = "fol_nominal_satuan";
		
		if(!$folId) $folId = $dtaccess->GetTransID();
		$dbValue[0] = QuoteValue(DPE_CHAR,$folId);
		$dbValue[1] = QuoteValue(DPE_CHAR,$regId);
		$dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_nama"]);
		$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya["biaya_total"]);
		$dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya["biaya_id"]);
		$dbValue[5] = QuoteValue(DPE_CHAR,STATUS_REGISTRASI);
		$dbValue[6] = QuoteValue(DPE_NUMERICKEY,$userCustId);
		$dbValue[7] = QuoteValue(DPE_DATE,$fol_waktunya);
		$dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
		$dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
		 
		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
		
		if($dataBayar) 
			$dtmodel->Update() or die("update error");
		else 
			$dtmodel->Insert() or die("insert error"); 
		
		unset($dtmodel);
		unset($dbField);
		unset($dbValue);
		unset($dbKey);
		 
		$sql = "delete from klinik.klinik_folio_split where id_fol = ".QuoteValue(DPE_CHAR,$folId);
		$dtaccess->Execute($sql);
		
		$sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,BIAYA_KARTU)." and bea_split_nominal > 0";
		$dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
		
		for($i=0,$n=count($dataSplit);$i<$n;$i++) {
			$dbTable = "klinik_folio_split";
		
			$dbField[0] = "folsplit_id";   // PK
			$dbField[1] = "id_fol";
			$dbField[2] = "id_split";
			$dbField[3] = "folsplit_nominal";
				  
			$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
			$dbValue[1] = QuoteValue(DPE_CHAR,$folId);
			$dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$i]["id_split"]);
			$dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$i]["bea_split_nominal"]);
			 
			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
			
			$dtmodel->Insert() or die("insert error"); 
			
			unset($dtmodel);
			unset($dbField);
			unset($dbValue);
			unset($dbKey);
		}
		
		unset($_POST["cust_usr_id"]);
		$_x_mode = "save";

	}

     // --- cari agama ---
     $sql = "select * from global.global_agama order by agm_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataAgama = $dtaccess->FetchAll($rs);

     // --- cari agama ---
     $sql = "select * from global.global_customer_tipe order by cust_tipe_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataTipePasien = $dtaccess->FetchAll($rs);
       
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>

<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>


<script type="text/javascript">

	function ajaxFileUpload()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});

		$.ajaxFileUpload
		(
			{
				url:'registrasi_upload.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							alert(data.msg);
							//UpdateMedia(data.file,'type=r');
                                   //GetThumbs('target=dv_thumbs');
                                   document.getElementById('cust_usr_foto').value= data.file;
                                   document.img_foto.src='<?php echo $lokasi."/";?>'+data.file;
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

	}
</script>	


<script language="Javascript">

<? $plx->Run(); ?>

var dataRol = Array();


var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=300,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=300,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
 
<?php if($_x_mode=="save"){ ?>
	if(confirm('Cetak Kartu Pasien?')) BukaWindow('cetakkartu.php?id=<?php echo $userCustId;?>','Kartu Pasien');
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
		<td align="left" colspan=2 class="tableheader">Cetak Kartu PASIEN</td>
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
               <input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="15" maxlength="15" value="<?php echo $_POST["cust_usr_kode"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
          </td>
          <td rowspan="5"  width= "40%"  valign="top" class="tablecontent-odd">
			<img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $fotoName;?>"  border="1">
			<input type="hidden" name="cust_usr_foto" id="cust_usr_foto" value="<?php echo $_POST["cust_usr_foto"];?>">
		</td>
	</tr>	
	<tr>
		<td width= "20%" align="left" class="tablecontent">Nama Lengkap</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_nama" id="cust_usr_nama" size="30" maxlength="50" value="<?php echo $_POST["cust_usr_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">Nama KK</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_nama" id="cust_nama" size="30" maxlength="50" value="<?php echo $_POST["cust_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
               <a href="<?php echo $cariPage;?>&height=400&width=450&modal=true" class="thickbox" title="Cari KK"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Pasien" alt="Cari KK" /></a>
		</td>
	</tr>
	<tr>
		<td width= "20%"class="tablecontent">Tempat Lahir / Tanggal Lahir <?if(readbit($err_code,1)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
		<td width= "40%" class="tablecontent-odd">
               <input type="text" name="cust_usr_tempat_lahir" size="15" maxlength="20" value="<?php echo $_POST["cust_usr_tempat_lahir"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/> / 
               <input type="text" id="cust_usr_tanggal_lahir" name="cust_usr_tanggal_lahir" size="15" maxlength="10" value="<?php echo $_POST["cust_usr_tanggal_lahir"];?>" onKeyDown="return tabOnEnter(this, event);"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lahir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
			<label>(dd-mm-yyy)</label>
		</td>
	</tr>
	<tr>
		<td width= "20%" class="tablecontent">Alamat</td>
		<td class="tablecontent-odd">
			<table border=1 cellpadding=1 cellspacing=0 width="100%">
				<tr>
					<td colspan="2">
						<textarea name="cust_usr_alamat" id="cust_usr_alamat" rows="3" cols="65"><?php echo $_POST["cust_usr_alamat"];?></textarea>
					</td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Kode Pos</td>
                         <td>
                              <input type="text" name="cust_usr_kodepos" size="15" maxlength="15" value="<?php echo $_POST["cust_usr_kodepos"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Telepon</td>
                         <td>
                              <input type="text" name="cust_usr_telp" size="15" maxlength="15" value="<?php echo $_POST["cust_usr_telp"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
				<tr>
					<td width="20%" class="tablecontent-odd">Hp</td>
                         <td>
                              <input type="text" name="cust_usr_hp" size="15" maxlength="15" value="<?php echo $_POST["cust_usr_hp"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                         </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">Kota Asal</td>
		<td width= "50%" align="left" class="tablecontent-odd" colspan=2>
               <input  type="text" name="cust_usr_kota_asal" size="30" maxlength="50" value="<?php echo $_POST["cust_usr_kota_asal"];?>" onKeyDown="return tabOnEnter(this, event);"/>
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Jenis Kelamin</td>
		<td colspan="2" class="tablecontent-odd">
			<select name="cust_usr_jenis_kelamin" onKeyDown="return tabOnEnter(this, event);">
				<option value="L" <?php if($_POST["cust_usr_jenis_kelamin"]=="L")echo "selected";?>>Laki-laki</option>
				<option value="P" <?php if($_POST["cust_usr_jenis_kelamin"]=="P")echo "selected";?>>Perempuan</option>
			</select>
          </td>
	</tr>
	<tr>
		<td class="tablecontent">Status Perkawinan</td>
		<td colspan="2" class="tablecontent-odd">
			<input type="radio" name="cust_usr_status_nikah" id="sty" value="y" <?php if($_POST["cust_usr_status_nikah"]=="y") echo "checked";?> onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="sty">Menikah</label>&nbsp;
			<input type="radio" name="cust_usr_status_nikah" id="stn" value="n" <?php if($_POST["cust_usr_status_nikah"]=="n") echo "checked";?> onKeyDown="return tabOnEnter_select_with_button(this, event);"><label for="stn">Belum Menikah</label>
		</td>
	</tr>
	<tr>
		<td class="tablecontent">Agama</td>
          <td colspan="2" class="tablecontent-odd">
			<select name="cust_usr_agama" id="cust_usr_agama" onKeyDown="return tabOnEnter(this, event);">
                    <option value="">[ Pilih Agama ]</option>
				<?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataAgama[$i]["agm_id"];?>" <?php if($dataAgama[$i]["agm_id"]==$_POST["cust_usr_agama"]) echo "selected"; ?>><?php echo $dataAgama[$i]["agm_nama"];?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="tablecontent" align="left">Golongan Darah</td>
		<td colspan="2" class="tablecontent-odd">
			<select name="cust_usr_golongan_darah" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                    <option value="-">[ Pilih Golongan Darah ]</option>
                    <option value="A" <?php if("A"==$_POST["cust_usr_golongan_darah"]) echo "selected"; ?>>A</option>
                    <option value="B" <?php if("B"==$_POST["cust_usr_golongan_darah"]) echo "selected"; ?>>B</option>
                    <option value="AB" <?php if("AB"==$_POST["cust_usr_golongan_darah"]) echo "selected"; ?>>AB</option>
                    <option value="O" <?php if("O"==$_POST["cust_usr_golongan_darah"]) echo "selected"; ?>>O</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">Tinggi Badan</td>
		<td width= "50%" align="left"  colspan="2" class="tablecontent-odd">
               <input  type="text" name="cust_usr_tinggi" size="7" maxlength="5" value="<?php echo $_POST["cust_usr_tinggi"];?>" onKeyDown="return tabOnEnter(this, event);"/> <label>cm</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<span class="tablecontent">Berat Badan</span>
               <input  type="text" name="cust_usr_berat" size="7" maxlength="5" value="<?php echo $_POST["cust_usr_berat"];?>" onKeyDown="return tabOnEnter(this, event);"/> <label>kg</label>
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">Upload Foto</td>
		<td width= "50%" align="left"  colspan="2" class="tablecontent-odd">
			<div id="loading" style="display:none;"><img id="imgloading" src="<?php echo $APLICATION_ROOT;?>images/loading.gif"></div> 
			<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="inputField">
			<button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload</button>
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">Pekerjaan</td>
		<td width= "50%" align="left" class="tablecontent-odd" colspan="2" >
               <input  type="text" name="cust_usr_pekerjaan" size="30" maxlength="50" value="<?php echo $_POST["cust_usr_pekerjaan"];?>" onKeyDown="return tabOnEnter(this, event);"/>
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


<script type="text/javascript">
// ---tanggal lahir pegawai ---
    Calendar.setup({
        inputField     :    "cust_usr_tanggal_lahir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_lahir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });

</script>

<?php } ?>
<?php echo $view->RenderBodyEnd(); ?>
