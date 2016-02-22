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
$_POST["_jenis"] = $_GET["jenis"];

$plx = new InoLiveX("GetData,CreateNewFolio");

function GetData($in_jenis,$in_kode=null,$in_nama=null,$in_order=null){
	global $dtaccess, $APLICATION_ROOT;

	
	$table = new InoTable("table1","100%","center",null,0,0,0,null,"tblForm");
	
	
	if($in_nama) $sql_where[] = "UPPER(cust_usr_nama) like '%".strtoupper($in_nama)."%'";
	if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like '%".strtoupper($in_kode)."%'"; 
	 
         $sql_where[] = "a.reg_tanggal=current_date";
	 // $sql_where[] = "c.fol_lunas = 'y'";
	 
	 if($in_jenis==1) $sql_where[] = "a.reg_jenis_pasien='3'";
	 if($in_jenis==0)$sql_where[] = "a.reg_jenis_pasien<>'3'";
	 
	 $sql_where = implode(" and ",$sql_where);	 
/* 
          
          $sql .= " order by UPPER(a.biaya_nama), a.biaya_kode";
      */        
      // --- cari datanya ---
      $sql = "select * 
	    from klinik.klinik_registrasi a 
	    left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
	    left join klinik.klinik_folio c on a.reg_id = c.id_reg";
      
      if($sql_where){
	     $sql = $sql." where ".$sql_where;
	}		
	if ($in_order == 'kode')
		$order_by = "cust_usr_kode";
	elseif ($in_order == 'nama')
		$order_by = "cust_usr_nama";
	else 
		$order_by = "reg_tanggal";

      $sql .= " order by ".$order_by." ASC";
		      
      $rs = $dtaccess->Execute($sql,DB_SCHEMA);     
      $dataTable = $dtaccess->FetchAll($rs);
      
      $counter = 0;          

      $tbHeader[0][$counter][TABLE_ISI] = "No";
      $tbHeader[0][$counter][TABLE_WIDTH] = "1%";
      $tbHeader[0][$counter][TABLE_ALIGN] = "center";
      $counter++;
      
      $tbHeader[0][$counter][TABLE_ISI] = "<a href=\"#\" onclick=\"Search(document.getElementById('_jenis').value,document.getElementById('_kode').value,document.getElementById('_name').value,'kode');\">Kode</a>";
      $tbHeader[0][$counter][TABLE_WIDTH] = "30%";
      $tbHeader[0][$counter][TABLE_ALIGN] = "center";
      $counter++;

      $tbHeader[0][$counter][TABLE_ISI] = "<a href=\"#\" onclick=\"Search(document.getElementById('_jenis').value,document.getElementById('_kode').value,document.getElementById('_name').value,'nama');\">Nama</a>";
      $tbHeader[0][$counter][TABLE_WIDTH] = "30%";
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
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
	    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img style="cursor:pointer;" src="'.$APLICATION_ROOT.'images/r_arrowgrnsm.gif" border="0" alt="Pilih" title="Pilih" width="12" height="12" class="img-button" OnClick="javascript: sendValue(\''.$dataTable[$i]["reg_id"].'\',\''.$dataTable[$i]["cust_usr_id"].'\');"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}

function CreateNewFolio($in_idReg,$in_idCustUsr) {
      global $dtaccess, $APLICATION_ROOT, $ROOT;
      
      $dbTable = "klinik.klinik_folio";
               
      $dbField[0] = "fol_id";   // PK
      $dbField[1] = "id_reg";
      $dbField[2] = "id_cust_usr";
      $dbField[3] = "fol_lunas";
      $dbField[4] = "fol_waktu";
      
      $folioId = $dtaccess->GetTransID("klinik.klinik_folio","fol_id",DB_SCHEMA);
      $dbValue[0] = QuoteValue(DPE_CHAR,$folioId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$in_idReg);
      $dbValue[2] = QuoteValue(DPE_CHAR,$in_idCustUsr);
      $dbValue[3] = QuoteValue(DPE_CHAR,'n');
      $dbValue[4] = QuoteValue(DPE_NUMBER,'localtimestamp');
      
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      $dtmodel->Insert() or die("insert  error");
      return true;
}


?>
<?php echo $view->RenderBody("inosoft.css",true); ;?>


<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(id_reg,id_custusr) {
      CreateNewFolio(id_reg,id_custusr,'type=r');
      self.parent.tb_remove();
      self.parent.location.reload();
}

function Search(jenis,kode,nama,order) {
	GetData(jenis,kode,nama,order,'target=dv_hasil');
}

</script>

<form name="frmSearch">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;Pasien</center></td>
			</tr>
			<tr>
				<td align="right" class="tablecontent">No. R.M.</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_kode","_kode",25,200,$_POST["_kode"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent">Nama Pasien</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="tablecontent"><center>
					<input type="button" name="btnSearch" value="Cari" class="button" onClick="Search(document.getElementById('_jenis').value,document.getElementById('_kode').value,document.getElementById('_name').value,'null')"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="button" /></center>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<input type="hidden" name="_jenis" id="_jenis" value="<?php echo $_POST["_jenis"];?>" />
</form>

<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_kode",true);?>
<?php echo $view->RenderBodyEnd(); ?>

