<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     require_once($ROOT."library/inoLiveX.php");
	require_once($ROOT."library/upload.func.php");
     
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
	$errorupload = 0;
      
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
      
     
     if(!$auth->IsAllowed("setup_help",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_help",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
     else $_x_mode = "New";
     $skr = date('Y-m-d H:i:s');
     $nama[1] = "Alur";
     $nama[2] = "User Guide";
     $nama[3] = "Training Kit";
     
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $tunjukId = $enc->Decode($_GET["id"]);
          }
     
               $sql = "select * 
                         from global.global_petunjuk 
                         where tunjuk_id = ".$tunjukId."";
               $rs = $dtaccess->Execute($sql,DB_SCHEMA);
               $row_edit = $dtaccess->Fetch($rs);
               
               $_POST["tunjuk_id"] = $row_edit["tunjuk_id"];
               $_POST["tunjuk_file"] = $row_edit["tunjuk_file"];
               $_POST["tunjuk_ket"] = $row_edit["tunjuk_ket"]; 
               $_POST["tunjuk_nama"] = $row_edit["tunjuk_nama"]; 
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
               $tunjukId = & $_POST["tunjuk_id"];
               $_x_mode = "Edit";
          }
          
          if($_GET["orifoto"]) $oriFotoAlur = $_GET["orifoto"];
          elseif($_POST["orifoto"]) $oriFotoAlur = $_POST["orifoto"];
          
          if($_GET["nama"]) $namaFotoAlur = & $_GET["nama"];
          elseif($_POST["nama"]) $namaFotoAlur = & $_POST["nama"];
          $lokasi = $APLICATION_ROOT."files/";
          $maxSize = 9000000000000;
          
          $tempAlur = explode("_",$oriFotoAlur);
          $counter = ($temp[2]+1);
          
          if($_FILES["fotopas"]["tmp_name"]){
               $destName = $_FILES["fotopas"]["name"];
               if(CheckUpload($_FILES["fotopas"], $lokasi, $maxSize, $destName)){
                    $errorupload = 0; 
                    if($oriFoto) unlink($lokasi."/".$oriFoto);
               } else $errorupload = 1;
               echo $destName;
          }
          
          if ($err_code == 0) {
               
               $dbTable = "global.global_petunjuk";
               
               $dbField[0] = "tunjuk_id";   // PK 
               $dbField[1] = "tunjuk_file";
               $dbField[2] = "tunjuk_ket";  
               $dbField[3] = "tunjuk_nama";  
               
				if(!$destName)
					$destName = $_POST["tunjuk_file"]; 
                                             
                    if(!$tunjukId) $tunjukId = $dtaccess->GetNewID("global.global_petunjuk","tunjuk_id",DB_SCHEMA);
                    $dbValue[0] = QuoteValue(DPE_NUMERIC,$tunjukId); 
                    $dbValue[1] = QuoteValue(DPE_CHAR,$destName);
                    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["tunjuk_ket"]);
                    $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["tunjuk_nama"]);
                    
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               header("location:petunjuk_view.php");
               exit(); 
          }
     
     }
     if ($_POST["btnDelete"]) {
          $tunjukId = & $_POST["cbDelete"];
          for($i=0,$n=count($tunjukId);$i<$n;$i++){
               $sql = "delete from global.global_petunjuk where tunjuk_id = ".$tunjukId[$i];
               $dtaccess->Execute($sql,DB_SCHEMA);
          }
          
          header("location:petunjuk_view.php");
          exit();    
     }     
     
?>
<?php echo $view->RenderBody("inosoft.css",false); ?>
<form name="frmEdit" method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
	<table align="center" border="0" cellpadding="4" cellspacing="1" width="100%" class="tblForm">
		<tr>
		  <td colspan="2" class="tableheader">Petunjuk Modul Caremax</td>
		</tr>
		<tr>
			<td width="15%" align="left" class="tablecontent"><strong>Modul</strong></td>
			<td width="35%">
				<select name="tunjuk_ket" class="inputField" onKeyDown="return tabOnEnter(this, event);">
					<?php for($i=1,$n=4;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["tunjuk_ket"]) echo "selected"; ?>><?php echo $nama[$i];?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td width="15%" align="left" class="tablecontent"><strong>Nama</strong></td>
			<td width="35%">
				<input type="text" size="45" maxleght="100" name="tunjuk_nama" value="<?php echo $_POST["tunjuk_nama"];?>" class="inputField">
			</td>
		</tr>
		<tr>
			<td class="tablecontent">File Upload</td>
			<td>
					   <input class="inputField" type="file" name="fotopas" size="60">
					   <br><label><?php echo $_POST["tunjuk_file"];?></label>
						<input type="hidden" name="orifoto" value="<?php echo $oriFoto;?>">
						<input type="hidden" name="nama" value="<?php echo $namaFoto;?>">
					<input type="hidden" name="tunjuk_file" value="<?php echo $_POST["tunjuk_file"];?>">
			  </td>
		</tr>	
		<tr>
			<td align="right" colspan=3>
				<input type="hidden" name="tunjuk_id" value="<?php echo $tunjukId;?>" /> 
				<input type="submit" name="<? if($_x_mode == "Edit"){?>btnUpdate<?}else{?>btnSave<? } ?>" value="Simpan" class="inputField"/>
				<input type="button" name="btnNew" value="Kembali" class="inputField" onClick="document.location.href='petunjuk_view.php'"/>
			</td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>
</table>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />
<script>document.frmEdit.tunjuk_file.focus();</script>
</form>

<? if ($err_code != 0) { ?>
     <font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? } ?>

<?php echo $view->RenderBodyEnd(); ?>
