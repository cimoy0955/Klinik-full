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
         
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}

    
     $cetakPage = "cashflow_harian_cetak.php?id_petugas=".$_POST["id_petugas"];
     $tipe["W"] = "Pemasukan Warnet";
     $tipe["M"] = "Pemasukan Multiplayer";
     $tipe["P"] = "Pemasukan Point of Sale";
     $tipe["O"] = "Operasional";
     $tipe["A"] = "Kas Awal";
     
     $sql = "select a.trans_nama, a.trans_ket,a.trans_create, a.trans_petugas,a.trans_harga_total, c.usr_loginname, a.trans_jenis, c.usr_name    
               from mp_member_trans a
               left join mp_member b on a.id_member = b.member_id 
               left join global_auth_user c on b.id_usr = c.usr_id
               where a.trans_create >= ".QuoteValue(DPE_DATE,getDateToday())."
               and id_petugas = ".QuoteValue(DPE_NUMERIC,$_POST["id_petugas"])."
               order by a.trans_jenis,a.trans_create asc";
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataTable = $dtaccess->FetchAll($rs);

     //*-- config table ---*//
     $table = new InoTable("table1","80%","left",null,0,2,1,null);     
     $PageHeader = "CashFlow Harian (Petugas : ".$dataTable[0]["trans_petugas"].")";

	// --- construct new table ---- //
	$tbHeader[0][0][TABLE_ISI] = "No";
	$tbHeader[0][0][TABLE_WIDTH] = "5%";

	$tbHeader[0][1][TABLE_ISI] = "Waktu";
	$tbHeader[0][1][TABLE_WIDTH] = "20%";

	$tbHeader[0][2][TABLE_ISI] = "Nama";
	$tbHeader[0][2][TABLE_WIDTH] = "30%";

	$tbHeader[0][3][TABLE_ISI] = "Pemasukan";
	$tbHeader[0][3][TABLE_WIDTH] = "15%";

	$tbHeader[0][4][TABLE_ISI] = "Pengeluaran";
	$tbHeader[0][4][TABLE_WIDTH] = "10%";
	
	$tbHeader[0][5][TABLE_ISI] = "Keterangan";
	$tbHeader[0][5][TABLE_WIDTH] = "20%";


	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	 
	  
	  if ($dataTable[$i]["trans_jenis"]=='W' || $dataTable[$i]["trans_jenis"]=='M') {
	  $nama = ($dataTable[$i]["usr_loginname"]) ? $dataTable[$i]["trans_nama"]." (".$dataTable[$i]["usr_loginname"].")" : "GUEST(".$dataTable[$i]["trans_nama"].")"; 
    } else { $nama=$dataTable[$i]["trans_nama"]; }
	  
    
      
		if ($dataTable[$i]["trans_jenis"]=='O') 
    { 
        $total -= $dataTable[$i]["trans_harga_total"]; 
        $totalPendapatan += $dataTable[$i]["trans_harga_total"];
        $kolomPendapatan="";
        $kolomPengeluaran=currency_format($dataTable[$i]["trans_harga_total"]);
    } else {
		    $total += $dataTable[$i]["trans_harga_total"]; 
		    $totalPengeluaran += $dataTable[$i]["trans_harga_total"];
        $kolomPendapatan=currency_format($dataTable[$i]["trans_harga_total"]);
        $kolomPengeluaran="";
    }
		
		if ($dataTable[$i]["trans_jenis"]!='O') {
		    $keterangan=$tipe[$dataTable[$i]["trans_jenis"]]; 
    } else {
		    $keterangan=$dataTable[$i]["trans_ket"];
		}
	
		$tbContent[$i][$counter][TABLE_ISI] = $i+1;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
	
		$tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["trans_create"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $nama;
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $kolomPendapatan;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = $kolomPengeluaran;
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = $keterangan;
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
	}

		
	$tbBottom[0][0][TABLE_ISI] = "Total : ";
	$tbBottom[0][0][TABLE_ALIGN] = "right";
	$tbBottom[0][0][TABLE_COLSPAN] = 3;

	$tbBottom[0][1][TABLE_ISI] = currency_format($total);
	$tbBottom[0][1][TABLE_ALIGN] = "right";

	$tbBottom[0][2][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">&nbsp;';
	$tbBottom[0][2][TABLE_ALIGN] = "right";
	$tbBottom[0][2][TABLE_COLSPAN] = 3;
	
	$sql = "select * from global_auth_user where id_rol <> ".ROLE_TIPE_MEMBER; 
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataUser = $dtaccess->FetchAll($rs);
  $usr[0] = $view->RenderOption("--","[Pilih Petugas]",$show);
  for($i=0,$n=count($dataUser);$i<$n;$i++){
         unset($show);
         if($_POST["id_petugas"]==$dataUser[$i]["usr_id"]) $show = "selected";
         $usr[$i+1] = $view->RenderOption($dataUser[$i]["usr_id"],$dataUser[$i]["usr_name"],$show);               
    } 
	
?>

<?php echo $view->RenderBody("inventori.css",true); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td>&nbsp;<?php echo $PageHeader;?></td>
     </tr>
</table>

<BR/>
		
<BR/>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</body>
</html>
 
