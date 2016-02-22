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
     $table = new InoTable("table","70%","left");
 
     $editPage = "icd_edit.php?";
     $thisPage = "icd_view.php?";
     if (!$_GET["recPerPage"]) $_GET["recPerPage"] = 25;
     if (!$_GET["currentPage"]) $_GET["currentPage"] = 1;

     if(!$auth->IsAllowed("setup_icd",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_icd",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     
     $sql = "select * 
          from klinik.klinik_prosedur a 
          order by a.prosedur_kode
          limit ".$_GET["recPerPage"]." offset ".(($_GET["currentPage"] - 1) * $_GET["recPerPage"]);
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;ICD 10";
     
     $isAllowedDel = $auth->IsAllowed("setup_icd",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_icd",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_icd",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
      
     
     if($isAllowedDel) {
          $tbHeader[0][$counterHeader][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
     }
     
     if($isAllowedCreate) {
          $tbHeader[0][$counterHeader][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
     } 
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "4";
     $tbHeader[0][$counterHeader][TABLE_ALIGN] = "left";
     
     $counterHeader = 0;
     if($isAllowedDel){
          $tbHeader[1][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
     if($isAllowedUpdate){
          $tbHeader[1][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "7%";
          $counterHeader++;
     }
          
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Code";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;
     
     $tbHeader[1][$counterHeader][TABLE_ISI] = "Prosedur";
     $tbHeader[1][$counterHeader][TABLE_WIDTH] = "75%";     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["prosedur_id"].'">';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["prosedur_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["prosedur_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["prosedur_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
     }
     
     $colspan = count($tbHeader[1]);

     if($isAllowedDel) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
     }
     
     if($isAllowedCreate) {
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
     }
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;

     $sql = "select count(*) as jml_row from klinik_prosedur";
     $rs_count = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataCount = $dtaccess->Fetch($rs_count);
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $view->RenderPaging($dataCount["jml_row"], 25, $_GET["currentPage"]);?><br />
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
