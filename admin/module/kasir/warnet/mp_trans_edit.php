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

	$userData = $auth->GetUserData();
	$thisPage = "mp_trans_edit.php";
	$viewPage = "mp_trans_view.php";
	$findPage = "member_find.php?";
     
	$backPage = "mp_trans_view.php";
     
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
	
	if ($_GET["shutdown"]) $shutdownMode=1;
  else $shutdownMode=0; 
	if (!$_POST["btnSave"])
	{
	if($shutdownMode==0) {
    $judulForm = "Form Pembayaran Member";
    $judulHeader = "Rincian Transaksi";
    $idTrans = $_GET["bayar"];
    $idUsr = $enc->Decode($_GET["usr"]);
    $idMember = $enc->Decode($_GET["member"]);
              
		$sql = "select member_tipe,member_nama  from  mp_member 
				where member_id = ".QuoteValue(DPE_CHAR,$idMember);
		$rs = $dtaccess->Execute($sql);
		$dataMember = $dtaccess->Fetch($rs); 
      
		$sql = "select trans_id,trans_harga_total from mp_member_trans 
				where trans_id = ".QuoteValue(DPE_CHAR,$idTrans)." and id_dep = ".QuoteValue(DPE_CHAR,APP_OUTLET)."  
				order by trans_create desc limit 1";		
		$rs = $dtaccess->Execute($sql);
		$flag = $dtaccess->Fetch($rs);
	   
		//$total = TimestampDiff($flag["trans_time_start"],date("Y-m-d H:i:s"));
    //$harga = ceil($total/900) * 500;
    $harga=$flag["trans_harga_total"];
  } else {   
    
    $idMember = $_GET["shutdown"];
    $idUsr = $enc->Decode($_GET["usr"]);
              
		$sql = "select member_tipe from  mp_member 
				where member_id = ".QuoteValue(DPE_CHAR,$idMember);
				
		$rs = $dtaccess->Execute($sql);
		$dataMember = $dtaccess->Fetch($rs);
	             
		$sql = "select trans_id, trans_time_start from mp_member_trans 
				where id_member = ".QuoteValue(DPE_CHAR,$idMember)." and id_dep = ".QuoteValue(DPE_CHAR,APP_OUTLET)."  
				order by trans_create desc limit 1";
		$rs = $dtaccess->Execute($sql);
		$flag = $dtaccess->Fetch($rs);
	   
		$sql = "update mp_member set member_aktif = 'n' where member_id = ".QuoteValue(DPE_CHAR,$idMember);
		$dtaccess->Execute($sql);
	   
  	    if($dataMember["member_tipe"]=="G") {
                 $sql = "delete from global_auth_user where usr_id = ".QuoteValue(DPE_NUMERICKEY,$idUsr);
                 $dtaccess->Execute($sql);
  	   	}
  	   	
	   	
      }
     }
     else  
     {
     
     $sql = "SELECT usr_name FROM global_auth_user 
                    WHERE usr_id = ".$userData["id"];
          $rs = $dtaccess->Execute($sql);
          $dataUserName = $dtaccess->Fetch($rs);
     
     $sql = "update mp_member_trans set trans_time_expire = 0, trans_time_sisa = 0, trans_time_total = ".QuoteValue(DPE_NUMERIC,$total).", 
              trans_time_flag = 'n',id_petugas = ".$userData["id"].",trans_petugas = ".QuoteValue(DPE_CHAR,$dataUserName["usr_name"])."
              where trans_id = ".QuoteValue(DPE_CHAR,$_POST["transId"]);
     $dtaccess->Execute($sql);
	   
		 $sql = "update mp_member set member_aktif = 'n' where member_id = ".QuoteValue(DPE_CHAR,$_POST["id_member"]);
		 $dtaccess->Execute($sql);
	   
	    if($dataMember["member_tipe"]=="G") {
               $sql = "delete from global_auth_user where usr_id = ".QuoteValue(DPE_NUMERICKEY,$_POST["id_usr"]);
               $dtaccess->Execute($sql);
	   	}
     }
     
     if ($_POST["btnSave"] || $shutdownMode==1)
     {
      header("Location:mp_trans_view.php");
      exit();
     } 	
     
?>

<?php echo $view->RenderBody("inventori.css",true); ;?>
<?php echo $view->InitThickBox(); ?>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td>&nbsp;<?php echo $judulForm; ?></td>
    </tr>
</table>
 
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" onSubmit="return CheckDataSave(this);">
<table width="65%" border="0" cellpadding="1" cellspacing="1">
     <tr>
          <td>
          <fieldset>
          <legend><strong>&nbsp;<?php echo $judulHeader;?>&nbsp;</strong><legend>
          <table width="100%" border="0" cellpadding="1" cellspacing="1">
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Nama Pengguna&nbsp;</td>
                    <td align="left" width="80%" class="tablecontent-odd">
                         <span id="spNamaGuest" style="visibility:visible">
                         <?php echo $view->RenderTextBox("txtNama","txtNama","20","20",$dataMember["member_nama"],"", "",false,false);?>
                        </span>
                    </td>
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Nama Pengguna&nbsp;</td>
                    <td align="left" width="80%" class="tablecontent-odd">
                         <span id="spNamaGuest" style="visibility:visible">
                         <?php echo $view->RenderTextBox("txtBiaya","txtBiaya","20","20",currency_format($harga),"", "",false,false);?>
                        </span>
                    </td>
               </tr>
               <tr>
                    <td colspan="4" align="right" class="tblCol">
                         <input type="submit" name="btnSave" value="Bayar" class="button"/>
                         <input type="button" name="btnBack" value="Kembali" class="button" onClick="document.location.href='<?php echo $backPage?>'">
                    </td>
               </tr>
          </table>
          </fieldset>
          </td>
     </tr>
</table>

<input type="hidden" name="transId" value="<?php echo $flag["trans_id"];?>" />
<input type="hidden" name="id_member" value="<?php echo $idMember;?>" />
<input type="hidden" name="id_usr" value="<?php echo $idUsr;?>" />
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />
<input type="hidden" name="member_id" value="<?php echo $member_id?>" />
</form>


     

