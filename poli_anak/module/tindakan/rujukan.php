<?php
    require_once("root.inc.php");
    require_once($ROOT."library/bitFunc.lib.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");
    require_once($ROOT."library/dateFunc.lib.php");
    require_once($ROOT."library/tree.cls.php");
    require_once($ROOT."library/inoLiveX.php");
    require_once($APLICATION_ROOT."library/view.cls.php");
     
     
    $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
    $dtaccess = new DataAccess();
    $enc = new textEncrypt();
    $auth = new CAuth();
    $err_code = 0;
    $userData = $auth->GetUserData();
     

    if(!$auth->IsAllowed("poli_anak",PRIV_CREATE)){
        die("access_denied");
        exit(1);
    } else if($auth->IsAllowed("poli_anak",PRIV_CREATE)===1){
        echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
        exit(1);
    }
    
    $_x_mode = "New";
    $thisPage = "rujukan.php";
       
     
    if($_GET["id_reg"]) $_POST["id_reg"] = $_GET["id_reg"];
    if($_GET["status"]) $_POST["status"] = $_GET["status"];

    $tableRujukan = new InoTable("table1","99%","center");

    $plx = new InoLiveX("GetRujukan,SetRujukan");      

    function GetRujukan($status) {
        global $dtaccess, $view, $tableRujukan, $thisPage, $APLICATION_ROOT,$rawatStatus,$page; 
             
        $sql = "select cust_usr_nama,a.reg_id, a.reg_status, a.reg_waktu, a.reg_jadwal 
                  from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                  where a.reg_tipe_umur = 'A' and a.reg_status like '".STATUS_RUJUKAN.$status."'
                  order by reg_status desc, reg_tanggal asc, reg_waktu asc";
        
        $dataTable = $dtaccess->FetchAll($sql);

        $counterHeader = 0;

        $tbHeader[0][$counterHeader][TABLE_ISI] = "";
        $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
        $counterHeader++;

        $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
        $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
        $counterHeader++;

        $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
        $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
        $counterHeader++;

        $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
        $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
        $counterHeader++;
        
        for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
            if($status==0) {
                $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/bul_arrowgrnlrg.gif" style="cursor:pointer" alt="Proses" title="Proses" border="0" onClick="ProsesPerawatan(\''.$dataTable[$i]["reg_id"].'\',\''.$dataTable[$i]["reg_status"]{0}.'\')"/>';
            } else {
                $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?id_reg='.$dataTable[$i]["reg_id"].'&status='.$dataTable[$i]["reg_status"]{0}.'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_select.png" alt="Proses" title="Proses" border="0"/></a>';               
            }
           $tbContent[$i][$counter][TABLE_ALIGN] = "center";
           $counter++;

           $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
           $tbContent[$i][$counter][TABLE_ALIGN] = "right";
           $counter++;
           
           $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
           $tbContent[$i][$counter][TABLE_ALIGN] = "left";
           $counter++;
           
           $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
           $tbContent[$i][$counter][TABLE_ALIGN] = "center";
           $counter++;
        }

        return $tableRujukan->RenderView($tbHeader,$tbContent,$tbBottom);
              
    }

    function SetRujukan($id,$status) {
               global $dtaccess;
               
               $sql = "update klinik.klinik_registrasi set reg_status = '".$status.STATUS_PROSES."' where reg_id = ".QuoteValue(DPE_CHAR,$id);
               $dtaccess->Execute($sql);
               
               return true;
       }
    
    if($_GET["id_reg"]) {
        $sql = "select cust_usr_nama,cust_usr_kode, cust_usr_ortu,cust_usr_telp, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, 
            ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr, c.ref_keluhan 
            from klinik.klinik_registrasi a
            left join klinik.klinik_refraksi c on a.reg_id = c.id_reg 
            left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
        $dataPasien= $dtaccess->Fetch($sql);
       
        $_POST["id_reg"] = $_GET["id_reg"]; 
        $_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
        $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];  
    }
    
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) {
        $dbTable = "poli_anak.poli_anak_rujukan";
        $dbField[0] = "rujukan_id";
        $dbField[1] = "id_reg";
        $dbField[2] = "rujukan_tujuan";
        $dbField[3] = "rujukan_anamnesa";
        $dbField[4] = "rujukan_diag_sementara";
        $dbField[5] = "rujukan_terapi_sementara";
        $dbField[6] = "rujukan_create";
        
        if(!$_POST["rujukan_id"]) $_POST["rujukan_id"] = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rujukan_id"]);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
        $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["rujukan_tujuan"]);
        $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["rujukan_anamnesa"]);
        $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["rujukan_diag_sementara"]);
        $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["rujukan_terapi_sementara"]);
        $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
        
        $dbKey[0] = 0;
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        
        if ($_POST["btnSave"]) {
            $dtmodel->Insert() or die("insert  error");	
        } elseif ($_POST["btnUpdate"]) {
            $dtmodel->Update() or die("update  error");	
        }	
        
        unset($dtmodel);
        unset($dbTable);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
        
        $sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_SELESAI.STATUS_ANTRI."', reg_waktu = CURRENT_TIME where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
        $dtaccess->Execute($sql); 
        
        echo "<script>document.location.href='".$thisPage."';</script>";
        exit();

    }
?>

<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php echo $view->InitThickBox(); ?>

<script type="text/javascript">

<?php $plx->Run(); ?>

var mTimer;

function timer(){     
    clearInterval(mTimer);      
    GetTindakan(0,'target=antri_kiri_isi');     
    GetTindakan(1,'target=antri_kanan_isi');     
    mTimer = setTimeout("timer()", 10000);
}

function ProsesPerawatan(id,status) {
    SetTindakan(id,status,'type=r');
    timer(); 
	
}

timer();
</script>

<?php if(!$_GET["id"]) { ?>
<div id="antri_main" style="width:100%;height:auto;clear:both;overflow:auto">
	<div id="antri_kiri" style="float:left;width:49%;">
		<div class="tableheader">Antrian Rujukan</div>
		<div id="antri_kiri_isi" style="height:100;overflow:auto"><?php echo GetRujukan(STATUS_ANTRI); ?></div>
	</div>
	
	<div id="antri_kanan" style="float:right;width:49%;">
		<div class="tableheader">Proses Rujukan</div>
		<div id="antri_kanan_isi" style="height:100;overflow:auto"><?php echo GetRujukan(STATUS_PROSES); ?></div>
	</div>
</div>
<?php } ?>

<?php if($dataPasien) { ?>
<table width="100%" border="0" cellpadding="4" cellspacing="1">
    <tr>
        <td align="left" colspan=2 class="tableheader">Input Data Rujukan</td>
    </tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
    <td width="100%">
        <fieldset>
            <legend><strong>Data Pasien</strong></legend>
            <table width="100%" border="1" cellpadding="4" cellspacing="1">
                <tr>
                    <td width= "30%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
                    <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
                </tr>	
                <tr>
                    <td width= "30%" align="left" class="tablecontent">Nama Lengkap</td>
                    <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
                </tr>
                <tr>
                    <td width= "30%" align="left" class="tablecontent">Umur</td>
                    <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
                </tr>
                <tr>
                    <td width= "30%" align="left" class="tablecontent">Jenis Kelamin</td>
                    <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?></label></td>
                </tr>
                <tr>
                    <td align="left" class="tablecontent">Tujuan Rujuk</td>
                    <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rujukan_tujuan","rujukan_tujuan","25","100",$_POST["rujukan_tujuan"],"inputField", null,false);?></td>
                </tr>
                <tr>
                    <td align="left" class="tablecontent">Anamnesa</td>
                    <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rujukan_anamnesa","rujukan_anamnesa","25","100",$_POST["rujukan_anamnesa"],"inputField", null,false);?></td>
                </tr>
                <tr>
                    <td align="left" class="tablecontent">Diagnosa Sementara</td>
                    <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rujukan_diag_sementara","rujukan_diag_sementara","25","100",$_POST["rujukan_diag_sementara"],"inputField", null,false);?></td>
                </tr>
                <tr>
                    <td align="left" class="tablecontent">Terapi Sementara</td>
                    <td align="left" class="tablecontent-odd"><?php echo $view->RenderTextBox("rujukan_terapi_sementara","rujukan_terapi_sementara","25","100",$_POST["rujukan_terapi_sementara"],"inputField", null,false);?></td>
                </tr>
            </table>
        </fieldset>
        
        <fieldset>
            <legend><strong></strong></legend>
            <table width="100%" border="1" cellpadding="4" cellspacing="1">
                <tr>
                    <td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>
</table>
     
<?php echo $view->SetFocus("rujukan_tujuan");?>

<input type="hidden" name="_x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="rujukan_id" value="<?php echo $_POST["rujukan_id"];?>" />
</form>
<?php } ?>
<?php echo $view->RenderBodyEnd(); ?>