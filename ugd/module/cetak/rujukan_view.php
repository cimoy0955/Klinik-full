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
	$skr = date('Y-m-d H:i:s');
	$tgl = date('Y-m-d');
     
     if(!$auth->IsAllowed("surat_rujukan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("surat_rujukan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
 
     $thisPage = "rujukan_view.php"; 
     $findPage = "pasien_find.php?";
     $dokterPage = "dokter_find.php?";
	
	if($_POST["cust_usr_kode"]) {
		$sql = "select cust_usr_id, cust_usr_nama,cust_usr_kode, cust_usr_alamat from global.global_customer_user a
				where a.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
          $_POST["id_cust_usr"] = $dataPasien["cust_usr_id"];
     }
	 
	if($_POST["btnSave"]) {
          $dbTable = "klinik_surat_rujukan";

		$dbField[0] = "surat_rujukan_id";   // PK
		$dbField[1] = "surat_rujukan_dokter";
		$dbField[2] = "surat_rujukan_rs";
		$dbField[3] = "id_pgw";
		$dbField[4] = "id_cust_usr";
		$dbField[5] = "surat_rujukan_alamat_rs";
		$dbField[6] = "surat_rujukan_ket";
		$dbField[7] = "surat_rujukan_diagnosis";
		$dbField[8] = "surat_rujukan_who";
		$dbField[9] = "surat_rujukan_when";
		
		$cetakId = $dtaccess->GetTransID();
		$dbValue[0] = QuoteValue(DPE_CHAR,$cetakId);
		$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["surat_rujukan_dokter"]);
		$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["surat_rujukan_rs"]);
		$dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_dokter"]);
		$dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
		$dbValue[5] = QuoteValue(DPE_CHAR,$_POST["surat_rujukan_alamat_rs"]);
		$dbValue[6] = QuoteValue(DPE_CHAR,$_POST["surat_rujukan_ket"]);
		$dbValue[7] = QuoteValue(DPE_CHAR,$_POST["surat_rujukan_diagnosis"]); 
		$dbValue[8] = QuoteValue(DPE_CHAR,$userData["name"]);
		$dbValue[9] = QuoteValue(DPE_DATE,$skr);
		 
		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
		
		$dtmodel->Insert() or die("insert error"); 
		
		unset($dtmodel);
		unset($dbField);
		unset($dbValue);
		unset($dbKey);
		
		$cetak = true;
	}
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<?php echo $view->InitThickBox(); ?>

<script language="JavaScript">
	<?php if($cetak) { ?>
		new_win = window.open("rujukan.php?id=<?php echo $cetakId;?>", "wndprint", "HEIGHT=878,WIDTH=554,menubar=yes,scrollbars=yes,left=300,top=200");
		document.location.href='<?php echo $thisPage;?>';
	<?php } ?>

	function CheckSimpan(frm){
		if(!frm.surat_rujukan_dokter.value) {
			alert('Teman Sejawat Dokter harus diisi');
			frm.surat_rujukan_dokter.focus();
			return false;
		}
		
		if(!frm.surat_rujukan_rs.value) {
			alert('Rumah Sakit harus diisi');
			frm.surat_rujukan_rs.focus();
			return false;
		} 
		
		if(!frm.surat_rujukan_alamat_rs.value) {
			alert('Rumah Sakit harus diisi');
			frm.surat_rujukan_alamat_rs.focus();
			return false;
		}
		if(!frm.surat_rujukan_ket.value) {
			alert('Mohon Pemeriksaan harus diisi');
			frm.surat_rujukan_ket.focus();
			return false;
		}
		
		
		if(!frm.surat_rujukan_diagnosis.value) {
			alert('Diagnosis harus diisi');
			frm.surat_rujukan_diagnosis.focus();
			return false;
		}
		
		if(!frm.id_dokter.value) {
			alert('Dokter Pemeriksa Harus dipilih');
			frm.dokter_nama.focus();
			return false;
		}
		
		return true;
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
		<td align="left" colspan=2 class="tableheader">Surat Rujukan diagnosis</td>
	</tr>
</table> 


	
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
     <tr>
		<td width= "5%" align="left" class="tablecontent">No. RM</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="25" maxlength="25" value="<?php echo $_POST["cust_usr_kode"];?>"/>
               <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
               <input type="submit" name="btnLanjut" value="Lanjut" class="button"/>
			<?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["id_cust_usr"]);?>
          </td>
</table>
<br>
<?php if(!$dataPasien["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>No. RM Tidak Ditemukan</strong></font>
<?php } ?>
<?php if($dataPasien["cust_usr_id"]) { ?>
<script>document.frmFind.cust_usr_kode.focus();</script>
	<table width="80%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td colspan="3" align="center" class="subHeader">DATA PRIBADI</td>
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">No. RM</td>
			<td width= "40%" align="left" class="tablecontent-odd">
				<?php echo $dataPasien["cust_usr_kode"];?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Nama Pasien</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $dataPasien["cust_usr_nama"];?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Alamat Pasien</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $dataPasien["cust_usr_alamat"];?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">TS.Dokter</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_rujukan_dokter","surat_rujukan_dokter","40","100",$_POST["surat_rujukan_dokter"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Rumah Sakit</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_rujukan_rs","surat_rujukan_rs","40","100",$_POST["surat_rujukan_rs"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Alamat Rumah Sakit</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_rujukan_alamat_rs","surat_rujukan_alamat_rs","40","100",$_POST["surat_rujukan_alamat_rs"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Mohon Pemeriksaan</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextArea("surat_rujukan_ket","surat_rujukan_ket","6","70",$_POST["surat_rujukan_ket"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Diagnosis</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_rujukan_diagnosis","surat_rujukan_diagnosis","30","100",$_POST["surat_rujukan_diagnosis"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Dokter Pemeriksa</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("dokter_nama","dokter_nama","30","100",$_POST["dokter_nama"],"inputField", "readonly",false);?>
				<a href="<?php echo $dokterPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
				<?php echo $view->RenderHidden("id_dokter","id_dokter",$_POST["id_dokter"]);?>
			</td> 
		</tr>
		
		<tr>
			<td colspan="2" align="center" class="tablecontent">
				<?php echo $view->RenderButton(BTN_SUBMIT,"btnSave","btnSave","Simpan","button",false,"onClick=\"return CheckSimpan(this.form);\"");?>
			</td> 
		</tr>
		
			
	</table>
</form> 
<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
