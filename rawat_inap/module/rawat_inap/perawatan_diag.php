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
     $thisPage = "perawatan_diag.php";

     $tableRefraksi = new InoTable("table1","99%","center");

	if($_GET["id_reg"]) $_POST["id_reg"] = $enc->Decode($_GET["id_reg"]);
	if($_GET["id_cust_usr"]) $_POST["id_cust_usr"] = $enc->Decode($_GET["id_cust_usr"]);
	
	if($_POST["id_reg"] && $_POST["id_cust_usr"]) {
		$sql = "select a.*
				from klinik.klinik_diagnostik a
                    where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dataDiagnostik= $dtaccess->Fetch($sql);
          
          $lokasiUsg = $APLICATION_ROOT."images/foto_usg";
     	$fotoUsg = $lokasiUsg."/".$dataDiagnostik["diag_gambar_usg"];
     
          $lokasiFundus = $APLICATION_ROOT."images/foto_fundus";
     	$fotoFundus = $lokasiFundus."/".$dataDiagnostik["diag_gambar_fundus"];
     
          $lokasiHumpre = $APLICATION_ROOT."images/foto_humpre";
     	$fotoHumpre = $lokasiHumpre."/".$dataDiagnostik["diag_gambar_humpre"];
     
          $lokasiOct = $APLICATION_ROOT."images/foto_oct";
     	$fotoOct = $lokasiOct."/".$dataDiagnostik["diag_gambar_oct"];

		$sql = "select rawat_id, rawat_mata_foto, rawat_waktu
				from klinik.klinik_perawatan
				where id_cust_usr = ".$_POST["id_cust_usr"]." and rawat_mata_foto <> '' 
				order by rawat_waktu desc";
		$dataMata= $dtaccess->FetchAll($sql);
	}

     $lokasi = $APLICATION_ROOT."images/foto_perawatan/";
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>

<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Data Diagnostik</td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <!--<fieldset>
     <legend><strong>Keratometri</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td align="center">&nbsp;</td>
               <td align="center">OD</td>
               <td align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">K1</td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_k1_od"];?></td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_k1_os"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">K2</td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_k2_od"];?></td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_k2_os"];?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Biometri</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr class="subheader">
               <td align="center">&nbsp;</td>
               <td align="center">OD</td>
               <td align="center">OS</td>
          </tr>	
          <tr>
               <td align="left" class="tablecontent">Acial Length</td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_acial_od"];?></td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_acial_os"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Power IOL</td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_iol_od"];?></td>
               <td align="center" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_iol_os"];?></td>
          </tr>
	</table>
     </fieldset>-->


     <fieldset>
     <legend><strong>USG</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="10%" class="tablecontent">COA</td>
               <td align="left" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_coa"];?></td>
          </tr>
          <!--<tr>
               <td align="left" class="tablecontent">Lensa</td>
               <td align="left" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_lensa"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Retina</td>
               <td align="left" class="tablecontent-odd">&nbsp;<?php echo $dataDiagnostik["diag_lensa"];?></td>
          </tr>-->
          <tr>
               <td align="left" class="tablecontent">Kesimpulan</td>
               <td align="left" class="tablecontent-odd">&nbsp;<?php echo nl2br($dataDiagnostik["diag_kesimpulan"]);?></td>
          </tr>
	</table>
     </fieldset>
     
     
     <fieldset>
     <legend><strong>Gambar Diagnostik Hari Ini</strong></legend>
     <table width="100%" border="1" cellpadding="2" cellspacing="1">
          <tr>
               <td align="center" width="40%" class="tablecontent-odd"><img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $fotoUsg;?>"  border="1"></td>
               <td align="center" width="40%" class="tablecontent-odd"><img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $fotoFundus;?>"  border="1"></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">USG I</td>
               <td align="center" class="tablecontent">USG II</td>
          </tr>
          <tr>
               <td align="center" width="40%" class="tablecontent-odd"><img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $fotoHumpre;?>"  border="1"></td>
               <td align="center" width="40%" class="tablecontent-odd"><img hspace="2" width="120" height="150" name="img_foto" id="img_foto" src="<?php echo $fotoOct;?>"  border="1"></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">USG III</td>
               <td align="center" class="tablecontent">USG IV</td>
          </tr>
	</table>
     </fieldset>
		
		
     </td>
</tr>	

</table>

<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>

</span>

</form>


<?php echo $view->RenderBodyEnd(); ?>
