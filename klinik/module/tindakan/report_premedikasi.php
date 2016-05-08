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
 
     $thisPage = "report_operasi.php";

     if(!$auth->IsAllowed("premedikasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("premedikasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y"); 
      
     $sql_where[] = "cast(preme_waktu as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     $sql_where[] = "cast(preme_waktu as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));

     $sql = "select * from klinik.v_report_premedikasi";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " order by preme_waktu"; 
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);

     $sqlCount = "select preme_id, count(*) as jml_preme  from klinik.v_report_premedikasi";
     $sqlCount.= " where ".implode(" and ",$sql_where);
     $sqlCount.= " group by preme_id"; 
     $rsCount = $dtaccess->Execute($sqlCount);
     while ($dataCount = $dtaccess->Fetch($rsCount)) {
          $colspan[$dataCount["preme_id"]] = $dataCount["jml_preme"];
     }
      
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Pasien Premedikasi";
     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
     $counterHeader++;
    
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
         
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";  
        
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "L/P";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";  
         
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";  
         
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Suster";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";  
         
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";  
         
     $counterHeader++;
     
     
     for($i=0,$j=0,$k=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$j++,$counter=0){ 
          
          if ($dataTable[$i]["preme_id"] != $dataTable[$i-1]["preme_id"]) {
               $k++;
               $tableClass = ($k % 2 == 0) ? "tablecontent-odd" : "tablecontent" ;
               $tbContent[$j][$counter][TABLE_ISI] = $k;
               $tbContent[$j][$counter][TABLE_ALIGN] = "right";
               $tbContent[$j][$counter][TABLE_ROWSPAN] = $colspan[$dataTable[$i]["preme_id"]]; 
               $tbContent[$j][$counter][TABLE_CLASS] = $tableClass;
               $counter++;
               
               $tbContent[$j][$counter][TABLE_ISI] = FormatFromTimeStamp($dataTable[$i]["preme_waktu"]);
               $tbContent[$j][$counter][TABLE_ALIGN] = "left"; 
               $tbContent[$j][$counter][TABLE_ROWSPAN] = $colspan[$dataTable[$i]["preme_id"]]; 
               $tbContent[$j][$counter][TABLE_CLASS] = $tableClass;
               $counter++;
               
               $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "left"; 
               $tbContent[$j][$counter][TABLE_ROWSPAN] = $colspan[$dataTable[$i]["preme_id"]]; 
               $tbContent[$j][$counter][TABLE_CLASS] = $tableClass;
               $counter++;
               
               $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "left";         
               $tbContent[$j][$counter][TABLE_ROWSPAN] = $colspan[$dataTable[$i]["preme_id"]]; 
               $tbContent[$j][$counter][TABLE_CLASS] = $tableClass;
               $counter++; 
               
               $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "left";       
               $tbContent[$j][$counter][TABLE_ROWSPAN] = $colspan[$dataTable[$i]["preme_id"]]; 
               $tbContent[$j][$counter][TABLE_CLASS] = $tableClass;  
               $counter++; 
               
               $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["dokter_nama"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "left";      
               $tbContent[$j][$counter][TABLE_ROWSPAN] = $colspan[$dataTable[$i]["preme_id"]];  
               $tbContent[$j][$counter][TABLE_CLASS] = $tableClass; 
               $counter++;
          }
          if ($dataTable[$i]["preme_id"] != $dataTable[$i-1]["preme_id"]) {
               $counter = 6;
          }
          $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["suster_nama"];
          $tbContent[$j][$counter][TABLE_ALIGN] = "left";     
          $tbContent[$j][$counter][TABLE_CLASS] = $tableClass;     
          $counter++; 
          
          if ($dataTable[$i]["preme_id"] != $dataTable[$i-1]["preme_id"]) {
               $tbContent[$j][$counter][TABLE_ISI] = ($dataTable[$i]["preme_status"] == 'y') ? "SETUJU OPERASI" : "BATAL OPERASI";
               $tbContent[$j][$counter][TABLE_ALIGN] = "left";      
               $tbContent[$j][$counter][TABLE_ROWSPAN] = $colspan[$dataTable[$i]["preme_id"]];               
               $tbContent[$j][$counter][TABLE_CLASS] = $tableClass;    
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
          header('Content-Disposition: attachment; filename=report_operasi_'.$_POST["tgl_awal"].'.xls');
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


var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
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
          <td width="35%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               -
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td> 
          <td class="tablecontent" width="50%">
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
    
    Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<?php echo $view->RenderBodyEnd(); ?>
