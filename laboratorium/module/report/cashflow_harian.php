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
     $_POST["id_petugas"]= $auth->GetUserId();   
     $usrId = $auth->GetUserId();   
     
     $printPage = "penjualan_dealer_cetak.php?";
         
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}
     
      //ambil data Outlet
     $sql = "select b.* from global.global_auth_user a 
             left join global.global_departemen b on a.id_dep=b.dep_id where usr_id =".$usrId;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataOutlet = $dtaccess->Fetch($rs);
     $_POST["outlet"] = $dataOutlet["dep_id"];
     $_POST["outlet_stok"] = $dataOutlet["id_stok"];
     
    
  
	   
     if(!$_POST["penjualan_tipe"]) $_POST["penjualan_tipe"]="--";
      $cetakPage = "cashflow_harian_cetak.php?id_petugas=".$_POST["id_petugas"].
                  "&penjualan_tipe=".$_POST["penjualan_tipe"];
                  
     if ($_POST["penjualan_tipe"]<> "--") $sql_where = " and a.penjualan_tipe = ".QuoteValue(DPE_CHAR,$_POST["penjualan_tipe"]);
      
	   $sql = "select a.penjualan_create, a.penjualan_id, a.penjualan_nomer,a.penjualan_petugas,
             a.penjualan_tipe, a.penjualan_total, a.penjualan_ppn,a.penjualan_customer,b.dep_nama,c.*                 
             from pos.pos_penjualan a 
             left join global.global_departemen b on a.id_dep=b.dep_id
             left join pos.pos_transaksi c on c.id_penjualan = a.penjualan_id
             where a.penjualan_create >= ".QuoteValue(DPE_DATE,getDateToday())."
             and a.id_dep = ".QuoteValue(DPE_CHAR,$_POST["outlet"]).
            "and a.id_petugas = ".QuoteValue(DPE_NUMERIC,$_POST["id_petugas"]).$sql_where;
     $sql .= " order by a.penjualan_create asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
	   $dataTable = $dtaccess->FetchAll($rs);
	   
     //*-- config table ---*//
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "Pendapatan Hari Ini (Petugas : ".$dataTable[0]["penjualan_petugas"].")";

		// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[0][$counter][TABLE_ISI] = "Re-print nota";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Waktu";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "No. Nota";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Customer";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Total Penjualan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Total Tax";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	if ($_POST["penjualan_tipe"] == "--") {
	$tbHeader[0][$counter][TABLE_ISI] = "Tipe Bayar";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%"; 
  $counter++;
  }
  
  if ($_POST["id_petugas"] == "--") {
	$tbHeader[0][$counter][TABLE_ISI] = "Petugas";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%"; 
  $counter++;
  }
  
  if ($_POST["id_dep"] == "--") {
	$tbHeader[0][$counter][TABLE_ISI] = "Outlet";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%"; 
  $counter++;
  }
	


	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	  //terjemahkan tipe
     if ($dataTable[$i]["penjualan_tipe"]=='T') $tipeBayar="TUNAI";
     else $tipeBayar="NON TUNAI";
     
     //hitung total
	  $total+=$dataTable[$i]["penjualan_total"];
	  
	  //hitung tax
	  $totaltax+=$dataTable[$i]["penjualan_ppn"];
	  
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "<a href=\"#\" onClick=\"document.location.href='".$printPage."&penjualan_id=".$dataTable[$i]["penjualan_id"]."'\"><img src=\"".$APLICATION_ROOT."images/print.png\" style=\"border:none\" width=\"25\" height=\"25\" /></a>";
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
	  
	  $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["penjualan_create"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomer"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_customer"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
   
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_total"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_ppn"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		if ($_POST["penjualan_tipe"] == "--") {
		$tbContent[$i][$counter][TABLE_ISI] = $tipeBayar;
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++; }
		
		if ($_POST["id_petugas"] == "--") {
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_petugas"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++; }
		
		if ($_POST["id_dep"] == "--") {
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dep_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++; }
		
		
	}

	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[0][$counter][TABLE_COLSPAN] = 2;
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[0][$counter][TABLE_COLSPAN] = 2;
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]     = currency_format($total);
	$tbBottom[0][$counter][TABLE_ALIGN]   = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($totaltax);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$counter=0;	
	$tbBottom[1][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[1][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[1][$counter][TABLE_COLSPAN] = 2;
	$counter++;
	
	$tbBottom[1][$counter][TABLE_ISI]     = "Total : ";
	$tbBottom[1][$counter][TABLE_ALIGN]   = "right";
	$tbBottom[1][$counter][TABLE_COLSPAN] = 2;
	$counter++;
	
	$tbBottom[1][$counter][TABLE_ISI]   = "&nbsp;";
	$tbBottom[1][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[1][$counter][TABLE_ISI]   = currency_format($total+$totaltax);
	$tbBottom[1][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[1][$counter][TABLE_ISI]   = "&nbsp;";
	$tbBottom[1][$counter][TABLE_ALIGN] = "right";
	$tbBottom[1][$counter][TABLE_COLSPAN] = 2;
	$counter++;
	
	if ($_POST["penjualan_tipe"] == "--") 
  {
	$tbBottom[0][$counter][TABLE_ISI]   = '&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	}
	
	if ($_POST["id_petugas"] == "--") 
  {
	$tbBottom[0][$counter][TABLE_ISI]   = '&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	}
	
	if ($_POST["id_dep"] == "--") 
  {
	$tbBottom[0][$counter][TABLE_ISI]   = '&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	}
	

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
  if($_POST["penjualan_tipe"]=='T') $show = "selected";
  $tipe[1] = $view->RenderOption("T","Tunai",$show);
  unset($show);
  if($_POST["penjualan_tipe"]=='N') $show = "selected";
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
      <td width="10%" class="tablecontent">&nbsp;Tipe</td>
          <td width="27%">
      <?php echo $view->RenderComboBox("penjualan_tipe","penjualan_tipe",$tipe,null,null,false);?>    
          </td>
     </tr>
     <tr>
          <td>
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>
</table>
</form>


		
<BR/>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</body>
</html>
 
