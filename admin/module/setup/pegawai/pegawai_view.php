<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();     
     $table = new InoTable("table1","100%","center");
     
     
     if(!$auth->IsAllowed("setup_pegawai",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("setup_pegawai",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
     
     
     $editPage = "pegawai_edit.php";
     $thisPage = "pegawai_view.php";
     
     
     $sql = "select * from hris_pegawai order by pgw_nip";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataTable = $dtaccess->FetchAll($rs);
     
     
     //*-- config table ---*//
     $PageHeader = "Data Pegawai";
     
     // --- construct new table ---- //
     $tbHeader[0][0][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
     $tbHeader[0][0][TABLE_WIDTH] = "5%";
     
     $tbHeader[0][1][TABLE_ISI] = "Edit";
     $tbHeader[0][1][TABLE_WIDTH] = "5%";
     
     $tbHeader[0][2][TABLE_ISI] = "ID Number";
     $tbHeader[0][2][TABLE_WIDTH] = "20%";
     
     $tbHeader[0][3][TABLE_ISI] = "Nama";
     $tbHeader[0][3][TABLE_WIDTH] = "20%";
     
     $tbHeader[0][4][TABLE_ISI] = "Alamat";
     $tbHeader[0][4][TABLE_WIDTH] = "55%";
     
     $tbHeader[0][5][TABLE_ISI] = "Telp";
     $tbHeader[0][5][TABLE_WIDTH] = "15%";
          
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
         
          $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["pgw_id"].'">';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["pgw_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pgw_nip"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pgw_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = ($dataTable[$i]["pgw_alamat_surabaya"])?$dataTable[$i]["pgw_alamat_surabaya"]:$dataTable[$i]["pgw_alamat_asal"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = ($dataTable[$i]["pgw_alamat_surabaya"])?$dataTable[$i]["pgw_telp_surabaya"]:$dataTable[$i]["pgw_telp_asal"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          //$tbContent[$i][$counter][TABLE_NOWRAP] = true;
     }
     
     $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;<input type="button" name="btnAdd" value="Tambah" class="button" onClick="document.location.href=\''.$editPage.'\'">';
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = 6;
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td>&nbsp;<?php echo $PageHeader;?></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>    
</form>
<?php echo $view->RenderBodyEnd(); ?>