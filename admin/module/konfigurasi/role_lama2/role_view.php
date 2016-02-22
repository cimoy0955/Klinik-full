<?php
    require_once("root.inc.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");
    require_once($APLICATION_ROOT."library/view.cls.php");

    $enc = new TextEncrypt();
    $dtaccess = new DataAccess();
    $auth = new CAuth();

  /*  if(!$auth->IsAllowed("setup_user",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } */

    $editPage = "role_edit.php";
    $detPage = "role_act_view.php";
    $thisPage = "role_view.php";
    
    $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
    
    $table = new InoTable("table","70%","left");
    
 //   $isAllowedDel = $auth->IsAllowed("setup_penelitian",PRIV_DELETE);
 //   $isAllowedUpdate = $auth->IsAllowed("setup_penelitian",PRIV_UPDATE);
 //   $isAllowedCreate = $auth->IsAllowed("setup_penelitian",PRIV_CREATE);
     
 $isAllowedDel=1;
 $isAllowedUpdate=1;
 $isAllowedCreate=1;
     
    $sql = "select * from global.global_auth_role 
                where rol_id <> 0 
                order by rol_id";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs); 
     
    //*-- config table ---*//
    // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";     
     $counterHeader++;
     
     
     
     if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
    

    for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
     
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rol_name"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          
          if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?&del=1&id='.$enc->Encode($dataTable[$i]["role_id"]).'"><img hspace="2" width="25" height="25" src="'.$APLICATION_ROOT.'gambar/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?&id='.$enc->Encode($dataTable[$i]["role_id"]).'"><img hspace="2" width="25" height="25" src="'.$APLICATION_ROOT.'gambar/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detPage.'?&id='.$dataTable[$i]["role_id"].'"><img hspace="2" width="25" height="25" src="'.$APLICATION_ROOT.'gambar/detail.png" alt="Tambah Tree Produk" title="Tambah Tree Produk" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
 
         
     }
     
     $colspan = count($tbHeader[0]);

    

?>
<script language="JavaScript">
function Edit()
{
  document.location.href='role_edit.php';
}
</script>

<?php echo $view->RenderBody("expose.css",false); ?>

<table width="100%" >
     <tr class="tableheaderatas">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>
<table width="100%">
     <tr class="tableheaderatas">
          <td align="center"><img src="<?php echo $ROOT;?>program/gambar/tambah_1.png" style="cursor:pointer"; onCLick="javascript:Edit();"></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>

<?php echo $view->RenderBodyEnd(); ?>

