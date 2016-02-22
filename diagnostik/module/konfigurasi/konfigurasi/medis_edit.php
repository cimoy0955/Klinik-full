<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($APLICATION_ROOT."library/view.cls.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	$auth = new CAuth();
     $err_code = 0;
     
     $viewPage = "medis_view.php";
     $editPage = "medis_edit.php";
  
	
     if(!$auth->IsAllowed("diagnostik",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("diagnostik",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["petugas_id"])  $poliId = & $_POST["petugas_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $poliId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.*, a.dep_nama, b.pgw_nama as dokter1, c.pgw_nama as dokter2 , d.pgw_nama as dokter3 , e.pgw_nama as perawat1 , 
f.pgw_nama as perawat2, g.pgw_nama as perawat3 , h.pgw_nama as perawat4, i.pgw_nama as perawat5
from global.global_petugas a
left join hris.hris_pegawai b on b.pgw_id = a.dokter_1
left join hris.hris_pegawai c on c.pgw_id = a.dokter_2
left join hris.hris_pegawai d on d.pgw_id = a.dokter_3
left join hris.hris_pegawai e on e.pgw_id = a.perawat_1
left join hris.hris_pegawai f on f.pgw_id = a.perawat_2
left join hris.hris_pegawai g on g.pgw_id = a.perawat_3
left join hris.hris_pegawai h on h.pgw_id = a.perawat_4
left join hris.hris_pegawai i on i.pgw_id = a.perawat_5
				where petugas_id = ".QuoteValue(DPE_CHAR,$poliId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
           

          
          $_POST["id_app"] = $row_edit["id_app"];
           $_POST["dep_nama"] = $row_edit["dep_nama"];
          $_POST["dokter1"] = $row_edit["dokter1"];
          $_POST["dokter2"] = $row_edit["dokter2"];
          $_POST["dokter3"] = $row_edit["dokter3"];
          $_POST["perawat1"] = $row_edit["perawat1"];
          $_POST["perawat2"] = $row_edit["perawat2"];
          $_POST["perawat3"] = $row_edit["perawat3"];
          $_POST["perawat4"] = $row_edit["perawat4"];
          $_POST["perawat5"] = $row_edit["perawat5"];
          
          $_POST["dokter_1"] = $row_edit["dokter_1"];
          $_POST["dokter_2"] = $row_edit["dokter_2"];
          $_POST["dokter_3"] = $row_edit["dokter_3"];
          $_POST["perawat_1"] = $row_edit["perawat_1"];
          $_POST["perawat_2"] = $row_edit["perawat_2"];
          $_POST["perawat_3"] = $row_edit["perawat_3"];
          $_POST["perawat_4"] = $row_edit["perawat_4"];
          $_POST["perawat_5"] = $row_edit["perawat_5"];

          
     
     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $poliId = & $_POST["petugas_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $dbTable = "global.global_petugas";
               
               $dbField[0] = "petugas_id";   // PK
               $dbField[1] = "dep_nama";
               $dbField[2] = "dokter_1";
               $dbField[3] = "dokter_2";
               $dbField[4] = "dokter_3";
               $dbField[5] = "perawat_1";
               $dbField[6] = "perawat_2";
               $dbField[7] = "perawat_3";
               $dbField[8] = "perawat_4";
               $dbField[9] = "perawat_5";
               
			         
               if(!$poliId) $poliId = $dtaccess->GetNewID("global.global_petugas","petugas_id",DB_SCHEMA);   
               $dbValue[0] = QuoteValue(DPE_CHAR,$poliId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["dep_nama"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["dokter_1"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["dokter_2"]);
               $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["dokter_3"]);
               $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["perawat_1"]);
               $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["perawat_2"]);
               $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["perawat_3"]);
               $dbValue[8] = QuoteValue(DPE_NUMERIC,$_POST["perawat_4"]);
               $dbValue[9] = QuoteValue(DPE_NUMERIC,$_POST["perawat_5"]);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
               
                  header("location:".$viewPage);
                  exit();
          }
     }
 
    if ($_GET["del"]) {
          $poliId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_petugas where petugas_id = ".QuoteValue(DPE_CHAR,$poliId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
           
    
          header("location:".$viewPage);
          exit();    
     }
     
     // -- cari dokter -- //
     $sql = "select pgw_id, pgw_nama from hris.hris_pegawai where pgw_jenis_pegawai = ".PGW_JENIS_DOKTER;
     //echo $sql;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataDokter = $dtaccess->FetchAll($rs);
     
     // -- cari suster -- //
     $sql = "select pgw_id, pgw_nama from hris.hris_pegawai where pgw_jenis_pegawai = ".PGW_JENIS_SUSTER;
     //echo $sql;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataSuster = $dtaccess->FetchAll($rs);
     
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>


<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Tenaga Medis </td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Jenis Poli</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("dep_nama","dep_nama","50","100",$_POST["dep_nama"],"inputField", "readonly",false);?>
 <!-- <input type="hidden" id="id_app" name="id_app" value="<?php echo $_POST["id_app"];?>"/>  -->                 
               </td>
          </tr> 
	<tr>

		<td align="right" class="tablecontent" width="30%">Dokter 1</td>
          <td width="70%">
			<select name="dokter_1" id="dokter_1" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Dokter 1]</option>
				<?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataDokter[$i]["pgw_id"];?>" <?php if($dataDokter[$i]["pgw_id"]==$_POST["dokter_1"]) echo "selected"; ?>><?php echo $dataDokter[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" class="tablecontent" width="30%">Dokter 2</td>
          <td width="70%">
			<select name="dokter_2" id="dokter_2" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Dokter 2]</option>
				<?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataDokter[$i]["pgw_id"];?>" <?php if($dataDokter[$i]["pgw_id"]==$_POST["dokter_2"]) echo "selected"; ?>><?php echo $dataDokter[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>

	<tr>
		<td align="right" class="tablecontent" width="30%">Dokter 3</td>
          <td width="70%">
			<select name="dokter_3" id="dokter_3" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Dokter 3]</option>
				<?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataDokter[$i]["pgw_id"];?>" <?php if($dataDokter[$i]["pgw_id"]==$_POST["dokter_3"]) echo "selected"; ?>><?php echo $dataDokter[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>

	<tr>
		<td align="right" class="tablecontent" width="30%">Perawat 1</td>
          <td width="70%">
			<select name="perawat_1" id="perawat_1" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 1]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_1"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>

	<tr>
		<td align="right" class="tablecontent" width="30%">Perawat 2</td>
          <td width="70%">
			<select name="perawat_2" id="perawat_2" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 2]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_2"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>
	
    <tr>
		<td align="right" class="tablecontent" width="30%">Perawat 3</td>
          <td width="70%">
			<select name="perawat_3" id="perawat_3" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 3 ]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_3"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>
		
    <tr>
		<td align="right" class="tablecontent" width="30%">Perawat 4</td>
          <td width="70%">
			<select name="perawat_4" id="perawat_4" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 4]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_4"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>

    <tr>
		<td align="right" class="tablecontent" width="30%">Perawat 5</td>
          <td width="70%">
			<select name="perawat_5" id="perawat_5" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 5]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_5"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>
          <tr>
               <td colspan="2" align="right">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='".$viewPage."';\"");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.poli_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("petugas_id","petugas_id",$poliId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
