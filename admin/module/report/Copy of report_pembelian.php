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
     
     $cetakPage = "report_pembelian_cetak.php?";
  //   // --- link untuk cetak ---
  //        $cetakPage = "sisipan_cetak.php?siswa_nis=".$_POST["siswa_nis"]
         
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}
     
	   $sql = "select a.pembelian_create, a.pembelian_id, a.pembelian_nomor, a.pembelian_total                
             from apotik_pembelian a 
             where a.pembelian_create >= ".QuoteValue(DPE_DATE,getDateToday())."
             order by a.pembelian_create asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
	   $dataTable = $dtaccess->FetchAll($rs);
	   
     //*-- config table ---*//
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "Pembelian Hari Ini";

		// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  /*
  $tbHeader[0][$counter][TABLE_ISI] = "Re-print nota";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
  $counter++;
  */
	$tbHeader[0][$counter][TABLE_ISI] = "Waktu";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "No. Nota";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  /*
	$tbHeader[0][$counter][TABLE_ISI] = "Customer";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  */
	$tbHeader[0][$counter][TABLE_ISI] = "Total pembelian";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	/*
	$tbHeader[0][$counter][TABLE_ISI] = "Total Tax";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	*/
	
  for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	   //hitung total
	  $total+=$dataTable[$i]["pembelian_total"];
	  
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	/*	
		$tbContent[$i][$counter][TABLE_ISI] = "<a href=\"#\" onClick=\"document.location.href='".$printPage."&pembelian_id=".$dataTable[$i]["pembelian_id"]."'\"><img src=\"".$APLICATION_ROOT."images/print.png\" style=\"border:none\" width=\"25\" height=\"25\" /></a>";
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
	  */
	  $tbContent[$i][$counter][TABLE_ISI] = format_date_long($dataTable[$i]["pembelian_create"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembelian_nomor"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
/*
		$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembelian_customer"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
  */ 
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["pembelian_total"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	}

	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[0][$counter][TABLE_COLSPAN] = 1;
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]     = "Total : ";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "right";
	$tbBottom[0][$counter][TABLE_COLSPAN] = 2;
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($total+$totaltax);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
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
  if($_POST["pembelian_tipe"]=='T') $show = "selected";
  $tipe[1] = $view->RenderOption("T","Tunai",$show);
  unset($show);
  if($_POST["pembelian_tipe"]=='N') $show = "selected";
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
     <tr><!--
          <td>
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td> -->
     </tr>
</table>
</form>


		
<BR/>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</body>
</html>
 
