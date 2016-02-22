<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $editPage = "ina_edit.php?jenis=".$_GET["jenis"];
     $thisPage = "ina_view.php?jenis=".$_GET["jenis"];

     if(!$auth->IsAllowed("setup_ina",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_ina",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     
     if(!$_GET["jenis"]) {
          die("denied");
          exit(1);
     }

	$sql = "select * from klinik.klinik_split order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
     
     $sql = "select * from
               klinik.klinik_ina a
               left join klinik.klinik_ina_split b on b.id_ina = a.ina_id 
               where a.ina_jenis = ".QuoteValue(DPE_CHAR,$_GET["jenis"])." 
               order by a.ina_kode";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     $tot = -1;
     for($i=0,$n=count($dataTable);$i<$n;$i++) {
          if($dataTable[$i]["ina_id"]!=$dataTable[$i-1]["ina_id"]){
               $tot++;
               $data[$tot] = $dataTable[$i]["ina_id"];
          }
          $nama[$dataTable[$i]["ina_id"]] = $dataTable[$i]["ina_nama"]; 
          $kode[$dataTable[$i]["ina_id"]] = $dataTable[$i]["ina_kode"]; 
          $total[$dataTable[$i]["ina_id"]] = $dataTable[$i]["ina_nominal"]; 
          $biaya[$dataTable[$i]["ina_id"]][$dataTable[$i]["id_split"]] = $dataTable[$i]["ina_split_nominal"]; 
     }
     
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;INA DRG";
     
     $isAllowedDel = $auth->IsAllowed("setup_ina",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_ina",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_ina",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
     }
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
     }
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
     
     if($dataSplit)
     $width = 59/count($dataSplit);
     for($j=0,$m=count($dataSplit);$j<$m;$j++) {
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = $dataSplit[$j]["split_nama"];
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = $width."%";
          $counterHeader++;
          
     } 
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;
     
     for($i=0,$counter=0,$n=$tot;$i<=$n;$i++,$counter=0){
          if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$data[$i].'">';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($data[$i]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $kode[$data[$i]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $nama[$data[$i]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          for($j=0,$m=count($dataSplit);$j<$m;$j++) {
               
               $tbContent[$i][$counter][TABLE_ISI] = currency_format($biaya[$data[$i]][$dataSplit[$j]["split_id"]]);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
          }
               
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($total[$data[$i]]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;         
     }
     
     $colspan = count($tbHeader[0]);

     
     if($isAllowedDel) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
     }
     
     if($isAllowedCreate) {
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
     }
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
