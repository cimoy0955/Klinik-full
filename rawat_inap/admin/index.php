<?php
    require_once("root.inc.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/inoLiveX.php");

    $auth = new CAuth();
    $dtaccess = new DataAccess();
    

    if($auth->IsAllowed()===1){
        include("login.php");
        exit();
    }
    /*
    $usrId = $auth->GetUserId();
    
	   $sql = "select * from global_auth_user where usr_id =".$usrId;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataUser = $dtaccess->Fetch($rs);
     */
    
	$link=& $_GET["link"];
	//menu konfigurasi
	if ($link=="role") $goLink="module/konfigurasi/role/role_view.php";
	if ($link=="hak_akses") $goLink="module/konfigurasi/hakases/hakakses_view.php";
	if ($link=="konfigurasi") $goLink="module/konfigurasi/konfigurasi/konfigurasi_edit.php";
	if ($link=="workstation") $goLink="module/konfigurasi/workstation/workstation_edit.php";
	
	//menu setup
	if ($link=="setup_paket") $goLink="module/setup/paket/paket_view.php";
	if ($link=="setup_workstation") $goLink="module/setup/workstation/workstation_view.php";
	if ($link=="setup_outlet") $goLink="module/setup/outlet/outlet_view.php";
	if ($link=="setup_game") $goLink="module/setup/game/game_view.php";
	if ($link=="setup_group") $goLink="module/setup/group/group_game_view.php";
	
	
	//menu pos
	if ($link=="pos_satuan_jual") $goLink="module/pos/setup/satuan_jual/satuan_jual_view.php";
	if ($link=="pos_satuan_beli") $goLink="module/pos/setup/satuan_beli/satuan_beli_view.php";
	if ($link=="pos_grup_item") $goLink="module/pos/setup/grup_item/grup_item_view.php";
	if ($link=="pos_item") $goLink="module/pos/setup/item/item_view.php";
	if ($link=="pos_pembelian") $goLink="module/pos/pembelian/pembelian_view.php";
	if ($link=="pos_opname") $goLink="module/pos/opname/opname_view.php";
	
	//menu inventori
	if ($link=="inv_gudang") $goLink="module/inventori/master_gudang/master_gudang_view.php";
	
	
	//menu admin
	if ($link=="admin_uangmuka") $goLink="module/admin/uang_muka/uang_muka_view.php";
	if ($link=="admin_operasional") $goLink="module/admin/operasional/operasional_view.php";
	if ($link=="admin_berita") $goLink="module/admin/berita/berita_view.php";
	if ($link=="admin_pengumuman") $goLink="module/admin/pengumuman/pengumuman_view.php";
//	if ($link=="admin_hapus") $goLink="module/admin/hapus_guest.php";
//	if ($link=="admin_fee") $goLink="module/admin/member_dapat.php";
	
	//menu kasir
	if ($link=="kasir_transaksi") $goLink="module/kasir/multiplayer/mp_trans_view.php";
	if ($link=="warnet_transaksi") $goLink="module/kasir/warnet/mp_trans_view.php";
	if ($link=="wifi_transaksi") $goLink="module/kasir/wifi/wifi_trans_view.php";
	if ($link=="pos_transaksi") $goLink="module/kasir/pos/pos_trans_edit.php";
	if ($link=="kasir_memberbaru") $goLink="module/kasir/member/member_edit.php";
	if ($link=="kasir_managemember") $goLink="module/kasir/member/member_view.php";
	if ($link=="kasir_operasional") $goLink="module/kasir/multiplayer/kasir_operasional_view.php";
	if ($link=="kasir_cashflow") $goLink="module/kasir/multiplayer/cashflow_harian.php";
	
	//menu laporan
	if ($link=="laporan_cashflow") $goLink="module/report/cashflow_harian.php";
	if ($link=="laporan_saran") $goLink="module/report/saran_kritik.php";
	if ($link=="laporan_pembelian") $goLink="module/report/pembelian.php";
	if ($link=="laporan_opname") $goLink="module/report/opname.php";
	if ($link=="laporan_cashflow_pos") $goLink="module/pos/report/cashflow_harian.php";
	if ($link=="laporan_labarugi_pos") $goLink="module/pos/report/labarugi.php";
	
	//menu bantuan
	if ($link=="bantuan") $goLink="../bantuan/bantuan_index.html";
	
	$plx = new InoLiveX("CekPenjualan,CekChatting,CheckBottom,Sync"); 
	
	function Sync() {
          global $dtaccess,$pg_dtaccess,$userData;
          
          $pg_dtaccess = new PG_DataAccess();
          
          $sql = "select * from mp_member_trans where trans_sync='n'";
          $rs = $dtaccess->Execute($sql);
          $dataSql = $dtaccess->FetchAll($rs);
          for($i=0,$counter=0,$n=count($dataSql);$i<$n;$i++,$counter=0){
           $pg_sql = "insert into multiplayer.mp_member_trans (trans_id,id_member,id_dep,trans_time_flag,trans_time_expire,
                      trans_time_total,trans_create,trans_time_start,trans_harga_satuan,trans_harga_total,trans_time_sisa,
                      trans_nama,trans_jenis,id_petugas,trans_ket,trans_petugas,id_meja) 
               values (".QuoteValue(DPE_CHAR,$dataSql[$i]["trans_id"]).",".QuoteValue(DPE_CHAR,$dataSql[$i]["id_member"]).",".
                       QuoteValue(DPE_CHAR,$dataSql[$i]["id_dep"]).",".QuoteValue(DPE_CHAR,$dataSql[$i]["trans_time_flag"]).",".
                       QuoteValue(DPE_NUMERIC,$dataSql[$i]["trans_time_expire"]).",".QuoteValue(DPE_NUMERIC,$dataSql[$i]["trans_time_total"]).",".
                       QuoteValue(DPE_DATE,$dataSql[$i]["trans_create"]).",".QuoteValue(DPE_DATE,$dataSql[$i]["trans_time_start"]).",".
                       QuoteValue(DPE_NUMERIC,$dataSql[$i]["trans_harga_satuan"]).",".QuoteValue(DPE_NUMERIC,$dataSql[$i]["trans_harga_total"]).",".
                       QuoteValue(DPE_NUMERIC,$dataSql[$i]["trans_time_sisa"]).",".QuoteValue(DPE_CHAR,$dataSql[$i]["trans_nama"]).",".
                       QuoteValue(DPE_CHAR,$dataSql[$i]["trans_jenis"]).",".QuoteValue(DPE_NUMERIC,$dataSql[$i]["id_petugas"]).",".
                       QuoteValue(DPE_CHAR,$dataSql[$i]["trans_ket"]).",".QuoteValue(DPE_CHAR,$dataSql[$i]["trans_petugas"]).",".
                       QuoteValue(DPE_CHAR,$dataSql[$i]["id_meja"]).
                       ")";
            
           $pg_rs = $pg_dtaccess->PG_Execute($pg_sql,DB_SCHEMA_GLOBAL);
           
           $sql = "update mp_member_trans set trans_sync = 'y' 
                   where trans_id = ".QuoteValue(DPE_CHAR,$dataSql[$i]["trans_id"]);
           $rs = $dtaccess->Execute($sql);
           }  
           
           
     };
     
	function CekPenjualan() {
          global $dtaccess, $userData;
          
          $sql = "select transaksi_id from pos_transaksi where transaksi_tipe='T'";
          $rs = $dtaccess->Execute($sql);
          $flag = $dtaccess->Fetch($rs);
          if ($flag) 
          { 
           $sql = "update pos_transaksi set transaksi_tipe = 'U' 
           where transaksi_id = ".QuoteValue(DPE_CHAR,$flag["transaksi_id"]);
           $dtaccess->Execute($sql); 
            return $flag["transaksi_id"]; 
          } else 
          { 
          return 0; }
          
     };
      
  function CheckBottom() {
     global $dtaccess;
          
     $sql = "select count(chat_id) as jum from mp_chat where chat_read = 'n' and chat_kepada = 'Admin'";
		 $rs = $dtaccess->Execute($sql);
		 $jumPesan = $dtaccess->Fetch($rs);  
         
     $hasil='<a href="javascript:gantiPassword()">Ubah Password</a> - 
     <a href="javascript:saranKritik()">Saran dan Kritik</a> - 
     <a href="javascript:penjualan()">Penjualan</a> -  
     <a href="javascript:chatting()">Chating('.$jumPesan["jum"].')</a>';
     return $hasil; 
     }
  
  function CekChatting() {
          global $dtaccess, $userData;
          
          $sql = "select chat_dari from mp_chat where chat_kepada='Admin' and chat_flag='y'";
          $rs = $dtaccess->Execute($sql);
          $flag = $dtaccess->Fetch($rs);
          if ($flag) 
          { 
           $sql = "update mp_chat set chat_flag = 'n' 
           where chat_flag = ".QuoteValue(DPE_CHAR,'y');
           $dtaccess->Execute($sql); 
            return $flag["chat_dari"]; 
          } else { return 0; }
          
     };   
  
   //Tampilan Menu
	//Menu Konfigurasi
	if ($auth->IsAllowed("setup_role",PRIV_READ))  $menuKonfigurasi=1; 
	if ($auth->IsAllowed("setup_hakakses",PRIV_READ))  $menuKonfigurasi=1; 
	if ($auth->IsAllowed("setup_konfigurasi",PRIV_READ))  $menuKonfigurasi=1; 
  if ($auth->IsAllowed("setup_workstation",PRIV_READ))  $menuKonfigurasi=1;  
  

	//Menu Penjualan
	if ($auth->IsAllowed("kasir_transaksi",PRIV_READ))  $menuPenjualan=1; 
  
	
?>

<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<style type="text/css"><!--
  body,td,div,p,a,font,span {font-family: arial,sans-serif;}
  body {margin-top:2}.c {width:4; height: 4} 
  body { bgcolor:"#ffffff" } 
  A:link {color:#0000cc; } 
  A:visited { color:#551a8b; }
  A:active { color:#ff0000; }
  .form-noindent {background-color: #ffffff; border: #C3D9FF 1px solid}
--></style>
<style type="text/css"><!--
.gaia.le.lbl { font-family: Arial, Helvetica, sans-serif; font-size: smaller; }
.gaia.le.fpwd { font-family: Arial, Helvetica, sans-serif; font-size: 70%; }
.gaia.le.chusr { font-family: Arial, Helvetica, sans-serif; font-size: 70%; }
.gaia.le.val { font-family: Arial, Helvetica, sans-serif; font-size: smaller; }
.gaia.le.button { font-family: Arial, Helvetica, sans-serif; font-size: smaller; }
.gaia.le.rem { font-family: Arial, Helvetica, sans-serif; font-size: smaller; }

.gaia.captchahtml.desc { font-family: arial, sans-serif; font-size: smaller; } 
.gaia.captchahtml.cmt { font-family: arial, sans-serif; font-size: smaller; font-style: italic; }
  
--></style>
  
  <title>Caremax Billing System</title>
  <style type="text/css"><!--
    .body { margin-left: 3em;
            margin-right: 5em;
            font-family: arial,sans-serif; }

    div.errorbox-good {}

    div.errorbox-bad {} 

    div.errormsg { color: red; font-size: smaller; font-family: arial,sans-serif;}
    font.errormsg { color: red; font-size: smaller; font-family: arial,sans-serif;}

    
    
    hr {
      border: 0;
      background-color:#DDDDDD;
      height: 1px;
      width: 100%;
      text-align: left;
      margin: 5px;
    }
    

    
    
  --></style>
  

<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>library/script/shell.js"></script>  
<script type="text/javascript" src="<?php echo $ROOT;?>library/script/anylink.js"></script>


<script language="JavaScript">

<? $plx->Run(); ?>

var mTimer,mulai=0;


function timer(){
var adaPenjualan,adaChatting,new_win;
var link;
       
 clearInterval(mTimer); 
 //biar kalau 0 tidak TRUE 
 if (mulai>10) {    
 if((mulai % 60) == 0){   
 //adaPenjualan=CekPenjualan('type=r');
 CheckBottom('target=dv_tabel');
 //if (adaPenjualan!='0')
 // {
  // new_win=new_win=window.open('penjualan.php','Penjualan','toolbar=no,scrollbars=no,resizable=no,top=100,left=200,width=550,height=480');
   //new_win.focus();  
 // }  
 }
 if((mulai % 600) == 0){
 Sync('target=dv_tabel');
 }   
 }
 mulai++;
 mTimer = setTimeout("timer()", 1000);
}
timer();

function Logout()
{
    if(confirm('Are You Sure to LogOut?')) {
         document.location.href='logout.php';
    }
    else return false;
}

function penjualan() {     
     var new_win;
     new_win=window.open('penjualan.php','Penjualan','toolbar=no,scrollbars=no,resizable=no,top=100,left=200,width=680,height=480');
     new_win.focus();
}

function chatting() {     
     var new_win;
     var link='chating_admin.php';
     new_win=window.open(link,'Chatting','toolbar=no,scrollbars=no,resizable=no,top=100,left=200,width=680,height=480');
     new_win.focus();
}


</script>
 <link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>library/css/anylink.css"> 

</head><body>
  <div id="main">
  <table width="100%" border="0" cellpadding="2" cellspacing="0">
  <tbody><tr>
  <td colspan="2"><img alt="" width="1" height="2"></td>
  </tr>
  <tr>
  <td valign="top" width="1%">
  <img src="images/logo_bkmm.gif" alt="Caremax" align="left" border="0">
  
  </a>
  </td>
  <td valign="top">
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tbody><tr>
  <td colspan="2"><img alt="" width="1" height="15"></td>
  </tr>
  <tr>
    <td><div align="right"><font size="2">Petugas : <?=$dataUser["usr_name"];?></font></div></td>
  </tr>
  <tr bgcolor="#3366cc">
  <td><img alt="" width="1" height="1"></td>
  </tr>
  
  <tr bgcolor="#e5ecf9">
  <td style="padding-left: 4px; padding-bottom: 3px; padding-top: 2px; font:normal 13px; font-family: arial,sans-serif;">
  
    <?php if ($menuKonfigurasi) { ?>
         <a style="cursor:pointer;"; onClick="return clickreturnvalue()" onMouseover="dropdownmenu(this, event, 'menu_konfigurasi')"><strong>Konfigurasi</strong></a>&nbsp;&nbsp;&nbsp; <? } ?>
    <?php if ($menuKonfigurasi) { ?> 
         <a style="cursor:pointer;"; onClick="return clickreturnvalue()" onMouseover="dropdownmenu(this, event, 'menu_setup')"><strong>Setup</strong></a>&nbsp;&nbsp;&nbsp; <? } ?>
    <?php if ($menuInventori) { ?> 
         <a style="cursor:pointer;"; onClick="return clickreturnvalue()" onMouseover="dropdownmenu(this, event, 'menu_inv')"><strong>Inventori</strong></a>&nbsp;&nbsp;&nbsp; <? } ?>
    <?php if ($menuKonfigurasi) { ?>
         <a style="cursor:pointer;"; onClick="return clickreturnvalue()" onMouseover="dropdownmenu(this, event, 'menu_pos')"><strong>Purchasing</strong></a>&nbsp;&nbsp;&nbsp; <? } ?>
    <?php if ($menuKonfigurasi) { ?>
         <a style="cursor:pointer;"; onClick="return clickreturnvalue()" onMouseover="dropdownmenu(this, event, 'menu_admin')"><strong>Admin</strong></a>&nbsp;&nbsp;&nbsp; <? } ?>
    <?php if ($menuKonfigurasi) { ?>
         <a style="cursor:pointer;"; onClick="return clickreturnvalue()" onMouseover="dropdownmenu(this, event, 'menu_kasir')"><strong>Penjualan</strong></a>&nbsp;&nbsp;&nbsp; <? } ?>
    <?php if ($menuKonfigurasi) { ?>
         <a style="cursor:pointer;"; onClick="return clickreturnvalue()" onMouseover="dropdownmenu(this, event, 'menu_laporan')"><strong>Laporan</strong></a>&nbsp;&nbsp;&nbsp; <? } ?>
    <?php if ($menuKonfigurasi) { ?>
    <a  style="cursor:pointer;"; onClick="return clickreturnvalue()" ><strong>Bantuan</strong></a>&nbsp;&nbsp;&nbsp; <? } ?>
    <a style="cursor:pointer" onClick="javascript: Logout();"> 
   <strong>Logout</strong></a>                                                 
<div id="menu_konfigurasi" class="anylinkcss">

<?if($auth->IsAllowed("setup_konfigurasi",PRIV_READ)){ ?><a href="index.php?link=role">Role</a> <? } ?>
<?if($auth->IsAllowed("setup_hakakses",PRIV_READ)){ ?><a href="index.php?link=hak_akses">Hak Akses</a> <? } ?>
<?if($auth->IsAllowed("setup_konfigurasi",PRIV_READ)){ ?><a href="index.php?link=konfigurasi">Konfigurasi</a> <? } ?>
<?if($auth->IsAllowed("setup_workstation",PRIV_READ)){ ?><a href="index.php?link=workstation">Workstation</a> <? } ?>
</div>

<!--2nd anchor link and menu -->
                                                    
<div id="menu_setup" class="anylinkcss">

<a href="index.php?link=setup_paket">Paket</a>
<!--<a href="index.php?link=setup_member">Member</a>-->
<a href="index.php?link=setup_workstation">Workstation</a>
<a href="index.php?link=setup_outlet">Outlet</a>
<a href="index.php?link=setup_group">Group Shortcut</a>
<a href="index.php?link=setup_game">Shortcut</a>
<a href="index.php?link=pos_satuan_beli">Satuan Beli</a>
<a href="index.php?link=pos_satuan_jual">Satuan Jual</a>
<a href="index.php?link=pos_grup_item">Kategori Item</a>
<a href="index.php?link=pos_item">Item</a>
</div>

<div id="menu_inv" class="anylinkcss">
<a href="index.php?link=inv_gudang">Master Gudang</a>
</div>

<div id="menu_pos" class="anylinkcss">


<a href="index.php?link=pos_pembelian">Pembelian</a>
<a href="index.php?link=pos_opname">Opname</a>
<!--<a href="index.php?link=setup_member">Member</a>
<a href="index.php?link=setup_workstation">Workstation</a>
<a href="index.php?link=setup_game">Game</a>-->
</div>

<div id="menu_admin" class="anylinkcss">

<a href="index.php?link=admin_uangmuka">Uang Muka</a>
<a href="index.php?link=admin_operasional">Operasional</a>
<a href="index.php?link=admin_berita">Berita</a>
<a href="index.php?link=admin_pengumuman">Pengumuman</a>
<!--<a href="index.php?link=admin_hapus">Hapus Guest</a>
<a href="index.php?link=admin_fee">Fee Member</a>-->
</div>

<div id="menu_kasir" class="anylinkcss">

<a href="index.php?link=kasir_transaksi">Kasir Transaksi MP</a>
<a href="index.php?link=warnet_transaksi">Kasir Transaksi Warnet</a>
<a href="index.php?link=wifi_transaksi">Kasir Transaksi Wifi</a>
<a href="index.php?link=pos_transaksi">Penjualan POS</a>
<a href="index.php?link=kasir_memberbaru">Member Baru</a>
<a href="index.php?link=kasir_managemember">Manage Member</a>
<a href="index.php?link=kasir_operasional">Operasional</a>
<a href="index.php?link=kasir_cashflow">Cashflow Harian</a>

</div>

<div id="menu_laporan" class="anylinkcss">

<a href="index.php?link=laporan_cashflow">Cashflow Harian</a>
<a href="index.php?link=laporan_saran">Saran dan Kritik</a>
<a href="index.php?link=laporan_pembelian">Pembelian</a>
<a href="index.php?link=laporan_opname">Opname</a>
<a href="index.php?link=laporan_cashflow_pos">POS Cashflow Harian</a>
<a href="index.php?link=laporan_labarugi_pos">POS Laba Rugi</a>
</div>

  </td>

  </tr>
  <tr>
  <td colspan="2"><img alt="" width="1" height="5"></td>
  </tr>
  </tbody></table>
  </td>
  </tr>
  </tbody></table>
  <br>
  <table width="100%" height="80%" align="center" border="0" cellpadding="5" cellspacing="1">
  <tbody>
      <tr> 
        <td><iframe style="width:100%;height:100%;" marginwidth="0" marginheight="0" id="awglogin" name="awglogin" src="<?php echo $goLink;?>" scrolling="auto" align="center" frameborder="0"></iframe></td>
  </tr>
  </tbody></table>

 <style type="text/css"><!--
.footer { padding-right: 5px; 
          padding-left: 5px; 
          padding-bottom: 5px; 
          padding-top: 5px; 
          font-size: 83%;
          border-top: #ffffff 1px solid; 
          border-bottom: #ffffff 1px solid; 
          background: #e5ecf9; 
          text-align: center;
          font-family: arial,sans-serif;
}
--></style>
  <div id="dv_tabel" class="footer" align="center"></div>
  </div>
  </body></html>