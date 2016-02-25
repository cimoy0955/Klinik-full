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
     

 	if(!$auth->IsAllowed("diagnostik",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("diagnostik",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "tipe_diagnostik.php";
	$dokterPage = "diag_dokter_find.php?";
	$susterPage = "diag_suster_find.php?";
     $backPage = "diag_view.php?";


     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetDiagnostik,SetDiagnostik");     


     function GetDiagnostik($status) {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, a.reg_jadwal, z.fol_lunas 
                    from klinik.klinik_registrasi a
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
				left join (
					select distinct fol_lunas, id_reg from klinik.klinik_folio
					where fol_lunas = 'n' and fol_jenis = '".STATUS_DIAGNOSTIK_TIPE."' 
				) z on a.reg_id = z.id_reg 
                    where a.reg_status = '".STATUS_DIAGNOSTIK_TIPE.$status."' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);
          //return $sql;
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
               $_POST["diag_id"] = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_diagnostik a 
				where diag_id = ".QuoteValue(DPE_CHAR,$_POST["diag_id"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $view->CreatePost($row_edit);
          $_GET["id_reg"] = $row_edit["id_reg"];
          
     }

     // --- cari input diagnostik pertama hari ini ---
     $sql = "select a.diag_id 
               from klinik.klinik_diagnostik a 
               where cast(a.diag_waktu as date) = ".QuoteValue(DPE_CHAR,date('Y-m-d'))." 
               order by diag_waktu asc limit 1";
     $rs = $dtaccess->Execute($sql);
     $firstData = $dtaccess->Fetch($rs);
     
     $edit = (($firstData["diag_id"]==$_POST["diag_id"])||!$firstData["diag_id"])?true:false;
     
	
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
               
          
               //Update ke Status sudah bayar di Diagnostik
              
                       // insert ke tabel klinik_history_pasien
                  $dbSchema = "klinik";
                  $dbTable = "klinik_history_pasien";

                  $dbField[0] = "history_id";
                  $dbField[1] = "id_reg";
                  $dbField[2] = "history_status_pasien";
                  $dbField[3] = "history_when_out";

                  $history_id = $dtaccess->GetTransID();
                  $dbValue[0] = QuoteValue(DPE_CHAR,$history_id);
                  $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                  $dbValue[2] = QuoteValue(DPE_CHAR,STATUS_DIAGNOSTIK_TIPE);
                  $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

                  $dbKey[0] = 0;

                  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,$dbSchema);

                  $dtmodel->Insert() or die("insert error");

                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
                  // end insert 

                   $sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_DIAGNOSTIK.STATUS_ANTRI."', reg_waktu = CURRENT_TIME  where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
                   $dtaccess->Execute($sql);
                             
                   // ---- inset ket folio ---//
                   if($_POST["diag_keratometri"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_KERATOMETRI);
                   if($_POST["diag_biometri"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_BIOMETRI);     
                   if($_POST["diag_usg"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_USG);
                   if($_POST["diag_lab_gula_darah"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_GULA);
                   if($_POST["diag_ekg"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_EKG);
                   if($_POST["diag_fundus"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_FUNDUS);
                   if($_POST["diag_opthalmoscop"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_OPTHALMOSCOPY);
                   if($_POST["diag_oct"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_OCT);
                   if($_POST["diag_yag"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_YAG);
                   if($_POST["diag_argon"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_ARGON);
                   if($_POST["diag_glaukoma"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_GLAUKOMA);
                   if($_POST["diag_humpre"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_HUMPREY);
                   if($_POST["diag_slt"]) $sql_where[] = "biaya_kode = ".QuoteValue(DPE_CHAR,BIAYA_SLT);
    
                   
                   if($sql_where) {
                        $sql = "select * from klinik.klinik_biaya where ".implode(" or ",$sql_where);
                        $dataBiaya = $dtaccess->FetchAll($sql,DB_SCHEMA);  
                        $folWaktu = date("Y-m-d H:i:s");
                   
                   }               
                   
                   //$lunas = ($_POST["reg_jenis_pasien"]!=PASIEN_BAYAR_SWADAYA)?'y':'n'; 
                   $lunas = "n";
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
                      $dbValue[5] = QuoteValue(DPE_CHAR,STATUS_DIAGNOSTIK);
                      $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
                      $dbValue[7] = QuoteValue(DPE_DATE,$folWaktu);
                      $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
                      $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                      $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataBiaya[$i]["biaya_total"]);
                      
                      //if($row_edit["cust_id"]) $custId = $row_edit["cust_id"];
                      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
                      
                      $dtmodel->Insert() or die("insert error"); 
                      
                      unset($dtmodel);
                      unset($dbValue);
                      unset($dbKey);
                      unset($dbField); 
                       
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
                      } //end for datasplit 
                   } //end for datafolio
                    $_x_mode = "Save";
                  // } //end if $err_code          
                  
              } // end if btnsave
	} // end if btnsave || btnupdate

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

function selectAll(){
         document.getElementById('cbKeratometri').checked = true;
         document.getElementById('cbBiometri').checked = true;
         document.getElementById('cbUSG').checked = true;
         document.getElementById('cbGulaDarah').checked = true;
         document.getElementById('cbEKG').checked = true;
         document.getElementById('cbFundus').checked = true;
         document.getElementById('cbOCT').checked = true;
         document.getElementById('cbYAG').checked = true;   
         document.getElementById('cbSLT').checked = true;
         document.getElementById('cbOpthalmoscop').checked = true;
         document.getElementById('cbArgon').checked = true;
         document.getElementById('cbGlaukoma').checked = true;
         document.getElementById('cbHumptre').checked = true;
}
                                                            
function deselectAll(){
         document.getElementById('cbKeratometri').checked = false;
         document.getElementById('cbBiometri').checked = false;
         document.getElementById('cbUSG').checked = false;
         document.getElementById('cbGulaDarah').checked = false;
         document.getElementById('cbEKG').checked = false;
         document.getElementById('cbFundus').checked = false;
         document.getElementById('cbOCT').checked = false;
         document.getElementById('cbYAG').checked = false;
         document.getElementById('cbSLT').checked = false;        
         document.getElementById('cbOpthalmoscop').checked = false;
         document.getElementById('cbArgon').checked = false;
         document.getElementById('cbGlaukoma').checked = false;
         document.getElementById('cbHumptre').checked = false;
}

function checkFrmSave() {
  if ((document.getElementById('cbKeratometri').checked == false) &&
      (document.getElementById('cbBiometri').checked == false) &&
      (document.getElementById('cbUSG').checked == false) &&
      (document.getElementById('cbGulaDarah').checked == false) &&
      (document.getElementById('cbEKG').checked == false) &&
      (document.getElementById('cbFundus').checked == false) &&
      (document.getElementById('cbOCT').checked == false) &&
      (document.getElementById('cbYAG').checked == false) &&
      (document.getElementById('cbSLT').checked == false) &&      
      (document.getElementById('cbOpthalmoscop').checked == false) &&
      (document.getElementById('cbArgon').checked == false) &&
      (document.getElementById('cbGlaukoma').checked == false) &&
      (document.getElementById('cbHumptre').checked == false)) {
    alert("Pilih salah satu pemeriksaan");
    return false;
  }else{
    return true;
  }
}

<?php if($_x_mode=="Save"){ ?>
    document.location.href='<?php echo $thisPage;?>';
<?php } ?>
</script>
<?php if(!$_GET["id"]) { ?>

<!--<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Tipe Diagnostik</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php //echo GetDiagnostik(0); ?></div>
	</div>-->
	
	<div id="antri_kanan" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:80%;">
		<div class="tableheader">Jenis Pemeriksaan</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php //echo GetDiagnostik(0); ?></div>
	</div>
</div>

<?php } ?>



<?php if($dataPasien) { ?>
<br />
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Diagnostik</td>
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
               <td width= "20%" align="left" class="tablecontent">Kode Pasien</td>
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
     <legend><strong>Diagnostik</strong>&nbsp;<a href="#diag" id="diag" onClick="selectAll();">Pilih Semua</a>/<a href="#diag" onClick="deselectAll();">Hilangkan Semua</a>&nbsp;<?php if($err_code==1) {?>&nbsp;<font color="red">(*)</font><?php }?></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr>
               <td colspan="4">
                    <?php echo $view->RenderCheckBox("diag_keratometri","cbKeratometri","1","inputField", $_POST["diag_keratometri"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbKeratometri","lbKeratometri","Keratometri","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_biometri","cbBiometri","1","inputField", $_POST["diag_biometri"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbBiometri","lbBiometri","Biometri","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_usg","cbUSG","1","inputField", $_POST["diag_usg"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbUSG","lbUSG","USG","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_lab_gula_darah","cbGulaDarah","1","inputField", $_POST["diag_lab_gula_darah"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbGulaDarah","lbBiometri","Gula Darah","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_ekg","cbEKG","1","inputField", $_POST["diag_ekg"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbEKG","lbEKG","EKG","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_fundus","cbFundus","1","inputField", $_POST["diag_fundus"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbFundus","lbFundus","Fundus","null","null")?>
               </td>
          </tr>
          <tr>
               <td colspan="4">
                    <?php echo $view->RenderCheckBox("diag_opthalmoscop","cbOpthalmoscop","1","inputField", $_POST["diag_opthalmoscop"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbOpthalmoscop","lbOpthalmoscop","Opthalmoscop","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_oct","cbOCT","1","inputField", $_POST["diag_oct"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbOCT","lbOCT","OCT","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_yag","cbYAG","1","inputField", $_POST["diag_yag"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbYAG","lbYAG","YAG","null","null")?>                   
                    <?php echo $view->RenderCheckBox("diag_slt","cbSLT","1","inputField", $_POST["diag_slt"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbSLT","lbSLT","SLT","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_argon","cbArgon","1","inputField", $_POST["diag_argon"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbArgon","lbArgon","Argon","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_glaukoma","cbGlaukoma","1","inputField", $_POST["diag_glaukoma"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbGlaukoma","lbGlaukoma","Glaukom","null","null")?>
                    <?php echo $view->RenderCheckBox("diag_humpre","cbHumptre","1","inputField", $_POST["diag_humpre"]=='1'?"checked":null,false);?><?php echo $view->RenderLabel("lbHumpre","lbHumpre","Humpre","null","null")?>
               </td>
          </tr>
	  </table>
               </td>
          </tr>
	</table>
     </fieldset>
     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
	  
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",null,"OnClick='return checkFrmSave();'");?></td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>	

</table>



<?php echo $view->SetFocus("cbKeratometri");?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="diag_id" value="<?php echo $_POST["diag_id"];?>"/>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>

</span>

</form>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
