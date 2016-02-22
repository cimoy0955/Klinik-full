<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");

     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     //$_POST["id_petugas"]= $auth->GetUserId();   
     //$usrId = $auth->GetUserId();   
     $skr = format_date(getDateToday());
     $printPage = "report_opname_cetak.php?";
     
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;    
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}
     /*
      //ambil data Outlet
     $sql = "select b.* from global.global_auth_user a 
             left join global.global_departemen b on a.id_dep=b.dep_id where usr_id =".$usrId;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataOutlet = $dtaccess->Fetch($rs);
     $_POST["outlet"] = $dataOutlet["dep_id"];
     $_POST["outlet_stok"] = $dataOutlet["id_stok"];
      
     
     if(!$_POST["opname_tipe"]) $_POST["opname_tipe"]="--";
      $cetakPage = "cashflow_harian_cetak.php?id_petugas=".$_POST["id_petugas"].
                  "&opname_tipe=".$_POST["opname_tipe"];
                  
     if ($_POST["opname_tipe"]<> "--") $sql_where = " and a.opname_tipe = ".QuoteValue(DPE_CHAR,$_POST["opname_tipe"]);
      */
     if ($_POST["tanggal_awal"]) $sql_where[] = " a.opname_tanggal >=".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     if ($_POST["tanggal_akhir"]) $sql_where[] = " a.opname_tanggal <=".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
	   $sql = "select a.*,c.item_nama,c.item_kode                
             from optik.optik_opname a 
             left join optik.optik_item_master c on c.item_id = a.id_item";
     if($sql_where) $sql .= " where ".implode(" and ",$sql_where);
     $sql .= " order by a.opname_tanggal asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
	   $dataTable = $dtaccess->FetchAll($rs);
	   
     //*-- config table ---*//
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "Laporan opname";

		// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Waktu";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Kode Item";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Item";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Stok Tercatat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Stok Real";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Selisih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Keterangan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	
  for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	   //hitung total
	  $total+=$dataTable[$i]["opname_total"];
	  
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	
	  $tbContent[$i][$counter][TABLE_ISI] = format_date_long($dataTable[$i]["opname_tanggal"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["item_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
  
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["opname_stok_tercatat"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["opname_stok_real"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	
	  $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["opname_miss"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	
	  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["opname_keterangan"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	}

	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "right";
	$tbBottom[0][$counter][TABLE_COLSPAN] = count($tbHeader[0]);
	$counter++;
	
/*
	$sql = "select * from global.global_departemen where dep_id='".$_POST["outlet"]."'"; 
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataDep = $dtaccess->FetchAll($rs);
  $dep[0] = $view->RenderOption("--","[Pilih Outlet]",$show);
  for($i=0,$n=count($dataDep);$i<$n;$i++){
         unset($show);
         if($_POST["id_dep"]==$dataDep[$i]["dep_id"]) $show = "selected";
         $dep[$i+1] = $view->RenderOption($dataDep[$i]["dep_id"],$dataDep[$i]["dep_nama"],$show);               
    } 
  
  //Tipe Bayar  
  $tipe[0] = $view->RenderOption("--","[Pilih Tipe Bayar]",$show);
  unset($show);
  if($_POST["opname_tipe"]=='T') $show = "selected";
  $tipe[1] = $view->RenderOption("T","Tunai",$show);
  unset($show);
  if($_POST["opname_tipe"]=='N') $show = "selected";
  $tipe[2] = $view->RenderOption("N"," Non Tunai",$show);
  
  $sql = "select * from global.global_auth_user where id_rol <> ".ROLE_TIPE_MEMBER; 
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataUser = $dtaccess->FetchAll($rs);
  $usr[0] = $view->RenderOption("--","[Pilih Petugas]",$show);
  for($i=0,$n=count($dataUser);$i<$n;$i++){
         unset($show);
         if($_POST["id_petugas"]==$dataUser[$i]["usr_id"]) $show = "selected";
         $usr[$i+1] = $view->RenderOption($dataUser[$i]["usr_id"],$dataUser[$i]["usr_name"],$show);               
    } 
      
	*/
	
	
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td>&nbsp;<?php echo $PageHeader;?></td>
     </tr>
</table>

<BR/>


<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td class="tablecontent">Mulai</td>
          <td class="tablecontent-odd"><input type="text" name="tanggal_awal" id="tanggal_awal" value="<? echo $_POST["tanggal_awal"];?>" /></td>
          <td class="tablecontent">Sampai</td>
          <td class="tablecontent-odd"><input type="text" name="tanggal_akhir" id="tanggal_akhir" value="<? echo $_POST["tanggal_akhir"];?>" /></td>
     </tr>
     <tr>
          <td colspan="4">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>
</table>
</form>


		
<BR/>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</body>
</html>
 
