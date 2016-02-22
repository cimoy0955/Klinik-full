<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($ROOT."library/currFunc.lib.php");
	   require_once($APLICATION_ROOT."library/view.cls.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0;
	
	   
	
     if(!$auth->IsAllowed("setup_workstation",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_workstation",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["meja_id"])  $mejaId = & $_POST["meja_id"];

   
   $plx = new InoLiveX("SetWorkstation");
   
   function SetWorkstation($id_workstation) {
          global $dtaccess, $userData, $view;
          
          $sql = "select * from mp_meja
                    where meja_id like '".$id_workstation."'";
          $rs = $dtaccess->Execute($sql);
          $flag = $dtaccess->Fetch($rs);
          $_POST["meja_id"]=$flag["meja_id"];
          $_POST["meja_nama"]=$flag["meja_nama"];
          $hasil .='
          <tr>
               <td align="right" class="tablecontent" width="40%"><strong>Nama Workstation</strong>&nbsp;</td>
               <td width="60%">'.
				            $view->RenderTextBox("meja_nama","meja_nama","50","100",$flag["meja_nama"],"inputField",null,false);
          $hasil .='</td></tr>';
          if ($flag["meja_id"]) $hasil.=$view->RenderHidden("meja_id","meja_id",$flag["meja_id"]);
          else       
             $hasil.=$view->RenderHidden("meja_id","meja_id",$id_workstation); 
             
          $hasil .='
          <tr><td colspan="2" align="right">';
           if ($flag["meja_id"])
             $hasil .='<font color="red">Workstation telah disetting, untuk perubahan atau penghapusan dilakukan di Setup Workstation</font>';
           else
             $hasil .=$view->RenderButton(BTN_SUBMIT, "btnSave","btnSave","Simpan","button",false,"onClick=ConfigMeja()");         
                                        
          $hasil .='</td></tr>';   
          return $hasil;
     }; 
   
  if ($_POST["meja_id"]) {        
  $sql = "select * from multiplayer.mp_meja where meja_id = ".QuoteValue(DPE_CHAR,$_POST["meja_id"]);
  $rs_edit = $dtaccess->Execute($sql);
  $row_edit = $dtaccess->Fetch($rs_edit);
  $dtaccess->Clear($rs_edit);
  if ($row_edit["meja_nama"]){
  $_POST["meja_nama"] = $row_edit["meja_nama"];
  }
  }

  $mejaId=$_POST["meja_id"];
  
  //id trans yang baru
  $mejaIdBaru = $dtaccess->GetTransID(); 
  
   if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
      
         
          if ($err_code == 0) {
               $dbTable = "multiplayer.mp_meja";
               
               $dbField[0] = "meja_id";   // PK
               $dbField[1] = "meja_nama";
               $dbField[2] = "meja_aktif";
               
			 
               
               $dbValue[0] = QuoteValue(DPE_CHAR,$mejaId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["meja_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,'y');
               
			
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
               $simpan=1;
               //header("location:workstation_edit.php");
               //exit();  
          }
     }
 
    
     
     
     

?>

<?php echo $view->RenderBody("inventori.css",false,"onload=ReadFile();"); ?>
<?php echo $view->InitUpload(); ?>


<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function ConfigMeja(){
var fileName = 'c://config.exp';

if (writeToFile(fileName,
'<?php echo $mejaIdBaru;?>'))
alert('Konfigurasi Workstation Berhasil');
}

function ConfigMejaUpdate(){
var fileName = 'c://config.exp';

if (writeToFile(fileName,
'<?php echo $mejaId;?>'))
alert('Konfigurasi Workstation Berhasil');
}

function writeToFile(fn, txt) {
if (window.netscape && navigator.javaEnabled) {

netscape.security.PrivilegeManager.enablePrivilege ('UniversalFileWrite')
;
var f = new java.io.File(fn);
if (f.exists())
if (!confirm('Konfigurasi Workstation Yang lama masih ada. Tetap ingin dirubah?'))
fn = prompt ('new file name: ', fn);
if (fn) {
try {
var fr = new java.io.FileWriter(fn);
fr.write (txt);
fr.close();
return true;
}
catch(e) {
alert('Permission to write to file was denied.');
return false;
}
}
else
return false;
}
else if (document.all) {
var fs = new ActiveXObject('Scripting.FileSystemObject');
if (fs.FileExists(fn))
if (!confirm('file ' + fn + ' exists. Overwrite?'))
fn = prompt ('new file name: ', fn);
if (fn) {
var fr = fs.CreateTextFile (fn, true);
fr.write (txt);
fr.close();
return true;
}
else
return false;
}
}

function ReadFile(){
var fso = new ActiveXObject("Scripting.FileSystemObject");

// Open the file for output.
var filename = "c:\\config.exp";
if (fso.FileExists(filename))
{
f = fso.OpenTextFile(filename, 1);
// Read from the file and display the results.
while (!f.AtEndOfStream)
    {
    var r = f.ReadLine();
    }
  f.Close();
  SetWorkstation(r,'target=dv_workstation');
} else {    
  SetWorkstation('<?php echo $mejaIdBaru;?>','target=dv_workstation');
	}
   
}



</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Manage Workstation</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Setup Workstation</strong></legend>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
          <div id="dv_workstation"><?php  echo SetWorkstation('target=dv_workstation');?><div>
     </table>
     </fieldset>
     </td>
</tr>
</table>


<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
<?php if($simpan) { ?>
    <font color="red"><strong>Konfigurasi telah disimpan</strong></font>
<?php } ?>

</form>

<?php echo $view->RenderBodyEnd(); ?>
