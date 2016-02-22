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
     
     if(!$_POST["perawat_tanggal_kontrol"]) $_POST["perawat_tanggal_kontrol"] = getDateToday();
     if(!$_POST["pukul_jam"]) $_POST["pukul_jam"] = date('H');
     if(!$_POST["pukul_menit"]) $_POST["pukul_menit"] = date('i');
     
     if(!$auth->IsAllowed("perawatan",PRIV_CREATE)){
              die("access_denied");
              exit(1);
         } else if($auth->IsAllowed("perawatan",PRIV_CREATE)===1){
              echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
              exit(1);
         }
         
     $tableRefraksi = new InoTable("table1","99%","center");


     $plx = new InoLiveX("GetPerawatan,GetTonometri,GetDosis");     

     function GetTonometri($scale,$weight) {
          global $dtaccess; 
               
          $sql = "select tono_pressure from klinik.klinik_tonometri where tono_scale = ".QuoteValue(DPE_NUMERIC,$scale)." and tono_weight = ".QuoteValue(DPE_NUMERIC,$weight);
          $dataTable = $dtaccess->Fetch($sql);
          return $dataTable["tono_pressure"];

     }     

     function GetPerawatan() {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
          
          $sql = "select cust_usr_nama,a.reg_id,a.reg_status,a.reg_waktu,a.reg_jadwal, 
                  c.rawatinap_tanggal_masuk,c.rawatinap_id,d.kategori_nama,e.kamar_kode,f.bed_kode
                  from klinik_registrasi a
                  left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            			left join klinik_rawatinap c on c.id_reg = a.reg_id
            			left join klinik_kamar_kategori d on d.kategori_id = c.id_kategori_kamar
            			left join klinik_kamar e on e.kamar_id = c.id_kamar and e.id_kategori = c.id_kategori_kamar
            			left join klinik_kamar_bed f on f.bed_id = c.id_bed and f.id_kamar = c.id_kamar
                  where a.reg_status like '".STATUS_RAWATINAP.STATUS_MENGINAP."' order by reg_status desc, kategori_nama, kamar_kode, rawatinap_tanggal_masuk asc, rawatinap_waktu_masuk asc";
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $dataTable = $dtaccess->FetchAll($rs);
          
          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Kelas";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Kamar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Bed";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

          
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
				
	       $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?id_reg='.$dataTable[$i]["reg_id"].'&id_rawatinap='.$dataTable[$i]["rawatinap_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;

         $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
         $tbContent[$i][$counter][TABLE_ALIGN] = "right";
         $counter++;
         
         $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "left";
         $counter++;
         
         $tbContent[$i][$counter][TABLE_ISI] = format_date_long($dataTable[$i]["rawatinap_tanggal_masuk"]);
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kategori_nama"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kamar_kode"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed_kode"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          }
			
          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}

     
     function GetDosis($fisik,$akhir,$id=null) {
          global $dtaccess, $view;
          
          $sql = "select dosis_id, dosis_nama from inventori.inv_dosis where id_fisik = ".QuoteValue(DPE_NUMERIC,$fisik);
          $dataTable = $dtaccess->FetchAll($sql);

          $optDosis[0] = $view->RenderOption("","[Pilih Dosis Obat]",$show); 
          for($i=0,$n=count($dataTable);$i<$n;$i++) {
               $show = ($id==$dataTable[$i]["dosis_id"]) ? "selected":"";
               $optDosis[$i+1] = $view->RenderOption($dataTable[$i]["dosis_id"],$dataTable[$i]["dosis_nama"],$show); 
          }
          
          return $view->RenderComboBox("id_dosis[]","id_dosis_".$akhir,$optDosis,null,null,null);
     }
     
     if(!$_POST["btnSaveRawat"] && !$_POST["btnUpdateRawat"]) {
          // --- buat cari suster yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id 
                    from klinik.klinik_perawatan_suster a 
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat 
                    where c.rawat_tanggal = ".QuoteValue(DPE_DATE,date("Y-m-d"));
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["rawat_suster_nama"][$i]) $_POST["rawat_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     }
	
	if(!$_POST["btnSaveRawat"] && !$_POST["btnUpdateRawat"]) {
	
     	// --- buat cari pemeriksaan yg tugas hari ini --- 
          $sql = "select distinct pgw_nama, pgw_id from klinik.klinik_perawatan_suster a
                    join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                    join klinik.klinik_perawatan c on c.rawat_id = a.id_rawat
                    where c.rawat_tanggal = ".QuoteValue(DPE_DATE,date('Y-m-d')); 
          $rs = $dtaccess->Execute($sql);
          
          $i=0;     
          while($row=$dtaccess->Fetch($rs)) {
               if(!$_POST["id_suster"][$i]) $_POST["id_suster"][$i] = $row["pgw_id"];
               if(!$_POST["ref_suster_nama"][$i]) $_POST["ref_suster_nama"][$i] = $row["pgw_nama"];
               $i++;
          }
     
     }
     
     if ($_GET["id"]) {
          // === buat ngedit ---          
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["rawat_id"] = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.id_reg from klinik.klinik_perawatan a 
				where rawat_id = ".QuoteValue(DPE_CHAR,$_POST["rawat_id"]);
          $row_edit = $dtaccess->Fetch($sql);
          
          $_GET["id_reg"] = $row_edit["id_reg"];
     }     
     
     if($_GET["id_reg"] && $_GET["id_rawatinap"]) {
    	$sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, 
                      ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                      from klinik.klinik_registrasi a
    			            left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                      where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
            $dataPasien= $dtaccess->Fetch($sql);
            //echo $sql;
          	$_POST["id_reg"] = $_GET["id_reg"]; 
          	$_POST["id_rawatinap"] = $_GET["id_rawatinap"];
          	$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
            $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
          	$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
      }
      
	foreach($rawatKeadaan as $key => $value) {
          unset($show);
          if($_POST["rawat_keadaan_umum"]==$key) $show="selected";
		$optionsKeadaan[] = $view->RenderOption($key,$value,$show);
	}
	
	for($r=0;$r<24;$r++){
    $opt_jam[] = $view->RenderOption((strlen($r)==1)?"0".$r:$r,(strlen($r)==1)?"0".$r:$r,($r==$_POST["pukul_jam"])?"selected":"");
  }
  
  for($r=0;$r<60;$r++){
    $opt_menit[] = $view->RenderOption((strlen($r)==1)?"0".$r:$r,(strlen($r)==1)?"0".$r:$r,($r==$_POST["pukul_menit"])?"selected":"");
  }
  
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>


<?php if(!$_GET["id"]) { ?>
	<div id="antri_kanan" style="width:100%;">
		<div class="tableheader">Proses Kontrol Harian</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetPerawatan(); ?></div>
	</div>
<?php } ?>
<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Pemeriksaan</td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
  <td width="60%"  style="vertical-align:top">
  <fieldset>
    <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "30%" align="left" class="tablecontent">Tanggal</td>
               <td width= "70%" align="left" class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("perawat_tanggal_kontrol","perawat_tanggal_kontrol","15","15",format_date($_POST["perawat_tanggal_kontrol"]),"inputfield"); ?>
	                <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               </td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent">Pukul</td>
               <td width= "70%" align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("pukul_jam","pukul_jam",$opt_jam,"inputField"); ?>&nbsp;:&nbsp;<?php echo $view->RenderComboBox("pukul_menit","pukul_menit",$opt_menit,"inputField"); ?></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Umur</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Keluhan Pasien</td>
               <td width= "40%" align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_keluhan","rawat_keluhan","50","200",$_POST["rawat_keluhan"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Keadaan Umum</td>
               <td width= "40%" align="left" class="tablecontent-odd"><?php echo $view->RenderComboBox("rawat_keadaan_umum","rawat_keadaan_umum",$optionsKeadaan,null,null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Tensi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_tensi","rawat_lab_tensi","15","15",$_POST["rawat_lab_tensi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Nadi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_nadi","rawat_lab_nadi","15","15",$_POST["rawat_lab_nadi"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pernafasan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rawat_lab_nafas","rawat_lab_nafas","15","15",$_POST["rawat_lab_nafas"],"inputField", null,false);?></td>
          </tr>
  	</table>
  </fieldset>
  <fieldset>
   <legend><strong>Terapi Alat</strong></legend>
   <table width="100%" border="1" cellpadding="4" cellspacing="1">
      <tr>
          <td align="center" class="tablesmallheader" width="50%">Jenis Alat</td>
          <td align="center" class="tablesmallheader">Nilai</td>
      </tr>
      <tr>
               <td align="left" class="tablecontent">EKG</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_ekg","diag_ekg","30","15",$_POST["diag_ekg"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Fundus Angiografi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_fundus","diag_fundus","30","15",$_POST["diag_fundus"],"inputField", null,false);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Indirect Opthalmoscopy</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_opthalmoscop","diag_opthalmoscop","30","50",$_POST["diag_opthalmoscop"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Optical Coherence Tomographi</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_oct","diag_oct","30","50",$_POST["diag_oct"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Yag Laser</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_yag","diag_yag","30","50",$_POST["diag_yag"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Argon Laser</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_argon","diag_argon","30","50",$_POST["diag_argon"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Laser Glaukoma</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_glaukoma","diag_glaukoma","30","50",$_POST["diag_glaukoma"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Humprey</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_humpre","diag_humpre","30","50",$_POST["diag_humpre"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Non Contact Biometri</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_nc_biometri","diag_nc_biometri","30","50",$_POST["diag_nc_biometri"],"inputField", null,null);?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Non Contact Tonometri</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("diag_nc_tonometri","diag_nc_tonometri","30","50",$_POST["diag_nc_tonometri"],"inputField", null,null);?></td>
          </tr>
   </table>
</fieldset>
</td>
<td style="vertical-align:top">
<fieldset>
   <legend><strong>Status Lokalis</strong></legend>
   <table width="100%" border="1" cellpadding="4" cellspacing="1">
      <tr>
          <td align="center" class="tablecontent-odd"><img src="<?php echo $APLICATION_ROOT;?>images/mata-1.gif" style="border:none"></td>
      </tr>
      <tr>
          <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextarea("rawat_lokalis","rawat_lokalis","5","50",$_POST["rawat_lokalis"],"inputField", null,false);?></td>
      </tr>
   </table>
</fieldset>
<fieldset>
    <legend><strong>Diagnosa Keperawatan</strong></legend>
    <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr>
          <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextarea("rawat_diagnosa_keperawatan","rawat_diagnosa_keperawatan","5","50",$_POST["rawat_diagnosa_keperawatan"],"inputField", null,false);?></td>
        </tr>
    </table>
</fieldset>
<fieldset>
    <legend><strong>Observasi</strong></legend>
    <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr>
          <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextarea("rawat_observasi","rawat_observasi","5","50",$_POST["rawat_observasi"],"inputField", null,false);?></td>
        </tr>
    </table>
</fieldset>
</td>
</tr>

<script>
    Calendar.setup({
        inputField     :    "perawat_tanggal_kontrol",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
</form>
<?php }?>