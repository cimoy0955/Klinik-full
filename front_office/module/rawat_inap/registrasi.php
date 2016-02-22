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

 	/*if(!$auth->IsAllowed("rawat_inap",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("rawat_inap",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }*/

     $_x_mode = "New";
     $thisPage = "registrasi.php";
     $backPage = "refraksi_view.php?";
     //$dokterPage = "ref_dokter_find.php?";
     //$susterPage = "ref_suster_find.php?";

     $tableRawatinap = new InoTable("table1","99%","center");

     if(!$_POST["rawatinap_tanggal_masuk"]) $_POST["rawatinap_tanggal_masuk"] = date("d-m-Y");

     $plx = new InoLiveX("GetRawatinap,SetRawatinap,SetCmbKamar,SetCmbBed,CancelRawatinap,BackRawatinap");     

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
     
     $sql = "select * from klinik_kamar where id_kategori=".QuoteValue(DPE_CHAR,$id_kategori);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     //echo $sql;
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
     
     
     function GetRawatinap($status) {
          global $dtaccess, $view, $tableRawatinap, $thisPage, $APLICATION_ROOT; 
               
          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, a.reg_jadwal, z.fol_lunas 
				from klinik.klinik_registrasi a
				left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
				left join (
					select distinct fol_lunas, id_reg from klinik.klinik_folio
					where fol_lunas = 'n' and fol_jenis = '".STATUS_REGISTRASI."' 
				) z on a.reg_id = z.id_reg 
                    where a.reg_status like '".STATUS_RAWATINAP.$status."' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);
//return $sql;	
          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "35%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;
          
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
	       if($status==0) {
		       /*if(!$dataTable[$i]["fol_lunas"])*/ $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesRawatinap(\''.$dataTable[$i]["reg_id"].'\')"/>';
	       } else {
		       $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?id_reg='.$dataTable[$i]["reg_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
	       }
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

	       if($status==0) {
		       /*if(!$dataTable[$i]["fol_lunas"])*/ $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" style="cursor:pointer" alt="Batal" title="Batal" border="0" onClick="BatalRawatinap(\''.$dataTable[$i]["reg_id"].'\')"/>';
	       } else {
		       $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" style="cursor:pointer" alt="Batal" title="Batal" border="0" onClick="UndoRawatinap(\''.$dataTable[$i]["reg_id"].'\')"/>';               
	       }
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

          return $tableRawatinap->RenderView($tbHeader,$tbContent,$tbBottom);
	   }	
	

     function SetRawatinap($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_RAWATINAP.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
	
     function CancelRawatinap($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_REGISTRASI.STATUS_ANTRI."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
	
     function BackRawatinap($id) {
		global $dtaccess;
		
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_RAWATINAP.STATUS_ANTRI."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
		$dtaccess->Execute($sql);
		
		return true;
	}
	
	if($_GET["id_reg"] ) {
		$sql = "select cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin, 
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, a.reg_jenis_pasien 
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

          $dbTable = "klinik_rawatinap";
          $dbField[0] = "rawatinap_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_kategori_kamar";
          $dbField[3] = "id_kamar";
          $dbField[4] = "id_bed";
          $dbField[5] = "rawatinap_tanggal_masuk";
          $dbField[6] = "rawatinap_waktu_masuk";
          $dbField[7] = "rawatinap_jenis_pasien";
	  $dbField[8] = "rawatinap_diet";
          
          if(!$_POST["rawatinap_id"]) $rawatinap_id = $dtaccess->GetTransID("klinik_rawatinap","rawatinap_id",DB_SCHEMA_KLINIK);
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$rawatinap_id);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_kategori_kamar"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_bed"]);
          $dbValue[5] = QuoteValue(DPE_DATE,date_db($_POST["rawatinap_tanggal_masuk"]));
          $dbValue[6] = QuoteValue(DPE_DATE,date("H:i:s"));
          $dbValue[7] = QuoteValue(DPE_CHARKEY,$_POST["cust_usr_jenis"]);
          $dbValue[8] = QuoteValue(DPE_CHARKEY,$_POST["reg_inap_diet"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
          
          if ($_POST["btnSave"]) {
              $dtmodel->Insert() or die("insert  error");	
          } else if ($_POST["btnUpdate"]) {
              $dtmodel->Update() or die("update  error");	
          }	
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          $sqlUpdate = "update klinik_kamar_bed set bed_reserved='y' where bed_id = ".QuoteValue(DPE_CHAR,$_POST["id_bed"]);
          $dtaccess->Execute($sqlUpdate,DB_SCHEMA_KLINIK);
          
          $sqlUpdate = "update klinik_registrasi set reg_status=".QuoteValue(DPE_CHAR,STATUS_RAWATINAP.STATUS_MENGINAP).", reg_tipe_rawat=".QuoteValue(DPE_CHAR,RAWAT_INAP)." where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dtaccess->Execute($sqlUpdate,DB_SCHEMA_KLINIK);
	  
          /*
          if($_POST["btnSave"]) echo "<script>document.location.href='".$thisPage."';</script>";
          else echo "<script>document.location.href='".$backPage."&id_cust_usr=".$enc->Encode($_POST["id_cust_usr"])."';</script>";
          exit();
          */
	  $_x_mode = "Save";
	}
   
     //-- untuk option kategori --//
     $sql = "select biaya_nama, biaya_jenis from klinik_biaya where biaya_jenis like '%K%'";
     
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $i=0;
     $opt_kategori[0] = $view->RenderOption("--","[pilih kelas kamar]",$show);
     while($data_kategori = $dtaccess->Fetch($rs)){
        unset($show); $i++;
        if($data_kategori["biaya_jenis"]==$_POST["id_kategori_kamar"]) $show="selected";
        $opt_kategori[] = $view->RenderOption($data_kategori["biaya_jenis"],$data_kategori["biaya_nama"],$show);
     }
     
     $opt_kamar[0] = $view->RenderOption("--","[pilih kamar]",$show);
     
     $opt_bed[0] = $view->RenderOption("--","[pilih bed]",$show);
     //if($_POST["id_kamar"] && $_POST["id_kamar"]!="--"){
        
     //}
     
     //$opt_diet[0] = $view->RenderOption("--","[pilih jenis diet]",$show);
     foreach($dietPasien as $key => $value){
	    $opt_diet[] = $view->RenderOption($key,$value,$show);
     }
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitDom(); ?>
<?php echo $view->InitThickBox(); ?>

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

function BatalRawatinap(id) {
	CancelRawatinap(id,'type=r');
	timer();
}

function UndoRawatinap(id) {
	BackRawatinap(id,'type=r');
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

var _wnd_stat;
function BukaStatWindow(url,judul)
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

var _wnd_id;
function BukaIdWindow(url,judul)
{
    if(!_wnd_id) {
			_wnd_id = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=610,height=600,left=100,top=100');
	} else {
		if (_wnd_id.closed) {
			_wnd_id = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=610,height=600,left=100,top=100');
		} else {
			_wnd_id.focus();
		}
	}
     return false;
}

<?php if($_x_mode=="Save"){ ?>
BukaStatWindow('cetakstatus.php?id=<?php echo $_POST["id_cust_usr"];?>','Kartu Status');
BukaIdWindow('cetak_identitas_pasien.php?id=<?php echo $_POST["id_cust_usr"];?>','Kartu Identitas Pasien');
if(confirm('Cetak Barcode Pasien?')) BukaWindow('pasien_print_single.php?idpas=<?php echo $_POST["id_cust_usr"];?>','Barcode Pasien');
document.location.href='<?php echo $thisPage;?>';
<?php } ?>


</script>

<?php if(!$_GET["id"]) { ?>

<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Registrasi Rawat Inap</div>
		<div id="antri_kiri_isi" style="height:150;overflow:auto"><?php echo GetRawatinap(0); ?></div>
	</div>
	
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Registrasi Rawat Inap</div>
		<div id="antri_kanan_isi" style="height:150;overflow:auto"><?php echo GetRawatinap(1); ?></div>
	</div>
</div>

<?php } ?>


<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Rawatinap</td>
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
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Registrasi Rawat Inap</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr>
	  <td class="tablecontent" width="20%">&nbsp;Tanggal Masuk</td>
	  <td class="tablecontent-odd">
	       <?php echo $view->RenderTextBox("rawatinap_tanggal_masuk","rawatinap_tanggal_masuk","25","25",$_POST["rawatinap_tanggal_masuk"],"inputfield",null,false); ?>
	       <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_masuk" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
	  </td>
	</tr>
        <tr>
          <td class="tablecontent"  width="20%">&nbsp;Kelas</td>
          <td class="tablecontent-odd"><?php echo $view->RenderComboBox("id_kategori_kamar","id_kategori_kamar",$opt_kategori,"inputfield",null,"onChange=\"CariKamar(this.options[this.selectedIndex].value);\""); ?>
        </tr>
        <tr >
          <td class="tablecontent">&nbsp;Kamar</td>
          <td class="tablecontent-odd">
            <div id="div_kamar">
              <?php echo $view->RenderComboBox("id_kamar","id_kamar",$opt_kamar,"inputfield",null,"onChange=\"CariBed(this.options[this.selectedIndex].value);\""); ?>
            </div>
          </td>
        </tr>
	      <tr>
          <td class="tablecontent">&nbsp;Bed</td>
          <td class="tablecontent-odd">
            <div id="div_bed">
              <?php echo $view->RenderComboBox("id_bed","id_bed",$opt_bed,"inputfield",null,null); ?>
            </div>
          </td>
        </tr>
        <tr>
          <td class="tablecontent">&nbsp;Jenis Pasien</td>
          <td class="tablecontent-odd">
            <select name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);">
                    <option value="" >[ pilih jenis pasien ]</option>
                    <?php foreach($bayarPasien as $key => $value) { ?>
                         <option value="<?php echo $key;?>" <?php if($_POST["cust_usr_jenis"]==$key) echo "selected";?>><?php echo $value;?></option>
                    <?php } ?>
			      </select>
          </td>
        </tr>
	  <tr>
	    <td class="tablecontent">&nbsp;Jenis Diet</td>
	    <td class="tablecontent-odd">
		   <?php echo $view->RenderComboBox("reg_inap_diet","reg_inap_diet",$opt_diet,"inputField",null,null);?>
	    </td>
	  </tr>
	   </table>
     </fieldset>
     
     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="left"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>	
</table>

<?php echo $view->SetFocus("id_kategori_kamar");?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="ref_tanggal" value="<?php echo $_POST["ref_tanggal"];?>"/>
<input type="hidden" name="ref_id" value="<?php echo $_POST["ref_id"];?>"/>
<?php echo $view->RenderHidden("hid_suster_del","hid_suster_del",'');?>

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
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "rawatinap_tanggal_masuk",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_masuk",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
