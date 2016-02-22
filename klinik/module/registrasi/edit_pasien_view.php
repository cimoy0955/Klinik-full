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
 
     $editPage = "edit_pasien_edit.php";
     $thisPage = "edit_pasien_view.php";

     if(!$auth->IsAllowed("klinik",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("klinik",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } 

	$sql = "select a.reg_status, a.reg_id, b.cust_usr_kode, cust_usr_nama, id_cust_usr 
			from klinik.klinik_registrasi a
			join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
			where reg_status not like '".STATUS_SELESAI."%' 
			order by reg_status, cust_usr_kode "; 
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
	  
     //*-- config table ---*//
     $tableHeader = "&nbsp;Edit Status Pasien";
     
     $isAllowedDel = $auth->IsAllowed("edit_status_pasien",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("edit_status_pasien",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("edit_status_pasien",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;      
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.Reg";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++; 
	
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++;
	
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
      
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){ 
          
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["reg_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
		
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
		
          $tbContent[$i][$counter][TABLE_ISI] = $rawatStatus[$dataTable[$i]["reg_status"]{0}];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
     }
     
     $colspan = count($tbHeader[0]);
 
     $tbBottom[0][0][TABLE_ISI] = "&nbsp;";
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
