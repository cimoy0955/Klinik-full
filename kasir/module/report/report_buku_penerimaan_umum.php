<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $thisPage = "report_loket.php";

     $plx = new InoLiveX("GetLayanan");     
     if(!$auth->IsAllowed("report_kasir",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("report_kasir",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
     function GetLayanan($jenisId)
	{
          global $dtaccess,$view,$bayarNama,$rawatStatus;
		
		if($jenisId!=PASIEN_BAYAR_SWADAYA) {
			$str = $bayarNama[BIAYA_KARTU];
		} else {
			
			$sql = "select biaya_id, biaya_nama, biaya_jenis 
					from klinik.klinik_biaya a
					order by biaya_id "; 
			$dataBiaya = $dtaccess->FetchAll($sql);
			unset($layanan);
			$layanan[0] = $view->RenderOption("","[ Pilih Layanan Biaya ]",$show);
			$i = 1;
			for($i=0,$n=count($dataBiaya);$i<$n;$i++){ 
				unset($show); 
				$layanan[$i+1] = $view->RenderOption($dataBiaya[$i]["biaya_id"],$rawatStatus[$dataBiaya[$i]["biaya_jenis"]]." - ".$dataBiaya[$i]["biaya_nama"],$show); 
			}
			
			$layanan[count($dataBiaya)+1] = $view->RenderOption(STATUS_OPERASI,$rawatStatus[STATUS_OPERASI],$show);
			
			$str = $view->RenderComboBox("id_biaya","id_biaya",$layanan,null,null);
		}
		
		return $str;
     } 
	
	$sql = "select * from klinik.klinik_split order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
     
     if ($_POST["btnDelete"]) {
          $folId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($folId);$i<$n;$i++){
               $sql_del = "delete from klinik.klinik_folio where fol_id = ".QuoteValue(DPE_CHAR,$folId[$i]);
               $dtaccess->Execute($sql_del);
          }
     } 
          
     $skr = date("d-m-Y");
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = $skr;
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = $skr; 
     
     $sql_where[] = "a.fol_lunas = ".QuoteValue(DPE_CHAR,"y"); 

     $timestampAwal = date_db($_POST["tgl_awal"])." ".$_POST["jam_awal"].":00:00";
     $timestampAkhir = date_db($_POST["tgl_akhir"])." ".$_POST["jam_akhir"].":00:00";
     
     if($_POST["tgl_awal"]) $sql_where[] = "a.fol_dibayar_when >= ".QuoteValue(DPE_DATE,$timestampAwal);
     if($_POST["tgl_akhir"]) $sql_where[] = "a.fol_dibayar_when <= ".QuoteValue(DPE_DATE,$timestampAkhir);
     
		$sql_where[] = "d.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,PASIEN_BAYAR_SWADAYA);
	
	
 //     if($_POST["id_biaya"] && $_POST["id_biaya"]!=STATUS_OPERASI) $sql_where[] = "a.id_biaya = ".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
	// if($_POST["id_biaya"]==STATUS_OPERASI) $sql_where[] = "a.fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
     
	$sql_where = implode(" and ",$sql_where);
	
     $sql = "select a.fol_id, a.id_reg, c.cust_usr_nama, c.cust_usr_kode, a.fol_dibayar, b.biaya_nama, b.biaya_kode, a.fol_jenis, a.id_biaya_tambahan, a.fol_nama ,CAST(a.fol_dibayar_when as DATE) as tanggal, extract(year from a.fol_dibayar_when) as tahun 
               from klinik.klinik_folio a 
               inner join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
	       left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
	       join klinik.klinik_registrasi d on d.reg_id = a.id_reg and a.id_cust_usr = d.id_cust_usr "; 
	$sql .= " where ".$sql_where; 
	$sql .= " order by a.fol_dibayar_when, a.id_reg";
  // echo $sql."<br>";
     $dataTable = $dtaccess->FetchAll($sql);
     // -- end ---
     $m=0;

     $sql = "select b.* 
               from klinik.klinik_folio_split b
               inner join klinik.klinik_folio a on b.id_fol = a.fol_id
			join klinik.klinik_registrasi d on d.reg_id = a.id_reg and d.id_cust_usr = a.id_cust_usr ";
	$sql .= " where ".$sql_where ." "; 
  // echo $sql."<br>";
	$rs = $dtaccess->Execute($sql); 
	while($row = $dtaccess->Fetch($rs)) {
		$dataFolSplit[$row["id_fol"]][$row["id_split"]] = $row["folsplit_nominal"];
	} 
	
	$counterHeader=0;
		
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2.5%";
	$counterHeader++;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Bulan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%"; 
	$counterHeader++;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%"; 
  $counterHeader++;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Reg";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
  $counterHeader++;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%"; 
  $counterHeader++;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
  $counterHeader++;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%"; 
	$counterHeader++;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Optik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $counterHeader++;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Apotek";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $counterHeader++;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
	$counterHeader++;
     
     for($i=0,$counter=0,$i=0,$j=0,$n=count($dataTable);$i<$n;$counter=0,$i++,$j++){
     
          $splitTgl = format_date_long($dataTable[$i]["tanggal"]);
          $splitTgl = explode(" ", $splitTgl);
            $m++;
            $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;".$m;
            $tbContent[$j][$counter][TABLE_ALIGN] = "left";
            $counter++;         
   
            $tbContent[$j][$counter][TABLE_ISI] = $splitTgl[1];
            $tbContent[$j][$counter][TABLE_ALIGN] = "center";
            $counter++;
   
            $tbContent[$j][$counter][TABLE_ISI] = $splitTgl[0];
            $tbContent[$j][$counter][TABLE_ALIGN] = "center";
            $counter++;
   
            $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
            $tbContent[$j][$counter][TABLE_ALIGN] = "left";
            $counter++;
   
            $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
            $tbContent[$j][$counter][TABLE_ALIGN] = "left";
            $counter++;

            $tbContent[$j][$counter][TABLE_ISI] = ($dataTable[$i]["biaya_kode"]) ? $dataTable[$i]["biaya_kode"] : "APT0-00";
            $tbContent[$j][$counter][TABLE_ALIGN] = "center";
            $counter++;
            
            $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["fol_nama"];
            $tbContent[$j][$counter][TABLE_ALIGN] = "left";
            $counter++;
    
            $tbContent[$j][$counter][TABLE_ISI] = (substr($dataTable[$i]["biaya_kode"], 0, 3) == "OPT") ? "&nbsp;".currency_format($dataTable[$i]["fol_dibayar"])."&nbsp;" : "&nbsp;";
            $tbContent[$j][$counter][TABLE_ALIGN] = "right";
            $counter++;

            $tbContent[$j][$counter][TABLE_ISI] = (!$dataTable[$i]["biaya_kode"]) ? "&nbsp;".currency_format($dataTable[$i]["fol_dibayar"])."&nbsp;" : "&nbsp;";
            $tbContent[$j][$counter][TABLE_ALIGN] = "right";
            $counter++;

            $tbContent[$j][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_dibayar"])."&nbsp;";
            $tbContent[$j][$counter][TABLE_ALIGN] = "right";
            $counter++;
        		$total += $dataTable[$i]["fol_dibayar"];
    //}

   //        $sql="SELECT b_tambahan_nama,b_tambahan_jasmed,b_tambahan_ops from klinik.klinik_biaya_tambahan a 
   //              where b_tambahan_id=".QuoteValue(DPE_CHAR,$dataTable[$i]["id_biaya_tambahan"]);
   //        $dataTambahan = $dtaccess->Fetch($sql);
          
          
  
    // if($dataTambahan){
    //      $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTambahan["b_tambahan_jasmed"]);
    //      $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    //      $counter++;
    //      $totSplit[$dataSplit[0]["split_id"]] += $dataTambahan["b_tambahan_jasmed"];
          
    //      $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTambahan["b_tambahan_ops"]);
    //      $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    //      $counter++;
    //      $totSplit[$dataSplit[1]["split_id"]] += $dataTambahan["b_tambahan_ops"];
          
    // }elseif(!$dataTambahan){
    //      for($j=0,$x=0,$i=2;$j<$i;$j++,$x++){
     //    $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataFolSplit[$dataTable[$i]["fol_id"]][$dataSplit[$j]["split_id"]]);
     //    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
     //    $counter++;
     //    $totSplit[$dataSplit[$x]["split_id"]] += $dataFolSplit[$dataTable[$i]["fol_id"]][$dataSplit[$j]["split_id"]];
   //          }
    // }

          
         
          
          
		//if ($dataTable[$i]["cust_usr_kode"]<>$dataTable[$i+1]["cust_usr_kode"]) $m++;
     }
     
     $counter = 0;
     $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
     $tbBottom[0][$counter][TABLE_COLSPAN] = 9;
     $tbBottom[0][$counter][TABLE_ALIGN] = "center";
     $counter++;

	// for($i=0,$n=2;$i<$n;$i++){
	// 	$tbBottom[0][$counter][TABLE_ISI] = currency_format($totSplit[$dataSplit[$i]["split_id"]]);
	// 	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	// 	$counter++;
	// }

	
	$tbBottom[0][$counter][TABLE_ISI] = currency_format($total);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
     
     $tableHeader = "Report Pembayaran Loket Pasien";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran_loket_'.$_POST["tgl_awal"].'.xls');
     }
	
     $sql = "select biaya_id, biaya_nama, biaya_jenis 
               from klinik.klinik_biaya a
			order by biaya_id "; 
     $dataBiaya = $dtaccess->FetchAll($sql);
?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>
<style type="text/css">
  .hidden {
    display: none;
  }
</style>
<script language="JavaScript">
$(document).ready(function() {

    function getChildren($row) {
        var children = [];
        while($row.next().hasClass('child')) {
             children.push($row.next());
             $row = $row.next();
        }            
        return children;
    }        

    $('.parent').on('click', function() {
    
        var children = getChildren($(this));
        $.each(children, function() {
            $(this).fadeToggle(500);
        })
    });
    
})

function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Awal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }

     if(!CheckDate(frm.tgl_akhir.value)) {
          return false;
     }
}
<? $plx->Run(); ?>
function CariLayanan(id){ 
	document.getElementById('div_layanan').innerHTML = GetLayanan(id,'type=r');
}

</script>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<?php if(!$_POST["btnExcel"]) { ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td colspan="<?php echo (count($dataSplit)+6)?>">&nbsp;<?php echo $tableHeader;?></td>
     </tr>
</table>

<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" id="tblSearching">
     <tr class="tablecontent">
          <td width="15%">&nbsp;Tanggal</td>
          <td width="35%">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               -
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               
          </td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <!-- <td width="10%">&nbsp;Jenis Pasien</td>
          <td width="40%">
			<select name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);" onchange="CariLayanan(document.getElementById('cust_usr_jenis').value)">
                    <option value="" >[ Pilih Jenis Pasien ]</option>
                    <?php foreach($bayarPasien as $iey => $value) { ?>
                         <option value="<?php echo $iey;?>" <?php if($_POST["cust_usr_jenis"]==$iey) echo "selected";?>><?php echo $value;?></option>
                    <?php } ?>
			</select>
          </td>  -->
     </tr>
     <tr class="tablecontent">
          <td width="15%">&nbsp;Pukul</td>
          <td width="35%" colspan="3">
            <select name="jam_awal" id="jam_awal">
              <?php for ($i=0; $i < 24; $i++) { ?>
                <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?>" <?php if ($i==8): ?>
                  selected
                <?php endif ?>><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?>:00</option>
              <?php }?>
            </select> - 
            <select name="jam_akhir" id="jam_akhir">
              <?php for ($i=0; $i < 24; $i++) { ?>
                <option value="<?php echo str_pad($i, 2, "0");?>" <?php if ($i==14): ?>
                  selected
                <?php endif ?>><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?>:00</option>
              <?php }?>
            </select>
          </td>
     </tr>
     <!-- <tr class="tablecontent">
		<td>&nbsp;Layanan</td>
          <td colspan="3">
			<div id="div_layanan">
				<?php if($_POST["cust_usr_jenis"] && $_POST["cust_usr_jenis"]!=PASIEN_BAYAR_SWADAYA) {
					echo $bayarNama[BIAYA_KARTU];
				} else { ?>
					<select name="id_biaya">
						 <option value="">[ Pilih Layanan Biaya ]</option>
						 <?php for($i=0,$n=count($dataBiaya);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataBiaya[$i]["biaya_id"];?>" <?php if($_POST["id_biaya"]==$dataBiaya[$i]["biaya_id"]) echo "selected";?>><?php echo $rawatStatus[$dataBiaya[$i]["biaya_jenis"]]." - ".$dataBiaya[$i]["biaya_nama"];?></option>
						 <?php } ?>
							 <option value="<?php echo STATUS_OPERASI;?>" <?php if($_POST["id_biaya"]==STATUS_OPERASI) echo "selected";?>><?php echo $biayaStatus[STATUS_OPERASI];?></option>
					 </select>
				<?php } ?>
			</div>
		</td>
	</tr> -->
	<tr>
    <td class="tablecontent" colspan="6">
      <input type="submit" name="btnLanjut" value="Lanjut" class="button" />
      <input type="submit" name="btnExcel" value="Export Excel" class="button" />
    </td>
  </tr>
</table>

<BR>


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
<?php } ?>

<?php if($_POST["btnExcel"]) {?>
     <table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="<?php echo (count($dataSplit)+6)?>"><strong>BUKU PENERIMAAN BIAYA PELAYANAN KASIR<br/>BKMM PROP. JATIM<br/>BUKU PENERIMAAN UMUM</strong></td>
          </tr>
     </table>
<?php }?>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>


<?php echo $view->RenderBodyEnd(); ?>
