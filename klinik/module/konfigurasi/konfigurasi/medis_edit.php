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
  
	
     if(!$auth->IsAllowed("klinik",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("klinik",PRIV_READ)===1){
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
f.pgw_nama as perawat2, g.pgw_nama as perawat3 , h.pgw_nama as perawat4, i.pgw_nama as perawat5,j.pgw_nama as perawat6,
k.pgw_nama as perawat7,l.pgw_nama as perawat8,m.pgw_nama as perawat9,n.pgw_nama as perawat10,o.pgw_nama as administrasi1,p.pgw_nama as administrasi2,q.pgw_nama as administrasi3
from global.global_petugas a
left join hris.hris_pegawai b on b.pgw_id = a.dokter_1
left join hris.hris_pegawai c on c.pgw_id = a.dokter_2
left join hris.hris_pegawai d on d.pgw_id = a.dokter_3
left join hris.hris_pegawai e on e.pgw_id = a.perawat_1
left join hris.hris_pegawai f on f.pgw_id = a.perawat_2
left join hris.hris_pegawai g on g.pgw_id = a.perawat_3
left join hris.hris_pegawai h on h.pgw_id = a.perawat_4
left join hris.hris_pegawai i on i.pgw_id = a.perawat_5
left join hris.hris_pegawai j on j.pgw_id = a.perawat_6
left join hris.hris_pegawai k on k.pgw_id = a.perawat_7
left join hris.hris_pegawai l on l.pgw_id = a.perawat_8
left join hris.hris_pegawai m on m.pgw_id = a.perawat_9
left join hris.hris_pegawai n on n.pgw_id = a.perawat_10
left join hris.hris_pegawai o on o.pgw_id = a.administrasi_1
left join hris.hris_pegawai p on p.pgw_id = a.administrasi_2
left join hris.hris_pegawai q on q.pgw_id = a.administrasi_3
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
$_POST["perawat6"] = $row_edit["perawat6"];
$_POST["perawat7"] = $row_edit["perawat7"];
$_POST["perawat8"] = $row_edit["perawat8"];
$_POST["perawat9"] = $row_edit["perawat9"];
$_POST["perawat10"] = $row_edit["perawat10"];
$_POST["administrasi2"] = $row_edit["administrasi2"];
$_POST["administrasi3"] = $row_edit["administrasi3"];
$_POST["administrasi1"] = $row_edit["administrasi1"];
          
          $_POST["dokter_1"] = $row_edit["dokter_1"];
          $_POST["dokter_2"] = $row_edit["dokter_2"];
          $_POST["dokter_3"] = $row_edit["dokter_3"];
          $_POST["perawat_1"] = $row_edit["perawat_1"];
          $_POST["perawat_2"] = $row_edit["perawat_2"];
          $_POST["perawat_3"] = $row_edit["perawat_3"];
          $_POST["perawat_4"] = $row_edit["perawat_4"];
          $_POST["perawat_5"] = $row_edit["perawat_5"];
$_POST["perawat_6"] = $row_edit["perawat_6"];
$_POST["perawat_7"] = $row_edit["perawat_7"];
$_POST["perawat_8"] = $row_edit["perawat_8"];
$_POST["perawat_9"] = $row_edit["perawat_9"];
$_POST["perawat_10"] = $row_edit["perawat_10"];
$_POST["administrasi_1"] = $row_edit["administrasi_1"];
$_POST["administrasi_2"] = $row_edit["administrasi_2"];
$_POST["administrasi_3"] = $row_edit["administrasi_3"];

          
     
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
$dbField[10] = "perawat_6";
$dbField[11] = "perawat_7";
$dbField[12] = "perawat_8";
$dbField[13] = "perawat_9";
$dbField[14] = "perawat_10";
$dbField[15] = "administrasi_1";
$dbField[16] = "administrasi_2";
$dbField[17] = "administrasi_3";
               
			         
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
$dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["perawat_6"]);
$dbValue[11] = QuoteValue(DPE_NUMERIC,$_POST["perawat_7"]);
$dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["perawat_8"]);
$dbValue[13] = QuoteValue(DPE_NUMERIC,$_POST["perawat_9"]);
$dbValue[14] = QuoteValue(DPE_NUMERIC,$_POST["perawat_10"]);
$dbValue[15] = QuoteValue(DPE_NUMERIC,$_POST["administrasi_1"]);
$dbValue[16] = QuoteValue(DPE_NUMERIC,$_POST["administrasi_2"]);
$dbValue[17] = QuoteValue(DPE_NUMERIC,$_POST["administrasi_3"]);
			
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
     
     // -- cari suster -- //
     $sql = "select pgw_id, pgw_nama from hris.hris_pegawai where pgw_jenis_pegawai = ".PGW_JENIS_ADMIN;
     //echo $sql;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataAdmin = $dtaccess->FetchAll($rs);
     
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
		<td align="right" class="tablecontent" width="30%">Perawat 6</td>
          <td width="70%">
			<select name="perawat_6" id="perawat_6" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 6]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_6"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>

    <tr>
		<td align="right" class="tablecontent" width="30%">Perawat 7</td>
          <td width="70%">
			<select name="perawat_7" id="perawat_7" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 7]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_7"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>

    <tr>
		<td align="right" class="tablecontent" width="30%">Perawat 8</td>
          <td width="70%">
			<select name="perawat_8" id="perawat_8" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 8]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_8"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>

    <tr>
		<td align="right" class="tablecontent" width="30%">Perawat 9</td>
          <td width="70%">
			<select name="perawat_9" id="perawat_9" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 9]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_9"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>

    <tr>
		<td align="right" class="tablecontent" width="30%">Perawat 10</td>
          <td width="70%">
			<select name="perawat_10" id="perawat_10" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Perawat 10]</option>
				<?php for($i=0,$n=count($dataSuster);$i<$n;$i++){ ?>								
					<option value="<?php echo $dataSuster[$i]["pgw_id"];?>" <?php if($dataSuster[$i]["pgw_id"]==$_POST["perawat_10"]) echo "selected"; ?>><?php echo $dataSuster[$i]["pgw_nama"];?></option><br>
      <?php } ?>      	
			</select>
		</td>
	</tr>

    <tr>
    <td align="right" class="tablecontent" width="30%">Administrasi 1</td>
          <td width="70%">
      <select name="administrasi_1" id="administrasi_1" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Administrasi 1]</option>
        <?php for($i=0,$n=count($dataAdmin);$i<$n;$i++){ ?>                
          <option value="<?php echo $dataAdmin[$i]["pgw_id"];?>" <?php if($dataAdmin[$i]["pgw_id"]==$_POST["administrasi_1"]) echo "selected"; ?>><?php echo $dataAdmin[$i]["pgw_nama"];?></option><br>
      <?php } ?>        
      </select>
    </td>
  </tr>

    <tr>
    <td align="right" class="tablecontent" width="30%">Administrasi 2</td>
          <td width="70%">
      <select name="administrasi_2" id="administrasi_2" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Administrasi 2]</option>
        <?php for($i=0,$n=count($dataAdmin);$i<$n;$i++){ ?>                
          <option value="<?php echo $dataAdmin[$i]["pgw_id"];?>" <?php if($dataAdmin[$i]["pgw_id"]==$_POST["administrasi_2"]) echo "selected"; ?>><?php echo $dataAdmin[$i]["pgw_nama"];?></option><br>
      <?php } ?>        
      </select>
    </td>
  </tr>

    <tr>
    <td align="right" class="tablecontent" width="30%">Administrasi 3</td>
          <td width="70%">
      <select name="administrasi_3" id="administrasi_3" onKeyDown="return tabOnEnter(this, event);">
                    <option value="0">[ Pilih Administrasi 3]</option>
        <?php for($i=0,$n=count($dataAdmin);$i<$n;$i++){ ?>                
          <option value="<?php echo $dataAdmin[$i]["pgw_id"];?>" <?php if($dataAdmin[$i]["pgw_id"]==$_POST["administrasi_3"]) echo "selected"; ?>><?php echo $dataAdmin[$i]["pgw_nama"];?></option><br>
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
