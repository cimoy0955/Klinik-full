<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
	require_once($APLICATION_ROOT."library/view.cls.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	$auth = new CAuth();
     $err_code = 0;
     
     $monthName = array("--","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
     $viewPage = "obat_view.php";
     $editPage = "obat_edit.php";
	
	$plx = new InoLiveX("CheckDataCustomerTipe");
	
     if(!$auth->IsAllowed("setup_role",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_role",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.obat_id FROM apotik_obat_master a 
                    WHERE upper(a.obat_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
          $dataobat = $dtaccess->Fetch($rs);
          
		return $dataobat["obat_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["obat_id"])  $obatId = & $_POST["obat_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $obatId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from apotik_obat_master a 
				          where obat_id = ".QuoteValue(DPE_CHAR,$obatId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["obat_nama"] = $row_edit["obat_nama"];
          $_POST["obat_kemasan"] = $row_edit["obat_kemasan"];
          $_POST["obat_komposisi"] = $row_edit["obat_komposisi"];
          $_POST["obat_pabrik"] = $row_edit["obat_pabrik"];
          $_POST["obat_satuan"] = $row_edit["obat_satuan"];
          $_POST["obat_stok"] = $row_edit["obat_stok"];
          $_POST["obat_berat"] = $row_edit["obat_berat_satuan"];
          $_POST["obat_harga_beli"] = $row_edit["obat_harga_beli"];
          $_POST["obat_harga_jual"] = $row_edit["obat_harga_jual"];
          $_POST["obat_berlaku"] = $row_edit["obat_berlaku"];
          $_POST["obat_keterangan"] = $row_edit["obat_keterangan"];
          $_POST["id_kategori"] = $row_edit["id_kategori"];
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
               $obatId = & $_POST["obat_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
          
               $dbTable = "apotik_obat_master";
               
               $dbField[0] = "obat_id";   // PK
               $dbField[1] = "obat_nama";
			         $dbField[2] = "obat_kemasan";
			         $dbField[3] = "obat_komposisi";
			         $dbField[4] = "obat_pabrik";
			         $dbField[5] = "obat_satuan";
			         $dbField[6] = "obat_stok";
			         $dbField[7] = "obat_berat_satuan";
			         $dbField[8] = "obat_harga_beli";
			         $dbField[9] = "obat_harga_jual";
			         $dbField[10] = "obat_keterangan";
			         $dbField[11] = "obat_berlaku";
			         $dbField[12] = "id_kategori";
			         
			         $berlakunya = $_POST["obat_berlaku_tahun"]."-".$_POST["obat_berlaku_bulan"];
               if(!$obatId) $obatId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$obatId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["obat_nama"]); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["obat_kemasan"]); 
			         $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["obat_komposisi"]); 
			         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["obat_pabrik"]); 
			         $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["obat_satuan"]); 
			         $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["obat_stok"])); 
			         $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["obat_berat"])); 
			         $dbValue[8] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["obat_harga_beli"])); 
			         $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["obat_harga_jual"])); 
			         $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["obat_keterangan"]); 
			         $dbValue[11] = QuoteValue(DPE_CHAR,$berlakunya); 
			         $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["id_kategori"]); 
			         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_APOTIK);
   
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
 
     if ($_POST["btnDelete"]) {
          $obatId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($obatId);$i<$n;$i++){
               $sql = "delete from apotik_obat_master 
                         where obat_id = ".QuoteValue(DPE_CHAR,$obatId[$i]);
               $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
          }
          
          header("location:".$viewPage);
          exit();    
     } 
     
     //-- bikin combo box untuk kategori --//
     $sql = "select * from apotik_obat_kategori order by kategori_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_APOTIK);
     
     unset($opt_kat);unset($show);$i=1;
     $opt_kat[0] = $view->RenderOption("--","[pilih kategori obat]",$show);
     while($data_kat = $dtaccess->Fetch($rs)){
        if($data_kat["kategori_id"] == $_POST["id_kategori"]) $show="selected";
        $opt_kat[$i] = $view->RenderOption($data_kat["kategori_id"],$data_kat["kategori_nama"],$show);
        $i++;
     }
      
     $berlaku = explode("-",$_POST["obat_berlaku"]);
     unset($opt_berlaku_tahun); unset($opt_berlaku_bulan);
     unset($show);
     for($r=0;$r<10;$r++){
       unset($show);
       if($berlaku[0]=="201".$r) $show = "selected";
       $opt_berlaku_tahun[$r] = $view->RenderOption("201".$r,"201".$r,$show);
     }
     
     for($r=1;$r<=13;$r++){
       unset($show);
       if($berlaku[1]==$r) $show = "selected";
       $opt_berlaku_bulan[$r] = $view->RenderOption($r,$monthName[$r],$show);
     }
?> 

<?php echo $view->RenderBody("inosoft.css",false); ?>

<script language="javascript" type="text/javascript">

<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 
     
     if(!frm.obat_nama.value){
		alert('Nama obat Optik Harus Diisi');
		frm.obat_nama.focus();
          return false;
	}
	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.obat_nama.value,'type=r')){
			alert('Nama obat Optik Sudah Ada');
			frm.obat_nama.focus();
			frm.obat_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}
</script>

<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit obat Optik</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="90%" border="1" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Obat Setup</strong></legend>
     <table width="100%" border="1" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="20%"><strong>Nama<?php if(readbit($err_code,1) || readbit($err_code,2)){?>&nbsp;<font color="red">(*)</font><?}?></strong>&nbsp;</td>
               <td width="80%" colspan="3" class="tablecontent-odd">
                 <?php echo $view->RenderTextBox("obat_nama","obat_nama","50","100",$_POST["obat_nama"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent"><strong>Kategori</strong>&nbsp;</td>
               <td colspan="3" class="tablecontent-odd">
                 <?php echo $view->RenderComboBox("id_kategori","id_kategori",$opt_kat,"inputField");?>
               </td>
          </tr> 
          <?php $lebarnya = 80/3; ?>
          <tr>
               <td align="right" class="tablecontent" rowspan="2" valign="top">Kemasan&nbsp;</td>
               <td align="left" class="tablecontent-odd" width="<? echo $lebarnya;?>">&nbsp;<?php echo $view->RenderRadio("obat_kemasan","obat_kemasan","tablet/kaplet","inputfield",($_POST["obat_kemasan"]=="tablet/kaplet")?"checked":"",null); ?>Tablet/Kaplet</td>
               <td align="left" class="tablecontent-odd" width="<? echo $lebarnya;?>">&nbsp;<?php echo $view->RenderRadio("obat_kemasan","obat_kemasan","kapsul","inputfield",($_POST["obat_kemasan"]=="kapsul")?"checked":"",null); ?>Kapsul</td>
               <td align="left" class="tablecontent-odd" width="<? echo $lebarnya;?>">&nbsp;<?php echo $view->RenderRadio("obat_kemasan","obat_kemasan","salut","inputfield",($_POST["obat_kemasan"]=="salut")?"checked":"",null); ?>Salut.</td>
          </tr>
          <tr>
               <td align="left" class="tablecontent-odd" width="<? echo $lebarnya;?>">&nbsp;<?php echo $view->RenderRadio("obat_kemasan","obat_kemasan","sirup","inputfield",($_POST["obat_kemasan"]=="sirup")?"checked":"",null); ?>Sirup</td>
               <td align="left" class="tablecontent-odd" width="<? echo $lebarnya;?>">&nbsp;<?php echo $view->RenderRadio("obat_kemasan","obat_kemasan","tube","inputfield",($_POST["obat_kemasan"]=="tube")?"checked":"",null); ?>Tube</td>
               <td align="left" class="tablecontent-odd" width="<? echo $lebarnya;?>">&nbsp;<?php echo $view->RenderRadio("obat_kemasan","obat_kemasan","botol","inputfield",($_POST["obat_kemasan"]=="botol")?"checked":"",null); ?>botol</td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" valign="top"><strong>Komposisi</strong>&nbsp;</td>
               <td colspan="3" class="tablecontent-odd">
                  <?php echo $view->RenderTextarea("obat_komposisi","obat_komposisi","3","50",$_POST["obat_komposisi"],"inputfield",null,null); ?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Pabrik</strong>&nbsp;</td>
               <td colspan="3" class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("obat_pabrik","obat_pabrik","50","100",$_POST["obat_pabrik"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent"><strong>Stok</strong>&nbsp;</td>
               <td colspan="3" class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("obat_stok","obat_stok","20","100",currency_format($_POST["obat_stok"]),"inputField", null,true);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent"><strong>Berat</strong>&nbsp;</td>
               <td class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("obat_berat","obat_berat","20","100",currency_format($_POST["obat_berat"]),"inputField", null,true);?>
               </td>
              <td align="right" class="tablecontent"><strong>Satuan</strong>&nbsp;</td>
               <td class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("obat_satuan","obat_satuan","20","100",$_POST["obat_satuan"],"inputField", null,false);?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent"><strong>Harga Beli</strong>&nbsp;</td>
               <td class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("obat_harga_beli","obat_harga_beli","20","100",currency_format($_POST["obat_harga_beli"]),"inputField", null,true);?>
               </td>
              <td align="right" class="tablecontent"><strong>Harga Jual</strong>&nbsp;</td>
               <td class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("obat_harga_jual","obat_harga_jual","20","100",currency_format($_POST["obat_harga_jual"]),"inputField", null,true);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent"><strong>Masa Berlaku</strong>&nbsp;</td>
               <td colspan="3" class="tablecontent-odd">
                  <?php echo $view->RenderComboBox("obat_berlaku_bulan","obat_berlaku_bulan",$opt_berlaku_bulan,"inputField");?>&nbsp;
                  <?php echo $view->RenderComboBox("obat_berlaku_tahun","obat_berlaku_tahun",$opt_berlaku_tahun,"inputField");?>
               </td>
          </tr> 
          <tr>
               <td align="right" class="tablecontent"><strong>Keterangan</strong>&nbsp;</td>
               <td colspan="3" class="tablecontent-odd">
                  <?php echo $view->RenderTextBox("obat_keterangan","obat_keterangan","50","100",$_POST["obat_keterangan"],"inputField", null,false);?>
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

<script>document.frmEdit.obat_nama.focus();</script>

<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("obat_id","obat_id",$obatId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
