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

     $bedPage    = "bed_view.php";
     $editPage   = "kelas_edit.php";
     $thisPage   = "kelas_view.php";

 	if(!$auth->IsAllowed("setup_kamar",PRIV_READ)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("setup_kamar",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
     
     $isAllowedDel = $auth->IsAllowed("setup_kamar",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_kamar",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_kamar",PRIV_CREATE);
     
     //--mencari jumlah bed--//
     $sql_bed = "select id_kategori,count(kamar_id) as total_kategori from klinik.klinik_kamar group by id_kategori";
     $rs_bed = $dtaccess->Execute($sql_bed);
     $data_bed = $dtaccess->FetchAll($rs_bed);
     for($i=0,$j=count($data_bed);$i<$j;$i++){
        $jml_kamar[$data_bed[$i]["id_kategori"]] = $data_bed[$i]["total_kategori"];
     }
     
     if(!$_POST["btnSearch"]) $cari = 1;
     
     if(isset($_POST["btnSearch"]) || $cari || ($_GET["_search"]=="yes")) {

          // -- search Nama kategori ---
         /* if($_POST["kategori_kategori"]) $sql_where[] = "a.kategori_kategori like '".$_POST["kategori_kategori"]."%'";

          if($_POST["id_kategori"] && $_POST["id_kategori"]!="") $sql_where[] = "a.id_kategori= ".QuoteValue(DPE_CHAR,$_POST["id_kategori"]);
          */

          $sql = "select a.* from klinik.klinik_kamar_kategori a "; 
          if($sql_where){
               $sql_where = implode(" and ",$sql_where);
               $sql = $sql ." where ".$sql_where;
          }
          $sql .= " order by UPPER(a.kategori_nama)";
               
          $rs = $dtaccess->Execute($sql);
          $dataTable = $dtaccess->FetchAll($rs); //echo $sql;
     
     }
    
     //*-- config table ---*//
     $tableHeader = "&nbsp;Kategori Rawat Inap";
     
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

     $tbHeader[0][$counter][TABLE_ISI] = "Nama Kategori";
     $tbHeader[0][$counter][TABLE_WIDTH] = "15%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI] = "Harga";
     $tbHeader[0][$counter][TABLE_WIDTH] = "15%";
     $counter++;
     
     $tbHeader[0][$counter][TABLE_ISI] = "Jumlah Kamar";
     $tbHeader[0][$counter][TABLE_WIDTH] = "10%";
     $counter++;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["kategori_id"].'">';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
          if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["kategori_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          }
     
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kategori_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["kategori_harga"])."&nbsp;&nbsp;";
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $jml_kamar[$dataTable[$i]["kategori_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
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
		$kategoriId = & $_POST["cbDelete"];
		for($i=0,$n=count($kategoriId);$i<$n;$i++) {
			$sql = "delete from klinik.klinik_kamar_kategori 
                         where kategori_id = ".QuoteValue(DPE_CHAR,$kategoriId[$i]);            
			$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		}

		echo "<script>document.location.href='kelas_view.php';</script>";
		exit();     
	}

     
     // -- cari Kategori untuk combo box ---
   /*  $sql = "select kategori_id,kategori_nama from klinik.klinik_kamar_kategori
               order by kategori_id";
     $rs = $dtaccess->Execute($sql);
     $datakategori = $dtaccess->FetchAll($rs);

     $kategori[0] = $view->RenderOption("","All",$show);
     for($i=0,$n=count($datakategori);$i<$n;$i++) {
          unset($show);
          if($_POST["id_kategori"]==$datakategori[$i]["kategori_id"]) $show = "selected";
          $kategori[$i+1] = $view->RenderOption($datakategori[$i]["kategori_id"],$datakategori[$i]["kategori_nama"],$show);
     }
     */
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="JavaScript">

function DeleteDetil() {
	if(confirm('Anda Yakin Ingin Menghapus kategori ?')){
		document.frmView.btnDelete.value = 'Hapus';
		document.location.href='kelas_view.php';
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
    <!-- <td>
          <fieldset>
          <legend><strong>Cari</strong></legend>
          <table width="100%" border="1" cellpadding="1" cellspacing="1">
               <tr>
                    <td align="right" class="tablecontent"><strong>Nama</strong></td>
                    <td>
                         <?php //echo $view->RenderTextBox("kategori_nama","kategori_nama","50","100",$_POST["kategori_nama"],null, null, false);?>
                    </td>
               </tr>
               <tr>
                    <td width="20%" align="right" class="tablecontent"><strong>Kategori</strong></td>
                    <td>
                         <?php //echo $view->RenderComboBox("id_kategori","id_kategori",$kategori,null,null);?>
                         &nbsp;<?php //echo $view->RenderButton(BTN_SUBMIT,"btnSearch","btnSearch","Search","button",false);?>
                    </td>
               </tr>
          </table>
          </fieldset>
     </td> -->
</tr>
</table>

<?php echo $view->SetFocus("kategori_kode");?>
<input type="submit" name="btnDelete" value="Hapus" class="button" OnClick="javascript:DeleteDetil();">
<?php echo $view->RenderButton(BTN_BUTTON,"btnAdd","btnAdd","Tambah","button",false,"onClick=\"window.document.location.href='$editPage'\"");?>&nbsp;

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>       
</form>

<?php echo $view->RenderBodyEnd();?>
