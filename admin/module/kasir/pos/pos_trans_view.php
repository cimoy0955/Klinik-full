<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");    
     require_once($APLICATION_ROOT."library/view.cls.php");

     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","70%","left",null,0,1,1,null);     
     $usrId = $auth->GetUserId();
     
	if(!$auth->IsAllowed("pos_penjualan",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("pos_penjualan",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$GLOBAL_ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     $editPage = "pos_trans_edit.php";
     $viewPage = "pos_trans_view.php";

	$PageHeader = "Penjualan Point of Sale";
	
	
	$plx = new InoLiveX("GetData");     

	function GetData() {
		global $dtaccess,$enc,$table,$view,$APLICATION_ROOT,$outlet;
	
		$editPage = "pos_trans_edit.php";

     
		$sql = "select * from pos.pos_meja order by meja_order";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs);
		$sql = "select a.penjualan_id,a.penjualan_room,a.penjualan_rate,a.penjualan_tipe,
        a.penjualan_customer,b.* from pos.pos_penjualan a left join 
				pos.pos_meja b on a.id_meja=b.meja_id where a.penjualan_tipe='N' 
        and a.id_dep = ".QuoteValue(DPE_CHAR,$outlet)." order by b.meja_order";    
		$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		$dataPOS = $dtaccess->FetchAll($rs);
    
    $j=0;
    for($i=1,$n=count($dataTable);$i<=$n;$i++){
     if ($i!=$dataPOS[$j]["meja_order"]) 
      {
         $meja[$i-1]=$dataTable[$i-1]; 
      } else {
         $meja[$i-1]=$dataPOS[$j]; 
         $j++;
      }
    }	
			
		// --- construct new table ---- //
		$tbHeader[0][0][TABLE_ISI] = "No";
		$tbHeader[0][0][TABLE_WIDTH] = "5%";
	
		$tbHeader[0][1][TABLE_ISI] = "Buka Meja";
		$tbHeader[0][1][TABLE_WIDTH] = "10%";
		
		$tbHeader[0][2][TABLE_ISI] = "Transaksi";
		$tbHeader[0][2][TABLE_WIDTH] = "10%";
		
		$tbHeader[0][3][TABLE_ISI] = "Meja";
		$tbHeader[0][3][TABLE_WIDTH] = "20%";
		
		$tbHeader[0][4][TABLE_ISI] = "Status";
		$tbHeader[0][4][TABLE_WIDTH] = "30%";
    	
	
	
	
		for($i=0,$counter=0,$n=count($meja);$i<$n;$i++,$counter=0){
			
			if ($meja[$i]["penjualan_tipe"]=="N") 
			   $tombolPesan='&nbsp;';
      else 
			   $tombolPesan = '<a href="'.$editPage.'?meja='.$dataTable[$i]["meja_nama"].'&meja_id='.$dataTable[$i]["meja_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/transaksi.gif" alt="Recharge" title="Check In" border="0"></a>';

      if ($meja[$i]["penjualan_tipe"]=="N") 
			   $tombolTambah = '<a href="'.$editPage.'?penjualan_id='.$meja[$i]["penjualan_id"].'&meja='.$dataTable[$i]["meja_nama"].'&meja_id='.$dataTable[$i]["meja_id"].'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/tambah_order.gif" alt="Recharge" title="Check In" border="0"></a>';
      else 
			    $tombolTambah='&nbsp;';
			    
			if ($meja[$i]["penjualan_tipe"]=="N") 
			   $status='Meja telah terpakai';
      else 
			   $status='&nbsp;';


			if ($meja[$i]["penjualan_tipe"]=="C") 
			   $status='Terpakai('.$meja[$i]["penjualan_customer"].')';
      else 
			   $status='&nbsp;';

			
			$tbContent[$i][$counter][TABLE_ISI] = $i+1;
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
			
			$tbContent[$i][$counter][TABLE_ISI] = $tombolPesan;
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
			
			$tbContent[$i][$counter][TABLE_ISI] = $tombolTambah;
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
			
			
			$tbContent[$i][$counter][TABLE_ISI] = $meja[$i]["meja_nama"];
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
			
			
			$tbContent[$i][$counter][TABLE_ISI] = $status;
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
	
			
		}
	
			


		return $table->RenderView($tbHeader,$tbContent,$tbBottom);
	}
	
	
?>

<?php echo $view->RenderBody("inventori.css",false); ?>

<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer,mulai=0;

function timer(){
var adaBantuan;     
 clearInterval(mTimer);      
   
 if((mulai % 2) == 0){
 GetData('target=dv_tabel'); 
 }
 
 mulai++;
 mTimer = setTimeout("timer()", 1000);

}
timer();

function Edit()
{
  document.location.href='<?php echo $editPage?>';
}
</script>

</script>


<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td>&nbsp;<?php echo $PageHeader;?></td>
     </tr>
</table>

<BR>
<table width="100%">
     <tr class="tableheaderatas">
          <td align="center"><img src="<?php echo $ROOT;?>admin/images/pos.png" style="cursor:pointer"; onCLick="javascript:Edit();"></td>
     </tr>
</table>

<form name="frmEdit" method="POST" action="<?php echo $editPage;?>">
<div id="dv_tabel"><?php echo GetData(); ?></div>

</form>

</body>
</html>
 
