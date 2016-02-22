<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
	require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $editPage = "medis_edit.php";
     $thisPage = "medis_view.php";

     if(!$auth->IsAllowed("klinik",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("klinik",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     $sql = "select a.petugas_id, a.id_app, a.dep_nama, b.pgw_nama as dokter1, c.pgw_nama as dokter2 , d.pgw_nama as dokter3 , e.pgw_nama as perawat1 , 
f.pgw_nama as perawat2, g.pgw_nama as perawat3 , h.pgw_nama as perawat4, i.pgw_nama as perawat5,j.pgw_nama as perawat6,
k.pgw_nama as perawat7,l.pgw_nama as perawat8,m.pgw_nama as perawat9,n.pgw_nama as perawat10
from global.global_petugas a
left join hris.hris_pegawai b on b.pgw_id = a.dokter_1
left join hris.hris_pegawai c on c.pgw_id = a.dokter_2
left join hris.hris_pegawai d on d.pgw_id = a.dokter_3
left join hris.hris_pegawai e on e.pgw_id = a.perawat_1
left join hris.hris_pegawai f on f.pgw_id = a.perawat_2
left join hris.hris_pegawai g on g.pgw_id = a.perawat_3
left join hris.hris_pegawai h on h.pgw_id = a.perawat_4
left join hris.hris_pegawai i on i.pgw_id = a.perawat_5
left join hris.hris_pegawai j on j.pgw_id = a.perawat_6
left join hris.hris_pegawai k on k.pgw_id = a.perawat_7
left join hris.hris_pegawai l on l.pgw_id = a.perawat_8
left join hris.hris_pegawai m on m.pgw_id = a.perawat_9
left join hris.hris_pegawai n on n.pgw_id = a.perawat_10
where petugas_id <> '5' and  petugas_id <> '2' and  petugas_id <> '7' 
order by a.dep_nama ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataTable = $dtaccess->FetchAll($rs);
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Tenaga Medis";
     
     $isAllowedDel = $auth->IsAllowed("klinik",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("klinik",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("klinik",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Departemen";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter 1";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter 2";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter 3";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 1";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 2";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 3";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 4";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
      $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 5";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 6";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 7";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 8";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 9";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Perawat 10";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     
     
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }   
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
         
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["dep_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["dokter1"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["dokter2"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["dokter3"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
           
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat1"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
               
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat2"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
               
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat3"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
               
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat4"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat5"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

	    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat6"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

	    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat7"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

	    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat8"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

	    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat9"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

	    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["perawat10"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 

                   
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["petugas_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }    
     }
     
     $colspan = count($tbHeader[0]);    
     
?>

<?php echo $view->RenderBody("inosoft.css",false); ?>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</form>
<?php echo $view->SetFocus("btnAdd"); ?>
<?php echo $view->RenderBodyEnd(); ?>
