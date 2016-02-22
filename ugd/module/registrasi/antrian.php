<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");     
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php"); 
     require_once($ROOT."library/bitFunc.lib.php");     
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");     

     $dtaccess = new DataAccess();     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $tableRefraksi = new InoTable("table1","100%","center");
     $tablePerawatan= new InoTable("table1","100%","center");
     $tableTindakan= new InoTable("table1","100%","center");
     $tableOperasi = new InoTable("table1","100%","center");

     $auth = new CAuth();          
     
     $statusAntri[0] = "Antri";
     $statusAntri[1] = "Masuk";
     
     $plx = new InoLiveX("GetRefraksi,GetOperasi,GetPerawatan,GetDiagnostik");     
     
     function GetRefraksi() {     
     
          global $dtaccess, $view, $tableRefraksi, $statusAntri; 
               
          $sql = "select cust_usr_nama,a.reg_status, a.reg_waktu,c.poli_nama from klinik.klinik_registrasi a 
                  join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                  join global.global_auth_poli c on a.id_poli = c.poli_id
                    where a.reg_status like '".STATUS_REFRAKSI."%' 
                    order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
     
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$dataTable[$i]["poli_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $statusAntri[$dataTable[$i]["reg_status"]{1}];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          }

          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);

          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
     }

     function GetOperasi() {     
     
          global $dtaccess, $view, $tableOperasi, $statusAntri; 
               
          $sql = "select cust_usr_nama,a.reg_status, a.reg_waktu,c.poli_nama from klinik.klinik_registrasi a 
                  join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                  join global.global_auth_poli c on a.id_poli = c.poli_id
                    where a.reg_status like '".STATUS_OPERASI."%' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
     
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$dataTable[$i]["poli_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $statusAntri[$dataTable[$i]["reg_status"]{1}];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          }

          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);

          return $tableOperasi->RenderView($tbHeader,$tbContent,$tbBottom);
     }

     function GetPerawatan() {     
     
          global $dtaccess, $view, $tablePerawatan, $statusAntri; 
               
          $sql = "select cust_usr_nama,a.reg_status, a.reg_waktu,c.poli_nama from klinik.klinik_registrasi a 
                  join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                  join global.global_auth_poli c on a.id_poli = c.poli_id
                    where a.reg_status like '".STATUS_PEMERIKSAAN."%' and a.reg_ugd = 'n' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
     
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$dataTable[$i]["poli_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $statusAntri[$dataTable[$i]["reg_status"]{1}];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          }

          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);

          return $tablePerawatan->RenderView($tbHeader,$tbContent,$tbBottom);
     }


    
     function GetDiagnostik() {     
     
          global $dtaccess, $view, $tableRefraksi, $statusAntri; 
               
          $sql = "select cust_usr_nama,a.reg_status, a.reg_waktu,c.poli_nama from klinik.klinik_registrasi a 
                  join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
                  join global.global_auth_poli c on a.id_poli = c.poli_id
                    where a.reg_status like '".STATUS_DIAGNOSTIK."%' order by reg_status desc, reg_tanggal asc, reg_waktu asc";
          $dataTable = $dtaccess->FetchAll($sql);

          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jam Masuk";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
     
          for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
               $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
               $tbContent[$i][$counter][TABLE_ALIGN] = "right";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$dataTable[$i]["cust_usr_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$dataTable[$i]["poli_nama"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "left";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = $statusAntri[$dataTable[$i]["reg_status"]{1}];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          }

          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);

          return $tableRefraksi->RenderView($tbHeader,$tbContent,$tbBottom);
     }

?>
<head>
<TITLE>.:: <?php  echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" media="screen" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
<html>
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/ew.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/elements.js"></script>

<script type="text/javascript">

<? $plx->Run(); ?>

var mTimer;

function timer(){     
     clearInterval(mTimer);      
     GetRefraksi('target=refraksi_div');     
     GetOperasi('target=operasi_div');     
     GetPerawatan('target=perawatan_div');     
     GetDiagnostik('target=diagnostik_div');     
     mTimer = setTimeout("timer()", 10000);
}

</script>

</head>

<body onLoad="timer();">
     
<div id="perawatan_main" style="float:left;width:48%">
     <div class="tableheader">Antrian Pemeriksaan</div>
     <div id="perawatan_div"><?php echo GetPerawatan(); ?></div>
</div>
    
<!--<div id="refraksi_main" style="float:left;width:48%">
     <div class="tableheader">Antrian Poli</div>
     <div id="refraksi_div"><?php echo GetRefraksi(); ?></div>
</div>-->

<div id="operasi_main" style="float:right;width:48%">
     <div class="tableheader">Antrian Operasi</div>
     <div id="operasi_div"><?php echo GetOperasi(); ?></div>
</div>

<div style="clear:both"></div> <BR>


<div id="operasi_main" style="float:left;width:48%">
     <div class="tableheader">Antrian Diagnostik</div>
     <div id="diagnostik_div"><?php echo GetDiagnostik(); ?></div>
</div>

<div style="clear:both"></div> <BR>



</body>
</html>
