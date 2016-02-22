<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/tree.cls.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
	$tree = new CTree("global.global_customer","cust_id",TREE_LENGTH);
     $userData = $auth->GetUserData();
     
     if(!$auth->IsAllowed("tindakan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("tindakan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "bedah_view.php";
     $editPage = "bedah.php?";
     $findPage = "pasien_find.php?";
     
     if($_GET["id_cust_usr"]) $_POST["cust_usr_id"] = $enc->Decode($_GET["id_cust_usr"]);
     	
	if($_POST["cust_usr_kode"]) {
		$sql = "select cust_usr_id, cust_usr_nama from global.global_customer_user a where a.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
          $_POST["cust_usr_id"] = $dataPasien["cust_usr_id"];
     }

	if($_POST["cust_usr_id"]) {
		$sql = "select cust_usr_id, cust_usr_nama,cust_usr_kode  from global.global_customer_user a where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
          $_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];
     }

	
     if($dataPasien) {
          $table = new InoTable("table","40%","left");

          $sql = "select a.op_id,  op_tanggal from klinik.klinik_perawatan_operasi a where a.id_cust_usr = ".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_id"])." 
                    order by a.op_tanggal desc";
          $rs = $dtaccess->Execute($sql);
          $dataTable = $dtaccess->FetchAll($rs);
     
          //*-- config table ---*//
          $tableHeader = "&nbsp;Nama : ".$dataPasien["cust_usr_nama"];
          
          $isAllowedUpdate = $auth->IsAllowed("tindakan",PRIV_UPDATE);
          
          // --- construct new table ---- //
          $colspan = ($isAllowedUpdate) ? 2:1;

          $tbHeader[0][0][TABLE_ISI] = $tableHeader ;
          $tbHeader[0][0][TABLE_WIDTH] = "100%";
          $tbHeader[0][0][TABLE_COLSPAN] = $colspan;


          $counterHeader = 0;
          if($isAllowedUpdate){
               $tbHeader[1][$counterHeader][TABLE_ISI] = "Edit";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "7%";
               $counterHeader++;
          }
               
          $tbHeader[1][$counterHeader][TABLE_ISI] = "Tanggal";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;
          
          for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
               
               if($isAllowedUpdate) {
                    $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["op_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
                    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                    $counter++;
               }
               
               $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["op_tanggal"]);
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
               $counter++;
          }
          
          $colspan = $colspan;
          
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
     }
	     

?>

<?php echo $view->RenderBody("inosoft.css",true); ?>

<?php echo $view->InitThickBox(); ?>

<style type="text/css">
.bDisable{
	color: #0F2F13;
	border: 1px solid #c2c6d3;
	background-color: #e2dede;
}
</style>

<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Data Pasien Bedah Minor</td>
	</tr>
</table> 


	
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
     <tr>
		<td width= "5%" align="left" class="tablecontent">Kode Pasien</td>
		<td width= "50%" align="left" class="tablecontent-odd">
               <input  type="text" name="cust_usr_kode" id="cust_usr_kode" size="25" maxlength="25" value="<?php echo $_POST["cust_usr_kode"];?>"/>
               <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
               <input type="submit" name="btnLanjut" value="Lanjut" class="button"/>
          </td>
</table>
<?php if(!$dataPasien["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>Kode Pasien Tidak Ditemukan</strong></font>
<?php } ?>

<script>document.frmFind.cust_usr_kode.focus();</script>

</form>

<?php if($dataPasien["cust_usr_id"] || $_POST["btnAdd"]) { ?>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data"  onSubmit="return CheckSimpan(this)">


<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</form>
<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
