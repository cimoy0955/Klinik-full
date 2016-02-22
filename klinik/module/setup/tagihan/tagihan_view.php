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
     $tableHeader = "Setup Biaya Tambahan";
     
     
    $viewPage = "tagihan_view.php";
     $editPage = "tagihan_edit.php";

     if(!$auth->IsAllowed("setup_biaya",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_biaya",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

  $sql = "select a.* from klinik.klinik_split a 
               order by a.split_nama ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataTable = $dtaccess->FetchAll($rs);
     
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Setup Jenis Biaya Rawat Jalan & UGD";
     
     $isAllowedDel = $auth->IsAllowed("setup_jenis_biaya",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_jenis_biaya",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_jenis_biaya",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++; 
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
    
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
    
    
          
     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
               
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["split_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
               
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$biayaStatus[$dataTable[$i]["split_flag"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["split_id"]).'"><img hspace="2" width="18" height="20" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
         
           if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["split_id"]).'"><img hspace="2" width="18" height="20" src="'.$APLICATION_ROOT.'images/b_drop.png" alt="Hapus" title="Hapus" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
         
     }
     
    
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<table width="80%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
     <tr> 
        <td colspan="<?php echo ($jumContent);?>"><div align="right">
            <input align="left" type="button" name="btnAdd" value="&nbsp;" id="button" onClick="document.location.href='<?php echo $editPage;?>?parent='">        
        <div></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>
<?php echo $view->SetFocus("btnAdd"); ?>
<?php echo $view->RenderBodyEnd(); ?>
