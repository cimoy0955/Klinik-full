<?php
     require_once("root.inc.php");
 
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $editPage = "pembayaran_edit.php";
     $thisPage = "pembayaran_view.php";

     if(!$auth->IsAllowed("report_registrasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_registrasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     $sql = "select a.*,c.biaya_nama,d.split_nama,e.nama_prk,f.nama_prk as nama_prk2 from acc.acc_convert a
             left join klinik.klinik_biaya_split b on a.id_bea_split=b.bea_split_id
             left join klinik.klinik_biaya c on b.id_biaya=c.biaya_id
             left join klinik.klinik_split d on b.id_split=d.split_id
             left join gl.gl_perkiraan e on e.id_prk=a.id_prk
             left join gl.gl_perkiraan f on f.id_prk = a.id_prk_kredit
             where a.biaya_jenis = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA).
             " order by a.id_bea_split,a.biaya_jenis";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Kode Pembayaran";
     
     $isAllowedDel = $auth->IsAllowed("report_registrasi",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("report_registrasi",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("report_registrasi",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Biaya";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Split";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya Status";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Acc Debet";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Acc Kredit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
       
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
         
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["split_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$bayarPasien[$dataTable[$i]["biaya_jenis"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["nama_prk"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["nama_prk2"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
                             
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["convert_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
          
         
          
         
     }
     
     $colspan = count($tbHeader[0]);

     
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>
<?php echo $view->SetFocus("btnAdd"); ?>
<?php echo $view->RenderBodyEnd(); ?>
