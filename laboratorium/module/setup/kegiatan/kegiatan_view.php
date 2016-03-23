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
     $thisPage = "kegiatan_view.php";
     $hiddenPage = "kegiatan_view_hidden.php";

     if(!$auth->IsAllowed("laboratorium",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("laboratorium",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     $plx = new InoLiveX("GetData,RenderPaging");

     //*-- config table ---*//
     $tableHeader = "&nbsp;Layanan Pemeriksaan";
     
     // --- construct new table ---- //
     function GetData($limit=20, $offset=0, $order_by=null) {
          global $table, $dtaccess, $enc, $auth, $APLICATION_ROOT, $editPage;

          $isAllowedDel = $auth->IsAllowed("laboratorium",PRIV_DELETE);
          $isAllowedUpdate = $auth->IsAllowed("laboratorium",PRIV_UPDATE);
          $isAllowedCreate = $auth->IsAllowed("laboratorium",PRIV_CREATE);
          
          $sql = "select a.*,b.kategori_nama,c.bonus_nama from lab_kegiatan a 
                  left join lab_kategori b on b.kategori_id = a.id_kategori
                  left join lab_bonus c on c.bonus_id = a.id_bonus ";
          $sql .= ($order_by != null) ? " order by ".$order_by.", kegiatan_nama" : "";
          $sql .= " limit ".$limit." offset ".$offset;
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);
          $dataTable = $dtaccess->FetchAll($rs);
          
          $counterHeader = 0;
          if($isAllowedDel){
               $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
               $counterHeader++;
          }
          
          if($isAllowedUpdate){
               $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
               $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
               $counterHeader++;
          }
             
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<a href=\"#\" onclick=\"LihatData(".$limit.",".$offset.",'kegiatan_nama');\" style=\"text-decoration: none; color: #FFFFFF;\" title=\"Urutkan berdasar nama\">Nama</a>";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
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
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Aktif";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++; 
          
          for($i=0,$j=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$j++,$counter=0){
            if($dataTable[$i]["kategori_nama"]!=$dataTable[$i-1]["kategori_nama"]){
              $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$dataTable[$i]["kategori_nama"];            
              $tbContent[$j][$counter][TABLE_CLASS] = "tablesmallheader";
              $tbContent[$j][$counter][TABLE_ALIGN] = "left";
              $tbContent[$j][$counter][TABLE_COLSPAN] = count($tbHeader[0]);
              $counter=0; $j++;
            }

               if ($dataTable[$i]["is_active"] == 'n') {
                $classnya = "tablecontent-disabled";
               }else{
                $classnya = "";
               }

               if($isAllowedDel) {
                    $tbContent[$j][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["kegiatan_id"].'">';
                    // $tbContent[$j][$counter][TABLE_CLASS] = $classnya;
                    $tbContent[$j][$counter][TABLE_ALIGN] = "center";
                    $counter++;
               }
               
               if($isAllowedUpdate) {
                    $tbContent[$j][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["kegiatan_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'images/b_edit.png" alt="Edit" title="Edit" border="0"></a>';
                    // $tbContent[$j][$counter][TABLE_CLASS] = $classnya;
                    $tbContent[$j][$counter][TABLE_ALIGN] = "center";
                    $counter++;
               }

               $normalValue = "";

               if ($dataTable[$i]["kegiatan_nilai_normal_dewasa_laki"]) $normalValue = $dataTable[$i]["kegiatan_nilai_normal_dewasa_laki"];
               elseif (($dataTable[$i]["kegiatan_nilai_normal_dewasa_laki"]) && ($dataTable[$i]["kegiatan_nilai_normal_dewasa_wanita"])) $normalValue = "L: ".$dataTable[$i]["kegiatan_nilai_normal_dewasa_laki"]."<br />".
                               "P: ".$dataTable[$i]["kegiatan_nilai_normal_dewasa_wanita"];

               if ($dataTable[$i]["kegiatan_nilai_normal_dewasa_anak"]) $normalValue .= "<br />A: ".$dataTable[$i]["kegiatan_nilai_normal_dewasa_anak"];
               

               $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kegiatan_nama"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               // $tbContent[$j][$counter][TABLE_CLASS] = $classnya;
               $counter++; 
          
               $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$normalValue;
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               // $tbContent[$j][$counter][TABLE_CLASS] = $classnya;
               $counter++; 
          
               $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kegiatan_satuan"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "center";
               // $tbContent[$j][$counter][TABLE_CLASS] = $classnya;
               $counter++; 
          
               $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["bonus_nama"];
               $tbContent[$j][$counter][TABLE_ALIGN] = "left";
               // $tbContent[$j][$counter][TABLE_CLASS] = $classnya;
               $counter++; 
          
               $tbContent[$j][$counter][TABLE_ISI] = "Rp.&nbsp;".currency_format($dataTable[$i]["kegiatan_biaya"])."&nbsp;&nbsp;";
               $tbContent[$j][$counter][TABLE_ALIGN] = "right";
               // $tbContent[$j][$counter][TABLE_CLASS] = $classnya;
               $counter++; 

                $aktifnya = ($dataTable[$i]["is_active"]=='n')?"NON-AKTIF":"AKTIF";
               $tbContent[$j][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$aktifnya;
               $tbContent[$j][$counter][TABLE_ALIGN] = "left";
               $tbContent[$j][$counter][TABLE_CLASS] = $classnya;
               $counter++; 
          
          }
          
          $colspan = count($tbHeader[0]);
     
          
          if($isAllowedDel) {
               $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
          }
          
          if($isAllowedCreate) {
               $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          }
          
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;

          return $table->RenderView($tbHeader,$tbContent,$tbBottom);
          // return $sql;
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
                      left join laboratorium.lab_kategori c on c.kategori_id = a.id_kategori";
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
          <div style="display:inline-block; margin-left:80%; margin-top:-18px;"><a href="<?php echo $hiddenPage;?>">Lihat Kegiatan</a> yang telah dihapus</div>
          <?php echo $view->RenderHidden("tot_record","tot_record",$_POST["tot_record"]); ?>
     </div>
</form>

<?php echo $view->RenderBodyEnd(); ?>
