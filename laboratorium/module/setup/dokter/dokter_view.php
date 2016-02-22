<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
	   require_once($APLICATION_ROOT."library/view.cls.php");	
     require_once($APLICATION_ROOT."library/config/global.cfg.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $editPage = "dokter_edit.php";
     $thisPage = "dokter_view.php";
     $bonusPage = "dokter_bonus.php?";

     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     $sql = "select * from lab_dokter  
             order by dokter_nama ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Daftar Dokter Lab";
     
     $isAllowedDel = $auth->IsAllowed("laboratorium",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("laboratorium",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("laboratorium",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
        
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Divisi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "23%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jabatan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "23%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Setup Bonus";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++; 
     
     
     for($i=0,$j=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$j++,$counter=0){
          if($isAllowedDel) {
               $tbContent[$j][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["dokter_id"].'">';               
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate) {
               $tbContent[$j][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["dokter_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["dokter_nama"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $counter++; 
     
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$divisi_dokter[$dataTable[$i]["id_divisi"]];
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $counter++; 
     
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["dokter_jabatan"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $counter++; 
          
          $tbContent[$j][$counter][TABLE_ISI] = "<a href=\"".$bonusPage."id=".$enc->Encode($dataTable[$i]["dokter_id"])."\" title=\"Edit Bonus Dokter\"><img hspace=\"2\" width=\"16\" height=\"16\" src=\"".$APLICATION_ROOT."images/b_bayar.gif\" alt=\"Edit Bonus\" title=\"Edit Bonus\" border=\"0\"></a>";
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
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
