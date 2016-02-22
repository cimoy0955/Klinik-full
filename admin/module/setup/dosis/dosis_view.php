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

     
     $editPage   = "dosis_edit.php";
     $thisPage   = "dosis_view.php";

 	if(!$auth->IsAllowed("setup_dosis",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("setup_dosis",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
     
     $isAllowedDel = $auth->IsAllowed("setup_dosis",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_dosis",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_dosis",PRIV_CREATE);
     
     if(!$_POST["btnSearch"]) $cari = 1;
     
     if(isset($_POST["btnSearch"]) || $cari || ($_GET["_search"]=="yes")) {

          // -- search Nama Item ---
          if($_POST["dosis_nama"]) $sql_where[] = "a.dosis_nama like '".$_POST["dosis_nama"]."%'";

          if($_POST["id_fisik"] && $_POST["id_fisik"]!="") $sql_where[] = "a.id_fisik= ".QuoteValue(DPE_CHAR,$_POST["id_fisik"]);
          

          $sql = "select a.*,b.fisik_nama from inventori.inv_dosis a 
                    left join inventori.inv_fisik b on b.fisik_id = a.id_fisik"; 
          if($sql_where){
               $sql_where = implode(" and ",$sql_where);
               $sql = $sql ." where ".$sql_where;
          }
          $sql .= " order by UPPER(a.dosis_nama)";
               
          $rs = $dtaccess->Execute($sql,DB_SCHEMA);
          $dataTable = $dtaccess->FetchAll($rs); //echo $sql;
     
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
     
     $tbHeader[0][$counter][TABLE_ISI] = "Nama";
     $tbHeader[0][$counter][TABLE_WIDTH] = "40%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI] = "Bentuk";
     $tbHeader[0][$counter][TABLE_WIDTH] = "20%";
     $counter++;

     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["dosis_id"].'">';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["dosis_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dosis_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fisik_nama"];
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
		$dosisId = & $_POST["cbDelete"];
		for($i=0,$n=count($dosisId);$i<$n;$i++) {
			$sql = "delete from inventori.inv_dosis 
                         where dosis_id = ".QuoteValue(DPE_CHAR,$dosisId[$i]);            
			$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		}

		echo "<script>document.location.href='dosis_view.php';</script>";
		exit();     
	}

     
     // -- cari Supplier ---
     $sql = "select fisik_id,fisik_nama from inventori.inv_fisik
               order by fisik_id";
     $rs = $dtaccess->Execute($sql);
     $dataFisik = $dtaccess->FetchAll($rs);

     $fisik[0] = $view->RenderOption("","All",$show);
     for($i=0,$n=count($dataFisik);$i<$n;$i++) {
          unset($show);
          if($_POST["id_fisik"]==$dataFisik[$i]["fisik_id"]) $show = "selected";
          $fisik[$i+1] = $view->RenderOption($dataFisik[$i]["fisik_id"],$dataFisik[$i]["fisik_nama"],$show);
     }
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="JavaScript">

function DeleteDetil() {
	if(confirm('Anda Yakin Ingin Menghapus Dosis ?')){
		document.frmView.btnDelete.value = 'Hapus';
		document.location.href='dosis_view.php';
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
                         <?php echo $view->RenderTextBox("dosis_nama","dosis_nama","50","100",$_POST["dosis_nama"],null, null, false);?>
                    </td>
               </tr>
               <tr>
                    <td width="20%" align="right" class="tablecontent"><strong>Bentuk</strong></td>
                    <td>
                         <?php echo $view->RenderComboBox("id_fisik","id_fisik",$fisik,null,null);?>
                         &nbsp;<?php echo $view->RenderButton(BTN_SUBMIT,"btnSearch","btnSearch","Search","button",false);?>
                    </td>
               </tr>
          </table>
          </fieldset>
     </td>
</tr>
</table>

<?php echo $view->SetFocus("dosis_kode");?>
<input type="submit" name="btnDelete" value="Hapus" class="button" OnClick="javascript:DeleteDetil();">
<?php echo $view->RenderButton(BTN_BUTTON,"btnAdd","btnAdd","Tambah","button",false,"onClick=\"window.document.location.href='$editPage'\"");?>&nbsp;

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>       
</form>

<?php echo $view->RenderBodyEnd();?>
