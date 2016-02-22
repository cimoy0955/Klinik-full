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
 
     $thisPage = "report_refraksi.php";

     if(!$auth->IsAllowed("report_refraksi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_refraksi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y"); 
     $sql_where[] = "c.ref_tanggal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     
     $sql = "select b.cust_usr_nama, c.ref_pinhole_od, c.ref_pinhole_os, c.ref_mata_os_koreksi_spheris,
              c.ref_mata_od_koreksi_spheris, c.ref_mata_od_koreksi_cylinder, c.ref_mata_os_koreksi_cylinder,
              c.ref_mata_od_koreksi_sudut, c.ref_mata_os_koreksi_sudut,
              d.visus_nama as nk_od, e.visus_nama as nk_os, f.visus_nama as k_od, g.visus_nama as k_os,
              c.ref_prisma_koreksi_dioptri, c.ref_prisma_koreksi_base1, c.ref_prisma_koreksi_base2
              from klinik.klinik_registrasi a 
              left join global.global_customer_user b 
              on a.id_cust_usr = b.cust_usr_id 
              join klinik.klinik_refraksi c on c.id_reg = a.reg_id 
              left join klinik.klinik_visus d on d.visus_id=c.id_visus_nonkoreksi_od
              left join klinik.klinik_visus e on e.visus_id=c.id_visus_nonkoreksi_os
              left join klinik.klinik_visus f on f.visus_id=c.id_visus_koreksi_od
              left join klinik.klinik_visus g on g.visus_id=c.id_visus_koreksi_os";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " order by a.reg_status_pasien, b.cust_usr_nama"; 
     $dataTable = $dtaccess->FetchAll($sql);
     //echo $sql;
 
     
     
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Pasien Refraksi";

     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "3";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "3";    
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Visus Tanpa Koreksi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";
     $counterHeader++; 
     
             $counHeader = 0;
             $tbHeader[1][$counHeader][TABLE_ISI] = "Visus OD";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%";
             $tbHeader[1][$counHeader][TABLE_ROWSPAN] = "2";     
             $counHeader++;
             
             $tbHeader[1][$counHeader][TABLE_ISI] = "Visus OS";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%"; 
             $tbHeader[1][$counHeader][TABLE_ROWSPAN] = "2";    
             $counHeader++; 
             
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Koreksi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "6";
     $counterHeader++; 
     
             
             $tbHeader[1][$counHeader][TABLE_ISI] = "Spheris";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%";  
             $tbHeader[1][$counHeader][TABLE_COLSPAN] = "2";   
             $counHeader++;
                 
                 $cHeader = 0;
                 $tbHeader[2][$cHeader][TABLE_ISI] = "OD";
                 $tbHeader[2][$cHeader][TABLE_WIDTH] = "5%";     
                 $cHeader++;
                 
                 $tbHeader[2][$cHeader][TABLE_ISI] = "OS";
                 $tbHeader[2][$cHeader][TABLE_WIDTH] = "5%";     
                 $cHeader++;
             
             $tbHeader[1][$counHeader][TABLE_ISI] = "Cylinder";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%";
             $tbHeader[1][$counHeader][TABLE_COLSPAN] = "2";     
             $counHeader++;
             
                 $tbHeader[2][$cHeader][TABLE_ISI] = "OD";
                 $tbHeader[2][$cHeader][TABLE_WIDTH] = "5%";     
                 $cHeader++;
                 
                 $tbHeader[2][$cHeader][TABLE_ISI] = "OS";
                 $tbHeader[2][$cHeader][TABLE_WIDTH] = "5%";     
                 $cHeader++; 
             
             $tbHeader[1][$counHeader][TABLE_ISI] = "Sudut";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%";
             $tbHeader[1][$counHeader][TABLE_COLSPAN] = "2";     
             $counHeader++; 
             
                 $tbHeader[2][$cHeader][TABLE_ISI] = "OD";
                 $tbHeader[2][$cHeader][TABLE_WIDTH] = "5%";     
                 $cHeader++;
                 
                 $tbHeader[2][$cHeader][TABLE_ISI] = "OS";
                 $tbHeader[2][$cHeader][TABLE_WIDTH] = "5%";     
                 $cHeader++;
             
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Visus Dengan Koreksi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";
     $counterHeader++; 
     
             
             $tbHeader[1][$counHeader][TABLE_ISI] = "Visus OD";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%"; 
             $tbHeader[1][$counHeader][TABLE_ROWSPAN] = "2";    
             $counHeader++;
             
             $tbHeader[1][$counHeader][TABLE_ISI] = "Visus OS";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%"; 
             $tbHeader[1][$counHeader][TABLE_ROWSPAN] = "2";    
             $counHeader++; 
             
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Pinhole";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";
     $counterHeader++; 
     
            
             $tbHeader[1][$counHeader][TABLE_ISI] = "Visus OD";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%"; 
             $tbHeader[1][$counHeader][TABLE_ROWSPAN] = "2";    
             $counHeader++;
             
             $tbHeader[1][$counHeader][TABLE_ISI] = "Visus OS";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%";  
             $tbHeader[1][$counHeader][TABLE_ROWSPAN] = "2";   
             $counHeader++;
             
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Koreksi Prisma";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "3";
     $counterHeader++; 
     
            
             $tbHeader[1][$counHeader][TABLE_ISI] = "Dioptri";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%"; 
             $tbHeader[1][$counHeader][TABLE_ROWSPAN] = "2";    
             $counHeader++;
             
             $tbHeader[1][$counHeader][TABLE_ISI] = "Base 1";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%";  
             $tbHeader[1][$counHeader][TABLE_ROWSPAN] = "2";   
             $counHeader++;
             
             $tbHeader[1][$counHeader][TABLE_ISI] = "Base 2";
             $tbHeader[1][$counHeader][TABLE_WIDTH] = "5%";  
             $tbHeader[1][$counHeader][TABLE_ROWSPAN] = "2";   
             $counHeader++;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nk_od"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["nk_os"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ref_mata_od_koreksi_spheris"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ref_mata_os_koreksi_spheris"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ref_mata_od_koreksi_cylinder"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ref_mata_os_koreksi_cylinder"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ref_mata_od_koreksi_sudut"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ref_mata_os_koreksi_sudut"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["k_od"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["k_os"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ref_pinhole_od"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ref_pinhole_os"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ref_prisma_koreksi_dioptri"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          if($dataTable[$i]["ref_prisma_koreksi_base1"]=='1') {
          $tbContent[$i][$counter][TABLE_ISI] = 'TRUE';
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          }else{
          $tbContent[$i][$counter][TABLE_ISI] = 'FALSE';
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          }
          
          if($dataTable[$i]["ref_prisma_koreksi_base2"]=='1') {
          $tbContent[$i][$counter][TABLE_ISI] = 'TRUE';
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          }else{
          $tbContent[$i][$counter][TABLE_ISI] = 'FALSE';
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          }



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
          header('Content-Disposition: attachment; filename=report_refraksi_'.$_POST["tgl_awal"].'.xls');
     }

?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>
<script language="JavaScript">
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }
}

</script>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<?php if(!$_POST["btnExcel"]) { ?>
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td width="15%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="15%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td> 
          <td class="tablecontent">
               <input type="submit" name="btnLanjut" value="Lanjut" class="button">
          </td>
     </tr>
</table>
<?php } ?>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</form>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<?php echo $view->RenderBodyEnd(); ?>
