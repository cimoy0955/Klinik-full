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
     
     if(!$_POST["mutasi_tanggal"]) $_POST["mutasi_tanggal"] = getDateToday();
     if(!$_POST["pukul_jam"]) $_POST["pukul_jam"] = date('H');
     if(!$_POST["pukul_menit"]) $_POST["pukul_menit"] = date('i');
     
     if(!$auth->IsAllowed("rawat_inap",PRIV_CREATE)){
              die("access_denied");
              exit(1);
         } else if($auth->IsAllowed("rawat_inap",PRIV_CREATE)===1){
              echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
              exit(1);
         }
         
     $tableRefraksi = new InoTable("table1","99%","center");
     
	$dokterPage = "rawat_dokter_find.php?";

     $plx = new InoLiveX("SetCmbKamar,SetCmbBed,GetPerawatan");       
     
      function SetCmbKamar($id_kategori){
          global $dtaccess, $view;
          
          $sql = "select * from klinik_kamar where id_kategori=".QuoteValue(DPE_CHAR,$id_kategori);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $i=0; unset($opt_kamar);
          $opt_kamar[0] = $view->RenderOption("--","[pilih kamar]",$show);
          while($data_kamar = $dtaccess->Fetch($rs)){
            unset($show); $i++;
            if($data_kamar["kamar_id"]==$_POST["id_kamar"]) $show="selected";
            $opt_kamar[$i] = $view->RenderOption($data_kamar["kamar_id"],$data_kamar["kamar_nama"],$show);
          }
          $str = $view->RenderComboBox("id_kamar","id_kamar",$opt_kamar,"inputfield",null,"onChange=\"CariBed(this.options[this.selectedIndex].value);\"");
          
          return $str;
     }
     
     function SetCmbBed($id_kamar){
          global $dtaccess, $view;
          
          $sql = "select * from klinik_kamar_bed where bed_reserved='n' and id_kamar=".QuoteValue(DPE_CHAR,$id_kamar);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $i=0; unset($opt_bed);
          
          $opt_bed[0] = $view->RenderOption("--","[pilih bed]",$show);
          while($data_bed = $dtaccess->Fetch($rs)){
            unset($show); $i++;
            if($data_bed["bed_id"]==$_POST["id_bed"]) $show="selected";
            $opt_bed[$i] = $view->RenderOption($data_bed["bed_id"],$data_bed["bed_kode"],$show);
          }
     
          $str = $view->RenderComboBox("id_bed","id_bed",$opt_bed,"inputfield",null,null);
          
          return $str;
     }
     
     function GetPerawatan() {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT; 
          
          $sql = "select cust_usr_nama,a.reg_id,a.reg_status,a.reg_waktu,a.reg_jadwal, 
                  c.rawatinap_tanggal_masuk,c.rawatinap_id,i.pgw_nama,d.kategori_nama,e.kamar_kode,f.bed_kode
                  from klinik_registrasi a
                  left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            			left join klinik_rawatinap c on c.id_reg = a.reg_id
            			left join klinik_kamar_kategori d on d.kategori_id = c.id_kategori_kamar
            			left join klinik_kamar e on e.kamar_id = c.id_kamar and e.id_kategori = c.id_kategori_kamar
            			left join klinik_kamar_bed f on f.bed_id = c.id_bed and f.id_kamar = c.id_kamar
                  left join klinik.klinik_perawatan g on g.id_reg = a.reg_id
                  left join klinik.klinik_perawatan_dokter h on h.id_rawat = g.rawat_id
                  left join hris.hris_pegawai i on i.pgw_id = h.id_pgw  
                  where a.reg_status like '".STATUS_RAWATINAP.STATUS_MENGINAP."' 
                  order by reg_status desc, kategori_nama, kamar_kode, rawatinap_tanggal_masuk asc, 
                  rawatinap_waktu_masuk asc";
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

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
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
          
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pgw_nama"];
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
    	$sql = "select a.reg_id,cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, 
                      ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan, c.id_reg 
                      from klinik.klinik_registrasi a
    			            left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                      where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
            $dataPasien= $dtaccess->Fetch($sql);
            //echo $sql;
          	$_POST["id_reg"] = $dataPasien["reg_id"]; 
          	$_POST["id_rawatinap"] = $_GET["id_rawatinap"];
          	$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
            $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
          	$_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
          	
          
          $sql = "select cust_usr_nama,cust_usr_kode,a.reg_id,a.reg_status,a.reg_waktu,a.reg_jadwal, 
                  c.rawatinap_tanggal_masuk,c.rawatinap_id,d.rawat_id,e.id_pgw,f.pgw_nama
                  from klinik.klinik_registrasi a 
                  left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                  left join klinik.klinik_rawatinap c on c.id_reg = a.reg_id
                  left join klinik.klinik_perawatan d on d.id_reg = a.reg_id
                  left join klinik.klinik_perawatan_dokter e on e.id_rawat = d.rawat_id
                  left join hris.hris_pegawai f on f.pgw_id = e.id_pgw   
                  where a.reg_status like '".STATUS_RAWATINAP.STATUS_MENGINAP."' 
                  and a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." 
                  order by reg_status desc, rawatinap_tanggal_masuk asc, 
                  rawatinap_waktu_masuk asc";
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $dataKamar = $dtaccess->Fetch($rs);
          //echo $sql;
            $_POST["id_rawat"] = $dataKamar["rawat_id"];
            $_POST["pgw"] = $dataKamar["id_pgw"];  
          	$_POST["pgw_nama"] = $dataKamar["pgw_nama"]; 
          	//echo $dataKamar["id_pgw"];
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
  
 //echo $_POST["id_cust_usr"]; 
          
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
     
               
          $dbTable = "klinik.klinik_perawatan_dokter";
          $dbField[0] = "id_rawat";   // PK
          $dbField[1] = "id_pgw";
          
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["id_rawat"]);   // PK
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["id_dokter"]);

          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          $dtmodel->Update() or die("update  error");
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          
          $dbTable = "klinik.klinik_mutasi_dokter";
          $dbField[0] = "mutasi_dokter_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_rawat";
          $dbField[3] = "id_pgw_sebelum";
          $dbField[4] = "id_pgw_sesudah";
          $dbField[5] = "mutasi_tanggal";
          $dbField[6] = "mutasi_keterangan";
          
          $mutasi = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$mutasi);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_rawat"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["pgw"]);
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["id_dokter"]);
          $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["mutasi_tanggal"]));
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["mutasi_keterangan"]);

          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
         $dtmodel->Insert() or die("insert  error");	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
     } 
     
     //-- untuk option kategori --//
     $sql = "select * from klinik_kamar_kategori";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $i=0;
     $opt_kategori[0] = $view->RenderOption("--","[pilih kelas kamar]",$show);
     while($data_kategori = $dtaccess->Fetch($rs)){
        unset($show); $i++;
        if($data_kategori["kategori_id"]==$_POST["id_kategori_kamar"]) $show="selected";
        $opt_kategori[] = $view->RenderOption($data_kategori["kategori_id"],$data_kategori["kategori_nama"],$show);
     }
     
     $opt_kamar[0] = $view->RenderOption("--","[pilih kamar]",$show);
     
     $opt_bed[0] = $view->RenderOption("--","[pilih bed]",$show);
     if($_POST["id_kamar"] && $_POST["id_kamar"]!="--"){
        
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
     GetRawatinap(0,'target=antri_kiri_isi');     
     GetRawatinap(1,'target=antri_kanan_isi');     
     mTimer = setTimeout("timer()", 10000);
}

function ProsesRawatinap(id) {
	SetRawatinap(id,'type=r');
	timer();
}

timer();


function ChangeDisplay(id) {
     var disp = Array();
     
     disp['none'] = 'block';
     disp['block'] = 'none';
     
     document.getElementById(id).style.display = disp[document.getElementById(id).style.display];
}

function CariKamar(id_kat)
{
  document.getElementById('div_kamar').innerHTML = SetCmbKamar(id_kat,'type=r');
  document.getElementById('id_kamar').focus();
}

function CariBed(id_kamar)
{
  document.getElementById('div_bed').innerHTML = SetCmbBed(id_kamar,'type=r');
  document.getElementById('id_bed').focus();
}
</script>

<?php if(!$_GET["id"]) { ?>
	<div id="antri_kanan" style="width:100%;">
		<div class="tableheader">Proses Mutasi Dokter</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetPerawatan(); ?></div>
	</div>
<?php } ?>
<?php if($_GET["id_reg"] && $_GET["id_rawatinap"]) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Mutasi Dokter</td>
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
               <td width= "30%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Dokter Sebelumnya</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $_POST["pgw_nama"]; ?></label></td>
          </tr>
            <tr>
               <td width= "30%" align="left" class="tablecontent">Tanggal Mutasi</td>
               <td width= "70%" align="left" class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("mutasi_tanggal","mutasi_tanggal","15","15",format_date($_POST["mutasi_tanggal"]),"inputfield"); ?>
	                <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               </td>
          </tr>
          <tr>
               <td width="20%"  class="tablecontent" align="left">Mutasi Ke Dokter</td>
               <td align="left" class="tablecontent-odd" width="80%"> 
                    <?php echo $view->RenderTextBox("rawat_dokter_nama","rawat_dokter_nama","30","100",$_POST["rawat_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Alasan</td>
               <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextArea("mutasi_keterangan","mutasi_keterangan","7","100",$_POST["mutasi_keterangan"],"inputField", null,false);?></td>
          </tr>
	  <tr>
          <td colspan="3" align="left" class="tablecontent-odd">
               <input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" id="btnSave" value="Simpan" class="button"/>
          </td>
    </tr>
  	</table>
  </fieldset>
</td>
</tr>
<?php echo $view->RenderHidden("id_reg","id_reg",$_POST["id_reg"]);?>
<?php echo $view->RenderHidden("id_rawatinap","id_rawatinap",$_POST["id_rawatinap"]);?>
<?php echo $view->RenderHidden("id_rawat","id_rawat",$_POST["id_rawat"]);?>
<?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["id_cust_usr"]);?>
<?php echo $view->RenderHidden("pgw","pgw",$_POST["pgw"]);?>
<script>
    Calendar.setup({
        inputField     :    "mutasi_tanggal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
</form>
<?php }?>