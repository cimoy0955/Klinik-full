<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");

     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     
     $editPage = "biaya_edit.php";
     $thisPage = "biaya_view.php";

    
     if(!$auth->IsAllowed("setup_biaya",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("setup_biaya",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
/*
	$isAllowedCreate = $auth->IsAllowed("setup_biaya",PRIV_CREATE);
	$isAllowedUpdate = $auth->IsAllowed("setup_biaya",PRIV_UPDATE);
	$isAllowedDel = $auth->IsAllowed("setup_biaya",PRIV_DELETE);
*/	
	$isAllowedCreate=1;
	$isAllowedUpdate=1;
	$isAllowedDel=1;

     function StripArr($num){
          return StripCurrency($num);
     }

	$sql = "select * from klinik.klinik_biaya 
            order by biaya_kode";//where biaya_jenis not like '%T%'
          //and biaya_jenis not like '%V%'
          
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataBiaya = $dtaccess->FetchAll($rs);
//echo $sql;
	$sql = "select * from klinik.klinik_split where split_flag like '".SPLIT_PERAWATAN."' order by split_id"; //
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
	
     $sql = "select * from klinik.klinik_biaya_split"; 
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     while($row = $dtaccess->Fetch($rs)) {
		$_POST["txtNom"][$row["id_biaya"]][$row["id_split"]] = $row["bea_split_nominal"];
	}

     $sqlStatus = "select * from global_status_pasien order by status_id";
     $rsStatus = $dtaccess->Execute($sqlStatus);
     while($dataStatus = $dtaccess->Fetch($rsStatus)) {
          $status_nya[$dataStatus["status_id"]] = $dataStatus["status_nama"];
     }
     
	$table = new InoTable("table1","100%","left",null,1,2,1,null);     
     $PageHeader = "Tabel Biaya";

     // --- construct new table ---- //
	$counter=0;
	 $counterHeader = 0;
     
     if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
     }
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
     }
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
	
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     /* request user tgl 23 Des 2015
      * untuk sementara di comment dulu   
     */	
     /*for($i=0,$n=count($dataSplit);$i<$n;$i++){
		$tbHeader[0][$counterHeader][TABLE_ISI] = $dataSplit[$i]["split_nama"];
		$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
    $counterHeader++;
	}*/
	
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     
     for($i=0,$j=0,$counter=0,$n=count($dataBiaya);$i<$n;$i++,$j++,$counter=0){
          // catatan tgl 22 Des 2015: untuk kode jenis biaya "OBAT" = "AP" sementara pada global.cfg.php = "T" sehingga sub header tidak muncul
          // perubahan tgl 23 Des 2015: untuk jenis biaya dijadikan tabel master sehingga dapat diedit oleh client
          /*if ($dataBiaya[$i]["biaya_jenis"] != $dataBiaya[$i-1]["biaya_jenis"]) {                            
               $tbContent[$j][$counter][TABLE_ISI] = (isset($dataBiaya[$i]["biaya_jenis"])) ? $status_nya[$dataBiaya[$i]["biaya_jenis"]] : "&nbsp;";
               $tbContent[$j][$counter][TABLE_ALIGN] = "left";
               $tbContent[$j][$counter][TABLE_COLSPAN] = count($tbHeader);
               $tbContent[$j][$counter][TABLE_CLASS] = "tablesmallheader";
               $counter=0;
               $j++;
          } */
          
          if($isAllowedDel) {
               $tbContent[$j][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataBiaya[$i]["biaya_id"].'">';
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
                    
          if($isAllowedUpdate) {
               $tbContent[$j][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataBiaya[$i]["biaya_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          $tbContent[$j][$counter][TABLE_ISI] = $dataBiaya[$i]["biaya_kode"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$j][$counter][TABLE_ISI] = $dataBiaya[$i]["biaya_nama"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
		
          $tbContent[$j][$counter][TABLE_ISI] = $status_nya[$dataBiaya[$i]["biaya_jenis"]];
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++;
	/* request user tgl 23 Des 2015
		for($l=0,$k=count($dataSplit);$l<$k;$l++){
               $tbContent[$j][$counter][TABLE_ISI] = currency_format($_POST["txtNom"][$dataBiaya[$i]["biaya_id"]][$dataSplit[$l]["split_id"]]);
     		$tbContent[$j][$counter][TABLE_ALIGN] = "center";
     		$counter++;
		}
	*/	
          $tbContent[$j][$counter][TABLE_ISI] = currency_format($dataBiaya[$i]["biaya_total"]);
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          
		
     }


     
     //$tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);
     
     if($isAllowedDel) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
     }

//     if($isAllowedUpdate) $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnUpdate" value="Simpan" class="button">&nbsp;';
     
     if($isAllowedCreate) {
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
     }
     $tbBottom[0][0][TABLE_ALIGN] = "center";
	$counter++;
	
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>


<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td>&nbsp;<?php echo $PageHeader;?></td>
     </tr>
</table>

<BR />


<form name="frmEdit" method="POST" action="<?php echo $editPage;?>">
     <div style="margin-left:10px;">
          <?php if($isAllowedDel) { ?>
               <input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;
          <?php } ?>
          <?php if($isAllowedCreate) { 
               echo '&nbsp;&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          } ?>
     </div>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</form>
</body>
</html>