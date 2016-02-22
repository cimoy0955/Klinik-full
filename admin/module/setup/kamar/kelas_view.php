<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $enc = new TextEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","100%","center");

     $bedPage    = "bed_view.php";
     $editPage   = "kelas_edit.php";
     $thisPage   = "kelas_view.php";

 	if(!$auth->IsAllowed("setup_kamar",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("setup_kamar",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
     
     $isAllowedDel = $auth->IsAllowed("setup_kamar",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_kamar",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_kamar",PRIV_CREATE);
     
     function StripArr($num){
          return StripCurrency($num);
     }

	$sql = "select * from klinik.klinik_biaya where biaya_jenis like '%K%' order by biaya_jenis, biaya_nama ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataBiaya = $dtaccess->FetchAll($rs);
     //echo $sql;

	$sql = "select * from klinik.klinik_split where split_flag = ".QuoteValue(DPE_CHAR,SPLIT_INAP)."  order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
	
     $sql = "select * from klinik.klinik_biaya_split"; 
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     while($row = $dtaccess->Fetch($rs)) {
		$_POST["txtNom"][$row["id_biaya"]][$row["id_split"]] = $row["bea_split_nominal"];
	}
     
	$table = new InoTable("table1","100%","left",null,1,2,1,null);     
     $PageHeader = "Tabel Biaya";

     // --- construct new table ---- //
	$counter=0;
	 $counterHeader = 0;
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
     } 
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
     }
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
	
	
	for($i=0,$n=count($dataSplit);$i<$n;$i++){
		$tbHeader[0][$i+4][TABLE_ISI] = $dataSplit[$i]["split_nama"];
		$tbHeader[0][$i+4][TABLE_WIDTH] = "10%";
    $counterHeader++;
	}
	
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;

     for($i=0,$counter=0,$n=count($dataBiaya);$i<$n;$i++,$counter=0){
          
          
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataBiaya[$i]["biaya_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataBiaya[$i]["biaya_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }          
          
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataBiaya[$i]["biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $namaKelas[$dataBiaya[$i]["biaya_jenis"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
	
		for($j=0,$k=count($dataSplit);$j<$k;$j++){
			$tbContent[$i][$counter][TABLE_ISI] = currency_format($_POST["txtNom"][$dataBiaya[$i]["biaya_id"]][$dataSplit[$j]["split_id"]]);
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
		}
		
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataBiaya[$i]["biaya_total"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
		
     }


     if($isAllowedUpdate) $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnUpdate" value="Simpan" class="button">&nbsp;';
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);
     $tbBottom[0][0][TABLE_ALIGN] = "center";
	$counter++;
	
?>
<?php echo $view->RenderBody("inosoft.css",false); ?>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
          <tr> 
        <td colspan="<?php echo ($jumContent);?>"><div align="right">
            <input type="button" name="btnAdd" value="Tambah" id="button" onClick="document.location.href='<?php echo $editPage;?>'">        
        <div></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
