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
     $userData = $auth->GetUserData();
     
 	if(!$auth->IsAllowed("setup_ina",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("setup_ina",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "penyakit_view.php";
     $icdPage = "icd_find.php?";
     
     
      $sql = "select icd_nama,icd_nomor, icd_id, penyakit_urut from global.global_penyakit a
                    join klinik.klinik_icd b on a.id_icd = b.icd_id 
                    order by penyakit_urut asc";
          $rs = $dtaccess->Execute($sql);
          $i=0;

          while($row=$dtaccess->Fetch($rs)) {
               $_POST["icd_id"][$i] = $row["icd_id"];
               $_POST["icd_nomor"][$i] = $row["icd_nomor"];
               $_POST["icd_nama"][$i] = $row["icd_nama"];
               $i++;
          }


// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
	
	// --- delete data e dulu ---
          if($_POST["btnSave"]) {               
               $sql = "delete from global.global_penyakit";
              $dtaccess->Execute($sql); 
          }
	
	 $dbTable = "global.global_penyakit";
          $dbField[0] = "penyakit_id";   // PK
          $dbField[1] = "id_icd";
          $dbField[2] = "nomor_icd";
          $dbField[3] = "nama_icd";
          $dbField[4] = "penyakit_urut";
                    
           for($i=0,$n=count($_POST["icd_id"]);$i<$n;$i++) {
          $dbValue[0] = QuoteValue(DPE_CHARKEY,$dtaccess->GetTransID());
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["icd_id"][$i]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["icd_nomor"][$i]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["icd_nama"][$i]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["urutan"][$i]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               if($_POST["btnSave"]) $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
	
	
	}

?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitUpload(); ?>
<?php echo $view->InitThickBox(); ?>
<?php echo $view->InitDom(); ?>
<script type="text/javascript">


</script>
<form method="POST" action="penyakit_view.php">
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Setup 10 Penyakit Diutamakan</td>
	</tr>
</table> 
<br />
<br />

     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="center" class="subheader" width="5%"></td>
               <td align="center" class="subheader" width="25%">ICD</td>
               <td align="center" class="subheader">Keterangan</td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">1</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[0]","icd_nomor_0","10","100",$_POST["icd_nomor"][0],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[0]" id="icd_id_0" value="<?php echo $_POST["icd_id"][0]?>" />
                    <input type="hidden" name="urutan[0]" id="urutan_0" value="1" />
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[0]","icd_nama_0","50","100",$_POST["icd_nama"][0],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">2</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[1]","icd_nomor_1","10","100",$_POST["icd_nomor"][1],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=1&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[1]" id="icd_id_1" value="<?php echo $_POST["icd_id"][1]?>" />                    
                    <input type="hidden" name="urutan[1]" id="urutan_1" value="2" />
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[1]","icd_nama_1","50","100",$_POST["icd_nama"][1],"inputField", "readonly",false);?></td>
          </tr>
           <tr>
               <td align="center" class="tablecontent">3</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[2]","icd_nomor_2","10","100",$_POST["icd_nomor"][2],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=2&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[2]" id="icd_id_2" value="<?php echo $_POST["icd_id"][2]?>" />                    
                    <input type="hidden" name="urutan[2]" id="urutan_2" value="3" />
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[2]","icd_nama_2","50","100",$_POST["icd_nama"][2],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">4</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[3]","icd_nomor_3","10","100",$_POST["icd_nomor"][3],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=3&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[3]" id="icd_id_3" value="<?php echo $_POST["icd_id"][3]?>" />                    
                    <input type="hidden" name="urutan[3]" id="urutan_3" value="4" />
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[3]","icd_nama_3","50","100",$_POST["icd_nama"][3],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">5</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[4]","icd_nomor_4","10","100",$_POST["icd_nomor"][4],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=4&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[4]" id="icd_id_4" value="<?php echo $_POST["icd_id"][4]?>" />                    
                    <input type="hidden" name="urutan[4]" id="urutan_4" value="5" />
              </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[4]","icd_nama_4","50","100",$_POST["icd_nama"][4],"inputField", "readonly",false);?></td>
          </tr>
          
                    <tr>
               <td align="center" class="tablecontent">6</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[5]","icd_nomor_5","10","100",$_POST["icd_nomor"][5],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=5&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[5]" id="icd_id_5" value="<?php echo $_POST["icd_id"][5]?>" /> 
                    <input type="hidden" name="urutan[5]" id="urutan_5" value="6" />
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[5]","icd_nama_5","50","100",$_POST["icd_nama"][5],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">7</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[6]","icd_nomor_6","10","100",$_POST["icd_nomor"][6],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=6&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[6]" id="icd_id_6" value="<?php echo $_POST["icd_id"][6]?>" />                    
                    <input type="hidden" name="urutan[6]" id="urutan_6" value="7" />
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[6]","icd_nama_6","50","100",$_POST["icd_nama"][6],"inputField", "readonly",false);?></td>
          </tr>
           <tr>
               <td align="center" class="tablecontent">8</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[7]","icd_nomor_7","10","100",$_POST["icd_nomor"][7],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=7&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[7]" id="icd_id_7" value="<?php echo $_POST["icd_id"][7]?>" />                    
                    <input type="hidden" name="urutan[7]" id="urutan_7" value="8" />
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[7]","icd_nama_7","50","100",$_POST["icd_nama"][7],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">9</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[8]","icd_nomor_8","10","100",$_POST["icd_nomor"][8],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=8&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[8]" id="icd_id_8" value="<?php echo $_POST["icd_id"][8]?>" />                    
                    <input type="hidden" name="urutan[8]" id="urutan_8" value="9" />
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[8]","icd_nama_8","50","100",$_POST["icd_nama"][8],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">10</td>
               <td align="center" class="tablecontent-odd">
                    <?php echo $view->RenderTextBox("icd_nomor[9]","icd_nomor_9","10","100",$_POST["icd_nomor"][9],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&tipe=os&el=9&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" name="icd_id[9]" id="icd_id_9" value="<?php echo $_POST["icd_id"][9]?>" />                    
                    <input type="hidden" name="urutan[9]" id="urutan_9" value="10" />
               </td>
               <td align="center" class="tablecontent-odd"><?php echo $view->RenderTextBox("icd_nama[9]","icd_nama_9","50","100",$_POST["icd_nama"][9],"inputField", "readonly",false);?></td>
          </tr>
          <tr>
          <td>
<input type="submit" name="btnSave" id="btnSave" value="Simpan" class="button" />                
</td>
</tr>
	</table>
	</form>
	
<input type="hidden" name="penyakit_id" value="<?php echo $_POST["penyakit_id"];?>"/>	
	
<?php echo $view->RenderBodyEnd(); ?>