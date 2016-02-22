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
     $userData = $auth->GetUserData();
     

 	if(!$auth->IsAllowed("klinik",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("klinik",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "tipe_tindakan.php";
	$dokterPage = "diag_dokter_find.php?";
	$susterPage = "diag_suster_find.php?";
     $backPage = "diag_view.php?";


     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetDiagnostik,SetDiagnostik");     


     function GetDiagnostik($status) {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
               
          /*$sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, a.reg_jadwal, z.fol_lunas 
                    from klinik.klinik_registrasi a
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
				left join (
					select distinct fol_lunas, id_reg from klinik.klinik_folio
					where fol_lunas = 'n' and fol_jenis = '".STATUS_PEMERIKSAAN."' 
				) z on a.reg_id = z.id_reg 
                    where a.reg_status like '".STATUS_DIAGNOSTIK.$status."' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);*/
          
              
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, a.reg_jadwal 
                    from klinik.klinik_registrasi a 
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    left join (
					select distinct fol_lunas, id_reg from klinik.klinik_folio
					where fol_lunas = 'n' and fol_jenis = '".STATUS_PEMERIKSAAN."' 
				) z on a.reg_id = z.id_reg 
                    where a.reg_status like '".STATUS_OPERASI_JADWAL.$status."'
				order by reg_status desc, reg_tanggal asc, reg_waktu asc";

          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;

          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {

				$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

          }

          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}

     function SetDiagnostik($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_DIAGNOSTIK.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}

     if ($_GET["id"]) {
          // === buat ngedit ---          
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["rawat_id"] = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_perawatan a 
				where rawat_id = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $view->CreatePost($row_edit);
          $_GET["id_reg"] = $row_edit["id_reg"];
          
     }

     // --- cari input perawatan pertama hari ini ---
     $sql = "select a.rawat_id 
               from klinik.klinik_perawatan a 
               where cast(a.rawat_waktu as date) = ".QuoteValue(DPE_CHAR,date('Y-m-d'))." 
               order by rawat_waktu asc limit 1";
     $rs = $dtaccess->Execute($sql);
     $firstData = $dtaccess->Fetch($rs);
     
     $edit = (($firstData["rawat_id"]==$_POST["rawat_id"])||!$firstData["rawat_id"])?true:false;
     
	
	if($_GET["id_reg"]) {
		$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, b.cust_usr_alergi, a.reg_jenis_pasien,  
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr
                    from klinik.klinik_registrasi a 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
    $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
	}

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
	
     

          

          if($_POST["btnSave"]) {
               
  if($_POST["preop_lab_gula_darah"] || $_POST["preop_lab_darah_lengkap"]) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_GULA_PREOP);               
  if($_POST["preop_regulasi"])$sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_GULAREGULASI_PREOP);                //=="y" && ($_POST["preop_regulasi_gula_obat"] || $_POST["preop_regulasi_gula_hasil"])) $sql_where[] = "biaya_id = ".QuoteValue(DPE_CHAR,BIAYA_GULAREGULASI_PREOP);               
               	
  if($sql_where) {
					$sql = "select * from klinik.klinik_biaya where ".implode(" or ",$sql_where);
					$dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
					$folWaktu = date("Y-m-d H:i:s");
			}
               $lunas = ($_POST["reg_jenis_pasien"]==PASIEN_BAYAR_SWADAYA)?'n':'y';

               for($i=0,$n=count($dataBiaya);$i<$n;$i++) {
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
                           
                    $folId = $dtaccess->GetTransID();
                    $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
                    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                    $dbValue[2] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_nama"]);
                    $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
                    $dbValue[4] = QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"]);
                    $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_OPERASI_JADWAL);
                    $dbValue[6] = QuoteValue(DPE_NUMERICKEY,$_POST["id_cust_usr"]);
                    $dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
                    $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
                    $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
                    
                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                    
                    $dtmodel->Insert() or die("insert error"); 
                    
                    unset($dtmodel);
                    unset($dbField);
                    unset($dbValue);
                    unset($dbKey);

                
                    $sql = "select * from klinik.klinik_biaya_split where id_biaya = ".QuoteValue(DPE_CHAR,$dataBiaya[$i]["biaya_id"])." and bea_split_nominal > 0";
                    $dataSplit = $dtaccess->FetchAll($sql,DB_SCHEMA);
                    
                    for($a=0,$b=count($dataSplit);$a<$b;$a++) { 
                         $dbTable = "klinik_folio_split";
                    
                         $dbField[0] = "folsplit_id";   // PK
                         $dbField[1] = "id_fol";
                         $dbField[2] = "id_split";
                         $dbField[3] = "folsplit_nominal";
                                
                         $dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
                         $dbValue[1] = QuoteValue(DPE_CHAR,$folId);
                         $dbValue[2] = QuoteValue(DPE_CHAR,$dataSplit[$a]["id_split"]);
                         $dbValue[3] = QuoteValue(DPE_NUMERIC,$dataSplit[$a]["bea_split_nominal"]);
                          
                         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                         
                         $dtmodel->Insert() or die("insert error"); 
                         
                         unset($dtmodel);
                         unset($dbField);
                         unset($dbValue);
                         unset($dbKey); 
                    } 


               }
          }          
          
          if($_POST["btnSave"]) echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";

          exit();   

	}

?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>


<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     //GetDiagnostik(0,'target=antri_kiri_isi');     
     GetDiagnostik(0,'target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}

function ProsesDiagnostik(id) {
	SetDiagnostik(id,'type=r');
	timer();
}

timer();

</script>


<?php if(!$_GET["id"]) { ?>
	
	<div id="antri_kanan" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:80%;">
		<div class="tableheader">Proses Tindakan</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetDiagnostik(0); ?></div>
	</div>
</div>

<?php } ?>



<?php if($dataPasien) { ?>
<br />
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Tindakan</td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "20%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "20%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Umur</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Alergi</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label style="color:red"><?php echo $dataPasien["cust_usr_alergi"]; ?></label></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Tindakan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr>
               <td colspan="4">
                    <?php //echo $view->RenderCheckBox("diag_keratometri","cbKeratometri","1","inputField", $_POST["diag_keratometri"]=='1'?"checked":null,false);?><?php //echo $view->RenderLabel("lbKeratometri","lbKeratometri","Keratometri","null","null")?>
                    <?php //echo $view->RenderCheckBox("diag_biometri","cbBiometri","1","inputField", $_POST["diag_biometri"]=='1'?"checked":null,false);?><?php //echo $view->RenderLabel("lbBiometri","lbBiometri","Biometri","null","null")?>
                    <?php //echo $view->RenderCheckBox("diag_usg","cbUSG","1","inputField", $_POST["diag_usg"]=='1'?"checked":null,false);?><?php //echo $view->RenderLabel("lbUSG","lbUSG","USG","null","null")?>
                    <?php echo $view->RenderCheckBox("preop_lab_gula_darah","cbGulaDarah","1","inputField", $_POST["preop_lab_gula_darah"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbGulaDarah","lbBiometri","Gula Darah","null","null")?>
                    <?php echo $view->RenderCheckBox("preop_regulasi","cbRegulasi","1","inputField", $_POST["preop_regulasi"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbRegulasi","lbRegulasi","Regulasi","null","null")?>                    
                    <?php //echo $view->RenderCheckBox("diag_ekg","cbEKG","1","inputField", $_POST["diag_ekg"]=='1'?"checked":null,false);?><?php //echo $view->RenderLabel("lbEKG","lbEKG","EKG","null","null")?>
                    <?php //echo $view->RenderCheckBox("diag_fundus","cbFundus","1","inputField", $_POST["diag_fundus"]=='1'?"checked":null,false);?><?php //echo $view->RenderLabel("lbFundus","lbFundus","Fundus","null","null")?>
               </td>
          </tr>
        <!--  <tr>
               <td colspan="4">
                    <?php// echo $view->RenderCheckBox("diag_opthalmoscop","cbOpthalmoscop","1","inputField", $_POST["diag_opthalmoscop"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbOpthalmoscop","lbOpthalmoscop","Opthalmoscop","null","null")?>
                    <?php// echo $view->RenderCheckBox("diag_oct","cbOCT","1","inputField", $_POST["diag_oct"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbOCT","lbOCT","OCT","null","null")?>
                    <?php// echo $view->RenderCheckBox("diag_yag","cbYAG","1","inputField", $_POST["diag_yag"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbYAG","lbYAG","YAG","null","null")?>
                    <?php// echo $view->RenderCheckBox("diag_argon","cbArgon","1","inputField", $_POST["diag_argon"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbArgon","lbArgon","Argon","null","null")?>
                    <?php// echo $view->RenderCheckBox("diag_glaukoma","cbGlaukoma","1","inputField", $_POST["diag_glaukoma"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbGlaukoma","lbGlaukoma","Glaukom","null","null")?>
                    <?php// echo $view->RenderCheckBox("diag_humpre","cbHumptre","1","inputField", $_POST["diag_humpre"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbHumpre","lbHumpre","Humpre","null","null")?>
               </td>
          </tr>-->
				</table>
               </td>
          </tr>
	</table>
     </fieldset>
     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>	

</table>



<?php echo $view->SetFocus("phmi");?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["rawat_id"];?>"/>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
</span>

</form>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
