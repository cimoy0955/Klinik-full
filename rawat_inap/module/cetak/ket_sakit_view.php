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
     
     if(!$auth->IsAllowed("rawat_inap",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("rawat_inap",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
 
     $thisPage = "ket_sakit_view.php"; 
     $findPage = "pasien_find.php?";
     $dokterPage = "dokter_find.php?";
	
	if($_POST["cust_usr_kode"]) {
		$sql = "select cust_usr_id, cust_usr_nama,cust_usr_kode, cust_usr_alamat from global.global_customer_user a
				where a.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
		
          $_POST["id_cust_usr"] = $dataPasien["cust_usr_id"];
     }
	
	if(!$_POST["surat_sakit_lama"]) $_POST["surat_sakit_lama"] = 1;
	if(!$_POST["surat_sakit_tanggal"]) $_POST["surat_sakit_tanggal"] = format_date($tgl);
	
	if($_POST["btnSave"]) {
          $dbTable = "klinik_surat_ket_sakit";

		$dbField[0] = "surat_sakit_id";   // PK
		$dbField[1] = "surat_sakit_lama";
		$dbField[2] = "surat_sakit_tanggal";
		$dbField[3] = "id_pgw";
		$dbField[4] = "id_cust_usr";
		$dbField[5] = "surat_sakit_who";
		$dbField[6] = "surat_sakit_when";
		
		$cetakId = $dtaccess->GetTransID();
		$dbValue[0] = QuoteValue(DPE_CHAR,$cetakId);
		$dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["surat_sakit_lama"]);
		$dbValue[2] = QuoteValue(DPE_DATE,date_db($_POST["surat_sakit_tanggal"]));
		$dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["id_dokter"]);
		$dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
		$dbValue[5] = QuoteValue(DPE_CHAR,$userData["name"]);
		$dbValue[6] = QuoteValue(DPE_DATE,$skr);
		 
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

<?php echo $view->RenderBody("inosoft.css",true); ?>

<?php echo $view->InitThickBox(); ?>

<script language="JavaScript">
	<?php if($cetak) { ?>
		new_win = window.open("ket_sakit.php?id=<?php echo $cetakId;?>", "wndprint", "HEIGHT=670,WIDTH=389,menubar=yes,scrollbars=yes,left=300,top=200");
		document.location.href='<?php echo $thisPage;?>';
	<?php } ?>

	function CheckSimpan(frm){
		if(!frm.surat_sakit_lama.value) {
			alert('Lama Istirahat harus diisi');
			frm.surat_sakit_lama.focus();
			return false;
		}
		
		if(!frm.surat_sakit_tanggal.value) {
			alert('Tanggal Istirahat harus diisi');
			frm.surat_sakit_tanggal.focus();
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
		<td align="left" colspan=2 class="tableheader">Surat Keterangan Sakit</td>
	</tr>
</table> 


	
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
     <tr>
		<td width= "5%" align="left" class="tablecontent">Kode Pasien</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="25" maxlength="25" value="<?php echo $_POST["cust_usr_kode"];?>"/>
               <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
               <input type="submit" name="btnLanjut" value="Lanjut" class="button"/>
			<?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["id_cust_usr"]);?>
          </td>
</table>
<br>
<?php if(!$dataPasien["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>Kode Pasien Tidak Ditemukan</strong></font>
<?php } ?>
<?php if($dataPasien["cust_usr_id"]) { ?>
<script>document.frmFind.cust_usr_kode.focus();</script>
	<table width="80%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td colspan="3" align="center" class="subHeader">DATA PRIBADI</td>
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Kode Pasien</td>
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
			<td width= "20%" align="left" class="tablecontent">Selama</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_sakit_lama","surat_sakit_lama","5","4",$_POST["surat_sakit_lama"],"inputField",null,true);?>
			</td> 
		</tr>
		<tr>
			<td width= "20%" align="left" class="tablecontent">Mulai Tanggal</td>
			<td width= "40%" align="left" class="tablecontent-odd"> 
				<?php echo $view->RenderTextBox("surat_sakit_tanggal","surat_sakit_tanggal","13","10",$_POST["surat_sakit_tanggal"],"inputField",null,false);?>
				<img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_surat_sakit_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
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

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "surat_sakit_tanggal",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_surat_sakit_tanggal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
