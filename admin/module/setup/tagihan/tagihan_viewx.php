<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $enc = new TextEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","100%","center");
     
     $aktif["y"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_okay.png" alt="OK" title="OK" border="0">';
     $aktif["n"] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/s_nokay.png" alt="OK" title="OK" border="0">';
     
     // -- paging config ---//
     $recordPerPage = 50;
     if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
     else $currPage = 1;
     $startPage = ($currPage-1)*$recordPerPage;
     $endPage = $startPage + $recordPerPage;
     // -- end paging config ---//
     
     $editPage   = "tagihan_edit.php";
     $thisPage   = "tagihan_view.php";
     
 	if(!$auth->IsAllowed("setup_tagihan",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("setup_tagihan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
     
     $isAllowedDel = $auth->IsAllowed("setup_tagihan",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_tagihan",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_tagihan",PRIV_CREATE);
     
     if(!$_POST["btnSearch"]) $cari = 1;
     
     if(isset($_POST["btnSearch"]) || $cari || ($_GET["_search"]=="yes")) {
          
          // -- search Nama tagihan ---
          if($_POST["b_tambahan_nama"]) $sql_where[] = "WHERE UPPER(a.b_tambahan_nama) like '".strtoupper($_POST["b_tambahan_nama"])."%'";

          $sql = "select a.* from klinik.klinik_biaya_tambahan a "; 
          if($sql_where){
               $sql .= $sql_where;
          }
          $sql .= " order by UPPER(a.b_tambahan_nama)";
               
          $rs = $dtaccess->Query($sql,$recordPerPage,$startPage,DB_SCHEMA);
          $dataTable = $dtaccess->FetchAll($rs); //echo $sql;
     
          $sql = "select count(a.b_tambahan_id) as total from klinik.klinik_biaya_tambahan a 
                    where a.b_tambahan_id is not null ";
          $rsNum = $dtaccess->Execute($sql,DB_SCHEMA);
          $numRows = $dtaccess->Fetch($rsNum);
          
          //$numRows["total"] = count($dataTable);
          
          if($endPage>$numRows["total"]) $endPage = $numRows["total"];
          $strRecord = ($startPage+1)." - ".$endPage." from ".$numRows["total"]." records";
     
     }
    
     //*-- config table ---*//
     $tableHeader = "&nbsp;Biaya Tambahan";
     
     // --- construct new table ---- //
     $counter=0;
     if($isAllowedDel){
          $tbHeader[0][$counter][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[0][$counter][TABLE_WIDTH] = "1%";
          $counter++;
     }
     
     if($isAllowedUpdate){
          
          $tbHeader[0][$counter][TABLE_ISI] = "Edit";
          $tbHeader[0][$counter][TABLE_WIDTH] = "1%"; 
          $counter++;  
     }
     
     $tbHeader[0][$counter][TABLE_ISI] = "Nama";
     $tbHeader[0][$counter][TABLE_WIDTH] = "30%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI] = "JASMED";
     $tbHeader[0][$counter][TABLE_WIDTH] = "15%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI] = "Operasional";
     $tbHeader[0][$counter][TABLE_WIDTH] = "15%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI] = "Total";
     $tbHeader[0][$counter][TABLE_WIDTH] = "15%";
     $counter++;
     
          
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["b_tambahan_id"].'">';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["b_tambahan_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["b_tambahan_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "Rp. ".currency_format($dataTable[$i]["b_tambahan_jasmed"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "Rp. ".currency_format($dataTable[$i]["b_tambahan_ops"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "Rp. ".currency_format($dataTable[$i]["b_tambahan_harga"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          
     }

          
     if($isAllowedDel && !$isAllowedCreate){
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$strRecord;
     }
     if($isAllowedCreate && !$isAllowedDel){
          $tbBottom[0][0][TABLE_ISI] = '<input type="button" name="btnAdd" value="Tambah" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$strRecord;
     }
     if($isAllowedDel && $isAllowedCreate){
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;<input type="button" name="btnAdd" value="Tambah" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$strRecord;
     }
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);

     
     if ($_POST["btnDelete"]) {
		$biayaId = & $_POST["cbDelete"];
		for($i=0,$n=count($biayaId);$i<$n;$i++) {
			$sql = "delete from klinik.klinik_biaya_tambahan 
                         where b_tambahan_id = '".$biayaId[$i]."'";            
			$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		}

		echo "<script>document.location.href='tagihan_view.php';</script>";
		exit();     
	}

?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="JavaScript">

function DeleteDetil() {
	if(confirm('Anda Yakin Ingin Menghapus Obat ?')){
		document.frmView.btnDelete.value = 'Hapus';
		document.location.href='tagihan_view.php';
	}
}
</script>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
        <td><?php echo $tableHeader;?></td>
    </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
          <fieldset>
          <legend><strong>Cari</strong></legend>
          <table width="100%" border="1" cellpadding="1" cellspacing="1">
               <tr>
                    <td align="right" class="tablecontent"><strong>Nama</strong></td>
                    <td>
                         <?php echo $view->RenderTextBox("b_tambahan_nama","b_tambahan_nama","50","100",$_POST["b_tambahan_nama"],null, null, false);?>
                    &nbsp;<?php echo $view->RenderButton(BTN_SUBMIT,"btnSearch","btnSearch","Search","button",false);?>
                    </td>
               </tr>
          </table>
          </fieldset>
     </td>
</tr>
</table>

<?php echo $view->SetFocus("b_tambahan_nama");?>
<input type="submit" name="btnDelete" value="Hapus" class="button" OnClick="javascript:DeleteDetil();">
<?php echo $view->RenderButton(BTN_BUTTON,"btnAdd","btnAdd","Tambah","button",false,"onClick=\"window.document.location.href='$editPage'\"");?>&nbsp;
<?php echo $view->RenderPaging($numRows["total"],$recordPerPage, $currPage); ?>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>       
</form>

<?php echo $view->RenderBodyEnd();?>
