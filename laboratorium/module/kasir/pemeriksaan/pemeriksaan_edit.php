<?php
    /* 29-12-2015
     * rencana perubahan:
     * tambahkan antrian dari setiap poli
     */

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
     $tablePasien = new InoTable("table1","99%","center");
     $usrID = $auth->GetUserID();

     $thisPage = "pemeriksaan_edit.php";
     $dokterPage = "lab_dokter_find.php?";
     $findPage = "pasien_find.php?";

     if(!$_POST["pemeriksaan_total"]) $_POST["pemeriksaan_total"] = 0;
     
     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     $plx = new InoLiveX("GetAntrian,SetAntrian");     
     
     function GetAntrian($status) {
          global $dtaccess, $view, $tablePasien, $thisPage, $APLICATION_ROOT,$rawatStatus;

          $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                    where a.reg_status like '".STATUS_LABORATORIUM.$status."' and a.reg_tipe_umur='D'
                    order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;

          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
            if($status==0) {
              $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesPerawatan(\''.$dataTable[$i]["reg_id"].'\',\''.$dataTable[$i]["reg_status"]{0}.'\')"/>';
            } else {
              $tbContent[$i][$counter][TABLE_ISI] = "<a href=\"".$thisPage."?id_reg=".$dataTable[$i]["reg_id"]."&status=".$dataTable[$i]["reg_status"]{0}."\"><img hspace=\"2\" width=\"16\" height=\"16\" src=\"".$APLICATION_ROOT."images/b_select.png\" alt=\"Proses\" title=\"Proses\" border=\"0\"/></a>";
            }
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = $rawatStatus[$dataTable[$i]["reg_status"]{0}];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }

          return $tablePasien->RenderView($tbHeader,$tbContent,$tbBottom);
    
  }

   function SetAntrian($id,$status) {
    global $dtaccess;

    $sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_LABORATORIUM.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
    $dtaccess->Execute($sql);

    return true;
  }

    if ($_GET["id_reg"]) {

      $sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, 
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
                    from klinik.klinik_registrasi a
                    left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
                    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                    where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
      $dataPasien= $dtaccess->Fetch($sql);
         
      $_POST["id_reg"] = $_GET["id_reg"]; 
      $_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
      $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
      $_POST["rawat_keluhan"] = $dataPasien["ref_keluhan"];
  
      $sql = "select * from lab_kegiatan a 
              left join lab_kategori b on b.kategori_id = a.id_kategori
              left join lab_bonus c on c.bonus_id = a.id_bonus
              where a.is_active = 'y' and b.is_active = 'y' and c.is_active = 'y'
              order by a.id_kategori ";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
      $dataTable = $dtaccess->FetchAll($rs);
      // echo $sql;
      for($r=0;$r<count($dataTable);$r++){
         $sub_total[$dataTable[$r]["kegiatan_id"]] = $dataTable[$r]["kegiatan_biaya"];
         $total_kegiatan += $dataTable[$r]["kegiatan_biaya"];
      }
    } 

     if($_POST["btnSave"])
     {
        $dbTable = "lab_pemeriksaan";
        
        $dbField[0] = "pemeriksaan_id";
        $dbField[1] = "id_reg";
        $dbField[2] = "id_dokter";
        $dbField[3] = "pemeriksaan_total";
        $dbField[4] = "who_update";
        $dbField[5] = "pemeriksaan_create";
        $dbField[6] = "id_cust_usr";
        
        if(!$_POST["pemeriksaan_id"]) $_POST["pemeriksaan_id"] = $dtaccess->GetTransID("lab_pemeriksaan","pemeriksaan_id",DB_SCHEMA_LAB);
        $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["pemeriksaan_id"]);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
        $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["id_dokter"]);
        $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["pemeriksaan_total"]));
        $dbValue[4] = QuoteValue(DPE_NUMERIC,$usrID);
        $dbValue[5] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
        $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["id_cust_usr"]);
        
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
        
        //masukan biaya lab ke folio
          $sql = "select a.pemeriksaan_id , a.id_reg , a.id_cust_usr , c.kegiatan_nama , c.kegiatan_id , c.kegiatan_biaya  
                  from laboratorium.lab_pemeriksaan a 
                  left join laboratorium.lab_pemeriksaan_detail b on b.id_pemeriksaan = a.pemeriksaan_id 
                  left join laboratorium.lab_kegiatan c on c.kegiatan_id = b.id_kegiatan 
                  where pemeriksaan_id = ".QuoteValue(DPE_CHAR,$pemeriksaanId);  
          $rs = $dtaccess->Execute($sql);
          $dataKegiatan = $dtaccess->FetchAll($rs);

          for($i=0,$n=count($dataKegiatan);$i<$n;$i++) {
          
          $dbTable = "klinik.klinik_folio";
          $dbField[0] = "fol_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "fol_nama";
          $dbField[3] = "fol_nominal";
          $dbField[4] = "fol_jenis";
          $dbField[5] = "id_cust_usr";
          $dbField[6] = "fol_waktu";
          $dbField[7] = "fol_lunas";
          $dbField[8] = "id_biaya";
          $dbField[9] = "fol_jumlah";
                    $dbField[10] = "fol_nominal_satuan";
          
          
               $folId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$folId);
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$dataKegiatan[$i]["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHARKEY,$dataKegiatan[$i]["kegiatan_nama"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($dataKegiatan[$i]["kegiatan_biaya"]));
               $dbValue[4] = QuoteValue(DPE_CHARKEY,STATUS_LABORATORIUM);
               $dbValue[5] = QuoteValue(DPE_CHARKEY,$dataKegiatan[$i]["id_cust_usr"]);
               $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
               $dbValue[7] = QuoteValue(DPE_CHARKEY,"n");
               $dbValue[8] = QuoteValue(DPE_CHARKEY,$dataKegiatan[$i]["kegiatan_id"]);
               $dbValue[9] = QuoteValue(DPE_NUMERIC,'1');
                    $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($dataKegiatan[$i]["kegiatan_biaya"]));

               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               $dtmodel->Update() or die("insert  error");
               
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
        }

    /*if($_POST["id_dokter"]){     
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
      }  */

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
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Pilih";
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
      
     
     
     for($i=0,$j=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$j++,$counter=0){
       if($dataTable[$i]["kategori_nama"]!=$dataTable[$i-1]["kategori_nama"]){
          $j=0;
       }
        
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ISI] = $j+1;               
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ALIGN] = "center";
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++;
          
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ISI] = '<input type="checkbox" name="cbPilih[]" value="'.$dataTable[$i]["kegiatan_id"].'" onChange="UpdateTotal(this,\''.$dataTable[$i]["kegiatan_biaya"].'\');" />';               
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ALIGN] = "center";
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++;
          
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kegiatan_nama"];
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ALIGN] = "left";
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++; 
     
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["bonus_nama"];
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ALIGN] = "center";
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++; 
     
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ISI] = "Rp.&nbsp;".currency_format($dataTable[$i]["kegiatan_biaya"])."&nbsp;&nbsp;";
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_ALIGN] = "right";
          $tbContent[$dataTable[$i]["kategori_id"]][$j][$counter][TABLE_CLASS] = "tablecontent-odd";
          $counter++;
          
 /*         $bonus = $dataTable[$i]["bonus_dokter_persen"]*$dataTable[$i]["kegiatan_biaya"]/100;
          
          $$tbContent["kategori_id"][$j][$counter][TABLE_ISI] = "Rp.&nbsp;".currency_format($bonus)."&nbsp;&nbsp;";
          $$tbContent["kategori_id"][$j][$counter][TABLE_ALIGN] = "right";
          $$tbContent["kategori_id"][$j][$counter][TABLE_CLASS] = "tablecontent-odd";
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
     
     $tbBottom[1][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnSave" value="Lanjut" class="button">&nbsp;';
     $tbBottom[1][0][TABLE_WIDTH] = "100%";
     $tbBottom[1][0][TABLE_ALIGN] = "center";
     $tbBottom[1][0][TABLE_COLSPAN] = $colspan;
     
//  }   
     
     $sql = "select * from laboratorium.lab_kategori where is_active = 'y' order by kategori_nama ";
     $rsKategori = $dtaccess->Execute($sql);

?>

<?php echo $view->RenderBody("inosoft.css",false); ?>
<?php echo $view->InitThickBox(); ?>

<!-- link css untuk tampilan tab ala winXP -->
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/anylink.css">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/winxp.css" />
<!-- link jscript untuk fungsi-fungsi tab dasar -->
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/listener.js"></script> 
<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/tabs.js"></script>  

<script  language="javascript" type="text/javascript">
<?php $plx->Run(); ?>
var mTimer;

function timer(){     
  clearInterval(mTimer);      
  GetAntrian(0,'target=antri_kiri_isi');     
  GetAntrian(1,'target=antri_kanan_isi');     
  mTimer = setTimeout("timer()", 10000);
}

function ProsesPerawatan(id) {
  SetAntrian(id,'type=r');
  timer();
}

timer();

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
<?php if(!$_GET["id"]) { ?>
<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
  <div id="antri_kiri" style="float:left;width:49%;">
    <div class="tableheader">Antrian Laboratorium</div>
    <div id="antri_kiri_isi" style="height:100;overflow:auto"></div>
  </div>

  <div id="antri_kanan" style="float:right;width:49%;">
    <div class="tableheader">Proses Laboratorium</div>
    <div id="antri_kanan_isi" style="height:100;overflow:auto"></div>
  </div>
</div>

<?php } ?>

<?php if($dataPasien) { ?>
<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <fieldset>
     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
        <tr>
          <td width= "30%" align="left" class="tablecontent">Kode Pasien</td>
          <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
        </tr> 
        <tr>
             <td width= "30%" align="left" class="tablecontent">Nama Lengkap</td>
             <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; echo $view->RenderHidden("cust_usr_nama","cust_usr_nama",$dataPasien["cust_usr_nama"]); ?></label></td>
        </tr>
        <tr>
             <td width= "30%" align="left" class="tablecontent">Umur</td>
             <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
        </tr>
        <tr>
             <td width= "30%" align="left" class="tablecontent">Jenis Kelamin</td>
             <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
        </tr>
        <tr>
             <td width="20%"  class="tablecontent" align="left">Dokter</td>
             <td align="left" class="tablecontent-odd" width="80%"> 
                  <?php echo $view->RenderTextBox("lab_dokter_nama","lab_dokter_nama","30","100",$_POST["lab_dokter_nama"],"inputField", "readonly",false);?>
                  <a href="<?php echo $dokterPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                  <input type="hidden" id="id_dokter" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
             </td>
        </tr>
        <tr>
          <td colspan="2">
            <?php 
              echo "<div class=\"tabsystem\"> ";
              while ($dataKategori = $dtaccess->Fetch($rsKategori)) {
                echo    "<div class=\"tabpage\">";
                echo    "<h2>&nbsp;<a style=\"text-decoration: none;\" title=\"".$dataKategori["kategori_nama"]."\">".$dataKategori["kategori_nama"]."</a>&nbsp;</h2>";
                echo $table->RenderView($tbHeader,$tbContent[$dataKategori["kategori_id"]],null);
                echo "</div>"; 
              }
              // echo "</div>";
              echo $table->RenderView(null,null,$tbBottom);

            ?>
          </td>
        </tr>
     </table>
     <?php echo $view->RenderHidden("id_cust_usr","id_cust_usr",$_POST["id_cust_usr"]);?>
</form>
<?php } ?>
<?php echo $view->RenderBodyEnd(); ?>