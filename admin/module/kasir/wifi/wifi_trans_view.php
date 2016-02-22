<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");    
     require_once($APLICATION_ROOT."library/view.cls.php");

    // $dtaccess = new DataAccess();
     $wifi_dtaccess = new Wifi_DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","70%","left",null,0,1,1,null);     
     
          
    	if($auth->IsAllowed()===1){
    	    include("login.php");
    	    exit();
    	}

     $editPage = "wifi_trans_edit.php";
     $viewPage = "wifi_trans_view.php";

	$PageHeader = "Kasir Wifi";
	
	
	$plx = new InoLiveX("GetData");     

	function GetData() {
		global $wifi_dtaccess,$enc,$table,$view,$APLICATION_ROOT;
		
		$shutdownPage = "wifi_trans_edit.php";
		$editPage = "wifi_trans_edit.php";
    
    $sql = "select * from radius.radcheck";
		$rs = $wifi_dtaccess->Wifi_Execute($sql,Wifi_DB_SCHEMA);
		$dataTable = $wifi_dtaccess->Wifi_FetchAll($rs);
     
			
		// --- construct new table ---- //
		$tbHeader[0][0][TABLE_ISI] = "No";
		$tbHeader[0][0][TABLE_WIDTH] = "5%";

		$tbHeader[0][1][TABLE_ISI] = "Shutdown/Reset";
		$tbHeader[0][1][TABLE_WIDTH] = "5%";
	
		$tbHeader[0][2][TABLE_ISI] = "Workstation";
		$tbHeader[0][2][TABLE_WIDTH] = "10%";
    	
		$tbHeader[0][3][TABLE_ISI] = "Nama";
		$tbHeader[0][3][TABLE_WIDTH] = "30%";
			
		$tbHeader[0][4][TABLE_ISI] = "Jam Masuk";
		$tbHeader[0][4][TABLE_WIDTH] = "20%";
			
		$tbHeader[0][5][TABLE_ISI] = "Sisa Jam";
		$tbHeader[0][5][TABLE_WIDTH] = "10%";
	
	
		for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
			
		
			
			$tbContent[$i][$counter][TABLE_ISI] = $i+1;
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
			
			$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$shutdownPage.'?shutdown='.(trim($meja[$i]["member_id"])).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/transaksi.gif" alt="Shutdown" title="Shutdown" border="0"></a>';
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
			
			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["UserName"];
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
	
			$tbContent[$i][$counter][TABLE_ISI] = $namaMeja;
			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
			$counter++;
	
			$tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($meja[$i]["trans_time_start"]);
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
	
			$tbContent[$i][$counter][TABLE_ISI] = $sisaJam;
			$tbContent[$i][$counter][TABLE_ALIGN] = "right";
			$counter++;
		}
	
			
		$tbBottom[0][0][TABLE_ISI] = $view->RenderButton(BTN_BUTTON,"btnPesan","btnPesan","Pesan","button",false,"onClick=\"document.location.href='".$editPage."';\"");
		$tbBottom[0][0][TABLE_WIDTH] = "100%";
		$tbBottom[0][0][TABLE_COLSPAN] = 6;

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
   
 if((mulai % 60) == 0){
 GetData('target=dv_tabel'); 
 }


 mulai++;
 mTimer = setTimeout("timer()", 1000);

}
timer();

//CekBantuan('type=r');
</script>


<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td>&nbsp;<?php echo $PageHeader;?></td>
     </tr>
</table>

<BR>


<form name="frmEdit" method="POST" action="<?php echo $editPage;?>">
<div id="dv_tabel"><?php echo GetData(); ?></div>

</form>

</body>
</html>
 
