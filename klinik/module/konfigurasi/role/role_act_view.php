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
    
     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if($_GET["id"]){
         $rolEnc = $_GET["id"];
         $rolId = $enc->Decode($_GET["id"]);
     }

     if($_GET["app_id"]){
         $appEnc = $_GET["app_id"];
         $appId = $_GET["app_id"];
     }

     $editPage = "role_act_edit.php?id_rol=".$rolEnc;
     $addPage = "role_act_add.php?id_rol=".$rolEnc."&app_id=".$appEnc;
     $backPage = "role_view.php";
     $thisPage = "role_act_view.php?id=".$rolEnc;
 
     $logoPriv["1"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_okay.png" alt="OK" title="OK" border="0">';
     $logoPriv["0"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_nokay.png" alt="NO" title="NO" border="0">';

     if($rolId){
          $sqlNama = "select rol_name from global_auth_role where rol_id = ".QuoteValue(DPE_NUMERIC,$rolId);
          $rsNama = $dtaccess->Execute($sqlNama);
          $dataTableNama = $dtaccess->FetchAll($rsNama);
 
          $sql = "select distinct(c.priv_name),d.app_nama,e.id,e.nama_app, d.app_id, a.*, b.rol_name 
                    from global.global_auth_role_priv a
                    join global_auth_role b on a.id_rol = b.rol_id
                    join global_auth_privilege c on a.id_priv = c.priv_id
                    left join global_app d on d.app_id = c.id_app
                    left join ( select app_nama as nama_app,app_id as id from global.global_app ) e on e.id = c.id_app_nama
                    where c.id_app_nama is not null and a.id_rol = ".QuoteValue(DPE_NUMERIC,$rolId)."
                    order by e.id,a.id_priv";
          $rs = $dtaccess->Execute($sql);
          $dataTable = $dtaccess->FetchAll($rs);
     }

     //*-- config table ---*//
	$tableHeader = "&nbsp;Role Name  : ".$dataTableNama[0]["rol_name"];

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
	
	$tbHeader[0][$counterHeader][TABLE_ISI] = "Action Name";
	$tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++;

	$tbHeader[0][$counterHeader][TABLE_ISI] = "Create";
	$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;

	$tbHeader[0][$counterHeader][TABLE_ISI] = "Read";
	$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;

	$tbHeader[0][$counterHeader][TABLE_ISI] = "Update";
	$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;

	$tbHeader[0][$counterHeader][TABLE_ISI] = "Delete";
	$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
       
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
		
			if($dataTable[$i]["id"]!=$dataTable[$i-1]["id"]){
      $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["nama_app"];
      $tbContent[$j][$counter][TABLE_ALIGN] = "left";
      $tbContent[$j][$counter][TABLE_CLASS] = "tablesmallheader";
      $tbContent[$j][$counter][TABLE_COLSPAN] = $counterHeader;
      $counter=0;
      $j++;
  }		
		
		if($auth->IsAllowed("setup_role",PRIV_DELETE)) {
			$tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["id_priv"].'">';
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
		}
		
		if($auth->IsAllowed("setup_role",PRIV_UPDATE)) {
			$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["id_priv"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
		}
		
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["priv_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          for($j=(7-4);$j<7;$j++) {
               $tbContent[$i][$counter][TABLE_ISI] = $logoPriv[$dataTable[$i]["rol_priv_access"]{$j-3}];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }        
	}
     
     $colspan = 7;
     if(!($auth->IsAllowed("setup_role",PRIV_DELETE))) {
          $colspan = $colspan-1;
     
     }
     
     if(!($auth->IsAllowed("setup_role",PRIV_UPDATE))) {
          $colspan = $colspan-1;
          
     }
     
	if($auth->IsAllowed("setup_role",PRIV_DELETE) && $auth->IsAllowed("setup_role",PRIV_CREATE)) {
		$tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Delete" class="button">&nbsp;<input type="button" name="btnAdd" value="Add New" class="button" onClick="document.location.href=\''.$addPage.'\'">&nbsp;<input type="button" name="btnBack" value="B a c k" class="button" onClick="document.location.href=\''.$backPage.'\'">';
	
	} elseif($auth->IsAllowed("setup_role",PRIV_DELETE) && !($auth->IsAllowed("setup_role",PRIV_CREATE))) {
		$tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Delete" class="button">&nbsp;<input type="button" name="btnBack" value="B a c k" class="button" onClick="document.location.href=\''.$backPage.'\'">';
          	
	} elseif(!($auth->IsAllowed("setup_role",PRIV_DELETE)) && $auth->IsAllowed("setup_role",PRIV_CREATE)) {
		$tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Add New" class="button" onClick="document.location.href=\''.$addPage.'\'">&nbsp;<input type="button" name="btnBack" value="B a c k" class="button" onClick="document.location.href=\''.$backPage.'\'">';
          
	} elseif(!($auth->IsAllowed("setup_role",PRIV_DELETE)) && !($auth->IsAllowed("setup_role",PRIV_CREATE))) {
		$tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="button" name="btnBack" value="B a c k" class="button" onClick="document.location.href=\''.$backPage.'\'">';
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