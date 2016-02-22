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
 
     $editPage = "obat_edit.php";
     $thisPage = "obat_view.php";

     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     $sql = "select a.*,b.kategori_nama from apotik_obat_master a 
             left join apotik_obat_kategori b on b.kategori_id=a.id_kategori
               order by b.kategori_id ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Kategori Obat";
     
     $isAllowedDel = $auth->IsAllowed("setup_role",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_role",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_role",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
      
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
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++; 
             
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Stok";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++; 
           
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Harga Beli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++; 
           
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Harga Jual";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++; 
       
     for($i=0,$j=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$j++,$counter=0){
      if($dataTable[$i]["kategori_nama"]!=$dataTable[$i-1]["kategori_nama"]){
       $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["kategori_nama"];               
       $tbContent[$j][$counter][TABLE_ALIGN] = "center";
       $tbContent[$j][$counter][TABLE_COLSPAN] = count($tbHeader[0]);
       $counter=0; $j++; $no=1;
      }
     
       $tbContent[$j][$counter][TABLE_ISI] = $no;               
       $tbContent[$j][$counter][TABLE_ALIGN] = "center";
       $counter++;$no++;
       
          if($isAllowedDel) {
               $tbContent[$j][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["obat_id"].'">';               
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate) {
               $tbContent[$j][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["obat_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["obat_nama"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["obat_stok"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++; 
     
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["obat_harga_beli"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++; 
     
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["obat_harga_jual"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $counter++; 
     
     }
     
     $colspan = count($tbHeader[0]);

     
     if($isAllowedDel) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
     }
     
     if($isAllowedCreate) {
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" id="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
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

<?php echo $view->SetFocus("btnAdd"); ?>
<?php echo $view->RenderBodyEnd(); ?>
