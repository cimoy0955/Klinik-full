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
     
     if(!$auth->IsAllowed("surat_ket_kesehatan_mata",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("surat_ket_kesehatan_mata",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
 
     $thisPage = "mata_view.php"; 
     $findPage = "pasien_find.php?";
     $dokterPage = "dokter_find.php?";
	
	if($_POST["cust_usr_kode"]) {
		$sql = "select cust_usr_id, cust_usr_nama,cust_usr_kode, cust_usr_alamat from global.global_customer_user a
				where a.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
          $_POST["id_cust_usr"] = $dataPasien["cust_usr_id"];
     }
	 
	if($_POST["btnSave"]) {
          $dbTable = "klinik_surat_mata";

		$dbField[0] = "surat_mata_id";   // PK
		$dbField[1] = "surat_mata_od";
		$dbField[2] = "surat_mata_os";
		$dbField[3] = "id_pgw";
		$dbField[4] = "id_cust_usr";
		$dbField[5] = "surat_mata_koreksi_od";
		$dbField[6] = "surat_mata_koreksi_os";
		$dbField[7] = "surat_mata_field";
		$dbField[8] = "surat_mata_ocular";
		$dbField[9] = "surat_mata_color";
		$dbField[10] = "surat_mata_lain";
		$dbField[11] = "surat_mata_diagnosis";
		$dbField[12] = "surat_mata_terapi";
		$dbField[13] = "surat_mata_who";
		$dbField[14] = "surat_mata_when";
		
		$cetakId = $dtaccess->GetTransID();
		$dbValue[0] = QuoteValue(DPE_CHAR,$cetakId);
		$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["surat_mata_od"]);
		$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["surat_mata_os"]);
		$dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_dokter"]);
		$dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
		$dbValue[5] = QuoteValue(DPE_CHAR,$_POST["surat_mata_koreksi_od"]);
		$dbValue[6] = QuoteValue(DPE_CHAR,$_POST["surat_mata_koreksi_os"]);
		$dbValue[7] = QuoteValue(DPE_CHAR,$_POST["surat_mata_field"]); 
		$dbValue[8] = QuoteValue(DPE_CHAR,$_POST["surat_mata_ocular"]); 
		$dbValue[9] = QuoteValue(DPE_CHAR,$_POST["surat_mata_color"]); 
		$dbValue[10] = QuoteValue(DPE_CHAR,$_POST["surat_mata_lain"]); 
		$dbValue[11] = QuoteValue(DPE_CHAR,$_POST["surat_mata_diagnosis"]); 
		$dbValue[12] = QuoteValue(DPE_CHAR,$_POST["surat_mata_terapi"]);
		$dbValue[13] = QuoteValue(DPE_CHAR,$userData["name"]);
		$dbValue[14] = QuoteValue(DPE_DATE,$skr);
		 
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
		new_win = window.open("mata.php?id=<?php echo $cetakId;?>", "wndprint", "HEIGHT=653,WIDTH=453,menubar=yes,scrollbars=yes,left=300,top=200");
		document.location.href='<?php echo $thisPage;?>';
	<?php } ?>

	function CheckSimpan(frm){
		if(!frm.surat_mata_od.value) {
			alert('Visual OD harus diisi');
			frm.surat_mata_od.focus();
			return false;
		}
		
		if(!frm.surat_mata_koreksi_od.value) {
			alert('Koreksi OD harus diisi');
			frm.surat_mata_koreksi_od.focus();
			return false;
		}
		if(!frm.surat_mata_os.value) {
			alert('Visual OS harus diisi');
			frm.surat_mata_os.focus();
			return false;
		}
		
		if(!frm.surat_mata_koreksi_os.value) {
			alert('Koreksi OS harus diisi');
			frm.surat_mata_koreksi_os.focus();
			return false;
		} 
		
		if(!frm.surat_mata_field.value) {
			alert('Visual Field harus diisi');
			frm.surat_mata_field.focus();
			return false;
		}
		if(!frm.surat_mata_color.value) {
			alert('Color Blindness harus diisi');
			frm.surat_mata_color.focus();
			return false;
		}
		 
		if(!frm.surat_mata_diagnosis.value) {
			alert('Diagnosis harus diisi');
			frm.surat_mata_diagnosis.focus();
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
		<td align="left" colspan=2 class="tableheader">Surat Kesehatan Mata</td>
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
			<td colspan="4" align="center" class="subHeader">DATA PRIBADI</td>
		</tr>
		<tr>
			<td align="left" class="tablecontent">No. RM</td>
			<td align="left" class="tablecontent-odd" colspan="3">
				<?php echo $dataPasien["cust_usr_kode"];?>
			</td> 
		</tr>
		<tr>
			<td align="left" class="tablecontent">Nama Pasien</td>
			<td align="left" class="tablecontent-odd" colspan="3"> 
				<?php echo $dataPasien["cust_usr_nama"];?>
			</td> 
		</tr>
		<tr>
			<td align="left" class="tablecontent">Alamat Pasien</td>
			<td width= "40%" align="left" class="tablecontent-odd" colspan="3"> 
				<?php echo $dataPasien["cust_usr_alamat"];?>
			</td> 
		</tr>
		<tr>
			<td width= "15%" align="left" class="tablecontent">Visual OD</td>
			<td width= "35%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_mata_od","surat_mata_od","25","50",$_POST["surat_mata_od"],"inputField",null,false);?>
			</td> 
			<td width= "15%" align="left" class="tablecontent">Koreksi</td>
			<td width= "35%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_mata_koreksi_od","surat_mata_koreksi_od","20","50",$_POST["surat_mata_koreksi_od"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td align="left" class="tablecontent">Visual OS</td>
			<td align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_mata_os","surat_mata_os","25","50",$_POST["surat_mata_os"],"inputField",null,false);?>
			</td> 
			<td align="left" class="tablecontent">Koreksi</td>
			<td align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_mata_koreksi_os","surat_mata_koreksi_os","20","50",$_POST["surat_mata_koreksi_os"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Visual Field</td>
			<td width= "40%" align="left" class="tablecontent-odd" colspan="3"> 
				<?php echo $view->RenderTextBox("surat_mata_field","surat_mata_field","60","100",$_POST["surat_mata_field"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Ocular Motility</td>
			<td width= "40%" align="left" class="tablecontent-odd" colspan="3"> 
				<?php echo $view->RenderTextBox("surat_mata_ocular","surat_mata_ocular","60","100",$_POST["surat_mata_ocular"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Color Blindness</td>
			<td width= "40%" align="left" class="tablecontent-odd" colspan="3"> 
				<?php echo $view->RenderTextBox("surat_mata_color","surat_mata_color","60","100",$_POST["surat_mata_color"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Kelainan Lain</td>
			<td width= "40%" align="left" class="tablecontent-odd" colspan="3"> 
				<?php echo $view->RenderTextBox("surat_mata_lain","surat_mata_lain","60","100",$_POST["surat_mata_lain"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Diagnosis</td>
			<td width= "40%" align="left" class="tablecontent-odd" colspan="3"> 
				<?php echo $view->RenderTextBox("surat_mata_diagnosis","surat_mata_diagnosis","60","100",$_POST["surat_mata_diagnosis"],"inputField",null,false);?>
			</td> 
		</tr> 
		<tr>
			<td width= "20%" align="left" class="tablecontent">Terapi</td>
			<td width= "40%" align="left" class="tablecontent-odd" colspan="3"> 
				<?php echo $view->RenderTextBox("surat_mata_terapi","surat_mata_terapi","60","100",$_POST["surat_mata_terapi"],"inputField",null,false);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Dokter Pemeriksa</td>
			<td width= "40%" align="left" class="tablecontent-odd" colspan="3"> 
				<?php echo $view->RenderTextBox("dokter_nama","dokter_nama","30","100",$_POST["dokter_nama"],"inputField", "readonly",false);?>
				<a href="<?php echo $dokterPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
				<?php echo $view->RenderHidden("id_dokter","id_dokter",$_POST["id_dokter"]);?>
			</td> 
		</tr>
		
		<tr>
			<td colspan="4" align="center" class="tablecontent">
				<?php echo $view->RenderButton(BTN_SUBMIT,"btnSave","btnSave","Simpan","button",false,"onClick=\"return CheckSimpan(this.form);\"");?>
			</td> 
		</tr>
		
			
	</table>
</form> 
<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
