<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","98%","left");
 
     $editPage = "kegiatan_edit.php";
     $thisPage = "kegiatan_view_hidden.php";
     $viewPage = "kegiatan_view.php";

     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     $plx = new InoLiveX("GetData,RenderPaging");

     //*-- config table ---*//
     $tableHeader = "&nbsp;Layanan Pemeriksaan yang Tidak Aktif";
     
     // --- construct new table ---- //
     function GetData($limit=20, $offset=0, $order_by=null) {
          global $table, $dtaccess, $enc, $auth, $APLICATION_ROOT, $editPage, $viewPage;

          $isAllowedDel = $auth->IsAllowed("laboratorium",PRIV_DELETE);
          $isAllowedUpdate = $auth->IsAllowed("laboratorium",PRIV_UPDATE);
          $isAllowedCreate = $auth->IsAllowed("laboratorium",PRIV_CREATE);
          
          $sql = "select * from lab_kegiatan a 
                  left join lab_kategori b on b.kategori_id = a.id_kategori
                  left join lab_bonus c on c.bonus_id = a.id_bonus
                  where a.is_active = 'n' ";
          $sql .= ($order_by != null) ? " order by ".$order_by : " order by kategori_nama ASC, kegiatan_nama ASC ";
          $sql .= " limit ".$limit." offset ".$offset;
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          $dataTable = $dtaccess->FetchAll($rs);
          echo $sql;
          $counterHeader = 0;
          if($isAllowedDel){
               $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbEnable[]');\">";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
               $counterHeader++;
          }
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<a href=\"#\" onclick=\"LihatData(".$limit.",".$offset.",'kegiatan_nama');\" style=\"text-decoration: none; color: #FFFFFF;\" title=\"Urutkan berdasar nama\">Nama</a>";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++; 
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nilai Normal";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++; 
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Satuan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++; 
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<a href=\"#\" onclick=\"LihatData(".$limit.",".$offset.",'kategori_nama');\" style=\"text-decoration: none; color: #FFFFFF;\"  title=\"Urutkan berdasar kategori\">Kategori</a>";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++; 
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<a href=\"#\" onclick=\"LihatData(".$limit.",".$offset.",'kegiatan_biaya');\" style=\"text-decoration: none; color: #FFFFFF;\" title=\"Urutkan berdasar biaya\">Biaya</a>";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++; 
          
          for($i=0,$j=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$j++,$counter=0){
               if($isAllowedDel) {
                    $tbContent[$j][$counter][TABLE_ISI] = '<input type="checkbox" name="cbEnable[]" value="'.$dataTable[$i]["kegiatan_id"].'">';               
                    $tbContent[$j][$counter][TABLE_ALIGN] = "center";
                    $counter++;
               }
               
               if ($dataTable[$i]["kegiatan_nilai_normal_dewasa_laki"]) $normalValue = $dataTable[$i]["kegiatan_nilai_normal_dewasa_laki"];
               elseif (($dataTable[$i]["kegiatan_nilai_normal_dewasa_laki"]) && ($dataTable[$i]["kegiatan_nilai_normal_dewasa_wanita"])) $normalValue = "L: ".$dataTable[$i]["kegiatan_nilai_normal_dewasa_laki"]."<br />".
                               "P: ".$dataTable[$i]["kegiatan_nilai_normal_dewasa_wanita"];

               if ($dataTable[$i]["kegiatan_nilai_normal_dewasa_anak"]) $normalValue .= "<br />A: ".$dataTable[$i]["kegiatan_nilai_normal_dewasa_anak"];
               
               $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kegiatan_nama"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++; 
          
               $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$normalValue;
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++; 
          
               $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kegiatan_satuan"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               $counter++; 
          
               $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["bonus_nama"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "left";
               $counter++; 
          
               $tbContent[$j][$counter][TABLE_ISI] = "Rp.&nbsp;".currency_format($dataTable[$i]["kegiatan_biaya"])."&nbsp;&nbsp;";
               $tbContent[$j][$counter][TABLE_ALIGN] = "right";
               $counter++; 
          }
          
          $colspan = count($tbHeader[0]);
     
          
          if($isAllowedDel) {
               $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnEnable" value="Aktifkan" class="button">&nbsp;';
          }
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btBack" value="Kembali" class="button" onClick="document.location.href=\''.$viewPage.'\';" >&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;

          return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }

     function RenderPaging($in_totRecord, $in_recPerPage=20, $in_currPage=1, $in_orderBy=null)
     {
          $pagingMax = 10;
          $selisih = 3;
          if(!$in_orderBy) $in_orderBy = "kategori_nama, kegiatan_nama";

          $nextPrev = $in_currPage - 1;
       
          if ($in_currPage == 1 || $in_totRecord == 0) $strPaging .= " Awal Sebelum ";
          else {
            $strPaging .= "<a href=\"#\" onclick=\"LihatData(".$in_recPerPage.",'0','".$in_orderBy."','1')\">Awal</a>\n";
            $strPaging .=  "<a href=\"#\" onclick=\"LihatData(".$in_recPerPage.",".($nextPrev - 1) * $in_recPerPage.",'".$in_orderBy."',".($in_currPage - 1).")\">Sebelumnya</a>\n";
          }
          $strPaging .= " | \n";

          $pagingNum = $in_totRecord / $in_recPerPage;

          if (strtoupper($in_recPerPage) != "ALL") $pagingNum = ceil($pagingNum);
          else  $pagingNum = 1;

          if($pagingNum <= $pagingMax) $atas = 1;
          else{
            $sisa = $in_currPage + $selisih; 
            $sisa2 = $sisa - $pagingMax;
            if($sisa2 <= 0) $atas = 1;
            elseif($sisa<=$pagingNum) $atas = $sisa2 + 1;   
            else $atas = $pagingNum - $pagingMax + 1;
          }

          if($pagingNum <= $pagingMax)  $bawah = $pagingNum;
          else $bawah=($atas+$pagingMax-1);

          for ($i=$atas; $i<=$bawah; $i++) {
            if ($i == $in_currPage) $strPaging .= $i." \n"; 
            else $strPaging .= "<a href=\"#\" onclick=\"LihatData(".$in_recPerPage.",".(($i - 1) * $in_recPerPage).",'".$in_orderBy."',".$i.")\">".$i."</a> \n" ;
          }
          $strPaging .= " | \n";

          if ($in_currPage == $pagingNum || $in_totRecord == 0) $strPaging .= "Berikutnya Akhir\n"; 
          else {
            $strPaging .= "<a href=\"#\" onclick=\"LihatData(".$in_recPerPage.",".(($nextPrev + 1) * $in_recPerPage).",'".$in_orderBy."',".($in_currPage + 1).")\">Berikutnya</a>\n";
            $strPaging .= "<a href=\"#\" onclick=\"LihatData(".$in_recPerPage.",".($pagingNum - 1) * $in_recPerPage.",'".$in_orderBy."',".$pagingNum.")\">Akhir</a>\n";
          }

          return $strPaging;
        }

        $sql_count = "select count(*) as jumlah_pemeriksaan from laboratorium.lab_kegiatan a
                      left join laboratorium.lab_bonus b on b.bonus_id = a.id_bonus
                      left join laboratorium.lab_kategori c on c.kategori_id = a.id_kategori
                      where a.is_active='n'";
        $rs_count = $dtaccess->Execute($sql_count);
        $count_data = $dtaccess->Fetch($rs_count);
        $_POST["tot_record"] = $count_data["jumlah_pemeriksaan"];

?>

<?php echo $view->RenderBody("inosoft.css",false,"onload=\"LihatData(20,0,'kategori_nama',1);\""); ?>
<script type="text/javascript">
<?php $plx->Run() ?>

function LihatData(limit,offset,order_by,curr_page){
     var tot_record = document.getElementById('tot_record').value;
     GetData(limit,offset,order_by,'target=dv_hasil');
     RenderPaging(tot_record,limit,curr_page,order_by,'target=dv_paging');
}

</script>
<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <div style="width: 98%">
          <div id="dv_hasil"></div>
          <div>
               <label>Perlihatkan data</label>&nbsp;
               <select name="view_data" id="view_data" onchange="LihatData(this.value,0,'kategori_nama',0)">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="30">30</option>
                    <option value="40">40</option>
                    <option value="50">50</option>
               </select>
          </div>
          <div id="dv_paging"></div>
          <?php echo $view->RenderHidden("tot_record","tot_record",$_POST["tot_record"]); ?>
          <?php //echo $_POST["tot_record"]."&nbsp;".ceil($_POST["tot_record"]/20);?>
     </div>
</form>

<?php echo $view->RenderBodyEnd(); ?>
