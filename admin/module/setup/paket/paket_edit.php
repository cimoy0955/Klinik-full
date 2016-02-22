<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
	   require_once($APLICATION_ROOT."library/view.cls.php");	
	   require_once($ROOT."library/currFunc.lib.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0;
	
	  $plx = new InoLiveX("CheckData");
	
     if(!$auth->IsAllowed("setup_paket",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_paket",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckData($paketNama,$paketId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT paket_id FROM mp_paket a 
                    WHERE upper(a.paket_nama) = ".QuoteValue(DPE_CHAR,strtoupper($paketNama));
                    
          if ($paketId) $sql .= " and a.paket_id <> ".QuoteValue(DPE_CHAR,$paketId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaPaket = $dtaccess->Fetch($rs);
        
		return $dataAdaPaket["paket_id"];
     }
  
    
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["paket_id"])  $paketId = & $_POST["paket_id"];

     $backPage = "paket_view.php?";

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $paketId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from mp_paket where paket_id = ".QuoteValue(DPE_CHAR,$paketId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["paket_nama"] = $row_edit["paket_nama"];
          $_POST["paket_member"] = $row_edit["paket_member"];
          $_POST["paket_harga"] = currency_format($row_edit["paket_harga"]);
          $_POST["paket_type"] = $row_edit["paket_type"];
          $_POST["paket_tgl_awal"] = format_date($row_edit["paket_tgl_awal"]);
          $_POST["paket_tgl_akhir"] = format_date($row_edit["paket_tgl_akhir"]);
          $_POST["paket_jumlah_jam_online"] = $row_edit["paket_jumlah_jam_online"];
          $mulai = explode(":",$row_edit["paket_jam_awal"]);
		      $_POST["awal_jam"] = $mulai[0];
		      $_POST["awal_menit"] = $mulai[1];
		      $akhir = explode(":",$row_edit["paket_jam_akhir"]);
		      $_POST["akhir_jam"] = $akhir[0];
		      $_POST["akhir_menit"] = $akhir[1];
          $_POST["paket_jenis"] = $row_edit["paket_jenis"];
        
          $hariPaket = explode("9",$row_edit["paket_hari"]);
          $_POST["paket_hari_minggu"] = $hariPaket[0];
          $_POST["paket_hari_senin"] = $hariPaket[1];
          $_POST["paket_hari_selasa"] = $hariPaket[2];
          $_POST["paket_hari_rabu"] = $hariPaket[3];
          $_POST["paket_hari_kamis"] = $hariPaket[4];
          $_POST["paket_hari_jumat"] = $hariPaket[5];
          $_POST["paket_hari_sabtu"] = $hariPaket[6];
          
          
          $_POST["paket_lama"] = $row_edit["paket_lama"];
     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $paketId = & $_POST["paket_id"];
               $_x_mode = "Edit";
          }
         
         
         
         
          if ($err_code == 0) {
               $dbTable = "mp_paket";
               
               $dbField[0] = "paket_id";   // PK
               $dbField[1] = "paket_nama";
               $dbField[2] = "paket_member";
               $dbField[3] = "paket_harga";
               $dbField[4] = "paket_type";
               $dbField[5] = "paket_jumlah_jam_online";
               $dbField[6] = "paket_tgl_awal";
               $dbField[7] = "paket_tgl_akhir";
               $dbField[8] = "paket_jam_awal";
               $dbField[9] = "paket_jam_akhir";
               $dbField[10] = "paket_jenis";
               $dbField[11] = "paket_hari";
               $dbField[12] = "paket_lama";
			         
			         if ($_POST["awal_jam"]<=9) $_POST["awal_jam"]="0".$_POST["awal_jam"];
			         if ($_POST["akhir_jam"]<=9) $_POST["akhir_jam"]="0".$_POST["akhir_jam"];
			         
			         if ($_POST["awal_menit"]<=9) $_POST["awal_menit"]="0".$_POST["awal_menit"];
			         if ($_POST["akhir_menit"]<=9) $_POST["akhir_menit"]="0".$_POST["akhir_menit"];
			         
			         $_POST["paket_jam_awal"] = $_POST["awal_jam"].":".$_POST["awal_menit"].":00";
		           $_POST["paket_jam_akhir"] = $_POST["akhir_jam"].":".$_POST["akhir_menit"].":00";
		           
		           if ($_POST["paket_hari_minggu"]) 
		                 $_POST["paket_hari"]='19'; else $_POST["paket_hari"].='09'; 
               if ($_POST["paket_hari_senin"]) 
		                 $_POST["paket_hari"].='19'; else $_POST["paket_hari"]='09';
		           if ($_POST["paket_hari_selasa"]) 
		                 $_POST["paket_hari"].='19'; else $_POST["paket_hari"].='09';      
		           if ($_POST["paket_hari_rabu"]) 
		                 $_POST["paket_hari"].='19'; else $_POST["paket_hari"].='09';      
		           if ($_POST["paket_hari_kamis"]) 
		                 $_POST["paket_hari"].='19'; else $_POST["paket_hari"].='09';      
		           if ($_POST["paket_hari_jumat"]) 
		                 $_POST["paket_hari"].='19'; else $_POST["paket_hari"].='09';      
		           if ($_POST["paket_hari_sabtu"]) 
		                 $_POST["paket_hari"].='19'; else $_POST["paket_hari"].='09';      
		                
		          
                  
               if(!$paketId) $paketId = $dtaccess->GetNewID("mp_paket","paket_id",DB_SCHEMA);   
               $dbValue[0] = QuoteValue(DPE_CHAR,$paketId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["paket_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["paket_member"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["paket_harga"]));
               $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["paket_type"]);
               $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["paket_jumlah_jam_online"]);
               $dbValue[6] = QuoteValue(DPE_DATE,date_db($_POST["paket_tgl_awal"]));
               $dbValue[7] = QuoteValue(DPE_DATE,date_db($_POST["paket_tgl_akhir"]));
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["paket_jam_awal"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["paket_jam_akhir"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["paket_jenis"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["paket_hari"]);
               $dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["paket_lama"]);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               header("location:".$backPage);
               exit();        
          }
     }
 
     if ($_POST["btnDelete"]) {
          $paketId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($paketId);$i<$n;$i++){
               $sql = "delete from mp_paket
                         where paket_id = ".QuoteValue(DPE_CHAR,$paketId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }	
     
     $tipeJenis[0] = $view->RenderOption("--","Pilih Tipe",$show);
     if($_POST["paket_jenis"]=="M") $show = "selected";
     $tipeJenis[1] = $view->RenderOption("M","Multiplayer",$show);
     unset($show);
     if($_POST["paket_jenis"]=="W") $show = "selected";
     $tipeJenis[2] = $view->RenderOption("W","Warnet",$show);
     unset($show);
     if($_POST["paket_jenis"]=="I") $show = "selected";
     $tipeJenis[3] = $view->RenderOption("I","Wifi",$show);
     unset($show);
     
     $typeJenis[0] = $view->RenderOption("--","Pilih Jenis",$show);
     if($_POST["paket_type"]=="1") $show = "selected";
     $typeJenis[1] = $view->RenderOption("1","Jenis Kelipatan",$show);
     unset($show);
     if($_POST["paket_type"]=="2") $show = "selected";
     $typeJenis[2] = $view->RenderOption("2","Jenis Paket",$show);
     unset($show);
     if($_POST["paket_type"]=="3") $show = "selected";
     $typeJenis[3] = $view->RenderOption("3","Jenis Happy Hour",$show);
     unset($show);
     
     if($_POST["paket_member"]=="y") $show = "selected";
     $memberJenis[0] = $view->RenderOption("y","Member",$show);
     unset($show);
     if($_POST["paket_member"]=="n") $show = "selected";
     $memberJenis[1] = $view->RenderOption("n","Guest",$show);
     unset($show);
     
     for($i=0,$n=24;$i<$n;$i++){
		 unset($show);
		 if($_POST["awal_jam"]==$i) $show = "selected";
		 $jamMulai[$i] = $view->RenderOption($i,$i,$show); 
	   }
	
     for($i=0,$n=3;$i<=$n;$i++){
		 unset($show);
		 unset($hasil);
		 $hasil = $i*15;
		 if($_POST["awal_menit"]==$hasil) $show = "selected";
		 $menitMulai[$i] = $view->RenderOption($hasil,$hasil,$show); 
	   }
	   
	   for($i=0,$n=24;$i<$n;$i++){
		 unset($show);
		 if($_POST["akhir_jam"]==$i) $show = "selected";
		 $jamAkhir[$i] = $view->RenderOption($i,$i,$show); 
	   }
	
     for($i=0,$n=3;$i<=$n;$i++){
		 unset($show);
		 unset($hasil);
		 $hasil = $i*15;
		 if($_POST["akhir_menit"]==$hasil) $show = "selected";
		 $menitAkhir[$i] = $view->RenderOption($hasil,$hasil,$show); 
	   }
?>

<?php echo $view->RenderBody("inventori.css",true); ?>
<?php echo $view->InitUpload(); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{     
	
  if(frm.paket_jenis.value=='--'){
		alert('Jenis Paket Harus Diisi');
		frm.paket_jenis.focus();
          return false;
	}	
	
	if(frm.paket_type.value=='--'){
		alert('Jenis Paket Harus Diisi');
		frm.paket_type.focus();
          return false;
	}	
	
	if(frm.paket_member.value=='--'){
		alert('Target Paket Harus Diisi');
		frm.paket_member.focus();
          return false;
	}	
	
	if(!frm.paket_nama.value){
		alert('Nama Harus Diisi');
		frm.paket_nama.focus();
          return false;
	}	
	
	if(!frm.paket_harga.value){
		alert('Harga Harus Diisi');
		frm.paket_harga.focus();
          return false;
	}	
	
	if(!frm.paket_jumlah_jam_online.value){
		alert('Jumlah Jam Harus Diisi');
		frm.paket_jumlah_jam_online.focus();
          return false;
	}	
	
	if(!frm.paket_lama.value){
		alert('Masa Berlaku Harus Diisi');
		frm.paket_lama.focus();
          return false;
	}
	
	
	if(CheckData(frm.paket_nama.value,frm.paket_id.value,'type=r')){
		alert('Nama Paket Sudah Ada');
		frm.paket_nama.focus();
		frm.paket_nama.select();
		return false;
	}
	
	return true;
          
}

</script>

<table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Paket</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="90%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Attribut Wajib</strong></legend>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" ><strong>Tipe</strong>&nbsp;</td>
               <td colspan="3" >
                 <?php echo $view->RenderComboBox("paket_jenis","paket_jenis",$tipeJenis,null,null,null);?>                  
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Jenis Paket</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderComboBox("paket_type","paket_type",$typeJenis,null,null,null);?>
               </td>
               <td align="right" class="tablecontent"><strong>Keterangan :</strong>&nbsp;</td>
               <td align="left" class="tablecontent"><strong>Jenis Kelipatan = Dipengaruhi jumlah jam pesanan</strong>&nbsp;</td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">&nbsp;</td>
               <td>&nbsp;</td>
               <td align="right" class="tablecontent">&nbsp;</td>
               <td align="left" class="tablecontent"><strong>Jenis Paket = Tidak dipengaruhi jumlah jam pesanan</strong>&nbsp;</td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">&nbsp;</td>
               <td>&nbsp;</td>
               <td align="right" class="tablecontent">&nbsp;</td>
               <td align="left" class="tablecontent"><strong>Jenis Happy Hour = Dipengaruhi jam awal dan jam akhir</strong>&nbsp;</td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Target Paket</strong>&nbsp;</td>
               <td colspan="3">
                    <?php echo $view->RenderComboBox("paket_member","paket_member",$memberJenis,null,null,null);?>
               </td>
          </tr>
          
          <tr>
               <td align="right" class="tablecontent" ><strong>Nama Paket</strong>&nbsp;</td>
               <td colspan="3" >
				            <?php echo $view->RenderTextBox("paket_nama","paket_nama","50","100",$_POST["paket_nama"],"inputField", null,false);?>                    
               </td>
          </tr>
          
          <tr>
            <td align="right" class="tablecontent"><strong>Harga Paket</td>
               <td colspan="3" >
				            <?php echo $view->RenderTextBox("paket_harga","paket_harga","30","100",$_POST["paket_harga"],"inputField", null,true);?>                    
               </td>
         </tr>
         <tr>
            <td align="right" class="tablecontent"><strong>Jumlah Jam (Khusus Tipe Paket)</td>
               <td colspan="3" >
				            <?php echo $view->RenderTextBox("paket_jumlah_jam_online","paket_jumlah_jam_online","5","100",$_POST["paket_jumlah_jam_online"],"inputField", null,false);?>&nbsp;Jam                    
               </td>
         </tr>
         <tr>
            <td align="right" class="tablecontent"><strong>Masa Berlaku</td>
               <td colspan="3" >
				            <?php echo $view->RenderTextBox("paket_lama","paket_lama","5","100",$_POST["paket_lama"],"inputField", null,false);?>&nbsp;Hari                    
               </td>
         </tr>
         </table>
     </fieldset>
     <fieldset>
     <legend><strong>Attribut Tambahan Guest (Tidak Berlaku untuk Member)</strong></legend>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
         
          <tr>
               <td align="right" class="tablecontent"><strong>Jam Berlaku Paket</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderComboBox("awal_jam","awal_jam",$jamMulai,null,null);?> &nbsp;:&nbsp; 
                    <?php echo $view->RenderComboBox("awal_menit","awal_menit",$menitMulai,null,null);?>
               </td>
               <td align="right" class="tablecontent-odd" ><strong>Sampai Pukul</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderComboBox("akhir_jam","akhir_jam",$jamAkhir,null,null);?> &nbsp;:&nbsp; 
                    <?php echo $view->RenderComboBox("akhir_menit","akhir_menit",$menitAkhir,null,null);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Tanggal Berlaku</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("paket_tgl_awal","paket_tgl_awal","15","100",$_POST["paket_tgl_awal"],"inputField", null,false);?>
                    <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" /> 
               </td>
               <td align="right" class="tablecontent"><strong>Sampai</strong>&nbsp;</td>
               <td>
                    <?php echo $view->RenderTextBox("paket_tgl_akhir","paket_tgl_akhir","15","100",$_POST["paket_tgl_akhir"],"inputField", null,false);?>
                    <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" /> 
               </td>
          </tr>
          
          <tr>
               <td align="right" class="tablecontent"><strong>Hari Berlaku(Untuk Happy Hour)</strong>&nbsp;</td>
               <td colspan="3">
                    <?php echo $view->RenderCheckBox("paket_hari_minggu","phmi","1","inputField", $_POST["paket_hari_minggu"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("phmi","phmi","Minggu","null","null")?>
                    <?php echo $view->RenderCheckBox("paket_hari_senin","phsn","1","inputField", $_POST["paket_hari_senin"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("phsn","phsn","Senin","null","null")?>
				            <?php echo $view->RenderCheckBox("paket_hari_selasa","phsl","1","inputField", $_POST["paket_hari_selasa"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("phsl","phsl","Selasa","null","null")?>	
				            <?php echo $view->RenderCheckBox("paket_hari_rabu","phra","1","inputField", $_POST["paket_hari_rabu"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("phra","phra","Rabu","null","null")?>
				            <?php echo $view->RenderCheckBox("paket_hari_kamis","phka","1","inputField", $_POST["paket_hari_kamis"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("phka","phka","Kamis","null","null")?>
				            <?php echo $view->RenderCheckBox("paket_hari_jumat","phju","1","inputField", $_POST["paket_hari_jumat"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("phju","phju","Jumat","null","null")?>
                    <?php echo $view->RenderCheckBox("paket_hari_sabtu","phsa","1","inputField", $_POST["paket_hari_sabtu"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("phsa","phsa","Sabtu","null","null")?>                    
               </td>
          </tr>
          
          
          
          <tr>
               <td colspan="4" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='paket_view.php';\"");?>                    
               </td>
          </tr>
     
     </td>
</tr>
</table>

<script>document.frmEdit.paket_jenis.focus();</script>
<?php echo $view->RenderHidden("paket_id","paket_id",$paketId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "paket_tgl_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "paket_tgl_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
</form>

<?php echo $view->RenderBodyEnd(); ?>
