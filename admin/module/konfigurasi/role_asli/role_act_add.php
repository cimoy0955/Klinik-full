<?php
    require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
	require_once($APLICATION_ROOT."library/view.cls.php");

     $enc = new TextEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $table = new InoTable("table1","100%","center");
    $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
    
    if(!$auth->IsAllowed($privFiles[$_SERVER["PHP_SELF"]],PRIV_CREATE)){
        //die("access_denied");
        //exit(1);
    }
	$priv["1"] = "checked";
	$priv["0"] = "";
    
    if($_GET["id_rol"]) $roleId = $_GET["id_rol"];
    if($_POST["id_rol"]) $roleId = $_POST["id_rol"];

    
    //echo "jab1 ".$_GET["id_rol"]."</br>";
    //echo "jab2 ".$_POST["id_rol"]."</br>";   
	
    $tabletblHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
    $tabletblHeader[0]["width"] = "1%";
    $tabletblHeader[0]["align"] = "center";
    
    $tabletblHeader[1]["name"]  = "Tampil";
    $tabletblHeader[1]["width"] = "20%";
    $tabletblHeader[1]["align"] = "center";
    
    $tabletblHeader[2]["name"]  = "Create";
    $tabletblHeader[2]["width"] = "10%";
    $tabletblHeader[2]["align"] = "center";
    
    $tabletblHeader[3]["name"]  = "Read";
    $tabletblHeader[3]["width"] = "10%";
    $tabletblHeader[3]["align"] = "center";

    $tabletblHeader[4]["name"]  = "Update";
    $tabletblHeader[4]["width"] = "10%";
    $tabletblHeader[4]["align"] = "center";

    $tabletblHeader[5]["name"]  = "Delete";
    $tabletblHeader[5]["width"] = "10%";
    $tabletblHeader[5]["align"] = "center";

    $tableContent[0]["name"]  = "priv_id";
    $tableContent[0]["wrap"]  = "nowrap";
    $tableContent[0]["align"] = "left";

    $tableContent[1]["name"]  = "priv_name";
    $tableContent[1]["wrap"]  = "nowrap";
    $tableContent[1]["align"] = "left";

    $jumContent = count($tabletblHeader);
    $startContent = 1;  // -- mulai bener2 listnya --


    if ($_POST["btnSave"]) {
        $err_code = 0;
        if ($err_code == 0) {
             $dbTable = "global.global_auth_role_priv";            
            $dbField[0] = "id_rol";   // PK
            $dbField[1] = "id_priv";
            $dbField[2] = "rol_priv_access";
            
            $actId = & $_POST["cbDelete"];
            for($i=0,$n=count($actId);$i<$n;$i++){
                if($_POST["jab_create_".$actId[$i]]) $actAkses  = "1";
                else $actAkses .= "0";
                if($_POST["jab_read_".$actId[$i]]) $actAkses .= "1";
                else $actAkses .= "0";
                if($_POST["jab_update_".$actId[$i]]) $actAkses .= "1";
                else $actAkses .= "0";
                if($_POST["jab_delete_".$actId[$i]]) $actAkses .= "1";
                else $actAkses .= "0";                   
                
               //  $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["jab_id"]);
				       $dbValue[0] = QuoteValue(DPE_NUMERIC,$roleId);
				       $dbValue[1] = QuoteValue(DPE_CHAR,$actId[$i]);
                //$dbValue[3] = QuoteValue(DPE_NUMERIC,$actId[$i]);
				       $dbValue[2] = QuoteValue(DPE_CHAR,$actAkses);             
                //$dbValue[5] = QuoteValue(DPE_NUMERIC,$actAkses);
                
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dbKey[1] = 1; // -- set key buat clause wherenya , valuenya = index array buat field / value

                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                $dtmodel->Insert() or die ("insert  error");	

                unset($dtmodel);
                unset($dbValue);
                unset($dbKey);
                unset($actAccses);
            }
            header("location:role_act_view.php?id=".$roleId);
            exit();        
        }
    }
    

      $sql = "select priv_id, priv_name from global.global_auth_privilege
            except
            select a.id_priv, b.priv_name from global.global_auth_role_priv a
            left join global.global_auth_privilege b on b.priv_id = a.id_priv  
            where id_rol = ".QuoteValue(DPE_NUMERIC,$roleId);        
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs);
    
  
    //echo $sql;
    $sql = "select rol_name from global.global_auth_role where rol_id = ".QuoteValue(DPE_NUMERIC,$roleId);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataJabatan = $dtaccess->Fetch($rs);
    

   
    $PageHeader = "Action Jabatan : ".$dataJabatan["rol_name"];
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>program/fungsi/css/inosoft.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>program/fungsi/script/ew.js"></script>
</head>
<?php echo $view->RenderBody("inosoft.css",false); ?>
<body>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr class="tableheader">
    <td colspan="<?php echo $jumContent;?>"><?php echo $PageHeader;?></td>
</tr>
<tr class="subheader">
    <?php for($i=0;$i<$jumContent;$i++){ ?>
        <td width="6%" align="<?php echo $tabletblHeader[$i]["align"];?>"><?php echo $tabletblHeader[$i]["name"];?></td>
    <?php } ?>
</tr>
<?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
    <tr class="<?php if($i%2==0) echo "tablecontent" ; else echo "tablecontent-odd" ;?> ">
        <td align="center"><div align="center"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"></div></td>
        <?php for($j=$startContent;$j<($jumContent-4);$j++){ ?>
            <td width="28%" align="<?php echo $tableContent[$j]["align"];?>" <?php echo $tableContent[$j]["wrap"];?>>&nbsp;<?php echo $dataTable[$i][$tableContent[$j]["name"]];?>&nbsp;</td>
        <?php } ?>
        <td width="23%" align="center" nowrap>&nbsp;
      <input type="checkbox" name="jab_create_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
        <td width="23%" align="center" nowrap>&nbsp;
      <input type="checkbox" name="jab_read_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
        <td width="23%" align="center" nowrap>&nbsp;
      <input type="checkbox" name="jab_update_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
        <td width="23%" align="center" nowrap>&nbsp;
      <input type="checkbox" name="jab_delete_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
    </tr>
<?php } ?>
<input type="hidden" name="id_rol" value="<?php echo $_GET["id_rol"];?>" />
<input type="hidden" name="id_rol" value="<?php echo $_POST["id_rol"];?>" />
<tr class="tblCol"> 
    <td colspan="<?php echo ($jumContent);?>"><div align="left">
       <!--<a href="role_act_view.php?">!--><!--</a>!-->
       <input type="submit" name="btnSave" value="Simpan" class="inputField">
    </div></td>
</tr>
</table>

</form>
</body>
</html>
<?php echo $view->RenderBodyEnd(); ?>