<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     

 	if(!$auth->IsAllowed("kasir",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kasir",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "kasir_view.php";
     $findPage = "item_find.php?";


     $tableRefraksi = new InoTable("table1","99%","center");
     $table = new InoTable("table","100%","left");


     $plx = new InoLiveX("GetFolio");     
     
     function GetFolio() {
          global $dtaccess, $view, $tableRefraksi, $thisPage, $APLICATION_ROOT,$rawatStatus; 
               
          $sql = "select distinct(a.id_reg), a.id_cust_usr, b.cust_usr_nama,a.fol_jenis,a.fol_waktu,id_biaya,a.fol_lunas
				from klinik.klinik_folio a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.fol_lunas = 'n' order by a.id_reg, a.fol_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);
		
		$row = -1;
		for($i=0,$n=count($dataTable);$i<$n;$i++) {
			 
			if($dataTable[$i]["id_reg"]!=$dataTable[$i-1]["id_reg"] || $dataTable[$i]["fol_jenis"]!=$dataTable[$i-1]["fol_jenis"]) {
				$row++;
				$data[$row] = $dataTable[$i]["id_reg"];
				
				$reg[$dataTable[$i]["id_reg"]] = $dataTable[$i]["id_reg"];
				$fol[$dataTable[$i]["id_reg"]][$row] = $dataTable[$i]["fol_jenis"];
				$biaya[$dataTable[$i]["id_reg"]][$row] = $dataTable[$i]["id_biaya"]; 
				$nama[$dataTable[$i]["id_reg"]] = $dataTable[$i]["cust_usr_nama"];
				$waktu[$dataTable[$i]["id_reg"]] = $dataTable[$i]["fol_waktu"];
			}
		}
		
          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
          
          for($i=0,$nomor=1,$n=count($data),$counter=0;$i<$n;$i++,$counter=0) {
						
			   $editPage = $thisPage."?jenis=".$fol[$data[$i]][$i]."&id_reg=".$reg[$data[$i]]."&waktu=".$waktu[$data[$i]];
				
        if($fol[$data[$i]][$i]==STATUS_PEMERIKSAAN)
				$editPage .= "&biaya=".$biaya[$data[$i]][$i];
				
    			$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$counter++;
    			 
    			$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "right";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$nama[$data[$i]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = $waktu[$data[$i]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$counter++;
    			 
    			$tbContent[$i][$counter][TABLE_ISI] = $rawatStatus[$fol[$data[$i]][$i]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$counter++; 
            }
          
          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
		
	}
        if($_POST["btnSaveTambah"]){
            
            $id_reg = $_POST["id_reg"];
            $skr = $_POST["waktunya"];
           
               $dbTable = "klinik.klinik_folio";
               
               $dbField[0] = "fol_id";   // PK
               $dbField[1] = "id_reg";
               $dbField[2] = "fol_nama";
               $dbField[3] = "fol_nominal";
               $dbField[4] = "id_biaya";
               $dbField[5] = "fol_jenis";
               $dbField[6] = "id_cust_usr";
               $dbField[7] = "fol_waktu";
               $dbField[8] = "fol_lunas";
               $dbField[9] = "fol_dibayar";
               $dbField[10] = "fol_dibayar_when";
               $dbField[11] = "id_biaya_tambahan";
               $dbField[12] = "fol_jumlah";
               
               if(!$folioId) $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
               $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$id_reg);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["biaya_nama"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["fol_jenis"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
               $dbValue[7] = QuoteValue(DPE_DATE,$skr);
               $dbValue[8] = QuoteValue(DPE_CHAR,'n');
               $dbValue[9] = QuoteValue(DPE_NUMERIC,0);
               $dbValue[10] = QuoteValue(DPE_DATE,'');
               $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["biaya_id_tambahan"]);
               $dbValue[12] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);
               			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               if ($_POST["btnSaveTambah"]) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
     
               $editPage = $thisPage."?jenis=".$_POST["fol_jenis"]."&id_reg=".$id_reg."&biaya=".$_POST["biaya_id"];
               header("location:".$editPage);
               exit();        
        
     }
	
	if($_GET["id_reg"]) {
		$sql = "select a.reg_jenis_pasien, cust_usr_alamat, cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin,b.cust_usr_alergi,
				((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr
                    from klinik.klinik_registrasi a 
                    join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
          $dataPasien= $dtaccess->Fetch($sql);
          
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["fol_jenis"] = $_GET["jenis"]; 
		$_POST["id_biaya"] = $_GET["biaya"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		
	
		$sql = "select * from klinik.klinik_folio
			where fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])."
			and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n' "; 
	//echo $sql;
		$rs = $dtaccess->Execute($sql);
		$dataFolio = $dtaccess->FetchAll($rs);
		
		 
		
	}
	
	$sql = "select * from klinik.klinik_folio
			where fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])."
			and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n' "; 
	//echo $sql;
		 $rs_edit = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs_edit);
     //$tableHeader = "&nbsp;Detail menu Pembelian";
     
     $isAllowedDel = $auth->IsAllowed("kasir",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("kasir",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("kasir",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
      $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
      $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
      $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
         $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["fol_id"].'">';               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;

          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          if($dataTable[$i]["fol_jumlah"]){
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_jumlah"]);
          }else{
          $tbContent[$i][$counter][TABLE_ISI] = 1;
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_nominal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          if($dataTable[$i]["fol_jumlah"]){
            $total = $dataTable[$i]["fol_jumlah"]*$dataTable[$i]["fol_nominal"];
          }elseif(!$dataTable[$i]["fol_jumlah"]){
            $total = $dataTable[$i]["fol_nominal"];
          }
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($total);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          $totalHarga+=$total;
          $colspan = count($tbHeader[0]);
     
     
			$tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
		
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan-1;
      
     $tbBottom[0][1][TABLE_ISI] = currency_format($totalHarga);
     $tbBottom[0][1][TABLE_ALIGN] = "right";
  
   }  
		 
     if($_POST["btnDelete"]){
      $folioId = & $_POST["cbDelete"];
      for($i=0,$n=count($folioId);$i<$n;$i++){
          $sql = "DELETE FROM klinik.klinik_folio WHERE fol_id = '".$folioId[$i]."'";
          $dtaccess->Execute($sql);
      }
      //unset($_POST["btnDelete"]);
      $editPage = $thisPage."?id_reg=".$id_reg."&biaya=".$_POST["biaya_id"];
      header("location:".$editPage);
      //exit();
   }		
		

	// ----- update data ----- //
	if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
		$sql = "update klinik.klinik_folio set fol_dibayar = fol_nominal, fol_lunas = 'y', fol_dibayar_when = CURRENT_TIMESTAMP where fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql); 
		$sql = "update klinik.klinik_registrasi set reg_waktu = CURRENT_TIME where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql);
          
	}
	

     
	if($_POST["btnHapus"]) { 
		$sql = "delete from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql);
	    
     }

?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitThickBox(); ?>

<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetFolio('target=antri_kiri_isi');     
     mTimer = setTimeout("timer()", 10000);
}

timer();

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=600,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=600,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}

function CheckDataSave(frm) {
     
     if(document.getElementById('rdMemberTipe_M').checked) {
          if(!document.getElementById('id_member').value) {
               alert('Member harap dipilih');
               return false;
          }
     }
     
     if(document.getElementById('txtLama').value==0) {
          alert('Waktu bermain tidak boleh kosong (0)');
          return false;
     }

     
     if(document.getElementById('txtHargaTotal').value==0) {
          alert('Harga Total tidak boleh kosong (0)');
          return false;
     }
     
     if (document.getElementById('spNamaGuest').style.visibility == 'visible') {
     if(document.getElementById('txtNama').value==0) {
          alert('Nama Guest tidak boleh kosong');
          return false;
     }
          
     if(CheckData(frm.txtNama.value,frm.item_id.value,'type=r')){
      	alert('Nama Guest Sudah Ada Sudah Ada');
    	 	frm.txtNama.focus();
    		frm.txtNama.select();
    		return false;
    	}
      }	
     return true;

}


function CheckSimpan() {
     if(confirm('Cetak Invoice?')) BukaWindow('kasir_cetak.php?jenis=<?php echo $_POST["fol_jenis"];?>&id_reg=<?php echo $_POST["id_reg"];?>','Invoice');
     return true;
}

function GantiHarga(harga) {
     var duit = document.getElementById('txtHargaSatuan').value.toString().replace(/\,/g,"");
     document.getElementById('txtHargaTotal').value = formatCurrency(duit*harga);
}

</script>



<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
		<div class="tableheader">Antrian Kasir</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetFolio(); ?></div>
</div>




<?php if($dataPasien) {  

       
     ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Input Data Pembayaran</td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" >
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
     
          <tr>
               <td width= "20%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "20%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Umur</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Alamat</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo nl2br($dataPasien["cust_usr_alamat"]); ?></label></td>
          </tr>
          <tr>
               <td width= "20%" align="left" class="tablecontent">Jenis Bayar</td>
               <td width= "80%" align="left" class="tablecontent-odd"><label><?php echo $bayarPasien[$dataPasien["reg_jenis_pasien"]]; ?></label></td>
          </tr>
	   </table>
     </fieldset>
     <!--<form name="frmTambah" method="POST" action="<?php //echo $_SERVER["PHP_SELF"]?>" > -->
     <fieldset>
     <legend><strong>Tambah</strong></legend>
      <table width="100%" border="0" cellpadding="1" cellspacing="1">
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Item&nbsp;</td>
                    <td align="left" width="80%" class="tablecontent-odd">
                      <?php echo $view->RenderTextBox("biaya_nama","biaya_nama","30","100",$_POST["biaya_nama"],"inputField", "readonly",false);?>
                      <a href="<?php echo $findPage?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Pilih Item"><img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih Item" alt="Pilih Item" /></a>    
                                   
                </td>
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Jumlah</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("txtJumlah","txtJumlah","3","3",$_POST["txtJumlah"],"curedit", "",false,'onChange=GantiHarga(this.value)');?>
                    </td>					
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Biaya</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("txtHargaSatuan","txtHargaSatuan","10","10",$_POST["txtHargaSatuan"],"curedit", "readonly",false);?>
                    </td>					
               </tr>
               <tr>
                    <td align="left" width="20%" class="tablecontent">&nbsp;Total Biaya</td>
                    <td align="left" width="15%" class="tablecontent-odd">
                         <?php echo $view->RenderTextBox("txtHargaTotal","txtHargaTotal","10","10",$_POST["txtHargaTotal"],"curedit", "readonly",false);?>
                    </td>					
               </tr>
               <tr>
                    <td colspan="4" align="left" class="tblCol">
                         <input type="submit" name="btnSaveTambah" value="Simpan" class="button">
                         <input type="hidden" name="biaya_id_tambahan" id="biaya_id_tambahan" value="<?php echo $_POST["biaya_id_tambahan"];?>" />
                         <input type="hidden" name="fol_jenis" id="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>" />  
                         <input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>    
                         <input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/> 
                         <input type="hidden" name="waktunya" value="<?php echo $_GET["waktu"];?>" />
                         <input type="hidden" name="id_biaya" value="<?php echo $_GET["biaya"];?>" />
                    </td>
               </tr>
            </table>
     </fieldset>
     <!--</form>-->
     
     <fieldset>
     <legend><strong>Data Tagihan</strong></legend>
     <table width="80%" border="1" cellpadding="4" cellspacing="1"> 
							<td colspan="4">
								<span id="div_menu">
									<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
								</span>
							</td>
						</tr>
          <!--<tr class="subheader">
               <td width="5%" align="center">No</td>
               <td width="30%" align="center">Layanan</td>
               <td width="10%" align="center">Jumlah</td>
               <td width="20%" align="center">Biaya</td>
               <td width="20%" align="center">Total</td>
          </tr>	
          <?php  for($i=0,$n=count($dataFolio);$i<$n;$i++) { $total+=$dataFolio[$i]["fol_nominal"];?>
			<tr>
				<td align="right" class="tablecontent"><?php echo ($i+1); ?></td>
				<td align="left" class="tablecontent-odd"><?php echo $dataFolio[$i]["fol_nama"];?></td>
				<td align="right" class="tablecontent-odd"><?php echo "1";?></td>
				<td align="right" class="tablecontent-odd"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
				<td align="right" class="tablecontent-odd"><?php echo currency_format($dataFolio[$i]["fol_nominal"]);?></td>
			</tr>
		<?php } ?>
          <tr>
               <td align="right" class="tablesmallheader" colspan=4>Total</td>
               <td align="right" class="tablesmallheader"><?php echo currency_format($total);?></td>
          </tr>-->
	</table>
     </fieldset>

     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="left">
				<?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Bayar","button",false,'onClick="CheckSimpan()"');?>
				<?php echo $view->RenderButton(BTN_BUTTON,"btnPrint","btnPrint","Cetak","button",false,'onClick="BukaWindow(\'kasir_cetak.php?jenis='.$_POST["fol_jenis"].'&id_reg='.$_POST["id_reg"].'\',\'Cetak Invoice\')"',null);?>
				<?php if($_POST["fol_jenis"] == STATUS_PEMERIKSAAN) { ?>
				      <?php echo $view->RenderButton(BTN_SUBMIT,"btnHapus" ,"btnHapus","Batal Registrasi","button",false,null);?>
			       <?php } ?>
			</td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>	

</table>

<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="fol_jenis" value="<?php echo $_POST["fol_jenis"];?>"/>

<span id="msg">
<? if ($err_code != 0) { ?>
<font color="red"><strong>Periksa lagi inputan yang bertanda (*)</strong></font>
<? }?>
<? if (readbit($err_code,11)) { ?>
<br>
<font color="green"><strong>Nomor Induk harus diisi.</strong></font>
<? } ?>
</span>

</form>

<?php } ?>

<?php echo $view->RenderBodyEnd(); ?>
