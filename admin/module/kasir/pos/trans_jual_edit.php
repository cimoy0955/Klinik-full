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
     $enc = new textEncrypt();
     $err_code = 0;
     $auth = new CAuth();
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $skr = date("Y-m-d");
     $usrId = $auth->GetUserId();

  	//$userData = $auth->GetUserData();
  	$thisPage = "trans_jual_edit.php";
  	$findPage1 = "item_find2.php?";
  	$findPage2 = "item_find3.php?";
  	$pasienFind = "pasien_find.php?";
	  
    if(!$_POST["faktur_tanggal"]) $_POST["faktur_tanggal"] = format_date($skr);
	  
	$table = new InoTable("table","100%","left");
     
     //$shutdownMode=0;

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    
  
     
	if($auth->IsAllowed()===1){
	    header("Location:".$APLICATION_ROOT."login.php");
	    exit();
	}
	//ambil data Outlet
  /*   $sql = "select b.* from global.global_auth_user a 
             left join global.global_departemen b on a.id_dep=b.dep_id where usr_id =".$usrId;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataOutlet = $dtaccess->Fetch($rs);
     $_POST["outlet"] = $dataOutlet["dep_id"];
     $_POST["outlet_stok"] = $dataOutlet["id_stok"];
     $outlet = $_POST["outlet"];
     */
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
	
  if ($_GET["transaksi"]) 
  { 
   $_x_mode = "Edit";
   $penjualanId = $enc->Decode($_GET["transaksi"]);
  }  
  
  if($_POST["penjualan_id"]) $penjualanId = $_POST["penjualan_id"];

     if ($shutdownMode==0) 
     {
       $judulForm = "Pembuatan Resep";
       $judulHeader = "Mini POS";
     }
     
     //ambil data penjualan baru
    if($_GET["penjualan_id"])  {
    $penjualanId = $_GET["penjualan_id"];
    $penjualan_edit=1;
    } 
    else if($_POST["penjualan_id"]) {
      $penjualanId = $_POST["penjualan_id"]; 
    } 
    else { 
    unset($penjualanId);
    }
     
    if($_POST["penjualan_edit"]) $penjualan_edit=$_POST["penjualan_edit"];
      
    //-- nomor nota otomatis --//  
    if(!$penjualanId){
    $urut = $dtaccess->GetNewID("apotik_penjualan","penjualan_urut",DB_SCHEMA_APOTIK);
    $tgl = explode("-",$skr);
    
    $_POST["faktur_no"] = str_pad($urut,5,"0",STR_PAD_LEFT)."/".$tgl[1]."/".$tgl[2]."/".$tgl[0];
    $_POST["hidUrut"] = $urut;
  }

	$plx = new InoLiveX("CheckData,CariItem");
  
  function CariItem($kodeitem) {
          global $dtaccess;
          
          $sql = "select obat_nama, obat_id
                    from apotik_obat_master a
                    where upper(a.obat_kode) = ".QuoteValue(DPE_CHAR,strtoupper($kodeitem));
                    
          $rs = $dtaccess->Execute($sql);
          $dataitem = $dtaccess->Fetch($rs);
          
          return $dataitem["obat_id"]."~~".$dataitem["obat_nama"];
     }
     
  function CheckData($loginNama,$memberId=null)
   	 {
          global $dtaccess;        
          
          $sql = "SELECT * FROM global.global_auth_user a 
                    WHERE upper(a.usr_loginname) = ".QuoteValue(DPE_CHAR,strtoupper($loginNama));
          $rs = $dtaccess->Execute($sql);
          $dataAdaLogin = $dtaccess->Fetch($rs);
		      return $dataAdaLogin["usr_loginname"];
		      
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
     
     
         //Jika Melakukan Pembayaran
     if ($_POST["btnBayar"]) {
     
      //Rubah Status Kuitansi Sudah Dibayar 
      $dbTable = "apotik_penjualan";
      $dbField[0]  = "penjualan_id";   // PK
      $dbField[1]  = "penjualan_create";
      $dbField[2]  = "penjualan_nomor";
      $dbField[3]  = "penjualan_total";     
      $dbField[4]  = "penjualan_terbayar";
      $dbField[5]  = "who_update";
      $dbField[6]  = "id_reg";
      $dbField[7]  = "penjualan_urut";
      $dbField[8]  = "cust_usr_nama";
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
      $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["faktur_no"]);
      $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalDibayar"]));  
      $dbValue[4] = QuoteValue(DPE_CHAR,'y');
      $dbValue[5] = QuoteValue(DPE_CHAR,$usrId);
      $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
      $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["hidUrut"]);
      $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["pasien_nama"]);
      
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_APOTIK);

      $dtmodel->Update() or die("update  error");
      	
      unset($dbField);
      unset($dbValue);
      
      //-- menambah stok dan update harga jual --/
      $sql = "select id_item,sum(trans_jumlah) as jumlah_jual from apotik_transaksi
              where id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId)."
              group by id_item ";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
      $dataDetail = $dtaccess->FetchAll($rs);
      
      for($i=0,$j=count($dataDetail);$i<$j;$i++){
          $sql = "select obat_stok from apotik_obat_master
                    where obat_id=".QuoteValue(DPE_CHAR,$dataDetail[$i]["id_item"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
          $stok = $dtaccess->Fetch($rs);
          
          $stok_baru = $stok["obat_stok"] + $dataDetail[$i]["jumlah_jual"];
          
          $dbTable = "apotik_obat_master";
          
          $dbField[0]  = "obat_id";   // PK
          $dbField[1]  = "obat_stok";  
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$dataDetail[$i]["id_item"]);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$stok_baru);  
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_APOTIK);

          $dtmodel->Update() or die("insert  error");	
          unset($dbField);
          unset($dbValue);
         }
      
      //Rubah Status Semua Transaksi
      $sql = "select a.trans_id,a.trans_jumlah
               from apotik_transaksi a 
               where a.id_penjualan=".QuoteValue(DPE_CHAR,$penjualanId);     
        
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
      $dataTransaksi = $dtaccess->FetchAll($rs);

      
      for($i=0,$n=count($dataTransaksi);$i<$n;$i++)
      { 
        $dbTable = "apotik_transaksi";
        $dbField[0]  = "trans_id";   // PK
        $dbField[1]  = "trans_flag";
        
        $dbValue[0] = QuoteValue(DPE_CHAR,$dataTransaksi[$i]["trans_id"]);
        $dbValue[1] = QuoteValue(DPE_CHAR,'y');
    
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_APOTIK);
  
        $dtmodel->Update() or die("insert  error");
        	
        unset($dbField);
        unset($dbValue);
        }
      
      unset($penjualanId);
      if ($_POST["btnBayar"])
      { 
       
       $cetakPage = "penjualan_dealer_cetak.php?penjualan_id=".$penjualanId;   
       header("location:".$thisPage);
      }
      else
      {
       $backPage = "penjualan_dealer2.php";   
       header("location:".$backPage);
      }
      exit();      
     }
     
     //Jika Melakukan penambahan item     
     if ($_POST["btnUpdate"] || $_POST["btnSave_1"] || $_POST["btnSave_2"]) {
          
          if (!$penjualanId) {
          //$sql = "select max(penjualan_kuitansi) as nomer from optik.optik_penjualan";     
          //$rs = $dtaccess->Execute($sql,DB_SCHEMA);
          //$dataNomer = $dtaccess->Fetch($rs);
          //$nomer=$dataNomer["nomer"]+1;
      
          //for($i=0,$n=strlen($nomer);$i<10-$n;$i++) $penjualanNomer=$penjualanNomer."0";
          //   $penjualanNomer = $penjualanNomer.$nomer;
          $dbTable = "apotik_penjualan";
          $dbField[0]  = "penjualan_id";   // PK
          $dbField[1]  = "penjualan_create";
          $dbField[2]  = "penjualan_nomor";
          $dbField[3]  = "penjualan_total";     
          $dbField[4]  = "penjualan_terbayar";
          $dbField[5]  = "who_update";
          
          $penjualanId = $dtaccess->GetTransID();
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["faktur_no"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,StripCurrency($_POST["txtTotalDibayar"]));  
          $dbValue[4] = QuoteValue(DPE_CHAR,'n');;
          $dbValue[5] = QuoteValue(DPE_CHAR,$usrId);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_APOTIK);
          $dtmodel->Insert() or die("insert  error");
          	
          unset($dbField);
          unset($dbValue); 
          
          }
          
          $transaksiId = $dtaccess->GetTransID();
          
          $dbTable = "apotik_transaksi";
          $dbField[0]  = "trans_id";   // PK
          $dbField[1]  = "id_penjualan";
          $dbField[2]  = "id_item";
          $dbField[3]  = "trans_jumlah";
          $dbField[4]  = "trans_harga_jual";
          $dbField[5]  = "trans_create";
          $dbField[6]  = "trans_flag";
          $dbField[7]  = "trans_tipe";
          $dbField[8]  = "trans_petunjuk";
          if($_POST["perintah_jumlah_2"] && $_POST["perintah_nama_2"]){
            $dbField[9]  = "trans_racik_jumlah";
            $dbField[10]  = "trans_racik_perintah";
          }
          
          if($_POST["obat_id_1"]) $obat_id = $_POST["obat_id_1"]; else $obat_id = $_POST["obat_id_2"];
          if($_POST["txtJumlah_1"]) $jumlah = $_POST["txtJumlah_1"]; else $jumlah = $_POST["txtJumlah_2"];
          if($_POST["txtHargaSatuan_1"]) $harga_jual = $_POST["txtHargaSatuan_1"]; else $harga_jual = $_POST["txtHargaSatuan_2"];
          if($_POST["id_petunjuk_1"]) $petunjuknya = $_POST["id_petunjuk_1"]; else $petunjuknya = $_POST["id_petunjuk_2"];
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$obat_id);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($jumlah));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($harga_jual));
          $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_CHAR,'J');
          $dbValue[8] = QuoteValue(DPE_CHAR,$petunjuknya);
          if($_POST["perintah_jumlah_2"] && $_POST["perintah_nama_2"]){
            $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["perintah_jumlah_2"]));
            $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["perintah_nama_2"]);
          }
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_APOTIK);

          $dtmodel->Insert() or die("insert  error");	
          unset($dbField);
          unset($dbValue); 
          
          unset($_POST["btnSave_1"]);
          unset($_POST["btnSave_2"]);
          unset($_POST["obat_nama_1"]);
          unset($_POST["obat_kode_1"]);
          unset($_POST["txtJumlah_1"]);
          unset($_POST["txtHargaSatuan_1"]);
          unset($_POST["txtHargaTotal_1"]);
          unset($_POST["obat_nama_2"]);
          unset($_POST["obat_kode_2"]);
          unset($_POST["txtJumlah_2"]);
          unset($_POST["txtHargaSatuan_2"]);
          unset($_POST["txtHargaTotal_2"]);
          unset($_POST["obat_id_1"]);
          unset($_POST["obat_id_2"]);
          unset($obat_id);
          unset($jumlah);
          unset($harga_jual);
     }
     
     if($_POST["btnDelete"]){
      $transaksiId = & $_POST["cbDelete"];
      for($i=0,$n=count($transaksiId);$i<$n;$i++){
          $sql = "DELETE FROM apotik_transaksi WHERE trans_id = '".$transaksiId[$i]."'";
          $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
      }
      unset($_POST["btnDelete"]);
   }
          
     $sql = "select *,b.obat_nama from apotik_transaksi a
             join apotik_obat_master b on a.id_item=b.obat_id 
             where a.id_penjualan = ".QuoteValue(DPE_CHAR, $penjualanId)."
             order by a.trans_create asc";
             
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
     $dataTable = $dtaccess->FetchAll($rs_edit);
     $tableHeader = "&nbsp;Detail menu penjualan";
     
     $isAllowedDel = $auth->IsAllowed("setup_role",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_role",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_role",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
      $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
      $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
      $counterHeader++;
 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Obat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Harga";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
         $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["trans_id"].'">';               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["obat_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["trans_harga_jual"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["trans_jumlah"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["trans_harga_jual"]*$dataTable[$i]["trans_jumlah"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          $totalHarga+=$dataTable[$i]["trans_harga_jual"]*$dataTable[$i]["trans_jumlah"];
          $colspan = count($tbHeader[0]);
     
     
			$tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
		
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan-1;
      
     $tbBottom[0][1][TABLE_ISI] = currency_format($totalHarga);
     $tbBottom[0][1][TABLE_ALIGN] = "right";
  
   }  
   /*$mejaId = & $_POST["cbDelete"];
        for{
            $sql = "delete from optik.optik_meja where meja_id = '".$mejaId[$i]."'";
            $dtaccess->Execute($sql,DB_SCHEMA);*/
     
      $pajak = $totalHarga * 0.1;
      $grandTotalHarga = $totalHarga + $pajak;

    if($_POST["transaksi_id"])
      {  
       $transaksiId = & $_POST["transaksi_id"];
       $updateData = true;
      } 
      /*     
     $sql = "select * from pulsa.mp_member a join 
             global.global_auth_user b on a.id_usr=b.usr_id
             join pulsa.mp_member_trans c on a.member_id=c.id_member
             join pulsa.mp_meja d on d.meja_id=c.id_meja
             where member_id like '".$userData["id_member"]."'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->Fetch($rs);        
      
     $usrId=$dataTable["usr_id"];  
     $_POST["usr_loginname"]= $dataTable["usr_loginname"];
     $_POST["member_nama"]= $dataTable["member_nama"];
     $_POST["member_id"]= $dataTable["member_id"];
*/

  $sql = "select * from apotik_obat_petunjuk";
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
  $r=0;
  while($data_dosis=$dtaccess->Fetch($rs))
  {
    unset($show);
    $opt_dosis[$r] = $view->RenderOption($data_dosis["petunjuk_id"],$data_dosis["petunjuk_nama"],$show);
    $r++;
  }
?>

<?php echo $view->RenderBody("inosoft.css",true);?>
<?php echo $view->InitThickBox(); ?>
<div onKeyDown="CaptureEvent(event);">

<script language="Javascript">

<? $plx->Run(); ?>

</script>

<script language="Javascript">


function GantiHarga(dari) {
     var jumlah = document.getElementById('txtJumlah_1').value.toString().replace(/\,/g,"")*1;
     var duit = document.getElementById('txtHargaSatuan_1').value.toString().replace(/\,/g,"")*1;
     document.getElementById('txtHargaTotal_1').value = formatCurrency(duit*jumlah);
     if(dari=='txtJumlah_1'){
        document.getElementById('txtHargaSatuan_1').focus();
     }else{
        document.getElementById('btnSave_1').focus();
     }
}

function GantiHarga2(dari2) {
     var jml = document.getElementById('txtJumlah_2').value.toString().replace(/\,/g,"")*1;
     var harga = document.getElementById('txtHargaSatuan_2').value.toString().replace(/\,/g,"")*1;
     document.getElementById('txtHargaTotal_2').value = formatCurrency(harga*jml);
     if(dari2=='txtJumlah_2'){
        document.getElementById('txtHargaSatuan_2').focus();
     }else{
        document.getElementById('btnSave_2').focus();
     }
}

function GantiKembalian(dibayar) {
     var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g,"");
     var totalnya = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g,"");
     var pajak = document.getElementById('txtPPN').value.toString().replace(/\,/g,"");
     dibayar_format = dibayar.toString().replace(/\,/g,"");
     document.getElementById('txtDibayar').value = formatCurrency(dibayar_format);
     dibayar_format_int=dibayar_format*1;
     pajakInt=pajak*1;
     diskonInt=diskon*1;
     totalnyaInt=totalnya*1;
     //document.getElementById('txtKembalian').value = formatCurrency(dibayar_format-(totalnya+(pajak-diskon)));
     document.getElementById('txtKembalian').value = formatCurrency(dibayar_format_int-totalnyaInt);
     document.getElementById('btnBayar').focus();
}

function GantiDiskon(diskon,total) {
     var dibayar = document.getElementById('txtDibayar').value.toString().replace(/\,/g,"");
     var pajak = document.getElementById('txtPPN').value.toString().replace(/\,/g,"");
     diskon_format=diskon.toString().replace(/\,/g,"");
     dibayarInt = dibayar*1;
     pajakInt = pajak*1;
     totalInt = total*1;
     pajakBaruInt = 0.1*(totalInt-diskon_formatInt);
     diskon_formatInt = diskon_format*1;
     document.getElementById('txtDiskon').value = formatCurrency(diskon_format);
     document.getElementById('txtPPN').value = formatCurrency(pajakBaruInt);
     document.getElementById('txtTotalDibayar').value = formatCurrency(totalInt+(pajakInt-diskon_formatInt));
     document.getElementById('txtKembalian').value = formatCurrency(dibayarInt-(totalInt+(pajakInt-diskon_formatInt)));
     document.getElementById('txtDibayar').focus();
}

function Masukkanitem(frm,kode) 
{        
     hasilKode=CariItem(kode,'type=r');
     hasilAkhir=hasilKode.split('~~');
     
     if(!hasilAkhir[0]) {
          document.getElementById('obat_kode').focus();
          alert('Obat dengan kode \''+kode+'\' tidak ditemukan');
          return false;
     }
     
     document.getElementById('obat_id').value=hasilAkhir[0];
     document.getElementById('obat_nama').value=hasilAkhir[1];     
     //document.getElementById('txtHargaSatuan').value=formatCurrency(hasilAkhir[2]);   
     document.getElementById('txtJumlah').value = 1;
     //document.getElementById('txtHargaTotal').value =formatCurrency(hasilAkhir[2])
     document.getElementById('txtJumlah').focus();
}


function CekData()
{
    if(!document.getElementById('txtDibayar').value || document.getElementById('txtDibayar').value =='0')
    {
      alert('Belum dibayar');
      document.getElementById('txtDibayar').focus();
      return false;
    }
    if(!document.getElementById('faktur_no').value) {
         alert('Nomor Faktur harap diisi');
         return false;
    }
    
      if(!document.getElementById('pasien_nama').value) {
         alert('Nama Pasien harap diisi');
         document.getElementById('pasien_nama').focus();
         return false;
    }
    
     if(document.getElementById('txtJumlah').value==0) {
          alert('Jumlah tidak boleh kosong (0)');
          document.getElementById('txtJumlah').focus();
          return false;
     }

     
     if(document.getElementById('txtHargaTotal').value==0) {
          alert('Harga Total tidak boleh kosong (0)');
          return false;
     }
    return true;
}
function CaptureEvent(evt){
     var keyCode = document.layers ? evt.which : document.all ? evt.keyCode : evt.keyCode;     	
     
     if(keyCode==113) {  // -- f2 buat fokus ke tipe transaksi ---
          document.getElementById('txtDiskon').focus();
     }
     return false;
}

function daftar_meja() {     
     var new_win;
     new_win=new_win=window.open('meja_find.php','Meja','status=no,toolbar=no,scrollbars=yes,resizable=no,width=680,height=480');
     new_win.focus();
}

function daftar_pembayaran() {     
     var new_win;
     new_win=new_win=window.open('bayar_find.php','Meja','status=no,toolbar=no,scrollbars=yes,resizable=no,width=680,height=480');
     new_win.focus();
}
</script>
<div onKeyDown="CaptureEvent(event);">
<!-- link css untuk tampilan tab ala winXP -->
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>/library/css/winxp.css" />
<!-- link jscript untuk fungsi-fungsi tab dasar -->
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>/library/script/listener.js"></script> 
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>/library/script/tabs.js"></script>  

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" ><!--onSubmit="return CheckDataSave(this);" --> 
<table width="70%" border="0" cellpadding="1" cellspacing="1">
      <tr class="tableheader">
        <td colspan="4">&nbsp;<?php echo $judulForm; ?></td>
    </tr>
     <tr>
      <td colspan="4">
        <fieldset>
        <legend>Data Faktur</legend>          
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
              <tr>
                    <td align="left" width="30%" class="tablecontent">&nbsp;Nomor Faktur&nbsp;</td>
                    <td align="left" width="70%" class="tablecontent-odd">
                      <?php echo $view->RenderTextBox("faktur_no","faktur_no","30","100",$_POST["faktur_no"],"inputField", "",false);?>
                    </td>      
              </tr>
              <tr>
                    <td align="left" width="30%" class="tablecontent">&nbsp;Nama Pasien&nbsp;</td>
                    <td align="left" width="70%" class="tablecontent-odd">
                      <?php echo $view->RenderTextBox("pasien_nama","pasien_nama","30","100",$_POST["pasien_nama"],"inputField", "",false);?>
                      <!--<a href="<?php //echo $pasienFind;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php //echo $outlet; ?>" class="thickbox" title="Pilih pasien"><img src="<?php //echo $APLICATION_ROOT;?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih obat" alt="Pilih obat" /></a>-->
                      <?php echo $view->RenderHidden("id_reg","id_reg",$_POST["id_reg"]); ?>     
              </tr>
             <!-- <tr>
                    <td align="left" width="30%" class="tablecontent">&nbsp;Umur&nbsp;</td>
                    <td align="left" width="70%" class="tablecontent-odd">
                      <?php //echo $view->RenderTextBox("pasien_umur","pasien_umur","12","100",$_POST["pasien_umur"],"inputField", "",true);?>      
              </tr> -->
            </table>
          </fieldset>
        </td>
     </tr>
     <tr>
      <td colspan="4">
        <div class="tabsystem"> 
          <div class="tabpage">
            <h2>Obat Jadi</h2>
            <fieldset>
                <legend>Obat Jadi</legend>
                <table width="100%" border="0" cellpadding="1" cellspacing="1">
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Obat&nbsp;</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                          <?php echo $view->RenderTextBox("obat_nama_1","obat_nama_1","30","100",$_POST["obat_nama_1"],"inputField", "readonly",false);?>                
                          <input type="hidden" name="obat_id_1" id="obat_id_1" value="<?php echo $_POST["obat_id_1"];?>" />
                          <a href="<?php echo $findPage1;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih obat"><img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih obat" alt="Pilih obat" /></a>
                        </td>
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Jumlah</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtJumlah_1","txtJumlah_1","4","4",$_POST["txtJumlah_1"],"curedit", "",false,'onChange=GantiHarga(this)');?>
                        </td>					
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Dosis</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderComboBox("id_petunjuk_1","id_petunjuk_1",$opt_dosis,"inputfield");?>
                        </td>					
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Harga Jual</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtHargaSatuan_1","txtHargaSatuan_1","10","10",currency_format($_POST["txtHargaSatuan_1"]),"curedit", "readonly",false,'onChange=GantiHarga(this)');?>
                        </td>					
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Total Harga</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtHargaTotal_1","txtHargaTotal_1","10","10",$_POST["txtHargaTotal_1"],"curedit", "readonly",false);?>
                        </td>					
                   </tr>
                   <tr>
                        <td colspan="4" align="left" class="tblCol">
                             <input type="submit" name="btnSave_1" id="btnSave_1" value="Simpan" class="button" />
                             
                        </td>
                   </tr>
                </table>
             </fieldset>
          </div>
          <div class="tabpage">         
           <h2>Obat Racikan</h2>
            <fieldset>
                <legend>Obat Racikan</legend>
                <table width="100%" border="0" cellpadding="1" cellspacing="1">
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Obat&nbsp;</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                          <?php echo $view->RenderTextBox("obat_nama_2","obat_nama_2","30","100",$_POST["obat_nama_2"],"inputField", "readonly",false);?>                
                          <input type="hidden" name="obat_id_2" id="obat_id_2" value="<?php echo $_POST["obat_id_2"];?>" />
                          <a href="<?php echo $findPage2;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih obat"><img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih obat" alt="Pilih obat" /></a>
                        </td>
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Jumlah</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtJumlah_2","txtJumlah_2","4","4",$_POST["txtJumlah_2"],"curedit", "",false,'onChange=GantiHarga2(this)');?>
                        </td>					
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Dosis</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderComboBox("id_petunjuk_2","id_petunjuk_2",$opt_dosis,"inputfield");?>
                        </td>					
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Perintah</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                            <select name="perintah_nama_2" id="perintah_nama_2" onKeyDown="return tabOnEnter(this, event);">
                                <?php foreach($racikan_obat as $key => $value) { ?>
                                     <option value="<?php echo $key;?>" <?php if($_POST["perintah_nama_2"]==$key) echo "selected";?>><?php echo $value;?></option>
                                <?php } ?>
            			      </select>
                        </td>
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Sebanyak</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("perintah_jumlah_2","perintah_jumlah_2","4","4",$_POST["perintah_jumlah"],"curedit", "",true,'onChange=GantiHarga2(this)');?>
                        </td>					
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Harga Jual</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtHargaSatuan_2","txtHargaSatuan_2","10","10",currency_format($_POST["txtHargaSatuan_2"]),"curedit", "readonly",false,'onChange=GantiHarga(this)');?>
                        </td>					
                   </tr>
                   <tr>
                        <td align="left" width="30%" class="tablecontent">&nbsp;Total Harga</td>
                        <td align="left" width="70%" class="tablecontent-odd">
                             <?php echo $view->RenderTextBox("txtHargaTotal_2","txtHargaTotal_2","10","10",$_POST["txtHargaTotal_2"],"curedit", "readonly",false);?>
                        </td>					
                   </tr>
                   <tr>
                        <td colspan="4" align="left" class="tblCol">
                             <input type="submit" name="btnSave_2" id="btnSave_2" value="Simpan" class="button" />
                        </td>
                   </tr>
               </table>              
             </fieldset>
          </div>
        </div>
        </td>
     </tr>
         <tr>
            <td colspan="4" class="tablecontent">Tekan tombol F2 untuk membayar</td>
         </tr>
     <tr> 
        <td colspan="4">
					<span id="div_menu">
						<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
					</span>
				</td>
		 </tr>
     <tr>
        <td colspan="4">
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Diskon</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("txtDiskon","txtDiskon","30","30",$_POST["txtDiskon"],"curedit", "",true,'onChange=GantiDiskon(this.value,'.$totalHarga.')');?>
                   
                    </td>		
                    <td>&nbsp; &nbsp; &nbsp;</td>			
                    
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;PPN</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                       <?php echo $view->RenderTextBox("txtPPN","txtPPN","30","30",currency_format($pajak),"curedit", "readonly",null,false);?>
                    </td>
                    <td>&nbsp;</td>				
               </tr>               
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Total yg harus dibayar</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                       <input type="hidden" name="txtTotalHarga" id="txtTotalHarga" value="<? echo $totalHarga?>" />
                       <?php echo $view->RenderTextBox("txtTotalDibayar","txtTotalDibayar","30","30",currency_format($grandTotalHarga),"curedit", "readonly",null,false);?>
                    </td>					
               <td>&nbsp;</td>
        	  </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Dibayar</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                       <?php echo $view->RenderTextBox("txtDibayar","txtDibayar","30","30",$_POST["txtDibayar"],"curedit", "",true,'onChange=GantiKembalian(this.value)');?>
                    </td>					
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Kembalian</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("txtKembalian","txtKembalian","30","30",$_POST["txtHargaTotal"],"curedit", "readonly",null,true);?>
                    </td>					
               </tr>
                         
               <tr>
                    <td colspan="4" align="left" class="tblCol">
                        <!-- <input type="submit" name="btnPesan" value="Pesan" class="button" />-->
                         <input type="submit" name="btnBayar" id="btnBayar" value="Bayar" class="button" onClick="javascript:return CekData();"/>
                         <input type="button" name="btnBack2" value="Kembali" class="button" onClick="document.location.href='<?php echo $backPage?>'">
                    </td>
               </tr>
          </table>
         
          </td>
     </tr>
</table>
<input type="hidden" name="id_meja" value="<?php echo $_POST["id_meja"]; ?>" />
<input type="hidden" name="pgw_cuti_id" value="<?php echo $pgwCutiId?>" />
<input type="hidden" name="penjualan_id" value="<?php echo $penjualanId;?>" />
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />

<input type="hidden" name="penjualan_edit" value="<?php echo $penjualan_edit;?>"/>
<input type="hidden" name="transaksi_id" id=="transaksi_id"/>
<input type="hidden" name="penjualan_id" value="<?php echo $penjualanId;?>" />
<input type="hidden" name="meja_nama" value="<?php echo $dataMeja["meja_nama"];?>" />
<input type="hidden" name="menu_harga_jual" value="<?php echo $datamenu["menu_harga_jual"];?>" />
<input type="hidden" name="member_id" value="<?php echo $_POST["member_id"];?>" />
<input type="hidden" name="jbayar_nama" value="<?php echo $dataMeja["jbayar_nama"];?>" />
<input type="hidden" name="awal" value="1" />
<input type="hidden" name="hidUrut" value="<? echo $_POST["hidUrut"]; ?>">

</form>
</div>
<script>document.frmEdit.faktur_no.focus();</script>
</body>
<?php echo $view->RenderBodyEnd(); ?>