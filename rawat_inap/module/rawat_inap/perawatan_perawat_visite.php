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
     

 	if(!$auth->IsAllowed("perawatan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("perawatan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }


     $_x_mode = "New";
     $thisPage = "perawatan_perawat_visite.php";

    $tableRefraksi = new InoTable("table1","99%","center");

	if($_GET["id_reg"]) $_POST["id_reg"] = $enc->Decode($_GET["id_reg"]);
	if($_GET["id_cust_usr"]) $_POST["id_cust_usr"] = $enc->Decode($_GET["id_cust_usr"]);
	

		$sql = "select rawat_id, date(rawat_waktu) as tanggal , cast(rawat_waktu as time) as waktu
				from klinik.klinik_perawatan
				where id_cust_usr = ".$_POST["id_cust_usr"]."   
				order by rawat_waktu desc";
		$dataTglPerawatan= $dtaccess->FetchAll($sql);
	//echo $sql;
	$tglPerawatan[0] = $view->RenderOption('--','Pilih Tanggal',$show); 
    for($i=0,$n=count($dataTglPerawatan);$i<$n;$i++){
         unset($show);
         if($_POST["id_tgl"]==$dataTglPerawatan[$i]["rawat_id"]) $show = "selected";
         $tglPerawatan[$i+1] = $view->RenderOption($dataTglPerawatan[$i]["rawat_id"],format_date($dataTglPerawatan[$i]["tanggal"])." ".$dataTglPerawatan[$i]["waktu"],$show);               
    } 
    
   

	if($_POST["id_tgl"])
  {   
   $sqlPerawatan = "select rawat_id,rawat_waktu,rawat_keluhan,rawat_lab_tensi,
   rawat_lab_nadi,rawat_lab_nafas,rawat_lab_alergi,
   rawat_catatan from klinik.klinik_perawatan 
   where rawat_id = '".$_POST["id_tgl"]."' order by rawat_waktu desc";
   $dataPerawatan= $dtaccess->Fetch($sqlPerawatan);
   $_POST["rawat_id"] =  $dataPerawatan["rawat_id"];
   $_POST["rawat_waktu"] =  $dataPerawatan["rawat_waktu"];
   $_POST["rawat_keluhan"] =  $dataPerawatan["rawat_keluhan"];
   $_POST["rawat_tensi"] =  $dataPerawatan["rawat_lab_tensi"];
   $_POST["rawat_nadi"] =  $dataPerawatan["rawat_lab_nadi"];
   $_POST["rawat_pernafasan"] =  $dataPerawatan["rawat_lab_nafas"];
   $_POST["rawat_rr"] =  $dataPerawatan["rawat_alergi"];
   //$_POST["rawat_diagnosis_txt"] =  $dataPerawatan["rawat_diagnosis_txt"];
   //$_POST["rawat_tindakan_txt"] =  $dataPerawatan["rawat_tindakan_txt"];
   $_POST["rawat_catatan"] =  $dataPerawatan["rawat_catatan"];
   //$_POST["rawat_terapi"] =  $dataPerawatan["rawat_terapi"];
	}	

     $lokasi = $APLICATION_ROOT."images/foto_perawatan/";
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>

<script type="text/javascript">
function Masuk()
{    
          alert('Pepi Coba');
          document.location.href='<?php echo $thisPage;?>';
          
          return false;

} 

</script>

 <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
	<!--  <td colspan="3" align="left"><font size="3"><strong>REKAM MEDIK PASIEN</strong></font></td>-->
	</tr>
  <tr>
	  <td align="right"><strong>Tgl :</strong></td>
		<td align="left"  class="tableheader">
    <?php echo $view->RenderComboBox("id_tgl","id_tgl",$tglPerawatan,null,null);?>
    </td>
    <td><input type="submit" name="btnLanjut" value="Lanjut" class="button"></td>
	</tr>
	<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>  
</table> 
 </form>                                                                              

<?php if ($_POST["rawat_id"]) { ?>
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">

     <legend><strong>Rekam Medik Tgl <?php echo $_POST["rawat_waktu"];?></strong></legend>
     <table width="100%" border="1" cellpadding="2" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent">Keluhan :</td>
               <td align="left" class="tablecontent-odd"><textarea readonly cols="25" rows="4"><?php echo $_POST["rawat_keluhan"];?></textarea></td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">Tensi :</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_tensi"];?>&nbsp;mmHg</td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">Nadi :</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_nadi"];?>&nbsp;x/menit</td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">Pernafasan :</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_pernafasan"];?>&nbsp;C</td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">RR :</td>
               <td align="left" class="tablecontent-odd"><?php echo $_POST["rawat_rr"];?>&nbsp;X/menit</td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">Diagnosis :</td>
               <td align="left" class="tablecontent-odd"><textarea readonly cols="25" rows="4"><?php echo $_POST["rawat_diagnosis_txt"];?></textarea></td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">Tindakan :</td>
               <td align="left" class="tablecontent-odd"><textarea readonly cols="25" rows="4"><?php echo $_POST["rawat_tindakan_txt"];?></textarea></td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">Resep :</td>
               <td align="left" class="tablecontent-odd"><textarea readonly cols="25" rows="4"><?php echo $_POST["rawat_terapi"];?></textarea></td>
          </tr>
          <tr>
               <td align="right" class="tablecontent">Catatan :</td>
               <td align="left" class="tablecontent-odd"><textarea readonly cols="25" rows="4"><?php echo $_POST["rawat_catatan"];?></textarea></td>
          </tr>
	</table>
		
     </td>
</tr>	

</table>

<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="rawat_id" value="<?php echo $_POST["id_tgl"];?>"/>

</span>

</form>
<? } ?>

<?php echo $view->RenderBodyEnd(); ?>
