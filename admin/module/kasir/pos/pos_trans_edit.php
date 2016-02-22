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
     $skr = date("Y-m-d H:i:s");
     $usrId = $auth->GetUserId();

	$userData = $auth->GetUserData();
	$thisPage = "pos_trans_edit.php";
	$viewPage = "pos_trans_view.php";
	$findPage = "item_find.php?";
     
	$backPage = "pos_trans_edit.php";
	
	$table = new InoTable("table","100%","left");
     
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
	
  if ($_GET["transaksi"]) 
  { 
   $_x_mode = "Edit";
   $penjualanId = $enc->Decode($_GET["transaksi"]);
  }  
 
  if($_POST["penjualan_id"]) $penjualanId = & $_POST["penjualan_id"];

	   if ($_GET["meja"]) $_POST["meja_nama"]=$_GET["meja"];
     if ($_GET["meja_id"]) $_POST["id_meja"]=$_GET["meja_id"];
    
     
     if ($shutdownMode==0) 
     {
       $judulForm = "Transaksi Penjualan ".$dataMeja["meja_nama"];
       $judulHeader = "POS";
     }
     
     //ambil data penjualan baru
    if($_GET["penjualan_id"])  {
    $penjualanId = & $_GET["penjualan_id"];
    $penjualan_edit=1;
    } 
    else if($_POST["penjualan_id"]) {
      $penjualanId = & $_POST["penjualan_id"]; 
    } 
    //else { 
   // $penjualanId = $dtaccess->GetTransID();
   // }
     
    if($_POST["penjualan_edit"]) $penjualan_edit=$_POST["penjualan_edit"];
      

	$plx = new InoLiveX("CheckData,CariMenu");
  
  function Carimenu($kodeMenu) {
          global $dtaccess,$outlet;
          
          $sql = "select item_nama, item_id, item_harga_jual
                    from pos_item a
                    where upper(a.item_kode) = ".QuoteValue(DPE_CHAR,strtoupper($kodeMenu));
                    
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $datamenu = $dtaccess->Fetch($rs);
          
          return $datamenu["item_id"]."~~".$datamenu["item_nama"]."~~".$datamenu["item_harga_jual"];
     }
     
  function CheckData($loginNama,$memberId=null)
   	 {
          global $dtaccess;        
          
          $sql = "SELECT * FROM global.global_auth_user a 
                    WHERE upper(a.usr_loginname) = ".QuoteValue(DPE_CHAR,strtoupper($loginNama));
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
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
    
     $sql = "SELECT usr_name FROM global_auth_user 
                  WHERE usr_id = ".$userData["id"];
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataUserName = $dtaccess->Fetch($rs);
    
      //Rubah Status Kuitansi Sudah Dibayar 
      $dbTable = "pos_penjualan";
      $dbField[0]  = "penjualan_id";   // PK
      $dbField[1]  = "penjualan_flag";
      $dbField[2]  = "penjualan_create";
      $dbField[3]  = "penjualan_diskon";
      $dbField[4]  = "penjualan_total";
      $dbField[5]  = "penjualan_dibayar";
      $dbField[6]  = "penjualan_kembalian";
      $dbField[7]  = "penjualan_tipe";  
      $dbField[8]  = "penjualan_ppn";
      $dbField[9]  = "id_petugas";
      $dbField[10]  = "penjualan_petugas";
      $dbField[11]  = "id_dep";
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'Y');
      $dbValue[2] = QuoteValue(DPE_DATE,$skr);
      $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"]));  
      $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTotalHarga"]));  
      $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"]));  
      $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtKembalian"]));
      $dbValue[7] = QuoteValue(DPE_CHAR,'T');
      $dbValue[8] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtPPN"]));
      $dbValue[9] = QuoteValue(DPE_NUMERIC,$userData["id"]);
      $dbValue[10] = QuoteValue(DPE_CHAR,$dataUserName["usr_name"]);
      $dbValue[11] = QuoteValue(DPE_CHAR,$outlet);
      
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

      $dtmodel->Update() or die("insert  error");
      	
      unset($dbField);
      unset($dbValue);
      

      
      //Rubah Status Semua Transaksi
      $sql = "select a.transaksi_id,a.transaksi_harga_jual,a.transaksi_total,
              a.transaksi_jumlah,b.item_nama
               from pos_transaksi a join pos_item b on a.id_item=b.item_id 
               where a.id_penjualan=".QuoteValue(DPE_CHAR,$penjualanId);     
        
      $rs = $dtaccess->Execute($sql,DB_SCHEMA);
      $dataTransaksi = $dtaccess->FetchAll($rs);

      
      for($i=0,$n=count($dataTransaksi);$i<$n;$i++)
      { 
        $dbTable = "pos_transaksi";
        $dbField[0]  = "transaksi_id";   // PK
        $dbField[1]  = "transaksi_tipe";
        
        $dbValue[0] = QuoteValue(DPE_CHAR,$dataTransaksi[$i]["transaksi_id"]);
        $dbValue[1] = QuoteValue(DPE_CHAR,'J');
    
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  
        $dtmodel->Update() or die("insert  error");
        	
        unset($dbField);
        unset($dbValue);
      
     //kurangi stok 
        $sql = "select transaksi_saldo from pos_transaksi
                  where id_item = ".QuoteValue(DPE_CHAR,$dataTransaksi[$i]["item_id"])." 
                  and id_dep = ".QuoteValue(DPE_CHAR,$_POST["outlet"])."  
                  order by transaksi_create desc limit 1";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $stok = $dtaccess->Fetch($rs);
        
      
        $dbTable = "pos_transaksi";
        $dbField[0]  = "transaksi_id";   // PK
        $dbField[1]  = "transaksi_tipe";
        
        $dbValue[0] = QuoteValue(DPE_CHAR,$dataTransaksi[$i]["transaksi_id"]);
        $dbValue[1] = QuoteValue(DPE_CHAR,'J');
    
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  
        $dtmodel->Update() or die("insert  error");
        	
        unset($dbField);
        unset($dbValue);
      
        $dbTable = "pos_item";
        
        $dbField[0]  = "item_id";   // PK
        $dbField[1]  = "item_jumlah";  
        
        $dbValue[0] = QuoteValue(DPE_CHAR,$dataTransaksi[$i]["item_id"]);
        $dbValue[1] = QuoteValue(DPE_NUMERIC,$stok["transaksi_saldo"]);  
        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

        $dtmodel->Update() or die("insert  error");	
        unset($dbField);
        unset($dbValue);
        
      //Masukkan ke member_trans untuk melihat pendapatan secara kesuluruhan
       /* $dbTable = "mp_member_trans";
            
        $dbField[0]  = "trans_id";   // PK
        $dbField[1]  = "id_dep";
        $dbField[2]  = "trans_time_flag";
        $dbField[3]  = "trans_create";
        $dbField[4]  = "trans_time_start";
        $dbField[5]  = "trans_harga_satuan";
        $dbField[6]  = "trans_harga_total";
        $dbField[7]  = "trans_nama";
        $dbField[8]  = "id_petugas";
        $dbField[9]  = "trans_petugas";
        $dbField[10]  = "trans_jenis";
        
        $sql = "SELECT usr_name FROM global.global_auth_user 
                  WHERE usr_id = ".$userData["id"];
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataUserName = $dtaccess->Fetch($rs);
        
        $transId = $dtaccess->GetTransID();
        $skr = date("Y-m-d H:i:s");
        $transNama = $dataTransaksi[$i]["item_nama"]."(".$dataTransaksi[$i]["transaksi_jumlah"].")";
        
        $dbValue[0] = QuoteValue(DPE_CHAR,$transId);
        $dbValue[1] = QuoteValue(DPE_CHAR,APP_OUTLET);
        $dbValue[2] = QuoteValue(DPE_CHAR,"y");
        $dbValue[3] = QuoteValue(DPE_DATE,$skr);
        $dbValue[4] = QuoteValue(DPE_DATE,$skr);
        $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTransaksi[$i]["transaksi_harga_jual"]));
        $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataTransaksi[$i]["transaksi_total"]));
        $dbValue[7] = QuoteValue(DPE_CHAR,$transNama);
        $dbValue[8] = QuoteValue(DPE_NUMERIC,$userData["id"]);
        $dbValue[9] = QuoteValue(DPE_CHAR,$dataUserName["usr_name"]);
        $dbValue[10] = QuoteValue(DPE_CHAR,"P");

        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

        $dtmodel->Insert() or die("insert  error");	
        unset($dbField);
        unset($dbValue);
        */
        //menghitung total pembelian 
        $totalHarga+=StripCurrency($dataTransaksi[$i]["transaksi_total"]);
        
        }
          
     // if ($_POST["btnBayar"])
     // { 
       
       $cetakPage = "penjualan_cetak.php?penjualan_id=".$penjualanId;   
       header("location:".$cetakPage);
     // }
    //  else
    //  {
     //  $backPage = "pos_trans_edit.php";   
    //   header("location:".$backPage);
     // }
     // exit();      
      
      
     }
    
     
     //Proses untuk melakukan Pesan
    if ($_POST["btnPesan"]) 
    {
        $sql = "select max(penjualan_kuitansi) as nomer from pos_penjualan";     
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataNomer = $dtaccess->Fetch($rs);
        $nomer=$dataNomer["nomer"]+1;
        echo "nomer".$nomer;
        $skr = date("Y-m-d H:i:s");
      
        for($i=0,$n=strlen($nomer);$i<10-$n;$i++) $penjualanNomer=$penjualanNomer."0";
           $penjualanNomer = $penjualanNomer.$nomer;
        
        $dbTable = "pos_penjualan";
        $dbField[0]  = "penjualan_id";   // PK
        $dbField[1]  = "penjualan_flag";
        $dbField[2]  = "penjualan_create";
        $dbField[3]  = "id_dep";
        $dbField[4]  = "penjualan_nomer";
        $dbField[5]  = "penjualan_kuitansi";
        $dbField[6]  = "penjualan_customer";
        $dbField[7]  = "id_customer";
        $dbField[8]  = "penjualan_tipe";
        $dbField[9]  = "id_petugas";
        $dbField[10]  = "penjualan_petugas";
	      $dbField[11]  = "id_meja";
	      
  
      
        $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
        $dbValue[1] = QuoteValue(DPE_CHAR,'Y');
        $dbValue[2] = QuoteValue(DPE_DATE,$skr);
        $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["outlet"]); 
        $dbValue[4] = QuoteValue(DPE_CHAR,$dataKonfigurasi["dep_no_surat"].$penjualanNomer);
        $dbValue[5] = QuoteValue(DPE_NUMERIC,$nomer); 
        $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["customer_nama"]);
        $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["id_customer"]); 
        $dbValue[8] = QuoteValue(DPE_CHAR,'N'); 
        $dbValue[9] = QuoteValue(DPE_NUMERIC,$userData["id"]);
        $dbValue[10] = QuoteValue(DPE_CHAR,$dataUserName["usr_name"]);
	      $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_meja"]);
	      


      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
      If ($penjualan_edit)
          $dtmodel->Update() or die("Update  error");
      else
          $dtmodel->Insert() or die("insert  error");
      	
      unset($dbField);
      unset($dbValue);
      
      $backPage = "pos_trans_edit.php";   
      header("location:".$backPage);
      exit();  
    
    }

     
     //Jika Melakukan penambahan menu     
     if ($_POST["btnUpdate"] || $_POST["btnSave"]) {
          
     //ambil data outlet dan data gudang
         $sql = "select konf_outlet,konf_gudang from mp_konfigurasi 
            where konf_id = 0";
         $rs_edit = $dtaccess->Execute($sql);
         $konfigurasi = $dtaccess->Fetch($rs_edit);
          if (!$penjualanId) {
          $sql = "select max(penjualan_kuitansi) as nomer from pos_penjualan";     
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataNomer = $dtaccess->Fetch($rs);
          $nomer=$dataNomer["nomer"]+1;
          for($i=0,$n=strlen($nomer);$i<10-$n;$i++) $penjualanNomer=$penjualanNomer."0";
             $penjualanNomer = $penjualanNomer.$nomer;
          $dbTable = "pos_penjualan";
          $dbField[0]  = "penjualan_id";   // PK
          $dbField[1]  = "penjualan_flag";
          $dbField[2]  = "penjualan_meja";
          $dbField[3]  = "penjualan_create";
          $dbField[4]  = "penjualan_nomer";
          $dbField[5]  = "penjualan_kuitansi";
          $dbField[6]  = "id_dep";
          
          $penjualanId = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[1] = QuoteValue(DPE_CHAR,'T');
          $dbValue[2] = QuoteValue(DPE_CHAR,"");
          $dbValue[3] = QuoteValue(DPE_DATE,$skr);
          $dbValue[4] = QuoteValue(DPE_CHAR,$penjualanNomer);
          $dbValue[5] = QuoteValue(DPE_NUMERIC,$nomer);         
          $dbValue[6] = QuoteValue(DPE_CHAR,$konfigurasi["konf_outlet"]);
    
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          unset($dbField);
          unset($dbValue); 
          }
          
          $sql = "select item_jumlah,item_harga_beli from pos_item
                    where item_id = ".QuoteValue(DPE_CHAR,$_POST["item_id"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $stok = $dtaccess->Fetch($rs);
          $stokSekarang = $stok["item_jumlah"]-$_POST["txtJumlah"];
          $transaksiId = $dtaccess->GetTransID();
          
          $dbTable = "pos_transaksi";
          $dbField[0]  = "transaksi_id";   // PK
          $dbField[1]  = "id_item";
          $dbField[2]  = "transaksi_jumlah";
          $dbField[3]  = "transaksi_create";
          $dbField[4]  = "transaksi_tipe";
          $dbField[5]  = "transaksi_saldo";
          $dbField[6]  = "id_petugas";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "transaksi_harga_beli";
          $dbField[9]  = "transaksi_harga_jual";
          $dbField[10]  = "transaksi_total";
          $dbField[11]  = "id_penjualan";
          $dbField[12]  = "item_nama";
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
          $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);
          $dbValue[3] = QuoteValue(DPE_DATE,$skr);
          $dbValue[4] = QuoteValue(DPE_CHAR,'S');
          $dbValue[5] = QuoteValue(DPE_NUMERIC,$stokSekarang);         
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$userData["id"]);
          $dbValue[7] = QuoteValue(DPE_CHAR,$outlet);
          $dbValue[8] = QuoteValue(DPE_NUMERIC,$stok["item_harga_beli"]);
          $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
          $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotal"]));
          $dbValue[11] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["item_nama"]);
         
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          unset($dbField);
          unset($dbValue); 
           
          $dbTable = "pos_item";
          
          $dbField[0]  = "item_id";   // PK
          $dbField[1]  = "item_jumlah";  
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$stokSekarang);  
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Update() or die("insert  error");	
          unset($dbField);
          unset($dbValue); 
          unset($_POST["btnSave"]);
          unset($_POST["item_nama"]);
          unset($_POST["item_kode"]);
          unset($_POST["txtJumlah"]);
          unset($_POST["txtHargaSatuan"]);
          unset($_POST["txtHargaTotal"]);
     }
     
     if($_POST["btnDelete"]){
      $transaksiId = & $_POST["cbDelete"];
      for($i=0,$n=count($transaksiId);$i<$n;$i++){
          $sql = "DELETE FROM pos_transaksi WHERE transaksi_id = '".$transaksiId[$i]."'";
          $dtaccess->Execute($sql,DB_SCHEMA);
      }
      unset($_POST["btnDelete"]);
   }
          
     $sql = "select *,b.item_nama from pos_transaksi a
             join pos_item b on a.id_item=b.item_id 
             where a.id_penjualan = ".QuoteValue(DPE_CHAR, $penjualanId)."
             order by a.transaksi_create desc";
             
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs_edit);
     $tableHeader = "&nbsp;Detail menu Pembelian";
     
     $isAllowedDel = $auth->IsAllowed("pos_pembelian",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("pos_pembelian",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("pos_pembelian",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
      $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
      $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
      $counterHeader++;
 
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
          $counterHeader++;
     }
     
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Item";
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
          
         $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["transaksi_id"].'">';               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;

         
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="#" onClick="Editmenu(\''.$dataTable[$i]["transaksi_id"].'\',\''.$dataTable[$i]["item_nama"].'\',\''.$dataTable[$i]["item_id"].'\',\''.$dataTable[$i]["transaksi_harga_beli"].'\',\''.$dataTable[$i]["transaksi_jumlah"].'\',\''.$dataTable[$i]["transaksi_total"].'\')"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["transaksi_harga_jual"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["transaksi_jumlah"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["transaksi_total"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          $totalHarga+=$dataTable[$i]["transaksi_total"];
          $colspan = count($tbHeader[0]);
     
     
			$tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
		
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan-1;
      
     $tbBottom[0][1][TABLE_ISI] = currency_format($totalHarga);
     $tbBottom[0][1][TABLE_ALIGN] = "right";
  
   }  
   /*$mejaId = & $_POST["cbDelete"];
        for{
            $sql = "delete from pos_meja where meja_id = '".$mejaId[$i]."'";
            $dtaccess->Execute($sql,DB_SCHEMA);*/
     
     
     
     //Setting Pajak
     // $pajak = $totalHarga * 0.1;
     
     
     
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
     $_POST["member_id"]= $dataTable["member_id"];*/

?>

<?php echo $view->RenderBody("inventori.css",true); ;?>
<?php echo $view->InitThickBox(); ?>
<div onKeyDown="CaptureEvent(event);">

<script language="Javascript">

<? $plx->Run(); ?>

</script>

<script language="Javascript">


function GantiHarga(harga) {
     var duit = document.getElementById('txtHargaSatuan').value.toString().replace(/\,/g,"");
     document.getElementById('txtHargaTotal').value = formatCurrency(duit*harga);
     document.getElementById('btnSave').focus();
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
     diskon_formatInt = diskon_format*1;
     document.getElementById('txtDiskon').value = formatCurrency(diskon_format);
     document.getElementById('txtTotalDibayar').value = formatCurrency(totalInt+(pajakInt-diskon_formatInt));
     document.getElementById('txtKembalian').value = formatCurrency(dibayarInt-(totalInt+(pajakInt-diskon_formatInt)));
     document.getElementById('txtDibayar').focus();
}

function MasukkanMenu(frm,kode) 
{        
     hasilKode=CariMenu(kode,'type=r');
     hasilAkhir=hasilKode.split('~~');
     
     if(!hasilAkhir[0]) {
          document.getElementById('item_kode').focus();
          alert('Item dengan kode \''+kode+'\' tidak ditemukan');
          return false;
     }
     
     document.getElementById('item_id').value=hasilAkhir[0];
     document.getElementById('item_nama').value=hasilAkhir[1];     
     document.getElementById('txtHargaSatuan').value=formatCurrency(hasilAkhir[2]);   
     document.getElementById('txtJumlah').value = 1;
     document.getElementById('txtHargaTotal').value =formatCurrency(hasilAkhir[2])
     document.getElementById('txtJumlah').focus();
}

function CheckDataSave(frm) {
     
     if(document.getElementById('rdMemberTipe_M').checked) {
          if(!document.getElementById('id_member').value) {
               alert('Member harap dipilih');
               return false;
          }
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
     
    if (document.getElementById('spNamaGuest').style.visibility == 'visible') {
     if(document.getElementById('txtNama').value==0) {
          alert('Nama Guest tidak boleh kosong');
          return false;
     }
          
     if(CheckData(frm.txtNama.value,frm.item_id.value,'type=r')){
      	alert('Nama Guest Sudah Ada Sudah Ada');
    	 	frm.txtNama.focus();
    		frm.txtNama.select();
    		return false;
    	}
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
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td>&nbsp;MINI POS</td>
    </tr>
</table>


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" ><!--onSubmit="return CheckDataSave(this);" --> 
<table width="70%" border="0" cellpadding="1" cellspacing="1">
      <tr class="tableheader">
        <td>&nbsp;<?php echo $judulForm; ?></td>
    </tr>
     <tr>
          <td>
          
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Kode Menu&nbsp;</td>
                    <td align="left" width="80%" class="tablecontent-odd">
                      <?php echo $view->RenderTextBox("item_kode","item_kode","30","100",$_POST["item_kode"],"inputField", "",false,"onChange=\"javascript:MasukkanMenu(this.form,this.value);\"");?>
                      <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih menu"><img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih menu" alt="Pilih menu" /></a>    
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Menu&nbsp;</td>
                    <td align="left" width="80%" class="tablecontent-odd">
                      <?php echo $view->RenderTextBox("item_nama","item_nama","30","100",$_POST["item_nama"],"inputField", "readonly",false);?>                
                      <input type="hidden" name="item_id" id="item_id" value="<?php echo $_POST["item_id"];?>" />
                    </td>
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Jumlah</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("txtJumlah","txtJumlah","3","3",$_POST["txtLama"],"curedit", "",false,'onChange=GantiHarga(this.value)');?>
                    </td>					
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Harga</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("txtHargaSatuan","txtHargaSatuan","10","10",currency_format($_POST["txtHargaSatuan"]),"curedit", "readonly",false);?>
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
                         <input type="submit" name="btnSave" id="btnSave" value="Simpan" class="button" />
                         
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
          </table>
         
          </td>
     </tr>
     <tr>
          <td>
          
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
                         <input type="submit" name="btnBayar" id="btnBayar" value="Bayar" class="button"/>
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
<input type="hidden" name="item_harga_jual" value="<?php echo $datamenu["item_harga_jual"];?>" />
<input type="hidden" name="member_id" value="<?php echo $_POST["member_id"];?>" />
<input type="hidden" name="jbayar_nama" value="<?php echo $dataMeja["jbayar_nama"];?>" />
<input type="hidden" name="awal" value="1" />

</form>
</div>
<script>document.frmEdit.item_kode.focus();</script>
<?php echo $view->RenderBodyEnd(); ?>

