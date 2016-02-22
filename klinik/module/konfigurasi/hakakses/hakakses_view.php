<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
	require_once($APLICATION_ROOT."library/view.cls.php");

     $enc = new TextEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
	$table = new InoTable("table1","100%","center");
     
     if(!$auth->IsAllowed("setup_hakakses",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("setup_hakakses",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }


     $statusLogin["y"] = "Aktif";
     $statusLogin["n"] = "Non Aktif";
 
     $editPage = "hakakses_edit.php";
     $thisPage = "hakakses_view.php";
 
     $sql = "select a.*, b.rol_name from global_auth_user a 
               left join global_auth_role b on a.id_rol = b.rol_id 
               where a.id_rol <> 0  
               order by a.usr_id";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);

	//*-- config table ---*//
     $tableHeader = "&nbsp;User Account Master";     
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "User Name";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "35%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Login Name";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "22%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Role Name";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Login Status";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
     
          $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["usr_id"].'">';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["usr_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_loginname"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rol_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusLogin[$dataTable[$i]["usr_status"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
     }
     
     $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Delete" class="button">&nbsp;<input type="button" name="btnAdd" value="Add New" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$strRecord;
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = 6;
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
	<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom);?>
</form>
</body>
</html>
