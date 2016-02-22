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
	
     if(!$auth->IsAllowed("pelaporan_jamkesmas",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("pelaporan_jamkesmas",PRIV_READ)!=1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     $thispage = "pelaporan_jamkesmas_edit.php";
     $backPage = "pelaporan_jamkesmas.php";
	
	   $regId = $_GET["id"];
	   $cek = $_GET["mode"];
	   //echo $cek;
	   
     if ($_GET["mode"]=="Edit") {
          $_x_mode = "Edit";
               
          $sql="select * from klinik.klinik_laporan_jamkesmas where id_reg=".QuoteValue(DPE_CHAR,$regId);
          $rs_laporan=$dtaccess->Execute($sql);
          $row_laporan=$dtaccess->Fetch($rs_laporan);
          
          $_POST["jamkesmas_id"] = $row_laporan["jamkesmas_id"];
          $_POST["jamkesmas_jns_rawat"] = $row_laporan["jamkesmas_jns_rawat"];
          $_POST["jamkesmas_cara_pulang"] = $row_laporan["jamkesmas_cara_pulang"];
          $_POST["jamkesmas_p1"] = $row_laporan["jamkesmas_p1"];
          $_POST["jamkesmas_p2"] = $row_laporan["jamkesmas_p2"];
          $_POST["jamkesmas_h2"] = $row_laporan["jamkesmas_h2"];
          $_POST["jamkes_tarif"] = $row_laporan["jamkesmas_tarif"];
          } 
          
       if($_GET["id"]){  
          $sql = "select a.reg_status, a.reg_tanggal, b.cust_usr_nama, b.cust_usr_kode, f.icd_nomor
          				from klinik.klinik_registrasi a
          				join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
          				inner join klinik.klinik_perawatan d on a.reg_id=d.id_reg
                  inner join klinik.klinik_perawatan_icd e on d.rawat_id=e.id_rawat
                  inner join klinik.klinik_icd f on e.id_icd=f.icd_id
          				where reg_id = ".QuoteValue(DPE_CHAR,$regId); 
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
		
      		$_POST["cust_usr_nama"] = $row_edit["cust_usr_nama"];
      		$_POST["cust_usr_kode"] = $row_edit["cust_usr_kode"];
      		$_POST["reg_tanggal"] = $row_edit["reg_tanggal"];
      		$_POST["reg_status"] = $row_edit["reg_status"];
      		$_POST["h_utama"] = $row_edit["icd_nomor"];
      		    
     }
     
     if($_POST["btnSave"]||$_POST["btnUpdate"]){
        $dbTable = "klinik.klinik_laporan_jamkesmas";
        
        $dbField[0] = "jamkesmas_id";
        $dbField[1] = "jamkesmas_jns_rawat";
        $dbField[2] = "id_reg";
        $dbField[3] = "jamkesmas_cara_pulang";
        $dbField[4] = "jamkesmas_p1";
        $dbField[5] = "jamkesmas_p2";
        $dbField[6] = "jamkesmas_h2";
        $dbField[7] = "jamkesmas_tarif";
          
        if(!$jamkesmasId) $jamkesmasId = $dtaccess->GetTransID("klinik_laporan_jamkesmas","jamkesmas_id",DB_SCHEMA_KLINIK);
        $dbValue[0] = QuoteValue(DPE_CHAR,$jamkesmasId);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["sel_jenis_rawat"]);
        $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["regId"]);
        $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["sel_pulang"]);
        $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["jamkes_p1"]);
        $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["jamkes_p2"]);
        $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["jamkes_h2"]);
        $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jamkes_tarif"]));
        
        $dbKey[0] = 0;
        
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        
        if($_POST["btnSave"]){
               $dtmodel->Insert() or die("insert error");
          }
        
        if($_POST["btnUpdate"]){
               $dtmodel->Update() or die("update error");
          }
          
          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          

		    header("location:".$backPage);
		    exit(0);        
     }
   
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>
<script type="text/javascript">

function ubahTarif(){
    var j_rawat = document.getElementById('sel_jenis_rawat').value;
    var h_utama = document.getElementById('jamkes_h_utama').value;
    var p1 = document.getElementById('jamkes_p1').value;
    var p2 = document.getElementById('jamkes_p2').value;
    h_utama = String.trim(h_utama);
    
    if(j_rawat=="2"){ //--rawat jalan
    if((h_utama=="H25.6") && (p1=="16.29") && (p2=="")){
        document.getElementById('jamkes_tarif').value= formatCurrency(131053);
      }
    if((h_utama=="H25.0")&&(p1=="13.69")&&(p2=="16.29")){
          document.getElementById('jamkes_tarif').value= formatCurrency(707396);
        }
    if((h_utama.substr(0,3)=="Z96")&&(p1=="16.21")&&(p2=="")){
          document.getElementById('jamkes_tarif').value= formatCurrency(143857);
      }
    if((h_utama=="H11.0")&&(p1=="16.21")&&(p2=="")){
          document.getElementById('jamkes_tarif').value=formatCurrency(143857);
    }    
    if((h_utama=="H11.1")&&(p1=="9.23")&&(p2=="")){
          document.getElementById('jamkes_tarif').value= formatCurrency(535193);
      }
    if((h_utama=="H11.2")&&(p1=="11.32")&&(p2=="")){
          document.getElementById('jamkes_tarif').value= formatCurrency(139134);
      }
    if((h_utama.substr(0,3)=="H52")&&(p1=="95.31")||(p1=="")){
          document.getElementById('jamkes_tarif').value= formatCurrency(0);
      }
    if((h_utama=="H40.9")&&(p1=="16.21")&&(p2=="")){
          document.getElementById('jamkes_tarif').value= formatCurrency(143857);
      }    
    if((h_utama=="")&&(p1=="95.13")&&(p2=="")){
          document.getElementById('jamkes_tarif').value= formatCurrency(139676);
      }
    if((h_utama=="H10.9")&&(p1=="16.21")&&(p2=="")){
          document.getElementById('jamkes_tarif').value= formatCurrency(143857);
      }
    }else if(j_rawat=="1"){     //--rawat inap
    if((h_utama=="H25.0")&&(p1=="13.69")&&(p2=="13.71")){
          document.getElementById('jamkes_tarif').value= formatCurrency(1078719);
        }
    if((h_utama=="H41.10")&&(p1=="")&&(p2=="")){
          document.getElementById('jamkes_tarif').value= formatCurrency(0);
      }
    if((h_utama=="H45.1")&&(p1=="")&&(p2=="")){
          document.getElementById('jamkes_tarif').value= formatCurrency(1628565);
      }
    }
    //document.write(j_rawat);
}

</script>
<table width="100%" border="1" cellpadding="1" cellspacing="1">
    <tr class="tableheader">
        <td width="100%">&nbsp;Edit Jamkesmas</td>
    </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>Status Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>No.Registrasi</strong>&nbsp;</td>
               <td width="70%"> 
				          <?php echo $_POST["cust_usr_kode"];?>
               </td>
          </tr>  
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Nama Pasien</strong>&nbsp;</td>
               <td width="70%"> 
				          <?php echo $_POST["cust_usr_nama"];?>
               </td>
          </tr>  
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Tanggal Masuk</strong>&nbsp;</td>
               <td width="70%"> 
				          <?php echo date_db($_POST["reg_tanggal"]);?>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%" onBlur="javascript: ubahTarif()"><strong>Jenis Perawatan</strong>&nbsp;</td>
               <td width="70%"> 
				<select name="sel_jenis_rawat" id="sel_jenis_rawat" >  
					 <option value="2" <?php if($_POST["jamkesmas_jns_rawat"]=="2") echo "selected"; ?>>Rawat Jalan</option>
			     <option value="1" <?php if($_POST["jamkesmas_jns_rawat"]=="2") echo "selected"; ?>>Rawat Inap</option>
				</select>
               </td>
          </tr>  
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Cara Pulang</strong>&nbsp;</td>
               <td width="70%"> 
				<select name="sel_pulang" id="sel_pulang" >  
					 <option value="1" <?php if($_POST["jamkesmas_cara_pulang"]=="1") echo "selected"; ?>>Sembuh</option>
			     <option value="2" <?php if($_POST["jamkesmas_cara_pulang"]=="2") echo "selected"; ?>>Pulang Paksa</option>
			     <option value="3" <?php if($_POST["jamkesmas_cara_pulang"]=="3") echo "selected"; ?>>Mati</option>
				</select>
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>HUtama</strong>&nbsp;</td>
               <td width="70%"> 
				          <?php echo $_POST["h_utama"]; ?>
				          <input type="hidden" name="jamkes_h_utama" id="jamkes_h_utama" value="<?php echo $_POST["h_utama"];?>"> 
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>H2</strong>&nbsp;</td>
               <td width="70%"> 
				            <input type="text" name="jamkes_h2" id="jamkes_h2" value="<?php echo $_POST["jamkesmas_h2"];?>">
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>P1</strong>&nbsp;</td>
               <td width="70%"> 
				            <input type="text" name="jamkes_p1" id="jamkes_p1" value="<?php echo $_POST["jamkesmas_p1"];?>" onChange="javascript: ubahTarif()" onFocus="javascript: ubahTarif(this)">
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>P2</strong>&nbsp;</td>
               <td width="70%"> 
				            <input type="text" name="jamkes_p2" id="jamkes_p2" value="<?php echo $_POST["jamkesmas_p2"];?>" onChange="javascript: ubahTarif()">
               </td>
          </tr>
          <tr>
               <td align="right" class="tablecontent" width="30%"><strong>Besaran Tarif</strong>&nbsp;</td>
               <td width="70%"> 
				            <?php echo $view->RenderTextBox("jamkes_tarif","jamkes_tarif","20","255",currency_format($_POST["jamkes_tarif"]),"curedit",null,true);?>
               </td>
          </tr>
          <input type="hidden" name="tgl_awal" id="tgl_awal" value="<?php echo $_GET["tgl_awal"];?>">
          <input type="hidden" name="regId" id="regId" value="<?php echo $regId; ?>">
          <tr>
               <td colspan="2" align="right">
                    <input type="submit" name="<? if($_x_mode == "Edit"){echo "btnUpdate";}else{echo "btnSave";} ?>" id="btnSave" value="Simpan" class="button" />
                    <?php// echo $view->RenderButton(BTN_SUBMIT,"btnSave","btnSave","Simpan","button",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","button",false,"onClick=\"document.location.href='pelaporan_jamkesmas.php?';\" ");?>                    
               </td>
          </tr>
     </table>
     </fieldset>
     </td>
</tr>
</table>

<script>document.frmEdit.jamkes_p1.focus();</script> 
<?php echo $view->RenderHidden("reg_id","reg_id",$regId);?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
</form>

<?php echo $view->RenderBodyEnd(); ?>
