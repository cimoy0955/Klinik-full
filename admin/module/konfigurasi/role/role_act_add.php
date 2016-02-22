<?php
     //LIBRARY 
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     //INISIALISAI AWAL LIBRARY
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     
     //Login Authentifikasi
     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
  
    //variable awal 
 	  $priv["1"] = "checked";
	  $priv["0"] = "";
    
     if($_GET["id_rol"]) { 
          $id_rol = $enc->Decode($_GET["id_rol"]);
          $idRolEnc = $enc->Decode($_GET["id_rol"]);
     }
     
     if($_POST["id_rol"]) $id_rol = $_POST["id_rol"]; 

    //echo "jab1 ".$_GET["id_jab"]."</br>";
    //echo "jab2 ".$_POST["id_jab"]."</br>";   
	
    $tabletblHeader[0]["name"]  = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
    $tabletblHeader[0]["width"] = "1%";
    $tabletblHeader[0]["align"] = "center";
    
    $tabletblHeader[1]["name"]  = "Tampil";
    $tabletblHeader[1]["width"] = "20%";
    $tabletblHeader[1]["align"] = "center";
 
 /*   
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
    */
    
    $tableContent[0]["name"]  = "priv_id";
    $tableContent[0]["wrap"]  = "nowrap";
    $tableContent[0]["align"] = "left";

    $tableContent[1]["name"]  = "priv_name";
    $tableContent[1]["wrap"]  = "nowrap";
    $tableContent[1]["align"] = "left";

    $jumContent = count($tabletblHeader);
    $startContent = 1;  // -- mulai bener2 listnya --
    
  
           
    if ($_POST["cbDelete"]) {
 echo "<script>alert('asd');</script>";
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
				$dbValue[0] = QuoteValue(DPE_NUMERIC,$_POST["id_rol"]);
				$dbValue[1] = QuoteValue(DPE_CHAR,$actId[$i]);
                //$dbValue[3] = QuoteValue(DPE_NUMERIC,$actId[$i]);
				$dbValue[2] = QuoteValue(DPE_CHAR,'1111');             
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
            header("location:role_act_view.php?id=".$enc->Encode($id_rol));
            exit();        
        }
    }
    

    /*"select a.* from global.global_auth_privilege a 
             left join global.global_auth_role_priv b 
             on a.priv_id = b.id_priv
             and b.id_rol = ".$id_rol." 
             where b.id_priv is null";
      */       
    $sql = "select priv_id,priv_name,app_nama,app_id,priv_urut from global.global_auth_privilege a
            left join global.global_app b on b.app_id = a.id_app_nama
            except
            select id_priv,priv_name,app_nama,app_id,priv_urut from global.global_auth_role_priv a
            left join global.global_auth_privilege b on b.priv_id=a.id_priv
            left join global.global_app c on c.app_id = b.id_app_nama
            where id_rol=".QuoteValue(DPE_NUMERIC,$id_rol)."
            order by app_nama,priv_urut asc";
        
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs);

    //echo $sql;
    $sql = "select rol_name from global.global_auth_role where rol_id = ".QuoteValue(DPE_NUMERIC,$id_rol);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataJabatan = $dtaccess->Fetch($rs);
   
    $PageHeader = "&nbsp;Role Name : ".$dataJabatan["rol_name"];

?>
<?php echo $view->RenderBody("inosoft.css",false); ?>
<body>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="80%" border="0" cellpadding="1" cellspacing="1">
<tr class="tableheader">
    <td colspan="<?php echo $jumContent;?>"><?php echo $PageHeader;?></td>
</tr>
<tr class="subheader">
    <?php for($i=0;$i<$jumContent;$i++){ ?>
        <td width="6%" align="<?php echo $tabletblHeader[$i]["align"];?>"><?php echo $tabletblHeader[$i]["name"];?></td>
    <?php } ?>
</tr>
<?php for($i=0,$k=0,$n=count($dataTable);$i<$n;$i++,$k++){ ?>


   <?php   if($dataTable[$i]["app_id"]!=$dataTable[$i-1]["app_id"]) { ?>
          <tr class="tablesmallheader"> 
           <td align="left" colspan="6"><b><?php echo $dataTable[$i]["app_nama"];?></b></td>
          </tr> 
     <?php $k++; } ?>
     
     
    <tr class="<?php if($i%2==0) echo "tablecontent-odd";else echo "tablecontent";?>">
        <td align="center"><div align="center"><input type="checkbox" name="cbDelete[]" value="<?php echo $dataTable[$i][$tableContent[0]["name"]];?>"></div></td>
        <?php for($j=$startContent;$j<($jumContent);$j++){ ?>
            <td width="28%" align="<?php echo $tableContent[$j]["align"];?>" <?php echo $tableContent[$j]["wrap"];?>>&nbsp;<?php echo $dataTable[$i][$tableContent[$j]["name"]];?>&nbsp;</td>
        <?php } ?>
    <!--
        <td width="23%" align="center" nowrap>&nbsp;
      <input type="checkbox" name="jab_create_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
        <td width="23%" align="center" nowrap>&nbsp;
      <input type="checkbox" name="jab_read_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
        <td width="23%" align="center" nowrap>&nbsp;
      <input type="checkbox" name="jab_update_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
        <td width="23%" align="center" nowrap>&nbsp;
      <input type="checkbox" name="jab_delete_<?php echo $dataTable[$i][$tableContent[0]["name"]];?>" class="inputField" value="1" checked>&nbsp;</td>
     -->
    </tr>
<?php } ?>
<?php //echo $_GET["id_jab"];?>
<input type="hidden" name="id_rol" value="<?php echo $id_rol;?>" />
<tr class="tblCol"> 
    <td colspan="<?php echo ($jumContent);?>"><div align="left">
       <!--<a href="jab_act_view.php?">!--><!--</a>!-->
       <input type="submit" name="btnSave" value="Simpan" class="inputField">
    </div></td>
</tr>
</table>

</form>
</body>
</html>
<?php echo $view->RenderBodyEnd(); ?>