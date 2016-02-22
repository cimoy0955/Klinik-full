<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");    
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $dtaccess = new DataAccess();
     $wifi_dtaccess = new Wifi_DataAccess();
     $enc = new textEncrypt();
     $err_code = 0;
     $auth = new CAuth();
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $skr = date("Y-m-d");

	$userData = $auth->GetUserData();
	$thisPage = "wifi_trans_edit.php";
	$viewPage = "wifi_trans_view.php";
	$findPage = "member_find.php?";
     
	$backPage = "wifi_trans_view.php";
     
     //$shutdownMode=0;

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    
  
     
	if($auth->IsAllowed()===1){
	    header("Location:".$APLICATION_ROOT."login.php");
	    exit();
	}
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
	
     
     
     if($_GET["shutdown"]) $shutdownMode = 1;
     else
     $shutdownMode = 0;
     
     
     
     
     if ($shutdownMode==0) 
     {
       $judulForm = "Form Transaksi";
       $judulHeader = "Identitas Member";
     }
     elseif ($shutdownMode==1) 
     {
        //echo "masuk";
        $member_id=($_GET["shutdown"]);
        $judulForm = "Form Shutdown / Reset Password";
        $judulHeader = "Identitas Member";
        $sql = "select * from mp_member where member_id  like '".$member_id."'"; 
	      $rs = $dtaccess->Execute($sql,DB_SCHEMA);
	      $dataMember = $dtaccess->Fetch($rs);
	      $_POST["txtNama"]=$dataMember["member_nama"];
     }

	$sql = "select * from mp_paket where paket_member = 'n' and paket_jenis = 'I' order by paket_id";
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataPaketGuest = $dtaccess->FetchAll($rs);

	
	$sql = "select * from mp_paket where paket_member = 'y' and paket_jenis = 'I' order by paket_id";
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataPaketMember = $dtaccess->FetchAll($rs);
     
     if(!$_POST["rdMemberTipe"]) $_POST["rdMemberTipe"] = "G";
    
     if(!$_POST["txtHargaSatuan"]){
          $_POST["txtHargaSatuan"] = currency_format($dataPaketGuest[0]["paket_harga"]);
          $_POST["txtLama"] = $dataPaketGuest[0]["paket_jumlah_jam_online"];
          if ($dataPaketGuest[0]["paket_type"]==1)
               $_POST["txtHargaTotal"] = currency_format($dataPaketGuest[0]["paket_harga"] * $dataPaketGuest[0]["paket_jumlah_jam_online"]);
          else
               $_POST["txtHargaTotal"] = currency_format($dataPaketGuest[0]["paket_harga"]);
          $_POST["txtMasa"] = $dataPaketGuest[0]["paket_lama"];
          $_POST["txtJamAwal"] = $dataPaketGuest[0]["paket_jam_awal"];
          $_POST["txtJamAkhir"] = $dataPaketGuest[0]["paket_jam_akhir"];
          $_POST["paket_tgl_awal"] = $dataPaketGuest[0]["paket_tgl_awal"];
          $_POST["paket_tgl_akhir"] = $dataPaketGuest[0]["paket_tgl_akhir"];
          $_POST["paket_hari"] = $dataPaketGuest[0]["paket_hari"];
     }

	$plx = new InoLiveX("CreatePaket,CheckData");
  
  function CheckData($loginNama,$memberId=null)
   	 {
          global $dtaccess;        
          
          $sql = "SELECT * FROM global_auth_user a 
                    WHERE upper(a.usr_loginname) = ".QuoteValue(DPE_CHAR,strtoupper($loginNama));
          $rs = $dtaccess->Execute($sql);
          $dataAdaLogin = $dtaccess->Fetch($rs);
		      return $dataAdaLogin["usr_loginname"];
		      
     }
     
      
     function CreatePaket($in_tipe){
          global $view, $dataPaketGuest, $dataPaketMember;

          // --- create options ----
          if($in_tipe=="M") {
               for($i=0,$n=count($dataPaketMember);$i<$n;$i++){ 
                   $options[] = $view->RenderOption($dataPaketMember[$i]["paket_id"],$dataPaketMember[$i]["paket_nama"],$show);
               }
          }elseif($in_tipe=="G") {
               for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ 
                   $options[] = $view->RenderOption($dataPaketGuest[$i]["paket_id"],$dataPaketGuest[$i]["paket_nama"],$show);
               }
          }
          
          $str = $view->RenderComboBox("cmbPaket","cmbPaket",$options,null,null,'onChange="GantiPaket(this.options[this.selectedIndex].value)"');        
          
          return $str;
     }
     
          
     function CreateGUID(){
          srand((double)microtime()*1000000);
          $r = rand ;
          $u = uniqid(getmypid() . $r . (double)microtime()*1000000,1);
          $m = md5 ($u);
          return($m);
     }
     
     function GetUser(){
          $guid = CreateGUID();
          $data["user"] = substr($guid,0,10);
          $data["password"] = substr($guid,11,10);
          return $data;
     }
        

     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
            $_x_mode = "Delete";
          } else { 
            $_x_mode = "Edit";
            $pgwCutiId = $enc->Decode($_GET["id"]);
          }
          $sql = "select a.*,b.cuti_nama, c.pgw_nama 
				from hris_pegawai_cuti a
				join hris_cuti b on a.id_cuti = b.cuti_id
				join hris_pegawai c on a.id_pgw = c.pgw_id 
				where pgw_cuti_id = ".QuoteValue(DPE_CHAR,$pgwCutiId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          $_POST["id_cuti"] = $row_edit["id_cuti"];
          $_POST["id_pgw"] = $row_edit["id_pgw"];
          $_POST["pgw_cuti_keterangan"] = $row_edit["pgw_cuti_keterangan"];
          if(!$_POST["pgw_cuti_tanggal_mulai"]) $_POST["pgw_cuti_tanggal_mulai"] = ($row_edit["pgw_cuti_tanggal_mulai"]) ? format_date($row_edit["pgw_cuti_tanggal_mulai"]) : format_date($row_edit["pgw_cuti_tanggal_mulai_aju"]);
          if(!$_POST["pgw_cuti_tanggal_selesai"]) $_POST["pgw_cuti_tanggal_selesai"] = ($row_edit["pgw_cuti_tanggal_selesai"]) ? format_date($row_edit["pgw_cuti_tanggal_selesai"]) : format_date($row_edit["pgw_cuti_tanggal_selesai_aju"]);
          $_POST["cuti_nama"] = $row_edit["cuti_nama"];
          $_POST["pgw_nama"] = $row_edit["pgw_nama"];
     }


//jika shutdown
if ($_POST["btnReset"]) {

          
               $dbTable = "mp_member";
               
               $dbField[0]  = "member_id";   // PK
               $dbField[1]  = "member_aktif";

               $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["member_id"]);
               $dbValue[1] = QuoteValue(DPE_CHAR,"n");

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               $dtmodel->update() or die("insert  error");	
               
               
               echo "<script>document.location.href='".$backPage."';</script>";
               exit();   
          
          }
          
          
     if ($_POST["btnUpdate"] || $_POST["btnSave"]) {
          if($_POST["rdMemberTipe"]=="G") {
               $guest = GetUser();
               $_POST["member_expire"]=format_date(DateAdd(getdateToday(),$_POST["txtMasa"]));
               
               // --- insert ke auth user dan member ----
               $dbTable = "global_auth_user";
               
               $dbField[0] = "usr_id";   // PK
               $dbField[1] = "usr_loginname";
               $dbField[2] = "usr_name";
               $dbField[3] = "id_rol";
               $dbField[4] = "usr_status";
               $dbField[5] = "usr_when_create";
               $dbField[6] = "usr_password";
               $dbField[7] = "usr_expire";
               
               $usrId = $dtaccess->GetNewID("global_auth_user","usr_id",DB_SCHEMA_GLOBAL);
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$usrId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["txtNama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,"GUEST");
               $dbValue[3] = QuoteValue(DPE_CHAR,ROLE_TIPE_MEMBER);
               $dbValue[4] = QuoteValue(DPE_CHAR,"y");
               $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[6] = QuoteValue(DPE_CHAR,md5(""));
               $dbValue[7] = QuoteValue(DPE_DATE,date_db($_POST["member_expire"]));
      
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
               $dtmodel->Insert() or die("insert  error");	
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          
               
           
               $dbTable = "mp_member";
               $dbField[0]  = "member_id";   // PK
               $dbField[1]  = "member_nama";  
               $dbField[2]  = "member_tipe";
               $dbField[3]  = "id_usr";
               $dbField[4]  = "member_jam_awal";
               $dbField[5]  = "member_jam_akhir";
               $dbField[6]  = "member_expire";
               $dbField[7]  = "member_expire_akhir";
               $dbField[8]  = "member_hari";
               $dbField[9]  = "member_batas";
               
               $memberId = $dtaccess->GetTransID();

               $dbValue[0] = QuoteValue(DPE_CHAR,$memberId);
               $dbValue[1] = QuoteValue(DPE_CHAR,"GUEST");
			         $dbValue[2] = QuoteValue(DPE_CHAR,MEMBER_TIPE_GUEST);
			         $dbValue[3] = QuoteValue(DPE_NUMERICKEY,$usrId);
			         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["txtJamAwal"]);
			         $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["txtJamAkhir"]);
			         $dbValue[6] = QuoteValue(DPE_DATE,$_POST["paket_tgl_awal"]);
			         $dbValue[7] = QuoteValue(DPE_DATE,$_POST["paket_tgl_akhir"]);
			         $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["paket_hari"]);
			         $dbValue[9] = QuoteValue(DPE_DATE,date_db($_POST["member_expire"]));
			         

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               $dtmodel->Insert() or die("insert  error");	
               unset($dbTable);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               $dbTable = "radius.radcheck";
          
                $dbField[0]  = "id";   // PK
                $dbField[1]  = "UserName";  
                $dbField[2]  = "Attribute";
                $dbField[3]  = "op";
                $dbField[4]  = "Value";
              
        
                $idWifi = $wifi_dtaccess->Wifi_GetNewID("radius.radcheck","id",Wifi_DB_SCHEMA); 
                
                $dbValue[0] = QuoteValue(DPE_CHAR,$idWifi);
                $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["txtNama"]);
                $dbValue[2] = QuoteValue(DPE_CHAR,"User-Password");
                $dbValue[3] = QuoteValue(DPE_CHAR,":=");
                $dbValue[4] = QuoteValue(DPE_CHAR,"");
              
      
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
                $dtmodel->Insert() or die("insert  error");	
                unset($dbTable);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);
                
                $detik = $_POST["txtLama"] * 3600;
                
                $dbTable = "radius.radcheck";
          
                $dbField[0]  = "id";   // PK
                $dbField[1]  = "UserName";  
                $dbField[2]  = "Attribute";
                $dbField[3]  = "op";
                $dbField[4]  = "Value";
              
        
                $idWifi = $wifi_dtaccess->Wifi_GetNewID("radius.radcheck","id",Wifi_DB_SCHEMA); 
                
                $dbValue[0] = QuoteValue(DPE_CHAR,$idWifi);
                $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["txtNama"]);
                $dbValue[2] = QuoteValue(DPE_CHAR,"Max-All-Session");
                $dbValue[3] = QuoteValue(DPE_CHAR,":=");
                $dbValue[4] = QuoteValue(DPE_CHAR,$detik);
              
      
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
                $dtmodel->Insert() or die("insert  error");	
                unset($dbTable);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);
                
                $dbTable = "radius.radcheck";
          
                $dbField[0]  = "id";   // PK
                $dbField[1]  = "UserName";  
                $dbField[2]  = "Attribute";
                $dbField[3]  = "op";
                $dbField[4]  = "Value";
              
        
                $idWifi = $wifi_dtaccess->Wifi_GetNewID("radius.radcheck","id",Wifi_DB_SCHEMA); 
                
                $dbValue[0] = QuoteValue(DPE_CHAR,$idWifi);
                $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["txtNama"]);
                $dbValue[2] = QuoteValue(DPE_CHAR,"Simultaneous-Use");
                $dbValue[3] = QuoteValue(DPE_CHAR,":=");
                $dbValue[4] = QuoteValue(DPE_CHAR,"1");
              
      
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
                $dtmodel->Insert() or die("insert  error");	
                unset($dbTable);
                unset($dbField);
                unset($dbValue);
                unset($dbKey);
               
          
          }
          
          if ($_POST["btnReset"]) {
               $dbTable = "mp_member";
               
               $dbField[0]  = "member_id";   // PK
               $dbField[1]  = "member_nama";  
               $dbField[2]  = "member_tipe";
               $dbField[3]  = "id_usr";

               $dbValue[0] = QuoteValue(DPE_CHAR,$memberId);
               $dbValue[1] = QuoteValue(DPE_CHAR,"GUEST");
			         $dbValue[2] = QuoteValue(DPE_CHAR,MEMBER_TIPE_GUEST);
			         $dbValue[3] = QuoteValue(DPE_NUMERICKEY,$usrId);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               $dtmodel->Insert() or die("insert  error");	
               echo "<script>document.location.href='".$backPage."';</script>";
               exit();   
          
          }
               
          if($_POST["id_member"]) $memberId = $_POST["id_member"]; 

          // --- insert transaksi ----
          
          // -- ini buat nyari expire yg blum expire trus digabung ke yg baru
          $sql = "select trans_id, id_meja, trans_time_expire from mp_member_trans 
                    where id_member = ".QuoteValue(DPE_CHAR,$memberId)." and id_dep = ".QuoteValue(DPE_CHAR,APP_OUTLET)." 
                    and trans_time_expire > 0"; 
          $rs = $dtaccess->Execute($sql);
          $dataTransLama = $dtaccess->Fetch($rs); 
          
          $sql = "SELECT usr_name FROM global_auth_user 
                    WHERE usr_id = ".$userData["id"];
          $rs = $dtaccess->Execute($sql);
          $dataUserName = $dtaccess->Fetch($rs);
          
          if($dataTransLama) {
               $sql = "update mp_member_trans set trans_time_expire = 0, id_meja = NULL, trans_time_sisa = ".QuoteValue(DPE_NUMERIC,$dataTransLama["trans_time_expire"]).", trans_time_flag = 'n' where trans_id = ".QuoteValue(DPE_CHAR,$dataTransLama["trans_id"]);
               $dtaccess->Execute($sql);
          }
          
          $dbTable = "mp_member_trans";
          
          $dbField[0]  = "trans_id";   // PK
          $dbField[1]  = "id_member";  
          $dbField[2]  = "id_dep";
          $dbField[3]  = "trans_time_flag";
          $dbField[4]  = "trans_time_expire";
          $dbField[5]  = "trans_time_total";
          $dbField[6]  = "trans_create";
          $dbField[7]  = "trans_time_start";
          $dbField[8]  = "trans_harga_satuan";
          $dbField[9]  = "trans_harga_total";
          $dbField[10]  = "trans_nama";
          $dbField[11]  = "id_petugas";
          $dbField[12]  = "trans_petugas";
          $dbField[13]  = "trans_jenis";
          $dbField[14]  = "id_meja";

          $transId = $dtaccess->GetTransID();
          $detik = $_POST["txtLama"] * 3600;
          $skr = date("Y-m-d H:i:s");
          $transNama = ($_POST["member_nama"]) ? $_POST["member_nama"] : "GUEST";
          
          if ($_POST["paket_tipe"]=="3") $flag="h";else $flag="y";
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$memberId);
          $dbValue[2] = QuoteValue(DPE_CHAR,APP_OUTLET);
          $dbValue[3] = QuoteValue(DPE_CHAR,$flag);
          $dbValue[4] = QuoteValue(DPE_NUMERIC,($detik+$dataTransLama["trans_time_expire"]));
          $dbValue[5] = QuoteValue(DPE_NUMERIC,$detik);
          $dbValue[6] = QuoteValue(DPE_DATE,$skr);
          $dbValue[7] = QuoteValue(DPE_DATE,$skr);
          $dbValue[8] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
          $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotal"]));
          $dbValue[10] = QuoteValue(DPE_CHAR,$transNama);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$userData["id"]);
          $dbValue[12] = QuoteValue(DPE_CHAR,$dataUserName["usr_name"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,'I');
          $dbValue[14] = QuoteValue(DPE_CHAR,($dataTransLama["id_meja"]));

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          
          
          

          echo "<script>document.location.href='".$backPage."';</script>";
          exit();        
     }

?>

<?php echo $view->RenderBody("inventori.css",true); ;?>
<?php echo $view->InitThickBox(); ?>


<script language="Javascript">

<? $plx->Run(); ?>

</script>

<script language="Javascript">

var	dataHarga = Array();
var	dataJamOnline = Array();
var	dataTipe = Array();
var	dataMasa = Array();
var	dataJamAwal = Array();
var	dataJamAkhir = Array();
var	dataTglAwal = Array();
var	dataTglAkhir = Array();
var	dataHari = Array();

<?php for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ ?>
    dataHarga[<?php echo $dataPaketGuest[$i]["paket_id"];?>] = '<?php echo $dataPaketGuest[$i]["paket_harga"];?>'
<?php } ?>
<?php for($i=0,$n=count($dataPaketMember);$i<$n;$i++){ ?>
    dataHarga[<?php echo $dataPaketMember[$i]["paket_id"];?>] = '<?php echo $dataPaketMember[$i]["paket_harga"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ ?>
    dataJamOnline[<?php echo $dataPaketGuest[$i]["paket_id"];?>] = '<?php echo $dataPaketGuest[$i]["paket_jumlah_jam_online"];?>'
<?php } ?>
<?php for($i=0,$n=count($dataPaketMember);$i<$n;$i++){ ?>
    dataJamOnline[<?php echo $dataPaketMember[$i]["paket_id"];?>] = '<?php echo $dataPaketMember[$i]["paket_jumlah_jam_online"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ ?>
    dataTipe[<?php echo $dataPaketGuest[$i]["paket_id"];?>] = '<?php echo $dataPaketGuest[$i]["paket_type"];?>'
<?php } ?>
<?php for($i=0,$n=count($dataPaketMember);$i<$n;$i++){ ?>
    dataTipe[<?php echo $dataPaketMember[$i]["paket_id"];?>] = '<?php echo $dataPaketMember[$i]["paket_type"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ ?>
    dataMasa[<?php echo $dataPaketGuest[$i]["paket_id"];?>] = '<?php echo $dataPaketGuest[$i]["paket_lama"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ ?>
    dataJamAwal[<?php echo $dataPaketGuest[$i]["paket_id"];?>] = '<?php echo $dataPaketGuest[$i]["paket_jam_awal"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ ?>
    dataJamAkhir[<?php echo $dataPaketGuest[$i]["paket_id"];?>] = '<?php echo $dataPaketGuest[$i]["paket_jam_akhir"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ ?>
    dataTglAwal[<?php echo $dataPaketGuest[$i]["paket_id"];?>] = '<?php echo $dataPaketGuest[$i]["paket_tgl_awal"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ ?>
    dataTglAkhir[<?php echo $dataPaketGuest[$i]["paket_id"];?>] = '<?php echo $dataPaketGuest[$i]["paket_tgl_akhir"];?>'
<?php } ?>

<?php for($i=0,$n=count($dataPaketGuest);$i<$n;$i++){ ?>
    dataHari[<?php echo $dataPaketGuest[$i]["paket_id"];?>] = '<?php echo $dataPaketGuest[$i]["paket_hari"];?>'
<?php } ?>


function SetPaket(tipe) {
     document.getElementById('dv_paket').innerHTML = CreatePaket(tipe,'type=r');
     if(tipe=='M') 
     {
       document.getElementById('spMember').style.visibility = 'visible';
       document.getElementById('spMember2').style.visibility = 'hidden';
       document.getElementById('spMember3').style.visibility = 'hidden';
       document.getElementById('spMember4').style.visibility = 'hidden';
       document.getElementById('spNamaGuest').style.visibility = 'hidden';
     }
     else  
     {
        document.getElementById('spMember').style.visibility = 'hidden';
        document.getElementById('spMember2').style.visibility = 'visible';
        document.getElementById('spMember3').style.visibility = 'visible';
        document.getElementById('spMember4').style.visibility = 'visible';
        document.getElementById('spNamaGuest').style.visibility = 'visible';
     }
     
     GantiPaket(document.getElementById('cmbPaket').value);
}



function GantiPaket(id) {
     var jam = dataJamOnline[id];
     var duit = dataHarga[id];
     var tipe = dataTipe[id];
     var masa = dataMasa[id];
     var jamAwal = dataJamAwal[id];
     var jamAkhir = dataJamAkhir[id];
     var tglAwal = dataTglAwal[id];
     var tglAkhir = dataTglAkhir[id];
     var hari = dataHari[id];
     document.getElementById('txtLama').value = jam;
     document.getElementById('txtHargaSatuan').value = formatCurrency(duit);
     document.getElementById('txtMasa').value = masa;
     document.getElementById('txtJamAwal').value = jamAwal;
     document.getElementById('txtJamAkhir').value = jamAkhir;
     document.getElementById('paket_tgl_awal').value = tglAwal;
     document.getElementById('paket_tgl_akhir').value = tglAkhir;
     document.getElementById('paket_tipe').value = tipe;
     document.getElementById('paket_hari').value = hari;
     if(tipe==1) {
          document.getElementById('txtLama').readOnly=false;
          document.getElementById('txtLama').select();
          document.getElementById('txtLama').focus();
          document.getElementById('txtHargaTotal').value = formatCurrency(duit*jam);
     } else if(tipe==2) {
          document.getElementById('txtLama').readOnly=true;
          document.getElementById('txtHargaTotal').value = formatCurrency(duit);
          document.getElementById('txtNama').focus();
     } else if(tipe==3) {
          document.getElementById('txtLama').readOnly=true;
          document.getElementById('txtHargaTotal').value = formatCurrency(duit);
          document.getElementById('txtNama').focus();     
	}
}

function GantiHarga(jam) {
     var duit = document.getElementById('txtHargaSatuan').value.toString().replace(/\,/g,"");
     document.getElementById('txtHargaTotal').value = formatCurrency(duit*jam);
}

function ClearHarga(){
     document.getElementById('txtLama').value = 0;
     document.getElementById('txtHargaSatuan').value = 0;
     document.getElementById('txtHargaTotal').value = 0;
}

function CheckDataSave(frm) {
     
     if(document.getElementById('rdMemberTipe_M').checked) {
          if(!document.getElementById('id_member').value) {
               alert('Member harap dipilih');
               return false;
          }
     }
     
     if(document.getElementById('txtLama').value==0) {
          alert('Waktu bermain tidak boleh kosong (0)');
          return false;
     }

     
     if(document.getElementById('txtHargaTotal').value==0) {
          alert('Harga Total tidak boleh kosong (0)');
          return false;
     }
     
     if (document.getElementById('spNamaGuest').style.visibility == 'visible') {
     if(document.getElementById('txtNama').value==0) {
          alert('Nama Guest tidak boleh kosong');
          return false;
     }
          
     if(CheckData(frm.txtNama.value,frm.member_id.value,'type=r')){
      	alert('Nama Guest Sudah Ada Sudah Ada');
    	 	frm.txtNama.focus();
    		frm.txtNama.select();
    		return false;
    	}
      }	
     return true;

}


</script>


<table width="100%" border="1" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td>&nbsp;<?php echo $judulForm; ?></td>
    </tr>
</table>

<?php if ($shutdownMode==0) { ?>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" onSubmit="return CheckDataSave(this);">
<table width="65%" border="0" cellpadding="0" cellspacing="0">
           <tr>
                <td align="left" width="20%" class="tablecontent">&nbsp;User&nbsp;</td>
                <td align="left" width="80%" class="tablecontent-odd">
                     <?php echo $view->RenderRadio("rdMemberTipe","rdMemberTipe_G","G",null,($_POST["rdMemberTipe"]=="G")?"checked":"", "onClick=\"SetPaket('G');\"");?>
                     <?php echo $view->RenderLabel("lblMemberTipe_G","rdMemberTipe_G","Guest");?>
                     
                     &nbsp;&nbsp;
                     <?php// echo $view->RenderRadio("rdMemberTipe","rdMemberTipe_M","M",null,($_POST["rdMemberTipe"]=="M")?"checked":"", "onClick=\"SetPaket('M');\"");?>
                     <?php// echo $view->RenderLabel("lblMemberTipe_M","rdMemberTipe_M","Member");?>
                     
                     <span id="spMember" style="visibility:hidden">
                          <?php echo $view->RenderTextBox("member_nama","member_nama","30","100",$_POST["member_nama"],"inputField", "readonly",false);?>
                          <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Member"><img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Member" alt="Pilih Member" /></a>
                     </span>
                     <input type="hidden" name="id_member" id="id_member" value="<?php echo $_POST["id_member"];?>" />
                     <input type="hidden" name="paket_hari" id="paket_hari" value="<?php echo $_POST["paket_hari"];?>" />
                     <input type="hidden" name="paket_tgl_awal" id="paket_tgl_awal" value="<?php echo $_POST["paket_tgl_awal"];?>" />
                     <input type="hidden" name="paket_tgl_akhir" id="paket_tgl_akhir" value="<?php echo $_POST["paket_tgl_akhir"];?>" />
                     <input type="hidden" name="paket_tipe" id="paket_tipe" value="<?php echo $_POST["paket_tipe"];?>" />
                </td>
           </tr>
           <tr>
                <td align="left" width="20%" class="tablecontent">&nbsp;Paket&nbsp;</td>
                <td align="left" width="80%" class="tablecontent-odd">
                     <div id="dv_paket"><?php echo CreatePaket($_POST["rdMemberTipe"]);?></div>
                </td>
           </tr>
           <tr>
                <td align="left" width="20%" class="tablecontent">&nbsp;Masa Berlaku</td>
                <td align="left" width="15%" class="tablecontent-odd">
                <span id="spMember2" style="visibility:visible">
                     <?php echo $view->RenderTextBox("txtMasa","txtMasa","3","3",$_POST["txtMasa"],"curedit", "readonly",false);?> hari
                </span>
                </td>					
           </tr>
           <tr>
                <td align="left" width="20%" class="tablecontent">&nbsp;Jam Berlaku Awal</td>
                <td align="left" width="15%" class="tablecontent-odd">
                <span id="spMember3" style="visibility:visible">
                     <?php echo $view->RenderTextBox("txtJamAwal","txtJamAwal","10","10",$_POST["txtJamAwal"],"curedit", "readonly",false);?>                    </span>
                </td>					
           </tr>
           <tr>
                <td align="left" width="20%" class="tablecontent">&nbsp;Jam Berlaku Akhir</td>
                <td align="left" width="15%" class="tablecontent-odd">
                <span id="spMember4" style="visibility:visible">
                     <?php echo $view->RenderTextBox("txtJamAkhir","txtJamAkhir","10","10",$_POST["txtJamAkhir"],"curedit", "readonly",false);?>
                </span>
                </td>					
           </tr>
           
           
           <tr>
                <td align="left" width="20%" class="tablecontent">&nbsp;Nama Guest&nbsp;</td>
                <td align="left" width="80%" class="tablecontent-odd">
                     <span id="spNamaGuest" style="visibility:visible">
                     <?php echo $view->RenderTextBox("txtNama","txtNama","20","20",$_POST["txtNama"],"", "",false,false);?>
                    </span>
                </td>
           </tr>
       
           
           <tr>
                <td align="left" width="20%" class="tablecontent">&nbsp;Lama Bermain</td>
                <td align="left" width="15%" class="tablecontent-odd">
                     <?php echo $view->RenderTextBox("txtLama","txtLama","3","3",$_POST["txtLama"],"curedit", "",false,'onChange=GantiHarga(this.value)');?> Jam
                </td>					
           </tr>
           <tr>
                <td align="left" width="20%" class="tablecontent">&nbsp;Harga per Jam</td>
                <td align="left" width="15%" class="tablecontent-odd">
                     <?php echo $view->RenderTextBox("txtHargaSatuan","txtHargaSatuan","10","10",$_POST["txtHargaSatuan"],"curedit", "readonly",false);?>
                </td>					
           </tr>
           <tr>
                <td align="left" width="20%" class="tablecontent">&nbsp;Total Harga</td>
                <td align="left" width="15%" class="tablecontent-odd">
                     <?php echo $view->RenderTextBox("txtHargaTotal","txtHargaTotal","10","10",$_POST["txtHargaTotal"],"curedit", "readonly",false);?>
                </td>					
           </tr>
           <tr>
                <td colspan="4" align="left" class="tblCol">
                     <input type="submit" name="btnSave" value="Simpan" class="button"/>
                     <input type="button" name="btnBack" value="Kembali" class="button" onClick="document.location.href='<?php echo $backPage?>'">
                </td>
           </tr>     
</table>

<input type="hidden" name="pgw_cuti_id" value="<?php echo $pgwCutiId?>" />
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />
<input type="hidden" name="member_id" value="<?php echo $member_id?>" />
</form>
<script>document.frmEdit.txtNama.focus();</script>
<?php //echo $view->SetFocus("pgw_cuti_tanggal_mulai"); ?>
<?php echo $view->RenderBodyEnd(); ?>
<? } else if ($shutdownMode==1) { ?>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" onSubmit="return CheckDataSave(this);">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Nama Member/Guest&nbsp;</td>
                    <td align="left" width="80%" class="tablecontent-odd">
                         <span id="spNamaGuest" style="visibility:visible">
                         <?php echo $view->RenderTextBox("txtNama","txtNama","20","20",$_POST["txtNama"],"", "",false,false);?>
                        </span>
                    </td>
               </tr>
           
               
               <tr>
                    <td colspan="4" align="left" class="tblCol">
                         <input type="submit" name="btnReset" value="Shutdown" class="button"/>
                         <input type="button" name="btnBack" value="Kembali" class="button" onClick="document.location.href='<?php echo $backPage?>'">
                    </td>
               </tr>
         
</table>

<input type="hidden" name="member_id" value="<?php echo $member_id?>" />
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />
</form>

<?php //echo $view->SetFocus("pgw_cuti_tanggal_mulai"); ?>
<?php echo $view->RenderBodyEnd(); ?>
<? } ?>
