<?php
     //LIBRARY 
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
 
     //INISIALISAI AWAL LIBRARY
     $enc = new TextEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $table = new InoTable("table1","100%","center");

     //Login Authentifikasi  
     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     //variable awal
     $editPage = "role_edit.php";
     $detPage = "role_act_view.php";
     $thisPage = "role_view.php";
     

     $sql = "select * from global_auth_role 
                order by rol_id, rol_name";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
    
     //*-- config table ---*//
     $tableHeader = "&nbsp;User Role Master";
     
     // --- construct new table ---- //
     $counterHeader = 0;
     if($auth->IsAllowed("setup_role",PRIV_DELETE)){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
     }
     
     if($auth->IsAllowed("setup_role",PRIV_UPDATE)){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
     }
     
     if($auth->IsAllowed("setup_role",PRIV_READ)){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
     }
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Role Name";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "85%";     
     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          if($auth->IsAllowed("setup_role",PRIV_DELETE)) {
               if($dataTable[$i]["rol_id"]!=ROLE_TIPE_ADMINISTRATOR && $dataTable[$i]["rol_id"]!=ROLE_TIPE_CUSTOMER && $dataTable[$i]["rol_id"]!=ROLE_TIPE_ADMIN_DAERAH && $dataTable[$i]["rol_id"]!=ROLE_TIPE_MARKETING)
                    $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["rol_id"].'">';
               else
                    $tbContent[$i][$counter][TABLE_ISI] = '';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($auth->IsAllowed("setup_role",PRIV_UPDATE)) {
               if($dataTable[$i]["rol_id"]!=ROLE_TIPE_ADMINISTRATOR && $dataTable[$i]["rol_id"]!=ROLE_TIPE_CUSTOMER && $dataTable[$i]["rol_id"]!=ROLE_TIPE_ADMIN_DAERAH && $dataTable[$i]["rol_id"]!=ROLE_TIPE_MARKETING) 
                    $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["rol_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
               else
                    $tbContent[$i][$counter][TABLE_ISI] = '';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($auth->IsAllowed("setup_role",PRIV_READ)) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detPage.'?id='.$enc->Encode($dataTable[$i]["rol_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Detail" title="Detail" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rol_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
     }
     
     $colspan = 4;
     if(!($auth->IsAllowed("setup_role",PRIV_DELETE))) {
          $colspan = $colspan-1;
     
     }
     
     if(!($auth->IsAllowed("setup_role",PRIV_UPDATE))) {
          $colspan = $colspan-1;
          
     }
     
     if($auth->IsAllowed("setup_role",PRIV_DELETE) && $auth->IsAllowed("setup_role",PRIV_CREATE)) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Delete" class="button">&nbsp;<input type="button" name="btnAdd" value="Add New" class="button" onClick="document.location.href=\''.$editPage.'\'">';
     
     } elseif($auth->IsAllowed("setup_role",PRIV_DELETE) && !($auth->IsAllowed("setup_role",PRIV_CREATE))) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Delete" class="button">';
          
     } elseif(!($auth->IsAllowed("setup_role",PRIV_DELETE)) && $auth->IsAllowed("setup_role",PRIV_CREATE)) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Add New" class="button" onClick="document.location.href=\''.$editPage.'\'">';
          
     }
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
     
?>
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
</head>

<body>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td><?php echo $tableHeader;?></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>    
</form>
</body>
</html>
