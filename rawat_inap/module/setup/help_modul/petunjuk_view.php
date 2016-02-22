<?php
     require_once("root.inc.php");  
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");  
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $enc = new TextEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","100%","center",null,0,3,1,null,"tblForm"); 
     
     if(!$auth->IsAllowed("setup_help",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_help",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     $editPage = "petunjuk_edit.php";
     $backPage = "petunjuk_view.php";  
     
     $sql = "select * 
               from global.global_petunjuk a  
               order by tunjuk_ket, tunjuk_file"; 
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
          //*-- config table ---*//
     $PageHeader = "Petunjuk Modul Caremax";
     $nama[1] = "Alur";
     $nama[2] = "User Guide";
     $nama[3] = "Training Kit";
     
     $counter = 0;    
     $tbHeader[0][$counter][TABLE_ISI]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
     $tbHeader[0][$counter][TABLE_WIDTH] = "1%";
     $counter++;
      
     $tbHeader[0][$counter][TABLE_ISI]  = "Edit";
     $tbHeader[0][$counter][TABLE_WIDTH] = "1%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI]  = "Jenis";
     $tbHeader[0][$counter][TABLE_WIDTH] = "15%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI]  = "Nama";
     $tbHeader[0][$counter][TABLE_WIDTH] = "20%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI]  = "File";
     $tbHeader[0][$counter][TABLE_WIDTH] = "20%";
     $counter++;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          $class = (($i%2)==0) ? "tblCol":"tblCol-odd";
                    
          $tbContent[$i][$counter][TABLE_ISI]  = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["tunjuk_id"].'"/>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI]  = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["tunjuk_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI]  = $nama[$dataTable[$i]["tunjuk_ket"]]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI]  = $dataTable[$i]["tunjuk_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI]  = $dataTable[$i]["tunjuk_file"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
     }
     
          $tbBottom[0][0][TABLE_ISI] = '<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;<input type="button" name="btnAdd" value="Tambah" class="button" onClick="document.location.href=\''.$editPage.'\'">';
          $tbBottom[0][0][TABLE_COLSPAN] = "5";
          $tbBottom[0][0][TABLE_ALIGN] = "left";
?>
<?php echo $view->RenderBody("inosoft.css",false); ?>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
     <tr class="tableheader">
          <td>&nbsp;<?php echo $PageHeader;?></td>
     </tr>
</table>
<form name="frmView" method="POST" action="<?php echo $editPage; ?>">     
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>
<input type="hidden" name="modul_id" value="<?php echo $_POST["modul_id"];?>"> 
</body>
</html>
 
