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
     
          
	if($auth->IsAllowed()===1){
	    include("login.php");
	    exit();
	}

     $editPage = "mp_trans_edit.php";
     $viewPage = "mp_trans_view.php";

	$PageHeader = "Kasir Multiplayer";
	
	
	$plx = new InoLiveX("GetData");     

	function GetData() {
		global $dtaccess,$enc,$table,$view,$APLICATION_ROOT;
		
		$shutdownPage = "mp_trans_edit.php";
		$editPage = "mp_trans_edit.php";
    
    $sql = "select meja_nama,meja_order from mp_meja where meja_tipe='M'
				order by meja_order";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		$dataMeja = $dtaccess->FetchAll($rs);
     
		$sql = "select a.trans_id, a.trans_time_start, a.trans_time_expire, b.member_nama,
            b.member_id,c.usr_loginname,d.meja_nama,d.meja_order
				from mp_member_trans a
				join mp_member b on a.id_member = b.member_id 
				join global_auth_user c on b.id_usr = c.usr_id
        join mp_meja d on a.id_meja=d.meja_id 
				where a.id_dep = ".QuoteValue(DPE_CHAR,APP_OUTLET)."
				and b.member_aktif = 'y' 
				and a.trans_time_expire > 0 
				order by d.meja_order"; 

		$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		
		$dataTable = $dtaccess->FetchAll($rs);
		

    $j=0;
    for($i=1,$n=count($dataMeja);$i<=$n;$i++){
     if ($i!=$dataTable[$j]["meja_order"]) 
      {
         $meja[$i-1]=$dataMeja[$i-1]; 
      } else {
         $meja[$i-1]=$dataTable[$j]; 
         $j++;
      }
    }		
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
	
	
		for($i=0,$counter=0,$n=count($meja);$i<$n;$i++,$counter=0){
			
			if ($meja[$i]["member_nama"]=="") $namaMeja=""; else
			$namaMeja=$meja[$i]["member_nama"].'('.$meja[$i]["usr_loginname"].')';
			
			if (FormatTime($meja[$i]["trans_time_expire"])=="00:00:00") $sisaJam=""; else
			$sisaJam=FormatTime($meja[$i]["trans_time_expire"]);
			
			$tbContent[$i][$counter][TABLE_ISI] = $i+1;
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
			
			$tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$shutdownPage.'?shutdown='.(trim($meja[$i]["member_id"])).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/transaksi.gif" alt="Shutdown" title="Shutdown" border="0"></a>';
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
			
			$tbContent[$i][$counter][TABLE_ISI] = $meja[$i]["meja_nama"];
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
 
