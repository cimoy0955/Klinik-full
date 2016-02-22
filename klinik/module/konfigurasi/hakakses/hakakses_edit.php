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
     $userData = $auth->GetUserData();
     $err_code = 0;
       
     if(!$auth->IsAllowed("setup_hakakses",PRIV_READ)){
          die("access_denied");
          exit(1);
     }
  
     if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
     else $_x_mode = "New";
   
     if($_POST["usr_id"])  $usrId = & $_POST["usr_id"];   
   
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $usrId = $enc->Decode($_GET["id"]);
          }
          
          $sql = "select * from global_auth_user where usr_id = ".$usrId;
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["usr_loginname"] = $row_edit["usr_loginname"];
          $_POST["usr_name"] = $row_edit["usr_name"];
          $_POST["id_rol"] = $row_edit["id_rol"];
          $_POST["usr_status"] = $row_edit["usr_status"];
          $_POST["usr_when_create"] = $row_edit["usr_when_create"];
          $_POST["usr_app_def"] = $row_edit["usr_app_def"];
          
          $sql = "select * from global_auth_user_app
                    where id_usr = ".$usrId."
                    order by id_app";
          $rs = $dtaccess->Execute($sql);
          $dataUsrApp = $dtaccess->FetchAll($rs);
          
          for($i=0,$n=count($dataUsrApp);$i<$n;$i++){
               $_POST["id_app"][$dataUsrApp[$i]["id_app"]] = $dataUsrApp[$i]["id_app"];               
          }
     }
  
     if($_x_mode=="New") $privMode = PRIV_CREATE;
     elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
     else $privMode = PRIV_DELETE;
  
     if(!$auth->IsAllowed("setup_hakakses",$privMode)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("setup_hakakses",$privMode)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }
  
     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
        
          if($_POST["btnUpdate"]){
               $usrId = & $_POST["usr_id"];
               $_x_mode = "Edit";
          }
         
          $err_code = 31;
          //--- Checking Data ---//
          if ($_POST["usr_loginname"]) $err_code = clearbit($err_code,1); 
          else $err_code = setbit($err_code,1);
          
          if ($_POST["btnSave"]) 
               $sql = sprintf("SELECT usr_id FROM global_auth_user WHERE usr_loginname = '%s'",$_POST["usr_loginname"]);
          else
               $sql = sprintf("SELECT usr_id FROM global_auth_user WHERE usr_loginname = '%s' AND usr_id <> '%s'",$_POST["usr_loginname"],$usrId);
    
          $rs_check = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          
          if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,2);
          else $err_code = clearbit($err_code,2); 
          $dtaccess->Clear($rs_check);
           
          if ($_POST["usr_name"]) $err_code = clearbit($err_code,3); 
          else $err_code = setbit($err_code,3);
          
          if ($_POST["btnSave"]) 
               $sql = sprintf("SELECT usr_id FROM global_auth_user WHERE usr_name = '%s'",$_POST["usr_name"]);
          else
               $sql = sprintf("SELECT usr_id FROM global_auth_user WHERE usr_name = '%s' AND usr_id <> '%s'",$_POST["usr_name"],$usrId);
              
          $rs_check = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          
          if ($dtaccess->Count($rs_check)) $err_code = setbit($err_code,4);
          else $err_code = clearbit($err_code,4);       
          $dtaccess->Clear($rs_check);
             
          if ($_POST["usr_password"] == $_POST["usr_password2"]) $err_code = clearbit($err_code,5); 
          else $err_code = setbit($err_code,5);
          
          if($_POST["btnSave"]) $_POST["usr_status"] = "y";
          elseif(!$_POST["usr_status"]) $_POST["usr_status"] = "n";
          //--- End Checking Data ---//
             
          if ($_x_mode == "New") $_POST["usr_status"]='y';  
             
    
          if ($err_code == 0) {
               $dbTable = "global_auth_user";
               
               $dbField[0] = "usr_id";   // PK
               $dbField[1] = "usr_loginname";
               $dbField[2] = "usr_name";
               $dbField[3] = "id_rol";
               $dbField[4] = "usr_status";
               $dbField[5] = "usr_when_create";
               $dbField[6] = "usr_app_def";      
               if($_POST["is_password"]) $dbField[7] = "usr_password";
      
               if(!$_POST["usr_when_create"]) $_POST["usr_when_create"] = date("Y-m-d H:i:s");
               
               if(!$usrId) $usrId = $dtaccess->GetNewID("global_auth_user","usr_id",DB_SCHEMA_GLOBAL);
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$usrId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["usr_loginname"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_rol"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["usr_status"]);
               $dbValue[5] = QuoteValue(DPE_DATE,$_POST["usr_when_create"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["usr_app_def"]);
               if($_POST["is_password"]) $dbValue[7] = QuoteValue(DPE_CHAR,md5($_POST["usr_password"]));
      
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
               
               
               // --- buat nyimpen applicationnya ---               
                    
               $sql = "delete from global_auth_user_app 
                         where id_usr = ".$usrId;
               $dtaccess->Execute($sql);                    
               
               $dbTable = "global_auth_user_app";
               
               $dbField[0] = "usr_app_id";   // PK
               $dbField[1] = "id_usr";
               $dbField[2] = "id_app";
            
               foreach($_POST["id_app"] as $key=>$value){
                    $usrAppId = $dtaccess->GetNewID("global_auth_user_app","usr_app_id",DB_SCHEMA_GLOBAL);
                    $dbValue[0] = QuoteValue(DPE_NUMERIC,$usrAppId);
                    $dbValue[1] = QuoteValue(DPE_NUMERIC,$usrId);
                    $dbValue[2] = QuoteValue(DPE_NUMERIC,$value);
                    
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        
                    $dtmodel->Insert() or die("insert  error");	                         
                    
                    unset($dtmodel);                         
                    unset($dbValue);
                    unset($dbKey);
               }
               
               unset($dbField);
               
               header("location:hakakses_view.php");
               exit();        
          }
     }
  
     if ($_POST["btnDelete"]) {
          $usrId = & $_POST["cbDelete"];
          for($i=0,$n=count($usrId);$i<$n;$i++){
               $sql = "delete from global_auth_user where usr_id = ".$usrId[$i];
               $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          }
    
          header("location:hakakses_view.php");
          exit();    
     }
  
     if($_x_mode == "Edit" && ($_POST["id_rol"] == ROLE_TIPE_CUSTOMER || $_POST["id_rol"] == ROLE_TIPE_DISTRIBUTOR)) {
          $sql = "select * from global_auth_role where rol_id = ".$_POST["id_rol"];
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataJabatan = $dtaccess->FetchAll($rs);
     } else {
          $sql = "select * from global_auth_role 
                    where rol_id <> 0 
                    and rol_id <> ".ROLE_TIPE_CUSTOMER."
                    order by rol_name asc";
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataJabatan = $dtaccess->FetchAll($rs);     
     }

     $sql = "select * from global.global_app";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataApp = $dtaccess->FetchAll($rs);     
	
?>
<?php echo $view->RenderBody("inosoft.css",true); ;?>
<script language="Javascript">
function GantiPassword(frm, elm)
{
     if(elm.checked){
          frm.usr_password.disabled = false;
          frm.usr_password2.disabled = false;
          frm.usr_password2.style.backgroundColor = '#FFFFFF';
          frm.usr_password.style.backgroundColor = '#FFFFFF';		
          frm.usr_password.style.borderColor = '#c2c6d3';		
          frm.usr_password2.style.borderColor = '#c2c6d3';
          frm.usr_password.focus();
     } else {
          frm.usr_password.disabled = true;
          frm.usr_password2.disabled = true;
          frm.usr_password2.style.backgroundColor = '#e2dede';
          frm.usr_password.style.backgroundColor = '#e2dede';
          frm.usr_password.style.borderColor = '#c2c6d3';		
          frm.usr_password2.style.borderColor = '#c2c6d3';
     }
}

function TampilCombo(elm,elm_combo)
{        
     if(elm.checked){
          elm_combo.disabled = false;
          elm_combo.checked = true;          
     } else {
          elm_combo.disabled = true;
     }
}

</script>

<style type="text/css">
.passDisable{
   color: #0F2F13;
   border: 1px solid #c2c6d3;
   background-color: #e2dede;
}
</style>

<body>
<table width="100%" border="1" cellpadding="1" cellspacing="1">
     <tr>
          <td align="left" colspan=2 class="tableheader">&nbsp;Master Pengguna</td>
     </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="60%" border="1" cellpadding="1" cellspacing="1">
     <tr>
          <td>
               <fieldset>
               <legend><strong>User&nbsp;Setup</strong></legend>
               <table width="100%" border="1" cellpadding="1" cellspacing="1">               
                    <tr>
                         <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Nama Jabatan</strong></td>
                         <td  width="70%" class="tblCol">
                              <select class="inputField" name="id_rol">
                                   <?php for($i=0,$n=count($dataJabatan);$i<$n;$i++){ ?>
                                        <option class="inputField" value="<?php echo $dataJabatan[$i]["rol_id"];?>" <?php if($dataJabatan[$i]["rol_id"]==$_POST["id_rol"]) echo "selected";?>><?php echo $dataJabatan[$i]["rol_name"];?></option>
                                   <?php } ?>
                              </select>
                         </td>
                    </tr>
                    <tr>
                         <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Nama Pengguna<?if(readbit($err_code,3) || readbit($err_code,4)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
                         <td width="70%" class="tblCol">
                         <?php echo $view->RenderTextBox("usr_name","usr_name","30","50",$_POST["usr_name"],"inputField", null,false);?></td>
                    </tr>
                    <tr>
                         <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Nama Login<?if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
                         <td width="70%" class="tblCol">
                         <?php echo $view->RenderTextBox("usr_loginname","usr_loginname","30","50",$_POST["usr_loginname"],"inputField", null,false);?></td>
                    </tr>
                    <tr>
                         <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Password</strong></td>
                         <td width="70%" class="tblCol">
                              <?php echo $view->RenderPassword("usr_password","usr_password","30","50","",($_x_mode=="Edit")?"passDisable":"inputField",($_x_mode=="Edit")?"disabled":"",false);			
                              if($_x_mode == "Edit"){ 
                                   echo $view->RenderCheckBox("is_password","is_password","y","inputField",false,"onClick='GantiPassword(this.form,this)'");
                                   echo $view->RenderLabel("lbl_password","is_password","Ganti Password"); 
                              } elseif($_x_mode == "New"){
                                   echo $view->RenderHidden("is_password","is_password","y");
                              } ?>
                         </td>
                    </tr>
                    <tr>
                         <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Ulangi Password<?if(readbit($err_code,5)){?>&nbsp;<font color="red">(*)</font><?}?></strong></td>
                         <td width="70%" class="tblCol">
                         <?php echo $view->RenderPassword("usr_password2","usr_password2","30","50","",($_x_mode=="Edit")?"passDisable":"inputField",($_x_mode=="Edit")?"disabled":"",false);?></td>
                    </tr>
                    <tr>
					<td class="tablecontent" align="right">Aplikasi&nbsp;</td>
                         <td>
                              <table width=80%">
                              <?php for($i=0,$n=count($dataApp);$i<$n;$i++){?>
                              <tr>
                                   <td width="50%">
                                        <input onKeyDown="return tabOnEnter(this, event);" type="checkbox" name="id_app[<?php echo $dataApp[$i]["app_id"];?>]" id="id_app[<?php echo $dataApp[$i]["app_id"];?>]" value="<?php echo $dataApp[$i]["app_id"];?>" checked onClick="TampilCombo(this,document.getElementById('usr_app_def_<?php echo $i?>'))"/>
                                        <label for="id_app[<?php echo $dataApp[$i]["app_id"];?>]"><?php echo $dataApp[$i]["app_nama"];?></label>                                       
                                   </td>
                                   <td width="50%">                                        
                                        <input onKeyDown="return tabOnEnter(this, event);" type="radio" name="usr_app_def" id="usr_app_def_<?php echo $i?>" value="<?php echo $dataApp[$i]["app_id"]?>" checked />
                                        <label for="usr_app_def_<?php echo $i?>">default</label> 
                                   </td>     
                              </tr>
                              <?php }?>
                              </table>
                         </td>
                    </tr>
                    <?php if($_x_mode == "Edit"){ ?>
                         <tr>
                              <td class="tablecontent" width="30%" align="right" class="tblMainCol"><strong>Status</strong></td>
                              <td width="70%" class="tblCol">
                              <?php echo $view->RenderCheckBox("usr_status","usr_status","y","inputField",($_POST["usr_status"]=="y")?"checked":"");						
                                   echo $view->RenderLabel("usr_status","usr_status","Aktif")?>
                              </td>
                         </tr>
                    <?php } ?>
                    <tr>
                         <td colspan="2" align="center" class="tblCol">
                              <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","button",false);				
                              echo $view->RenderButton(BTN_SUBMIT,"btnNew","btnNew","Tambah","button",false);
                              echo $view->RenderButton(BTN_BUTTON,"btnback","btnback","Kembali","button",false,"onClick=\"window.document.location.href='hakakses_view.php'\"")?>
                         </td>
                    </tr>
               </table>
               </fieldset>
          </td>
     </tr>
</table>
               
<?php echo $view->SetFocus("usr_name");?>

<?php if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { 
	echo $view->RenderHidden("usr_id","usr_id",$usrId);
}
echo $view->RenderHidden("x_mode","x_mode",$_x_mode);
echo $view->RenderHidden("usr_when_create","usr_when_create",$_POST["usr_when_create"]);?>
</form>
          
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? } ?>
<? if (readbit($err_code,1)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Login&nbsp;harus&nbsp;Diisi.</strong></font>
<? } ?>
<? if (readbit($err_code,2)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Login&nbsp;udah&nbsp;ada.</strong></font>
<? } ?>
<? if (readbit($err_code,3)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Pengguna&nbsp;harus&nbsp;Diisi.</strong></font>
<? } ?>
<? if (readbit($err_code,4)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Nama&nbsp;Pengguna&nbsp;udah&nbsp;ada.</strong></font>
<? } ?>
<? if (readbit($err_code,5)) { ?>
<br>
<font color="green"><strong>Hint&nbsp;:&nbsp;Password Missmatch</strong></font>
<? } ?>
</body>
<?
   $dtaccess->Close();
?>
