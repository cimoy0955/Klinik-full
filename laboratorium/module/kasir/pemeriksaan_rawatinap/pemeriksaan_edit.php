<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $usrID = $auth->GetUserID();
     
     $thisPage = "pemeriksaan_edit.php";
     $findPage = "pasien_find.php?";

     if(!$_POST["pemeriksaan_total"]) $_POST["pemeriksaan_total"] = 0;
     
     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     $sql = "select * from lab_kegiatan a 
             left join lab_kategori b on b.kategori_id = a.id_kategori
             left join lab_bonus c on c.bonus_id = a.id_bonus
             order by b.kategori_nama ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     $dataTable = $dtaccess->FetchAll($rs);
     
     for($r=0;$r<count($dataTable);$r++){
        $sub_total[$dataTable[$r]["kegiatan_id"]] = $dataTable[$r]["kegiatan_biaya"];
        $total_kegiatan += $dataTable[$r]["kegiatan_biaya"];
     }
     
     if($_POST["btnSave"])
     {
          $sql = "select a.cust_usr_id, a.cust_usr_nama, b.reg_id
                  from global.global_customer_user a
                  left join klinik.klinik_registrasi b on b.id_cust_usr = a.cust_usr_id
                 where cust_usr_id = ".$_POST["cust_usr_id"];
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          $dataReg = $dtaccess->Fetch($rs);     
     
        $dbTable = "lab_pemeriksaan";
        
        $dbField[0] = "pemeriksaan_id";
        $dbField[1] = "id_reg";
        $dbField[2] = "pemeriksaan_pasien_nama";
        $dbField[3] = "id_dokter";
        $dbField[4] = "pemeriksaan_total";
        $dbField[5] = "who_update";
        $dbField[6] = "pemeriksaan_create";
        $dbField[7] = "id_cust_usr";
        $dbField[8] = "pemeriksaan_bayar";
        $dbField[9] = "pemeriksaan_rawatinap";

        
        if(!$_POST["pemeriksaan_id"]) $_POST["pemeriksaan_id"] = $dtaccess->GetTransID("lab_pemeriksaan","pemeriksaan_id",DB_SCHEMA_LAB);
        $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["pemeriksaan_id"]);
        $dbValue[1] = QuoteValue(DPE_CHAR,$dataReg["reg_id"]);
        $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama"]);
        $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
        $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["pemeriksaan_total"]));
        $dbValue[5] = QuoteValue(DPE_NUMERIC,$usrID);
        $dbValue[6] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
        $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["cust_usr_id"]);
        $dbValue[8] = QuoteValue(DPE_CHAR,"n");
        $dbValue[9] = QuoteValue(DPE_CHAR,'y');
        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LAB);

        if ($_POST["btnSave"]) {
            $dtmodel->Insert() or die("insert  error");	
          
        } else if ($_POST["btnUpdate"]) {
            $dtmodel->Update() or die("update  error");	
        }
        
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
        unset($dbTable);
        
        
        $dbTable = "lab_pemeriksaan_detail";
        
        $dbField[0] = "periksa_det_id";
        $dbField[1] = "id_pemeriksaan";
        $dbField[2] = "id_kegiatan";
        $dbField[3] = "periksa_det_total";
        $dbField[4] = "who_update";
        
        $kegiatanID = & $_POST["cbPilih"];
        for($i=0,$n=count($kegiatanID);$i<$n;$i++){
            $periksa_det_id = $dtaccess->GetTransID("lab_pemeriksaan_detail","periksa_det_id",DB_SCHEMA_LAB);
            $dbValue[0] = QuoteValue(DPE_CHAR,$periksa_det_id);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pemeriksaan_id"]);
            $dbValue[2] = QuoteValue(DPE_CHAR,$kegiatanID[$i]);
            $dbValue[3] = QuoteValue(DPE_NUMERIC,$sub_total[$kegiatanID[$i]]);
            $dbValue[4] = QuoteValue(DPE_NUMERIC,$usrID);
        
        
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LAB);
    
            if ($_POST["btnSave"]) {
                $dtmodel->Insert() or die("insert  error");	
              
            } else if ($_POST["btnUpdate"]) {
                $dtmodel->Update() or die("update  error");	
            }
            
            unset($dtmodel);
            unset($dbValue);
            unset($dbKey);
        }
        
        unset($dbField);
        unset($dbTable);
        
    if($_POST["id_dokter"]){     
        $dbTable = "lab_hasil_bonus";
        
        $dbField[0] = "bonus_hasil_id";
        $dbField[1] = "bonus_hasil_tanggal";
        $dbField[2] = "bonus_hasil_nominal";
        $dbField[3] = "id_dokter";
        $dbField[4] = "pasien_nama";
        $dbField[5] = "id_kegiatan";
        
       
        $kegiatanID = & $_POST["cbPilih"];
        for($i=0,$n=count($kegiatanID);$i<$n;$i++){
        
        $sql = "select * from laboratorium.lab_kegiatan a
                left join laboratorium.lab_bonus_dokter b on a.id_bonus = b.id_bonus
                where b.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"])."
                and a.kegiatan_id = ".QuoteValue(DPE_CHAR,$kegiatanID[$i]);        
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     $dataBonus = $dtaccess->Fetch($rs);
        
        //for($z=0,$y=count($dataBonus);$z<$y;$z++){
          $bonus = $dataBonus["bonus_dokter_persen"]*$dataBonus["kegiatan_biaya"]/100;
        //}
            $bonus_hasil_id = $dtaccess->GetTransID("lab_hasil_bonus","bonus_hasil_id",DB_SCHEMA_LAB);
            $dbValue[0] = QuoteValue(DPE_CHAR,$bonus_hasil_id);
            $dbValue[1] = QuoteValue(DPE_DATE,date('Y-m-d'));
            $dbValue[2] = QuoteValue(DPE_NUMERIC,$bonus);
            $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$kegiatanID[$i]);
        
        
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LAB);
    
            if ($_POST["btnSave"]) {
                $dtmodel->Insert() or die("insert  error");	
              
            } else if ($_POST["btnUpdate"]) {
                $dtmodel->Update() or die("update  error");	
            }
            
            unset($dtmodel);
            unset($dbValue);
            unset($dbKey);
        }
        
        unset($dbField);
        unset($dbTable);
      }  
        $cetakPage = "pemeriksaan_lihat.php?pemeriksaan_id=".$_POST["pemeriksaan_id"];
        header ("location:".$cetakPage);
        
     }
     
    // if($_POST["id_dokter"]){
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Layanan Pemeriksaan";
     
     $isAllowedDel = $auth->IsAllowed("registrasi",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("registrasi",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("registrasi",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbPilih[]');\" onChange=\"TotalSemua(this,'".$total_kegiatan."')\">";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Bonus";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
      
     
     $k=0;
     for($i=0,$j=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$j++,$counter=0){
       if($dataTable[$i]["kategori_nama"]!=$dataTable[$i-1]["kategori_nama"]){
         $k++;
         $tbContent[$j][$counter][TABLE_ISI] = $k.".&nbsp;";            
         $tbContent[$j][$counter][TABLE_CLASS] = "tablesmallheader";
         $tbContent[$j][$counter][TABLE_ALIGN] = "right";
         $tbContent[$j][$counter][TABLE_CLASS] = "tablecontent";
         $counter++;
         
         $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$dataTable[$i]["kategori_nama"];            
         $tbContent[$j][$counter][TABLE_CLASS] = "tablesmallheader";
         $tbContent[$j][$counter][TABLE_ALIGN] = "left";
         $tbContent[$j][$counter][TABLE_COLSPAN] = count($tbHeader[0])-1;
         $tbContent[$j][$counter][TABLE_CLASS] = "tablecontent";
         $counter=0; $j++;
       }
        
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;";               
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++;
          
          $tbContent[$j][$counter][TABLE_ISI] = '<input type="checkbox" name="cbPilih[]" value="'.$dataTable[$i]["kegiatan_id"].'" onChange="UpdateTotal(this,\''.$dataTable[$i]["kegiatan_biaya"].'\');" />';               
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++;
          
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kegiatan_nama"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";
          $tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++; 
     
          $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["bonus_nama"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++; 
     
          $tbContent[$j][$counter][TABLE_ISI] = "Rp.&nbsp;".currency_format($dataTable[$i]["kegiatan_biaya"])."&nbsp;&nbsp;";
          $tbContent[$j][$counter][TABLE_ALIGN] = "right";
          $tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++;
          
 /*         $bonus = $dataTable[$i]["bonus_dokter_persen"]*$dataTable[$i]["kegiatan_biaya"]/100;
          
          $tbContent[$j][$counter][TABLE_ISI] = "Rp.&nbsp;".currency_format($bonus)."&nbsp;&nbsp;";
          $tbContent[$j][$counter][TABLE_ALIGN] = "right";
          $tbContent[$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++;  */
     }
     
     $colspan = count($tbHeader[0]);

     $tbBottom[0][0][TABLE_ISI] = "Biaya Total&nbsp;";
     $tbBottom[0][0][TABLE_WIDTH] = "30%";
     $tbBottom[0][0][TABLE_ALIGN] = "right";
     $tbBottom[0][0][TABLE_COLSPAN] = "3";
     
     $tbBottom[0][1][TABLE_ISI] = $view->RenderTextBox("pemeriksaan_total","pemeriksaan_total","20","100",$_POST["pemeriksaan_total"],"curedit","readonly",true);
     $tbBottom[0][1][TABLE_WIDTH] = "70%";
     $tbBottom[0][1][TABLE_COLSPAN] = count($tbHeader[0])-3;
     
     $tbBottom[1][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnSave" value="Simpan" class="button">&nbsp;';
     $tbBottom[1][0][TABLE_WIDTH] = "100%";
     $tbBottom[1][0][TABLE_ALIGN] = "center";
     $tbBottom[1][0][TABLE_COLSPAN] = $colspan;
     
//  }   
     //-- membuat option untuk combo dokter --//
     $sql = "select * from lab_dokter order by id_divisi";
     $rs_dokter = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
     $div_before = "";
     $opt_dokter[] = $view->RenderOption("--","Permintaan Sendiri","selected");
     while($data_dokter = $dtaccess->Fetch($rs_dokter)){
        if($data_dokter["id_divisi"]!=$div_before) $opt_dokter[] = "<optgroup style=\"font-family:sans-serif;font-style:normal;\" label=\"".$divisi_dokter[$data_dokter["id_divisi"]]."\">";
        $opt_dokter[] = $view->RenderOption($data_dokter["dokter_id"],$data_dokter["dokter_nama"],($data_dokter["dokter_id"]==$_POST["id_dokter"])?"selected":"");
        $div_before = $data_dokter["id_divisi"];
     }     
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>
<?php echo $view->InitThickBox(); ?>
<script  language="javascript" type="text/javascript">

function UpdateTotal(elm,biaya)
{
  var total_nya = document.getElementById('pemeriksaan_total').value.toString().replace(/\,/g,"")*1;
  var biaya_nya = biaya*1;
  if(elm.checked==true)
  {
    total_baru = total_nya + biaya_nya;
  }else{
    total_baru = total_nya - biaya_nya;
  }
  
  document.getElementById('pemeriksaan_total').value= formatCurrency(total_baru)
}

function TotalSemua(elm,biaya)
{
  var biaya_nya = biaya*1;
  if(elm.checked==true)
  {
    document.getElementById('pemeriksaan_total').value = formatCurrency(biaya_nya);
  }else{
    document.getElementById('pemeriksaan_total').value = 0;
  }
}

</script>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <table width="80%" cellpadding="0" cellspacing="0">
          <tr>
               <td style="text-align:right" class="tablecontent" width="30%"><strong>Nama Pasien&nbsp;</strong></td>
               <td class="tablecontent-odd">&nbsp;<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama","35","255",$_POST["cust_usr_nama"],"inputField", "readonly",false);?>
                    <?php echo $view->RenderHidden("cust_usr_id","cust_usr_id",$_POST["cust_usr_id"]); ?>
                    <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=450&modal=true&outlet=<?php echo $outlet; ?>" class="thickbox" title="Pilih item"><img src="<?php echo $APLICATION_ROOT;?>images/b_select.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Pilih item" alt="Pilih item" /></a>  
                    <?php echo $view->RenderHidden("id_reg","id_reg",$_POST["id_reg"]); ?>
               </td>
          </tr>
      <tr>
        <td class="tablecontent" style="text-align:right">Nama Dokter&nbsp;</td>
        <td class="tablecontent-odd" onKeyDown="return tabOnEnter_select(this, event);">&nbsp;<?php echo $view->RenderComboBox("id_dokter","id_dokter",$opt_dokter,"inputField",null,"onKeyDown=\"return tabOnEnter(this, event);\""); ?></td>
      </tr>
        <td colspan="2">
          <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
        </td>
     </table>
</form>

<?php echo $view->RenderBodyEnd(); ?>
