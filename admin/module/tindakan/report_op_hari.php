<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $thisPage = "report_op_hari.php";
     $sekarang = getDateToday();

     if(!$auth->IsAllowed("report_op_hari",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_op_hari",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } 
     
     $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu,cast(c.op_waktu as time) as waktunya,cust_usr_kode 
              from klinik.klinik_registrasi a 
              join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
              left join klinik.klinik_operasi c on c.id_reg=a.reg_id
              where cast(c.op_waktu as date)=".QuoteValue(DPE_DATE,$sekarang)."
              order by reg_status desc, reg_tanggal asc, reg_waktu asc ";
     //echo $sql;
     $dataTable = $dtaccess->FetchAll($sql); 
          
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Pasien Operasi Hari Ini";

      
     $counterHeader = 0;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu Operasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
     
     for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
          $tbContent[$i][$counter][TABLE_ISI] = ($i+1);               
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["waktunya"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
     }

     $colspan = count($tbHeader[0]);
     
     if(!$_POST["btnExcel"]){
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
          $tbBottom[0][0][TABLE_ALIGN] = "center";
     }
     
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_operasi_hari_ini_'.$_POST["tgl_awal"].'.xls');
     }

?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",false); ?>
<?php } ?> 
<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
     <tr class="tablesmallheader">
          <td style="text-align:right"><?php echo getDayName($sekarang).", ".format_date($sekarang);?>&nbsp;&nbsp;</td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</form> 
<?php echo $view->RenderBodyEnd(); ?>
