<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
 
     $enc = new TextEncrypt();
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $table = new InoTable("table1","100%","center");

     if($_x_mode=="New") $privMode = PRIV_CREATE;
     elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
     else $privMode = PRIV_DELETE;

     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }


     $editPage = "role_edit.php";
     $detPage = "role_act_view.php";
     $thisPage = "role_view.php";
     

     $sql = "select * from global.global_auth_role 
                where rol_id <> 0 
                order by rol_name";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
    
     //*-- config table ---*//
     $tableHeader = "&nbsp;User Role Master";
     
     // --- construct new table ---- //
     $counterHeader = 0;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Role Name";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "55%"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Aplikasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";    
     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
     
          if($dataTable[$i]["rol_id"]!=0 && $dataTable[$i]["rol_id"]!=ROLE_TIPE_CUSTOMER)
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["rol_id"].'">';
          else
               $tbContent[$i][$counter][TABLE_ISI] = '';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          if ($dataTable[$i]["rol_aplikasi"]=='A') $aplikasi="Semua Aplikasi";
          if ($dataTable[$i]["rol_aplikasi"]=='H') $aplikasi="Hotel";
          if ($dataTable[$i]["rol_aplikasi"]=='R') $aplikasi="Restaurant";
          if ($dataTable[$i]["rol_aplikasi"]=='K') $aplikasi="Karaoke"; 
          if ($dataTable[$i]["rol_aplikasi"]=='O') $aplikasi="Owner";  
          
          if($dataTable[$i]["rol_id"]!=0 && $dataTable[$i]["rol_id"]!=ROLE_TIPE_CUSTOMER)
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["rol_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
          else
               $tbContent[$i][$counter][TABLE_ISI] = '';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detPage.'?id='.$enc->Encode($dataTable[$i]["rol_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="zz" title="Detail" border="0"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rol_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";  
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $aplikasi;
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";           
     }
     
     $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Delete" class="button">&nbsp;<input type="button" name="btnAdd" value="Add New" class="button" onClick="document.location.href=\''.$editPage.'\'">';
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = 7;
     
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