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
     $usrId = $auth->GetUserId();
     $viewPage = "bonus_view.php?";
     $editPage = "bonus_edit.php?";
     $thisPage = "bonus_view_hidden.php?";


     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     
     $sql = "select a.* from lab_bonus a where is_active = 'n'
               order by a.bonus_nama ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Setup Kategori";
     
     $isAllowedDel = $auth->IsAllowed("laboratorium",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("laboratorium",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("laboratorium",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbEnable[]');\">";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Aktif";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";     
     $counterHeader++;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbEnable[]" value="'.$dataTable[$i]["bonus_id"].'">';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
         
          $lokasi = $ROOT."logistik/images/item";
          $fotoName=$lokasi."/".$dataTable[$i]["grup_pic"];
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bonus_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["is_active"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
         
     }
     
     $colspan = count($tbHeader[0]);

     
     if($isAllowedDel) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnEnable" value="Aktifkan" class="button">&nbsp;&nbsp;&nbsp;<input type="button" name="btnBack" value="Kembali" class="button" onClick="backPage();">&nbsp;';
     }
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>
<script type="text/javascript">
  function backPage(){
    window.location.href = '<?php echo $viewPage; ?>';
  }
</script>

<table width="70%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>

<?php echo $view->RenderBodyEnd(); ?>