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

     $editPage = "workstation_edit.php?";
     $thisPage = "workstation_view.php?";

     if(!$auth->IsAllowed("setup_workstation",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("setup_workstation",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
   
     $tipe["y"] = "Aktif";
     $tipe["n"] = "Tidak";
     
     $tipeWork["M"] = "Multiplayer";
     $tipeWork["W"] = "Warnet";
     
     $sql = "select * from mp_meja order by meja_order";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Setup Workstation";
     
     $isAllowedDel = $auth->IsAllowed("setup_workstation",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_workstation",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_workstation",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
          $counterHeader++;
     }
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "IP Addreas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Keterangan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Aktif";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";     
     $counterHeader++;
     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["meja_id"].'">';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["meja_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["meja_order"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["meja_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["meja_ip"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["meja_keterangan"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $tipeWork[$dataTable[$i]["meja_tipe"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $tipe[$dataTable[$i]["meja_aktif"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
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

<?php echo $view->RenderBody("inventori.css",false); ?>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
