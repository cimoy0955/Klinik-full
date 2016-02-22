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
     

$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);


$plx = new InoLiveX("GetData");

function GetData($in_nama=null){
	global $dtaccess, $APLICATION_ROOT;

	
	$table = new InoTable("table1","100%","center",null,0,0,0,null,"tblForm");
	
	
	if($in_nama) $sql_where[] = "UPPER(biaya_nama) like '%".strtoupper($in_nama)."%'"; 
	 
	 $sql_where[] = "biaya_kode like 'OP3%'";
	 
	 $sql_where = implode(" and ",$sql_where);
/* 
          
          $sql .= " order by UPPER(a.biaya_nama), a.biaya_kode";
      */        
      // --- cari datanya ---
      $sql = "select * from klinik.klinik_biaya";
      
      if($sql_where){
	     $sql = $sql." where ".$sql_where;
	}		
      $sql .= " order by biaya_id";
		      
      $rs = $dtaccess->Execute($sql,DB_SCHEMA);     
      $dataTable = $dtaccess->FetchAll($rs);
      
      $counter = 0;          

      $tbHeader[0][$counter][TABLE_ISI] = "No";
      $tbHeader[0][$counter][TABLE_WIDTH] = "1%";
      $tbHeader[0][$counter][TABLE_ALIGN] = "center";
      $counter++;
      
      $tbHeader[0][$counter][TABLE_ISI] = "Nama Obat";
      $tbHeader[0][$counter][TABLE_WIDTH] = "64%";
      $tbHeader[0][$counter][TABLE_ALIGN] = "center";
      $counter++;

      $tbHeader[0][$counter][TABLE_ISI] = "Harga";
      $tbHeader[0][$counter][TABLE_WIDTH] = "64%";
      $tbHeader[0][$counter][TABLE_ALIGN] = "center";
      $counter++;

      $tbHeader[0][$counter][TABLE_ISI] = "Pilih";
      $tbHeader[0][$counter][TABLE_WIDTH] = "10%";
      $tbHeader[0][$counter][TABLE_ALIGN] = "center";
      $counter++;
	
	
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {
		
		($i%2==0)? $class="tablecontent":$class="tablecontent-odd";

		$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["biaya_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
	    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;Rp. ".currency_format($dataTable[$i]["biaya_total"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer;" src="'.$APLICATION_ROOT.'images/r_arrowgrnsm.gif" border="0" alt="Pilih" title="Pilih" width="12" height="12" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["biaya_nama"])).'\',\''.$dataTable[$i]["biaya_id"].'\',\''.currency_format($dataTable[$i]["biaya_total"]).'\',\''.$dataTable[$i]["biaya_kode"].'\',\''.$dataTable[$i]["biaya_jenis"].'\');"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
	//return $sql;
}


?>
<?php echo $view->RenderBody("inosoft.css",true); ;?>


<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(nama,id,harga,kode,jenis) {
	self.parent.document.getElementById('operasi_nama').value = nama;
	self.parent.document.getElementById('operasi_id').value = id;
	self.parent.document.getElementById('txtHargaSatuanOperasi').value = harga;
	self.parent.document.getElementById('txtJumlahObat').value = 1;
	self.parent.document.getElementById('txtHargaTotalOperasi').value = harga;
	self.parent.document.getElementById('operasi_kode').value = kode;
	self.parent.document.getElementById('operasi_jenis').value = jenis;
	self.parent.document.getElementById('txtJumlahOperasi').focus();
	self.parent.tb_remove();
}

function Search(nama) {
	GetData(nama,'target=dv_hasil');
}

</script>

<form name="frmSearch">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;biaya</center></td>
			</tr>
			<tr>
				<td align="right" class="tablecontent">Nama biaya</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="tablecontent"><center>
					<input type="button" name="btnSearch" value="Cari" class="button" onClick="Search(document.getElementById('_name').value)"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="button" /></center>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</form>

<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_name",true);?>
<?php echo $view->RenderBodyEnd(); ?>

