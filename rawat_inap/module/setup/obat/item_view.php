<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
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
     
     $editPage   = "item_edit.php";
     $detailPage = "item_detail.php";
     $printPage = "item_print.php";
     $rakitanPage = "item_rakitan.php";
     $historiPage = "item_histori.php";
     $thisPage   = "item_view.php";
     $variasiPage = "item_variasi.php";
     $saldoAwalPage = "item_saldo_awal.php";
     


 	if(!$auth->IsAllowed("item",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("item",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
     
     $isAllowedDel = $auth->IsAllowed("item",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("item",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("item",PRIV_CREATE);
     
     if(!$_POST["btnSearch"]) $cari = 1;
     
     if(isset($_POST["btnSearch"]) || $cari || ($_GET["_search"]=="yes")) {
          // -- search Kode Item ---
          if($_POST["item_kode"]) $sql_where[] = "a.item_kode = ".QuoteValue(DPE_CHAR,$_POST["item_kode"]);
          
          // -- search Nama Item ---
          if($_POST["item_nama"]) $sql_where[] = "a.item_nama like '".$_POST["item_nama"]."%'";

          if($_POST["id_kat_item"] && $_POST["id_kat_item"]!="") $sql_where[] = "a.id_kat_item= ".QuoteValue(DPE_CHAR,$_POST["id_kat_item"]);
          

          $sql = "select a.*,b.kat_item_nama from inventori.inv_item a 
                    left join inventori.inv_kat_item b on b.kat_item_id = a.id_kat_item"; 
          if($sql_where){
               $sql_where = implode(" and ",$sql_where);
               $sql = $sql ." where ".$sql_where;
          }
          $sql .= " order by UPPER(a.item_nama), a.item_kode";
               
          $rs = $dtaccess->Query($sql,$recordPerPage,$startPage,DB_SCHEMA);
          $dataTable = $dtaccess->FetchAll($rs); //echo $sql;
     
          $sql = "select count(a.item_id) as total from inventori.inv_item a 
                    left join inventori.inv_kat_item b on b.kat_item_id = a.id_kat_item 
                    left join inventori.inv_item_supplier c on c.id_item = a.item_id 
                    where a.item_id is not null ";
          // -- search Kode Item ---
          if($sql_where) $sql = $sql ." and ".$sql_where;
          
          $rsNum = $dtaccess->Execute($sql,DB_SCHEMA);
          $numRows = $dtaccess->Fetch($rsNum);
          
          //$numRows["total"] = count($dataTable);
          
          if($endPage>$numRows["total"]) $endPage = $numRows["total"];
          $strRecord = ($startPage+1)." - ".$endPage." from ".$numRows["total"]." records";
     
     }
    
     //*-- config table ---*//
     $tableHeader = "&nbsp;Obat";
     
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
     
     $tbHeader[0][$counter][TABLE_ISI] = "Kode";
     $tbHeader[0][$counter][TABLE_WIDTH] = "15%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI] = "Nama";
     $tbHeader[0][$counter][TABLE_WIDTH] = "40%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI] = "Kategori";
     $tbHeader[0][$counter][TABLE_WIDTH] = "20%";
     $counter++;

     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["item_id"].'">';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["item_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["item_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kat_item_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
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
		$itemId = & $_POST["cbDelete"];
		for($i=0,$n=count($itemId);$i<$n;$i++) {
			$sql = "delete from inventori.inv_item 
                         where item_id = ".$itemId[$i];            
			$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		}

		echo "<script>document.location.href='item_view.php';</script>";
		exit();     
	}

     
     // -- cari Supplier ---
     $sql = "select kat_item_id,kat_item_nama from inventori.inv_kat_item
               order by kat_item_id";
     $rs = $dtaccess->Execute($sql);
     $dataKatItem = $dtaccess->FetchAll($rs);

     $katItem[0] = $view->RenderOption("","All",$show);
     for($i=0,$n=count($dataKatItem);$i<$n;$i++) {
          unset($show);
          if($_POST["id_kat_item"]==$dataKatItem[$i]["kat_item_id"]) $show = "selected";
          $katItem[$i+1] = $view->RenderOption($dataKatItem[$i]["kat_item_id"],$dataKatItem[$i]["kat_item_nama"],$show);
     }
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="JavaScript">

function DeleteDetil() {
	if(confirm('Anda Yakin Ingin Menghapus Obat ?')){
		document.frmView.btnDelete.value = 'Hapus';
		document.location.href='item_view.php';
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
                    <td align="right" class="tablecontent" width="30%"><strong>Kode</strong></td>
                    <td width="70%">
                         <?php echo $view->RenderTextBox("item_kode","item_kode","25","50",$_POST["item_kode"],null, null, false);?>
                    </td>
               </tr>
               <tr>
                    <td align="right" class="tablecontent"><strong>Nama</strong></td>
                    <td>
                         <?php echo $view->RenderTextBox("item_nama","item_nama","50","100",$_POST["item_nama"],null, null, false);?>
                    </td>
               </tr>
               <tr>
                    <td width="20%" align="right" class="tablecontent"><strong>Kategori</strong></td>
                    <td>
                         <?php echo $view->RenderComboBox("id_kat_item","id_kat_item",$katItem,null,null);?>
                         &nbsp;<?php echo $view->RenderButton(BTN_SUBMIT,"btnSearch","btnSearch","Search","button",false);?>
                    </td>
               </tr>
          </table>
          </fieldset>
     </td>
</tr>
</table>

<?php echo $view->SetFocus("item_kode");?>
<input type="submit" name="btnDelete" value="Hapus" class="button" OnClick="javascript:DeleteDetil();">
<?php echo $view->RenderButton(BTN_BUTTON,"btnAdd","btnAdd","Tambah","button",false,"onClick=\"window.document.location.href='$editPage'\"");?>&nbsp;
<?php echo $view->RenderPaging($numRows["total"],$recordPerPage, $currPage); ?>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>       
</form>

<?php echo $view->RenderBodyEnd();?>
